<?php
/**
 * Genes_World_Public_Geodata-related functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Genes_World_Plugin
 * @subpackage Genes_World_Plugin/admin
 */

use function PHPSTORM_META\map;

/**
 * Trait containing form-related functionality.
 */
trait Genes_World_Public_Geodata {

	private $raw_columns = [];
	private $raw_data = [];

	private $country_mapping = [
		'United States of America' => 'United States',
		'United Republic of Tanzania' => 'Tanzania',
		'Bosnia and Herzegovina' => 'Bosnia and Herz.',
		'Republic of the Congo' => 'Congo',
		'Western Sahara' => 'W. Sahara',
		'Democratic Republic of the Congo' => 'Dem. Rep. Congo',
		'Dominican Republic' => 'Dominican Rep.',
		'Central African Republic' => 'Central African Rep.',
	];

	public function genes_world_geodata_shortcode() {
		if (!(is_admin() || defined( 'REST_REQUEST' ) && REST_REQUEST)) {
			ob_start();
			$this->genes_geodata();
			return ob_get_clean();
		}
    }

	// ***********
	// GEODATA
	// ***********

	public function genes_geodata() {
		$this -> show_geodata();
		$this -> show_griddata();
		$this -> show_grid_row_info();
	}

	public function show_geodata() {
		// THESE ARE KEYS TO EASILY AUTO-SYNC with Loco Translate so they are not erased when PO template is updated
		// dropdown
		__( 'country_origin_avg_coverage', 'genes-world-plugin' );
		__( 'country_origin_max_coverage', 'genes-world-plugin' );
		__( 'country_origin_sum_n_pub_available', 'genes-world-plugin' );
		__( 'country_origin_sum_n_available_upon_request', 'genes-world-plugin' );
		__( 'country_origin_sum_total_samples_available', 'genes-world-plugin' );
		__( 'country_origin_max_has_features', 'genes-world-plugin' );
		__( 'country_origin_max_centralized_project', 'genes-world-plugin' );
		__( 'country_origin_start_year', 'genes-world-plugin' );
		__( 'country_origin_latest_year', 'genes-world-plugin' );

		$mapStats = [];
		$statTypes = [
			'country_origin' => ['sum_n_pub_available', 'sum_n_available_upon_request', 'sum_total_samples_available', 'max_has_features', 'max_centralized_project', 'start_year', 'latest_year'], // SQL-query but with same data grouping - just some stats (AVG\MAX\SUM etc) - each will be in dropdown as separate option
			//'country_origin' => ['avg_coverage', 'max_coverage', 'sum_n_pub_available', 'sum_n_available_upon_request', 'sum_total_samples_available', 'max_has_features', 'max_centralized_project', 'start_year', 'latest_year'], // SQL-query but with same data grouping - just some stats (AVG\MAX\SUM etc) - each will be in dropdown as separate option
			// 'part_world' => ['Total'] // support completely different SQL-query
		];

		// generate stats with SQL-queries
		foreach ($statTypes as $statType => $statTypeAttributes) {
			$mapStats[$statType] = [
				'data' => $this->wpdb->get_results("SELECT `$statType`, 
					AVG(`coverage`) AS `avg_coverage`, 
					MAX(`coverage`) AS `max_coverage`, 
					SUM(`n_pub_available`) AS `sum_n_pub_available`, 
					SUM(`n_available_upon_request`) AS `sum_n_available_upon_request`, 
					(SUM(`n_pub_available`) + SUM(`n_available_upon_request`)) AS `sum_total_samples_available`,
					(MAX(`has_reads`) + MAX(`has_variants`) + MAX(`has_summary`)) AS `max_has_features`,
					MAX(`centralized_project`) AS `max_centralized_project`,
					MIN(`year`) AS `start_year`,
					MAX(`year`) AS `latest_year`,
					count(*) AS `Total` 
					FROM $this->projects_table_name GROUP BY `$statType` ORDER BY `Total` DESC")
			];
			foreach ($mapStats[$statType]['data'] as $data) {
				$data->Total = +$data->Total;
				$data->avg_coverage = +$data->avg_coverage;
				$data->max_coverage = +$data->max_coverage;
				$data->sum_n_pub_available = +$data->sum_n_pub_available;
				$data->sum_n_available_upon_request = +$data->sum_n_available_upon_request;
				$data->sum_total_samples_available = +$data->sum_total_samples_available;
				$data->max_has_features = +$data->max_has_features;
				$data->max_centralized_project = +$data->max_centralized_project;
				$data->start_year = +$data->start_year;
				$data->latest_year = +$data->latest_year;
				
				if (!empty($this->country_mapping[$data->$statType])) {
					$data->regionTitle = $this->country_mapping[$data->$statType];
				} else $data->regionTitle = $data->$statType;
			}

			$mapStats[$statType]['stats'] = [];

			foreach ($statTypeAttributes as $statTypeAttr) {
				$mapStats[$statType]['stats'][$statTypeAttr] = [];
				$mapStats[$statType]['stats'][$statTypeAttr]['maxValue'] = array_reduce($mapStats[$statType]['data'], function ($carry, $data) use ($statTypeAttr) {
					return ($carry === null || $data->$statTypeAttr > $carry) ? $data->$statTypeAttr : $carry;
				}, null);
	
				$mapStats[$statType]['stats'][$statTypeAttr]['minValue'] = array_reduce($mapStats[$statType]['data'], function ($carry, $data) use ($statTypeAttr) {
					return ($carry === null || $data->$statTypeAttr < $carry) ? $data->$statTypeAttr : $carry;
				}, null);
			}			
		}

		if (count($mapStats[array_key_first($statTypes)]['data'])) { // if at least 1st items of stats has some data - run next code
			?>
			<section class="map-top-controls">
				<div class="bu-field">
					<label for="form-geodata-type" class="bu-label"><?php _e( 'Select layer filter type:', 'genes-world-plugin' ) ?></label>
					<div class="bu-control">
						<div class="bu-select js-form-geodata-type" id="form-geodata-type" >
							<select>
								<?php
									foreach ($statTypes as $statType => $statTypeAttributes) {
										foreach ($statTypeAttributes as $statTypeAttr) {
											if (in_array($statTypeAttr, ['avg_coverage', 'max_coverage'])) {
												$valueType = '%';
											} else if ($statTypeAttr === 'max_has_features') {
												$valueType = json_encode(['1 feature'=>'#edf8b1', '2 features'=>'#7fcdbb', '3 features'=>'#2c7fb8']);
											} else if ($statTypeAttr === 'max_centralized_project') {
												$valueType = json_encode(['International'=>'#66c2a5', 'Centralized'=>'#fc8d62', 'Regional'=>'#7570b3']);
											} else $valueType = '';
											echo "<option value='" . esc_attr("$statType|$statTypeAttr|$valueType") . "'>" . __( $statType .'_'. $statTypeAttr, 'genes-world-plugin' ) . "</option>";
										}
									}
								?>
							</select>
						</div>
					</div>
				</div>
			</section>

			<div class="geo-data-layout">
				<div id="map"></div>

				<div class="map-feature-details-wrapper">
					<section id="map-feature-details">
						<div class="map-feature-details__info"></div>
						<button class="bu-button js-exit-region-view-btn exit-region-view-btn"><span class="dashicons dashicons-fullscreen-alt"></span> Exit country view</button>
					</section>
				
					<?php echo $this->message_html(__( 'Sorry, not enough data found to build the chart...', 'genes-world-plugin' ), 'bu-is-info chart-notice is-hidden'); ?>
				
					<script>
						window.genes_world = <?php echo json_encode($mapStats, JSON_UNESCAPED_UNICODE); ?>;
						window.genes_world.statTypes = <?php echo json_encode($statTypes); ?>;
					</script>
				</div>
			</div>			
			<?php
		}
	}

	public function show_griddata() {
		// table column headers
		__( 'country_origin', 'genes-world-plugin' );
		__( 'population_name', 'genes-world-plugin' );
		__( 'technology', 'genes-world-plugin' );
		__( 'coverage', 'genes-world-plugin' );
		__( 'n_pub_available', 'genes-world-plugin' );
		__( 'n_available_upon_request', 'genes-world-plugin' );
		__( 'request_from', 'genes-world-plugin' );
		__( 'has_reads', 'genes-world-plugin' );
		__( 'has_variants', 'genes-world-plugin' );
		__( 'has_summary', 'genes-world-plugin' );
		__( 'centralized_project', 'genes-world-plugin' );
		__( 'year', 'genes-world-plugin' );
		__( 'papers_with_data', 'genes-world-plugin' );
		__( 'link', 'genes-world-plugin' );
		__( 'link_alt', 'genes-world-plugin' );

		$data = $this->wpdb->get_results("SELECT * FROM $this->projects_table_name ORDER BY `country_origin` DESC", ARRAY_A);

		// Extract only the values from the associative array to create a simple array of arrays
		$data_values = array_map('array_values', $data);

		// Check if there's data and get the first row to extract column names
		$first_row = !empty($data) ? array_keys($data[0]) : [];

		$first_row = array_map(function($item) {
			return __( $item, 'genes-world-plugin' );
		}, $first_row);
		
		$first_row[0] = ['name' => 'Id', 'hidden' => 'true'];
		$first_row[7] = ['name' => $first_row[7], 'hidden' => 'true'];
		$first_row[13] = ['name' => $first_row[13], 'hidden' => 'true'];
		$first_row[14] = ['name' => $first_row[14], 'hidden' => 'true'];
		$first_row[15] = ['name' => $first_row[15], 'hidden' => 'true'];

		echo '<script>';
		echo '	window.genes_world.gridData = ' . wp_json_encode($data_values) . ';';
		echo '	window.genes_world.gridColumns = ' . wp_json_encode($first_row) . ';';
		echo '	window.genes_world.country_mapping = ' . wp_json_encode($this -> country_mapping) . ';';
		echo '</script>';
		echo '<section id="grid-data-wrapper" class="grid-data-wrapper"></section>';
		echo '<section class="grid-data-info"></section>';

		$this -> raw_columns = !empty($data) ? array_keys($data[0]) : [];
		$this -> raw_data = $data_values;
	}

	public function show_grid_row_info() {
		echo '<section class="grid-row-info genes-report">';
		echo "<dl>";
		foreach ($this -> raw_columns as $value) {
			echo "<dt>". __( $value, 'genes-world-plugin' ) ."</dt><dd></dd>";
		}
		echo "</dl>";
		echo '</section>';
	}
}

// Define other form-related functions outside the trait as needed...
