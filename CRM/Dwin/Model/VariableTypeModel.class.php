<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/7/14
 * Time: 下午1:47
 */


namespace Dwin\Model;

use Think\Model;

class VariableTypeModel extends Model
{
    const TYPE_SUPPLIER_AUDIT = 1; // 供应商审核类别

    const STATUS_ACTIVE = 1; // 有效
    const STATUS_DELETE = 0; // 无效

    /**
     * @param $type  类型的type
     * @return mixed
     *  获取一个type的所有有效的类型
     */
    public function getOneKindtype($type){
        $data = [];
        $data['type'] = ['EQ', $type];
        $data['status'] = ['EQ', self::STATUS_ACTIVE];
        return $this->field("crm_variable_type.*,crm_staff.name")
            ->join("left join crm_staff on crm_staff.id = crm_variable_type.create_user")
            ->where($data)
            ->select();
    }
}