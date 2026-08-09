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
 * Jalali (Shamsi) Calendar
 *
 * Port of the Khayyam/Birashk algorithm used by jalaali-js, so the PHP
 * validation and the JS date picker agree on month lengths and leap years.
 * ---------------------------------------------------------------------- */

/**
 * Integer division truncating toward zero.
 *
 * @param int $a Dividend.
 * @param int $b Divisor.
 * @return int
 */
function afq_jalali_div( $a, $b ) {
	return intdiv( (int) $a, (int) $b );
}

/**
 * Remainder matching the truncated division above.
 *
 * @param int $a Dividend.
 * @param int $b Divisor.
 * @return int
 */
function afq_jalali_mod( $a, $b ) {
	return (int) $a - intdiv( (int) $a, (int) $b ) * (int) $b;
}

/**
 * Calendar data for a Jalali year: leap offset, Gregorian year and the
 * March day on which Farvardin 1 falls.
 *
 * @param int $jy Jalali year.
 * @return array|null Null when the year is outside the supported range.
 */
function afq_jalali_cal( $jy ) {

	$jy = (int) $jy;

	$breaks = array( -61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 1701, 1866, 2020, 2053, 2400, 3178 );
	$bl     = count( $breaks );
	$gy     = $jy + 621;
	$leap_j = -14;
	$jp     = $breaks[0];
	$jump   = 0;

	if ( $jy < $jp || $jy >= $breaks[ $bl - 1 ] ) {
		return null;
	}

	for ( $i = 1; $i < $bl; $i++ ) {
		$jm   = $breaks[ $i ];
		$jump = $jm - $jp;

		if ( $jy < $jm ) {
			break;
		}

		$leap_j = $leap_j + afq_jalali_div( $jump, 33 ) * 8 + afq_jalali_div( afq_jalali_mod( $jump, 33 ), 4 );
		$jp     = $jm;
	}

	$n      = $jy - $jp;
	$leap_j = $leap_j + afq_jalali_div( $n, 33 ) * 8 + afq_jalali_div( afq_jalali_mod( $n, 33 ) + 3, 4 );

	if ( 4 === afq_jalali_mod( $jump, 33 ) && 4 === $jump - $n ) {
		$leap_j++;
	}

	$leap_g = afq_jalali_div( $gy, 4 ) - afq_jalali_div( ( afq_jalali_div( $gy, 100 ) + 1 ) * 3, 4 ) - 150;
	$march  = 20 + $leap_j - $leap_g;

	if ( $jump - $n < 6 ) {
		$n = $n - $jump + afq_jalali_div( $jump + 4, 33 ) * 33;
	}

	$leap = afq_jalali_mod( afq_jalali_mod( $n + 1, 33 ) - 1, 4 );

	if ( -1 === $leap ) {
		$leap = 4;
	}

	return array(
		'leap'  => $leap,
		'gy'    => $gy,
		'march' => $march,
	);
}

/**
 * Whether a Jalali year is a leap year (Esfand has 30 days).
 *
 * @param int $jy Jalali year.
 * @return bool
 */
function afq_jalali_is_leap_year( $jy ) {

	$cal = afq_jalali_cal( $jy );

	return $cal && 0 === $cal['leap'];
}

/**
 * Number of days in a Jalali month.
 *
 * @param int $jy Jalali year.
 * @param int $jm Jalali month (1-12).
 * @return int
 */
function afq_jalali_month_length( $jy, $jm ) {

	$jm = (int) $jm;

	if ( $jm <= 6 ) {
		return 31;
	}

	if ( $jm <= 11 ) {
		return 30;
	}

	return afq_jalali_is_leap_year( $jy ) ? 30 : 29;
}

/**
 * Validate a Jalali date string in YYYY/MM/DD form.
 *
 * Unlike a plain regex this rejects impossible days such as 1370/12/30
 * (Esfand had 29 days that year) or 1400/07/31.
 *
 * @param string $value Date string.
 * @return bool
 */
function afq_signup_is_valid_jalali_date( $value ) {

	if ( ! preg_match( '#^(1[34]\d{2})/(\d{2})/(\d{2})$#', (string) $value, $m ) ) {
		return false;
	}

	$jy = (int) $m[1];
	$jm = (int) $m[2];
	$jd = (int) $m[3];

	if ( $jm < 1 || $jm > 12 || $jd < 1 ) {
		return false;
	}

	return $jd <= afq_jalali_month_length( $jy, $jm );
}

/**
 * Gregorian date to Julian Day Number.
 *
 * @param int $gy Gregorian year.
 * @param int $gm Gregorian month (1-12).
 * @param int $gd Gregorian day.
 * @return int
 */
function afq_jalali_g2d( $gy, $gm, $gd ) {

	$gy = (int) $gy;
	$gm = (int) $gm;
	$gd = (int) $gd;

	$d = afq_jalali_div( ( $gy + afq_jalali_div( $gm - 8, 6 ) + 100100 ) * 1461, 4 )
		+ afq_jalali_div( 153 * afq_jalali_mod( $gm + 9, 12 ) + 2, 5 )
		+ $gd - 34840408;

	return $d - afq_jalali_div( afq_jalali_div( $gy + 100100 + afq_jalali_div( $gm - 8, 6 ), 100 ) * 3, 4 ) + 752;
}

/**
 * Julian Day Number back to a Gregorian date.
 *
 * @param int $jdn Julian Day Number.
 * @return array{gy:int,gm:int,gd:int}
 */
function afq_jalali_d2g( $jdn ) {

	$j = 4 * (int) $jdn + 139361631;
	$j = $j + afq_jalali_div( afq_jalali_div( 4 * (int) $jdn + 183187720, 146097 ) * 3, 4 ) * 4 - 3908;
	$i = afq_jalali_div( afq_jalali_mod( $j, 1461 ), 4 ) * 5 + 308;

	$gd = afq_jalali_div( afq_jalali_mod( $i, 153 ), 5 ) + 1;
	$gm = afq_jalali_mod( afq_jalali_div( $i, 153 ), 12 ) + 1;
	$gy = afq_jalali_div( $j, 1461 ) - 100100 + afq_jalali_div( 8 - $gm, 6 );

	return array(
		'gy' => $gy,
		'gm' => $gm,
		'gd' => $gd,
	);
}

/**
 * Convert a Gregorian date to Jalali.
 *
 * Mirrors d2j() in assets/js/jalali-picker.js.
 *
 * @param int $gy Gregorian year.
 * @param int $gm Gregorian month (1-12).
 * @param int $gd Gregorian day.
 * @return array|null Array with jy/jm/jd, or null outside the supported range.
 */
function afq_jalali_from_gregorian( $gy, $gm, $gd ) {

	$jdn = afq_jalali_g2d( $gy, $gm, $gd );
	$g   = afq_jalali_d2g( $jdn );
	$jy  = $g['gy'] - 621;
	$cal = afq_jalali_cal( $jy );

	if ( ! $cal ) {
		return null;
	}

	$k = $jdn - afq_jalali_g2d( $g['gy'], 3, $cal['march'] );

	if ( $k >= 0 ) {
		if ( $k <= 185 ) {
			return array(
				'jy' => $jy,
				'jm' => 1 + afq_jalali_div( $k, 31 ),
				'jd' => afq_jalali_mod( $k, 31 ) + 1,
			);
		}

		$k -= 186;
	} else {
		$jy--;
		$k += 179;

		if ( 1 === $cal['leap'] ) {
			$k++;
		}
	}

	return array(
		'jy' => $jy,
		'jm' => 7 + afq_jalali_div( $k, 30 ),
		'jd' => afq_jalali_mod( $k, 30 ) + 1,
	);
}

/**
 * Jalali month names, indexed 1-12.
 *
 * @return array
 */
function afq_jalali_month_names() {

	return array(
		1  => 'فروردین',
		2  => 'اردیبهشت',
		3  => 'خرداد',
		4  => 'تیر',
		5  => 'مرداد',
		6  => 'شهریور',
		7  => 'مهر',
		8  => 'آبان',
		9  => 'آذر',
		10 => 'دی',
		11 => 'بهمن',
		12 => 'اسفند',
	);
}

/**
 * Convert English digits to Persian ones (display only).
 *
 * @param string $value Input value.
 * @return string
 */
function afq_fa_digits( $value ) {

	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );

	return str_replace( $en, $fa, (string) $value );
}

/**
 * Format a post's publish date as a Jalali label, e.g. «۱۸ مرداد ۱۴۰۳».
 *
 * Reads the date through get_the_date() so the site timezone applies.
 *
 * @param int    $post_id Post ID.
 * @param string $format  'label' for «۱۸ مرداد ۱۴۰۳», 'numeric' for «۱۴۰۳/۰۵/۱۸».
 * @return string Empty string when the date is outside the supported range.
 */
function afq_jalali_post_date( $post_id, $format = 'label' ) {

	$jalali = afq_jalali_from_gregorian(
		(int) get_the_date( 'Y', $post_id ),
		(int) get_the_date( 'n', $post_id ),
		(int) get_the_date( 'j', $post_id )
	);

	if ( ! $jalali ) {
		return '';
	}

	if ( 'numeric' === $format ) {
		return afq_fa_digits( sprintf( '%04d/%02d/%02d', $jalali['jy'], $jalali['jm'], $jalali['jd'] ) );
	}

	$months = afq_jalali_month_names();

	return afq_fa_digits( $jalali['jd'] ) . ' ' . $months[ $jalali['jm'] ] . ' ' . afq_fa_digits( $jalali['jy'] );
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
 * Kept as the signup-specific name the original code used; it now delegates
 * to the shared validator so both forms apply identical rules.
 *
 * @param string $rule  Validation rule.
 * @param string $value Normalized value.
 * @return string
 */
function afq_signup_validate_value( $rule, $value ) {
	return afq_option_validate_value( $rule, $value );
}

/**
 * Shared field validator used by every AFQ form.
 *
 * @param string $rule  Validation rule.
 * @param string $value Normalized value.
 * @return string Error message, or an empty string when the value is fine.
 */
function afq_option_validate_value( $rule, $value ) {

	switch ( $rule ) {

		case 'vin':
			/* 17 characters, and a real VIN never uses I, O or Q. */
			if ( ! preg_match( '/^[A-HJ-NPR-Z0-9]{17}$/i', $value ) ) {
				return 'شماره شاسی باید ۱۷ کاراکتر و بدون حروف I، O و Q باشد.';
			}
			break;

		case 'number':
			if ( ! preg_match( '/^\d{1,9}$/', $value ) ) {
				return 'فقط عدد وارد کنید.';
			}
			break;

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
			if ( ! preg_match( '#^1[34]\d{2}/\d{2}/\d{2}$#', $value ) ) {
				return 'تاریخ را به صورت 1370/01/01 وارد کنید.';
			}
			if ( ! afq_signup_is_valid_jalali_date( $value ) ) {
				return 'این تاریخ در تقویم شمسی وجود ندارد.';
			}
			break;
	}

	return '';
}
