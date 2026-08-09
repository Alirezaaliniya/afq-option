<?php
/**
 * [afq_flipbook] — Render a PDF as a page-turning book.
 *
 *   [afq_flipbook url="https://site.ir/catalog.pdf"]
 *   [afq_flipbook id="1234"]                     → media library attachment
 *   [afq_flipbook url="..." title="کاتالوگ ۱۴۰۵" download="no" dir="ltr"]
 *   [afq_flipbook url="..." spread="no" height="70vh" start="3"]
 *
 * Rendering happens in the browser with Mozilla PDF.js (bundled, no CDN).
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve and validate the PDF address for the shortcode.
 *
 * @param string $url Raw url attribute.
 * @param int    $id  Attachment ID.
 * @return string Empty string when nothing usable was given.
 */
function afq_flipbook_resolve_url( $url, $id ) {

	if ( $id > 0 ) {
		$attached = wp_get_attachment_url( $id );

		if ( $attached && 'application/pdf' === get_post_mime_type( $id ) ) {
			return $attached;
		}

		return '';
	}

	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	/* Protocol-relative and root-relative addresses are fine; anything with
	 * another scheme (javascript:, data:) is not. */
	if ( preg_match( '#^[a-z][a-z0-9+.-]*:#i', $url ) && ! preg_match( '#^https?://#i', $url ) ) {
		return '';
	}

	$path = wp_parse_url( $url, PHP_URL_PATH );

	if ( ! $path || 'pdf' !== strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) ) {
		return '';
	}

	return esc_url_raw( $url );
}

/**
 * Sanitize a CSS length used for the book height.
 *
 * @param string $value Raw attribute.
 * @return string Safe CSS length, falling back to the default.
 */
function afq_flipbook_css_length( $value ) {

	$value = trim( (string) $value );

	return preg_match( '/^\d{1,4}(\.\d{1,2})?(px|vh|vw|rem|em|%)$/', $value ) ? $value : '78vh';
}

/**
 * Inline SVG icons for the toolbar.
 *
 * @param string $name Icon name.
 * @return string
 */
function afq_flipbook_icon( $name ) {

	$icons = array(
		'prev'     => '<path d="M15 5 8 12l7 7"/>',
		'next'     => '<path d="M9 5l7 7-7 7"/>',
		'first'    => '<path d="M18 5l-7 7 7 7M7 5v14"/>',
		'last'     => '<path d="M6 5l7 7-7 7M17 5v14"/>',
		'expand'   => '<path d="M9 4H4v5M4 4l6 6M15 4h5v5m0-5-6 6M9 20H4v-5m0 5 6-6m5 6h5v-5m0 5-6-6"/>',
		'download' => '<path d="M12 4v11m0 0 4-4m-4 4-4-4M5 19h14"/>',
	);

	$path = $icons[ $name ] ?? $icons['next'];

	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">' . $path . '</svg>';
}

/**
 * Flipbook shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function afq_flipbook_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'url'      => '',
			'id'       => 0,
			'title'    => '',
			'download' => 'yes',
			'dir'      => 'rtl',
			'spread'   => 'yes',
			'height'   => '78vh',
			'start'    => 1,
			'class'    => '',
		),
		$atts,
		'afq_flipbook'
	);

	$url = afq_flipbook_resolve_url( $atts['url'], absint( $atts['id'] ) );

	if ( '' === $url ) {
		/* Only editors see the hint; visitors just get nothing. */
		if ( current_user_can( 'edit_posts' ) ) {
			return '<div class="afq-book__notice">شورت‌کد کتاب: آدرس فایل PDF وارد نشده یا معتبر نیست.</div>';
		}

		return '';
	}

	$rtl    = 'ltr' !== strtolower( (string) $atts['dir'] );
	$spread = 'no' !== strtolower( (string) $atts['spread'] );
	$height = afq_flipbook_css_length( $atts['height'] );
	$start  = max( 1, absint( $atts['start'] ) );

	afq_flipbook_enqueue_assets();

	static $instance = 0;
	$instance++;

	$uid = 'afq-book-' . $instance;

	$classes = 'afq-book afq-book--' . ( $rtl ? 'rtl' : 'ltr' );

	if ( '' !== $atts['class'] ) {
		$classes .= ' ' . sanitize_html_class( $atts['class'] );
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr( $classes ); ?>"
		id="<?php echo esc_attr( $uid ); ?>"
		dir="<?php echo $rtl ? 'rtl' : 'ltr'; ?>"
		style="--afq-book-h:<?php echo esc_attr( $height ); ?>;"
		data-afq-book
		data-afq-pdf="<?php echo esc_url( $url ); ?>"
		data-afq-rtl="<?php echo $rtl ? '1' : '0'; ?>"
		data-afq-spread="<?php echo $spread ? '1' : '0'; ?>"
		data-afq-start="<?php echo esc_attr( $start ); ?>">

		<?php if ( '' !== $atts['title'] || 'no' !== $atts['download'] ) : ?>
			<div class="afq-book__bar">
				<span class="afq-book__title"><?php echo esc_html( $atts['title'] ); ?></span>

				<div class="afq-book__tools">
					<button type="button" class="afq-book__tool" data-afq-book-full aria-label="تمام‌صفحه">
						<?php echo afq_flipbook_icon( 'expand' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>

					<?php if ( 'no' !== $atts['download'] ) : ?>
						<a class="afq-book__tool" href="<?php echo esc_url( $url ); ?>" download target="_blank" rel="noopener" aria-label="دانلود فایل">
							<?php echo afq_flipbook_icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="afq-book__stage" data-afq-stage>

			<button type="button" class="afq-book__nav afq-book__nav--prev" data-afq-book-prev aria-label="صفحه قبل">
				<?php echo afq_flipbook_icon( $rtl ? 'next' : 'prev' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>

			<div class="afq-book__viewport" data-afq-viewport>
				<div class="afq-book__sheet afq-book__sheet--b" data-afq-sheet="b"></div>
				<div class="afq-book__sheet afq-book__sheet--a" data-afq-sheet="a"></div>

				<div class="afq-book__leaf" data-afq-leaf>
					<div class="afq-book__face afq-book__face--front" data-afq-face="front"></div>
					<div class="afq-book__face afq-book__face--back" data-afq-face="back"></div>
				</div>

				<div class="afq-book__spine" aria-hidden="true"></div>
			</div>

			<button type="button" class="afq-book__nav afq-book__nav--next" data-afq-book-next aria-label="صفحه بعد">
				<?php echo afq_flipbook_icon( $rtl ? 'prev' : 'next' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>

			<div class="afq-book__status" data-afq-status>
				<span class="afq-book__spinner" aria-hidden="true"></span>
				<span class="afq-book__status-text" data-afq-status-text>در حال بارگذاری کتاب…</span>
			</div>

		</div>

		<div class="afq-book__footer">
			<button type="button" class="afq-book__step" data-afq-book-first aria-label="اولین صفحه">
				<?php echo afq_flipbook_icon( $rtl ? 'last' : 'first' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>

			<div class="afq-book__counter">
				<input type="text" class="afq-book__page-input" data-afq-book-input
					value="۱" inputmode="numeric" aria-label="شماره صفحه" />
				<span class="afq-book__of">از</span>
				<b data-afq-book-total>—</b>
			</div>

			<button type="button" class="afq-book__step" data-afq-book-last aria-label="آخرین صفحه">
				<?php echo afq_flipbook_icon( $rtl ? 'first' : 'last' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>

	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'afq_flipbook', 'afq_flipbook_shortcode' );
