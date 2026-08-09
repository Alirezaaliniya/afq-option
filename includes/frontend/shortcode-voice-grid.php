<?php
/**
 * [afq_voice_grid] — Customer testimonials grid ("نظرات مشتریان").
 *
 *   [afq_voice_grid]                → all items, 3 columns on desktop
 *   [afq_voice_grid count="6"]      → limit items
 *   [afq_voice_grid columns="4"]    → desktop columns (1-4)
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Voice grid shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function afq_voice_grid_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'count'   => -1,
			'columns' => 3,
		),
		$atts,
		'afq_voice_grid'
	);

	$columns = min( 4, max( 1, absint( $atts['columns'] ) ) );

	$query = new WP_Query(
		array(
			'post_type'              => 'afq_voice',
			'post_status'            => 'publish',
			'posts_per_page'         => (int) $atts['count'],
			'orderby'                => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	wp_enqueue_style( 'afq-voice-grid' );
	wp_enqueue_script( 'afq-voice-grid' );

	static $instance = 0;
	$instance++;
	$uid = 'afq-voices-' . $instance;

	ob_start();
	?>
	<div class="afq-voices" id="<?php echo esc_attr( $uid ); ?>" style="--afq-voice-cols:<?php echo esc_attr( $columns ); ?>;">

		<div class="afq-voices__grid">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();

				$post_id   = get_the_ID();
				$name      = get_the_title();
				$image_id  = absint( get_post_meta( $post_id, '_afq_voice_image', true ) );
				$desc      = get_post_meta( $post_id, '_afq_voice_desc', true );
				$audio_url = get_post_meta( $post_id, '_afq_voice_audio', true );
				$video_url = get_post_meta( $post_id, '_afq_voice_video', true );
				?>
				<article class="afq-voice-card">

					<div class="afq-voice-card__head">

						<?php if ( $image_id ) : ?>
							<div class="afq-voice-card__avatar">
								<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
							</div>
						<?php endif; ?>

						<h3 class="afq-voice-card__name"><?php echo esc_html( $name ); ?></h3>

						<?php if ( $video_url ) : ?>
							<button type="button"
								class="afq-voice-card__video-btn"
								data-afq-video="<?php echo esc_url( $video_url ); ?>"
								data-afq-video-name="<?php echo esc_attr( $name ); ?>"
								aria-haspopup="dialog"
								aria-label="مشاهده ویدیوی <?php echo esc_attr( $name ); ?>">
								<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M8 6.5v11l9-5.5-9-5.5Z" fill="currentColor"/></svg>
							</button>
						<?php endif; ?>

					</div>

					<?php if ( '' !== $desc ) : ?>
						<p class="afq-voice-card__desc"><?php echo nl2br( esc_html( $desc ) ); ?></p>
					<?php endif; ?>

					<?php if ( $audio_url ) : ?>
						<div class="afq-voice-player" data-afq-audio="<?php echo esc_url( $audio_url ); ?>">
							<button type="button" class="afq-voice-player__btn" aria-label="پخش صدای <?php echo esc_attr( $name ); ?>">
								<svg class="afq-voice-player__icon-play" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M8 5.5v13l10-6.5-10-6.5Z" fill="currentColor"/></svg>
								<svg class="afq-voice-player__icon-pause" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M8 5h3v14H8V5Zm5 0h3v14h-3V5Z" fill="currentColor"/></svg>
							</button>
							<div class="afq-voice-player__track" role="slider" aria-label="نوار پیشرفت پخش">
								<div class="afq-voice-player__progress"></div>
							</div>
							<span class="afq-voice-player__time">0:00</span>
						</div>
					<?php endif; ?>

				</article>
			<?php endwhile; ?>
		</div>

		<div class="afq-voice-modal" role="dialog" aria-modal="true" aria-label="ویدیوی مشتری" aria-hidden="true">
			<div class="afq-voice-modal__overlay" data-afq-vclose></div>
			<div class="afq-voice-modal__card">
				<div class="afq-voice-modal__header">
					<span class="afq-voice-modal__title"></span>
					<button type="button" class="afq-voice-modal__close" data-afq-vclose aria-label="بستن">&times;</button>
				</div>
				<div class="afq-voice-modal__body"></div>
			</div>
		</div>

	</div>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'afq_voice_grid', 'afq_voice_grid_shortcode' );
