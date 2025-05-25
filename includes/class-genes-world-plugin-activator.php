<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Genes_World_Plugin
 * @subpackage Genes_World_Plugin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Genes_World_Plugin
 * @subpackage Genes_World_Plugin/includes
 * @author     Alex Dubiv <alex.dubiv@uzhnu.edu.ua>
 */
class Genes_World_Plugin_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if (!current_user_can('manage_options')) wp_die('Access Denied!');

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$table_name = $wpdb->prefix . 'genes_world_projects';

		$sql = "CREATE TABLE $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			country_origin tinytext NOT NULL,
			population_name tinytext NOT NULL,
			technology tinytext NOT NULL,
			coverage decimal(10,2) DEFAULT NULL,
			n_pub_available int(11) NOT NULL,
			n_available_upon_request int(11) NOT NULL,
			request_from tinytext NOT NULL,
			has_reads tinyint(1) DEFAULT NULL,
			has_variants tinyint(1) DEFAULT NULL,
			has_summary tinyint(1) DEFAULT NULL,
			centralized_project tinyint(1) DEFAULT NULL,
			year smallint(6) NOT NULL,
			papers_with_data text NOT NULL,
			link text NOT NULL,
			link_alt text NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta($sql);
	}
}
