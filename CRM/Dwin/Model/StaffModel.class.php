<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/6/7
 * Time: 14:19
 */

namespace Dwin\Model;


use Dwin\Controller\CustomerController;
use Think\Model;

class StaffModel extends Model
{

    /**
     * @name getOneStaffInfo
     * @abstract 获取某一个员工的相关信息
     * @param array $map 查询where条件
     * @param string $field 要查询的字段
     * @return array $rst 要获取对应$field的数据
    */
    public function getOneStaffInfo($map, $field,$order = "id")
    {
        return $rst = M('staff')->where($map)->field($field)->order($order)->find();
    }


    /**
     * @name getStaffInfo
     * @abstract 获取用户信息方法（FIND_IN_SET函数连职位表）读取职位时用GROUP_CONCAT(c.role_name)
     * @param string $field 要select的字段 连表dept as b,auth_role as c,staff_login_status as d
     * @param mixed $where 查询条件 注意查询条件中的id要写表名
     * @param int $start limit查询的开始位置
     * @param int $length limit查询的长度
     * @order string 排序查询条件
     * @return array 数据库查询结果
    */
    public function getStaffInfo($field, $where = '1 = 1', $start, $length, $order)
    {
        $data = $this->field($field)
            ->join('LEFT JOIN `crm_dept` AS b ON b.id = deptid')
            ->join('LEFT JOIN `crm_auth_role` AS c ON FIND_IN_SET(crm_staff.id, c.staff_ids)')
            ->join('LEFT JOIN `crm_staff_login_status` AS d ON d.id = loginstatus')
            ->where($where)
            ->order($order)
            ->group('crm_staff.id')
            ->limit($start, $length)
            ->select();
        return $data;
    }


    /**
     * @name setStaffData
     * @param mixed $map where条件
     * $param array $setData 修改的数据（需要包含主键）
     * @return int $rst
    */
    public function setStaffData($map, $setData)
    {
        return $rst = $this->where($map)->setField($setData);
    }


    public function getCusAddAuditId()
    {
        $filter['roleid'] = array('IN', CustomerController::CUS_AUDIT);
        return $this->where($filter)->field('id, name, deptid')->order('deptid ASC')->select();
    }
}