<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Genes_World_Plugin
 * @subpackage Genes_World_Plugin/admin
 */

 require_once plugin_dir_path( __FILE__ ) . 'partials/form-settings.php';
 

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Genes_World_Plugin
 * @subpackage Genes_World_Plugin/admin
 * @author     Alex Dubiv <alex.dubiv@uzhnu.edu.ua>
 */
class Genes_World_Plugin_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $genes_world_plugin    The ID of this plugin.
	 */
	private $genes_world_plugin = 'genes_world_plugin';

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	private $plugin_name;


    use Genes_World_Plugin_Form_Settings;
	//use Genes_World_Plugin_Sheduled_Tasks;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $genes_world_plugin       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $genes_world_plugin, $version ) {

		$this->genes_world_plugin = $genes_world_plugin;
		$this->plugin_name = $genes_world_plugin;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Genes_World_Plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Genes_World_Plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->genes_world_plugin, plugin_dir_url( __FILE__ ) . 'css/genes-world-plugin-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Genes_World_Plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Genes_World_Plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->genes_world_plugin, plugin_dir_url( __FILE__ ) . 'js/genes-world-plugin-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the settings page for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function register_settings_page() {
		// Create our settings page as a submenu page.
		add_submenu_page(
			'tools.php',                             // parent slug
			__( 'Genes WORLD', 'genes-world-plugin' ),      // page title
			__( 'Genes WORLD', 'genes-world-plugin' ),      // menu title
			'manage_options',                        // capability
			'genes-world-plugin',                           // menu_slug
			array( $this, 'display_settings_page' )  // callable function
		);
	}

	/**
	 * Display the settings page content for the page we have created.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/genes-world-plugin-admin-display.php';

	}

	private function parse_csv($file_path) {
		if (!file_exists($file_path) || !is_readable($file_path)) {
			return ['success' => false, 'error' => 'File not found or not readable'];
		}

		$results = [
			'lines_skipped' => 0,
			'data_array' => [],
		];
	
		// Open the CSV file
		if (($handle = fopen($file_path, 'r')) !== false) {
			// Skip the first 2 rows if it contains headers
			fgetcsv($handle, 10000, ',');
			fgetcsv($handle, 10000, ',');
	
			while (($data = fgetcsv($handle, 10000, ',')) !== false) {
				if (!$data[2]) {
					$results['lines_skipped']++;
					continue; //  skip empty line  (origin_country is empty)
				}
				// Validate and sanitize each column value
				$row = [
					'population_name' => sanitize_text_field($data[0]),
					'_part_world' => sanitize_text_field($data[1]),
					'country_origin' => sanitize_text_field($data[2]),
					'_superpopulation_name' => sanitize_text_field($data[3]),
					'technology' => sanitize_text_field($data[4]),
					'coverage' => $data[5],
					'_total_n_samples' => $data[6],
					'_pub_available' => $data[7],
					'_avail_upon_request' => $data[8],
					'n_pub_available' => $data[9],
					'n_available_upon_request' => $data[10],
					'_n_total' => $data[11],
					'_n_only_summaries' => $data[12],
					'request_from' => sanitize_text_field($data[13]),
					'has_reads' => $data[14],
					'has_variants' => $data[15],
					'has_summary' => $data[16],
					'_t1' => $data[17],
					'_t2' => $data[18],
					'centralized_project' => $data[19],
					'year' => $data[20],
					'_international_project' => $data[21],
					'_data_accession' => $data[22],
					'_browser_available' => $data[23],
					'_empty' => $data[24],
					'papers_with_data' => sanitize_text_field($data[25]),
					'_data_release_source' => sanitize_text_field($data[26]),
					'link' => sanitize_text_field($data[27]),
					'link_alt' => sanitize_text_field($data[28]),
				];
	
				// Add the row to the data array
				$results['data_array'][] = $row;
			}
	
			// Close the CSV file
			fclose($handle);
			$results['success'] = true;
		} else {
			$results['error'] = true;
		}
	
		return $results;
	}

	/**
	 * Import data file
	 *
	 * @since    1.0.0
	 */
	public function import_data_file($file) {
		if (!current_user_can('manage_options')) wp_die('Access Denied!');
		$error_message = '';
		$imported_records = 0;

		// Flush the output buffer
		ob_flush();
		flush();

		if ($file) {
			// Get the contents of your CSV file
			$csv_file_path = $file;
			// Parse the CSV data
			$results = $this->parse_csv($csv_file_path);

			// delete file after read
			unlink($csv_file_path);

			
			// Check if the CSV decoding was successful
			if (!$results['success']) {
				// Handle the error, e.g., invalid CSV format
				$error_message = __('Error decoding CSV', 'genes-world-plugin');
			} else {
				global $wpdb;
				$table_name = $wpdb->prefix . 'genes_world_projects';

				$wpdb->query("TRUNCATE TABLE `{$table_name}`");

				$line = 0;

				foreach ($results['data_array'] as $data) {
					$line++;
					if ($line === 1) continue;
					$wpdb->insert(
						$table_name,
						array(
							'population_name' => sanitize_text_field($data['population_name']),
							'country_origin' => sanitize_text_field($data['country_origin']),
							'technology' => sanitize_text_field($data['technology']),
							'coverage' => is_numeric($data['coverage']) ? floatval($data['coverage']) : null,
							'n_pub_available' => is_numeric($data['n_pub_available']) ? intval($data['n_pub_available']) : null,
							'n_available_upon_request' => is_numeric($data['n_available_upon_request']) ? intval($data['n_available_upon_request']) : null,
							'request_from' => sanitize_text_field($data['request_from']),
							'has_reads' => isset($data['has_reads']) && $data['has_reads'] !== '' ? intval($data['has_reads']) : null,
							'has_variants' => isset($data['has_variants']) && $data['has_variants'] !== '' ? intval($data['has_variants']) : null,
							'has_summary' => isset($data['has_summary']) && $data['has_summary'] !== '' ? intval($data['has_summary']) : null,
							'centralized_project' => isset($data['centralized_project']) && $data['centralized_project'] !== '' ? intval($data['centralized_project']) + 1 : null,
							'year' => is_numeric($data['year']) ? intval($data['year']) : null,
							'papers_with_data' => sanitize_text_field($data['papers_with_data']),
							'link' => esc_url_raw($data['link']),
							'link_alt' => esc_url_raw($data['link_alt'])
						),
						array(
							'%s','%s','%s','%f','%d','%d','%s','%d','%d','%d','%d','%d','%s','%s','%s',
						)
					);
					$imported_records++;
				}
			}
		} else $error_message = 'No file path provided';

		if (!$error_message) {
			set_transient("_genes_world_latest_report_time_", time());
			return (object) [ 'success' => true, 'message' => 'successfully imported '.$imported_records.' records.' ];
		} else {
			return (object) [ 'success' => false, 'message' => 'FAILURE! '. $error_message ];
		}
	}

	/**
	 * Upload data file
	 *
	 * @since    1.0.0
	 */
	public function upload_data_file() {
		if (
			!isset($_POST['genes_world_upload_nonce']) ||
			!wp_verify_nonce($_POST['genes_world_upload_nonce'], 'genes_world_upload_action')
		) {
			wp_die(__('Security check failed. Please try again.', 'genes-world-plugin'));
		}
		
		if (!current_user_can('manage_options')) wp_die('Access Denied!');

		if (!empty($_FILES['data_file']['tmp_name'])) {
			$uploadedfile = $_FILES['data_file'];

			// Check MIME type
			$file_mime_type = mime_content_type($uploadedfile['tmp_name']);
			if ($file_mime_type !== 'text/csv' && $file_mime_type !== 'text/plain') {
				wp_die('Invalid file type. Please upload a CSV file.');
			}
	
			// Check file extension
			$file_extension = pathinfo($uploadedfile['name'], PATHINFO_EXTENSION);
			if (strtolower($file_extension) !== 'csv') {
				wp_die('Invalid file extension. Please upload a CSV file.');
			}

			add_filter( 'upload_dir', 'genes_upload_dir' );		
			add_filter( 'sanitize_file_name', 'genes_hash_filename', 10 );
			
			$movefile = wp_handle_upload( $uploadedfile, ['test_form' => false] );

			// Remove filters after upload
			remove_filter( 'upload_dir', 'genes_upload_dir' );
			remove_filter( 'sanitize_file_name', 'genes_hash_filename', 10 );
			
			$fileurl = "";
			if ( $movefile && !isset( $movefile['error'] ) ) {
				$fileurl = $movefile['url'];
				return (object) [ 'success' => true, 'message' => 'File successfully uploaded: '.$fileurl, 'file' => $movefile['file'] ];
			} else {
				return (object) [ 'success' => false, 'message' => $movefile['error'] ];
			}
		}
	}
}

function genes_upload_dir( $dirs ) {
	$upload_dir = '/genes-world';
	$dirs['subdir'] = $upload_dir;
	$dirs['path'] = $dirs['basedir'] . $upload_dir;
	$dirs['url'] = $dirs['baseurl'] . $upload_dir;
	return $dirs;
}

function genes_hash_filename( $filename ) {
	$info = pathinfo( $filename );
	$ext  = empty( $info['extension'] ) ? '' : '.' . $info['extension'];
	$name = basename( $filename, $ext );
	return 'genes-world-import-'.date('Y-m-d_H-i').'-'.$name.$ext;
}
