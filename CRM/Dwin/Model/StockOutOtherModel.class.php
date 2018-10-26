<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/8/2
 * Time: 下午2:08
 */

namespace Dwin\Model;


use Think\Exception;
use Think\Model;

class StockOutOtherModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

    /* 审核状态：0-未审核 1-审核不合格 2 质控审核完毕 3 库房审核完毕*/
    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_UNQUALIFIED = 1;     // 审核不合格
    const TYPE_QUALIFIED = 2;     // 质控审核完毕
    const TYPE_STOCK_QUALIFIED = 3;       // 库房审核完毕

    const IS_DEL = 1; // 已被删除
    const NO_DEL = 0; // 有效

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

    public function getAddData($params){
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], '没有提交新增数据'];
        }

        // 判空处理
        if(empty($data['stock_out_id']) || empty($data['id']) || empty($data['picking_kind'])){
            return [-2, [], '请将数据填写完整'];
        }

        // 当领料类型为生产改件 ， 出库类别自动修改为生产管理出库
        if($data['picking_kind'] == StockOutOtherApplyModel::PRODUCTION_MODIFICATION){
            $data['purchase_cate_id'] = StockOutOtherApplyModel::PRODUCTION_MANAGEMENT;
        }

        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['create_name']  = session('nickname');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');
        $data['audit_status']    = self::TYPE_QUALIFIED;
        $data['source_kind']  = StockOutRecordModel::TYPE_STOCK_OUT_OTHER;
        $data = $this->create($data);
        if ($data) {
            return [0, $data, '数据实例化成功'];
        } else {
            return [-2, [], $this->getError()];
        }
    }

    /**
     * 添加其他出库类型出库单基本信息
     * @param $data
     * @return array
     */
    public function addstock($data)
    {
        list($code, $addData, $msg) = $this->getAddData($data);
        if ($code != 0) {
            return [$msg, self::$failStatus];
        } else {
            $rst = $this->add($addData);
            if (!$rst) {
                return [$this->getError(), self::$failStatus];
            }
            return ["新增成功", self::$successStatus];
        }
    }

    public function getEditData($params)
    {
        if (empty($params)) {
            return [-1, [], "无修改数据提交"];
        }

        $oldData = $this->field("*")->find($params['id']);

        if($oldData['audit_status'] == self::TYPE_STOCK_QUALIFIED){
            return [-2, [], '当前出库单已出库，不可修改'];
        }
        $editData = $this->compareData($oldData, $params);

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
     * 修改其他出库类型出库单基本信息
     * @param $data
     * @return array
     */
    public function modifyStockOut($data)
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
     * 生成其他出库类型出库单
     * @param $stockMsg
     * @param $materialMsg
     * @return array
     */
    public function createStock($stockMsg, $materialMsg){
        $this->startTrans();

        $applyModel = new StockOutOtherApplyModel();
        $applyData = $applyModel->find($stockMsg['source_id']);

        // 首先判断一下是否已经下推出库单
        $result = $this->where(['source_id' => $stockMsg['source_id'], 'is_del' => self::NO_DEL])->find();
        if(!empty($result)){
            $this->rollback();
            return dataReturn("当前申请单已经下推出库，不可重复下推", 400);
        }

        $idArr = array_unique(array_column($materialMsg, "product_id"));
        if(count($idArr) != count($materialMsg)){
            $this->rollback();
            return dataReturn("当前添加的物料有重复的", 400);
        }

        // 首先将申请单数据相对应填入出库单中
        $stockMsg['source_id'] = $applyData['id'];
        $stockMsg['picking_dept_id'] = $applyData['apply_dept_id'];
        $stockMsg['picking_dept_name'] = $applyData['apply_dept_name'];
        $stockMsg['picking_kind'] = $applyData['picking_kind'];

        list($msg, $status) = self::addstock($stockMsg);
        if ($status == self::$failStatus) {
            $this->rollback();
            return dataReturn($msg, self::$failStatus);
        }

        $applyMaterialModel = new StockOutOtherApplyMaterialModel();
        $applyMaterialData = $applyMaterialModel->getMaterialByApplyId($stockMsg['source_id']);


        $materialModel = new StockMaterialModel();
        $stockModel = new StockModel();
        // 新增出库单物料信息
        foreach ($materialMsg as $key => $item){
            if(!isset($applyMaterialData[$item['product_id']])){
                $this->rollback();
                return dataReturn("物料" . $item['product_number'] . "不在申请单中",self::$failStatus);
            }

            $stockData = $stockModel->where(['product_id' => $item['product_id'], 'warehouse_number' => $item['rep_pid']])->find();
            if(empty($stockData) || $stockData['stock_number'] + $stockData['o_audit'] < $item['num']){
                $this->rollback();
                return dataReturn("物料编号：" . $item['product_no'] . "仓库数量不够", 400);
            }

            list($code, $data, $msg) = self::addStockMaterialByPurchase($item, $stockMsg['id']);

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
        list($code, $message) = $recordModel->autoSaveRecordForStockOutOther($stockMsg['id'], $applyMaterialData,StockOutRecordModel::TYPE_STOCK_OUT_OTHER);
        if($code != 0){
            $this->rollback();
            return dataReturn($message, self::$failStatus);
        }

        if($applyData['stock_status'] != StockOutOtherApplyModel::TYPE_OUT_OF_REP){
            $applyRes = $applyModel->where(['id' => $applyData['id']])->setField(["update_time" => time(), "stock_status" => StockOutOtherApplyModel::TYPE_OUT_OF_REP]);
            if($applyRes === false){
                $this->rollback();
                return dataReturn("修改源单出库单状态失败", 400);
            }
        }

        $this->commit();
        return dataReturn('添加其他出库类型出库单成功', self::$successStatus);
    }

    /**
     * 修改其他出库类型出库单全部信息
     * @param $stockData
     * @param $materialData
     * @return array
     */
    public function editStockOut($stockData, $materialData)
    {
        try {
            $this->startTrans();

            $statusStr = '';
            $materialModel = new StockMaterialModel();

            foreach ($materialData as $key => $item){
                list($code, $materiaDataOne, $msg) = self::editMaterial($stockData['id'], $item);
                if($code == 0){
                    $statusStr = self::$successStatus;
                    $saveRst = $materialModel->save($materiaDataOne);

                    if ($saveRst === false) {
                        $this->rollback();
                        return dataReturn($materialModel->getError(), self::$failStatus);
                        break;
                    }
                }

                if($code == -2){
                    $this->rollback();
                    return dataReturn($msg, self::$failStatus);
                    break;
                }
            }

            list($stockMsg, $stockCode) = $this->modifyStockOut($stockData);
            if ($stockCode == -2) {
                $this->rollback();
                return dataReturn($stockMsg, 400);
            }

            if($statusStr == "" && $stockCode == -1){
                $this->rollback();
                return dataReturn($stockMsg, self::$failStatus);
            }
            if($stockCode == -2){
                $this->rollback();
                return dataReturn($stockMsg, self::$failStatus);
            }

            $this->where(['id' => $stockData['id']])->setField(["audit_status" => self::TYPE_QUALIFIED]);
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
        $map['crm_stock_out_other.is_del'] = ['eq', self::NO_DEL];
        $recordMap = $map;
        if(strlen($condition) != 0){
            $where['crm_stock_out_other.stock_out_id'] = ['like', "%" . $condition . "%"];
            $where['crm_stock_out_other.picking_dept_name'] = ['like', "%" . $condition . "%"];
            $where['crm_stock_out_other.create_name']=['like', "%" . $condition . "%"];
            $where['_logic'] = 'OR';
            $recordMap['_complex'] = $where;
        }

        $data =  $this->field("*")
            ->limit($start, $length)
            ->where($recordMap)
            ->order($order)
            ->select();
        /** 后台传输局到前台
        @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
        $count = $this->where($map)->count();
        $recordsFiltered = $this->where($recordMap)->count();

        return [$data,$count,$recordsFiltered];
    }

    /**
     * create by chendd 新增物料信息
     * @param $postData
     * @param $purchaseId
     * @return array|bool
     */
    public function addStockMaterialByPurchase($postData, $purchaseId)
    {
        $materialModel = new StockMaterialModel();
        $data = $materialModel->getNewField($postData);
        if(empty($data)){
            return [-1, [], "未添加单据的基本信息"];
        }
        if(empty($data['product_id']) || empty($data['product_no']) || empty($data['num']) || empty($data['rep_pid'])){
            return [-2, [], "请将数据填写完整"];
        }
        $data['source_id'] = $purchaseId;
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
        $recordData = $stockRecordModel->getNumByMaterialId($oldData['id'],StockOutRecordModel::TYPE_STOCK_OUT_OTHER);

        if($data['num'] > $recordData['num']){
            return [-2, [], "修改后的出库库数量不能低于已出库数量"];
        }

        $editData = $materialModel->compareData($oldData, $data);

        $stockModel = new StockModel();
        $recordModel = new StockOutRecordModel();
        if(isset($editData['rep_pid'])){
            $num = isset($editData['num']) ? $editData['num'] : $oldData['num'];
            $productNo = isset($editData['product_no']) ? $editData['product_no'] : $oldData['product_no'];
            $productId = isset($editData['product_id']) ? $editData['product_id'] : $oldData['product_id'];

            list($code, $msg) = $stockModel->updateStockOutToUpdateStock($productId, $oldData['rep_pid'], $editData['rep_pid'], $oldData['num'], $num);
            if($code != 0){
                return [-2,[], $productNo . $msg];
            }

            $recordModel->where(['source_id' => $stockId, "product_id" => $productId, "is_del" => self::NO_DEL])->setField(['repertory_id' => $editData['rep_pid'], 'num' => $num]);

        }

        if(isset($editData['num'])){
            return [-2, [], "其他类型出库单不能修改其物料的数量"];
        }

        if ($editData === false) {
            return [-1, [], "无数据修改"];
        }else if($editData == -1) {
            return [-2, [], "只能对出库数量进行修改"];
        } else {
            $createData = $materialModel->create($editData);
            if(!$createData){
                return[-2,[], $materialModel->getError()];
            }
            return [0, $createData, "数据实例化成功"];
        }
    }

    /**
     * 删除整个出库单
     * @param $id
     * @return array
     */
    public function delOther($id){
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

        $otherData = $this->find($id);
        if($otherData['audit_status'] != self::TYPE_STOCK_QUALIFIED){
            return dataReturn("当前出库单已出库，不可以删除", 400);
        }
        try{
            $this->startTrans();
            $res = $this->where(['id' => $id])->setField(['is_del' => self::IS_DEL]);
            if($res === false){
                $this->rollback();
                return dataReturn("删除失败", 400);
            }

            $materialModel = new StockMaterialModel();
            $materialModel->where(['source_id' => $id])->setField(['is_del' => StockMaterialModel::IS_DEL, "update_time" => time()]);

            // 删除对应出库记录
            list($code, $msg) = $recordModel->autoDelStockOutRecordByStockIdMany($id, StockOutRecordModel::TYPE_STOCK_OUT_OTHER, $recordModel->getRecordByStockId($id));
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
     * 下载出库单为pdf
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
                    <div style="width: 100%;text-align: center;">其他类型出库单</div>
                    <table style="margin-top: 20px; min-height: 25px; line-height: 25px;width: 100%">
                        <tr style="width:100%;height: 30px;">
                            <td width="10%">出库类型：</td>
                            <td width="25%">' . $baseMsg["purchase_cate_name"] . '</td>
                            <td width="10%">用途：</td>
                            <td width="28%">' . $baseMsg["purpose"] . '</td>
                            <td width="8%">编号：</td>
                            <td width="18%">' . $baseMsg["stock_out_id"] . '</td>
                        </tr>
                        <tr style="width:100%;height: 30px;">
                            <td width="10%">领料部门：</td>
                            <td width="25%">' . $baseMsg["picking_dept_name"] . '</td>
                            <td width="10%">领料类型：</td>
                            <td width="28%">' . StockOutOtherApplyModel::$pickingType[$baseMsg["picking_kind"]] . '</td>
                            <td width="8%">日期：</td>
                            <td width="24%">' . date("Y/m/d",$baseMsg["create_time"]) . '</td>
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
        $fileName = "其他出库单". '_' . $repId . '.pdf';

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
//                            width: 25%;
//                        }
//                        .td3{
//                            width: 10%;
//                        }
//                        .td4{
//                            width: 28%;
//                        }
//                        .td5{
//                            width: 8%;
//                        }
//                        .td6{
//                            width: 18%;
//                        }
//                        .td7{
//                            width: 10%;
//                        }
//                        .td8{
//                            width: 25%;
//                        }
//                        .td9{
//                            width: 10%;
//                        }
//                        .td10{
//                            width: 28%;
//                        }
//                        .td11{
//                            width: 8%;
//                        }
//                        .td12{
//                            width: 18%;
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
                            <div class="title">其他类型出库单</div>
                            <table class="baseMsg">
                                <tr class="userMsg">
                                    <td width="10%">出库类型：</td>
                                    <td width="20%">' . $baseMsg["purchase_cate_name"] . '</td>
                                    <td width="10%">用途：</td>
                                    <td width="20%">' . $baseMsg["purpose"] . '</td>
                                    <td width="10%">编号：</td>
                                    <td width="30%">' . $baseMsg["stock_out_id"] . '</td>
                                </tr>
                                <tr class="userMsg">
                                    <td width="10%">领料部门：</td>
                                    <td width="20%">' . $baseMsg["picking_dept_name"] . '</td>
                                    <td width="10%">领料类型：</td>
                                    <td width="20%">' . StockOutOtherApplyModel::$pickingType[$baseMsg["picking_kind"]] . '</td>
                                    <td width="10%">日期：</td>
                                    <td width="30%">' . date("Y/m/d",$baseMsg["create_time"]) . '</td>
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
//        $fileName = "其他出库单.pdf";
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
        $materialModel = new StockMaterialModel();
        $stockData = $this->where(['id' => $id])->find();

        $materialData = $materialModel->selectByStockId($id);

//            仓库名称map  从crm_repertorylist 表中查出
        $repertoryListModel = new RepertorylistModel();
        $repMap = $repertoryListModel->getStockOutList();

        // 人员信息
        $staffModel = new StaffModel();
        $staffData = $staffModel->field("id,name")->select();

        // 部门信息
        $deptModel = new DeptModel();
        $deptData = $deptModel->field("id,name")->select();

        $data = [
            'stockData' => $stockData,  // 其他出库类型出库单基本信息
            'materialData' => $materialData, // 其他出库类型出库单物料信息
            'outOfTreasuryType' => StockOutOtherApplyModel::$outOfTreasuryType, // 其他出库类型中的出库类别
            'repMap'    => $repMap,  // 其他出库名称
            'staffData' => $staffData, // 公司员工map
            'deptData' => $deptData,    // 部门map
            "cate_id"      => StockOutRecordModel::TYPE_STOCK_OUT_OTHER,   // 当前出库单类型id
            "cate_name"    => StockOutRecordModel::$stockOutType[StockOutRecordModel::TYPE_STOCK_OUT_OTHER], // 出库类型名称
            "auditMap"     => self::$auditMap,
            'pickingType' => StockOutOtherApplyModel::$pickingType,                  // 领料类型
        ];

        return [true, "数据获取成功", $data];
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

            // 将其他出库单相关的信息全部删除 ， 包括申请单数据

            // 删除申请单
            $applyModel = new StockOutOtherApplyModel();
            $applyRes = $applyModel->where(['id' => $stockOutData['source_id'], 'is_del' => StockOutOtherApplyModel::NO_DEL])->setField(['is_del' => StockOutOtherApplyModel::IS_DEL, 'update_time' => time()]);
            if($applyRes === false){
                $this->rollback();
                return dataReturn($applyModel->getError(),400);
            }

            // 删除申请单物料
            $applyMaterialModel = new StockOutOtherApplyMaterialModel();
            $applyMaterialRes = $applyMaterialModel->where(['apply_id' => $stockOutData['source_id'], 'is_del' => StockOutOtherApplyMaterialModel::NO_DEL])->setField(['is_del' => StockOutOtherApplyMaterialModel::IS_DEL, 'update_time' => time()]);
            if($applyMaterialRes === false){
                $this->rollback();
                return dataReturn($applyMaterialModel->getError(),400);
            }

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
            $materialModel = new MaterialModel();
            foreach ($recordData as $key => $value){
                // 删除出库记录
                $recordRes = $recordModel->where(['id' => $value['id']])->setField(['is_del' => StockOutRecordModel::IS_DEL, 'update_time' => time()]);
                if($recordRes === false){
                    $this->rollback();
                    return dataReturn($recordModel->getError(),400);
                    break;
                }

                if($stockOutData['purchase_cate_id'] == StockOutOtherApplyModel::PRODUCTION_MODIFICATION){
                    $material = $materialModel->where(['product_id' => $value['product_id']])->find();
                    $map['rework_number'] = ['eq', $material['rework_number'] + $value['num']];
                    $materialRes = $materialModel->where(['product_id' => $value['product_id']])->setField($map);
                    if(!$materialRes){
                        $this->rollback();
                        return dataReturn($materialModel->getError(),400);
                    }
                }

                // 修改库房记录
                list($code, $msg) = $stockModel->rollBackStockNum($value['product_id'], $value['repertory_id'], $value['num']);
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

            if($stockOutData['purchase_cate_id'] == StockOutOtherApplyModel::PRODUCTION_MODIFICATION){
                $materialModel = new MaterialModel();
                $material = $materialModel->where(['product_id' => $stockOutMaterialData['product_id']])->find();
                $map['rework_number'] = ['eq', $material['rework_number'] - $num];
                $materialRes = $materialModel->where(['product_id' => $stockOutMaterialData['product_id']])->setField($map);
                if($materialRes === false){
                    $this->rollback();
                    return dataReturn($materialModel->getError(),400);
                }
            }

            // 修改申请单物料
            $applyMaterialModel = new StockOutOtherApplyMaterialModel();
            $applyMaterialRes = $applyMaterialModel->where(['apply_id' => $stockOutData['source_id'], 'product_id' => $stockOutMaterialData['product_id'], 'is_del' => StockOutOtherApplyMaterialModel::NO_DEL])->setField(['num' => $stockNum, 'update_time' => time()]);
            if($applyMaterialRes === false){
                $this->rollback();
                return dataReturn($applyMaterialModel->getError(),400);
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
            list($code, $msg) = $stockModel->rollBackStockNum($stockOutMaterialData['product_id'], $stockOutMaterialData['rep_pid'], $num);
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

            if($stockOutData['purchase_cate_id'] == StockOutOtherApplyModel::PRODUCTION_MODIFICATION){
                $materialModel = new MaterialModel();
                $material = $materialModel->where(['product_id' => $stockOutMaterialData['product_id']])->find();
                $map['rework_number'] = ['eq', $material['rework_number'] - $stockOutMaterialData['num']];
                $materialRes = $materialModel->where(['product_id' => $stockOutMaterialData['product_id']])->setField($map);
                if($materialRes === false){
                    $this->rollback();
                    return dataReturn($materialModel->getError(),400);
                }
            }

            $map['id'] = ['neq', $id];
            $map['is_del'] = ['eq', StockMaterialModel::NO_DEL];
            $map['type'] = ['eq', StockMaterialModel::TYPE_STOCK_OUT];
            $stockOutMaterialOtherData = $stockMaterialModel->where($map)->select();
            if(empty($stockOutMaterialOtherData)){
                // 删除申请单
                $applyModel = new StockOutOtherApplyModel();
                $applyRes = $applyModel->where(['id' => $stockOutData['source_id'], 'is_del' => StockOutOtherApplyModel::NO_DEL])->setField(['is_del' => StockOutOtherApplyModel::IS_DEL, 'update_time' => time()]);
                if($applyRes === false){
                    $this->rollback();
                    return dataReturn($applyModel->getError(),400);
                }

                // 删除当前出库单
                $stockOutRes = $this->where(['id' => $id])->setField(['is_del' => self::IS_DEL, 'update_time' => time()]);
                if($stockOutRes === false){
                    $this->rollback();
                    return dataReturn($this->getError(),400);
                }
            }

            // 删除出库单物料
            $stockMaterialRes = $stockMaterialModel->where(['id' => $id])->setField(['is_del' => StockMaterialModel::IS_DEL, 'update_time' => time()]);
            if($stockMaterialRes === false){
                $this->rollback();
                return dataReturn($stockMaterialModel->getError(),400);
            }

            // 删除申请单物料
            $applyMaterialModel = new StockOutOtherApplyMaterialModel();
            $applyMaterialRes = $applyMaterialModel->where(['apply_id' => $stockOutData['source_id'], 'product_id' => $stockOutMaterialData['product_id'], 'is_del' => StockOutOtherApplyMaterialModel::NO_DEL])->setField(['is_del' => StockOutOtherApplyMaterialModel::IS_DEL, 'update_time' => time()]);
            if($applyMaterialRes === false){
                $this->rollback();
                return dataReturn($applyMaterialModel->getError(),400);
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
            list($code, $msg) = $stockModel->rollBackStockNum($stockOutMaterialData['product_id'], $stockOutMaterialData['rep_pid'], $stockOutMaterialData['num']);
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