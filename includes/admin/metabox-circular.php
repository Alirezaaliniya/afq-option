<?php
/**
 * Sales circular meta box: sold-out toggle + cars repeater.
 *
 * Meta keys:
 *   afq_circular_sold_out  ('yes' when sold out, deleted when off)
 *   _afq_circular_cars     (comma-separated afq_car post IDs, keeps order)
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register circular meta box.
 */
function afq_circular_add_meta_box() {
	add_meta_box(
		'afq_circular_details',
		'تنظیمات بخش‌نامه',
		'afq_circular_meta_box_callback',
		'afq_circular',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'afq_circular_add_meta_box' );

/**
 * Render circular meta box.
 *
 * @param WP_Post $post Current post object.
 */
function afq_circular_meta_box_callback( $post ) {

	wp_nonce_field( 'afq_circular_save_meta', 'afq_circular_meta_nonce' );

	$sold_out     = get_post_meta( $post->ID, 'afq_circular_sold_out', true );
	$selected_ids = afq_circular_get_car_ids( $post->ID );

	$cars = get_posts(
		array(
			'post_type'              => 'afq_car',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	/* Options markup, reused for every repeater row. */
	$options_html = '<option value="">انتخاب ماشین...</option>';
	foreach ( $cars as $car ) {
		$options_html .= '<option value="' . esc_attr( $car->ID ) . '">' . esc_html( $car->post_title ) . '</option>';
	}
	?>
	<div class="afq-circular-admin">

		<div class="afq-circular-admin__card">
			<div class="afq-circular-admin__toggle-row">
				<div>
					<strong>اتمام فروش</strong>
					<p>با فعال بودن، متای <code>afq_circular_sold_out</code> مقدار <code>yes</code> می‌گیرد (برای کاندیشن المنتور).</p>
				</div>

				<label class="afq-switch">
					<input type="checkbox" name="afq_circular_sold_out" value="yes" <?php checked( $sold_out, 'yes' ); ?> />
					<span class="afq-switch__slider"></span>
				</label>
			</div>
		</div>

		<div class="afq-circular-admin__card">
			<div class="afq-circular-admin__repeater-head">
				<div>
					<strong>ماشین‌های این بخش‌نامه</strong>
					<p>ماشین‌ها به همین ترتیب در سایت نمایش داده می‌شوند.</p>
				</div>
				<button type="button" class="button afq-circular-btn afq-circular-add-row">افزودن ماشین</button>
			</div>

			<div class="afq-circular-rows">
				<?php foreach ( $selected_ids as $car_id ) : ?>
					<div class="afq-circular-row">
						<span class="afq-circular-row__handle dashicons dashicons-menu"></span>
						<select name="afq_circular_cars[]">
							<option value="">انتخاب ماشین...</option>
							<?php foreach ( $cars as $car ) : ?>
								<option value="<?php echo esc_attr( $car->ID ); ?>" <?php selected( $car_id, $car->ID ); ?>>
									<?php echo esc_html( $car->post_title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="button afq-circular-btn afq-circular-btn--ghost afq-circular-remove-row">حذف</button>
					</div>
				<?php endforeach; ?>
			</div>

			<p class="afq-circular-empty" <?php echo $selected_ids ? 'style="display:none;"' : ''; ?>>هنوز ماشینی اضافه نشده است.</p>
		</div>

		<script type="text/template" id="afq-circular-row-template">
			<div class="afq-circular-row">
				<span class="afq-circular-row__handle dashicons dashicons-menu"></span>
				<select name="afq_circular_cars[]"><?php echo $options_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></select>
				<button type="button" class="button afq-circular-btn afq-circular-btn--ghost afq-circular-remove-row">حذف</button>
			</div>
		</script>

	</div>
	<?php
}

/**
 * Save circular meta.
 *
 * @param int $post_id Post ID.
 */
function afq_circular_save_meta( $post_id ) {

	if ( ! isset( $_POST['afq_circular_meta_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['afq_circular_meta_nonce'] ), 'afq_circular_save_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'afq_circular' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/* Sold-out toggle: 'yes' or deleted. */
	if ( isset( $_POST['afq_circular_sold_out'] ) && 'yes' === $_POST['afq_circular_sold_out'] ) {
		update_post_meta( $post_id, 'afq_circular_sold_out', 'yes' );
	} else {
		delete_post_meta( $post_id, 'afq_circular_sold_out' );
	}

	/* Cars repeater. */
	$ids = array();

	if ( isset( $_POST['afq_circular_cars'] ) && is_array( $_POST['afq_circular_cars'] ) ) {
		foreach ( wp_unslash( $_POST['afq_circular_cars'] ) as $raw_id ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$car_id = absint( $raw_id );

			if ( $car_id && 'afq_car' === get_post_type( $car_id ) && ! in_array( $car_id, $ids, true ) ) {
				$ids[] = $car_id;
			}
		}
	}

	if ( $ids ) {
		update_post_meta( $post_id, '_afq_circular_cars', implode( ',', $ids ) );
	} else {
		delete_post_meta( $post_id, '_afq_circular_cars' );
	}
}
add_action( 'save_post_afq_circular', 'afq_circular_save_meta' );
