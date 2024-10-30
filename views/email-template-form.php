<div class="linkbuildr-wrapper">
	<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	<div class="logoholder">
		<img src="<?php echo esc_url( $logo_url ); ?>" />
		<h2 class="lbhead">
			<?php
				// translators: %s: Edit or New before 'Email Template' depending on context.
				esc_html_e( sprintf( __( '%s Email Template', 'linkbuildr' ), $title_type ) );
			?>
			<a class="lb-h2" href="<?php echo esc_attr( $backlink_url ); ?>">
				<?php echo esc_html_e( 'Return to List', 'linkbuildr' ); ?>
			</a>
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

	<form id="form" class="lb-form-element" method="POST">
		<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>"/>
		<input type="hidden" name="id" value="<?php echo esc_attr( $item['id'] ); ?>"/>

		<?php
			$form_variables                  = array();
			$form_variables['item']          = $item;
			$form_variables['contentformat'] = $contentformat;
			$form_variables['subjectformat'] = $subjectformat;
			$form_variables['tweetformat']   = $tweetformat;
		?>
		<?php echo Linkbuildr_Settings::render_template( 'email-template-form-meta-box.php', $form_variables, 'always' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</form>
</div>
