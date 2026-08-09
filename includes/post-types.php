<?php
/**
 * Post types, taxonomies and registered meta.
 *
 * All registrations run from a single `init` callback instead of one hook
 * per module.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register every post type, taxonomy and meta key of the plugin.
 */
function afq_option_register_content_types() {
	afq_register_car_post_type();
	afq_car_register_spec_meta();
	afq_register_faq_post_type();
	afq_register_voice_post_type();
	afq_register_rep_post_type();
	afq_register_circular_post_type();
	afq_register_signup_post_type();
	afq_register_request_post_type();
}
add_action( 'init', 'afq_option_register_content_types' );

/* -------------------------------------------------------------------------
 * Car
 * ---------------------------------------------------------------------- */

/**
 * Register "Car" post type and its taxonomy.
 */
function afq_register_car_post_type() {

	$labels = array(
		'name'               => 'ماشین‌ها',
		'singular_name'      => 'ماشین',
		'menu_name'          => 'ماشین‌ها',
		'add_new'            => 'افزودن ماشین',
		'add_new_item'       => 'افزودن ماشین جدید',
		'edit_item'          => 'ویرایش ماشین',
		'new_item'           => 'ماشین جدید',
		'view_item'          => 'مشاهده ماشین',
		'search_items'       => 'جستجوی ماشین',
		'not_found'          => 'ماشینی یافت نشد',
		'not_found_in_trash' => 'ماشینی در زباله‌دان یافت نشد',
		'all_items'          => 'همه ماشین‌ها',
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'has_archive'        => 'car',
		'rewrite'            => array(
			'slug'       => 'car',
			'with_front' => false,
		),
		'menu_icon'          => 'dashicons-car',
		'menu_position'      => 20,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ),
		'show_in_rest'       => true,
		'capability_type'    => 'post',
		'hierarchical'       => false,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
	);

	register_post_type( 'afq_car', $args );

	$tax_labels = array(
		'name'              => 'دسته‌بندی ماشین',
		'singular_name'     => 'دسته‌بندی ماشین',
		'search_items'      => 'جستجوی دسته‌بندی',
		'all_items'         => 'همه دسته‌بندی‌ها',
		'parent_item'       => 'دسته‌بندی مادر',
		'parent_item_colon' => 'دسته‌بندی مادر:',
		'edit_item'         => 'ویرایش دسته‌بندی',
		'update_item'       => 'بروزرسانی دسته‌بندی',
		'add_new_item'      => 'افزودن دسته‌بندی جدید',
		'new_item_name'     => 'نام دسته‌بندی جدید',
		'menu_name'         => 'دسته‌بندی‌ها',
	);

	$tax_args = array(
		'labels'            => $tax_labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'query_var'         => true,
		'rewrite'           => array(
			'slug'       => 'car-category',
			'with_front' => false,
		),
	);

	register_taxonomy( 'afq_car_cat', array( 'afq_car' ), $tax_args );
}

/**
 * Register spec meta keys (REST-ready for Elementor dynamic tags).
 */
function afq_car_register_spec_meta() {

	$auth_callback = static function () {
		return current_user_can( 'edit_posts' );
	};

	foreach ( afq_car_get_all_spec_fields() as $key => $field ) {
		register_post_meta(
			'afq_car',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => ( 'textarea' === $field['type'] ) ? 'sanitize_textarea_field' : 'sanitize_text_field',
				'auth_callback'     => $auth_callback,
			)
		);
	}
}

/* -------------------------------------------------------------------------
 * FAQ
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

/* -------------------------------------------------------------------------
 * Customer Voice
 * ---------------------------------------------------------------------- */

/**
 * Register the customer testimonials post type ("نظرات مشتریان").
 *
 * Distinct from afq_request ("صدای مشتری"), which holds incoming requests
 * and complaints. The afq_voice slug, [afq_voice_grid] shortcode and CSS
 * classes are unchanged — only the dashboard labels were renamed.
 */
function afq_register_voice_post_type() {

	$labels = array(
		'name'               => 'نظرات مشتریان',
		'singular_name'      => 'نظر مشتری',
		'menu_name'          => 'نظرات مشتریان',
		'add_new'            => 'افزودن نظر',
		'add_new_item'       => 'افزودن نظر جدید',
		'edit_item'          => 'ویرایش نظر مشتری',
		'new_item'           => 'نظر جدید',
		'view_item'          => 'مشاهده نظر',
		'search_items'       => 'جستجوی نظر',
		'not_found'          => 'نظری یافت نشد',
		'not_found_in_trash' => 'نظری در زباله‌دان یافت نشد',
		'all_items'          => 'همه نظرات',
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
		'menu_icon'           => 'dashicons-testimonial',
		'menu_position'       => 21,
		'supports'            => array( 'title', 'page-attributes' ),
		'capability_type'     => 'post',
		'hierarchical'        => false,
	);

	register_post_type( 'afq_voice', $args );
}

/* -------------------------------------------------------------------------
 * Representatives
 * ---------------------------------------------------------------------- */

/**
 * Register representative post type, province taxonomy and its term meta.
 */
function afq_register_rep_post_type() {

	$labels = array(
		'name'               => 'نمایندگان',
		'singular_name'      => 'نماینده',
		'menu_name'          => 'نمایندگان',
		'add_new'            => 'افزودن نماینده',
		'add_new_item'       => 'افزودن نماینده جدید',
		'edit_item'          => 'ویرایش نماینده',
		'new_item'           => 'نماینده جدید',
		'view_item'          => 'مشاهده',
		'search_items'       => 'جستجوی نماینده',
		'not_found'          => 'موردی یافت نشد',
		'not_found_in_trash' => 'موردی در زباله‌دان یافت نشد',
		'all_items'          => 'همه نمایندگان',
	);

	register_post_type(
		'afq_rep',
		array(
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
			'menu_icon'           => 'dashicons-location-alt',
			'menu_position'       => 23,
			'supports'            => array( 'title', 'page-attributes' ),
			'capability_type'     => 'post',
			'hierarchical'        => false,
		)
	);

	register_taxonomy(
		'afq_rep_province',
		array( 'afq_rep' ),
		array(
			'labels'            => array(
				'name'          => 'استان‌ها',
				'singular_name' => 'استان',
				'search_items'  => 'جستجوی استان',
				'all_items'     => 'همه استان‌ها',
				'edit_item'     => 'ویرایش استان',
				'update_item'   => 'بروزرسانی استان',
				'add_new_item'  => 'افزودن استان جدید',
				'new_item_name' => 'نام استان جدید',
				'menu_name'     => 'استان‌ها',
			),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => false,
		)
	);

	foreach ( array( 'afq_rep_spot_left', 'afq_rep_spot_top' ) as $meta_key ) {
		register_term_meta(
			'afq_rep_province',
			$meta_key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
	}
}

/* -------------------------------------------------------------------------
 * Sales Circular
 * ---------------------------------------------------------------------- */

/**
 * Register sales circular post type.
 */
function afq_register_circular_post_type() {

	$labels = array(
		'name'               => 'بخش‌نامه‌های فروش',
		'singular_name'      => 'بخش‌نامه فروش',
		'menu_name'          => 'بخش‌نامه فروش',
		'add_new'            => 'افزودن بخش‌نامه',
		'add_new_item'       => 'افزودن بخش‌نامه جدید',
		'edit_item'          => 'ویرایش بخش‌نامه',
		'new_item'           => 'بخش‌نامه جدید',
		'view_item'          => 'مشاهده بخش‌نامه',
		'search_items'       => 'جستجوی بخش‌نامه',
		'not_found'          => 'بخش‌نامه‌ای یافت نشد',
		'not_found_in_trash' => 'بخش‌نامه‌ای در زباله‌دان یافت نشد',
		'all_items'          => 'همه بخش‌نامه‌ها',
	);

	register_post_type(
		'afq_circular',
		array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'has_archive'        => false,
			'rewrite'            => array(
				'slug'       => 'sales-circular',
				'with_front' => false,
			),
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-megaphone',
			'menu_position'      => 24,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'capability_type'    => 'post',
			'hierarchical'       => false,
		)
	);

	/*
	 * Intentionally NOT underscore-prefixed so it stays selectable in
	 * Elementor dynamic tags / display conditions.
	 */
	register_post_meta(
		'afq_circular',
		'afq_circular_sold_out',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}

/* -------------------------------------------------------------------------
 * Customer Voice Requests
 * ---------------------------------------------------------------------- */

/**
 * Register the customer voice request post type (dashboard only).
 */
function afq_register_request_post_type() {

	register_post_type(
		'afq_request',
		array(
			'labels'              => array(
				'name'               => 'صدای مشتری',
				'singular_name'      => 'درخواست',
				'menu_name'          => 'صدای مشتری',
				'edit_item'          => 'مشاهده درخواست',
				'search_items'       => 'جستجوی درخواست',
				'not_found'          => 'درخواستی یافت نشد',
				'not_found_in_trash' => 'درخواستی در زباله‌دان یافت نشد',
				'all_items'          => 'همه درخواست‌ها',
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'rewrite'             => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-format-chat',
			'menu_position'       => 26,
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'        => true,
			'hierarchical'        => false,
		)
	);
}

/* -------------------------------------------------------------------------
 * Signup Submissions
 * ---------------------------------------------------------------------- */

/**
 * Register signup submissions post type (dashboard only, no "Add New").
 */
function afq_register_signup_post_type() {

	register_post_type(
		'afq_signup',
		array(
			'labels'              => array(
				'name'               => 'ثبت‌نام‌ها',
				'singular_name'      => 'ثبت‌نام',
				'menu_name'          => 'ثبت‌نام‌ها',
				'edit_item'          => 'مشاهده ثبت‌نام',
				'search_items'       => 'جستجوی ثبت‌نام',
				'not_found'          => 'ثبت‌نامی یافت نشد',
				'not_found_in_trash' => 'ثبت‌نامی در زباله‌دان یافت نشد',
				'all_items'          => 'همه ثبت‌نام‌ها',
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'rewrite'             => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-id-alt',
			'menu_position'       => 25,
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'        => true,
			'hierarchical'        => false,
		)
	);
}
