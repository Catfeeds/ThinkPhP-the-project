<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/9/21
 * Time: 下午1:28
 */
namespace Dwin\Model;
use think\Exception;
use Think\Model;

class StockOutProductionModel extends Model{
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
        $data['source_id'] = implode(',', $data['source_id']);
        $data['create_id']    = session('staffId');
        $data['create_name']    = session('nickname');
        $data['update_time']  = time();
        $data['audit_status']    = self::TYPE_QUALIFIED;
        $data['source_kind']  = StockOutRecordModel::TYPE_STOCK_OUT_PRODUCTION;

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

    /**
     * 新增领料出库单
     * @param $productionData
     * @param $materialData
     * @return array
     */
    public function createProduction($productionData, $materialData){
        try{
            $this->startTrans();
            list($code, $msg) = self::getAddData($productionData);
            if($code != 0){
                $this->rollback();
                return dataReturn($msg,400);
            }

            $productModel =new ProductionOrderProductModel();
            // 处理物料数据
            $material = [];
            foreach ($materialData as $k => $v){
                $productResult = $productModel->where(['id' => $v['id']])->setField(['push_num' => $v['num']]);
                if($productResult === false){
                    $this->rollback();
                    return dataReturn($productModel->getError(),400);
                }
                $material[$v['product_id']][] = $v;
            }
            unset($v);

            $data = [];
            foreach ($material as $k => $v){
                $num = array_sum(array_column($v,'num'));
                $orderIdStr = implode(",", array_column($v,'id'));
                if(empty($v[0]['product_id']) || empty($v[0]['product_no']) || empty($num) || empty($v[0]['rep_pid']) || empty($orderIdStr)){
                    $this->rollback();
                    return dataReturn("参数不全",400);
                }

                $data[] = [
                    'source_id' => $productionData['id'],
                    'order_id_str' => $orderIdStr,
                    'product_id' => $v[0]['product_id'],
                    'product_no' => $v[0]['product_no'],
                    'num' => $num,
                    'rep_pid' => $v[0]['rep_pid'],
                    'type' => StockMaterialModel::TYPE_STOCK_OUT,
                    'create_time' => time(),
                    'update_time' => time(),
                ];
            }

            // 写物料信息
            $materialModel = new StockMaterialModel();
            $materialRes = $materialModel->addAll($data);
            if($materialRes === false){
                $this->rollback();
                return dataReturn($materialModel->getError(), 400);
            }

            // 直接写record
            $recordModel = new StockOutRecordModel();
            list($code, $message) = $recordModel->autoSaveRecordForProduce($productionData['id'], StockOutRecordModel::TYPE_STOCK_OUT_PRODUCTION);
            if($code != 0){
                $this->rollback();
                return dataReturn($message, 400);
            }

            $productionOrderModel = new ProductionOrderModel();
//            $productionData = $productionOrderModel->getOrderBaseMsgById($productionData['source_id']);
//            $map['id'] = ['in', implode(array_column($productionData,'source_id'))];
            $map['id'] = ['in', implode(',', $productionData['source_id'])];
            $productRes = $productionOrderModel->where($map)->setField(['stock_status' => ProductionOrderModel::TYPE_OUT_OF_REP]);
            if($productRes === false){
                $this->rollback();
                return dataReturn("修改源单出库单状态失败", 400);
            }

            $this->commit();
            return dataReturn("新增领料单成功", 200);
        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(), 400);
        }
    }

    /**
     * 删除领料出库单
     * @param $id
     * @return array
     */
    public function delProduction($id){
        try{
            $this->startTrans();
            $productionData = $this->find($id);
            if($productionData['audit_status'] != self::TYPE_STOCK_QUALIFIED){
                $this->rollback();
                return dataReturn("当前出库单已出库，不可以删除", 400);
            }
            $productionRes = $this->where(['id' => $id])->setField(['is_del' => self::IS_DEL]);
            if($productionRes === false){
                $this->rollback();
                return dataReturn($this->getError(), 400);
            }

            $materialModel = new StockMaterialModel();
            // 修改源单物料下推数量
            $materialData = $materialModel->where(['source_id' => $id, "is_del" => self::NO_DEL])->select();
            $productIdArr = [];
            foreach ($materialData as $k => $v){
                $productIdArr = array_merge($productIdArr, explode(',', $v['order_id_str']));
            }
            $map['id'] = ['in', implode(',', array_filter($productIdArr))];
            $productModel = new ProductionOrderProductModel();
            $productRes = $productModel->where($map)->setField(['push_num' => 0]);
            if($productRes === false){
                $this->rollback();
                return dataReturn($productModel->getError(),400);
            }

            // 删除物料
            $materialModel->where(['source_id' => $id, "is_del" => self::NO_DEL])->setField(['is_del' => StockMaterialModel::IS_DEL, "update_time" => time()]);

            // 删除对应出库记录
            $recordModel = new StockOutRecordModel();
            list($code, $msg) = $recordModel->autoDelStockOutRecordByStockIdMany($id, StockOutRecordModel::TYPE_STOCK_OUT_PRODUCTION, $recordModel->getRecordByStockId($id));
            if($code != 0){
                $this->rollback();
                return dataReturn($msg, 400);
            }

            $this->commit();
            return dataReturn("删除领料单成功", 200);
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
            ->field("p.*, o.production_line, GROUP_CONCAT(o.production_code) AS production_code")
            ->join("left join crm_production_order o on FIND_IN_SET(o.id,(p.source_id))")
            ->where($map)
            ->group("p.id")
            ->find();
        if(!empty($data['production_line'])){
            $lineModel = new ProductionLineModel();
            $lineData = $lineModel->find($data['production_line']);
            if(empty($lineData['pid'])){
                $data['production_line_name'] = $lineData['production_line'];
            }else{
                $linePidData = $lineModel->find($lineData['pid']);
                $data['production_line_name'] = $linePidData['production_line'];
            }
        }
        return $data;
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
                                <tr>
                                    <td width="10%">生产单号：</td>
                                    <td width="85%" colspan="5">' . $baseMsg["production_code"] . '</td>
                                </tr>
                                <tr class="userMsg">
                                    <td width="10%">生产线：</td>
                                    <td width="20%">' . $baseMsg["production_line_name"] . '</td>
                                    <td width="10%">编号：</td>
                                    <td width="20%">' . $baseMsg["stock_out_id"] . '</td>
                                    <td width="10%">日期：</td>
                                    <td width="30%">' . date("Y/m/d",$baseMsg["create_time"]) . '</td>
                                </tr>
                                <tr class="userMsg">
                                    <td width="10%">领料部门：</td>
                                    <td width="20%">' . $baseMsg["picking_dept_name"] . '</td>
                                    <td width="10%">领料用途：</td>
                                    <td width="50%" colspan="3">' . $baseMsg["picking_purpose"] . '</td>
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
}