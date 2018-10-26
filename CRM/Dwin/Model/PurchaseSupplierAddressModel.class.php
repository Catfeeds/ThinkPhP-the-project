<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/6/15
 * Time: 下午2:18
 */

namespace Dwin\Model;


use think\Exception;
use Think\Model;

class PurchaseSupplierAddressModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

    static protected $insert;
    static protected $notDel = 0;
    static protected $isDel  = 1;

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

    //数据验证
    protected $_validate = array(
        array("address","require","供应商地址不能为空!"),
        array("addr_description","require","供应商地址描述不能为空!"),
    );

    public function getAddData($params)
    {
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], '没有提交新增数据'];
        }

        //数据非空验证
        if(empty($data['address']) || empty($data['addr_description'])){
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


    private function compareData($oldData, $editedData)
    {
        // 然后在与原先的数据做对比
        foreach ($editedData as $key => $val) {
            if ($val == $oldData[$key]) {
                unset($editedData[$key]);
            } else {
                continue;
            }
        }

        if(empty($editedData)){
            return false;
        }

        $editedData['id']   = $oldData['id'];
        $editedData['supplier_pid'] = $oldData['supplier_pid'];
        $editedData['update_time']  = time();
        $editedData['update_id']    = session('staffId');
        return $editedData;
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
            return [-1, [], "无数据修改"];
        } else {
            $createData = $this->create($editData);
            if(!$createData){
                return[-2,[], $this->getError()];
            }
            return [0, $createData, '数据实例化成功'];
        }
    }

    public function getAddressWithPId($supplierId)
    {
        $addressMap['is_del'] = ['EQ', self::$notDel];
        $addressMap['supplier_pid'] = ['EQ', $supplierId];
        return $this
            ->field("crm_purchase_supplier_address.*,crm_staff.name")
            ->join("left join crm_staff on crm_staff.id = crm_purchase_supplier_address.create_id")
            ->where($addressMap)
            ->select();
    }
}