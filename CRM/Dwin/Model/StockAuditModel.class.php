<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/13
 * Time: 9:51
 */
namespace Dwin\Model;
use Think\Model;
class StockAuditModel extends Model
{
    const SUCCESS_STATUS = 1;
    const FAIL_STATUS    = -1;
    /* 审核状态：1 待审 2 通过 3 不通过*/
    const AUDIT_PENDING  = '1';
    const AUDIT_PASS     = '2';
    const AUDIT_FAIL     = '3';
    /* type 库存记录类别：1入库 2 出库*/
    const IN_TYPE  = '1';
    const OUT_TYPE = '2';

    const PRODUCTION_CATE = 3;  //生产入库分类id
    const REWORK_CATE = 13;  //返工生产入库分类id

    protected $_validate = [
        ['type','require','请选择出库或者入库', 1],
        ['product_name','require','请输入产品名', 1],
        ['product_id','require','请输入产品名', 1],
        ['num','require','请输入数量', 1],
        ['auditor','require','请选择审核人', 1],
        ['cate','require','请选择分类', 1],
        ['proposer','require','请重新登录', 1],
    ];

    protected $_auto = [
        ['update_time', 'time', 3, 'function'],
    ];

    /**
     * 获得所有产品信息
     * @param $map      array   搜索条件
     * @param $start    string  起始行
     * @param $length   string  结果长度
     * @param $order    string  排序
     * @return array
     */
    public function index($map = [], $start = '0', $length = '10', $order = '')
    {
        return $this
            -> alias('audit')
            -> field('audit.*')
            -> where($map)
            -> limit($start,$length)
            -> order($order)
            -> select();
    }

    public function indexCount($map = [])
    {
        return $this
            -> alias('audit')
            -> field('audit.*,cate.cate_name, proposer.name proposer_name, auditor.name auditor_name')
            -> join('left join crm_stock_io_cate as cate on audit.cate = cate.id')
            -> join('left join crm_staff as proposer on audit.proposer = proposer.id')
            -> join('left join crm_staff as auditor on audit.auditor = auditor.id')
            -> where($map)
            -> count();
    }

    /**
     * 根据根据关联订单号查找出入库记录
     * @param string $order 出入库行为对应的订单单号
     * @param string $audit_type
     * @param string $audit_status
     * @return  array
     */
    public function showAllAuditActionOrderNumber($order, $audit_type = '1', $audit_status = '2')
    {
        $map =[
            'action_order_number' => ['EQ', $order],
            'type' => ['EQ', $audit_type],
            'audit_status' => ['EQ', $audit_status],
            'is_del' => ['EQ', 0],
        ];
        $data = $this
             -> alias('audit')
             -> field('audit.*, staff.name')
             -> join('left join crm_staff as staff on audit.auditor = staff.id')
             -> where($map)
             -> select();
        return $data;
    }


    /**
     * addProductionStockInLog
    */
    public function addProductionStockInLog()
    {

    }
    /**
     * 增加申请
     * @param array $params   新增的数据
     * @return bool
     */
    public function addAudit($params)
    {
        $params['create_time'] = time();

        $productModel   = new MaterialModel();
        $planModel      = new ProductionPlanModel();
        $repertoryModel = new RepertorylistModel();
        $stockModel     = new StockModel();

        $auditUpdate  = false;
        $planUpdate   = true;
        $repMap['rep_id'] = ['EQ', $params['putInWarehouse']];
        $warehouseData = $repertoryModel->where($repMap)->field('rep_id warehouse_number, repertory_name warehouse_name')->find();
        $product = $productModel->find($params['product_id']);
        $params['warehouse_name']   = $warehouseData['warehouse_name'];
        $params['warehouse_number'] = $warehouseData['warehouse_number'];
        if (isset($params['cateArr'])) {
            $cateArr = explode('_', $params['cateArr']);
            $params['cate'] = $cateArr[0];
            $params['cate_name'] = $cateArr[1];
            if (isset($params['putinProductionLineArr'])) {
                $arr = explode('_', $params['putinProductionLineArr']);
                $params['putin_production_line_id'] = $arr[0];
                $params['putin_production_line_name'] = $arr[1];
            } elseif ($params['cate'] == self::PRODUCTION_CATE) {
                $this->error = '请选择生产线';
                return false;
            }
        }

        $params['auditor']        = session('staffId');
        $params['auditor_name']   = session('nickname');
        $params['proposer']       = session('staffId');
        $params['proposer_name']  = session('nickname');
        $params['product_number'] = $product['product_number'];
        $params['product_no']     = $product['product_no'];

        $repertoryAuthString = $repertoryModel->getWarehouseManagerIds($params['warehouse_number']);
        if (!in_array(session('staff_id'), explode(',', $repertoryAuthString))) {
            $this->error = "非所选仓库库管，禁止提交数据";
            return false;

        }
        $audit = $this -> create($params);
        if ($audit === false) {
            return false;
        }
        if ($audit['num'] <= 0) {
            $this->error = '数量不合法';
            return false;
        }

        $this->startTrans();
        // 更新库存数据
//        $productUpdate = ($stockModel->addAudit($audit) !== false) ? true : false;
        $productUpdate = true;
        if ($productUpdate !== false) {
            // 判断是普通入库还是生产入库
            if ($audit['cate'] == self::PRODUCTION_CATE){
                // 检查数量是否合法
                if (!$audit['action_order_number']){
                    $this->error = '生产入库必须填写生产单号';
                    return false;
                }
                $productionPlan = M('production_plan') -> where(['production_order' => ['EQ', $audit['action_order_number']]]) -> find();
                if ($audit['num'] > $productionPlan['production_plan_rest_number']){
                    $this->error = '入库数量不得大于出库数量';
                    return false;
                }
                // 更新产品表的正在生产数量
                if (!$productModel -> updateProducingNumber($audit['product_id'], -$audit['num'])){
                    $productUpdate = false;
                }
                // 更新生产计划表
                $planUpdate = $planModel -> addAudit($audit['action_order_number'] , $audit['num']);
            }

            // 判断在返工生产入库
            if ($audit['cate'] == self::REWORK_CATE){
                if ($audit['num'] > $product['rework_number']){
                    $this->error = '入库数量不得大于返工数量';
                    return false;
                }
                $productUpdate = $productModel -> save(['product_id' => $audit['product_id'], 'rework_number' => $product['rework_number'] - $audit['num']]);
                $productUpdate = $productUpdate === false ? false : true;
            }
        }

        // 更新申请表的数据
        if ($this -> add($audit) !== false) {
            $auditUpdate = true;
        }

        if ($productUpdate && $auditUpdate && $planUpdate) {
            $this->commit();
            return true;
        } else {
            $this->rollback();
            if (!$productUpdate) {
                $this->error = '产品更新失败';
            }
            if (!$auditUpdate) {
                $this->error = '提交申请失败';
            }
            if (!$planUpdate) {
                $this->error = '生产计划更新失败';
            }
        }
        return false;
    }



    /**
     * 获取待审核的出入库产品数量
     * @param $type         int                 出入库
     * @param $product_id   int                 产品id
     * @param $action_order_number string   订单号
     * @param string $audit_status              审核状态
     * @return              int                 待审核的出/入库产品数量
     */
    public function getAuditNumber($type, $product_id, $action_order_number = 'default', $audit_status = '1')
    {
        $map = [
            'product_id'   => ['EQ', $product_id],
            'audit_status' => ['EQ', $audit_status],
            'type'         => ['EQ', $type],
            'action_order_number' => ['EQ', $action_order_number],
            'is_del'       => ['EQ', 0],
            '_logic'       => 'AND',
        ];

        if ($map['action_order_number'][1] == 'default'){
            unset($map['action_order_number']);
        }
        $data = $this
            -> where($map)
            -> sum('num');
        return $data;

    }


    /**
     * 修改审核状态
     * @param $auditID
     * @param $audit_status
     * @param $tips         string  申请备注
     * @return bool
     */
    public function changeAuditStatus($auditID, $audit_status, $tips)
    {


        $stockModel   = new StockModel();
        $planModel      = new ProductionPlanModel();
        $repertoryModel = new RepertorylistModel();
        $stockAuditData = $this->field('*')->find($auditID);
        if (!$stockAuditData) {
            $this->error = "数据有误,请联系管理";
            return false;
        }
        $logisticsStaffIds = $repertoryModel->getWarehouseManagerIds($stockAuditData['warehouse_number']);
        if (!in_array(session('staffId'), explode(',', $logisticsStaffIds))) {
            $this->error = "非该库房的库管，您没有权限处理，有问题请联系管理员";
            return false;
        }
        if ($stockAuditData['audit_status'] != self::AUDIT_PENDING) {
            $this->error = '该记录已审核，不可重复审核';
            return false;
        }
        $this -> startTrans();
        $planUpdate = true;
        // 判断生产入库的情况
        if ($stockAuditData['cate'] == self::PRODUCTION_CATE){
            if ($audit_status == self::AUDIT_PASS){
                $planUpdate = $planModel -> auditPass($stockAuditData);
            }else{
                $planUpdate = $planModel -> auditFail($stockAuditData);
            }
        }
        // 修改库存表
        $stockUpdate = $stockModel -> editAudit($stockAuditData, $audit_status);
        // 更新审核表
        $auditUpdate = false;
        if ($stockUpdate && $planUpdate) {
            $res = $this->where(['id' => ['EQ',$auditID]])->save(['audit_status' => $audit_status, 'audit_tips' => $tips]);
            if ($res !== false){
                $auditUpdate = true;
            }
        }
        if ($stockUpdate && $auditUpdate && $planUpdate) {
            $this->commit();
            return true;
        } else {
            if (!$auditUpdate){
                $this->error = '审核更新失败';
            }
            if (!$planUpdate){
                $this->error = '生产计划更新失败';
            }
            $this->rollback();
            return false;
        }
    }

    /**
     * 删除入库申请后的数据回滚方法
     * @param $auditID
     * @return bool
     */
    public function changeAuditStatusRollBack($auditID)
    {
        $stockModel = new StockModel();
        $planModel = new ProductionPlanModel();
        $audit = $this -> find($auditID);
        $planUpdate = true;
        // 判断生产入库的情况
        if ($audit['cate'] == self::PRODUCTION_CATE){
            if ($audit['audit_status'] == self::AUDIT_PASS){
                $planUpdate = $planModel -> auditPassRollback($audit);
            }elseif ($audit['audit_status'] == 1){
                $planUpdate = $planModel -> deleteAudit($audit);
            }
        }
        // 修改产品表
        $productUpdate = $stockModel -> editAuditRollback($audit);
        // 更新审核表
        $auditUpdate = $this->where(['id' => ['EQ',$auditID]])->save(['is_del' => 1]) === false ? false : true;

        if ($productUpdate && $auditUpdate && $planUpdate) {
            return true;
        } else {
            if (!$productUpdate){
                $this->error = '库存更新失败';
            }
            if (!$auditUpdate){
                $this->error = '审核更新失败';
            }
            if (!$planUpdate){
                $this->error = '生产计划更新失败';
            }
            return false;
        }
    }

    /**f
     * 将申请的状态改为未申请并且更新内容
     * @param $id   int     申请id
     * @param $params    array  修改内容
     * @return bool
     */
    public function updateStockAudit($id, $params)
    {
        $data = [
            'audit_tips' => '',
            'audit_status' => self::AUDIT_PENDING
        ];
        $data = array_merge($data, $params);
        $res = $this->where(['id' => ['EQ', $id]]) -> save($data);
        return $res;
    }
    public function getStockOutData($filter)
    {
        $map['audit_status'] = ['IN', self::AUDIT_PENDING];
        return $stockOutData = M('stock_audit')
            ->where($filter)->field('from_unixtime(update_time) update_time,audit_status,product_name,audit_order_number,action_order_number,warehouse_name,auditor_name,proposer_name,num')
            ->select();
    }






    public function getStockData($field, $map, $start, $length, $order)
    {
        return $this->field($field)
            ->where($map)
            ->join('left join crm_staff as proposer on proposer = proposer.id')
            ->join('left join crm_staff as auditor on auditor = auditor.id')
            ->limit($start, $length)
            ->order($order)
            ->select();
    }

    public function getOneStockLog($map,$field,$order='id',$group = 'id')
    {
        return $this->where($map)->field($field)->order($order)->group($group)->find();
    }


    /**
     * 删除出入库审核并回滚相关数据
     * @param $id
     * @return bool
     */
    public function deleteAudit($id)
    {
        $map = ['id' => ['EQ', $id]];
        $audit = $this -> find($id);
        $this->startTrans();
        if ($audit['audit_status'] != '3'){
            $thisUpdate = $this->changeAuditStatusRollBack($id);
        }else{
            $thisUpdate = $this->where($map) -> save(['is_del' => 1]) === false ? false : true;
        }
        if ($thisUpdate){
            $this->commit();
        }else{
            $this->rollback();
        }
        return $thisUpdate;
    }


    public function getAutoStockLogData($stockInProduceBase,$stockInData, $planData)
    {

        $model = new MaxIdModel();
        $productData = M('material')->find($stockInData['product_id']);
        $time = time();
        $tmpAdd['product_id'] = $productData['product_id'];
        $tmpAdd['product_no'] = $productData['product_no'];
        $tmpAdd['product_name']   = $productData['product_name'];
        $tmpAdd['product_number'] = $productData['product_number'];
        $tmpAdd['type']           = self::IN_TYPE;
        $tmpAdd['audit_status']   = self::AUDIT_PASS;
        $tmpAdd['proposer']       = session('staffId');
        $tmpAdd['proposer_name']  = session('nickname');
        $tmpAdd['auditor']        = session('staffId');
        $tmpAdd['auditor_name']   = session('nickname');
        $tmpAdd['warehouse_name'] = $stockInData['repertory_name'];
        $tmpAdd['warehouse_number'] = $stockInData['rep_id'];
        $tmpAdd['create_time']  = $time;
        $tmpAdd['update_time']  = $time;
        $tmpAdd['cate_name']    = $stockInProduceBase['cate_name'];
        $tmpAdd['cate']         = $stockInProduceBase['cate'];
        $tmpAdd['batch'] = $stockInProduceBase['batch'];
        $tmpAdd['putin_production_line_name'] = $stockInProduceBase['production_line_name'];
        $tmpAdd['putin_production_line_id']   = $stockInProduceBase['production_line_id'];

        $tmpNum = $stockInData['num'];

        $stockAuditAddData = [];
        for ($i = 0; $i < count($planData); $i++) {
            if ($tmpNum > 0) {
                if ($planData[$i]['can_insert_num'] >= $tmpNum) {
                    $tmpAdd['action_order_number'] = $planData[$i]['production_order'];
                    $tmpAdd['num'] = $tmpNum;
                    $stockAuditAddData[] = $tmpAdd;
                    $tmpNum = 0;
                }
                if (0 < $planData[$i]['can_insert_num'] && $planData[$i]['can_insert_num'] < $tmpNum) {
                    $tmpAdd['action_order_number'] = $planData[$i]['production_order'];
                    $tmpAdd['num'] = $planData[$i]['can_insert_num'];
                    $stockAuditAddData[] = $tmpAdd;
                    $tmpNum = $tmpNum - $tmpAdd['num'];
                }
            }
        }

        foreach ($stockAuditAddData as &$auditAddDatum) {
            if (!$auditAddDatum['num']) {
                unset($auditAddDatum);
            }
            $maxId = $model->getMaxId('stock_audit');
            $auditAddDatum['id'] = $maxId;
            $auditAddDatum['audit_order_number'] = "RKJL-" . $maxId;
        }
        return $stockAuditAddData;

    }
    public function autoAddStockInLog($stockInPrimaryId, $stockInData)
    {
        $stockInModel  = new StockInProductionModel();
        $relationModel = new ProductionRelationModel();
        $productionPlanModel = new ProductionPlanModel();


        $stockInProduceBase = $stockInModel->find($stockInPrimaryId);
        $produceOrderId = M('production_task')->find($stockInProduceBase['source_id'])['order_pid'];

        $planIdArr = $relationModel->getPlanIdsWithOrderId($produceOrderId);

        if (ProductionRelationModel::$failStatus == $planIdArr['status']) {
            $this->error = "入库记录自动写入失败，联系管理";
            return false;
        }

        $planData = $productionPlanModel->getPlanDataWithPlanIds($planIdArr['data']);

        $stockAuditAddData = $this->getAutoStockLogData($stockInProduceBase, $stockInData, $planData);

        $auditAddRst = $this->addAll(array_values($stockAuditAddData));
        if ($auditAddRst === false) {
            $this->error = "入库操作失败，联系管理1";
            return false;
        }
        foreach ($planData as $val) {
            $audit = [
                'action_order_number' => $val['action_order_number'],
                'num'                 => $val['num'],
                'product_id'          => $val['product_id']
                ];
            $productionPlanStatusResetRst = $productionPlanModel->auditPass($audit);
            if (false === $productionPlanStatusResetRst) {
                $this->error = $productionPlanModel->getError();
                return false;
            }
        }
        $updPlanRst = $productionPlanModel->resetPlanProductionStatus($planIdArr['data']);
        if (false === $updPlanRst) {
            $this->error = $productionPlanModel->getError();
            return false;
        }
        return true;
    }
}