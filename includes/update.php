<?php

defined('ABSPATH') or exit;

final class RY_IFECPAY_Update
{
    public static function update()
    {
        $now_version = RY_IFECPAY::get_option('version', '0.0.0');

        if (RY_IFECPAY_VERSION === $now_version) {
            return;
        }

        if ($now_version === '0.0.0') {
            RY_IFECPAY::update_option('version', RY_IFECPAY_VERSION, true);
            return;
        }

        if (version_compare($now_version, '2026.7.5', '<')) {
            RY_IFECPAY::update_option('version', '2026.7.5', true);
        }
    }
}
