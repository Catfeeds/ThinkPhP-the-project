<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/17
 * Time: 上午11:56
 */

namespace Dwin\Model;

use think\Exception;
use Think\Model;

class StockInOtherModel extends StockInModel
{

    public function validateBase($data)
    {
        return $this->validate($this->validateOther)->create($data);
    }


    public function addStockInOtherTrans($baseData, $materialData)
    {
        try {
            $this->startTrans();
            $stockInAddRst = $this->add($baseData);
            if ($stockInAddRst === false) {
                $this->rollback();
                $this->error = "添加base数据出错";
                return false;
            }
            $stockMaterialModel = new StockMaterialModel();
            $stockMaterialAddRst = $stockMaterialModel->addAll($materialData);

            if ($stockMaterialAddRst === false) {
                $this->rollback();
                $this->error = "添加material数据出错";
                return false;
            }
            /****---------------------------此部分逻辑为由于其他入库不需要二级审核，自动提交数据到stock in record中--------------------------------------------------***/
            $recordData = $stockMaterialModel->getInsertStockDataWithSourceId($baseData['id'],'crm_stock_in_other');
            $recordInModel = new StockInRecordModel();
            $preData = $recordInModel->getAddData($recordData);
            $validateRecordData = $recordInModel->validateBase($preData);
            if ($validateRecordData === false) {
                $this->rollback();
                $this->error = $recordInModel->getError();
                return false;
            }
            $preAddRst = $recordInModel->addAll($validateRecordData);
            if ($preAddRst === false) {
                $this->rollback();
                $this->error = "添加record数据出错";
                return false;
            }


            /*---------------------------------------此部分逻辑20180905添加 当售后入库申请时，更新source_id对应的入库申请为已下推------------------------------------------*/
            if (StockInOtherApplyModel::STOCK_SOURCE_AFTER_SALE == $baseData['type_id']) {
                $applyModel = new StockInOtherApplyModel();
                $resetRst = $applyModel->resetApplyStatus($baseData['source_id']);
                if (false === $resetRst) {
                    $this->rollback();
                    $this->error = $applyModel->getError();
                    return false;
                }
            }
            $this->commit();
            return true;
        } catch (Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }

    }

    /**
     * 获取对应待审核记录
     */
    public function getAuditData($config)
    {
        $map['a.is_del'] = ['EQ', self::NO_DEL];
        $map['a.audit_status'] = ['EQ', self::TYPE_QUALIFIED];

        $alias = 'a';
        $field = "
            a.id,
            a.stock_in_id,
            ifnull(a.source_id,'无源单') s_id,
            a.cate,
            a.print_time,
            a.cate_name,
            batch,
            a.create_id,
            a.create_name,
            from_unixtime(a.create_time) c_time,
            from_unixtime(a.update_time) update_time,
            a.tips,
            a.audit_status,
            a.keep_id,
            a.keep_name,
            a.check_id,
            a.check_name,
            crm_dept.name dept_name,
            supplier.supplier_name,
            type_name stock_in_other_name
        ";
        $count = $this->alias($alias)->where($map)->count();
        if (trim($config['search'])) {
            $map['a.stock_in_id|a.create_name|a.auditor_name|a.cate_name'] = ['LIKE', "%" . trim($config['search']) . "%"];
        }

        $data = $this->alias($alias)
            ->field($field)
            ->where($map)
            ->join('LEFT JOIN crm_dept ON crm_dept.id = a.dept_id')
            ->join('LEFT JOIN crm_purchase_supplier supplier ON supplier.id = a.supplier_id')
            ->order($config['order'])
            ->limit($config['start'], $config['length'])
            ->select();

        $filterCount = $this->alias($alias)->join('LEFT JOIN crm_dept ON crm_dept.id = a.dept_id')
            ->join('LEFT JOIN crm_purchase_supplier supplier ON supplier.id = a.supplier_id')->where($map)->count();
        return [$data, $count, $filterCount];
    }

    /**
     * 根据入库单id获取信息
     * @param $id
     * @param array $map
     * @return mixed
     */
    public function getBaseDataById($id, $map = []){
        $map['o.id'] = ['eq', $id];
        $map['o.is_del'] = self::NO_DEL;
        $data = $this->alias("o")
            ->field("o.*, s.supplier_name")
            ->join("left join crm_purchase_order s on s.id = o.source_id")
            ->where($map)
            ->select()[0];
        return $data;
    }


    /**
     * 打印其他入库单
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

        // 人员信息
        $staffModel = new StaffModel();
        $staffData = $staffModel->field("id,name")->select();
        $staffMap = array_column($staffData,"name", "id");

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
                            <div class="title">其他入库单</div>
                            <table class="baseMsg">
                                <tr class="userMsg">
                                    <td width="11%">入库类型：</td>
                                    <td width="22%">' . $baseMsg["cate_name"] . '</td>
                                    <td width="11%">备注：</td>
                                    <td width="22%" colspan="3">' . $baseMsg["tips"] . '</td>
                                </tr>
                                <tr class="userMsg">
                                    <td width="11%"">供应商：</td>
                                    <td width="22%">' . $baseMsg["supplier_name"] . '</td>
                                    <td width="11%">日期：</td>
                                    <td width="22%">' . date("Y/m/d",$baseMsg["create_time"]) . '</td>
                                    <td width="11%">入库单编号：</td>
                                    <td width="22%">' . $baseMsg["stock_in_id"] . '</td>
                                </tr>
                            </table>
                            <table class="materialMsg">
                                <tr>
                                    <td>序号</td>
                                    <td>物料编码</td>
                                    <td>物料名称</td>
                                    <td>规格型号</td>
                                    <td>数量</td>
                                    <td>发货仓库</td>
                                </tr>';

                $i = 1;
                foreach ($value as $k => $v){
                    $html .= '<tr>
                            <td>' . $i . '</td>
                            <td>' . $v["product_no"] . '</td>
                            <td>' . $v["product_number"] . '</td>
                            <td>' . $v["product_name"] . '</td>
                            <td>' . $v["num"] . '</td>
                            <td>' . $v["repertory_name"] . '</td>
                        </tr>';
                    $i++;
                }

                $html .=  '</table>
                        <table  class="baseMsg">
                            <tr class="userMsg">
                                <td width="6%">审核：</td>
                                <td width="14%">' . $baseMsg["auditor_name"] . '</td>
                                <td width="6%">记账：</td>
                                <td width="14%">' . $staffMap[$baseMsg["bookkeeper_id"]] . '</td>
                                <td width="6%">验收：</td>
                                <td width="14%">' . $baseMsg["check_name"] . '</td>
                                <td width="6%">保管：</td>
                                <td width="14%">' . $baseMsg["keep_name"] . '</td>
                                <td width="6%">制单：</td>
                                <td width="14%">' . $baseMsg["create_name"] . '</td>
                            </tr>
                        </table>
                    </div>
                </div>';
            }
        }
        return $html;

        $mpdf->WriteHTML($html);
        $fileName = "其他入库单.pdf";

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
}