<?php

namespace RY\Invoice\Ecpay;

defined('ABSPATH') or exit;

use RY\General\V20260724\Logs;

final class LinkProvider
{
    private static ?self $_instance = null;

    private array $api_test_url = [
        'get' => 'https://einvoice-stage.ecpay.com.tw/B2CInvoice/Issue',
        'invalid' => 'https://einvoice-stage.ecpay.com.tw/B2CInvoice/Invalid',
    ];

    private array $api_url = [
        'get' => 'https://einvoice.ecpay.com.tw/B2CInvoice/Issue',
        'invalid' => 'https://einvoice.ecpay.com.tw/B2CInvoice/Invalid',
    ];

    public static function instance(): LinkProvider
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init(): void {}

    public function get_invoice($invoice_data, $object_ID)
    {
        $general_info = $this->get_info();
        $api_info = $this->get_api_info();

        $post_args = [
            'MerchantID' => $api_info['MerchantID'],
            'RelateNumber' => $this->generate_trade_no($object_ID, $invoice_data['prefix']),
            'ProductServiceID' => $invoice_data['trackcode'],
            'CustomerIdentifier' => '',
            'CustomerName' => __('Customer', 'ry-invoice-for-ecpay'),
            'CustomerAddr' => __('Taiwan', 'ry-invoice-for-ecpay'),
            'CustomerEmail' => $invoice_data['email'],
            'Print' => 0,
            'Donation' => 0,
            'CarrierType' => '',
            'CarrierNum' => '',
            'TaxType' => 1,
            'SalesAmount' => round($invoice_data['total'], 0),
            'TaxAmount' => 0,
            'InvoiceRemark' => '#' . $invoice_data['no'],
            'Items' => [],
            'InvType' => '07',
            'vat' => 1,
        ];

        switch ($invoice_data['type']) {
            case 'host':
                $post_args['CarrierType'] = 1;
                break;
            case 'MOICA':
                $post_args['CarrierType'] = 2;
                $post_args['CarrierNum'] = $invoice_data['moica_no'];
                break;
            case 'phone_barcode':
                $post_args['CarrierType'] = 3;
                $post_args['CarrierNum'] = $invoice_data['phone_barcode'];
                break;
            case 'company':
                $post_args['Print'] = 1;
                $post_args['vat'] = 0;
                $post_args['CustomerIdentifier'] = $invoice_data['tax_no'];
                $post_args['CustomerName'] = $invoice_data['tax_name'];
                if (empty($post_args['CustomerName'])) {
                    $post_args['CustomerName'] = $post_args['CustomerIdentifier'];
                }
                break;
            case 'donate':
                $post_args['Donation'] = 1;
                $post_args['LoveCode'] = $invoice_data['donate_no'];
                break;
        }

        $salse_amount = 0;
        foreach ($invoice_data['item'] as $invoice_item) {
            if ($invoice_item['qty'] == 0 && $invoice_item['total'] == 0) {
                continue;
            }
            if ($invoice_item['qty'] == 0) {
                $invoice_item['qty'] = 1;
            }

            $name = mb_strimwidth(str_replace('|', '', wp_strip_all_tags($invoice_item['name'])), 0, 80, '');
            $unit = mb_strimwidth(str_replace('|', '', wp_strip_all_tags($invoice_item['unit'])), 0, 6, '');
            $qty = round($invoice_item['qty'], $general_info['count_precision']);
            $total = $invoice_item['total'];
            if ($post_args['vat'] === 0) {
                $total = round($total / 1.05, 0);
                $unit_price = round($total / $qty, $general_info['count_precision']);
                $total = $unit_price * $qty;
            } else {
                $unit_price = round($total / $qty, $general_info['count_precision']);
                $total = round($unit_price * $qty, $general_info['count_precision']);
            }

            match($invoice_item['tax']) {
                1 => $salse_amount += round($total, $general_info['count_precision']),
            };
            if ($post_args['vat'] === 0) {
                $total = round($total * 1.05, $general_info['count_precision']);
            }
            $post_args['Items'][] = [
                'ItemSeq' => count($post_args['Items']) + 1,
                'ItemName' => $name,
                'ItemCount' => $qty,
                'ItemWord' => $unit,
                'ItemPrice' => $unit_price,
                'ItemTaxType' => $invoice_item['tax'],
                'ItemAmount' => $total,
            ];
        }

        $salse_amount = round($salse_amount, 0);
        $post_args['TaxAmount'] = $post_args['SalesAmount'] - $salse_amount;

        $post_args['InvoiceRemark'] = apply_filters('ry_invoice-main_remark', $post_args['InvoiceRemark'], $object_ID);
        $post_args['InvoiceRemark'] = mb_strimwidth(wp_strip_all_tags($post_args['InvoiceRemark']), 0, 200, '');

        foreach ($post_args as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $sub_key => $sub_value) {
                    if (is_int($sub_value) || is_float($sub_value)) {
                        $post_args[$key][$sub_key] = (string) $sub_value;
                    }
                }
            }
            if (is_int($value) || is_float($value)) {
                $post_args[$key] = (string) $value;
            }
        }

        if ($api_info['testmode']) {
            $post_url = $this->api_test_url['get'];
        } else {
            $post_url = $this->api_url['get'];
        }

        do_action('ry_invoice_ecpay-pre_get_invoice', $post_args, $object_ID);
        Logs::log('ecpay-invoice', 'info', 'Get LINK #' . $object_ID, $post_args);
        $result = $this->link_server($post_url, $post_args, $api_info['MerchantID'], $api_info['HashKey'], $api_info['HashIV']);
        if ($result) {
            Logs::log('ecpay-invoice', 'info', 'Get response #' . $object_ID, $result);
            do_action('ry_invoice_ecpay-post_get_invoice', $post_args, $result, $object_ID);
        }
    }

    public function invalid_invoice($invoice_data, $object_ID = null)
    {
        $api_info = $this->get_api_info();

        $post_args = [
            'MerchantID' => $api_info['MerchantID'],
            'InvoiceNo' => $invoice_data['no'],
            'InvoiceDate' => substr($invoice_data['date'], 0, 10),
            'Reason' => __('Order cancel', 'ry-invoice-for-ecpay'),
        ];

        if ($api_info['testmode']) {
            $post_url = $this->api_test_url['invalid'];
        } else {
            $post_url = $this->api_url['invalid'];
        }

        do_action('ry_invoice_ecpay-pre_invalid_invoice', $post_args, $object_ID);
        Logs::log('ecpay-invoice', 'info', 'Invalid LINK #' . $object_ID, $post_args);
        $result = $this->link_server($post_url, $post_args, $api_info['MerchantID'], $api_info['HashKey'], $api_info['HashIV']);
        if ($result) {
            Logs::log('ecpay-invoice', 'info', 'Invalid response #' . $object_ID, $result);
            do_action('ry_invoice_ecpay-post_invalid_invoice', $post_args, $result, $object_ID);
        }
    }

    public function get_info()
    {
        $general_info = \RY_IFECPAY::get_option('general', []);
        if (!is_array($general_info)) {
            $general_info = [];
        }

        $general_info = array_merge([
            'count_precision' => 3,
            'amount_precision' => 7,
        ], $general_info);
        $general_info['count_precision'] = (int) $general_info['count_precision'];
        $general_info['amount_precision'] = (int) $general_info['amount_precision'];

        return $general_info;
    }

    public function get_api_info($load_test = true)
    {
        $api_info = \RY_IFECPAY::get_option('apiinfo', []);
        if (!is_array($api_info)) {
            $api_info = [];
        }
        $api_info = array_merge([
            'testmode' => 'no',
            'MerchantID' => '',
            'HashKey' => '',
            'HashIV' => '',
        ], $api_info);
        $api_info['testmode'] = $api_info['testmode'] === 'yes';

        if ($load_test && $api_info['testmode'] === true) {
            $api_info['MerchantID'] = '2000132';
            $api_info['HashKey'] = 'ejCk326UnaZWKisg';
            $api_info['HashIV'] = 'q9jcZX8Ib9LM8wYk';
        }

        return $api_info;
    }

    protected function generate_trade_no($object_ID, $order_prefix = '')
    {
        $trade_no = $order_prefix . $object_ID . 'T' . random_int(0, 9) . strrev((string) time());
        $trade_no = apply_filters('ry_invoice_ecpay-trade_no', $trade_no, $object_ID, $order_prefix);

        return substr($trade_no, 0, 18);
    }

    protected function link_server(string $url, array $args, string $MerchantID, string $HashKey, string $HashIV, int $timeout = 30)
    {
        wc_set_time_limit(40);

        $json_string = wp_json_encode($args);
        $json_string = str_replace(
            ['%2d', '%5f', '%2e', '%21', '%2a', '%28', '%29'],
            ['-', '_', '.', '!', '*', '(', ')'],
            urlencode($json_string)
        );
        $encrypt_string = @openssl_encrypt($json_string, 'aes-128-cbc', $HashKey, OPENSSL_RAW_DATA, $HashIV);

        $now = new \DateTime('now', new \DateTimeZone('Asia/Taipei'));
        $post_data = [
            'MerchantID' => $MerchantID,
            'RqHeader' => [
                'Timestamp' => $now->getTimestamp(),
            ],
            'Data' => base64_encode($encrypt_string),
        ];
        $response = wp_remote_post($url, [
            'timeout' => $timeout,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($post_data),
            'user-agent' => apply_filters('http_headers_useragent', 'WordPress/' . get_bloginfo('version')),
        ]);

        if (is_wp_error($response)) {
            Logs::log('ecpay-invoice', 'error', 'Link failed', $response->get_error_messages());
            return;
        }

        if (wp_remote_retrieve_response_code($response) != 200) {
            Logs::log('ecpay-invoice', 'error', 'Link HTTP status error', [
                '$post_data' => $post_data,
                'status' => wp_remote_retrieve_response_code($response),
            ]);
            return;
        }

        $result = json_decode(wp_remote_retrieve_body($response));

        if (!is_object($result)) {
            Logs::log('ecpay-invoice', 'error', 'Link response parse failed', ['response' => wp_remote_retrieve_body($response)]);
            return;
        }

        if ($result->TransCode == 1) {
            $result->Data = openssl_decrypt($result->Data, 'aes-128-cbc', $HashKey, 0, $HashIV);
            $result->Data = urldecode($result->Data);
            $result->Data = @json_decode($result->Data);
        }

        return $result;
    }
}
