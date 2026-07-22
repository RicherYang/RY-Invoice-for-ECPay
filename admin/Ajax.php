<?php

namespace RY\Invoice\Ecpay\Admin;

defined('ABSPATH') or exit;

use RY\Invoice\Ecpay\WooCommerce\Invoice;

final class Ajax
{
    private static ?self $_instance = null;

    public static function instance(): Ajax
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init(): void
    {
        add_action('wp_ajax_RY_IFECPAY_get', [$this, 'get_invoice']);
        add_action('wp_ajax_RY_IFECPAY_cancel', [$this, 'cancel_invoice']);
        add_action('wp_ajax_RY_IFECPAY_invalid', [$this, 'invalid_invoice']);
    }

    public function get_invoice()
    {
        check_ajax_referer('get-invoice');

        $object_ID = intval($_POST['id'] ?? '');
        as_unschedule_action(\RY_IFECPAY::OPTION_PREFIX . 'auto_get_invoice', [$object_ID], 'ry-invoice');

        if (function_exists('wc_get_order')) {
            $order = wc_get_order($object_ID);
            if ($order) {
                Invoice::instance()->get_invoice($order);
            }
        }

        wp_die();
    }

    public function cancel_invoice()
    {
        check_ajax_referer('cancel-invoice');

        $object_ID = intval($_POST['id'] ?? '');
        as_unschedule_action(\RY_IFECPAY::OPTION_PREFIX . 'auto_get_invoice', [$object_ID], 'ry-invoice');

        if (function_exists('wc_get_order')) {
            $order = wc_get_order($object_ID);
            if ($order) {
                Invoice::instance()->cancel_invoice($order);
            }
        }

        wp_die();
    }

    public function invalid_invoice()
    {
        check_ajax_referer('invalid-invoice');

        $object_ID = intval($_POST['id'] ?? '');
        as_unschedule_action(\RY_IFECPAY::OPTION_PREFIX . 'auto_get_invoice', [$object_ID], 'ry-invoice');

        if (function_exists('wc_get_order')) {
            $order = wc_get_order($object_ID);
            if ($order) {
                Invoice::instance()->invalid_invoice($order);
            }
        }

        wp_die();
    }
}
