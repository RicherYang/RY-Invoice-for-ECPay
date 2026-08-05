<?php

namespace RY\Invoice\Ecpay;

defined('ABSPATH') or exit;

final class Utils
{
    public static function track_term_to_name($value = '')
    {
        static $list = [];
        if (empty($list)) {
            $list = [
                '1' => _x('Jan - Feb', 'track term', 'ry-invoice-for-ecpay'),
                '2' => _x('Mar - Apr', 'track term', 'ry-invoice-for-ecpay'),
                '3' => _x('May - Jun', 'track term', 'ry-invoice-for-ecpay'),
                '4' => _x('Jul - Aug', 'track term', 'ry-invoice-for-ecpay'),
                '5' => _x('Sep - Oct', 'track term', 'ry-invoice-for-ecpay'),
                '6' => _x('Nov - Dec', 'track term', 'ry-invoice-for-ecpay'),
            ];
        }

        return $list[$value] ?? $value;
    }

    public static function track_status_to_name($value = '')
    {
        static $list = [];
        if (empty($list)) {
            $list = [
                '1' => _x('Not enabled', 'track status', 'ry-invoice-for-ecpay'),
                '2' => _x('Use', 'track status', 'ry-invoice-for-ecpay'),
                '3' => _x('Disable', 'track status', 'ry-invoice-for-ecpay'),
                '4' => _x('Paused', 'track status', 'ry-invoice-for-ecpay'),
                '5' => _x('Pending', 'track status', 'ry-invoice-for-ecpay'),
                '6' => _x('Review failed', 'track status', 'ry-invoice-for-ecpay'),
            ];
        }

        return $list[$value] ?? $value;
    }
}
