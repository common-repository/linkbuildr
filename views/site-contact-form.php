<div class="linkbuildr-wrapper <?php echo ( 1 === intval( $nb ) ) ? 'linkbuildr-modal-form' : ''; ?>">
	<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	<div class="logoholder">
		<img src="<?php echo esc_url( $logo_url ); ?>" />
		<h2 class="lbhead">
			<?php if ( 1 === intval( $nb ) ) : ?>
				<?php echo esc_html_e( 'New Contacts', 'linkbuildr' ); ?>
			<?php else : ?>
				<?php
					// translators: %s: Edit or New before 'Contact' depending on context.
					esc_html_e( sprintf( __( '%s Contact', 'linkbuildr' ), $title_type ) );
				?>
				<a class="lb-h2" href="<?php echo esc_url( $backlink_url ); ?>"><?php esc_html_e( 'Return to List', 'linkbuildr' ); ?></a>
			<?php endif; ?>
		</h2>
	</div>

	<?php if ( ( 0 !== count( $notice ) ) || ( 0 !== count( $message ) ) ) : ?>
		<div class="lb-message-container">
			<?php if ( 0 !== count( $notice ) ) : ?>
				<div class="lb-message error">
					<div class="lb-message-content-container">
						<span class="lb-message-content">
							<?php
							$notice_count = count( $notice );
							for ( $i = 0; $i < $notice_count; $i++ ) {
								esc_html_e( $notice[ $i ] );
								if ( ( $i + 1 ) < $notice_count ) {
									echo '</br>';
								}
							}
							?>
						</span>
					</div>
				</div>
			<?php endif; ?>
			<?php if ( 0 !== count( $message ) ) : ?>
				<div class="lb-message success">
					<div class="lb-message-content-container">
						<span class="lb-message-content">
							<?php
							$message_count = count( $message );
							for ( $i = 0; $i < $message_count; $i++ ) {
								esc_html_e( $message[ $i ] );
								if ( ( $i + 1 ) < $message_count ) {
									echo '</br>';
								}
							}
							?>
						</span>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( 1 === intval( $nb ) ) : ?>
		<style>
			#wpadminbar,
			#adminmenuback,
			#adminmenuwrap,
			#wpfooter,
			h2 .lb-h2,
			.handlediv,
			.hidethis,
			.update-nag { 
				display:none !important;
			}

			.auto-fold #wpcontent, 
			.auto-fold #wpfooter { 
				margin-left:0px !important;
				padding-left:0px;
			}

			#post-body-content { 
				margin-top: -30px;
			}

			#wpbody-content { 
				background-color: #f1f1f1;
				padding-bottom: 10px;
			}

			.linkbuildr-wrapper {
				padding-top:0px;
			}

			#poststuff .inside {
				margin: -15px 0 0;
			}

			.pager {
				padding:8px 12px;
				background-color:#FFFFFF;
				border: 2px solid #1A237E;
				color:#1A237E;
				font-weight:bold;
				text-decoration:none;
			}

			.current {
				background-color:#1A237E;
				color:#FFFFFF;
			}

			.pager:hover {
				background-color:#1A237E;
				color:#FFFFFF;
			}

			html.wp-toolbar {
					padding-top: 0px!important;
			}
		</style>

		<?php if ( 0 === count( $site_contacts_to_update ) ) : ?>
			<div class="lb-row lb-text-center">
				<a class="lb-button-primary lb-close-modal" href="#"><?php esc_html_e( 'Close', 'linkbuildr' ); ?></a>
			</div>
		<?php endif; ?>

		<?php foreach ( $site_contacts_to_update as $site_contact_item ) : ?>
			<form id="form" class="lb-form-element" method="POST">
				<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>"/>
				<input type="hidden" name="id" value="<?php echo esc_attr( $site_contact_item['id'] ); ?>"/>

				<?php
					$form_variables                        = array();
						$form_variables['nb']              = $nb;
						$form_variables['item']            = $site_contact_item;
						$form_variables['email_templates'] = $email_templates;
				?>

				<?php echo Linkbuildr_Settings::render_template( 'site-contact-form-meta-box.php', $form_variables, 'always' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</form>
		<?php endforeach; ?>
	<?php else : ?>
		<form id="form" class="lb-form-element" method="POST">
			<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>"/>
			<input type="hidden" name="id" value="<?php echo esc_attr( $item['id'] ); ?>"/>

			<?php
				$form_variables                    = array();
				$form_variables['nb']              = $nb;
				$form_variables['item']            = $item;
				$form_variables['email_templates'] = $email_templates;
			?>
			<?php echo Linkbuildr_Settings::render_template( 'site-contact-form-meta-box.php', $form_variables, 'always' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</form>
	<?php endif; ?>
</div>
