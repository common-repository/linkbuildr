<div class="updated notice is-dismissible" id="linkbuildr_contact_alert">
	<p class="logoholdermsg">
		<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_html_e( 'Linkbuildr', 'linkbuildr' ); ?>" />
		<?php
			// translators: %1$d: count of urls/domains needing contact info. %2$s: adds an 's' for plural if needed.
			esc_html_e( sprintf( __( 'found %1$d website%2$s in your post that need contact details added:', 'linkbuildr' ), $show_count, ( ( 1 === intval( $show_count ) ) ? '' : 's' ) ) );
		?>
		<a href="/wp-admin/admin.php?page=site-contact-form&id=<?php echo esc_attr( $post_site_contact_id ); ?>&scid=<?php echo esc_attr( $site_contact_id ); ?>&nb=1&pid=<?php echo esc_attr( $post_id_local ); ?>&bid=<?php echo esc_attr( $blog_id_local ); ?>&TB_iframe=true&width=600px&height=525px" class="thickbox" target="_blank"><?php esc_html_e( 'Add Details', 'linkbuildr' ); ?></a>
	</p>
</div>
