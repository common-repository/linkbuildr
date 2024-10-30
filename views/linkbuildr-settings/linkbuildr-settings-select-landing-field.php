<div class="lb-row">
	<div class="input-group input-group-second input-group-for-select">
		<div class="input-group-spacer">
			<select name="<?php echo esc_attr( $setting_group ) . '[' . esc_attr( $setting_name ) . ']'; ?>">
				<?php if ( null === $current_value ) : ?>
					<option value="">Select a Page...</option>
				<?php endif; ?>
				<?php foreach ( $pages as $page_option ) : ?>
					<option 
						value="<?php echo esc_attr( $page_option->ID ); ?>" 
						<?php selected( $page_option->ID, $current_value ); ?>
					>
						<?php echo esc_html( $page_option->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<label for="<?php echo esc_attr( $setting_group ) . '[' . esc_attr( $setting_name ) . ']'; ?>"><?php esc_html_e( 'Landing Page for Unsubscribe Link', 'linkbuildr' ); ?></label>
		</div>
	</div>
</div>


