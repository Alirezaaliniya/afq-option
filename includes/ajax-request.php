<?php
/**
 * Customer Voice AJAX endpoints: submit a request and track one.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Very small per-IP throttle, backed by a transient.
 *
 * @param string $bucket  Throttle name.
 * @param int    $limit   Allowed hits per window.
 * @param int    $window  Window length in seconds.
 * @return bool True when the caller is still within its allowance.
 */
function afq_request_throttle( $bucket, $limit, $window ) {

	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	if ( '' === $ip ) {
		return true;
	}

	$key   = 'afq_thr_' . $bucket . '_' . md5( $ip );
	$hits  = (int) get_transient( $key );
	$hits++;

	set_transient( $key, $hits, $window );

	return $hits <= $limit;
}

/**
 * Validate and store the optional attachment.
 *
 * Guests can reach this, so the check is deliberately strict: the extension
 * must be on the admin's allow-list, the size under the configured cap, and
 * wp_handle_upload() re-checks the real type against that same list.
 *
 * @return array { id: int } on success, { error: string } on failure.
 */
function afq_request_handle_upload() {

	$settings = afq_request_get_settings();

	if ( empty( $settings['upload_enabled'] ) ) {
		return array( 'id' => 0 );
	}

	if ( empty( $_FILES['afq_request_file'] ) || ! isset( $_FILES['afq_request_file']['error'] ) ) {
		return array( 'id' => 0 );
	}

	$error = (int) $_FILES['afq_request_file']['error'];

	if ( UPLOAD_ERR_NO_FILE === $error ) {
		return array( 'id' => 0 );
	}

	if ( UPLOAD_ERR_OK !== $error ) {
		return array( 'error' => 'دریافت فایل ناموفق بود. دوباره تلاش کنید.' );
	}

	$max_mb = max( 1, (int) $settings['upload_max_mb'] );

	if ( (int) $_FILES['afq_request_file']['size'] > $max_mb * MB_IN_BYTES ) {
		return array( 'error' => sprintf( 'حجم فایل نباید بیشتر از %d مگابایت باشد.', $max_mb ) );
	}

	$allowed  = afq_request_get_allowed_extensions();
	$filename = sanitize_file_name( (string) $_FILES['afq_request_file']['name'] );
	$ext      = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

	if ( ! $allowed || ! in_array( $ext, $allowed, true ) ) {
		return array( 'error' => 'فرمت این فایل مجاز نیست.' );
	}

	/* Restrict wp_handle_upload to exactly the allowed extensions. */
	$mimes = array();

	foreach ( wp_get_mime_types() as $pattern => $mime ) {
		foreach ( explode( '|', $pattern ) as $one ) {
			if ( in_array( $one, $allowed, true ) ) {
				$mimes[ $pattern ] = $mime;
				break;
			}
		}
	}

	if ( ! $mimes ) {
		return array( 'error' => 'فرمت این فایل مجاز نیست.' );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';

	$moved = wp_handle_upload(
		$_FILES['afq_request_file'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		array(
			'test_form' => false,
			'mimes'     => $mimes,
		)
	);

	if ( ! is_array( $moved ) || isset( $moved['error'] ) ) {
		return array( 'error' => 'فایل ارسالی معتبر نیست.' );
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $moved['type'],
			'post_title'     => sanitize_file_name( wp_basename( $moved['file'] ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$moved['file']
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return array( 'error' => 'ذخیره فایل ناموفق بود.' );
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $moved['file'] ) );

	return array( 'id' => (int) $attachment_id );
}

/**
 * AJAX handler: customer voice form submit.
 */
function afq_request_ajax_submit() {

	check_ajax_referer( 'afq_request_submit', 'nonce' );

	/* Honeypot: silently pretend success for bots. */
	if ( ! empty( $_POST['afq_request_website'] ) ) {
		wp_send_json_success(
			array(
				'code'    => '',
				'message' => 'درخواست شما با موفقیت ثبت شد.',
			)
		);
	}

	if ( ! afq_request_throttle( 'req', 5, 10 * MINUTE_IN_SECONDS ) ) {
		wp_send_json_error( array( 'message' => 'تعداد درخواست‌های شما زیاد است. کمی بعد دوباره تلاش کنید.' ) );
	}

	$settings = afq_request_get_settings();
	$fields   = afq_request_get_fields();
	$errors   = array();
	$data     = array();

	foreach ( $fields as $key => $field ) {

		/* Multi-value checkboxes. */
		if ( 'checkboxes' === $field['type'] ) {

			$raw = isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] )
				? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) )
				: array();

			$picked = array_values( array_intersect( $field['options'], $raw ) );

			if ( ! $picked ) {
				if ( ! empty( $field['required'] ) ) {
					$errors[ $key ] = 'حداقل یک گزینه را انتخاب کنید.';
				}
				continue;
			}

			$data[ $key ] = implode( '، ', $picked );
			continue;
		}

		$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$value = ( 'textarea' === $field['type'] ) ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		$value = trim( $value );

		if ( '' === $value ) {

			/*
			 * A city can only be filled once its province is chosen, so a
			 * required city stays silent while its province is still empty.
			 */
			$waiting_on_province = ! empty( $field['city_of'] ) && empty( $data[ $field['city_of'] ] );

			if ( ! empty( $field['required'] ) && ! $waiting_on_province ) {
				$errors[ $key ] = 'این فیلد ضروری است.';
			}
			continue;
		}

		/* Selects must hold one of their defined options; city and model are
		 * filled dynamically, so they are treated as free text. */
		if ( 'select' === $field['type'] && empty( $field['model_of'] ) && ! in_array( $value, $field['options'], true ) ) {
			$errors[ $key ] = 'گزینه انتخاب‌شده معتبر نیست.';
			continue;
		}

		if ( ! empty( $field['min'] ) && mb_strlen( $value ) < (int) $field['min'] ) {
			$errors[ $key ] = sprintf( 'حداقل %d کاراکتر لازم است.', (int) $field['min'] );
			continue;
		}

		if ( ! empty( $field['max'] ) && mb_strlen( $value ) > (int) $field['max'] ) {
			$errors[ $key ] = sprintf( 'حداکثر %d کاراکتر مجاز است.', (int) $field['max'] );
			continue;
		}

		$rule = $field['validate'] ?? '';

		if ( $rule ) {
			$value = afq_signup_en_digits( $value );

			if ( 'vin' === $rule ) {
				$value = strtoupper( str_replace( array( ' ', '-' ), '', $value ) );
			}

			$message = afq_option_validate_value( $rule, $value );

			if ( $message ) {
				$errors[ $key ] = $message;
				continue;
			}
		}

		$data[ $key ] = $value;
	}

	/* Consent is always required. */
	if ( empty( $_POST['afq_request_terms'] ) ) {
		$errors['terms'] = 'پذیرش قوانین و شرایط الزامی است.';
	}

	if ( $errors ) {
		wp_send_json_error( array( 'errors' => $errors ) );
	}

	$upload = afq_request_handle_upload();

	if ( isset( $upload['error'] ) ) {
		wp_send_json_error( array( 'errors' => array( 'attachment' => $upload['error'] ) ) );
	}

	$code  = afq_request_generate_code();
	$title = trim( ( $data['full_name'] ?? '' ) . ' — ' . $code );

	if ( '' === trim( str_replace( '—', '', $title ) ) ) {
		$title = $code;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'afq_request',
			'post_status' => 'publish',
			'post_title'  => $title,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'خطا در ثبت درخواست. لطفاً دوباره تلاش کنید.' ) );
	}

	foreach ( $data as $key => $value ) {
		update_post_meta( $post_id, '_afq_request_' . $key, $value );
	}

	update_post_meta( $post_id, '_afq_request_code', $code );
	update_post_meta( $post_id, '_afq_request_status', 'new' );

	if ( ! empty( $upload['id'] ) ) {
		update_post_meta( $post_id, '_afq_request_file', (int) $upload['id'] );
	}

	afq_request_send_emails( $post_id, $data, $code );

	wp_send_json_success(
		array(
			'code'    => $code,
			'title'   => $settings['success_title'],
			'message' => $settings['success_message'],
		)
	);
}
add_action( 'wp_ajax_afq_request_submit', 'afq_request_ajax_submit' );
add_action( 'wp_ajax_nopriv_afq_request_submit', 'afq_request_ajax_submit' );

/**
 * Replace the {placeholders} used in the e-mail settings.
 *
 * @param string $text Template.
 * @param array  $data Submitted values.
 * @param string $code Tracking code.
 * @return string
 */
function afq_request_render_template( $text, $data, $code ) {

	return strtr(
		(string) $text,
		array(
			'{code}'    => $code,
			'{name}'    => $data['full_name'] ?? '',
			'{mobile}'  => $data['mobile'] ?? '',
			'{type}'    => $data['request_type'] ?? '',
			'{subject}' => $data['subject'] ?? '',
		)
	);
}

/**
 * Send the admin notification and the customer acknowledgement.
 *
 * @param int    $post_id Request post ID.
 * @param array  $data    Submitted values.
 * @param string $code    Tracking code.
 */
function afq_request_send_emails( $post_id, $data, $code ) {

	$settings = afq_request_get_settings();
	$headers  = array( 'Content-Type: text/html; charset=UTF-8' );

	/* ---- Admin notification ---- */

	if ( ! empty( $settings['notify_enabled'] ) ) {

		/**
		 * Filter the customer voice notification recipients.
		 *
		 * @param string[] $emails  Recipient addresses.
		 * @param int      $post_id Request post ID.
		 */
		$recipients = apply_filters(
			'afq_request_notify_emails',
			array_map( 'trim', explode( ',', (string) $settings['notify_emails'] ) ),
			$post_id
		);

		$recipients = array_filter( array_map( 'sanitize_email', (array) $recipients ) );

		if ( $recipients ) {

			$rows = '<tr><td style="padding:8px 12px;border:1px solid #e3e6ea;background:#f5f6f8;font-weight:bold;">کد رهگیری</td>'
				. '<td style="padding:8px 12px;border:1px solid #e3e6ea;direction:ltr;">' . esc_html( $code ) . '</td></tr>';

			foreach ( afq_request_get_sections() as $section ) {
				foreach ( $section['fields'] as $key => $field ) {
					if ( ! isset( $data[ $key ] ) ) {
						continue;
					}
					$rows .= '<tr><td style="padding:8px 12px;border:1px solid #e3e6ea;background:#f5f6f8;font-weight:bold;white-space:nowrap;">' . esc_html( $field['label'] ) . '</td>'
						. '<td style="padding:8px 12px;border:1px solid #e3e6ea;">' . nl2br( esc_html( $data[ $key ] ) ) . '</td></tr>';
				}
			}

			$link = admin_url( 'post.php?post=' . $post_id . '&action=edit' );

			$body = '<div dir="rtl" style="font-family:Tahoma,Arial,sans-serif;font-size:13px;color:#222;">'
				. '<h2 style="font-size:16px;">درخواست جدید در مرکز ارتباط با مشتریان</h2>'
				. '<table style="border-collapse:collapse;width:100%;max-width:640px;">' . $rows . '</table>'
				. '<p style="margin-top:16px;"><a href="' . esc_url( $link ) . '">مشاهده در پیشخوان</a></p>'
				. '</div>';

			wp_mail(
				$recipients,
				afq_request_render_template( $settings['notify_subject'], $data, $code ),
				$body,
				$headers
			);
		}
	}

	/* ---- Customer acknowledgement ---- */

	if ( empty( $settings['ack_enabled'] ) || empty( $data['email'] ) ) {
		return;
	}

	$customer = sanitize_email( $data['email'] );

	if ( ! $customer ) {
		return;
	}

	$message = afq_request_render_template( $settings['ack_message'], $data, $code );

	$body = '<div dir="rtl" style="font-family:Tahoma,Arial,sans-serif;font-size:13px;color:#222;line-height:2;">'
		. wpautop( esc_html( $message ) )
		. '<p style="margin-top:16px;font-size:15px;"><strong>کد رهگیری:</strong> '
		. '<span style="direction:ltr;display:inline-block;">' . esc_html( $code ) . '</span></p>'
		. '</div>';

	wp_mail(
		$customer,
		afq_request_render_template( $settings['ack_subject'], $data, $code ),
		$body,
		$headers
	);
}

/**
 * AJAX handler: look a request up by tracking code + mobile.
 */
function afq_request_ajax_track() {

	check_ajax_referer( 'afq_request_track', 'nonce' );

	if ( ! afq_request_throttle( 'trk', 20, 10 * MINUTE_IN_SECONDS ) ) {
		wp_send_json_error( array( 'message' => 'تعداد تلاش‌های شما زیاد است. کمی بعد دوباره تلاش کنید.' ) );
	}

	$code   = strtoupper( sanitize_text_field( wp_unslash( $_POST['code'] ?? '' ) ) );
	$mobile = afq_signup_en_digits( sanitize_text_field( wp_unslash( $_POST['mobile'] ?? '' ) ) );

	$code   = trim( $code );
	$mobile = trim( $mobile );

	if ( '' === $code || '' === $mobile ) {
		wp_send_json_error( array( 'message' => 'شماره موبایل و کد رهگیری را وارد کنید.' ) );
	}

	$found = get_posts(
		array(
			'post_type'              => 'afq_request',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'AND',
				array(
					'key'   => '_afq_request_code',
					'value' => $code,
				),
				array(
					'key'   => '_afq_request_mobile',
					'value' => $mobile,
				),
			),
		)
	);

	/* One message for both "wrong code" and "wrong mobile" so the endpoint
	 * cannot be used to confirm which codes exist. */
	if ( ! $found ) {
		wp_send_json_error( array( 'message' => 'درخواستی با این مشخصات پیدا نشد. شماره موبایل و کد رهگیری را بررسی کنید.' ) );
	}

	$post_id  = $found[0]->ID;
	$statuses = afq_request_get_statuses();
	$status   = get_post_meta( $post_id, '_afq_request_status', true );
	$status   = isset( $statuses[ $status ] ) ? $status : 'new';

	wp_send_json_success(
		array(
			'code'    => $code,
			'status'  => $statuses[ $status ]['label'],
			'color'   => $statuses[ $status ]['color'],
			'bg'      => $statuses[ $status ]['bg'],
			'date'    => get_the_date( 'Y/m/d', $post_id ),
			'type'    => get_post_meta( $post_id, '_afq_request_request_type', true ),
			'subject' => get_post_meta( $post_id, '_afq_request_subject', true ),
			'reply'   => get_post_meta( $post_id, '_afq_request_reply', true ),
		)
	);
}
add_action( 'wp_ajax_afq_request_track', 'afq_request_ajax_track' );
add_action( 'wp_ajax_nopriv_afq_request_track', 'afq_request_ajax_track' );
