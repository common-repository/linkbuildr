<form id="form" class="lb-form-element" method="POST">
	<input type="hidden" name="new-domain-nonce" value="<?php echo esc_attr( $nonce ); ?>"/>
	<div id="ignoredDomainsFormWrapper" class="lb-form">
		<div class="lb-row lb-row-first">
			<div class="input-group input-group-half-width">
				<div class="input-group-spacer">
					<input class="new-ignore-domain" name="new-ignore-domain" type="text" value="" required>

					<label for="new-ignore-domain" class=""><?php esc_html_e( 'New Ignored Domain', 'linkbuildr' ); ?></label>
				</div>
			</div>
			<input type="submit" value="<?php esc_html_e( 'Add', 'linkbuildr' ); ?>" id="submit" class="lb-button-primary" name="submit">
		</div>
	</div>
</form>
