<div class="lb-row">
	<div class="input-group input-group-full-width">
		<div class="input-group-spacer">
			<textarea class="content <?php echo ( '' === $unsubscribe_message ? '' : 'has-value' ); ?>" name="<?php echo esc_attr( $attr_name ); ?>" rows="4" cols="50"><?php echo esc_html( $unsubscribe_message ); ?></textarea>
			<label for="<?php echo esc_attr( $attr_name ); ?>" class="lb-textarea-label"><?php esc_html_e( 'Unsubscribe Message', 'linkbuildr' ); ?></label>
			<div class="lb-subnote">
				<?php esc_html_e( 'Message displayed when a Contact Unsubscribes.', 'linkbuildr' ); ?>
				</br>
				<?php esc_html_e( 'Shortcodes:[contactemail], [contactname], [contactsitename]', 'linkbuildr' ); ?>
			</div>
		</div>
	</div>
</div>



