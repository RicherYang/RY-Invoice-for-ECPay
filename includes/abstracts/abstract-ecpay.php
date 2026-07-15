<?php

use RY\General\Logs;

abstract class RY_IFECPAY_Abstract_Invoice
{
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

        $now = new DateTime('now', new DateTimeZone('Asia/Taipei'));
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
