<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/3/13
 * Time: 上午11:47
 */

namespace Dwin\Model;


use Think\Model;

class ProductionLineModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

    static public $notDel = 0;
    static public $isDel  = 1;

    // 字段重构
    function getNewField($params){
        $fieldData = $this->getDbFields();
        $data = [];
        foreach ($fieldData as $key => $field){
            if(isset($params[$field])){
                $data[$field] = $params[$field];
            }
        }
        return $data;
    }

    /**
     * 比较前后是否修改
     * @param $oldData
     * @param $editedData
     * @return bool
     */
    private function compareData($oldData, $editedData)
    {
        // 先把不存在当前表里面的字段剔除，然后在与原先的数据做对比
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

        $editedData['id']           = $oldData['id'];
        return $editedData;
    }

    /**
     * 根据时间获取选定时间各个小组的产能分配情况。供后续进行订单的产能分配
     * @todo 排序问题
    */
    public function getGroupDataWithTime($time, $map, $sqlCondition)
    {
        $productionTaskModel = new ProductionTaskModel();
        $preData = $productionTaskModel->getGroupAbility($time);

        $baseData = $this->getBaseInfo($map, $sqlCondition);
//        $field = "unfinished_number,
//                  assign_number_with_time,
//                  assign_task,
//	                task_product_no,
//	                group_ability,
//	                can_assign_number,
//	                group_name,
//	                selectTime";

        foreach ($baseData as &$baseDatum) {
            $baseDatum['unfinished_number']       = 0;
            $baseDatum['assign_number_with_time'] = 0;
            $baseDatum['assign_task']             = '';
            $baseDatum['task_product_no']         = '';
            $baseDatum['can_assign_number']       = $baseDatum['group_ability'];
            $baseDatum['select_time']             = date('Y-m-d',$time);
        }
        foreach ($preData as $k => $v) {
            foreach ($baseData as $key => &$value) {
                if ((int)$value['id'] == (int)$v['group_id']) {
                    $value['unfinished_number']       += (int)$v['unfinished_number'];
                    $value['assign_number_with_time'] += (int)$v['assign_number_with_time'];
                    $value['assign_task']             = empty($value['assign_task']) ? $v['assign_task'] : $value['assign_task'] . "," . $v['assign_task'];
                    $value['task_product_no']         = empty($value['task_product_no']) ? $v['task_product_no'] : $value['assign_task'] . "," . $v['assign_task'];
                    $value['can_assign_number']       = $v['can_assign_number'];
                    $value['select_time']             = $v['select_time'];
                    break;
                }
            }
        }
        return $baseData;

    }

    public function getBaseInfo($map, $condition)
    {
        $map['a.is_del'] = ['EQ', self::$notDel];
        if (!$map['a.pid']) {
            $map['a.pid'] = ['NEQ', 0];
        }
        $field = "
                a.id,
                a.production_line group_name,
                a.pid,
                a.manufacturability group_ability,
                b.production_line line_name";
        return $this->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_production_line b ON b.id = a.pid')
            ->where($map)
            ->limit($condition['start'], $condition['length'])
            ->select();
    }

    public function getProductionData()
    {
        $map['is_del'] = ['EQ', self::$notDel];
        $map['pid'] = ['EQ', 0];
        $field = "
                id,
                production_line";
        return $this->where($map)->field($field)->select();
    }

    /**
     * 获取父级生产线
     * @param array $map
     * @return mixed
     */
    public function getProductionLine($map = []){
        $map['a.pid'] = ['eq', 0];
        $map['a.is_del'] = ['eq', self::$notDel];
        $data = $this->alias('a')
            ->field("a.*,ifnull(b.name,'无') responsible_name")
            ->join('LEFT JOIN crm_staff b ON a.responsible_id = b.id')
            ->where($map)
            ->select();
        return $data;
    }

    /**
     * 获取子级生产线
     * @param array $map
     * @return mixed
     */
    public function getProductionChildLine($map = []){
        $map['a.pid'] = ['neq', 0];
        $map['a.is_del'] = ['eq', self::$notDel];
        $data = $this->alias('a')
            ->field("a.*,ifnull(b.name,'无') responsible_name")
            ->join('LEFT JOIN crm_staff b ON a.responsible_id = b.id')
            ->where($map)
            ->select();
        return $data;
    }

    public function editProductionLine($parma){
        $data = self::getNewField($parma);

        $oldData = $this->find($data['id']);
        if(empty($oldData['pid'])){
            return dataReturn("父级生产线不可以修改",400);
        }

        $editData = self::compareData($oldData, $data);
        if($editData === false){
            return dataReturn("数据无修改",400);
        }
        $res = $this->save($editData);
        if($res === false){
            return dataReturn($this->getError(),400);
        }
        return dataReturn("生产线数据修改成功",200);
    }

}