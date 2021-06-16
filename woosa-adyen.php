<?php
/**
 * Plugin Name: Woosa - Adyen for WooCommerce
 * Plugin URI: https://www.woosa.nl/product/adyen-woocommerce-plugin/
 * Description: Allows WooCommerce to take payments via Adyen platform
 * Version: 1.1.4
 * Author: Woosa
 * Author URI:  https://www.woosa.nl
 * Text Domain: woosa-adyen
 * Domain Path: /languages
 * Network: false
 *
 * WC requires at least: 3.5.0
 * WC tested up to: 4.4.1
 *
 * @package	Woosa-Adyen
 * @author Woosa Team
 * @since 1.0.0
 */


namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


define(__NAMESPACE__ . '\PREFIX', 'adn');

define(__NAMESPACE__ . '\PLUGIN_VERSION', '1.1.4');

define(__NAMESPACE__ . '\PLUGIN_NAME', 'Woosa - Adyen for WooCommerce');

define(__NAMESPACE__ . '\PLUGIN_URL', untrailingslashit(plugin_dir_url(__FILE__)));

define(__NAMESPACE__ . '\PLUGIN_DIR', untrailingslashit(plugin_dir_path(__FILE__)));

define(__NAMESPACE__ . '\PLUGIN_BASENAME', plugin_basename(PLUGIN_DIR) . '/'.basename(__FILE__));

define(__NAMESPACE__ . '\PLUGIN_FOLDER', plugin_basename(PLUGIN_DIR));

define(__NAMESPACE__ . '\SETTINGS_TAB_ID', 'adyen');
define(__NAMESPACE__ . '\SETTINGS_TAB_LABEL', 'Adyen');
define(__NAMESPACE__ . '\SETTINGS_URL', admin_url('/admin.php?page=wc-settings&tab=' . SETTINGS_TAB_ID));

define(__NAMESPACE__ . '\LOGS_URL', admin_url('admin.php?page=wc-status&tab=logs'));

define(__NAMESPACE__ . '\ERROR_PATH', plugin_dir_path(__FILE__) . 'error.log');


//include files
require_once PLUGIN_DIR . '/vendor/autoload.php';

define(__NAMESPACE__ . '\DEBUG', Utility::is_debug_enabled());

Core::instance()->init();
Core::instance()->register_activation_hook();
Core::instance()->register_deactivation_hook();
Core::instance()->register_uninstall_hook();
Core::instance()->register_upgrade_hook();

//load translation
add_action('plugins_loaded', function(){
   load_textdomain( '{text_domain}', plugin_dir_path(__FILE__) . 'languages/{text_domain}-' . get_locale() . '.mo' );
});