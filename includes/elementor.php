<?php
/**
 * Elementor dynamic tag registration.
 *
 * The tag classes live in includes/elementor/tags.php and are only loaded
 * when Elementor is actually present and asking for tags — they extend
 * Elementor base classes, so they cannot be parsed without it.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the dynamic tag classes if Elementor is available.
 *
 * @return bool True when the classes are loaded and usable.
 */
function afq_option_load_elementor_tags() {

	if ( ! class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) ) {
		return false;
	}

	require_once AFQ_OPTION_PATH . 'includes/elementor/tags.php';

	return true;
}

/**
 * Register car media dynamic tags for Elementor.
 *
 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager Dynamic tags manager.
 */
function afq_register_car_dynamic_tags( $dynamic_tags_manager ) {

	if ( ! afq_option_load_elementor_tags() ) {
		return;
	}

	$dynamic_tags_manager->register( new AFQ_Car_Image_Normal_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Image_Hover_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Image_Spot_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Image_Details_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Gallery_Tag() );
}
add_action( 'elementor/dynamic_tags/register', 'afq_register_car_dynamic_tags' );

/**
 * Register the current-post category image dynamic tag.
 *
 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager Dynamic tags manager.
 */
function afq_register_car_cat_image_dynamic_tag( $dynamic_tags_manager ) {

	if ( ! afq_option_load_elementor_tags() ) {
		return;
	}

	$dynamic_tags_manager->register( new AFQ_Car_Cat_Image_Tag() );
}
add_action( 'elementor/dynamic_tags/register', 'afq_register_car_cat_image_dynamic_tag' );

/**
 * Register the archive category image dynamic tag.
 *
 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager Dynamic tags manager.
 */
function afq_register_car_cat_archive_image_tag( $dynamic_tags_manager ) {

	if ( ! afq_option_load_elementor_tags() ) {
		return;
	}

	$dynamic_tags_manager->register( new AFQ_Car_Cat_Archive_Image_Tag() );
}
add_action( 'elementor/dynamic_tags/register', 'afq_register_car_cat_archive_image_tag' );

/**
 * Register price / catalog / video dynamic tags.
 *
 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager Dynamic tags manager.
 */
function afq_register_car_details_dynamic_tags( $dynamic_tags_manager ) {

	if ( ! afq_option_load_elementor_tags() ) {
		return;
	}

	$dynamic_tags_manager->register( new AFQ_Car_Price_Regular_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Price_Sale_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Catalog_Tag() );
	$dynamic_tags_manager->register( new AFQ_Car_Video_Tag() );
}
add_action( 'elementor/dynamic_tags/register', 'afq_register_car_details_dynamic_tags' );
