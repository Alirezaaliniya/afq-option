<?php
/**
 * [afq_signup_form] — Registration form ("مراحل ثبت نام").
 *
 *   [afq_signup_form]                                  → default types: عادی، جانبازان، اقامتی
 *   [afq_signup_form types="عادی,جانبازان,اقامتی"]     → selectable types
 *   [afq_signup_form type="اقامتی"]                    → ONE fixed, locked type
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

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
	wp_enqueue_script( 'afq-signup-form' );
	wp_localize_script(
		'afq-signup-form',
		'afqSignupCfg',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'afq_signup_submit' ),
		)
	);

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
