
/**
 * AFQ Car — Spot Image Shortcode
 * Add this code to your theme's functions.php.
 *
 * Requires afq_car_get_spec_sections() (specs meta box code) to be loaded.
 *
 * Usage:
 *   [afq_car_spot]                                  → current car post
 *   [afq_car_spot id="123"]                         → specific car
 *   [afq_car_spot pos_engine="20,25"]               → override button position
 *
 * Position attributes (values are "left,top" in percent of the image):
 *   pos_engine, pos_performance, pos_dimensions, pos_features
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register empty front asset handles (inline CSS/JS attached on demand).
 */
function afq_car_spot_register_assets() {
	wp_register_style( 'afq-car-spot', false, array(), '1.0.0' );
	wp_register_script( 'afq-car-spot', false, array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'afq_car_spot_register_assets' );

/**
 * Parse a "left,top" percent pair.
 *
 * @param string $value Raw attribute value.
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
	wp_add_inline_style( 'afq-car-spot', afq_car_spot_inline_css() );

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
		'engine'      => array( 'left' => 22, 'top' => 28 ),
		'performance' => array( 'left' => 72, 'top' => 22 ),
		'dimensions'  => array( 'left' => 30, 'top' => 68 ),
		'features'    => array( 'left' => 78, 'top' => 62 ),
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

	/* Assets (inline, printed in footer). */
	wp_enqueue_style( 'afq-car-spot' );
	wp_add_inline_style( 'afq-car-spot', afq_car_spot_inline_css() );

	wp_enqueue_script( 'afq-car-spot' );
	wp_add_inline_script( 'afq-car-spot', afq_car_spot_inline_js() );

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

/**
 * Inline frontend CSS.
 *
 * Selectors are intentionally high-specificity and fully scoped under
 * .afq-spot (element + class), with resets on properties Elementor /
 * theme kits commonly override on button, ul and img elements.
 *
 * @return string
 */
function afq_car_spot_inline_css() {
	return '
	div.afq-spot,
	div.afq-spot * {
		box-sizing: border-box;
	}

	/* Stage */
	div.afq-spot div.afq-spot__stage {
		position: relative;
		display: block;
		line-height: 0;
	}
	div.afq-spot div.afq-spot__stage img.afq-spot__image {
		width: 100% !important;
		max-width: 100% !important;
		height: auto !important;
		display: block !important;
		margin: 0 !important;
		border-radius: 0;
		box-shadow: none;
	}

	/* Hotspot button (hard reset against Elementor/theme button styles) */
	div.afq-spot div.afq-spot__stage button.afq-spot__btn {
		position: absolute !important;
		transform: translate(-50%, -50%);
		display: flex !important;
		align-items: center;
		gap: 8px;
		width: auto !important;
		min-width: 0 !important;
		min-height: 0 !important;
		margin: 0 !important;
		padding: 0 !important;
		border: none !important;
		border-radius: 0 !important;
		background: transparent !important;
		box-shadow: none !important;
		outline: none;
		color: inherit !important;
		font-family: inherit !important;
		line-height: 1 !important;
		text-transform: none !important;
		letter-spacing: normal !important;
		cursor: pointer;
		z-index: 2;
	}
	div.afq-spot div.afq-spot__stage button.afq-spot__btn:hover,
	div.afq-spot div.afq-spot__stage button.afq-spot__btn:focus {
		background: transparent !important;
		border: none !important;
		box-shadow: none !important;
		transform: translate(-50%, -50%);
	}
	div.afq-spot button.afq-spot__btn span.afq-spot__btn-dot {
		position: relative;
		display: block;
		width: 22px;
		height: 22px;
		border-radius: 50%;
		background: linear-gradient(135deg, #d7dce3, #9aa3b0);
		box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.85), 0 4px 14px rgba(0, 0, 0, 0.35);
		flex-shrink: 0;
	}
	div.afq-spot button.afq-spot__btn span.afq-spot__btn-dot::before {
		content: "";
		position: absolute;
		inset: 0;
		border-radius: 50%;
		background: rgba(199, 205, 214, 0.6);
		animation: afq-spot-pulse 2s ease-out infinite;
	}
	div.afq-spot button.afq-spot__btn span.afq-spot__btn-dot::after {
		content: "+";
		position: absolute;
		inset: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 14px;
		font-weight: 700;
		color: #14181f;
	}
	@keyframes afq-spot-pulse {
		0%   { transform: scale(1);   opacity: 0.9; }
		70%  { transform: scale(2.4); opacity: 0;   }
		100% { transform: scale(2.4); opacity: 0;   }
	}

	/* Label: always visible (no hover needed) */
	div.afq-spot button.afq-spot__btn span.afq-spot__btn-label {
		display: inline-block;
		background: rgba(20, 24, 31, 0.85);
		color: #e7ebf0 !important;
		font-size: 12px !important;
		font-weight: 600;
		padding: 6px 12px !important;
		margin: 0 !important;
		border-radius: 999px;
		white-space: nowrap;
		line-height: 1.4 !important;
		pointer-events: none;
		transition: background 0.2s ease;
	}
	div.afq-spot button.afq-spot__btn:hover span.afq-spot__btn-label,
	div.afq-spot button.afq-spot__btn:focus-visible span.afq-spot__btn-label {
		background: rgba(20, 24, 31, 1);
	}

	/* Modal */
	div.afq-spot div.afq-spot-modal {
		position: fixed;
		inset: 0;
		z-index: 99999;
		display: none;
	}
	div.afq-spot div.afq-spot-modal.is-open {
		display: block;
	}
	div.afq-spot div.afq-spot-modal div.afq-spot-modal__overlay {
		position: absolute;
		inset: 0;
		background: rgba(10, 13, 18, 0.6);
		backdrop-filter: blur(4px);
		-webkit-backdrop-filter: blur(4px);
	}
	div.afq-spot div.afq-spot-modal div.afq-spot-modal__card {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		width: min(560px, 92vw);
		max-height: 82vh;
		display: flex;
		flex-direction: column;
		background: #fff;
		border-radius: 16px;
		overflow: hidden;
		box-shadow: 0 30px 70px rgba(0, 0, 0, 0.35);
		animation: afq-spot-modal-in 0.25s ease;
	}
	@keyframes afq-spot-modal-in {
		from { opacity: 0; transform: translate(-50%, -46%); }
		to   { opacity: 1; transform: translate(-50%, -50%); }
	}
	div.afq-spot div.afq-spot-modal__header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 16px 20px;
		background: linear-gradient(135deg, #14181f 0%, #232a36 60%, #2c3442 100%);
	}
	div.afq-spot span.afq-spot-modal__title {
		color: #e7ebf0;
		font-size: 15px;
		font-weight: 700;
	}
	div.afq-spot button.afq-spot-modal__close {
		display: inline-flex !important;
		align-items: center;
		justify-content: center;
		width: 30px !important;
		height: 30px !important;
		min-width: 0 !important;
		min-height: 0 !important;
		margin: 0 !important;
		padding: 0 !important;
		border: none !important;
		border-radius: 50% !important;
		background: rgba(255, 255, 255, 0.12) !important;
		box-shadow: none !important;
		color: #fff !important;
		font-size: 17px !important;
		line-height: 1 !important;
		cursor: pointer;
		transition: background 0.15s ease;
	}
	div.afq-spot button.afq-spot-modal__close:hover {
		background: rgba(255, 255, 255, 0.25) !important;
	}
	div.afq-spot div.afq-spot-modal__body {
		padding: 8px 20px 18px;
		overflow-y: auto;
	}
	div.afq-spot div.afq-spot-modal__row {
		display: flex;
		align-items: baseline;
		justify-content: space-between;
		gap: 16px;
		padding: 11px 0;
		border-bottom: 1px solid #f0f2f5;
	}
	div.afq-spot div.afq-spot-modal__row:last-child {
		border-bottom: none;
	}
	div.afq-spot span.afq-spot-modal__label {
		font-size: 12.5px;
		color: #6b7280;
		flex-shrink: 0;
	}
	div.afq-spot span.afq-spot-modal__value {
		font-size: 13.5px;
		font-weight: 600;
		color: #1f2937;
		text-align: left;
	}
	div.afq-spot div.afq-spot-modal__row--list {
		flex-direction: column;
		align-items: stretch;
	}
	div.afq-spot ul.afq-spot-modal__list {
		margin: 8px 0 0 !important;
		padding: 0 !important;
		list-style: none !important;
	}
	div.afq-spot ul.afq-spot-modal__list li {
		position: relative;
		margin: 0 !important;
		padding: 6px 20px 6px 0 !important;
		list-style: none !important;
		font-size: 13px;
		color: #1f2937;
	}
	div.afq-spot ul.afq-spot-modal__list li::before {
		content: "";
		position: absolute;
		right: 4px;
		top: 13px;
		width: 7px;
		height: 7px;
		border-radius: 50%;
		background: linear-gradient(135deg, #d7dce3, #9aa3b0);
	}

	/* Empty state (no specs entered) */
	div.afq-spot.afq-spot--empty {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: 180px;
		border: 1px dashed #d9dde3;
		border-radius: 14px;
		background: #f8f9fa;
	}
	div.afq-spot.afq-spot--empty p.afq-spot__empty-text {
		margin: 0 !important;
		padding: 0 !important;
		font-size: 14px;
		font-weight: 600;
		color: #9ca3af;
	}

	body.afq-spot-lock {
		overflow: hidden;
	}

	/* Mobile */
	@media (max-width: 767px) {
		div.afq-spot button.afq-spot__btn span.afq-spot__btn-dot {
			width: 18px;
			height: 18px;
			box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.85), 0 3px 10px rgba(0, 0, 0, 0.35);
		}
		div.afq-spot button.afq-spot__btn span.afq-spot__btn-dot::after {
			font-size: 12px;
		}
		div.afq-spot button.afq-spot__btn span.afq-spot__btn-label {
			font-size: 10.5px !important;
			padding: 5px 10px !important;
		}
		div.afq-spot div.afq-spot__stage button.afq-spot__btn {
			min-height: 36px !important;
		}
		div.afq-spot div.afq-spot-modal div.afq-spot-modal__card {
			top: auto;
			bottom: 0;
			left: 0;
			transform: none;
			width: 100%;
			max-height: 84vh;
			border-radius: 18px 18px 0 0;
			animation: afq-spot-sheet-in 0.25s ease;
		}
		@keyframes afq-spot-sheet-in {
			from { transform: translateY(30px); opacity: 0; }
			to   { transform: translateY(0);    opacity: 1; }
		}
	}
	';
}

/**
 * Inline frontend JS (vanilla, no dependencies).
 *
 * @return string
 */
function afq_car_spot_inline_js() {
	return '
	( function () {
		"use strict";

		function openModal( modal ) {
			modal.classList.add( "is-open" );
			modal.setAttribute( "aria-hidden", "false" );
			document.body.classList.add( "afq-spot-lock" );

			var close = modal.querySelector( ".afq-spot-modal__close" );
			if ( close ) {
				close.focus();
			}
		}

		function closeModal( modal ) {
			modal.classList.remove( "is-open" );
			modal.setAttribute( "aria-hidden", "true" );

			if ( ! document.querySelector( ".afq-spot-modal.is-open" ) ) {
				document.body.classList.remove( "afq-spot-lock" );
			}
		}

		document.addEventListener( "click", function ( e ) {
			var btn = e.target.closest( ".afq-spot__btn" );
			if ( btn ) {
				var modal = document.getElementById( btn.getAttribute( "data-afq-modal" ) );
				if ( modal ) {
					openModal( modal );
				}
				return;
			}

			var closer = e.target.closest( "[data-afq-close]" );
			if ( closer ) {
				var openModalEl = closer.closest( ".afq-spot-modal" );
				if ( openModalEl ) {
					closeModal( openModalEl );
				}
			}
		} );

		document.addEventListener( "keydown", function ( e ) {
			if ( "Escape" !== e.key ) {
				return;
			}

			var open = document.querySelector( ".afq-spot-modal.is-open" );
			if ( open ) {
				closeModal( open );
			}
		} );
	} )();
	';
}