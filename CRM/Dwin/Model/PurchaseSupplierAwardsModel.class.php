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

class PurchaseSupplierAwardsModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus = 400;
    static protected $insert;
    static protected $notDel = 0;
    static protected $isDel  = 1;


    const FILE_IS_UPLOAD = 1; // 文件已上传
    const FILE_NO_UPLOAD = 0; // 文件未上传

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

    protected $_validate = array(
        array("awards_name","require","获奖奖项不能为空!"),
        array("issuing_authority","require","颁发机构不能为空!"),
        array("validity_time","require","颁发时间不能为空!"),
    );

    public function getAddData($params)
    {
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], '没有提交新增数据'];
        }

        //数据非空验证
        if(empty($data['awards_name']) || empty($data['issuing_authority']) || empty($data['validity_time'])){
            return [-2, [], "请将数据填写完成"];
        }

        if($data['validity_time'] == "NaN"){
            return [-2, [], "奖状颁发时间填写不规范或则未填写"];
        }

        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');
        $data['supplier_pid'] = session('supplierPid');

        $data = $this->create($data);
        if ($data) {
            if(!empty($data['file_id'])){
                $data['file_status'] = self::FILE_IS_UPLOAD;
            }else {
                unset($data['file_status']);
            }
            return [0, $data, '数据实例化成功'];
        } else {
            return [-2, [], $this->getError()];
        }
    }

    /**
     * 验证修改提交的数据
     * @param $params
     * @return array
     */
    public function getEditData($params)
    {
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], '无修改数据提交'];
        }

        $oldData = $this->field("*")->find($data['id']);

        if(isset($data['validity_time']) && ($data['validity_time'] == 'NaN' || empty($data['validity_time']))){
            return [-2, [], "奖状颁发时间填写不规范或则未填写"];
        }

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

    /**
     * 比较是否有发生了改变
     * @param $oldData
     * @param $editedData
     * @return mixed
     */
    private function compareData($oldData, $editedData)
    {
        if(!empty($editedData['file_id'])){
            $editedData['file_status'] = self::FILE_IS_UPLOAD;
        }else {
            unset($editedData['file_status']);
        }

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

    public function getAwardsWithPId($supplierId)
    {
        $awardsMap['is_del'] = ['EQ', self::$notDel];
        $awardsMap['supplier_pid'] = ['EQ', $supplierId];
        return $this
            ->field("crm_purchase_supplier_awards.*,crm_staff.name,crm_file_upload.file_name,crm_file_upload.path as file_url,SUBSTRING_INDEX(crm_file_upload.path, '.', -1) as file_type")
            ->join("left join crm_staff on crm_staff.id = crm_purchase_supplier_awards.create_id")
            ->join("left join crm_file_upload on crm_file_upload.id = crm_purchase_supplier_awards.file_id")
            ->where($awardsMap)
            ->select();
    }
}