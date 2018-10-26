<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/16
 * Time: 下午4:26
 */

namespace Dwin\Model;

use Think\Model;
class RepertorylistModel extends Model
{
    const MRP_CATE = [1,4];
    const QUALITY_CATE = [2,4];
    const ADMIN_CATE = [3,4];

    const NOT_DEL = 0;

    const PRODUCTION_WAREHOUSE = "K001,K002,K003";// 半成品库、元器件库A\B
    const PRODUCTION_INPUT_WAREHOUSE = "K003,K004"; // 生产可入库的库房编号

    public function getUserRightsRepertoryList($id)
    {
        $level = M('auth_role') -> query("SELECT MAX(stock_level) AS max FROM crm_auth_role WHERE FIND_IN_SET('".session('staffId')."',staff_ids)")[0]['max'];
        $this->field = "rep_id,repertory_name";
        $sql = "SELECT 
                        rep_id,repertory_name 
                        FROM crm_repertorylist 
                        WHERE 
                          display_level <= {$level} 
                          or 
                          find_in_set({$id}, warehouse_manager_id) 
                          or
                          find_in_set({$id}, logistics_staff_id)";
        $repoData = $this->query($sql);
        return $repoData;
    }

    public function getMrpWarehouseWithRight($id)
    {
        $roleModel      = new AuthRoleModel();
        $map['_string'] = "find_in_set({$id}, staff_ids)";
        $level = $roleModel->where($map)->field('max(stock_level) max')->select()[0]['max'];
        $repoMap['cate_flag'] = ['IN', implode(',',self::MRP_CATE)];
        $repoMap['is_del']    = ['EQ', self::NOT_DEL];
        $sonMap['display_level']  = ['ELT', $level];
        $sonMap['_string'] = "find_in_set({$id}, warehouse_manager_id)";
        $sonMap['_string'] = "find_in_set({$id}, logistics_staff_id)";
        $sonMap['_logic'] = 'or';
        $repoMap['_complex'] = $sonMap;


        $repoList = $this -> where($repoMap) -> select();
        return $repoList;
    }

    public function getRepertoryData($field, $map, $start = 0, $length = 100, $order = 'rep_id', $group = 'rep_id')
    {
        $map['is_del'] = ['EQ', self::NOT_DEL];
        return $this->field($field)
            ->alias('repertory')
            ->join('LEFT JOIN crm_staff manager ON FIND_IN_SET(manager.id,repertory.warehouse_manager_id)')
            ->join('LEFT JOIN crm_staff logistics_staff ON FIND_IN_SET(logistics_staff.id,repertory.logistics_staff_id)')
            ->where($map)
            ->limit($start, $length)
            ->order($order)
            ->group($group)
            ->select();
    }

    public function getRepertoryNumWithJoin($where)
    {
        $where['is_del'] = ['EQ', self::NOT_DEL];
        return $this->alias('repertory')
            ->join('LEFT JOIN crm_staff manager ON FIND_IN_SET(manager.id,repertory.warehouse_manager_id)')
            ->join('LEFT JOIN crm_staff logistics_staff ON FIND_IN_SET(logistics_staff.id,repertory.logistics_staff_id)')
            ->where($where)
            ->count('distinct rep_id');
    }


    /**
     * 获取可以入库提交记录的员工id.
    */
    public function getWarehouseManagerIds($ids)
    {
        $repertoryFilter['rep_id'] = ['IN', $ids];
        $repertoryFilter['is_del'] = ['EQ', self::NOT_DEL];
        $data = $this->where($repertoryFilter)->field('warehouse_manager_id')->select();
        $managerIds = getPrjIds($data, 'warehouse_manager_id');
        return $managerIds;
    }

    /**
     * 获得对应库房的物流员id
     * */
    public function getWarehouseLogisticsIds($repIds)
    {
        $repertoryFilter['rep_id'] = ['IN', $repIds];
        $repertoryFilter['is_del'] = ['EQ', self::NOT_DEL];
        $data = $this->where($repertoryFilter)->field('logistics_staff_id')->select();
        $managerIds = getPrjIds($data, 'logistics_staff_id');
        return $managerIds;
    }
    /**
     * 返回可生产入库的选项
    */
    public function getWarehouseManagerData($ids)
    {

        $repertoryFilter['rep_id'] = ['IN', $ids];
        $repertoryFilter['is_del'] = ['EQ', self::NOT_DEL];
        $data = $this->where($repertoryFilter)->field('rep_id warehouse_number, repertory_name warehouse_name')->select();
        return $data;
    }


    public function getRepInfoWithProductionLimit()
    {
        $map['id_del'] = ['EQ', self::NOT_DEL];
        $map['cate_flag'] = ['EQ', "1"];
        $field = "rep_id warehouse_id, repertory_name warehouse_name,concat_ws(',',warehouse_manager_id,logistics_staff_id) auditor_ids";
        return $data = $this->where($map)->field($field)->select();
    }

    /**
     * 获取入库单生成时可选库名称的list
     * @return mixed
     */
    public function getRepInfoList(){
        $map['id_del'] = ['EQ', self::NOT_DEL];
        $map['cate_flag'] = ['EQ', "2"];
        return $data = $this->where($map)->select();
    }

    /**
     * 获取出库单生成时可选库名称的list
     * @return mixed
     */
    public function getStockOutList(){
        $map['id_del'] = ['EQ', self::NOT_DEL];
        $map['cate_flag'] = ['EQ', "1"];
        return $data = $this->where($map)->select();
    }



    public function getRepBaseInfo()
    {
        $field = "rep_id id, repertory_name name";
        $map['is_del'] = ['eq', self::NOT_DEL];
        return $this->where($map)->field($field)->select();
    }


}