<div class="lb-row lb-row-first lb-row-checkbox">
	<div class="input-group input-group-full-width input-group-checkbox">
		<div class="input-group-spacer">
			<input type='checkbox' name='<?php echo esc_attr( $setting_name ); ?>' <?php checked( $setting_value ); ?>  value='1'>
			<label for="<?php echo esc_attr( $setting_name ); ?>"><?php esc_html_e( 'Send Linkbuildr Emails on Publish by Default', 'linkbuildr' ); ?></label>
		</div>
	</div>
</div>
