<?php
/**
 * Created by Sublime.
 * User: yajun_sun
 * Date: 2017/11/27
 * Time: 9:31
 */

namespace Dwin\Model;


use Think\Model;

class ProductionTaskModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

    static protected $insert;
    static public $notDel = 0;
    static public $isDel  = 1;
    protected $addData = [];

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
        if (empty($params)) {
            $this->error = '没有提交数据';
            return false;
        }
        $idModel = new \Dwin\Model\MaxIdModel();
        $this->addData = [];
        $timeStart = time();
        $sessionId = session('staffId');
        foreach ($params as $key => &$value) {
            $maxId = $idModel->getMaxId('production_task');
            $value['id'] = $maxId;
            $value['task_id'] = 'PCRW-' . (int)$maxId;
            $value['create_time']  = $timeStart;
            $value['create_id']    = $sessionId;
            $value['update_time']  = $timeStart;
            $value['update_id']    = $sessionId;
            $value['task_start_time']  = $value['task_start_time'] / 1000;
            $value['task_end_time']    = $value['task_end_time'] / 1000;
        }

        foreach ($params as $k => $item) {
            $d = $this->create($item);
            if ($d)
                if (!empty($d['task_number']) && $d['task_number'] > 0)
                    $this->addData[] = $d;

        }
        if (count($this->addData)) {
            return $this->addData;
        } else {
            $this->error = '数据过滤发生问题，上传参数可能非法，如有问题请联系管理';
            return false;
        }
    }

    private function compareData($oldData, $editedData)
    {
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
        $editedData['order_pid']           = $oldData['order_pid'];
        return $editedData;
    }

    /**
     * 修改task
     * @param $postData
     * @return array
     */
    public function editProductTask($postData){
        if (empty($postData['id']) || empty($postData['order_pid']) ){
            return dataReturn("参数不全",400);
        }

        // 判断当前task是否一下推入库单
        $stockModel = new StockInProductionModel();
        $check = $stockModel->where(['source_id' => $postData['id'], 'is_del' => StockInProductionModel::NO_DEL])->select();
        if(!empty($check)){
            return dataReturn("当前task中已下推，不可以修改",400);
        }

        $data = $this->getNewField($postData);

        $oldData = $this->where(['id' => $data['id']])->find();

        $editData = $this->compareData($oldData, $data);
        if (!$editData) {
            return dataReturn("无数据修改",400);
        } else {
            if (isset($editData['task_number'])){
                $taskData = self::getTaskMsgByOrderId($editData['order_pid']);
                $taskNum = array_sum(array_column($taskData,'task_number'));

                // order 信息
                $orderModel = new ProductionOrderModel();
                $orderData = $orderModel->where(['id' => $editData['order_pid']])->find();

                $num = $orderData['plan_number'] + $oldData['num'] - $taskNum - $editData['task_number'];
                if($num < 0){
                    return dataReturn("task总数量不能大于生产计划的数量",400);
                }
            }

            $res = $this->save($editData);
            if($res === false){
                return dataReturn($this->getError(),400);
            }
            return dataReturn("task修改成功",400);
        }

    }

    /**
     * 删除task
     * @param $id
     * @return array
     */
    public function delProductionTask($id){
        // 判断是否有下推有效的入库单
        $stockModel = new StockInProductionModel();
        $check = $stockModel->where(['source_id' => $id, 'is_del' => StockInProductionModel::NO_DEL])->select();
        if(!empty($check)){
            return dataReturn("当前task中已下推，不可以修改",400);
        }

        $res = $this->where(['id' => $id])->setField(['is_del' => self::$isDel]);
        if($res === false){
            return dataReturn($this->getError(),400);
        }
        return dataReturn("task删除成功",200);
    }

    /**
     * 校验各个小组剩余产能与添加的新任务总数量是否合法
     * @todo 传递的data为处理后的数据，captiData为当前剩余可分配产能
    */
    public function validateAddData($data)
    {
        $processData = $this->getTaskNumByGroup($data);

        // $data 提交到任务根据日期分组
        $compared = $this->compareTaskData($processData);

        $unAddMsg = "";
        $addData = [];
        foreach($data as $k => $v) {
            if (in_array($v['task_group'],$compared['not'])) {
                $unAddMsg .= $v['product_no'] . "分配给" . $v['task_group'] . "的份额超出了可分配范围，数据未提交<br>";
            } else {
                $addData[] = $v;
            }
        }
        if (count($addData) && $unAddMsg == "") {
            return dataReturn('ok', self::$successStatus, $addData);
        } elseif (count($addData) && $unAddMsg != "") {
            return dataReturn($unAddMsg,300, $addData);
        } else {
            return dataReturn($unAddMsg, self::$failStatus);
        }

    }

    /**
     * 根据添加的数据，获取各个组不同日期的新任务总数量
    */
    public function getTaskNumByGroup($data)
    {
        // $data[$i] 为某条数据，包括开始日期，型号，数量，分组；
        // 得到结果：日期 分组 数量（总和）

        $returnData = [];
        $sortData = arr_sort(arr_sort($data, 'task_start_time','asc'),'task_group','asc');

        for ($i = 0; $i < count($sortData); $i++) {
            $tmp = $sortData[$i];
            $tmp['flag'] = true;
            for ($j = 0; $j < count($sortData); $j++) {
                if ($i != $j) {
                    if ($tmp['task_start_time'] == $sortData[$j]['task_start_time'] && $tmp['task_group'] == $sortData[$j]['task_group']) {
                        $tmp['task_number'] += $sortData[$j]['task_number'];
                    }
                }
            }
            if (count($returnData)) {
                for ($n = 0; $n < count($sortData); $n++) {
                    for($m = 0; $m < count($returnData[$m]);$m++) {
                        $insertFlag = $tmp['order_pid'] == $returnData[$m]['order_pid']
                            && $tmp['product_id'] == $returnData[$m]['product_id']
                            && $tmp['task_group'] == $returnData[$m]['task_group']
                            && $tmp['task_number'] == $returnData[$m]['task_number']
                            && $tmp['task_end_time'] == $returnData[$m]['task_end_time']
                            && $tmp['task_start_time'] == $returnData[$m]['task_start_time'];

                        if ($insertFlag) {
                            $tmp['flag'] = false;
                        }
                    }
                }
            }

            if ($tmp['flag']) {
                $returnData[] = $tmp;
            }
        }
        return array_values($returnData);
    }

    /**
     * 验证提交的数据与各班组的可分配产能数量
     * 大于分配产能数量将不会被提交数据
     * @param array $processData 要提交的准备数据（班组，排产数量等）
     * @return array $arr $arr['not'] 为不提交数据， $arr['yes']为提交数据
    */
    public function compareTaskData($processData)
    {
        $arr = [];
        for ($i = 0; $i < count($processData); $i++) {
            $map['task_start_time'] = ['eq', $processData[$i]['task_start_time']];
            $map['task_group'] = ['eq', $processData[$i]['task_group']];
            $num = $this->alias('task')->where($map)
                ->field('sum(task.task_number) - sum(ifnull(sip.stock_in_num,0)) num')
                ->join('LEFT JOIN (select a.source_id,sum(ifnull(b.num,0)) stock_in_num from crm_stock_in_production a left join crm_stock_in_record b on a.id = b.source_id where a.is_del = 0 group by a.source_id)sip ON sip.source_id = task.id')
                ->group('task.task_group')
                ->select()[0]['num'];

            $num = $num ? $num : 0;
            $abilityNum = M('production_line')->where(['id' => ['eq', $processData[$i]['task_group']]])->field('manufacturability')->find()['manufacturability'];

            $checkNum = $abilityNum - $num;
            if ($checkNum < $processData[$i]['task_number']) {
                $arr['not'][] = $processData[$i]['task_group'];
            } else {
                $arr['yes'][] = $processData[$i]['task_group'];
            }
        }
        return $arr;
    }

    /**
     * 添加生产任务：添加任务:小组，生产型号、数量、源单编号等，更新生产生产计划中已分配数量，添加关联关系表数据
     * 添加任务需校验小组剩余可分配产能。
     * @todo 维护Order状态；
    */
    public function addProductionTaskTrans($postData)
    {
        $addData = $this->getAddData($postData['data']);

        if (!$addData) {
            return false;
        }
        $validateRst = $this->validateAddData($addData);

        if ($validateRst['status'] == self::$failStatus) {
            $this->error = $validateRst['msg'];
            return false;
        }
        $this->startTrans();
        /** 更新状态需放在前，不然校验数量由于事务隔离级别会有bug */
        $productionPlanModel = new ProductionOrderModel();
        for ($i = 0; $i < count($postData['updData']); $i++) {
            $rst[$i] = $productionPlanModel->updateNumWithProductionTask($postData['updData'][$i]['id'], $postData['updData'][$i]['num'], 'addProductionTask');
            if ($rst[$i] === false) {
                $this->rollback();
                $this->error = $productionPlanModel->getError();
                return false;
            }
        }

        $addOrderRst = $this->addAll($validateRst['data']);
        if ($addOrderRst === false) {
            $this->rollback();
            $this->error = "添加生产任务数据失败";
            return false;
        }

        $productionRelationModel = new ProductionTaskRelationModel();
        $relationAddRst = $productionRelationModel->addRelation($validateRst['data']);
        if ($relationAddRst === false) {
            $this->rollback();
            $this->error = $productionRelationModel->getError();
            return false;
        }
        $this->commit();
        return true;

    }

    public function getProductionTaskInfoWithId($alias, $taskId) {

        $map["$alias.id"] = ['EQ', $taskId];
        $map["$alias.is_del"] = ['EQ', self::$notDel];
        $field = "$alias.id,
                  from_unixtime($alias.create_time) create_time,
                  $alias.task_id,
                  $alias.product_no,
                  $alias.product_id,
                  material.warehouse_id default_rep_id,
                  $alias.task_number,
                  $alias.task_line,
                  $alias.task_group,
                  $alias.production_type,
                  from_unixtime($alias.task_start_time,'%y-%m-%d') start_t,
                  from_unixtime($alias.task_end_time,'%y-%m-%d') end_t,
                  $alias.production_status,
                  sum(ifnull(sip.in_num,0)) complete_quantity,
                  ifnull(from_unixtime($alias.actual_end_time,'%y-%m-%d'),' ') actual_t,
                  $alias.tips,
                  $alias.create_id,
                  $alias.create_name,
                  line1.production_line task_line_name,
                  line2.production_line task_group_name,
                  produce_order.production_code order_code";
        return $this->getTaskDataWithLimit($alias, $field, $map, 0, 10, "$alias.id")[0];
    }

    public function getIndexData($sqlCondition, $map, $alias)
    {
        $map[$alias . ".is_del"] = ['EQ', self::$notDel];
        $field = "$alias.id,
                  from_unixtime($alias.create_time) create_time,
                  $alias.task_id,
                  $alias.product_no,
                  $alias.product_id,
                  material.warehouse_id default_rep_id,
                  $alias.task_number,
                  $alias.task_line,
                  $alias.task_group,
                  $alias.production_type,
                  from_unixtime($alias.task_start_time,'%y-%m-%d') start_t,
                  from_unixtime($alias.task_end_time,'%y-%m-%d') end_t,
                  $alias.production_status,
                  sum(ifnull(sip.in_num,0)) complete_quantity,
                  ifnull(from_unixtime($alias.actual_end_time,'%y-%m-%d'),' ') actual_t,
                  $alias.tips,
                  $alias.create_id,
                  $alias.create_name,
                  line1.production_line task_line_name,
                  line2.production_line task_group_name,
                  produce_order.production_code order_code
                  ";
        return $data = $this->getTaskDataWithLimit($alias, $field, $map, $sqlCondition['start'], $sqlCondition['length'], $sqlCondition['order']);
    }

    public function getTaskDataWithLimit($alias,$field, $map, $start, $length, $order)
    {
        return $data = $this->alias($alias)
            ->field($field)
            ->where($map)
            ->join('LEFT JOIN crm_production_line line1 ON line1.id = task_line')
            ->join('LEFT JOIN crm_production_line line2 ON line2.id = task_group')
            ->join("LEFT JOIN crm_material material ON material.product_id = $alias.product_id")
            ->join("LEFT JOIN crm_production_order produce_order ON $alias.order_pid = produce_order.id")
            ->join("LEFT JOIN (select a.*,sum(ifnull(b.num,0)) in_num from crm_stock_in_production a left join crm_stock_in_record b on a.id = b.source_id group by a.id) sip ON $alias.id = sip.source_id and sip.is_del = 0")
            ->group("$alias.id")
            ->limit($start, $length)
            ->order($order)
            ->select();
    }

    public function getTaskInfoWithOrderId($id)
    {

        $taskRelationModel = new ProductionTaskRelationModel();
        $planIdData = $taskRelationModel->getTaskIdsWithOrderId($id);
        if ($planIdData['status'] !== 200) {
            return [];
        } else {
            $map['task.id'] = ['IN', $planIdData['data']];
            $map['task.is_del'] = ['eq', 0];
            $field = "order.production_code,
                      task.product_id,
                      task.product_no,
                      line.production_line group_name,
                      task.task_id,
                      task.task_number,
                      from_unixtime(task.task_start_time) start_t,
                      from_unixtime(task.task_end_time) end_t,
                      sum(ifnull(rec.num,0)) complete_quantity,
                      from_unixtime(task.actual_end_time) actual_t";
            return $this->alias('task')->field($field)
                ->join('LEFT JOIN crm_production_line line ON line.id = task.task_group')
                ->join('LEFT JOIN crm_production_order `order` ON `order`.id = task.order_pid')
                ->join('LEFT JOIN crm_stock_in_production sip ON sip.source_id = task.id')
                ->join('LEFT JOIN crm_stock_in_record rec ON (rec.source_id = sip.id AND rec.product_id = task.product_id)')
                ->where($map)
                ->group('task.id')
                ->select();
        }
    }

    // 查看不同时间的可分配产能，可添加产能，各生产班组的情况。
    public function getGroupAbility($time)
    {
        $field = "sum(task.task_number) - sum(ifnull(sip.stock_in_num,0)) unfinished_number,
                  sum(task.task_number),
                  sum(case when task.task_start_time = $time then task.task_number else 0 end) assign_number_with_time,
                  GROUP_CONCAT(distinct task.task_id) assign_task,
	              GROUP_CONCAT(distinct task.product_no) task_product_no,
	              line.manufacturability group_ability,
	              line.manufacturability - sum(task.task_number) + sum(ifnull(sip.stock_in_num,0)) can_assign_number,
	              line.production_line group_name,
	              line.id group_id,
	              from_unixtime($time) select_time";
        $data = $this->alias('task')
            ->field($field)
            ->join('LEFT JOIN crm_production_line line ON line.id = task.task_group')
            ->join('LEFT JOIN (select a.source_id,sum(ifnull(b.num,0)) stock_in_num from crm_stock_in_production a left join crm_stock_in_record b on a.id = b.source_id where a.is_del = 0 group by a.source_id)sip ON sip.source_id = task.id')
            ->group('task.task_group')
            ->select();
        return $data;
    }

    /**
     * 添加下推入库记录时调用，更新完工数量；
    */
    public function updateCompleteQuantity($base, $material)
    {
        $filter['id'] = ['eq', $base['source_id']];
        $updateData['complete_quantity'] = array('exp',"complete_quantity + {$material['num']}");
        $rst = $this->where($filter)->save($updateData);
        return $rst === false ? false : true;
    }

    /**删除入库记录时调用，更新完工数量*/
    public function resetCompleteQuantity($base, $material)
    {
        $filter['id'] = ['eq', $base['source_id']];
        $updateData['complete_quantity'] = array('exp', "complete_quantity - {$material['num']}");
        $rst = $this->where($filter)->save($updateData);
        return $rst === false ? false : true;
    }

    public function rollbackTaskNumber($baseData, $materialData)
    {
        $taskUpdRst = [];
        foreach ($materialData as $datum) {
            $taskUpdRst[] = $this->resetCompleteQuantity($baseData, $datum);
        }
        if (in_array(false,$taskUpdRst)) {
            $this->error = "修改updateNumber数据出错";
            return false;
        }
        $resetStatusRst = $this->resetTaskStatus($baseData['source_id']);
        if ($resetStatusRst === false) {
            return false;
        }
        $orderModel = new ProductionOrderModel();
        $resetRst = $orderModel->resetOrderStatusWithTaskId($baseData['source_id']);
        if ($resetRst === false) {
            $this->error = $orderModel->getError();
            return false;
        }
        return true;
    }

    public function resetTaskStatus($id)
    {

        $filter['id'] = ['EQ', $id];
        $info = $this->where($filter)->find();
        $stockInModel = new StockInProductionModel();
        $config = [
            'alias'     => 'b',
            'taskId'    => $info['id'],
            'productId' => $info['product_id']
        ];
        $completeQuantity = $stockInModel->countNumWithProductIdAndSourceId($config);
        if ($completeQuantity > 0) {
            if ($completeQuantity >= $info['task_number']) {
                $data['actual_end_time'] = $info['actual_end_time'] ? $info['actual_end_time'] : time();
                $data['production_status'] = 2;
            } else {
                $data['actual_end_time'] = null;
                $data['production_status'] = 1;
            }
        } else {
            $data['actual_end_time'] = null;
            $data['production_status'] = 0;
        }

        $rst = $this->where($filter)->save($data);
        if ($rst === false) {
            $this->error = "状态更新失败";
            return false;
        } else {
            return true;
        }
    }

    public function validateUpdateNum($taskId, $num)
    {
        if ($num < 0) {
            $this->error = "生产入库制单入库不能为负数";
            return false;
        }
        if (0 == $num) {
            $this->error = "生产入库制单入库不能为0";
            return false;
        }
        $taskInfo = $this->find($taskId);
        $stockInModel = new StockInProductionModel();
        $config = [
            'alias'     => 'b',
            'taskId'    => $taskInfo['id'],
            'productId' => $taskInfo['product_id']
        ];
        $completeQuantity = $stockInModel->countNumWithProductIdAndSourceId($config);
        $checkNum = $taskInfo['task_number'] - (int)$completeQuantity;
        if ($checkNum < 0) {
            $this->error = "程序出现问题，请联系管理，下推数量已经超过了该单据的数量";
            return false;
        }
        if (0 == $checkNum) {
            $this->error = "无需下推";
            return false;
        }
        if (($num - $checkNum) > 0) {
            $this->error = "您入库的数量超出了该任务单的可入库数，请您重新录入" . $checkNum . "," . $num;
            return false;
        }
        return true;
    }

    public function getDataWithTaskId($id, $returnDataSet)
    {
        $sourceOrderModel = new ProductionOrderModel();
        $stockInRecordModel       = new StockInRecordModel();
        $configArr = ['base','sourceOrder','stockInData'];
        $orderData = [];
        foreach ($returnDataSet as $key => $item) {
            if (in_array($item, $configArr)) {
                switch ($item) {
                    case "base" :
                        $orderData[$item] = $this->getProductionTaskInfoWithId("a", $id);
                        break;
                    case "sourceOrder" :
                        $ids = $this->find($id)['order_pid'];
                        $orderData[$item] = $sourceOrderModel->getOrderInfoWithId($ids);
                        break;
                    case "stockInData" :
                        $orderData[$item] = $stockInRecordModel->getStockInRecordWithTaskId($id);
                        break;
                    default :
                        break;
                }
            }
        }
        return $orderData;
    }

    /**
     * 通过orderId 获取名下所有有效task信息
     * @param $id
     * @return mixed
     */
    public function getTaskMsgByOrderId($id){
        $data = $this->where(['order_pid' => $id, 'is_del' => self::$notDel])->select();
        return $data;
    }
}