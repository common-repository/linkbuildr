<div class="linkbuildr-wrapper">
	<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	<div class="logoholder">
		<img src="<?php echo esc_url( $logo_url ); ?>" />
		<h2 class="lbhead">
			<?php echo esc_html_e( 'Contact Importer', 'linkbuildr' ); ?>
		</h2>
	</div>

	<?php if ( ( 0 !== count( $notice ) ) || ( 0 !== count( $message ) ) ) : ?>
		<div id="lb-message-notice-container" class="lb-message-container">
			<?php if ( 0 !== count( $message ) ) : ?>
				<div class="lb-message success">
					<div class="lb-message-content-container">
						<span id="lb-importer-messages" class="lb-message-content">
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
			<?php if ( 0 !== count( $notice ) ) : ?>
				<div class="lb-message error">
					<div class="lb-message-content-container">
						<span id="lb-importer-notices" class="lb-message-content">
							<?php
							$notice_count = count( $notice );
							for ( $i = 0; $i < $notice_count; $i++ ) {
								if ( 'lb-line-break' === $notice[ $i ] ) {
									if ( ( $i + 1 ) < $notice_count ) {
										echo '<br>';
									}
								} else {
									esc_html_e( $notice[ $i ] );
									if ( ( $i + 1 ) < $notice_count ) {
										echo '<br>';
									}
								}
							}
							?>
						</span>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="lb-form">
		<form id="lb-importer-pre-form" class="lb-form-element">
			<div class="lb-row lb-row-first">
				<div class="input-group input-group-full-width input-group-second input-group-for-select">
					<div class="input-group-spacer">
						<select id="default_email_template_id" name="email_template_id" >
							<?php foreach ( $email_templates as $email_template ) { ?>
								<option value="<?php echo esc_attr( $email_template['id'] ); ?>">
									<?php esc_html_e( $email_template['templatename'] ); ?>
								</option>
							<?php } ?>
						</select>
						<label for="email_template_id"><?php esc_html_e( 'Default Email Template to use for imported Contacts', 'linkbuildr' ); ?></label>
						<input type="hidden" id="php_max_input_vars" name="php_max_input_vars" value="<?php esc_attr_e( $max_input_vars ); ?>"/>
					</div>
				</div>
			</div>

			<div class="lb-row lb-row-half-margin">
				<div id="lb-file-requirements" class="input-group input-group-full-width">
					<h4><?php esc_html_e( 'CSV File Requirements for Import', 'linkbuildr' ); ?><span id="lb-file-requirements-icon" class="dashicons-before"></span></h4>
					<ul>
						<li id="lb-file-req-firstLine" class="lb-file-requirement-notice dashicons-before"><?php esc_html_e( 'First row should be column headers', 'linkbuildr' ); ?></li>
						<li id="lb-file-req-includedHeaders" class="lb-file-requirement-notice dashicons-before"><?php esc_html_e( 'Column headers should include the following values to specify corresponding data:', 'linkbuildr' ); ?>
							<ul>
								<li id="lb-file-req-includedHeaders-domain" class="lb-file-requirement-notice dashicons-before"><?php esc_html_e( '"domain" - full url, including "http://" or "https://"', 'linkbuildr' ); ?></li>
								<li id="lb-file-req-includedHeaders-site" class="lb-file-requirement-notice dashicons-before"><?php esc_html_e( '"site" - the name used to reference the site', 'linkbuildr' ); ?></li>
								<li id="lb-file-req-includedHeaders-name" class="lb-file-requirement-notice dashicons-before"><?php esc_html_e( '"name" - the name of the contact', 'linkbuildr' ); ?></li>
								<li id="lb-file-req-includedHeaders-email" class="lb-file-requirement-notice dashicons-before"><?php esc_html_e( '"email" - the email address for the contact', 'linkbuildr' ); ?></li>
							</ul>
						</li>
						<li id="lb-file-req-columnLimit" class="lb-file-requirement-notice dashicons-before"><?php esc_html_e( 'There should only be 4 columns in the file', 'linkbuildr' ); ?></li>
						<li id="lb-file-req-rowLimit" class="lb-file-requirement-notice dashicons-before"><?php esc_html_e( 'The file should not exceed ', 'linkbuildr' ); ?><?php esc_html_e( $max_input_vars - 5 ); ?><?php esc_html_e( ' rows', 'linkbuildr' ); ?></li>
					</ul>
				</div>
				<div class="input-group input-group-full-width">
					<div class="input-group-spacer">
						<div class="upload-btn-wrapper">
							<button class="file-select-button lb-button-primary"><?php esc_html_e( 'Select a file', 'linkbuildr' ); ?></button>
							<input type="file" id="import_site_contacts_csv" name="import_site_contacts_csv" value="" class="has-value all-options" />
							<output id="file-details"></output>
						</div>
						<label class="lb-static-label" for="import_site_contacts_csv"><?php esc_html_e( 'CSV File to import new Contacts', 'linkbuildr' ); ?></label>
					</div>
				</div>
			</div>
		</form>

		<form id="lb-importer-data-form" class="lb-form-element">
			<div id="importer-file-ui">
				<input type="hidden" name="columnMap[domain]" class="lb-datamap-store" value="-"/>
				<input type="hidden" name="columnMap[site]" class="lb-datamap-store" value="-"/>
				<input type="hidden" name="columnMap[email]" class="lb-datamap-store" value="-"/>
				<input type="hidden" name="columnMap[name]" class="lb-datamap-store" value="-"/>
				<input type="hidden" name="columnMap[template]" class="lb-datamap-store" value="4"/>

				<div id="file-data-errors"></div>
				<div id="header-holder-wrapper" class="hide">
					<label><?php esc_html_e( 'Unassociated Headers', 'linkbuildr' ); ?></label>
					<div id="header-holder"></div>
				</div>
				<div id="file-data-table-output"></div>
				<div id="lb-submit-row" class="lb-row lb-submit-row lb-importer-submit-row">
					<input type="hidden" id="lb-submit-import" name="lb-submit-import" value="false"/>
					<input type="submit" value="<?php esc_html_e( 'Import', 'linkbuildr' ); ?>" id="lb-contact-import-submit" class="lb-button-primary" name="submit">
					<div id="file-data-summary"></div>
				</div>
				<div id="form-data-output"></div>
			</div>
		</form>
		<form id="lb-importer-form" class="lb-hidden-form-element" method="post" action="" enctype="multipart/form-data">
			<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>"/>
			<input type="hidden" id="lb-importer-skip-count" name="skip-count" value="" />
			<div id="lb-importer-form-columnMap-container"></div>
			<div id="lb-importer-form-rowData-container"></div>
		</form>
	</div>
</div>
