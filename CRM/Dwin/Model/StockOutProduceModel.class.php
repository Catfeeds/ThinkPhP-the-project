<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/8/10
 * Time: 上午10:11
 */
namespace Dwin\Model;


use Think\Exception;
use Think\Model;
class StockOutProduceModel extends model{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

    const IS_DEL = 1; // 已删除
    const NO_DEL = 0; // 未删除

    /* 审核状态：0-未审核 1-审核不合格 2 质控审核完毕 3 库房审核完毕*/
    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_UNQUALIFIED = 1;     // 审核不合格
    const TYPE_QUALIFIED = 2;     // 质控审核完毕
    const TYPE_STOCK_QUALIFIED = 3;       // 库房审核完毕

    public static $auditMap = [
        self::TYPE_NOT_AUDIT => "未审核",
        self::TYPE_UNQUALIFIED => "审核不合格",
        self::TYPE_QUALIFIED => "质控审核完毕",
        self::TYPE_STOCK_QUALIFIED => "库房审核完毕",
    ];


    /**
     * create by  chendd 去除非表中字段
     * @param $params
     * @return array
     */
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

    /**
     * 比较前后数据是否发生改变
     * @param $oldData
     * @param $editedData
     * @return bool
     */
    private function compareData($oldData, $editedData)
    {
        // 然后在与原先的数据做对比
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

        $editedData['id'] = $oldData['id'];
        $editedData['update_time']  = time();
        $editedData['update_id']    = session('staffId');
        return $editedData;
    }

    public function getAddData($postData){
        $data = self::getNewField($postData);
        if(empty($data)){
            return [-1, '没有提交新增数据'];
        }

        if(empty($data['stock_out_id']) || empty($data['source_id']) || empty($data['picking_dept_id']) || empty($data['choise_no']) || empty($data['picking_dept_name']) || empty($data['id'])){
            return [-2, "数据未填写完整"];
        }

        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['create_name']    = session('nickname');
        $data['update_time']  = time();
        $data['audit_status']    = self::TYPE_QUALIFIED;
        $data['source_kind']  = StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL;

        $data = $this->create($data);

        if(!$data){
            return [-2, $this->getError()];
        }else {
            $res = $this->add($data);
            if(!$res){
                return [-2, $this->getError()];
            }
            return [0, '数据新增成功'];
        }
    }

    public function getEditData($params)
    {
        $data = self::getNewField($params);
        if (empty($data)) {
            return [-1, [], "无修改数据提交"];
        }

        $oldData = $this->field("*")->find($data['id']);

        if($oldData['audit_status'] == self::TYPE_STOCK_QUALIFIED){
            return [-2, [], '当前出库单已出库，不可修改'];
        }
        $editData = $this->compareData($oldData, $data);

        if ($editData === false) {
            return [-1, [], '无数据修改'];
        } else {
            $data = $this->create($editData);
            if(!$data){
                return[-2,[], $this->getError()];
            }
            return [0, $data, '数据实例化成功'];
        }
    }

    public function modifyProduce($postData){
        try {
            list($code, $editData, $msg) = $this->getEditData($postData);

            if ($code != 0) {
                return [$msg, -1];
            }

            $editRst = $this->save($editData);
            if ($editRst === false) {
                return [$this->getError(), -2];
            }

            return ['修改订单基本信息成功', 0];
        } catch (\Exception $exception) {
            return dataReturn($exception->getMessage(), self::$failStatus);
        }
    }

    /**
     * @param $data
     * @param $produceId  领料单主键
     * @param $orderId  领料单源单主键
     * @return array
     */
    public function addProcessData($data, $produceId, $orderId){

        // 判断数据是否为空
        if(empty($data['num']) || empty($data['substituted_id']) || empty($data['product_id'])){
            return [-1, "物料数据不全，不可以新增",[],[]];
        }

        // 当前需要添加的物料
        $materialData = [];
        $materialData['source_id'] = $produceId;
        $materialData['product_id'] = $data['substituted_id'];
        $materialData['product_no'] = $data['substituted_no'];
        $materialData['num'] = $data['num'];
        $materialData['rep_pid'] = $data['rep_pid'];
        $materialData['unqualified_rep_pid'] = isset($data['unqualified_rep_pid']) ? $data['unqualified_rep_pid'] : '';
        $materialData['create_id'] = session("staffId");
        $materialData['type'] = StockMaterialModel::TYPE_STOCK_OUT;
        $materialData['create_time'] = time();
        $materialData['update_time'] = time();


        // 当前替代物信息
        $replaceData = [];
        $replaceData['order_id'] = $orderId;
        $replaceData['produce_id'] = $produceId;
        $replaceData['product_id'] = $data['product_id'];
        $replaceData['substituted_id'] = $data['substituted_id'];
        $replaceData['num'] = $data['num'];
        $replaceData['rep_pid'] = $data['rep_pid'];
        $replaceData['unqualified_rep_pid'] = isset($data['unqualified_rep_pid']) ? $data['unqualified_rep_pid'] : '';
        $replaceData['create_id'] = session("staffId");
        $replaceData['create_time'] = time();
        $replaceData['update_time'] = time();


        return [0, "处理数据成功", $materialData, $replaceData];
    }

    public function editProcessData($data){
        // 判断数据是否为空
        if(empty($data['num'])){
            return [-1, "物料数量为0，不可以新增",[],[]];
        }

        // 当前需要添加的物料
        $materialData = [];
        $materialData['id'] = $data['id'];
        $materialData['product_id'] = $data['substituted_id'];
        $materialData['product_no'] = $data['substituted_no'];
        $materialData['num'] = $data['num'];
        $materialData['rep_pid'] = $data['rep_pid'];
        $materialData['update_time'] = time();

        return [0, "处理数据成功", $materialData, $data['product_id']];
    }

    /**
     * 生成一个领料出库单
     * @param $produce
     * @param $material
     * @return array
     */
    public function createProduce($produce, $material){
        try{
            $this->startTrans();
            list($produceCode, $produceMsg) = self::getAddData($produce);
            if($produceCode != 0){
                $this->rollback();
                return dataReturn($produceMsg, 400);
            }

            $data = [];
            $replace = [];

            // 查处当前生产计划中所需要生产数量
            $productionOrderModel = new ProductionOrderModel();
            $orderMaterialData = $productionOrderModel->getMaterialMsg($produce['source_id']);

            $orderMaterialDataArr = reformArray($orderMaterialData, 'product_id');

            // 处理数据，将当前数据分为两个数组，一个正常存储在物料表中，另一个生成一个log，用来以后领料出库单源单推当前领料出库单数据
            foreach ($material as $key => $item){
                // 当前生产计划任务剩余生产数量
                if(empty($orderMaterialDataArr[$item['product_id']]['used_num'])){
                    $surplus = ($orderMaterialDataArr[$item['product_id']]['total_num']) * $orderMaterialDataArr[$item['product_id']]['one_num'];
                }else {
                    $surplus = ($orderMaterialDataArr[$item['product_id']]['total_num'] * $orderMaterialDataArr[$item['product_id']]['one_num']) - $orderMaterialDataArr[$item['product_id']]['used_num'];
                }

                if($surplus < $item['num']){
                    $this->rollback();
                    return dataReturn("所推数量超过领料出库单所需数量，即不可下推", 400, [$surplus,$item['num']]);
                }


                list($code, $msg, $materialData, $replaceData) = self::addProcessData($item, $produce['id'], $produce['source_id']);

                if($code != 0){
                    $this->rollback();
                    return dataReturn($msg, 400);
                }
                $data[] = $materialData;
                $replace[] = $replaceData;

            }

            // 写物料信息
            $materialModel = new StockMaterialModel();
            $materialRes = $materialModel->addAll($data);
            if($materialRes === false){
                $this->rollback();
                return dataReturn($materialModel->getError(), 400);
            }

            // 写物料log关系
            $replaceModel = new BomReplaceLogModel();
            $replaceRes = $replaceModel->addAll($replace);
            if($replaceRes === false){
                $this->rollback();
                return dataReturn($replaceModel->getError(), 400);
            }

            // 直接写record
            $recordModel = new StockOutRecordModel();
            list($code, $message) = $recordModel->autoSaveRecordForProduce($produce['id'], StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL);
            if($code != 0){
                $this->rollback();
                return dataReturn($message, 400);
            }

            // 修改源单状态
            $productionData = $productionOrderModel->getOrderInfoWithId($produce['source_id']);
            if($productionData['stock_status'] != ProductionOrderModel::TYPE_OUT_OF_REP){
                $productRes = $productionOrderModel->where(['id' => $produce['source_id']])->setField(['stock_status' => ProductionOrderModel::TYPE_OUT_OF_REP]);
                if($productRes === false){
                    $this->rollback();
                    return dataReturn("修改源单出库单状态失败", 400);
                }
            }

            $this->commit();
            return dataReturn("新增领料单成功", 200);
        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(), 400);
        }

    }

    /**
     * 修改领料出库单数据
     * @param $postData
     * @return array
     */
    public function editProduce($postData){
        try{
            $this->startTrans();
            $productionOrderModel = new ProductionOrderModel();

            $statusStr = '';

            if(!empty($postData['edit_material'])){
                // 获取除当前记录之外的入库信息 已用总数包含当前修改的数量
                $map['so.id'] = ['neq', $postData['produce']['id']];
                $orderMaterialData = $productionOrderModel->getMaterialMsg($postData['produce']['source_id'], $map);
                $productArr = reformArray($orderMaterialData, "product_id");

                foreach ($postData['edit_material'] as $key => $value){
                    // 处理剩余数量
                    if(empty($productArr[$value['product_id']]['used_num'])){
                        $surplusNum = $productArr[$value['product_id']]['total_num'] * $productArr[$value['product_id']]['one_num'];
                    }else {
                        $surplusNum = ($productArr[$value['product_id']]['total_num'] - $productArr[$value['product_id']]['used_num'])  * $productArr[$value['product_id']]['one_num'];
                    }

                    if($surplusNum < $value['num']){
                        $this->rollback();
                        return dataReturn("物料" . $value['product_no'] . "总出库数量不能超过订单剩余数量" ,self::$failStatus);
                    }

                    list($editCode, $msg) = self::editProductMaterial($postData['produce']['id'], $value);
                    if($editCode == 0){
                        $statusStr = self::$successStatus;
                    }

                    if($editCode == -2){
                        $this->rollback();
                        return dataReturn($msg, self::$failStatus);
                        break;
                    }

                }
            }

            // 生产领料出库单没有新增的物料信息
            /*if (!empty($postData['new_material'])){
                $productionOrderModel = new ProductionOrderModel();
                $orderMaterialData = $productionOrderModel->getMaterialMsg($postData['produce']['source_id']);

                $orderMaterialDataArr = reformArray($orderMaterialData, 'product_id');

                $data = [];
                $replace = [];

                // 处理数据，将当前数据分为两个数组，一个正常存储在物料表中，另一个生成一个log，用来以后领料出库单源单推当前领料出库单数据
                foreach ($postData['new_material'] as $key => $item){
                    // 当前生产计划任务剩余生产数量
                    if(empty($orderMaterialDataArr[$item['product_id']]['used_num'])){
                        $surplus = $orderMaterialDataArr[$item['product_id']]['bom_num'] * $orderMaterialDataArr[$item['product_id']]['plan_num'];
                    }else {
                        $surplus = ($orderMaterialDataArr[$item['product_id']]['plan_num'] - $orderMaterialDataArr[$item['product_id']]['used_num']) * $orderMaterialDataArr[$item['product_id']]['bom_num'];
                    }

                    if($surplus < $item['num']){
                        $this->rollback();
                        return dataReturn("库存不足，无法下推领料订单", 400);
                    }


                    list($code, $msg, $materialData, $replaceData) = self::addProcessData($item, $postData['produce']['id'], $postData['produce']['source_id']);

                    if($code != 0){
                        $this->rollback();
                        return dataReturn($msg, 400);
                    }
                    $data[] = $materialData;
                    $replace[] = $replaceData;

                }

                // 写物料信息
                $materialModel = new StockMaterialModel();
                $materialRes = $materialModel->addAll($data);
                if(!$materialRes){
                    $this->rollback();
                    return dataReturn($materialModel->getError(), 400);
                }

                // 写物料log关系
                $replaceModel = new BomReplaceLogModel();
                $replaceRes = $replaceModel->addAll($replace);
                if(!$replaceRes){
                    $this->rollback();
                    return dataReturn($replaceModel->getError(), 400);
                }

                // 直接写record
                $recordModel = new StockOutRecordModel();
                list($code, $message) = $recordModel->autoSaveRecordByPurchase($postData['produce']['id'], StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL);
                if($code != 0){
                    $this->rollback();
                    return dataReturn($message, 400);
                }
            }*/

            list($msg, $code) = $this->modifyProduce($postData['produce']);
            if ($code == -2) {
                $this->rollback();
                return dataReturn($msg, self::$failStatus);
            }
            if($code == -1 && $statusStr == ''){
                return dataReturn('数据未发生修改', 400);
            }

            $this->where(['id' => $postData['apply']['id']])->setField(["audit_status" => self::TYPE_NOT_AUDIT]);

            $this->commit();
            return dataReturn("修改成功", 200);
        }catch (\Exception $exception){
            return dataReturn($exception->getMessage(), 200);
        }
    }

    public function editProductMaterial($produceId, $params){
        $materialModel = new StockMaterialModel();
        list($code, $msg, $data, $productId) = self::editProcessData($params);

        if($code != 0){
            return [-2, $msg];
        }

        $oldData = $materialModel->field("*")->find($data['id']);


        $stockRecordModel = new StockOutRecordModel();
        $recordData = $stockRecordModel->getNumByMaterialId($oldData['id'], StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL);

        if($data['num'] < $recordData['num']){
            return [-2, "修改后的出库库数量不能低于已出库数量"];
        }

        $editData = $materialModel->compareData($oldData, $data);

        if ($editData === false) {
            return [-1, "无数据修改"];
        }else if($editData == -1) {
            return [-2, "只能对出库数量进行修改"];
        }
        // 修改出库单时，同步修改库存信息
        $stockModel = new StockModel();
        $recordModel = new StockOutRecordModel();

        // 首先处理物料修改的情况
        if(isset($editData['product_id'])){
            $recordRes = $recordModel->where(['source_pid' => $data['id'], "is_del" => self::NO_DEL, 'product_id' => $oldData['product_id']])->setField(['repertory_id' => $data['rep_pid'], 'num' => $data['num']]);
            if(!$recordRes){
                return [-2, "修改出库记录失败"];
            }
            // crm_stock处理待出库数量
            list($code, $msg) = $stockModel->stockOutToUpdateStock($oldData['product_id'], $oldData['rep_pid'], $oldData['num'], 2);
            if($code != 0){
                return [-2, $data['product_no'] . $msg];
            }

            list($code, $msg) = $stockModel->stockOutToUpdateStock($data['product_id'], $data['rep_pid'], $data['num'], 1);
            if($code != 0){
                return [-2, $data['product_no'] . $msg];
            }


        }else {
            if(isset($editData['rep_pid'])){
                $num = isset($editData['num']) ? $editData['num'] : $oldData['num'];

                list($code, $msg) = $stockModel->updateStockOutToUpdateStock($oldData['product_id'], $oldData['rep_pid'], $editData['rep_pid'], $oldData['num'], $num);
                if($code != 0){
                    return [-2, $oldData['product_no'] . $msg];
                }

                $recordModel->where(['source_pid' => $data['id'], "is_del" => self::NO_DEL])->setField(['repertory_id' => $editData['rep_pid'], 'num' => $data['num']]);
            }

            if(isset($editData['num']) && !isset($editData['rep_pid'])){
                $num = $editData['num'] - $oldData['num'];

                // crm_stock处理待出库数量
                list($code, $msg) = $stockModel->stockOutToUpdateStock($oldData['product_id'], $oldData['rep_pid'], $num);
                if($code != 0){
                    return [-2, $oldData['product_no'] . $msg];
                }
                $recordModel->where(['source_id' => $produceId, "product_id" => $oldData['product_id'], "is_del" => self::NO_DEL])->setField(['num' => $data['num']]);

            }
        }


        // 修改log表
        $replaceLogModel = new BomReplaceLogModel();
        $oldReplaceData = $replaceLogModel->where(['produce_id' => $produceId, 'product_id' => $productId, 'is_del' => BomReplaceLogModel::NO_DEL])->find();
        $replaceLogModel->where(['id' => $oldReplaceData['id']])->setField(['substituted_id' => $data['product_id'], 'rep_pid' => $data]);

        $res = $materialModel->save($editData);
        if(!$res){
            return[-2, $materialModel->getError()];
        }

        return [0,  "修改生产领料物料信息保存成功"];

    }

    /**
     * 获取当前领料出库单名下所有物料信息 , 以物料替换记录表为主表
     * @param $produceId
     * @param array $map
     * @return mixed
     */
    public function getMaterialMsg($produceId, $map = []){
        $map['r.is_del'] = ['eq', StockMaterialModel::NO_DEL];
        $map['r.produce_id'] = ['eq', $produceId];

        $replaceModel = new BomReplaceLogModel();
        $data = $replaceModel->alias('r')
            ->field("m.id,m.source_id,m.product_id as substituted_id,cm.product_no as substituted_no,cm.product_name as substituted_name, cm.product_number as substituted_number, m.num, m.rep_pid, cs.stock_number, cs.o_audit,cs.out_processing,r.product_id,rcm.product_name, rcm.product_no, rcm.product_number")
            ->join("left join crm_stock_material m on r.produce_id = m.source_id and r.substituted_id = m.product_id and m.is_del = " . StockMaterialModel::NO_DEL . " and m.type = " . StockMaterialModel::TYPE_STOCK_OUT)
            ->join("left join crm_material cm on cm.product_id = m.product_id")
            ->join("left join crm_stock cs on cs.product_id = m.product_id and cs.warehouse_number = m.rep_pid")
            ->join("left join crm_material rcm on rcm.product_id = r.product_id")
            ->where($map)
            ->select();
        return $data;
    }

    /**
     * 获取出库单单列表页信息
     */
    public function getList($condition, $start, $length, $order){
        $map['sop.is_del'] = ['eq', self::NO_DEL];
        $recordMap = $map;

        if(strlen($condition) != 0){
            $where['sop.stock_out_id'] = ['like', "%" . $condition . "%"];
            $where['sop.picking_dept_name'] = ['like', "%" . $condition . "%"];
            $where['sop.create_name']=['like', "%" . $condition . "%"];
            $where['_logic'] = 'OR';
            $recordMap['_complex'] = $where;
        }

        $data =  $this->alias("sop")
            ->field("sop.*,po.product_id, po.product_no")
            ->join("left join crm_production_order po on po.id = sop.source_id and po.is_del = " . ProductionOrderModel::$notDel)
            ->limit($start, $length)
            ->where($recordMap)
            ->order($order)
            ->select();
        /** 后台传输局到前台
        @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
        $count = $this->alias("sop")
            ->join("left join crm_production_order po on po.id = sop.source_id and po.is_del = " . ProductionOrderModel::$notDel)
            ->where($map)
            ->count();
        $recordsFiltered = $this->alias("sop")
            ->join("left join crm_production_order po on po.id = sop.source_id and po.is_del = " . ProductionOrderModel::$notDel)
            ->where($recordMap)
            ->count();

        return [$data,$count,$recordsFiltered];
    }

    /**
     * 删除整个出库单
     * @param $id
     * @return array
     */
    public function delStockOutProduce($id){
        try{
            $this->startTrans();
            // 判断当前出库单在出库记录里面是否有出库记录
            $recordModel = new StockOutRecordModel();

            $produceData = $this->find($id);
            if($produceData['audit_status'] != self::TYPE_STOCK_QUALIFIED){
                return dataReturn("当前出库单已出库，不可以删除", 400);
            }

            $res = $this->where(['id' => $id])->setField(['is_del' => self::IS_DEL]);
            if($res === false){
                $this->rollback();
                return dataReturn("删除失败", 400);
            }


            $materialModel = new StockMaterialModel();
            $materialModel->where(['source_id' => $id, "is_del" => self::NO_DEL])->setField(['is_del' => StockMaterialModel::IS_DEL, "update_time" => time()]);

            // 删除替换记录
            $replaceModel = new BomReplaceLogModel();
            $replaceModel->where(['order_id' => $produceData['source_id'], 'produce_id' => $id, 'is_del' => BomReplaceLogModel::NO_DEL])->setField(['update_time' => time(), 'is_del' => BomReplaceLogModel::IS_DEL]);

            // 删除对应出库记录
            list($code, $msg) = $recordModel->autoDelStockOutRecordByStockIdMany($id, StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL, $recordModel->getRecordByStockId($id));
            if($code != 0){
                $this->rollback();
                return dataReturn($msg, 400);
            }

            $this->commit();
            return dataReturn("删除成功", 200);
        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(), 400);
        }

    }

    /**
     * 获取出库单基本信息 目前为了是打印出库单
     * @param $id
     * @param array $map
     * @return mixed
     */
    public function getMsgForPrinting($id, $map = []){
        $map['p.id'] = ['eq', $id];
        $data = $this->alias("p")
            ->field("p.*, o.production_line, o.production_code")
            ->join("left join crm_production_order o on o.id = p.source_id")
            ->where($map)
            ->find();
        return $data;
    }

    /**
     * 生产领料单打印
     * @param $baseMsg
     * @param $materialData
     * @param $repId
     * @return string
     */
    public function printingToPdf($baseMsg, $materialData, $repId){
        Vendor('mpdf.mpdf');
        //设置中文编码
        $mpdf=new \mPDF('zh-cn','A4', 0, '宋体', 0, 0);
        $mpdf->useAdobeCJK = true;

        //html内容
        $html = '<div style="margin: 0 6%">
                    <div style="width: 100%;text-align: center;">生产领料出库单</div>
                    <table style="margin-top: 20px; min-height: 25px; line-height: 25px;width: 100%">
                        <tr style="width:100%;height: 30px;">
                            <td width="10%">生产单号：</td>
                            <td width="17%">' . $baseMsg["production_code"] . '</td>
                            <td width="10%">生产线：</td>
                            <td width="17%">' . $baseMsg["picking_purpose"] . '</td>
                            <td width="10%">编号：</td>
                            <td width="17%">' . $baseMsg["stock_out_id"] . '</td>
                            <td width="8%">日期：</td>
                            <td width="12%">' . date("Y/m/d",$baseMsg["create_time"]) . '</td>
                        </tr>
                        <tr style="width:100%;height: 30px;">
                            <td width="10%">领料部门：</td>
                            <td width="25%">' . $baseMsg["picking_dept_name"] . '</td>
                            <td width="10%">领料类型：</td>
                            <td width="28%">' . StockOutOtherApplyModel::$pickingType[$baseMsg["picking_kind"]] . '</td>
                            <td width="10%">领料用途：</td>
                            <td width="24%">' . $baseMsg["picking_purpose"] . '</td>
                        </tr>
                    </table>
                    <table style="margin-top:10px;border:1px solid black;width: 100%; min-height: 25px; line-height: 25px; text-align: center; border-collapse: collapse;">
                        <tr style="width:100%;height: 30px;border:1px solid black;">
                            <td style="border:1px solid black;">序号</td>
                            <td style="border:1px solid black;">物料编码</td>
                            <td style="border:1px solid black;">物料名称</td>
                            <td style="border:1px solid black;">规格型号</td>
                            <td style="border:1px solid black;">发货仓库</td>
                            <td style="border:1px solid black;">备注</td>

                        </tr>';

        $i = 1;
        foreach ($materialData as $k => $v){
            $html .= '<tr style="width:100%;height: 30px;border-style: 1px solid #999">
                            <td style="border:1px solid black;">' . $i . '</td>
                            <td style="border:1px solid black;">' . $v["product_no"] . '</td>
                            <td style="border:1px solid black;">' . $v["product_number"] . '</td>
                            <td style="border:1px solid black;">' . $v["product_name"] . '</td>
                            <td style="border:1px solid black;">' . $v["repertory_name"] . '</td>
                            <td style="border:1px solid black;">' . $v["tips"] . '</td>
                        </tr>';
            $i++;
        }

        $html .= '</table>
                    <table style="margin-top: 10px; min-height: 25px; line-height: 25px;width: 100%">
                        <tr style="width:100%;height: 30px;">
                            <td width="10%">审核：</td>
                            <td width="22%">' . $baseMsg["audit_name"] . '</td>
                            <td width="10%">记账：</td>
                            <td width="22%">' . $baseMsg["account_name"] . '</td>
                            <td width="10%">发货：</td>
                            <td width="22%">' . $baseMsg["send_name"] . '</td>
                        </tr>
                        <tr style="width:100%;height: 30px;">
                            <td width="10%">领料：</td>
                            <td width="22%">' . $baseMsg["collect_name"] . '</td>
                            <td width="10%">制单：</td>
                            <td width="22%">' . $baseMsg["create_name"] . '</td>
                            <td width="10%">业务员：</td>
                            <td width="22%">' . $baseMsg["business_name"] . '</td>
                        </tr>
                    </table>
                </div>';

        $mpdf->WriteHTML($html);
        $fileName = "生产领料出库单". '_' . $repId . '.pdf';

        // 1.保存至本地Excel表格
        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/PDF/";
        if (!file_exists($rootPath)) {
            mkdir($rootPath, 0777,true);
        }
//        $mpdf->Output($rootPath . $fileName, true);  // 当直接调用接口，能够下载文件，但是不知道为什么使用ajax回调就无法下载
        $mpdf->Output($rootPath . $fileName, "f");  // 保存文件至服务器

        $printModel = new PrintingLogModel();
        $printRes = $printModel->addPrintData($baseMsg['id']);
        if(!$printRes){
            return false;
        }

        return UPLOAD_ROOT_PATH . "/PDF/" . $fileName;
    }

    /**
     * @param $baseMsg
     * @param $materialData
     * @param $repId
     * @return string
     */
    public function printingToPdfEx($baseMsg, $materialData){
//        Vendor('mpdf.mpdf');
//        //设置中文编码
//        $mpdf=new \mPDF('zh-cn','216mm 93mm', 0, '宋体', 0, 0 ,0,0,0,0);
//        $mpdf->useAdobeCJK = true;
//
//        $html = '<html>
//                <head>
//                    <meta charset="utf-8">
//                    <style>
//                        @page one{
//                            size: 216mm 93mm;
//                        }
//                        .onePage {
//                            height: 93mm;
//                            width: 216mm;
//                        }
//                        .oneBill{
//                            height: 93mm;
//                            width: 216mm;
//                            padding: 0 2mm 0 2mm;
//                        }
//                        .title {
//                            width: 100%;
//                            text-align: center;
//                            font-size: 17px;
//                        }
//                        .baseMsg {
//                            min-height: 25px;
//                            line-height: 25px;
//                            width: 100%;
//                            font-size: 13px;
//
//                        }
//                        .materialMsg {
//                            border:1px solid black;
//                            width: 100%;
//                            min-height: 25px;
//                            line-height: 25px;
//                            text-align: center;
//                            border-collapse: collapse;
//                            font-size: 14px;
//                        }
//                        .materialMsg tr {
//                            width:100%;
//                            height: 30px;
//                            border:1px solid black;
//                        }
//                        .materialMsg td {
//                            border:1px solid black;
//                        }
//
//                        .userMsg {
//                            min-height: 25px;
//                            line-height: 25px;
//                            width: 100%;
//                            font-size: 13px;
//                        }
//
//                        .userMsg tr{
//                            width:100%;
//                            height: 30px;
//                        }
//
//                        .td1{
//                            width: 10%;
//                        }
//                        .td2{
//                            width: 15%;
//                        }
//                        .td3{
//                            width: 10%;
//                        }
//                        .td4{
//                            width: 15%;
//                        }
//                        .td5{
//                            width: 10%;
//                        }
//                        .td6{
//                            width: 15%;
//                        }
//                        .td7{
//                            width: 6%;
//                        }
//                        .td8{
//                            width: 15%;
//                        }
//                        .td9{
//                            width: 10%;
//                        }
//                        .td10{
//                            width: 15%;
//                        }
//                        .td11{
//                            width: 10%;
//                        }
//                        .td12{
//                            width: 15%;
//                        }
//                        .td13{
//                            width: 10%;
//                        }
//                        .td14{
//                            width: 30%;
//                        }
//                    </style>
//                </head>';
        $html = "";

        foreach ($materialData as $ke => $va){
            $material = [];  // 存放所有物料信息的
            $materialV = []; // 存放中间物料信息的
            $i = 1;
            foreach ($va as $k => $v){
                if($i < 5){
                    $materialV[] = $v;
                    $i++;
                } else {
                    $materialV[] = $v;
                    $material[] = $materialV;
                    $materialV = [];
                    $i = 1;
                }
            }
            if(!empty($materialV)){
                $material[] = $materialV;
            }

            foreach ($material as $key => $value){
                $html .= '<div class="onePage" style="page:one">
                        <div class="oneBill">
                            <div class="title">生产领料出库单</div>
                            <table class="baseMsg">
                                <tr class="userMsg">
                                    <td width="10%">生产单号：</td>
                                    <td width="15%">' . $baseMsg["production_code"] . '</td>
                                    <td width="10%">生产线：</td>
                                    <td width="15%">' . $baseMsg["picking_purpose"] . '</td>
                                    <td width="10%">编号：</td>
                                    <td width="15%">' . $baseMsg["stock_out_id"] . '</td>
                                    <td width="10%">日期：</td>
                                    <td width="15%">' . date("Y/m/d",$baseMsg["create_time"]) . '</td>
                                </tr>
                                <tr class="userMsg">
                                    <td width="10%">领料部门：</td>
                                    <td width="15%">' . $baseMsg["picking_dept_name"] . '</td>
                                    <td width="10%">领料类型：</td>
                                    <td width="15%">' . StockOutOtherApplyModel::$pickingType[$baseMsg["picking_kind"]] . '</td>
                                    <td width="10%">领料用途：</td>
                                    <td width="30%" colspan="3">' . $baseMsg["picking_purpose"] . '</td>
                                </tr>
                            </table>
                            <table class="materialMsg">
                                <tr>
                                    <td>序号</td>
                                    <td>物料编码</td>
                                    <td>物料名称</td>
                                    <td>规格型号</td>
                                    <td>发货仓库</td>
                                    <td>备注</td>
                                </tr>';

                $i = 1;
                foreach ($value as $k => $v){
                    $html .= '<tr>
                            <td>' . $i . '</td>
                            <td>' . $v["product_no"] . '</td>
                            <td>' . $v["product_number"] . '</td>
                            <td>' . $v["product_name"] . '</td>
                            <td>' . $v["repertory_name"] . '</td>
                            <td style="border:1px solid black;">' . $v["tips"] . '</td>
                        </tr>';
                    $i++;
                }

                $html .=  '</table>
                        <table  class="baseMsg">
                            <tr class="userMsg">
                                <td width="10%">审核：</td>
                                <td width="22%">' . $baseMsg["audit_name"] . '</td>
                                <td width="10%">记账：</td>
                                <td width="22%">' . $baseMsg["account_name"] . '</td>
                                <td width="10%">发货：</td>
                                <td width="22%">' . $baseMsg["send_name"] . '</td>
                            </tr>
                            <tr class="userMsg">
                                <td width="10%">领料：</td>
                                <td width="22%">' . $baseMsg["collect_name"] . '</td>
                                <td width="10%">制单：</td>
                                <td width="22%">' . $baseMsg["create_name"] . '</td>
                                <td width="10%">业务员：</td>
                                <td width="22%">' . $baseMsg["business_name"] . '</td>
                            </tr>
                        </table>
                    </div>
                </div>';
            }
        }
        return $html;

//        $html .= '</html>';
//
//        $mpdf->WriteHTML($html);
//        $fileName = "生产领料出库单.pdf";
//
//        // 1.保存至本地Excel表格
//        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/PDF/";
//        if (!file_exists($rootPath)) {
//            mkdir($rootPath, 0777,true);
//        }
////        $mpdf->Output($rootPath . $fileName, true);  // 当直接调用接口，能够下载文件，但是不知道为什么使用ajax回调就无法下载
//        $mpdf->Output($rootPath . $fileName, "f");  // 保存文件至服务器
//
//        $printModel = new PrintingLogModel();
//        $printRes = $printModel->addPrintData($baseMsg['id']);
//        if(!$printRes){
//            return false;
//        }
//
//        return UPLOAD_ROOT_PATH . "/PDF/" . $fileName;
    }

    /**
     * 检查权限
     * @param $id
     * @param $type 1=>修改 2=>回退
     * @return array
     */
    public  function checkAuth($id, $type){
        $produceData = $this->where(['id' => $id])->find();

        switch ($type){
            case 1 :
                if($produceData['audit_status'] == self::TYPE_STOCK_QUALIFIED){
                    return [false, "当前领料出库单已出库，不能修改"];
                }
                if($produceData['create_id'] != session("staffId")){
                    return [false, "当前操作人不是制单人，不能修改"];
                }
                break;
            case 2 :
                if($produceData['audit_id'] != session("staffId")){
                    return [false, "当前操作人不是审核人，不能回退物料"];
                }
                break;
            default :
                return [false, "页面类型不明"];
                break;
        }
        return [true, "可以操作"];
    }


    /**
     * 获取出库单全部数据
     * @param $id
     * @param $type 1=>修改页面  2=>回退页面
     * @return array
     */
    public function getStockOutAllMsg($id, $type){
        $produceData = $this->where(['id' => $id])->find();
        switch ($type){
            case 1 :
                // 修改页面 =》 获取除当前记录之外的出库信息
                $map['so.id'] = ['neq', $id];
                break;
            case 2 :
                // 回退页面 =》 获取全部记录的信息
                $map = [];
                break;
            default :
                return [false, "页面类型不明",[]];
                break;
        }

        // 获取当前出库单中的物料信息
        $materialData = self::getMaterialMsg($id);


        $productionOrderModel = new ProductionOrderModel();
        $productionOrderData = $productionOrderModel->getOrderBaseMsgById($produceData['source_id']);

        // 获取当前生产领料单源单所有物料信息数据
        $orderMaterialData = $productionOrderModel->getMaterialMsg($produceData['source_id'], $map);

        // 替代物料信息
        $productIdArr= array_column($orderMaterialData, 'product_id');
        if(empty($productIdArr)){
            return [false,"当前生产单不存在bom或bom内没有物料",[]];
        }
        $replaceModel = new MaterialSubstituteModel();
        $replaceData = $replaceModel->findSubstituteByProductId($productIdArr, 2);

        $orderMaterialExData = [];
        // 处理源单全部的物料信息
        foreach ($orderMaterialData as $k => &$v){
            foreach ($replaceData as $key => $item){
                if($v['product_id'] == $item['product_id']){
                    $v['replace_data'][] = $item;
                }
            }
            $orderMaterialExData[$v['product_id']] = $v;
        }
        unset($v);

        $productionOrderData['used_num'] = 0;
        $produceData['num'] = 0;
        // 处理出库单的物料信息
        foreach ($materialData as $key => &$value){
            $value['replace_data'][] = ['substituted_id' => $value['product_id'], 'product_name' => $value['product_name'], "product_number" => $value['product_number'], 'product_no' => $value['product_no']];
            foreach ($replaceData as $ke => $va){
                if($value['product_id'] == $va['product_id']){
                    $value['replace_data'][] = $va;
                }
            }

            $value['used_num'] = $orderMaterialExData[$value['product_id']]['used_num'];
            $value['total_num'] = $orderMaterialExData[$value['product_id']]['total_num'];
            $value['one_num'] = $orderMaterialExData[$value['product_id']]['one_num'];
            $productionOrderData['used_num'] = $orderMaterialExData[$value['product_id']]['used_num'];
            $produceData['num'] = $value['num'] / $orderMaterialExData[$value['product_id']]['one_num'];
        }
        unset($value);

        // 人员信息
        $staffModel = new StaffModel();
        $staffData = $staffModel->field("id,name")->select();

        // 部门信息
        $deptModel = new DeptModel();
        $deptData = $deptModel->field("id,name")->select();

//            仓库名称map  从crm_repertorylist 表中查出
        $repertoryListModel = new RepertorylistModel();
        $repMap = $repertoryListModel->getStockOutList();

        $data = [
            'repMap'    => $repMap,  // 其他出库名称
            'staffData' => $staffData, // 公司员工map
            'deptData' => $deptData,    // 部门map
            'productionOrderData' => $productionOrderData, // 订单基本信息
            'orderMaterialData' => $orderMaterialData, // bom物料信息
            'pickingType' => StockOutOtherApplyModel::$pickingType,                  // 领料类型
            'produceData' => $produceData,        // 出库单基本信息
            'materialData' => $materialData,        // 当前出库单物料信息
            "cate_id"      => StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL,   // 当前出库单类型id
            "cate_name"    => StockOutRecordModel::$stockOutType[StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL], // 出库类型名称
        ];
        return [true,"数据返回成功",$data];
    }

    /**
     * 回退全部物料
     * @param $id 出库单ID
     * @param $num 退回的数量 0=>退回全部
     * @return array
     */
    public function rollBackAllMaterial($id, $num = 0){
        try{
            $this->startTrans();
            $stockOutData = $this->where(['id' => $id])->find();
            $productionOrderModel = new ProductionOrderModel();
            // 获取当前生产领料单源单所有物料信息数据
            $orderMaterialData = $productionOrderModel->getMaterialMsg($stockOutData['source_id']);
            $orderMaterialDataMap = reformArray($orderMaterialData, 'product_id');

            // 获取当前出库单中的物料信息
            $materialData = self::getMaterialMsg($id);

            // todo 获task里面对应的数量


            $stockModel = new StockModel();
            $stockMaterialModel = new StockMaterialModel();
            $recordModel = new StockOutRecordModel();
            $replaceModel = new BomReplaceLogModel();
            foreach ($materialData as $k => $v){
                $orderMaterial = $orderMaterialDataMap[$v['product_id']];
                $totalNum = $orderMaterial['total_num'];  // 总计划数量
                $usedNum = $orderMaterial['used_num'];    // 已出库数量
                $oneNum = $orderMaterial['one_num'];      // bom对应一个配件所需的数量


                if(empty($num)){
                    $delNum = $v['num'];

                    // todo 判断总计划数与task数量 和 剩余出库数量的差值
                    $usedNumber = $usedNum - ($delNum/$oneNum);  // 剩下出库数量



                    // 删除出库单物料
                    $stockMaterialRes = $stockMaterialModel->where(['id' => $v['id']])->setField(['is_del' => StockMaterialModel::IS_DEL, 'update_time' => time()]);
                    if($stockMaterialRes === false){
                        $this->rollback();
                        return dataReturn($stockMaterialModel->getError(),400);
                    }

                    // 删除出库记录
                    $recordRes = $recordModel->where(['source_id' => $id, "is_del" => StockMaterialModel::NO_DEL, 'product_id' => $v['substituted_id']])->setField(['is_del' => StockOutRecordModel::IS_DEL, 'update_time' => time()]);
                    if($recordRes === false){
                        $this->rollback();
                        return dataReturn($recordModel->getError(),400);
                    }

                    // 删除替换履历表重的数据
                    $replaceRes = $replaceModel->where(['produce_id' => $id, "is_del" => StockMaterialModel::NO_DEL, 'product_id' => $v['product_id']])->setField(['is_del' => StockOutRecordModel::IS_DEL, 'update_time' => time()]);
                    if($replaceRes === false){
                        $this->rollback();
                        return dataReturn($replaceModel->getError(),400);
                        break;
                    }

                }else {
                    $delNum = $num * $oneNum;  // 当前物料退回多少
                    $stockNum = $v['num'] - $delNum; // 修改后数量

                    if($stockNum < 0){
                        $this->rollback();
                        return dataReturn("退回套数不等大于或等于当前已出库套数",400);
                        break;
                    }

                    // todo 判断总计划数与task数量 和 剩余出库数量的差值
                    $usedNumber = $usedNum - ($delNum/$oneNum);  // 剩下出库数量



                    // 修改出库单物料信息
                    $stockMaterialRes = $stockMaterialModel->where(['id' => $v['id']])->setField(['num' => $stockNum, 'update_time' => time()]);
                    if($stockMaterialRes === false){
                        $this->rollback();
                        return dataReturn($stockMaterialModel->getError(),400);
                    }

                    // 修改出库单记录
                    $recordRes = $recordModel->where(['source_id' => $id, "is_del" => StockMaterialModel::NO_DEL, 'product_id' => $v['substituted_id']])->setField(['num' => $stockNum, 'update_time' => time()]);
                    if($recordRes === false){
                        $this->rollback();
                        return dataReturn($recordModel->getError(),400);
                        break;
                    }
                }

                // 修改库房记录
                list($code, $msg) = $stockModel->rollBackStockNum($v['substituted_id'], $v['rep_pid'], $delNum);
                if($code != 0){
                    $this->rollback();
                    return dataReturn($msg, 400);
                    break;
                }
            }

            // 判断是否对源单进行操作
            $productionOrderData = $productionOrderModel->where(['id' => $stockOutData['source_id']])->find();
            if($productionOrderData['stock_status'] == ProductionOrderModel::TYPE_OUT_ALL){
                $productionOrderRes = $productionOrderModel->where(['id' => $stockOutData['source_id']])->setField(['stock_status' => ProductionOrderModel::TYPE_OUT_OF_REP]);
                if($productionOrderRes === false){
                    $this->rollback();
                    return dataReturn($productionOrderModel->getError(),400);
                }
            }

            // 如果是回退整个单子需要删除出库单
            if(empty($num)){
                $stockOutRes = $this->where(['id' => $id])->setField(['is_del' => self::IS_DEL, 'update_time' => time()]);
                if($stockOutRes === false){
                    $this->rollback();
                    return dataReturn($this->getError(),400);
                }
            }

            $this->commit();
            return dataReturn("回退全部物料成功", 200);
        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(), 400);
        }
    }

}