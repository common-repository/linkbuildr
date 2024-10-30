
<div class="linkbuildr-wrapper">
	<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	<div class="logoholder">
		<img src="<?php echo esc_attr( $logo_url ); ?>" />
		<h2 class="lbhead">
			<?php esc_html_e( 'Email Templates', 'linkbuildr' ); ?>
			<a class="lb-h2" href="<?php echo esc_attr( $edit_form_url ); ?>">
				<?php esc_html_e( 'Add new', 'linkbuildr' ); ?>
			</a>
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

	<div class="tableWrapper">
		<form id="templates-table" method="GET">
			<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>"/>
			<?php $table->display(); ?>
		</form>
	</div>

</div>
