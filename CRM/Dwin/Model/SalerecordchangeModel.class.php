<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2018/3/28
 * Time: 16:08
 */

namespace Dwin\Model;


use Think\Model;

class SalerecordchangeModel extends Model
{
    /**
     * 售后记录更新操作的记录
     * @param int  $id           对应salerecord的sid
     * @param string  $status       插入状态
     * @return bool|int     返回插入成功id
     */
    public function changeSaleStatus($id,$status)
    {
        $data['saleid']             = $id;
        $data['change_status']      = $status;
        $data['changemanid']        = session('staffId');
        $data['changemanname']      = session('nickname');
        $data['change_status_time'] = time();
        $filterData = $this->create($data);
        return $this->add($filterData);
    }
}