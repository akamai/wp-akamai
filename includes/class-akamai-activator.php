<?php

/**
 * Fired during plugin activation
 *
 * @link       https://developer.akamai.com
 * @since      0.1.0
 *
 * @package    Akamai
 * @subpackage Akamai/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1.0
 * @package    Akamai
 * @subpackage Akamai/includes
 * @author     Davey Shafik <dshafik@akamai.com>
 */
class Akamai_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.1.0
	 */
	public static function activate() {
		add_option( 'akamai-version', Akamai::VERSION );
	}

}
