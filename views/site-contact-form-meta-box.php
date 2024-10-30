<div class="siteContactMetaBoxWrapper lb-form">
	<div class="lb-row <?php echo ( 1 !== intval( $nb ) ) ? 'lb-row-first' : 'lb-row-nb-first'; ?>">
		<div class="input-group input-group-full-width">
			<div class="input-group-spacer">
				<?php if ( 1 === intval( $nb ) ) : ?>
				<div class="domains lb-input-div has-value" name="domain" type="text">
					<?php echo esc_attr( $item['domain'] ); ?>
				</div>
				<input name="domain" type="hidden" value="<?php echo esc_attr( $item['domain'] ); ?>">
				<?php else : ?>
					<input class="domains <?php echo ( '' === $item['domain'] ? '' : 'has-value' ); ?>" name="domain" type="text" value="<?php echo esc_attr( $item['domain'] ); ?>" required>
				<?php endif; ?>

				<label for="domains" class="<?php echo esc_attr( ( 1 === intval( $nb ) ) ? 'lb-static-label' : '' ); ?>"><?php esc_html_e( 'Domain', 'linkbuildr' ); ?></label>

				<?php if ( 1 !== intval( $nb ) ) : ?>
					<div class="lb-subnote"><?php echo esc_html_e( 'Use full root domain path including http://', 'linkbuildr' ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="lb-row">
		<div class="input-group">
			<div class="input-group-spacer">
				<input class="sitename <?php echo esc_attr( '' === $item['sitename'] ? '' : 'has-value' ); ?>" name="sitename" type="text" value="<?php echo esc_attr( $item['sitename'] ); ?>" required>
				<label for="sitename"><?php esc_html_e( 'Site Name', 'linkbuildr' ); ?></label>
			</div>
		</div>
		<div class="input-group input-group-second">
			<div class="input-group-spacer">
				<input class="firstname <?php echo esc_attr( '' === $item['firstname'] ? '' : 'has-value' ); ?>" name="firstname" type="text" value="<?php echo esc_attr( $item['firstname'] ); ?>" required>
				<label for="firstname"><?php esc_html_e( 'Contact Name', 'linkbuildr' ); ?></label>
			</div>
		</div>
	</div>
	<div class="lb-row">
		<div class="input-group">
			<div class="input-group-spacer">
				<input class="email <?php echo esc_attr( '' === $item['email'] ? '' : 'has-value' ); ?>" name="email" type="email" value="<?php echo esc_attr( $item['email'] ); ?>" required>
				<label for="email"><?php esc_html_e( 'Email', 'linkbuildr' ); ?></label>
			</div>
		</div>
		<div class="input-group input-group-second input-group-for-select">
			<div class="input-group-spacer">
				<select id="email_template_id" name="email_template_id" >
					<?php foreach ( $email_templates as $email_template ) { ?>
						<option value="<?php echo esc_attr( $email_template['id'] ); ?>" <?php echo esc_attr( ( $item['email_template_id'] === $email_template['id'] ) ? 'selected' : '' ); ?>>
							<?php esc_html_e( $email_template['templatename'] ); ?>
						</option>
					<?php } ?>
				</select>
				<label for="email_template_id"><?php esc_html_e( 'Message Template', 'linkbuildr' ); ?></label>
			</div>
		</div>
	</div>
	<div class="lb-row">
		<div class="input-group input-group-checkbox">
			<div class="input-group-spacer">
				<input class="unsubscribed" name="unsubscribed" type="checkbox" value="<?php echo esc_attr( $item['unsubscribed'] ); ?>" <?php echo esc_attr( $item['unsubscribed'] ? 'checked' : '' ); ?>>
				<label for="unsubscribed"><?php esc_html_e( 'Unsubscribed', 'linkbuildr' ); ?></label>
			</div>
		</div>
	</div>
	<div class="lb-row">
		<input type="submit" value="<?php esc_html_e( 'Save', 'linkbuildr' ); ?>" id="submit" class="lb-button-primary" name="submit">
	</div>
</div>
