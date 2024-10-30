<div class="linkbuildr-wrapper">
	<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	<div class="logoholder"><img src="<?php echo esc_attr( $logo_url ); ?>" /></div>
	<?php if ( 0 !== count( $message ) ) : ?>
		<div class="lb-message-container">
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
		</div>
	<?php endif; ?>
	<div class="dashboardWrapper">
		<div class="siteContactTableWrapper">
			<div class="tableWrapper">
				<h3 class="lbhead"><a href="<?php echo esc_url( $site_contact_list_url ); ?>"><?php echo esc_html_e( 'Contacts', 'linkbuildr' ); ?></a></h3>
				<form id="contacts-table" method="GET">
					<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>"/>
					<input type="hidden" name="table" value="<?php echo esc_attr( $site_contact_table->__get( '_args' )['singular'] ); ?>"/>
					<?php $site_contact_table->display(); ?>
				</form>
			</div>
		</div>
		<div class="emailTemplateWrapper">
			<div class="tableWrapper">
				<h3 class="lbhead"><a href="<?php echo esc_url( $email_template_list_url ); ?>"><?php esc_html_e( 'Email Templates', 'linkbuildr' ); ?></a></h3>
				<form id="templates-table" method="GET">
					<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>"/>
					<input type="hidden" name="table" value="<?php echo esc_attr( $email_template_table->__get( '_args' )['singular'] ); ?>"/>
					<?php $email_template_table->display(); ?>
				</form>
			</div>
		</div>
	</div>
</div>
