<?php
/**
 * Elementor dynamic tag classes.
 *
 * Loaded only from afq_option_load_elementor_tags(), i.e. only when
 * Elementor is active. Tag names are unchanged, so existing Elementor
 * widgets keep resolving their saved dynamic tags.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Car Image Tags
 * ---------------------------------------------------------------------- */

/**
 * Base class for car image dynamic tags.
 */
abstract class AFQ_Car_Image_Tag_Base extends \Elementor\Core\DynamicTags\Data_Tag {

	/**
	 * Get the post meta key for the image.
	 *
	 * @return string
	 */
	abstract protected function get_meta_key();

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
	 * Get image value.
	 *
	 * @param array $options Options.
	 * @return array
	 */
	public function get_value( array $options = array() ) {

		$attachment_id = absint( get_post_meta( get_the_ID(), $this->get_meta_key(), true ) );

		if ( ! $attachment_id ) {
			return array(
				'id'  => '',
				'url' => '',
			);
		}

		return array(
			'id'  => $attachment_id,
			'url' => wp_get_attachment_image_url( $attachment_id, 'full' ),
		);
	}
}

/**
 * Normal image dynamic tag.
 */
class AFQ_Car_Image_Normal_Tag extends AFQ_Car_Image_Tag_Base {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'afq-car-image-normal';
	}

	/**
	 * Tag title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'تصویر عادی ماشین';
	}

	/**
	 * Meta key.
	 *
	 * @return string
	 */
	protected function get_meta_key() {
		return '_afq_car_image_normal';
	}
}

/**
 * Hover image dynamic tag.
 */
class AFQ_Car_Image_Hover_Tag extends AFQ_Car_Image_Tag_Base {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'afq-car-image-hover';
	}

	/**
	 * Tag title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'تصویر هاور ماشین';
	}

	/**
	 * Meta key.
	 *
	 * @return string
	 */
	protected function get_meta_key() {
		return '_afq_car_image_hover';
	}
}

/**
 * Spot image dynamic tag.
 */
class AFQ_Car_Image_Spot_Tag extends AFQ_Car_Image_Tag_Base {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'afq-car-image-spot';
	}

	/**
	 * Tag title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'تصویر اسپات ماشین';
	}

	/**
	 * Meta key.
	 *
	 * @return string
	 */
	protected function get_meta_key() {
		return '_afq_car_image_spot';
	}
}

/**
 * Details image dynamic tag.
 */
class AFQ_Car_Image_Details_Tag extends AFQ_Car_Image_Tag_Base {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'afq-car-image-details';
	}

	/**
	 * Tag title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'تصویر جزئیات ماشین';
	}

	/**
	 * Meta key.
	 *
	 * @return string
	 */
	protected function get_meta_key() {
		return '_afq_car_image_details';
	}
}

/**
 * Gallery dynamic tag.
 */
class AFQ_Car_Gallery_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'afq-car-gallery';
	}

	/**
	 * Tag title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'گالری تصاویر ماشین';
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
		return array( \Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY );
	}

	/**
	 * Get gallery value.
	 *
	 * @param array $options Options.
	 * @return array
	 */
	public function get_value( array $options = array() ) {

		$ids   = afq_get_car_gallery_ids( get_the_ID() );
		$value = array();

		foreach ( $ids as $attachment_id ) {
			$value[] = array( 'id' => $attachment_id );
		}

		return $value;
	}
}

/* -------------------------------------------------------------------------
 * Car Category Image Tags
 * ---------------------------------------------------------------------- */

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

			$attachment_id = afq_car_cat_find_term_image( $term );

			if ( $attachment_id ) {
				return array(
					'id'  => $attachment_id,
					'url' => wp_get_attachment_image_url( $attachment_id, 'full' ),
				);
			}
		}

		return $empty;
	}
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

		$attachment_id = afq_car_cat_find_term_image( $term );

		if ( ! $attachment_id ) {
			return $empty;
		}

		return array(
			'id'  => $attachment_id,
			'url' => wp_get_attachment_image_url( $attachment_id, 'full' ),
		);
	}
}

/* -------------------------------------------------------------------------
 * Car Details Tags (price / catalog / video)
 * ---------------------------------------------------------------------- */

/**
 * Base class for text meta dynamic tags.
 */
abstract class AFQ_Car_Meta_Text_Tag_Base extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get the post meta key.
	 *
	 * @return string
	 */
	abstract protected function get_meta_key();

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
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	/**
	 * Render tag output.
	 */
	public function render() {
		echo esc_html( get_post_meta( get_the_ID(), $this->get_meta_key(), true ) );
	}
}

/**
 * Regular price dynamic tag.
 */
class AFQ_Car_Price_Regular_Tag extends AFQ_Car_Meta_Text_Tag_Base {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'afq-car-price-regular';
	}

	/**
	 * Tag title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'قیمت عادی ماشین';
	}

	/**
	 * Meta key.
	 *
	 * @return string
	 */
	protected function get_meta_key() {
		return '_afq_car_price_regular';
	}
}

/**
 * Sale price dynamic tag.
 */
class AFQ_Car_Price_Sale_Tag extends AFQ_Car_Meta_Text_Tag_Base {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'afq-car-price-sale';
	}

	/**
	 * Tag title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'قیمت فروش ویژه ماشین';
	}

	/**
	 * Meta key.
	 *
	 * @return string
	 */
	protected function get_meta_key() {
		return '_afq_car_price_sale';
	}
}

/**
 * Catalog file URL dynamic tag.
 */
class AFQ_Car_Catalog_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'afq-car-catalog';
	}

	/**
	 * Tag title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'کاتالوگ ماشین';
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
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
	}

	/**
	 * Get catalog URL.
	 *
	 * @param array $options Options.
	 * @return string
	 */
	public function get_value( array $options = array() ) {
		return afq_get_car_catalog_url( get_the_ID() );
	}
}

/**
 * Intro video URL dynamic tag.
 */
class AFQ_Car_Video_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'afq-car-video';
	}

	/**
	 * Tag title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'ویدیوی معرفی ماشین';
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
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
	}

	/**
	 * Get video URL.
	 *
	 * @param array $options Options.
	 * @return string
	 */
	public function get_value( array $options = array() ) {
		return afq_get_car_video_url( get_the_ID() );
	}
}
