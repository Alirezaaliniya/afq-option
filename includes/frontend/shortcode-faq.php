<?php
/**
 * [afq_faq_list] — FAQ accordion ("سوالات متداول").
 *
 *   [afq_faq_list]                  → all items (accordion)
 *   [afq_faq_list count="8"]        → limit items
 *   [afq_faq_list open_first="no"]  → first item closed by default (default: yes)
 *   [afq_faq_list schema="no"]      → disable FAQPage JSON-LD schema (default: yes)
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * FAQ accordion shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function afq_faq_list_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'count'      => -1,
			'open_first' => 'yes',
			'schema'     => 'yes',
		),
		$atts,
		'afq_faq_list'
	);

	$query = new WP_Query(
		array(
			'post_type'              => 'afq_faq',
			'post_status'            => 'publish',
			'posts_per_page'         => (int) $atts['count'],
			'orderby'                => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	wp_enqueue_style( 'afq-faq-list' );
	wp_enqueue_script( 'afq-faq-list' );

	static $instance = 0;
	$instance++;

	$open_first  = ( 'no' !== $atts['open_first'] );
	$schema_data = array();

	ob_start();
	?>
	<div class="afq-faq" id="afq-faq-<?php echo esc_attr( $instance ); ?>">

		<?php
		$index = 0;
		while ( $query->have_posts() ) :
			$query->the_post();

			$question = get_the_title();
			$answer   = apply_filters( 'the_content', get_the_content() );
			$is_open  = ( $open_first && 0 === $index );
			$item_id  = 'afq-faq-' . $instance . '-item-' . $index;

			if ( '' === trim( wp_strip_all_tags( $answer ) ) ) {
				continue;
			}

			$schema_data[] = array(
				'question' => $question,
				'answer'   => wp_strip_all_tags( $answer ),
			);
			?>
			<div class="afq-faq__item<?php echo $is_open ? ' is-open' : ''; ?>">

				<button type="button"
					class="afq-faq__question"
					aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr( $item_id ); ?>">
					<span class="afq-faq__question-text"><?php echo esc_html( $question ); ?></span>
					<span class="afq-faq__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
					</span>
				</button>

				<div class="afq-faq__answer" id="<?php echo esc_attr( $item_id ); ?>" role="region">
					<div class="afq-faq__answer-inner">
						<?php echo wp_kses_post( $answer ); ?>
					</div>
				</div>

			</div>
			<?php
			$index++;
		endwhile;
		wp_reset_postdata();
		?>

	</div>

	<?php if ( 'no' !== $atts['schema'] && $schema_data ) : ?>
		<?php
		$schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array(),
		);

		foreach ( $schema_data as $item ) {
			$schema['mainEntity'][] = array(
				'@type'          => 'Question',
				'name'           => $item['question'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['answer'],
				),
			);
		}
		?>
		<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>
	<?php endif; ?>

	<?php
	return ob_get_clean();
}
add_shortcode( 'afq_faq_list', 'afq_faq_list_shortcode' );
