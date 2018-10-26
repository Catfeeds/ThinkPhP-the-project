<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/8/6
 * Time: 上午10:06
 */
namespace Dwin\Model;


use Think\Model;

class StockOutOrderformModel extends Model{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

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


    const IS_DEL = 1; // 已被删除
    const NO_DEL = 0; // 有效

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

    // todo 确定那些字段是否可以为空，做判空处理
    public function getAddData($params){
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, '没有提交新增数据'];
        }

        if(empty($data['stock_out_id']) || empty($data['express_no']) || empty($data['choose_no']) || empty($data['source_id']) || empty($data['id'])){
            return [-2, "数据未填写完整"];
        }

        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['create_name']    = session('nickname');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');
        $data['update_name']    = session('nickname');
        $data['audit_status']    = self::TYPE_QUALIFIED;
        $data['source_kind']  = StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM;

        $data = $this->create($data);
        if ($data) {
            $res = $this->add($data);
            if(!$res){
                return [-2, $this->getError()];
            }
            return [0, $res, '新增成功'];
        } else {
            return [-2, $this->getError()];
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

        if(isset($editData['picking_dept_id']) || isset($editData['picking_dept_name']) || isset($editData['picking_kind'])){
            return [-2, [], "修改了不能修改的数据"];
        }

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
    /**
     * 修改销售类型出库单基本信息
     * @param $data
     * @return array
     */
    public function modifyOrderform($data)
    {
        try {
            list($code, $editData, $msg) = $this->getEditData($data);

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
     * 生成销售类型出库单
     * @param $orderformMsg
     * @param $materialMsg
     * @return array
     */
    public function createOrderform($orderformMsg, $materialMsg){
        $this->startTrans();

        $idArr = array_unique(array_column($materialMsg, "product_id"));
        if(count($idArr) != count($materialMsg)){
            $this->rollback();
            return dataReturn("当前添加的物料有重复的", 400);
        }

        list($code, $msg) = self::getAddData($orderformMsg);
        if ($code != 0) {
            $this->rollback();
            return dataReturn($msg, self::$failStatus);
        }

        $productModel = new OrderproductModel();
        $productDataArr = $productModel->getOrderProductMsgById($orderformMsg['source_id']);

        $productData = reformArray($productDataArr, "product_id");


        $materialModel = new StockMaterialModel();
        // 新增出库单物料信息
        foreach ($materialMsg as $key => $item){
            if(!isset($productData[$item['product_id']])){
                $this->rollback();
                return dataReturn("物料" . $item['product_number'] . "不在申请单中",self::$failStatus);
            }

            if(empty($productData[$item['product_id']]['used_num'])){
                $surplusNum = $productData[$item['product_id']]['product_num'];
            }else {
                $surplusNum = $productData[$item['product_id']]['product_num'] - $productData[$item['product_id']]['used_num'];
            }
            // 因为销售出库单可以有多张对应同一个接口 所以得判断当前添加得物料是否超过当前订单得数量
            if($surplusNum < $item['num']){
                $this->rollback();
                return dataReturn("物料" . $item['product_number'] . "总出库数量不能超过订单剩余数量" . $productData[$item['product_id']]['surplus_num'],self::$failStatus);
            }

            list($code, $data, $msg) = self::addStockMaterial($item, $orderformMsg['id']);

            if($code == 0 ){
                $statusstr = self::$successStatus;
                $materialData[] = $data;
            }
            if($code == -2){
                $this->rollback();
                return dataReturn($msg, self::$failStatus);
            }
        }

        if(empty($statusstr)){
            $this->rollback();
            return dataReturn($msg, self::$failStatus);
        }
        $res = $materialModel->addAll($materialData);
        if($res === false){
            $this->rollback();
            return dataReturn($materialModel->getError(), self::$failStatus);
        }

        // 直接写record
        $recordModel = new StockOutRecordModel();
        list($code, $message) = $recordModel->autoSaveRecordForOrderForm($orderformMsg['id'], StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM);
        if($code != 0){
            $this->rollback();
            return dataReturn($message, self::$failStatus);
        }

        $orderFormModel = new OrderformModel();
        $orderFormData = $orderFormModel->find($orderformMsg['source_id']);
        if($orderFormData['stock_status'] != OrderformModel::TYPE_OUT_OF_REP){
            // 修改当前源单的出库状态
            $orderFormRes = $orderFormModel->where(['id' => $orderformMsg['source_id']])->setField(['stock_status' => OrderformModel::TYPE_OUT_OF_REP]);
            if($orderFormRes === false){
                $this->rollback();
                return dataReturn("修改源单出库单状态失败", 400);
            }
        }

        $this->commit();
        return dataReturn('添加销售类型出库单成功', self::$successStatus);
    }


    /**
     * 修改销售类型出库单全部信息
     * @param $postData
     * @return array
     */
    public function editOrderform($postData)
    {
        try {
            $this->startTrans();

            $materialModel = new StockMaterialModel();
            $productModel = new OrderproductModel();
            $statusStr = '';

            if(!empty($postData['edit_material'])){
                // 获取除当前记录之外的入库信息  因为做数量判断，需要减去当前订单中当前物料的数量
                $map['so.id'] = ['neq', $postData['orderform']['id']];
                $product = $productModel->getOrderProductMsgById($postData['orderform']['source_id'], $map);
                $productArr = reformArray($product, "product_id");

                foreach ($postData['edit_material'] as $key => $value){
                    if(empty($productArr[$value['product_id']]['used_num'])){
                        $surplusNum = $productArr[$value['product_id']]['product_num'];
                    }else {
                        $surplusNum = $productArr[$value['product_id']]['product_num'] - $productArr[$value['product_id']]['used_num'];
                    }
                    if($surplusNum < $value['num']){
                        $this->rollback();
                        return dataReturn("物料" . $value['product_number'] . "总出库数量不能超过订单剩余数量，当前剩余数量为" . $productArr[$value['product_id']]['surplus_num'],self::$failStatus);
                    }else{
                        list($editCode, $materiaDataOne, $msg) = self::editMaterial($postData['orderform']['id'], $value);
                        if($editCode == 0){
                            $statusStr = self::$successStatus;
                            $saveRst = $materialModel->save($materiaDataOne);
                            if ($saveRst === false) {
                                $this->rollback();
                                return dataReturn($materialModel->getError(), self::$failStatus);
                                break;
                            }
                        }

                        if($editCode == -2){
                            $this->rollback();
                            return dataReturn($msg, self::$failStatus);
                            break;
                        }

                    }
                }
            }

            if (!empty($postData['new_material'])){
                $productData = $productModel->getOrderProductMsgById($postData['orderform']['id']);
                $materialModel = new StockMaterialModel();

                $productDataArr = reformArray($productData, "product_id");

                // 检查当前销售出库单是否有相同的物料
                $check = $materialModel->where(['product' => ['in', array_column($postData['new_material'], "product_id")]]);
                if(!empty($check)){
                    $this->rollback();
                    return dataReturn("新增的物料与当前出库单已有的重复，请重新编辑", 400);
                }

                foreach ($postData['new_material'] as $key => $item){
                    if(!isset($productDataArr[$item['product_id']])){
                        $this->rollback();
                        return dataReturn("物料" . $item['product_number'] . "不在申请单中",self::$failStatus);
                    }

                    if(empty($productData[$item['product_id']]['used_num'])){
                        $surplusNum = $productData[$item['product_id']]['product_num'];
                    }else {
                        $surplusNum = $productData[$item['product_id']]['product_num'] - $productData[$item['product_id']]['used_num'];
                    }

                    // 因为销售出库单可以有多张对应同一个接口 所以得判断当前添加得物料是否超过当前订单得数量
                    if($surplusNum < $item['num']){
                        $this->rollback();
                        return dataReturn("物料" . $item['product_number'] . "总出库数量不能超过订单剩余数量，当前剩余数量为" . $productDataArr[$item['product_id']]['surplus_num'],self::$failStatus);
                    }



                    list($newCode, $data, $msg) = self::addStockMaterial($item, $postData['orderform']['id']);

                    if($newCode == 0 ){
                        $statusstr = self::$successStatus;
                        $materialData[] = $data;
                    }
                    if($newCode == -2){
                        $this->rollback();
                        return dataReturn($msg, self::$failStatus);
                    }
                }

                // 同步修改出库记录和库存信息
                $productIdArr = array_column($postData['new_material'], 'product_id');
                $filter["m.product_id"] = ['in' , $productIdArr];
                $recordModel = new StockOutRecordModel();
                list($recordCode, $message) = $recordModel->autoSaveRecordForOrderForm($postData['orderform']['id'], StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM, $filter);
                if($recordCode != 0){
                    $this->rollback();
                    return dataReturn($message, self::$failStatus);
                }
            }

            list($msg, $code) = $this->modifyOrderform($postData['orderform']);
            if ($code == -2) {
                $this->rollback();
                return dataReturn($msg, self::$failStatus);
            }
            if($code == -1 && $statusStr == ''){
                $this->rollback();
                return dataReturn('数据未发生修改', 400);
            }

            $this->where(['id' => $postData['apply']['id']])->setField(["audit_status" => self::TYPE_NOT_AUDIT]);
            $this->commit();
            return dataReturn('ok', self::$successStatus);
        } catch (\Exception $exception) {
            return dataReturn($exception->getMessage(), self::$failStatus);
        }
    }

    /**
     * 获取出库单单列表页信息
     */
    public function getList($condition, $start, $length, $order){
        $map['soo.is_del'] = ['eq', self::NO_DEL];
        $recordMap = $map;
        if(strlen($condition) != 0){
            $where['soo.stock_out_id'] = ['like', "%" . $condition . "%"];
            $where['soo.picking_dept_name'] = ['like', "%" . $condition . "%"];
            $where['soo.create_name']=['like', "%" . $condition . "%"];
            $where['_logic'] = 'OR';
            $recordMap['_complex'] = $where;
        }

        $data =  $this->alias("soo")
            ->field("soo.*,co.order_id, co.cus_id")
            ->join("left join crm_orderform co on co.id = soo.source_id and co.is_del = " . OrderformModel::NO_DEL)
            ->limit($start, $length)
            ->where($recordMap)
            ->order($order)
            ->select();
        /** 后台传输局到前台
        @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
        $count = $this->alias("soo")
            ->join("left join crm_orderform co on co.id = soo.source_id and co.is_del = " . OrderformModel::NO_DEL)
            ->where($map)
            ->count();
        $recordsFiltered = $this->alias("soo")
            ->join("left join crm_orderform co on co.id = soo.source_id and co.is_del = " . OrderformModel::NO_DEL)
            ->where($recordMap)
            ->count();

        return [$data,$count, $recordsFiltered];
    }

    /**
     * @param $id
     * @param array $map
     * @return mixed
     */
    public function getDataById($id, $map = []){
        $map['is_del'] = ['eq', self::NO_DEL];
        return $this->where($map)->find($id);
    }

    /**
     * 删除整个出库单
     * @param $id
     * @return array
     */
    public function delOrderform($id){
        // 判断当前出库单在出库记录里面是否有出库记录
        $recordModel = new StockOutRecordModel();
        $map['status'] = ['eq', StockOutRecordModel::TYPE_QUALIFIED];
        $recordData = $recordModel->getRecordByStockId($id, $map);
        if(!empty($recordData)){
            $num = array_sum(array_column($recordData, "num"));
            if(!empty($num)){
                return dataReturn("当前出库单已出库，不可以删除", 400);
            }
        }

        $orderformData = $this->find($id);
        if($orderformData['audit_status'] != self::TYPE_STOCK_QUALIFIED){
            return dataReturn("当前出库单已出库，不可以删除", 400);
        }
        try{
            $this->startTrans();
            $res = $this->where(['id' => $id])->setField(['is_del' => self::IS_DEL]);
            if(!$res){
                $this->rollback();
                return dataReturn("删除失败", 400);
            }

            $materialModel = new StockMaterialModel();
            $materialModel->where(['source_id' => $id])->setField(['is_del' => StockMaterialModel::IS_DEL, "update_time" => time()]);

            // 删除对应出库记录
            list($code, $msg) = $recordModel->autoDelStockOutRecordByStockIdMany($id, StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM, $recordModel->getRecordByStockId($id));
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
     * 删除销售出库单一个物料
     * @param $materialId
     * @return array
     */
    public function delStockOutOrderformMaterial($materialId){
        $materialModel = new StockMaterialModel();

        // 判断当前出库单在出库记录里面是否有出库记录
        $recordModel = new StockOutRecordModel();
        $map['status'] = ['eq', StockOutRecordModel::TYPE_QUALIFIED];
        $recordData = $recordModel->getRecordByMaterialId($materialId, $map);
        if(empty($recordData)){
            return dataReturn("当前出库单物料已出库，不可以删除", 400);
        }

        try {
            $this->startTrans();

            $materialModel->where(['id' => $materialId])->setField(['is_del' => StockMaterialModel::IS_DEL, "update_time" => time()]);

            // 删除对应出库记录
            list($code, $msg) = $recordModel->autoDelStockOutRecordByStockIdOne(StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM, $recordModel->getRecordByMaterialId($materialId));
            if($code != 0){
                $this->rollback();
                return dataReturn($msg, 400);
            }

            $this->commit();
            return dataReturn("删除物料信息成功", 200);
        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(), 400);
        }
    }

    /**
     * create by chendd 新增物料信息
     * @param $postData
     * @return array|bool
     */
    public function addStockMaterial($postData, $orderformId)
    {
        $materialModel = new StockMaterialModel();
        $data = $materialModel->getNewField($postData);
        if(empty($data)){
            return [-1, [], "未添加单据的基本信息"];
        }
        if(empty($data['product_id']) || empty($data['product_no']) || empty($data['num']) || empty($data['rep_pid'])){
            return [-2, [], "请将数据填写完整"];
        }
        $data['source_id'] = $orderformId;

        $data['type'] = StockMaterialModel::TYPE_STOCK_OUT;
        $data['create_time'] = time();
        $data['update_time'] = time();
        $rst = $materialModel->create($data);
        if($rst){
            return [0, $rst, "实例化单据基本信息成功"];

        }else {
            return [-2, [], $materialModel->getError()];
        }
    }

    /**
     * 修改出库单物料信息
     * @param $params
     * @param $stockId
     * @return array
     */
    public function editMaterial($stockId, $params){
        $materialModel = new StockMaterialModel();
        $data = $materialModel->getNewField($params);
        if (empty($data)) {
            return [-1, [], "无修改数据提交"];
        }

        $oldData = $materialModel->field("*")->find($data['id']);


        $stockRecordModel = new StockOutRecordModel();
        $recordData = $stockRecordModel->getNumByMaterialId($oldData['id'], StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM);

        if($data['num'] < $recordData['num']){
            return [-2, [], "修改后的出库库数量不能低于已出库数量"];
        }

        $editData = $materialModel->compareData($oldData, $data);

        // 修改出库单时，同步修改库存信息
        $stockModel = new StockModel();
        $recordModel = new StockOutRecordModel();
        if(isset($editData['rep_pid'])){
            $num = isset($editData['num']) ? $editData['num'] : $oldData['num'];

            list($code, $msg) = $stockModel->updateOrderFormStockOutToUpdateStock($oldData['product_id'], $oldData['rep_pid'], $editData['rep_pid'], $oldData['num'], $num);
            if($code != 0){
                return [-2,[], $oldData['product_no'] . $msg];
            }

            $recordModel->where(['source_id' => $stockId, "product_id" => $oldData['product_id'], "is_del" => self::NO_DEL])->setField(['repertory_id' => $editData['rep_pid'], 'num' => $num]);

        }

        if(isset($editData['num']) && !isset($editData['rep_pid'])){
            $num = $editData['num'] - $oldData['num'];

            // crm_stock处理待出库数量
            list($code, $msg) = $stockModel->orderFormStockOutToUpdateStock($oldData['product_id'], $oldData['rep_pid'], $num);
            if($code != 0){
                return [-2, [], $oldData['product_no'] . $msg];
            }
            $recordModel->where(['source_id' => $stockId, "product_id" => $oldData['product_id'], "is_del" => self::NO_DEL])->setField(['num' => $num]);

        }

        if ($editData === false) {
            return [-1, [], "无数据修改"];
        }else if($editData == -1) {
            return [-2, [], "只能对出库数量进行修改"];
        } else {
            return [0, $editData, "数据实例化成功"];
        }
    }

    public function getAllMaterialMsgByOrderId($orderId, $map = []){
        $map['soo.source_id'] = ['eq', $orderId];
        $map['soo.is_del'] = ['eq', self::NO_DEL];
        return $this->alias("soo")
            ->field("soo.stock_out_id, r.product_id,m.product_name,m.product_number,r.num, r.repertory_id,soo.audit_name,soo.send_name,soo.audit_status,cs.stock_number,cs.o_audit,cs.out_processing, from_unixtime(r.update_time) update_time, r.create_time, soo.express_no")
            ->join("left join crm_stock_out_record r on r.source_id = soo.id and r.is_del = " . StockOutRecordModel::NO_DEL)
            ->join("left join crm_material m on m.product_id = r.product_id")
            ->join("left join crm_stock cs on cs.product_id = r.product_id and cs.warehouse_number = r.repertory_id")
            ->where($map)
            ->select();
    }

    /**
     * 获取出库单基本信息 目前为了是打印出库单
     * @param $id
     * @param array $map
     * @return mixed
     */
    public function getMsgForPrinting($id, $map = []){
        $map['soo.id'] = ['eq', $id];
        $data = $this->alias("soo")
            ->field("soo.*, co.cus_name, co.order_id")
            ->join("left join crm_orderform co on co.id = soo.source_id")
            ->where($map)
            ->find();
        return $data;
    }

    /**
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

        $html = '<div style="margin: 0 6%">
                    <div style="width: 100%;text-align: center;">销售出库单</div>
                    <table style="margin-top: 20px; min-height: 25px; line-height: 25px;width: 100%">
                        <tr style="width:100%;height: 30px;">
                            <td width="10%">客户：</td>
                            <td width="35%">' . $baseMsg["cus_name"] . '</td>
                            <td width="12%">订单编号：</td>
                            <td width="38%" ="5">' . $baseMsg["order_id"] . '</td>
                        </tr>
                        <tr style="width:100%;height: 30px;">
                            <td width="10%">日期：</td>
                            <td width="35%">' . date("Y/m/d",$baseMsg["create_time"]) . '</td>
                            <td width="12%">出库单编号：</td>
                            <td width="38%">' . $baseMsg["stock_out_id"] . '</td>
                        </tr>
                    </table>
                    <table style="margin-top:10px;border:1px solid black;width: 100%; min-height: 25px; line-height: 25px; text-align: center; border-collapse: collapse;">
                        <tr style="width:100%;height: 30px;border:1px solid black;">
                            <td style="border:1px solid black;">序号</td>
                            <td style="border:1px solid black;">物料编码</td>
                            <td style="border:1px solid black;">物料名称</td>
                            <td style="border:1px solid black;">规格型号</td>
                            <td style="border:1px solid black;">发货仓库</td>
                        </tr>';

        $i = 1;
        foreach ($materialData as $k => $v){
            $html .= '<tr style="width:100%;height: 30px;border-style: 1px solid #999">
                            <td style="border:1px solid black;">' . $i . '</td>
                            <td style="border:1px solid black;">' . $v["product_no"] . '</td>
                            <td style="border:1px solid black;">' . $v["product_number"] . '</td>
                            <td style="border:1px solid black;">' . $v["product_name"] . '</td>
                            <td style="border:1px solid black;">' . $v["repertory_name"] . '</td>
                        </tr>';
            $i++;
        }

        $html .= '</table>
                    <table style="margin-top: 10px; min-height: 25px; line-height: 25px;width: 100%">
                        <tr style="width:100%;height: 30px;">
                            <td width="10%">制单：</td>
                            <td width="22%">' . $baseMsg["create_name"] . '</td>
                            <td width="10%">保管：</td>
                            <td width="22%">' . $baseMsg["keep_name"] . '</td>
                            <td width="10%">发货：</td>
                            <td width="22%">' . $baseMsg["send_name"] . '</td>
                        </tr>
                    </table>
                </div>';

        $mpdf->WriteHTML($html);
        $fileName = "销售出库单". '_' . $repId . '.pdf';

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
//                            width: 45%;
//                        }
//                        .td3{
//                            width: 10%;
//                        }
//                        .td4{
//                            width: 38%;
//                        }
//                        .td5{
//                            width: 10%;
//                        }
//                        .td6{
//                            width: 45%;
//                        }
//                        .td7{
//                            width: 12%;
//                        }
//                        .td8{
//                            width: 38%;
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
                }
                if($i == 5){
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
                            <div class="title">销售出库单</div>
                            <table class="baseMsg">
                                <tr class="userMsg">
                                    <td width="6%">客户:</td>
                                    <td width="44%">' . $baseMsg["cus_name"] . '</td>
                                    <td width="6%">用途:</td>
                                    <td width="44%">' . $baseMsg["order_id"] . '</td>
                                </tr>
                                <tr class="userMsg">
                                    <td width="6%">日期:</td>
                                    <td width="44%">' . date("Y/m/d",$baseMsg["create_time"]) . '</td>
                                    <td width="15%">出库单编号:</td>
                                    <td width="35%">' . $baseMsg["stock_out_id"] . '</td>
                                </tr>
                            </table>
                            <table class="materialMsg">
                                <tr>
                                    <td>序号</td>
                                    <td>物料编码</td>
                                    <td>物料名称</td>
                                    <td>规格型号</td>
                                    <td>发货仓库</td>
                                </tr>';

                $i = 1;
                foreach ($value as $k => $v){
                    $html .= '<tr>
                            <td>' . $i . '</td>
                            <td>' . $v["product_no"] . '</td>
                            <td>' . $v["product_number"] . '</td>
                            <td>' . $v["product_name"] . '</td>
                            <td>' . $v["repertory_name"] . '</td>
                        </tr>';
                    $i++;
                }

                $html .=  '</table>
                        <table  class="baseMsg">
                            <tr class="userMsg">
                                <td width="10%">制单：</td>
                                <td width="22%">' . $baseMsg["create_name"] . '</td>
                                <td width="10%">保管：</td>
                                <td width="22%">' . $baseMsg["keep_name"] . '</td>
                                <td width="10%">发货：</td>
                                <td width="22%">' . $baseMsg["send_name"] . '</td>
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
//        $fileName = "销售出库单.pdf";
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
        $stockData = $this->where(['id' => $id])->find();

        switch ($type){
            case 1 :
                if($stockData['audit_status'] == self::TYPE_STOCK_QUALIFIED){
                    return [false, "当前领料出库单已出库，不能修改",[]];
                }
                if($stockData['create_id'] != session("staffId")){
                    return [false, "当前操作人不是制单人，不能修改",[]];
                }
                break;
            case 2 :
                if($stockData['audit_id'] != session("staffId")){
                    return [false, "当前操作人不是审核人，不能回退物料",[]];
                }
                break;
            default :
                return [false, "页面类型不明",[]];
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
        $productModel = new OrderproductModel();
        $orderformData = self::getDataById($id);

        $formModel = new OrderformModel();
        $formData = $formModel->where(["is_del" => OrderformModel::NO_DEL, 'id' => $orderformData['source_id']])->find();

        $materialModel = new StockMaterialModel();
        $materialData = $materialModel->selectByStockId($id);

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

        $productData = $productModel->getOrderProductMsgById($orderformData['source_id'],$map);
        $productArr = reformArray($productData, "product_id");

        // 将当前出库剩余数量放入数组当中
        foreach ($materialData as $key => &$value){
            if(isset($productArr[$value['product_id']])){
                $value['surplus_num'] = empty($productArr[$value['product_id']]['surplus_num']) ? 0 : $productArr[$value['product_id']]['surplus_num'];
                $value['used_num'] = empty($productArr[$value['product_id']]['used_num']) ? 0 : $productArr[$value['product_id']]['used_num'];
                $value['product_num'] = empty($productArr[$value['product_id']]['product_num']) ? 0 : $productArr[$value['product_id']]['product_num'];
            }
        }
        unset($value);

        $staffModel = new StaffModel();
        $staffData = $staffModel->field("id, name")->select();

        $deptModel = new DeptModel();
        $deptData = $deptModel->field("id, name")->select();

        // 仓库名称map  从crm_repertorylist 表中查出
        $repertoryListModel = new RepertorylistModel();
        $repMap = $repertoryListModel->getStockOutList();

        $data = [
                'formData' => $formData, // 出货单源单基本信息
                'orderformData' => $orderformData, // 出货单基本信息
                'materialData' => $materialData,  // 出货单物料信息
                'productData' => $productData,  // 订单物料信息
                'staffData' => $staffData,  // 员工列表
                'deptData' => $deptData,   // 部门列表
                'create_name'   => session("nickname"),
                "cate_id"      => StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM,   // 当前出库单类型id
                "cate_name"    => StockOutRecordModel::$stockOutType[StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM], // 出库类型名称
                "stockOutType" => StockOutRecordModel::$stockOutType,
                'repMap'    => $repMap,  // 其他出库名称
        ];

        return [true,"数据返回成功",$data];
    }

    /**
     * 回退全部物料
     * @param $id 出库单ID
     * @return array
     */
    public function rollBackAllMaterial($id){
        try{
            $this->startTrans();
            $stockOutData = $this->where(['id' => $id])->find();

            // 修改销售出库单源单的出库状态
            $orderFormModel = new OrderformModel();
            $orderFormData = $orderFormModel->where(['id' => $stockOutData['source_id']])->find();
            if($orderFormData['stock_status'] == OrderformModel::TYPE_OUT_ALL){
                $orderFormRes = $orderFormModel->where(['id' => $stockOutData['source_id']])->setField(['stock_status' => OrderformModel::TYPE_OUT_OF_REP]);
                if($orderFormRes === false){
                    $this->rollback();
                    return dataReturn($orderFormModel->getError(),400);
                }
            }

            // 将其他出库单相关的信息全部删除
            // 删除当前出库单
            $stockOutRes = $this->where(['id' => $id])->setField(['is_del' => self::IS_DEL, 'update_time' => time()]);
            if($stockOutRes === false){
                $this->rollback();
                return dataReturn($this->getError(),400);
            }

            // 删除出库单物料信息
            $stockMaterialModel = new StockMaterialModel();
            $stockMaterialRes = $stockMaterialModel->where(['type' => StockMaterialModel::TYPE_STOCK_OUT, 'source_id' => $id, "is_del" => StockMaterialModel::NO_DEL])->setField(['is_del' =>StockMaterialModel::IS_DEL, 'update_time' => time()]);
            if($stockMaterialRes === false){
                $this->rollback();
                return dataReturn($stockMaterialModel->getError(),400);
            }

            $recordModel = new StockOutRecordModel();
            $recordData = $recordModel->where(['source_id' => $id, "is_del" => StockMaterialModel::NO_DEL])->select();

            $stockModel = new StockModel();
            foreach ($recordData as $key => $value){
                // 删除出库记录
                $recordRes = $recordModel->where(['id' => $value['id']])->setField(['is_del' => StockOutRecordModel::IS_DEL, 'update_time' => time()]);
                if($recordRes === false){
                    $this->rollback();
                    return dataReturn($recordModel->getError(),400);
                    break;
                }

                // 修改库房记录
                list($code, $msg) = $stockModel->orderFormStockOutToUpdateStock($value['product_id'], $value['repertory_id'], $value['num'],2);
                if($code != 0){
                    $this->rollback();
                    return dataReturn($msg, 400);
                    break;
                }
            }

            $this->commit();
            return dataReturn("回退全部物料成功", 200);
        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(), 400);
        }
    }

    /**
     * 回退一个物料的部分数量
     * @param $id 物料id主键
     * @param $num 物料减少数量
     * @return array
     */
    public function rollBackOnePartMaterial($id, $num){
        try{
            $this->startTrans();
            $stockMaterialModel = new StockMaterialModel();
            $stockOutMaterialData = $stockMaterialModel->where(['id' => $id])->find();
            if($stockOutMaterialData['num'] <= $num){
                $this->rollback();
                return dataReturn("减少的物料数量不能大于或等于当前出库单物料数量", 400);
            }
            $stockNum = $stockOutMaterialData['num'] - $num; // 修改后数量
            // 修改出库单物料信息
            $stockMaterialRes = $stockMaterialModel->where(['id' => $id])->setField(['num' => $stockNum, 'update_time' => time()]);
            if($stockMaterialRes === false){
                $this->rollback();
                return dataReturn($stockMaterialModel->getError(),400);
            }

            $stockOutData = $this->where(['id' => $stockOutMaterialData['source_id']])->find();
            // 修改销售出库单源单的出库状态
            $orderFormModel = new OrderformModel();
            $orderFormData = $orderFormModel->where(['id' => $stockOutData['source_id']])->find();
            if($orderFormData['stock_status'] == OrderformModel::TYPE_OUT_ALL){
                $orderFormRes = $orderFormModel->where(['id' => $stockOutData['source_id']])->setField(['stock_status' => OrderformModel::TYPE_OUT_OF_REP]);
                if($orderFormRes === false){
                    $this->rollback();
                    return dataReturn($orderFormModel->getError(),400);
                }
            }

            // 修改出库单记录
            $recordModel = new StockOutRecordModel();
            $recordRes = $recordModel->where(['source_id' => $stockOutMaterialData['source_id'], "is_del" => StockMaterialModel::NO_DEL, 'product_id' => $stockOutMaterialData['product_id']])->setField(['num' => $stockNum, 'update_time' => time()]);
            if($recordRes === false){
                $this->rollback();
                return dataReturn($recordModel->getError(),400);
            }

            // 修改库房记录
            $stockModel = new StockModel();
            list($code, $msg) = $stockModel->orderFormStockOutToUpdateStock($stockOutMaterialData['product_id'], $stockOutMaterialData['rep_pid'], $num,2);
            if($code != 0){
                $this->rollback();
                return dataReturn($msg, 400);
            }

            $this->commit();
            return dataReturn("回退部分物料成功", 200);
        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(), 400);
        }
    }

    /**
     * 回退一个物料的全部数量
     * @param $id 物料id主键
     * @return array
     */
    public function rollBackOneAllMaterial($id){
        try{
            $this->startTrans();
            $stockMaterialModel = new StockMaterialModel();
            $stockOutMaterialData = $stockMaterialModel->where(['id' => $id])->find();
            $stockOutData = $this->where(['id' => $stockOutMaterialData['source_id']])->find();
            // 修改销售出库单源单的出库状态
            $orderFormModel = new OrderformModel();
            $orderFormData = $orderFormModel->where(['id' => $stockOutData['source_id']])->find();
            if($orderFormData['stock_status'] == OrderformModel::TYPE_OUT_ALL){
                $orderFormRes = $orderFormModel->where(['id' => $stockOutData['source_id']])->setField(['stock_status' => OrderformModel::TYPE_OUT_OF_REP]);
                if($orderFormRes === false){
                    $this->rollback();
                    return dataReturn($orderFormModel->getError(),400);
                }
            }

            // 删除出库单物料
            $stockMaterialRes = $stockMaterialModel->where(['id' => $id])->setField(['is_del' => StockMaterialModel::IS_DEL, 'update_time' => time()]);
            if($stockMaterialRes === false){
                $this->rollback();
                return dataReturn($stockMaterialModel->getError(),400);
            }

            // 删除出库记录
            $recordModel = new StockOutRecordModel();
            $recordRes = $recordModel->where(['source_id' => $stockOutMaterialData['source_id'], "is_del" => StockMaterialModel::NO_DEL, 'product_id' => $stockOutMaterialData['product_id']])->setField(['is_del' => StockOutRecordModel::IS_DEL, 'update_time' => time()]);
            if($recordRes === false){
                $this->rollback();
                return dataReturn($recordModel->getError(),400);
            }

            // 修改库房记录
            $stockModel = new StockModel();
            list($code, $msg) = $stockModel->orderFormStockOutToUpdateStock($stockOutMaterialData['product_id'], $stockOutMaterialData['rep_pid'], $stockOutMaterialData['num'],2);
            if($code != 0){
                $this->rollback();
                return dataReturn($msg, 400);
            }

            $this->commit();
            return dataReturn("回退全部物料成功", 200);
        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(), 400);
        }
    }
}