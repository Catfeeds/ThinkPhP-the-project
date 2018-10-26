<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/8/10
 * Time: 下午5:38
 */

namespace Dwin\Model;


use Think\Model;

class StockTransferModel extends Model
{
    /* 审核状态：0-未审核 1-审核不合格 2 制单人审核完毕 3 库房审核完毕*/
    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_UNQUALIFIED = 1;     // 审核不合格
    const TYPE_QUALIFIED = 2;     // 制单人审核完毕
    const TYPE_STOCK_QUALIFIED = 3;       // 库房审核完毕

    const IS_DEL = 1; // 已被删除
    const NO_DEL = 0; // 有效

    const SUCCESS_STATUS = 200;
    const FAIL_STATUS    = 404;
    const FORBIDDEN_STATUS = 403;

    public function getAddData($base, $idsArray)
    {
        $time = time();
        $createId = session('staffId');
        $createName = session('nickname');
        $data = [];
        foreach ($idsArray as $key => $idItem) {
            $data[$key]['id'] = $idItem['orderId'];
            $data[$key]['transfer_id'] = "DBD-" . $idItem['orderId'];
            $data[$key]['source_type'] = $base['transferType'];
            $data[$key]['source_id']   = $base['source_id'];
            $data[$key]['create_id']   = $createId;
            $data[$key]['create_name'] = $createName;
            $data[$key]['create_time'] = $time;
            $data[$key]['update_time'] = $time;
            $data[$key]['tips'] = date('YmdHis', $time) . "制单，制单人：" . $createName . ".备注：(" . $base['tips'] . ")";
        }
        return $data;
    }
    public function getEditData($data)
    {
        $upd = [];
        $upd['id'] = $data['id'];
        if ($data['keepArr']) {
            $upd['keep_id']    = getStringId($data['keepArr']);
            $upd['keep_name']  = getStringChar($data['keepArr']);
        }
        if ($data['checkArr']) {
            $upd['check_name'] = getStringChar($data['checkArr']);
            $upd['check_id']   = getStringId($data['checkArr']);
        }
        if (data['tips']) {
            $upd['tips'] = nl2br($data['tips']);
        }
        return $upd;
    }

    /**
     * 调拨单添加方法
     * 添加数据，库存减少
    */
    public function addStockTransferTrans($base, $material)
    {
        $materialModel = new StockTransferMaterialModel();
        $stockModel = new StockModel();
        $this->startTrans();
        $addRst = $this->addAll($base);
        if ($addRst === false) {
            $this->error = "提交失败";
            $this->rollback();
            return false;
        }
        $materialAddRst = $materialModel->addAll($material);
        if ($materialAddRst === false) {
            $this->error = "commit false";
            $this->rollback();
            return false;
        }
        foreach ($material as $key => $value) {
            $filter[$key]['product_id'] = ['EQ', $value['product_id']];
            $filter[$key]['warehouse_number'] = ['EQ', $value['rep_id_out']];
            $updateRst[$key] = $stockModel->updateWithFlag('addStockOutWithOutOrder', $filter[$key], $value['num']);
            if ($updateRst[$key] === false) {
                $this->error = $stockModel->getError();
                $this->rollback();
                return false;
            }
        }

        $this->commit();
        return true;
    }

    /**
     * 编辑未审核的调拨单据
     * @param array $base 修改的基本表数据
     * @param array $material 修改的调拨物料表数据（目前只修改备注和数量）
     * @param array $stock 更新的库存表数据（主要更新stock_number,out_processing)
     *
    */
    public function editTransfer($base, $material, $stock)
    {
        $this->startTrans();
        if ($base['id']) {
            $map['id'] = ['eq', $base['id']];
            $baseRst = $this->where($map)->setField($base);
            if (false === $baseRst) {
                $this->rollback();
                $this->error = "更新失败";
                return false;
            }
        }
        $transferMaterialModel = new StockTransferMaterialModel();
        foreach ($material as $value) {
            if ($value['id']) {
                $filter['id'] = ['EQ', $value['id']];
                $materialRst = $transferMaterialModel->where($filter)->setField($value);
                if (false === $materialRst) {
                    $this->rollback();
                    $this->error = "更新失败";
                    return false;
                }
            }
        }
        $stockModel = new StockModel();
        foreach ($stock as $item) {
            $updFilter['product_id'] = ['EQ' ,$item['product_id']];
            $updFilter['warehouse_number'] = ['eq', $item['warehouse_number']];
            $num = $item['num'];
            $flag = $num > 0 ? "addStockOutWithOutOrder" : "stockOutNoActionOrderFalse";
            $num = abs($num);
            $stockRst = $stockModel->updateWithFlag($flag,$updFilter, $num);
            if (false === $stockRst) {
                $this->rollback();
                $this->error = "更新失败";
                return false;
            }
        }
        $this->commit();
        return true;
    }

    public function checkAuditStatus($base, $flag)
    {
        switch ($base['audit_status']) {
            case self::TYPE_NOT_AUDIT:
                if ($flag != 1) {
                    $this->error = "审核参数有误，不能审核";
                    return false;
                }
                if (session('staffId') != $base['create_id']) {
                    $this->error = "审核人只能为制单人本人，请确认当前登录用户是否为制单人？";
                }
                return true;
                break;
            case self::TYPE_UNQUALIFIED:
                $this->error = "单据被驳回过且还未修改，请您修改后再审核";
                return false;
                break;
            case self::TYPE_QUALIFIED:
                if ($flag != 2) {
                    $this->error = "审核参数有误，不能审核";
                    return false;
                }
                $transferMaterModel = new StockTransferMaterialModel();
                $authIds = $transferMaterModel->obtainAuditIdWithTransferId($base['id']);
                if (empty($authIds)) {
                    $this->error = "库房未选择物流员，请联系物流经理或管理员";
                    return false;
                }
                if ((!in_array(session('staffId'), explode(",", $authIds)))) {
                    $this->error = "非库房物流员不能进行二级审核";
                    return false;
                }
                return true;
                break;
            case self::TYPE_STOCK_QUALIFIED:
                $this->error = "该调拨单已经完结，不能审核";
                return false;
                break;
            default :
                $this->error = "未知错误";
                return false;
                break;
        }
    }

    public function auditTransferOrder($base, $flag)
    {

        switch ($flag) {
            case 1 :
                $rst = $this->firstAudit($base);
                if ($rst === false) {
                    $this->error = "1级审核失败";
                }
                break;
            case 2 :
                $rst = $this->secondAudit($base);
                break;
            default :
                $this->error = "错误";
                $rst = false;
                break;
        }
        return $rst;
    }

    public function firstAudit($base)
    {
        $data['audit_status'] = self::TYPE_QUALIFIED;
        $data['keep_id']    = getStringId($base['keepArr']);
        $data['keep_name']  = getStringChar($base['keepArr']);
        $data['check_name'] = getStringChar($base['checkArr']);
        $data['check_id']   = getStringId($base['checkArr']);
        $data['update_time'] = time();
        $map['id'] = ['EQ', $base['transferId']];
        return $this->where($map)->setField($data);
    }

    public function secondAudit($base)
    {
        $this->startTrans();
        $data['audit_status'] = self::TYPE_STOCK_QUALIFIED;
        $data['auditor'] = session('staffId');
        $data['auditor_name'] = session('nickname');
        $map['id'] = ['eq', $base['transferId']];
        $auditRst = $this->where($map)->setField($data);
        if ($auditRst === false) {
            $this->error = "错误1";
            $this->rollback();
            return false;
        }
        $transferMaterialModel = new StockTransferMaterialModel();
        $stockModel = new StockModel();
        $materialData = $transferMaterialModel->getMaterialWithPid($base['transferId']);
        foreach ($materialData as $key => $materialDatum) {
            if (!in_array($data['auditor'], $materialDatum['rep_id_arr'])) {
                $this->error = "您非该物料出库仓库的物流员，不能审核此单据(物料编号：{$materialDatum['product_no']})";
                $this->rollback();
                return false;
            }
            $filterIn[$key]['product_id'] = ['EQ', $materialDatum['product_id']];
            $filterIn[$key]['warehouse_number'] = ['EQ', $materialDatum['rep_id_out']];
            $subRst[$key] = $stockModel->updateWithFlag('stockOutTrue', $filterIn[$key], $materialDatum['num']);
            if ($subRst[$key] === false) {
                $this->rollback();
                $this->error = "错误2";
                return false;
            }
            $filterOut[$key]['product_id'] = ['EQ', $materialDatum['product_id']];
            $filterOut[$key]['warehouse_number'] = ['EQ', $materialDatum['rep_id_in']];
            $outData = $stockModel->where($filterOut[$key])->find();

            if (empty($outData)) {
                $dataAdd['product_id'] = $materialDatum['product_id'];
                $dataAdd['warehouse_number'] = $materialDatum['rep_id_in'];
                $dataAdd['warehouse_name'] = $materialDatum['rep_name_in'];
                $dataAdd['update_time'] = time();
                $rst = $stockModel->add($dataAdd);
                if ($rst === false) {
                    $this->rollback();
                    $this->error = "错误4";
                    return false;
                }
            }
            $plusRst[$key] = $stockModel->updateWithFlag('checkStockIn', $filterOut[$key], $materialDatum['num']);

            if ($plusRst[$key] === false) {
                $this->rollback();
                $this->error = "错误3";
                return false;
            }
        }
        $this->commit();
        return true;

    }


    public function getAuditListWithLv($limitConfig, $sqlCondition, $lv)
    {
        $map['create_id'] = ['IN', (string)$limitConfig['staffLimit']];
        switch ($lv) {
            case 1 :
                $statusLimit = self:: TYPE_NOT_AUDIT;
                break;
            case 2 :
                $statusLimit = self:: TYPE_QUALIFIED;
                break;
            case 3:
                $statusLimit = self:: TYPE_STOCK_QUALIFIED;
                break;
            default :
                $statusLimit = self:: TYPE_NOT_AUDIT;
                break;
        }
        $map['audit_status'] = ['EQ', $statusLimit];
        $map['is_del'] = ['EQ', self::NO_DEL];

        $count = $this->where($map)->count();
        if (trim($sqlCondition['search'])) {
            $map['id|transfer_id|create_name|tips'] = ['LIKE', "%" . trim($sqlCondition) . "%"];
        }
        $filterCount = $this->where($map)->count();
        $field = "id,
                  transfer_id,
                  from_unixtime(create_time) create_time,
                  create_id,
                  create_name,
                  source_type,
                  audit_status,
                  print_time,
                  check_id,
                  check_name,
                  keep_id,
                  keep_name,
                  from_unixtime(update_time) update_time,
                  tips
                  ";
        $data = $this->field($field)
            ->where($map)
            ->order($sqlCondition['order'])
            ->limit($sqlCondition['start'], $sqlCondition['length'])
            ->select();
        return [$count, $filterCount, $data];
    }

    public function deleteTransfer($id)
    {
        $this->startTrans();
        $data['is_del'] = self::IS_DEL;
        $map['id'] = ['eq', $id];
        $delRst = $this->where($map)->setField($data);
        if (false === $delRst) {
            $this->rollback();
            $this->error = "删除失败2";
            return false;
        }
        $transferMaterialModel = new StockTransferMaterialModel();
        $materialRst = $transferMaterialModel->deleteMaterialWithPid($id);
        if (false === $materialRst) {
            $this->rollback();
            $this->error = $transferMaterialModel->getError();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 打印调拨单
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
//                        .td1{
//                            width: 10%;
//                        }
//                        .td2{
//                            width: 23%;
//                        }
//                        .td3{
//                            width: 10%;
//                        }
//                        .td4{
//                            width: 23%;
//                        }
//                        .td5{
//                            width: 1%;
//                        }
//                        .td6{
//                            width: 24%;
//                        }
//                    </style>
//                </head>';
        $html = "";

        $material = [];  // 存放所有物料信息的
        $materialV = []; // 存放中间物料信息的
        $i = 1;
        foreach ($materialData as $k => $v){
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
            $tmp = $baseMsg["source_type"] == 2 ? "其他调拨":"领料调拨";
            $tips = substr($baseMsg['tips'], strpos($baseMsg['tips'],'('));
            $html .= '<div class="onePage" style="page:one">
                    <div class="oneBill">
                        <div class="title">调拨单</div>
                        <table class="baseMsg">
                            <tr class="userMsg">
                                <td width="10%">编号：</td>
                                <td width="18%">' . $baseMsg["transfer_id"] . '</td>
                                <td width="10%">日期：</td>
                                <td width="18%">' . date("Y/m/d",$baseMsg["create_time"]) . '</td>
                                <td width="10%">调拨类型：</td>
                                <td width="26%">' . $tmp . '</td>
                            </tr>
                           
                            <tr class="userMsg">
                                <td width="10%">调出仓库：</td>
                                <td width="18%">' . $value[0]["rep_name_out"] . '</td>
                                <td width="10%">调入仓库：</td>
                                <td width="18%">' . $value[0]["rep_name_in"] . '</td>
                                <td width="10%">用途：</td>
                                <td width="26%">' . $tips . '</td>
                            </tr>
                        </table>
                        <table class="materialMsg">
                            <tr>
                                <td>序号</td>
                                <td>物料编码</td>
                                <td>物料名称</td>
                                <td>规格型号</td>
                                <td>数量</td>
                                <td>备注</td>
                            </tr>';

            $i = 1;
            foreach ($value as $k => $v){
                $html .= '<tr>
                        <td>' . $i . '</td>
                        <td>' . $v["product_no"] . '</td>
                        <td>' . $v["product_number"] . '</td>
                        <td>' . $v["product_name"] . '</td>
                        <td>' . $v["num"] . '</td>
                        <td>' . $v["remark"] . '</td>
                    </tr>';
                $i++;
            }

            $html .=  '</table>
                    <table  class="baseMsg">
                        <tr class="userMsg">
                            <td width="6%">审核：</td>
                            <td width="19%">' . $baseMsg["auditor_name"] . '</td>
                            <td width="6%">验收：</td>
                            <td width="19%">' . $baseMsg["check_name"] . '</td>
                            <td width="6%">保管：</td>
                            <td width="19%">' . $baseMsg["keep_name"] . '</td>
                            <td width="6%">制单：</td>
                            <td width="19%">' . $baseMsg["create_name"] . '</td>
                        </tr>
                    </table>
                </div>
            </div>';
        }

        return $html;
//        $html .= '</html>';

//        $mpdf->WriteHTML($html);
//        $fileName = "调拨单.pdf";
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