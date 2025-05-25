<?php

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Genes_World_Plugin
 * @subpackage Genes_World_Plugin/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div id="wrap">
	<form method="post" action="" enctype="multipart/form-data">
        <?php
			// Upload file
			if (isset($_POST['is_file_submitted'])) {
				$upload_result = $this->upload_data_file();
			}
		?>
		
		<h3>Upload Genes-WORLD data file to auto-import</h3>

		<p>File has to be in specific .CSV format</p>

		<?php wp_nonce_field('genes_world_upload_action', 'genes_world_upload_nonce'); ?>		
		
		<p>
			<input type="hidden" name="is_file_submitted" value="1" />
			<input type="file" name="data_file" accept=".csv,text/csv"/>
			<input type="submit" class="button-primary" name="import_file" value="<?php _e('Upload', 'genes-world-plugin') ?>" />
		</p>

		<?php
			if (!empty($upload_result)) { 
				echo "<p>" . esc_html($upload_result->message) . "</p>";

				if ($upload_result->success) {
					echo "<p>Start importing...</p>";
					
					$import_result = $this->import_data_file($upload_result->file);
					
					echo "<div class='notice ". ($upload_result->success ? 'notice-success' : 'notice-error') ."'><p>Import result: <span>" . esc_html($import_result->message) . "</span></p></div>";
				}
			}
		?>
	</form>

	<form method="post" action="options.php">
		<?php
            // Output nonce, action, and option_page fields for a settings page
            settings_fields( $this->plugin_name . '-settings' );

            // Output sections of a settings page
            do_settings_sections( $this->plugin_name . '-settings' );
			
			settings_errors();
			
            // Output a submit button
            submit_button( 'Save Settings' );

		?>
	</form>
</div>
