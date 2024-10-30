<div class="emailTemplateMetaBoxWrapper lb-form">
	<div class="lb-row lb-row-first">
		<div class="input-group input-group-full-width">
			<div class="input-group-spacer">
				<input class="templatename <?php echo ( '' === $item['templatename'] ? '' : 'has-value' ); ?>" name="templatename" type="text" value="<?php echo esc_attr( $item['templatename'] ); ?>" required>
				<label for="templatename"><?php esc_html_e( 'Email Template Name', 'linkbuildr' ); ?></label>
			</div>
		</div>
	</div>
	<div class="lb-row">
		<div class="input-group input-group-full-width">
			<div class="input-group-spacer">
				<input class="sender <?php echo ( ( ( 'Post Author' === $item['sender'] ) || ( '' === $item['sender'] ) ) ? '' : 'has-value' ); ?>" name="sender" type="email" value="<?php echo ( ( ( 'Post Author' !== $item['sender'] ) && ( '' !== $item['sender'] ) ) ? esc_attr( $item['sender'] ) : '' ); ?>">
				<label for="sender"><?php esc_html_e( 'Sender Email', 'linkbuildr' ); ?></label>
				<div class="lb-subnote"><?php esc_html_e( 'Optional: Defaults to Post Author if left blank', 'linkbuildr' ); ?></div>
			</div>
		</div>
	</div>
	<div class="lb-row">
		<div class="input-group input-group-full-width">
			<div class="input-group-spacer">
				<input class="subject <?php echo ( '' === $subjectformat ? '' : 'has-value' ); ?>" name="subject" type="text" value="<?php echo esc_attr( $subjectformat ); ?>" required>
				<label for="subject"><?php esc_html_e( 'Email Subject', 'linkbuildr' ); ?></label>
			</div>
		</div>
	</div>
	<div class="lb-row">
		<div class="input-group input-group-full-width">
			<div class="input-group-spacer">
				<textarea class="content <?php echo ( '' === $contentformat ? '' : 'has-value' ); ?>" name="content" type="text" rows="8" required><?php echo esc_textarea( $contentformat ); ?></textarea>
				<label for="content" class="lb-textarea-label"><?php esc_html_e( 'Message Template', 'linkbuildr' ); ?></label>
				<div class="lb-subnote"><?php esc_html_e( 'Shortcodes:[posturl], [contactname], [contactsitename], [author]', 'linkbuildr' ); ?></div>
			</div>
		</div>
	</div>
	<div class="lb-row">
		<div class="input-group input-group-full-width">
			<div class="input-group-spacer">
				<textarea class="tweet <?php echo ( '' === $tweetformat ? '' : 'has-value' ); ?>" name="tweet" type="text" maxlength="266" rows="4"  required><?php echo esc_textarea( $tweetformat ); ?></textarea>
				<label for="tweet" class="lb-textarea-label"><?php esc_html_e( 'Tweet Template', 'linkbuildr' ); ?></label>
				<div class="lb-subnote"><?php esc_html_e( 'Shortcodes:[posturl], [contactname], [contactsitename], [author]', 'linkbuildr' ); ?><br/><?php esc_html_e( 'Maximum of ', 'linkbuildr' ); ?><strong><?php esc_html_e( '266 characters', 'linkbuildr' ); ?></strong><?php esc_html_e( ' including shortcode values', 'linkbuildr' ); ?></div>
			</div>
		</div>
	</div>
	<div class="lb-row">
		<input type="submit" value="<?php esc_html_e( 'Save', 'linkbuildr' ); ?>" id="submit" class="lb-button-primary" name="submit">
	</div>
</div>
