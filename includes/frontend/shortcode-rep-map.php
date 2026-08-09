<?php
/**
 * [afq_rep_map] — Iran map with province spots and AJAX representative cards.
 *
 *   [afq_rep_map map="123"]
 *   [afq_rep_map map="https://example.com/iran-map.png"]
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Iran map shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function afq_rep_map_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'map' => '',
		),
		$atts,
		'afq_rep_map'
	);

	/* Resolve map image (attachment ID or direct URL). */
	$map_url = '';
	if ( is_numeric( $atts['map'] ) ) {
		$map_url = wp_get_attachment_image_url( absint( $atts['map'] ), 'full' );
	} elseif ( $atts['map'] ) {
		$map_url = esc_url_raw( $atts['map'] );
	}

	if ( ! $map_url ) {
		return '<div class="afq-repmap afq-repmap--empty"><p>تصویر نقشه تنظیم نشده است. شناسه تصویر را در شورت‌کد وارد کنید: [afq_rep_map map="123"]</p></div>';
	}

	/* Provinces that have representatives and a spot position. */
	$terms = get_terms(
		array(
			'taxonomy'   => 'afq_rep_province',
			'hide_empty' => true,
		)
	);

	$spots = array();

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$left = get_term_meta( $term->term_id, 'afq_rep_spot_left', true );
			$top  = get_term_meta( $term->term_id, 'afq_rep_spot_top', true );

			if ( '' === $left || '' === $top || ! is_numeric( $left ) || ! is_numeric( $top ) ) {
				continue;
			}

			$spots[] = array(
				'term' => $term,
				'left' => (float) $left,
				'top'  => (float) $top,
			);
		}
	}

	wp_enqueue_style( 'afq-rep-map' );
	wp_enqueue_script( 'afq-rep-map' );
	wp_localize_script(
		'afq-rep-map',
		'afqRepMapCfg',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'afq_rep_map' ),
		)
	);

	ob_start();
	?>
	<div class="afq-repmap">

		<div class="afq-repmap__stage">
			<img class="afq-repmap__image" src="<?php echo esc_url( $map_url ); ?>" alt="نقشه نمایندگی‌ها" />

			<?php foreach ( $spots as $spot ) : ?>
				<button type="button"
					class="afq-repmap__spot"
					style="left:<?php echo esc_attr( $spot['left'] ); ?>%;top:<?php echo esc_attr( $spot['top'] ); ?>%;"
					data-afq-term="<?php echo esc_attr( $spot['term']->term_id ); ?>"
					aria-label="نمایندگان استان <?php echo esc_attr( $spot['term']->name ); ?>">
					<span class="afq-repmap__spot-dot"></span>
					<span class="afq-repmap__spot-label"><?php echo esc_html( $spot['term']->name ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="afq-repmap__results" aria-live="polite">
			<p class="afq-repmap__intro">برای مشاهده نمایندگان، استان مورد نظر را روی نقشه انتخاب کنید.</p>
		</div>

	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'afq_rep_map', 'afq_rep_map_shortcode' );
