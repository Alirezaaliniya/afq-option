
/**
 * AFQ FAQ — Frequently Asked Questions ("سوالات متداول")
 * Add this code to your theme's functions.php.
 *
 * Post type: afq_faq (no archive, no public single page — content is
 * rendered only via the [afq_faq_list] shortcode).
 * Question = post title, Answer = post content (editor).
 *
 * Shortcode:
 *   [afq_faq_list]                  → all items (accordion)
 *   [afq_faq_list count="8"]        → limit items
 *   [afq_faq_list open_first="no"]  → first item closed by default (default: yes)
 *   [afq_faq_list schema="no"]      → disable FAQPage JSON-LD schema (default: yes)
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Post Type
 * ---------------------------------------------------------------------- */

/**
 * Register FAQ post type.
 */
function afq_register_faq_post_type() {

	$labels = array(
		'name'               => 'سوالات متداول',
		'singular_name'      => 'سوال متداول',
		'menu_name'          => 'سوالات متداول',
		'add_new'            => 'افزودن سوال',
		'add_new_item'       => 'افزودن سوال جدید',
		'edit_item'          => 'ویرایش سوال',
		'new_item'           => 'سوال جدید',
		'view_item'          => 'مشاهده',
		'search_items'       => 'جستجوی سوال',
		'not_found'          => 'سوالی یافت نشد',
		'not_found_in_trash' => 'سوالی در زباله‌دان یافت نشد',
		'all_items'          => 'همه سوالات',
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-editor-help',
		'menu_position'       => 22,
		'supports'            => array( 'title', 'editor', 'page-attributes' ),
		'capability_type'     => 'post',
		'hierarchical'        => false,
	);

	register_post_type( 'afq_faq', $args );
}
add_action( 'init', 'afq_register_faq_post_type' );

/* -------------------------------------------------------------------------
 * Shortcode
 * ---------------------------------------------------------------------- */

/**
 * Register empty front asset handles.
 */
function afq_faq_register_front_assets() {
	wp_register_style( 'afq-faq-list', false, array(), '1.0.0' );
	wp_register_script( 'afq-faq-list', false, array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'afq_faq_register_front_assets' );

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
			'post_type'      => 'afq_faq',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $atts['count'],
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
			'no_found_rows'  => true,
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	wp_enqueue_style( 'afq-faq-list' );
	wp_add_inline_style( 'afq-faq-list', afq_faq_inline_css() );

	wp_enqueue_script( 'afq-faq-list' );
	wp_add_inline_script( 'afq-faq-list', afq_faq_inline_js() );

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

/**
 * Frontend inline CSS.
 *
 * @return string
 */
function afq_faq_inline_css() {
	return '
	div.afq-faq,
	div.afq-faq * {
		box-sizing: border-box;
	}

	div.afq-faq {
		display: flex;
		flex-direction: column;
		gap: 14px;
	}

	div.afq-faq div.afq-faq__item {
		background: #fff;
		border: 1px solid #eef0f3;
		border-radius: 14px;
		overflow: hidden;
		box-shadow: 0 4px 14px rgba(15, 20, 30, 0.05);
		transition: box-shadow 0.2s ease, border-color 0.2s ease;
	}
	div.afq-faq div.afq-faq__item.is-open {
		border-color: #c7cdd6;
		box-shadow: 0 12px 28px rgba(139, 149, 163, 0.18);
	}

	/* Question button (hard reset against theme/Elementor button styles) */
	div.afq-faq button.afq-faq__question {
		display: flex !important;
		align-items: center;
		justify-content: space-between;
		gap: 14px;
		width: 100% !important;
		min-height: 0 !important;
		margin: 0 !important;
		padding: 18px 20px !important;
		border: none !important;
		border-radius: 0 !important;
		background: transparent !important;
		box-shadow: none !important;
		color: #1f2937 !important;
		font-family: inherit !important;
		font-size: 14.5px !important;
		font-weight: 700;
		line-height: 1.7 !important;
		text-align: start;
		letter-spacing: normal !important;
		text-transform: none !important;
		cursor: pointer;
	}
	div.afq-faq button.afq-faq__question:hover {
		background: transparent !important;
		color: #1f2937 !important;
	}
	div.afq-faq span.afq-faq__question-text {
		flex: 1;
		min-width: 0;
	}
	div.afq-faq span.afq-faq__icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 32px;
		height: 32px;
		border-radius: 50%;
		background: linear-gradient(135deg, #d7dce3, #9aa3b0);
		color: #14181f;
		flex-shrink: 0;
		transition: transform 0.3s ease;
	}
	div.afq-faq span.afq-faq__icon svg {
		width: 15px;
		height: 15px;
	}
	div.afq-faq div.afq-faq__item.is-open span.afq-faq__icon {
		transform: rotate(45deg);
	}

	/* Answer (animated open/close) */
	div.afq-faq div.afq-faq__answer {
		display: grid;
		grid-template-rows: 0fr;
		transition: grid-template-rows 0.3s ease;
	}
	div.afq-faq div.afq-faq__item.is-open div.afq-faq__answer {
		grid-template-rows: 1fr;
	}
	div.afq-faq div.afq-faq__answer-inner {
		min-height: 0;
		overflow: hidden;
	}
	div.afq-faq div.afq-faq__item.is-open div.afq-faq__answer-inner {
		padding: 0 20px 18px;
	}
	div.afq-faq div.afq-faq__answer-inner,
	div.afq-faq div.afq-faq__answer-inner p {
		font-size: 13.5px;
		line-height: 2.1;
		color: #4b5563;
	}
	div.afq-faq div.afq-faq__answer-inner p {
		margin: 0 0 10px;
	}
	div.afq-faq div.afq-faq__answer-inner p:last-child {
		margin-bottom: 0;
	}
	div.afq-faq div.afq-faq__answer-inner ul,
	div.afq-faq div.afq-faq__answer-inner ol {
		margin: 0 0 10px;
		padding-inline-start: 20px;
	}
	div.afq-faq div.afq-faq__answer-inner img {
		max-width: 100%;
		height: auto;
		border-radius: 10px;
	}

	@media (max-width: 640px) {
		div.afq-faq button.afq-faq__question {
			padding: 15px 16px !important;
			font-size: 13.5px !important;
		}
		div.afq-faq div.afq-faq__item.is-open div.afq-faq__answer-inner {
			padding: 0 16px 15px;
		}
		div.afq-faq span.afq-faq__icon {
			width: 28px;
			height: 28px;
		}
	}
	';
}

/**
 * Frontend inline JS (vanilla, no dependencies).
 *
 * @return string
 */
function afq_faq_inline_js() {
	return '
	( function () {
		"use strict";

		document.addEventListener( "click", function ( e ) {
			var btn = e.target.closest( ".afq-faq__question" );
			if ( ! btn ) {
				return;
			}

			var item = btn.closest( ".afq-faq__item" );
			var list = btn.closest( ".afq-faq" );
			var open = item.classList.contains( "is-open" );

			/* Close other items in the same list (accordion behavior). */
			list.querySelectorAll( ".afq-faq__item.is-open" ).forEach( function ( openItem ) {
				if ( openItem !== item ) {
					openItem.classList.remove( "is-open" );
					openItem.querySelector( ".afq-faq__question" ).setAttribute( "aria-expanded", "false" );
				}
			} );

			item.classList.toggle( "is-open", ! open );
			btn.setAttribute( "aria-expanded", open ? "false" : "true" );
		} );
	} )();
	';
}