<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/6/14
 * Time: 16:32
 */

namespace Dwin\Model;


use Think\Model;

class CusCallbackModel extends Model
{
    public function getCallbackList($where,$field,$group = "online_id")
    {
        $rst = $this->where($where)
            ->field($field)
            ->join('LEFT JOIN crm_staff a ON a.id = crm_cus_callback.online_id')
            ->group($group)
            ->select();
        return $rst;
    }

    public function getCusCallbackData($field, $where, $order, $start, $length)
    {
        $data = $this->field($field)
            ->join('LEFT JOIN crm_staff AS a ON crm_cus_callback.uid = a.id')
            ->join('LEFT JOIN crm_staff AS b ON crm_cus_callback.online_id = b.id')
            ->where($where)
            ->order($order)
            ->limit($start, $length)
            ->select();
        return $data;
    }
}
