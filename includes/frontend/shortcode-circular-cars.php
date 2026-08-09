<?php
/**
 * [afq_circular_cars] — Cars attached to a sales circular ("بخش‌نامه فروش").
 *
 *   [afq_circular_cars]           → cars of the current circular
 *   [afq_circular_cars id="123"]  → cars of a specific circular
 *
 * Also registers the Elementor custom queries for circulars.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Circular cars shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function afq_circular_cars_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'id' => 0,
		),
		$atts,
		'afq_circular_cars'
	);

	$circular_id = $atts['id'] ? absint( $atts['id'] ) : get_the_ID();

	if ( ! $circular_id || 'afq_circular' !== get_post_type( $circular_id ) ) {
		return '';
	}

	$car_ids = afq_circular_get_car_ids( $circular_id );

	if ( ! $car_ids ) {
		return '';
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'afq_car',
			'post_status'            => 'publish',
			'posts_per_page'         => count( $car_ids ),
			'post__in'               => $car_ids,
			'orderby'                => 'post__in',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	$sold_out = ( 'yes' === get_post_meta( $circular_id, 'afq_circular_sold_out', true ) );

	wp_enqueue_style( 'afq-circular-cars' );

	ob_start();
	?>
	<div class="afq-circ-cars<?php echo $sold_out ? ' is-soldout' : ''; ?>">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();

			$car_id        = get_the_ID();
			$normal_id     = absint( get_post_meta( $car_id, '_afq_car_image_normal', true ) );
			$hover_id      = absint( get_post_meta( $car_id, '_afq_car_image_hover', true ) );
			$price_regular = get_post_meta( $car_id, '_afq_car_price_regular', true );
			$price_sale    = get_post_meta( $car_id, '_afq_car_price_sale', true );

			if ( ! $normal_id && has_post_thumbnail( $car_id ) ) {
				$normal_id = get_post_thumbnail_id( $car_id );
			}
			?>
			<a class="afq-circ-car" href="<?php echo esc_url( get_permalink( $car_id ) ); ?>">

				<?php if ( $sold_out ) : ?>
					<span class="afq-circ-car__ribbon">اتمام فروش</span>
				<?php endif; ?>

				<?php if ( $normal_id ) : ?>
					<span class="afq-circ-car__media<?php echo $hover_id ? ' has-hover' : ''; ?>">
						<?php echo wp_get_attachment_image( $normal_id, 'large', false, array( 'class' => 'afq-circ-car__img afq-circ-car__img--normal' ) ); ?>
						<?php if ( $hover_id ) : ?>
							<?php echo wp_get_attachment_image( $hover_id, 'large', false, array( 'class' => 'afq-circ-car__img afq-circ-car__img--hover' ) ); ?>
						<?php endif; ?>
					</span>
				<?php endif; ?>

				<span class="afq-circ-car__body">
					<span class="afq-circ-car__name"><?php the_title(); ?></span>

					<?php if ( $price_regular || $price_sale ) : ?>
						<span class="afq-circ-car__prices">
							<?php if ( $price_sale ) : ?>
								<?php if ( $price_regular ) : ?>
									<del><?php echo esc_html( $price_regular ); ?></del>
								<?php endif; ?>
								<ins><?php echo esc_html( $price_sale ); ?></ins>
							<?php else : ?>
								<ins><?php echo esc_html( $price_regular ); ?></ins>
							<?php endif; ?>
						</span>
					<?php endif; ?>

					<span class="afq-circ-car__cta">مشاهده خودرو</span>
				</span>

			</a>
		<?php endwhile; ?>
	</div>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'afq_circular_cars', 'afq_circular_cars_shortcode' );

/* -------------------------------------------------------------------------
 * Elementor Custom Queries
 *
 * Usage in Elementor (Loop Grid / Posts widget → Query → Query ID):
 *   afq_circular_sold       → بخش‌نامه‌هایی که اتمام فروش خورده‌اند
 *   afq_circular_available  → بخش‌نامه‌هایی که اتمام فروش نخورده‌اند
 * ---------------------------------------------------------------------- */

/**
 * Elementor query: sold-out circulars.
 *
 * @param WP_Query $query The Elementor widget query.
 */
function afq_circular_query_sold( $query ) {

	$query->set( 'post_type', 'afq_circular' );

	$meta_query   = (array) $query->get( 'meta_query' );
	$meta_query[] = array(
		'key'   => 'afq_circular_sold_out',
		'value' => 'yes',
	);

	$query->set( 'meta_query', $meta_query );
}
add_action( 'elementor/query/afq_circular_sold', 'afq_circular_query_sold' );

/**
 * Elementor query: available (not sold-out) circulars.
 *
 * The sold-out meta is deleted when the toggle is off, so "available"
 * means the meta does not exist OR its value is not 'yes'.
 *
 * @param WP_Query $query The Elementor widget query.
 */
function afq_circular_query_available( $query ) {

	$query->set( 'post_type', 'afq_circular' );

	$meta_query   = (array) $query->get( 'meta_query' );
	$meta_query[] = array(
		'relation' => 'OR',
		array(
			'key'     => 'afq_circular_sold_out',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => 'afq_circular_sold_out',
			'value'   => 'yes',
			'compare' => '!=',
		),
	);

	$query->set( 'meta_query', $meta_query );
}
add_action( 'elementor/query/afq_circular_available', 'afq_circular_query_available' );
