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

class PurchaseSupplierCertificationModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus    = 400;
    static protected $insert;
    static protected $notDel = 0;
    static protected $isDel  = 1;

    const FILE_IS_UPLOAD = 1; // 文件已上传
    const FILE_NO_UPLOAD = 0; // 文件未上传

    //数据验证
    protected $_validate = array(
        array("cer_name","require","资质文件名不能为空!"),
        array("issuing_authority","require","颁发机构不能为空!"),
        array("start_time","require","生效日期不能为空!"),
        array("stop_time","require","失效日期不能为空!"),
    );

    // 字段重构
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

        //数据非空验证
        if(empty($data['cer_name']) || empty($data['issuing_authority']) || empty($data['start_time']) || empty($data['stop_time'])){
            return [-2, [], "请将数据填写完成"];
        }

        if(($data['start_time'] == "NaN") || ($data['stop_time'] == "NaN")){
            return [-2, [], "资质文件起止时间填写不规范或则未填写"];
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
            if($data['start_time'] >= $data['stop_time']){
                return [-2, [], '资质文件有效时间不正确，请重新填写'];
            }
            return [0, $data, '数据实例化成功'];
        } else {
            return [-2, [], $this->getError()];
        }
    }

    public function getEditData($params)
    {
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], '无修改数据提交'];
        }

        if((isset($data['start_time']) && ($data['start_time'] == 'NaN' || empty($data['start_time']))) || (isset($data['stop_time']) && ($data['stop_time'] == 'NaN' || empty($data['stop_time'])))){
            return [-2, [], "资质文件起止时间填写不规范或则未填写"];
        }

        $oldData = $this->field("*")->find($data['id']);
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

    private function compareData($oldData, $editedData)
    {
        if(!empty($editedData['file_id'])){
            $editedData['file_status'] = self::FILE_IS_UPLOAD;
        }else {
            unset($editedData['file_status']);
        }

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

        $editedData['id']    = $oldData['id'];
        $editedData['supplier_pid'] = $oldData['supplier_pid'];
        $editedData['update_time']  = time();
        $editedData['update_id']    = session('staffId');
        return $editedData;
    }

    public function getCertificationWithPId($supplierId)
    {
        $certificationMap['is_del'] = ['EQ', self::$notDel];
        $certificationMap['supplier_pid'] = ['EQ', $supplierId];
        return $this
            ->field("crm_purchase_supplier_certification.*,crm_staff.name,crm_file_upload.file_name,crm_file_upload.path as file_url,SUBSTRING_INDEX(crm_file_upload.path, '.', -1) as file_type")
            ->join("left join crm_staff on crm_staff.id = crm_purchase_supplier_certification.create_id")
            ->join("left join crm_file_upload on crm_file_upload.id = crm_purchase_supplier_certification.file_id")
            ->where($certificationMap)
            ->select();
    }
}