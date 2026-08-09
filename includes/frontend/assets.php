<?php
/**
 * Front-end asset registration.
 *
 * Styles and scripts live in real files under /assets so browsers can cache
 * them, instead of being re-inlined into the HTML of every page render.
 * Handles are unchanged from the original functions.php code.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map of shortcode tag => front-end asset handle.
 *
 * @return array<string,string>
 */
function afq_option_shortcode_handles() {
	return array(
		'afq_faq_list'      => 'afq-faq-list',
		'afq_car_spot'      => 'afq-car-spot',
		'afq_rep_map'       => 'afq-rep-map',
		'afq_voice_grid'    => 'afq-voice-grid',
		'afq_circular_cars' => 'afq-circular-cars',
		'afq_signup_form'   => 'afq-signup-form',
		'afq_request_form'  => 'afq-request-form',
		'afq_request_track' => 'afq-request-form',
	);
}

/**
 * Register front-end styles and scripts.
 *
 * Registration is cheap; nothing is enqueued until a shortcode actually
 * renders (or the pre-scan below finds its tag in the page).
 */
function afq_option_register_front_assets() {

	$css = AFQ_OPTION_URL . 'assets/css/';
	$js  = AFQ_OPTION_URL . 'assets/js/';
	$ver = AFQ_OPTION_VERSION;

	wp_register_style( 'afq-faq-list', $css . 'faq.css', array(), $ver );
	wp_register_script( 'afq-faq-list', $js . 'faq.js', array(), $ver, true );

	wp_register_style( 'afq-car-spot', $css . 'car-spot.css', array(), $ver );
	wp_register_script( 'afq-car-spot', $js . 'car-spot.js', array(), $ver, true );

	wp_register_style( 'afq-rep-map', $css . 'rep-map.css', array(), $ver );
	wp_register_script( 'afq-rep-map', $js . 'rep-map.js', array(), $ver, true );

	wp_register_style( 'afq-voice-grid', $css . 'voice-grid.css', array(), $ver );
	wp_register_script( 'afq-voice-grid', $js . 'voice-grid.js', array(), $ver, true );

	wp_register_style( 'afq-circular-cars', $css . 'circular-cars.css', array(), $ver );

	/*
	 * Shared building blocks. The province/city dataset (~2700 towns) and the
	 * Jalali date picker are their own files so the browser caches them once,
	 * instead of them being re-sent inside the HTML of every page.
	 */
	wp_register_script( 'afq-iran-cities', $js . 'iran-cities.js', array(), $ver, true );
	wp_register_style( 'afq-jalali-picker', $css . 'jalali-picker.css', array(), $ver );
	wp_register_script( 'afq-jalali-picker', $js . 'jalali-picker.js', array(), $ver, true );

	wp_register_style( 'afq-signup-form', $css . 'signup-form.css', array( 'afq-jalali-picker' ), $ver );
	wp_register_script( 'afq-signup-form', $js . 'signup-form.js', array( 'afq-iran-cities', 'afq-jalali-picker' ), $ver, true );

	wp_register_style( 'afq-request-form', $css . 'request-form.css', array( 'afq-jalali-picker' ), $ver );
	wp_register_script( 'afq-request-form', $js . 'request-form.js', array( 'afq-iran-cities', 'afq-jalali-picker' ), $ver, true );
}
add_action( 'wp_enqueue_scripts', 'afq_option_register_front_assets' );

/**
 * Enqueue and configure the Customer Voice assets.
 *
 * Both [afq_request_form] and [afq_request_track] call this; the localized
 * config is emitted only once even when both shortcodes are on one page.
 */
function afq_request_enqueue_assets() {

	static $done = false;

	wp_enqueue_style( 'afq-request-form' );
	wp_enqueue_script( 'afq-request-form' );

	if ( $done ) {
		return;
	}

	$done     = true;
	$settings = afq_request_get_settings();

	wp_localize_script(
		'afq-request-form',
		'afqRequestCfg',
		array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'afq_request_submit' ),
			'trackNonce'  => wp_create_nonce( 'afq_request_track' ),
			'models'      => afq_request_get_brand_models(),
			'uploadExts'  => afq_request_get_allowed_extensions(),
			'uploadMaxMb' => (int) $settings['upload_max_mb'],
			'i18n'        => array(
				'chooseProvince' => 'ابتدا استان را انتخاب کنید',
				'chooseCity'     => 'شهر را انتخاب کنید',
				'otherCity'      => 'سایر (وارد کردن دستی)',
				'chooseBrand'    => 'ابتدا برند را انتخاب کنید',
				'chooseModel'    => 'انتخاب مدل',
				'required'       => 'این فیلد ضروری است.',
				'terms'          => 'پذیرش قوانین و شرایط الزامی است.',
				'tooBig'         => 'حجم فایل بیشتر از حد مجاز است.',
				'badFormat'      => 'فرمت این فایل مجاز نیست.',
				'minChars'       => 'حداقل %d کاراکتر لازم است.',
				'genericError'   => 'خطا در ارسال. دوباره تلاش کنید.',
			),
		)
	);
}

/**
 * Enqueue a module's stylesheet in <head> when its shortcode is on the page.
 *
 * Without this the stylesheet is only enqueued while the shortcode renders,
 * which pushes it to the footer. Scanning the post content (and Elementor's
 * stored layout) lets the CSS load in the head instead. If nothing matches,
 * the shortcode still enqueues its own assets as a fallback.
 */
function afq_option_preload_shortcode_styles() {

	if ( ! is_singular() ) {
		return;
	}

	$post = get_post();

	if ( ! $post ) {
		return;
	}

	$content = (string) $post->post_content;

	$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );

	if ( is_string( $elementor_data ) && '' !== $elementor_data ) {
		$content .= ' ' . $elementor_data;
	}

	if ( false === strpos( $content, '[afq_' ) ) {
		return;
	}

	foreach ( afq_option_shortcode_handles() as $tag => $handle ) {
		if ( false !== strpos( $content, '[' . $tag ) ) {
			wp_enqueue_style( $handle );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'afq_option_preload_shortcode_styles', 20 );
