<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/7/31
 * Time: 上午10:40
 */

namespace Dwin\Model;
use Think\Model;
class StockOutRecordModel extends Model{
    /* 审核状态：0-未审核 1-审核不合格 2-已入库*/
    const TYPE_NOT_AUDIT   = 0;     // 未审核
    const TYPE_UNQUALIFIED = 1;     // 审核不合格
    const TYPE_QUALIFIED   = 2;     // 已入库

    const TYPE_STOCK_OUT_OTHER = 1;   // 其他出库类型
    const TYPE_STOCK_OUT_ORDER_FORM = 2;   // 销售出库类型
    const TYPE_STOCK_OUT_PRODUCE_MATERIAL = 3; // 生产领料类型
    const TYPE_STOCK_OUT_PRODUCTION = 4; // 生产领料类型


    public static $stockOutType = [
        self::TYPE_STOCK_OUT_OTHER   =>  "其他出库类型",
        self::TYPE_STOCK_OUT_ORDER_FORM  =>  "销售出库类型",
//        self::TYPE_STOCK_OUT_PRODUCE_MATERIAL => "生产领料类型",
        self::TYPE_STOCK_OUT_PRODUCTION => "生产领料类型"
    ];

    public static $auditMap = [
        self::TYPE_NOT_AUDIT => "未审核",
        self::TYPE_UNQUALIFIED => "审核不合格",
        self::TYPE_QUALIFIED => "已出库",
    ];

    const IS_DEL = 1; // 已被删除
    const NO_DEL = 0; // 有效

    /**
     * 出库单（其他类别）下推   当前没有地方调用此函数
     * create by chendd
     * @param $stockId
     * @param $cateId
     * @param $batch
     * @param $materialData
     * @return array
     */
    public function stockOutToRecord($stockId, $cateId, $batch, $materialData){
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
     * 保存出库记录  还需要将出库单自动变成已审核   当前没有地方调用此函数
     * @param $stockId 出库单主键
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
     * 根据入库单id，返回当前已入库数量   当前没有地方调用此函数
     * @param $stockId  入库单id
     * @param $cateId  出库单类型
     * @param $repertoryId  库房id
     * @return array
     */
    public function getNumByStockId($stockId, $cateId, $repertoryId = ''){
        $map['is_del'] = ['eq', self::NO_DEL];
        $map['source_id'] = ['eq', $stockId];
        $map['cate_id'] = ['eq', $cateId];
        if(!empty($repertoryId)){
            $map['repertory_id'] = ['eq', $repertoryId];
        }
        $map['is_del'] = ['eq', self::NO_DEL];
        $data = $this->field("ifunll(sum(num),0) as total_num")
            ->group("source_pid")
            ->where($map)
            ->select();

        return $data;
    }

    /**
     * 根据入库物料单id，返回当前已出库数量
     * @param $materialId  出库单物料id
     * @param $cateId  出库单类型
     * @param int $repertoryId 库房id
     * @return array
     */
    public function getNumByMaterialId($materialId, $cateId, $repertoryId = 0){
        $map['is_del'] = ['eq', self::NO_DEL];
        $map['source_pid'] = ['eq', $materialId];
        $map['cate_id'] = ['eq', $cateId];
        $map['status'] = ['eq', self::TYPE_QUALIFIED];
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
     * 其他类型出库申请单下推其他类型出库单自动生成出库记录和修改库房记录
     * @param $stockId
     * @param $applyMaterialData
     * @param $cateId
     * @return array
     */
    public function autoSaveRecordForStockOutOther($stockId, $applyMaterialData, $cateId){
        $materialModel = new StockMaterialModel();
        $stockModel = new StockModel();
        $map['m.type'] = ['eq', StockMaterialModel::TYPE_STOCK_OUT];
        $stockOutMaterialData = $materialModel->selectByStockId($stockId);
        $recordData = [];
        foreach ($stockOutMaterialData as $key => $value){
            $recordOneData = [];
            $recordOneData['source_id'] = $stockId;
            $recordOneData['source_pid'] = $value['id'];
            $recordOneData['cate_id'] = $cateId;
            $recordOneData['cate_name'] = self::$stockOutType[$cateId];
            $recordOneData['product_id'] = $value['product_id'];
            $recordOneData['product_no'] = $value['product_no'];
            $recordOneData['num'] = $value['num'];
            $recordOneData['repertory_id'] = $value['rep_pid'];
            $recordOneData['create_time'] = time();
            $recordOneData['update_time'] = time();
            $recordOneData['status'] = StockOutRecordModel::TYPE_NOT_AUDIT;
            $recordOneData['is_del'] = StockOutRecordModel::NO_DEL;
            $recordData[] = $recordOneData;

            // crm_stock处理待出库数量
            $stockApplyData = $stockModel->where(['warehouse_number' => $applyMaterialData[$value['product_id']]['default_rep_id'], "product_id" => $value['product_id']])->find();
            if (empty($stockApplyData)){
                return [-1, $value['product_no'] . "物料在当前库房无记录"];
            }

            if($applyMaterialData[$value['product_id']]['default_rep_id'] != $value['rep_pid']){
                $stockNumber = $stockApplyData['stockNum'] + $value['num'];
                $auditNumber = $stockApplyData['o_audit'] - $value['num'];
                $res = $stockModel->where(['warehouse_number' => $applyMaterialData[$value['product_id']]['default_rep_id'], "product_id" => $value['product_id']])->setField(['stock_number' => $stockNumber,'o_audit' => $auditNumber]);
                if ($res === false){
                    return [-1, "修改库房记录失败"];
                }

                $stockData = $stockModel->where(['warehouse_number' => $value['rep_pid'], "product_id" => $value['product_id']])->find();
                $stockNum = $stockData['stockNum'] + $value['num'];
                $auditNum = $stockData['o_audit'] - $value['num'];
                $res = $stockModel->where(['warehouse_number' => $applyMaterialData[$value['product_id']]['default_rep_id'], "product_id" => $value['product_id']])->setField(['stock_number' => $stockNum,'o_audit' => $auditNum]);
                if ($res === false){
                    return [-1, "修改库房记录失败"];
                }
            }else {
                $outProcess = $stockApplyData['out_processing'] + $value['num'];
                $auditNumber = $stockApplyData['o_audit'] - $value['num'];
                $res = $stockModel->where(['warehouse_number' => $applyMaterialData[$value['product_id']]['default_rep_id'], "product_id" => $value['product_id']])->setField(['out_processing' => $outProcess,'o_audit' => $auditNumber]);
                if ($res === false){
                    return [-1, "修改库房记录失败"];
                }
            }
        }

        $result = $this->addAll($recordData);
        if($result === false){
            return [-1, $this->getError()];
        }

        return [0, "新增出库记录成功"];
    }

    /**
     * 生产(领料)出库单
     * 通过出库单id直接新增出库记录， 并修改crm_stock 库存信息
     * @param $stockId  int 出库单id
     * @param $cateId   int 出库类别id
     * @return array
     */
    public function autoSaveRecordForProduce($stockId, $cateId){
        $materialModel = new StockMaterialModel();
        $stockModel = new StockModel();
        $map['m.type'] = ['eq', StockMaterialModel::TYPE_STOCK_OUT];
        $stockOutMaterialData = $materialModel->selectByStockId($stockId);
        $recordData = [];
        foreach ($stockOutMaterialData as $key => $value){
            $recordOneData = [];
            $recordOneData['source_id'] = $stockId;
            $recordOneData['source_pid'] = $value['id'];
            $recordOneData['cate_id'] = $cateId;
            $recordOneData['cate_name'] = self::$stockOutType[$cateId];
            $recordOneData['product_id'] = $value['product_id'];
            $recordOneData['product_no'] = $value['product_no'];
            $recordOneData['num'] = $value['num'];
            $recordOneData['repertory_id'] = $value['rep_pid'];
            $recordOneData['create_time'] = time();
            $recordOneData['update_time'] = time();
            $recordOneData['status'] = StockOutRecordModel::TYPE_NOT_AUDIT;
            $recordOneData['is_del'] = StockOutRecordModel::NO_DEL;
            $recordData[] = $recordOneData;

            // crm_stock处理待出库数量
            list($code, $msg) = $stockModel->stockOutToUpdateStock($value['product_id'], $value['rep_pid'], $value['num']);
            if($code != 0){
                return [-1, $msg];
            }
        }

        $result = $this->addAll($recordData);
        if($result === false){
            return [-1, $this->getError()];
        }

        return [0, "新增出库记录成功"];
    }

    /**
     * 销售出库单
     * 通过出库单id直接新增出库记录， 并修改crm_stock 库存信息
     * @param $stockId  出库单id
     * @param $cateId   出库类别id
     * @param $filter   为了修改的的时候做筛选
     * @return array
     */
    public function autoSaveRecordForOrderForm($stockId, $cateId, $filter = []){
        $materialModel = new StockMaterialModel();
        $stockModel = new StockModel();
        $filter['m.type'] = ['eq', StockMaterialModel::TYPE_STOCK_OUT];
        $stockOutMaterialData = $materialModel->selectByStockId($stockId, $filter);
        $recordData = [];
        foreach ($stockOutMaterialData as $key => $value){
            $recordOneData = [];
            $recordOneData['source_id'] = $stockId;
            $recordOneData['source_pid'] = $value['id'];
            $recordOneData['cate_id'] = $cateId;
            $recordOneData['cate_name'] = self::$stockOutType[$cateId];
            $recordOneData['product_id'] = $value['product_id'];
            $recordOneData['product_no'] = $value['product_no'];
            $recordOneData['num'] = $value['num'];
            $recordOneData['repertory_id'] = $value['rep_pid'];
            $recordOneData['create_time'] = time();
            $recordOneData['update_time'] = time();
            $recordOneData['status'] = StockOutRecordModel::TYPE_NOT_AUDIT;
            $recordOneData['is_del'] = StockOutRecordModel::NO_DEL;
            $recordData[] = $recordOneData;

            // crm_stock处理待出库数量
            $stockData = $stockModel->where(['warehouse_number' => 'K004', "product_id" => $value['product_id']])->find();
            if (empty($stockData)){
                return [-1, $value['product_no'] . "物料在当前库房无记录"];
            }

            if('K004' != $value['rep_pid']){
                $stockNumber = $stockData['stockNum'] + $value['num'];
                $auditNumber = $stockData['o_audit'] - $value['num'];
                $res = $stockModel->where(['warehouse_number' => 'K004', "product_id" => $value['product_id']])->setField(['stock_number' => $stockNumber,'o_audit' => $auditNumber]);
                if ($res === false){
                    return [-1, "修改库房记录失败"];
                }

                $stockData = $stockModel->where(['warehouse_number' => $value['rep_pid'], "product_id" => $value['product_id']])->find();
                $stockNum = $stockData['stockNum'] + $value['num'];
                $auditNum = $stockData['o_audit'] - $value['num'];
                $res = $stockModel->where(['warehouse_number' => 'K004', "product_id" => $value['product_id']])->setField(['stock_number' => $stockNum,'o_audit' => $auditNum]);
                if ($res === false){
                    return [-1, "修改库房记录失败"];
                }
            }else {
                $outProcess = $stockData['out_processing'] + $value['num'];
                $auditNumber = $stockData['o_audit'] - $value['num'];
                $res = $stockModel->where(['warehouse_number' => 'K004', "product_id" => $value['product_id']])->setField(['out_processing' => $outProcess,'o_audit' => $auditNumber]);
                if ($res === false){
                    return [-1, "修改库房记录失败"];
                }
            }
        }

        $result = $this->addAll($recordData);
        if($result === false){
            return [-1, $this->getError()];
        }

        return [0, "新增出库记录成功"];
    }

    /**
     * 删除的出库记录
     * @param $sourceId  出库单ID
     * @param $cateId  出库单类型
     * @param $data  二维数组
     * @return array
     */
    public function autoDelStockOutRecordByStockIdMany($sourceId, $cateId, $data){
        $res = $this->where(['source_id' => $sourceId, "is_del" => self::NO_DEL, "cate_id" => $cateId])->setField(['is_del' => self::IS_DEL]);
        if($res === false){
            return [-1, "修改出库记录失败"];
        }
        $stockModel = new StockModel();
        foreach ($data as $key => $value){
            // crm_stock处理待出库数量
            switch ($cateId){
                case self::TYPE_STOCK_OUT_OTHER:
                    // 目前直接合用 销售出库单 删除
                    list($code, $msg) = $stockModel->orderFormStockOutToUpdateStock($value['product_id'], $value['repertory_id'], $value['num'], 2);
                    if($code != 0){
                        return [-1, $msg];
                    }
                    break;
                case self::TYPE_STOCK_OUT_ORDER_FORM:
                    list($code, $msg) = $stockModel->orderFormStockOutToUpdateStock($value['product_id'], $value['repertory_id'], $value['num'], 2);
                    if($code != 0){
                        return [-1, $msg];
                    }
                    break;
                /*case self::TYPE_STOCK_OUT_PRODUCE_MATERIAL:
                    list($code, $msg) = $stockModel->stockOutToUpdateStock($value['product_id'], $value['repertory_id'], $value['num'], 2);
                    if($code != 0){
                        return [-1, $msg];
                    }
                    break;*/
                case self::TYPE_STOCK_OUT_PRODUCTION:
                    list($code, $msg) = $stockModel->stockOutToUpdateStock($value['product_id'], $value['repertory_id'], $value['num'], 2);
                    if($code != 0){
                        return [-1, $msg];
                    }
                    break;
                default:
                    return [-1, "出库单类型不明"];
                    break;
            }
        }
        return [0, "删除出库记录成功"];
    }

    /**
     * 删除 销售出库单 的一条出库记录
     * @param $cateId  出库单类型
     * @param $data  一维数组
     * @return array
     */
    public function autoDelStockOutRecordByStockIdOne($cateId, $data){
        $res = $this->where(['source_pid' => $data['source_pid'], "is_del" => self::NO_DEL, 'cate_id' => $cateId])->setField(['is_del' => self::IS_DEL]);
        if($res === false){
            return [-1, "修改出库记录失败"];
        }
        $stockModel = new StockModel();
        // crm_stock处理待出库数量
        list($code, $msg) = $stockModel->orderFormStockOutToUpdateStock($data['product_id'], $data['repertory_id'], $data['num'], 2);
        if($code != 0){
            return [-1, $msg];
        }
        return [0, "删除出库记录成功"];
    }

    /**
     * 根据出库单id获取出库记录信息
     * @param $stockId
     * @param $map
     * @return array
     */
    public function getRecordByStockId($stockId, $map = []){
        $map['m.is_del'] = ['eq', self::NO_DEL];
        $map['m.source_id'] = ['eq', $stockId];

        $data = $this->alias("m")
            ->field("m.*, q.repertory_name as qualified_repertory_name")
            ->join("crm_repertorylist q on q.rep_id = m.repertory_id")
            ->where($map)->select();
        return $data;
    }

    /**
     * 根据出库单id获取出库记录信息   针对出库单  因为出库单的物料只从一个库里面取
     * @param $materialId
     * @param $map
     * @param $repId
     * @return array
     */
    public function getRecordByMaterialId($materialId, $map = []){
        $map['m.is_del'] = ['eq', self::NO_DEL];
        $map['m.source_pid'] = ['eq', $materialId];
        $data = $this->alias("m")
            ->field("m.*, q.repertory_name as qualified_repertory_name")
            ->join("crm_repertorylist q on q.rep_id = m.repertory_id")
            ->where($map)->find();
        return $data;
    }

    /**
     * 根据出库类型获取出库记录信息
     * @param $cateId
     * @return array
     */
    public function getRecordBySourceId($cateId){
        $map['m.is_del'] = ['eq', self::NO_DEL];
        $map['m.cate_id'] = ['eq', $cateId];
        $data = $this->alias("m")
            ->field("m.*, q.repertory_name as qualified_repertory_name")
            ->join("crm_repertorylist q on q.rep_id = m.repertory_id")
            ->where($map)->select();
        return $data;
    }

    /**
     * 出库单记录列表
     * @param $condition
     * @param $start
     * @param $length
     * @param $order
     * @param $sourceKind
     * @param $map
     * @return array
     */
    public function getList($condition, $start, $length, $order, $sourceKind, $map = []){
        $map['r.is_del'] = ['eq', self::NO_DEL];
        if(!empty($sourceKind)){
            $map['r.cate_id'] = ['eq', $sourceKind];
        }
        $recordMap = $map;

        if(strlen($condition) != 0){
            $where['c.stock_out_id'] = ['like', "%" . $condition . "%"];
            $where['o.stock_out_id'] = ['like', "%" . $condition . "%"];
            $where['o.stock_out_id'] = ['like', "%" . $condition . "%"];
            $where['r.product_no'] = ['like', "%" . $condition . "%"];
            $where['m.product_name'] = ['like', "%" . $condition . "%"];
            $where['m.product_number'] = ['like', "%" . $condition . "%"];
            $where['_logic'] = 'OR';
            $recordMap['_complex'] = $where;
        }

        $field = "r.*,
                  (
                    CASE
                    WHEN r.cate_id = 1 THEN c.stock_out_id 
                    WHEN r.cate_id = 2 THEN o.stock_out_id 
                    WHEN r.cate_id = 3 THEN p.stock_out_id 
                    ELSE null
                    END 
                  ) AS stock_no,
                  cs.stock_number,
                  cs.o_audit,
                  cs.out_processing,
                  m.product_name,
                  m.product_number";

        $data =  $this->alias("r")
            ->field($field)
//            ->join("LEFT JOIN crm_stock_out_produce p ON p.id = r.source_id AND p.is_del = " . StockOutProduceModel::NO_DEL)
            ->join("LEFT JOIN crm_stock_out_production p ON p.id = r.source_id AND p.is_del = " . StockOutProductionModel::NO_DEL)
            ->join("LEFT JOIN crm_stock_out_orderform o ON o.id = r.source_id AND o.is_del = " . StockOutOrderformModel::NO_DEL)
            ->join("LEFT JOIN crm_stock_out_other c ON c.id = r.source_id AND c.is_del = " . StockOutOtherModel::NO_DEL)
            ->join("left join crm_stock cs on cs.product_id = r.product_id and cs.warehouse_number = r.repertory_id")
            ->join("left join crm_material m on m.product_id = r.product_id")
            ->limit($start, $length)
            ->where($recordMap)
            ->order($order)
            ->select();
        /** 后台传输局到前台
        @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
        $count = $this->alias("r")
//            ->join("LEFT JOIN crm_stock_out_produce p ON p.id = r.source_id AND p.is_del = " . StockOutProduceModel::NO_DEL)
            ->join("LEFT JOIN crm_stock_out_production p ON p.id = r.source_id AND p.is_del = " . StockOutProductionModel::NO_DEL)
            ->join("LEFT JOIN crm_stock_out_orderform o ON o.id = r.source_id AND o.is_del = " . StockOutOrderformModel::NO_DEL)
            ->join("LEFT JOIN crm_stock_out_other c ON c.id = r.source_id AND c.is_del = " . StockOutOtherModel::NO_DEL)
            ->join("left join crm_stock cs on cs.product_id = r.product_id and cs.warehouse_number = r.repertory_id")
            ->where($map)
            ->count();
        $recordsFiltered = $this->alias("r")
//            ->join("LEFT JOIN crm_stock_out_produce p ON p.id = r.source_id AND p.is_del = " . StockOutProduceModel::NO_DEL)
            ->join("LEFT JOIN crm_stock_out_production p ON p.id = r.source_id AND p.is_del = " . StockOutProductionModel::NO_DEL)
            ->join("LEFT JOIN crm_stock_out_orderform o ON o.id = r.source_id AND o.is_del = " . StockOutOrderformModel::NO_DEL)
            ->join("LEFT JOIN crm_stock_out_other c ON c.id = r.source_id AND c.is_del = " . StockOutOtherModel::NO_DEL)
            ->join("left join crm_stock cs on cs.product_id = r.product_id and cs.warehouse_number = r.repertory_id")
            ->where($recordMap)
            ->count();

        return [$data,$count,$recordsFiltered];
    }

    /**
     * 审核一条出库记录
     * @param $id
     * @param $status
     * @return array
     */
    public function recordAudit($id, $status){
        $recordData = $this->where(['id' => $id, "is_del" => StockOutRecordModel::NO_DEL])->find();
        if(empty($recordData)){
            return dataReturn("当前物料出库记录没有找到", 400, $id);
        }
        if($recordData['status'] != StockOutRecordModel::TYPE_NOT_AUDIT){
            return dataReturn("当前物料出库记录已审核，不要重复审核", 400);
        }

        $this->startTrans();
        $res = $this->where(['id' => $id])->setField(['status' => $status, 'update_time' => time()]);
        if($res === false){
            $this->rollback();
            return dataReturn("修改物料出库记录失败", 400, $id);
        }

        if($status == StockOutRecordModel::TYPE_QUALIFIED){
            // 修改库房待出库数量
            $stockModel = new StockModel();
            $stockData = $stockModel->where(['product_id' => $recordData['product_id'], 'warehouse_number' => $recordData['repertory_id']])->find();


            if($recordData['cate_id'] == self::TYPE_STOCK_OUT_OTHER){
                $stockOutModel = new StockOutOtherModel();
                $stockOutData = $stockOutModel->where(['id' => $recordData['source_id']])->find();
                if($stockOutData['purchase_cate_id'] == StockOutOtherApplyModel::PRODUCTION_MODIFICATION){
                    $materialModel = new MaterialModel();
                    $material = $materialModel->where(['product_id' => $stockData['product_id']])->find();
                    $map['rework_number'] = ['eq', $material['rework_number'] + $stockData['num']];
                    $materialRes = $materialModel->where(['product_id' => $stockData['product_id']])->setField($map);
                    if($materialRes === false){
                        $this->rollback();
                        return dataReturn($materialModel->getError(),400);
                    }
                }
            }

            $num = $stockData['out_processing'] - $recordData['num'];
            $stockRes = $stockModel->where(['id' => $stockData['id']])->setField(['out_processing' => $num]);
            if($stockRes === false){
                $this->rollback();
                return dataReturn("修改库房信息失败", 400);
            }

            // 查找当前出库记录对应的出库单，如果全部都是已出库，那就将出库单的审核状态改为出库完成
            $map['source_id'] = ['eq', $recordData['source_id']];
            $map['status'] = ['neq', self::TYPE_QUALIFIED];
            $map['is_del'] = ['neq', self::NO_DEL];
            $stockOutRecord = $this->where($map)->select();
            if(empty($stockOutRecord)){
                switch ($recordData['cate_id']){
                    case self::TYPE_STOCK_OUT_OTHER :
                        $stockOutModel = new StockOutOtherModel();
                        $stockOutData = $stockOutModel->where(['id' => $recordData['source_id']])->find();

                        $stockOutRes = $stockOutModel->where(['id' => $stockOutData['id']])->setField(['audit_status' => StockOutOtherModel::TYPE_STOCK_QUALIFIED, "update_time" => time(), "audit_id" => session("staffId"), "audit_name" => session("nickname"), "audit_time" => time()]);
                        if($stockOutRes === false){
                            $this->rollback();
                            return dataReturn("修改出库单状态失败", 400);
                        }
                        // 修改源单中的出库状态  出库申请单与其他出库类型出库单是一对一的
                        $applyModel = new StockOutOtherApplyModel();
                        $applyRes = $applyModel->where(['id' => $stockOutData['source_id']])->setField(['audit_id' => session("staffId"), "update_time" => time(), 'audit_time' => time(), "audit_name" => session("nickname"), "stock_status" => StockOutOtherApplyModel::TYPE_OUT_ALL]);
                        if($applyRes === false){
                            $this->rollback();
                            return dataReturn("修改源单出库单状态失败", 400);
                        }
                        break;
                    case self::TYPE_STOCK_OUT_ORDER_FORM :
                        $stockOutModel = new StockOutOrderformModel();
                        $stockOutData = $stockOutModel->where(['id' => $recordData['source_id']])->find();

                        $stockOutRes = $stockOutModel->where(['id' => $stockOutData['id']])->setField(['audit_status' => StockOutOrderformModel::TYPE_STOCK_QUALIFIED, "update_time" => time(), "audit_id" => session("staffId"), "audit_name" => session("nickname"), "audit_time" => time()]);
                        if($stockOutRes === false){
                            $this->rollback();
                            return dataReturn("修改出库单状态失败", 400);
                        }

                        // 修改源单中的出库状态  销售出库单与源单是多对一关系
                        $orderFormModel = new OrderformModel();
                        $orderProdcutModel = new OrderproductModel();

                        // 首先判断当前源单名下所有出库单是否都出库完成
                        $map['s.source_id'] = ['eq', $stockOutData['source_id']];
                        $map['s.is_del']  = ['eq', StockOutOrderformModel::NO_DEL];
                        $map['s.audit_status'] = ['neq', StockOutOrderformModel::TYPE_STOCK_QUALIFIED];
                        $stockOutStatus = $stockOutModel->alias("s")->where($map)->select();
                        if(empty($stockOutStatus)){
                            // 判断出库数量是否符合源单要求
                            $allOut = true;
                            $productData = $orderProdcutModel->getOrderProductMsgById($stockOutData['source_id']);
                            foreach ($productData as $key => $value){
                                if($value['product_num'] != $value['used_num']){
                                    $allOut = false;
                                    break;
                                }
                            }
                            if($allOut == true){
                                // 修改当前源单的出库状态
                                $orderFormRes = $orderFormModel->where(['id' => $stockOutData['source_id']])->setField(['stock_status' => OrderformModel::TYPE_OUT_ALL]);
                                if($orderFormRes === false){
                                    $this->rollback();
                                    return dataReturn("修改源单出库单状态失败", 400);
                                }
                            }
                        }

                        break;
                    /*case self::TYPE_STOCK_OUT_PRODUCE_MATERIAL :
                        $stockOutModel = new StockOutProduceModel();
                        $stockOutData = $stockOutModel->where(['id' => $recordData['source_id']])->find();

                        $stockOutRes = $stockOutModel->where(['id' => $stockOutData['id']])->setField(['audit_status' => StockOutProduceModel::TYPE_STOCK_QUALIFIED, "update_time" => time(), "audit_id" => session("staffId"), "audit_name" => session("nickname"), "audit_time" => time()]);
                        if($stockOutRes === false){
                            $this->rollback();
                            return dataReturn("修改出库单状态失败", 400);
                        }

                        // 修改源单中的出库状态  领料订单与生产任务单是多对一关系

                        // 首先判断当前源单名下所有出库单是否都出库完成
                        $map['s.source_id'] = ['eq', $stockOutData['source_id']];
                        $map['s.is_del']  = ['eq', StockOutProduceModel::NO_DEL];
                        $map['s.audit_status'] = ['neq', StockOutProduceModel::TYPE_STOCK_QUALIFIED];
                        $stockOutStatus = $stockOutModel->alias("s")->where($map)->select();

                        if(empty($stockOutStatus)){
                            $productionOrderModel = new ProductionOrderModel();
                            $materialData = $productionOrderModel->getMaterialMsg($stockOutData['source_id']);

                            // 判断出库数量是否符合源单要求
                            $allOut = true;
                            foreach ($materialData as $key => $value){
                                if($value['total_num'] > $value['used_num']){
                                    $allOut = false;
                                    break;
                                }
                            }
                            if($allOut == true){
                                // 修改当前源单的出库状态
                                $productRes = $productionOrderModel->where(['id' => $stockOutData['source_id']])->setField(['stock_status' => ProductionOrderModel::TYPE_OUT_ALL]);
                                if($productRes === false){
                                    $this->rollback();
                                    return dataReturn("修改源单出库单状态失败", 400);
                                }
                            }
                        }
                        break;*/
                    case self::TYPE_STOCK_OUT_PRODUCTION:
                        return dataReturn("当前接口目前不用",400);
                    default:
                        $this->rollback();
                        return dataReturn("出库单类型不明，请联系后台管理员查看问题。", 400);
                        break;
                }
            }
        }

        $this->commit();
        return dataReturn("审核成功", 200);
    }

    /**
     * 审核整个出库单出库 直接审核出库完成
     * @param $stockId
     * @param $sourceType
     * @param int $status
     * @return array
     */
    public function stockOutAudit($stockId, $sourceType, $status = StockOutOtherModel::TYPE_STOCK_QUALIFIED){
        $this->startTrans();
        switch ($sourceType){
            case self::TYPE_STOCK_OUT_OTHER :
                $stockOutModel = new StockOutOtherModel();
                $stockOutData = $stockOutModel->where(['id' => $stockId])->find();
                if($stockOutData['audit_id'] != session("staffId")){
                    $this->rollback();
                    return dataReturn("您没有权限审核当前出库单", 400);
                }

                $stockOutRes = $stockOutModel->where(['id' => $stockId])->setField(['audit_status' => $status, "update_time" => time(), "audit_time" => time()]);
                if($stockOutRes === false){
                    $this->rollback();
                    return dataReturn("修改出库单状态失败", 400);
                }

                // 修改出库记录
                $recordRes = $this->where(['source_id' => $stockId, 'is_del' => self::NO_DEL])->setField(['status' => self::TYPE_QUALIFIED, "update_time" => time()]);
                if ($recordRes === false){
                    $this->rollback();
                    return dataReturn("修改出库记录失败", 400);
                }

                $applyModel = new StockOutOtherApplyModel();
                $applyRes = $applyModel->where(['id' => $stockOutData['source_id']])->setField(['audit_id' => session("staffId"), "update_time" => time(), 'audit_time' => time(), "audit_name" => session("nickname"), "stock_status" => StockOutOtherApplyModel::TYPE_OUT_ALL]);
                if($applyRes === false){
                    $this->rollback();
                    return dataReturn("修改源单出库单状态失败", 400);
                }
                break;
            case self::TYPE_STOCK_OUT_ORDER_FORM :
                $stockOutModel = new StockOutOrderformModel();
                $stockOutData = $stockOutModel->where(['id' => $stockId])->find();
                if($stockOutData['audit_id'] != session("staffId")){
                    $this->rollback();
                    return dataReturn("您没有权限审核当前出库单", 400);
                }

                $stockOutRes = $stockOutModel->where(['id' => $stockId])->setField(['audit_status' => $status, "update_time" => time(), "audit_time" => time()]);
                if($stockOutRes === false){
                    $this->rollback();
                    return dataReturn("修改出库单状态失败", 400);
                }

                // 修改出库记录
                $recordRes = $this->where(['source_id' => $stockId, 'is_del' => self::NO_DEL])->setField(['status' => self::TYPE_QUALIFIED, "update_time" => time()]);
                if ($recordRes === false){
                    $this->rollback();
                    return dataReturn("修改出库记录失败", 400);
                }

                // 修改源单中的出库状态  销售出库单与源单是多对一关系
                $orderFormModel = new OrderformModel();
                $orderProdcutModel = new OrderproductModel();

                // 判断当前出库单源单下方所有出库单是否都已经出库完成
                $map['s.source_id'] = ['eq', $stockOutData['source_id']];
                $map['s.is_del']  = ['eq', StockOutOrderformModel::NO_DEL];
                $map['s.audit_status'] = ['neq', StockOutOrderformModel::TYPE_STOCK_QUALIFIED];
                $stockOutStatus = $stockOutModel->alias("s")->where($map)->select();
                if(empty($stockOutStatus)){
                    // 判断出库数量是否符合源单要求
                    $allOut = true;
                    $productData = $orderProdcutModel->getOrderProductMsgById($stockOutData['source_id']);
                    foreach ($productData as $key => $value){
                        if($value['product_num'] != $value['used_num']){
                            $allOut = false;
                            break;
                        }
                    }
                    if($allOut == true){
                        // 修改当前源单的出库状态
                        $orderFormRes = $orderFormModel->where(['id' => $stockOutData['source_id']])->setField(['stock_status' => OrderformModel::TYPE_OUT_ALL]);
                        if($orderFormRes === false){
                            $this->rollback();
                            return dataReturn("修改源单出库单状态失败", 400);
                        }
                    }
                }
                break;
            /*case self::TYPE_STOCK_OUT_PRODUCE_MATERIAL :
                $stockOutModel = new StockOutProduceModel();
                $stockOutData = $stockOutModel->where(['id' => $stockId])->find();
                if($stockOutData['audit_id'] != session("staffId")){
                    $this->rollback();
                    return dataReturn("您没有权限审核当前出库单", 400);
                }

                $stockOutRes = $stockOutModel->where(['id' => $stockId])->setField(['audit_status' => $status, "update_time" => time(), "audit_time" => time()]);
                if($stockOutRes === false){
                    $this->rollback();
                    return dataReturn("修改出库单状态失败", 400);
                }

                // 修改出库记录
                $recordRes = $this->where(['source_id' => $stockId, 'is_del' => self::NO_DEL])->setField(['status' => self::TYPE_QUALIFIED, "update_time" => time()]);
                if ($recordRes === false){
                    $this->rollback();
                    return dataReturn("修改出库记录失败", 400);
                }

                // 修改源单中的出库状态  领料订单与生产任务单是多对一关系
                // 首先判断当前源单名下所有出库单是否都出库完成
                $map['s.source_id'] = ['eq', $stockOutData['source_id']];
                $map['s.is_del']  = ['eq', StockOutProduceModel::NO_DEL];
                $map['s.audit_status'] = ['neq', StockOutProduceModel::TYPE_STOCK_QUALIFIED];
                $stockOutStatus = $stockOutModel->alias("s")->where($map)->select();

                if(empty($stockOutStatus)){
                    $productionOrderModel = new ProductionOrderModel();
                    $materialData = $productionOrderModel->getMaterialMsg($stockOutData['source_id']);

                    // 判断出库数量是否符合源单要求
                    $allOut = true;
                    foreach ($materialData as $key => $value){
                        if($value['total_num'] > $value['used_num']){
                            $allOut = false;
                            break;
                        }
                    }
                    if($allOut == true){
                        // 修改当前源单的出库状态
                        $productRes = $productionOrderModel->where(['id' => $stockOutData['source_id']])->setField(['stock_status' => ProductionOrderModel::TYPE_OUT_ALL]);
                        if($productRes === false){
                            $this->rollback();
                            return dataReturn("修改源单出库单状态失败", 400);
                        }
                    }
                }

                break;*/
            case self::TYPE_STOCK_OUT_PRODUCTION:
                $stockOutModel = new StockOutProductionModel();
                $stockOutData = $stockOutModel->where(['id' => $stockId])->find();
                if($stockOutData['audit_id'] != session("staffId")){
                    $this->rollback();
                    return dataReturn("您没有权限审核当前出库单", 400);
                }

                $stockOutRes = $stockOutModel->where(['id' => $stockId])->setField(['audit_status' => $status, "update_time" => time(), "audit_time" => time()]);
                if($stockOutRes === false){
                    $this->rollback();
                    return dataReturn("修改出库单状态失败", 400);
                }

                // 修改出库记录
                $recordRes = $this->where(['source_id' => $stockId, 'is_del' => self::NO_DEL])->setField(['status' => self::TYPE_QUALIFIED, "update_time" => time()]);
                if ($recordRes === false){
                    $this->rollback();
                    return dataReturn("修改出库记录失败", 400);
                }

                $productionOrderModel = new ProductionOrderModel();
                $map = [];
                $map['o.id'] = ['in', $stockOutData['source_id']];
                $map['p.push_num'] = ['eq', 0];
                // 判断源单物料是否全部下推
                $productionIdNoPush = $productionOrderModel->alias("o")
                    ->field("o.id")
                    ->join("left join crm_production_order_product p on p.order_pid = o.id and p.is_del = " . ProductionOrderProductModel::NO_DEL)
                    ->where($map)
                    ->group("o.id")
                    ->select();
                if(empty($productionIdNoPush)){
                    $idNoPushArr = array_column($productionIdNoPush,'id');
                }else {
                    $idNoPushArr = [];
                }
                $idArr = array_filter(explode(',',$stockOutData['source_id']));

                foreach ($idArr as $k => $v){
                    $map = [];
                    $map['id'] = ['eq', $v];
                    if(in_array($v, $idNoPushArr)){
                        // 未下推完成
                        $productionRes = $productionOrderModel->where($map)->setField(['stock_status' => ProductionOrderModel::TYPE_OUT_OF_REP]);
                        if($productionRes === false){
                            $this->rollback();
                            return dataReturn($productionOrderModel->getError(),400);
                        }
                    }else {
                        $outOfRepRes = $stockOutModel->where(" find_in_set($v,source_id) and audit_status != " . StockOutProductionModel::TYPE_STOCK_QUALIFIED)->select();
                        if(empty($outOfRepRes)){
                            $productionRes = $productionOrderModel->where($map)->setField(['stock_status' => ProductionOrderModel::TYPE_OUT_ALL]);
                            if($productionRes === false){
                                $this->rollback();
                                return dataReturn($productionOrderModel->getError(),400);
                            }
                        }else {
                            $productionRes = $productionOrderModel->where($map)->setField(['stock_status' => ProductionOrderModel::TYPE_OUT_OF_REP]);
                            if($productionRes === false){
                                $this->rollback();
                                return dataReturn($productionOrderModel->getError(),400);
                            }
                        }
                    }
                }
                break;
            default :
                $this->rollback();
                return dataReturn("参数不正确",400);
                break;
        }

        $data = self::getRecordByStockId($stockId);


        $stockModel = new StockModel();
        $materialModel = new MaterialModel();
        foreach ($data as $key => $value){
            // crm_stock处理待出库数量
            $stockData = $stockModel->where(['warehouse_number' => $value['repertory_id'], 'product_id' => $value['product_id']])->find();

            // 针对其他出库单，如果出库类别为生产改件，则做以下操作
            if(isset($stockOutData['purchase_cate_id']) && $stockOutData['purchase_cate_id'] == StockOutOtherApplyModel::PRODUCTION_MODIFICATION && $sourceType == self::TYPE_STOCK_OUT_OTHER){
                $material = $materialModel->where(['product_id' => $value['product_id']])->find();
                $map['rework_number'] = ['eq', $material['rework_number'] + $value['num']];
                $materialRes = $materialModel->where(['product_id' => $value['product_id']])->setField($map);
                if($materialRes === false){
                    $this->rollback();
                    return dataReturn($materialModel->getError(),400);
                }
            }

            $num = $stockData['out_processing'] - $value['num'];
            $stockRes = $stockModel->where(['id' => $stockData['id']])->setField(['out_processing' => $num]);
            if($stockRes === false){
                $this->rollback();
                return dataReturn("修改库房库存信息失败",400);
            }
        }

        $this->commit();
        return dataReturn("审核成功",200);
    }

    /**
     * 获得三种出库单数据
     * @param $condition
     * @param $start
     * @param $length
     * @param $order
     * @param $sourceKind
     * @param array $map
     * @return array
     */
    public function getStockOutList($condition, $start, $length, $order , $sourceKind, $map = []){
        $map['o.is_del'] = ['eq', self::NO_DEL];

        $recordMap = $map;

        if(strlen($condition) != 0){
            $where['o.stock_out_id'] = ['like', "%" . $condition . "%"];
            $where['_logic'] = 'OR';
            $map['_complex'] = $where;
        }

        $field = "o.id, o.stock_out_id, o.printing_times, o.source_kind, o.audit_status, o.create_id, o.create_name , o.create_time, o.send_id, o.send_name, o.audit_id, o.audit_name";
        $lastField = "id, stock_out_id, printing_times, source_kind, audit_status, create_id, create_name , create_time, send_id, send_name, audit_id, audit_name, source_id";

        switch ($sourceKind){
            case self::TYPE_STOCK_OUT_OTHER :
                $otherSql = M("stock_out_other")
                    ->alias("o")
                    ->field($field . ", a.apply_id as source_id")
                    ->join("left join crm_stock_out_other_apply a on a.id = o.source_id")
                    ->where($map)
                    ->select(false);
                $sql = "select " . $lastField . "  from ($otherSql) a" ;

                $otherSqlOne = M("stock_out_other")
                    ->alias("o")
                    ->field($field . ", a.apply_id as source_id")
                    ->join("left join crm_stock_out_other_apply a on a.id = o.source_id")
                    ->where($recordMap)
                    ->select(false);
                $sqlOne = "select " . $lastField . "  from ($otherSqlOne) a" ;
                break;
            case self::TYPE_STOCK_OUT_ORDER_FORM :
                $orderFormSql = M("stock_out_orderform")
                    ->alias("o")
                    ->field($field . ", co.cpo_id as source_id")
                    ->join("left join crm_orderform co on co.id = o.source_id")
                    ->where($map)
                    ->select(false);
                $sql = "select " . $lastField . "  from ($orderFormSql) a" ;

                $orderFormSqlOne = M("stock_out_orderform")
                    ->alias("o")
                    ->field($field . ", co.cpo_id as source_id")
                    ->join("left join crm_orderform co on co.id = o.source_id")
                    ->where($recordMap)
                    ->select(false);
                $sqlOne = "select " . $lastField . "  from ($orderFormSqlOne) a" ;
                break;
            /*case self::TYPE_STOCK_OUT_PRODUCE_MATERIAL :
                $productSql = M("stock_out_produce")
                    ->alias("o")
                    ->field($field . ", p.production_code as source_id")
                    ->join("left join crm_production_order p on p.id = o.source_id")
                    ->where($map)
                    ->select(false);
                $sql = "select " . $lastField . "  from ($productSql) a" ;
                $productSqlOne = M("stock_out_produce")
                    ->alias("o")
                    ->field($field . ", p.production_code as source_id")
                    ->join("left join crm_production_order p on p.id = o.source_id")
                    ->where($recordMap)
                    ->select(false);
                $sqlOne = "select " . $lastField . "  from ($productSqlOne) a" ;
                break;*/
            case self::TYPE_STOCK_OUT_PRODUCTION :
                $productionSql = M("stock_out_production")
                    ->alias("o")
                    ->field($field . ", GROUP_CONCAT(p.production_code) AS source_id")
                    ->join("left join crm_production_order p on FIND_IN_SET(p.id,(o.source_id)) ")
                    ->where($map)
                    ->group("o.id")
                    ->select(false);
                $sql = "select " . $lastField . "  from ($productionSql) a" ;
                $productionSqlOne = M("stock_out_production")
                    ->alias("o")
                    ->field($field . ", GROUP_CONCAT(p.production_code) AS source_id")
                    ->join("left join crm_production_order p on FIND_IN_SET(p.id,(o.source_id)) ")
                    ->where($recordMap)
                    ->group("o.id")
                    ->select(false);
                $sqlOne = "select " . $lastField . "  from ($productionSqlOne) a" ;
                break;
            default :
                $otherSql = M("stock_out_other")
                    ->alias("o")
                    ->field($field . ", a.apply_id as source_id")
                    ->join("left join crm_stock_out_other_apply a on a.id = o.source_id")
                    ->where($map)
                    ->buildSql();
//                $produceSql = M("stock_out_produce")
//                    ->alias("o")
//                    ->field($field . ", p.production_code as source_id")
//                    ->join("left join crm_production_order p on p.id = o.source_id")
//                    ->where($map)
//                    ->buildSql();
                $productionSql = M("stock_out_production")
                    ->alias("o")
                    ->field($field . ", GROUP_CONCAT(p.production_code) AS source_id ")
                    ->join("left join crm_production_order p on FIND_IN_SET(p.id,(o.source_id))")
                    ->where($map)
                    ->group("o.id")
                    ->buildSql();
                $orderFormSql = M("stock_out_orderform")
                    ->alias("o")
                    ->field($field . ", co.cpo_id as source_id")
                    ->join("left join crm_orderform co on co.id = o.source_id")
                    ->where($map)
//                    ->union($produceSql, true)
                    ->union($productionSql, true)
                    ->union($otherSql, true)
                    ->select(false);
                $sql = "select " . $lastField . "  from ($orderFormSql) a" ;

                // 计算没有经过查找条件筛选的数据量
                $otherSqlOne = M("stock_out_other")
                    ->alias("o")
                    ->field($field . ", a.apply_id as source_id")
                    ->join("left join crm_stock_out_other_apply a on a.id = o.source_id")
                    ->where($recordMap)
                    ->buildSql();

//                $produceSqlOne = M("stock_out_produce")
//                    ->alias("o")
//                    ->field($field . ", p.production_code as source_id")
//                    ->join("left join crm_production_order p on p.id = o.source_id")
//                    ->where($recordMap)
//                    ->buildSql();

                $productionSqlOne = M("stock_out_production")
                    ->alias("o")
                    ->field($field . ", GROUP_CONCAT(p.production_code) AS source_id")
                    ->join("left join crm_production_order p on FIND_IN_SET(p.id,(o.source_id))")
                    ->where($recordMap)
                    ->group("o.id")
                    ->buildSql();

                $orderFormSqlOne = M("stock_out_orderform")
                    ->alias("o")
                    ->field($field . ", co.cpo_id as source_id")
                    ->join("left join crm_orderform co on co.id = o.source_id")
                    ->where($recordMap)
                    ->union($otherSqlOne, true)
//                    ->union($produceSqlOne, true)
                    ->union($productionSqlOne, true)
                    ->select(false);
                $sqlOne = "select " . $lastField . "  from ($orderFormSqlOne) a" ;
                break;
        }
        $model = new Model();

        $dataCount = $model->query($sql);
        $count = count($dataCount);

        $dataOne = $model->query($sqlOne);
        $recordsFiltered = count($dataOne);

        $sql .= " order by " . $order . " limit " . $start . "," . $length ;
        $data = $model->query($sql);


        return [$data, $count, $recordsFiltered];
    }

    /**
     * 根据生产计划production_order主键获取出库的物料
    */
    public function getStockOutRecordWithProduceOrderId($productionOrderId)
    {
        $map['source_id'] = ['EQ', $productionOrderId];
        $stockOutPrimaryIdArr = M('stock_out_produce')->where($map)->field('id')->select();
        if (!count($stockOutPrimaryIdArr)) {
            return [];
        }
        $stockOutPrimaryIds = getPrjIds($stockOutPrimaryIdArr, 'id');
        $filter['a.source_id'] = ['IN', $stockOutPrimaryIds];
        $filter['a.is_del'] = ['EQ', self::NO_DEL];
        return $this->alias('a')
            ->field("a.product_id,a.product_no,c.product_name,a.repertory_id rep_id_out,'K20' rep_id_in,d.stock_number stock_total_number,sum(a.num) num_all,count(distinct b.id) times")
            ->where($filter)
            ->join('LEFT JOIN crm_stock_out_produce b ON a.source_id = b.id')
            ->join('LEFT JOIN crm_material c ON a.product_id = c.product_id')
            ->join('LEFT JOIN crm_stock d ON d.product_id = a.product_id and d.warehouse_number = a.repertory_id')
            ->group('a.product_id,a.repertory_id')
            ->select();
    }


    /**
     * 根据生产计划production_order主键获取出库的物料
     */
    public function getStockOutRecordWithProductionOrderId($productionOrderId)
    {
        $condition['a.order_pid'] = ['eq', $productionOrderId];
        $condition['a.is_del'] = ['eq',self::NO_DEL];
        $data = M('production_order_product')->alias('a')
            ->field("a.product_id,b.product_no,b.product_name,b.product_number,a.num,a.push_num,b.warehouse_id,c.repertory_name,b.warehouse_id rep_id_out, 'K20' rep_id_in,d.stock_number stock_total_number")
            ->where($condition)
            ->join('LEFT JOIN crm_material b ON a.product_id = b.product_id')
            ->join('LEFT JOIN crm_repertorylist c ON b.warehouse_id = c.rep_id')
            ->join('LEFT JOIN crm_stock d ON d.product_id = a.product_id AND b.warehouse_id = d.warehouse_number')
            ->select();
        return $data;
    }
}