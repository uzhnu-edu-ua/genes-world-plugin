<?php
/**
 * Form-related functionality of the plugin.
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
trait Genes_World_Plugin_Form_Settings {
    /**
	 * Register the settings for our settings page.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		register_setting(
			$this->plugin_name . '-settings',
			$this->plugin_name . '-settings',
			array(
				'sanitize_callback' => [$this, 'sandbox_register_setting' ], 
			)
		);

		// Here we are going to add a section for our setting.
		add_settings_section(
			$this->plugin_name . '-settings-section',
			__( 'Settings', 'genes-world-plugin' ),
			array( $this, 'sandbox_add_settings_section' ),
			$this->plugin_name . '-settings'
		);

		// Settings fields ************************
		// Add checkbox field
		add_settings_field(
			'genes_send_notification',
			'',
			array( $this, 'genes_send_notification_callback' ),
			$this->plugin_name . '-settings',
			$this->plugin_name . '-settings-section'
		);

		// Add text input field
		add_settings_field(
			'genes_notification_email',
			__( 'Notification email', 'genes-world-plugin' ),
			array( $this, 'genes_notification_email_callback' ),
			$this->plugin_name . '-settings',
			$this->plugin_name . '-settings-section'
		);
	}

	/**
	 * Callback for rendering the checkbox setting field.
	 *
	 * @since    1.0.0
	 */
	public function genes_send_notification_callback() {
		$settings = get_option( $this->plugin_name . '-settings' );
		$field_id = 'genes_send_notification';
		$name = $this->plugin_name . "-settings[" . $field_id . "]";
		?>
			<label>
				<input type="checkbox" id="<?php echo $field_id; ?>" name="<?php echo $name; ?>" <?php checked( isset( $settings[$field_id] ) ); ?>>
				<?php _e( 'Send notifications', $this->plugin_name ); ?>
			</label>
		<?php
	}

	/**
	 * Callback for rendering the text input setting field.
	 *
	 * @since    1.0.0
	 */
	public function genes_notification_email_callback() {
		$settings = get_option( $this->plugin_name . '-settings' );
		$field_id = 'genes_notification_email';
		$name = $this->plugin_name . "-settings[" . $field_id . "]";
		?>
			<input type="email" size="35" id="<?php echo $field_id; ?>" name="<?php echo $name; ?>" value="<?php echo esc_attr( isset( $settings[$field_id] ) ? $settings[$field_id] : '' ); ?>">
		<?php
	}

	/**
	 * Sandbox our settings.
	 *
	 * @since    1.0.0
	 */
	public function sandbox_register_setting( $input ) {

		$new_input = array();

		if (isset($input['genes_send_notification'])) {
			$new_input['genes_send_notification'] = (bool) $input['genes_send_notification'];
		}

		if (isset($input['genes_notification_email'])) {
			$new_input['genes_notification_email'] = sanitize_email($input['genes_notification_email']);
		}

		// Add success message
		add_settings_error(
			$this->plugin_name . '-settings', // Setting name used in register_setting
			'genes_world_plugin_settings_saved', // Error code
			__('Settings saved.', $this->plugin_name), // Message
			'updated' // Message type: error, updated, or notice
		);

		if (!empty($msg)) {
			add_settings_error(
				$this->plugin_name . '-settings', // Setting name used in register_setting
				'genes_world_plugin_settings_saved', // Error code
				__($msg, $this->plugin_name), // Message
				'notice' // Message type: error, updated, or notice
			);
		}

		return $new_input;

	}

	/**
	 * Sandbox our section for the settings.
	 *
	 * @since    1.0.0
	 */
	public function sandbox_add_settings_section() {

	}

}

// Define other form-related functions outside the trait as needed...
