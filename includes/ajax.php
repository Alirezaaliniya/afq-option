<?php
/**
 * AJAX endpoints: province representatives and signup submissions.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Representatives by Province
 * ---------------------------------------------------------------------- */

/**
 * Render representative cards for a province term.
 *
 * @param int $term_id Province term ID.
 * @return string
 */
function afq_rep_render_cards( $term_id ) {

	$term = get_term( $term_id, 'afq_rep_province' );

	if ( ! $term instanceof WP_Term ) {
		return '';
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'afq_rep',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'afq_rep_province',
					'field'    => 'term_id',
					'terms'    => $term_id,
				),
			),
		)
	);

	ob_start();
	?>
	<h3 class="afq-repmap__province-title">نمایندگان استان <?php echo esc_html( $term->name ); ?></h3>

	<?php if ( ! $query->have_posts() ) : ?>
		<p class="afq-repmap__no-result">در این استان نمایندگی یا عاملیت فروش ثبت نشده است.</p>
		<?php
		return ob_get_clean();
	endif;

	$field_keys = array_keys( afq_rep_get_fields() );
	?>

	<div class="afq-rep-cards">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();

			$post_id = get_the_ID();
			$meta    = array();
			foreach ( $field_keys as $key ) {
				$meta[ $key ] = get_post_meta( $post_id, $key, true );
			}

			$phones = array_filter( array_map( 'trim', explode( "\n", (string) $meta['_afq_rep_phone'] ) ) );
			$faxes  = array_filter( array_map( 'trim', explode( "\n", (string) $meta['_afq_rep_fax'] ) ) );
			?>
			<article class="afq-rep-card">

				<div class="afq-rep-card__main">

					<div class="afq-rep-card__head">
						<h4 class="afq-rep-card__name"><?php the_title(); ?></h4>

						<?php if ( $meta['_afq_rep_type'] ) : ?>
							<span class="afq-rep-card__badge"><?php echo esc_html( $meta['_afq_rep_type'] ); ?></span>
						<?php endif; ?>

						<?php if ( $meta['_afq_rep_grade'] ) : ?>
							<span class="afq-rep-card__badge afq-rep-card__badge--grade">گرید <?php echo esc_html( $meta['_afq_rep_grade'] ); ?></span>
						<?php endif; ?>
					</div>

					<div class="afq-rep-card__rows">

						<?php if ( $meta['_afq_rep_code'] ) : ?>
							<div class="afq-rep-card__row"><span>کد نمایندگی</span><strong><?php echo esc_html( $meta['_afq_rep_code'] ); ?></strong></div>
						<?php endif; ?>

						<?php if ( $meta['_afq_rep_city'] ) : ?>
							<div class="afq-rep-card__row"><span>شهر</span><strong><?php echo esc_html( $meta['_afq_rep_city'] ); ?></strong></div>
						<?php endif; ?>

						<?php if ( $meta['_afq_rep_area_code'] ) : ?>
							<div class="afq-rep-card__row"><span>کد شهر</span><strong><?php echo esc_html( $meta['_afq_rep_area_code'] ); ?></strong></div>
						<?php endif; ?>

						<?php if ( $phones ) : ?>
							<div class="afq-rep-card__row"><span>تلفن</span>
								<strong class="afq-rep-card__phones">
									<?php foreach ( $phones as $i => $phone ) : ?>
										<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a><?php echo ( $i < count( $phones ) - 1 ) ? ' ، ' : ''; ?>
									<?php endforeach; ?>
								</strong>
							</div>
						<?php endif; ?>

						<?php if ( $faxes ) : ?>
							<div class="afq-rep-card__row"><span>فکس</span>
								<strong class="afq-rep-card__phones"><?php echo esc_html( implode( ' ، ', $faxes ) ); ?></strong>
							</div>
						<?php endif; ?>

						<?php if ( $meta['_afq_rep_postal'] ) : ?>
							<div class="afq-rep-card__row"><span>کد پستی</span><strong><?php echo esc_html( $meta['_afq_rep_postal'] ); ?></strong></div>
						<?php endif; ?>

						<?php if ( $meta['_afq_rep_address'] ) : ?>
							<div class="afq-rep-card__row afq-rep-card__row--full"><span>آدرس</span><strong><?php echo esc_html( $meta['_afq_rep_address'] ); ?></strong></div>
						<?php endif; ?>

					</div>

				</div>

			</article>
		<?php endwhile; ?>
	</div>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}

/**
 * AJAX handler: representatives of a province.
 */
function afq_rep_ajax_filter() {

	check_ajax_referer( 'afq_rep_map', 'nonce' );

	$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;

	if ( ! $term_id ) {
		wp_send_json_error();
	}

	$html = afq_rep_render_cards( $term_id );

	if ( '' === $html ) {
		wp_send_json_error();
	}

	wp_send_json_success( $html );
}
add_action( 'wp_ajax_afq_rep_filter', 'afq_rep_ajax_filter' );
add_action( 'wp_ajax_nopriv_afq_rep_filter', 'afq_rep_ajax_filter' );

/* -------------------------------------------------------------------------
 * Signup Submit
 * ---------------------------------------------------------------------- */

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
	 * @param string[] $emails  Recipient email addresses.
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
