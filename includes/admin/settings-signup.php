<?php
/**
 * Signup form settings screen.
 *
 * Lets an administrator mark each form field as required or optional.
 * The choice is stored as a list of *optional* keys, so the default
 * (empty option) keeps every field required — exactly how the form
 * behaved before this screen existed.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the settings submenu under "ثبت‌نام‌ها".
 */
function afq_signup_add_settings_page() {
	add_submenu_page(
		'edit.php?post_type=afq_signup',
		'تنظیمات فرم ثبت‌نام',
		'تنظیمات فرم',
		'manage_options',
		'afq-signup-settings',
		'afq_signup_render_settings_page'
	);
}
add_action( 'admin_menu', 'afq_signup_add_settings_page' );

/**
 * All configurable field keys, grouped for display.
 *
 * @return array<string,array{label:string,fields:array<string,string>}>
 */
function afq_signup_get_settings_groups() {

	$groups = array();

	foreach ( afq_signup_get_sections() as $section_id => $section ) {

		$fields = array();

		foreach ( $section['fields'] as $key => $field ) {
			$fields[ $key ] = $field['label'];
		}

		$groups[ $section_id ] = array(
			'label'  => $section['label'],
			'fields' => $fields,
		);
	}

	$groups['signup'] = array(
		'label'  => 'نوع ثبت‌نام',
		'fields' => array( 'signup_type' => 'نوع ثبت‌نام' ),
	);

	return $groups;
}

/**
 * Render the settings screen.
 */
function afq_signup_render_settings_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'شما دسترسی لازم برای مشاهده این صفحه را ندارید.' );
	}

	$optional = afq_signup_get_optional_fields();
	$groups   = afq_signup_get_settings_groups();

	$total    = 0;
	$required = 0;

	foreach ( $groups as $group ) {
		foreach ( $group['fields'] as $key => $label ) {
			$total++;
			if ( ! in_array( (string) $key, $optional, true ) ) {
				$required++;
			}
		}
	}
	?>
	<div class="wrap afq-signup-settings">

		<h1>تنظیمات فرم ثبت‌نام</h1>

		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p>تنظیمات ذخیره شد.</p></div>
		<?php endif; ?>

		<div class="afq-signup-settings__intro">
			<span class="dashicons dashicons-forms"></span>
			<div>
				<strong>کدام فیلدها پر کردنشان اجباری باشد؟</strong>
				<p>
					فیلدهای «ضروری» با ستاره قرمز نمایش داده می‌شوند و بدون آن‌ها فرم ارسال نمی‌شود.
					فیلدهای «اختیاری» را کاربر می‌تواند خالی بگذارد؛ در این صورت آن فیلد در ثبت‌نام
					ذخیره نمی‌شود و در ایمیل اطلاع‌رسانی هم نمی‌آید.
					در حال حاضر <strong><?php echo esc_html( $required ); ?></strong> فیلد از
					<strong><?php echo esc_html( $total ); ?></strong> فیلد ضروری است.
				</p>
			</div>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

			<input type="hidden" name="action" value="afq_signup_save_settings" />
			<?php wp_nonce_field( 'afq_signup_save_settings' ); ?>

			<div class="afq-signup-settings__bulk">
				<button type="button" class="button afq-signup-settings__all">همه ضروری</button>
				<button type="button" class="button afq-signup-settings__none">همه اختیاری</button>
			</div>

			<?php foreach ( $groups as $group_id => $group ) : ?>
				<div class="afq-signup-settings__card">
					<h2><?php echo esc_html( $group['label'] ); ?></h2>

					<div class="afq-signup-settings__grid">
						<?php foreach ( $group['fields'] as $key => $label ) : ?>
							<?php $is_required = ! in_array( (string) $key, $optional, true ); ?>
							<label class="afq-signup-settings__row">
								<span class="afq-signup-settings__name">
									<?php echo esc_html( $label ); ?>
									<code><?php echo esc_html( $key ); ?></code>
								</span>

								<span class="afq-signup-settings__switch">
									<input type="checkbox"
										name="afq_signup_required[]"
										value="<?php echo esc_attr( $key ); ?>"
										<?php checked( $is_required ); ?> />
									<span class="afq-signup-settings__slider"></span>
									<span class="afq-signup-settings__state"><?php echo $is_required ? 'ضروری' : 'اختیاری'; ?></span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<?php submit_button( 'ذخیره تنظیمات' ); ?>

		</form>
	</div>
	<?php
}

/**
 * Persist the settings form.
 */
function afq_signup_save_settings() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'شما دسترسی لازم برای انجام این کار را ندارید.' );
	}

	check_admin_referer( 'afq_signup_save_settings' );

	/* Checked boxes are the REQUIRED fields; everything else is optional. */
	$checked = isset( $_POST['afq_signup_required'] ) && is_array( $_POST['afq_signup_required'] )
		? array_map( 'sanitize_key', wp_unslash( $_POST['afq_signup_required'] ) )
		: array();

	$optional = array();

	foreach ( afq_signup_get_settings_groups() as $group ) {
		foreach ( array_keys( $group['fields'] ) as $key ) {
			if ( ! in_array( (string) $key, $checked, true ) ) {
				$optional[] = (string) $key;
			}
		}
	}

	update_option( AFQ_SIGNUP_OPTIONAL_OPTION, $optional, false );

	wp_safe_redirect(
		add_query_arg(
			array(
				'post_type' => 'afq_signup',
				'page'      => 'afq-signup-settings',
				'updated'   => 1,
			),
			admin_url( 'edit.php' )
		)
	);
	exit;
}
add_action( 'admin_post_afq_signup_save_settings', 'afq_signup_save_settings' );
