<?php
/**
 * Taxonomy term fields.
 *
 *   afq_car_cat       → category image (afq_car_cat_image)
 *   afq_rep_province  → map spot position (afq_rep_spot_left / afq_rep_spot_top)
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Car Category — Image
 * ---------------------------------------------------------------------- */

/**
 * Add image field on "add new term" screen.
 */
function afq_car_cat_add_image_field() {

	wp_nonce_field( 'afq_car_cat_save_meta', 'afq_car_cat_meta_nonce' );
	?>
	<div class="form-field afq-term-image-field afq-media">
		<label>تصویر دسته‌بندی</label>

		<div class="afq-media-card">
			<div class="afq-image-preview afq-media-card__preview">
				<span class="afq-media-card__placeholder dashicons dashicons-format-image"></span>
			</div>

			<input type="hidden" class="afq-image-id" name="afq_car_cat_image" value="" />

			<div class="afq-media-card__actions">
				<button type="button" class="button afq-btn afq-btn--gold afq-image-upload">انتخاب تصویر</button>
				<button type="button" class="button afq-btn afq-btn--ghost afq-image-remove" style="display:none;">حذف</button>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'afq_car_cat_add_form_fields', 'afq_car_cat_add_image_field' );

/**
 * Add image field on "edit term" screen.
 *
 * @param WP_Term $term Current term object.
 */
function afq_car_cat_edit_image_field( $term ) {

	$attachment_id = absint( get_term_meta( $term->term_id, 'afq_car_cat_image', true ) );
	$image_url     = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';

	wp_nonce_field( 'afq_car_cat_save_meta', 'afq_car_cat_meta_nonce' );
	?>
	<tr class="form-field afq-term-image-field">
		<th scope="row"><label>تصویر دسته‌بندی</label></th>
		<td class="afq-media">
			<div class="afq-media-card">
				<div class="afq-image-preview afq-media-card__preview<?php echo $image_url ? ' has-image' : ''; ?>">
					<?php if ( $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>" alt="" />
					<?php endif; ?>
					<span class="afq-media-card__placeholder dashicons dashicons-format-image"></span>
				</div>

				<input type="hidden"
					class="afq-image-id"
					name="afq_car_cat_image"
					value="<?php echo esc_attr( $attachment_id ? $attachment_id : '' ); ?>" />

				<div class="afq-media-card__actions">
					<button type="button" class="button afq-btn afq-btn--gold afq-image-upload">انتخاب تصویر</button>
					<button type="button" class="button afq-btn afq-btn--ghost afq-image-remove" <?php echo $attachment_id ? '' : 'style="display:none;"'; ?>>حذف</button>
				</div>
			</div>
		</td>
	</tr>
	<?php
}
add_action( 'afq_car_cat_edit_form_fields', 'afq_car_cat_edit_image_field' );

/**
 * Save term image meta.
 *
 * @param int $term_id Term ID.
 */
function afq_car_cat_save_image_field( $term_id ) {

	if ( ! isset( $_POST['afq_car_cat_meta_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afq_car_cat_meta_nonce'] ) ), 'afq_car_cat_save_meta' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	if ( isset( $_POST['afq_car_cat_image'] ) ) {
		$attachment_id = absint( $_POST['afq_car_cat_image'] );
		if ( $attachment_id ) {
			update_term_meta( $term_id, 'afq_car_cat_image', $attachment_id );
		} else {
			delete_term_meta( $term_id, 'afq_car_cat_image' );
		}
	}
}
add_action( 'created_afq_car_cat', 'afq_car_cat_save_image_field' );
add_action( 'edited_afq_car_cat', 'afq_car_cat_save_image_field' );

/* -------------------------------------------------------------------------
 * Representative Province — Map Spot Position
 * ---------------------------------------------------------------------- */

/**
 * Spot position fields on "add term" screen.
 */
function afq_rep_province_add_spot_fields() {

	wp_nonce_field( 'afq_rep_province_save', 'afq_rep_province_nonce' );
	?>
	<div class="form-field">
		<label>موقعیت اسپات روی نقشه (درصد)</label>
		<div style="display:flex;gap:10px;">
			<input type="number" name="afq_rep_spot_left" min="0" max="100" step="0.1" placeholder="Left %" style="direction:ltr;" />
			<input type="number" name="afq_rep_spot_top" min="0" max="100" step="0.1" placeholder="Top %" style="direction:ltr;" />
		</div>
		<p>فاصله از چپ و بالای تصویر نقشه به درصد. استانی که موقعیت نداشته باشد روی نقشه نمایش داده نمی‌شود.</p>
	</div>
	<?php
}
add_action( 'afq_rep_province_add_form_fields', 'afq_rep_province_add_spot_fields' );

/**
 * Spot position fields on "edit term" screen.
 *
 * @param WP_Term $term Current term object.
 */
function afq_rep_province_edit_spot_fields( $term ) {

	$left = get_term_meta( $term->term_id, 'afq_rep_spot_left', true );
	$top  = get_term_meta( $term->term_id, 'afq_rep_spot_top', true );

	wp_nonce_field( 'afq_rep_province_save', 'afq_rep_province_nonce' );
	?>
	<tr class="form-field">
		<th scope="row"><label>موقعیت اسپات روی نقشه (درصد)</label></th>
		<td>
			<div style="display:flex;gap:10px;max-width:340px;">
				<input type="number" name="afq_rep_spot_left" min="0" max="100" step="0.1" value="<?php echo esc_attr( $left ); ?>" placeholder="Left %" style="direction:ltr;" />
				<input type="number" name="afq_rep_spot_top" min="0" max="100" step="0.1" value="<?php echo esc_attr( $top ); ?>" placeholder="Top %" style="direction:ltr;" />
			</div>
			<p class="description">فاصله از چپ و بالای تصویر نقشه به درصد. استانی که موقعیت نداشته باشد روی نقشه نمایش داده نمی‌شود.</p>
		</td>
	</tr>
	<?php
}
add_action( 'afq_rep_province_edit_form_fields', 'afq_rep_province_edit_spot_fields' );

/**
 * Save province spot position.
 *
 * @param int $term_id Term ID.
 */
function afq_rep_province_save_spot_fields( $term_id ) {

	if ( ! isset( $_POST['afq_rep_province_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['afq_rep_province_nonce'] ), 'afq_rep_province_save' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	$keys = array( 'afq_rep_spot_left', 'afq_rep_spot_top' );

	foreach ( $keys as $key ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$raw = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );

		if ( '' === $raw || ! is_numeric( $raw ) ) {
			delete_term_meta( $term_id, $key );
			continue;
		}

		$value = min( 100, max( 0, (float) $raw ) );
		update_term_meta( $term_id, $key, (string) $value );
	}
}
add_action( 'created_afq_rep_province', 'afq_rep_province_save_spot_fields' );
add_action( 'edited_afq_rep_province', 'afq_rep_province_save_spot_fields' );
