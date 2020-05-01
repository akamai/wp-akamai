<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link    https://developer.akamai.com
 * @since   0.1.0
 *
 * @package Akamai
 */

use \Akamai\Open\EdgeGrid\Authentication as Akamai_Auth;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Akamai
 * @subpackage Akamai/admin
 * @author     Davey Shafik <dshafik@akamai.com>
 */
class Akamai_Admin {

	/**
	 * Get the Akamai logo icon for SVG.
	 *
	 * @since 0.1.0
	 */
	static public function get_icon() {
		return 'data:image/svg+xml;charset=utf-8;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3' .
			   'LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgcHJlc2VydmVBc3BlY3RSYXRp' .
			   'bz0ieE1pZFlNaWQiPg0KICA8Zz4NCiAgIDxwYXRoIGQ9Im0xMC44NTM2NiwxOS4zNzI1OWMtNC4wMzI2MiwtMS4yMzU5NyAtNi45' .
			   'NTQ5NCwtNC45NTQzNSAtNi45NTQ5NCwtOS4zMzI2MWMwLC00LjQ1MTU4IDIuOTk1NjUsLTguMTkwOTIgNy4wOTExMSwtOS40MDU5' .
			   'M2MwLjQxODk3LC0wLjExNTIyIDAuMzAzNzYsLTAuMzk4MDMgLTAuMTk5MDEsLTAuMzk4MDNjLTUuNDM2MTcsMCAtOS44NjY4LDQu' .
			   'Mzc4MjYgLTkuODY2OCw5Ljc3MjU0YzAsNS4zOTQyNyA0LjM5OTIxLDkuNzcyNTQgOS44NjY4LDkuNzcyNTRjMC41MDI3NywwLjAz' .
			   'MTQyIDAuNTIzNzIsLTAuMjUxMzkgMC4wNjI4NSwtMC40MDg1bDAsMGwwLC0wLjAwMDAxem0tNS4wODAwNCwtNy4wNzAxNmMtMC4w' .
			   'MjA5NSwtMC4yNjE4NiAtMC4wNDE5LC0wLjUyMzcyIC0wLjA0MTksLTAuNzk2MDVjMCwtNC4yOTQ0NyAzLjQ3NzQ3LC03Ljc3MTk1' .
			   'IDcuNzcxOTQsLTcuNzcxOTVjNC4wNTM1NiwwIDUuMjg5NTMsMS44MDE1OSA1LjQxNTIyLDEuNjk2ODRjMC4xNTcxMiwtMC4xMzYx' .
			   'NyAtMS40NzY4NywtMy43MTgzOCAtNi4yMzIyMSwtMy43MTgzOGMtNC4yOTQ0NywwIC03Ljc3MTk1LDMuNDc3NDcgLTcuNzcxOTUs' .
			   'Ny43NzE5NGMwLDAuOTk1MDYgMC4xOTkwMSwxLjkzNzc1IDAuNTIzNzIsMi44MTc2YzAuMTM2MTcsMC4zNzcwOCAwLjM1NjEyLDAu' .
			   'Mzc3MDggMC4zMzUxNywwbDAuMDAwMDEsMC4wMDAwMXptMy4yMzY1NywtNS41OTMyOGMyLjAwMDU5LC0wLjg3OTg0IDQuNTU2MzMs' .
			   'LTAuOTAwOCA3LjA0OTIyLC0wLjA0MTljMS42NzU4OSwwLjU5NzA0IDIuNjM5NTMsMS40MTQwNCAyLjczMzc5LDEuMzgyNjFjMC4x' .
			   'MzYxNywtMC4wNjI4NSAtMC45NzQxMSwtMS44MDE1OCAtMi45NzQ3LC0yLjU1NTc0Yy0yLjQxOTU4LC0wLjkwMDggLTUuMDE3Miwt' .
			   'MC40Mzk5MiAtNi45MTMwNSwxLjA1NzkxYy0wLjIwOTQ4LDAuMTU3MTIgLTAuMTQ2NjUsMC4yNzIzMyAwLjEwNDc0LDAuMTU3MTJs' .
			   'MCwweiIgZmlsbD0iIzAwOThDQyIvPg0KICA8L2c+DQo8L3N2Zz4=';
	}

	/**
	 * The name of this plugin's menu page ID retrived from $screen.
	 *
	 * @since 0.7.0
	 * @var   string $akamai The menu page ID.
	 */
	public $menu_page_id;

	/**
	 * A reference to the Akamai class instance.
	 *
	 * @since 0.7.0
	 * @var   string $akamai The Akamai class instance.
	 */
	public $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 0.1.0
	 *
	 * @param string $plugin A reference to the plugin class instance.
	 */
	public function __construct( $plugin ) {
		$this->menu_page_id = "toplevel_page_{$plugin->plugin_name}";
		$this->plugin       = $plugin;
	}

	/**
	 * A helper to get the plugin name succinctly.
	 *
	 * @since  0.7.0
	 * @return string The plugin name.
	 */
	public function name() {
		return $this->plugin->plugin_name;
	}

	/**
	 * A helper to get the plugin version succinctly.
	 *
	 * @since  0.7.0
	 * @return string The plugin version.
	 */
	public function version() {
		return $this->plugin->version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 0.1.0
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Akamai_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Akamai_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style(
			$this->name(),
			plugin_dir_url( __DIR__ ) . 'admin/css/akamai-admin.css',
			[],
			$this->version(),
			'all'
		);

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 0.1.0
	 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Akamai_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Akamai_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script(
			$this->name(),
			plugin_dir_url( __DIR__ ) . 'admin/js/akamai-admin.js',
			[ 'jquery' ],
			$this->version(),
			false
		);

	}

	/**
	 * Register the administration menu for this plugin into the WordPress
	 * Dashboard menu.
	 *
	 * @since 0.1.0
	 */
	public function add_plugin_admin_menu() {
		/*
		 * Add a top-level menu
		 *
		 * NOTE: Alternative menu locations are available via WordPress
		 * administration menu functions.
		 *
		 * Administration Menus: http://codex.wordpress.org/Administration_Menus
		 */
		add_menu_page(
			'Akamai for WordPress',
			'Akamai for WP',
			'manage_options',
			$this->name(),
			[ $this, 'display_plugin_setup_page' ],
			static::get_icon()
		);
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * Documentation: https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
	 *
	 * @since 0.1.0
	 * @param array $links An array of links, I guess?
	 */
	public function add_action_links( $links ) {
		$settings_link = [
			'<a href="' . admin_url( 'admin.php?page=' . $this->name() ) . '">' .
			__( 'Settings', $this->name() ) .
			'</a>',
		];

		return array_merge( $settings_link, $links );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since 0.1.0
	 */
	public function display_plugin_setup_page() {
		include_once( plugin_dir_path( __DIR__ ) . 'admin/partials/akamai-admin-display.php' );
	}

	/**
	 * A helper to determine if we just updated the settings. This can be
	 * used to shortcircuit validation of the existing settings loaded on
	 * to the page, since we just validated the incoming/updated settings.
	 *
	 * @since  0.7.0
	 * @return bool Whether or not the page being rendered is "post"-update.
	 */
	public function is_post_update() {
		return (bool) isset( $_GET['settings-updated'] );
	}

	/**
	 * Register settings as a single option, after running the $_POST
	 * input through the Akamai_Admin::validate() method.
	 *
	 * Should run on admin_init, and it is triggered on an update action.
	 *
	 * @since	0.1.0
	 */
	public function settings_update() {
		register_setting( $this->name(), $this->name(), [ $this, 'validate' ] );
	}

	/**
	 * Verifies the settings as a single option, loaded from the database.
	 *
	 * @since 0.7.0
	 */
	public function settings_load() {
		if ( ! $this->is_post_update() ) {
			$this->validate(); // Defaults to current settings when none sent.
		}
	}

	/**
	 * Verifies credentials, sending the result as an XHR/JSON response.
	 *
	 * @since 0.1.0
	 */
	public function handle_verify_credentials_request() {
		$settings = $this->plugin->get_settings( $_POST );
		echo json_encode( $this->verify_credentials( $settings ) );
		wp_die();
	}

	/**
	 * Verifies the current credentials settings with the EdgeGrid Auth
	 * service.
	 *
	 * @since  0.7.0
	 * @param  array $settings Optional. An Akamai settings array subset.
	 * @return array A normalized Akamai API response.
	 */
	public function verify_credentials( $settings = [] ) {
		try {
			return $this->plugin->purge_api_test( $settings );
		} catch ( Akamai_Auth\Exception\ConfigException $e ) {
			return Akamai_Purge_Request::normalize_response(
				$wp_response = null,
				$success = false,
				$error = $e->getMessage()
			);
		}
	}

	/**
	 * Handle logic around generating settings errors and logging as
	 * necessary.
	 *
	 * @since 0.7.0
	 * @param string $code        Forwarded param.
	 * @param string $message     Forwarded param.
	 * @param string $type        Optional. Forwarded param. Defaults to 'error'.
	 * @param bool   $force_debug Optional. Whether to force debug.
	 *                            Defaults to false.
	 */
	public function add_settings_error(
		$code, $message, $type = 'error', $force_debug = false ) {
		if ( $this->plugin->setting( 'log-errors' ) || $force_debug ) {
			$payload = [
				'error' => "setting-error:$code",
				'message' => $message
			];
			if ( $type !== 'error' ) {
				$payload['type'] = $type;
			}
			error_log( print_r( $payload, true ) );
		}
		add_settings_error( $this->name(), $code, $message, $type );
	}

	/**
	 * Validates the settings (either in the system, or passed in via $_POST),
	 * filling in defaults and checking for usefulness of data. If it fails
	 * validation it generates error notices.
	 *
	 * Returns the completed settings in case used as part of a filter chain.
	 *
	 * @since  0.1.0
	 * @param  string $input The array of options to validate (a la $_POST).
	 * @param  bool   $verify_creds Whether to attempt to verify creds.
	 *                Defaults to true.
	 * @return array  The complete, current Akamai settings array.
	 */
	public function validate( $new_settings = [], $verify_creds = true ) {
		$settings = apply_filters(
			'akamai_settings_to_validate',
			$this->plugin->get_settings( $new_settings )
		);

		$log_errors = $this->plugin->setting( 'log-errors', $settings );

		// Add warnings for required fields (for first time)...
		if ( empty( $settings['unique-sitecode'] ) ) {
			$this->add_settings_error(
				'sitecode-missing',
				'Missing "Unique Site Code" setting.',
				'error',
				$log_errors
			);
		}

		// Check for valid credentials...
		$missing_creds = false;
		foreach ( array_keys( $this->plugin->default_credentials ) as $credential ) {
			if ( empty( $settings['credentials'][$credential] ) && ! $missing_creds ) {
				$this->add_settings_error(
					'missing-credential',
					'Missing necessary API credentials: can not purge.',
					'warning',
					$log_errors
				);
				$missing_creds = true;
			}
		}
		if ( ! $missing_creds && $verify_creds ) {
			$result = $this->verify_credentials( $settings );
			if ( isset( $result['error'] ) ) {
				$this->add_settings_error(
					'invalid-credentials',
					'Invalid API credentials: ' . $result['error'],
					'error',
					$log_errors
				);

			}
		}

		return $settings;
	}

}
