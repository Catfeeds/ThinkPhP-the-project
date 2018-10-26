<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2018/3/9
 * Time: 16:01
 */

namespace Dwin\Model;


use Think\Model;

class AuthRoleModel extends Model
{
    /**
     * @name getOneRole
     * @abstract 职位信息获取 不连表得到的是职位名等信息
     * @param string $field 查询的字段
     * @param mixed $where where查询条件
     * @param string $order 查询的order排序条件
     *
    */
    public function getOneRole($field, $where, $order)
    {
        return $this->field($field)->where($where)->order($order)->find();
    }


    /**
     * @name getRoleList
     * @abstract 职位信息获取 不连表得到的是职位名等信息
     * @param string $field 查询的字段
     * @param mixed $where where查询条件
     * @param string $order 查询的order排序条件
     * @param string $start limit查询的起始位置
     * @param string $length limit查询的长度
     *
     */
    public function getRoleList($field, $where, $order,$start, $length)
    {
        return $this->field($field)->where($where)->order($order)->limit($start, $length)->select();
    }



    /**
     * @name getRoleListWithJoin
     * @abstract 职位信息获取 连表得到的是职位的权限、该职位的员工等信息 auth_group as b,staff as c 读取b c的值用group_concat
     * @example $field = "crm_auth_role.role_name,GROUP_CONCAT(DISTINCT b.group_name), GROUP_CONCAT(DISTINCT c.name)"
     * @param string $field 查询的字段
     * @param mixed $where where查询条件
     * @param string $order 查询的order排序条件
     * @param string $start limit查询的起始位置
     * @param string $length limit查询的长度
     *
     */
    public function getRoleListWithJoin($field = "*", $where = "1 = 1", $order, $start = 0, $length = -1)
    {
        return $this->field($field)
            ->join('LEFT JOIN `crm_auth_group` b ON FIND_IN_SET(b.group_id, crm_auth_role.rule_ids)')
            ->join('LEFT JOIN `crm_staff` c ON FIND_IN_SET(c.id, crm_auth_role.staff_ids)')
            ->where($where)
            ->group("crm_auth_role.role_id")
            ->order($order)
            ->limit($start, $length)
            ->select();
    }
    /**
     * @name editRoleData
     * @abstract 编辑权限表信息
     * @param array $map where条件
     * @param array $updateData 更新的数据（key为字段名 value为变更的数据）
     * @return boolean
    */
    public function editRoleList($map, $updateData)
    {
        return $this->where($map)->setField($updateData);
    }


}