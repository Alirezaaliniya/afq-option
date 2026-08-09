<?php
/**
 * Customer Voice admin: read-only submission view, status + reply box,
 * and the list table.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the request meta boxes.
 */
function afq_request_add_meta_boxes() {

	add_meta_box(
		'afq_request_data',
		'اطلاعات درخواست',
		'afq_request_data_meta_box',
		'afq_request',
		'normal',
		'high'
	);

	add_meta_box(
		'afq_request_status',
		'وضعیت و پاسخ',
		'afq_request_status_meta_box',
		'afq_request',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'afq_request_add_meta_boxes' );

/**
 * Render the read-only submission data.
 *
 * @param WP_Post $post Current post object.
 */
function afq_request_data_meta_box( $post ) {

	$code = get_post_meta( $post->ID, '_afq_request_code', true );
	$file = absint( get_post_meta( $post->ID, '_afq_request_file', true ) );
	?>
	<div class="afq-request-admin">

		<div class="afq-request-admin__code">
			<span>کد رهگیری</span>
			<strong><?php echo esc_html( $code ? $code : '—' ); ?></strong>
		</div>

		<?php foreach ( afq_request_get_sections() as $section ) : ?>
			<div class="afq-request-admin__section">
				<h4><?php echo esc_html( $section['label'] ); ?></h4>
				<div class="afq-request-admin__rows">
					<?php foreach ( $section['fields'] as $key => $field ) : ?>
						<?php $value = get_post_meta( $post->ID, '_afq_request_' . $key, true ); ?>
						<div class="afq-request-admin__row<?php echo ( 'textarea' === $field['type'] ) ? ' afq-request-admin__row--wide' : ''; ?>">
							<span><?php echo esc_html( $field['label'] ); ?></span>
							<strong><?php echo $value ? nl2br( esc_html( $value ) ) : '—'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>

		<div class="afq-request-admin__section">
			<h4>پیوست</h4>
			<div class="afq-request-admin__rows">
				<div class="afq-request-admin__row">
					<span>فایل ارسالی</span>
					<strong>
						<?php if ( $file ) : ?>
							<a href="<?php echo esc_url( wp_get_attachment_url( $file ) ); ?>" target="_blank" rel="noopener">
								<?php echo esc_html( wp_basename( get_attached_file( $file ) ) ); ?>
							</a>
						<?php else : ?>
							—
						<?php endif; ?>
					</strong>
				</div>
			</div>
		</div>

	</div>
	<?php
}

/**
 * Render the status + reply box.
 *
 * @param WP_Post $post Current post object.
 */
function afq_request_status_meta_box( $post ) {

	wp_nonce_field( 'afq_request_status_save', 'afq_request_status_nonce' );

	$current = get_post_meta( $post->ID, '_afq_request_status', true );
	$current = $current ? $current : 'new';
	$reply   = get_post_meta( $post->ID, '_afq_request_reply', true );
	?>
	<p>
		<label for="afq_request_status_value"><strong>وضعیت درخواست</strong></label>
		<select name="afq_request_status_value" id="afq_request_status_value" style="width:100%;margin-top:6px;">
			<?php foreach ( afq_request_get_statuses() as $status_key => $status ) : ?>
				<option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $current, $status_key ); ?>>
					<?php echo esc_html( $status['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>

	<p>
		<label for="afq_request_reply"><strong>پاسخ به مشتری</strong></label>
		<textarea name="afq_request_reply" id="afq_request_reply" rows="6" style="width:100%;margin-top:6px;"
			placeholder="این متن در صفحه پیگیری به مشتری نمایش داده می‌شود."><?php echo esc_textarea( $reply ); ?></textarea>
	</p>

	<p class="description">پس از تغییر، دکمه «بروزرسانی» را بزنید.</p>
	<?php
}

/**
 * Save the status and reply.
 *
 * @param int $post_id Post ID.
 */
function afq_request_save_status( $post_id ) {

	if ( ! isset( $_POST['afq_request_status_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['afq_request_status_nonce'] ), 'afq_request_status_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['afq_request_status_value'] ) ) {
		$status = sanitize_key( $_POST['afq_request_status_value'] );

		if ( array_key_exists( $status, afq_request_get_statuses() ) ) {
			update_post_meta( $post_id, '_afq_request_status', $status );
		}
	}

	if ( isset( $_POST['afq_request_reply'] ) ) {
		$reply = sanitize_textarea_field( wp_unslash( $_POST['afq_request_reply'] ) );

		if ( '' !== $reply ) {
			update_post_meta( $post_id, '_afq_request_reply', $reply );
		} else {
			delete_post_meta( $post_id, '_afq_request_reply' );
		}
	}
}
add_action( 'save_post_afq_request', 'afq_request_save_status' );

/* -------------------------------------------------------------------------
 * List Table
 * ---------------------------------------------------------------------- */

/**
 * Admin list columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function afq_request_admin_columns( $columns ) {
	return array(
		'cb'           => $columns['cb'],
		'title'        => 'درخواست‌کننده',
		'code'         => 'کد رهگیری',
		'request_type' => 'نوع',
		'mobile'       => 'موبایل',
		'status'       => 'وضعیت',
		'date'         => 'تاریخ',
	);
}
add_filter( 'manage_afq_request_posts_columns', 'afq_request_admin_columns' );

/**
 * Admin list column values.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function afq_request_admin_column_values( $column, $post_id ) {

	if ( 'code' === $column ) {
		echo '<span style="direction:ltr;unicode-bidi:embed;font-family:monospace;">'
			. esc_html( get_post_meta( $post_id, '_afq_request_code', true ) ) . '</span>';
	}

	if ( 'request_type' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_afq_request_request_type', true ) );
	}

	if ( 'mobile' === $column ) {
		echo '<span style="direction:ltr;unicode-bidi:embed;">'
			. esc_html( get_post_meta( $post_id, '_afq_request_mobile', true ) ) . '</span>';
	}

	if ( 'status' === $column ) {
		$statuses = afq_request_get_statuses();
		$status   = get_post_meta( $post_id, '_afq_request_status', true );
		$status   = isset( $statuses[ $status ] ) ? $status : 'new';

		printf(
			'<span style="display:inline-block;padding:3px 12px;border-radius:999px;font-size:11px;font-weight:600;color:%1$s;background:%2$s;">%3$s</span>',
			esc_attr( $statuses[ $status ]['color'] ),
			esc_attr( $statuses[ $status ]['bg'] ),
			esc_html( $statuses[ $status ]['label'] )
		);
	}
}
add_action( 'manage_afq_request_posts_custom_column', 'afq_request_admin_column_values', 10, 2 );

/**
 * Status filter dropdown in the admin list.
 */
function afq_request_admin_filter() {

	$screen = get_current_screen();

	if ( ! $screen || 'edit-afq_request' !== $screen->id ) {
		return;
	}

	$current = isset( $_GET['afq_request_status'] ) ? sanitize_key( $_GET['afq_request_status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<select name="afq_request_status">
		<option value="">همه وضعیت‌ها</option>
		<?php foreach ( afq_request_get_statuses() as $status_key => $status ) : ?>
			<option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $current, $status_key ); ?>>
				<?php echo esc_html( $status['label'] ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}
add_action( 'restrict_manage_posts', 'afq_request_admin_filter' );

/**
 * Apply the status filter to the admin list query.
 *
 * @param WP_Query $query Current query.
 */
function afq_request_admin_filter_query( $query ) {

	if ( ! is_admin() || ! $query->is_main_query() || 'afq_request' !== $query->get( 'post_type' ) ) {
		return;
	}

	$status = isset( $_GET['afq_request_status'] ) ? sanitize_key( $_GET['afq_request_status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( $status && array_key_exists( $status, afq_request_get_statuses() ) ) {
		$query->set( 'meta_key', '_afq_request_status' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$query->set( 'meta_value', $status ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	}
}
add_action( 'pre_get_posts', 'afq_request_admin_filter_query' );
