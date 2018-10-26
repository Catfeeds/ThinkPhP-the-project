<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/6/15
 * Time: 下午2:18
 */

namespace Dwin\Model;

use Think\Exception;
use Think\Model;

class PurchaseSupplierFinanceModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus    = 400;
    static protected $notDel = 0;
    static protected $isDel  = 1;

    //数据验证
    protected $_validate = array(
        array("finance_year","require","财务年不能为空!"),
        array("total_assets","require","总资产不能为空!"),
        array("main_income","require","主要营收不能为空!"),
        array("net_profit","require","净利润不能为空!"),
        array("profit_rat","require","净润率不能为空!"),
    );

    /**
     * 去除非此表的字段数据
     * @param $params
     * @return array
     */
    public function getNewField($params){
        $fieldData = $this->getDbFields();
        $data = [];
        foreach ($fieldData as $key => $field){
            if(isset($params[$field])){
                $data[$field] = $params[$field];
            }
        }
        return $data;
    }

    public function getAddData($params)
    {
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], '没有提交新增数据'];
        }

        if(empty($data['finance_year']) || empty($data['total_assets']) || empty($data['main_income']) || empty($data['profit_rat']) || empty($data['net_profit'])){
            return [-2, [], "请将数据填写完成"];
        }

        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');
        $data['supplier_pid'] = session('supplierPid');

        $data = $this->create($data);
        if(!$data){
            return [-2, [], $this->getError()];
        }else {
            return [0, $data, '数据实例化成功'];
        }
    }
    public function getEditData($params)
    {
        if (empty($params)) {
            return [-1, [], '无修改数据提交'];
        }

        $oldData = $this->field("*")->find($params['id']);
        $data = $this->getNewField($params);

        $editData = $this->compareData($oldData, $data);
        if ($editData === false) {
            return [-1, [], '无数据修改'];
        } else {
            $createData = $this->create($editData);
            if(!$createData){
                $this->error = $this->getError();
                return[-2,[], $this->getError()];
            }
            return [0, $createData, "数据实例化成功"];
        }
    }

    private function compareData($oldData, $editedData)
    {
        // 先把不存在当前表里面的字段剔除，然后在与原先的数据做对比
        foreach ($editedData as $key => $val) {
            if(!isset($oldData[$key])){
                unset($editedData[$key]);
                continue;
            }else{
                if ($val == $oldData[$key]) {
                    unset($editedData[$key]);
                } else {
                    continue;
                }
            }
        }

        if(empty($editedData)){
            return false;
        }
        $editedData['id']    = $oldData['id'];
        $editedData['supplier_pid'] = $oldData['supplier_pid'];
        $editedData['update_time']  = time();
        $editedData['update_id']    = session('staffId');
        return $editedData;
    }

    public function getFinanceWithPId($supplierId)
    {
        $map['supplier_pid'] = ['EQ', $supplierId];
        $map['is_del'] = ['EQ', self::$notDel];
        return $this->field("*")->where($map)->select();
    }

}