/**
 * AFQ Circular — بخش‌نامه فروش
 * Add this code to your theme's functions.php (after the afq_car code).
 *
 * Post type: afq_circular (public single page, no archive, no taxonomy).
 *   - Title   = عنوان بخش‌نامه
 *   - Content = ناحیه توضیحات (editor)
 *   - Image   = تصویر شاخص (featured image)
 *
 * Meta keys:
 *   afq_circular_sold_out  ('yes' when sold out, deleted when off)
 *                          → intentionally NOT underscore-prefixed so it is
 *                            selectable in Elementor dynamic tags/conditions.
 *   _afq_circular_cars     (comma-separated afq_car post IDs, keeps order)
 *
 * Shortcode:
 *   [afq_circular_cars]           → cars of the current circular (use inside its single template)
 *   [afq_circular_cars id="123"]  → cars of a specific circular
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Post Type
 * ---------------------------------------------------------------------- */

/**
 * Register sales circular post type.
 */
function afq_register_circular_post_type() {

	$labels = array(
		'name'               => 'بخش‌نامه‌های فروش',
		'singular_name'      => 'بخش‌نامه فروش',
		'menu_name'          => 'بخش‌نامه فروش',
		'add_new'            => 'افزودن بخش‌نامه',
		'add_new_item'       => 'افزودن بخش‌نامه جدید',
		'edit_item'          => 'ویرایش بخش‌نامه',
		'new_item'           => 'بخش‌نامه جدید',
		'view_item'          => 'مشاهده بخش‌نامه',
		'search_items'       => 'جستجوی بخش‌نامه',
		'not_found'          => 'بخش‌نامه‌ای یافت نشد',
		'not_found_in_trash' => 'بخش‌نامه‌ای در زباله‌دان یافت نشد',
		'all_items'          => 'همه بخش‌نامه‌ها',
	);

	register_post_type(
		'afq_circular',
		array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'has_archive'        => false,
			'rewrite'            => array(
				'slug'       => 'sales-circular',
				'with_front' => false,
			),
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-megaphone',
			'menu_position'      => 24,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'capability_type'    => 'post',
			'hierarchical'       => false,
		)
	);

	register_post_meta(
		'afq_circular',
		'afq_circular_sold_out',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'afq_register_circular_post_type' );

/* -------------------------------------------------------------------------
 * Meta Box — Sold-out Toggle + Cars Repeater
 * ---------------------------------------------------------------------- */

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
 * Get selected car IDs of a circular.
 *
 * @param int $post_id Circular post ID.
 * @return int[]
 */
function afq_circular_get_car_ids( $post_id ) {
	$raw = (string) get_post_meta( $post_id, '_afq_circular_cars', true );

	return array_filter( array_map( 'absint', explode( ',', $raw ) ) );
}

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
			'post_type'      => 'afq_car',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
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

/* -------------------------------------------------------------------------
 * Admin Assets
 * ---------------------------------------------------------------------- */

/**
 * Enqueue admin assets on afq_circular edit screens.
 *
 * @param string $hook Current admin page hook.
 */
function afq_circular_admin_assets( $hook ) {

	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'afq_circular' !== $screen->post_type ) {
		return;
	}

	wp_register_style( 'afq-circular-admin', false, array(), '1.0.0' );
	wp_enqueue_style( 'afq-circular-admin' );
	wp_add_inline_style( 'afq-circular-admin', afq_circular_admin_inline_css() );

	wp_register_script( 'afq-circular-admin', false, array( 'jquery', 'jquery-ui-sortable' ), '1.0.0', true );
	wp_enqueue_script( 'afq-circular-admin' );
	wp_add_inline_script( 'afq-circular-admin', afq_circular_admin_inline_js() );
}
add_action( 'admin_enqueue_scripts', 'afq_circular_admin_assets' );

/**
 * Admin inline CSS.
 *
 * @return string
 */
function afq_circular_admin_inline_css() {
	return '
	#afq_circular_details.postbox {
		border: none;
		border-radius: 14px;
		overflow: hidden;
		box-shadow: 0 10px 30px rgba(15, 20, 30, 0.08);
	}
	#afq_circular_details .postbox-header {
		background: linear-gradient(135deg, #14181f 0%, #232a36 60%, #2c3442 100%);
		border-bottom: none;
	}
	#afq_circular_details .postbox-header .hndle {
		color: #e8cf9a;
		font-size: 13px;
	}
	#afq_circular_details .postbox-header .handle-actions button {
		color: rgba(255, 255, 255, 0.7);
	}
	#afq_circular_details .inside {
		margin: 0;
		padding: 16px;
		background: #fbfbfc;
	}

	.afq-circular-admin__card {
		background: #fff;
		border: 1px solid #eef0f3;
		border-radius: 12px;
		padding: 14px;
		margin-bottom: 14px;
		box-shadow: 0 2px 8px rgba(15, 20, 30, 0.04);
	}
	.afq-circular-admin__card:last-of-type {
		margin-bottom: 0;
	}
	.afq-circular-admin__toggle-row,
	.afq-circular-admin__repeater-head {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
	}
	.afq-circular-admin strong {
		display: block;
		font-size: 13px;
		color: #1f2937;
	}
	.afq-circular-admin p {
		margin: 4px 0 0;
		font-size: 11.5px;
		color: #6b7280;
	}
	.afq-circular-admin__repeater-head {
		margin-bottom: 12px;
	}

	/* Toggle switch */
	.afq-switch {
		position: relative;
		display: inline-block;
		width: 52px;
		height: 28px;
		flex-shrink: 0;
	}
	.afq-switch input {
		position: absolute;
		opacity: 0;
		width: 0;
		height: 0;
	}
	.afq-switch__slider {
		position: absolute;
		inset: 0;
		background: #d5d9e0;
		border-radius: 999px;
		cursor: pointer;
		transition: background 0.2s ease;
	}
	.afq-switch__slider::before {
		content: "";
		position: absolute;
		top: 3px;
		inset-inline-start: 3px;
		width: 22px;
		height: 22px;
		border-radius: 50%;
		background: #fff;
		box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25);
		transition: transform 0.2s ease;
	}
	.afq-switch input:checked + .afq-switch__slider {
		background: linear-gradient(135deg, #d8b46a, #b8934a);
	}
	.afq-switch input:checked + .afq-switch__slider::before {
		transform: translateX(-24px);
	}
	.afq-switch input:focus-visible + .afq-switch__slider {
		box-shadow: 0 0 0 3px rgba(201, 164, 92, 0.3);
	}

	/* Repeater */
	.afq-circular-rows {
		display: flex;
		flex-direction: column;
		gap: 8px;
	}
	.afq-circular-row {
		display: flex;
		align-items: center;
		gap: 10px;
		background: #fbfbfc;
		border: 1px solid #eef0f3;
		border-radius: 10px;
		padding: 8px 10px;
	}
	.afq-circular-row__handle {
		color: #c3c9d1;
		cursor: grab;
		flex-shrink: 0;
	}
	.afq-circular-row.ui-sortable-helper {
		box-shadow: 0 10px 22px rgba(15, 20, 30, 0.18);
		cursor: grabbing;
	}
	.afq-circular-row select {
		flex: 1;
		border: 1px solid #e4e7ec;
		border-radius: 8px;
		background: #fff;
		padding: 6px 10px;
		font-size: 13px;
		color: #1f2937;
		max-width: none;
	}
	.afq-circular-row select:focus {
		border-color: #c9a45c;
		box-shadow: 0 0 0 3px rgba(201, 164, 92, 0.18);
		outline: none;
	}

	.afq-circular-admin .afq-circular-btn.button {
		border-radius: 8px;
		border: 1px solid #e4e7ec;
		background: linear-gradient(135deg, #d8b46a, #b8934a);
		border-color: transparent;
		color: #14181f;
		font-weight: 600;
		font-size: 12px;
		line-height: 2.2;
		padding: 0 14px;
		flex-shrink: 0;
	}
	.afq-circular-admin .afq-circular-btn.button:hover {
		filter: brightness(1.06);
		color: #14181f;
	}
	.afq-circular-admin .afq-circular-btn--ghost.button {
		background: #fff;
		border-color: #e4e7ec;
		color: #374151;
		font-weight: 400;
	}
	.afq-circular-admin .afq-circular-btn--ghost.button:hover {
		border-color: #d9534f;
		color: #d9534f;
		filter: none;
	}
	.afq-circular-empty {
		margin: 10px 0 0 !important;
		padding: 14px;
		text-align: center;
		border: 1px dashed #d9dde3;
		border-radius: 10px;
		background: #f6f7f9;
	}
	';
}

/**
 * Admin inline JS (repeater).
 *
 * @return string
 */
function afq_circular_admin_inline_js() {
	return <<<'JS'
( function( $ ) {
	'use strict';

	var $wrap = $( '.afq-circular-admin' );

	if ( ! $wrap.length ) {
		return;
	}

	var $rows     = $wrap.find( '.afq-circular-rows' );
	var $empty    = $wrap.find( '.afq-circular-empty' );
	var template  = $( '#afq-circular-row-template' ).html();

	function toggleEmpty() {
		$empty.toggle( 0 === $rows.children( '.afq-circular-row' ).length );
	}

	$wrap.on( 'click', '.afq-circular-add-row', function( e ) {
		e.preventDefault();
		$rows.append( template );
		toggleEmpty();
	} );

	$wrap.on( 'click', '.afq-circular-remove-row', function( e ) {
		e.preventDefault();
		$( this ).closest( '.afq-circular-row' ).remove();
		toggleEmpty();
	} );

	$rows.sortable( {
		items: '.afq-circular-row',
		handle: '.afq-circular-row__handle',
		tolerance: 'pointer'
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
function afq_circular_register_front_assets() {
	wp_register_style( 'afq-circular-cars', false, array(), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'afq_circular_register_front_assets' );

/**
 * Circular cars shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function afq_circular_cars_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'id' => 0,
		),
		$atts,
		'afq_circular_cars'
	);

	$circular_id = $atts['id'] ? absint( $atts['id'] ) : get_the_ID();

	if ( ! $circular_id || 'afq_circular' !== get_post_type( $circular_id ) ) {
		return '';
	}

	$car_ids = afq_circular_get_car_ids( $circular_id );

	if ( ! $car_ids ) {
		return '';
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'afq_car',
			'post_status'    => 'publish',
			'posts_per_page' => count( $car_ids ),
			'post__in'       => $car_ids,
			'orderby'        => 'post__in',
			'no_found_rows'  => true,
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	$sold_out = ( 'yes' === get_post_meta( $circular_id, 'afq_circular_sold_out', true ) );

	wp_enqueue_style( 'afq-circular-cars' );
	wp_add_inline_style( 'afq-circular-cars', afq_circular_cars_inline_css() );

	ob_start();
	?>
	<div class="afq-circ-cars<?php echo $sold_out ? ' is-soldout' : ''; ?>">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();

			$car_id        = get_the_ID();
			$normal_id     = absint( get_post_meta( $car_id, '_afq_car_image_normal', true ) );
			$hover_id      = absint( get_post_meta( $car_id, '_afq_car_image_hover', true ) );
			$price_regular = get_post_meta( $car_id, '_afq_car_price_regular', true );
			$price_sale    = get_post_meta( $car_id, '_afq_car_price_sale', true );

			if ( ! $normal_id && has_post_thumbnail( $car_id ) ) {
				$normal_id = get_post_thumbnail_id( $car_id );
			}
			?>
			<a class="afq-circ-car" href="<?php echo esc_url( get_permalink( $car_id ) ); ?>">

				<?php if ( $sold_out ) : ?>
					<span class="afq-circ-car__ribbon">اتمام فروش</span>
				<?php endif; ?>

				<?php if ( $normal_id ) : ?>
					<span class="afq-circ-car__media<?php echo $hover_id ? ' has-hover' : ''; ?>">
						<?php echo wp_get_attachment_image( $normal_id, 'large', false, array( 'class' => 'afq-circ-car__img afq-circ-car__img--normal' ) ); ?>
						<?php if ( $hover_id ) : ?>
							<?php echo wp_get_attachment_image( $hover_id, 'large', false, array( 'class' => 'afq-circ-car__img afq-circ-car__img--hover' ) ); ?>
						<?php endif; ?>
					</span>
				<?php endif; ?>

				<span class="afq-circ-car__body">
					<span class="afq-circ-car__name"><?php the_title(); ?></span>

					<?php if ( $price_regular || $price_sale ) : ?>
						<span class="afq-circ-car__prices">
							<?php if ( $price_sale ) : ?>
								<?php if ( $price_regular ) : ?>
									<del><?php echo esc_html( $price_regular ); ?></del>
								<?php endif; ?>
								<ins><?php echo esc_html( $price_sale ); ?></ins>
							<?php else : ?>
								<ins><?php echo esc_html( $price_regular ); ?></ins>
							<?php endif; ?>
						</span>
					<?php endif; ?>

					<span class="afq-circ-car__cta">مشاهده خودرو</span>
				</span>

			</a>
		<?php endwhile; ?>
	</div>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'afq_circular_cars', 'afq_circular_cars_shortcode' );

/**
 * Frontend inline CSS (silver palette).
 *
 * @return string
 */
function afq_circular_cars_inline_css() {
	return '
	div.afq-circ-cars,
	div.afq-circ-cars * {
		box-sizing: border-box;
	}

	div.afq-circ-cars {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
		gap: 22px;
	}

	div.afq-circ-cars a.afq-circ-car {
		position: relative;
		display: flex;
		flex-direction: column;
		background: #fff;
		border: 1px solid #e7ebf0;
		border-radius: 16px;
		overflow: hidden;
		text-decoration: none !important;
		box-shadow: 0 6px 20px rgba(15, 20, 30, 0.06);
		transition: box-shadow 0.2s ease, transform 0.2s ease;
	}
	div.afq-circ-cars a.afq-circ-car:hover {
		box-shadow: 0 16px 36px rgba(15, 20, 30, 0.13);
		transform: translateY(-4px);
	}

	/* Ribbon */
	div.afq-circ-cars span.afq-circ-car__ribbon {
		position: absolute;
		top: 14px;
		inset-inline-start: -34px;
		transform: rotate(45deg);
		background: #c62828;
		color: #fff;
		font-size: 11px;
		font-weight: 700;
		padding: 5px 40px;
		z-index: 2;
		box-shadow: 0 3px 8px rgba(0, 0, 0, 0.25);
	}
	div.afq-circ-cars.is-soldout span.afq-circ-car__media {
		filter: grayscale(0.55);
	}

	/* Media (normal/hover swap) */
	div.afq-circ-cars span.afq-circ-car__media {
		position: relative;
		display: block;
		background: #f2f4f7;
	}
	div.afq-circ-cars img.afq-circ-car__img {
		width: 100% !important;
		height: 190px !important;
		object-fit: cover;
		display: block !important;
		margin: 0 !important;
		transition: opacity 0.3s ease;
	}
	div.afq-circ-cars img.afq-circ-car__img--hover {
		position: absolute;
		inset: 0;
		opacity: 0;
	}
	div.afq-circ-cars a.afq-circ-car:hover span.has-hover img.afq-circ-car__img--hover {
		opacity: 1;
	}
	div.afq-circ-cars a.afq-circ-car:hover span.has-hover img.afq-circ-car__img--normal {
		opacity: 0;
	}

	/* Body */
	div.afq-circ-cars span.afq-circ-car__body {
		display: flex;
		flex-direction: column;
		gap: 10px;
		padding: 16px 18px 18px;
	}
	div.afq-circ-cars span.afq-circ-car__name {
		font-size: 14.5px;
		font-weight: 700;
		color: #1f2937;
		line-height: 1.7;
	}
	div.afq-circ-cars span.afq-circ-car__prices {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 8px;
	}
	div.afq-circ-cars span.afq-circ-car__prices del {
		color: #9aa3b0;
		font-size: 12px;
		text-decoration: line-through;
	}
	div.afq-circ-cars span.afq-circ-car__prices ins {
		color: #1f2937;
		font-size: 13.5px;
		font-weight: 700;
		text-decoration: none;
		background: none;
	}
	div.afq-circ-cars span.afq-circ-car__cta {
		display: inline-block;
		margin-top: 2px;
		padding: 9px 0;
		text-align: center;
		border-radius: 999px;
		background: linear-gradient(135deg, #d7dce3, #9aa3b0);
		color: #14181f;
		font-size: 12.5px;
		font-weight: 700;
		transition: filter 0.15s ease;
	}
	div.afq-circ-cars a.afq-circ-car:hover span.afq-circ-car__cta {
		filter: brightness(1.05);
	}

	@media (max-width: 640px) {
		div.afq-circ-cars {
			grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
			gap: 14px;
		}
		div.afq-circ-cars img.afq-circ-car__img {
			height: 130px !important;
		}
		div.afq-circ-cars span.afq-circ-car__body {
			padding: 12px 14px 14px;
		}
	}
	';
}


/**
 * AFQ Circular — Elementor Custom Queries
 * Add this code to functions.php AFTER the afq_circular code.
 *
 * Usage in Elementor (Loop Grid / Posts widget → Query → Query ID):
 *   afq_circular_sold       → بخش‌نامه‌هایی که اتمام فروش خورده‌اند
 *   afq_circular_available  → بخش‌نامه‌هایی که اتمام فروش نخورده‌اند
 */

defined( 'ABSPATH' ) || exit;

/**
 * Elementor query: sold-out circulars.
 *
 * @param WP_Query $query The Elementor widget query.
 */
function afq_circular_query_sold( $query ) {

	$query->set( 'post_type', 'afq_circular' );

	$meta_query   = (array) $query->get( 'meta_query' );
	$meta_query[] = array(
		'key'   => 'afq_circular_sold_out',
		'value' => 'yes',
	);

	$query->set( 'meta_query', $meta_query );
}
add_action( 'elementor/query/afq_circular_sold', 'afq_circular_query_sold' );

/**
 * Elementor query: available (not sold-out) circulars.
 *
 * The sold-out meta is deleted when the toggle is off, so "available"
 * means the meta does not exist OR its value is not 'yes'.
 *
 * @param WP_Query $query The Elementor widget query.
 */
function afq_circular_query_available( $query ) {

	$query->set( 'post_type', 'afq_circular' );

	$meta_query   = (array) $query->get( 'meta_query' );
	$meta_query[] = array(
		'relation' => 'OR',
		array(
			'key'     => 'afq_circular_sold_out',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => 'afq_circular_sold_out',
			'value'   => 'yes',
			'compare' => '!=',
		),
	);

	$query->set( 'meta_query', $meta_query );
}
add_action( 'elementor/query/afq_circular_available', 'afq_circular_query_available' );