<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both
 * the public-facing side of the site and the admin area.
 *
 * @link       https://developer.akamai.com
 * @since      0.1.0
 *
 * @package    Akamai
 */

use \Akamai\Open\EdgeGrid\Authentication as Akamai_Auth;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1.0
 * @package    Akamai
 * @subpackage Akamai/includes
 * @author     Davey Shafik <dshafik@akamai.com>
 */
class Akamai {
	/**
	 * Plugin version.
     *
     * @since 0.1.0
     * @var   string $version ...
	 */
	public $version = '0.7.0';

	/**
     * The unique identifier of this plugin.
	 *
     * @since 0.1.0
     * @var   string $plugin_name The string used to uniquely identify this
	 *               plugin.
	 */
	public $plugin_name;

	/**
     * The basename for the plugin core class file.
	 *
     * @since 0.7.0
     * @var   string $plugin_name The basename for the plugin core class file.
	 */
	public $plugin_basename;

    /**
     * The loader that's responsible for maintaining and registering all hooks
     * that power the plugin.
     *
     * @since 0.1.0
     * @var   Akamai_Loader $loader Maintains and registers all hooks for the
	 *                      plugin.
     */
    public $loader;

    /**
     * A reference to the admin class instance.
     *
     * @since  0.7.0
     * @var    Akamai_Admin $admin The admin class instance.
     */
    public $admin;

    /**
     * A reference to the purge class instance.
     *
     * @since  0.7.0
     * @var    Akamai_Purge $purge The purge class instance.
     */
    public $purge;

	/**
	 * Default credentials settings.
	 *
	 * @since   0.7.0
	 * @var     array $default_credentials The default credentials settings.
	 */
	public $default_credentials = [
		'host'          => '',
		'access-token'  => '',
		'client-token'  => '',
		'client-secret' => '',
	];

	/**
	 * Default options settings.
	 *
	 * @since 0.7.0
	 * @var   array $default_options The default options settings.
	 */
	public $default_options = [
		// 'hostname'              => ..., // Handled in Akamai::get_settings().
		'unique-sitecode'       => '',
		'log-errors'            => 0,
		'log-purges'            => 0,
		'emit-cache-headers'    => 0,
		'emit-cache-tags'       => 0,
		'cache-default-headers' => '',
		'cache-related-tags'    => 1,
		'purge-network'         => 'all',
		'purge-type'            => 'invalidate',
		'purge-method'          => 'tags',
		'purge-related'         => 1,
		'purge-default'         => 1,
		'purge-on-comment'      => 0,
	];

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function __construct() {
		$this->plugin_name = 'akamai';
		$this->plugin_basename = plugin_basename(
			plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );

		$this->load_dependencies();
		$this->loader = new Akamai_Loader();
		$this->admin = new Akamai_Admin( $this );
		$this->purge = Akamai_Purge::instance( $this );

		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  0.1.0
	 * @access private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-akamai-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-akamai-purge.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-akamai-purge-request.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-akamai-cache-tags.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-akamai-admin.php';
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  0.1.0
	 * @access private
	 */
	private function define_admin_hooks() {
		// Add Admin/Settings menu hooks to the plugin.
		$this->loader->add_action(
			'admin_enqueue_scripts', $this->admin, 'enqueue_styles' );
		$this->loader->add_action(
			'admin_enqueue_scripts', $this->admin, 'enqueue_scripts' );
		$this->loader->add_action(
			'admin_menu', $this->admin, 'add_plugin_admin_menu' );
		$this->loader->add_filter(
			"plugin_action_links_{$this->plugin_basename}",
			$this->admin,
			'add_action_links'
		);

		// Save/update plugin options; load error messages on settings page.
		$this->loader->add_action(
			'admin_init', $this->admin, 'settings_update' );
		$this->loader->add_action(
			"load-{$this->admin->menu_page_id}", $this->admin, 'settings_load' );

		// Validate Credentials AJAX.
		$this->loader->add_action(
			'wp_ajax_akamai_verify_credentials',
			$this->admin,
			'handle_verify_credentials_request'
		);

		// Purging Actions/Hooks.
		// TODO: move the hooks defined in Akamai_Purge here.
		$this->loader->add_action(
			'admin_notices', $this->purge, 'display_purge_notices' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.1.0
	 */
	public function run() {
		$this->loader->run();
    }

	/**
	 * Get an Akamai settings array, ensuring that defaults are all set.
	 * Optionally pass a subset of an Akamai settings array to override
	 * the saved option values.
	 *
	 * @since  0.7.0
	 * @param  array $settings Optional. An Akamai settings array subset.
	 * @return array A complete Akamai settings array.
	 */
    public function get_settings( $new_settings = [] ) {
        $settings = [];

        $old_settings = get_option( $this->plugin_name );

        foreach ( $this->default_options as $key => $default_value ) {
            $settings[$key] =
                isset( $new_settings[$key] )
                    ? $new_settings[$key]
                    : ( isset( $old_settings[$key] )
                        ? $old_settings[$key]
                        : $default_value );
		}
		foreach ( $this->default_credentials as $key => $default_cred ) {
            $settings['credentials'][$key] =
                isset( $new_settings['credentials'][$key] )
				    ? $new_settings['credentials'][$key]
                    : ( isset( $old_settings['credentials'][$key] )
                        ? $old_settings['credentials'][$key]
						: $default_cred );
        }

        // A more dynamic default...
        if ( isset( $new_settings['hostname'] ) ) {
            $settings['hostname'] = $new_settings['hostname'];
        } elseif ( isset( $old_settings['hostname'] ) ) {
            $settings['hostname'] = $old_settings['hostname'];
        } else {
            $wpurl = parse_url( get_bloginfo( 'wpurl' ) );
            $settings['hostname'] = $wpurl['host'];
        }

        return $settings;
    }

	/**
	 * A helper to extract plugin option settings. Allows us to use an
	 * updated list of options (may or may not be complete) to get it.
	 *
	 * @since	0.7.0
	 * @param	string	$option_name The setting name.
	 * @param	array	$new_options Optional. An Akamai settings array subset
	 *                  to override system settings.
	 * @return	mixed	The setting value, or default if not set.
	 */
	public function setting( $option_name, $new_options = [] ) {
		$options = $this->get_settings( $new_options );
		return isset( $options[$option_name] )
			? $options[$option_name]
			: null;
	}

	/**
	 * A helper to extract plugin credential settings.
	 *
	 * @since	0.7.0
	 * @param	string	$credential_name The setting name.
	 * @param	array	$new_options Optional. An Akamai settings array subset
	 *                  to override system settings.
	 * @return	mixed	The setting value, or default if not set.
	 */
	public function credential( $credential_name, $new_options = [] ) {
		$options = $this->get_settings( $new_options );
		return isset( $options['credentials'][$credential_name] )
			? $options['credentials'][$credential_name]
			: null;
    }

    /**
     * Generate a plugin-specific user agent for sending API requests.
     *
     * @since  0.7.0
     * @return string A user agent entry.
     */
    public function get_user_agent() {
        return
            'WordPress/' . get_bloginfo( 'version' ) . ' ' .
            get_class( $this ) . '/' . $this->version . ' ' .
            'PHP/' . phpversion();
    }

	/**
	 * Handle generating an EdgeGrid auth client based on specific credentials,
	 * without having to set env vars or upload an .edgerc file. It's a bit of a
	 * hack, but the auth class does not provide a more direct way initializing
	 * other than to load the .edgerc file.
	 *
	 * @since  0.7.0
	 * @param  array       $credentials Optional. An array of credentials to use
	 *                     when generating the auth client.
	 * @return Akamai_Auth ...
	 */
	public function get_edge_auth_client( $credentials = [] ) {
		$_ENV['AKAMAI_DEFAULT_HOST'] = isset( $credentials['host'] )
			? $credentials['host']
			: $this->credential( 'host' );
		$_ENV['AKAMAI_DEFAULT_ACCESS_TOKEN'] = isset( $credentials['access-token'] )
			? $credentials['access-token']
			: $this->credential( 'access-token' );
		$_ENV['AKAMAI_DEFAULT_CLIENT_TOKEN'] = isset( $credentials['client-token'] )
			? $credentials['client-token']
			: $this->credential( 'client-token' );
		$_ENV['AKAMAI_DEFAULT_CLIENT_SECRET'] = isset( $credentials['client-secret'] )
			? $credentials['client-secret']
			: $this->credential( 'client-secret' );
		return Akamai_Auth::createFromEnv();
	}

	/**
     * Send a credential verification request to the Fast Purge v3 API.
     *
     * @since  0.7.0
	 * @param  array $settings Optional. An Akamai settings array subset.
     * @return array A normalized Akamai API response.
     */
	public function purge_api_test( $settings = [] ) {
        $credentials = [];
        if ( isset( $settings['credentials'] ) ) {
            $credentials = $settings['credentials'];
        }
		$client = new Akamai_Purge_Request(
            $this->get_edge_auth_client( $credentials ),
            $this->get_user_agent()
		);
        return $client->test_creds( $log_purges = $settings['log-purges'] );
	}
}
