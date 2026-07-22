<?php

defined('ABSPATH') or exit;

use RY\General\AbstractBasic;
use RY\General\ActionScheduler;
use RY\General\Logs;
use RY\Invoice\Ecpay\Admin\Admin;
use RY\Invoice\Ecpay\Cron;
use RY\Invoice\Ecpay\License;
use RY\Invoice\Ecpay\Update;
use RY\Invoice\Ecpay\Updater;
use RY\Invoice\Ecpay\WooCommerce\Fields;
use RY\Invoice\Ecpay\WooCommerce\Invoice;

final class RY_IFECPAY extends AbstractBasic
{
    public const OPTION_PREFIX = 'RY_IFECPAY_';

    public const PLUGIN_NAME = 'RY Invoice for ECPay';

    private static ?self $_instance = null;

    public static function instance(): RY_IFECPAY
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init(): void
    {
        load_plugin_textdomain('ry-invoice-for-ecpay', false, plugin_basename(dirname(__DIR__)) . '/languages');
        include_once RY_IFECPAY_PLUGIN_DIR . 'includes/vendor/woocommerce/action-scheduler/action-scheduler.php';
        ActionScheduler::instance();

        Logs::set_log(RY_IFECPAY::get_option('log', 'no') === 'yes', 'ecpay-invoice');

        if (is_admin()) {
            Update::update();
        }

        add_action('init', [$this, 'do_wp_init'], 9);
    }

    public function do_wp_init(): void
    {
        Updater::instance();

        if (is_admin()) {
            Admin::instance();
        }

        if (License::instance()->is_activated()) {
            Cron::add_action();
        }

        if (has_action('woocommerce_init')) {
            Fields::instance();

            if (License::instance()->is_activated()) {
                Invoice::instance();
            }
        }
    }

    public static function plugin_activation() {}

    public static function plugin_deactivation()
    {
        wp_unschedule_hook(self::OPTION_PREFIX . 'check_expire');
    }
}
