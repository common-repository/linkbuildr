<div class="error">
	<p><?php esc_html_e( LINKBUILDR_NAME ); ?> <?php esc_html_e( 'error: Your environment doesn\'t meet all of the system requirements listed below.', 'linkbuildr' ); ?></p>

	<ul class="ul-disc">
		<li>
			<strong>
			<?php
				// translators: %d: the version of PHP required.
				esc_html_e( sprintf( __( 'PHP %d+', 'linkbuildr' ), LINKBUILDR_REQUIRED_PHP_VERSION ) );
			?>
			</strong>
			<em>
			<?php
				// translators: %d: the version of PHP currently installed.
				esc_html_e( sprintf( __( '(You\'re running version %d)', 'linkbuildr' ), PHP_VERSION ) );
			?>
			</em>
		</li>

		<li>
			<strong>
			<?php
				// translators: %d: the version of WordPress required.
				esc_html_e( sprintf( __( 'WordPress %d+', 'linkbuildr' ), LINKBUILDR_REQUIRED_WP_VERSION ) );
			?>
			</strong>
			<em>
			<?php
				// translators: %d: the version of WordPress currently in use.
				esc_html_e( sprintf( __( '(You\'re running version %d)', 'linkbuildr' ), $wp_version ) );
			?>
			</em>
		</li>
	</ul>

	<p><?php echo esc_html_e( 'If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to ', 'linkbuildr' ); ?><a href="http://codex.wordpress.org/Upgrading_WordPress"><?php echo esc_html_e( 'the Codex', 'linkbuildr' ); ?></a>.</p>
</div>
