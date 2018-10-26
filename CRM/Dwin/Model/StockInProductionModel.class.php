<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/17
 * Time: 上午11:56
 */

namespace Dwin\Model;


class StockInProductionModel extends StockInModel
{

    public function validateBase($data)
    {
        return $data1 = $this->auto($this->rules)->validate($this->validate)->create($data);
    }

    public function addProductionStockInTrans($baseData, $materialData)
    {
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
        if (StockInRecordModel::SOURCE_PRODUCTION_TYPE == $baseData['cate']) {
            $taskModel = new ProductionTaskModel();
            $taskUpdRst = [];
            foreach ($materialData as $datum) {
                $taskUpdRst[] = $taskModel->updateCompleteQuantity($baseData, $datum);
            }


            if (in_array(false,$taskUpdRst)) {
                $this->rollback();
                $this->error = "修改updateNumber数据出错";
                return false;
            }
            $resetStatusRst = $taskModel->resetTaskStatus($baseData['source_id']);
            if ($resetStatusRst === false) {
                $this->rollback();
                $this->error = $taskModel->getError();
                return false;
            }
        }

        /****---------------------------此部分逻辑为由于其他入库不需要二级审核，自动提交数据到stock in record中--------------------------------------------------***/
        $recordData = $stockMaterialModel->getInsertStockDataWithSourceId($baseData['id'],'crm_stock_in_production');
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

        $this->commit();
        return true;
    }


    public function countNumWithProductIdAndSourceId($config)
    {
        $materialAlias = $config['alias'];
        $map['crm_stock_in_production.source_id'] = ['EQ', $config['taskId']];
        $map['crm_stock_in_production.audit_status'] = ['NEQ', 1];

        $map[$materialAlias . '.product_id'] = ['EQ', $config['productId']];
        return $this->where($map)
            ->join("LEFT JOIN crm_stock_material $materialAlias ON $materialAlias.source_id = crm_stock_in_production.id and $materialAlias.type = " . StockMaterialModel::TYPE_STOCK_IN)
            ->field("ifnull(sum({$materialAlias}.num),0) num1")
            ->select()[0]['num1'];
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
            ifnull(task.task_id,'无源单') s_id,
            a.print_time,
            a.cate,
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
            a.production_line_name,
            a.production_group_name
        ";
        $count = $this->alias($alias)->where($map)->count();
        if (trim($config['search'])) {
            $map['a.stock_in_id|a.create_name|a.auditor_name|a.cate_name|task.task_id'] = ['LIKE', "%" . trim($config['search']) . "%"];
        }

        $data = $this->alias($alias)
            ->field($field)
            ->where($map)
            ->join('LEFT JOIN crm_production_task task ON task.id = a.source_id')
            ->order($config['order'])
            ->limit($config['start'], $config['length'])
            ->select();

        $filterCount = $this->alias($alias)->join('LEFT JOIN crm_production_task task ON task.id = a.source_id')->where($map)->count();
        return [$data, $count, $filterCount];
    }


    /**
     * 获取入库单基本信息
     * @param $id
     * @return mixed
     */
    public function getBaseDataById($id){
        $map['p.id'] = ['eq', $id];
        $map['p.is_del'] = self::NO_DEL;

        $data = $this->alias("p")
            ->field("p.*,t.task_number,t.task_id order_pid,line.production_line")
            ->join("left join crm_production_task t on t.id = p.source_id")
            ->join("LEFT JOIN crm_production_line line on line.id = t.task_group")
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
        Vendor('mpdf.mpdf');
        //设置中文编码
        $mpdf=new \mPDF('zh-cn','216mm 93mm', 0, '宋体', 0, 0 ,0,0,0,0);
        $mpdf->useAdobeCJK = true;

//        $html = '<html>
//                <head>
//                    <meta charset="utf-8">';
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
                            <div class="title">产品入库单</div>
                            <table class="baseMsg">
                                <tr class="userMsg">
                                    <td class="td1">交货单位：</td>
                                    <td class="td2">' . $baseMsg["production_line"] . '</td>
                                    <td class="td3">入库单编号：</td>
                                    <td class="td4">' . $baseMsg["stock_in_id"] . '</td>
                                </tr>
                                <tr class="userMsg">
                                    <td class="td5">源单类型：</td>
                                    <td class="td6">' . $baseMsg["cate_name"] . '</td>
                                    <td class="td7">日期：</td>
                                    <td class="td8">' . date("Y/m/d",$baseMsg["create_time"]) . '</td>
                                </tr>
                            </table>
                            <table class="materialMsg">
                                <tr>
                                    <td rowspan="2">源单编号</td>
                                    <td rowspan="2">物料编码</td>
                                    <td rowspan="2">物料名称</td>
                                    <td rowspan="2">规格型号</td>
                                    <td colspan="2">数量</td>
                                    <td rowspan="2">发货仓库</td>
                                </tr>
                                <tr>
                                    <td>应收</td>
                                    <td>实收</td>
                                </tr>';

                $i = 1;
                foreach ($value as $k => $v){
                    $html .= '<tr>
                            <td>' . $baseMsg["order_pid"] . '</td>
                            <td>' . $v["product_no"] . '</td>
                            <td>' . $v["product_number"] . '</td>
                            <td>' . $v["product_name"] . '</td>
                            <td>' . $baseMsg["task_number"] . '</td>
                            <td>' . $v["num"] . '</td>
                            <td>' . $v["repertory_name"] . '</td>
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
        }
        return $html;

//        $html .= '</html>';

        $rst = $mpdf->WriteHTML($html);
        $fileName = "产品入库单.pdf";

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