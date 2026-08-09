
/**
 * AFQ Signup — فرم ثبت‌نام (مراحل ثبت نام)
 * Add this code to your theme's functions.php.
 *
 * Frontend shortcode:
 *   [afq_signup_form]                                  → default types: عادی، جانبازان، اقامتی
 *   [afq_signup_form types="عادی,جانبازان,اقامتی"]     → selectable types (manage from shortcode)
 *   [afq_signup_form type="اقامتی"]                    → ONE fixed, locked type (not changeable by user)
 *
 * Submissions are stored as an "afq_signup" post type (dashboard only),
 * with changeable statuses and an email notification on every submit.
 *
 * Notification recipient: site admin email by default. To change it, add:
 *   add_filter( 'afq_signup_notify_emails', function () {
 *       return array( 'someone@example.com', 'another@example.com' );
 *   } );
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Config
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
 * @return string[]
 */
function afq_signup_get_provinces() {
	return array(
		'آذربایجان شرقی', 'آذربایجان غربی', 'اردبیل', 'اصفهان', 'البرز', 'ایلام',
		'بوشهر', 'تهران', 'چهارمحال و بختیاری', 'خراسان جنوبی', 'خراسان رضوی', 'خراسان شمالی',
		'خوزستان', 'زنجان', 'سمنان', 'سیستان و بلوچستان', 'فارس', 'قزوین', 'قم',
		'کردستان', 'کرمان', 'کرمانشاه', 'کهگیلویه و بویراحمد', 'گلستان', 'گیلان',
		'لرستان', 'مازندران', 'مرکزی', 'هرمزگان', 'همدان', 'یزد',
	);
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

	$provinces = afq_signup_get_provinces();

	return array(
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
					'label' => 'شهر محل تولد',
					'type'  => 'text',
				),
				'issue_province' => array(
					'label'   => 'استان محل صدور',
					'type'    => 'select',
					'options' => $provinces,
				),
				'issue_city'     => array(
					'label' => 'شهر محل صدور',
					'type'  => 'text',
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
					'label' => 'شهر محل سکونت',
					'type'  => 'text',
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
					'label' => 'شهر محل کار',
					'type'  => 'text',
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
}

/**
 * Flat fields list.
 *
 * @return array
 */
function afq_signup_get_fields() {
	$fields = array();
	foreach ( afq_signup_get_sections() as $section ) {
		$fields = array_merge( $fields, $section['fields'] );
	}
	return $fields;
}

/* -------------------------------------------------------------------------
 * Post Type
 * ---------------------------------------------------------------------- */

/**
 * Register signup submissions post type (dashboard only, no "Add New").
 */
function afq_register_signup_post_type() {

	register_post_type(
		'afq_signup',
		array(
			'labels'              => array(
				'name'               => 'ثبت‌نام‌ها',
				'singular_name'      => 'ثبت‌نام',
				'menu_name'          => 'ثبت‌نام‌ها',
				'edit_item'          => 'مشاهده ثبت‌نام',
				'search_items'       => 'جستجوی ثبت‌نام',
				'not_found'          => 'ثبت‌نامی یافت نشد',
				'not_found_in_trash' => 'ثبت‌نامی در زباله‌دان یافت نشد',
				'all_items'          => 'همه ثبت‌نام‌ها',
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'rewrite'             => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-id-alt',
			'menu_position'       => 25,
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'        => true,
			'hierarchical'        => false,
		)
	);
}
add_action( 'init', 'afq_register_signup_post_type' );

/* -------------------------------------------------------------------------
 * Frontend Form Shortcode
 * ---------------------------------------------------------------------- */

/**
 * Register empty front asset handles.
 */
function afq_signup_register_front_assets() {
	wp_register_style( 'afq-signup-form', false, array(), '1.0.0' );
	wp_register_script( 'afq-signup-form', false, array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'afq_signup_register_front_assets' );

/**
 * Signup form shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function afq_signup_form_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'types' => 'عادی,جانبازان,اقامتی',
			'type'  => '',
		),
		$atts,
		'afq_signup_form'
	);

	$fixed_type = trim( $atts['type'] );
	$types      = array_filter( array_map( 'trim', explode( ',', $atts['types'] ) ) );

	wp_enqueue_style( 'afq-signup-form' );
	wp_add_inline_style( 'afq-signup-form', afq_signup_inline_css() );

	wp_enqueue_script( 'afq-signup-form' );
	wp_add_inline_script(
		'afq-signup-form',
		'var afqSignupCfg = ' . wp_json_encode(
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'afq_signup_submit' ),
			)
		) . ';',
		'before'
	);
	wp_add_inline_script( 'afq-signup-form', afq_signup_inline_js() );

	ob_start();
	?>
	<form class="afq-signup" novalidate>

		<?php foreach ( afq_signup_get_sections() as $section_id => $section ) : ?>
			<fieldset class="afq-signup__section">
				<legend class="afq-signup__section-title"><?php echo esc_html( $section['label'] ); ?></legend>

				<?php if ( ! empty( $section['hint'] ) ) : ?>
					<p class="afq-signup__hint"><?php echo esc_html( $section['hint'] ); ?></p>
				<?php endif; ?>

				<div class="afq-signup__grid">
					<?php foreach ( $section['fields'] as $key => $field ) : ?>
						<?php
						$field_id = 'afq-signup-' . $key;
						$is_wide  = ( 'textarea' === $field['type'] );
						$is_ltr   = ! empty( $field['ltr'] );
						?>
						<div class="afq-signup__field<?php echo $is_wide ? ' afq-signup__field--wide' : ''; ?>" data-afq-field="<?php echo esc_attr( $key ); ?>">
							<label for="<?php echo esc_attr( $field_id ); ?>">
								<?php echo esc_html( $field['label'] ); ?> <b>*</b>
							</label>

							<?php if ( 'select' === $field['type'] ) : ?>
								<select id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $key ); ?>" required>
									<option value="">انتخاب کنید</option>
									<?php foreach ( $field['options'] as $option ) : ?>
										<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
									<?php endforeach; ?>
								</select>

							<?php elseif ( 'textarea' === $field['type'] ) : ?>
								<textarea id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $key ); ?>" rows="3" required
									placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"></textarea>

							<?php else : ?>
								<input type="text" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $key ); ?>" required
									placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
									<?php echo $is_ltr ? 'class="is-ltr"' : ''; ?> />
							<?php endif; ?>

							<span class="afq-signup__error" aria-live="polite"></span>
						</div>
					<?php endforeach; ?>
				</div>
			</fieldset>
		<?php endforeach; ?>

		<fieldset class="afq-signup__section">
			<legend class="afq-signup__section-title">نوع ثبت‌نام</legend>

			<div class="afq-signup__grid">
				<div class="afq-signup__field" data-afq-field="signup_type">
					<label for="afq-signup-signup_type">نوع ثبت‌نام <b>*</b></label>

					<?php if ( $fixed_type ) : ?>
						<select id="afq-signup-signup_type" disabled>
							<option selected><?php echo esc_html( $fixed_type ); ?></option>
						</select>
						<input type="hidden" name="signup_type" value="<?php echo esc_attr( $fixed_type ); ?>" />
					<?php else : ?>
						<select id="afq-signup-signup_type" name="signup_type" required>
							<option value="">انتخاب کنید</option>
							<?php foreach ( $types as $type_option ) : ?>
								<option value="<?php echo esc_attr( $type_option ); ?>"><?php echo esc_html( $type_option ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>

					<span class="afq-signup__error" aria-live="polite"></span>
				</div>
			</div>
		</fieldset>

		<input type="text" name="afq_signup_website" class="afq-signup__hp" tabindex="-1" autocomplete="off" />

		<div class="afq-signup__footer">
			<button type="submit" class="afq-signup__submit">
				<span class="afq-signup__submit-text">ثبت‌نام</span>
				<span class="afq-signup__submit-loading">در حال ارسال...</span>
			</button>
		</div>

		<div class="afq-signup__message" role="alert"></div>

	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'afq_signup_form', 'afq_signup_form_shortcode' );

/* -------------------------------------------------------------------------
 * Validation + AJAX Submit
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

/**
 * AJAX handler: signup form submit.
 */
function afq_signup_ajax_submit() {

	check_ajax_referer( 'afq_signup_submit', 'nonce' );

	/* Honeypot: silently pretend success for bots. */
	if ( ! empty( $_POST['afq_signup_website'] ) ) {
		wp_send_json_success( array( 'message' => 'ثبت‌نام شما با موفقیت انجام شد.' ) );
	}

	$fields = afq_signup_get_fields();
	$errors = array();
	$data   = array();

	foreach ( $fields as $key => $field ) {

		$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$value = ( 'textarea' === $field['type'] ) ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		$value = trim( $value );

		if ( '' === $value ) {
			$errors[ $key ] = 'این فیلد ضروری است.';
			continue;
		}

		/* Selects: value must be one of the defined options. */
		if ( 'select' === $field['type'] && ! in_array( $value, $field['options'], true ) ) {
			$errors[ $key ] = 'گزینه انتخاب‌شده معتبر نیست.';
			continue;
		}

		$rule = $field['validate'] ?? '';

		if ( $rule ) {
			$value = afq_signup_en_digits( $value );

			if ( 'sheba' === $rule ) {
				$value = strtoupper( str_replace( array( ' ', '-' ), '', $value ) );
			}

			$error = afq_signup_validate_value( $rule, $value );

			if ( $error ) {
				$errors[ $key ] = $error;
				continue;
			}
		}

		$data[ $key ] = $value;
	}

	/* Signup type. */
	$signup_type = isset( $_POST['signup_type'] ) ? sanitize_text_field( wp_unslash( $_POST['signup_type'] ) ) : '';
	$signup_type = trim( $signup_type );

	if ( '' === $signup_type || mb_strlen( $signup_type ) > 100 ) {
		$errors['signup_type'] = 'نوع ثبت‌نام را انتخاب کنید.';
	}

	if ( $errors ) {
		wp_send_json_error( array( 'errors' => $errors ) );
	}

	/* Create submission post. */
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'afq_signup',
			'post_status' => 'publish',
			'post_title'  => $data['first_name'] . ' ' . $data['last_name'] . ' — ' . $data['national_id'],
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'خطا در ثبت اطلاعات. لطفاً دوباره تلاش کنید.' ) );
	}

	foreach ( $data as $key => $value ) {
		update_post_meta( $post_id, '_afq_signup_' . $key, $value );
	}

	update_post_meta( $post_id, '_afq_signup_signup_type', $signup_type );
	update_post_meta( $post_id, '_afq_signup_status', 'pending' );

	afq_signup_send_notification( $post_id, $data, $signup_type );

	wp_send_json_success( array( 'message' => 'ثبت‌نام شما با موفقیت انجام شد. کارشناسان ما به‌زودی با شما تماس می‌گیرند.' ) );
}
add_action( 'wp_ajax_afq_signup_submit', 'afq_signup_ajax_submit' );
add_action( 'wp_ajax_nopriv_afq_signup_submit', 'afq_signup_ajax_submit' );

/* -------------------------------------------------------------------------
 * Notification Email
 * ---------------------------------------------------------------------- */

/**
 * Send the admin notification email.
 *
 * @param int    $post_id     Submission post ID.
 * @param array  $data        Sanitized field values keyed by field key.
 * @param string $signup_type Selected signup type.
 */
function afq_signup_send_notification( $post_id, $data, $signup_type ) {

	/**
	 * Filter the notification recipients.
	 *
	 * @param string[] $emails Recipient email addresses.
	 * @param int      $post_id Submission post ID.
	 */
	$recipients = apply_filters( 'afq_signup_notify_emails', array( get_option( 'admin_email' ) ), $post_id );
	$recipients = array_filter( array_map( 'sanitize_email', (array) $recipients ) );

	if ( ! $recipients ) {
		return;
	}

	$subject = 'ثبت‌نام جدید: ' . $data['first_name'] . ' ' . $data['last_name'] . ' (' . $signup_type . ')';

	$rows = '<tr><td style="padding:8px 12px;border:1px solid #e3e6ea;background:#f5f6f8;font-weight:bold;">نوع ثبت‌نام</td><td style="padding:8px 12px;border:1px solid #e3e6ea;">' . esc_html( $signup_type ) . '</td></tr>';

	foreach ( afq_signup_get_sections() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			if ( ! isset( $data[ $key ] ) ) {
				continue;
			}
			$rows .= '<tr><td style="padding:8px 12px;border:1px solid #e3e6ea;background:#f5f6f8;font-weight:bold;white-space:nowrap;">' . esc_html( $field['label'] ) . '</td>'
				. '<td style="padding:8px 12px;border:1px solid #e3e6ea;">' . esc_html( $data[ $key ] ) . '</td></tr>';
		}
	}

	$admin_link = admin_url( 'post.php?post=' . $post_id . '&action=edit' );

	$body = '<div dir="rtl" style="font-family:Tahoma,Arial,sans-serif;font-size:13px;color:#222;">'
		. '<h2 style="font-size:16px;">یک ثبت‌نام جدید در سایت انجام شد</h2>'
		. '<table style="border-collapse:collapse;width:100%;max-width:640px;">' . $rows . '</table>'
		. '<p style="margin-top:16px;"><a href="' . esc_url( $admin_link ) . '">مشاهده در پیشخوان</a></p>'
		. '</div>';

	wp_mail(
		$recipients,
		$subject,
		$body,
		array( 'Content-Type: text/html; charset=UTF-8' )
	);
}

/* -------------------------------------------------------------------------
 * Admin — Submission View + Status
 * ---------------------------------------------------------------------- */

/**
 * Register admin meta boxes for submissions.
 */
function afq_signup_add_meta_boxes() {

	add_meta_box(
		'afq_signup_data',
		'اطلاعات ثبت‌نام',
		'afq_signup_data_meta_box',
		'afq_signup',
		'normal',
		'high'
	);

	add_meta_box(
		'afq_signup_status',
		'وضعیت ثبت‌نام',
		'afq_signup_status_meta_box',
		'afq_signup',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'afq_signup_add_meta_boxes' );

/**
 * Render read-only submission data.
 *
 * @param WP_Post $post Current post object.
 */
function afq_signup_data_meta_box( $post ) {

	$signup_type = get_post_meta( $post->ID, '_afq_signup_signup_type', true );
	?>
	<div class="afq-signup-admin">

		<div class="afq-signup-admin__section">
			<h4>نوع ثبت‌نام</h4>
			<div class="afq-signup-admin__rows">
				<div class="afq-signup-admin__row">
					<span>نوع ثبت‌نام</span>
					<strong><?php echo esc_html( $signup_type ? $signup_type : '—' ); ?></strong>
				</div>
			</div>
		</div>

		<?php foreach ( afq_signup_get_sections() as $section ) : ?>
			<div class="afq-signup-admin__section">
				<h4><?php echo esc_html( $section['label'] ); ?></h4>
				<div class="afq-signup-admin__rows">
					<?php foreach ( $section['fields'] as $key => $field ) : ?>
						<?php $value = get_post_meta( $post->ID, '_afq_signup_' . $key, true ); ?>
						<div class="afq-signup-admin__row">
							<span><?php echo esc_html( $field['label'] ); ?></span>
							<strong><?php echo $value ? nl2br( esc_html( $value ) ) : '—'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>

	</div>
	<?php
}

/**
 * Render status select meta box.
 *
 * @param WP_Post $post Current post object.
 */
function afq_signup_status_meta_box( $post ) {

	wp_nonce_field( 'afq_signup_status_save', 'afq_signup_status_nonce' );

	$current = get_post_meta( $post->ID, '_afq_signup_status', true );
	$current = $current ? $current : 'pending';
	?>
	<select name="afq_signup_status_value" class="afq-signup-status-select" style="width:100%;">
		<?php foreach ( afq_signup_get_statuses() as $status_key => $status ) : ?>
			<option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $current, $status_key ); ?>>
				<?php echo esc_html( $status['label'] ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<p class="description" style="margin-top:8px;">پس از تغییر، دکمه «بروزرسانی» را بزنید.</p>
	<?php
}

/**
 * Save status.
 *
 * @param int $post_id Post ID.
 */
function afq_signup_save_status( $post_id ) {

	if ( ! isset( $_POST['afq_signup_status_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['afq_signup_status_nonce'] ), 'afq_signup_status_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['afq_signup_status_value'] ) ) {
		$status = sanitize_key( $_POST['afq_signup_status_value'] );

		if ( array_key_exists( $status, afq_signup_get_statuses() ) ) {
			update_post_meta( $post_id, '_afq_signup_status', $status );
		}
	}
}
add_action( 'save_post_afq_signup', 'afq_signup_save_status' );

/**
 * Admin list columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function afq_signup_admin_columns( $columns ) {
	return array(
		'cb'          => $columns['cb'],
		'title'       => 'نام و نام خانوادگی',
		'signup_type' => 'نوع ثبت‌نام',
		'mobile'      => 'تلفن همراه',
		'status'      => 'وضعیت',
		'date'        => 'تاریخ',
	);
}
add_filter( 'manage_afq_signup_posts_columns', 'afq_signup_admin_columns' );

/**
 * Admin list column values.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function afq_signup_admin_column_values( $column, $post_id ) {

	if ( 'signup_type' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_afq_signup_signup_type', true ) );
	}

	if ( 'mobile' === $column ) {
		echo '<span style="direction:ltr;unicode-bidi:embed;">' . esc_html( get_post_meta( $post_id, '_afq_signup_mobile', true ) ) . '</span>';
	}

	if ( 'status' === $column ) {
		$statuses = afq_signup_get_statuses();
		$status   = get_post_meta( $post_id, '_afq_signup_status', true );
		$status   = isset( $statuses[ $status ] ) ? $status : 'pending';

		printf(
			'<span style="display:inline-block;padding:3px 12px;border-radius:999px;font-size:11px;font-weight:600;color:%1$s;background:%2$s;">%3$s</span>',
			esc_attr( $statuses[ $status ]['color'] ),
			esc_attr( $statuses[ $status ]['bg'] ),
			esc_html( $statuses[ $status ]['label'] )
		);
	}
}
add_action( 'manage_afq_signup_posts_custom_column', 'afq_signup_admin_column_values', 10, 2 );

/**
 * Status filter dropdown in admin list.
 */
function afq_signup_admin_filter() {

	$screen = get_current_screen();

	if ( ! $screen || 'edit-afq_signup' !== $screen->id ) {
		return;
	}

	$current = isset( $_GET['afq_signup_status'] ) ? sanitize_key( $_GET['afq_signup_status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<select name="afq_signup_status">
		<option value="">همه وضعیت‌ها</option>
		<?php foreach ( afq_signup_get_statuses() as $status_key => $status ) : ?>
			<option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $current, $status_key ); ?>>
				<?php echo esc_html( $status['label'] ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}
add_action( 'restrict_manage_posts', 'afq_signup_admin_filter' );

/**
 * Apply the status filter to the admin list query.
 *
 * @param WP_Query $query Current query.
 */
function afq_signup_admin_filter_query( $query ) {

	if ( ! is_admin() || ! $query->is_main_query() || 'afq_signup' !== $query->get( 'post_type' ) ) {
		return;
	}

	$status = isset( $_GET['afq_signup_status'] ) ? sanitize_key( $_GET['afq_signup_status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( $status && array_key_exists( $status, afq_signup_get_statuses() ) ) {
		$query->set( 'meta_key', '_afq_signup_status' );
		$query->set( 'meta_value', $status );
	}
}
add_action( 'pre_get_posts', 'afq_signup_admin_filter_query' );

/**
 * Admin styles for the submission screen.
 *
 * @param string $hook Current admin page hook.
 */
function afq_signup_admin_assets( $hook ) {

	if ( 'post.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'afq_signup' !== $screen->post_type ) {
		return;
	}

	wp_register_style( 'afq-signup-admin', false, array(), '1.0.0' );
	wp_enqueue_style( 'afq-signup-admin' );
	wp_add_inline_style(
		'afq-signup-admin',
		'
		#afq_signup_data.postbox, #afq_signup_status.postbox { border: none; border-radius: 14px; overflow: hidden; box-shadow: 0 10px 30px rgba(15,20,30,0.08); }
		#afq_signup_data .postbox-header, #afq_signup_status .postbox-header { background: linear-gradient(135deg, #14181f 0%, #232a36 60%, #2c3442 100%); border-bottom: none; }
		#afq_signup_data .postbox-header .hndle, #afq_signup_status .postbox-header .hndle { color: #e8cf9a; font-size: 13px; }
		#afq_signup_data .postbox-header .handle-actions button, #afq_signup_status .postbox-header .handle-actions button { color: rgba(255,255,255,0.7); }
		#afq_signup_data .inside { margin: 0; padding: 16px; background: #fbfbfc; }
		#afq_signup_status .inside { padding: 12px; }
		.afq-signup-admin__section { background: #fff; border: 1px solid #eef0f3; border-radius: 12px; padding: 14px; margin-bottom: 14px; box-shadow: 0 2px 8px rgba(15,20,30,0.04); }
		.afq-signup-admin__section:last-child { margin-bottom: 0; }
		.afq-signup-admin__section h4 { margin: 0 0 12px; font-size: 13px; color: #b8934a; }
		.afq-signup-admin__rows { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 8px 24px; }
		.afq-signup-admin__row { display: flex; gap: 10px; align-items: baseline; padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
		.afq-signup-admin__row span { color: #8b95a3; font-size: 12px; flex-shrink: 0; min-width: 110px; }
		.afq-signup-admin__row strong { color: #1f2937; font-weight: 600; overflow-wrap: anywhere; }
		'
	);
}
add_action( 'admin_enqueue_scripts', 'afq_signup_admin_assets' );

/* -------------------------------------------------------------------------
 * Frontend Assets
 * ---------------------------------------------------------------------- */

/**
 * Frontend inline CSS (silver palette).
 *
 * @return string
 */
function afq_signup_inline_css() {
	return '
	form.afq-signup,
	form.afq-signup * {
		box-sizing: border-box;
	}

	form.afq-signup {
		display: flex;
		flex-direction: column;
		gap: 22px;
	}

	form.afq-signup fieldset.afq-signup__section {
		border: 1px solid #e7ebf0;
		border-radius: 16px;
		background: #fff;
		padding: 10px 22px 22px;
		margin: 0;
		box-shadow: 0 6px 20px rgba(15, 20, 30, 0.05);
	}
	form.afq-signup legend.afq-signup__section-title {
		padding: 6px 16px;
		background: linear-gradient(135deg, #14181f 0%, #232a36 60%, #2c3442 100%);
		color: #e7ebf0;
		font-size: 13.5px;
		font-weight: 700;
		border-radius: 999px;
	}
	form.afq-signup p.afq-signup__hint {
		margin: 4px 0 12px !important;
		font-size: 12.5px;
		color: #6b7280;
	}

	form.afq-signup div.afq-signup__grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
		gap: 16px;
	}
	form.afq-signup div.afq-signup__field {
		display: flex;
		flex-direction: column;
		gap: 6px;
	}
	form.afq-signup div.afq-signup__field--wide {
		grid-column: 1 / -1;
	}
	form.afq-signup div.afq-signup__field label {
		font-size: 12.5px;
		font-weight: 600;
		color: #374151;
	}
	form.afq-signup div.afq-signup__field label b {
		color: #c62828;
	}
	form.afq-signup div.afq-signup__field input,
	form.afq-signup div.afq-signup__field select,
	form.afq-signup div.afq-signup__field textarea {
		width: 100% !important;
		border: 1px solid #d9dee5 !important;
		border-radius: 10px !important;
		background: #fbfcfd !important;
		padding: 10px 13px !important;
		font-size: 13px !important;
		font-family: inherit !important;
		color: #1f2937 !important;
		box-shadow: 0 1px 2px rgba(15, 20, 30, 0.03) !important;
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}
	form.afq-signup div.afq-signup__field input:focus,
	form.afq-signup div.afq-signup__field select:focus,
	form.afq-signup div.afq-signup__field textarea:focus {
		border-color: #aab3bf !important;
		box-shadow: 0 0 0 3px rgba(170, 179, 191, 0.25) !important;
		outline: none !important;
	}
	form.afq-signup div.afq-signup__field input.is-ltr {
		direction: ltr;
		text-align: left;
	}
	form.afq-signup div.afq-signup__field select:disabled {
		background: #eef1f5 !important;
		color: #4b5563 !important;
		cursor: not-allowed;
	}
	form.afq-signup div.afq-signup__field.has-error input,
	form.afq-signup div.afq-signup__field.has-error select,
	form.afq-signup div.afq-signup__field.has-error textarea {
		border-color: #d9534f !important;
	}
	form.afq-signup span.afq-signup__error {
		display: none;
		font-size: 11.5px;
		color: #c62828;
	}
	form.afq-signup div.afq-signup__field.has-error span.afq-signup__error {
		display: block;
	}

	form.afq-signup input.afq-signup__hp {
		position: absolute !important;
		left: -9999px !important;
		opacity: 0;
		height: 0;
		width: 0;
		padding: 0;
		border: none;
	}

	form.afq-signup div.afq-signup__footer {
		display: flex;
		justify-content: center;
	}
	form.afq-signup button.afq-signup__submit {
		min-width: 220px;
		margin: 0 !important;
		padding: 13px 34px !important;
		border: none !important;
		border-radius: 999px !important;
		background: linear-gradient(135deg, #2c3442, #14181f) !important;
		color: #e7ebf0 !important;
		font-size: 14px !important;
		font-weight: 700;
		font-family: inherit !important;
		cursor: pointer;
		box-shadow: 0 10px 24px rgba(20, 24, 31, 0.3) !important;
		transition: filter 0.15s ease, transform 0.15s ease;
	}
	form.afq-signup button.afq-signup__submit:hover {
		filter: brightness(1.25);
		transform: translateY(-1px);
	}
	form.afq-signup button.afq-signup__submit:disabled {
		opacity: 0.7;
		cursor: wait;
	}
	form.afq-signup span.afq-signup__submit-loading {
		display: none;
	}
	form.afq-signup.is-loading span.afq-signup__submit-loading {
		display: inline;
	}
	form.afq-signup.is-loading span.afq-signup__submit-text {
		display: none;
	}

	form.afq-signup div.afq-signup__message {
		display: none;
		padding: 15px 18px;
		border-radius: 12px;
		font-size: 13.5px;
		font-weight: 600;
		text-align: center;
	}
	form.afq-signup div.afq-signup__message.is-success {
		display: block;
		background: #e5f5ec;
		color: #1f7a4d;
		border: 1px solid #bfe5d0;
	}
	form.afq-signup div.afq-signup__message.is-error {
		display: block;
		background: #f5e7e5;
		color: #a04a41;
		border: 1px solid #e8c8c3;
	}

	@media (max-width: 640px) {
		form.afq-signup fieldset.afq-signup__section {
			padding: 8px 16px 16px;
		}
		form.afq-signup button.afq-signup__submit {
			width: 100%;
		}
	}
	';
}

/**
 * Frontend inline JS (vanilla, no dependencies).
 *
 * @return string
 */
function afq_signup_inline_js() {
	return <<<'JS'
( function () {
	'use strict';

	document.addEventListener( 'submit', function ( e ) {
		var form = e.target.closest( 'form.afq-signup' );
		if ( ! form ) {
			return;
		}

		e.preventDefault();

		if ( form.classList.contains( 'is-loading' ) ) {
			return;
		}

		var message = form.querySelector( '.afq-signup__message' );
		var button  = form.querySelector( '.afq-signup__submit' );

		/* Reset previous state. */
		message.className = 'afq-signup__message';
		message.textContent = '';
		form.querySelectorAll( '.afq-signup__field.has-error' ).forEach( function ( field ) {
			field.classList.remove( 'has-error' );
			field.querySelector( '.afq-signup__error' ).textContent = '';
		} );

		/* Client-side required check. */
		var firstInvalid = null;
		form.querySelectorAll( '[name]' ).forEach( function ( input ) {
			if ( 'afq_signup_website' === input.name ) {
				return;
			}
			if ( '' === input.value.trim() ) {
				var field = input.closest( '.afq-signup__field' );
				if ( field ) {
					field.classList.add( 'has-error' );
					field.querySelector( '.afq-signup__error' ).textContent = 'این فیلد ضروری است.';
					if ( ! firstInvalid ) {
						firstInvalid = field;
					}
				}
			}
		} );

		if ( firstInvalid ) {
			firstInvalid.scrollIntoView( { behavior: 'smooth', block: 'center' } );
			return;
		}

		form.classList.add( 'is-loading' );
		button.disabled = true;

		var formData = new FormData( form );
		formData.append( 'action', 'afq_signup_submit' );
		formData.append( 'nonce', afqSignupCfg.nonce );

		fetch( afqSignupCfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( json ) {

				if ( json && json.success ) {
					form.reset();
					message.classList.add( 'is-success' );
					message.textContent = json.data.message;
					message.scrollIntoView( { behavior: 'smooth', block: 'center' } );
					return;
				}

				if ( json && json.data && json.data.errors ) {
					var first = null;

					Object.keys( json.data.errors ).forEach( function ( key ) {
						var field = form.querySelector( '[data-afq-field="' + key + '"]' );
						if ( field ) {
							field.classList.add( 'has-error' );
							field.querySelector( '.afq-signup__error' ).textContent = json.data.errors[ key ];
							if ( ! first ) {
								first = field;
							}
						}
					} );

					if ( first ) {
						first.scrollIntoView( { behavior: 'smooth', block: 'center' } );
					}

					message.classList.add( 'is-error' );
					message.textContent = 'برخی فیلدها نیاز به اصلاح دارند.';
					return;
				}

				message.classList.add( 'is-error' );
				message.textContent = ( json && json.data && json.data.message ) ? json.data.message : 'خطا در ارسال. دوباره تلاش کنید.';
			} )
			.catch( function () {
				message.classList.add( 'is-error' );
				message.textContent = 'خطا در ارسال. دوباره تلاش کنید.';
			} )
			.finally( function () {
				form.classList.remove( 'is-loading' );
				button.disabled = false;
			} );
	} );
} )();
JS;
}