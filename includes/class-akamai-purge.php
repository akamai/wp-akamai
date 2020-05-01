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
     * Instantiate or return the one Akamai_Purge instance.
     *
     * @since  0.7.0
     * @param  string       $akamai The Akamai class instance.
     * @return Akamai_Purge The created instance.
     */
    public static function instance( $akamai ) {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self( $akamai );
        }
        return self::$instance;
    }

	/**
	 * A reference to the Akamai Plugin class instance.
	 *
	 * @since 0.7.0
	 * @var   string $plugin The Akamai Plugin class instance.
	 */
	public $plugin;

    /**
     * Initiate actions.
     *
     * @param string $plugin The Akamai Plugin class instance.
     * @since 0.7.0
     */
    public function __construct( $plugin ) {
        $this->plugin = $plugin;

        // TODO, send these back to the plugin loader.
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
     * Callback for post changing events to trigger purges. THIS IS A WIP.
     *
     * @since 0.7.0
     * @param int    $post_id The post ID for the triggered post.
     * @param string $action The action that triggered the purge.
     */
    public function purge_post( $post_id, $action ) {
        // Only run once per request.
        if ( did_action( 'akamai_to_purge_post' ) ) {
            return;
        }
        $purge_post_statuses = apply_filters(
            'akamai_purge_post_statuses',
            [ 'publish', 'trash', 'future', 'draft' ]
        );
        if ( ! in_array( get_post_status( $post_id ), $purge_post_statuses ) ) {
            return;
        }

        $settings = $this->plugin->get_settings();

        // Generate objects to query. TODO: break out, switch on purge method.
        $cache_tags =
            Akamai_Cache_Tags::instance( $this->plugin )->get_tags_for_purge_post( $post_id );
        $purge_info = [
            'action'      => $action,
            'target-type' => 'post-' . get_post_type( $post_id ),
            'target-post' => get_post( $post_id ),
            'cache-tags'  => $cache_tags,
            'purge-type'  => 'invalidate',
        ];
        $purge_params = array_values( $purge_info );
        if ( ! apply_filters( 'akamai_do_purge', true, ...$purge_params ) ) {
            return;
        }

        do_action( 'akamai_to_purge', ...$purge_params );
        do_action( 'akamai_to_purge_post', ...$purge_params );
        $client = new Akamai_Purge_Request(
            $this->plugin->get_edge_auth_client(),
            $this->plugin->get_user_agent()
		);
        $response = $client->purge(
            $options = $settings,
            $objects = $cache_tags
        );
        do_action( 'akamai_purged_post', $response, ...$purge_params );

        if ( $response['error'] ) {
            add_filter(
                'redirect_post_location',
                [ $this, 'add_error_query_arg' ],
                100
            );
        } else {
            add_filter(
                'redirect_post_location',
                [ $this, 'add_success_query_arg' ],
                100
            );
        }
    }

    /**
     * Add query args to set notices and other changes after a submit/update
     * that triggered a purge. MERGE WITH BELOW.
     *
     * By removing itself after running, it ensures that the hook is run
     * dynamically and once.
     *
     * @since  0.1.0
     * @param  string $location The Location header of the redirect: passed in
     *                by the filter hook.
     * @param  string $response The HTTP response code of the redirect: passed
     *                in by the filter hook.
     * @return string
     */
    public function add_error_query_arg( $location, $response ) {
        remove_filter(
            'redirect_post_location', [ $this, 'add_error_query_arg' ], 100 );
        return add_query_arg(
            [ 'akamai-cache-purge-error' => urlencode( $response['error'] ) ],
            $location
        );
    }

    /**
     * Add query args to set notices and other changes after a submit/update
     * that triggered a purge. MERGE WITH ABOVE.
     *
     * By removing itself after running, it ensures that the hook is run
     * dynamically and once.
     *
     * @since  0.1.0
     * @param  string $location The Location header of the redirect: passed in
     *                by the filter hook.
     * @return string The updated location.
     */
    public function add_success_query_arg( $location ) {
        remove_filter(
            'redirect_post_location', [ $this, 'add_success_query_arg' ], 100 );
        return add_query_arg(
            [ 'akamai-cache-purge-success' => 'true' ],
            $location
        );
    }

    /**
     * Checks if queries have been set to create notices in the current page
     * load, and if so display them.
     */
    public function display_purge_notices() {
        if ( isset( $_GET['akamai-cache-purge-error'] ) ) {
            ?>
            <div class="error notice is-dismissible">
                <p>
                    <img src="<?= Akamai_Admin::get_icon(); ?>"
                         style="height: 1em" alt="Akamai for WordPress">&nbsp;
                    <?php esc_html_e(
                        'Akamai: unable to purge cache: ' .
                        $_GET['akamai-cache-purge-error'], 'akamai' ); ?>
                </p>
            </div>
            <?php
        }
        if ( isset( $_GET['akamai-cache-purge-success'] ) ) {
            ?>
            <div class="updated notice notice-success is-dismissible">
                <p>
                    <img src="<?= Akamai_Admin::get_icon(); ?>"
                         style="height: 1em" alt="Akamai for WordPress">&nbsp;
                    <?php esc_html_e(
                        'Akamai: all related cache objects successfully purged.',
                        'akamai' ); ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * A list of post actions to initiate purge.
     *
     * @since  0.7.0
     * @return array List of actions.
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
     * @return array List of actions.
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
