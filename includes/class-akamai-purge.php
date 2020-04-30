<?php

/**
 * The file that defines the plugin's purges behavior and hooks.
 *
 * @link    https://developer.akamai.com
 * @since   0.7.0
 *
 * @package Akamai
 */

/**
 * The core plugin class for managing purge behavior.
 *
 * Singleton for registering default purges. Based on \Purgely_Purges from
 * the Fastly WP plugin.
 *
 * @since   0.7.0
 * @package Akamai
 */
class Akamai_Purge {
    /**
     * The one instance of Akamai_Purge.
     *
     * @since 0.7.0
     * @var   Akamai_Purge
     */
    private static $instance;

	/**
	 * A reference to the Akamai class instance.
	 *
	 * @since 0.7.0
	 * @var   string $akamai The Akamai class instance.
	 */
	public $akamai;

    /**
     * Instantiate or return the one Akamai_Purge instance.
     *
     * @since  0.7.0
     * @param  string $akamai The Akamai class instance.
     * @return Akamai_Purge
     */
    public static function instance( $akamai ) {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self( $akamai );
        }
        return self::$instance;
    }

    /**
     * Initiate actions.
     *
     * @param string $akamai The Akamai class instance.
     * @since 0.7.0
     */
    public function __construct( $akamai ) {
        $this->akamai = $akamai;

        $instance = $this; // Just to be PHP < 6.0 compliant.
        foreach ( $this->purge_post_actions() as $action ) {
            add_action(
                $action,
                function ( $post_id ) use ( $instance, $action ) {
                    return $instance->purge_post( $post_id, $action );
                },
                10,
                1
            );
        }
        foreach ( $this->purge_term_actions() as $action ) {
            add_action(
                $action,
                function ( $term_id, $tt_id, $taxonomy ) use ( $instance, $action ) {
                    // error_log( [ 'PURGE', $term_id, $tt_id, $taxonomy, $action ] );
                    // FIXME ...
                },
                10,
                3
            );
        }
    }

    /**
     * Callback for post changing events to purge keys.
     *
     * @since  0.7.0
     * @param  int $post_id Post ID.
     * @return void
     */
    public function purge_post( $post_id, $action ) {
        $purge_post_statuses = apply_filters(
            'akamai_purge_post_statuses',
            [ 'publish', 'trash', 'future', 'draft' ]
        );
        if ( ! in_array( get_post_status( $post_id ), $purge_post_statuses ) ) {
            return;
        }

        // 1. Load credentials and generate auth client.
        $auth = $this->akamai->get_edge_auth_client();

        // 2. TODO: check credentials.
        // $test = test_fastly_api_connection( $fastly_hostname, $fastly_service_id, $fastly_api_key );
        // if ( ! $test['status'] ) {
        //     ... LOAD A WARNING ABOUT NO CREDS TO PURGE ...
        // }

        // 3. Load related items.
        $cache_tags = Akamai_Cache_Tags::instance( $this->akamai )->get_tags_for_purge_post( $post_id );

        $purge_info = [
            'action'      => $action,
            'target-type' => 'post-' . get_post_type( $post_id ),
            'target-post' => get_post( $post_id ),
            'cache-tags'  => $cache_tags,
            'purge-type'  => 'invalidate',
        ];

        tpt_log( $purge_info );

        // 4. Check if we really want to go through with this!
        if ( ! apply_filters( 'akamai_do_purge', true, ...array_values( $purge_info ) ) ) {
            return;
        }

        // 5. Send purge.
        do_action( 'akamai_to_purge', ...array_values( $purge_info ) );
        $options  = get_option( $this->akamai->get_plugin_name() );
        $request  = new Akamai_Purge_Request( $auth, $options, $post_id );
        $response = $request->purge();
        do_action( 'akamai_purged', $response, ...array_values( $purge_info ) );

        // 6. Handle response.
        if ( $response instanceof \WP_Error ) {
            $instance = $this; // Just to be PHP < 6.0 compliant.
            add_filter(
                'redirect_post_location',
                function( $location ) use ( $instance, $response ) {
                    $error = (object) [ 'detail' => wp_json_encode( $response->errors ) ];
                    return $instance->add_error_query_arg( $location, $error );
                },
                100
            );
        } elseif ( wp_remote_retrieve_response_code( $response ) !== 201 ) {
            $instance = $this; // Just to be PHP < 6.0 compliant.
            add_filter(
                'redirect_post_location',
                function( $location ) use ( $instance, $response ) {
                    $body = json_decode( wp_remote_retrieve_body( $response ) );
                    return $instance->add_error_query_arg( $location, $body );
                },
                100
            );
        } else {
            add_filter( 'redirect_post_location', [ $this, 'add_success_query_arg' ], 100);
        }
    }

    /**
     * ...
     *
     * By removing itself after running, it ensures that the hook is run
     * dynamically and once.
     *
     * @param  string $location The Location header of the redirect: passed in
     *                by the filter hook.
     * @param  string $response The HTTP response code of the redirect: passed
     *                in by the filter hook.
     * @return string
     */
    public function add_error_query_arg( $location, $response ) {
        remove_filter( 'redirect_post_location', [ $this, 'add_error_query_arg' ], 100 );
        return add_query_arg( [ 'akamai-cache-purge-error' => urlencode( $response->detail ) ], $location );
    }

    /**
     * ...
     *
     * By removing itself after running, it ensures that the hook is run
     * dynamically and once.
     *
     * @param  string $location The Location header of the redirect: passed in
     *                by the filter hook.
     * @return string The updated location.
     */
    public function add_success_query_arg( $location ) {
        remove_filter( 'redirect_post_location', [ $this, 'add_success_query_arg' ], 100 );
        return add_query_arg( [ 'akamai-cache-purge-success' => 'true' ], $location );
    }

    /**
     * ...
     */
    public function display_purge_notices() {
        if ( isset( $_GET['akamai-cache-purge-error'] ) ) {
            ?>
            <div class="error notice is-dismissible">
                <p>
                    <img src="<?= Akamai_Admin::get_icon(); ?>" style="height: 1em" alt="Akamai for WordPress">&nbsp;
                    <?php esc_html_e( 'Akamai: unable to purge cache: ' . $_GET['akamai-cache-purge-error'], 'akamai' ); ?>
                </p>
            </div>
            <?php
        }
        if ( isset( $_GET['akamai-cache-purge-success'] ) ) {
            ?>
            <div class="updated notice notice-success is-dismissible">
                <p>
                    <img src="<?= Akamai_Admin::get_icon(); ?>" style="height: 1em" alt="Akamai for WordPress">&nbsp;
                    <?php esc_html_e( 'Akamai: all related cache objects successfully purged.', 'akamai' ); ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * A list of post actions to initiate purge.
     *
     * @since  0.7.0
     * @return array    List of actions.
     */
    private function purge_post_actions() {
        return apply_filters(
            'akamai_purge_post_actions',
            [
                'save_post',
                'deleted_post',
                'trashed_post',
                'delete_attachment',
                'future_to_publish',
            ]
        );
    }

    /**
     * A list of term actions to initiate purge.
     *
     * @since  0.7.0
     * @return array    List of actions.
     */
    private function purge_term_actions() {
        return apply_filters(
            'akamai_purge_term_actions',
            [
                'edit_term',
                'delete_term',
            ]
        );
    }
}
