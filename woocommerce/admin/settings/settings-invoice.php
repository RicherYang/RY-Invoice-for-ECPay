<?php

defined('ABSPATH') or exit;

$order_statuses = wc_get_order_statuses();
$paid_status = [];
foreach (wc_get_is_paid_statuses() as $status) {
    $paid_status[] = $order_statuses['wc-' . $status];
}
$paid_status = implode(', ', $paid_status);

return [
    [
        'title' => __('Base options', 'ry-invoice-for-ecpay'),
        'id' => 'base_options',
        'type' => 'title',
    ],
    [
        'title' => __('Show invoice number', 'ry-invoice-for-ecpay'),
        'id' => RY_IFECPAY::OPTION_PREFIX . 'show_invoice_number',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Show invoice number in Frontend order list', 'ry-invoice-for-ecpay'),
    ],
    [
        'title' => __('Move billing company', 'ry-invoice-for-ecpay'),
        'id' => RY_IFECPAY::OPTION_PREFIX . 'move_billing_company',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Move billing company to invoice area', 'ry-invoice-for-ecpay'),
    ],
    [
        'id' => 'base_options',
        'type' => 'sectionend',
    ],
    [
        'title' => __('Invoice options', 'ry-invoice-for-ecpay'),
        'id' => 'invoice_options',
        'type' => 'title',
    ],
    [
        'title' => __('Get mode', 'ry-invoice-for-ecpay'),
        'id' => RY_IFECPAY::OPTION_PREFIX . 'get_mode',
        'type' => 'select',
        'default' => 'manual',
        'options' => [
            'manual' => _x('manual', 'get mode', 'ry-invoice-for-ecpay'),
            'auto_paid' => _x('auto ( when order paid )', 'get mode', 'ry-invoice-for-ecpay'),
            'auto_completed' => _x('auto ( when order completed )', 'get mode', 'ry-invoice-for-ecpay'),
        ],
        'desc' => sprintf(
            /* translators: %s: paid status */
            __('Order paid status: %s', 'ry-invoice-for-ecpay'),
            $paid_status,
        ),
    ],
    [
        'title' => __('Skip foreign orders', 'ry-invoice-for-ecpay'),
        'id' => RY_IFECPAY::OPTION_PREFIX . 'skip_foreign_order',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Disable auto get invoice for order billing country and shipping country are not in Taiwan.', 'ry-invoice-for-ecpay'),
        'autoload' => false,
    ],
    [
        'title' => __('Delay time (hours)', 'ry-invoice-for-ecpay'),
        'id' => RY_IFECPAY::OPTION_PREFIX . 'get_delay_time',
        'type' => 'number',
        'default' => '0',
        'custom_attributes' => [
            'min' => '0',
            'max' => '336',
            'step' => '1',
        ],
        'desc' => __('After N hours get invoice.', 'ry-invoice-for-ecpay')
            . __('According to WordPress cron job, the actual execution time will be later than the specified time.', 'ry-invoice-for-ecpay'),
        'autoload' => false,
    ],
    [
        'title' => __('Invalid mode', 'ry-invoice-for-ecpay'),
        'id' => RY_IFECPAY::OPTION_PREFIX . 'invalid_mode',
        'type' => 'select',
        'default' => 'manual',
        'options' => [
            'manual' => _x('manual', 'invalid mode', 'ry-invoice-for-ecpay'),
            'auto_cancel' => _x('auto ( when order status cancelled OR refunded )', 'invalid mode', 'ry-invoice-for-ecpay'),
        ],
    ],
    [
        'title' => __('Trade no prefix', 'ry-invoice-for-ecpay'),
        'id' => RY_IFECPAY::OPTION_PREFIX . 'prefix',
        'type' => 'text',
        'desc' => __('The prefix string of trade no. Only letters and numbers allowed.', 'ry-invoice-for-ecpay'),
        'desc_tip' => true,
        'autoload' => false,
    ],
    [
        'title' => __('Custom track code', 'ry-invoice-for-ecpay'),
        'id' => RY_IFECPAY::OPTION_PREFIX . 'trackcode',
        'type' => 'text',
        'default' => '',
        'autoload' => false,
    ],
    [
        'id' => 'invoice_options',
        'type' => 'sectionend',
    ],
];
