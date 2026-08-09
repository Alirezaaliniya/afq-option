<?php
/**
 * Field, section and option definitions.
 *
 * These arrays are constant per request but are read many times (meta box
 * render, save, shortcode, AJAX validation, notification email), so each
 * builder memoizes its result.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Car — Technical Specs
 * ---------------------------------------------------------------------- */

/**
 * Field definitions grouped by section (tab).
 *
 * @return array
 */
function afq_car_get_spec_sections() {

	static $sections = null;

	if ( null !== $sections ) {
		return $sections;
	}

	$sections = array(
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

	return $sections;
}

/**
 * Flat list of all spec fields.
 *
 * @return array
 */
function afq_car_get_all_spec_fields() {

	static $fields = null;

	if ( null !== $fields ) {
		return $fields;
	}

	$fields = array();

	foreach ( afq_car_get_spec_sections() as $section ) {
		$fields = array_merge( $fields, $section['fields'] );
	}

	return $fields;
}

/* -------------------------------------------------------------------------
 * Representatives
 * ---------------------------------------------------------------------- */

/**
 * Representative field definitions.
 *
 * @return array
 */
function afq_rep_get_fields() {

	static $fields = null;

	if ( null !== $fields ) {
		return $fields;
	}

	$fields = array(
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

	return $fields;
}

/* -------------------------------------------------------------------------
 * Signup Form
 * ---------------------------------------------------------------------- */

/**
 * Submission statuses.
 *
 * @return array
 */
function afq_signup_get_statuses() {
	return array(
		'pending'   => array(
			'label' => 'در انتظار بررسی',
			'color' => '#b07d1a',
			'bg'    => '#fdf3dd',
		),
		'reviewing' => array(
			'label' => 'در حال بررسی',
			'color' => '#1a5fb0',
			'bg'    => '#e3edfb',
		),
		'approved'  => array(
			'label' => 'تایید شده',
			'color' => '#1f7a4d',
			'bg'    => '#e5f5ec',
		),
		'rejected'  => array(
			'label' => 'رد شده',
			'color' => '#a04a41',
			'bg'    => '#f5e7e5',
		),
	);
}

/**
 * Iran provinces list.
 *
 * Derived from the city map keys so the two can never drift apart.
 *
 * @return string[]
 */
function afq_signup_get_provinces() {
	return array_keys( afq_signup_get_cities() );
}

/**
 * Option name holding the field keys the admin marked as optional.
 */
const AFQ_SIGNUP_OPTIONAL_OPTION = 'afq_signup_optional_fields';

/**
 * Field keys currently marked optional in the settings screen.
 *
 * Default is an empty list — i.e. every field is required, matching the
 * behaviour the form had before the setting existed.
 *
 * @return string[]
 */
function afq_signup_get_optional_fields() {

	$optional = get_option( AFQ_SIGNUP_OPTIONAL_OPTION, array() );

	return is_array( $optional ) ? array_values( array_filter( array_map( 'strval', $optional ) ) ) : array();
}

/**
 * Whether a signup field must be filled in.
 *
 * @param string $key Field key.
 * @return bool
 */
function afq_signup_is_field_required( $key ) {
	return ! in_array( (string) $key, afq_signup_get_optional_fields(), true );
}

/**
 * Form field definitions grouped by section. All fields are required.
 *
 * validate: required (default) | digits | national_id | mobile | phone
 *           | email | postal | sheba | jalali_date
 *
 * @return array
 */
function afq_signup_get_sections() {

	static $sections = null;

	if ( null !== $sections ) {
		return $sections;
	}

	$provinces = afq_signup_get_provinces();

	$sections = array(
		'identity'  => array(
			'label'  => 'اطلاعات هویتی',
			'fields' => array(
				'national_id' => array(
					'label'       => 'کد ملی',
					'type'        => 'text',
					'validate'    => 'national_id',
					'ltr'         => true,
					'placeholder' => 'مثال: 0012345678',
				),
				'first_name'  => array(
					'label' => 'نام',
					'type'  => 'text',
				),
				'last_name'   => array(
					'label' => 'نام خانوادگی',
					'type'  => 'text',
				),
				'father_name' => array(
					'label' => 'نام پدر',
					'type'  => 'text',
				),
				'id_number'   => array(
					'label'    => 'شماره شناسنامه',
					'type'     => 'text',
					'validate' => 'digits',
					'ltr'      => true,
				),
				'id_serial'   => array(
					'label'       => 'سریال شناسنامه',
					'type'        => 'text',
					'placeholder' => 'مثال: الف/12 123456',
				),
				'gender'      => array(
					'label'   => 'جنسیت',
					'type'    => 'select',
					'options' => array( 'مرد', 'زن' ),
				),
				'marital'     => array(
					'label'   => 'وضعیت تاهل',
					'type'    => 'select',
					'options' => array( 'مجرد', 'متاهل' ),
				),
				'education'   => array(
					'label'   => 'سطح تحصیلات',
					'type'    => 'select',
					'options' => array( 'زیر دیپلم', 'دیپلم', 'کاردانی', 'کارشناسی', 'کارشناسی ارشد', 'دکتری' ),
				),
				'job'         => array(
					'label' => 'شغل',
					'type'  => 'text',
				),
			),
		),
		'birth'     => array(
			'label'  => 'تولد و صدور شناسنامه',
			'fields' => array(
				'birth_province' => array(
					'label'   => 'استان محل تولد',
					'type'    => 'select',
					'options' => $provinces,
				),
				'birth_city'     => array(
					'label'   => 'شهر محل تولد',
					'type'    => 'text',
					'city_of' => 'birth_province',
				),
				'issue_province' => array(
					'label'   => 'استان محل صدور',
					'type'    => 'select',
					'options' => $provinces,
				),
				'issue_city'     => array(
					'label'   => 'شهر محل صدور',
					'type'    => 'text',
					'city_of' => 'issue_province',
				),
				'birth_date'     => array(
					'label'       => 'تاریخ تولد',
					'type'        => 'text',
					'validate'    => 'jalali_date',
					'ltr'         => true,
					'placeholder' => 'مثال: 1370/01/01',
				),
				'issue_date'     => array(
					'label'       => 'تاریخ صدور',
					'type'        => 'text',
					'validate'    => 'jalali_date',
					'ltr'         => true,
					'placeholder' => 'مثال: 1370/01/15',
				),
			),
		),
		'residence' => array(
			'label'  => 'محل سکونت',
			'fields' => array(
				'home_province' => array(
					'label'   => 'استان محل سکونت',
					'type'    => 'select',
					'options' => $provinces,
				),
				'home_city'     => array(
					'label'   => 'شهر محل سکونت',
					'type'    => 'text',
					'city_of' => 'home_province',
				),
				'main_street'   => array(
					'label' => 'خیابان اصلی',
					'type'  => 'text',
				),
				'side_street'   => array(
					'label' => 'خیابان فرعی',
					'type'  => 'text',
				),
				'alley'         => array(
					'label' => 'کوچه',
					'type'  => 'text',
				),
				'floor'         => array(
					'label' => 'طبقه',
					'type'  => 'text',
				),
				'unit'          => array(
					'label' => 'واحد',
					'type'  => 'text',
				),
				'plaque'        => array(
					'label' => 'پلاک',
					'type'  => 'text',
				),
				'postal_code'   => array(
					'label'    => 'کد پستی',
					'type'     => 'text',
					'validate' => 'postal',
					'ltr'      => true,
				),
				'district'      => array(
					'label' => 'منطقه شهری',
					'type'  => 'text',
				),
			),
		),
		'contact'   => array(
			'label'  => 'اطلاعات تماس',
			'fields' => array(
				'phone'  => array(
					'label'       => 'تلفن ثابت',
					'type'        => 'text',
					'validate'    => 'phone',
					'ltr'         => true,
					'placeholder' => 'مثال: 02122334455',
				),
				'mobile' => array(
					'label'       => 'تلفن همراه',
					'type'        => 'text',
					'validate'    => 'mobile',
					'ltr'         => true,
					'placeholder' => 'مثال: 09121234567',
				),
				'email'  => array(
					'label'       => 'آدرس ایمیل',
					'type'        => 'text',
					'validate'    => 'email',
					'ltr'         => true,
					'placeholder' => 'you@example.com',
				),
			),
		),
		'work'      => array(
			'label'  => 'محل کار',
			'fields' => array(
				'work_province'    => array(
					'label'   => 'استان محل کار',
					'type'    => 'select',
					'options' => $provinces,
				),
				'work_city'        => array(
					'label'   => 'شهر محل کار',
					'type'    => 'text',
					'city_of' => 'work_province',
				),
				'work_phone'       => array(
					'label'    => 'تلفن محل کار',
					'type'     => 'text',
					'validate' => 'phone',
					'ltr'      => true,
				),
				'work_postal_code' => array(
					'label'    => 'کد پستی محل کار',
					'type'     => 'text',
					'validate' => 'postal',
					'ltr'      => true,
				),
				'work_address'     => array(
					'label' => 'آدرس محل کار',
					'type'  => 'textarea',
				),
			),
		),
		'bank'      => array(
			'label'  => 'اطلاعات بانکی',
			'hint'   => 'خواهشمند است شماره شبای ۲۶ رقمی خود را به صورت کامل وارد نمایید.',
			'fields' => array(
				'sheba' => array(
					'label'       => 'شماره شبا',
					'type'        => 'text',
					'validate'    => 'sheba',
					'ltr'         => true,
					'placeholder' => 'IR123456789012345678901234',
				),
			),
		),
	);

	/* Merge the admin's required/optional choice into every field. */
	$optional = afq_signup_get_optional_fields();

	foreach ( $sections as $section_id => $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$sections[ $section_id ]['fields'][ $key ]['required'] = ! in_array( (string) $key, $optional, true );
		}
	}

	return $sections;
}

/**
 * Flat signup fields list.
 *
 * @return array
 */
function afq_signup_get_fields() {

	static $fields = null;

	if ( null !== $fields ) {
		return $fields;
	}

	$fields = array();

	foreach ( afq_signup_get_sections() as $section ) {
		$fields = array_merge( $fields, $section['fields'] );
	}

	return $fields;
}
