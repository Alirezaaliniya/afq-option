<?php
/**
 * [afq_request_form] — Customer Voice request form (صدای مشتری).
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline SVG icons used by the section headers.
 *
 * @param string $name Icon name.
 * @return string
 */
function afq_request_icon( $name ) {

	$icons = array(
		'user' => '<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2c-4 0-7 2-7 4.5V20h14v-1.5C19 16 16 14 12 14Z"/>',
		'car'  => '<path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11m-14 0h14m-14 0a2 2 0 0 0-2 2v3h3m13-5a2 2 0 0 1 2 2v3h-3m-12 0h12m-12 0v2H5v-2m14 0v2h-2v-2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
		'pin'  => '<path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11Zm0-8.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z"/>',
		'chat' => '<path d="M4 5h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H9l-5 4V6a1 1 0 0 1 1-1Z"/>',
	);

	$path = $icons[ $name ] ?? $icons['chat'];

	return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">' . $path . '</svg>';
}

/**
 * Render one form field.
 *
 * @param string $key   Field key.
 * @param array  $field Field definition.
 */
function afq_request_render_field( $key, $field ) {

	$field_id    = 'afq-voc-' . $key;
	$is_required = ! empty( $field['required'] );
	$is_wide     = ! empty( $field['wide'] );
	$is_ltr      = ! empty( $field['ltr'] );
	$required_at = $is_required ? ' required' : '';
	?>
	<div class="afq-voc__field<?php echo $is_wide ? ' afq-voc__field--wide' : ''; ?>"
		data-afq-field="<?php echo esc_attr( $key ); ?>"
		data-afq-required="<?php echo $is_required ? '1' : '0'; ?>">

		<label for="<?php echo esc_attr( $field_id ); ?>">
			<?php echo esc_html( $field['label'] ); ?>
			<?php if ( $is_required ) : ?>
				<b>*</b>
			<?php endif; ?>
		</label>

		<?php if ( 'checkboxes' === $field['type'] ) : ?>
			<div class="afq-voc__checks">
				<?php foreach ( $field['options'] as $option ) : ?>
					<label class="afq-voc__check">
						<input type="checkbox" name="<?php echo esc_attr( $key ); ?>[]" value="<?php echo esc_attr( $option ); ?>" />
						<span><?php echo esc_html( $option ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>

		<?php elseif ( ! empty( $field['city_of'] ) ) : ?>
			<select id="<?php echo esc_attr( $field_id ); ?>"
				name="<?php echo esc_attr( $key ); ?>"
				class="afq-voc__city"
				data-afq-city-of="<?php echo esc_attr( $field['city_of'] ); ?>"
				<?php echo esc_attr( $required_at ); ?>>
				<option value="">ابتدا استان را انتخاب کنید</option>
			</select>
			<input type="text" class="afq-voc__city-other" style="display:none;"
				placeholder="نام شهر را وارد کنید" autocomplete="off" />

		<?php elseif ( ! empty( $field['model_of'] ) ) : ?>
			<select id="<?php echo esc_attr( $field_id ); ?>"
				name="<?php echo esc_attr( $key ); ?>"
				class="afq-voc__model"
				data-afq-model-of="<?php echo esc_attr( $field['model_of'] ); ?>"
				<?php echo esc_attr( $required_at ); ?>>
				<option value="">انتخاب مدل</option>
			</select>

		<?php elseif ( 'select' === $field['type'] ) : ?>
			<select id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $key ); ?>"<?php echo esc_attr( $required_at ); ?>>
				<option value="">انتخاب کنید</option>
				<?php foreach ( $field['options'] as $option ) : ?>
					<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
				<?php endforeach; ?>
			</select>

		<?php elseif ( 'textarea' === $field['type'] ) : ?>
			<textarea id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $key ); ?>" rows="5"<?php echo esc_attr( $required_at ); ?>
				<?php if ( ! empty( $field['max'] ) ) : ?>maxlength="<?php echo esc_attr( $field['max'] ); ?>"<?php endif; ?>
				<?php if ( ! empty( $field['min'] ) ) : ?>data-afq-min="<?php echo esc_attr( $field['min'] ); ?>"<?php endif; ?>
				placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"></textarea>
			<?php if ( ! empty( $field['max'] ) ) : ?>
				<span class="afq-voc__counter" data-afq-counter>0 / <?php echo esc_html( $field['max'] ); ?></span>
			<?php endif; ?>

		<?php elseif ( ! empty( $field['jalali'] ) ) : ?>
			<input type="text" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $key ); ?>"<?php echo esc_attr( $required_at ); ?>
				class="is-ltr afq-voc__jalali"
				data-afq-jalali
				data-afq-min-year="<?php echo esc_attr( $field['min_year'] ?? 1300 ); ?>"
				placeholder="<?php echo esc_attr( $field['placeholder'] ?? 'انتخاب تاریخ' ); ?>"
				readonly autocomplete="off" />

		<?php else : ?>
			<input type="text" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $key ); ?>"<?php echo esc_attr( $required_at ); ?>
				placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
				<?php echo $is_ltr ? 'class="is-ltr"' : ''; ?> autocomplete="off" />
		<?php endif; ?>

		<span class="afq-voc__error" aria-live="polite"></span>
	</div>
	<?php
}

/**
 * Customer voice form shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function afq_request_form_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'intro' => 'yes',
		),
		$atts,
		'afq_request_form'
	);

	$settings = afq_request_get_settings();
	$sections = afq_request_get_sections();
	$fields   = afq_request_get_fields();

	afq_request_enqueue_assets();

	static $instance = 0;
	$instance++;

	ob_start();
	?>
	<div class="afq-voc" id="afq-voc-<?php echo esc_attr( $instance ); ?>">

		<form class="afq-voc__form" novalidate enctype="multipart/form-data">

			<?php if ( 'no' !== $atts['intro'] ) : ?>
				<p class="afq-voc__intro">
					اگر نظر، پیشنهاد، انتقاد یا شکایتی دارید، لطفاً فرم زیر را تکمیل کنید.
					کارشناسان ما در کوتاه‌ترین زمان با شما تماس خواهند گرفت.
				</p>
			<?php endif; ?>

			<?php foreach ( $sections as $section ) : ?>
				<section class="afq-voc__section">

					<h3 class="afq-voc__section-title">
						<span class="afq-voc__section-icon"><?php echo afq_request_icon( $section['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php echo esc_html( $section['label'] ); ?>
					</h3>

					<div class="afq-voc__grid">
						<?php
						foreach ( $section['fields'] as $key => $field ) {

							/* Rendered in the bottom row, next to the upload box. */
							if ( 'contact_methods' === $key ) {
								continue;
							}

							afq_request_render_field( $key, $field );
						}
						?>
					</div>

				</section>
			<?php endforeach; ?>

			<div class="afq-voc__bottom">

				<?php if ( isset( $fields['contact_methods'] ) ) : ?>
					<div class="afq-voc__bottom-col">
						<?php afq_request_render_field( 'contact_methods', $fields['contact_methods'] ); ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $settings['upload_enabled'] ) ) : ?>
					<div class="afq-voc__bottom-col">
						<div class="afq-voc__field" data-afq-field="attachment" data-afq-required="0">
							<label for="afq-voc-file">پیوست مدارک</label>

							<div class="afq-voc__drop" data-afq-drop>
								<input type="file" id="afq-voc-file" name="afq_request_file"
									accept="<?php echo esc_attr( '.' . implode( ',.', afq_request_get_allowed_extensions() ) ); ?>" />
								<span class="afq-voc__drop-icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M12 16V4m0 0L8 8m4-4 4 4M4 17v2a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-2"/></svg>
								</span>
								<span class="afq-voc__drop-text">فایل خود را اینجا بکشید یا کلیک کنید</span>
								<span class="afq-voc__drop-hint">
									فرمت‌های مجاز: <?php echo esc_html( implode( '، ', afq_request_get_allowed_extensions() ) ); ?>
									(حداکثر <?php echo esc_html( (int) $settings['upload_max_mb'] ); ?> مگابایت)
								</span>
								<span class="afq-voc__drop-file" data-afq-drop-name></span>
							</div>

							<span class="afq-voc__error" aria-live="polite"></span>
						</div>
					</div>
				<?php endif; ?>

			</div>

			<?php
			if ( ! empty( $settings['terms_enabled'] ) ) :
				$terms_required = ! empty( $settings['terms_required'] );
				$terms_text     = (string) $settings['terms_text'];
				$terms_url      = (string) $settings['terms_url'];
				?>
				<div class="afq-voc__field afq-voc__field--terms" data-afq-field="terms"
					data-afq-required="<?php echo $terms_required ? '1' : '0'; ?>">
					<label class="afq-voc__check afq-voc__check--terms">
						<input type="checkbox" name="afq_request_terms" value="1" />
						<span>
							<?php if ( '' !== $terms_url ) : ?>
								<a href="<?php echo esc_url( $terms_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $terms_text ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $terms_text ); ?>
							<?php endif; ?>
							<?php if ( $terms_required ) : ?>
								<b>*</b>
							<?php endif; ?>
						</span>
					</label>
					<span class="afq-voc__error" aria-live="polite"></span>
				</div>
			<?php endif; ?>

			<input type="text" name="afq_request_website" class="afq-voc__hp" tabindex="-1" autocomplete="off" />

			<div class="afq-voc__footer">
				<button type="submit" class="afq-voc__submit">
					<span class="afq-voc__submit-text">ثبت درخواست</span>
					<span class="afq-voc__submit-loading">در حال ارسال...</span>
				</button>
			</div>

			<div class="afq-voc__message" role="alert"></div>

		</form>

		<?php /* Shown only after a successful submit — opened by request-form.js. */ ?>
		<div class="afq-voc__modal" role="dialog" aria-modal="true" aria-hidden="true"
			data-afq-modal-for="afq-voc-<?php echo esc_attr( $instance ); ?>"
			aria-labelledby="afq-voc-success-title-<?php echo esc_attr( $instance ); ?>">

			<div class="afq-voc__modal-overlay" data-afq-success-close></div>

			<div class="afq-voc__success">

				<button type="button" class="afq-voc__success-close" data-afq-success-close aria-label="بستن">&times;</button>

				<span class="afq-voc__success-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="m5 13 4 4L19 7"/></svg>
				</span>
				<h3 class="afq-voc__success-title" id="afq-voc-success-title-<?php echo esc_attr( $instance ); ?>"></h3>
				<p class="afq-voc__success-text"></p>
				<div class="afq-voc__success-code">
					<span>کد رهگیری شما</span>
					<strong data-afq-success-code></strong>
				</div>
				<button type="button" class="afq-voc__again">ثبت درخواست جدید</button>
			</div>
		</div>

	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'afq_request_form', 'afq_request_form_shortcode' );
