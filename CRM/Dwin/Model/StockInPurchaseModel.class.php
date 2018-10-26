<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/17
 * Time: 上午11:56
 */

namespace Dwin\Model;


use Think\Model;

class StockInPurchaseModel extends StockInModel
{

    protected $validatePurchase = [
        ['id',                  'require', '主键', 1],
        ['stock_in_id',         'require', '入库编号非法', 1, 'unique'],
        ['cate',                'require', '入库类别id不能为空', 0],
        ['keep_id',             'require', '验收人未选择',1],
        ['check_id',            'require', '保管人未选择',1],
        ['auditor',             'require', '审核人不能为空',1],
        ['create_id',           'require', '创建人不能为空', 0]
    ];

    protected $rulesPurchase = [
        ['create_name','getCreateName',3,'callback'],
        ['create_id','getCreateId',3,'callback'],
        ['create_time','time',3,'function'],
        ['audit_status', '0'],
        ['update_time','time',3,'function'], // 对update_time字段在更新的时候写入当前时间戳
    ];

    /**
     * addStockIn 添加入库单据（基本表信息）
     * Created by
     * User: ma xu
     * Time: 2018.07.09
     * @param $postsData
     * @return array
     */

    public function validateBase($data)
    {
        return $this->validate($this->validatePurchase)->create($data);
    }


    public function addStockInPurchaseTrans($baseData, $materialData)
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
        $purchaseOrderModel = new PurchaseOrderModel();
        $resetRst = $purchaseOrderModel->resetStockStatus($baseData['source_id']);

        if ($resetRst === false) {
            $this->rollback();
            $this->error = $purchaseOrderModel->getError();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 获取入库审核列表页信息
     */
    public function getAuditList($condition, $start, $length, $order){
        $map['a.is_del']       = ['eq', self::NO_DEL];
        $map['a.audit_status'] = ['eq', self::TYPE_NOT_AUDIT];
        $count = $this->alias('a')->where($map)->count();
        $filterCount = $count;
        if(strlen(trim($condition)) != 0){
            $map['a.stock_in_id|a.create_name'] = ['like', "%" . $condition . "%"];
            $filterCount = $this->alias('a')->where($map)
                ->join('LEFT JOIN crm_purchase_order o ON o.id = a.source_id')
                ->count();
        }

        $field = "a.id,
                  a.cate,
                  a.cate_name,
                  a.stock_in_id,
                  a.source_id,
                  a.other_bill,
                  from_unixtime(a.pay_time) pay_time,
                  a.pay_condition,
                  a.intercourse_subject,
                  a.print_time,
                  a.batch,
                  a.create_id,
                  a.create_name,
                  from_unixtime(a.create_time) create_time,
                  a.tips,
                  a.auditor,
                  a.auditor_name,
                  a.audit_status,
                  a.audit_tips,
                  from_unixtime(a.audit_time) audit_time,
                  a.keep_id,
                  a.keep_name,
                  a.charge_id,
                  a.charge_name,
                  a.account_id,
                  a.account_name,
                  a.check_id,
                  a.check_name,
                  a.business_id,
                  a.business_name,
                  from_unixtime(a.update_time) update_time,
                  o.supplier_name,o.purchase_order_id,o.purchase_mode,o.purchase_type
                ";
        $data = $this->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_purchase_order o ON o.id = a.source_id')
            ->where($map)
            ->order($order)
            ->limit($start, $length)
            ->select();
        /** 后台传输局到前台
        @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/

        return [$data, $count, $filterCount];
    }

    public function getBaseInfo($stockId)
    {
        $map['a.is_del'] = ['EQ', self::NO_DEL];
        $map['a.id']     = ['EQ', $stockId];
        $field = "a.id,
                  a.cate,
                  a.cate_name,
                  a.stock_in_id,
                  a.source_id,
                  a.other_bill,
                  from_unixtime(a.pay_time) pay_time,
                  a.pay_condition,
                  a.intercourse_subject,
                  a.print_time,
                  a.batch,
                  a.create_id,
                  a.create_name,
                  from_unixtime(a.create_time) create_time,
                  a.tips,
                  a.auditor,
                  a.auditor_name,
                  a.audit_status,
                  a.audit_tips,
                  from_unixtime(a.audit_time) audit_time,
                  a.keep_id,
                  a.keep_name,
                  a.charge_id,
                  a.charge_name,
                  a.account_id,
                  a.account_name,
                  a.check_id,
                  a.check_name,
                  a.business_id,
                  a.business_name,
                  from_unixtime(a.update_time) update_time,
                  o.supplier_name,o.purchase_order_id,o.purchase_mode,o.purchase_type";
        return $this->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_purchase_order o ON o.id = a.source_id')
            ->where($map)
            ->find();
    }

    public function checkPurchaseStockIn($stockId)
    {
        $map['id'] = ['EQ', $stockId];
        $field = [
            'update_time' => time(),
            'audit_status' => self::TYPE_QUALIFIED
        ];
        return $this->where($map)->setField($field);
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
            ifnull(o.purchase_order_id,'无源单') s_id,
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
            a.check_name,
            a.other_bill
        ";
        $count = $this->alias($alias)->where($map)->count();
        if (trim($config['search'])) {
            $map['a.stock_in_id|a.create_name|a.auditor_name|a.cate_name|o.purchase_order_id'] = ['LIKE', "%" . trim($config['search']) . "%"];
        }

        $data = $this->alias($alias)
            ->field($field)
            ->where($map)
            ->join('LEFT JOIN crm_purchase_order o ON o.id = a.source_id')
            ->order($config['order'])
            ->limit($config['start'], $config['length'])
            ->select();

        $filterCount = $this->alias($alias)->join('LEFT JOIN crm_purchase_order o ON o.id = a.source_id')->where($map)->count();
        return [$data, $count, $filterCount];
    }

    /**
     * 根据入库单id获取信息
     * @param $id
     * @param array $map
     * @return mixed
     */
    public function getBaseDataById($id, $map = []){
        $map['p.id'] = ['eq', $id];
        $map['p.is_del'] = ['eq',self::NO_DEL];
        $data = $this->alias("p")
            ->field("p.*, o.supplier_name, o.purchase_order_id, c.contract_id")
            ->join("left join crm_purchase_order o on o.id = p.source_id")
            ->join("left join crm_purchase_contract c on c.id = o.contract_pid")
            ->where($map)
            ->select()[0];
        return $data;
    }

    /**
     * 打印外购入库单
     * @param $baseMsg
     * @param $materialData
     * @param $repId
     * @return string
     */
    public function printingToPdfEx($baseMsg, $materialData){
        // 先去查当前外购订单中所需数量
        $productModel = new PurchaseOrderProductModel();
        $productData = $productModel->getAllMaterialBaseMsg($baseMsg['source_id']);
        $productMap = array_column($productData, "number", "product_id");

//        Vendor('mpdf.mpdf');
//        //设置中文编码
//        $mpdf=new \mPDF('zh-cn','216mm 93mm', 0, '宋体', 0, 0 ,0,0,0,0);
//        $mpdf->useAdobeCJK = true;

        $html = '';

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
                            <div class="title">外购入库单</div>
                            <table class="baseMsg">
                                <tr class="userMsg">
                                    <td style="width: 11%;">供应商：</td>
                                    <td style="width: 22%;">' . $baseMsg["supplier_name"] . '</td>
                                    <td style="width: 11%;">日期：</td>
                                    <td style="width: 22%;">' . date("Y/m/d",$baseMsg["create_time"]) . '</td>
                                    <td style="width: 11%;">入库单编号：</td>
                                    <td style="width: 22%;">' . $baseMsg["stock_in_id"] . '</td>
                                </tr>
                            </table>
                            <table class="materialMsg">
                                <tr>
                                    <td>序号</td>
                                    <td>合同编号</td>
                                    <td>订单编号</td>
                                    <td>物料编码</td>
                                    <td>物料名称</td>
                                    <td>规格型号</td>
                                    <td>应收</td>
                                    <td>实收</td>
                                    <td>发货仓库</td>
                                </tr>';

                $i = 1;
                foreach ($value as $k => $v){
                    $html .= '<tr>
                            <td>' . $i . '</td>
                            <td>' . $baseMsg["contract_id"] . '</td>
                            <td>' . $baseMsg["purchase_order_id"] . '</td>
                            <td>' . $v["product_no"] . '</td>
                            <td>' . $v["product_number"] . '</td>
                            <td>' . $v["product_name"] . '</td>
                            <td>' . $productMap[$v["product_id"]] . '</td>
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
//
//        $mpdf->WriteHTML($html);
//        $fileName = "外购入库单.pdf";
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