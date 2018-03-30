<?php

/**
 * The file that defines the core plugin class
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
	const VERSION = '0.5.0';

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
	 * - Akamai_Loader. Orchestrates the hooks of the plugin.
	 * - Akamai_Admin. Defines all hooks for the admin area.
	 * - Akamai_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-akamai-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
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
		$plugin_admin = new Akamai_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );


		// Add Settings link to the plugin
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

		// Save/Update our plugin options
		$this->loader->add_action( 'admin_init', $plugin_admin, 'options_update' );

		// Validate Credentials AJAX
		$this->loader->add_action( 'wp_ajax_akamai_verify_credentials', $plugin_admin, 'verify_credentials' );

		// Purging Actions/Hooks
		$this->loader->add_action( 'save_post', $this, 'purgeOnPost' );
		$this->loader->add_action( 'comment_post', $this, 'purgeOnPost', 10, 3 );
		$this->loader->add_action( 'admin_notices', $this, 'admin_notices' );
		$this->loader->add_action( 'send_headers', $this, 'sendHeaders' );
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
	 * @param $post_ID
	 *
	 * @return bool
	 */
	public function purgeOnPost($post_ID ) {
		$post = get_post( $post_ID );
		if ( ! is_object( $post ) || $post->post_status != 'publish' ) {
			return true;
		}

		$options = get_option( $this->plugin_name );

		$options['purge_front'] = true;

		$this->purge($options, $post);
	}

	public function purgeOnComment($comment_ID, $comment_approved, $comment_data) {
		if ($comment_approved == 1) {
			$options = get_option( $this->plugin_name );

			if ( $options['purge_comments'] ) {
				$post = get_post($comment_data['comment_post_ID']);
				$this->purge($options, $post);
			}
		}
	}

	public function sendHeaders($WP)
	{

	}

	/**
	 * @param $options
	 * @param $post
	 *
	 * @return mixed|string|void
	 */
	protected function get_purge_body( $options, $post ) {
		$baseUrl   = parse_url( get_bloginfo( 'wpurl' ), PHP_URL_PATH ) . '/';
		$permalink = get_permalink( $post->ID );

		$objects = array(
			$this->get_item_url( $permalink ), // Post
		);

		if ( $options['purge_front'] ) {
			$objects[] = $baseUrl;
		}

		$host = $this->get_hostname($options);

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
			'hostname' => $host,
			'objects'  => $objects
		);

		return json_encode( $data );
	}

	/**
	 * @param $url
	 *
	 * @return mixed|string
	 */
	protected function get_item_url( $url ) {
		$itemUrl = parse_url( $url, PHP_URL_PATH );
		if ( strpos( $url, '?' ) !== false ) {
			$itemUrl .= '?' . parse_url( $url, PHP_URL_QUERY );
		}

		return $itemUrl;
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

	/**
	 * @param array $options
	 * @param string $body
	 *
	 * @return \Akamai\Open\EdgeGrid\Authentication
	 */
	protected function get_purge_auth( $options, $body ) {
	    try {
		    $auth = \Akamai\Open\EdgeGrid\Authentication::createFromEdgeRcFile( $options['section'], $options['edgerc'] );
		    $auth->setHttpMethod( 'POST' );
		    $auth->setPath( '/ccu/v3/invalidate/url' );
		    $auth->setBody( $body );

		    return $auth;
	    } catch (\Exception $e) {
	        return false;
        }
	}

	/**
	 * @return string
	 */
	protected function get_user_agent() {
		return
			'WordPress/' . get_bloginfo( 'version' ) . ' ' .
			'Akamai-for-WordPress/' . self::VERSION . ' ' .
			'PHP/' . phpversion();
	}

	/**
	 * @param $location
	 * @param $response
	 *
	 * @return string
	 */
	public function add_error_query_arg( $location, $response ) {
		remove_filter( 'redirect_post_location', array( $this, 'add_error_query_arg' ), 100 );

		return add_query_arg( array( 'akamai-cache-purge-error' => urlencode( $response->detail ) ), $location );
	}

	/**
	 * @param $location
	 *
	 * @return string
	 */
	public function add_success_query_arg( $location ) {
		remove_filter( 'redirect_post_location', array( $this, 'add_success_query_arg' ), 100 );

		return add_query_arg( array( 'akamai-cache-purge-success' => 'true' ), $location );
	}

	public function admin_notices() {
		if ( isset( $_GET['akamai-cache-purge-error'] ) ) {
			?>
			<div class="error notice is-dismissible">
				<p>
					<img src="<?= Akamai_Admin::get_icon(); ?>" style="height: 1em" alt="Akamai for WordPress">
					<?php esc_html_e( 'Unable to purge cache: ' . $_GET['akamai-cache-purge-error'], 'akamai' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * @param $options
	 * @param $post
	 */
	protected function purge($options, $post)
	{
		$body = $this->get_purge_body($options, $post);
		$auth = $this->get_purge_auth($options, $body);

		if (!($auth instanceof \Akamai\Open\EdgeGrid\Authentication)) {
		    return;
		}

		$response = wp_remote_post('https://' . $auth->getHost() . $auth->getPath(), array(
			'user-agent' => $this->get_user_agent(),
			'headers' => array(
				'Authorization' => $auth->createAuthHeader(),
				'Content-Type' => 'application/json',
			),
			'body' => $body
		));

		if (wp_remote_retrieve_response_code($response) != 201) {
			$instance = $this;
			add_filter('redirect_post_location', function ($location) use ($instance, $response) {
				$body = json_decode(wp_remote_retrieve_body($response));

				return $instance->add_error_query_arg($location, $body);
			}, 100);
		} else {
			add_filter('redirect_post_location', array($this, 'add_success_query_arg'), 100);
		}
	}
}
