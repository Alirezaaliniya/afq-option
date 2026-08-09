
/**
 * AFQ Voice — Customer Testimonials ("صدای مشتری")
 * Add this code to your theme's functions.php.
 *
 * Post type: afq_voice (no archive, no public single page — content is
 * rendered only via the [afq_voice_grid] shortcode).
 *
 * Meta keys:
 *   _afq_voice_image  (attachment ID — customer photo)
 *   _afq_voice_desc   (textarea — customer description/quote)
 *   _afq_voice_audio  (URL — customer voice, from media library or external)
 *   _afq_voice_video  (URL — customer video, media library file or embed link)
 *
 * Shortcode:
 *   [afq_voice_grid]                → all items, 3 columns on desktop
 *   [afq_voice_grid count="6"]      → limit items
 *   [afq_voice_grid columns="4"]    → desktop columns (1-4)
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Post Type
 * ---------------------------------------------------------------------- */

/**
 * Register customer voice post type.
 */
function afq_register_voice_post_type() {

	$labels = array(
		'name'               => 'صدای مشتریان',
		'singular_name'      => 'صدای مشتری',
		'menu_name'          => 'صدای مشتریان',
		'add_new'            => 'افزودن مشتری',
		'add_new_item'       => 'افزودن صدای مشتری جدید',
		'edit_item'          => 'ویرایش صدای مشتری',
		'new_item'           => 'صدای مشتری جدید',
		'view_item'          => 'مشاهده',
		'search_items'       => 'جستجو',
		'not_found'          => 'موردی یافت نشد',
		'not_found_in_trash' => 'موردی در زباله‌دان یافت نشد',
		'all_items'          => 'همه موارد',
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-testimonial',
		'menu_position'       => 21,
		'supports'            => array( 'title', 'page-attributes' ),
		'capability_type'     => 'post',
		'hierarchical'        => false,
	);

	register_post_type( 'afq_voice', $args );
}
add_action( 'init', 'afq_register_voice_post_type' );

/* -------------------------------------------------------------------------
 * Meta Box
 * ---------------------------------------------------------------------- */

/**
 * Register voice meta box.
 */
function afq_voice_add_meta_box() {
	add_meta_box(
		'afq_voice_details',
		'اطلاعات مشتری',
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
				<span class="afq-voice-admin__label">صدای مشتری (ویس)</span>
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

/* -------------------------------------------------------------------------
 * Admin Assets
 * ---------------------------------------------------------------------- */

/**
 * Enqueue admin assets on afq_voice edit screens.
 *
 * @param string $hook Current admin page hook.
 */
function afq_voice_admin_assets( $hook ) {

	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'afq_voice' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();

	wp_register_style( 'afq-voice-admin', false, array(), '1.0.0' );
	wp_enqueue_style( 'afq-voice-admin' );
	wp_add_inline_style( 'afq-voice-admin', afq_voice_admin_inline_css() );

	wp_register_script( 'afq-voice-admin', false, array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'afq-voice-admin' );
	wp_add_inline_script( 'afq-voice-admin', afq_voice_admin_inline_js() );
}
add_action( 'admin_enqueue_scripts', 'afq_voice_admin_assets' );

/**
 * Admin inline CSS.
 *
 * @return string
 */
function afq_voice_admin_inline_css() {
	return '
	#afq_voice_details.postbox {
		border: none;
		border-radius: 14px;
		overflow: hidden;
		box-shadow: 0 10px 30px rgba(15, 20, 30, 0.08);
	}
	#afq_voice_details .postbox-header {
		background: linear-gradient(135deg, #14181f 0%, #232a36 60%, #2c3442 100%);
		border-bottom: none;
	}
	#afq_voice_details .postbox-header .hndle {
		color: #e8cf9a;
		font-size: 13px;
	}
	#afq_voice_details .postbox-header .handle-actions button {
		color: rgba(255, 255, 255, 0.7);
	}
	#afq_voice_details .inside {
		margin: 0;
		padding: 16px;
		background: #fbfbfc;
	}

	.afq-voice-admin__hint {
		margin: 0 0 14px;
		font-size: 12px;
		color: #6b7280;
	}
	.afq-voice-admin__grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
		gap: 14px;
	}
	.afq-voice-admin__card {
		background: #fff;
		border: 1px solid #eef0f3;
		border-radius: 12px;
		padding: 12px;
		box-shadow: 0 2px 8px rgba(15, 20, 30, 0.04);
	}
	.afq-voice-admin__card--wide {
		grid-column: 1 / -1;
	}
	.afq-voice-admin__label {
		display: block;
		font-size: 12px;
		font-weight: 600;
		color: #374151;
		margin-bottom: 8px;
	}
	.afq-voice-admin__preview {
		position: relative;
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: 110px;
		border: 1px dashed #d9dde3;
		border-radius: 10px;
		background: #f6f7f9;
		margin-bottom: 10px;
		overflow: hidden;
	}
	.afq-voice-admin__preview img {
		max-width: 100%;
		height: auto;
		display: block;
		border-radius: 10px;
	}
	.afq-voice-admin__preview .dashicons {
		color: #c3c9d1;
		font-size: 30px;
		width: 30px;
		height: 30px;
	}
	.afq-voice-admin__preview.has-image {
		border-style: solid;
		background: #fff;
	}
	.afq-voice-admin__preview.has-image .dashicons,
	.afq-voice-admin__preview img + .dashicons {
		display: none;
	}
	.afq-voice-admin__actions {
		display: flex;
		gap: 8px;
	}
	.afq-voice-admin__input,
	.afq-voice-admin__textarea {
		width: 100%;
		border: 1px solid #e4e7ec;
		border-radius: 10px;
		background: #fff;
		padding: 9px 12px;
		font-size: 13px;
		color: #1f2937;
		box-shadow: 0 1px 2px rgba(15, 20, 30, 0.04);
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}
	.afq-voice-admin__input {
		margin-bottom: 10px;
		direction: ltr;
		text-align: left;
	}
	.afq-voice-admin__input:focus,
	.afq-voice-admin__textarea:focus {
		border-color: #c9a45c;
		box-shadow: 0 0 0 3px rgba(201, 164, 92, 0.18);
		outline: none;
	}
	.afq-voice-admin__textarea {
		resize: vertical;
		min-height: 120px;
		line-height: 1.8;
	}

	.afq-voice-admin .afq-voice-btn.button {
		border-radius: 8px;
		border: 1px solid #e4e7ec;
		background: #fff;
		color: #374151;
		font-size: 12px;
		line-height: 2.2;
		padding: 0 14px;
		box-shadow: 0 1px 2px rgba(15, 20, 30, 0.05);
		transition: all 0.15s ease;
	}
	.afq-voice-admin .afq-voice-btn--gold.button {
		background: linear-gradient(135deg, #d8b46a, #b8934a);
		border-color: transparent;
		color: #14181f;
		font-weight: 600;
	}
	.afq-voice-admin .afq-voice-btn--gold.button:hover {
		filter: brightness(1.06);
		color: #14181f;
	}
	.afq-voice-admin .afq-voice-btn--ghost.button:hover {
		border-color: #d9534f;
		color: #d9534f;
	}
	';
}

/**
 * Admin inline JS.
 *
 * @return string
 */
function afq_voice_admin_inline_js() {
	return <<<'JS'
( function( $ ) {
	'use strict';

	/* Customer image */

	$( document ).on( 'click', '.afq-voice-image-upload', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-voice-image-field' );
		var $input   = $wrapper.find( '.afq-voice-image-id' );
		var $preview = $wrapper.find( '.afq-voice-admin__preview' );

		var frame = wp.media( {
			title: 'انتخاب تصویر مشتری',
			button: { text: 'استفاده از این تصویر' },
			library: { type: 'image' },
			multiple: false
		} );

		frame.on( 'select', function() {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			var url = ( attachment.sizes && attachment.sizes.medium ) ? attachment.sizes.medium.url : attachment.url;

			$input.val( attachment.id );
			$preview.addClass( 'has-image' ).find( 'img' ).remove();
			$preview.prepend( '<img src="' + url + '" alt="" />' );
			$wrapper.find( '.afq-voice-image-remove' ).show();
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.afq-voice-image-remove', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-voice-image-field' );

		$wrapper.find( '.afq-voice-image-id' ).val( '' );
		$wrapper.find( '.afq-voice-admin__preview' ).removeClass( 'has-image' ).find( 'img' ).remove();
		$( this ).hide();
	} );

	/* Audio / video URL pickers */

	$( document ).on( 'click', '.afq-voice-media-select', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-voice-media-field' );
		var $input   = $wrapper.find( '.afq-voice-media-url' );
		var type     = $wrapper.data( 'media-type' ) || '';

		var frame = wp.media( {
			title: 'انتخاب فایل',
			button: { text: 'استفاده از این فایل' },
			library: type ? { type: type } : {},
			multiple: false
		} );

		frame.on( 'select', function() {
			var attachment = frame.state().get( 'selection' ).first().toJSON();

			$input.val( attachment.url );
			$wrapper.find( '.afq-voice-media-clear' ).show();
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.afq-voice-media-clear', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-voice-media-field' );

		$wrapper.find( '.afq-voice-media-url' ).val( '' );
		$( this ).hide();
	} );

	$( document ).on( 'input', '.afq-voice-media-url', function() {
		var $wrapper = $( this ).closest( '.afq-voice-media-field' );
		$wrapper.find( '.afq-voice-media-clear' ).toggle( '' !== $.trim( $( this ).val() ) );
	} );

} )( jQuery );
JS;
}

/* -------------------------------------------------------------------------
 * Frontend Shortcode
 * ---------------------------------------------------------------------- */

/**
 * Register empty front asset handles.
 */
function afq_voice_register_front_assets() {
	wp_register_style( 'afq-voice-grid', false, array(), '1.0.0' );
	wp_register_script( 'afq-voice-grid', false, array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'afq_voice_register_front_assets' );

/**
 * Check whether a URL points to a playable video file.
 *
 * @param string $url Video URL.
 * @return bool
 */
function afq_voice_is_video_file( $url ) {
	return (bool) preg_match( '/\.(mp4|webm|ogv|ogg|m4v|mov)(\?.*)?$/i', (string) $url );
}

/**
 * Voice grid shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function afq_voice_grid_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'count'   => -1,
			'columns' => 3,
		),
		$atts,
		'afq_voice_grid'
	);

	$columns = min( 4, max( 1, absint( $atts['columns'] ) ) );

	$query = new WP_Query(
		array(
			'post_type'      => 'afq_voice',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $atts['count'],
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'  => true,
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	wp_enqueue_style( 'afq-voice-grid' );
	wp_add_inline_style( 'afq-voice-grid', afq_voice_front_inline_css() );

	wp_enqueue_script( 'afq-voice-grid' );
	wp_add_inline_script( 'afq-voice-grid', afq_voice_front_inline_js() );

	static $instance = 0;
	$instance++;
	$uid = 'afq-voices-' . $instance;

	ob_start();
	?>
	<div class="afq-voices" id="<?php echo esc_attr( $uid ); ?>" style="--afq-voice-cols:<?php echo esc_attr( $columns ); ?>;">

		<div class="afq-voices__grid">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();

				$post_id   = get_the_ID();
				$name      = get_the_title();
				$image_id  = absint( get_post_meta( $post_id, '_afq_voice_image', true ) );
				$desc      = get_post_meta( $post_id, '_afq_voice_desc', true );
				$audio_url = get_post_meta( $post_id, '_afq_voice_audio', true );
				$video_url = get_post_meta( $post_id, '_afq_voice_video', true );
				?>
				<article class="afq-voice-card">

					<div class="afq-voice-card__head">

						<?php if ( $image_id ) : ?>
							<div class="afq-voice-card__avatar">
								<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
							</div>
						<?php endif; ?>

						<h3 class="afq-voice-card__name"><?php echo esc_html( $name ); ?></h3>

						<?php if ( $video_url ) : ?>
							<button type="button"
								class="afq-voice-card__video-btn"
								data-afq-video="<?php echo esc_url( $video_url ); ?>"
								data-afq-video-name="<?php echo esc_attr( $name ); ?>"
								aria-haspopup="dialog"
								aria-label="مشاهده ویدیوی <?php echo esc_attr( $name ); ?>">
								<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M8 6.5v11l9-5.5-9-5.5Z" fill="currentColor"/></svg>
							</button>
						<?php endif; ?>

					</div>

					<?php if ( '' !== $desc ) : ?>
						<p class="afq-voice-card__desc"><?php echo nl2br( esc_html( $desc ) ); ?></p>
					<?php endif; ?>

					<?php if ( $audio_url ) : ?>
						<div class="afq-voice-player" data-afq-audio="<?php echo esc_url( $audio_url ); ?>">
							<button type="button" class="afq-voice-player__btn" aria-label="پخش صدای <?php echo esc_attr( $name ); ?>">
								<svg class="afq-voice-player__icon-play" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M8 5.5v13l10-6.5-10-6.5Z" fill="currentColor"/></svg>
								<svg class="afq-voice-player__icon-pause" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M8 5h3v14H8V5Zm5 0h3v14h-3V5Z" fill="currentColor"/></svg>
							</button>
							<div class="afq-voice-player__track" role="slider" aria-label="نوار پیشرفت پخش">
								<div class="afq-voice-player__progress"></div>
							</div>
							<span class="afq-voice-player__time">0:00</span>
						</div>
					<?php endif; ?>

				</article>
			<?php endwhile; ?>
		</div>

		<div class="afq-voice-modal" role="dialog" aria-modal="true" aria-label="ویدیوی مشتری" aria-hidden="true">
			<div class="afq-voice-modal__overlay" data-afq-vclose></div>
			<div class="afq-voice-modal__card">
				<div class="afq-voice-modal__header">
					<span class="afq-voice-modal__title"></span>
					<button type="button" class="afq-voice-modal__close" data-afq-vclose aria-label="بستن">&times;</button>
				</div>
				<div class="afq-voice-modal__body"></div>
			</div>
		</div>

	</div>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'afq_voice_grid', 'afq_voice_grid_shortcode' );

/**
 * Frontend inline CSS.
 *
 * @return string
 */
function afq_voice_front_inline_css() {
	return '
	div.afq-voices,
	div.afq-voices * {
		box-sizing: border-box;
	}

	/* Grid */
	div.afq-voices div.afq-voices__grid {
		display: grid;
		grid-template-columns: repeat(var(--afq-voice-cols, 3), minmax(0, 1fr));
		gap: 22px;
	}
	@media (max-width: 1024px) {
		div.afq-voices div.afq-voices__grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}
	@media (max-width: 640px) {
		div.afq-voices div.afq-voices__grid {
			grid-template-columns: 1fr;
		}
	}

	/* Card */
	div.afq-voices article.afq-voice-card {
		display: flex;
		flex-direction: column;
		gap: 14px;
		background: #fff;
		border: 1px solid #eef0f3;
		border-radius: 16px;
		padding: 22px;
		margin: 0;
		box-shadow: 0 6px 20px rgba(15, 20, 30, 0.06);
		transition: box-shadow 0.2s ease, transform 0.2s ease;
	}
	div.afq-voices article.afq-voice-card:hover {
		box-shadow: 0 16px 36px rgba(15, 20, 30, 0.12);
		transform: translateY(-3px);
	}
	div.afq-voices div.afq-voice-card__head {
		display: flex;
		align-items: center;
		gap: 12px;
	}
	div.afq-voices div.afq-voice-card__avatar {
		width: 54px;
		height: 54px;
		border-radius: 50%;
		overflow: hidden;
		flex-shrink: 0;
		box-shadow: 0 0 0 2px #fff, 0 0 0 4px #d7dce3;
	}
	div.afq-voices div.afq-voice-card__avatar img {
		width: 100% !important;
		height: 100% !important;
		object-fit: cover;
		display: block !important;
		margin: 0 !important;
		border-radius: 0;
	}
	div.afq-voices h3.afq-voice-card__name {
		margin: 0 !important;
		padding: 0 !important;
		font-size: 15px;
		font-weight: 700;
		color: #1f2937;
		line-height: 1.5;
		flex: 1;
		min-width: 0;
	}
	div.afq-voices button.afq-voice-card__video-btn {
		display: inline-flex !important;
		align-items: center;
		justify-content: center;
		width: 40px !important;
		height: 40px !important;
		min-width: 0 !important;
		min-height: 0 !important;
		margin: 0 !important;
		padding: 0 !important;
		border: none !important;
		border-radius: 50% !important;
		background: linear-gradient(135deg, #d7dce3, #9aa3b0) !important;
		box-shadow: 0 6px 14px rgba(139, 149, 163, 0.45) !important;
		color: #14181f !important;
		cursor: pointer;
		flex-shrink: 0;
		transition: transform 0.15s ease, filter 0.15s ease;
	}
	div.afq-voices button.afq-voice-card__video-btn:hover {
		transform: scale(1.08);
		filter: brightness(1.05);
		background: linear-gradient(135deg, #d7dce3, #9aa3b0) !important;
	}
	div.afq-voices button.afq-voice-card__video-btn svg {
		width: 20px;
		height: 20px;
	}
	div.afq-voices p.afq-voice-card__desc {
		margin: 0 !important;
		padding: 0 !important;
		font-size: 13.5px;
		line-height: 2;
		color: #4b5563;
	}

	/* Audio player */
	div.afq-voices div.afq-voice-player {
		display: flex;
		align-items: center;
		gap: 12px;
		margin-top: auto;
		background: #f2f4f7;
		border: 1px solid #d9dee5;
		border-radius: 999px;
		padding: 8px 14px 8px 8px;
	}
	div.afq-voices button.afq-voice-player__btn {
		display: inline-flex !important;
		align-items: center;
		justify-content: center;
		width: 38px !important;
		height: 38px !important;
		min-width: 0 !important;
		min-height: 0 !important;
		margin: 0 !important;
		padding: 0 !important;
		border: none !important;
		border-radius: 50% !important;
		background: linear-gradient(135deg, #14181f, #2c3442) !important;
		box-shadow: none !important;
		color: #e7ebf0 !important;
		cursor: pointer;
		flex-shrink: 0;
		transition: transform 0.15s ease;
	}
	div.afq-voices button.afq-voice-player__btn:hover {
		transform: scale(1.06);
		background: linear-gradient(135deg, #14181f, #2c3442) !important;
	}
	div.afq-voices button.afq-voice-player__btn svg {
		width: 18px;
		height: 18px;
	}
	div.afq-voices .afq-voice-player__icon-pause {
		display: none;
	}
	div.afq-voices div.afq-voice-player.is-playing .afq-voice-player__icon-pause {
		display: block;
	}
	div.afq-voices div.afq-voice-player.is-playing .afq-voice-player__icon-play {
		display: none;
	}
	div.afq-voices div.afq-voice-player__track {
		position: relative;
		flex: 1;
		height: 5px;
		border-radius: 999px;
		background: rgba(154, 163, 176, 0.3);
		cursor: pointer;
		overflow: hidden;
	}
	div.afq-voices div.afq-voice-player__progress {
		position: absolute;
		inset-inline-start: 0;
		top: 0;
		bottom: 0;
		width: 0;
		border-radius: 999px;
		background: linear-gradient(90deg, #d7dce3, #9aa3b0);
	}
	div.afq-voices span.afq-voice-player__time {
		font-size: 11.5px;
		font-weight: 600;
		color: #667081;
		direction: ltr;
		flex-shrink: 0;
		min-width: 34px;
		text-align: center;
	}

	/* Video modal */
	div.afq-voices div.afq-voice-modal {
		position: fixed;
		inset: 0;
		z-index: 99999;
		display: none;
	}
	div.afq-voices div.afq-voice-modal.is-open {
		display: block;
	}
	div.afq-voices div.afq-voice-modal__overlay {
		position: absolute;
		inset: 0;
		background: rgba(10, 13, 18, 0.7);
		backdrop-filter: blur(4px);
		-webkit-backdrop-filter: blur(4px);
	}
	div.afq-voices div.afq-voice-modal__card {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		width: min(760px, 94vw);
		background: #fff;
		border-radius: 16px;
		overflow: hidden;
		box-shadow: 0 30px 70px rgba(0, 0, 0, 0.4);
		animation: afq-voice-modal-in 0.25s ease;
	}
	@keyframes afq-voice-modal-in {
		from { opacity: 0; transform: translate(-50%, -46%); }
		to   { opacity: 1; transform: translate(-50%, -50%); }
	}
	div.afq-voices div.afq-voice-modal__header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 14px 18px;
		background: linear-gradient(135deg, #14181f 0%, #232a36 60%, #2c3442 100%);
	}
	div.afq-voices span.afq-voice-modal__title {
		color: #e7ebf0;
		font-size: 14px;
		font-weight: 700;
	}
	div.afq-voices button.afq-voice-modal__close {
		display: inline-flex !important;
		align-items: center;
		justify-content: center;
		width: 30px !important;
		height: 30px !important;
		min-width: 0 !important;
		min-height: 0 !important;
		margin: 0 !important;
		padding: 0 !important;
		border: none !important;
		border-radius: 50% !important;
		background: rgba(255, 255, 255, 0.12) !important;
		box-shadow: none !important;
		color: #fff !important;
		font-size: 17px !important;
		line-height: 1 !important;
		cursor: pointer;
		transition: background 0.15s ease;
	}
	div.afq-voices button.afq-voice-modal__close:hover {
		background: rgba(255, 255, 255, 0.25) !important;
	}
	div.afq-voices div.afq-voice-modal__body {
		background: #000;
	}
	div.afq-voices div.afq-voice-modal__body video,
	div.afq-voices div.afq-voice-modal__body iframe {
		display: block;
		width: 100%;
		aspect-ratio: 16 / 9;
		border: none;
	}

	body.afq-voice-lock {
		overflow: hidden;
	}

	@media (max-width: 640px) {
		div.afq-voices article.afq-voice-card {
			padding: 18px;
		}
		div.afq-voices div.afq-voice-modal__card {
			width: 94vw;
		}
	}
	';
}

/**
 * Frontend inline JS (vanilla, no dependencies).
 *
 * @return string
 */
function afq_voice_front_inline_js() {
	return <<<'JS'
( function () {
	'use strict';

	/* ---------------- Audio players ---------------- */

	var activePlayer = null;

	function formatTime( seconds ) {
		if ( ! isFinite( seconds ) ) {
			return '0:00';
		}
		var m = Math.floor( seconds / 60 );
		var s = Math.floor( seconds % 60 );
		return m + ':' + ( s < 10 ? '0' + s : s );
	}

	function getAudio( player ) {
		var audio = player._afqAudio;

		if ( ! audio ) {
			audio = new Audio( player.getAttribute( 'data-afq-audio' ) );
			audio.preload = 'metadata';
			player._afqAudio = audio;

			var progress = player.querySelector( '.afq-voice-player__progress' );
			var time     = player.querySelector( '.afq-voice-player__time' );

			audio.addEventListener( 'loadedmetadata', function () {
				time.textContent = formatTime( audio.duration );
			} );

			audio.addEventListener( 'timeupdate', function () {
				if ( audio.duration ) {
					progress.style.width = ( audio.currentTime / audio.duration * 100 ) + '%';
				}
				time.textContent = formatTime( audio.currentTime );
			} );

			audio.addEventListener( 'ended', function () {
				player.classList.remove( 'is-playing' );
				progress.style.width = '0';
				time.textContent = formatTime( audio.duration );
				if ( activePlayer === player ) {
					activePlayer = null;
				}
			} );
		}

		return audio;
	}

	function pausePlayer( player ) {
		if ( player && player._afqAudio ) {
			player._afqAudio.pause();
			player.classList.remove( 'is-playing' );
		}
	}

	document.addEventListener( 'click', function ( e ) {

		/* Play / pause */
		var btn = e.target.closest( '.afq-voice-player__btn' );
		if ( btn ) {
			var player = btn.closest( '.afq-voice-player' );
			var audio  = getAudio( player );

			if ( audio.paused ) {
				if ( activePlayer && activePlayer !== player ) {
					pausePlayer( activePlayer );
				}
				audio.play();
				player.classList.add( 'is-playing' );
				activePlayer = player;
			} else {
				audio.pause();
				player.classList.remove( 'is-playing' );
			}
			return;
		}

		/* Seek */
		var track = e.target.closest( '.afq-voice-player__track' );
		if ( track ) {
			var seekPlayer = track.closest( '.afq-voice-player' );
			var seekAudio  = getAudio( seekPlayer );

			if ( seekAudio.duration ) {
				var rect  = track.getBoundingClientRect();
				var ratio = ( e.clientX - rect.left ) / rect.width;

				/* RTL pages fill from the right visually; ratio stays LTR because progress uses inset-inline-start. */
				if ( 'rtl' === getComputedStyle( track ).direction ) {
					ratio = 1 - ratio;
				}

				seekAudio.currentTime = Math.min( Math.max( ratio, 0 ), 1 ) * seekAudio.duration;
			}
			return;
		}

		/* Open video modal */
		var videoBtn = e.target.closest( '.afq-voice-card__video-btn' );
		if ( videoBtn ) {
			var wrap  = videoBtn.closest( '.afq-voices' );
			var modal = wrap.querySelector( '.afq-voice-modal' );
			var body  = modal.querySelector( '.afq-voice-modal__body' );
			var title = modal.querySelector( '.afq-voice-modal__title' );
			var url   = videoBtn.getAttribute( 'data-afq-video' );

			title.textContent = videoBtn.getAttribute( 'data-afq-video-name' ) || '';

			if ( /\.(mp4|webm|ogv|ogg|m4v|mov)(\?.*)?$/i.test( url ) ) {
				var video = document.createElement( 'video' );
				video.src = url;
				video.controls = true;
				video.autoplay = true;
				video.playsInline = true;
				body.innerHTML = '';
				body.appendChild( video );
			} else {
				var iframe = document.createElement( 'iframe' );
				iframe.src = url;
				iframe.allow = 'autoplay; fullscreen; picture-in-picture';
				iframe.setAttribute( 'allowfullscreen', '' );
				body.innerHTML = '';
				body.appendChild( iframe );
			}

			if ( activePlayer ) {
				pausePlayer( activePlayer );
			}

			modal.classList.add( 'is-open' );
			modal.setAttribute( 'aria-hidden', 'false' );
			document.body.classList.add( 'afq-voice-lock' );
			return;
		}

		/* Close video modal */
		var closer = e.target.closest( '[data-afq-vclose]' );
		if ( closer ) {
			closeModal( closer.closest( '.afq-voice-modal' ) );
		}
	} );

	function closeModal( modal ) {
		if ( ! modal ) {
			return;
		}
		modal.classList.remove( 'is-open' );
		modal.setAttribute( 'aria-hidden', 'true' );
		modal.querySelector( '.afq-voice-modal__body' ).innerHTML = '';
		document.body.classList.remove( 'afq-voice-lock' );
	}

	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key ) {
			closeModal( document.querySelector( '.afq-voice-modal.is-open' ) );
		}
	} );

} )();
JS;
}