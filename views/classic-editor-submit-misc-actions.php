<div class="misc-pub-section" id="LB-options">
	<div class="logoholdermeta"><img src="<?php echo esc_url( $plugin_dir ) . 'img/linkbuilder_logo.svg'; ?>" /></div>
	<?php wp_nonce_field( plugin_basename( __FILE__ ), 'linkbuildr_email_nonce' ); ?>
	<?php if ( ( 'draft' === $post_status ) || ( 'auto-draft' === $post_status ) ) : ?>
		<div class="savetext"><?php _e( 'Click', 'linkbuildr' ); ?> <u style="font-weight:700;"><?php _e( 'Save Draft', 'linkbuildr' ); ?></u> <?php _e( 'above to find new links before publishing', 'linkbuildr' ); ?></div>
		<label for="linkbuildr_send_email_on_publish_post" style="font-weight:700;"><?php _e( 'Send Linkbuildr Emails on Publish', 'linkbuildr' ); ?></label>
		<input type="checkbox" id="linkbuildr_send_email_on_publish_post" name="linkbuildr_send_email_on_publish_post" <?php echo ( $linkbuildr_send_email_on_publish_value ? 'checked' : '' ); ?>/>
		<input type="checkbox" id="linkbuildr_classic_editor_check" name="linkbuildr_classic_editor_check" checked/>
	<?php elseif ( 'publish' === $post_status ) : ?>
		<div class="lb-sent-email-notice">
			<?php esc_html_e( sprintf( '%1$d outreach email%2$s sent for this post', $sent_email_count, ( 1 < $sent_email_count ? 's' : '' ) ) ); ?>
			<input type="checkbox" id="linkbuildr_classic_editor_check" name="linkbuildr_classic_editor_check" checked/>
		</div>
	<?php endif; ?>
</div>
