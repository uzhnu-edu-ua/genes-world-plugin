<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Genes_World_Plugin
 * @subpackage Genes_World_Plugin/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Genes_World_Plugin
 * @subpackage Genes_World_Plugin/includes
 * @author     Alex Dubiv <alex.dubiv@uzhnu.edu.ua>
 */
class Genes_World_Plugin_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// wp_clear_scheduled_hook( 'scheduled_tasks_main_hook' );
	}

}
