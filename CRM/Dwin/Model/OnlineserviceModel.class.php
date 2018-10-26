<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/6/14
 * Time: 16:03
 */

namespace Dwin\Model;


use Think\Model;

class OnlineserviceModel extends Model
{
    public function getOnlineServiceList($where)
    {
        return $this->where($where)
                ->join('crm_customer AS cus ON cus.cid = customer_id')
                ->join('crm_staff AS sta ON sta.id = server_id')
                ->field("crm_onlineservice.*,cus.cname,cus.keyword,cus.uid AS rpbid,sta.name AS pname")
                ->order('addtime DESC')
		->limit(0, 5)
                ->select();

    }

    public function getServiceList($where, $field, $start, $length, $order = 's.id')
    {
        if ($order == ' '){
            $order = 's.id';
        }
        $data = $this->field($field)
            ->join('LEFT JOIN crm_staff AS s ON crm_onlineservice.server_id = s.id')
            ->join('LEFT JOIN crm_customer AS c ON crm_onlineservice.customer_id = c.cid')
            ->join('LEFT JOIN crm_staff AS d ON c.uid = d.id')
            ->join('LEFT JOIN crm_industry ind ON ind.id= c.ctype')
            ->where($where)
            ->limit($start, $length)
            ->order($order)
            ->select();
        return $data;
    }

    public function getServiceListWithGroup($where, $field, $order, $start, $length, $group)
    {
        $data = $this->field($field)
            ->join('LEFT JOIN crm_staff AS s ON crm_onlineservice.server_id = s.id')
            ->join('LEFT JOIN crm_customer AS c ON crm_onlineservice.customer_id = c.cid')
            ->join('LEFT JOIN crm_staff AS d ON c.uid = d.id')
            ->join('LEFT JOIN crm_industry ind ON ind.id= c.ctype')
            ->where($where)
            ->order($order)
            ->limit($start, $length)
            ->group($group)
            ->select();
        return $data;
    }

}
