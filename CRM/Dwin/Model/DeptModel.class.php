<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/3/13
 * Time: 上午11:47
 */

namespace Dwin\Model;


use Think\Model;

class DeptModel extends Model
{
    const TOP_DEPT = 1;
    const ONLINE_DEPT = 4;
    const MARKET_DEPT = 54;
    const SALE_DEPT   = 3;


    /**
     * @name getDeptList
     * @abstract 部门信息获取 连表得到的是上级部门 连的是自己，写条件时注意加表名.表字段 连表别名b
     * @param string $field 查询的字段
     * @param mixed $where where查询条件
     * @param string $order 查询的order排序条件
     * @param string $start limit查询的起始位置
     * @param string $length limit查询的长度
     * @return array $deptData 二维数组
     *
     */
    public function getDeptList($field, $where, $order, $start, $length)
    {
        $deptData = $this->field($field)
            ->join('LEFT JOIN crm_dept b ON crm_dept.parent_id = b.id')
            ->where($where)
            ->order($order)
            ->limit($start, $length)
            ->select();
        return $deptData;
    }

    /**
     * @name getOneDept
     * @abstract 部门信息获取 连表得到的是上级部门 连的是自己，写条件时注意加表名.表字段 连表别名b
     * @param string $field 查询的字段
     * @param mixed $where where查询条件
     * @param string $order 查询的order排序条件
     * @return array $data 一维数组
     *
     */
    public function getOneDept($field, $where, $order)
    {
        $data = $this->field($field)
            ->join('LEFT JOIN crm_dept b ON crm_dept.parent_id = b.id')
            ->where($where)
            ->order($order)
            ->find();
        return $data;
    }

    /**
     * @name getDeptNumber
     * @abstract 部门数量获取
     * @param string $primaryKey 计数的字段
     * @param mixed $where where查询条件
     * @return int $count
     *
     */
    public function getDeptNumber($where, $primaryKey)
    {
        $count = $this->where($where)->count($primaryKey);
        return $count;
    }


    /**
     * @name editDept
     * @abstract 部门编辑
     * @param array $updateData 更新的数据 key为数据表字段名 value为要更新的数据
     * @param mixed $where where查询条件
     * @return int $data
     */
    public function editDept($where, $updateData)
    {
        $data = $this->where($where)->setField($updateData);
        return $data;
    }

    /**
     * @name deleteDept
     * @abstract 部门删除
     * @param int $deptId
     * @return boolean
     */
    public function deleteDept($deptId)
    {
        $deleteFilter['dept.id'] = array('EQ', $deptId);
        $deptInfo = $this->getOneDept("crm_dept.parent_id", $deleteFilter, 'dept.id');
        $subDeptFilter['dept.parent_id'] = array('EQ', $deptId);
        $subDeptNumber = $this->getDeptNumber($subDeptFilter, "crm_dept.id");
        if ($deptInfo['parent_id'] == 0)  {
            return false;
        } else {
            if ($subDeptNumber > 0) {
                return false;
            } else {
                $rst = $this->where($deleteFilter)->delete();
                return $rst;
            }
        }

    }
}