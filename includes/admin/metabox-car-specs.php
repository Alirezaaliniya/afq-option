<?php
/**
 * Car technical specs meta box (tabbed).
 *
 * Field definitions live in includes/config.php.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add the specs meta box.
 */
function afq_car_add_specs_meta_box() {
	add_meta_box(
		'afq_car_specs',
		'مشخصات فنی خودرو',
		'afq_car_render_specs_meta_box',
		'afq_car',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'afq_car_add_specs_meta_box' );

/**
 * Render the specs meta box.
 *
 * @param WP_Post $post Current post object.
 */
function afq_car_render_specs_meta_box( $post ) {

	wp_nonce_field( 'afq_car_specs_save', 'afq_car_specs_nonce' );

	$sections = afq_car_get_spec_sections();
	?>
	<div class="afq-specs">

		<div class="afq-specs__header">
			<span class="dashicons dashicons-car"></span>
			<div>
				<strong>مشخصات فنی</strong>
				<p>اطلاعات فنی خودرو را در تب‌های زیر تکمیل کنید. فیلدهای خالی در سایت نمایش داده نمی‌شوند.</p>
			</div>
		</div>

		<div class="afq-specs__tabs" role="tablist">
			<?php $i = 0; ?>
			<?php foreach ( $sections as $section_id => $section ) : ?>
				<button type="button"
					class="afq-specs__tab<?php echo ( 0 === $i ) ? ' is-active' : ''; ?>"
					data-afq-tab="<?php echo esc_attr( $section_id ); ?>">
					<span class="dashicons <?php echo esc_attr( $section['icon'] ); ?>"></span>
					<?php echo esc_html( $section['label'] ); ?>
				</button>
				<?php $i++; ?>
			<?php endforeach; ?>
		</div>

		<?php $i = 0; ?>
		<?php foreach ( $sections as $section_id => $section ) : ?>
			<div class="afq-specs__panel<?php echo ( 0 === $i ) ? ' is-active' : ''; ?>"
				data-afq-panel="<?php echo esc_attr( $section_id ); ?>">

				<div class="afq-specs__grid">
					<?php foreach ( $section['fields'] as $key => $field ) : ?>
						<?php
						$value    = get_post_meta( $post->ID, $key, true );
						$field_id = 'afq-field-' . sanitize_html_class( $key );
						$is_wide  = ( 'textarea' === $field['type'] );
						?>
						<div class="afq-specs__field<?php echo $is_wide ? ' afq-specs__field--wide' : ''; ?>">
							<label for="<?php echo esc_attr( $field_id ); ?>">
								<?php echo esc_html( $field['label'] ); ?>
							</label>

							<?php if ( 'textarea' === $field['type'] ) : ?>
								<textarea
									id="<?php echo esc_attr( $field_id ); ?>"
									name="<?php echo esc_attr( $key ); ?>"
									rows="6"
									placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"><?php echo esc_textarea( $value ); ?></textarea>

							<?php elseif ( 'select' === $field['type'] ) : ?>
								<select
									id="<?php echo esc_attr( $field_id ); ?>"
									name="<?php echo esc_attr( $key ); ?>">
									<?php foreach ( $field['options'] as $opt_value => $opt_label ) : ?>
										<option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $value, $opt_value ); ?>>
											<?php echo esc_html( $opt_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>

							<?php else : ?>
								<input
									type="text"
									id="<?php echo esc_attr( $field_id ); ?>"
									name="<?php echo esc_attr( $key ); ?>"
									value="<?php echo esc_attr( $value ); ?>"
									placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>" />
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

			</div>
			<?php $i++; ?>
		<?php endforeach; ?>

	</div>
	<?php
}

/**
 * Save spec meta values.
 *
 * @param int $post_id Post ID.
 */
function afq_car_save_specs_meta( $post_id ) {

	if ( ! isset( $_POST['afq_car_specs_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['afq_car_specs_nonce'] ), 'afq_car_specs_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( afq_car_get_all_spec_fields() as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$value = ( 'textarea' === $field['type'] )
			? sanitize_textarea_field( $raw )
			: sanitize_text_field( $raw );

		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_afq_car', 'afq_car_save_specs_meta' );
