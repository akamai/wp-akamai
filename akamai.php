<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://developer.akamai.com
 * @since             0.2.0
 * @package           Akamai
 * @author            Davey Shafik <dshafik@akamai.com>
 *
 * @wordpress-plugin
 * Plugin Name:       Akamai for WordPress
 * Plugin URI:        http://github.com/akamai/wp-akamai
 * Description:       Akamai for WordPress Plugin. Control Akamai CDN and more.
 * Version:           0.6.0
 * Author:            Akamai Technologies
 * Author URI:        https://developer.akamai.com
 * License:           Apache-2.0
 * License URI:       http://www.apache.org/licenses/LICENSE-2.0.txt
 * Text Domain:       akamai
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'AKAMAI_MIN_PHP', '5.3' );

if ( version_compare( phpversion(), AKAMAI_MIN_PHP, '<' ) ) {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error">' .
		     __( 'Error: "Akamai for WordPress" requires a newer version of PHP to be running.', 'akamai' ) .
		     '<br/>' . __( 'Minimal version of PHP required: ',
				'akamai' ) . '<strong>' . AKAMAI_MIN_PHP . '</strong>' .
		     '<br/>' . __( 'Your server\'s PHP version: ', 'akamai' ) . '<strong>' . phpversion() . '</strong>' .
		     '</div>';
	} );

	return false;
}

require_once 'vendor/akamai-open/edgegrid-auth/src/Authentication.php';
require_once 'vendor/akamai-open/edgegrid-auth/src/Authentication/Timestamp.php';
require_once 'vendor/akamai-open/edgegrid-auth/src/Authentication/Nonce.php';
require_once 'vendor/akamai-open/edgegrid-auth/src/Authentication/Exception.php';
require_once 'vendor/akamai-open/edgegrid-auth/src/Authentication/Exception/ConfigException.php';
require_once 'vendor/akamai-open/edgegrid-auth/src/Authentication/Exception/SignerException.php';
require_once 'vendor/akamai-open/edgegrid-auth/src/Authentication/Exception/SignerException/InvalidSignDataException.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-akamai-activator.php
 */
function activate_akamai() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-akamai-activator.php';
	Akamai_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-akamai-deactivator.php
 */
function deactivate_akamai() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-akamai-deactivator.php';
	Akamai_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_akamai');
register_deactivation_hook( __FILE__, 'deactivate_akamai');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-akamai.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_akamai() {

	$plugin = new Akamai();
	$plugin->run();

}

run_akamai();
