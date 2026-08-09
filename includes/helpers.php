<?php
/**
 * Public template helpers and shared utilities.
 *
 * Every function here keeps the name it had in functions.php, so theme
 * templates that already call them keep working unchanged.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Car — Media
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

/**
 * Get a car category term image, walking up parents if the term has none.
 *
 * @param WP_Term|mixed $term Term object.
 * @return int Attachment ID or 0.
 */
function afq_car_cat_find_term_image( $term ) {

	while ( $term instanceof WP_Term ) {

		$attachment_id = absint( get_term_meta( $term->term_id, 'afq_car_cat_image', true ) );

		if ( $attachment_id ) {
			return $attachment_id;
		}

		if ( ! $term->parent ) {
			break;
		}

		$term = get_term( $term->parent, 'afq_car_cat' );

		if ( is_wp_error( $term ) ) {
			break;
		}
	}

	return 0;
}

/* -------------------------------------------------------------------------
 * Car — Details
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
 * Sales Circular
 * ---------------------------------------------------------------------- */

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

/* -------------------------------------------------------------------------
 * Customer Voice
 * ---------------------------------------------------------------------- */

/**
 * Check whether a URL points to a playable video file.
 *
 * @param string $url Video URL.
 * @return bool
 */
function afq_voice_is_video_file( $url ) {
	return (bool) preg_match( '/\.(mp4|webm|ogv|ogg|m4v|mov)(\?.*)?$/i', (string) $url );
}

/* -------------------------------------------------------------------------
 * Signup — Validation
 * ---------------------------------------------------------------------- */

/**
 * Convert Persian/Arabic digits to English.
 *
 * @param string $value Input value.
 * @return string
 */
function afq_signup_en_digits( $value ) {

	$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' );
	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );

	return str_replace( $fa, $en, (string) $value );
}

/**
 * Validate Iranian national ID (10 digits + checksum).
 *
 * @param string $value National ID.
 * @return bool
 */
function afq_signup_is_valid_national_id( $value ) {

	if ( ! preg_match( '/^\d{10}$/', $value ) || preg_match( '/^(\d)\1{9}$/', $value ) ) {
		return false;
	}

	$check = (int) $value[9];
	$sum   = 0;

	for ( $i = 0; $i < 9; $i++ ) {
		$sum += (int) $value[ $i ] * ( 10 - $i );
	}

	$remainder = $sum % 11;

	return ( $remainder < 2 && $check === $remainder ) || ( $remainder >= 2 && $check === 11 - $remainder );
}

/**
 * Validate one field value. Returns error message or empty string.
 *
 * @param string $rule  Validation rule.
 * @param string $value Normalized value.
 * @return string
 */
function afq_signup_validate_value( $rule, $value ) {

	switch ( $rule ) {

		case 'national_id':
			if ( ! afq_signup_is_valid_national_id( $value ) ) {
				return 'کد ملی معتبر نیست.';
			}
			break;

		case 'digits':
			if ( ! preg_match( '/^\d{1,10}$/', $value ) ) {
				return 'فقط عدد وارد کنید.';
			}
			break;

		case 'mobile':
			if ( ! preg_match( '/^09\d{9}$/', $value ) ) {
				return 'شماره همراه معتبر نیست (مثال: 09121234567).';
			}
			break;

		case 'phone':
			if ( ! preg_match( '/^0\d{7,10}$/', $value ) ) {
				return 'شماره تلفن معتبر نیست (با کد شهر وارد کنید).';
			}
			break;

		case 'email':
			if ( ! is_email( $value ) ) {
				return 'آدرس ایمیل معتبر نیست.';
			}
			break;

		case 'postal':
			if ( ! preg_match( '/^\d{10}$/', $value ) ) {
				return 'کد پستی باید ۱۰ رقم باشد.';
			}
			break;

		case 'sheba':
			$normalized = strtoupper( str_replace( array( ' ', '-' ), '', $value ) );
			if ( ! preg_match( '/^IR\d{24}$/', $normalized ) ) {
				return 'شماره شبا معتبر نیست (IR + ۲۴ رقم).';
			}
			break;

		case 'jalali_date':
			if ( ! preg_match( '/^1[34]\d{2}\/(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])$/', $value ) ) {
				return 'تاریخ را به صورت 1370/01/01 وارد کنید.';
			}
			break;
	}

	return '';
}
