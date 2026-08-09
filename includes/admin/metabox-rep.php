<?php
/**
 * Representative meta box ("اطلاعات نمایندگی").
 *
 * Field definitions live in includes/config.php.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register representative meta box.
 */
function afq_rep_add_meta_box() {
	add_meta_box(
		'afq_rep_info',
		'اطلاعات نمایندگی',
		'afq_rep_meta_box_callback',
		'afq_rep',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'afq_rep_add_meta_box' );

/**
 * Render representative meta box.
 *
 * @param WP_Post $post Current post object.
 */
function afq_rep_meta_box_callback( $post ) {

	wp_nonce_field( 'afq_rep_save_meta', 'afq_rep_meta_nonce' );
	?>
	<div class="afq-rep-admin">
		<p class="afq-rep-admin__hint">نام نمایندگی همان «عنوان» است و استان از باکس «استان‌ها» انتخاب می‌شود. فیلد خالی در سایت نمایش داده نمی‌شود.</p>

		<div class="afq-rep-admin__grid">
			<?php foreach ( afq_rep_get_fields() as $key => $field ) : ?>
				<?php
				$value    = get_post_meta( $post->ID, $key, true );
				$field_id = 'afq-rep-' . sanitize_html_class( $key );
				$is_wide  = ( 'textarea' === $field['type'] );
				$is_ltr   = in_array( $field['type'], array( 'url', 'email' ), true );
				?>
				<div class="afq-rep-admin__field<?php echo $is_wide ? ' afq-rep-admin__field--wide' : ''; ?>">
					<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>

					<?php if ( 'textarea' === $field['type'] ) : ?>
						<textarea
							id="<?php echo esc_attr( $field_id ); ?>"
							name="<?php echo esc_attr( $key ); ?>"
							rows="4"
							placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"><?php echo esc_textarea( $value ); ?></textarea>

					<?php elseif ( 'select' === $field['type'] ) : ?>
						<select id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $key ); ?>">
							<?php foreach ( $field['options'] as $opt_value => $opt_label ) : ?>
								<option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $value, $opt_value ); ?>>
									<?php echo esc_html( $opt_label ); ?>
								</option>
							<?php endforeach; ?>
						</select>

					<?php else : ?>
						<input
							type="<?php echo esc_attr( $field['type'] ); ?>"
							id="<?php echo esc_attr( $field_id ); ?>"
							name="<?php echo esc_attr( $key ); ?>"
							value="<?php echo esc_attr( $value ); ?>"
							placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
							<?php echo $is_ltr ? 'style="direction:ltr;text-align:left;"' : ''; ?> />
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Save representative meta.
 *
 * @param int $post_id Post ID.
 */
function afq_rep_save_meta( $post_id ) {

	if ( ! isset( $_POST['afq_rep_meta_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['afq_rep_meta_nonce'] ), 'afq_rep_save_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'afq_rep' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( afq_rep_get_fields() as $key => $field ) {

		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		switch ( $field['type'] ) {
			case 'textarea':
				$value = sanitize_textarea_field( $raw );
				break;
			case 'url':
				$value = esc_url_raw( $raw );
				break;
			case 'email':
				$value = sanitize_email( $raw );
				break;
			default:
				$value = sanitize_text_field( $raw );
		}

		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_afq_rep', 'afq_rep_save_meta' );
