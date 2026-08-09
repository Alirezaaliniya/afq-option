/**
 * AFQ Car Post Type
 * Add this code to your theme's functions.php
 */

defined( 'ABSPATH' ) || exit;

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
add_action( 'init', 'afq_register_car_post_type' );

/**
 * Flush rewrite rules once after registration.
 */
function afq_car_maybe_flush_rewrite() {
	if ( 'yes' !== get_option( 'afq_car_rewrite_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'afq_car_rewrite_flushed', 'yes' );
	}
}
add_action( 'init', 'afq_car_maybe_flush_rewrite', 99 );


/**
 * AFQ Car — Category Image Elementor Dynamic Tag
 * Add this code to functions.php AFTER the media meta code
 * (needs the afq_car_cat_image term meta / afq_get_car_cat_image helper context).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register category image dynamic tag for Elementor.
 *
 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager Dynamic tags manager.
 */
function afq_register_car_cat_image_dynamic_tag( $dynamic_tags_manager ) {

	if ( ! class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) ) {
		return;
	}

	/**
	 * Current car category image dynamic tag.
	 */
	class AFQ_Car_Cat_Image_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

		/**
		 * Tag name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'afq-car-cat-image';
		}

		/**
		 * Tag title.
		 *
		 * @return string
		 */
		public function get_title() {
			return 'تصویر دسته‌بندی ماشین';
		}

		/**
		 * Tag group.
		 *
		 * @return array
		 */
		public function get_group() {
			return array( 'post' );
		}

		/**
		 * Tag categories.
		 *
		 * @return array
		 */
		public function get_categories() {
			return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
		}

		/**
		 * Get the category image of the current car.
		 *
		 * Uses the first assigned term that has an image; falls back to
		 * the term's parents if the term itself has no image.
		 *
		 * @param array $options Options.
		 * @return array
		 */
		public function get_value( array $options = array() ) {

			$empty = array(
				'id'  => '',
				'url' => '',
			);

			$post_id = get_the_ID();

			if ( ! $post_id ) {
				return $empty;
			}

			$terms = get_the_terms( $post_id, 'afq_car_cat' );

			if ( ! $terms || is_wp_error( $terms ) ) {
				return $empty;
			}

			foreach ( $terms as $term ) {

				$attachment_id = $this->find_term_image( $term );

				if ( $attachment_id ) {
					return array(
						'id'  => $attachment_id,
						'url' => wp_get_attachment_image_url( $attachment_id, 'full' ),
					);
				}
			}

			return $empty;
		}

		/**
		 * Get term image, walking up parents if the term has none.
		 *
		 * @param WP_Term $term Term object.
		 * @return int Attachment ID or 0.
		 */
		protected function find_term_image( $term ) {

			while ( $term instanceof WP_Term ) {

				$attachment_id = absint( get_term_meta( $term->term_id, 'afq_car_cat_image', true ) );

				if ( $attachment_id ) {
					return $attachment_id;
				}

				if ( ! $term->parent ) {
					break;
				}

				$term = get_term( $term->parent, 'afq_car_cat' );

				if ( is_wp_error( $term ) ) {
					break;
				}
			}

			return 0;
		}
	}

	$dynamic_tags_manager->register( new AFQ_Car_Cat_Image_Tag() );
}
add_action( 'elementor/dynamic_tags/register', 'afq_register_car_cat_image_dynamic_tag' );
