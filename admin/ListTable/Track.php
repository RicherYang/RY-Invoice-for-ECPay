<?php

namespace RY\Invoice\Ecpay\Admin\ListTable;

defined('ABSPATH') or exit;

use RY\Invoice\Ecpay\LinkProvider;
use RY\Invoice\Ecpay\Utils;
use RY\Invoice\V20260827\ListTable\Track as BaseTrack;

final class Track extends BaseTrack
{
    public function prepare_items()
    {
        $time = new \DateTime('now', new \DateTimeZone('Asia/Taipei'));
        $get_list[] = [$time->format('Y'), ceil($time->format('n') / 2)];
        $time->add(new \DateInterval('P2M'));
        $get_list[] = [$time->format('Y'), ceil($time->format('n') / 2)];

        foreach ($get_list as $get) {
            $result = LinkProvider::instance()->track_status($get[0], $get[1]);
            if ($result->TransCode == '1') {
                if ($result->Data->RtnCode == '1') {
                    foreach ($result->Data->InvoiceInfo as $status) {
                        $this->items[] = [
                            'year' => $status->InvoiceYear,
                            'term' => $status->InvoiceTerm,
                            'code' => $status->InvoiceHeader,
                            'start_no' => $status->InvoiceStart,
                            'end_no' => $status->InvoiceEnd,
                            'now_no' => $status->InvoiceNo,
                            'trackcode' => $status->ProductServiceId,
                            'status' => $status->UseStatus,
                        ];
                    }
                }
            }
        }
    }

    protected function column_year($item)
    {
        return $item['year'] + 1911;
    }

    protected function column_term($item)
    {
        return Utils::track_term_to_name($item['term']);
    }

    protected function column_status($item)
    {
        $info = '';
        if ($item['status'] == '2') {
            $info = '<span class="dashicons dashicons-saved"></span>';
        }
        return $info . Utils::track_status_to_name($item['status']);
    }
}
