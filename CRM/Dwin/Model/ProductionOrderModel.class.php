<?php
/**
 * Created by Sublime.
 * User: yajun_sun
 * Date: 2017/11/27
 * Time: 9:31
 */

namespace Dwin\Model;


use Think\Model;

class ProductionOrderModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

    static protected $insert;
    public static $notDel = 0;
    public static $isDel  = 1;
    protected $addData = [];

    const TYPE_UNTREATED = 0;  // 未处理
    const TYPE_OUT_OF_REP = 1; // 出库中
    const TYPE_OUT_ALL = 2;    // 出库完成

    /** 生产状态，是否完结*/
    const PROCESS_PENDING = 0;
    const PROCESS_PRODUCING = 1;
    const PROCESS_DONE = 2;

    /**
     * 排产状态
     * */
    const ASSIGN_PENDING = 0;
    const ASSIGN_DOING = 1;
    const ASSIGN_DONE = 2;

    const TYPE_NOT_AUDIT = 0;      // 未审核
    const TYPE_UNQUALIFIED = 1;     // 不合格
    const TYPE_QUALIFIED = 2;       // 合格
    const TYPE_NO_CONFIRM = 3;       // 未确认配料

    public static $auditStatus = [
        self::TYPE_NOT_AUDIT => '未审核',
        self::TYPE_UNQUALIFIED => '不合格',
        self::TYPE_QUALIFIED => '合格',
        self::TYPE_NO_CONFIRM => '未确认配料',
    ];


    public static $stockOutMap = [
        self::TYPE_UNTREATED => "未处理",
        self::TYPE_OUT_OF_REP => "出库中",
        self::TYPE_OUT_ALL => "出库完成",
    ];
    private $orderToTaskNum;
    public function _initialize()
    {
        $this->orderToTaskNum = 1;
    }

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
            $maxId = $idModel->getMaxId('production_order');
            $filter['product_id'] = ['EQ', $value['product_id']];
            $filter['bom_status'] = ['EQ', 2];
            $filter['is_del'] = ['EQ', 0];
            $value['id'] = $maxId;
            $value['plan_start_time'] = strtotime(date('Y-m-d',$value['plan_start_time']/1000));
            $value['plan_end_time']   = strtotime(date('Y-m-d',$value['plan_end_time']/1000));
            $value['production_code'] = 'SCJH-' . (int)$maxId;
            $value['create_time']  = $timeStart;
            $value['create_id']    = $sessionId;
            $value['update_time']  = $timeStart;
            $value['update_id']    = $sessionId;
            $value['bom_pid'] = M('material_bom')->where($filter)->find()['id'];
        }
        foreach ($params as $k => $item) {
            $d = $this->create($item);
            if (!empty($d['plan_number']) && ((int)$d['plan_number'] != 0) && !empty($d['bom_pid'])) {
                $this->addData[] = $d;
            }
            if (empty($d['bom_pid'])) {
                $this->error = "提交的生产计划中，该生产型号缺少有效的BOM,请联系质控或管理员解决后再下推生产计划，物料编号：" . M('material')->find($d['product_id'])['product_no'] . ";";
                return false;
            }
        }
        if (count($this->addData)) {
            return $this->addData;
        } else {
            $this->error = '数据过滤发生问题，上传参数可能非法，请联系管理';
            return false;
        }
    }

    /**
     * 添加生产计划：添加主订单，更新生产单，添加关联关系表数据
     *
    */
    public function addProductionOrderTrans($postData)
    {
        $addData = $this->getAddData($postData['data']);
        if (!$addData) {
            return dataReturn($this->getError(), self::$failStatus,$this->posts);
        }

        $this->startTrans();

        $productionPlanModel = new ProductionPlanModel();
        for ($i = 0; $i < count($postData['updData']); $i++) {
            $rst[$i] = $productionPlanModel->updateNumWithProductionOrder($postData['updData'][$i]['id'], $postData['updData'][$i]['num'], 'addProductionOrder');
            if ($rst[$i] === false) {
                $this->rollback();
                return dataReturn( "修改数据失败", self::$failStatus);
            }
        }

        $addOrderRst = $this->addAll($addData);

        if ($addOrderRst === false) {
            $this->rollback();
            return dataReturn( "添加失败", self::$failStatus);
        }

        $productionRelationModel = new ProductionRelationModel();
        $relationAddRst = $productionRelationModel->addRelation($this->addData);


        if ($relationAddRst === false) {
            $this->rollback();
            return dataReturn( "添加失败", self::$failStatus);
        }
        $this->commit();
        return dataReturn('ok，请前往计划排产页面',self::$successStatus);
    }

    /**
     * 比较两个数组信息，并返回后面一个与前面不同的数组
     * @param $oldData
     * @param $editedData
     * @return bool
     */
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
        return $editedData;
    }

    /**
     * 修改生产计划
     * @param $postData
     * @return array
     */
    public function editProductOrder($postData){
        if (empty($postData['id'])){
            return dataReturn("参数不全",400);
        }
        // 判断是否有下推有效的task
        $taskModel = new ProductionTaskModel();
        $check = $taskModel->where(['order_pid' => $postData['id'], 'is_del' => ProductionTaskModel::$notDel])->select();
        if(!empty($check)){
            return dataReturn("当前生产订单中已下推，不可以修改",400);
        }

        $data = [];
        $data['id'] = $postData['id'];
        $data['plan_number'] =  $postData['plan_number'];
        $data['plan_start_time'] =  strtotime($postData['plan_start_time']);
        $data['plan_end_time'] =  strtotime($postData['plan_end_time']);

        $oldData = $this->where(['id' => $data['id']])->find();

        $editData = $this->compareData($oldData, $data);
        if ($editData === false) {
            return dataReturn("无数据修改",400);
        } else {
            if (isset($editData['plan_number'])){
                $relationModel = new ProductionRelationModel();
                $relationData = $relationModel->where(['production_order_id' => $data['id']])->select();

                if(empty($relationData)){
                    return dataReturn("源单数据未找到",400);
                }

                $planModel = new ProductionPlanModel();
                $planIdArr = array_column($relationData,'production_plan_id');
                if(count($planIdArr) == 1){
                    // plan数据
                    $planData = $planModel->where(['id' => $planIdArr[0]])->find();

                    // plan 一对N order
                    $map = [];
                    $map['production_plan_id'] = ['eq', $planIdArr[0]];
                    $planNumber = $relationModel->getPlanNumber($map);
                    $num = $planData['production_plan_number'] - $planNumber + $oldData['plan_number'] - $editData['plan_number'];
                    if($num < 0){
                        return dataReturn("生产订单总量不可以大于生产计划数量",400);
                    }
                }

                if(count($planIdArr) > 1){
                    // plan N对- order
                    $map = [];
                    $map['production_plan_id'] = ['in', implode(',',$planIdArr)];
                    $planData = $planModel->where($map)->select();
                    $productionNumber = array_sum(array_column($planData,'production_plan_number'));

                    if($productionNumber < $editData['plan_number']){
                        return dataReturn("生产订单总量不可以大于生产计划数量",400);
                    }
                }
            }

            $res = $this->save($editData);
            if($res === false){
                return dataReturn($this->getError(),400);
            }
            return dataReturn("数据修改成功",200);
        }
    }

    /**
     * 删除生产计划
     * @param $id
     * @return array
     */
    public function delProductOrder($id){
        // 判断是否有下推有效的task
        $taskModel = new ProductionTaskModel();
        $check = $taskModel->where(['order_pid' => $id, 'is_del' => ProductionTaskModel::$notDel])->select();
        if(!empty($check)){
            return dataReturn("当前生产订单中已下推，不可以删除",400);
        }

        $res = $this->where(['id' => $id])->setField(['is_del' => self::$isDel]);
        if($res === false){
            return dataReturn($this->getError(),400);
        }
        return dataReturn("生产订单删除成功",200);
    }

    public function updateNumWithProductionTask($updateId, $num, $flag)
    {
        $filter['id'] = ['EQ', $updateId];
        $data = $this->getOrderInfoWithId($updateId);
        $data['can_update_num'] = $data['plan_number'] - $data['assign_number'];

        if ($data['can_update_num'] < $num) {
            $this->error = "有问题，生产单号" . $data['production_code'] . "可生成生产计划数量不合法" . $num . "," . $data['can_update_num'] . "," . $data['plan_number'] . "," . $data['assign_number'];
            return false;
        }
        switch ($flag) {
            case "addProductionTask" :
                $updateData['assign_number'] = array('exp', "assign_number + {$num}");
                $updateData['production_status'] = $data['can_update_num'] == $num ? 2 : 1;
                break;
            default :
                break;
        }

        if (empty($updateData)) {
            $this->error = "有问题，生产单号" . $data['production_code'] . "要更新的数据为空";
            return false;
        } else {
            return $this->where($filter)->setField($updateData) === false ? false : true;
        }

    }

    /**
     * 获取首页数据，与dataTable对接
     * todo sum(produce.out_num),produce.out_num,group_concat(produce.source_id),group_concat(produce.out_num) 连表链接了重复表，可能会有问题，在此备注
    */
    public function getIndexData($sqlCondition,$map, $alias, $time)
    {
        $map[$alias . ".is_del"] = ['EQ', self::$notDel];
        $count = $this->alias($alias)
            ->join("LEFT JOIN crm_material material on material.product_id  = {$alias}.product_id")
            ->join("LEFT JOIN crm_production_line line ON line.id = {$alias}.production_line")
            ->join("LEFT JOIN crm_material_bom bom ON bom.id = $alias.bom_pid")
            ->where($map)
            ->count();
        $filterCount = $count;
        if (trim($sqlCondition['search'])) {
            $map['material.product_no|material.product_name|line.production_line'] = ['LIKE', "%" . trim($sqlCondition['search']) . "%"];
            $filterCount = $this->alias($alias)
                ->join("LEFT JOIN crm_material material on material.product_id  = {$alias}.product_id")
                ->join("LEFT JOIN crm_production_line line ON line.id = {$alias}.production_line")
                ->where($map)
                ->count();
        }
        $days = "(case when ($alias.plan_end_time - $time) > 0 then ($alias.plan_end_time - $time)/86400 else 1 end)";
        $remainingDay = "(round(($alias.plan_end_time - $time) /86400))"; //剩余天数
        $selTimeAssignedNumber = "sum(case when task.task_start_time = $time then task.task_number else 0 end)"; // 当前时间已经分配了的数量
        $unAssignedNumber = "round($alias.plan_number - sum(ifnull(task.task_number,0)))"; // 未排产的数量
        $normalAssignNumber = "(case when ($alias.plan_number/($days)) > ($selTimeAssignedNumber) then ($alias.plan_number/($days) - ($selTimeAssignedNumber)) else 0 end)";

        $assignNumber = "(case when $alias.plan_end_time < $time then $unAssignedNumber when $alias.plan_start_time > $time then 0 else $normalAssignNumber end)";
        $field = "$alias.id,
                  from_unixtime($alias.create_time) create_t,
                  $alias.production_code,
                  material.product_no,
                  material.product_id,
                  bom.bom_id,
                  material.product_name,
                  line.production_line,
                  $alias.production_type,
                  from_unixtime($alias.plan_start_time,'%Y-%m-%d') start_t,
                  from_unixtime($alias.plan_end_time,'%Y-%m-%d') end_t,
                  $alias.production_status,
                  $alias.plan_number,
                  sum(ifnull(task.task_number,0)) assign_number,
                  $alias.actual_end_time,
                  ifnull($alias.tips,'无') tips,
                  $alias.create_id,
                  $alias.create_name,
                  $alias.stock_status,
                  $remainingDay remaining_day,
                  $selTimeAssignedNumber assigned_number,
                  round($assignNumber) need_assign_num,
                  sum(task.nums) in_stock_num,
                  production_progress,
                  bom.bom_num,
                  $alias.audit_status
                  ";
        $data = $this->alias($alias)
            ->field($field)
            ->where($map)
            ->join("LEFT JOIN crm_material material on material.product_id  = {$alias}.product_id")
            ->join("LEFT JOIN crm_production_line line ON line.id = {$alias}.production_line")
            ->join("LEFT JOIN  
                        (select mb.*,sum(mbs.num) bom_num 
                            from crm_material_bom mb 
                            LEFT JOIN crm_material_bom_sub mbs 
                                ON (mbs.bom_pid = mb.id and mbs.is_del = 0)
                            group by mb.id) 
                            bom 
                        ON bom.id = $alias.bom_pid")
//            ->join("LEFT JOIN crm_material_bom_sub bom_sub ON bom_sub.bom_pid = bom.id")
            ->join("LEFT JOIN 
                        (select t.*,sum(sip.num_s) nums
                            from crm_production_task t
                         left join 
                            (select a.is_del,a.source_id,sum(ifnull(b.num,0)) num_s 
                                 from crm_stock_in_production a
                             left join crm_stock_in_record b 
                                 on (b.source_id = a.id and b.is_del = 0)
                             group by a.id) sip on sip.source_id = t.id and sip.is_del = 0 group by t.id)  task ON (task.order_pid = $alias.id and task.is_del = 0)")
//            ->join("LEFT JOIN crm_stock_out_record sor ON sor.source_id = produce.id")
            ->limit($sqlCondition['start'], $sqlCondition['length'])
            ->order($sqlCondition['order'])
            ->group("$alias.id")
            ->select();
//        $data =  $this->getLastSql();
        return [$count, $filterCount, $data];
    }

    public function getProductionData($alias, $map, $sqlCondition, $field)
    {

        $map[$alias . ".is_del"] = ['EQ', self::$notDel];
        $data = $this->alias($alias)
            ->field($field)
            ->where($map)
            ->join("LEFT JOIN crm_material material on material.product_id  = {$alias}.product_id")
            ->join("LEFT JOIN crm_production_line line ON line.id = {$alias}.production_line")
            ->join("LEFT JOIN crm_material_bom bom ON bom.id = $alias.bom_pid")
            ->join("LEFT JOIN 
                        (select t.*,sum(sip.num_s) nums
                            from crm_production_task t
                         left join 
                            (select a.is_del,a.source_id,sum(ifnull(b.num,0)) num_s 
                                 from crm_stock_in_production a
                             left join crm_stock_in_record b 
                                 on b.source_id = a.id 
                             group by a.id) sip on sip.source_id = t.id and sip.is_del = 0 group by t.id)  task ON (task.order_pid = $alias.id and task.is_del = 0)")
            ->limit($sqlCondition['start'], $sqlCondition['length'])
            ->order($sqlCondition['order'])
            ->group("$alias.id")
            ->select();
        return $data;
    }

    public function resetOrderStatusWithTaskId($taskId)
    {
        $relationModel = new ProductionTaskRelationModel();
        $orderIds = $relationModel->getOrderIdWithTaskId($taskId);
        $alias = "pro_ord";
        $field = "$alias.id, sum(ifnull(task.nums,0)) in_stock_num,$alias.plan_number, $alias.production_progress";
        $map["$alias.id"] = ['IN', $orderIds];
        $sqlCondition = [
            'start'  => 0,
            'length' => 10,
            'order'  => "$alias.id desc",
        ];
        $data = $this->getProductionData($alias, $map, $sqlCondition, $field);
        foreach ($data as $datum) {
            $tmp['id'] = $datum['id'];
            if (0 === (int)$datum['in_stock_num']) {
                $tmp['production_progress'] = self::PROCESS_PENDING;
            } elseif ($datum['in_stock_num'] < $datum['plan_number']) {
                $tmp['production_progress'] = self::PROCESS_PRODUCING;
            } else {
                $tmp['production_progress'] = self::PROCESS_DONE;
            }
            $limitMap['id'] = ['EQ', $datum['id']];
            $rst = $this->where($limitMap)->setField($tmp);
            if ($rst === false) {
                $this->error = $datum['id'] . "更新完工状态失败";
                return false;
            }
        }
        return true;
    }

    public function getOrderInfoWithId($id)
    {
        $alias = 'a';
        $field = "$alias.id,
                  from_unixtime($alias.create_time) create_time,
                  $alias.production_code,
                  material.product_no,
                  material.product_id,
                  bom.bom_id,
                  material.product_name,
                  $alias.plan_number,
                  $alias.production_line,
                  $alias.production_type,
                  from_unixtime($alias.plan_start_time,'%Y-%m-%d') plan_start_time,
                  from_unixtime($alias.plan_end_time,'%Y-%m-%d') plan_end_time,
                  $alias.production_status,
                  $alias.plan_number,
                  sum(ifnull(task.task_number,0)) assign_number,
                  $alias.actual_end_time,
                  $alias.stock_status,
                  ifnull($alias.tips,'无') tips,
                  $alias.create_id,
                  $alias.create_name,
                  sum(ifnull(task.nums,0)) in_stock_num,
                  $alias.plan_number - sum(ifnull(task.nums,0)) remaining_amount,
                  production_progress
                  ";
        return $this->alias($alias)
            ->field($field)
            ->where(["$alias.id"=>['eq', $id]])
            ->join("LEFT JOIN crm_material material on material.product_id  = {$alias}.product_id")
            ->join("LEFT JOIN crm_production_line line ON line.id = {$alias}.production_line")
            ->join("LEFT JOIN crm_material_bom bom ON bom.id = $alias.bom_pid")
            ->join("LEFT JOIN 
                        (select t.*,sum(sip.num_s) nums
                            from crm_production_task t
                         left join 
                            (select a.is_del,a.source_id,sum(ifnull(b.num,0)) num_s 
                                 from crm_stock_in_production a
                             left join crm_stock_in_record b 
                                 on b.source_id = a.id 
                             group by a.id) sip on sip.source_id = t.id and sip.is_del = 0 group by t.id)  task ON (task.order_pid = $alias.id and task.is_del = 0)")
            ->group("$alias.id")
            ->limit(0,10)
            ->select()[0];
    }

    public function getDataWithOrderId($orderId, $returnDataSet = ['base'])
    {
        $sourcePlanModel = new ProductionPlanModel();
        $taskModel       = new ProductionTaskModel();
        $bomMaterialModel = new MaterialBomSubModel();
        $stockOutModel = new StockOutRecordModel();
        $configArr = ['base','sourcePlan','productionTask','bomData', 'stockOutData'];
        $orderData = [];
        foreach ($returnDataSet as $key => $item) {
            if (in_array($item, $configArr)) {
                switch ($item) {
                    case "base" :
                        $orderData[$item] = $this->getOrderInfoWithId($orderId);
                        break;
                    case "sourcePlan" :
                        $orderData[$item] = $sourcePlanModel->getPlanInfoWithOrderId($orderId);
                        break;
                    case "productionTask" :
                        $orderData[$item] = $taskModel->getTaskInfoWithOrderId($orderId);
                        break;
                    case "bomData" :
                        $orderData[$item] = $bomMaterialModel->getBomMaterialWithProduceOrderId($orderId);
                        break;
                    case "stockOutData" :
                        $orderData[$item] = $stockOutModel->getStockOutRecordWithProductionOrderId($orderId);
                        break;
                    default :
                        break;
                }
            }
        }
        return $orderData;
    }

    public function getAssigningOrder($time, $map)
    {
        $days = "(case when (plan_end_time - $time) > 0 then (plan_end_time - $time)/86400 else 1 end)";
        $selTimeAssignedNumber = "sum(case when task.task_start_time = $time then task.task_number else 0 end)";
        $unAssignedNumber = "round(plan_number - sum(ifnull(task.task_number,0)))";
        $normalAssignNumber = "(case when (plan_number/($days)) > ($selTimeAssignedNumber) then round((plan_number/($days) - ($selTimeAssignedNumber))) else 0 end)";

//        $materialBomOut = "round((ifnull(produce.out_num,0))/(bom.bom_num))";// 单据对应已经领料的数量
//        $canAssignNumberLimitByBom = $materialBomOut .  " - sum(ifnull(task.task_number,0))";
        $assignNumber = "(case when a.plan_end_time < $time then $unAssignedNumber else $normalAssignNumber end)";
        $field = "
                a.id,
                a.production_code,
                a.product_id,
                b.product_no,
                b.product_name,
                a.production_line,
                from_unixtime(a.plan_start_time) plan_start_time,
                from_unixtime(a.plan_end_time) plan_end_time,
                from_unixtime($time) assign_time,
                $days days,
                sum(ifnull(task.task_number,0)) assign_number,
                a.plan_number,
                a.plan_number - sum(ifnull(task.task_number,0)) pro_num,
                $selTimeAssignedNumber assign_time_num,
                $assignNumber t2,
                $normalAssignNumber t3
                ";
        $map['a.is_del'] = ['eq', self::$notDel];
        $map['a.production_status'] = ['in', '0,1,2'];
        return $this->alias('a')
            ->join('LEFT JOIN crm_material b ON b.product_id = a.product_id')
            ->join("LEFT JOIN crm_production_task_relation rel ON (rel.order_id = a.id and rel.is_del = 0)")
            ->join("LEFT JOIN crm_production_task task ON (task.id = rel.task_id and task.is_del = 0)")
            ->join("LEFT JOIN  
                        (select mb.*,sum(mbs.num) bom_num 
                            from crm_material_bom mb 
                            LEFT JOIN crm_material_bom_sub mbs 
                                ON (mbs.bom_pid = mb.id and mbs.is_del = 0)
                            group by mb.id) 
                            bom 
                        ON bom.id = a.bom_pid")
//            ->join("LEFT JOIN crm_material_bom_sub bom_sub ON bom_sub.bom_pid = bom.id")
            ->group('a.id')
            ->where($map)
            ->field($field)->select();
    }

    private function getPreDataGroupKey($groupData, $orderData, $num)
    {
        $key = 0;
        foreach ($groupData as $k => $v) {
            if ($k <= $num) {
                continue;
            }
            if ($v['pid'] == $orderData['production_line']) {
                return $key = $k;
            }
        }
        return $key;
    }

    /**
     * getPreData 某产线下的各个组的产能分配情况，排序后传入此方法，得到对应下推订单的预计分配情况
     * @param array $groupData 分组产能分配情况
     * @param array $orderData 生产计划数据 1维数组
     * @param int $time 时间戳 分配的时间
     * @return array 自动分配的task结果
     */
    public function getPreData($groupData,$orderData, $time, $num = 0, &$returnData = [])
    {

        $key = $this->getPreDataGroupKey($groupData, $orderData, $num);

        $totalCanAssignNum = 0;
        for ($i = 0; $i < count($groupData); $i++) {
            if ($groupData[$i]['can_assign_number'] >= 0) {
                $totalCanAssignNum += $groupData[$i]['can_assign_number'];
            }
        }

        $tmpNumber = $groupData[$key]['can_assign_number'] < $orderData['t2']
            ? ($groupData[$key]['can_assign_number'] <= 0
                ? $orderData['t2']
                : $groupData[$key]['can_assign_number'])
            : $orderData['t2'];
//        if ($totalCanAssignNum < $orderData['t2']) {
//            if ($key = 0) {
//                $tmpNumber = ceil($orderData['t2']/(count($groupData))); // 向上取整
//            } else {
//                $tmpNumber = ceil($orderData['t2']/(count($groupData) - $key)); // 向上取整
//            }
//
//        }

        $returnData[] = [
            'order_pid'         => $orderData['id'],
            'product_no'        => $orderData['product_no'],
            'product_id'        => $orderData['product_id'],
            'task_line'         => $orderData['production_line'],
            'task_start_time'   => $time,
            'task_end_time'     => $time + 24 * 3600,
            'production_status' => 0,
            'task_number'       => $tmpNumber,
            'task_group'        => $groupData[$key]['id'],
            'base_group'        => $groupData,
            'source_order'      => $orderData,
            'task_line_name'    => $groupData[$key]['line_name'],
            'task_group_name'   => $groupData[$key]['group_name'],
            'source_order_string' => $orderData['production_code']
        ];
        if ($groupData[$key]['can_assign_number'] < $orderData['t2'] && $this->orderToTaskNum < count($groupData)) {
            if ($groupData[$key]['can_assign_number'] <= 0) {
                $this->orderToTaskNum = 1;
                return $returnData;
            } else {
//                $key += 1;
                $this->orderToTaskNum += 1;
                $orderData['t2'] = $orderData['t2'] - $returnData[count($returnData) - 1]['task_number'];
                $this->getPreData($groupData, $orderData, $time, $key, $returnData);
            }
            $this->orderToTaskNum = 1;
            return $returnData;
        }
        $this->orderToTaskNum = 1;
        return $returnData;
    }

    /**
     * @param array  $baseData 处于分配生产过程中的订单二维数组
     * @param array $assignData 各个组的产能、未完成数据、可分配产能数据
     * $param int $timeLimit 选定的时间（要分配任务的时间点）
     * @todo preAssign 将各个订单分配给对应产线的各个小组，分组规则按照产线中班组的可分配数量排序，选可分配数量最少的。分配后can_assign_number重置
    */
    public function getPreAssignTask($baseData, $assignData, $timeLimit)
    {
        $data = [];
        foreach ($baseData as $key => $valueA) {
            $groupData = getFilterDataWithPid($assignData, $valueA['production_line'],'pid', 'can_assign_number', 'desc');
            if (count($groupData)) {
                $tmp = $this->getPreData($groupData, $valueA, $timeLimit);

                foreach ($tmp as $valueC) {
                    foreach ($assignData as $k => &$valueB) {
                        if ($valueB['id'] == $valueC['task_group']) {
                            $valueB['unfinished_number'] += $valueC['task_number'];
                            $valueB['assign_number_with_time'] += $valueC['task_number'];
                            $valueB['can_assign_number'] -= $valueC['task_number'];
                        }
                    }
                    $data[] = $valueC;
                }
            }
        }
        return $data;
    }

    /**
     * 查找生产领料中物料信息  针对出库单 crm_stock_material加了 type = 2
     * @param $id
     * @param $where
     * @return mixed
     */
    public function getMaterialMsg($id, $where = []) {
        $map['p.id'] = ['eq', $id];

        $where['so.source_id'] = ['eq', $id];
        $where['so.is_del'] = ['eq', StockOutProduceModel::NO_DEL];

        // 获取当前生产计划下物料对应的已下推数量
        $produceModel = new StockOutProduceModel();
        $sql = $produceModel->alias("so")
            ->field("IFNULL( SUM( r.num ), 0 ) AS number,l.product_id")
            ->join("left join crm_stock_out_record r ON r.is_del = " . StockOutRecordModel::NO_DEL . " AND r.source_id = so.id ")
            ->join("left join crm_bom_replace_log l on l.substituted_id = r.product_id and l.is_del = " . BomReplaceLogModel::NO_DEL . " AND l.produce_id = r.source_id")
            ->where($where)
            ->group("r.product_id")
            ->select(false);

        $data =  $this->alias('p')
            ->field("p.id,p.bom_pid,p.stock_status,mbs.product_id,m.product_no,m.product_name,m.product_number, p.plan_number as total_num, floor((ifnull(y.number,0) / mbs.num)) as used_num,mbs.num as one_num, m.warehouse_id as rep_pid,ifnull(cs.stock_number,0) as stock_number,ifnull(cs.o_audit,0) as o_audit,ifnull(cs.out_processing,0) as out_processing")
            ->join("left join crm_material_bom mb on mb.id = p.bom_pid and mb.is_del = " . MaterialBomModel::NO_DEL)
            ->join("left join crm_material_bom_sub mbs on mbs.bom_pid = mb.id and mbs.is_del = " . MaterialBomSubModel::NO_DEL)
            ->join("left join crm_material m on m.product_id = mbs.product_id")
            ->join("left join crm_stock cs on cs.product_id = mbs.product_id and cs.warehouse_number = m.warehouse_id")
            ->join("left join ( " . $sql . ") y on y.product_id = mbs.product_id")
//            ->join("left join crm_bom_replace_log r on r.order_id = p.id and r.product_id = mbs.product_id and r.is_del = " . BomReplaceLogModel::NO_DEL)
//            ->join("left join crm_stock_material sm on sm.source_id = r.produce_id and sm.is_del = " . StockMaterialModel::NO_DEL . " and sm.type = " . StockMaterialModel::TYPE_STOCK_OUT)
//            ->group("mbs.product_id")
            ->where($map)
            ->select();
        return $data;
    }

    public function getRelationOrderData($planId)
    {
        $productionRelationModel = new ProductionRelationModel();
        $planIdData = $productionRelationModel->getPlanIdsWithPlanId($planId);
        if ($planIdData['status'] !== 200) {
            return [];
        } else {
            $map['id'] = ['IN', $planIdData['data']];
            $map['is_del'] = ['eq', 0];
            $field = "*,from_unixtime(plan_start_time) plan_start_t,
            from_unixtime(plan_end_time) plan_end_t";
            return $this->field($field)->where($map)->select();
        }
    }

    /**
     * 根据id，获取当前生产计划基本信息
     * @param $id
     * @param array $map
     * @return mixed
     */
    public function getOrderBaseMsgById($id, $map=[]){
        if(is_array($id)){
            $map['o.id'] = ['in', implode(',', array_filter($id))];
        }else {
            $map['o.id'] = ['eq', $id];
        }
        $data = $this->alias("o")
            ->field("o.*, m.product_id, m.product_name, m.product_number")
            ->join("left join crm_material_bom b on b.id = o.bom_pid")
            ->join("left join crm_material m on m.product_id = b.product_id")
            ->where($map)
            ->find();
        return $data;
    }

    /**
     * 根据id，获取当前生产计划基本信息
     * @param $idArr
     * @param array $map
     * @return mixed
     */
    public function getOrderBaseMsgByIdArr($idArr, $map=[]){
        $map['o.id'] = ['in', implode(',', array_filter($idArr))];
        $data = $this->alias("o")
            ->field("o.*, m.product_id, m.product_name, m.product_number")
            ->join("left join crm_material_bom b on b.id = o.bom_pid")
            ->join("left join crm_material m on m.product_id = b.product_id")
            ->where($map)
            ->select();
        return $data;
    }

    /**
     * 获取订单的bom信息
     * @param $id
     * @param array $map
     * @return mixed
     */
    public function getOrderBomMsg($id, $map = []){
        $map['p.id'] = ['eq', $id];

        $where['so.source_id'] = ['eq', $id];
        $where['so.is_del'] = ['eq', StockOutProduceModel::NO_DEL];
        $data =  $this->alias('p')
            ->field("p.id,p.bom_pid,p.stock_status,mbs.product_id,m.product_no,m.product_name,m.product_number, p.plan_number as total_num, mbs.num as one_num, m.warehouse_id as rep_pid, ifnull(cs.stock_number,0) as stock_number,ifnull(cs.o_audit,0) as o_audit,ifnull(cs.out_processing,0) as out_processing")
            ->join("left join crm_material_bom mb on mb.id = p.bom_pid and mb.is_del = " . MaterialBomModel::NO_DEL)
            ->join("left join crm_material_bom_sub mbs on mbs.bom_pid = mb.id and mbs.is_del = " . MaterialBomSubModel::NO_DEL)
            ->join("left join crm_material m on m.product_id = mbs.product_id")
            ->join("left join crm_stock cs on cs.product_id = mbs.product_id and cs.warehouse_number = m.warehouse_id")
            ->where($map)
            ->select();
        return $data;
    }
}