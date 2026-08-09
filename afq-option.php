<?php
/**
 * Plugin Name:       AFQ Option
 * Description:       ماژول‌های اختصاصی سایت: ماشین‌ها، بخش‌نامه فروش، نمایندگان، صدای مشتریان، سوالات متداول و فرم ثبت‌نام — به همراه شورت‌کدها و داینامیک تگ‌های المنتور.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            AFQ
 * Text Domain:       afq-option
 *
 * @package AFQ_Option
 */

defined( 'ABSPATH' ) || exit;

define( 'AFQ_OPTION_VERSION', '1.0.0' );
define( 'AFQ_OPTION_FILE', __FILE__ );
define( 'AFQ_OPTION_PATH', plugin_dir_path( __FILE__ ) );
define( 'AFQ_OPTION_URL', plugin_dir_url( __FILE__ ) );

require_once AFQ_OPTION_PATH . 'includes/class-afq-option.php';

AFQ_Option::instance();

register_activation_hook( __FILE__, array( 'AFQ_Option', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AFQ_Option', 'deactivate' ) );
