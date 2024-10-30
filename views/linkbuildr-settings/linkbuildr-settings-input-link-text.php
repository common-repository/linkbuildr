<div class="lb-row lb-row-nb-first">
	<div class="input-group input-group-full-width">
		<div class="input-group-spacer">
			<input class="<?php echo esc_attr( '' === $link_text ? '' : 'has-value' ); ?>" type="text" name="<?php echo esc_attr( $setting_name ); ?>" value="<?php echo esc_attr( $link_text ); ?>" required>
			<label for="email"><?php esc_html_e( 'Unsubscribe Link Text', 'linkbuildr' ); ?></label>
		</div>
	</div>
</div>
