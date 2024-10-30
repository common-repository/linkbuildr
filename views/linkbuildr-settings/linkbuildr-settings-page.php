<div class="linkbuildr-wrapper">
	<div class="logoholder">
		<img src="<?php echo esc_url( $logo_url ); ?>" />
		<h2 class="lbhead">
			<?php esc_html_e( 'Settings', 'linkbuildr' ); ?>
		</h2>
	</div>
	<form action='options.php' method='post' class="lb-form-element">
		<div class="linkbuildr-settingsFormWrapper lb-form">
			<?php settings_fields( 'linkbuildr-user-settings' ); ?>

			<div class="linkbuildr-settings-section">
				<?php 
					echo $general_section; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $send_on_publish_default_checkbox;  // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</div>
			<div class="linkbuildr-settings-section">
				<?php
					echo $notification_section;  // phpcs:ignore WordPress.Security.EscapeOutput
					echo $notification_checkbox;  // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</div>
			<div class="linkbuildr-settings-section">
				<?php
					echo $unsubscribe_section; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $unsubscribe_link_text; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $unsubscribe_landing_select; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $unsubscribe_404_checkbox; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $unsubscribe_message_textarea; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $unsubscribe_already_message_textarea; // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</div>
			<div class="linkbuildr-settings-submit-wrapper">
				<input type="submit" value="<?php esc_html_e( 'Save', 'linkbuildr' ); ?>" id="submit" class="lb-button-primary" name="submit">
			</div>
		</div>
	</form>
</div>
