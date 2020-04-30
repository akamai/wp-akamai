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
     * ...
     *
     * @since 0.7.0
     * @var   ...
     */
    public $auth;

    /**
     * ...
     *
     * @since 0.7.0
     * @var   ...
     */
    public $body;

    /**
     * ...
     *
     * @since  0.7.0
     * @param  array   $options ...
     * @param  WP_Post $post ...
     */
    public function __construct( $auth, $options, $post ) {
        if ( is_int( $post ) ) {
            $post = get_post( $post );
        }
        try {
            $this->auth = $auth;

            $this->auth->setHttpMethod( 'POST' );
            $this->auth->setPath( '/ccu/v3/invalidate/url' );

            $this->body = $this->get_purge_body( $options, $post );
            $this->auth->setBody( $this->body );
        } catch ( \Exception $e ) {
            // error_log( [ 'purge req excepted' => $e ] );
            // FIXME ...
        }
    }

    // public function chunk_cache_tags( $cache_tags ) {
    //     $num = count( $cache_tags );
    //
    //     // Split keys for multiple requests if needed.
    //     if ( $num >= AKAMAI_MAX_HEADER_KEY_SIZE ) {
    //         $parts = $num / AKAMAI_MAX_HEADER_KEY_SIZE;
    //         $additional = ( $parts > (int) $parts ) ? 1 : 0;
    //         $parts = (int) $parts + (int) $additional;
    //         $chunks = ceil( $num / $parts );
    //         $cache_tags = array_chunk( $cache_tags, $chunks );
    //     } else {
    //         $cache_tags = [ $cache_tags ];
    //     }
    //
    //     return $cache_tags;
    // }

    /**
     * ...
     *
     * @return array   The HTTP response from the request.
     */
    public function purge() {
        if ( ! ( $this->auth instanceof Akamai_Auth ) ) {
            return (object) [ 'detail' => 'Akamai Plugin internal: bad auth client' ];
        }
        return wp_remote_post(
            'https://' . $this->auth->getHost() . $this->auth->getPath(),
            [
                'user-agent' => $this->get_user_agent(),
                'headers' => [
                    'Authorization' => $this->auth->createAuthHeader(),
                    'Content-Type'  => 'application/json',
                ],
                'body' => $this->body,
            ]
        );
    }

    /**
     * ...
     *
     * @param  ... $options ...
     * @param  ... $post    ...
     * @return mixed|string|void
     */
    public function get_purge_body( $options, $post ) {
        $baseUrl   = parse_url( get_bloginfo( 'wpurl' ), PHP_URL_PATH ) . '/';
        $permalink = get_permalink( $post->ID );

        $objects = array(
            $this->get_item_url( $permalink ), // Post
        );

        // TODO: fix me: create a purge front on URL setting and blah.
        $options['purge_front'] = true;
        if ( $options['purge_front'] ) {
            $objects[] = $baseUrl;
        }

        if ( $options['purge_tags'] ) {
            $tags = get_the_tags( $post->ID );
            if ( $tags !== false && ! ( $tags instanceof WP_Error ) ) {
                foreach ( $tags as $tag ) {
                    $objects[] = $this->get_item_url( get_tag_link( $tag ) );
                }
            }
        }

        if ( $options['purge_categories'] ) {
            $categories = get_the_category( $post->ID );
            if ( $categories !== false && ! ( $categories instanceof WP_Error ) ) {
                foreach ( $categories as $category ) {
                    $url       = $this->get_item_url( get_category_link( $category ) );
                    $objects[] = $url;
                }
            }
        }

        if ( $options['purge_archives'] ) {
            $archive = get_month_link( get_post_time( 'Y', false, $post ), get_post_time( 'm', false, $post ) );
            if ( $archive !== false && ! ( $archive instanceof WP_Error ) ) {
                $objects[] = $this->get_item_url( $archive );
            }
        }

        $data = array(
            'hostname' => $options['hostname'],
            'objects'  => $objects
        );

        return wp_json_encode( $data );
    }

    /**
     * ...
     *
     * @since  0.7.0
     * @param  string       $url ...
     * @return mixed|string ...
     */
    public function get_item_url( $url ) {
        $itemUrl = parse_url( $url, PHP_URL_PATH );
        if ( strpos( $url, '?' ) !== false ) {
            $itemUrl .= '?' . parse_url( $url, PHP_URL_QUERY );
        }

        return $itemUrl;
    }


    /**
     * ...
     *
     * @since  0.7.0
     * @return string ...
     */
    public function get_user_agent() {
        return
            'WordPress/' . get_bloginfo( 'version' ) . ' ' .
            'Akamai-for-WordPress/' /* . self::VERSION */ . ' ' .
            'PHP/' . phpversion();
    }
}
