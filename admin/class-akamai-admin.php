<?php

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
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/akamai-admin.css', array(),
			$this->version, 'all' );

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/akamai-admin.js', array( 'jquery' ),
			$this->version, false );

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
			array( $this, 'display_plugin_setup_page' ),
			static::get_icon()
		);
	}

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
	 * Add settings action link to the plugins page.
	 *
	 * @since    0.1.0
	 */

	public function add_action_links( $links ) {
		/*
		*  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
		*/
		$settings_link = array(
			'<a href="' . admin_url( 'admin.php?page=' . $this->plugin_name ) . '">' . __( 'Settings',
				$this->plugin_name ) . '</a>',
		);

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

	public function options_update() {
		register_setting( $this->plugin_name, $this->plugin_name, array( $this, 'validate' ) );
	}

	public function verify_credentials() {
		$valid = $this->validate( $_POST );

		try {
			$auth = \Akamai\Open\EdgeGrid\Authentication::createFromEdgeRcFile( $valid['section'], $valid['edgerc'] );

			echo json_encode( array( "success" => true ) );
		} catch ( \Akamai\Open\EdgeGrid\Authentication\Exception\ConfigException $e ) {
			echo json_encode( array( "error" => $e->getMessage() ) );
		}

		wp_die();
	}

	public function validate( $input ) {
		// All checkboxes inputs
		$valid = array(
			'edgerc'           => null,
			'section'          => 'default',
			'hostname'         => '',
            'purge_comemnts'   => 1,
			'purge_tags'       => 1,
			'purge_categories' => 1,
			'purge_archives'   => 1,
		);

		$akamai = new Akamai();
		$hostname = $akamai->get_hostname($input);
		if (!empty($hostname)) {
			$valid['hostname'] = $hostname;
		} else {
			add_settings_error( $this->plugin_name, 'hostname-error', 'Invalid hostname.');
		}

		if ( isset( $input['edgerc'] ) && ! empty( $input['edgerc'] ) ) {
			$path = $input['edgerc'];
			if ( basename( $path ) != '.edgerc' ) {
				if ( substr( $path, - 1 ) != DIRECTORY_SEPARATOR ) {
					$path .= DIRECTORY_SEPARATOR;
				}
				$path .= '.edgerc';
			}

			$valid['edgerc'] = $path;
		}

		if ( isset( $input['section'] ) && ! empty( $input['section'] ) ) {
			$valid['section'] = $input['section'];
		}

		foreach ( array( 'purge_comments', 'purge_tags', 'purge_categories', 'purge_archives' ) as $checkbox ) {
			$valid[ $checkbox ] = 0;
			if ( isset( $input[ $checkbox ] ) && ! empty( $input[ $checkbox ] ) ) {
				$valid[ $checkbox ] = 1;
			}
		}

		try {
			$auth = \Akamai\Open\EdgeGrid\Authentication::createFromEdgeRcFile( $valid['section'], $valid['edgerc'] );
		} catch ( \Akamai\Open\EdgeGrid\Exception\ConfigException $e ) {
			add_settings_error( $this->plugin_name, 'edgerc-error', $e->getMessage() );
		}

		return $valid;
	}

}
