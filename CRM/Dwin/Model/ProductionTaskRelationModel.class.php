<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/6/27
 * Time: 下午3:22
 */

namespace Dwin\Model;


use Think\Model;

class ProductionTaskRelationModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

    static protected $notDel = 0;
    static protected $isDel  = 1;
    protected $_map = [
        'orderId'    => 'order_id',
        'taskId'     => 'task_id',
        'createTime' => 'create_time',
        'staffId'    => 'staff_id',
        'staffName'  => 'staff_name',
        'isDel'      => 'is_del',
    ];

    /**
     * 添加 生产计划与排产任务关系
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
            $explodeArr = explode(',', $param[$i]['order_pid']);
            for ($j = 0; $j < count($explodeArr); $j++) {
                $add['order_id'] = $explodeArr[$j];
                $add['task_id'] = $param[$i]['id'];
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

    public function getTaskIdsWithOrderId($orderId)
    {
        $filter['order_id'] = ['eq', $orderId];
        $field = "task_id";
        $data  = $this->field($field)->where($filter)->select();
        if (count($data)) {
            return dataReturn('ok', self::$successStatus, getPrjIds($data,'task_id'));
        } else {
            return dataReturn('数据为空', self::$failStatus);
        }
    }

    public function getOrderIdWithTaskId($taskId)
    {
        $filter['task_id'] = ['eq', $taskId];
        $field = "order_id";
        $data  = $this->field($field)->where($filter)->select();
        if (count($data)) {
            return dataReturn('ok', self::$successStatus, getPrjIds($data,'order_id'));
        } else {
            return dataReturn('数据为空', self::$failStatus);
        }
    }
}