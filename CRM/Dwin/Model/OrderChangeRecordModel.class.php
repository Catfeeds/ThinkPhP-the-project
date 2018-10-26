<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2018/4/14
 * Time: 11:26
 */

namespace Dwin\Model;


use Think\Model;

class OrderChangeRecordModel extends Model
{

    public function getOrderChangeRecord($filter, $field, $start = 0, $length = 10, $order = 'crm_order_change_record.id')
    {
        return $data =  $this->where($filter)
            ->field($field)
            ->join('LEFT JOIN crm_staff staff ON staff.id = finance_id')
            ->limit($start, $length)
            ->order($order)
            ->select();
    }

    public function getRecordAddData($orderId, $content)
    {
        return $recordData = array(
            'finance_id'     => session('staffId'),
            'change_time'    => time(),
            'order_id'       => $orderId,
            'change_content' => $content
        );
    }
}