<?php
/**
 * Customer Voice (صدای مشتری) configuration.
 *
 * Field definitions, statuses, request types and the plugin settings that
 * drive the [afq_request_form] / [afq_request_track] shortcodes.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Option holding the field keys the admin marked as optional.
 */
const AFQ_REQUEST_OPTIONAL_OPTION = 'afq_request_optional_fields';

/**
 * Option holding the module settings (emails, uploads, messages).
 */
const AFQ_REQUEST_SETTINGS_OPTION = 'afq_request_settings';

/**
 * Option holding the running tracking-code counter.
 */
const AFQ_REQUEST_COUNTER_OPTION = 'afq_request_code_counter';

/* -------------------------------------------------------------------------
 * Statuses
 * ---------------------------------------------------------------------- */

/**
 * Request statuses shown in the dashboard and to the customer.
 *
 * @return array
 */
function afq_request_get_statuses() {
	return array(
		'new'       => array(
			'label' => 'ثبت شده',
			'color' => '#1a5fb0',
			'bg'    => '#e3edfb',
		),
		'reviewing' => array(
			'label' => 'در حال بررسی',
			'color' => '#b07d1a',
			'bg'    => '#fdf3dd',
		),
		'referred'  => array(
			'label' => 'ارجاع به واحد مربوطه',
			'color' => '#5b4bb0',
			'bg'    => '#ece9fb',
		),
		'waiting'   => array(
			'label' => 'در انتظار پاسخ مشتری',
			'color' => '#8a5a55',
			'bg'    => '#faf4f3',
		),
		'answered'  => array(
			'label' => 'پاسخ داده شده',
			'color' => '#1f7a4d',
			'bg'    => '#e5f5ec',
		),
		'closed'    => array(
			'label' => 'بسته شده',
			'color' => '#4b5563',
			'bg'    => '#eef1f5',
		),
		'rejected'  => array(
			'label' => 'رد شده',
			'color' => '#a04a41',
			'bg'    => '#f5e7e5',
		),
	);
}

/**
 * Request types offered in the form.
 *
 * @return string[]
 */
function afq_request_get_types() {
	return array(
		'شکایت',
		'پیشنهاد',
		'انتقاد',
		'تشکر',
		'درخواست گارانتی',
		'خدمات پس از فروش',
		'قطعات یدکی',
		'سایر',
	);
}

/**
 * Preferred answer channels.
 *
 * @return string[]
 */
function afq_request_get_contact_methods() {
	return array( 'تماس تلفنی', 'پیامک', 'واتساپ', 'ایمیل' );
}

/**
 * Car production years offered in the form.
 *
 * @return string[]
 */
function afq_request_get_car_years() {

	$years = array();
	$now   = (int) gmdate( 'Y' ) + 1;

	for ( $y = $now; $y >= 1995; $y-- ) {
		$years[] = (string) $y;
	}

	return $years;
}

/* -------------------------------------------------------------------------
 * Data pulled from the site's own content
 * ---------------------------------------------------------------------- */

/**
 * Brand => car models, taken from the afq_car post type and its taxonomy.
 *
 * Cached for a day; the cache is dropped whenever a car or a car category
 * is edited (see afq_request_flush_car_cache).
 *
 * @return array<string,string[]>
 */
function afq_request_get_brand_models() {

	static $map = null;

	if ( null !== $map ) {
		return $map;
	}

	$cached = get_transient( 'afq_request_brand_models' );

	if ( is_array( $cached ) ) {
		$map = $cached;
		return $map;
	}

	$map = array();

	$cars = get_posts(
		array(
			'post_type'              => 'afq_car',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
		)
	);

	foreach ( $cars as $car ) {

		$names = wp_get_post_terms( $car->ID, 'afq_car_cat', array( 'fields' => 'names' ) );

		if ( is_wp_error( $names ) || ! $names ) {
			$names = array( 'سایر' );
		}

		foreach ( $names as $name ) {
			$map[ $name ][] = $car->post_title;
		}
	}

	foreach ( $map as $brand => $models ) {
		$models = array_values( array_unique( $models ) );
		sort( $models );
		$map[ $brand ] = $models;
	}

	ksort( $map );

	set_transient( 'afq_request_brand_models', $map, DAY_IN_SECONDS );

	return $map;
}

/**
 * Brand names only.
 *
 * @return string[]
 */
function afq_request_get_brands() {
	return array_keys( afq_request_get_brand_models() );
}

/**
 * Dealership names, taken from the afq_rep post type.
 *
 * @return string[]
 */
function afq_request_get_dealerships() {

	static $list = null;

	if ( null !== $list ) {
		return $list;
	}

	$cached = get_transient( 'afq_request_dealerships' );

	if ( is_array( $cached ) ) {
		$list = $cached;
		return $list;
	}

	$reps = get_posts(
		array(
			'post_type'              => 'afq_rep',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$list = array();

	foreach ( $reps as $rep ) {
		$list[] = $rep->post_title;
	}

	set_transient( 'afq_request_dealerships', $list, DAY_IN_SECONDS );

	return $list;
}

/**
 * Drop the cached brand/model and dealership lists.
 */
function afq_request_flush_car_cache() {
	delete_transient( 'afq_request_brand_models' );
	delete_transient( 'afq_request_dealerships' );
}
add_action( 'save_post_afq_car', 'afq_request_flush_car_cache' );
add_action( 'deleted_post', 'afq_request_flush_car_cache' );
add_action( 'save_post_afq_rep', 'afq_request_flush_car_cache' );
add_action( 'edited_afq_car_cat', 'afq_request_flush_car_cache' );
add_action( 'created_afq_car_cat', 'afq_request_flush_car_cache' );

/* -------------------------------------------------------------------------
 * Required / optional
 * ---------------------------------------------------------------------- */

/**
 * Fields that are optional out of the box.
 *
 * Mirrors the asterisks in the approved design: only name, mobile, province,
 * city and the description start out as required.
 *
 * @return string[]
 */
function afq_request_default_optional_fields() {
	return array(
		'email', 'national_id',
		'car_brand', 'car_model', 'car_year', 'vin', 'plate', 'mileage',
		'dealership', 'visit_date', 'reception_no',
		'request_type', 'subject', 'contact_methods',
	);
}

/**
 * Field keys currently marked optional.
 *
 * @return string[]
 */
function afq_request_get_optional_fields() {

	$saved = get_option( AFQ_REQUEST_OPTIONAL_OPTION, false );

	/* Never saved yet: fall back to the design's defaults. */
	if ( false === $saved ) {
		return afq_request_default_optional_fields();
	}

	return is_array( $saved ) ? array_values( array_map( 'strval', $saved ) ) : array();
}

/**
 * Whether a request field must be filled in.
 *
 * @param string $key Field key.
 * @return bool
 */
function afq_request_is_field_required( $key ) {
	return ! in_array( (string) $key, afq_request_get_optional_fields(), true );
}

/* -------------------------------------------------------------------------
 * Settings
 * ---------------------------------------------------------------------- */

/**
 * Module settings merged over their defaults.
 *
 * @return array
 */
function afq_request_get_settings() {

	$defaults = array(
		'notify_enabled'  => 1,
		'notify_emails'   => get_option( 'admin_email' ),
		'notify_subject'  => 'درخواست جدید صدای مشتری — {code}',
		'ack_enabled'     => 1,
		'ack_subject'     => 'ثبت درخواست شما در آفاق موتور ایرانیان — {code}',
		'ack_message'     => "{name} عزیز،\nدرخواست شما با موفقیت ثبت شد و کد رهگیری آن {code} است.\nکارشناسان مرکز ارتباط با مشتریان حداکثر تا ۲۴ ساعت کاری آینده با شما تماس خواهند گرفت.",
		'success_title'   => 'درخواست شما با موفقیت ثبت شد',
		'success_message' => 'با تشکر از اعتماد شما به آفاق موتور ایرانیان. کارشناسان مرکز ارتباط با مشتریان حداکثر تا ۲۴ ساعت کاری آینده با شما تماس خواهند گرفت.',
		'terms_enabled'   => 1,
		'terms_required'  => 1,
		'terms_text'      => 'قوانین و شرایط استفاده و حفظ حریم خصوصی را مطالعه کرده و می‌پذیرم.',
		'terms_url'       => '',
		'upload_enabled'  => 1,
		'upload_exts'     => 'jpg,jpeg,png,pdf,mp4',
		'upload_max_mb'   => 10,
		'desc_min'        => 100,
		'desc_max'        => 1000,
	);

	$saved = get_option( AFQ_REQUEST_SETTINGS_OPTION, array() );

	return array_merge( $defaults, is_array( $saved ) ? $saved : array() );
}

/**
 * Allowed upload extensions as a clean lowercase list.
 *
 * @return string[]
 */
function afq_request_get_allowed_extensions() {

	$settings = afq_request_get_settings();
	$raw      = explode( ',', (string) $settings['upload_exts'] );

	$exts = array();

	foreach ( $raw as $ext ) {
		$ext = strtolower( trim( $ext, " \t\n\r\0\x0B." ) );

		/* Only ever allow plain alphanumeric extensions. */
		if ( '' !== $ext && preg_match( '/^[a-z0-9]{1,5}$/', $ext ) ) {
			$exts[] = $ext;
		}
	}

	return array_values( array_unique( $exts ) );
}

/* -------------------------------------------------------------------------
 * Field definitions
 * ---------------------------------------------------------------------- */

/**
 * Form field definitions grouped by section.
 *
 * @return array
 */
function afq_request_get_sections() {

	static $sections = null;

	if ( null !== $sections ) {
		return $sections;
	}

	$settings = afq_request_get_settings();

	$sections = array(
		'customer' => array(
			'label'  => 'اطلاعات مشتری',
			'icon'   => 'user',
			'fields' => array(
				'full_name'   => array(
					'label'       => 'نام و نام خانوادگی',
					'type'        => 'text',
					'placeholder' => 'نام و نام خانوادگی خود را وارد کنید',
				),
				'mobile'      => array(
					'label'       => 'شماره موبایل',
					'type'        => 'text',
					'validate'    => 'mobile',
					'ltr'         => true,
					'placeholder' => '09121234567',
				),
				'email'       => array(
					'label'       => 'ایمیل',
					'type'        => 'text',
					'validate'    => 'email',
					'ltr'         => true,
					'placeholder' => 'example@email.com',
				),
				'national_id' => array(
					'label'       => 'کد ملی',
					'type'        => 'text',
					'validate'    => 'national_id',
					'ltr'         => true,
					'placeholder' => 'کد ملی خود را وارد کنید',
				),
				'province'    => array(
					'label'   => 'استان',
					'type'    => 'select',
					'options' => afq_signup_get_provinces(),
				),
				'city'        => array(
					'label'   => 'شهر',
					'type'    => 'text',
					'city_of' => 'province',
				),
			),
		),
		'car'      => array(
			'label'  => 'اطلاعات خودرو',
			'icon'   => 'car',
			'fields' => array(
				'car_brand' => array(
					'label'   => 'برند خودرو',
					'type'    => 'select',
					'options' => afq_request_get_brands(),
				),
				'car_model' => array(
					'label'    => 'مدل خودرو',
					'type'     => 'select',
					'options'  => array(),
					'model_of' => 'car_brand',
				),
				'car_year'  => array(
					'label'   => 'سال تولید',
					'type'    => 'select',
					'options' => afq_request_get_car_years(),
				),
				'vin'       => array(
					'label'       => 'شماره شاسی (VIN)',
					'type'        => 'text',
					'validate'    => 'vin',
					'ltr'         => true,
					'placeholder' => 'شماره شاسی ۱۷ رقمی',
				),
				'plate'     => array(
					'label'       => 'شماره پلاک',
					'type'        => 'text',
					'placeholder' => 'مثال: ۱۲۳ ب ۴۵ ایران ۱۲',
				),
				'mileage'   => array(
					'label'       => 'کارکرد خودرو (کیلومتر)',
					'type'        => 'text',
					'validate'    => 'number',
					'ltr'         => true,
					'placeholder' => 'مثال: 25000',
				),
			),
		),
		'visit'    => array(
			'label'  => 'اطلاعات مراجعه',
			'icon'   => 'pin',
			'fields' => array(
				'dealership'   => array(
					'label'   => 'نمایندگی مراجعه شده',
					'type'    => 'select',
					'options' => afq_request_get_dealerships(),
				),
				'visit_date'   => array(
					'label'       => 'تاریخ مراجعه',
					'type'        => 'text',
					'validate'    => 'jalali_date',
					'ltr'         => true,
					'jalali'      => true,
					'min_year'    => 1380,
					'placeholder' => 'انتخاب تاریخ',
				),
				'reception_no' => array(
					'label'       => 'شماره پذیرش',
					'type'        => 'text',
					'ltr'         => true,
					'placeholder' => 'شماره پذیرش نمایندگی',
				),
			),
		),
		'request'  => array(
			'label'  => 'نوع درخواست',
			'icon'   => 'chat',
			'fields' => array(
				'request_type'    => array(
					'label'   => 'نوع درخواست',
					'type'    => 'select',
					'options' => afq_request_get_types(),
				),
				'subject'         => array(
					'label'       => 'موضوع درخواست',
					'type'        => 'text',
					'placeholder' => 'موضوع درخواست خود را وارد کنید',
				),
				'description'     => array(
					'label'       => 'شرح کامل درخواست',
					'type'        => 'textarea',
					'wide'        => true,
					'min'         => (int) $settings['desc_min'],
					'max'         => (int) $settings['desc_max'],
					'placeholder' => sprintf( 'لطفا درخواست خود را به طور کامل توضیح دهید (حداقل %d کاراکتر)', (int) $settings['desc_min'] ),
				),
				'contact_methods' => array(
					'label'   => 'روش پاسخگویی مورد نظر',
					'type'    => 'checkboxes',
					'wide'    => true,
					'options' => afq_request_get_contact_methods(),
				),
			),
		),
	);

	/* Merge the admin's required/optional choice into every field. */
	$optional = afq_request_get_optional_fields();

	foreach ( $sections as $section_id => $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$sections[ $section_id ]['fields'][ $key ]['required'] = ! in_array( (string) $key, $optional, true );
		}
	}

	return $sections;
}

/**
 * Flat request field list.
 *
 * @return array
 */
function afq_request_get_fields() {

	static $fields = null;

	if ( null !== $fields ) {
		return $fields;
	}

	$fields = array();

	foreach ( afq_request_get_sections() as $section ) {
		$fields = array_merge( $fields, $section['fields'] );
	}

	return $fields;
}

/* -------------------------------------------------------------------------
 * Tracking code
 * ---------------------------------------------------------------------- */

/**
 * Build the next tracking code, e.g. VOC-20260805-001254.
 *
 * @return string
 */
function afq_request_generate_code() {

	$counter = (int) get_option( AFQ_REQUEST_COUNTER_OPTION, 0 );
	$counter++;

	update_option( AFQ_REQUEST_COUNTER_OPTION, $counter, false );

	return sprintf( 'VOC-%s-%06d', gmdate( 'Ymd', current_time( 'timestamp' ) ), $counter ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
}
