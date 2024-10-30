<div class="linkbuildr-wrapper">
	<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	<div class="logoholder">
		<img src="<?php echo esc_url( $logo_url ); ?>" />
		<h2 class="lbhead">
			<?php esc_html_e( 'Ignored Domains', 'linkbuildr' ); ?>
		</h2>
	</div>

	<?php if ( 0 !== count( $message ) ) : ?>
		<div class="lb-message-container">
			<div id="message" class="updated">
				<p>
					<?php
					$message_count = count( $message );
					for ( $i = 0; $i < $message_count; $i++ ) {
						esc_html_e( $message[ $i ] );
						if ( ( $i + 1 ) < $message_count ) {
							echo '</br>';
						}
					}
					?>
				</p>
			</div>
		</div>
	<?php endif; ?>

	<?php
		$form_variables          = array();
		$form_variables['nonce'] = $new_form_nonce;
		echo Linkbuildr_Settings::render_template( 'ignored-domains-form.php', $form_variables, 'always' ); // phpcs:ignore WordPress.Security.EscapeOutput 
	?>

	<div class="tableWrapper">
		<form id="ignored-domains-table" method="GET">
			<input type="hidden" name="page" value="ignored-domains" />
			<?php $table->search_box( __( 'Search', 'linkbuildr' ), 'search_id' ); ?>
			<?php $table->display(); ?>
		</form>
	</div>

</div>
