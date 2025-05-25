<?php
/**
 * Genes_World_Public_Dashboard-related functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Genes_World_Plugin
 * @subpackage Genes_World_Plugin/admin
 */

/**
 * Trait containing form-related functionality.
 */
trait Genes_World_Public_Dashboard {
	public function genes_world_dashboard_shortcode() {
		if (!(is_admin() || defined( 'REST_REQUEST' ) && REST_REQUEST)) {
			ob_start();
			$this->genes_dashboard();
			return ob_get_clean();
		}
    }

	public function genes_dashboard_counter_shortcode($params) {
		if (!(is_admin() || defined( 'REST_REQUEST' ) && REST_REQUEST)) {
			ob_start();
			// Extract shortcode attributes
			$params = shortcode_atts(array(
				'type' => 'samples', // Set default values for parameters
			), $params);
			$type = $params['type'];
			$this->genes_dashboard_counter($type);
			return ob_get_clean();
		}
    }

	// ***********
	// DASHBOARD
	// ***********
	public function show_dashboard($params = []) {
		$is_ajax = isset($params['ajax']) && $params['ajax'] === true;
		if ($is_ajax && (!isset($params['region']) || !$params['region'])) {
			return ['ERROR' => 'No required REGION param in request'];
		}
		
		if (get_transient('_genes_world_import_in_progress_')) {
			$message = $this->message_html(__('Latest Data update process currently is in progress, please visit us in a minute...', 'genes-plugin'), 'bu-is-warning');
			if ($is_ajax) {
				return ['ERROR' => $message];
			} else {
				echo $message;
			}
			return;
		}

		$genes_world = [];
		$genes_world['latest_dashboard_time'] = __( 'Last updated: ', 'genes-plugin' ).date('Y-m-d', get_transient("_genes_latest_dashboard_time_"));
		$genes_world['i18n'] = "{
			'Processed samples': '".__( 'Processed samples', 'genes-plugin' )."',
			'Months': '".__( 'Months', 'genes-plugin' )."'
		}";

		if (!$is_ajax) {
			echo "<p class='date-last-updated'>". $genes_world['latest_dashboard_time'] ."</p>";
			echo "<script> window.genes_world = {i18n: " . $genes_world['i18n'] . "}; </script>";
		}

		if ($is_ajax) {
			return $genes_world;
		}
	}

	public function genes_dashboard() {
		$this -> show_dashboard();
	}

	public function genes_dashboard_counter($type) {
		$output = '';
		$projects_table_name = $this->projects_table_name;
		switch ($type) {
			case 'samples':
				$projects_data = $this->wpdb->get_results("SELECT COUNT(*) as `Total` FROM $projects_table_name");
				if (!empty($projects_data) && isset($projects_data[0]->Total)) {
					$output = round(+$projects_data[0]->Total / 10) * 10 . "+";
				} else {
					$output = "0+";
				}
				break;
			
			default:
				break;
		}
	
		echo esc_html($output);
	}
}
