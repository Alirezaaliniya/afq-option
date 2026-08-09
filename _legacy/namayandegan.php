/**
 * AFQ Representatives — نمایندگان + نقشه ایران
 * Add this code to your theme's functions.php.
 *
 * Post type: afq_rep (no archive/single — rendered only via shortcode).
 * Taxonomy:  afq_rep_province (استان) — each term stores the spot position
 *            on the map image (left/top %) so spots are managed from the
 *            taxonomy edit screen.
 *
 * Map image: upload the Iran map image to the Media Library once and pass
 * its attachment ID (or a URL) to the shortcode:
 *
 *   [afq_rep_map map="123"]
 *   [afq_rep_map map="https://example.com/iran-map.png"]
 *
 * Meta keys (post):
 *   _afq_rep_code, _afq_rep_city, _afq_rep_type, _afq_rep_grade,
 *   _afq_rep_phone (one number per line), _afq_rep_fax, _afq_rep_postal,
 *   _afq_rep_area_code, _afq_rep_address (phone & fax: one number per line)
 *
 * Term meta (province):
 *   afq_rep_spot_left, afq_rep_spot_top (percent 0-100 on the map image)
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Post Type + Taxonomy
 * ---------------------------------------------------------------------- */

/**
 * Register representative post type and province taxonomy.
 */
function afq_register_rep_post_type() {

	$labels = array(
		'name'               => 'نمایندگان',
		'singular_name'      => 'نماینده',
		'menu_name'          => 'نمایندگان',
		'add_new'            => 'افزودن نماینده',
		'add_new_item'       => 'افزودن نماینده جدید',
		'edit_item'          => 'ویرایش نماینده',
		'new_item'           => 'نماینده جدید',
		'view_item'          => 'مشاهده',
		'search_items'       => 'جستجوی نماینده',
		'not_found'          => 'موردی یافت نشد',
		'not_found_in_trash' => 'موردی در زباله‌دان یافت نشد',
		'all_items'          => 'همه نمایندگان',
	);

	register_post_type(
		'afq_rep',
		array(
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
			'menu_icon'           => 'dashicons-location-alt',
			'menu_position'       => 23,
			'supports'            => array( 'title', 'page-attributes' ),
			'capability_type'     => 'post',
			'hierarchical'        => false,
		)
	);

	register_taxonomy(
		'afq_rep_province',
		array( 'afq_rep' ),
		array(
			'labels'            => array(
				'name'          => 'استان‌ها',
				'singular_name' => 'استان',
				'search_items'  => 'جستجوی استان',
				'all_items'     => 'همه استان‌ها',
				'edit_item'     => 'ویرایش استان',
				'update_item'   => 'بروزرسانی استان',
				'add_new_item'  => 'افزودن استان جدید',
				'new_item_name' => 'نام استان جدید',
				'menu_name'     => 'استان‌ها',
			),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => false,
		)
	);

	register_term_meta(
		'afq_rep_province',
		'afq_rep_spot_left',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	register_term_meta(
		'afq_rep_province',
		'afq_rep_spot_top',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
}
add_action( 'init', 'afq_register_rep_post_type' );

/* -------------------------------------------------------------------------
 * Province Term Meta — Spot Position
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

/* -------------------------------------------------------------------------
 * Meta Box
 * ---------------------------------------------------------------------- */

/**
 * Representative field definitions.
 *
 * @return array
 */
function afq_rep_get_fields() {
	return array(
		'_afq_rep_code'      => array(
			'label'       => 'کد نمایندگی',
			'type'        => 'text',
			'placeholder' => 'مثال: 100',
		),
		'_afq_rep_city'      => array(
			'label'       => 'شهر',
			'type'        => 'text',
			'placeholder' => 'مثال: تهران',
		),
		'_afq_rep_type'      => array(
			'label'   => 'نوع',
			'type'    => 'select',
			'options' => array(
				''   => 'انتخاب کنید',
				'1S' => '1S',
				'2S' => '2S',
				'3S' => '3S',
			),
		),
		'_afq_rep_grade'     => array(
			'label'       => 'گرید',
			'type'        => 'text',
			'placeholder' => 'مثال: +A',
		),
		'_afq_rep_area_code' => array(
			'label'       => 'کد شهر',
			'type'        => 'text',
			'placeholder' => 'مثال: 21',
		),
		'_afq_rep_phone'     => array(
			'label'       => 'تلفن',
			'type'        => 'textarea',
			'placeholder' => "هر شماره در یک خط:\n02122375593\n02122375594",
		),
		'_afq_rep_fax'       => array(
			'label'       => 'فکس',
			'type'        => 'textarea',
			'placeholder' => "هر شماره در یک خط:\n02122375593\n02122375594",
		),
		'_afq_rep_postal'    => array(
			'label'       => 'کد پستی',
			'type'        => 'text',
			'placeholder' => 'مثال: 9174693755',
		),
		'_afq_rep_address'   => array(
			'label'       => 'آدرس',
			'type'        => 'textarea',
			'placeholder' => 'نشانی کامل نمایندگی...',
		),
	);
}

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

/**
 * Admin styles for the representative meta box.
 *
 * @param string $hook Current admin page hook.
 */
function afq_rep_admin_assets( $hook ) {

	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'afq_rep' !== $screen->post_type ) {
		return;
	}

	wp_register_style( 'afq-rep-admin', false, array(), '1.0.0' );
	wp_enqueue_style( 'afq-rep-admin' );
	wp_add_inline_style(
		'afq-rep-admin',
		'
		#afq_rep_info.postbox { border: none; border-radius: 14px; overflow: hidden; box-shadow: 0 10px 30px rgba(15,20,30,0.08); }
		#afq_rep_info .postbox-header { background: linear-gradient(135deg, #14181f 0%, #232a36 60%, #2c3442 100%); border-bottom: none; }
		#afq_rep_info .postbox-header .hndle { color: #e8cf9a; font-size: 13px; }
		#afq_rep_info .postbox-header .handle-actions button { color: rgba(255,255,255,0.7); }
		#afq_rep_info .inside { margin: 0; padding: 16px; background: #fbfbfc; }
		.afq-rep-admin__hint { margin: 0 0 14px; font-size: 12px; color: #6b7280; }
		.afq-rep-admin__grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
		.afq-rep-admin__field { display: flex; flex-direction: column; gap: 6px; }
		.afq-rep-admin__field--wide { grid-column: 1 / -1; }
		.afq-rep-admin__field label { font-size: 12px; font-weight: 600; color: #374151; }
		.afq-rep-admin__field input, .afq-rep-admin__field select, .afq-rep-admin__field textarea {
			width: 100%; border: 1px solid #e4e7ec; border-radius: 10px; background: #fff; padding: 9px 12px;
			font-size: 13px; color: #1f2937; box-shadow: 0 1px 2px rgba(15,20,30,0.04);
			transition: border-color .15s ease, box-shadow .15s ease;
		}
		.afq-rep-admin__field input:focus, .afq-rep-admin__field select:focus, .afq-rep-admin__field textarea:focus {
			border-color: #c9a45c; box-shadow: 0 0 0 3px rgba(201,164,92,0.18); outline: none;
		}
		.afq-rep-admin__field textarea { resize: vertical; min-height: 90px; line-height: 1.8; }
		'
	);
}
add_action( 'admin_enqueue_scripts', 'afq_rep_admin_assets' );

/* -------------------------------------------------------------------------
 * AJAX — Representatives by Province
 * ---------------------------------------------------------------------- */

/**
 * Render representative cards for a province term.
 *
 * @param int $term_id Province term ID.
 * @return string
 */
function afq_rep_render_cards( $term_id ) {

	$term = get_term( $term_id, 'afq_rep_province' );

	if ( ! $term instanceof WP_Term ) {
		return '';
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'afq_rep',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'afq_rep_province',
					'field'    => 'term_id',
					'terms'    => $term_id,
				),
			),
		)
	);

	ob_start();
	?>
	<h3 class="afq-repmap__province-title">نمایندگان استان <?php echo esc_html( $term->name ); ?></h3>

	<?php if ( ! $query->have_posts() ) : ?>
		<p class="afq-repmap__no-result">در این استان نمایندگی یا عاملیت فروش ثبت نشده است.</p>
		<?php
		return ob_get_clean();
	endif;
	?>

	<div class="afq-rep-cards">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();

			$post_id = get_the_ID();
			$meta    = array();
			foreach ( array_keys( afq_rep_get_fields() ) as $key ) {
				$meta[ $key ] = get_post_meta( $post_id, $key, true );
			}

			$phones = array_filter( array_map( 'trim', explode( "\n", (string) $meta['_afq_rep_phone'] ) ) );
			$faxes  = array_filter( array_map( 'trim', explode( "\n", (string) $meta['_afq_rep_fax'] ) ) );
			?>
			<article class="afq-rep-card">

				<div class="afq-rep-card__main">

					<div class="afq-rep-card__head">
						<h4 class="afq-rep-card__name"><?php the_title(); ?></h4>

						<?php if ( $meta['_afq_rep_type'] ) : ?>
							<span class="afq-rep-card__badge"><?php echo esc_html( $meta['_afq_rep_type'] ); ?></span>
						<?php endif; ?>

						<?php if ( $meta['_afq_rep_grade'] ) : ?>
							<span class="afq-rep-card__badge afq-rep-card__badge--grade">گرید <?php echo esc_html( $meta['_afq_rep_grade'] ); ?></span>
						<?php endif; ?>
					</div>

					<div class="afq-rep-card__rows">

						<?php if ( $meta['_afq_rep_code'] ) : ?>
							<div class="afq-rep-card__row"><span>کد نمایندگی</span><strong><?php echo esc_html( $meta['_afq_rep_code'] ); ?></strong></div>
						<?php endif; ?>

						<?php if ( $meta['_afq_rep_city'] ) : ?>
							<div class="afq-rep-card__row"><span>شهر</span><strong><?php echo esc_html( $meta['_afq_rep_city'] ); ?></strong></div>
						<?php endif; ?>

						<?php if ( $meta['_afq_rep_area_code'] ) : ?>
							<div class="afq-rep-card__row"><span>کد شهر</span><strong><?php echo esc_html( $meta['_afq_rep_area_code'] ); ?></strong></div>
						<?php endif; ?>

						<?php if ( $phones ) : ?>
							<div class="afq-rep-card__row"><span>تلفن</span>
								<strong class="afq-rep-card__phones">
									<?php foreach ( $phones as $i => $phone ) : ?>
										<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a><?php echo ( $i < count( $phones ) - 1 ) ? ' ، ' : ''; ?>
									<?php endforeach; ?>
								</strong>
							</div>
						<?php endif; ?>

						<?php if ( $faxes ) : ?>
							<div class="afq-rep-card__row"><span>فکس</span>
								<strong class="afq-rep-card__phones"><?php echo esc_html( implode( ' ، ', $faxes ) ); ?></strong>
							</div>
						<?php endif; ?>

						<?php if ( $meta['_afq_rep_postal'] ) : ?>
							<div class="afq-rep-card__row"><span>کد پستی</span><strong><?php echo esc_html( $meta['_afq_rep_postal'] ); ?></strong></div>
						<?php endif; ?>

						<?php if ( $meta['_afq_rep_address'] ) : ?>
							<div class="afq-rep-card__row afq-rep-card__row--full"><span>آدرس</span><strong><?php echo esc_html( $meta['_afq_rep_address'] ); ?></strong></div>
						<?php endif; ?>

					</div>

				</div>

			</article>
		<?php endwhile; ?>
	</div>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}

/**
 * AJAX handler: representatives of a province.
 */
function afq_rep_ajax_filter() {

	check_ajax_referer( 'afq_rep_map', 'nonce' );

	$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;

	if ( ! $term_id ) {
		wp_send_json_error();
	}

	$html = afq_rep_render_cards( $term_id );

	if ( '' === $html ) {
		wp_send_json_error();
	}

	wp_send_json_success( $html );
}
add_action( 'wp_ajax_afq_rep_filter', 'afq_rep_ajax_filter' );
add_action( 'wp_ajax_nopriv_afq_rep_filter', 'afq_rep_ajax_filter' );

/* -------------------------------------------------------------------------
 * Frontend Shortcode
 * ---------------------------------------------------------------------- */

/**
 * Register empty front asset handles.
 */
function afq_rep_register_front_assets() {
	wp_register_style( 'afq-rep-map', false, array(), '1.0.0' );
	wp_register_script( 'afq-rep-map', false, array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'afq_rep_register_front_assets' );

/**
 * Iran map shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function afq_rep_map_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'map' => '',
		),
		$atts,
		'afq_rep_map'
	);

	/* Resolve map image (attachment ID or direct URL). */
	$map_url = '';
	if ( is_numeric( $atts['map'] ) ) {
		$map_url = wp_get_attachment_image_url( absint( $atts['map'] ), 'full' );
	} elseif ( $atts['map'] ) {
		$map_url = esc_url_raw( $atts['map'] );
	}

	if ( ! $map_url ) {
		return '<div class="afq-repmap afq-repmap--empty"><p>تصویر نقشه تنظیم نشده است. شناسه تصویر را در شورت‌کد وارد کنید: [afq_rep_map map="123"]</p></div>';
	}

	/* Provinces that have representatives and a spot position. */
	$terms = get_terms(
		array(
			'taxonomy'   => 'afq_rep_province',
			'hide_empty' => true,
		)
	);

	$spots = array();

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$left = get_term_meta( $term->term_id, 'afq_rep_spot_left', true );
			$top  = get_term_meta( $term->term_id, 'afq_rep_spot_top', true );

			if ( '' === $left || '' === $top || ! is_numeric( $left ) || ! is_numeric( $top ) ) {
				continue;
			}

			$spots[] = array(
				'term' => $term,
				'left' => (float) $left,
				'top'  => (float) $top,
			);
		}
	}

	wp_enqueue_style( 'afq-rep-map' );
	wp_add_inline_style( 'afq-rep-map', afq_rep_map_inline_css() );

	wp_enqueue_script( 'afq-rep-map' );
	wp_add_inline_script(
		'afq-rep-map',
		'var afqRepMapCfg = ' . wp_json_encode(
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'afq_rep_map' ),
			)
		) . ';',
		'before'
	);
	wp_add_inline_script( 'afq-rep-map', afq_rep_map_inline_js() );

	ob_start();
	?>
	<div class="afq-repmap">

		<div class="afq-repmap__stage">
			<img class="afq-repmap__image" src="<?php echo esc_url( $map_url ); ?>" alt="نقشه نمایندگی‌ها" />

			<?php foreach ( $spots as $spot ) : ?>
				<button type="button"
					class="afq-repmap__spot"
					style="left:<?php echo esc_attr( $spot['left'] ); ?>%;top:<?php echo esc_attr( $spot['top'] ); ?>%;"
					data-afq-term="<?php echo esc_attr( $spot['term']->term_id ); ?>"
					aria-label="نمایندگان استان <?php echo esc_attr( $spot['term']->name ); ?>">
					<span class="afq-repmap__spot-dot"></span>
					<span class="afq-repmap__spot-label"><?php echo esc_html( $spot['term']->name ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="afq-repmap__results" aria-live="polite">
			<p class="afq-repmap__intro">برای مشاهده نمایندگان، استان مورد نظر را روی نقشه انتخاب کنید.</p>
		</div>

	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'afq_rep_map', 'afq_rep_map_shortcode' );

/**
 * Frontend inline CSS (silver palette).
 *
 * @return string
 */
function afq_rep_map_inline_css() {
	return '
	div.afq-repmap,
	div.afq-repmap * {
		box-sizing: border-box;
	}

	/* Desktop: map and results side by side */
	@media (min-width: 1025px) {
		div.afq-repmap {
			display: flex;
			align-items: flex-start;
			gap: 30px;
		}
		div.afq-repmap div.afq-repmap__stage {
			flex: 0 0 46%;
			max-width: 46%;
			position: sticky;
			top: 30px;
		}
		div.afq-repmap div.afq-repmap__results {
			flex: 1;
			min-width: 0;
			margin-top: 0;
		}
	}

	/* Stage */
	div.afq-repmap div.afq-repmap__stage {
		position: relative;
		line-height: 0;
	}
	div.afq-repmap img.afq-repmap__image {
		width: 100% !important;
		max-width: 100% !important;
		height: auto !important;
		display: block !important;
		margin: 0 !important;
	}

	/* Spot */
	div.afq-repmap button.afq-repmap__spot {
		position: absolute !important;
		transform: translate(-50%, -50%);
		display: flex !important;
		flex-direction: column;
		align-items: center;
		gap: 5px;
		width: auto !important;
		min-width: 30px !important;
		min-height: 30px !important;
		margin: 0 !important;
		padding: 0 !important;
		border: none !important;
		border-radius: 0 !important;
		background: transparent !important;
		box-shadow: none !important;
		line-height: 1 !important;
		cursor: pointer;
		z-index: 2;
	}
	div.afq-repmap span.afq-repmap__spot-dot {
		position: relative;
		display: block;
		width: 16px;
		height: 16px;
		border-radius: 50%;
		background: linear-gradient(135deg, #ef5350, #c62828);
		box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.9), 0 3px 10px rgba(0, 0, 0, 0.3);
		animation: afq-repmap-blink 1.1s ease-in-out infinite;
	}
	div.afq-repmap span.afq-repmap__spot-dot::before {
		content: "";
		position: absolute;
		inset: 0;
		border-radius: 50%;
		background: rgba(229, 57, 53, 0.55);
		animation: afq-repmap-pulse 2s ease-out infinite;
	}
	@keyframes afq-repmap-pulse {
		0%   { transform: scale(1);   opacity: 0.9; }
		70%  { transform: scale(2.3); opacity: 0;   }
		100% { transform: scale(2.3); opacity: 0;   }
	}
	@keyframes afq-repmap-blink {
		0%, 100% { opacity: 1; }
		50%      { opacity: 0.35; }
	}
	div.afq-repmap span.afq-repmap__spot-label {
		display: inline-block;
		background: rgba(20, 24, 31, 0.88);
		color: #e7ebf0;
		font-size: 11px;
		font-weight: 600;
		padding: 4px 9px;
		border-radius: 999px;
		white-space: nowrap;
		opacity: 0;
		transform: translateY(-3px);
		transition: opacity 0.18s ease, transform 0.18s ease;
		pointer-events: none;
	}
	div.afq-repmap button.afq-repmap__spot:hover span.afq-repmap__spot-label,
	div.afq-repmap button.afq-repmap__spot:focus-visible span.afq-repmap__spot-label,
	div.afq-repmap button.afq-repmap__spot.is-active span.afq-repmap__spot-label {
		opacity: 1;
		transform: translateY(0);
	}
	div.afq-repmap button.afq-repmap__spot.is-active span.afq-repmap__spot-dot {
		background: linear-gradient(135deg, #2c3442, #14181f);
		box-shadow: 0 0 0 3px #ef5350, 0 3px 10px rgba(0, 0, 0, 0.35);
		animation: none;
	}
	div.afq-repmap button.afq-repmap__spot.is-active span.afq-repmap__spot-dot::before {
		animation: none;
		opacity: 0;
	}

	/* Results */
	div.afq-repmap div.afq-repmap__results {
		margin-top: 26px;
	}
	div.afq-repmap p.afq-repmap__intro {
		margin: 0 !important;
		padding: 18px;
		text-align: center;
		font-size: 13px;
		color: #6b7280;
		border: 1px dashed #c7cdd6;
		border-radius: 12px;
		background: #f6f7f9;
	}
	div.afq-repmap div.afq-repmap__loading {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 10px;
		padding: 26px;
		font-size: 13px;
		color: #6b7280;
	}
	div.afq-repmap div.afq-repmap__loading::before {
		content: "";
		width: 18px;
		height: 18px;
		border: 3px solid #d7dce3;
		border-top-color: #667081;
		border-radius: 50%;
		animation: afq-repmap-spin 0.8s linear infinite;
	}
	@keyframes afq-repmap-spin {
		to { transform: rotate(360deg); }
	}
	div.afq-repmap h3.afq-repmap__province-title {
		margin: 0 0 16px !important;
		padding: 10px 16px;
		font-size: 16px;
		font-weight: 700;
		color: #e7ebf0;
		background: linear-gradient(135deg, #14181f 0%, #232a36 60%, #2c3442 100%);
		border-radius: 12px;
	}
	div.afq-repmap p.afq-repmap__no-result {
		margin: 0 !important;
		padding: 18px;
		text-align: center;
		font-size: 13.5px;
		font-weight: 600;
		color: #8a5a55;
		border: 1px dashed #e2c9c5;
		border-radius: 12px;
		background: #faf4f3;
	}

	/* Cards (horizontal, stacked list) */
	div.afq-repmap div.afq-rep-cards {
		display: flex;
		flex-direction: column;
		gap: 14px;
	}
	div.afq-repmap article.afq-rep-card {
		display: flex;
		gap: 18px;
		background: #fff;
		border: 1px solid #e7ebf0;
		border-radius: 14px;
		padding: 18px 20px;
		margin: 0;
		box-shadow: 0 5px 16px rgba(15, 20, 30, 0.06);
		transition: box-shadow 0.2s ease, transform 0.2s ease;
	}
	div.afq-repmap article.afq-rep-card:hover {
		box-shadow: 0 14px 30px rgba(15, 20, 30, 0.11);
		transform: translateY(-2px);
	}
	div.afq-repmap div.afq-rep-card__main {
		flex: 1;
		min-width: 0;
	}
	div.afq-repmap div.afq-rep-card__head {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 8px;
		margin-bottom: 12px;
	}
	div.afq-repmap h4.afq-rep-card__name {
		margin: 0 !important;
		padding: 0 !important;
		font-size: 15px;
		font-weight: 700;
		color: #1f2937;
	}
	div.afq-repmap span.afq-rep-card__badge {
		display: inline-block;
		padding: 3px 10px;
		border-radius: 999px;
		background: linear-gradient(135deg, #d7dce3, #aab3bf);
		color: #14181f;
		font-size: 10.5px;
		font-weight: 700;
	}
	div.afq-repmap span.afq-rep-card__badge--grade {
		background: #eef1f5;
		color: #4b5563;
	}
	div.afq-repmap div.afq-rep-card__rows {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
		gap: 8px 22px;
	}
	div.afq-repmap div.afq-rep-card__row {
		display: flex;
		align-items: baseline;
		gap: 8px;
		font-size: 13px;
		min-width: 0;
	}
	div.afq-repmap div.afq-rep-card__row--full {
		grid-column: 1 / -1;
	}
	div.afq-repmap div.afq-rep-card__row span {
		color: #8b95a3;
		font-size: 12px;
		flex-shrink: 0;
	}
	div.afq-repmap div.afq-rep-card__row strong {
		color: #1f2937;
		font-weight: 600;
		line-height: 1.9;
		overflow-wrap: anywhere;
	}
	div.afq-repmap div.afq-rep-card__row a {
		color: #3d4756;
		text-decoration: none;
		direction: ltr;
		unicode-bidi: embed;
	}
	div.afq-repmap div.afq-rep-card__row a:hover {
		color: #14181f;
	}

	/* Mobile */
	@media (max-width: 767px) {
		div.afq-repmap span.afq-repmap__spot-dot {
			width: 13px;
			height: 13px;
		}
		div.afq-repmap span.afq-repmap__spot-label {
			font-size: 9.5px;
			padding: 3px 7px;
		}
		div.afq-repmap article.afq-rep-card {
			flex-direction: column;
			gap: 14px;
			padding: 16px;
		}
	}
	';
}

/**
 * Frontend inline JS (vanilla, no dependencies).
 *
 * @return string
 */
function afq_rep_map_inline_js() {
	return '
	( function () {
		"use strict";

		var isLoading = false;

		document.addEventListener( "click", function ( e ) {
			var spot = e.target.closest( ".afq-repmap__spot" );
			if ( ! spot || isLoading ) {
				return;
			}

			var wrap    = spot.closest( ".afq-repmap" );
			var results = wrap.querySelector( ".afq-repmap__results" );
			var termId  = spot.getAttribute( "data-afq-term" );

			wrap.querySelectorAll( ".afq-repmap__spot.is-active" ).forEach( function ( el ) {
				el.classList.remove( "is-active" );
			} );
			spot.classList.add( "is-active" );

			results.innerHTML = "<div class=\"afq-repmap__loading\">در حال دریافت نمایندگان...</div>";
			isLoading = true;

			var formData = new FormData();
			formData.append( "action", "afq_rep_filter" );
			formData.append( "nonce", afqRepMapCfg.nonce );
			formData.append( "term_id", termId );

			fetch( afqRepMapCfg.ajaxUrl, {
				method: "POST",
				credentials: "same-origin",
				body: formData
			} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( json ) {
					if ( json && json.success ) {
						results.innerHTML = json.data;
					} else {
						results.innerHTML = "<p class=\"afq-repmap__no-result\">خطا در دریافت اطلاعات. دوباره تلاش کنید.</p>";
					}
				} )
				.catch( function () {
					results.innerHTML = "<p class=\"afq-repmap__no-result\">خطا در دریافت اطلاعات. دوباره تلاش کنید.</p>";
				} )
				.finally( function () {
					isLoading = false;

					if ( window.innerWidth < 768 ) {
						results.scrollIntoView( { behavior: "smooth", block: "start" } );
					}
				} );
		} );
	} )();
	';
}