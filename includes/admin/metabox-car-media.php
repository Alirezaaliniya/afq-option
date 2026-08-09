<?php
/**
 * Car media meta boxes: images, gallery and short description.
 *
 * Meta keys:
 *   _afq_car_image_normal, _afq_car_image_hover, _afq_car_image_spot,
 *   _afq_car_image_details, _afq_car_gallery, _afq_car_short_desc
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register meta boxes for car post type.
 */
function afq_car_add_meta_boxes() {

	add_meta_box(
		'afq_car_images',
		'تصاویر ماشین',
		'afq_car_images_meta_box_callback',
		'afq_car',
		'side',
		'default'
	);

	add_meta_box(
		'afq_car_gallery',
		'گالری تصاویر ماشین',
		'afq_car_gallery_meta_box_callback',
		'afq_car',
		'normal',
		'high'
	);

	add_meta_box(
		'afq_car_short_desc',
		'توضیحات کوتاه',
		'afq_car_short_desc_meta_box_callback',
		'afq_car',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'afq_car_add_meta_boxes' );

/**
 * Single-image field renderer.
 *
 * @param string $meta_key      Meta key / input name.
 * @param string $label         Field label.
 * @param int    $attachment_id Attachment ID.
 */
function afq_car_render_image_field( $meta_key, $label, $attachment_id ) {

	$attachment_id = absint( $attachment_id );
	$image_url     = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
	?>
	<div class="afq-image-field afq-media-card">
		<span class="afq-media-card__label"><?php echo esc_html( $label ); ?></span>

		<div class="afq-image-preview afq-media-card__preview<?php echo $image_url ? ' has-image' : ''; ?>">
			<?php if ( $image_url ) : ?>
				<img src="<?php echo esc_url( $image_url ); ?>" alt="" />
			<?php endif; ?>
			<span class="afq-media-card__placeholder dashicons dashicons-format-image"></span>
		</div>

		<input type="hidden"
			class="afq-image-id"
			name="<?php echo esc_attr( $meta_key ); ?>"
			value="<?php echo esc_attr( $attachment_id ? $attachment_id : '' ); ?>" />

		<div class="afq-media-card__actions">
			<button type="button" class="button afq-btn afq-btn--gold afq-image-upload">انتخاب تصویر</button>
			<button type="button" class="button afq-btn afq-btn--ghost afq-image-remove" <?php echo $attachment_id ? '' : 'style="display:none;"'; ?>>حذف</button>
		</div>
	</div>
	<?php
}

/**
 * Render image meta box (normal, hover, spot, details).
 *
 * @param WP_Post $post Current post object.
 */
function afq_car_images_meta_box_callback( $post ) {

	wp_nonce_field( 'afq_car_save_meta', 'afq_car_meta_nonce' );

	$fields = array(
		'_afq_car_image_normal'  => 'تصویر عادی',
		'_afq_car_image_hover'   => 'تصویر هاور',
		'_afq_car_image_spot'    => 'تصویر حالت اسپات',
		'_afq_car_image_details' => 'تصویر جزئیات ماشین',
	);

	echo '<div class="afq-media">';

	foreach ( $fields as $meta_key => $label ) {
		afq_car_render_image_field( $meta_key, $label, get_post_meta( $post->ID, $meta_key, true ) );
	}

	echo '</div>';
}

/**
 * Render gallery meta box.
 *
 * @param WP_Post $post Current post object.
 */
function afq_car_gallery_meta_box_callback( $post ) {

	wp_nonce_field( 'afq_car_save_meta', 'afq_car_meta_nonce' );

	$ids_raw = (string) get_post_meta( $post->ID, '_afq_car_gallery', true );
	$ids     = array_filter( array_map( 'absint', explode( ',', $ids_raw ) ) );
	?>
	<div class="afq-media afq-gallery">

		<div class="afq-gallery__head">
			<span class="dashicons dashicons-images-alt2"></span>
			<div>
				<strong>گالری تصاویر</strong>
				<p>برای مرتب‌سازی، تصاویر را بکشید و جابه‌جا کنید.</p>
			</div>
			<button type="button" class="button afq-btn afq-btn--gold afq-gallery-add">افزودن تصویر</button>
		</div>

		<input type="hidden"
			class="afq-gallery-ids"
			name="_afq_car_gallery"
			value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" />

		<ul class="afq-gallery__grid">
			<?php foreach ( $ids as $attachment_id ) : ?>
				<?php $thumb = wp_get_attachment_image_url( $attachment_id, 'thumbnail' ); ?>
				<?php if ( $thumb ) : ?>
					<li class="afq-gallery__item" data-id="<?php echo esc_attr( $attachment_id ); ?>">
						<img src="<?php echo esc_url( $thumb ); ?>" alt="" />
						<button type="button" class="afq-gallery__remove" aria-label="حذف">&times;</button>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>

		<p class="afq-gallery__empty" <?php echo $ids ? 'style="display:none;"' : ''; ?>>
			هنوز تصویری به گالری اضافه نشده است.
		</p>

	</div>
	<?php
}

/**
 * Render short description meta box.
 *
 * @param WP_Post $post Current post object.
 */
function afq_car_short_desc_meta_box_callback( $post ) {

	wp_nonce_field( 'afq_car_save_meta', 'afq_car_meta_nonce' );

	$short_desc = get_post_meta( $post->ID, '_afq_car_short_desc', true );

	echo '<div class="afq-media afq-editor-wrap">';

	wp_editor(
		$short_desc,
		'afq_car_short_desc_editor',
		array(
			'textarea_name' => '_afq_car_short_desc',
			'textarea_rows' => 6,
			'media_buttons' => false,
			'teeny'         => true,
			'quicktags'     => true,
		)
	);

	echo '</div>';
}

/**
 * Save car post meta.
 *
 * @param int $post_id Post ID.
 */
function afq_car_save_post_meta( $post_id ) {

	if ( ! isset( $_POST['afq_car_meta_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afq_car_meta_nonce'] ) ), 'afq_car_save_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'afq_car' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$image_keys = array(
		'_afq_car_image_normal',
		'_afq_car_image_hover',
		'_afq_car_image_spot',
		'_afq_car_image_details',
	);

	foreach ( $image_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$value = absint( $_POST[ $key ] );
			if ( $value ) {
				update_post_meta( $post_id, $key, $value );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}
	}

	if ( isset( $_POST['_afq_car_gallery'] ) ) {
		$raw = sanitize_text_field( wp_unslash( $_POST['_afq_car_gallery'] ) );
		$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );

		if ( $ids ) {
			update_post_meta( $post_id, '_afq_car_gallery', implode( ',', $ids ) );
		} else {
			delete_post_meta( $post_id, '_afq_car_gallery' );
		}
	}

	if ( isset( $_POST['_afq_car_short_desc'] ) ) {
		$short_desc = wp_kses_post( wp_unslash( $_POST['_afq_car_short_desc'] ) );
		if ( '' !== trim( $short_desc ) ) {
			update_post_meta( $post_id, '_afq_car_short_desc', $short_desc );
		} else {
			delete_post_meta( $post_id, '_afq_car_short_desc' );
		}
	}
}
add_action( 'save_post', 'afq_car_save_post_meta' );
