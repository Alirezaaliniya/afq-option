<?php
/**
 * [afq_request_track] — Look up a customer voice request by tracking code.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Request tracking shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function afq_request_track_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'title' => 'پیگیری درخواست',
		),
		$atts,
		'afq_request_track'
	);

	afq_request_enqueue_assets();

	static $instance = 0;
	$instance++;

	$uid = 'afq-track-' . $instance;

	ob_start();
	?>
	<div class="afq-track" id="<?php echo esc_attr( $uid ); ?>">

		<form class="afq-track__form" novalidate>

			<?php if ( '' !== $atts['title'] ) : ?>
				<h3 class="afq-track__title"><?php echo esc_html( $atts['title'] ); ?></h3>
			<?php endif; ?>

			<p class="afq-track__intro">وضعیت درخواست خود را با وارد کردن اطلاعات زیر مشاهده کنید.</p>

			<div class="afq-track__field">
				<label for="<?php echo esc_attr( $uid ); ?>-mobile">شماره موبایل</label>
				<input type="text" id="<?php echo esc_attr( $uid ); ?>-mobile" name="mobile"
					class="is-ltr" placeholder="شماره موبایل خود را وارد کنید" autocomplete="off" required />
			</div>

			<div class="afq-track__field">
				<label for="<?php echo esc_attr( $uid ); ?>-code">کد رهگیری</label>
				<input type="text" id="<?php echo esc_attr( $uid ); ?>-code" name="code"
					class="is-ltr" placeholder="مثال: VOC-20260805-001254" autocomplete="off" required />
			</div>

			<button type="submit" class="afq-track__submit">
				<span class="afq-track__submit-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
				</span>
				<span class="afq-track__submit-text">پیگیری درخواست</span>
				<span class="afq-track__submit-loading">در حال بررسی...</span>
			</button>

			<div class="afq-track__message" role="alert"></div>

			<div class="afq-track__result" hidden>
				<div class="afq-track__row">
					<span>کد رهگیری</span>
					<strong class="is-ltr" data-afq-track-code></strong>
				</div>
				<div class="afq-track__row">
					<span>وضعیت</span>
					<strong><span class="afq-track__badge" data-afq-track-status></span></strong>
				</div>
				<div class="afq-track__row" data-afq-track-row="type">
					<span>نوع درخواست</span>
					<strong data-afq-track-type></strong>
				</div>
				<div class="afq-track__row" data-afq-track-row="subject">
					<span>موضوع</span>
					<strong data-afq-track-subject></strong>
				</div>
				<div class="afq-track__row">
					<span>تاریخ ثبت</span>
					<strong data-afq-track-date></strong>
				</div>
				<div class="afq-track__reply" data-afq-track-row="reply">
					<span>پاسخ کارشناسان</span>
					<p data-afq-track-reply></p>
				</div>
			</div>

		</form>

	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'afq_request_track', 'afq_request_track_shortcode' );
