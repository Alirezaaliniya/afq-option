<?php
/**
 * Customer Voice settings screen: required/optional fields, notification
 * e-mails, upload limits and the on-screen messages.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the settings submenu under «صدای مشتری».
 */
function afq_request_add_settings_page() {
	add_submenu_page(
		'edit.php?post_type=afq_request',
		'تنظیمات صدای مشتری',
		'تنظیمات',
		'manage_options',
		'afq-request-settings',
		'afq_request_render_settings_page'
	);
}
add_action( 'admin_menu', 'afq_request_add_settings_page' );

/**
 * Render a required/optional switch row.
 *
 * @param string $key      Field key.
 * @param string $label    Field label.
 * @param bool   $required Current state.
 */
function afq_request_setting_switch( $key, $label, $required ) {
	?>
	<label class="afq-settings__row">
		<span class="afq-settings__name">
			<?php echo esc_html( $label ); ?>
			<code><?php echo esc_html( $key ); ?></code>
		</span>
		<span class="afq-settings__switch">
			<input type="checkbox" data-afq-toggle
				name="afq_request_required[]"
				value="<?php echo esc_attr( $key ); ?>"
				<?php checked( $required ); ?> />
			<span class="afq-settings__slider"></span>
			<span class="afq-settings__state" data-on="ضروری" data-off="اختیاری"><?php echo $required ? 'ضروری' : 'اختیاری'; ?></span>
		</span>
	</label>
	<?php
}

/**
 * Render an on/off switch for a settings key.
 *
 * @param string $name    Input name.
 * @param string $label   Label.
 * @param bool   $enabled Current state.
 */
function afq_request_setting_toggle( $name, $label, $enabled ) {
	?>
	<label class="afq-settings__field afq-settings__field--toggle">
		<span class="afq-settings__name"><?php echo esc_html( $label ); ?></span>
		<span class="afq-settings__switch">
			<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $enabled ); ?> />
			<span class="afq-settings__slider"></span>
			<span class="afq-settings__state" data-on="فعال" data-off="غیرفعال"><?php echo $enabled ? 'فعال' : 'غیرفعال'; ?></span>
		</span>
	</label>
	<?php
}

/**
 * Render the settings screen.
 */
function afq_request_render_settings_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'شما دسترسی لازم برای مشاهده این صفحه را ندارید.' );
	}

	$settings = afq_request_get_settings();
	$optional = afq_request_get_optional_fields();
	$sections = afq_request_get_sections();

	$total    = 0;
	$required = 0;

	foreach ( $sections as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$total++;
			if ( ! in_array( (string) $key, $optional, true ) ) {
				$required++;
			}
		}
	}
	?>
	<div class="wrap afq-settings afq-request-settings">

		<h1>تنظیمات صدای مشتری</h1>

		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p>تنظیمات ذخیره شد.</p></div>
		<?php endif; ?>

		<div class="afq-settings__intro">
			<span class="dashicons dashicons-format-chat"></span>
			<div>
				<strong>فرم صدای مشتری</strong>
				<p>
					شورت‌کد فرم: <code style="background:rgba(255,255,255,.12);color:#fff;">[afq_request_form]</code>
					&nbsp;—&nbsp; شورت‌کد پیگیری: <code style="background:rgba(255,255,255,.12);color:#fff;">[afq_request_track]</code><br />
					فیلدهای «ضروری» با ستاره قرمز نمایش داده می‌شوند و بدون آن‌ها فرم ارسال نمی‌شود.
					در حال حاضر <strong><?php echo esc_html( $required ); ?></strong> فیلد از
					<strong><?php echo esc_html( $total ); ?></strong> فیلد ضروری است.
				</p>
			</div>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

			<input type="hidden" name="action" value="afq_request_save_settings" />
			<?php wp_nonce_field( 'afq_request_save_settings' ); ?>

			<div class="afq-settings__bulk">
				<button type="button" class="button afq-settings__all">همه ضروری</button>
				<button type="button" class="button afq-settings__none">همه اختیاری</button>
			</div>

			<?php foreach ( $sections as $section ) : ?>
				<div class="afq-settings__card">
					<h2><?php echo esc_html( $section['label'] ); ?></h2>
					<div class="afq-settings__grid">
						<?php foreach ( $section['fields'] as $key => $field ) : ?>
							<?php afq_request_setting_switch( $key, $field['label'], ! in_array( (string) $key, $optional, true ) ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="afq-settings__card">
				<h2>ایمیل اطلاع‌رسانی</h2>
				<div class="afq-settings__fields">

					<?php afq_request_setting_toggle( 'afq_request[notify_enabled]', 'ارسال ایمیل به مدیران پس از ثبت درخواست', ! empty( $settings['notify_enabled'] ) ); ?>

					<div class="afq-settings__field">
						<label for="afq_notify_emails">گیرندگان ایمیل</label>
						<input type="text" id="afq_notify_emails" name="afq_request[notify_emails]"
							value="<?php echo esc_attr( $settings['notify_emails'] ); ?>" dir="ltr" />
						<p class="description">چند ایمیل را با کاما جدا کنید.</p>
					</div>

					<div class="afq-settings__field afq-settings__field--wide">
						<label for="afq_notify_subject">موضوع ایمیل مدیران</label>
						<input type="text" id="afq_notify_subject" name="afq_request[notify_subject]"
							value="<?php echo esc_attr( $settings['notify_subject'] ); ?>" />
						<p class="description afq-settings__tokens">
							{code} {name} {mobile} {type} {subject}
						</p>
					</div>

				</div>
			</div>

			<div class="afq-settings__card">
				<h2>ایمیل تأیید برای مشتری</h2>
				<div class="afq-settings__fields">

					<?php afq_request_setting_toggle( 'afq_request[ack_enabled]', 'ارسال ایمیل تأیید به مشتری (در صورت وارد کردن ایمیل)', ! empty( $settings['ack_enabled'] ) ); ?>

					<div class="afq-settings__field">
						<label for="afq_ack_subject">موضوع ایمیل مشتری</label>
						<input type="text" id="afq_ack_subject" name="afq_request[ack_subject]"
							value="<?php echo esc_attr( $settings['ack_subject'] ); ?>" />
					</div>

					<div class="afq-settings__field afq-settings__field--wide">
						<label for="afq_ack_message">متن ایمیل مشتری</label>
						<textarea id="afq_ack_message" name="afq_request[ack_message]" rows="5"><?php echo esc_textarea( $settings['ack_message'] ); ?></textarea>
						<p class="description afq-settings__tokens">{code} {name} {mobile} {type} {subject}</p>
					</div>

				</div>
			</div>

			<div class="afq-settings__card">
				<h2>پیوست فایل</h2>
				<div class="afq-settings__fields">

					<?php afq_request_setting_toggle( 'afq_request[upload_enabled]', 'فعال بودن آپلود فایل در فرم', ! empty( $settings['upload_enabled'] ) ); ?>

					<div class="afq-settings__field">
						<label for="afq_upload_exts">پسوندهای مجاز</label>
						<input type="text" id="afq_upload_exts" name="afq_request[upload_exts]"
							value="<?php echo esc_attr( $settings['upload_exts'] ); ?>" dir="ltr" />
						<p class="description">با کاما جدا کنید. فقط پسوندهایی که وردپرس می‌شناسد پذیرفته می‌شوند.</p>
					</div>

					<div class="afq-settings__field">
						<label for="afq_upload_max">حداکثر حجم (مگابایت)</label>
						<input type="number" id="afq_upload_max" name="afq_request[upload_max_mb]" min="1" max="64"
							value="<?php echo esc_attr( (int) $settings['upload_max_mb'] ); ?>" dir="ltr" />
						<p class="description">سقف سرور نیز اعمال می‌شود (upload_max_filesize در PHP).</p>
					</div>

				</div>
			</div>

			<div class="afq-settings__card">
				<h2>متن‌ها و محدودیت‌ها</h2>
				<div class="afq-settings__fields">

					<div class="afq-settings__field">
						<label for="afq_desc_min">حداقل کاراکتر شرح درخواست</label>
						<input type="number" id="afq_desc_min" name="afq_request[desc_min]" min="0" max="2000"
							value="<?php echo esc_attr( (int) $settings['desc_min'] ); ?>" dir="ltr" />
					</div>

					<div class="afq-settings__field">
						<label for="afq_desc_max">حداکثر کاراکتر شرح درخواست</label>
						<input type="number" id="afq_desc_max" name="afq_request[desc_max]" min="100" max="10000"
							value="<?php echo esc_attr( (int) $settings['desc_max'] ); ?>" dir="ltr" />
					</div>

					<div class="afq-settings__field afq-settings__field--wide">
						<label for="afq_success_title">عنوان پیام موفقیت</label>
						<input type="text" id="afq_success_title" name="afq_request[success_title]"
							value="<?php echo esc_attr( $settings['success_title'] ); ?>" />
					</div>

					<div class="afq-settings__field afq-settings__field--wide">
						<label for="afq_success_message">متن پیام موفقیت</label>
						<textarea id="afq_success_message" name="afq_request[success_message]" rows="3"><?php echo esc_textarea( $settings['success_message'] ); ?></textarea>
					</div>

				</div>
			</div>

			<?php submit_button( 'ذخیره تنظیمات' ); ?>

		</form>
	</div>
	<?php
}

/**
 * Persist the settings form.
 */
function afq_request_save_settings() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'شما دسترسی لازم برای انجام این کار را ندارید.' );
	}

	check_admin_referer( 'afq_request_save_settings' );

	/* ---- Required / optional ---- */

	$checked = isset( $_POST['afq_request_required'] ) && is_array( $_POST['afq_request_required'] )
		? array_map( 'sanitize_key', wp_unslash( $_POST['afq_request_required'] ) )
		: array();

	$optional = array();

	foreach ( array_keys( afq_request_get_fields() ) as $key ) {
		if ( ! in_array( (string) $key, $checked, true ) ) {
			$optional[] = (string) $key;
		}
	}

	update_option( AFQ_REQUEST_OPTIONAL_OPTION, $optional, false );

	/* ---- Everything else ---- */

	$raw = isset( $_POST['afq_request'] ) && is_array( $_POST['afq_request'] )
		? wp_unslash( $_POST['afq_request'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: array();

	$settings = afq_request_get_settings();

	$settings['notify_enabled'] = empty( $raw['notify_enabled'] ) ? 0 : 1;
	$settings['ack_enabled']    = empty( $raw['ack_enabled'] ) ? 0 : 1;
	$settings['upload_enabled'] = empty( $raw['upload_enabled'] ) ? 0 : 1;

	if ( isset( $raw['notify_emails'] ) ) {
		$emails = array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', (string) $raw['notify_emails'] ) ) ) );
		$settings['notify_emails'] = implode( ', ', $emails );
	}

	foreach ( array( 'notify_subject', 'ack_subject', 'success_title', 'upload_exts' ) as $key ) {
		if ( isset( $raw[ $key ] ) ) {
			$settings[ $key ] = sanitize_text_field( $raw[ $key ] );
		}
	}

	foreach ( array( 'ack_message', 'success_message' ) as $key ) {
		if ( isset( $raw[ $key ] ) ) {
			$settings[ $key ] = sanitize_textarea_field( $raw[ $key ] );
		}
	}

	$settings['upload_max_mb'] = isset( $raw['upload_max_mb'] ) ? min( 64, max( 1, absint( $raw['upload_max_mb'] ) ) ) : 10;
	$settings['desc_min']      = isset( $raw['desc_min'] ) ? min( 2000, absint( $raw['desc_min'] ) ) : 100;
	$settings['desc_max']      = isset( $raw['desc_max'] ) ? min( 10000, max( 100, absint( $raw['desc_max'] ) ) ) : 1000;

	/* A minimum longer than the maximum would lock the form. */
	if ( $settings['desc_min'] > $settings['desc_max'] ) {
		$settings['desc_min'] = $settings['desc_max'];
	}

	update_option( AFQ_REQUEST_SETTINGS_OPTION, $settings, false );

	wp_safe_redirect(
		add_query_arg(
			array(
				'post_type' => 'afq_request',
				'page'      => 'afq-request-settings',
				'updated'   => 1,
			),
			admin_url( 'edit.php' )
		)
	);
	exit;
}
add_action( 'admin_post_afq_request_save_settings', 'afq_request_save_settings' );
