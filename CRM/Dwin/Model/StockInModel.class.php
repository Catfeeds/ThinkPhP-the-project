<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/17
 * Time: 上午11:56
 */

namespace Dwin\Model;


use Think\Model;

class StockInModel extends Model
{
    /* 审核状态：0-未审核 1-审核不合格 2 质控审核完毕 3 库房审核完毕*/
    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_UNQUALIFIED = 1;     // 审核不合格
    const TYPE_QUALIFIED = 2;     // 质控审核完毕
    const TYPE_STOCK_QUALIFIED = 3;       // 库房审核完毕

    const IS_DEL = 1; // 已被删除
    const NO_DEL = 0; // 有效

    const SUCCESS_STATUS = 200;
    const FAIL_STATUS    = 404;
    const FORBIDDEN_STATUS = 403;

    const TYPE_PURCHASE = 8;
    const TYPE_PRODUCTION = 2;
    const TYPE_OTHER = 5;

    /*入库类别*/
    const OTHER_TYPE = 2;

    protected $validate = [
        ['source_id',           'require', '入库单源单编号不能为空', 1],
        ['id',                  'require', '主键', 1],
        ['stock_in_id',         'require', '入库编号非法', 1, 'unique'],
        ['cate',                'require', '入库类别id不能为空', 1],
        ['production_group_id', 'require', '生产班组id不能为空', ],
        ['production_line_id',  'require', '生产线id不能为空', 1],
        ['auditor',             'require', '审核人不能为空',1],
        ['create_id',           'require', '创建人不能为空', 1]
    ];
    protected $validateOther = [
        ['id',                  'require', '主键', 1],
        ['stock_in_id',         'require', '入库编号非法', 1, 'unique'],
        ['cate',                'require', '入库类别id不能为空', 1],
        ['keep_id',             'require', '验收人未选择',1],
        ['check_id',             'require', '保管人未选择',1],
        ['type_id',             'require', '入库类型必选',1],
        ['auditor',             'require', '审核人不能为空',1],
        ['create_id',           'require', '创建人不能为空', 1]
    ];
    protected $rules = [
        ['create_name','getCreateName',3,'callback'],
        ['create_id','getCreateId',3,'callback'],
        ['create_time','time',3,'function'],
        ['audit_status', '2'],
        ['update_time','time',3,'function'], // 对update_time字段在更新的时候写入当前时间戳
    ];

    public function getCreateName()
    {
        return session('nickname');
    }
    public function getCreateId()
    {
        return session('staffId');
    }
    /**
     * addStockIn 添加入库单据（基本表信息）
     * Created by
     * User: ma xu
     * Time: 2018.07.09
     * @param $postsData
     * @return array
     */
    public function getAddData($postsData)
    {
        $map['is_del'] = ['eq', 0];
        $map['source_id'] = ['eq', $postsData['source_id']];
        $time = time();
        $stockData = array(
            'source_id'            => empty($postsData['source_id']) ? null : $postsData['source_id'],
            'id'                   => $postsData['id'],
            'stock_in_id'          => $postsData['stock_in_id'],
            'cate'                 => getStringId($postsData['cateArr']),
            'cate_name'            => getStringChar($postsData['cateArr']),
            'tips'                 => nl2br($postsData['tips']),
            'auditor'              => getStringId($postsData['auditorArr']),
            'auditor_name'         => getStringChar($postsData['auditorArr']),
            'keep_id'              => getStringId($postsData['keepArr']),
            'keep_name'            => getStringChar($postsData['keepArr']),
            'check_id'             => getStringId($postsData['checkArr']),
            'check_name'           => getStringChar($postsData['checkArr']),
            'create_name'          => $this->getCreateName(),
            'create_id'            => $this->getCreateId(),
            'create_time'          => $time,
            'update_time'          => $time
        );
        if (StockInRecordModel::SOURCE_PRODUCTION_TYPE == $stockData['cate']) {
            $stockData['audit_status'] = 2;
            $stockData['production_line_name'] = getStringChar($postsData['lineArr']);
            $stockData['production_group_name']= getStringChar($postsData['groupArr']);
            $stockData['production_group_id']  = getStringChar($postsData['groupArr']);
            $stockData['production_line_id']   = getStringId($postsData['lineArr']);
            $stockData['batch']   = M('stock_in_production')->where($map)->count() + 1;
        }
        if (StockInRecordModel::SOURCE_OTHER_TYPE == $stockData['cate']) {
            $stockData['audit_status'] = 2;
            $stockData['type_id']   = getStringId($postsData['typeArr']);
            $stockData['type_name'] = getStringChar($postsData['typeArr']);
            $stockData['dept_id']   = $postsData['dept_id'];
            $stockData['batch']   = M('stock_in_other')->where($map)->count() + 1;
        }
        if (StockInRecordModel::SOURCE_PURCHASE_TYPE == $stockData['cate']) {
            $stockData['audit_status'] = 0;
            $stockData['batch'] = M('stock_in_purchase')->where($map)->count() + 1;
            $stockData['pay_time'] = (int)$postsData['pay_time'];
            $stockData['other_bill'] = $postsData['other_bill'];
        }
        return $stockData;
    }


    /**
     * 联合查询获取审核完毕的入库单据列表
    */
    public function getUnionDataTableData($map,$config)
    {
        $map['a.is_del'] = ['EQ', self::NO_DEL];
        $map['a.audit_status'] = ['EQ', self::TYPE_STOCK_QUALIFIED];

        $alias = 'a';
        $field = "
            a.id,
            a.stock_in_id,
            a.cate,
            a.print_time,
            a.cate_name,
            a.batch,
            a.create_id,
            a.create_name,
            from_unixtime(a.create_time) c_time,
            from_unixtime(a.update_time) update_time,
            a.tips,
            a.audit_status,
            a.keep_id,
            a.keep_name,
            a.check_id,
            a.check_name";
        $productionModel = M();
        $countA = $productionModel->table('crm_stock_in_production a')->where($map)->count();
        $countB = $productionModel->table('crm_stock_in_purchase a')->where($map)->count();
        $countC = $productionModel->table('crm_stock_in_other a')->where($map)->count();
        $count = $countA + $countB + $countC;

        if (trim($config['search'])) {
            $map['a.stock_in_id|a.create_name|a.auditor_name|a.cate_name'] = ['LIKE', "%" . trim($config['search']) . "%"];
        }
        $unionPurchase = "SELECT {$field},ord.purchase_order_id 
                              FROM crm_stock_in_purchase a 
                              LEFT JOIN crm_purchase_order ord ON ord.id = a.source_id 
                              LEFT JOIN crm_stock_in_record record ON record.source_id = a.id 
                              WHERE a.audit_status = 3 AND a.is_del = 0";
        $unionOther = "SELECT {$field},ifnull(a.source_id,'无') 
                          FROM crm_stock_in_other a 
                          LEFT JOIN crm_stock_in_record record ON record.source_id = a.id 
                          WHERE a.audit_status = 3 and a.is_del = 0 
                          ORDER BY {$config['order']} 
                          LIMIT {$config['start']},{$config['length']}";
        $data = $productionModel
            ->alias($alias)
            ->field($field . ",task.task_id s_id")
            ->table('crm_stock_in_production')
            ->union($unionPurchase,true)
            ->union($unionOther,true)
            ->where($map)
            ->join('LEFT JOIN crm_production_task task ON task.id = a.source_id')
            ->join('LEFT JOIN crm_stock_in_record record ON record.source_id = a.id')
            ->select();

        $filterCountA = $productionModel->table('crm_stock_in_production')->alias($alias)->where($map)->count();
        $filterCountB = $productionModel->table('crm_stock_in_purchase')->alias($alias)->where($map)->count();
        $filterCountC = $productionModel->table('crm_stock_in_other')->alias($alias)->where($map)->count();
        $filterCount = $filterCountA + $filterCountB + $filterCountC;
        return [$data, $count, $filterCount];
    }


    public function checkAuditStatus($config)
    {
        $map['id'] = ['EQ', $config['source_id']];
        $data = $this->where($map)->find();
        switch ($data['audit_status']) {
            case self::TYPE_STOCK_QUALIFIED :
                $this->error = "已审核单据不能重复审核(" . $data['stock_in_id'] . ")";
                $flag = false;
                break;
            case self::TYPE_QUALIFIED :
                $flag = true;
                break;
            case self::TYPE_UNQUALIFIED :
                $this->error = "不合格单据不能审核，请制单人修改后再审核(". $data['stock_in_id'] . ")";
                $flag = false;
                break;
            case self::TYPE_NOT_AUDIT :
                $this->error = "单据还未进行质检审核下推，请质检后再审核入库(". $data['stock_in_id'] . ")";
                $flag = false;
                break;
            default :
                $this->error = "未知错误(". $data['stock_in_id'] . ")";
                $flag = false;
                break;
        }
        return $flag;
    }
    /**
     * 修改stock_in_... 三个表的审核状态
    */
    public function resetStatus($config)
    {
        $map['id'] = ['eq', $config['source_id']];
        $updateStatus['audit_status'] = $config['status'] == 1 ? self::TYPE_STOCK_QUALIFIED : self::TYPE_UNQUALIFIED;
        $updateStatus['update_time']  = time();
        $rst = $this->where($map)->setField($updateStatus);
        if ($rst === false) {
            $this->error = "变更状态出错";
            return $rst;
        }
        return $rst;
    }

    /**
     * 删除stock_in... 三个表的单据记录
     *
    */
    public function delStockInRecord($id)
    {
        $map['id'] = ['EQ', $id];
        $data['is_del'] = self::IS_DEL;
        $data['update_time']  = time();
        $stockInRecordModel = new StockInRecordModel();


        $baseData = $this->where($map)->find();
        $materialData = $stockInRecordModel->getStockInRecordWithSourceId($id);
        if (!count($baseData)) {
            $this->error = "出错，单据编号有误，没查询到数据";
            return false;
        }
        if (self::TYPE_STOCK_QUALIFIED == $baseData['audit_status']) {
            $this->error = "已经审核入库的单据，禁止会影响库存，禁止删除";
            return false;
        }
        if (self::IS_DEL == $baseData['is_del']) {
            $this->error = "已删除";
            return false;
        }

        if (0 == count($materialData)) {
            $this->error = "出错，单据编号对应没查到入库物料";
            return false;
        }
        $this->startTrans();

        $rst = $this->where($map)->setField($data);
        if ($rst === false) {
            $this->rollback();
            $this->error = "变更状态出错";
            return $rst;
        }

        $recordDelRst = $stockInRecordModel->deleteTrans($id);
        if ($recordDelRst === false) {
            $this->rollback();
            $this->error = $stockInRecordModel->getError();
            return false;
        }
        $stockMaterial = new StockMaterialModel();
        $materialRst = $stockMaterial->delTrans($id, StockMaterialModel::TYPE_STOCK_IN);
        if ($materialRst === false) {
            $this->rollback();
            $this->error = $stockMaterial->getError();
            return false;
        }

        if (StockInRecordModel::SOURCE_PRODUCTION_TYPE == $baseData['cate']) {
            $taskModel = new ProductionTaskModel();
            $rollbackRst = $taskModel->rollbackTaskNumber($baseData, $materialData);
            if (false === $rollbackRst) {
                $this->rollback();
                $this->error = $taskModel->getError();
                return false;
            }

        }

        if (StockInRecordModel::SOURCE_OTHER_TYPE == $baseData['cate']) {
            if (!empty($baseData['source_id']) && StockInOtherApplyModel::STOCK_SOURCE_AFTER_SALE == $baseData['type_id']) {
                $appModel = new StockInOtherApplyModel();
                $appDelRst = $appModel->delTrans($baseData['source_id']);
                if ($appDelRst === false) {
                    $this->rollback();
                    $this->error = $appModel->getError();
                    return false;
                }
            }
        }

        if (StockInRecordModel::SOURCE_PURCHASE_TYPE == $baseData['cate']) {
            $purchaseOrderModel = new PurchaseOrderModel();
            $resetRst = $purchaseOrderModel->resetStockStatus($baseData['source_id']);
            if ($resetRst === false) {
                $this->rollback();
                $this->error = $purchaseOrderModel->getError();
                return false;
            }
        }
        $this->commit();
        return $rst;
    }





    /**
     * create by chendd 通过crm_stock_in 中的id查找
     * @param $stockId
     * @return mixed
     */
    public function findByStockId($stockId){
        $map['is_del'] = ['eq', self::NO_DEL];
        $map['id'] = ['eq', $stockId];
        $data = $this->field("*")->where($map)->find();
        return $data;
    }

    /**
     * create by chendd 修改入库单
     * @param $editMaterialData
     * @param $stockData
     * @return array
     */
    public function editStockByPurchase($editMaterialData,$stockData){

        $this->startTrans();
        $materialModel = new StockMaterialModel();
        $orderProductModel = new PurchaseOrderProductModel();

        $statusStr = '';
        foreach ($editMaterialData as $key => $item){
            list($code, $materiaDataOne, $msg) = $materialModel->editMaterialByPurchase($item);
            if($code == 0){
                list($message, $status) = $orderProductModel->updateStockInNum($stockData['source_id'], $materiaDataOne['product_no'], $materiaDataOne['num'], $materiaDataOne['id']);
                if ($status != 0){
                    $this->rollback();
                    return dataReturn($message, 403);
                }

                $statusStr = self::SUCCESS_STATUS;
                $saveRst = $materialModel->save($materiaDataOne);
                if ($saveRst === false) {
                    $this->rollback();
                    return dataReturn($materialModel->getError(), self::FAIL_STATUS);
                    break;
                }
            }

            if($code == -2){
                $this->rollback();
                return dataReturn($msg, self::FAIL_STATUS);
                break;
            }
        }
        // 两边都没有修改才是没有修改
        list($stockCode, $stockMsg) = self::editStockBaseMsg($stockData);

        if($statusStr == "" && $stockCode == -1){
            $this->rollback();
            return dataReturn($msg, self::FAIL_STATUS);
        }
        if($stockCode == -2){
            $this->rollback();
            return dataReturn($stockMsg, self::FAIL_STATUS);
        }

        $this->where(['id' => $stockData['id']])->setField(["audit_status" => self::TYPE_NOT_AUDIT]);
        $this->commit();
        return dataReturn("入库单修改成功", self::SUCCESS_STATUS);
    }

    /**
     * 修改入库单基本信息
     * @param $params
     * @return array
     */
    public function editStockBaseMsg($params){
        if (empty($params)) {
            return [-1, "无修改数据提交"];
        }

        $oldData = $this->field("*")->find($params['id']);
        $data = $this->getNewField($params);

        if($oldData['audit_status'] == self::TYPE_QUALIFIED){
            return [-2, '当前出库单已审核通过，不可修改'];
        }
        $editData = $this->compareData($oldData, $data);
        if ($editData === false) {
            return [-1, "无数据修改"];
        } else {
            $createData = $this->create($editData);
            if(!$createData){
                return[-2, $this->getError()];
            }
            $saveData = $this->save($editData);
            if (!$saveData){
                return[-2, $this->getError()];
            }
            return [0, "数据修改成功"];
        }
    }

    /**
     * 修改的时候对比前后数据是否一致。筛选是否进行过修改
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

        $editedData['audit_status'] = self::TYPE_NOT_AUDIT;
        $editedData['id']    = $oldData['id'];
        $editedData['update_time']  = time();
        return $editedData;
    }

    /**
     * 删除入库单
     * @param $stockId 入库单id
     * @return array
     */
    public function deleteStock($stockId){
        $stockRecordModel = new StockInRecordModel();
        $recordData = $stockRecordModel->getNumByStockId($stockId);
        if(!empty($recordData['total_num'])){
            return dataReturn("已有入库物料，不可删除此物料", 400);
        }
        $data = $this->find($stockId);

        if($data['audit_status'] != self::TYPE_UNQUALIFIED){
            return dataReturn("当前订单不是不合格订单，不可删除", 400);
        }

        $this->startTrans();

        $stockRes = $this->where(['id' => $stockId])->setField(['is_del' => self::IS_DEL]);
        if(!$stockRes){
            $this->rollback();
            return dataReturn("删除失败",400);
        }
        $materialModel = new StockMaterialModel();
        $materialData = $materialModel->where(['source_id' => $stockId, 'is_del' => self::NO_DEL])->select();



        // 修改订单物料表中剩余物料
        $orderProductModel = new PurchaseOrderProductModel();
        foreach ($materialData as $key => $item){
            list($message, $status) = $orderProductModel->updateStockInNum($data['source_id'], $item['product_no'], 0, $item['id']);
            if ($status != 0){
                $this->rollback();
                return dataReturn($message, 403);
            }
        }

        $materialRes = $materialModel->where(['source_id' => $stockId, 'is_del' => self::NO_DEL])->setField(["is_del" => self::IS_DEL]);
        if (!$materialRes){
            $this->rollback();
            return dataReturn("删除失败",400);
        }
        $this->commit();
        return dataReturn("删除成功",200);
    }

    /**
     * 获取入库单列表页信息
     */
    public function getList($condition, $start, $length, $order){
        $map['crm_stock_in.is_del'] = ['eq', self::NO_DEL];
        if(strlen($condition) != 0){
            $where['crm_stock_in.stock_in_id'] = ['like', "%" . $condition . "%"];
            $where['crm_stock_in.create_name']=['like', "%" . $condition . "%"];
            $where['_logic'] = 'OR';
            $map['_complex'] = $where;
        }

        $data =  $this->field("*")
            ->limit($start, $length)
            ->where($map)
            ->order($order)
            ->select();
        /** 后台传输局到前台
        @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
        $count = $this->where($map)->count();

        return [$data,$count];
    }
}