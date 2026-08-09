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

	wp_register_style( 'afq-signup-form', $css . 'signup-form.css', array(), $ver );

	/*
	 * The province/city dataset (~2700 towns) is its own file so the browser
	 * caches it once, instead of it being re-sent inside the HTML of every
	 * page that shows the form.
	 */
	wp_register_script( 'afq-iran-cities', $js . 'iran-cities.js', array(), $ver, true );
	wp_register_script( 'afq-signup-form', $js . 'signup-form.js', array( 'afq-iran-cities' ), $ver, true );
}
add_action( 'wp_enqueue_scripts', 'afq_option_register_front_assets' );

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
