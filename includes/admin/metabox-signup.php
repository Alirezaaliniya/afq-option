<?php
/**
 * Signup submissions admin: read-only data view, status box, list table.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register admin meta boxes for submissions.
 */
function afq_signup_add_meta_boxes() {

	add_meta_box(
		'afq_signup_data',
		'اطلاعات ثبت‌نام',
		'afq_signup_data_meta_box',
		'afq_signup',
		'normal',
		'high'
	);

	add_meta_box(
		'afq_signup_status',
		'وضعیت ثبت‌نام',
		'afq_signup_status_meta_box',
		'afq_signup',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'afq_signup_add_meta_boxes' );

/**
 * Render read-only submission data.
 *
 * @param WP_Post $post Current post object.
 */
function afq_signup_data_meta_box( $post ) {

	$signup_type = get_post_meta( $post->ID, '_afq_signup_signup_type', true );
	?>
	<div class="afq-signup-admin">

		<div class="afq-signup-admin__section">
			<h4>نوع ثبت‌نام</h4>
			<div class="afq-signup-admin__rows">
				<div class="afq-signup-admin__row">
					<span>نوع ثبت‌نام</span>
					<strong><?php echo esc_html( $signup_type ? $signup_type : '—' ); ?></strong>
				</div>
			</div>
		</div>

		<?php foreach ( afq_signup_get_sections() as $section ) : ?>
			<div class="afq-signup-admin__section">
				<h4><?php echo esc_html( $section['label'] ); ?></h4>
				<div class="afq-signup-admin__rows">
					<?php foreach ( $section['fields'] as $key => $field ) : ?>
						<?php $value = get_post_meta( $post->ID, '_afq_signup_' . $key, true ); ?>
						<div class="afq-signup-admin__row">
							<span><?php echo esc_html( $field['label'] ); ?></span>
							<strong><?php echo $value ? nl2br( esc_html( $value ) ) : '—'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>

	</div>
	<?php
}

/**
 * Render status select meta box.
 *
 * @param WP_Post $post Current post object.
 */
function afq_signup_status_meta_box( $post ) {

	wp_nonce_field( 'afq_signup_status_save', 'afq_signup_status_nonce' );

	$current = get_post_meta( $post->ID, '_afq_signup_status', true );
	$current = $current ? $current : 'pending';
	?>
	<select name="afq_signup_status_value" class="afq-signup-status-select" style="width:100%;">
		<?php foreach ( afq_signup_get_statuses() as $status_key => $status ) : ?>
			<option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $current, $status_key ); ?>>
				<?php echo esc_html( $status['label'] ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<p class="description" style="margin-top:8px;">پس از تغییر، دکمه «بروزرسانی» را بزنید.</p>
	<?php
}

/**
 * Save status.
 *
 * @param int $post_id Post ID.
 */
function afq_signup_save_status( $post_id ) {

	if ( ! isset( $_POST['afq_signup_status_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['afq_signup_status_nonce'] ), 'afq_signup_status_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['afq_signup_status_value'] ) ) {
		$status = sanitize_key( $_POST['afq_signup_status_value'] );

		if ( array_key_exists( $status, afq_signup_get_statuses() ) ) {
			update_post_meta( $post_id, '_afq_signup_status', $status );
		}
	}
}
add_action( 'save_post_afq_signup', 'afq_signup_save_status' );

/* -------------------------------------------------------------------------
 * List Table
 * ---------------------------------------------------------------------- */

/**
 * Admin list columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function afq_signup_admin_columns( $columns ) {
	return array(
		'cb'          => $columns['cb'],
		'title'       => 'نام و نام خانوادگی',
		'signup_type' => 'نوع ثبت‌نام',
		'mobile'      => 'تلفن همراه',
		'status'      => 'وضعیت',
		'date'        => 'تاریخ',
	);
}
add_filter( 'manage_afq_signup_posts_columns', 'afq_signup_admin_columns' );

/**
 * Admin list column values.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function afq_signup_admin_column_values( $column, $post_id ) {

	if ( 'signup_type' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_afq_signup_signup_type', true ) );
	}

	if ( 'mobile' === $column ) {
		echo '<span style="direction:ltr;unicode-bidi:embed;">' . esc_html( get_post_meta( $post_id, '_afq_signup_mobile', true ) ) . '</span>';
	}

	if ( 'status' === $column ) {
		$statuses = afq_signup_get_statuses();
		$status   = get_post_meta( $post_id, '_afq_signup_status', true );
		$status   = isset( $statuses[ $status ] ) ? $status : 'pending';

		printf(
			'<span style="display:inline-block;padding:3px 12px;border-radius:999px;font-size:11px;font-weight:600;color:%1$s;background:%2$s;">%3$s</span>',
			esc_attr( $statuses[ $status ]['color'] ),
			esc_attr( $statuses[ $status ]['bg'] ),
			esc_html( $statuses[ $status ]['label'] )
		);
	}
}
add_action( 'manage_afq_signup_posts_custom_column', 'afq_signup_admin_column_values', 10, 2 );

/**
 * Status filter dropdown in admin list.
 */
function afq_signup_admin_filter() {

	$screen = get_current_screen();

	if ( ! $screen || 'edit-afq_signup' !== $screen->id ) {
		return;
	}

	$current = isset( $_GET['afq_signup_status'] ) ? sanitize_key( $_GET['afq_signup_status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<select name="afq_signup_status">
		<option value="">همه وضعیت‌ها</option>
		<?php foreach ( afq_signup_get_statuses() as $status_key => $status ) : ?>
			<option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $current, $status_key ); ?>>
				<?php echo esc_html( $status['label'] ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}
add_action( 'restrict_manage_posts', 'afq_signup_admin_filter' );

/**
 * Apply the status filter to the admin list query.
 *
 * @param WP_Query $query Current query.
 */
function afq_signup_admin_filter_query( $query ) {

	if ( ! is_admin() || ! $query->is_main_query() || 'afq_signup' !== $query->get( 'post_type' ) ) {
		return;
	}

	$status = isset( $_GET['afq_signup_status'] ) ? sanitize_key( $_GET['afq_signup_status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( $status && array_key_exists( $status, afq_signup_get_statuses() ) ) {
		$query->set( 'meta_key', '_afq_signup_status' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$query->set( 'meta_value', $status ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	}
}
add_action( 'pre_get_posts', 'afq_signup_admin_filter_query' );
