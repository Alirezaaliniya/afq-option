<?php
/**
 * Car details meta box: prices, catalog file and intro video.
 *
 * Meta keys:
 *   _afq_car_price_regular  (text)
 *   _afq_car_price_sale     (text)
 *   _afq_car_catalog        (attachment ID — any file type, e.g. PDF)
 *   _afq_car_video          (URL — uploaded video or external link)
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register details meta box.
 */
function afq_car_add_details_meta_box() {
	add_meta_box(
		'afq_car_details',
		'قیمت و اطلاعات تکمیلی',
		'afq_car_details_meta_box_callback',
		'afq_car',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'afq_car_add_details_meta_box' );

/**
 * Render details meta box.
 *
 * @param WP_Post $post Current post object.
 */
function afq_car_details_meta_box_callback( $post ) {

	wp_nonce_field( 'afq_car_details_save', 'afq_car_details_nonce' );

	$price_regular = get_post_meta( $post->ID, '_afq_car_price_regular', true );
	$price_sale    = get_post_meta( $post->ID, '_afq_car_price_sale', true );
	$catalog_id    = absint( get_post_meta( $post->ID, '_afq_car_catalog', true ) );
	$video_url     = get_post_meta( $post->ID, '_afq_car_video', true );

	$catalog_name = '';
	if ( $catalog_id ) {
		$catalog_path = get_attached_file( $catalog_id );
		$catalog_name = $catalog_path ? wp_basename( $catalog_path ) : get_the_title( $catalog_id );
	}
	?>
	<div class="afq-media afq-details">

		<div class="afq-details__grid">

			<div class="afq-media-card afq-details__field">
				<span class="afq-media-card__label">قیمت عادی</span>
				<input type="text"
					class="afq-details__input"
					name="_afq_car_price_regular"
					value="<?php echo esc_attr( $price_regular ); ?>"
					placeholder="مثال: ۲٬۵۰۰٬۰۰۰٬۰۰۰ تومان" />
			</div>

			<div class="afq-media-card afq-details__field">
				<span class="afq-media-card__label">قیمت فروش ویژه</span>
				<input type="text"
					class="afq-details__input"
					name="_afq_car_price_sale"
					value="<?php echo esc_attr( $price_sale ); ?>"
					placeholder="مثال: ۲٬۳۵۰٬۰۰۰٬۰۰۰ تومان" />
			</div>

			<div class="afq-media-card afq-details__field afq-file-field">
				<span class="afq-media-card__label">کاتالوگ خودرو</span>

				<div class="afq-file-box<?php echo $catalog_id ? ' has-file' : ''; ?>">
					<span class="dashicons dashicons-media-document"></span>
					<span class="afq-file-name"><?php echo $catalog_name ? esc_html( $catalog_name ) : 'فایلی انتخاب نشده'; ?></span>
				</div>

				<input type="hidden"
					class="afq-file-id"
					name="_afq_car_catalog"
					value="<?php echo esc_attr( $catalog_id ? $catalog_id : '' ); ?>" />

				<div class="afq-media-card__actions">
					<button type="button" class="button afq-btn afq-btn--gold afq-file-upload">انتخاب فایل</button>
					<button type="button" class="button afq-btn afq-btn--ghost afq-file-remove" <?php echo $catalog_id ? '' : 'style="display:none;"'; ?>>حذف</button>
				</div>
			</div>

			<div class="afq-media-card afq-details__field afq-video-field">
				<span class="afq-media-card__label">ویدیوی معرفی خودرو</span>

				<input type="url"
					class="afq-details__input afq-video-url"
					name="_afq_car_video"
					value="<?php echo esc_url( $video_url ); ?>"
					placeholder="لینک ویدیو (آپارات، یوتیوب و...) یا انتخاب از کتابخانه" />

				<div class="afq-media-card__actions">
					<button type="button" class="button afq-btn afq-btn--gold afq-video-select">انتخاب از کتابخانه</button>
					<button type="button" class="button afq-btn afq-btn--ghost afq-video-clear" <?php echo $video_url ? '' : 'style="display:none;"'; ?>>حذف</button>
				</div>
			</div>

		</div>

	</div>
	<?php
}

/**
 * Save details meta.
 *
 * @param int $post_id Post ID.
 */
function afq_car_save_details_meta( $post_id ) {

	if ( ! isset( $_POST['afq_car_details_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afq_car_details_nonce'] ) ), 'afq_car_details_save' ) ) {
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

	$text_keys = array( '_afq_car_price_regular', '_afq_car_price_sale' );

	foreach ( $text_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
			if ( '' !== $value ) {
				update_post_meta( $post_id, $key, $value );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}
	}

	if ( isset( $_POST['_afq_car_catalog'] ) ) {
		$catalog_id = absint( $_POST['_afq_car_catalog'] );
		if ( $catalog_id ) {
			update_post_meta( $post_id, '_afq_car_catalog', $catalog_id );
		} else {
			delete_post_meta( $post_id, '_afq_car_catalog' );
		}
	}

	if ( isset( $_POST['_afq_car_video'] ) ) {
		$video_url = esc_url_raw( wp_unslash( $_POST['_afq_car_video'] ) );
		if ( '' !== $video_url ) {
			update_post_meta( $post_id, '_afq_car_video', $video_url );
		} else {
			delete_post_meta( $post_id, '_afq_car_video' );
		}
	}
}
add_action( 'save_post', 'afq_car_save_details_meta' );
