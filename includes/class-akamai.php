<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://developer.akamai.com
 * @since      0.1.0
 *
 * @package    Akamai
 * @subpackage Akamai/includes
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
	 * Plugin Version Number
	 */
	const VERSION = '0.7.0';

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      Akamai_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

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

		$this->load_dependencies();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Akamai_Loader:         orchestrates the hooks of the plugin.
	 * - Akamai_Admin:          defines all hooks for the admin area.
	 * - Akamai_Purge:          handles purging behavior.
	 * - Akamai_Purge_Request:  dispatches and actual purge request.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-akamai-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-akamai-purge.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-akamai-purge-request.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-akamai-cache-tags.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-akamai-admin.php';

		$this->loader = new Akamai_Loader();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		// Add Settings link to the plugin
		$plugin_admin = new Akamai_Admin( $this->get_plugin_name(), $this->get_version(), $this );
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

		// Save/update plugin options; load error messages on settings page.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'options_update' );
		$this->loader->add_action( "load-{$plugin_admin->menu_page_id}", $plugin_admin, 'options_load' );

		// Validate Credentials AJAX.
		$this->loader->add_action( 'wp_ajax_akamai_verify_credentials', $plugin_admin, 'handle_verify_credentials_request' );

		// Purging Actions/Hooks.
		$plugin_purge = Akamai_Purge::instance( $this );
		$this->loader->add_action( 'admin_notices', $plugin_purge, 'display_purge_notices' );
		// $this->loader->add_action( 'save_post', $this, 'purgeOnPost' );
		// $this->loader->add_action( 'comment_post', $this, 'purgeOnPost', 10, 3 );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return self::VERSION;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.1.0
	 * @return    Akamai_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * A helper to extract plugin option settings.
	 *
	 * @since	0.7.0
	 * @param	string	$option_name The setting name.
	 * @return	mixed	The setting value, or default if not set.
	 */
	public function get_opt( $option_name ) {
		$options = get_option( $this->plugin_name );
		return isset( $options[$option_name] )
			? $options[$option_name]
			: Akamai_Admin::$default_options[$option_name];
	}

	/**
	 * A helper to extract plugin credential settings.
	 *
	 * @since	0.7.0
	 * @param	string	$credential_name The setting name.
	 * @return	mixed	The setting value, or default if not set.
	 */
	public function get_cred( $credential_name ) {
		$options = get_option( $this->plugin_name );
		$options['credentials'] = isset( $options['credentials'] )
			? $options['credentials']
			: [];
		return isset( $options['credentials'][$credential_name] )
			? $options['credentials'][$credential_name]
			: Akamai_Admin::$default_credentials[$credential_name];
	}

	/**
	 * A helper to extract plugin setting for debug-mode.
	 *
	 * @since	0.7.0
	 * @return	bool	If debug-mode enabled.
	 */
	public function debug_mode( $incoming_settings = null ) {
		$debug_mode = false;
		if ( ! empty( $incoming_settings ) && is_array( $incoming_settings ) ) {
			$debug_mode = (bool) $incoming_settings['debug-mode'];
		} else {
			$debug_mode = (bool) $this->get_opt( 'debug-mode' );
		}
		return $debug_mode;
	}

	/**
	 * Handle generating an EdgeGrid auth client based on specific credentials,
	 * without having to set env vars or upload an .edgerc file. It's a bit of a
	 * hack, but the auth class does not provide a more direct way initializing
	 * other than to load the .edgerc file.
	 *
	 * @since	0.7.0
	 * @param	array	$credentials Optional. An array of credentials to use
	 *                  when generating the auth client. Defaults to [].
	 * @return	Akamai_Auth
	 */
	public function get_edge_auth_client( $credentials = [] ) {
		$_ENV['AKAMAI_DEFAULT_HOST'] = isset( $credentials['host'] )
			? $credentials['host']
			: $this->get_cred( 'host' );
		$_ENV['AKAMAI_DEFAULT_ACCESS_TOKEN'] = isset( $credentials['access-token'] )
			? $credentials['access-token']
			: $this->get_cred( 'access-token' );
		$_ENV['AKAMAI_DEFAULT_CLIENT_TOKEN'] = isset( $credentials['client-token'] )
			? $credentials['client-token']
			: $this->get_cred( 'client-token' );
		$_ENV['AKAMAI_DEFAULT_CLIENT_SECRET'] = isset( $credentials['client-secret'] )
			? $credentials['client-secret']
			: $this->get_cred( 'client-secret' );
		return Akamai_Auth::createFromEnv();
	}

	/**
	 * @return string
	 */
	public function get_hostname($options) {
		if (isset($options['hostname'])) {
			return $options['hostname'];
		}

		$wpurl = parse_url( get_bloginfo( 'wpurl' ) );
		$host  = $wpurl['host'];

		return $host;
	}
}
