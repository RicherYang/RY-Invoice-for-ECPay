<?php

namespace RY\Invoice\Ecpay;

defined('ABSPATH') or exit;

use RY\General\V20260727\Logs;

final class Update
{
    public static function update()
    {
        $now_version = \RY_IFECPAY::get_option('version', '0.0.0');

        if (RY_IFECPAY_VERSION === $now_version) {
            return;
        }

        if ($now_version === '0.0.0') {
            \RY_IFECPAY::update_option('version', RY_IFECPAY_VERSION, true);
            return;
        }

        if (version_compare($now_version, '2026.7.27', '<')) {
            $old_dir = WP_CONTENT_DIR . '/ry-logs';
            if (is_dir($old_dir)) {
                $new_dir = Logs::get_log_directory();
                foreach (new \FilesystemIterator($old_dir, \FilesystemIterator::SKIP_DOTS) as $file) {
                    @rename($file->getPathname(), $new_dir . $file->getFilename());
                }
                @rmdir($old_dir);
            }
            add_action('init', [Logs::class, 'set_cron_job']);

            \RY_IFECPAY::update_option('version', '2026.7.27', true);
        }
    }
}
