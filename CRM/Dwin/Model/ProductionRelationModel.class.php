<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/6/27
 * Time: 下午3:22
 */

namespace Dwin\Model;


use Think\Model;

class ProductionRelationModel extends Model
{
    static public $successStatus = 200;
    static public $failStatus = 400;

    static public $notDel = 0;
    static public $isDel  = 1;

    protected $_map = [
        'productionPlanId'  => 'production_plan_id',
        'productionOrderId' => 'production_order_id',
        'createTime'        => 'create_time',
        'staffId'           => 'staff_id',
        'staffName'         => 'staff_name',
        'isDel'             => 'is_del',
    ];

    /**
     * 添加 生产单与生产计划关系
     * @param array $param 生产计划提交数据：$param[$i]['id'] 为production_order_id;$param[$i]['plan_pid']为源单编号，以逗号连接
    */
    public function addRelation($param)
    {
        if (empty($param)) {
            $this->error = '没有要添加的数据';
        }
        $addArray = [];
        $this->createTime = time();
        $this->staffId = session('staffId');
        $this->staffName = session('nickname');
        for ($i = 0; $i < count($param); $i++) {
            $explodeArr = explode(',', $param[$i]['plan_pid']);
            for ($j = 0; $j < count($explodeArr); $j++) {
                $add['production_plan_id'] = $explodeArr[$j];
                $add['production_order_id'] = $param[$i]['id'];
                $add['create_time'] = $this->createTime;
                $add['staff_id']    = $this->staffId;
                $add['staff_name']  = $this->staffName;
                $addArray[] = $add;
            }
        }

        if (!count($addArray)) {
            $this->error = "关系表字段过滤问题";
            return false;
        }
        $addRst =  $this->addAll($addArray);
        return $addRst === false ? false : true;
    }

    public function getPlanIdsWithOrderId($orderId)
    {
        $filter['production_order_id'] = ['eq', $orderId];
        $field = "production_plan_id plan_id";
        $data  = $this->field($field)->where($filter)->select();
        if (count($data)) {
            return dataReturn('ok', self::$successStatus,getPrjIds($data,'plan_id'));
        } else {
            return dataReturn('数据为空', self::$failStatus);
        }
    }


    public function getPlanIdsWithPlanId($planId)
    {
        $filter['production_plan_id'] = ['eq', $planId];
        $field = "production_order_id order_id";
        $data  = $this->field($field)->where($filter)->select();
        if (count($data)) {
            return dataReturn('ok', self::$successStatus,getPrjIds($data,'order_id'));
        } else {
            return dataReturn('数据为空', self::$failStatus);
        }
    }

    public function getPlanNumber($map = []){
        $map["r.is_del"] = ['eq', self::$notDel];
        $data = $this->alias("r")
            ->field("r.production_plan_id, o.plan_number")
            ->join("left join crm_production_order o on o.id = r.production_order_id and o.is_del = " . ProductionOrderModel::$notDel)
            ->where($map)
            ->select();
        $planNumber = array_sum(array_column($data,'plan_number'));
        return $planNumber;
    }



}