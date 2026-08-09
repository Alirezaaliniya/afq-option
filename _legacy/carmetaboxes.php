/**
 * Field definitions grouped by section (tab).
 *
 * @return array
 */
function afq_car_get_spec_sections() {
	return array(
		'engine'      => array(
			'label'  => 'پیشرانه',
			'icon'   => 'dashicons-performance',
			'fields' => array(
				'_afq_car_engine_type'    => array(
					'label'       => 'نوع موتور',
					'type'        => 'text',
					'placeholder' => 'مثال: ۴ سیلندر خطی هیبریدی ۱.۸ لیتری',
				),
				'_afq_car_engine_code'    => array(
					'label'       => 'کد موتور',
					'type'        => 'text',
					'placeholder' => 'مثال: 8AR-FTS',
				),
				'_afq_car_displacement'   => array(
					'label'       => 'حجم موتور',
					'type'        => 'text',
					'placeholder' => 'مثال: ۱۹۹۸ سی‌سی',
				),
				'_afq_car_valves'         => array(
					'label'       => 'تعداد سوپاپ',
					'type'        => 'text',
					'placeholder' => 'مثال: ۱۶ (۴ سوپاپ برای هر سیلندر)',
				),
				'_afq_car_vvt'            => array(
					'label'       => 'سیستم زمان‌بندی سوپاپ',
					'type'        => 'text',
					'placeholder' => 'مثال: Dual VVT-i',
				),
				'_afq_car_engine_power'   => array(
					'label'       => 'قدرت موتور بنزینی',
					'type'        => 'text',
					'placeholder' => 'مثال: ۹۸ اسب بخار (۷۲ کیلووات)',
				),
				'_afq_car_electric_power' => array(
					'label'       => 'قدرت موتور الکتریکی',
					'type'        => 'text',
					'placeholder' => 'مثال: ۷۰ کیلووات',
				),
				'_afq_car_total_power'    => array(
					'label'       => 'قدرت کل سیستم',
					'type'        => 'text',
					'placeholder' => 'مثال: ۱۳۷ اسب بخار (۱۰۱ کیلووات)',
				),
				'_afq_car_torque'         => array(
					'label'       => 'حداکثر گشتاور',
					'type'        => 'text',
					'placeholder' => 'مثال: ۳۵۰ نیوتن‌متر در ۱۶۵۰ تا ۴۰۰۰ دور',
				),
				'_afq_car_transmission'   => array(
					'label'       => 'گیربکس',
					'type'        => 'text',
					'placeholder' => 'مثال: E-CVT انتقال پیوسته الکترونیکی',
				),
				'_afq_car_drive_type'     => array(
					'label'   => 'سیستم انتقال قدرت',
					'type'    => 'select',
					'options' => array(
						''    => 'انتخاب کنید',
						'FWD' => 'محور جلو (FWD)',
						'RWD' => 'محور عقب (RWD)',
						'AWD' => 'تمام‌چرخ (AWD)',
						'4WD' => 'چهارچرخ محرک (4WD)',
					),
				),
			),
		),
		'performance' => array(
			'label'  => 'عملکرد و مصرف',
			'icon'   => 'dashicons-dashboard',
			'fields' => array(
				'_afq_car_acceleration'  => array(
					'label'       => 'شتاب ۰ تا ۱۰۰ کیلومتر',
					'type'        => 'text',
					'placeholder' => 'مثال: حدود ۷.۱ ثانیه',
				),
				'_afq_car_top_speed'     => array(
					'label'       => 'حداکثر سرعت',
					'type'        => 'text',
					'placeholder' => 'مثال: ۲۰۰ کیلومتر بر ساعت',
				),
				'_afq_car_fuel_combined' => array(
					'label'       => 'مصرف سوخت ترکیبی',
					'type'        => 'text',
					'placeholder' => 'مثال: ۴.۸ لیتر در ۱۰۰ کیلومتر',
				),
				'_afq_car_fuel_city'     => array(
					'label'       => 'مصرف سوخت شهری',
					'type'        => 'text',
					'placeholder' => 'مثال: ۵.۵ لیتر در ۱۰۰ کیلومتر',
				),
				'_afq_car_fuel_highway'  => array(
					'label'       => 'مصرف سوخت جاده‌ای',
					'type'        => 'text',
					'placeholder' => 'مثال: ۴.۵ لیتر در ۱۰۰ کیلومتر',
				),
				'_afq_car_emission'      => array(
					'label'       => 'استاندارد آلایندگی',
					'type'        => 'text',
					'placeholder' => 'مثال: Euro 6',
				),
				'_afq_car_battery_type'  => array(
					'label'       => 'نوع باتری',
					'type'        => 'text',
					'placeholder' => 'مثال: لیتیوم-یون BYD',
				),
				'_afq_car_battery_port'  => array(
					'label'       => 'درگاه شارژ / باتری',
					'type'        => 'text',
					'placeholder' => 'مثال: USB Type-C',
				),
				'_afq_car_parking_brake' => array(
					'label'   => 'ترمز پارک الکترونیکی',
					'type'    => 'select',
					'options' => array(
						''    => 'انتخاب کنید',
						'yes' => 'دارد',
						'no'  => 'ندارد',
					),
				),
			),
		),
		'dimensions'  => array(
			'label'  => 'ابعاد و وزن',
			'icon'   => 'dashicons-editor-expand',
			'fields' => array(
				'_afq_car_length'    => array(
					'label'       => 'طول',
					'type'        => 'text',
					'placeholder' => 'مثال: ۴,۶۴۰ میلی‌متر',
				),
				'_afq_car_width'     => array(
					'label'       => 'عرض',
					'type'        => 'text',
					'placeholder' => 'مثال: ۱,۸۵۰ میلی‌متر',
				),
				'_afq_car_height'    => array(
					'label'       => 'ارتفاع',
					'type'        => 'text',
					'placeholder' => 'مثال: ۱,۷۰۵ میلی‌متر',
				),
				'_afq_car_wheelbase' => array(
					'label'       => 'فاصله بین محورها',
					'type'        => 'text',
					'placeholder' => 'مثال: ۲,۷۹۰ میلی‌متر',
				),
				'_afq_car_weight'    => array(
					'label'       => 'وزن خالص',
					'type'        => 'text',
					'placeholder' => 'مثال: حدود ۱,۷۰۰ کیلوگرم',
				),
				'_afq_car_fuel_tank' => array(
					'label'       => 'ظرفیت باک',
					'type'        => 'text',
					'placeholder' => 'مثال: ۵۵ لیتر',
				),
				'_afq_car_trunk'     => array(
					'label'       => 'ظرفیت صندوق عقب',
					'type'        => 'text',
					'placeholder' => 'مثال: حدود ۵۸۰ لیتر',
				),
			),
		),
		'features'    => array(
			'label'  => 'امکانات',
			'icon'   => 'dashicons-star-filled',
			'fields' => array(
				'_afq_car_features' => array(
					'label'       => 'تجهیزات برجسته',
					'type'        => 'textarea',
					'placeholder' => "هر مورد در یک خط:\nToyota Safety Sense 3.0\nشارژر بی‌سیم\nسیستم تهویه مطبوع خودکار",
				),
			),
		),
	);
}
 
/**
 * Flat list of all fields.
 *
 * @return array
 */
function afq_car_get_all_spec_fields() {
	$fields = array();
	foreach ( afq_car_get_spec_sections() as $section ) {
		$fields = array_merge( $fields, $section['fields'] );
	}
	return $fields;
}
 
/**
 * Register meta keys (REST-ready for Elementor dynamic tags).
 */
function afq_car_register_spec_meta() {
	foreach ( afq_car_get_all_spec_fields() as $key => $field ) {
		register_post_meta(
			'afq_car',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => ( 'textarea' === $field['type'] ) ? 'sanitize_textarea_field' : 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'afq_car_register_spec_meta' );
 
/**
 * Add the meta box.
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
 * Render the meta box.
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
 * Save meta values.
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
 
/**
 * Admin styles + tab script (only on afq_car edit screen).
 *
 * @param string $hook Current admin page hook.
 */
function afq_car_specs_admin_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}
 
	$screen = get_current_screen();
	if ( ! $screen || 'afq_car' !== $screen->post_type ) {
		return;
	}
 
	wp_register_style( 'afq-car-specs', false, array(), '1.0.0' );
	wp_enqueue_style( 'afq-car-specs' );
	wp_add_inline_style( 'afq-car-specs', afq_car_specs_inline_css() );
 
	wp_register_script( 'afq-car-specs', false, array(), '1.0.0', true );
	wp_enqueue_script( 'afq-car-specs' );
	wp_add_inline_script( 'afq-car-specs', afq_car_specs_inline_js() );
}
add_action( 'admin_enqueue_scripts', 'afq_car_specs_admin_assets' );
 
/**
 * Inline CSS for the meta box.
 *
 * @return string
 */
function afq_car_specs_inline_css() {
	return '
	#afq_car_specs.postbox {
		border: none;
		border-radius: 14px;
		overflow: hidden;
		box-shadow: 0 10px 30px rgba(15, 20, 30, 0.08);
	}
	#afq_car_specs .postbox-header {
		background: #ffffff;
		border-bottom: 1px solid #eef0f3;
	}
	#afq_car_specs .inside {
		margin: 0;
		padding: 0;
	}
 
	.afq-specs {
		background: #fbfbfc;
		font-family: inherit;
	}
 
	/* Header */
	.afq-specs__header {
		display: flex;
		align-items: center;
		gap: 16px;
		padding: 22px 24px;
		background: linear-gradient(135deg, #14181f 0%, #232a36 60%, #2c3442 100%);
		color: #fff;
	}
	.afq-specs__header .dashicons {
		width: 44px;
		height: 44px;
		font-size: 26px;
		line-height: 44px;
		text-align: center;
		border-radius: 12px;
		background: linear-gradient(135deg, #d8b46a, #b8934a);
		color: #14181f;
		flex-shrink: 0;
	}
	.afq-specs__header strong {
		display: block;
		font-size: 15px;
		letter-spacing: 0.2px;
	}
	.afq-specs__header p {
		margin: 4px 0 0;
		font-size: 12px;
		color: rgba(255, 255, 255, 0.65);
	}
 
	/* Tabs */
	.afq-specs__tabs {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		padding: 14px 24px 0;
		background: #fbfbfc;
	}
	.afq-specs__tab {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 9px 16px;
		border: 1px solid #e4e7ec;
		border-radius: 999px;
		background: #fff;
		color: #4b5563;
		font-size: 12.5px;
		cursor: pointer;
		transition: all 0.18s ease;
	}
	.afq-specs__tab .dashicons {
		font-size: 16px;
		width: 16px;
		height: 16px;
		line-height: 16px;
	}
	.afq-specs__tab:hover {
		border-color: #c9a45c;
		color: #1f2937;
	}
	.afq-specs__tab.is-active {
		background: #1a1f29;
		border-color: #1a1f29;
		color: #e8cf9a;
		box-shadow: 0 6px 16px rgba(26, 31, 41, 0.25);
	}
 
	/* Panels */
	.afq-specs__panel {
		display: none;
		padding: 22px 24px 26px;
	}
	.afq-specs__panel.is-active {
		display: block;
	}
 
	/* Fields grid */
	.afq-specs__grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
		gap: 16px;
	}
	.afq-specs__field {
		display: flex;
		flex-direction: column;
		gap: 6px;
	}
	.afq-specs__field--wide {
		grid-column: 1 / -1;
	}
	.afq-specs__field label {
		font-size: 12px;
		font-weight: 600;
		color: #374151;
	}
	.afq-specs__field input[type="text"],
	.afq-specs__field select,
	.afq-specs__field textarea {
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
	.afq-specs__field input[type="text"]:focus,
	.afq-specs__field select:focus,
	.afq-specs__field textarea:focus {
		border-color: #c9a45c;
		box-shadow: 0 0 0 3px rgba(201, 164, 92, 0.18);
		outline: none;
	}
	.afq-specs__field input::placeholder,
	.afq-specs__field textarea::placeholder {
		color: #9ca3af;
	}
	.afq-specs__field textarea {
		resize: vertical;
		min-height: 120px;
		line-height: 1.8;
	}
	';
}
 
/**
 * Inline JS for tab switching (vanilla, no dependencies).
 *
 * @return string
 */
function afq_car_specs_inline_js() {
	return '
	document.addEventListener("DOMContentLoaded", function () {
		var box = document.querySelector(".afq-specs");
		if (!box) {
			return;
		}
 
		var tabs   = box.querySelectorAll(".afq-specs__tab");
		var panels = box.querySelectorAll(".afq-specs__panel");
 
		tabs.forEach(function (tab) {
			tab.addEventListener("click", function () {
				var target = tab.getAttribute("data-afq-tab");
 
				tabs.forEach(function (t) {
					t.classList.toggle("is-active", t === tab);
				});
				panels.forEach(function (p) {
					p.classList.toggle("is-active", p.getAttribute("data-afq-panel") === target);
				});
			});
		});
	});
	';
}



/**
 * AFQ Car — Details Meta Box (Catalog / Prices / Video)
 * Add this code to functions.php AFTER the media meta code (afq-car-media-meta).
 * Nothing in the previous blocks needs to change.
 *
 * Meta keys:
 *   _afq_car_price_regular  (text)
 *   _afq_car_price_sale     (text)
 *   _afq_car_catalog        (attachment ID — any file type, e.g. PDF)
 *   _afq_car_video          (URL — uploaded video from media library or external link)
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Meta Box
 * ---------------------------------------------------------------------- */

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

/* -------------------------------------------------------------------------
 * Admin Assets (appended to the existing afq-car-media handles)
 * ---------------------------------------------------------------------- */

/**
 * Append details CSS/JS to the media handles (runs after afq_car_admin_assets).
 *
 * @param string $hook Current admin page hook.
 */
function afq_car_details_admin_assets( $hook ) {

	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'afq_car' !== $screen->post_type ) {
		return;
	}

	if ( ! wp_style_is( 'afq-car-media', 'enqueued' ) ) {
		return;
	}

	wp_add_inline_style( 'afq-car-media', afq_car_details_inline_css() );
	wp_add_inline_script( 'afq-car-media', afq_car_details_inline_js() );
}
add_action( 'admin_enqueue_scripts', 'afq_car_details_admin_assets', 20 );

/**
 * Inline CSS for the details meta box.
 *
 * @return string
 */
function afq_car_details_inline_css() {
	return '
	#afq_car_details.postbox {
		border: none;
		border-radius: 14px;
		overflow: hidden;
		box-shadow: 0 10px 30px rgba(15, 20, 30, 0.08);
	}
	#afq_car_details .postbox-header {
		background: linear-gradient(135deg, #14181f 0%, #232a36 60%, #2c3442 100%);
		border-bottom: none;
	}
	#afq_car_details .postbox-header .hndle {
		color: #e8cf9a;
		font-size: 13px;
	}
	#afq_car_details .postbox-header .handle-actions button {
		color: rgba(255, 255, 255, 0.7);
	}
	#afq_car_details .inside {
		margin: 0;
		padding: 16px;
		background: #fbfbfc;
	}

	.afq-details__grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
		gap: 14px;
	}
	.afq-details .afq-media-card {
		margin-bottom: 0;
	}
	.afq-details__input {
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
	.afq-details__input:focus {
		border-color: #c9a45c;
		box-shadow: 0 0 0 3px rgba(201, 164, 92, 0.18);
		outline: none;
	}
	.afq-details__input::placeholder {
		color: #9ca3af;
	}
	.afq-video-field .afq-details__input {
		margin-bottom: 10px;
		direction: ltr;
		text-align: left;
	}

	/* Catalog file box */
	.afq-file-box {
		display: flex;
		align-items: center;
		gap: 10px;
		border: 1px dashed #d9dde3;
		border-radius: 10px;
		background: #f6f7f9;
		padding: 12px;
		margin-bottom: 10px;
	}
	.afq-file-box .dashicons {
		color: #c3c9d1;
		font-size: 24px;
		width: 24px;
		height: 24px;
		flex-shrink: 0;
	}
	.afq-file-box .afq-file-name {
		font-size: 12px;
		color: #9ca3af;
		word-break: break-all;
	}
	.afq-file-box.has-file {
		border-style: solid;
		background: #fff;
	}
	.afq-file-box.has-file .dashicons {
		color: #b8934a;
	}
	.afq-file-box.has-file .afq-file-name {
		color: #1f2937;
		font-weight: 600;
	}
	';
}

/**
 * Inline JS for catalog file + video pickers.
 *
 * @return string
 */
function afq_car_details_inline_js() {
	return <<<'JS'
( function( $ ) {
	'use strict';

	/* ---------------- Catalog file ---------------- */

	$( document ).on( 'click', '.afq-file-upload', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-file-field' );
		var $input   = $wrapper.find( '.afq-file-id' );
		var $box     = $wrapper.find( '.afq-file-box' );

		var frame = wp.media( {
			title: 'انتخاب فایل کاتالوگ',
			button: { text: 'استفاده از این فایل' },
			multiple: false
		} );

		frame.on( 'select', function() {
			var attachment = frame.state().get( 'selection' ).first().toJSON();

			$input.val( attachment.id );
			$box.addClass( 'has-file' ).find( '.afq-file-name' ).text( attachment.filename || attachment.title );
			$wrapper.find( '.afq-file-remove' ).show();
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.afq-file-remove', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-file-field' );

		$wrapper.find( '.afq-file-id' ).val( '' );
		$wrapper.find( '.afq-file-box' ).removeClass( 'has-file' ).find( '.afq-file-name' ).text( 'فایلی انتخاب نشده' );
		$( this ).hide();
	} );

	/* ---------------- Video ---------------- */

	$( document ).on( 'click', '.afq-video-select', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-video-field' );
		var $input   = $wrapper.find( '.afq-video-url' );

		var frame = wp.media( {
			title: 'انتخاب ویدیوی معرفی',
			button: { text: 'استفاده از این ویدیو' },
			library: { type: 'video' },
			multiple: false
		} );

		frame.on( 'select', function() {
			var attachment = frame.state().get( 'selection' ).first().toJSON();

			$input.val( attachment.url );
			$wrapper.find( '.afq-video-clear' ).show();
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.afq-video-clear', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-video-field' );

		$wrapper.find( '.afq-video-url' ).val( '' );
		$( this ).hide();
	} );

	$( document ).on( 'input', '.afq-video-url', function() {
		var $wrapper = $( this ).closest( '.afq-video-field' );
		$wrapper.find( '.afq-video-clear' ).toggle( '' !== $.trim( $( this ).val() ) );
	} );

} )( jQuery );
JS;
}

/* -------------------------------------------------------------------------
 * Helper Functions
 * ---------------------------------------------------------------------- */

/**
 * Get car price.
 *
 * @param int    $post_id Post ID.
 * @param string $type    Price type: regular|sale.
 * @return string
 */
function afq_get_car_price( $post_id = 0, $type = 'regular' ) {

	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$key     = ( 'sale' === $type ) ? '_afq_car_price_sale' : '_afq_car_price_regular';

	return (string) get_post_meta( $post_id, $key, true );
}

/**
 * Get car catalog file URL.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function afq_get_car_catalog_url( $post_id = 0 ) {

	$post_id       = $post_id ? absint( $post_id ) : get_the_ID();
	$attachment_id = absint( get_post_meta( $post_id, '_afq_car_catalog', true ) );

	if ( ! $attachment_id ) {
		return '';
	}

	$url = wp_get_attachment_url( $attachment_id );

	return $url ? $url : '';
}

/**
 * Get car intro video URL.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function afq_get_car_video_url( $post_id = 0 ) {

	$post_id = $post_id ? absint( $post_id ) : get_the_ID();

	return esc_url( get_post_meta( $post_id, '_afq_car_video', true ) );
}

/* -------------------------------------------------------------------------
 * Elementor Dynamic Tags
 * ---------------------------------------------------------------------- */

/**
 * Register details dynamic tags for Elementor.
 *
 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager Dynamic tags manager.
 */
function afq_register_car_details_dynamic_tags( $dynamic_tags_manager ) {

	if ( ! class_exists( '\Elementor\Core\DynamicTags\Tag' ) ) {
		return;
	}

	/**
	 * Base class for text meta dynamic tags.
	 */
	abstract class AFQ_Car_Meta_Text_Tag_Base extends \Elementor\Core\DynamicTags\Tag {

		/**
		 * Get the post meta key.
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
			return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
		}

		/**
		 * Render tag output.
		 */
		public function render() {
			echo esc_html( get_post_meta( get_the_ID(), $this->get_meta_key(), true ) );
		}
	}

	/**
	 * Regular price dynamic tag.
	 */
	class AFQ_Car_Price_Regular_Tag extends AFQ_Car_Meta_Text_Tag_Base {

		/**
		 * Tag name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'afq-car-price-regular';
		}

		/**
		 * Tag title.
		 *
		 * @return string
		 */
		public function get_title() {
			return 'قیمت عادی ماشین';
		}

		/**
		 * Meta key.
		 *
		 * @return string
		 */
		protected function get_meta_key() {
			return '_afq_car_price_regular';
		}
	}

	/**
	 * Sale price dynamic tag.
	 */
	class AFQ_Car_Price_Sale_Tag extends AFQ_Car_Meta_Text_Tag_Base {

		/**
		 * Tag name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'afq-car-price-sale';
		}

		/**
		 * Tag title.
		 *
		 * @return string
		 */
		public function get_title() {
			return 'قیمت فروش ویژه ماشین';
		}

		/**
		 * Meta key.
		 *
		 * @return string
		 */
		protected function get_meta_key() {
			return '_afq_car_price_sale';
		}
	}

	/**
	 * Catalog file URL dynamic tag.
	 */
	class AFQ_Car_Catalog_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

		/**
		 * Tag name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'afq-car-catalog';
		}

		/**
		 * Tag title.
		 *
		 * @return string
		 */
		public function get_title() {
			return 'کاتالوگ ماشین';
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
			return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
		}

		/**
		 * Get catalog URL.
		 *
		 * @param array $options Options.
		 * @return string
		 */
		public function get_value( array $options = array() ) {
			return afq_get_car_catalog_url( get_the_ID() );
		}
	}

	/**
	 * Intro video URL dynamic tag.
	 */
	class AFQ_Car_Video_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

		/**
		 * Tag name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'afq-car-video';
		}

		/**
		 * Tag title.
		 *
		 * @return string
		 */
		public function get_title() {
			return 'ویدیوی معرفی ماشین';
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
			return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
		}

		/**
		 * Get video URL.
		 *
		 * @param array $options Options.
		 * @return string
		 */
		public function get_value( array $options = array() ) {
			return afq_get_car_video_url( get_the_ID() );
		}
	}

	$dynamic_tags_manager->register( new AFQ_Car_Price_Regular_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Price_Sale_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Catalog_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Video_Tag() );
}
add_action( 'elementor/dynamic_tags/register', 'afq_register_car_details_dynamic_tags' );