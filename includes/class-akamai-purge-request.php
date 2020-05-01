<?php

/**
 * The file that implements the plugin's purge action.
 *
 * @link    https://developer.akamai.com
 * @since   0.7.0
 * @package Akamai
 */

 use \Akamai\Open\EdgeGrid\Authentication as Akamai_Auth;

/**
 * The class for implementing purge actions. It issues purge requests for a
 * given resource. It will handle purging by individual URL, by "cache tag" (ie
 * surrogate key), and sitewide and multi-site "property" purges.
 *
 * Inspired by \Purgely_Purge from the Fastly WP plugin, with basic interaction
 * taken from the core plugin class (Akamai).
 *
 * @since   0.7.0
 * @package Akamai
 */
class Akamai_Purge_Request {
    /**
     * Takes some information (either a WP response array only, or a specific
     * error, or both), and returns a normalized response information array so
     * that consumers of the API can make simple assumptions about what to do
     * with the result.
     *
     * @since  0.7.0
     * @param  array       $wp_response A WP response array.
     * @param  bool        $success Optional. Specifically set response outcome.
     *                     Defaults to true.
     * @param  string|null $error Optional. Specifically set error message.
     *                     Defaults to null.
     * @return array       A normalized Akamai API response.
     */
    public static function normalize_response(
        $wp_response, $success = true, $error = null ) {
        if ( ! empty( $wp_response ) ) {
            if ( $wp_response instanceof \WP_Error ) {
                $success = false;
                $error = $wp_response->get_error_message();
            } else {
                try {
                    unset( $wp_response['http_response'] );
                    $code = $wp_response['response']['code'];
                    if ( $code < 200 || $code >= 300 ) {
                        $success = false;

                        // Attempt to uncover akamai error detail, fall back on
                        // HTTP message.
                        $message = null;
                        if (
                            $wp_response['body']
                            && $body = json_decode( $wp_response['body'] )
                        ) {
                            if ( isset( $body->detail ) ) {
                                $message = $body->detail;
                            }
                        }

                        if ( ! empty( $message ) ) {
                            $error = "AKAMAI_API_ERROR: {$message}.";
                        } else {
                            $error =
                                "HTTP_ERROR: {$wp_response['response']['code']}" .
                                " – {$wp_response['response']['message']}.";
                        }
                    }
                } catch ( \Exception $e ) {
                    // My kingdom for a typed language...
                    $success = false;
                    $error =
                        'AKAMAI_PLUGIN_INTERNAL: ' .
                        'error or invalid WP_HTTP_Response returned';
                }
            }
        }
        return [
            'success'  => $success,
            'response' => $wp_response,
            'error'    => $error,
        ];
    }

    /**
     * The Akamai EdgeServer auth client to use to generate the request.
     *
     * @since 0.7.0
     * @var   Akamai_Auth $auth An Akamai EdgeServer auth client.
     */
    public $auth;

    /**
     * The user agent to use in the request.
     *
     * @since 0.7.0
     * @var   string $user_agent The user agent description.
     */
    public $user_agent;

    /**
     * Create a new request object with a credentialed auth client.
     *
     * @since  0.7.0
     * @param  Akamai_Auth $auth The Akamai EdgeServer auth client to use.
     * @param  string      $user_agent Optional. The user agent description to
     *                     use. Defaults to the User-Agent used to make the
     *                     request to the server.
     */
    public function __construct( $auth, $user_agent = null ) {
        $this->auth       = $auth;
        $this->user_agent = ! empty( $user_agent )
            ? $user_agent
            : $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Given a correctly setup auth client in the current context, this
     * generates the objects necessary to pass to wp_remote_* functions if we
     * want to use that to send API requests (for whatever reason).
     *
     * @since  0.7.0
     * @return array The URL and request object to pass to wp_remote_*. Must be
     *               spread when sent to the function.
     */
    public function wp_request_from_auth() {
        $request = [
            'https://' . $this->auth->getHost() . $this->auth->getPath(),
            [
                'user-agent' => $this->user_agent,
                'headers' => [
                    'Authorization' => $this->auth->createAuthHeader(),
                ],
            ],
        ];
        $body = $this->auth->getBody();
        if ( ! empty( $body ) ) {
            $request[1]['body'] = $body;
            // For now, assuming JSON.
            if ( empty( $request[1]['headers']['Content-Type'] ) ) {
                $request[1]['headers']['Content-Type'] = 'application/json';
            }
        }
        return $request;
    }

    /**
     * Send a purge request to the Fast Purge v3 API!
     *
     * @param  array $options An Akamai settings array subset of options to set
     *               in the request.
     * @param  array $objects The list of "objects" to purge from the cache.
     * @return array A normalized Akamai API response.
     */
    public function purge( $options, $objects ) {
        if ( ! ( $this->auth instanceof Akamai_Auth ) ) {
            return static::normalize_response(
                $wp_response = null,
                $success = false,
                $error = 'AKAMAI_PLUGIN_INTERNAL: bad auth client'
            );
        }

        // Fall back on defaults instead of erroring.
        $type = isset( $options['purge-type'] )
            ? $options['purge-type']
            : 'invalidate';
        $method = isset( $options['purge-method'] )
            ? $options['purge-method']
            : 'url';
        $network = isset( $options['purge-network'] )
            ? $options['purge-network']
            : 'staging';
        $version = isset( $options['version'] )
            ? $options['version']
            : '-';

        // Transition setting values to actual sent values.
        if ( 'all' === $network ) {
            $network = '';
        }
        if ( 'arl' === $method ) {
            $method = 'url';
        }

        $body = [ 'objects' => $objects ];
        if ( 'url' === $method ) {
            if ( ! isset( $options['hostname'] ) ) {
                // TODO: class Akamai\WP_Plugin\Exception extends \Exception {},
                //       appends AKAMAI_PLUGIN_INTERNAL: ...
                throw new \Exception(
                    'AKAMAI_PLUGIN_INTERNAL: ' .
                    'can not create URL purge request without hostname'
                );
            }
            $body['hostname'] = $options['hostname'];
        }
        $body_json = wp_json_encode( $body );

        $this->auth->setHttpMethod( 'POST' );
        $this->auth->setPath( "/ccu/v3/$type/$method/$network" );
        $this->auth->setBody( $body_json );
        $request = $this->wp_request_from_auth();

        if ( isset( $options['log-purges'] ) && $options['log-purges'] ) {
            error_log(
                print_r(
                    [ 'AKAMAI_PLUGIN_INTERNAL: purge_request' => $request ],
                    true
                )
            );
        }
        $response = static::normalize_response( wp_remote_post( ...$request ) );
        if ( isset( $options['log-purges'] ) && $options['log-purges'] ) {
            error_log(
                print_r(
                    [ 'AKAMAI_PLUGIN_INTERNAL: purge_response' => $response ],
                    true
                )
            );
        }
        return $response;
    }

    /**
     * Send a request to the Akamai API to test if the credentials are valid.
     *
     * @param  bool  Optional. Whether to log the request. Defaults to false.
     * @return array A normalized Akamai API response.
     */
    public function test_creds( $log_purges = false) {
        if ( ! ( $this->auth instanceof Akamai_Auth ) ) {
            return static::normalize_response(
                $wp_response = null,
                $success = false,
                $error = 'AKAMAI_PLUGIN_INTERNAL: bad auth client'
            );
        }

        $this->auth->setHttpMethod( 'GET' );
        $this->auth->setPath( '/-/client-api/active-grants/implicit' );
        $request = $this->wp_request_from_auth();

        if ( $log_purges ) {
            error_log(
                print_r(
                    [ 'AKAMAI_PLUGIN_INTERNAL: creds_verify_request' => $request ],
                    true
                )
            );
        }
        $response = static::normalize_response( wp_remote_get( ...$request ) );
        if ( $log_purges ) {
            error_log(
                print_r(
                    [ 'AKAMAI_PLUGIN_INTERNAL: purge_response' => $response ],
                    true
                )
            );
        }
        return $response;
    }
}
