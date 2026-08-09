
/**
 * AFQ Car — Media Meta Boxes (Restyled)
 * Replaces the previous "Post Meta Boxes / Taxonomy Image / Admin Assets /
 * Helpers / Elementor Dynamic Tags" block in functions.php.
 *
 * Existing meta keys are unchanged:
 *   _afq_car_image_normal, _afq_car_image_hover, _afq_car_short_desc, afq_car_cat_image
 *
 * New meta keys:
 *   _afq_car_gallery        (comma-separated attachment IDs)
 *   _afq_car_image_spot     (attachment ID)
 *   _afq_car_image_details  (attachment ID)
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Post Meta Boxes
 * ---------------------------------------------------------------------- */

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

/* -------------------------------------------------------------------------
 * Taxonomy Image Field
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
 * Admin Assets
 * ---------------------------------------------------------------------- */

/**
 * Enqueue media uploader, styles and scripts on relevant admin screens.
 *
 * @param string $hook Current admin page hook.
 */
function afq_car_admin_assets( $hook ) {

	$screen = get_current_screen();

	if ( ! $screen ) {
		return;
	}

	$is_post_screen = ( 'afq_car' === $screen->post_type && in_array( $hook, array( 'post.php', 'post-new.php' ), true ) );
	$is_term_screen = ( 'afq_car_cat' === $screen->taxonomy && in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) );

	if ( ! $is_post_screen && ! $is_term_screen ) {
		return;
	}

	wp_enqueue_media();

	wp_register_style( 'afq-car-media', false, array(), '1.0.0' );
	wp_enqueue_style( 'afq-car-media' );
	wp_add_inline_style( 'afq-car-media', afq_car_media_inline_css() );

	wp_register_script( 'afq-car-media', false, array( 'jquery', 'jquery-ui-sortable' ), '1.0.0', true );
	wp_enqueue_script( 'afq-car-media' );
	wp_add_inline_script( 'afq-car-media', afq_car_media_inline_js() );
}
add_action( 'admin_enqueue_scripts', 'afq_car_admin_assets' );

/**
 * Inline CSS for media meta boxes.
 *
 * @return string
 */
function afq_car_media_inline_css() {
	return '
	#afq_car_images.postbox,
	#afq_car_gallery.postbox,
	#afq_car_short_desc.postbox {
		border: none;
		border-radius: 14px;
		overflow: hidden;
		box-shadow: 0 10px 30px rgba(15, 20, 30, 0.08);
	}
	#afq_car_images .postbox-header,
	#afq_car_gallery .postbox-header,
	#afq_car_short_desc .postbox-header {
		background: linear-gradient(135deg, #14181f 0%, #232a36 60%, #2c3442 100%);
		border-bottom: none;
	}
	#afq_car_images .postbox-header .hndle,
	#afq_car_gallery .postbox-header .hndle,
	#afq_car_short_desc .postbox-header .hndle {
		color: #e8cf9a;
		font-size: 13px;
	}
	#afq_car_images .postbox-header .handle-actions button,
	#afq_car_gallery .postbox-header .handle-actions button,
	#afq_car_short_desc .postbox-header .handle-actions button {
		color: rgba(255, 255, 255, 0.7);
	}
	#afq_car_images .inside,
	#afq_car_gallery .inside,
	#afq_car_short_desc .inside {
		margin: 0;
		padding: 16px;
		background: #fbfbfc;
	}

	/* Buttons */
	.afq-media .afq-btn.button {
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
	.afq-media .afq-btn--gold.button {
		background: linear-gradient(135deg, #d8b46a, #b8934a);
		border-color: transparent;
		color: #14181f;
		font-weight: 600;
	}
	.afq-media .afq-btn--gold.button:hover {
		filter: brightness(1.06);
		color: #14181f;
	}
	.afq-media .afq-btn--ghost.button:hover {
		border-color: #d9534f;
		color: #d9534f;
	}

	/* Single image card */
	.afq-media-card {
		background: #fff;
		border: 1px solid #eef0f3;
		border-radius: 12px;
		padding: 12px;
		margin-bottom: 14px;
		box-shadow: 0 2px 8px rgba(15, 20, 30, 0.04);
	}
	.afq-media-card:last-child {
		margin-bottom: 0;
	}
	.afq-media-card__label {
		display: block;
		font-size: 12px;
		font-weight: 600;
		color: #374151;
		margin-bottom: 8px;
	}
	.afq-media-card__preview {
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
	.afq-media-card__preview img {
		max-width: 100%;
		height: auto;
		display: block;
		border-radius: 10px;
	}
	.afq-media-card__placeholder {
		color: #c3c9d1;
		font-size: 30px;
		width: 30px;
		height: 30px;
	}
	.afq-media-card__preview.has-image {
		border-style: solid;
		background: #fff;
	}
	.afq-media-card__preview.has-image .afq-media-card__placeholder,
	.afq-media-card__preview img + .afq-media-card__placeholder {
		display: none;
	}
	.afq-media-card__actions {
		display: flex;
		gap: 8px;
	}

	/* Gallery */
	.afq-gallery__head {
		display: flex;
		align-items: center;
		gap: 12px;
		background: #fff;
		border: 1px solid #eef0f3;
		border-radius: 12px;
		padding: 12px 14px;
		margin-bottom: 14px;
		box-shadow: 0 2px 8px rgba(15, 20, 30, 0.04);
	}
	.afq-gallery__head > .dashicons {
		width: 38px;
		height: 38px;
		font-size: 20px;
		line-height: 38px;
		text-align: center;
		border-radius: 10px;
		background: linear-gradient(135deg, #d8b46a, #b8934a);
		color: #14181f;
		flex-shrink: 0;
	}
	.afq-gallery__head strong {
		display: block;
		font-size: 13px;
		color: #1f2937;
	}
	.afq-gallery__head p {
		margin: 2px 0 0;
		font-size: 11.5px;
		color: #6b7280;
	}
	.afq-gallery__head .afq-btn {
		margin-right: auto;
	}
	.afq-gallery__grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
		gap: 10px;
		margin: 0;
	}
	.afq-gallery__item {
		position: relative;
		margin: 0;
		border-radius: 10px;
		overflow: hidden;
		border: 1px solid #eef0f3;
		background: #fff;
		cursor: grab;
		box-shadow: 0 2px 6px rgba(15, 20, 30, 0.06);
		transition: box-shadow 0.15s ease, transform 0.15s ease;
	}
	.afq-gallery__item:hover {
		box-shadow: 0 8px 18px rgba(15, 20, 30, 0.14);
		transform: translateY(-2px);
	}
	.afq-gallery__item img {
		width: 100%;
		height: 100px;
		object-fit: cover;
		display: block;
	}
	.afq-gallery__item.ui-sortable-helper {
		cursor: grabbing;
		box-shadow: 0 14px 28px rgba(15, 20, 30, 0.25);
	}
	.afq-gallery__remove {
		position: absolute;
		top: 6px;
		left: 6px;
		width: 22px;
		height: 22px;
		border: none;
		border-radius: 50%;
		background: rgba(20, 24, 31, 0.75);
		color: #fff;
		font-size: 14px;
		line-height: 22px;
		text-align: center;
		cursor: pointer;
		opacity: 0;
		transition: opacity 0.15s ease, background 0.15s ease;
	}
	.afq-gallery__item:hover .afq-gallery__remove {
		opacity: 1;
	}
	.afq-gallery__remove:hover {
		background: #d9534f;
	}
	.afq-gallery__empty {
		margin: 4px 0 0;
		padding: 18px;
		text-align: center;
		font-size: 12px;
		color: #9ca3af;
		border: 1px dashed #d9dde3;
		border-radius: 10px;
		background: #f6f7f9;
	}

	/* Short description editor */
	.afq-editor-wrap .wp-editor-container {
		border: 1px solid #e4e7ec;
		border-radius: 10px;
		overflow: hidden;
		box-shadow: 0 1px 2px rgba(15, 20, 30, 0.04);
	}
	.afq-editor-wrap .wp-editor-tabs .wp-switch-editor {
		border-radius: 8px 8px 0 0;
	}

	/* Term screen card width */
	.afq-term-image-field .afq-media-card {
		max-width: 340px;
	}
	';
}

/**
 * Inline JS: single image picker + gallery manager.
 *
 * @return string
 */
function afq_car_media_inline_js() {
	return <<<'JS'
( function( $ ) {
	'use strict';

	/* ---------------- Single image fields ---------------- */

	$( document ).on( 'click', '.afq-image-upload', function( e ) {
		e.preventDefault();

		var $button  = $( this );
		var $wrapper = $button.closest( '.afq-media-card' );
		var $input   = $wrapper.find( '.afq-image-id' );
		var $preview = $wrapper.find( '.afq-image-preview' );

		var frame = wp.media( {
			title: 'انتخاب تصویر',
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
			$wrapper.find( '.afq-image-remove' ).show();
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.afq-image-remove', function( e ) {
		e.preventDefault();

		var $button  = $( this );
		var $wrapper = $button.closest( '.afq-media-card' );

		$wrapper.find( '.afq-image-id' ).val( '' );
		$wrapper.find( '.afq-image-preview' ).removeClass( 'has-image' ).find( 'img' ).remove();
		$button.hide();
	} );

	/* ---------------- Gallery ---------------- */

	function afqGallerySync( $gallery ) {
		var ids = [];

		$gallery.find( '.afq-gallery__item' ).each( function() {
			ids.push( $( this ).data( 'id' ) );
		} );

		$gallery.find( '.afq-gallery-ids' ).val( ids.join( ',' ) );
		$gallery.find( '.afq-gallery__empty' ).toggle( ids.length === 0 );
	}

	$( document ).on( 'click', '.afq-gallery-add', function( e ) {
		e.preventDefault();

		var $gallery = $( this ).closest( '.afq-gallery' );
		var $grid    = $gallery.find( '.afq-gallery__grid' );

		var frame = wp.media( {
			title: 'افزودن تصاویر به گالری',
			button: { text: 'افزودن به گالری' },
			library: { type: 'image' },
			multiple: 'add'
		} );

		frame.on( 'select', function() {
			var selection = frame.state().get( 'selection' );

			selection.each( function( attachment ) {
				attachment = attachment.toJSON();

				if ( $grid.find( '.afq-gallery__item[data-id="' + attachment.id + '"]' ).length ) {
					return;
				}

				var url = ( attachment.sizes && attachment.sizes.thumbnail )
					? attachment.sizes.thumbnail.url
					: attachment.url;

				$grid.append(
					'<li class="afq-gallery__item" data-id="' + attachment.id + '">' +
						'<img src="' + url + '" alt="" />' +
						'<button type="button" class="afq-gallery__remove" aria-label="حذف">&times;</button>' +
					'</li>'
				);
			} );

			afqGallerySync( $gallery );
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.afq-gallery__remove', function( e ) {
		e.preventDefault();

		var $gallery = $( this ).closest( '.afq-gallery' );

		$( this ).closest( '.afq-gallery__item' ).remove();
		afqGallerySync( $gallery );
	} );

	$( function() {
		$( '.afq-gallery__grid' ).sortable( {
			items: '.afq-gallery__item',
			tolerance: 'pointer',
			update: function() {
				afqGallerySync( $( this ).closest( '.afq-gallery' ) );
			}
		} );
	} );

	/* ---------------- Term screen: reset after AJAX add ---------------- */

	$( document ).on( 'ajaxComplete', function( event, xhr, settings ) {
		if ( settings.data && settings.data.indexOf( 'action=add-tag' ) !== -1 ) {
			var $field = $( '.afq-term-image-field' );
			$field.find( '.afq-image-id' ).val( '' );
			$field.find( '.afq-image-preview' ).removeClass( 'has-image' ).find( 'img' ).remove();
			$field.find( '.afq-image-remove' ).hide();
		}
	} );

} )( jQuery );
JS;
}

/* -------------------------------------------------------------------------
 * Helper Functions
 * ---------------------------------------------------------------------- */

/**
 * Get car image HTML by type.
 *
 * @param int    $post_id Post ID.
 * @param string $type    Image type: normal|hover|spot|details.
 * @param string $size    Image size.
 * @return string
 */
function afq_get_car_image( $post_id = 0, $type = 'normal', $size = 'full' ) {

	$post_id = $post_id ? absint( $post_id ) : get_the_ID();

	$keys = array(
		'normal'  => '_afq_car_image_normal',
		'hover'   => '_afq_car_image_hover',
		'spot'    => '_afq_car_image_spot',
		'details' => '_afq_car_image_details',
	);

	$key = isset( $keys[ $type ] ) ? $keys[ $type ] : $keys['normal'];

	$attachment_id = absint( get_post_meta( $post_id, $key, true ) );

	return $attachment_id ? wp_get_attachment_image( $attachment_id, $size ) : '';
}

/**
 * Get car gallery attachment IDs.
 *
 * @param int $post_id Post ID.
 * @return int[]
 */
function afq_get_car_gallery_ids( $post_id = 0 ) {

	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$raw     = (string) get_post_meta( $post_id, '_afq_car_gallery', true );

	return array_filter( array_map( 'absint', explode( ',', $raw ) ) );
}

/**
 * Get car short description.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function afq_get_car_short_desc( $post_id = 0 ) {

	$post_id = $post_id ? absint( $post_id ) : get_the_ID();

	return wp_kses_post( get_post_meta( $post_id, '_afq_car_short_desc', true ) );
}

/**
 * Get car category image HTML.
 *
 * @param int    $term_id Term ID.
 * @param string $size    Image size.
 * @return string
 */
function afq_get_car_cat_image( $term_id, $size = 'full' ) {

	$attachment_id = absint( get_term_meta( absint( $term_id ), 'afq_car_cat_image', true ) );

	return $attachment_id ? wp_get_attachment_image( $attachment_id, $size ) : '';
}

/* -------------------------------------------------------------------------
 * Elementor Dynamic Tags
 * ---------------------------------------------------------------------- */

/**
 * Register car dynamic tags for Elementor.
 *
 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager Dynamic tags manager.
 */
function afq_register_car_dynamic_tags( $dynamic_tags_manager ) {

	if ( ! class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) ) {
		return;
	}

	/**
	 * Base class for car image dynamic tags.
	 */
	abstract class AFQ_Car_Image_Tag_Base extends \Elementor\Core\DynamicTags\Data_Tag {

		/**
		 * Get the post meta key for the image.
		 *
		 * @return string
		 */
		abstract protected function get_meta_key();

		/**
		 * Tag group.
		 *
		 * @return array
		 */
		public function get_group() {
			return array( 'post' );
		}

		/**
		 * Tag categories.
		 *
		 * @return array
		 */
		public function get_categories() {
			return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
		}

		/**
		 * Get image value.
		 *
		 * @param array $options Options.
		 * @return array
		 */
		public function get_value( array $options = array() ) {

			$attachment_id = absint( get_post_meta( get_the_ID(), $this->get_meta_key(), true ) );

			if ( ! $attachment_id ) {
				return array(
					'id'  => '',
					'url' => '',
				);
			}

			return array(
				'id'  => $attachment_id,
				'url' => wp_get_attachment_image_url( $attachment_id, 'full' ),
			);
		}
	}

	/**
	 * Normal image dynamic tag.
	 */
	class AFQ_Car_Image_Normal_Tag extends AFQ_Car_Image_Tag_Base {

		/**
		 * Tag name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'afq-car-image-normal';
		}

		/**
		 * Tag title.
		 *
		 * @return string
		 */
		public function get_title() {
			return 'تصویر عادی ماشین';
		}

		/**
		 * Meta key.
		 *
		 * @return string
		 */
		protected function get_meta_key() {
			return '_afq_car_image_normal';
		}
	}

	/**
	 * Hover image dynamic tag.
	 */
	class AFQ_Car_Image_Hover_Tag extends AFQ_Car_Image_Tag_Base {

		/**
		 * Tag name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'afq-car-image-hover';
		}

		/**
		 * Tag title.
		 *
		 * @return string
		 */
		public function get_title() {
			return 'تصویر هاور ماشین';
		}

		/**
		 * Meta key.
		 *
		 * @return string
		 */
		protected function get_meta_key() {
			return '_afq_car_image_hover';
		}
	}

	/**
	 * Spot image dynamic tag.
	 */
	class AFQ_Car_Image_Spot_Tag extends AFQ_Car_Image_Tag_Base {

		/**
		 * Tag name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'afq-car-image-spot';
		}

		/**
		 * Tag title.
		 *
		 * @return string
		 */
		public function get_title() {
			return 'تصویر اسپات ماشین';
		}

		/**
		 * Meta key.
		 *
		 * @return string
		 */
		protected function get_meta_key() {
			return '_afq_car_image_spot';
		}
	}

	/**
	 * Details image dynamic tag.
	 */
	class AFQ_Car_Image_Details_Tag extends AFQ_Car_Image_Tag_Base {

		/**
		 * Tag name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'afq-car-image-details';
		}

		/**
		 * Tag title.
		 *
		 * @return string
		 */
		public function get_title() {
			return 'تصویر جزئیات ماشین';
		}

		/**
		 * Meta key.
		 *
		 * @return string
		 */
		protected function get_meta_key() {
			return '_afq_car_image_details';
		}
	}

	/**
	 * Gallery dynamic tag.
	 */
	class AFQ_Car_Gallery_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

		/**
		 * Tag name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'afq-car-gallery';
		}

		/**
		 * Tag title.
		 *
		 * @return string
		 */
		public function get_title() {
			return 'گالری تصاویر ماشین';
		}

		/**
		 * Tag group.
		 *
		 * @return array
		 */
		public function get_group() {
			return array( 'post' );
		}

		/**
		 * Tag categories.
		 *
		 * @return array
		 */
		public function get_categories() {
			return array( \Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY );
		}

		/**
		 * Get gallery value.
		 *
		 * @param array $options Options.
		 * @return array
		 */
		public function get_value( array $options = array() ) {

			$ids   = afq_get_car_gallery_ids( get_the_ID() );
			$value = array();

			foreach ( $ids as $attachment_id ) {
				$value[] = array( 'id' => $attachment_id );
			}

			return $value;
		}
	}

	$dynamic_tags_manager->register( new AFQ_Car_Image_Normal_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Image_Hover_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Image_Spot_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Image_Details_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Gallery_Tag() );
}
add_action( 'elementor/dynamic_tags/register', 'afq_register_car_dynamic_tags' );