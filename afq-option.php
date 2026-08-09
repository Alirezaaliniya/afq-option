<?php
/**
 * AFQ Option
 *
 * @package           AFQ_Option
 * @author            Alireza aliniya
 * @copyright         2026 nias
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       AFQ Option | پلاگین اختصاصی خودرو و نمایندگان
 * Plugin URI:        https://nias.ir
 * Description:       پکیج کامل مدیریت محتوای سایت خودرو در یک پلاگین: پست‌تایپ <b>ماشین‌ها</b> (به‌همراه دسته‌بندی، گالری، تصاویر عادی/هاور/اسپات/جزئیات، مشخصات فنی تب‌بندی‌شده، قیمت، کاتالوگ و ویدیو)، <b>بخش‌نامه‌های فروش</b> با کلید «اتمام فروش» و انتخاب ترتیبی خودروها، <b>نمایندگان</b> با نقشه تعاملی ایران و جست‌وجوی استانی به‌صورت آجاکس، <b>صدای مشتریان</b> با پخش‌کننده صوت و مودال ویدیو، <b>سوالات متداول</b> با آکاردئون و اسکیمای FAQPage، و <b>فرم ثبت‌نام</b> چندبخشی با اعتبارسنجی کد ملی/شبا/کدپستی و اطلاع‌رسانی ایمیلی. تمام بخش‌ها با شورت‌کد قابل استفاده‌اند و داینامیک تگ‌ها و کوئری‌های اختصاصی المنتور را هم اضافه می‌کند.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Alireza aliniya
 * Author URI:        https://nias.ir
 * Text Domain:       afq-option
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * شورت‌کدها:
 *   [afq_faq_list]           سوالات متداول (count, open_first, schema)
 *   [afq_car_spot]           تصویر اسپات خودرو (id, pos_engine, pos_performance, pos_dimensions, pos_features)
 *   [afq_rep_map]            نقشه نمایندگان (map)
 *   [afq_voice_grid]         صدای مشتریان (count, columns)
 *   [afq_circular_cars]      خودروهای بخش‌نامه (id)
 *   [afq_signup_form]        فرم ثبت‌نام (types, type)
 *   [afq_request_form]     فرم صدای مشتری
 *   [afq_request_track]    فرم پیگیری
 *
 * کوئری‌های المنتور (Query ID):
 *   afq_circular_sold        بخش‌نامه‌های اتمام فروش
 *   afq_circular_available   بخش‌نامه‌های فعال
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
