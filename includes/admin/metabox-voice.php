<?php
/**
 * Customer testimonial meta box ("اطلاعات نظر مشتری").
 *
 * Meta keys:
 *   _afq_voice_image  (attachment ID — customer photo)
 *   _afq_voice_desc   (textarea — customer description/quote)
 *   _afq_voice_audio  (URL — customer voice)
 *   _afq_voice_video  (URL — customer video)
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register voice meta box.
 */
function afq_voice_add_meta_box() {
	add_meta_box(
		'afq_voice_details',
		'اطلاعات نظر مشتری',
		'afq_voice_meta_box_callback',
		'afq_voice',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'afq_voice_add_meta_box' );

/**
 * Render voice meta box.
 *
 * @param WP_Post $post Current post object.
 */
function afq_voice_meta_box_callback( $post ) {

	wp_nonce_field( 'afq_voice_save_meta', 'afq_voice_meta_nonce' );

	$image_id  = absint( get_post_meta( $post->ID, '_afq_voice_image', true ) );
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
	$desc      = get_post_meta( $post->ID, '_afq_voice_desc', true );
	$audio_url = get_post_meta( $post->ID, '_afq_voice_audio', true );
	$video_url = get_post_meta( $post->ID, '_afq_voice_video', true );
	?>
	<div class="afq-voice-admin">

		<p class="afq-voice-admin__hint">نام مشتری همان «عنوان» بالای صفحه است. همه فیلدهای زیر اختیاری‌اند؛ فیلد خالی در سایت نمایش داده نمی‌شود.</p>

		<div class="afq-voice-admin__grid">

			<div class="afq-voice-admin__card afq-voice-image-field">
				<span class="afq-voice-admin__label">تصویر مشتری</span>

				<div class="afq-voice-admin__preview<?php echo $image_url ? ' has-image' : ''; ?>">
					<?php if ( $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>" alt="" />
					<?php endif; ?>
					<span class="dashicons dashicons-admin-users"></span>
				</div>

				<input type="hidden"
					class="afq-voice-image-id"
					name="_afq_voice_image"
					value="<?php echo esc_attr( $image_id ? $image_id : '' ); ?>" />

				<div class="afq-voice-admin__actions">
					<button type="button" class="button afq-voice-btn afq-voice-btn--gold afq-voice-image-upload">انتخاب تصویر</button>
					<button type="button" class="button afq-voice-btn afq-voice-btn--ghost afq-voice-image-remove" <?php echo $image_id ? '' : 'style="display:none;"'; ?>>حذف</button>
				</div>
			</div>

			<div class="afq-voice-admin__card afq-voice-admin__card--wide">
				<span class="afq-voice-admin__label">توضیحات مشتری</span>
				<textarea
					class="afq-voice-admin__textarea"
					name="_afq_voice_desc"
					rows="6"
					placeholder="نظر یا توضیحات مشتری..."><?php echo esc_textarea( $desc ); ?></textarea>
			</div>

			<div class="afq-voice-admin__card afq-voice-media-field" data-media-type="audio">
				<span class="afq-voice-admin__label">فایل صوتی نظر (ویس)</span>
				<input type="url"
					class="afq-voice-admin__input afq-voice-media-url"
					name="_afq_voice_audio"
					value="<?php echo esc_url( $audio_url ); ?>"
					placeholder="لینک فایل صوتی یا انتخاب از کتابخانه" />
				<div class="afq-voice-admin__actions">
					<button type="button" class="button afq-voice-btn afq-voice-btn--gold afq-voice-media-select">انتخاب از کتابخانه</button>
					<button type="button" class="button afq-voice-btn afq-voice-btn--ghost afq-voice-media-clear" <?php echo $audio_url ? '' : 'style="display:none;"'; ?>>حذف</button>
				</div>
			</div>

			<div class="afq-voice-admin__card afq-voice-media-field" data-media-type="video">
				<span class="afq-voice-admin__label">ویدیوی مشتری</span>
				<input type="url"
					class="afq-voice-admin__input afq-voice-media-url"
					name="_afq_voice_video"
					value="<?php echo esc_url( $video_url ); ?>"
					placeholder="لینک ویدیو (فایل یا embed آپارات/یوتیوب) یا انتخاب از کتابخانه" />
				<div class="afq-voice-admin__actions">
					<button type="button" class="button afq-voice-btn afq-voice-btn--gold afq-voice-media-select">انتخاب از کتابخانه</button>
					<button type="button" class="button afq-voice-btn afq-voice-btn--ghost afq-voice-media-clear" <?php echo $video_url ? '' : 'style="display:none;"'; ?>>حذف</button>
				</div>
			</div>

		</div>

	</div>
	<?php
}

/**
 * Save voice meta.
 *
 * @param int $post_id Post ID.
 */
function afq_voice_save_meta( $post_id ) {

	if ( ! isset( $_POST['afq_voice_meta_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afq_voice_meta_nonce'] ) ), 'afq_voice_save_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'afq_voice' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['_afq_voice_image'] ) ) {
		$image_id = absint( $_POST['_afq_voice_image'] );
		if ( $image_id ) {
			update_post_meta( $post_id, '_afq_voice_image', $image_id );
		} else {
			delete_post_meta( $post_id, '_afq_voice_image' );
		}
	}

	if ( isset( $_POST['_afq_voice_desc'] ) ) {
		$desc = sanitize_textarea_field( wp_unslash( $_POST['_afq_voice_desc'] ) );
		if ( '' !== $desc ) {
			update_post_meta( $post_id, '_afq_voice_desc', $desc );
		} else {
			delete_post_meta( $post_id, '_afq_voice_desc' );
		}
	}

	$url_keys = array( '_afq_voice_audio', '_afq_voice_video' );

	foreach ( $url_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$url = esc_url_raw( wp_unslash( $_POST[ $key ] ) );
			if ( '' !== $url ) {
				update_post_meta( $post_id, $key, $url );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}
	}
}
add_action( 'save_post', 'afq_voice_save_meta' );
