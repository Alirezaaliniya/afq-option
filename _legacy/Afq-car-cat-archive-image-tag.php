
/**
 * AFQ Car — Archive Category Image Elementor Dynamic Tag
 * Add this code to functions.php AFTER the previous dynamic tag blocks.
 *
 * Adds an IMAGE dynamic tag in the "Archive" group so it appears in
 * Elementor archive templates (تصویر برچسب‌های آرشیو). On the afq_car_cat
 * archive it returns the queried term's image (afq_car_cat_image), walking
 * up to parent terms if the term itself has no image.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register archive category image dynamic tag for Elementor.
 *
 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager Dynamic tags manager.
 */
function afq_register_car_cat_archive_image_tag( $dynamic_tags_manager ) {

	if ( ! class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) ) {
		return;
	}

	/**
	 * Queried car category (archive) image dynamic tag.
	 */
	class AFQ_Car_Cat_Archive_Image_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

		/**
		 * Tag name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'afq-car-cat-archive-image';
		}

		/**
		 * Tag title.
		 *
		 * @return string
		 */
		public function get_title() {
			return 'تصویر دسته‌بندی آرشیو (ماشین)';
		}

		/**
		 * Tag group — "archive" so it shows in archive templates.
		 *
		 * @return array
		 */
		public function get_group() {
			return array( 'archive' );
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
		 * Get the queried term's image.
		 *
		 * @param array $options Options.
		 * @return array
		 */
		public function get_value( array $options = array() ) {

			$empty = array(
				'id'  => '',
				'url' => '',
			);

			$term = get_queried_object();

			if ( ! $term instanceof WP_Term || 'afq_car_cat' !== $term->taxonomy ) {
				return $empty;
			}

			$attachment_id = $this->find_term_image( $term );

			if ( ! $attachment_id ) {
				return $empty;
			}

			return array(
				'id'  => $attachment_id,
				'url' => wp_get_attachment_image_url( $attachment_id, 'full' ),
			);
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

	$dynamic_tags_manager->register( new AFQ_Car_Cat_Archive_Image_Tag() );
}
add_action( 'elementor/dynamic_tags/register', 'afq_register_car_cat_archive_image_tag' );