<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/6/14
 * Time: 16:32
 */

namespace Dwin\Model;


use Think\Model;

class ContactrecordModel extends Model
{

    public function getContactList($where)
    {

        return $this->where($where)
                ->join('crm_customer AS cus ON cus.cid = customerid')
                ->join('crm_staff AS sta ON sta.id = picid')
                ->field("crm_contactrecord.*,cus.cname,cus.keyword,cus.auditorid,sta.name AS pname")
                ->order('posttime DESC')
                ->select();
    }

    public function getRecordList($where,$field,$order,$start = 0, $length = -1)
    {
        return $this->where($where)
            ->join('crm_customer AS b ON cus.cid = customerid')
            ->join('crm_staff AS c ON sta.id = picid')
            ->field($field)
            ->order($order)
            ->limit($start,$length)
            ->select();
    }
}
