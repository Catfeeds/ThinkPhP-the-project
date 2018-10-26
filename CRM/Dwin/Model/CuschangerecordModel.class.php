<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2018/3/7
 * Time: 16:07
 */

namespace Dwin\Model;

use Think\Model;

class CuschangerecordModel extends Model
{
    const FLAG_ABANDON = 10;

    public function cusChangeRecord($cusChangeData)
    {

        $res = $this->addAll($cusChangeData);
        return $res;
    }

    public function getRecordDataWithEditCustomerName($postData,$cusName)
    {
        $changeData = array(
            'changetime'    => time(),
            'change_reason' => $postData['changeReason'],
            'oldname'       => $cusName,
            'nowname'       => $postData['companyName'],
            'cusid'         => (int)$postData['cid'],
            'auth_flag'     => 2,
            'auth_id'       => (int)$postData['audi'],
            'change_id'     => session('staffId')
        );
        return $changeData;
    }

    public function abandonCustomer($cusId, $authId, $cusName)
    {
        $changeData = array(
            'cusid'         => $cusId,
            'changetime'    => time(),
            'change_id'     =>  session('staffId'),
            'auth_id'       => $authId,
            'auth_flag'     => self::FLAG_ABANDON,
            'oldname'       => $cusName,
            'change_reason' => "该客户于" . date("Y-m-d H:i:s",time()) . "执行了客户放弃申请操作"
        );
        return $this->add($changeData);
    }

    public function abandonCustomerAll($cusData, $authId)
    {
        $changeData = [];
        foreach ($cusData as $datum) {
            $tmp = [
                'cusid'         => $datum['cid'],
                'oldname'       => $datum['cname'],
                'changetime'    => time(),
                'change_id'     => session('staffId'),
                'auth_id'       => $authId,
                'auth_flag'     => self::FLAG_ABANDON,
                'change_reason' => "该客户于" . date("Y-m-d H:i:s",time()) . "执行了客户放弃申请操作"
            ];
            $changeData[] = $tmp;
        }
        return $this->addAll($changeData);
    }
    public function getAbandonCusData($cusId)
    {
        $removeMap['auth_flag'] = ['eq', self::FLAG_ABANDON];
        $removeMap['cusid'] = ['eq', $cusId];
        return $this->where($removeMap)->field('id,cusid,changetime')->select();
    }
}