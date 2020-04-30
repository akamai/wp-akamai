<?php

use \Akamai\Open\EdgeGrid\Authentication as Akamai_Auth;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://developer.akamai.com
 * @since      0.1.0
 *
 * @package    Akamai
 * @subpackage Akamai/admin
 */

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
	 * The ID of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The name of this plugin's menu page ID retrived from $screen.
	 *
	 * @since    0.7.0
	 * @var      string $akamai The menu page ID.
	 */
	public $menu_page_id;

	/**
	 * A reference to the Akamai class instance.
	 *
	 * @since    0.7.0
	 * @var      string $akamai The Akamai class instance.
	 */
	public $akamai;

	/**
	 * Default crednetials settings.
	 *
	 * @since   0.7.0
	 * @var     array $default_credentials The default credentials settings.
	 */
	static public $default_credentials = [
		'host'          => '',
		'access-token'  => '',
		'client-token'  => '',
		'client-secret' => '',
	];

	/**
	 * Default options settings.
	 *
	 * @since   0.7.0
	 * @var     array $default_options The default options settings.
	 */
	static public $default_options = [
		// 'hostname' => ..., // Handled in Akamai::get_hostname().
		'unique-sitecode' => '',
		'debug-mode'      => 0,

		// TODO (PJ): May remove? These were in legacy plugin.
		'purge_comments'   => 1,
		'purge_tags'       => 1,
		'purge_categories' => 1,
		'purge_archives'   => 1,
	];

	/**
	 * Get the Akamai logo icon for SVG.
	 *
	 * @since	0.1.0
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
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $akamai ) {
		$this->plugin_name  = $plugin_name;
		$this->menu_page_id = "toplevel_page_{$this->plugin_name}";
		$this->version      = $version;
		$this->akamai       = $akamai;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.1.0
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
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/akamai-admin.css',
			[],
			$this->version,
			'all'
		);

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.1.0
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
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/akamai-admin.js',
			[ 'jquery' ],
			$this->version,
			false
		);

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    0.1.0
	 */
	public function add_plugin_admin_menu() {
		/*
		 * Add a top-level menu
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 */
		add_menu_page(
			'Akamai for WordPress',
			'Akamai for WP',
			'manage_options',
			$this->plugin_name,
			[ $this, 'display_plugin_setup_page' ],
			static::get_icon()
		);
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * Documentation: https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
	 *
	 * @since    0.1.0
	 */
	public function add_action_links( $links ) {
		$settings_link = [
			'<a href="' . admin_url( 'admin.php?page=' . $this->plugin_name ) . '">' .
			__( 'Settings', $this->plugin_name ) .
			'</a>',
		];

		return array_merge( $settings_link, $links );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    0.1.0
	 */
	public function display_plugin_setup_page() {
		include_once( 'partials/akamai-admin-display.php' );
	}

	/**
	 * A helper to determine if we just updated the settings. This can be
	 * used to shortcircuit validation of the existing settings loaded on
	 * to the page, since we just validated the incoming/updated settings.
	 *
	 * @since   0.7.0
	 * @return  bool    Whether or not the page being rendered is "post"-update.
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
	public function options_update() {
		register_setting( $this->plugin_name, $this->plugin_name, [ $this, 'validate' ] );
	}

	/**
	 * Verifies the settings as a single option, loaded from the database
	 * and sent to the Akamai_Admin::validate() method.
	 *
	 *
	 *
	 * @since	0.7.0
	 */
	public function options_load() {
		if ( ! $this->is_post_update() ) {
			$options = get_option( $this->plugin_name );
			$this->validate( $options );
		}
	}

	/**
	 * Verifies the credentials settings pulled through the
	 * Akamai_Admin::validate() method and loaded into the EdgeGrid Auth
	 * service, sending the result as an XHR/JSON response.
	 *
	 * @since	0.1.0
	 */
	public function handle_verify_credentials_request() {
		$settings = $this->validate( $_POST, $verify_creds = false );
		echo json_encode( $this->verify_credentials( $settings['credentials'] ) );
		wp_die();
	}

	/**
	 * Verifies the current credentials settings with the EdgeGrid Auth
	 * service.
	 *
	 * @since	0.7.0
	 */
	public function verify_credentials( $credentials = [] ) {
		try {
			$auth = $this->akamai->get_edge_auth_client( $credentials );
			return [ 'success' => true ];
		} catch ( Akamai_Auth\Exception\ConfigException $e ) {
			return [ 'error' => $e->getMessage() ];
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
	 * @param bool   $force_debug Optional. Whether to force debug. Defaults to false.
	 */
	public function add_settings_error( $code, $message, $type = 'error', $force_debug = false ) {
		if ( $this->akamai->debug_mode() || $force_debug ) {
			$payload = [ 'error' => "setting-error:$code", 'message' => $message ];
			if ( $type !== 'error' ) {
				$payload['type'] = $type;
			}
			error_log( print_r( $payload, true ) );
		}
		add_settings_error( $this->plugin_name, $code, $message, $type );
	}

	/**
	 * Validates the information sent in $_POST, filling in defaults
	 * and checking for usefulness of data. If it fails validation it
	 * generates error notices, otherwise it saves values to the database
	 * and returns them.
	 *
	 * @since	0.1.0
	 * @param   string  $input The array of options to validate (a la $_POST).
	 * @param   bool    $verify_creds Whether to attempt to verify creds. Default: true.
	 */
	public function validate( $input, $verify_creds = true ) {
		$values = [];

		// Merge incoming and default.
		foreach ( static::$default_options as $key => $default_value ) {
			$values[$key] = isset( $input[$key] ) ? $input[$key] : $default_value;
		}
		foreach ( static::$default_credentials as $key => $default_cred ) {
			$values['credentials'][$key] = isset( $input['credentials'][$key] )
				? $input['credentials'][$key]
				: $default_cred;
		}
		$values = apply_filters( 'akamai_settings_to_validate', $values );

		$debug_mode = $this->akamai->debug_mode( $values );

		// Add warnings for required fields (for first time)...
		$hostname = $this->akamai->get_hostname( $input );
		if ( ! empty( $hostname ) ) {
			$values['hostname'] = $hostname;
		} else {
			$this->add_settings_error(
				'hostname-missing', 'Missing "Public Hostname" setting.', 'error', $debug_mode );
		}
		if ( empty( $values['unique-sitecode'] ) ) {
			$this->add_settings_error(
				'sitecode-missing', 'Missing "Unique Site Code" setting.', 'error', $debug_mode );
		}

		// Check for valid credentials...
		$missing_creds = false;
		foreach ( array_keys( static::$default_credentials ) as $credential ) {
			if ( empty( $values['credentials'][$credential] ) && ! $missing_creds ) {
				$this->add_settings_error(
					'missing-credential', 'Missing necessary API credentials: can not purge.', 'warning', $debug_mode );
				$missing_creds = true;
			}
		}
		if ( ! $missing_creds && $verify_creds ) {
			$result = $this->verify_credentials( $values['credentials'] );
			if ( isset( $result['error'] ) ) {
				$this->add_settings_error(
					'invalid-credentials', 'Invalid API credentials: ' . $result['error'], 'error', $debug_mode );

			}
		}

		return $values;
	}

}
