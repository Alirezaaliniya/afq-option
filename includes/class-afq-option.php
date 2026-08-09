<?php
/**
 * Plugin bootstrap / file loader.
 *
 * Admin-only code is not parsed on front-end requests, and front-end
 * markup helpers are not parsed on plain admin page loads.
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
final class AFQ_Option {

	/**
	 * Option holding the version the rewrite rules were last flushed for.
	 */
	const REWRITE_OPTION = 'afq_option_rewrite_version';

	/**
	 * Singleton instance.
	 *
	 * @var AFQ_Option|null
	 */
	private static $instance = null;

	/**
	 * Get the shared instance.
	 *
	 * @return AFQ_Option
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_files();

		add_action( 'admin_init', array( $this, 'maybe_flush_rewrite_rules' ) );
	}

	/**
	 * Load plugin files for the current request type.
	 */
	private function load_files() {

		$path = AFQ_OPTION_PATH . 'includes/';

		/* ---- Shared: needed on every request type. ---- */
		require_once $path . 'config.php';
		require_once $path . 'config-request.php';
		require_once $path . 'helpers.php';
		require_once $path . 'post-types.php';
		require_once $path . 'ajax.php';
		require_once $path . 'ajax-request.php';
		require_once $path . 'elementor.php';

		/*
		 * Front-end rendering. Also loaded in admin because shortcodes are
		 * rendered during AJAX requests and inside the Elementor editor.
		 */
		require_once $path . 'frontend/assets.php';
		require_once $path . 'frontend/shortcode-faq.php';
		require_once $path . 'frontend/shortcode-car-spot.php';
		require_once $path . 'frontend/shortcode-rep-map.php';
		require_once $path . 'frontend/shortcode-voice-grid.php';
		require_once $path . 'frontend/shortcode-circular-cars.php';
		require_once $path . 'frontend/shortcode-signup-form.php';
		require_once $path . 'frontend/shortcode-request-form.php';
		require_once $path . 'frontend/shortcode-request-track.php';

		/* ---- Admin only: meta boxes, list tables, admin assets. ---- */
		if ( is_admin() ) {
			require_once $path . 'admin/assets.php';
			require_once $path . 'admin/metabox-car-media.php';
			require_once $path . 'admin/metabox-car-specs.php';
			require_once $path . 'admin/metabox-car-details.php';
			require_once $path . 'admin/metabox-rep.php';
			require_once $path . 'admin/metabox-voice.php';
			require_once $path . 'admin/metabox-circular.php';
			require_once $path . 'admin/metabox-signup.php';
			require_once $path . 'admin/settings-signup.php';
			require_once $path . 'admin/metabox-request.php';
			require_once $path . 'admin/settings-request.php';
			require_once $path . 'admin/taxonomy-fields.php';
		}
	}

	/**
	 * Flush rewrite rules once per plugin version (admin requests only).
	 */
	public function maybe_flush_rewrite_rules() {

		if ( AFQ_OPTION_VERSION === get_option( self::REWRITE_OPTION ) ) {
			return;
		}

		flush_rewrite_rules();
		update_option( self::REWRITE_OPTION, AFQ_OPTION_VERSION, false );
	}

	/**
	 * Activation: register content types, then flush rewrite rules once.
	 */
	public static function activate() {
		afq_option_register_content_types();
		flush_rewrite_rules();
		update_option( self::REWRITE_OPTION, AFQ_OPTION_VERSION, false );
	}

	/**
	 * Deactivation: drop the plugin's rewrite rules.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
		delete_option( self::REWRITE_OPTION );
	}
}
