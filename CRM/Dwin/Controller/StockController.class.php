<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/10
 * Time: 13:01
 */
namespace Dwin\Controller;

use Dwin\Model\AuthRoleModel;
use Dwin\Model\DeptModel;
use Dwin\Model\MaterialBomSubModel;
use Dwin\Model\MaterialModel;
use Dwin\Model\MaterialSubstituteModel;
use Dwin\Model\MaxIdModel;
use Dwin\Model\OrderformModel;
use Dwin\Model\OrderproductModel;
use Dwin\Model\PrintingLogModel;
use Dwin\Model\ProductionLineModel;
use Dwin\Model\ProductionOrderModel;
use Dwin\Model\ProductionOrderProductModel;
use Dwin\Model\ProductionTaskModel;
use Dwin\Model\PurchaseOrderModel;
use Dwin\Model\PurchaseOrderProductModel;
use Dwin\Model\RepertorylistModel;
use Dwin\Model\StaffModel;
use Dwin\Model\StockAuditModel;
use Dwin\Model\StockAuditOutModel;
use Dwin\Model\StockInModel;
use Dwin\Model\StockInOtherApplyMaterialModel;
use Dwin\Model\StockInOtherApplyModel;
use Dwin\Model\StockInOtherModel;
use Dwin\Model\StockInProductionModel;
use Dwin\Model\StockInPurchaseModel;
use Dwin\Model\StockInRecordModel;
use Dwin\Model\StockIoCateModel;
use Dwin\Model\StockMaterialModel;
use Dwin\Model\StockModel;
use Dwin\Model\StockOutModel;
use Dwin\Model\StockOutOrderformModel;
use Dwin\Model\StockOutProduceModel;
use Dwin\Model\StockOutOtherApplyMaterialModel;
use Dwin\Model\StockOutOtherApplyModel;
use Dwin\Model\StockOutOtherModel;
use Dwin\Model\StockOutProductionModel;
use Dwin\Model\StockOutRecordModel;
use Dwin\Model\StockTransferMaterialModel;
use Dwin\Model\StockTransferModel;

class StockController extends CommonController {

    const SUCCESS_STATUS = 1;
    const FAIL_STATUS    = -1;
    const AUDIT_ROLE = '43,44,45';
    const STOCK_AUDIT_TABLE_NAME = 'stock_audit';
    const STOCK_AUDIT_OUT_TABLE_NAME = 'stock_audit_out';
    /* OUT_TYPE 出入库类别字符串（id以逗号连接）*/
    const OUT_TYPE = "2";
    const IN_TYPE  = "1";

    const NOT_DEL_STATUS = 0;
    /**
     * stock_audit 字段audit_status
     * P : 待审核 N：审核驳回 Y：审核通过
     * */
    const AUDIT_P_STATUS = '1';
    const AUDIT_Y_STATUS = '2';
    const AUDIT_N_STATUS = '3';
    /* 物流员 库管员RoleId*/
    const LOGISTICS_ROLE_IDS = [47];    // 47: 物流员
    const MANAGER_ROLE_IDS   = [44];      // 44: 库管员
    /* 可查看所有出库记录的职位*/
    const ALL_OUT_RECORD_AUTH = [1,2,3,8,13,16,34,37,40,43,44,45,46,47,48,49,51,21];

    const LOGISTICS_LINE = [43,44,45,46,47,48];

    /**
     * 出库单源单类型Map
     */
    const STOCK_OUT_OTHER      = 1;  // 出库申请单
    const STOCK_OUT_ORDER_FORM = 2;  // 销售出库单
//    const STOCK_OUT_PRODUCE    = 3;  // 生产任务单
    const STOCK_OUT_PRODUCTION = 4;  // 生产任务单

    public static $sourceTypeMap = [
        self::STOCK_OUT_OTHER => '出库申请单',
        self::STOCK_OUT_ORDER_FORM => '销售出库单',
//        self::STOCK_OUT_PRODUCE => '生产任务单',
        self::STOCK_OUT_PRODUCTION => '生产任务单',
    ];

    /**
     * 添加出入库申请
     * @param $product_name         string  产品名
     * @param $product_id
     * @param $type   int  入库（冗余了，只有入库）
     * @todo 入库权限判定
     * @20180904 关闭接口 不直接添加入库记录
     */
    private function addAudit($product_name, $product_id, $type)
    {
        //die('该功能未启用');

        if (IS_POST){
            $params = I('post.');

            $stockAuditModel = new StockAuditModel();
            $result = $stockAuditModel -> addAudit($params);
            if ($result) {
                $this->returnAjaxMsg('出库记录提交成功', self::SUCCESS_STATUS);
            }else{
                $this->returnAjaxMsg($stockAuditModel -> getError(), self::FAIL_STATUS);
            }
        } else {
            $audit_type = 'RK';
            $orderInfoArr = $this->getOrderNumber(self::STOCK_AUDIT_TABLE_NAME);
            $id = $orderInfoArr['orderId'];
            $audit_order_number = $audit_type . $orderInfoArr['orderString'];
            // 生产入库生产线
            $putinProductionLine = M('production_line') -> select();
            foreach ($putinProductionLine as $key => $value) {
                $putinProductionLine[$key]['arr'] = $value['id'] . '_' . $value['putin_production_line'];
            }
//            // 审核员
//            $auditor = [];
//            $auditorIds = explode(',', $warehouse['logistics_staff_id']);
//            foreach ($auditorIds as $key => $value) {
//                $auditor[] = M('staff') -> field('id, name') -> find($value);
//            }
            // todo: 审核员暂时是只有他自己可以选,生产入库也是这样
            $auditor = [
                ['id' => session('staffId'),'name' => session('nickname')]
            ];

            // 查找所有出入库分类
            $cate = M('stock_io_cate') -> where(['type' => self::IN_TYPE]) -> select();
            $map['_string'] = "1 = 1";
            $warehouseData = M('repertorylist') -> where($map)->field('rep_id,repertory_name')->select();
            $this->assign(compact('putinProductionLine', 'product_name', 'type', 'product_id','auditor', 'cate', 'audit_order_number', 'warehouseData', 'id', 'url'));
            $this->display();
        }
    }

    /**
     * 入库记录审核
     * @param $auditID
     * @param $audit_status
     * @param string $audit_tips
     *  @20180904 关闭接口 入库记录不再影响库存
     */
    private function editStockAuditStatus($auditID, $audit_status, $audit_tips = '')
    {

        if (IS_POST) {
            $model = new StockAuditModel();
            $result = $model -> changeAuditStatus($auditID, $audit_status, $audit_tips);
            if ($result) {
                $msg = '处理成功';
                $status = self::SUCCESS_STATUS;
            } else {
                $msg = $model -> getError();
                $status = self::FAIL_STATUS;
            }
            $this->ajaxReturn([
                'status' => $status,
                'msg'    => $msg
            ]);
        }
    }

    /**
     * 批量处理出入库申请
     * 20180904 关闭接口 入库记录不再影响库存
     */
    private function editStockAuditStatusMulti()
    {
        if (IS_POST){
            $params = I('post.data');
            $model = new StockAuditModel();
            $finalResult = true;
            foreach ($params as $key => $value) {
                $res = $model -> changeAuditStatus($value['auditID'], $value['audit_status'], $value['tips']);
                if (!$res) {
                    $finalResult = false;
                }
            }
            if ($finalResult){
                $msg = '全部处理完成';
                $status = self::SUCCESS_STATUS;
            }else{
                $msg = '部分审核处理失败,请单独处理';
                $status = self::FAIL_STATUS;
            }
            $this->ajaxReturn([
                'status' => $status,
                'msg'    => $msg
            ]);
        }
    }

    /**
     *  get add data
     * 1 check data can be insert ? insert : return reason;
     * 2 processing data and find data cannot be insert;
     * 3 inset data
     * @todo 根据销货单提交出库记录。后续变成物流员根据销货单制出库单。库房根据出库单直接提交出库记录。
     * @todo 20180904 关闭接口 出库记录不再影响库存
     */
    private function addStockOut()
    {
        $orderModel = new OrderformModel();
        $inventoryRecordModel = new StockAuditOutModel();
        if (IS_POST) {
            $this->posts = I('post.');

            if (!empty($this->posts['productData'][0]['action_order_number'])) {
                $stockData     = $inventoryRecordModel->getStockRemainingDataByOrderId($this->posts['productData'][0]['action_order_number']);
                $processedData = $inventoryRecordModel->getStockAddData($this->posts['productData'], $stockData);
            } else {
                $productFilter['product_id'] = ['EQ', $this->posts['productData'][0]['product_id']];
                $productFilter['warehouse_number'] = ['eq', $this->posts['productData'][0]['warehouse_number']];
                $stockData = M('stock')->where($productFilter)->field('product_id,product_name,stock_number + o_audit can_insert_num')->select();
                $processedData = $inventoryRecordModel->getStockAddData($this->posts['productData'], $stockData);
            }
            /**
             * @todo : 提交出库信息前校验权限
             *
             */
            if (count($processedData['addData']) != 0) {

                foreach ($processedData['addData'] as &$value) {
                    if (empty($value['audit_order_number'])) {
                        $data = $this->getOrderNumber(self::STOCK_AUDIT_OUT_TABLE_NAME);
                        unset($value['insert_flag']);
                        $value['audit_order_number'] = "CK" . $data['orderString'];
                        $value['id'] = $data['orderId'];
                    }
                }
                $insertRst = $inventoryRecordModel->insertStockOutData($processedData['addData']);

                if ($insertRst !== false) {
                    $msg = array(
                        'status' => 200,
                        'msg'    => "提交成功" . $processedData['unAddMsg']
                    );
                } else {
                    $msg = array(
                        'status' => 400,
                        'msg'    => "提交失败"
                    );
                }
            } else {
                $msg = array(
                    'status' => 300,
                    'msg'    => $processedData['unAddMsg']
                );
            }
            $this->ajaxReturn($msg);

        } else {
            $auditor = $this->getAuditor(self::AUDIT_ROLE);
            $cate = M('stock_io_cate') -> where(['type' => self::OUT_TYPE]) -> select();

            $this->assign(compact('auditor','cate'));
            if (!empty(I('get.orderId'))) {
                $orderId = (int)I('get.orderId');
                $returnDataSet    = ['orderData','productData'];
                $orderDetailData  = $orderModel->getOrderPendingDataById($orderId, "CPO" . $orderId, $returnDataSet);
                foreach ($orderDetailData['productData'] as &$item) {
                    $filter['product_id'] = array('eq', $item['product_id']);
                    $item['warehouse'] = M('stock')->where($filter)->field('warehouse_name,warehouse_number')->select();
                }

                $this->assign($orderDetailData);
            } else {
                die('非法操作');
            }
            $this->display();
        }
    }

    /**
     * 出库记录添加页面（无订单）
     * @todo
     * @todo 20180904 关闭接口 出库记录不再影响库存
     */
    private function addStockOutWithoutOrder()
    {
        if (IS_POST) {
            die('非法操作');
        } else {
            $auditor = $this->getAuditor(self::AUDIT_ROLE);
            $cate = M('stock_io_cate') -> where(['type' => self::OUT_TYPE]) -> select();

            $this->assign(compact('auditor','cate'));
            if (!empty(I('get.productId'))) {
                $productId = (int)I('get.productId');
                $filter['stock.product_id'] = ['EQ', $productId];
                $this->field = "stock.product_id,
                                product.product_name, 
                                stock_number, 
                                o_audit, 
                                stock_number + o_audit stock_all_num,
                                warehouse_name,
                                warehouse_number";
                $productInfo = M('stock')->alias('stock')
                    ->join('LEFT JOIN crm_material product ON product.product_id = stock.product_id')->where($filter)->field($this->field)->select();

                foreach($productInfo as $key => $value) {
                    $warehouse[$key]['warehouse_name'] = $value['warehouse_name'];
                    $warehouse[$key]['warehouse_number'] = $value['warehouse_number'];
                }
                $this->assign(compact('productInfo','warehouse'));
            } else {
                die('非法操作');
            }
            $this->display();
        }
    }

    /**
     * 出库申请审核页面
     * @todo 待办事项使用该页面
     *  20180904 关闭接口 出库入库记录无需审核
     */
    private function showStockOutAuditList()
    {
        if(IS_POST){
            $params = I('post.');
            if (isset($params['orderId'])) {
                $orderModel = new OrderformModel();
                $returnDataSet = ['productData', 'stockOutData'];
                $orderDetailData = $orderModel->getOrderPendingDataById(str_replace("CPO","",$params['orderId']),$params['orderId'], $returnDataSet);
                $this->ajaxReturn($orderDetailData);
            }
            $model = new StockAuditOutModel();
            $this->sqlCondition = $this->getSqlCondition($params);
            $this->whereCondition['audit_status'] = ['EQ', self::AUDIT_P_STATUS];
            $this->whereCondition['auditor']      = ['EQ', $this->staffId];
            $this->whereCondition['type']         = ['EQ', self::OUT_TYPE];
            $this->whereCondition['is_del']       = ['EQ', self::NOT_DEL_STATUS];

            $data['recordsTotal'] = $model->where($this->whereCondition)->count();

            if ($this->sqlCondition['where']) {
                $this->whereCondition['action_order_number|id|warehouse_number|warehouse_name|product_name|product_number|product_no'] = ['LIKE',"%" . $this->sqlCondition . "%"];
            }
            $data['recordsFiltered'] = $model->where($this->whereCondition)->count();

            $this->field  = "crm_stock_audit_out.id, ifnull(audit_order_number,crm_stock_audit_out.id) audit_order_number,action_order_number,product_name,num,warehouse_name,cate_name,ifnull(tips,'无'),audit_status,audit_tips,tips,from_unixtime(update_time) update_time,audit_status";
            $data['data'] = $model->getStockData($this->field, $this->whereCondition, $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);
            foreach ($data['data'] as $key => &$value) {
                $value['DT_RowId'] = $value['action_order_number'];
            }

            $data['draw'] = (int) $params['draw'];
            $this->ajaxReturn($data);
        }else{
            $repertoryModel = new RepertorylistModel();
            $repData = $repertoryModel->getUserRightsRepertoryList($this->staffId);
            $this->assign('repertoryList', $repData);
            $this->display();
        }
    }

    /**
     * 1 判断是否有权限、是否有重复审核
     * 2 获取更新数据
     * 3 执行更新操作
     * 20180904 关闭接口 出库入库记录无需审核
     * */
    private function checkStockOutAudit()
    {
        $this->posts = I('post.');
        $stockAuditModel = new StockAuditOutModel();
        $orderFormModel  = new OrderformModel();
        $authorityCheckRst = $stockAuditModel->getAuthority($this->posts['auditData'], $this->staffId);
        if ($authorityCheckRst['status'] == 400) {
            $this->ajaxReturn($authorityCheckRst);
        }
        $updateData = $stockAuditModel->getStockUpdateData($this->posts);
        if (count($updateData) == 0) {
            $this->returnAjaxMsg('未提交要审核的数据', 400);
        }
        $updateRst  = $stockAuditModel->checkStockOutData($updateData);
        if ($updateRst['status'] != 200) {
            $this->ajaxReturn($updateRst);
        }
        $updateOrderStatusRst = $orderFormModel->updateStatusWithStockLog($updateData);
        $this->ajaxReturn($updateOrderStatusRst);
    }

    /**
     * 20180904 关闭接口 删除出库入库记录不再使用 如果要开启，需要调整事务，现代码中程序会影响库存
     */
    private function delStockOutItem()
    {

        $stockOutModel = new StockAuditOutModel();

        $params = I('post.data');
        $ids = [];
        foreach ($params as $key => $value) {
            $ids[] = $value['id'];
        }
        $rst = $stockOutModel->deleteTrans($ids);
        $this->ajaxReturn($rst);
    }

    /**
     * 删除入库申请
     * @param   $data   array   删除的申请的数组
     * 20180904 关闭接口 删除出库入库记录不再使用 如果要开启，需要调整事务，现代码中程序会影响库存
     */
    private function delStockInItem()
    {
//        die("未开启");
        $params = I('post.data');
        $res = true;
        $model = new StockAuditModel();
        foreach ($params as $key => $value) {
            if ($res){
                $res = $model -> deleteAudit($value['id']);
            }
        }
        if ($res === false){
            $this->ajaxReturn([
                'status' => self::FAIL_STATUS,
                'msg' => '删除失败'
            ]);
        }else{
            $this->ajaxReturn([
                'status' => self::SUCCESS_STATUS,
                'msg' => '删除成功'
            ]);
        }
    }

    /* 出入库记录查看权限*/

    /**
     * 物流生产查看即时库存页面
    */
    public function stockIndex()
    {
        if (IS_POST) {
            $params = I('post.');
            $_map = [];
            $mapTableData = $this->dataTable($params, $_map);
            if ($params['repoID'] !== '') {
                $mapTableData['map']['warehouse_number'] = [['EQ', $params['repoID']]];
            }
            $model = new MaterialModel();
            $data['draw'] = (int) $params['draw'];
            $data['recordsTotal'] = $model -> count();
            $data['recordsFiltered'] = $model -> where($mapTableData['map']) -> count();
            $data['data'] = $model -> index($mapTableData['map'], $params['start'], $params['length'], $mapTableData['order']);
            $this->ajaxReturn($data);
        } else {
            $repertoryModel = new RepertorylistModel();
            $repoList = $repertoryModel->getRepBaseInfo();
            $this->assign(compact('repoList'));
            $this->display();
        }
    }

    /**
     * 即时库存数据导出excel
     */
    public function stockExportToExcel(){
        $rep = I("post.rep");
        $map = [];
        if(!empty($rep)){
           $map['stock.warehouse_number'] = ['eq', $rep];
        }

        $model = new MaterialModel();
        $data = $model->index($map,0,0);

        Vendor('PHPExcel.PHPExcel');//引入类
        Vendor('PHPExcel.PHPExcel_IOFactory');//引入类
//        Vendor('PHPExcel.Writer.Excel5');  // 后缀是xls
        Vendor('PHPExcel.Writer.Excel2007'); // 后缀是xlsx

        $objPHPExcel = new \PHPExcel();                        //初始化PHPExcel(),不使用模板

        $objActSheet = $objPHPExcel->getActiveSheet();

        $objActSheet->setCellValue("A1","物料编号");
        $objActSheet->setCellValue("B1","物料型号");
        $objActSheet->setCellValue("C1","实际库存");
        $objActSheet->setCellValue("D1","剩余库存");
        $objActSheet->setCellValue("E1","锁库数量");
        $objActSheet->setCellValue("F1","出库中数量");
        $objActSheet->setCellValue("G1","待入库");
        $objActSheet->setCellValue("H1","在生产数量");
        $objActSheet->setCellValue("I1","在返工数量");
        $objActSheet->setCellValue("J1","更新时间");

        if(!empty($rep)){
            $objActSheet->setCellValue("K1","库房名称");
        }

        $i = 2;
        $saveData = [];
        $time = time();
        foreach ($data as $k => $v){
            $objActSheet->setCellValue("A".$i,$v['product_no']);
            $objActSheet->setCellValue("B".$i,$v['product_name']);
            $objActSheet->setCellValue("C".$i,$v['all_number']);
            $objActSheet->setCellValue("D".$i,$v['stock_number']);
            $objActSheet->setCellValue("E".$i,$v['o_audit']);
            $objActSheet->setCellValue("F".$i,$v['out_processing']);
            $objActSheet->setCellValue("G".$i,$v['i_audit']);
            $objActSheet->setCellValue("H".$i,$v['production_number']);
            $objActSheet->setCellValue("I".$i,$v['rework_number']);
            $objActSheet->setCellValue("J".$i,$v['update_time']);
            if(!empty($rep)){
                $objActSheet->setCellValue("K".$i,$v['repertory_name']);
            }

            $i++;
            $saveData[] = [
                'product_no'        => $v['product_no'],
                'product_name'      => $v['product_name'],
                'repertory_name'    => $v['repertory_name'],
                'rep_pid'           => $v['warehouse_number'],
                'all_number'        => $v['all_number'],
                'stock_number'      => $v['stock_number'],
                'o_audit'           => $v['o_audit'],
                'out_processing'    => $v['out_processing'],
                'i_audit'           => $v['i_audit'],
                'production_number' => $v['production_number'],
                'rework_number'     => $v['rework_number'],
                'update_time'       => $v['update_time'],
                'create_time'       => $time,
            ];
        }

        $res = D("stock_resume")->addAll($saveData);
        if(!$res){
            $this->returnAjaxMsg("即时库存履历数据储存失败",400);
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $fileName = "即时物料信息". '_' . date('Ymd') . '.xlsx';
//        $fileName = iconv('utf-8', 'gb2312', $fileName);//文件名称

        // 1.保存至本地Excel表格
        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/excel/";
        if (!file_exists($rootPath)) {
            mkdir($rootPath, 777,true);
        }
        $objWriter->save($rootPath . $fileName);

        $this->returnAjaxMsg("下载成功",200,[
            'file_url' => UPLOAD_ROOT_PATH . "/excel/" . $fileName
        ]);
    }

    /**
     * 成品库库存查询即时库存
     */
    public function index()
    {
        if(IS_POST){
            $params = I('post.');
            $_map = [];
            $mapTableData = $this->dataTable($params, $_map);
            if ($params['repoID'] !== ''){
                $mapTableData['map']['warehouse_number'] = [['EQ', $params['repoID']]];
            } else {
                $mapTableData['map']['warehouse_number'] = ['IN', "K003,K004"];
            }
            $model = new MaterialModel();
            $data['draw'] = (int) $params['draw'];
            $data['recordsTotal'] = $model -> count();
            $data['recordsFiltered'] = $model -> where($mapTableData['map']) -> count();
            $data['data'] = $model -> index($mapTableData['map'], $params['start'], $params['length'], $mapTableData['order']);
            $this->ajaxReturn($data);
        } else {
            $repertoryModel = new RepertorylistModel();
            $repoList = $repertoryModel->getMrpWarehouseWithRight($this->staffId);

            $tmp = M('auth_role') -> where(['role_id' => ['IN', self::LOGISTICS_LINE]]) -> select();
            $ids = [];
            foreach ($tmp as $key => $value) {
                $ids = array_merge($ids, explode(',', $value['staff_ids']));
            }
            $btn = in_array(session('staffId'), $ids);
            $this->assign(compact('repoList', 'btn'));
            $this->display();
        }
    }


    /**
     * 获取物料信息
     */
    public function getProductMsg(){
        if(IS_POST){
            $data = I("post.");
            if(!empty($data['condition'])){
                $map['a.product_no|a.product_name|a.product_number'] = ['LIKE', "%" . $data['condition'] . "%"];
                $materialModel = new MaterialModel();
                $field = "a.product_id,
                          a.product_no,
                          a.product_name,
                          a.product_number,
                          a.warehouse_id,
                          re.repertory_name warehouse_name,
                          ifnull((st.stock_number + st.o_audit),0) stock_total_number";
                $materialData = $materialModel
                    ->alias('a')
                    ->where($map)
                    ->field($field)
                    ->join('LEFT JOIN crm_repertorylist re ON re.rep_id = warehouse_id')
                    ->join("LEFT JOIN crm_stock st ON (st.product_id = a.product_id and a.warehouse_id = st.warehouse_number)")
                    ->limit(0,20)
                    ->order('product_name desc')
                    ->select();
                $this->returnAjaxMsg('ok',200, $materialData);
            }
            $this->returnAjaxMsg('none', 400);
        }else {
            die('非法');
        }
    }
    /**
     * 获取添加入库记录时页面的下拉选择数组，包括部门、审核人等
     * @todo 后续要调整选择的内容
     */
    public function getSelectInfo()
    {
        if (IS_POST) {
            $warehouseModel = new RepertorylistModel();
            $repertoryData = $warehouseModel->getRepInfoWithProductionLimit();
            $str = "";
            foreach ($repertoryData as $val) {
                $tmpStr = empty($val['auditor_ids']) ? "" : "," . $val['auditor_ids'];
                $str = empty($str) ? $val['auditor_ids'] : $str . $tmpStr;
            }
            $map['id'] = ['in', $str];
            $auditorIds = M('staff')->where($map)->field('id auditor_id,name auditor_name')->select();
            $filter['loginstatus'] = ['neq', 1];
            $ids = $this->getRoleStaffIds(self::ALL_OUT_RECORD_AUTH);
            $map['id'] = ['in', $ids];
            $staffIds = M('staff')->where($map)->field('id,name')->select();
            $data['warehouse'] = $repertoryData;
            $data['auditor'] = $auditorIds;
            $data['staff'] = $staffIds;
            if (I('post.type') == 'getDept') {
                $data['dept'] = M('dept')->field('id,name')->select();
                $data['stockInCate'] = StockInOtherApplyModel::$stockInTypeMap;
            }
            $this->returnAjaxMsg('ok', 200, $data);
        } else {
            die('非法');
        }
    }
    /**-----------------------入库接口---------------------------------------------------------------*/
    /**
     * 入库接口
    */

    /**
     * 待下推订单列表
     * @todo :现在显示所有，需要调整：完成版本需要只显示待下推，下推中的。下推完毕的不能显示
     */
    public function purchaseOrderIndex(){
        $orderModel = new PurchaseOrderModel();
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            list($count, $filterCount, $data) = $orderModel->getDataWithSqlCondition($this->sqlCondition,[],'a');

            $this->output = $this->getDataTableOut($draw, $count, $filterCount, $data);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign(array(
                'auditMsg' => PurchaseOrderModel::$auditStatus,
            ));
            $this->display();
        }
    }

    /**
     * 获取一个订单下的物料信息
     */
    public function getOrderProduct(){
        if(IS_POST){
            $orderId = I("post.id");
            $contractProductModel = new PurchaseOrderProductModel();
            $data  = $contractProductModel->getAllMaterialMsg($orderId);
            $this->returnAjaxMsg("获取成功",200, $data);
        }else {
            die("非法");
        }
    }

    /**
     * 对接售后入库，售后专员提交入库记录（入库申请）物流员下推入库单据
     */
    public function otherStockInApplyIndex()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $stockInAppModel = new StockInOtherApplyModel();
            $map['a.stock_status'] = ['eq', StockInOtherApplyModel::TYPE_UNTREATED];
            if (trim($this->sqlCondition['search'])) {
                $map['create_name'] = ['like', "%" . trim($this->sqlCondition['search']) . '%'];
            }

            list($count, $filterCount, $data) = $stockInAppModel->getIndexData($map,$this->sqlCondition);
            $this->output = $this->getDataTableOut($this->posts['draw'], $count, $filterCount, $data);
            $this->ajaxReturn($this->output);

        } else {
            $this->assign([
                'stockInTypeMap' => StockInOtherApplyModel::$stockInTypeMap, // 申请类型
                "stockInMap" => StockInOtherApplyModel::$stockInMap,  // 出库状态
            ]);
            $this->display('createStockShipmentList');
        }

    }

    public function getOtherStockInApplyMaterial()
    {
        if (IS_POST) {
            $applyId = I('post.id');
            $stockInAppMaterialModel = new StockInOtherApplyMaterialModel();
            $materialData = $stockInAppMaterialModel->findApplyMaterialWithPid($applyId);
            $this->returnAjaxMsg('ok', 200, $materialData);
        } else {
            die('非法');
        }
    }

    /**
     * 入库单质控审核列表
     */
    public function purchaseAuditIndex()
    {
        $stockInModel = new StockInPurchaseModel();
        if (IS_POST) {
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            list($contractData,$count,$filterCount) = $stockInModel->getAuditList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);

            $this->output = $this->getDataTableOut($draw, $count, $filterCount, $contractData);
            $this->ajaxReturn($this->output);
        } else {
            $this->display();
        }
    }
    /**
     * 获取待审核采购入库单下方所有物料信息
     */
    public function getStockInMaterial(){
        if(IS_POST){
            $stockId = I("post.id");
            $materialModel = new StockMaterialModel();
            $materialData = $materialModel->getAllMaterialMsg($stockId);
            $this->returnAjaxMsg("获取成功",200, $materialData);
        }else {
            die("非法");
        }
    }

    /**
     * 待审核的入库单据列表。根据下拉参数不同获取不同的类型入库待审记录
     */
    public function showStockInAuditIndex()
    {
        if (IS_POST) {
//            $recordModel = new StockInRecordModel();
            $this->posts = I('post.');
            $this->posts['type'] = empty($this->posts['type']) ? 1 : (int)$this->posts['type'];
            switch ($this->posts['type']) {
                case StockInRecordModel::SOURCE_PRODUCTION_TYPE:
                    $stockModel  = new StockInProductionModel();
                    break;
                case StockInRecordModel::SOURCE_PURCHASE_TYPE:
                    $stockModel = new StockInPurchaseModel();
                    break;
                case StockInRecordModel::SOURCE_OTHER_TYPE:
                    $stockModel = new StockInOtherModel();
                    break;
                default:
                    $stockModel = new StockInProductionModel();
                    break;
            }
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            list($data, $count, $filterCount) = $stockModel->getAuditData($this->sqlCondition);

            $this->output= $this->getDataTableOut($this->posts['draw'], $count, $filterCount, $data);
            $this->ajaxReturn($this->output);
        } else {
            $this->display();
        }
    }
    /**
     * 入库单据列表
     */
    public function showStockInRecord()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $stockInModel = new StockInModel();
            $this->sqlCondition = $this->getSqlCondition($this->posts);

//            $this->whereCondition['status'] = ['EQ', 1];
//            if (empty($this->posts['rep_id'])) {
//                $repertoryModel = new RepertorylistModel();
//                $repData = $repertoryModel->getUserRightsRepertoryList($this->staffId);
//
//                $this->whereCondition['repertory_id'] = ['IN', getPrjIds($repData,'rep_id')];
//            } else {
//                $this->whereCondition['repertory_id'] = ['EQ', $this->posts['rep_id']];
//            }

//            if (!empty($this->sqlCondition['search'])) {
//                $this->whereCondition['product_name|proposer_name|auditor_name|warehouse_name|cate_name|audit_order_number|action_order_number'] = ['LIKE','%' . $this->sqlCondition['search'] . '%'];
//            }

            list($data, $count, $filterCount) = $stockInModel->getUnionDataTableData($this->whereCondition,$this->sqlCondition);
            $this->output = $this->getDataTableOut($this->posts['draw'],$count, $filterCount, $data);
            $this->ajaxReturn($this->output);
        } else {
            $this->display();
        }
    }

    /**
     * 查看某条入库单对应的单据详情
     */
    public function getStockInDetail()
    {
        if (IS_POST) {
            $stockInId = I('post.id');
            if (empty($stockInId) || !is_numeric($stockInId)) {
                $this->returnAjaxMsg('非法请求', 400);
            }
            $recordModel = new StockInRecordModel();
            $data = $recordModel->getStockInRecordWithSourceId($stockInId);
            if ($data) {
                $this->returnAjaxMsg('OK', 200, $data);
            } else {
                $this->returnAjaxMsg('访问发生错误', 400,$data);
            }
        } else {
            die('非法');
        }
    }

    /*-----------------------------------------入库制单--------------------------*/
    /**
     * 其他入库制单页面接口
     */
    public function otherTypeIndex()
    {
        if (IS_POST) {
            die('FEIFA');
        } else{
            $this->display();
        }
    }

    /**
     * 其他入库申请下推生成入库单
     */
    public function putStockInApply()
    {
        if (IS_POST) {
            $stockInModel       = new StockInOtherModel();
            $stockMaterialModel = new StockMaterialModel();

            $this->posts = I('post.');


            $this->posts['stock']['cateArr'] = "5_其他入库";
            $this->posts['stock']['typeArr'] = StockInOtherApplyModel::STOCK_SOURCE_AFTER_SALE . "_" . StockInOtherApplyModel::$stockInTypeMap[StockInOtherApplyModel::STOCK_SOURCE_AFTER_SALE];
            $this->posts['stock']['source_id'] = $this->posts['stock']['app_primary_id'];
            $this->posts['stock']['stock_in_id'] = $this->posts['stock']['storage_in_id'];


            $baseAddData = $stockInModel->getAddData($this->posts['stock']);

            $validateBaseData = $stockInModel->validateBase($baseAddData);

            if ($validateBaseData === false) {
                $this->returnAjaxMsg($stockInModel->getError(),401);
            }
            foreach ($this->posts['material'] as &$post) {
                $post['num']  = $post['shipment_num'];
                $post['tips'] = $post['shipment_tips'];
                $post['default_rep_id'] = $post['shipment_rep_pid'];
                $post['fail_rep_id']    = $post['shipment_rep_pid'];
            }
            $materialAddData = $stockMaterialModel->getAddStockMaterial($this->posts['material'], $validateBaseData['id']);

            if ($materialAddData === false) {
                $this->returnAjaxMsg($stockMaterialModel->getError(), 401);
            }
            $validateMaterialData = $stockMaterialModel->validateMaterialWithRule($materialAddData);
            if ($validateMaterialData === false) {
                $this->returnAjaxMsg($stockMaterialModel->getError(),400);
            }

            $rst = $stockInModel->addStockInOtherTrans($validateBaseData, $validateMaterialData);
            if ($rst === false) {
                $this->returnAjaxMsg($stockInModel->getError(),4042);
            }
            $this->returnAjaxMsg('下推完成', 200);
        } else {
            $id = I('get.id');
            $stockInAppModel = new StockInOtherApplyModel();
            $stockInAppMaterialModel = new StockInOtherApplyMaterialModel();
            $applyData = $stockInAppModel->findApplyInfo($id);
            if (StockInOtherApplyModel::TYPE_IN_OF_REP == $applyData['storage_status']) {
                die("<h3 style='margin-top: 20%;text-align: center;'>该单据已下推，禁止重复下推</h3>");
            }

            $maxId = new MaxIdModel();
            $idData = $maxId->getMaxId('stock_in');

            $applyData['keep_id']    = $applyData['create_id'];
            $applyData['keep_name']  = $applyData['single_name'];
            $applyData['check_id']   = $applyData['auditor'];
            $applyData['check_name'] = $applyData['auditor_name'];
            $materialData = $stockInAppMaterialModel->findApplyMaterialWithPid($id);
            $stockInTypeMap = StockInOtherApplyModel::$stockInTypeMap;
            $this->assign(compact('applyData', 'materialData','stockInTypeMap','idData'));
            $this->display('stockSkipmentDown');
        }

    }
    /**
     * 添加其他入库单接口
     */
    public function addStockInWithOther()
    {
        if (IS_POST) {
            $stockInModel       = new StockInOtherModel();
            $stockMaterialModel = new StockMaterialModel();

            $this->posts = I('post.');

            $idData = $this->getOrderNumber('stock_in');

            $this->posts['base']['cateArr'] = "5_其他入库";
            $this->posts['base']['id'] = $idData['orderId'];
            $this->posts['base']['stock_in_id'] = 'QTRK-' . $idData['orderId'];

            $baseAddData = $stockInModel->getAddData($this->posts['base']);

            $validateBaseData = $stockInModel->validateBase($baseAddData);

            if ($validateBaseData === false) {
                $this->returnAjaxMsg($stockInModel->getError(),401);
            }
            $materialAddData = $stockMaterialModel->getAddStockMaterial($this->posts['material'], $validateBaseData['id']);

            if ($materialAddData === false) {
                $this->returnAjaxMsg($stockMaterialModel->getError(), 401);
            }
            $validateMaterialData = $stockMaterialModel->validateMaterialWithRule($materialAddData);
            if ($validateMaterialData === false) {
                $this->returnAjaxMsg($stockMaterialModel->getError(),400);
            }
            $rst = $stockInModel->addStockInOtherTrans($validateBaseData, $validateMaterialData);
            if ($rst === false) {
                $this->returnAjaxMsg($stockInModel->getError(),4042);
            }
            $this->returnAjaxMsg('ok', 200);
        }
    }

    /**
     * 添加生产入库数据接口
     * version2.0 一步审核添加直接默认质控审核完毕 默认提交in_stock_record数据
     * 中间过程说明：
     * getAddData() 获取单据添加的数据
     * validateBase() 校验数据有效性
     * getAddStockMaterial() 获取入库物料数据
     * validateMaterialWithRule() 校验物料数据
     * validateUpdateNum 校验物料数量
     * addProductionStockInTrans() 添加数据事务
     *
     */
    public function addStockInWithProduction()
    {

        if (IS_POST) {
            $taskModel = new ProductionTaskModel();
            $stockInModel = new StockInProductionModel();
            $stockMaterialModel = new StockMaterialModel();

            $this->posts = I('post.');

            $idData = $this->getOrderNumber('stock_in');

            $this->posts['base']['cateArr'] = StockInRecordModel::SOURCE_PRODUCTION_TYPE . "_" . "生产入库";
            $this->posts['base']['id'] = $idData['orderId'];
            $this->posts['base']['stock_in_id'] = 'SCRK-' . $idData['orderId'];

            $baseAddData = $stockInModel->getAddData($this->posts['base']);

            $validateBaseData = $stockInModel->validateBase($baseAddData);
            if ($validateBaseData === false) {
                $this->returnAjaxMsg($stockInModel->getError(),401);
            }
            $materialData[] = $this->posts['material'];
            $materialAddData = $stockMaterialModel->getAddStockMaterial($materialData, $validateBaseData['id']);
            $validateMaterialData = $stockMaterialModel->validateMaterialWithRule($materialAddData);

            if ($validateMaterialData === false) {
                $this->returnAjaxMsg($stockMaterialModel->getError(),400);
            }

            $numberCheckRst = $taskModel->validateUpdateNum($validateBaseData['source_id'], $validateMaterialData[0]['num']);

            if ($numberCheckRst === false) {
                $this->returnAjaxMsg($taskModel->getError(),4043);
            }

            $rst = $stockInModel->addProductionStockInTrans($validateBaseData, $validateMaterialData);

            if ($rst === false) {
                $this->returnAjaxMsg($stockInModel->getError(),4042);
            }
            $orderModel = new ProductionOrderModel();
            $resetRst = $orderModel->resetOrderStatusWithTaskId($validateBaseData['source_id']);
            if ($resetRst === false) {
                $this->returnAjaxMsg($orderModel->getError(),4042);
            }
            $this->returnAjaxMsg('ok', 200);
        }
    }

    /**
     * 外购入库单添加接口
     *
     */
    public function addStockInWithPurchase(){
        $orderModel = new PurchaseOrderModel();
        $productModel = new PurchaseOrderProductModel();

        // 入库分类map
        $cateModel = new StockIoCateModel();
        $cateMap = $cateModel->index();

        if(IS_POST){
            $stockInModel       = new StockInPurchaseModel();
            $stockMaterialModel = new StockMaterialModel();

            $this->posts = I('post.');

            $orderData = $orderModel->getOrderWithId($this->posts['base']['source_id']);
            if ($orderData['audit_status'] != PurchaseOrderModel::TYPE_QUALIFIED) {
                $this->returnAjaxMsg("单据未审核，不能下推", 400,$this->posts);
            }

            $idData = $this->getOrderNumber('stock_in');

            $this->posts['base']['cateArr'] = StockInOtherModel::TYPE_PURCHASE .  "_" . "外购入库";
            $this->posts['base']['id'] = $idData['orderId'];
            $this->posts['base']['stock_in_id'] = 'WGRK-' . $idData['orderId'];
            // 判断当前入库单物料是否超过订单本身的数量
            $validateRst = $productModel->validateMaterialNum($this->posts['material'], $this->posts['base']['source_id']);
            if($validateRst === false){
                $this->returnAjaxMsg($productModel->getError(), 400);
            }


            $baseAddData = $stockInModel->getAddData($this->posts['base']);
            $validateBaseData = $stockInModel->validateBase($baseAddData);

            if ($validateBaseData === false) {
                $this->returnAjaxMsg($stockInModel->getError(),401);
            }

            $materialAddData = $stockMaterialModel->getAddStockMaterial($this->posts['material'], $validateBaseData['id']);
            if ($materialAddData === false) {
                $this->returnAjaxMsg($stockMaterialModel->getError(), 401);
            }

            $validateMaterialData = $stockMaterialModel->validateMaterialWithRule($materialAddData);
//            $this->returnAjaxMsg('ok', 200,$validateMaterialData);

            if ($validateMaterialData === false) {
                $this->returnAjaxMsg($stockMaterialModel->getError(),400);
            }

            $rst = $stockInModel->addStockInPurchaseTrans($validateBaseData, $validateMaterialData);
            if ($rst === false) {
                $this->returnAjaxMsg($stockInModel->getError(),4042);
            }
            $this->returnAjaxMsg('ok', 200);

        }else {
            // 入库单首先从订单表中取得数据
            $orderId = I('get.orderId');
            $data = [];
            $data['order'] = $orderModel->getOrderWithId($orderId);
            if($data['order']['audit_status'] != PurchaseOrderModel::TYPE_QUALIFIED){
                die(returnDieHtml("审核未通过的订单不可以下推入库单"));
            }
            $data['product'] = $productModel->getPreMaterial($orderId);
//            // 判断当前操作人是否是创建改订单的人
//            if($data['order']['create_id'] != session('staffId')){
//                die('当前用户不可以对此订单下推入库单');
//            }

            //仓库名称map  从crm_repertorylist 表中查出
//            $repertoryListModel = new RepertorylistModel();
//            $repMap = $repertoryListModel->getRepInfoList();
            $this->assign([
                'order' => $data['order'],
                'product' => $data['product'],
                'cateMap' => $cateMap
            ]);
            $this->display();
        }
    }


    /*-----------------------------------------入库审核--------------------------*/
    /**
     * 外购入库单审核下推，生成具体的入库单
     */
    public function addRecordWithPurchaseStockIn(){
        if(IS_POST){

            $recordModel   = new StockInRecordModel();
            $stockModel    = new StockInPurchaseModel();
            $materialModel = new StockMaterialModel();
            $this->posts = I('post.');
            if ($this->posts['flag'] == 'get') {
                // 获取入库单id
                $stockId = $this->posts['id'];
                $data['base']     = $stockModel->getBaseInfo($stockId);
                $data['material'] = $materialModel->getMaterialWithStockInNum($stockId);
                $this->returnAjaxMsg('ok', 200, $data);
            }
            if ($this->posts['flag'] == 'post') {

                if(empty($this->posts['stockId'])  || empty($this->posts['material'])){
                    $this->returnAjaxMsg("参数不全", 400);
                }

                $status = $stockModel->find($this->posts['stockId'])['audit_status'];
                if ($status == StockInPurchaseModel::TYPE_QUALIFIED || $status == StockInPurchaseModel::TYPE_STOCK_QUALIFIED) {
                    $this->returnAjaxMsg('质控已质检完毕，不能再下推入库单据', 400);
                }
                $materialData = $materialModel->getInsertStockDataWithSourceId($this->posts['stockId'],'crm_stock_in_purchase');

                $data = $recordModel->getPurchaseStockInData($this->posts['stockId'], $materialData, $this->posts['material']);

                // 入库单下推
                if ($data === false) {
                    $this->returnAjaxMsg($recordModel->getError(), 400, $data);
                }
                $addRst = $recordModel->addStockInRecordWithPurchaseData($this->posts['stockId'], $data);
                if ($addRst === false) {
                    $this->returnAjaxMsg($recordModel->getError(), 400, $data);
                }
                $this->returnAjaxMsg('ok', 200);
            }
        } else {
            $id = I('get.id');
            $this->assign(compact('id'));
            $this->display();
        }
    }

    /**
     * 审核单据功能
     * */
    public function checkStockInRecord()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $recordModel = new StockInRecordModel();
            $rst = $recordModel->checkStockInRecord($this->posts['status'], $this->posts['params']);
            if ($rst === false) {
                $this->returnAjaxMsg($recordModel->getError(), 400);
            }
            $this->returnAjaxMsg('ok', 200);
        } else {
            die('非法');
        }
    }

    /*-----------------------------------------入库维护--------------------------*/
    /**
     * 删除入库申请（售后）
    */
    public function delStockInApply()
    {
        if (IS_POST) {
            $id = I('post.applyId');
            $stockInAppModel = new StockInOtherApplyModel();
            $applyData = $stockInAppModel->find($id);
            if (!$applyData)
                $this->returnAjaxMsg("非法操作",500);
            if (StockInOtherApplyModel::IS_DEL == $applyData['is_del'])
                $this->returnAjaxMsg('已经删除,未查到数据', 404);
            if (StockInOtherApplyModel::TYPE_IN_OF_REP == $applyData['stock_status'])
                $this->returnAjaxMsg('不能删除，该单据已下推', 400);
            $rst = $stockInAppModel->delTrans($id);
            if (false === $rst) {
                $this->returnAjaxMsg($stockInAppModel->getError(),404);
            }
            $this->returnAjaxMsg("ok",200);
        } else {
            die('非法');
        }
    }

    /**
     * 编辑入库申请（售后）
    */
    public function editStockInApply()
    {
        $stockInAppMaterialModel = new StockInOtherApplyMaterialModel();
        if (IS_POST) {
            $materialData = I('post.material');
            $rst = $stockInAppMaterialModel->updateDataTrans($materialData);
            if (false === $rst) {
                $this->returnAjaxMsg($stockInAppMaterialModel->getError(),404);
            }
            $this->returnAjaxMsg('ok',200);
        } else {
            $id = I('get.id');
            $stockInAppModel = new StockInOtherApplyModel();
            $applyData = $stockInAppModel->findApplyInfo($id);
            $materialData = $stockInAppMaterialModel->findApplyMaterialWithPid($id);
            $stockInTypeMap = StockInOtherApplyModel::$stockInTypeMap;
            $this->assign(compact('applyData', 'materialData', 'stockInTypeMap'));
            $this->display('editStockInApply');
        }
    }

    /**
     * 入库删除记录
    */
    public function delStockInRecord()
    {
        if (IS_POST) {
            $params = I('post.');
            if(empty($params['recordType']) || empty($params['recordId'])){
                $this->returnAjaxMsg("参数不全", 400);
            }
            switch ((int)$params['recordType']) {
                case StockInRecordModel::SOURCE_PRODUCTION_TYPE:
                    $stockInModel = new StockInProductionModel();
                    break;
                case StockInRecordModel::SOURCE_PURCHASE_TYPE:
                    $stockInModel = new StockInPurchaseModel();
                    break;
                case StockInRecordModel::SOURCE_OTHER_TYPE:
                    $stockInModel = new StockInOtherModel;
                    break;
                default:
                    $this->returnAjaxMsg('参数有误',404);
                    break;
            }
            if (isset($stockInModel)) {
                $rst = $stockInModel->delStockInRecord($params['recordId']);
                if (false === $rst)
                    $this->returnAjaxMsg($stockInModel->getError(),400);
                $this->returnAjaxMsg('删除完毕',200,$rst);
            }
            $this->returnAjaxMsg('参数有误',404);

        } else {
            die('非法操作');
        }
    }

    /*-----------------------------------------------------------------------------------------------*/

    /*----------------------------------------调拨管理------------------------------------------------*/

    /*-----------------------------------------查看--------------------------*/
    /**
     * 可调拨生产单页面接口
    */
    public function productionOrderIndex()
    {
        if (IS_POST) {
            die('非法');
        } else {
            $pushBtn = true;
            $transferBtn = false;
            $this->assign(compact('pushBtn', 'transferBtn'));
            $this->display();
        }
    }

    /**
     * 生产调拨页面
    */
    public function productionTransferIndex()
    {
        if (IS_POST) {
            die('非法');
        } else {
            $pushBtn = false;
            $transferBtn = true;
            $this->assign(compact('pushBtn', 'transferBtn'));
            $this->display('productionOrderIndex');
        }
    }

    /**
     * 调拨记录
     */
    public function showTransferList()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $transferModel = new StockTransferModel();
            $LimitConfig = [
                'staffLimit' => $this->staffId
            ];
            list($count, $filterCount, $data) = $transferModel->getAuditListWithLv($LimitConfig, $this->sqlCondition, 3);
            $this->output = $this->getDataTableOut($this->posts['draw'], $count, $filterCount, $data);
            $this->ajaxReturn($this->output);
        } else {

            $this->display();
        }
    }

    /**
     * 待审核列表页
     * @todo : 筛选功能
     */
    public function showAuditTransferList()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $transferModel = new StockTransferModel();
            $LimitConfig = [
                'staffLimit' => $this->staffId
            ];
            list($count, $filterCount, $data) = $transferModel->getAuditListWithLv($LimitConfig, $this->sqlCondition, 1);
            $this->output = $this->getDataTableOut($this->posts['draw'], $count, $filterCount, $data);
            $this->ajaxReturn($this->output);
        } else {
            $this->display();
        }
    }

    /**
     * 根据调拨单id获取数据
     */
    public function getTransferDataWithId()
    {
        if (IS_POST) {
            $id = I('post.orderId');
            if (empty($id) || !is_numeric($id)) {
                $this->returnAjaxMsg('非法参数', 400);
            }
            $transferMaterialModel = new StockTransferMaterialModel();
            $data = $transferMaterialModel->getMaterialWithPid($id);

            if ($data == false) {
                $this->returnAjaxMsg($transferMaterialModel->getError(), 400);
            }
            $this->returnAjaxMsg('ok', 200, $data);


        } else {
            die('illegal');
        }
    }

    /**
     * 物流员待审核记录
     */
    public function showAuditTransferListSecond()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $transferModel = new StockTransferModel();
            $LimitConfig = [
                'staffLimit' => $this->staffId
            ];
            list($count, $filterCount, $data) = $transferModel->getAuditListWithLv($LimitConfig, $this->sqlCondition, 2);
            $this->output = $this->getDataTableOut($this->posts['draw'], $count, $filterCount, $data);
            $this->ajaxReturn($this->output);
        } else {
            $this->display();
        }
    }

    /*----------------------------------------制单----------------------------*/

    /**
     * 获取物料在某个库房的库存
     * @param array $params : 数组，产品内部id + 库房id
     * @return array 返回库存数据
     */
    public function getProductStockNumber()
    {
        if (IS_POST) {
            $params = I('post.');
            if (empty($params['warehouse_id']) || empty($params['product_id'])) {
                $this->returnAjaxMsg('false', 400);
            }
            $stockModel = new StockModel();
            $number = $stockModel->getStockNumberWithRepAndPid($params['warehouse_id'], $params['product_id']);
            $this->returnAjaxMsg('ok', 200, $number);
        }
    }

    /**
     * 调拨制单
     * @todo 没有领料单下推，后续需补充
    */
    public function addStockTransfer()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $transferModel = new StockTransferModel();
            $transferMaterialModel = new StockTransferMaterialModel();
            $idsArray = [];
            for ($i = 0; $i < $this->posts['length']; $i++) {
                $idsArray[] = $this->getOrderNumber('stock_transfer');
            }
            $transferAddData = $transferModel->getAddData($this->posts['base'], $idsArray);

            $transferMaterialAddData = $transferMaterialModel->getAddData($this->posts['material'], $idsArray);
            $validateMaterial = $transferMaterialModel->validateAddData($transferMaterialAddData);
            if ($validateMaterial === false) {
                $this->returnAjaxMsg($transferMaterialModel->getError(), 404);
            }
            $rst = $transferModel->addStockTransferTrans($transferAddData, $validateMaterial);
            if ($rst === false) {
                $this->returnAjaxMsg($transferModel->getError(), 400);
            }
            $this->returnAjaxMsg('ok', 200);

        } else {
            $this->display();
        }
    }

    public function addStockTransferWithOrder()
    {
        $productionOrderId = I('get.id');
        // orderId
        $returnDataSet = ['base', 'bomData', 'stockOutData'];
        $orderModel = new ProductionOrderModel();
        $data = $orderModel->getDataWithOrderId($productionOrderId, $returnDataSet);
        if (0 == $data[$returnDataSet[0]]['remaining_amount'] || ProductionOrderModel::PROCESS_DONE == $data[$returnDataSet[0]]['production_process']) {
            die('<h3 style="margin-top: 10%; text-align: center;">您好，该生产计划已完结，不能再进行物料调拨</h3>');
        }
        foreach($data[$returnDataSet[2]] as & $val) {
            $val['repInArr']  = $val['rep_id_in']  . "_" . M('repertorylist')->find($val['rep_id_in'])['repertory_name'];
            $val['repOutArr'] = $val['rep_id_out'] . "_" . M('repertorylist')->find($val['rep_id_out'])['repertory_name'];
        }
        $this->assign(compact('data'));
        $this->display();
    }

    /*---------------------------------------审核-----------------------------*/
    /**
     * FLAG : 1 1级审核， 2 2级审核（库房物流员）
     */
    public function auditStockTransfer()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $stockTransferModel = new StockTransferModel();
            if (empty($this->posts['form']['transferId'])) {
                $this->returnAjaxMsg("非法请求", 404);
            }
            $base = $stockTransferModel->find((int)$this->posts['form']['transferId']);
            $authFlag = $stockTransferModel->checkAuditStatus($base, $this->posts['flag']);
            if ($authFlag === false) {
                $this->returnAjaxMsg($stockTransferModel->getError(), 404);
            }
            $checkRst = $stockTransferModel->auditTransferOrder($this->posts['form'], $this->posts['flag']);
            if ($checkRst === false) {
                $this->returnAjaxMsg($stockTransferModel->getError(), 404);
            }
            $this->returnAjaxMsg('ok', 200);
        } else {
            die('illegal');
        }
    }

    /**
     * 删除调拨单接口
    */
    public function delStockTransfer()
    {
        if (IS_POST) {
            $delId = I('post.delId');
            $transferModel = new StockTransferModel();
            $base = $transferModel->find($delId);
            if (count($base) == 0)
                $this->returnAjaxMsg('没有找到该单据',404);
            if (StockTransferModel::TYPE_STOCK_QUALIFIED == $base['audit_status'])
                $this->returnAjaxMsg('单据已经完成入库，禁止删除',500);
            if (StockTransferModel::IS_DEL == $base['audit_status'])
                $this->returnAjaxMsg('单据已删除,不能重复删除', 404);
            $rst = $transferModel->deleteTransfer($delId);
            if (false === $rst)
                $this->returnAjaxMsg($transferModel->getError(),400);
            $this->returnAjaxMsg('删除完成，请根据需求重新制单', 200);
        } else {
            die("非法操作");
        }
    }

    /**
     * 删除调拨单单个物料的接口
    */
    public function delStockTransferMaterial()
    {
        if (IS_POST) {
            $delId = I('post.id');
            $transferModel = new StockTransferMaterialModel();
            $rst = $transferModel->deleteMaterialWithId($delId);
            if (false === $rst)
                $this->returnAjaxMsg($transferModel->getError(),400);
            $this->returnAjaxMsg('删除完成', 200);
        } else {
            die("非法操作");
        }

    }
    /**
     * 编辑调拨单页面
     * 目前不支持库房修改。
     */
    public function editStockTransfer()
    {
        $transferModel = new StockTransferModel();
        $transferMaterialModel = new StockTransferMaterialModel();
        if (IS_POST) {
            $params = I('post.');
            $updData = $transferModel->getEditData($params['base']);
            $updMaterialData = $transferMaterialModel->getEditData($params['material']);
            $updStockData = $transferMaterialModel->getUpdStockDataWithEditMaterial($params['material']);
            $updRst = $transferModel->editTransfer($updData, $updMaterialData, $updStockData);
            if (false === $updRst)
                $this->returnAjaxMsg($transferModel->getError(), 400);
            $this->returnAjaxMsg('修改完成',200, $params);
        } else {
            $id = I('get.id');
            $base = $transferModel->find($id);
            if (count($base) == 0)
                die(returnDieHtml('没有找到该单据'));
            if (StockTransferModel::TYPE_STOCK_QUALIFIED == $base['audit_status'])
                die(returnDieHtml('单据已经完成入库，禁止删除'));
            if (StockTransferModel::IS_DEL == $base['audit_status'])
                die(returnDieHtml('已删除单据，无法修改'));

            $material = $transferMaterialModel->getMaterialWithPid($id);
            foreach ($material as &$value) {
                $value['validate'] = $value['num'] + $value['stock_total_number'];
                $value['base_num'] = $value['num'];
            }
            $productionOrderId = $base['source_id'];
            if ($productionOrderId) {
                // orderId
                $returnDataSet = ['base', 'bomData', 'stockOutData'];
                $orderModel = new ProductionOrderModel();
                $data = $orderModel->getDataWithOrderId($productionOrderId, $returnDataSet);
                if (0 == $data[$returnDataSet[0]]['remaining_amount'] || ProductionOrderModel::PROCESS_DONE == $data[$returnDataSet[0]]['production_process']) {
                    die('<h3 style="margin-top: 10%; text-align: center;">您好，该生产计划已完结，不能再进行物料调拨</h3>');
                }
                foreach($data[$returnDataSet[2]] as & $val) {
                    $val['repInArr']  = $val['rep_id_in']  . "_" . M('repertorylist')->find($val['rep_id_in'])['repertory_name'];
                    $val['repOutArr'] = $val['rep_id_out'] . "_" . M('repertorylist')->find($val['rep_id_out'])['repertory_name'];
                }
            }
            $this->assign(compact('base', 'material','data'));
            $template = count($data) ? 'editStockTransferWithOrder' : 'editStockTransfer';
            $this->display($template);
        }
    }
    /*---------------------------------------单据打印-----------------------------*/
    /**
     * 调拨单，打印成PDF
     */
    public function stockTransferToPdf(){
        if(IS_POST){
            $id = I("post.id"); // 调拨单id

            if(empty($id) || empty($sourceType)){
                $this->returnAjaxMsg("参数不全", 400);
            }

            $transferModel = new StockTransferModel();
            $baseMsg = $transferModel->find();
            $materialModel = new StockTransferMaterialModel();
            $materialData = $materialModel->getBaseMaterialMsgByPid($id);
            if(empty($materialData)){
                $this->returnAjaxMsg("当前出库单没有出库物料信息", 400);
            }

            $fileUrl = $transferModel->printingToPdfEx($baseMsg, $materialData);
            if(!$fileUrl){
                $this->returnAjaxMsg("更新下载记录失败", 400);
            }

            $this->returnAjaxMsg("出库单下载成功",200, [
                'fileUrl' => $fileUrl
            ]);
        }else {
            die(returnDieHtml('非法'));
        }
    }
    /*————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————*/
    /**
     * 待出库销货单列表
    */
    public function showPendingOrderList()
    {
        if (IS_POST) {
            $orderModel = new OrderformModel();
            $this->posts = I('post.');
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            if (isset($this->posts['orderId'])) {
                $returnDataSet = ['stockOutData','productionPlanData','productData','orderRecordData'];
                $orderDetailData  = $orderModel->getOrderPendingDataById($this->posts['orderId'], "CPO" . $this->posts['orderId'],$returnDataSet);
                $this->ajaxReturn($orderDetailData);
            }
            if (!$this->checkAuthByRole(self::ALL_OUT_RECORD_AUTH)) {
                $map['picid'] = ['eq', $this->staffId];
            }
            $map['check_status'] = array('EQ', "4");
            $map['stock_status'] = array('in', '0,1,2');


            if (isset($this->posts['pendingData']) && (int)$this->posts['pendingData'] === 1) {
                $map['_string'] = 'FIND_IN_SET('.$this->staffId.',warehouse_manager_ids) OR FIND_IN_SET(' . $this->staffId . ',warehouse_logistics_ids)';
                $map['stock_status'] = array('in', '0,1');
            }
            if (isset($this->posts['pendingData']) && $this->posts['pendingData'] == 2) {
                $map['stock_status'] = array('in','0,1');
            }
            if ((int)$this->posts['orderLimit'] === 2) {
                $map['stock_status'] = ['IN', '0,1,2'];
                $map['check_status'] = ['IN', '3,4'];
                $map['order_type'] = ['eq', "6"];
            }
            $count = $orderModel->countOrderNumber($map);
            if ($this->sqlCondition['search']) {
                $c = new \SphinxClient();
                $c->setServer('localhost', 9312);
                $c->setMatchMode(SPH_MATCH_ALL);
                $c->setLimits(0,1000);
                $datai = $c->Query(trim($this->sqlCondition['search']), 'stock_order');
                $index = array_keys($datai['matches']);
                $c->close();
                $primaryIds = implode(',', $index);
                $data['pri'] = $datai;
                if ($primaryIds) {
                    $map['crm_orderform.id'] = ['IN', $primaryIds];
                }
                //$map['cpo_id|pic_name|cus_name'] = ['like', '%' . trim($this->sqlCondition['search']) . '%'];
            }
            $filterCount = $orderModel->countOrderNumber($map);
            $data  = $this->getOrderStockOutData($map, $this->sqlCondition);
            $this->output = $this->getDataTableOut((int)$this->posts['draw'],$count, $filterCount, $data);
            $this->ajaxReturn($this->output);
        } else {
            //仓库名称map  从crm_repertorylist 表中查出
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();
            $this->assign([
                "auditMap" => StockOutOrderformModel::$auditMap,
                'repMap'    => $repMap,            // 仓库名称
                "stockOutMap" => StockOutOtherApplyModel::$stockOutMap,  // 出库状态
            ]);
            $this->display();
        }
    }
    /*-——————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————*/
    /**
     * 根据用户id显示所有入库申请
     */
    public function auditList()
    {
        if (IS_POST) {
            $params = I('post.');
            $model  = new StockAuditModel();
            $_map   = [
                'id'           => 'audit.id',
                'product_name' => 'audit.product_name',
                'update_time'  => 'audit.update_time',
            ];
            $mapTableData = $this->dataTable($params, $_map);
            $mapTableData['map']['auditor']      = session('staffId');
            $mapTableData['map']['audit.type']   = $params['type'];
            $mapTableData['map']['audit_status'] = $params['audit_status'];
            $mapTableData['map']['is_del']       = ['EQ', self::NOT_DEL_STATUS];

            $data['draw']         = (int) $params['draw'];
            $data['recordsTotal'] = $model->count();
            $data['recordsFiltered'] = $model->indexCount($mapTableData['map']);
            $data['data'] = $model->index($mapTableData['map'], $params['start'], $params['length'], $mapTableData['order']);
            foreach ($data['data'] as $key => &$value) {
                $value['update_time'] = date('Y-m-d H:i:s', $value['update_time']);
            }
            $this->ajaxReturn($data);
        } else {
            $this->display();
        }
    }

    /**
     * 审核及查看入库记录
     * 展示所有的入库记录
     */
    public function showRecord()
    {
        if (IS_POST){
            $params = I('post.');
            $this->sqlCondition = $this->getSqlCondition($params);

            $this->whereCondition['type'] = ['IN', self::IN_TYPE];
            $this->whereCondition['is_del'] = ['EQ', self::NOT_DEL_STATUS];
            $this->whereCondition['audit_status'] = ['in', $params['status']];
            if (empty($params['repertory_id'])) {
                $repertoryModel = new RepertorylistModel();
                $repData = $repertoryModel->getUserRightsRepertoryList($this->staffId);

                $this->whereCondition['warehouse_number'] = ['IN', getPrjIds($repData,'rep_id')];
            } else {
                $this->whereCondition['warehouse_number'] = ['EQ', $params['repertory_id']];
            }


            $model = new StockAuditModel();
            $data['draw'] = (int) $params['draw'];
            $data['recordsTotal'] = $model->where($this->whereCondition)->count();
            $advancedCondition = getAdvancedCondition($params['vueData']);

            if ($advancedCondition['status'] == 200) {
                $this->whereCondition['_complex'] = $advancedCondition['data'];
                $this->sqlCondition['search'] = "";
            }
            if (!empty($this->sqlCondition['search'])) {
                $this->whereCondition['product_name|proposer_name|auditor_name|warehouse_name|cate_name|audit_order_number|action_order_number'] = ['LIKE','%' . $this->sqlCondition['search'] . '%'];
            }
            $data['recordsFiltered'] = $model->where($this->whereCondition)->count();
            $data['data'] = $model -> index($this->whereCondition, $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);
            foreach ($data['data'] as $key => &$value) {
                $value['update_time'] = date('Y-m-d H:i:s', $value['update_time']);
            }
            $this->ajaxReturn($data);
        }else{
            $repertoryModel = new RepertorylistModel();
            $repData = $repertoryModel->getUserRightsRepertoryList($this->staffId);
            $this->assign('repoData', $repData);
            $this->display();
        }
    }

    /**
     * 处理dataTables参数的方法
     * @param $params array dataTables传的参数
     * @param array $_map   映射
     * @return array    数组中有用在where方法的map和用在order方法的order
     */
    protected function dataTable($params, $_map = [])
    {
        $dataField = [];
        $searchAble = [];
        foreach ($params['columns'] as $k => $v) {
            if (isset($_map[$v['data']])){
                $dataField[] = $_map[$v['data']];
            }else{
                $dataField[] = $v['data'];
            }
            if ($v['searchable'] == 'true'){
                if (isset($_map[$v['data']])){
                    $searchAble[] = $_map[$v['data']];
                }else{
                    $searchAble[] = $v['data'];
                }
            }
        }
        $order = $dataField[$params['order'][0]['column']] . ' ' . $params['order'][0]['dir'];
        if ($params['search']['value'] == ''){
            $map = [];
        }else{
            $searchAble = rtrim(implode('|', $searchAble), '|');
            $word = $params['search']['value'];
            $map = [$searchAble => ['LIKE',"%".$word."%"]];
        }
        return [
            'order' => $order,
            'map' => $map,
        ];
    }

    /**
     * 展示及修改库存报警
     * @todo :库放编号去掉。安全库存值在哪设置后续确定
     */
    public function alarm()
    {
        $editAble = false;
        if (IS_POST){
            $params = I('post.');
            $model = new MaterialModel();
            if ($params['method'] != 'edit'){
                $mapTableData = $this->dataTable($params);
                $data['draw'] = (int) $params['draw'];
                $data['recordsTotal'] = $model -> count();
                $data['recordsFiltered'] = $model -> where($mapTableData['map']) -> count();
                $data['data'] = $model -> index($mapTableData['map'], $params['start'], $params['length'], $mapTableData['order']);
            } else {
                if (!$editAble){
                    $this->ajaxReturn(['msg' => '你没有权限','status' => self::FAIL_STATUS]);
                }
                $map = [
                    'product_id' => ['EQ', $params['chanpinid']]
                ];
                $update = [
                    'safety_number' => $params['anquanshuliang']
                ];
                $res = $model -> where($map) -> save($update);
                if ($res !== false){
                    $data = [
                        'status' => self::SUCCESS_STATUS,
                        'msg' => '修改成功',
                    ];
                }else{
                    $data = [
                        'status' => self::FAIL_STATUS,
                        'msg' => '修改失败',
                    ];
                }
            }
            $this->ajaxReturn($data);
        }else{
            $editAble = $editAble ? 'true' : 'false';
            $this->assign(compact('editAble'));
            $this->display();
        }
    }
    
    /**
     * 新增库房
     * @todo 新增库房对应增加库存表物料型号库存。
     */
    public function addWarehouse()
    {
        $warehouseName = I('post.warehouseName');
        $warehouseNumber = I('post.warehouseNumber');
        if ($warehouseName != ''){
            $res = M('repertorylist') -> add(['rep_id' => $warehouseNumber, 'repertory_name' => $warehouseName]);
        } else {
            $res = false;
        }
        if ($res) {
            $status = self::SUCCESS_STATUS;
            $msg = '添加成功';
        }else{
            $status = self::FAIL_STATUS;
            $msg = '添加失败';
        }
        $this->ajaxReturn([
            'status' => $status,
            'msg' => $msg,
        ]);
    }

    /**
     * 根据角色查询对应的所有职员
     * @param $role_ids mixed   role_id的集合,可以是数组或者字符串
     * @return array    符合条件的职员
     */
    protected function getAuditor($role_ids){
        $res = M('auth_role') -> where(['role_id' => ['IN', $role_ids]]) -> select();
        $role_ids = [];
        foreach ($res as $key => $value) {
            $role_ids = array_merge(explode(',',$value['staff_ids']), $role_ids);
        }
        $map = ['id' => ['IN', $role_ids], 'loginstatus' => ['NEQ','1']];
        $staffs = M('staff') -> field('id, name') -> where($map) -> select();
        return $staffs;
    }

    /**
     * 将申请的状态改为未申请并且更新内容
     */
    public function updateStockAudit()
    {
        if (IS_POST){
            $data = I("post.");
            $model = new StockAuditModel();
            $res = $model -> updateStockAudit($data['id'], $data);
            if ($res != false){
                $msg = '修改成功';
                $status = self::SUCCESS_STATUS;
            }else{
                $msg = $model -> getError();
                $status = self::FAIL_STATUS;
            }
            $this->ajaxReturn([
                'status' => $status,
                'msg' => $msg
            ]);
        }
    }

    /**
     * 展示所有的出库记录
     */
    public function showStockOutRecord()
    {
        $repertoryModel = new RepertorylistModel();
        if (IS_POST){
            $params = I('post.');
            $this->sqlCondition = $this->getSqlCondition($params);
//            $stockType = empty($params['type']) ? self::ALL_TYPE : $params['type'];

            $this->whereCondition['is_del'] = ['EQ', self::NOT_DEL_STATUS];
            $this->whereCondition['type']   = ['EQ', self::OUT_TYPE];
            if (!empty($params['status'])) {
                $this->whereCondition['audit_status'] = ['EQ', $params['status']];
            }
            //$this->whereCondition['audit_status'] = ['EQ', '2'];
            if (empty($params['repertory_id'])) {
                $repData = $repertoryModel->getUserRightsRepertoryList($this->staffId);
                $this->whereCondition['warehouse_number'] = ['IN', getPrjIds($repData,'rep_id')];
            } else {
                $this->whereCondition['warehouse_number'] = ['EQ', $params['repertory_id']];
            }

            if (!$this->checkAuthByRole(self::ALL_OUT_RECORD_AUTH)) {
                $mapSub['tips'] = ['LIKE', "%" . session('nickname') . "%"];
                $orderModel = new OrderformModel();
                $orderIds = $orderModel->getOwnOrderData($this->staffId, strtotime('-6 months'));
                $mapSub['action_order_number'] = ['IN', $orderIds];
                $mapSub['_logic'] = 'OR';
                $this->whereCondition['update_time'] = ['GT', strtotime('-1 months')];
                $this->whereCondition['_complex'] = $mapSub;
            }

            $model = new StockAuditOutModel();
            $data['draw'] = (int) $params['draw'];
            $data['recordsTotal'] = $model -> where($this->whereCondition)->count();
            if ($this->sqlCondition['search']) {
                $c = new \SphinxClient();
                $c->setServer('localhost', 9312);
                $c->setMatchMode(SPH_MATCH_ALL);
                $c->setLimits(0,1000);
                $datai = $c->Query(trim($this->sqlCondition['search']), 'stock_out_record');
                $index = array_keys($datai['matches']);
                $c->close();
                $primaryIds = implode(',', $index);
                $data['pri'] = $datai;
                if ($primaryIds) {
                    $this->whereCondition['audit.id'] = ['IN', $primaryIds];
                }
                // $this->whereCondition['tips|product_name|proposer_name|auditor_name|warehouse_name|cate_name|audit_order_number|action_order_number'] = ['LIKE','%' . trim($this->sqlCondition['search']) . '%'];
            }
            $data['recordsFiltered'] = $model->alias('audit')->where($this->whereCondition)->count();
            $this->field = 'audit.*';
            $data['data'] = $model -> index($this->field,$this->whereCondition, $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);
            foreach ($data['data'] as $key => &$value) {
                $value['update_time'] = date('Y-m-d H:i:s', $value['update_time']);
                $value['DT_RowId']    = $value['action_order_number'];
            }
            $this->ajaxReturn($data);
        } else {

            $repData = $repertoryModel->getUserRightsRepertoryList($this->staffId);
            $this->assign('repoData', $repData);
            $this->display();
        }
    }

    protected function getOrderStockOutData($map, $condition)
    {
        $orderModel = new OrderformModel();
        $this->field = "
            crm_orderform.id,
            cpo_id,
            d.order_type_name type_name,
            from_unixtime(settlement_time) finance_audit_time,
            pic_name staname,
            pic_phone staff_phone,
            if (LENGTH(cus_name) < 7, cus_name, 
            REPLACE(cus_name,SUBSTRING(cus_name,3,4),\"****\")) cusname,
            j.check_type_name check_status_name,
            stock_status stock_out_status,
            f.logistics_type_name log_type,
            GROUP_CONCAT(distinct b.repertory_name) ware_house,
            is_batch_delivery,
            logistices_tip";
        return $data = $orderModel->getOrderformData($map, $this->field, $condition['start'], $condition['length'],$condition['order'],'id');

    }




    /**
     * 显示库房基本信息
    */
    public function showWarehouse()
    {
        $repertoryModel = new RepertorylistModel();
        if (IS_POST) {
            $this->posts = I('post.');
            $this->field = "
                repertory.*, 
                group_concat(DISTINCT logistics_staff.name) logistics_staff_name,
                group_concat(DISTINCT manager.name) manager_name";
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $count = $repertoryModel->count('rep_id');
            if (strlen($this->sqlCondition['search']) >= 2) {
                $where['repertory.repertory_name|manager.name|logistics_staff.name'] = ['LIKE', "%" . $this->sqlCondition['search'] . "%"];
            } else {
                $where['is_del'] = ['neq', 0];
            }
            $countFiltered = $repertoryModel->getRepertoryNumWithJoin($where);

            $data = $repertoryModel->getRepertoryData($this->field, $where, $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);
            if (count($data) != 0) {
                foreach($data as &$val) {
                    $val['DT_RowId'] = $val['rep_id'];
                }
            }
            $this->output = $this->getDataTableOut($this->posts['draw'], $count, $countFiltered, $data);
            $this->ajaxReturn($this->output);
        } else {
            $this->display();
        }
    }

    /**
     * 修改仓库物流员与管理员
     */
    public function warehouseManager()
    {
        if (IS_POST){
            $params = I('post.');
            $res = M('repertorylist') -> save($params);
            if ($res !== false){
                $this->ajaxReturn([
                    'status' => self::SUCCESS_STATUS,
                    'msg' => '修改成功'
                ]);
            }else{
                $this->ajaxReturn([
                    'status' => self::FAIL_STATUS,
                    'msg' => '修改失败'
                ]);
            }
        }else{
            $data   = [];
            $former = [];
            // 获取仓库信息
            $data['warehouseArr'] = M('repertorylist') -> select();
            foreach ($data['warehouseArr'] as $key => $value) {
                if ($data['warehouseArr'][$key]['warehouse_manager_id'] != ''){
                    $ids = explode(',', $data['warehouseArr'][$key]['warehouse_manager_id']);
                }else{
                    $ids = [];
                }
                $data['warehouseArr'][$key]['warehouse_manager_id'] = $ids;
                $former = array_merge($former, $ids);
                if ($data['warehouseArr'][$key]['logistics_staff_id'] != ''){
                    $ids = explode(',', $data['warehouseArr'][$key]['logistics_staff_id']);
                }else{
                    $ids = [];
                }
                $former = array_merge($former, $ids);
                $data['warehouseArr'][$key]['logistics_staff_id'] = $ids;
            }
            $former = array_unique($former);
            // 获取所有物流员信息
            $tmp = M('auth_role') -> where(['role_id' => ['IN', self::LOGISTICS_ROLE_IDS]]) -> select();
            $logisticsIds = [];
            foreach ($tmp as $key => $value) {
                $logisticsIds = array_unique(array_merge($logisticsIds, explode(',', $value['staff_ids'])));
            }
            $data['logistics'] = M('staff') -> field('id, name label') -> where(['id' => ['IN', $logisticsIds]]) -> select();
            foreach ($data['logistics'] as $key => $value) {
                $data['logistics'][$key]['key'] = $value['id'];
            }
            // 获取所有库管信息
            $tmp = M('auth_role') -> where(['role_id' => ['IN', self::MANAGER_ROLE_IDS]]) -> select();
            $managerIds = [];
            foreach ($tmp as $key => $value) {
                $managerIds = array_unique(array_merge($managerIds, explode(',', $value['staff_ids'])));
            }
            $data['manager'] = M('staff') -> field('id, name label') -> where(['id' => ['IN', $managerIds]]) -> select();
            foreach ($data['manager'] as $key => $value) {
                $data['manager'][$key]['key'] = $value['id'];
            }
            // 补全旧管理员信息
            foreach ($former as $key => $value) {
                if (!in_array($value, $logisticsIds)){
                    $data['logistics'][] = ['key' => $value, 'label' => M('staff') -> field('name') -> find($value)['name'], 'disabled' => true];
                }
                if (!in_array($value, $managerIds)){
                    $data['manager'][] = ['key' => $value, 'label' => M('staff') -> field('name') -> find($value)['name'], 'disabled' => true];
                }
            }
            $this->assign(compact('data'));
            $this->display();
        }
    }


    //-------------------------------------------------------------------------------------------------------------------------------------------------------------------
    /**
     * 其他出库类型出库申请单列表
     */
    public function otherStockOutApplyList(){
        $applyModel = new StockOutOtherApplyModel();
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $stockOutType = $this->posts['stockOutType'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            if($stockOutType == 1) {
                $map['ifnull(p.stock_out_id,0)'] = ['eq', '0'];
            }else if ($stockOutType == 2){
                $map['ifnull(p.stock_out_id,0)'] = ['neq', '0'];
            }else {
                $map = [];
            }

            list($data,$count,$recordsFiltered) = $applyModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $data);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign([
                'pickingType' => StockOutOtherApplyModel::$pickingType,  //领料类型
                'auditTypeMap' => StockOutOtherApplyModel::$auditTypeMap, // 审核类型
                "stockOutMap" => StockOutOtherApplyModel::$stockOutMap,  // 出库状态
            ]);
            $this->display();
        }
    }

    /**
     * 获取申请单下方物料信息
     */
    public function getOtherStockOutApplyMaterial(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                die("参数不全");
            }
            $applyMaterialModel = new StockOutOtherApplyMaterialModel();
            $materialData = $applyMaterialModel->getMsgByApplyId($id);
            $this->returnAjaxMsg("数据获取成功",200, $materialData);

        }else {
            die("非法");
        }
    }

    /**
     * 生成其他入库类型的申请单
     */
    public function createOtherStockOutApply(){
        $purchaseApplyModel = new StockOutOtherApplyModel();
        if(IS_POST){
            $postData = I("post.");
            if(empty($postData['baseMsg']) || empty($postData['materialMsg'])){
                $this->returnAjaxMsg("请将数据填写完整", 400);
            }
            $res = $purchaseApplyModel->createApply($postData['baseMsg'], $postData['materialMsg']);
            $this->ajaxReturn($res);

        }else {
            $staffModel = new StaffModel();
            $staffData = $staffModel->field("id, name")->select();

            $deptModel = new DeptModel();
            $deptData = $deptModel->field("id, name")->select();

            $this->assign([
                'pickingType' => StockOutOtherApplyModel::$pickingType,  //领料类型
                'auditTypeMap' => StockOutOtherApplyModel::$auditTypeMap, // 审核类型
                'staffData' => $staffData,  // 员工列表
                'deptData' => $deptData,   // 部门列表
                "create_name"  => session("nickname"),
            ]);
            $this->display();
        }
    }

    /**
     * 生成申请单编号
     */
    public function createApplyId(){
        $createId = new MaxIdModel();
        $id = $createId->getMaxId('stock_out_purchase_apply');
        if($id){
            $this->returnAjaxMsg('获取编号成功', 200, [
                'idString' => 'SQD-' . $id,
                'id' => $id
            ]);
        }else {
            $this->returnAjaxMsg('获取编号失败', 401);
        }
    }

    /**
     * 申请单审核
     */
    public function auditOtherStockOutApply(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['id']) || empty($data['status'])){
                $this->returnAjaxMsg("参数错误", 400);
            }

            $purchaseApplyModel = new StockOutOtherApplyModel();
            $res = $purchaseApplyModel->auditApply($data['id'], $data['status']);

            $this->ajaxReturn($res);
        }else {
            die("非法");
        }
    }

    /**
     * 修改其他出库类型的申请单
     */
    public function editOtherStockOutApply(){
        $purchaseApplyModel = new StockOutOtherApplyModel();
        if(IS_POST){
            $postData = I("post.");

            if(empty($postData)){
                $this->returnAjaxMsg("未提交修改数据", 400);
            }
            $res = $purchaseApplyModel->editApplyTrans($postData);
            $this->ajaxReturn($res);

        }else {
            $id = I("get.id");
            if(empty($id)){
                die("参数不全");
            }

            $applyData = $purchaseApplyModel->find($id);
            if($applyData['audit_status'] == StockOutOtherApplyModel::TYPE_QUALIFIED){
                die("当前申请单不可以修改");
            }

            $applyMaterialModel = new StockOutOtherApplyMaterialModel();
            $materialData = $applyMaterialModel->getMsgByApplyId($id);

            $staffModel = new StaffModel();
            $staffData = $staffModel->field("id, name")->select();

            $deptModel = new DeptModel();
            $deptData = $deptModel->field("id, name")->select();

            $this->assign([
                'pickingType' => StockOutOtherApplyModel::$pickingType,                  // 领料类型
                'auditTypeMap' => StockOutOtherApplyModel::$auditTypeMap,                // 审核类型
                'outOfTreasuryType' => StockOutOtherApplyModel::$outOfTreasuryType,      // 出库类型
                'staffData' => $staffData,                                                  // 员工列表
                'deptData' => $deptData,                                                    // 部门列表
                'applyData' => $applyData,                                                  // 申请单基本信息
                'materialData' => $materialData                                             // 申请单物料信息
            ]);
            $this->display();
        }
    }

    /**
     * 删除申请单
     */
    public function delOtherApply(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['applyId'])){
                $this->returnAjaxMsg("参数错误", 400);
            }

            $purchaseApplyModel = new StockOutOtherApplyModel();
            $res = $purchaseApplyModel->delApply($data['applyId']);

            $this->ajaxReturn($res);
        }else {
            die("非法");
        }
    }

    /**
     * 删除申请单中一个物料
     */
    public function delOtherApplyMaterial(){
        if(IS_POST){
            $data = I("post.");
                if(empty($data['applyId']) || empty($data['materialId'])){
                $this->returnAjaxMsg("参数错误", 400);
            }

            $purchaseApplyModel = new StockOutOtherApplyModel();
            $res = $purchaseApplyModel->delApplyMaterial($data['applyId'], $data['materialId']);

            $this->ajaxReturn($res);
        }else {
            die("非法");
        }
    }

    //----------------common--------------------------
    //----------------common--------------------------
    /**
     * 修改物料或物料库房调用的接口
     */
    public function getStockMsgOne(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['materialId']) || empty($data['repId'])){
                $this->returnAjaxMsg("参数错误",400);
            }
            $stockModel = new StockModel();
            $stockData = $stockModel->getStockMsgByRepIDAndMaterialId($data['repId'], $data['materialId']);
            $this->ajaxReturn($stockData);
        }else {
            die("非法");
        }
    }

    /**
     * 修改出库单打印次数  自增1
     */
    public function editStockOutOtherPrintTime(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                $this->returnAjaxMsg("参数错误", 400);
            }

            $purchaseModel = new StockOutOtherModel();
            $res = $purchaseModel->where(['id' => $id])->setInc("printing_times");
            if(!$res){
                $this->returnAjaxMsg("打印次数增加失败", 400);
            }

            $this->returnAjaxMsg("打印成功", 200);
        }else {
            die("非法");
        }
    }

    /**
     * 生成出库单编号
     */
    public function createStockOutId(){
        $createId = new MaxIdModel();
        $sourceKind = I("post.source_kind");
        if(empty($sourceKind)){
            $this->returnAjaxMsg("参数不全", 400);
        }
        $id = $createId->getMaxId('stock_out');
        if($id){
            switch ($sourceKind){
                case StockOutRecordModel::TYPE_STOCK_OUT_OTHER :
                    $idString = 'QTCK-' . $id;
                    break;
                case StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM :
                    $idString = 'XSCK-' . $id;
                    break;
//                case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL :
//                    $idString = 'SCLL-' . $id;
//                    break;
                case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCTION :
                    $idString = 'SCLL-' . $id;
                    break;
                default :
                    $this->returnAjaxMsg("参数错误", 400);
                    break;
            }
            $this->returnAjaxMsg('获取编号成功', 200, [
                'idString' => $idString,
                'id'       => $id
            ]);
        }else {
            $this->returnAjaxMsg('获取编号失败', 401);
        }
    }

    //-----------------------------------------------
    /**
     * 获取其他出库类型出库单列表
     */
    public function stockOutOtherList(){
        $stockOutPurchaseModel = new StockOutOtherModel();
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            list($data,$count,$recordsFiltered) = $stockOutPurchaseModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $data);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign([
                'pickingType' => StockOutOtherApplyModel::$pickingType,  //领料类型
                'outOfTreasuryType' => StockOutOtherApplyModel::$outOfTreasuryType,  //出库类别
                "auditMap"     => StockOutOtherModel::$auditMap
            ]);
            $this->display();
        }
    }

    /**
     * 获取其他出库类型出库单下方物料信息
     */
    public function stockOutOtherMaterial(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                die("参数不全");
            }
            $materialModel = new StockMaterialModel();
            $materialData = $materialModel->selectByStockId($id);
            $this->returnAjaxMsg("数据获取成功",200, $materialData);

        }else {
            die("非法");
        }
    }

    /**
     * 申请单下推生成其他出库类型出库单
     */
    public function createOtherStockOut(){
        $stockOutModel = new StockOutOtherModel();
        if (IS_POST){
            $postData = I("post.");
            if(empty($postData['stock']) || empty($postData['material'])){
                $this->returnAjaxMsg("参数不全", 400);
            }
            $res = $stockOutModel->createStock($postData['stock'], $postData['material']);
            $this->ajaxReturn($res);
        }else {
            $id = I("get.id");
            if(empty($id)){
                die("参数不全");
            }
            $purchaseApplyModel = new StockOutOtherApplyModel();
            $applyData = $purchaseApplyModel->find($id);
            if($applyData['audit_status'] != StockOutOtherApplyModel::TYPE_QUALIFIED){
                die("当前申请单未审核通过");
            }

            // 判断是否已经下推了出库单
            $check = $stockOutModel->where(['is_del' => StockOutOtherModel::NO_DEL, "source_id" => $id])->find();
            if(!empty($check)){
                die("当前申请单已经下推了出库单，请不要重复下推");
            }
            //仓库名称map  从crm_repertorylist 表中查出
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();

            $staffModel = new StaffModel();
            $staffData = $staffModel->field("id, name")->select();

            $deptModel = new DeptModel();
            $deptData = $deptModel->field("id, name")->select();

            $applyMaterialModel = new StockOutOtherApplyMaterialModel();
            $materialData = $applyMaterialModel->getMsgByApplyId($id);

            $this->assign([
                'repMap'    => $repMap,                     // 仓库名称
                'pickingType' => StockOutOtherApplyModel::$pickingType,   // 领料类型
                'auditTypeMap' => StockOutOtherApplyMaterialModel::$auditTypeMap,  //审核类型
                'outOfTreasuryType' => StockOutOtherApplyModel::$outOfTreasuryType, // 其他出库类型中的出库类别
                'staffData' => $staffData,   //     员工列表
                'deptData' => $deptData,     //     部门列表
                'applyData' => $applyData,   //     申请单数据
                'materialData' => $materialData,       // 申请单物料数据
                "create_name"  => session("nickname"),
                "cate_id"      => StockOutRecordModel::TYPE_STOCK_OUT_OTHER,   // 当前出库单类型id
                "cate_name"    => StockOutRecordModel::$stockOutType[StockOutRecordModel::TYPE_STOCK_OUT_OTHER], // 出库类型名称
                'sourceTypeMap' => self::$sourceTypeMap,
                'sourceTypeId'  => self::STOCK_OUT_OTHER
            ]);
            $this->display();
        }
    }

    /**
     * 修改其他出库类型的出库单
     */
    public function editOtherStockOut(){
        $stockOutModel = new StockOutOtherModel();
        $materialModel = new StockMaterialModel();
        if (IS_POST){
            $postData = I("post.");
            if(empty($postData['editMaterial']) && empty($postData['stock'])){
                $this->returnAjaxMsg("未获取有效参数", self::$failStatus);
            }

            // 其他出库类型出库单修改 只能修改出库单自有的字段，其他物料相关数据等不能修改
            $stockData = $postData['stock'];
            $editMaterialData = $postData['editMaterial'];
            $data = $stockOutModel->editStockOut($stockData, $editMaterialData);
            $this->ajaxReturn($data);
        }else {
            $id = I("get.id"); // 目前想法：先获取crm_stock_out_orderform 的 id ，将其数据渲染到页面上
            $type = 1; // 标志修改出库单页面
            if(empty($id)){
                die("参数不全");
            }

            // 权限控制
            list($code, $msg) = $stockOutModel->checkAuth($id,$type);
            if(!$code){
                die($msg);
            }

            list($code, $msg, $data) = $stockOutModel->getStockOutAllMsg($id, $type);

            if(!$code){
                die($msg);
            }

            $this->assign($data);
            $this->display();
        }
    }

    /**
     * 其他出库详情页
     */
    public function otherStockOutAllMsg($id){
        if (IS_POST) {
            $stockOutModel = new StockOutOtherModel();
            $materialModel = new StockMaterialModel();
            $stockData = $stockOutModel->find($id);

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

            $this->returnAjaxMsg("数据返回成功",200, [
                'stockData' => $stockData,  // 其他出库类型出库单基本信息
                'materialData' => $materialData, // 其他出库类型出库单物料信息
                'outOfTreasuryType' => StockOutOtherApplyModel::$outOfTreasuryType, // 其他出库类型中的出库类别
                'repMap'    => $repMap,  // 其他出库名称
                'staffData' => $staffData, // 公司员工map
                'deptData' => $deptData,    // 部门map
                "cate_id"      => StockOutRecordModel::TYPE_STOCK_OUT_OTHER,   // 当前出库单类型id
                "cate_name"    => StockOutRecordModel::$stockOutType[StockOutRecordModel::TYPE_STOCK_OUT_OTHER], // 出库类型名称
                "auditMap"     => StockOutOtherModel::$auditMap,
                'pickingType' => StockOutOtherApplyModel::$pickingType, //领料类型
            ]);
        } else {
            $this->assign(compact('id'));
            $this->display();
        }
    }

    /**
     * 获取当前出库单的出库记录
     */
    public function getStockOutRecord(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                die("参数不全");
            }
            $recordModel = new StockOutRecordModel();
            $data = $recordModel->getRecordByStockId($id);
            $this->returnAjaxMsg("数据获取成功",200, $data);
        }else {
            die("非法");
        }
    }

    /**
     * 删除其他类型出库单
     */
    public function delOtherStockOut(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                $this->returnAjaxMsg("参数不全", 400);
            }

            $otherModel = new StockOutOtherModel();
            // 删除销售出库单
            $res = $otherModel->delOther($id);
            $this->ajaxReturn($res);
        }else{
            die("非法");
        }
    }



    //-------------------------------------------------
    /**
     * 销售源单物料信息列表
     */
    public function orderFormSourceProductList(){
        $productModel = new OrderproductModel();
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            list($data,$count,$recordsFiltered) = $productModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $data);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign([
                'stockOutMap' => OrderformModel::$stockOutMap,  // 出库状态Map
                'orderTypeMap' => OrderformModel::$orderTypeMap,  // 订单类型Map
            ]);
            $this->display();
        }
    }

    /**
     * 销售类型出库单列表
     */
    public function orderformStockOutList(){
        $orderformModel = new StockOutOrderformModel();
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            list($data,$count,$recordsFiltered) = $orderformModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $data);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign([
                'auditMap' => StockOutOrderformModel::$auditMap,  // 审核Map
            ]);
            $this->display();
        }
    }

    /**
     * 获取销售出库单下方物料信息
     */
    public function getStockOutOrderformMaterial(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                die("参数不全");
            }
            $materialModel = new StockMaterialModel();
            $materialData = $materialModel->selectByStockId($id);
            $this->returnAjaxMsg("数据获取成功",200, $materialData);

        }else {
            die("非法");
        }
    }

    /**
     * 销售出库新增
     */
    public function createStockOutOrderform(){
        $orderformModel = new OrderformModel();
        $productModel = new OrderproductModel();

        if(IS_POST){
            $postData = I("post.");
            if(empty($postData['baseMsg']) || empty($postData['materialData'])){
                $this->returnAjaxMsg("参数不全", 400);
            }
            $stockOutOrderformModel = new StockOutOrderformModel();
            $res = $stockOutOrderformModel->createOrderform($postData['baseMsg'], $postData['materialData']);
            $this->ajaxReturn($res);
        }else {
            $id = I("get.id"); // 目前想法：先获取crm_orderform 的 id ，将其数据渲染到页面上
            $orderformData = $orderformModel->getOrderformDataById($id);

            if($orderformData['stock_status'] == OrderformModel::TYPE_OUT_ALL){
                die("当前订单下物料已全部出库，不能新增");
            }

            $productData   = $productModel->getOrderProductMsgById($id);

            // 比对当前每一个物料是否满足了要求
            $canCreate = false;
            foreach ($productData as $key => $value){
                if($value['product_num'] != $value['used_num']){
                    $canCreate = true;
                    break;
                }
            }
            if($canCreate == false){
                die("当前销售出库单中物料数量已经达到要求，目前不能新增");
            }

            //仓库名称map  从crm_repertorylist 表中查出
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();

            $staffModel = new StaffModel();
            $staffData = $staffModel->field("id, name")->select();

            $deptModel = new DeptModel();
            $deptData = $deptModel->field("id, name")->select();

            $this->assign([
                'repMap'    => $repMap,            // 仓库名称
                'orderformData' => $orderformData, // 订单基本信息
                'productData' => $productData,  // 订单物料信息
                'staffData' => $staffData,  // 员工列表
                'deptData' => $deptData,   // 部门列表
                "create_name"  => session("nickname"),
                "cate_id"      => StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM,   // 当前出库单类型id
                "cate_name"    => StockOutRecordModel::$stockOutType[StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM], // 出库类型名称
                'sourceTypeMap' => self::$sourceTypeMap,
                'sourceTypeId'  => self::STOCK_OUT_ORDER_FORM,
            ]);
            $this->display();
        }
    }

    /**
     * 销售出库修改
     */
    public function editStockOutOrderform(){
        $orderformModel = new StockOutOrderformModel();
        if(IS_POST){
            $postData = I("post.");
            $stockOutOrderformModel = new StockOutOrderformModel();
            $res = $stockOutOrderformModel->editOrderform($postData);
            $this->ajaxReturn($res);
        }else {
            $id = I("get.id"); // 目前想法：先获取crm_stock_out_orderform 的 id ，将其数据渲染到页面上
            $type = 1; // 标志修改出库单页面

            // 权限控制
            list($code, $msg) = $orderformModel->checkAuth($id,$type);
            if(!$code){
               die($msg);
            }

            list($code, $msg, $data) = $orderformModel->getStockOutAllMsg($id, $type);

            if(!$code){
                die($msg);
            }

            $this->assign($data);
            $this->display();
        }
    }

    /**
     * 销售出库单详情页
     * @param $id
     */
    public function stockOutOrderMsg($id){
        $orderformModel = new StockOutOrderformModel();
        if(IS_POST){
            $orderformData = $orderformModel->getDataById($id);

            $formModel = new OrderformModel();
            $formData = $formModel->where(["is_del" => OrderformModel::NO_DEL, 'id' => $orderformData['source_id']])->find();

            $materialModel = new StockMaterialModel();
            $materialData = $materialModel->selectByStockId($id);
            // 仓库名称map  从crm_repertorylist 表中查出
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();

            // 人员信息
            $staffModel = new StaffModel();
            $staffData = $staffModel->field("id,name")->select();

            // 部门信息
            $deptModel = new DeptModel();
            $deptData = $deptModel->field("id,name")->select();

            $this->returnAjaxMsg("数据返回成功",200, [
                'formData' => $formData,  // 销售出库单源单基本信息
                'orderformData' => $orderformData,  // 销售出库单源单基本信息
                'materialData' => $materialData, // 销售出库单物料信息
                'repMap'    => $repMap,  // 其他出库名称
                'staffData' => $staffData, // 公司员工map
                'deptData' => $deptData,    // 部门map
                "cate_id"      => StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM,   // 当前出库单类型id
                "cate_name"    => StockOutRecordModel::$stockOutType[StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM], // 出库类型名称
                "auditMap"     => StockOutOrderformModel::$auditMap,
            ]);
        }else{
            $this->assign(compact('id'));
            $this->display();
        }
    }

    /**
     * 销售出库删除出库单
     */
    public function delStockOutOrderform(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                $this->returnAjaxMsg("参数不全", 400);
            }

            $orderformModel = new StockOutOrderformModel();
            // 删除销售出库单
            $res = $orderformModel->delOrderform($id);
            $this->ajaxReturn($res);

        }else {
            die("非法");
        }
    }

    /**
     * 销售出库删除物料信息
     */
    public function delStockOutOrderformMaterial(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                $this->returnAjaxMsg("参数不全", 400);
            }

            $orderformModel = new StockOutOrderformModel();

            // 删除销售出库单
            $res = $orderformModel->delStockOutOrderformMaterial($id);
            $this->ajaxReturn($res);

        }else {
            die("非法");
        }
    }

    //-----------------------------------------
    /**
     * 生产领料列表
     */
    public function stockOutProductionOrderList(){
        $produceModel = new StockOutProduceModel();
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            list($data,$count, $recordsFiltered) = $produceModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $data);
            $this->ajaxReturn($this->output);
        }else {
            //            仓库名称map  从crm_repertorylist 表中查出
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();
            $this->assign([
                'repMap'    => $repMap,  // 其他出库名称
                'auditMap' => StockOutOrderformModel::$auditMap,  // 审核Map
                'pickingType' => StockOutOtherApplyModel::$pickingType,   // 领料类型
            ]);
            $this->display();
        }
    }

    /**
     * 生成生产领料
     */
    public function createStockOutProduce(){
        if(IS_POST){
            $postData = I("post.");
            if(empty($postData['produce']) || empty($postData['material'])){
                $this->returnAjaxMsg("参数不全", 400);
            }

            $stockOutProduceModel = new StockOutProduceModel();
            $res = $stockOutProduceModel->createProduce($postData['produce'], $postData['material']);
            $this->ajaxReturn($res);
        } else {
            $id = I("get.id"); // crm_production_order 的ID

            $productionOrderModel = new ProductionOrderModel();
            $productionOrderData = $productionOrderModel->getOrderBaseMsgById($id);

            if($productionOrderData['stock_status'] == ProductionOrderModel::TYPE_OUT_ALL){
                die("该生产计划已下推所有配料，不能生成领料单");
            }

            if(empty($productionOrderData['bom_pid'])){
                die("当前生产订单没有bom");
            }

            // 当前生产任务单中所有的物料信息
            $materialData = $productionOrderModel->getMaterialMsg($id);
            // 将替代物料放入对应的被替代物料信息数组里面
            $productIdArr= array_column($materialData, 'product_id');
            $replaceModel = new MaterialSubstituteModel();
            $replaceData = $replaceModel->findSubstituteByProductId($productIdArr, 2);

            $productionOrderData['used_num'] = 0;
            $canCreate = false;
            foreach ($materialData as $k => &$v){
                if($v['total_num'] > $v['used_num']){
                    $canCreate = true;
                }
                $productionOrderData['used_num'] = $v['used_num'];
                $v['replace_data'][] = ['substituted_id' => $v['product_id'], 'product_name' => $v['product_name'], "product_number" => $v['product_number'],'product_no' => $v['product_no']];
                foreach ($replaceData as $key => $item){
                    if($v['product_id'] == $item['product_id']){
                        $v['replace_data'][] = $item;
                    }
                }
            }
            unset($v);

            if(!$canCreate){
                die("领料出库单中申请数量已经达到要求，目前不可新增领料出库单");
            }

            // 人员信息
            $staffModel = new StaffModel();
            $staffData = $staffModel->field("id,name")->select();

            // 部门信息
            $deptModel = new DeptModel();
            $deptData = $deptModel->field("id,name")->select();

//            仓库名称map  从crm_repertorylist 表中查出
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();

            $this->assign([
                'repMap'    => $repMap,  // 其他出库名称
                'staffData' => $staffData, // 公司员工map
                'deptData'  => $deptData,    // 部门map
                'productionOrderData' => $productionOrderData, // 领料订单源单信息
                'materialData' => $materialData, // bom物料信息
                'pickingType' => StockOutOtherApplyModel::$pickingType, // 领料类型
                "cate_id"      => StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL,   // 当前出库单类型id
                "cate_name"    => StockOutRecordModel::$stockOutType[StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL], // 出库类型名称
                "create_name"  => session("nickname"),
                'sourceTypeMap' => self::$sourceTypeMap,
                'sourceTypeId'  => self::STOCK_OUT_PRODUCE
            ]);

            $this->display();
        }
    }

    /**
     * 修改生产领料出库单
     */
    public function editStockOutProduce(){
        $produceModel = new StockOutProduceModel();

        if(IS_POST){
            $postData = I("post.");
            if(empty($postData['produce'])){
                return dataReturn("参数不全", 400);
            }
            $res = $produceModel->editProduce($postData);
            $this->ajaxReturn($res);
        }else {
            $id = I("get.id");
            $type = 1; // 标志修改页面
            if(empty($id)){
                die("参数不全");
            }

            // 权限控制
            list($code, $msg) = $produceModel->checkAuth($id,$type);
            if(!$code){
                die($msg);
            }

            list($code, $msg, $data) = $produceModel->getStockOutAllMsg($id, $type);

            if(!$code){
                die($msg);
            }

            $this->assign($data);
            $this->display();

        }
    }

    /**
     * 领料出库单详情
     */
    public function stockOutProduceMsg($id){
        $produceModel = new StockOutProduceModel();
        if(IS_POST){
            $produceData = $produceModel->find($id);

            $orderModel = new ProductionOrderModel();
            $orderData = $orderModel->where(["is_del" => OrderformModel::NO_DEL, 'id' => $produceData['source_id']])->find();

            $materialModel = new StockMaterialModel();
            $materialData = $materialModel->selectByStockId($id);
            // 仓库名称map  从crm_repertorylist 表中查出
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();

            // 人员信息
            $staffModel = new StaffModel();
            $staffData = $staffModel->field("id,name")->select();

            // 部门信息
            $deptModel = new DeptModel();
            $deptData = $deptModel->field("id,name")->select();

            $this->returnAjaxMsg("数据返回成功",200, [
                'orderData' => $orderData,  // 领料出库单源单基本信息
                'produceData' => $produceData,  // 领料出库单源单基本信息
                'materialData' => $materialData, // 领料出库单物料信息
                'repMap'    => $repMap,  // 其他出库名称
                'staffData' => $staffData, // 公司员工map
                'deptData' => $deptData,    // 部门map
                "cate_id"      => StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL,   // 当前出库单类型id
                "cate_name"    => StockOutRecordModel::$stockOutType[StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL], // 出库类型名称
                "auditMap"     => StockOutOrderformModel::$auditMap,
                'pickingType' => StockOutOtherApplyModel::$pickingType,   // 领料类型
            ]);
        }else{
            $this->assign(compact('id'));
            $this->display();
        }
    }

    /**
     * 删除生产领料单信息
     */
    public function delStockOutProduce(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                $this->returnAjaxMsg("参数不全", 400);
            }

            $produceModel = new StockOutProduceModel();
            // 删除销售出库单
            $res = $produceModel->delStockOutProduce($id);
            $this->ajaxReturn($res);

        }else {
            die("非法");
        }
    }

    /**
     * 获取领料类型出库单下方物料信息
     */
    public function stockOutProduceMaterial(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                die("参数不全");
            }
            $materialModel = new StockMaterialModel();
            $materialData = $materialModel->selectByStockId($id);
            $this->returnAjaxMsg("数据获取成功",200, $materialData);

        }else {
            die("非法");
        }
    }


    //------------------------------------------------

    /**
     * 生成领料出库单  新需求的领料出库单
     */
    public function createStockOutProduction(){
        if(IS_POST){
            $postData = I("post.");
            $productionModel = new StockOutProductionModel();
            $data = $productionModel->createProduction($postData['production'], $postData['material']);
            $this->ajaxReturn($data);
        }else {
            $id = I("get.id"); // crm_production_order 的多个ID 是一个数组
            $idArr = explode(',', $id);

            $productionOrderModel = new ProductionOrderModel();
            $productModel = new ProductionOrderProductModel();
            $productionOrderData = $productionOrderModel->getOrderBaseMsgByIdArr($idArr);

            $status = array_unique(array_column($productionOrderData,'audit_status'));
            if(!(count($status) == 1 && $status[0] == ProductionOrderModel::TYPE_QUALIFIED)){
                die("当前选择的生产计划不是全部审核合格的,请重新选择");
            }

            $stockStatus = array_unique(array_column($productionOrderData,'stock_status'));
            if(in_array(ProductionOrderModel::TYPE_OUT_ALL,$stockStatus)){
                die("当前选择的生产计划存在出库完成的,请重新选择");
            }
            
            $bomPid = array_column($productionOrderData,'bom_pid');

            if(count(array_filter($bomPid)) != count($bomPid)){
                die("当前选择的生产计划存在没有bom的情况");
            }

            $productionLine = array_unique(array_column($productionOrderData,'production_line'));
            if(count($productionLine) != 1){
                die("当前选择的生产计划不是同一条生产线，即无法合并下推");
            }

            // 当前生产任务单未下推的物料信息
            $map['p.push_num'] = ['eq', 0];
            $noPushMaterialData = $productModel->getProductionProductByIdArr($idArr, $map);
            if(empty($noPushMaterialData)){
                die("当前选择的生产计划，无可下推物料");
            }

            $noPushRepMap = array_unique(array_column($noPushMaterialData,'warehouse_id'));
            $materialData = [];
            foreach ($noPushMaterialData as $k => $v){
                $materialData[$v['warehouse_id']][] = $v;
            }

            // 当前生产任务单已下推的物料信息
            $where['p.push_num'] = ['neq', 0];
            $pushMaterialData = $productModel->getProductionProductByIdArr($idArr, $where);

            // 人员信息
            $staffModel = new StaffModel();
            $staffData = $staffModel->field("id,name")->select();

            // 部门信息
            $deptModel = new DeptModel();
            $deptData = $deptModel->field("id,name")->select();

//            仓库名称map  从crm_repertorylist 表中查出
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();

            $this->assign([
                'repMap'    => $repMap,  // 出库仓库Map
                'staffData' => $staffData, // 公司员工map
                'deptData'  => $deptData,    // 部门map
                'productionOrderData' => $productionOrderData, // 领料订单源单信息
                'noPushMaterialData' => $materialData, // 没有下推的物料信息
                'pushMaterialData' => $pushMaterialData, // 已下推的物料信息
                'noPushRepMap' => $noPushRepMap, // 未下推物料出库仓库分类
                'pickingType'  => StockOutOtherApplyModel::$pickingType, // 领料类型
                "cate_id"      => StockOutRecordModel::TYPE_STOCK_OUT_PRODUCTION,   // 当前出库单类型id
                "cate_name"    => StockOutRecordModel::$stockOutType[StockOutRecordModel::TYPE_STOCK_OUT_PRODUCTION], // 出库类型名称
                "create_name"  => session("nickname"),
                'sourceTypeMap' => self::$sourceTypeMap,
                'sourceTypeId'  => self::STOCK_OUT_PRODUCTION
            ]);
            $this->display();
        }
    }

    /**
     * 删除领料出库单
     */
    public function delStockOutProduction(){
        if(IS_POST){
            $id = I("post.id");

            if(empty($id)){
                $this->returnAjaxMsg("参数错误",400);
            }
            $productionModel = new StockOutProductionModel();

            $data = $productionModel->delProduction($id);
            $this->ajaxReturn($data);
        }else {
            die("非法");
        }
    }

    //-------------------------------------------------

    /**
     * 出库单审核出库记录
     */
    public function auditStockOut(){
        if(IS_POST){
            $id = I("post.id");
            $status = I("post.status");
            if(empty($id)|| empty($status)){
                $this->returnAjaxMsg("参数不全", 400);
            }
            $recordModel = new StockOutRecordModel();
            $res = $recordModel->recordAudit($id, $status);
            $this->ajaxReturn($res);

        }else {
            die("非法");
        }
    }

    /**
     * 出库记录列表  已入库
     */
    public function stockRecordQualifiedList(){
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $sourceKind = $this->posts['source_kind'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $recordModel = new StockOutRecordModel();

            $checkStatus = $this->posts['checkStatus'];
            switch ($checkStatus){
                case 2:
                    // 未出的出库单出库记录
                    $map['r.status'] = ['neq', StockOutRecordModel::TYPE_QUALIFIED];
                    break;
                case 1:
                    // 已出库单出库记录
                    $map['r.status'] = ['eq', StockOutRecordModel::TYPE_QUALIFIED];
                default :
                    $this->ajaxReturn("参数错误",200);
            }

            list($data,$count,$recordsFiltered) = $recordModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $sourceKind, $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $data);
            $this->ajaxReturn($this->output);
        }else {
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();
            $this->assign([
                'auditMap' => StockOutRecordModel::$auditMap,
                'repMap'    => $repMap,  // 其他出库名称
                "cateMap"   => StockOutRecordModel::$stockOutType,
            ]);
            $this->display();
        }
    }

    /**
     * 出库记录列表   目前是为了审核出库记录
     */
    public function stockRecordUnQualifiedList(){
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $sourceKind = $this->posts['source_kind'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $recordModel = new StockOutRecordModel();
            $map['r.status'] = ['neq', StockOutRecordModel::TYPE_QUALIFIED];
            list($data,$count,$recordsFiltered) = $recordModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $sourceKind, $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $data);
            $this->ajaxReturn($this->output);
        }else {
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();
            $this->assign([
                'auditMap' => StockOutRecordModel::$auditMap,
                'repMap'    => $repMap,  // 其他出库名称
                "cateMap"   => StockOutRecordModel::$stockOutType,
            ]);
            $this->display();
        }
    }

    /**
     * 整个出库单审核
     */
    public function auditWholeStockOut(){
        if(IS_POST){
            $id = I("post.id");
            $sourceType = I("post.source_kind");
            if(empty($id) || empty($sourceType)){
                $this->returnAjaxMsg("参数不全", 400);
            }
            $recordModel = new StockOutRecordModel();
            $res = $recordModel->stockOutAudit($id, $sourceType);
            $this->ajaxReturn($res);

        }else {
            die("非法");
        }
    }

    /**
     * 全部非审核完成出库单信息 显示出库单内信息
     */
    public function stockOutingList(){
        $recordModel = new StockOutRecordModel();
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $sourceKind = $this->posts['source_kind'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $checkStatus = $this->posts['checkStatus'];

            switch ($checkStatus){
                case 1:
                    // 已出的出库单单据
                    $map['o.audit_status'] = ['eq', StockOutOrderformModel::TYPE_STOCK_QUALIFIED];
                    break;
                case 2:
                    // 未出库单单据
                    $map['o.audit_status'] = ['neq', StockOutOrderformModel::TYPE_STOCK_QUALIFIED];
                default :
                    $this->ajaxReturn("参数错误",200);
            }
            list($data,$count,$recordsFiltered) = $recordModel->getStockOutList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $sourceKind, $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $data);
            $this->ajaxReturn($this->output);
        }else {
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();
            $this->assign([
                'auditMap' => StockOutOtherModel::$auditMap,  // 出库单状态map
                'repMap'    => $repMap,  // 其他出库库房名称
                "cateMap"   => StockOutRecordModel::$stockOutType,  //出库单类型
                'recordAuditMap' => StockOutRecordModel::$auditMap
            ]);
            $this->display();
        }
    }

    /**
     * 全部出库完成出库单信息 显示出库单内信息
     */
    public function stockOutQualifiedList(){
        $recordModel = new StockOutRecordModel();
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $sourceKind = $this->posts['source_kind'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $map['o.audit_status'] = ['eq', StockOutOrderformModel::TYPE_STOCK_QUALIFIED];
            list($data,$count,$recordsFiltered) = $recordModel->getStockOutList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $sourceKind, $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $data);
            $this->ajaxReturn($this->output);
        }else {
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();
            $this->assign([
                'auditMap' => StockOutOrderformModel::$auditMap,
                'repMap'    => $repMap,  // 其他出库名称
                "cateMap"   => StockOutRecordModel::$stockOutType,
                'recordAuditMap' => StockOutRecordModel::$auditMap
            ]);
            $this->display();
        }
    }

    /**
     * 获取出库单下方物料信息
     */
    public function stockOutMaterial(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                die("参数不全");
            }
            $materialModel = new StockMaterialModel();
            $materialData = $materialModel->selectByStockId($id);
            $this->returnAjaxMsg("数据获取成功",200, $materialData);

        }else {
            die("非法");
        }
    }

    /**
     * 删除出库单
     */
    public function delStockOut(){
        if(IS_POST){
            $id = I("post.id");
            $sourceType = I("post.source_kind");
            if(empty($id) || empty($sourceType)){
                $this->returnAjaxMsg("参数不全", 400);
            }
            switch ($sourceType){
                case StockOutRecordModel::TYPE_STOCK_OUT_OTHER :
                    $stockOutModel = new StockOutOtherModel();
                    $res = $stockOutModel->delOther($id);
                    break;
                case StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM :
                    $stockOutModel = new StockOutOrderformModel();
                    $res = $stockOutModel->delOrderform($id);
                    break;
                /*case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL :
                    $stockOutModel = new StockOutProduceModel();
                    $res = $stockOutModel->delStockOutProduce($id);
                    break;*/
                case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCTION :
                    $stockOutModel = new StockOutProductionModel();
                    $res = $stockOutModel->delProduction($id);
                    break;
                default :
                    $this->returnAjaxMsg("出库参数有误",400);
                    break;
            }
            $this->ajaxReturn($res);
        }else {
            die("非法");
        }
    }

    /**
     * 获取出库单详情
     */
    public function getStockOutDetail(){
        if (IS_POST) {
            die("非法");
        } else {
            $data = I("get.");
            if(empty($data['id']) || empty($data['source_kind'])){
                die("参数获取不全");
            }

            switch ($data['source_kind']){
                case StockOutRecordModel::TYPE_STOCK_OUT_OTHER :
                    $stockOutModel = new StockOutOtherModel();
                    break;
                case StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM :
                    $stockOutModel = new StockOutOrderformModel();
                    break;
                /*case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL :
                    $stockOutModel = new StockOutProduceModel();
                    break;*/
                default :
                    $this->returnAjaxMsg("参数不正确",400);
                    break;
            }
            $materialModel = new StockMaterialModel();
            $stockData = $stockOutModel->find($data['id']);
            $materialData = $materialModel->selectByStockId($data['id']);

            // 仓库名称map  从crm_repertorylist 表中查出
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();

            // 人员信息
            $staffModel = new StaffModel();
            $staffData = $staffModel->field("id,name")->select();

            // 部门信息
            $deptModel = new DeptModel();
            $deptData = $deptModel->field("id,name")->select();

            $this->assign([
                'repMap'    => $repMap,  // 其他出库名称
                'staffData' => $staffData, // 公司员工map
                'deptData' => $deptData,    // 部门map
                'outOfTreasuryType' => StockOutOtherApplyModel::$outOfTreasuryType, // 其他出库类型中的出库类别
                "cateMap"    => StockOutRecordModel::$stockOutType, // 出库类型
                "auditMap"     => StockOutOtherModel::$auditMap,    // 审核状态
                'pickingType' => StockOutOtherApplyModel::$pickingType, //领料类型
                'stockData' => $stockData,  // 其他出库类型出库单基本信息
                'materialData' => $materialData, // 其他出库类型出库单物料信息
                "stockOutType" => StockOutRecordModel::$stockOutType,
            ]);
            $this->display();
        }
    }

    /**
     * 回退出库单渲染页面
     */
    public function rollBackMaterial(){
        if(IS_POST){
            die("非法");
        }else {
            $id = I("get.id");
            $sourceKind = I("get.source_kind");
            $type = 2; // 标志回退物料页面

            if(empty($id) || empty($sourceKind)){
                die("参数不全");
            }
            switch ($sourceKind){
                case StockOutRecordModel::TYPE_STOCK_OUT_OTHER :
                    $stockOutModel = new StockOutOtherModel();
                    break;
                case StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM :
                    $stockOutModel = new StockOutOrderformModel();
                    break;
                /*case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL :
                    $stockOutModel = new StockOutProduceModel();
                    break;*/
                case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCTION :
                    die("目前不支持回退领料出库单物料");
                    break;
                default :
                    die("出库单类型不明确");
                    break;
            }

            // 权限控制
            list($code, $msg) = $stockOutModel->checkAuth($id,$type);
            if(!$code){
                die($msg);
            }

            list($code, $msg, $data) = $stockOutModel->getStockOutAllMsg($id, $type);

            if(!$code){
                die($msg);
            }

            $this->assign($data);

            switch ($sourceKind){
                case StockOutRecordModel::TYPE_STOCK_OUT_OTHER :
                    $this->display("rollBackOtherMaterial");
                    break;
                case StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM :
                    $this->display("rollBackOrderFormMaterial");
                    break;
                /*case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL :
                    $this->display("rollBackProduceMaterial");
                    break;*/
                default :
                    die("出库单类型不明确");
                    break;
            }
        }
    }

    /**
     * 回退整个出库单
     */
    public function rollBackStockOut(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['id']) || empty($data['source_kind'])){
                $this->returnAjaxMsg("参数不全",400);
            }

            switch ($data['source_kind']){
                case StockOutRecordModel::TYPE_STOCK_OUT_OTHER :
                    $stockOutModel = new StockOutOtherModel();
                    break;
                case StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM :
                    $stockOutModel = new StockOutOrderformModel();
                    break;
                /*case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL :
                    $stockOutModel = new StockOutProduceModel();
                    break;*/
                case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCTION :
                    $this->returnAjaxMsg("目前不支持回退领料出库单物料",400);
                    break;
                default :
                    $this->returnAjaxMsg("参数不正确",400);
                    break;
            }

            $data = $stockOutModel->rollBackAllMaterial($data['id']);
            $this->ajaxReturn($data);
        }else {
            die("非法");
        }
    }

    /**
     * 回退出库单一个物料全部数量
     */
    public function rollBackStockOutOneMaterial(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['id']) || empty($data['source_kind'])){
                $this->returnAjaxMsg("参数不全",400);
            }

            switch ($data['source_kind']){
                case StockOutRecordModel::TYPE_STOCK_OUT_OTHER :
                    $stockOutModel = new StockOutOtherModel();
                    break;
                case StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM :
                    $stockOutModel = new StockOutOrderformModel();
                    break;
                case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCTION :
                    $this->returnAjaxMsg("目前不支持回退领料出库单物料",400);
                    break;
                default :
                    $this->returnAjaxMsg("参数不正确",400);
                    break;
            }

            $data = $stockOutModel->rollBackOneAllMaterial($data['id']);
            $this->ajaxReturn($data);
        }else {
            die("非法");
        }
    }

    /**
     * 回退出库单一个物料的部分数量
     */
    public function rollBackStockOutOnePartMaterial(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['id']) || empty($data['source_kind']) || empty($data['num'])){
                $this->returnAjaxMsg("参数不全",400);
            }

            switch ($data['source_kind']){
                case StockOutRecordModel::TYPE_STOCK_OUT_OTHER :
                    $stockOutModel = new StockOutOtherModel();
                    break;
                case StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM :
                    $stockOutModel = new StockOutOrderformModel();
                    break;
                default :
                    $this->returnAjaxMsg("参数不正确",400);
                    break;
            }

            $data = $stockOutModel->rollBackOnePartMaterial($data['id'], $data['num']);
            $this->ajaxReturn($data);
        }else {
            die("非法");
        }
    }

    /**
     * 回退领料出库单出库部分数量
     */
    public function rollBacKProductMaterial(){
        if(IS_POST){
            $id = I("post.id");
            $num = I("post.num");
            if(empty($id) || empty($num)){
                $this->returnAjaxMsg("参数不全",400);
            }

            $stockOutModel = new StockOutProduceModel();
            $data = $stockOutModel->rollBackAllMaterial($id, $num);
            $this->ajaxReturn($data);
        }else {
            die("非法");
        }
    }

    //==================================

    /**
     * 按库房打印生产领料类型出库单
     */
    private function printingStockOutProduce(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                $this->returnAjaxMsg("参数不全", 400);
            }
            $produceModel = new StockOutProduceModel();
            $materialModel = new StockMaterialModel();

            $baseMsg = $produceModel->getMsgForPrinting($id);
            $materialData = $materialModel->selectBaseMsgByStockOutId($id);

            if(empty($materialData)){
                $this->returnAjaxMsg("当前出库单没有出库物料信息", 400);
            }

            $data = [];
            foreach ($materialData as $key => $value){
                $data[$value['rep_pid']][] = $value;
            }

            $fileUrl = $produceModel->printingToPdfEx($baseMsg, $data);
            if(!$fileUrl){
                $this->returnAjaxMsg("更新下载记录失败", 400);
            }

            $this->returnAjaxMsg("出库单下载成功",200, [
                'fileUrl' => $fileUrl
            ]);

        }else {
            die("非法");
        }
    }

    /**
     * 按库房打印销售类型出库单
     */
    private function printingStockOutOrderForm(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                $this->returnAjaxMsg("参数不全", 400);
            }
            $orderformModel = new StockOutOrderformModel();
            $materialModel = new StockMaterialModel();

            $baseMsg = $orderformModel->getMsgForPrinting($id);
            $materialData = $materialModel->selectBaseMsgByStockOutId($id);

            if(empty($materialData)){
                $this->returnAjaxMsg("当前出库单没有出库物料信息", 400);
            }
            $data = [];
            foreach ($materialData as $key => $value){
                $data[$value['rep_pid']][] = $value;
            }

            $fileUrl = $orderformModel->printingToPdfEx($baseMsg, $data);
            if(!$fileUrl){
                $this->returnAjaxMsg("更新下载记录失败", 400);
            }

            $this->returnAjaxMsg("出库单下载成功",200, [
                'fileUrl' => $fileUrl
            ]);

        }else {
            die("非法");
        }
    }

    /**
     * 按库房打印其他类型出库单
     */
    private function printingStockOutOther(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                $this->returnAjaxMsg("参数不全", 400);
            }
            $otherModel = new StockOutOtherModel();
            $materialModel = new StockMaterialModel();

            $baseMsg = $otherModel->where(['id' => $id])->find();
            $materialData = $materialModel->selectBaseMsgByStockOutId($id);
            if(empty($materialData)){
                $this->returnAjaxMsg("当前出库单下方无数据", 400);
            }

            $data = [];
            foreach ($materialData as $key => $value){
                $data[$value['rep_pid']][] = $value;
            }

            $fileUrl = $otherModel->printingToPdfEx($baseMsg, $data);
            if(!$fileUrl){
                $this->returnAjaxMsg("更新下载记录失败", 400);

            }

            $this->returnAjaxMsg("出库单下载成功",200, [
                'fileUrl' => $fileUrl
            ]);

        }else {
            die("非法");
        }
    }

    /**
     * 待出库记录表中出库单打印成pdf
     */
    private function stockOutPrintToPdf(){
        if(IS_POST){
            $id = I("post.id"); // 出库单id
            $sourceType = I("post.source_kind"); // 出库单类型

            if(empty($id) || empty($sourceType)){
                $this->returnAjaxMsg("参数不全", 400);
            }

            switch ($sourceType){
                case StockOutRecordModel::TYPE_STOCK_OUT_OTHER :
                    $stockOutModel = new StockOutOtherModel();
                    $baseMsg = $stockOutModel->where(['id' => $id])->find();
                    break;
                case StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM :
                    $stockOutModel = new StockOutOrderformModel();
                    $baseMsg = $stockOutModel->getMsgForPrinting($id);
                    break;
                /*case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL :
                    $stockOutModel = new StockOutProduceModel();
                    $baseMsg = $stockOutModel->getMsgForPrinting($id);
                    break;*/
                default :
                    $this->returnAjaxMsg("出库单类型不明", 400);
                    break;
            }

            $materialModel = new StockMaterialModel();
            $materialData = $materialModel->selectBaseMsgByStockOutId($id);
            if(empty($materialData)){
                $this->returnAjaxMsg("当前出库单没有出库物料信息", 400);
            }

            // 按库房分类
            $data = [];
            foreach ($materialData as $key => $value){
                $data[$value['rep_pid']][] = $value;
            }

            $fileUrl = $stockOutModel->printingToPdfEx($baseMsg, $data);
            if(!$fileUrl){
                $this->returnAjaxMsg("更新下载记录失败", 400);
            }

            $this->returnAjaxMsg("出库单下载成功",200, [
                'fileUrl' => $fileUrl
            ]);
        }else {
            die("非法");
        }
    }

    /**
     * 待入库记录表中出库单打印成pdf
     */
    private function stockInPrintToPdf(){
        if(IS_POST){
            $id = I("post.recordId"); // 出库单id
            $sourceType = I("post.recordType"); // 出库单类型

            if(empty($id) || empty($sourceType)){
                $this->returnAjaxMsg("参数不全", 400);
            }

            switch ($sourceType){
                case StockInRecordModel::SOURCE_OTHER_TYPE :
                    $stockInModel = new StockInOtherModel();
                    break;
                case StockInRecordModel::SOURCE_PURCHASE_TYPE :
                    $stockInModel = new StockInPurchaseModel();
                    break;
                case StockInRecordModel::SOURCE_PRODUCTION_TYPE :
                    $stockInModel = new StockInProductionModel();
                    break;
                default :
                    $this->returnAjaxMsg("出库单类型不明", 400);
                    break;
            }
            $baseMsg = $stockInModel->getBaseDataById($id);

            $materialModel = new StockInRecordModel();
            $materialData = $materialModel->getDataByStockInId($id);
            if(empty($materialData)){
                $this->returnAjaxMsg("当前出库单没有出库物料信息", 400, $materialData);
            }

            // 按库房分类
            $data = [];
            foreach ($materialData as $key => $value){
                $data[$value['repertory_id']][] = $value;
            }

            $fileUrl = $stockInModel->printingToPdfEx($baseMsg, $data);
            if(!$fileUrl){
                $this->returnAjaxMsg("更新下载记录失败", 400);
            }

            $this->returnAjaxMsg("生成出库单据完毕，是否打印",200, [
                'fileUrl' => $fileUrl
            ]);
        }else {
            die("非法");
        }
    }

    public function printInHtml()
    {
        $id = I("get.recordId"); // 出库单id
        $sourceType = I("get.recordType"); // 出库单类型

        if(empty($id) || empty($sourceType)){
            die('参数不全');
        }

        switch ($sourceType){
            case StockInRecordModel::SOURCE_OTHER_TYPE :
                $stockInModel = new StockInOtherModel();
                break;
            case StockInRecordModel::SOURCE_PURCHASE_TYPE :
                $stockInModel = new StockInPurchaseModel();
                break;
            case StockInRecordModel::SOURCE_PRODUCTION_TYPE :
                $stockInModel = new StockInProductionModel();
                break;
            default :
                die('出库单类型不明');
                break;
        }
        $baseMsg = $stockInModel->getBaseDataById($id);

        $materialModel = new StockInRecordModel();
        $materialData = $materialModel->getDataByStockInId($id);
        if(empty($materialData)){
            die('当前出库单没有出库物料信息');
//            $this->returnAjaxMsg("当前出库单没有出库物料信息", 400, $materialData);
        }

        // 按库房分类
        $data = [];
        foreach ($materialData as $key => $value){
            $data[$value['repertory_id']][] = $value;
        }

        $fileUrl = $stockInModel->printingToPdfEx($baseMsg, $data);
        if(!$fileUrl){
            die("生成失败");
        }

        // 更新打印次数
        $stockRes = $stockInModel->where(['id' => $id])->setInc("print_time");
        if(!$stockRes){
            die("更新打印次数失败");
        }
        $this->assign(compact('fileUrl'));
        $this->display('printHtml');
    }

    public function printOutHtml()
    {
        $id = I("get.id"); // 出库单id
        $sourceType = I("get.source_kind"); // 出库单类型

        if(empty($id) || empty($sourceType)){
            die("参数不全");
        }

        switch ($sourceType){
            case StockOutRecordModel::TYPE_STOCK_OUT_OTHER :
                $stockOutModel = new StockOutOtherModel();
                $baseMsg = $stockOutModel->where(['id' => $id])->find();
                break;
            case StockOutRecordModel::TYPE_STOCK_OUT_ORDER_FORM :
                $stockOutModel = new StockOutOrderformModel();
                $baseMsg = $stockOutModel->getMsgForPrinting($id);
                break;
            case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCE_MATERIAL :
                $stockOutModel = new StockOutProduceModel();
                $baseMsg = $stockOutModel->getMsgForPrinting($id);
                break;
            case StockOutRecordModel::TYPE_STOCK_OUT_PRODUCTION :
                $stockOutModel = new StockOutProductionModel();
                $baseMsg = $stockOutModel->getMsgForPrinting($id);
                break;
            default :
                die("出库参数有误");
                break;
        }

        $materialModel = new StockMaterialModel();
        $materialData = $materialModel->selectBaseMsgByStockOutId($id);
        if(empty($materialData)){
            die("当前出库单没有出库物料信息");
//            $this->returnAjaxMsg("当前出库单没有出库物料信息", 400);
        }

        // 按库房分类
        $data = [];
        foreach ($materialData as $key => $value){
            $data[$value['rep_pid']][] = $value;
        }

        $fileUrl = $stockOutModel->printingToPdfEx($baseMsg, $data);

        // 更新打印次数
        $stockRes = $stockOutModel->where(['id' => $id])->setInc("printing_times");
        if(!$stockRes){
            die("更新打印次数失败");
        }
        $this->assign(compact('fileUrl'));
        $this->display('printHtml');

    }

    public function printTransferHtml()
    {
        $id = I("get.id"); // 调拨单id

        if(empty($id)){
            die('参数不全');
        }

        $transferModel = new StockTransferModel();
        $baseMsg = $transferModel->find();
        $materialModel = new StockTransferMaterialModel();
        $materialData = $materialModel->getBaseMaterialMsgByPid($id);
        if(empty($materialData)){
            die('当前出库单没有出库物料信息');
        }

        $fileUrl = $transferModel->printingToPdfEx($baseMsg, $materialData);
        if(!$fileUrl){
            die('更新下载记录失败');
        }

        // 更新打印次数
        $transRes = $transferModel->where(['id' => $id])->setInc("print_time");
        if(!$transRes){
            die("更新打印次数失败");
        }

        $this->assign(compact('fileUrl'));
        $this->display('printHtml');
    }

    /**
     * 预览pdf
     * pdf在线预览方法, 分为chrome和ie两种
     * @param $id
     */
    public function previewPdf()
    {
        $id = I("get.id"); // 合同id
        if(empty($id)){
            die("参数不全");
        }

        $ua = $_SERVER['HTTP_USER_AGENT'];
        $ie = ['compatible', 'Trident', 'MSIE '];
        $viewName = 'previewPdf';
        foreach ($ie as $item) {
            if (strpos($ua, $item) !== false){
                $viewName = 'previewPdfIE';
                break;
            }
        }
        if (file_exists(WORKING_PATH . $id)){
            if ($viewName == 'previewPdf'){
                $messy = sha1($id);
                $messy = str_repeat($id, 5);
                redirect(U('previewPdfChrome', '', '') . '?file='. $id);
            }else{
                die("<h3 style='text-align: center'>您的浏览器版本过低，请您改用Chrome\FireFox\360等浏览器进行查看</h3>");
            }
        }else{
            die("<h3 style='text-align: center;'>未找到文件，请联系管理解决？</h3>");
        }
    }

    public function previewPdfChrome()
    {
        $this->display('File/previewPdfChrome');
    }
    //================================================

    /**
     * 查看库存历史某段时间所有物料在库房的库存记录
     */
    public function stockResumeList(){
        if(IS_POST){
            $postData = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $postData['draw'];
            $sqlCondition = $this->getSqlCondition($postData);
            $startDate = $postData['startDate'];
            $endDate = $postData['endDate'];

            $map=[];
            if(empty($startDate) || $startDate == 'NaN' || $endDate == "NaN" || empty($endDate)){
                $timeData = D("stock_resume")->field("create_time")->order("id desc")->limit("1")->find();
                $map['create_time'] = ['eq', $timeData['create_time']];
            }else {
                if($startDate >= $endDate){
                    $output = array(
                        "draw" => intval($draw),
                        "recordsTotal" => 0,
                        "recordsFiltered" => 0,
                        "data" => array()
                    );
                    $this->ajaxReturn($output);
                }else {
                    $map['create_time'] = [['egt', $startDate], ['elt', $endDate]];
                }
            }
            $count = D("stock_resume")->where($map)->count();

            if(strlen($sqlCondition['search']) != 0){
                $map['product_no'] = ['like', "%" . $sqlCondition['search'] . "%"];
                $map['product_name'] = ['like', "%" . $sqlCondition['search'] . "%"];
            }

            $data = D("stock_resume")->where($map)->limit($sqlCondition['start'],$sqlCondition['length'])->order($sqlCondition['order'])->select();
            $recordsFiltered = D("stock_resume")->where($map)->count();

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $data);
            $this->ajaxReturn($this->output);
        }else {
            $this->display();
        }
    }
}