<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Genes_World_Plugin
 * @subpackage Genes_World_Plugin/public
 */

require_once plugin_dir_path( __FILE__ ) . 'partials/genes-world-public-geodata.php';

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Genes_World_Plugin
 * @subpackage Genes_World_Plugin/public
 * @author     Alex Dubiv <alex.dubiv@uzhnu.edu.ua>
 */
class Genes_World_Plugin_Public {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $genes_world_plugin    The ID of this plugin.
	 */
	private $genes_world_plugin;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $is_user_logged_in;
	private $session_uid;
	private $wpdb;
	private $table_name;

	private $data_keys_i18n;
	private $dbrecord_id;
	private $projects_table_name;

	use Genes_World_Public_Geodata;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $genes_world_plugin       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $genes_world_plugin, $version ) {
		global $wpdb;

		$this->genes_world_plugin = $genes_world_plugin;
		$this->version = $version;
        $this->wpdb = $wpdb;
		$this->table_name = $this->wpdb->prefix . 'genes_world';
		$this->projects_table_name = $this->table_name . '_projects';

		if (session_status() == PHP_SESSION_NONE) session_start();
		$this->session_uid = $_SESSION['user_genes_uid'] ?? null;
		$this->is_user_logged_in = get_transient("_genes_uid_active_".$this->session_uid); // cache result

		add_shortcode('genes_world_geodata', array($this, 'genes_world_geodata_shortcode'));
		add_action('wp_head', array($this, 'add_settings_script_to_head'));

		// AJAX ENDPOINTS
		// Register the AJAX action for logged-in users
		add_action('wp_ajax_show_dashboard_endpoint', array($this, 'handle_show_dashboard_endpoint'));
		// Register the AJAX action for non-logged-in users
		add_action('wp_ajax_nopriv_show_dashboard_endpoint', array($this, 'handle_show_dashboard_endpoint'));
	}

	public function add_settings_script_to_head() {
		?>
			<script>
				// Your JavaScript code here
				window.genes_world_settings = {};
				window.genes_world_settings.ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
			</script>
    	<?php	
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		if (is_singular()) {
			// Get the post name for the current post
			$post_name = get_post_field('post_name', get_post());

			if ($post_name === 'genes-world-dashboard' || $post_name === 'world-geo-data') {
				wp_enqueue_style( 'c3', 'https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.20/c3.min.css', array(), $this->version, 'all' );
			}

			if ($post_name === 'world-geo-data') {
				wp_enqueue_style( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), $this->version, 'all' );
				wp_enqueue_style( 'grid', 'https://unpkg.com/gridjs/dist/theme/mermaid.min.css', array(), '6.2.0', 'all' );
			}
		}

		wp_enqueue_style( $this->genes_world_plugin, plugin_dir_url( __FILE__ ) . 'css/genes-world-plugin-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'bulma-prefixed-styles', plugin_dir_url( __FILE__ ) . 'css/bulma.custom.prefixed.min.css', array(), $this->version, 'all' );
		

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		if (is_singular()) {
			// Get the post name for the current post
			$post_name = get_post_field('post_name', get_post());

			if ($post_name === 'genes-world-report') {
				wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '2.10.0', true );
			}
			
			if ($post_name === 'genes-world-dashboard' || $post_name === 'world-geo-data') {
				wp_enqueue_script( 'd3', 'https://cdnjs.cloudflare.com/ajax/libs/d3/5.16.0/d3.min.js', array(), '5.16.0', true );
				wp_enqueue_script( 'c3', 'https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.20/c3.min.js', array(), '0.7.20', true );
			}

			if ($post_name === 'world-geo-data') {
				wp_enqueue_script( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '5.16.0', true );
				wp_enqueue_script( 'grid', 'https://unpkg.com/gridjs/dist/gridjs.umd.js', array(), '6.2.0', true );
				wp_enqueue_script( 'genes-world-js-geodata', plugin_dir_url( __FILE__ ) . 'js/genes-world-plugin-geodata.js', array( 'jquery' ), $this->version, true );
			}
		}
		wp_enqueue_script( 'genes-world-js', plugin_dir_url( __FILE__ ) . 'js/genes-world-plugin-public.js', array( 'jquery' ), $this->version, true );
	}

	private function track_user_info($id) {
		$table_name = $this->wpdb->prefix . 'genes_world_track';
		$this->wpdb->insert(
			$table_name,
			array(
				'id' => $id,
				'ipaddress' => $_SERVER['REMOTE_ADDR'],
				'useragent' => $_SERVER['HTTP_USER_AGENT'],
				'mobile' => wp_is_mobile()
			),
			array(
				'%d',
				'%s',
				'%s',
				'%d'
			)
		);
	}

	private function message_html($text, $class = 'bu-is-info') {
		return "<div class='bu-message $class'><div class='bu-message-body'>".wp_kses_post($text)."</div></div>";
	}

	// Callback function to handle the AJAX request
	public function handle_show_dashboard_endpoint() {
		// // Call the function to generate the dashboard content - reserved for future functionality
		// $region = isset($_POST['region']) ? $_POST['region'] : null;
		// $dashboard_content = $this -> show_dashboard(['ajax' => true, 'region' => $region]);

		// // Return the dashboard content as JSON
		// wp_send_json_success($dashboard_content);
	}
}
