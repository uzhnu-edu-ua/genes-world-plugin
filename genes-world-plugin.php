<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Genes_World_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Genes-WORLD Plugin
 * Plugin URI:        http://example.com/genes-world-plugin-uri/
 * Implementation:	  https://globgen.uzhnu.edu.ua/world-geo-data/
 * Description:       Admin plugin for Genes-WORLD  project
 * Version:           1.0.0
 * Author:            Alex Dubiv, UzhNU
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       genes-world-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'GENES_WORLD_PLUGIN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-genes-world-plugin-activator.php
 */
function activate_genes_world_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-genes-world-plugin-activator.php';
	Genes_World_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-genes-world-plugin-deactivator.php
 */
function deactivate_genes_world_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-genes-world-plugin-deactivator.php';
	Genes_World_Plugin_Deactivator::deactivate();
}

function uninstall_genes_world_plugin(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'genes_world_projects';
    $wpdb->query("DROP TABLE IF EXISTS `{$table_name}`"); 
}

register_activation_hook( __FILE__, 'activate_genes_world_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_genes_world_plugin' );
register_uninstall_hook(__FILE__,'uninstall_genes_world_plugin');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-genes-world-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_genes_world_plugin() {

	$plugin = new Genes_World_Plugin();
	$plugin->run();

}

run_genes_world_plugin();
