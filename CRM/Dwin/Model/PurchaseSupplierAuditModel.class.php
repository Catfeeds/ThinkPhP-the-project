<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/7/14
 * Time: 下午1:50
 */
namespace Dwin\Model;


use Think\Model;

class PurchaseSupplierAuditModel extends Model
{
    // 审核状态
    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_UNQUALIFIED = 1;     // 不合格
    const TYPE_QUALIFIED = 2;       // 合格

    public static $auditStatus = [
        self::TYPE_NOT_AUDIT => '未审核',
        self::TYPE_UNQUALIFIED => '不合格',
        self::TYPE_QUALIFIED => '合格',
    ];

    /**
     * @param $supplierId
     * @return mixed
     * 获取某家供应商相关的二级审核信息
     */
    public function getAuditWithPId($supplierId)
    {
        $map['crm_purchase_supplier_audit.supplier_pid'] = ['EQ', $supplierId];
        $map['crm_variable_type.status'] = ['EQ', VariableTypeModel::STATUS_ACTIVE];
        return $this->join('crm_variable_type on crm_variable_type.id = crm_purchase_supplier_audit.audit_id')
            ->join("left join crm_staff on crm_staff.id = crm_purchase_supplier_audit.staff_id")
            ->join("left join crm_file_upload on crm_file_upload.id = crm_purchase_supplier_audit.file_id")
            ->where($map)
            ->field("crm_purchase_supplier_audit.*,crm_variable_type.name as type_name,crm_staff.name,crm_file_upload.file_name,crm_file_upload.path as file_url,SUBSTRING_INDEX(crm_file_upload.path, '.', -1) as file_type")
            ->select();
    }

    /**
     * @param $supplierPid
     * 生成供应商的时候生成对应的二级审核内容
     * @return array
     */
    public function createSupplierAuditMsg($supplierPid, $auditMsg){
        if(empty($supplierPid)){
            return [false, "供应商id未获取"];
        }

        $allData = [];
        if (!empty($auditMsg)){
            foreach ($auditMsg as $key => $item) {
                $data = [];
                $data['supplier_pid'] = $supplierPid;
                $data['audit_id'] = $item['id'];
                $data['status'] = self::TYPE_NOT_AUDIT;

                $allData[] = $data;
            }

            $res = $this->addAll($allData);
            if($res === false){
                return [$auditMsg, $this->getError()];
            }

            return [true, "二级审核成功生成"];
        }else{
            return [false, "二级审核数据未找到"];
        }
    }
}