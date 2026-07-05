<?php

/**
 * Plugin Name: RY Invoice for ECPay
 * Plugin URI: https://ry-plugin.com/ry-invoice-for-ecpay
 * Description: ECPay E-invoice, support WooCommerce.
 * Version: 2026.7.5
 * Requires at least: 6.8
 * Requires PHP: 8.2
 * Author: Richer Yang
 * Author URI: https://richer.tw/
 * License: GPLv3
 * Update URI: https://ry-plugin.com/ry-invoice-for-ecpay
 *
 * Text Domain: ry-invoice-for-ecpay
 * Domain Path: /languages
 */

defined('ABSPATH') or exit;

define('RY_IFECPAY_VERSION', '2026.7.5');
define('RY_IFECPAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RY_IFECPAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RY_IFECPAY_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('RY_IFECPAY_PLUGIN_LANGUAGES_DIR', plugin_dir_path(__FILE__) . '/languages');

require_once RY_IFECPAY_PLUGIN_DIR . 'includes/main.php';

register_activation_hook(__FILE__, ['RY_IFECPAY', 'plugin_activation']);
register_deactivation_hook(__FILE__, ['RY_IFECPAY', 'plugin_deactivation']);

function RY_IFECPAY(): RY_IFECPAY
{
    return RY_IFECPAY::instance();
}

RY_IFECPAY();
