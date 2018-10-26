<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/6/14
 * Time: 15:53
 */

namespace Dwin\Model;

use Think\Model;
class SaleserviceModel extends Model
{
    public function getSaleServiceList($where)
    {
        return $this->where($where)
            ->join('crm_customer AS cus ON cus.cid = customer_id')
            ->join('crm_staff AS sta ON sta.id = pid')
            ->field("crm_saleservice.*,cus.cname,cus.keyword,cus.uid AS rpbid,sta.name AS pname")
            ->order('addtime DESC')
            ->limit(0, 5)
	    ->select();
        // rpbid : respinsibleid
    }
}
