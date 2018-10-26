<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/17
 * Time: 上午11:56
 */

namespace Dwin\Model;


use Think\Model;

class StockInRecordModel extends Model
{
    /* 审核状态：1 待审 2 通过 3 不通过*/
    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_UNQUALIFIED = 1;     // 入库
    const TYPE_QUALIFIED = 2;       // 不合格

    const IS_DEL = 1; // 已被删除
    const NO_DEL = 0; // 有效

    const SUCCESS_STATUS = 200;
    const FAIL_STATUS    = 404;
    const FORBIDDEN_STATUS = 403;

    //源单表 1 2 3
    const SOURCE_PRODUCTION_TYPE = 2;// 生产入库
    const SOURCE_PURCHASE_TYPE   = 8;// 采购入库
    const SOURCE_OTHER_TYPE      = 5;// 其他入库

    protected $validate = [
        ['source_id',    'require', '入库单源单编号不能为空', 1],
        ['source_pid',   'require', '主键', 1],
        ['product_id',   'require', '入库编号非法', 1],
        ['num',          'require', '入库类别id不能为空', 1],
        ['cate_id',      'require', '生产班组id不能为空', 1],
        ['batch',        'require', 'batch非法', 1],
        ['repertory_id', 'require', '库房不能为空', 1],
    ];
    protected $rules = [
        ['create_time', 'time', 3, 'function'],
        ['update_time', 'time', 3, 'function'], // 对update_time字段在更新的时候写入当前时间戳
        ['status', '0']
    ];

    /**
     * addStockIn 添加入库记录（基本表信息）
     * Created by
     * User: ma xu
     * Time: 2018.07.28
     * @param $base
     * @return array|bool
     */
    public function getAddData($param)
    {
        $stockData = [];
        foreach ($param as $item) {
            $stockData[] = array(
                'source_id'    => $item['source_id'],
                'source_pid'   => $item['id'],
                'product_id'   => $item['product_id'],
                'num'          => $item['insert_num'],
                'cate_id'      => $item['cate_id'],
                'repertory_id' => $item['insert_rep_id'],
                'batch'        => $item['batch'],
            );
        }
        if (!count($stockData)) {
            $this->error = "数据有误";
            return false;
        }

        return $stockData;
    }

    public function validateBase($data)
    {
        $returnData = [];
        foreach ($data as $datum) {
            $tmp = $this->auto($this->rules)->validate($this->validate)->create($datum);
            if ($tmp === false) {
                return false;
            }
            $returnData[] = $tmp;
        }
        return $returnData;
    }


    public function getPurchaseStockInData($stockId, $materialData, $postData)
    {
        $flag = [];
        $stockData = [];
        foreach ($materialData as $materialDatum) {
            foreach ($postData as $postDatum) {
                if ($materialDatum['product_id'] == $postDatum['product_id']) {

                    $flag[] = $materialDatum['insert_num'] == ($postDatum['success_num'] + $postDatum['fail_num']) ? true : false;
                }
            }
        }

        if (in_array(false, $flag)) {
            $this->error = "检验数量有问题";
            return false;
        }
        $baseInfo = M('stock_in_purchase')->field("*")->find($stockId);
        foreach ($postData as $item) {
            $stockData[] = array(
                'source_id'    => $baseInfo['id'],
                'source_pid'   => $item['id'],
                'product_id'   => $item['product_id'],
                'num'          => $item['success_num'],
                'cate_id'      => $baseInfo['cate'],
                'repertory_id' => $item['default_rep_id'],
                'batch'        => $baseInfo['batch'],
            );
            $stockData[] = array(
                'source_id'    => $baseInfo['id'],
                'source_pid'   => $item['id'],
                'product_id'   => $item['product_id'],
                'num'          => $item['fail_num'],
                'cate_id'      => $baseInfo['cate'],
                'repertory_id' => $item['fail_rep_id'],
                'batch'        => $baseInfo['batch'],
            );
        }
        foreach ($stockData as $key =>  &$stockDatum) {
            if ($stockDatum['num'] == 0) {
                unset($stockData[$key]);
            }
        }
        if (!count($stockData)) {
            $this->error = "数据有误";
            return false;
        }
        return array_values($stockData);

    }

    public function addStockInRecordWithPurchaseData($stockId, $recordData)
    {
        $data = $this->validateBase($recordData);
        if ($data === false) {
            return false;
        }
        $this->startTrans();
        $addRst = $this->addAll($data);
        if ($addRst === false) {
            $this->rollback();
            $this->error = "添加数据出错";
            return false;
        }
        $stockInPurchaseModel = new StockInPurchaseModel();
        $upd = $stockInPurchaseModel->checkPurchaseStockIn($stockId);
        if ($upd === false) {
            $this->rollback();
            $this->error = "添加数据出错";
            return false;
        }
        $this->commit();
        return true;
    }

    public function getStockInRecordWithSourceId($stockInId)
    {
        $map['a.source_id'] = ['in', $stockInId];
        $map['a.is_del'] = ['eq', self::NO_DEL];
        $field = "
            a.id,
            a.num,
            a.status,
            b.product_id,
            b.product_no,
            b.product_name,
            b.product_number,
            c.rep_id,
            c.repertory_name
        ";
        return $this->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_material b ON a.product_id = b.product_id')
            ->join('LEFT JOIN crm_repertorylist c ON c.rep_id = a.repertory_id')
            ->where($map)
            ->select();
    }

    public function getStockInRecordWithTaskId($id)
    {
        $data = M('stock_in_production')->field('id')->where(['source_id' => ['eq', $id]])->select();
        $stockInIds = getPrjIds($data,'id');
        if (empty($stockInIds)) {
            return [];
        }
        $map['a.source_id'] = ['in', $stockInIds];
        $map['a.is_del'] = ['eq', self::NO_DEL];
        $field = "
            a.id,
            a.num,
            a.status,
            b.product_id,
            b.product_no,
            b.product_name,
            b.product_number,
            c.rep_id,
            c.repertory_name,
            p.stock_in_id,
            from_unixtime(p.update_time) update_time,
            production_group_name
        ";

        return $this->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_material b ON a.product_id = b.product_id')
            ->join('LEFT JOIN crm_repertorylist c ON c.rep_id = a.repertory_id')
            ->join('LEFT JOIN crm_stock_in_production p ON p.id = a.source_id and p.is_del = 0')
            ->where($map)
            ->select();
    }

    /**
     * @param int $status 审核状态 1时为通过
     * @param array $data 要审核的数据
    */
    public function checkStockInRecord($status, $data)
    {
        $this->startTrans();
        foreach($data as $datum) {
            $rst = $this->checkOneStockInRecord($status, $datum);
            if ($rst === false) {
                $this->rollback();
                $this->error = $this->getError();
                return false;
            }

        }
        $this->commit();
        return true;
    }

    public function checkOneStockInRecord($status, $params)
    {
        $stockAuditModel = new StockAuditModel();
        $config = [
            'status'        => $status,
            'source_id'     => $params['id'], // record的源单主键
            'stock_in_type' => (int)$params['cate']
        ];

        switch((int)$config['stock_in_type']) {
            case self::SOURCE_PRODUCTION_TYPE :
                $stockInModel = new StockInProductionModel();
                break;
            case self::SOURCE_PURCHASE_TYPE :
                $stockInModel = new StockInPurchaseModel();
                break;
            case self::SOURCE_OTHER_TYPE :
                $stockInModel = new StockInOtherModel();
                break;
            default:
                $this->error = "stock_in_type错误参数，联系管理";
                return false;
                break;
        }
        $flag = $stockInModel->checkAuditStatus($config);
        if ($flag === false) {
            $this->error = $stockInModel->getError();
            return false;
        }

        $resetRst = $stockInModel->resetStatus($config);

        if ($resetRst === false) {
            $this->error = $stockInModel->getError();
            return false;
        }
        if ($config['status'] == 1) {
            $recordResetRst = $this->resetStatus($config['source_id']);

            if ($recordResetRst === false) {
                return false;
            }
            $data = $this->getStockInRecordWithSourceId($config['source_id']);


            if ($config['stock_in_type'] === self::SOURCE_OTHER_TYPE) {
                $info = $stockInModel->find($config['source_id']);
                if (StockInOtherApplyModel::STOCK_SOURCE_PRODUCTION == $info['type_id']) {
                    foreach ($data as $item) {
                        $map['product_id'] = ['EQ', $item['product_id']];
                        $materialModel = new MaterialModel();
                        $materialInfo =  $materialModel->where($map)->find();
                        $materialData['rework_number'] = (int)$materialInfo['rework_number'] - $item['num'];
                        if ((int)$materialData['rework_number'] < 0) {
                            $this->error = "返工入库数量更新后小于0，数据有误，请联系管理";
                            return false;
                        }
                        $materialRst = $materialModel->where($map)->setField($materialData);
                        if (false === $materialRst) {
                            $this->error = $materialModel->getError();
                            return false;
                        }
                    }
                }
            }
            $stockModel = new StockModel();
            foreach($data as $datum) {
                $filter['product_id'] = ['EQ', $datum['product_id']];
                $filter['warehouse_number'] = ['EQ', $datum['rep_id']];
                $outData = $stockModel->where($filter)->find();

                if (empty($outData)) {
                    $dataAdd['product_id'] = $datum['product_id'];
                    $dataAdd['warehouse_number'] = $datum['rep_id'];
                    $dataAdd['warehouse_name']   = $datum['repertory_name'];
                    $dataAdd['update_time'] = time();
                    $rst = $stockModel->add($dataAdd);
                    if ($rst === false) {
                        $this->error = "错误4";
                        return false;
                    }
                }
                $updRst = $stockModel->updateWithFlag('checkStockIn', $filter, $datum['num']);

                if ($updRst === false) {
                    $this->error = "入库操作失败，联系管理";
                    return false;
                }
            }


            if (self::SOURCE_PRODUCTION_TYPE == (int)$config['stock_in_type']) {
                // 写入stock_audit 入库记录（仅写入生产入库记录）
                $autoLotRst = $stockAuditModel->autoAddStockInLog($config['source_id'], $data[0]);
                if ($autoLotRst === false) {
                    $this->error = $stockAuditModel->getError();
                    return false;
                }
                // 同步crm_stock_out_production_line_statistics 中数据
                $lineData = $stockInModel->find($config['source_id']);
                if (empty($lineData)){
                    $this->rollback();
                    $this->error = "源单数据不存在";
                    return false;
                }
                $statisticsMap['source_id'] = ['eq', $config['source_id']];
                $statisticsMap['is_del'] = ['eq', self::NO_DEL];
                $statisticsMap['status'] = ['eq', self::TYPE_UNQUALIFIED];
                $stockInNum = $this->where($statisticsMap)->field("*")->select();
                $num = array_sum(array_column($stockInNum,'num'));
                $lineModel = new StockOutProductionLineStatisticsModel();
                list($code, $msg) = $lineModel->syncStatistics($lineData['production_line_id'], $num);
                if($code !== 200){
                    $this->rollback();
                    $this->error = $msg;
                    return false;
                }

            }
        }
        return true;
    }

    /**
     * 修改入库记录的状态，（审核时调用。入库记录状态为1的为审核通过的，不能再入库一次）
    */
    public function resetStatus($sourceId)
    {
        $filter['source_id'] = ['eq', $sourceId];
        $filter['is_del']    = ['eq', self::NO_DEL];
        $resetData['status'] = 1;
        $resetData['update_time'] = time();
        $rst = $this->where($filter)->setField($resetData);
        if ($rst === false) {
            $this->error = "变更状态失败";
            return false;
        }
        return true;
    }

    public function deleteTrans($sourceId)
    {
        $filter['source_id'] = ['eq', $sourceId];
        $resetData['is_del'] = self::IS_DEL;
        $resetData['update_time'] = time();
        $rst = $this->where($filter)->setField($resetData);
        if ($rst === false) {
            $this->error = "记录删除失败";
            return false;
        }
        return true;
    }

    public function getIndexDataTable($map,$sqlCondition)
    {
        $map['a.is_del'] = ['EQ', self::NO_DEL];
        $count = $this->alias('a')->where($map)->count();
        $field = "
            a.*,
            (case 
                when a.cate_id = 3 then b.stock_in_id
                when a.cate_id = 5 then d.stock_in_id
                else c.stock_in_id end) sid,
            e.cate_name
            ";
        $data = $this->alias('a')
            ->field($field)
            ->where($map)
            ->join('LEFT JOIN crm_stock_in_production b ON b.id = a.source_id')
            ->join('LEFT JOIN crm_stock_in_purchase c ON b.id = a.source_id')
            ->join('LEFT JOIN crm_stock_in_other d ON b.id = a.source_id')
            ->join('LEFT JOIN crm_stock_io_cate e ON e.id = a.cate_id')
            ->order($sqlCondition['order'])
            ->limit($sqlCondition['start'], $sqlCondition['length'])
            ->select();
        $filterCount = $this->alias('a')->where($map)->count();
        return [$data, $count, $filterCount];
    }


    /**
     * 根据入库单id，返回当前已入库数量
     * @param $stockId  入库单id
     * @param $repertoryId  库房id
     * @return array
     */
    public function getNumByStockId($stockId, $repertoryId = ''){
        $map['is_del'] = ['eq', self::NO_DEL];
        $map['source_id'] = ['eq', $stockId];
        if(!empty($repertoryId)){
            $map['repertory_id'] = ['eq', $repertoryId];
        }
        $map['is_del'] = ['eq', self::NO_DEL];
        $data = $this->field("sum(num) as total_num")
            ->group("source_id")
            ->where($map)
            ->find();

        return $data;
    }

    /**
     * 根据入库物料单id，返回当前已入库数量
     * @param $materialId  入库单物料id
     * @param int $repertoryId 库房id
     * @return array
     */
    public function getNumByMaterialId($materialId, $repertoryId = 0){
        $map['is_del'] = ['eq', self::NO_DEL];
        $map['source_pid'] = ['eq', $materialId];
        if(!empty($repertoryId)){
            $map['repertory_id'] = ['eq', $repertoryId];
        }
        $map['is_del'] = ['eq', self::NO_DEL];
        $data = $this->field("*")
            ->where($map)
            ->find();
        return $data;
    }

    /**
     * 入库单下推
     * create by chendd
     * @param $stockId
     * @param $cateId
     * @param $batch
     * @param $materialData
     * @return array
     */
    public function stockInToRecord($stockId, $cateId, $batch, $materialData){
        $baseData = [];
        $baseData['source_id'] = $stockId;
        $baseData['cate_id'] = $cateId;
        $baseData['batch'] = $batch;
        $baseData['create_time'] = time();
        $baseData['update_time'] = time();
        $baseData['status'] = self::TYPE_NOT_AUDIT;
        $baseData['is_del'] = self::NO_DEL;

        $data = [];
        foreach ($materialData as $key => $item){
            $repData = [];
            $unqualifiedData = [];

            if($item['num'] != ($item['rep_num'] + $item['unqualified_rep_num'])){
                return dataReturn("合格数量和不合格数量相加与总数不等",400);
                break;
            }

            if(!empty($item['rep_num'])){
                $repData['source_pid'] = $item['id'];
                $repData['product_id'] = $item['product_id'];
                $repData['repertory_id'] = $item['rep_pid'];
                $repData['num'] = $item['rep_num'];

                $repData = array_merge($repData, $baseData);
                $data[] = $repData;
            }

            if(!empty($item['unqualified_rep_num'])){
                $unqualifiedData['source_pid'] = $item['id'];
                $unqualifiedData['product_id'] = $item['product_id'];
                $unqualifiedData['repertory_id'] = $item['unqualified_rep_pid'];
                $unqualifiedData['num'] = $item['unqualified_rep_num'];

                $unqualifiedData = array_merge($unqualifiedData, $baseData);
                $data[] = $unqualifiedData;
            }
        }

        $result = self::saveStockToRecord($stockId, $data);

        return $result;
    }

    /**
     * 保存入库记录  还需要将入库单自动变成已审核
     * @param $stockId 入库单主键
     * @param $data  二维数组，多条数据
     * @return array
     */
    public function saveStockToRecord($stockId, $data){
        $this->startTrans();
        $res = $this->addAll($data);
        if($res === false){
            $this->rollback();
            return dataReturn($this->getError(), 400);
        }

        $stockModel = new StockInModel();
        $res = $stockModel->where(['id' => $stockId])->setField(['audit_status' => $data['status'], 'auditor_name' => session('nickname'), 'auditor' => session('staffId'), 'update_time' => time()]);
        if($res === false){
            $this->rollback();
            return dataReturn($stockModel->getError(), 400);
        }
        $this->commit();
        return dataReturn("入库单下推成功", 200);
    }


    /**
     * 根据入库单id查当前入库单物料数据
     * @param $id
     * @return mixed
     */
    public function getDataByStockInId($id){
        $map['r.source_id'] = ['eq', $id];
        $map['r.is_del'] = ['eq', self::NO_DEL];
        $data =  $this->alias("r")
            ->field("r.*, q.repertory_name, m.product_name, m.product_number, m.product_no")
            ->join("LEFT JOIN crm_material m on m.product_id = r.product_id")
            ->join("left join crm_repertorylist q on q.rep_id = r.repertory_id")
            ->where($map)
            ->select();
        return $data;
    }
}