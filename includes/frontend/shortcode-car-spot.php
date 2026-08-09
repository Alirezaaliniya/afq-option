<?php
/**
 * [afq_car_spot] — Car spot image with hotspot modals.
 *
 * Requires afq_car_get_spec_sections() (config.php).
 *
 * Usage:
 *   [afq_car_spot]                                  → current car post
 *   [afq_car_spot id="123"]                         → specific car
 *   [afq_car_spot pos_engine="20,25"]               → override button position
 *
 * Position attributes (values are "left,top" in percent of the image):
 *   pos_engine, pos_performance, pos_dimensions, pos_features
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Parse a "left,top" percent pair.
 *
 * @param string $value    Raw attribute value.
 * @param array  $fallback Fallback pair.
 * @return array { left: float, top: float }
 */
function afq_car_spot_parse_pos( $value, $fallback ) {

	$parts = array_map( 'trim', explode( ',', (string) $value ) );

	if ( 2 !== count( $parts ) || ! is_numeric( $parts[0] ) || ! is_numeric( $parts[1] ) ) {
		return $fallback;
	}

	return array(
		'left' => min( 100, max( 0, (float) $parts[0] ) ),
		'top'  => min( 100, max( 0, (float) $parts[1] ) ),
	);
}

/**
 * Empty state markup ("no specs entered").
 *
 * @return string
 */
function afq_car_spot_empty_html() {

	wp_enqueue_style( 'afq-car-spot' );

	return '<div class="afq-spot afq-spot--empty"><p class="afq-spot__empty-text">مشخصات خودرو وارد نشده</p></div>';
}

/**
 * Spot shortcode callback.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function afq_car_spot_shortcode( $atts ) {

	if ( ! function_exists( 'afq_car_get_spec_sections' ) ) {
		return afq_car_spot_empty_html();
	}

	$atts = shortcode_atts(
		array(
			'id'              => 0,
			'pos_engine'      => '22,28',
			'pos_performance' => '72,22',
			'pos_dimensions'  => '30,68',
			'pos_features'    => '78,62',
		),
		$atts,
		'afq_car_spot'
	);

	$post_id = $atts['id'] ? absint( $atts['id'] ) : get_the_ID();

	if ( ! $post_id || 'afq_car' !== get_post_type( $post_id ) ) {
		return '';
	}

	$spot_image_id = absint( get_post_meta( $post_id, '_afq_car_image_spot', true ) );

	if ( ! $spot_image_id ) {
		return afq_car_spot_empty_html();
	}

	$defaults = array(
		'engine'      => array(
			'left' => 22,
			'top'  => 28,
		),
		'performance' => array(
			'left' => 72,
			'top'  => 22,
		),
		'dimensions'  => array(
			'left' => 30,
			'top'  => 68,
		),
		'features'    => array(
			'left' => 78,
			'top'  => 62,
		),
	);

	$positions = array();
	foreach ( $defaults as $section_id => $fallback ) {
		$positions[ $section_id ] = afq_car_spot_parse_pos( $atts[ 'pos_' . $section_id ], $fallback );
	}

	/* Collect filled fields per section. */
	$sections = array();

	foreach ( afq_car_get_spec_sections() as $section_id => $section ) {

		$rows = array();

		foreach ( $section['fields'] as $key => $field ) {
			$value = get_post_meta( $post_id, $key, true );

			if ( '' === $value ) {
				continue;
			}

			if ( 'select' === $field['type'] && isset( $field['options'][ $value ] ) ) {
				$value = $field['options'][ $value ];
			}

			$rows[] = array(
				'label' => $field['label'],
				'value' => $value,
				'type'  => $field['type'],
			);
		}

		if ( $rows ) {
			$sections[ $section_id ] = array(
				'label' => $section['label'],
				'rows'  => $rows,
			);
		}
	}

	if ( ! $sections ) {
		return afq_car_spot_empty_html();
	}

	wp_enqueue_style( 'afq-car-spot' );
	wp_enqueue_script( 'afq-car-spot' );

	static $instance = 0;
	$instance++;
	$uid = 'afq-spot-' . $post_id . '-' . $instance;

	ob_start();
	?>
	<div class="afq-spot" id="<?php echo esc_attr( $uid ); ?>">

		<div class="afq-spot__stage">
			<?php echo wp_get_attachment_image( $spot_image_id, 'full', false, array( 'class' => 'afq-spot__image' ) ); ?>

			<?php foreach ( $sections as $section_id => $section ) : ?>
				<?php $pos = $positions[ $section_id ]; ?>
				<button type="button"
					class="afq-spot__btn"
					style="left:<?php echo esc_attr( $pos['left'] ); ?>%;top:<?php echo esc_attr( $pos['top'] ); ?>%;"
					data-afq-modal="<?php echo esc_attr( $uid . '-' . $section_id ); ?>"
					aria-haspopup="dialog"
					aria-label="<?php echo esc_attr( $section['label'] ); ?>">
					<span class="afq-spot__btn-dot"></span>
					<span class="afq-spot__btn-label"><?php echo esc_html( $section['label'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>

		<?php foreach ( $sections as $section_id => $section ) : ?>
			<div class="afq-spot-modal"
				id="<?php echo esc_attr( $uid . '-' . $section_id ); ?>"
				role="dialog"
				aria-modal="true"
				aria-label="<?php echo esc_attr( $section['label'] ); ?>"
				aria-hidden="true">

				<div class="afq-spot-modal__overlay" data-afq-close></div>

				<div class="afq-spot-modal__card">
					<div class="afq-spot-modal__header">
						<span class="afq-spot-modal__title"><?php echo esc_html( $section['label'] ); ?></span>
						<button type="button" class="afq-spot-modal__close" data-afq-close aria-label="بستن">&times;</button>
					</div>

					<div class="afq-spot-modal__body">
						<?php foreach ( $section['rows'] as $row ) : ?>

							<?php if ( 'textarea' === $row['type'] ) : ?>
								<?php $items = array_filter( array_map( 'trim', explode( "\n", $row['value'] ) ) ); ?>
								<div class="afq-spot-modal__row afq-spot-modal__row--list">
									<span class="afq-spot-modal__label"><?php echo esc_html( $row['label'] ); ?></span>
									<ul class="afq-spot-modal__list">
										<?php foreach ( $items as $item ) : ?>
											<li><?php echo esc_html( $item ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php else : ?>
								<div class="afq-spot-modal__row">
									<span class="afq-spot-modal__label"><?php echo esc_html( $row['label'] ); ?></span>
									<span class="afq-spot-modal__value"><?php echo esc_html( $row['value'] ); ?></span>
								</div>
							<?php endif; ?>

						<?php endforeach; ?>
					</div>
				</div>

			</div>
		<?php endforeach; ?>

	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'afq_car_spot', 'afq_car_spot_shortcode' );
