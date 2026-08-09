<?php
/**
 * Admin asset registration and per-screen enqueueing.
 *
 * One `admin_enqueue_scripts` callback decides what the current screen
 * needs, instead of seven callbacks each re-checking the same screen.
 * Handles are unchanged from the original functions.php code.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue admin styles and scripts for AFQ screens.
 *
 * @param string $hook Current admin page hook.
 */
function afq_option_admin_assets( $hook ) {

	$screen = get_current_screen();

	if ( ! $screen ) {
		return;
	}

	$css = AFQ_OPTION_URL . 'assets/css/';
	$js  = AFQ_OPTION_URL . 'assets/js/';
	$ver = AFQ_OPTION_VERSION;

	$is_post_edit = in_array( $hook, array( 'post.php', 'post-new.php' ), true );
	$is_term_edit = in_array( $hook, array( 'edit-tags.php', 'term.php' ), true );

	/* ---- Car: media meta boxes (post screen + car category term screen). ---- */

	$car_post_screen = ( $is_post_edit && 'afq_car' === $screen->post_type );
	$car_term_screen = ( $is_term_edit && 'afq_car_cat' === $screen->taxonomy );

	if ( $car_post_screen || $car_term_screen ) {

		wp_enqueue_media();

		wp_enqueue_style( 'afq-car-media', $css . 'admin-car-media.css', array(), $ver );
		wp_enqueue_script( 'afq-car-media', $js . 'admin-car-media.js', array( 'jquery', 'jquery-ui-sortable' ), $ver, true );
	}

	/* ---- Car: specs + details meta boxes (post screen only). ---- */

	if ( $car_post_screen ) {

		wp_enqueue_style( 'afq-car-specs', $css . 'admin-car-specs.css', array(), $ver );
		wp_enqueue_script( 'afq-car-specs', $js . 'admin-car-specs.js', array(), $ver, true );

		/* Depends on afq-car-media so it always loads after it. */
		wp_enqueue_style( 'afq-car-details', $css . 'admin-car-details.css', array( 'afq-car-media' ), $ver );
		wp_enqueue_script( 'afq-car-details', $js . 'admin-car-details.js', array( 'afq-car-media' ), $ver, true );
	}

	/* ---- Signup: form settings screen. ---- */

	if ( 'afq_signup_page_afq-signup-settings' === $hook ) {
		wp_enqueue_style( 'afq-signup-admin', $css . 'admin-signup.css', array(), $ver );
		wp_enqueue_script( 'afq-signup-settings', $js . 'admin-signup-settings.js', array(), $ver, true );
		return;
	}

	if ( ! $is_post_edit ) {
		return;
	}

	/* ---- Representatives. ---- */

	if ( 'afq_rep' === $screen->post_type ) {
		wp_enqueue_style( 'afq-rep-admin', $css . 'admin-rep.css', array(), $ver );
	}

	/* ---- Customer voice. ---- */

	if ( 'afq_voice' === $screen->post_type ) {
		wp_enqueue_media();
		wp_enqueue_style( 'afq-voice-admin', $css . 'admin-voice.css', array(), $ver );
		wp_enqueue_script( 'afq-voice-admin', $js . 'admin-voice.js', array( 'jquery' ), $ver, true );
	}

	/* ---- Sales circular. ---- */

	if ( 'afq_circular' === $screen->post_type ) {
		wp_enqueue_style( 'afq-circular-admin', $css . 'admin-circular.css', array(), $ver );
		wp_enqueue_script( 'afq-circular-admin', $js . 'admin-circular.js', array( 'jquery', 'jquery-ui-sortable' ), $ver, true );
	}

	/* ---- Signup submissions (view screen only). ---- */

	if ( 'afq_signup' === $screen->post_type && 'post.php' === $hook ) {
		wp_enqueue_style( 'afq-signup-admin', $css . 'admin-signup.css', array(), $ver );
	}
}
add_action( 'admin_enqueue_scripts', 'afq_option_admin_assets' );
