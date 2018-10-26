<?php

/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/9/13
 * Time: 13:42
 */
namespace Dwin\Controller;

use Dwin\Model\IndustrialSeralScreenModel;
use Dwin\Model\OrderChangeRecordModel;
use Dwin\Model\OrderCollectionModel;
use Dwin\Model\OrderformModel;
use Dwin\Model\OrderproductModel;
use Dwin\Model\ProchangerecordModel;
use Dwin\Model\ProductionPlanModel;
use Dwin\Model\StockAuditOutModel;
use Org\Net\Http;
use Think\Controller;
class FinanceController extends CommonController
{
    /*销售部门具有订单权限的部门分组*/
    const DEPT_SALE     = "3,11,24,25,26,27,28,29,30,31,32";
    /*客服部门具有订单权限的部门分组*/
    const DEPT_MARKET   = "44,45,46,47,48";
    /*市场部门具有订单权限的部门分组*/
    const DEPT_SERVICE  = "4,18,43,49,50,51";
    /*公司部门具有订单权限的部门分组*/
    const DEPT_CUSTOMER = "3,4,11,18,24,25,26,27,28,29,30,31,32,43,44,45,46,47,48,49,50,51,54";
    /*财务部分具有订单审核权限的职位*/
    protected $orderCheckRole;
    /*订单权限下的员工*/
    protected $orderIds = "";
    /* 财务审核权限*/
    protected $financeRole;
    public function _initialize()
    {
        parent::_initialize();
        $this->financeRole = array(4,5,6,7);
        $this->orderCheckRole = "1,3,16";

    }
    /**
     * 37 财务订单查看 showOrderList showInvoiceDetail showUnqualified
     */

    /**
     * @name showOrderList
     * @abstract 加载订单（所有订单）
     * 根据orderType的post值的不同显示不同状态的订单
     * @return array $this->output 返回json数组给dataTable 渲染表格
     * @todo 订单优化多条件筛选
    */
    public function showOrderList()
    {

        $roleInfo = (int)session('rId');
        $financeArr = array(20);// 权限表ids
        if (IS_AJAX) {
            $this->posts = I('post.');
            switch ($this->posts['orderType']) {
                case 'order_1' :
                    $k = "3";
                    break;
                case 'order_2' :
                    $k = "4";
                    break;
                case 'order_3' :
                    $k = '2';
                    break;
                default :
                    $k = "3";
                    break;
            }
            //获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition =$this->getSqlCondition($this->posts);

            //搜索
            $indexStr = $this->getSearchIndex($this->sqlCondition['search'], SPL_MATCH_ALL, "order", true);

            $map['check_status'] = array('IN', (string)$k); //审核状态
            // 权限判断
            $financeRole = array_intersect($this->postRoles, $financeArr);
            $countNum = count($financeRole);
            $maxLevel = min($this->uLevels);
            $map['picid'] = (!$countNum || $maxLevel === 1) ? array('NEQ', "") : array('EQ', session('staffId'));
            /** 后台传输局到前台
            @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
            $count = M('orderform')->where($map)->count();
            if ($indexStr !== null) {
                $map['crm_orderform.id'] = array('in', $indexStr);
            }
            $orderModel = new OrderformModel();
            $recordsFiltered = M('orderform')->where($map)->count();

            $info = $orderModel->getOrderIndexData($map, $this->sqlCondition);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $info);

            $this->ajaxReturn($this->output);
        } else {
            $this->display();
        }
    }



    /**
     * showOrderList页面获取订单详情方法
     */
    public function getOrderInfo()
    {
        $params = I('post.');
        $model = new \Dwin\Model\OrderformModel();
        $data = $model -> getOrderPendingDataById($params['id'], $params['cpoId'], $params['returnDataSet']);
        $planStatus = ['', '待审核', '齐料确认中', '生产中','待产线确认'];
        foreach ($data['productionPlanData'] as $key => &$value) {
            $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            $value['delivery_time'] = date('Y-m-d', $value['delivery_time']);
            $value['production_status'] = $planStatus[$value['production_status']];
        }
        $stockOutStatus = ['', '未审核', '审核通过', '审核不通过'];
        foreach ($data['stockOutData'] as $key => &$value) {
            $value['audit_status'] = $stockOutStatus[$value['audit_status']];
        }
        $this->ajaxReturn($data);
    }
    /**
     * @name showInvoiceDetail
     * @abstract 加载订单的所有数据，形成一个订单单据
     * 接口数据：orderId 订单的主键Id
     * 方式：get
     * 数据：订单的所有数据
     * 直接assign 到模板中
     * @todo 订单优化多条件筛选
     */
    public function showInvoiceDetail()
    {
        $orderId = (int)I('get.orderId');
        $map['crm_orderform.id'] = array('EQ', $orderId);
        $this->field = "crm_orderform.*, b.repertory_name ware_house, c.settle_name";
        $orderContents = D('orderform')->getOrderOneData($map, $this->field,'crm_orderform.id');

        $map_2['order_id'] = array('EQ', $orderId);
        $productData = M('orderproduct')
            ->field('crm_orderproduct.*, typ.prod_type_name prodtype')
            ->where($map_2)
            ->join('LEFT JOIN crm_product_type typ ON typ.type_id = crm_orderproduct.product_type')
            ->select();
        $this->assign(array(
            'data' => $orderContents,
            'prod' => $productData
        ));
        $this->display();
    }

    /**
     * @name showUnqualified
     * @abstract 订单不合格反馈信息
     * 接口数据：orderId 订单的主键Id
     * 方式：get
     * 数据：订单不合格信息
     * 传值方式：ajaxReturn
     */
    public function showUnqualified()
    {
        $orderId = I('get.id');
        $orderFilter['crm_orderform.id'] = array('EQ', $orderId);
        $this->field = "dept_feedback deptFeedback,finance_feedback financeFeedback";
        $unqualifiedContent = D('orderform')->getOrderOneData($orderFilter, $this->field, "crm_orderform.id");
        $this->ajaxReturn($unqualifiedContent);
    }

    /**
     * 40 项目审核 showOrderAudit checkOrder addUnqualified
     */


    /**
     * @name showOrderAudit
     * @abstract 订单待审核页面
     * 接口数据：datatable post数据
     * 方式：post
     * 数据：订单基本信息
     * 传值方式：ajaxReturn
     * @todo 订单的筛选功能优化：多列筛选
     */
    public function showOrderAudit()
    {
        if (IS_POST) {
            $orderFormModel = new OrderformModel();
            $this->posts = I('post.');
            //获取Datatables发送的参数 必要
            // 排序 分页 搜索条h件
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            // 获取权限下需要审核的订单
            /*$this->whereCondition = array(
                'check_status'     => array('EQ', '3'),
                'finance_check_id' => array('EQ', session('staffId'))
            );   原where条件，审核人变更为出纳职位*/
            $this->whereCondition = array(
                'check_status'     => array('EQ', '3')
            );

            $count = $orderFormModel->countOrderNumber($this->whereCondition, 'id');
            $indexStr = $this->getSearchIndex($this->sqlCondition['search'], SPL_MATCH_ALL, 'order', true);
            if ($indexStr !== null) {
                $this->whereCondition['crm_orderform.id'] = array('IN', $indexStr);
            }
            $recordsFiltered = $orderFormModel->countOrderNumber($this->whereCondition, 'id');
            $this->field = 'crm_orderform.id,crm_orderform.cpo_id,from_unixtime(otime) time,pic_name staname,d.order_type_name,
            cus_name cusname,e.performance_type_name,order_id k3_id, oprice,f.logistics_type_name,g.freight_payment_name,
            group_concat(b.repertory_name) repertory_name,c.settle_name,invoice_name,i.invoice_situation_name,j.check_type_name';

            $orderContents = $orderFormModel->getOrderformData($this->whereCondition, $this->field, $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'],'id');
            $this->output  = $this->getDataTableOut($this->posts['draw'], $count, $recordsFiltered, $orderContents);
            $this->ajaxReturn($this->output);
        } else {
            $this->display();
        }
    }
    /**
     * @name resetOrderStatus
     * @abstract 订单表的结算状态的修改
     * @param string $orderformSettlementStatus 要修改的订单的状态
     * @param array $orderformFilter 订单where条件
     * $return boolean 订单修改状态结果
     */
    protected function resetOrderStatus($orderformSettlementStatus, $orderformFilter)
    {
        $orderSettlementStatusData['settlement_status'] = $orderformSettlementStatus;
        return M('orderform')->where($orderformFilter)->setField($orderSettlementStatusData);

    }
    /**
     * @name settlementTrans
     * @abstract 审核订单的事务 1 添加订单结算状态 2 订单业绩表中添加数据
     * @param string $orderformSettlementStatus 要修改的订单结算状态
     * @param array $orderformFilter 订单筛选条件
     * @param array $addData 业绩表中要添加的结算信息
     * @param array $orderInfo 订单的基本信息
     * @return $msg 该事务的执行结果。
     */
    protected function settlementTrans($orderformSettlementStatus, $orderformFilter, $addData, $orderInfo)
    {
        $orderSettlementStatusData['settlement_status'] = $orderformSettlementStatus;
        M()->startTrans();
        $orderSettlementRst_1 = M()->table('crm_orderform')->where($orderformFilter)->setField($orderSettlementStatusData);
        if ($orderSettlementRst_1 !== false) {
            //添加产品结算数据

            $orderIdFilter['order_id'] = array('EQ', $addData['cus_order_id']);
            $productData = M('orderproduct')
                ->field('order_id cus_order_id, product_name, product_id, product_num, product_price single_price,product_total_price settle_price,product_type')
                ->where($orderIdFilter)
                ->select();
            foreach ($productData as &$val) {
                $val['settle_type'] = $addData['settle_type'];
                $val['settle_time'] = $addData['settle_time'];
                $val['settle_id']   = $addData['settle_id'];
                $val['total_num']   = $val['product_num'];
            }
            $orderSettlementRst_2 = M()->table('crm_order_collection')->addAll($productData);
            if ($orderSettlementRst_2 !== false) {
                M()->commit();
                $msg['status'] = 200;
                $msg['msg'] = "该订单为" . $orderInfo['orderType'] . "订单,审核通过,系统已自动记录结算信息：结算金额：" . $orderInfo['oprice'] . ",将计入当日业绩统计,统计根据订单类型计算";
            } else {
                M()->rollback();
                $msg['status'] = 500;
                $msg['msg'] = "审核通过,订单结算信息记录失败,订单系统内部编号:" . $orderInfo['id'] .",请联系管理员";
            }
        } else {
            M()->rollback();
            $msg['status'] = 500;
            $msg['msg'] = "审核通过,订单结算信息记录失败,订单系统内部编号:" . $orderInfo['id'] .",请联系管理员";
        }
        return $msg;
    }

    /**
     * @name checkOrder
     * @abstract 订单待审核页面
     * 接口数据：id 审核的订单主键id  flag 是否同意
     * 方式：post
     * 数据：进行订单表数据及客户表数据的修改，返回状态码和数字
     * 传值方式：ajaxReturn
     * @todo 订单审核对客户表、订单表进行了修改、需要记录对应时间到后台。
     */
    public function checkOrder()
    {
        $orderFormModel = new OrderformModel();
        $orderRecordModel = new OrderChangeRecordModel();
        $this->posts = I('post.');
        $orderId  = (int)$this->posts['id'];
        $this->posts['settlementTime'] = empty($this->posts['settlementTime']) ? time() : strtotime($this->posts['settlementTime']);
        $map['id'] = array('EQ', $this->staffId);
        $roleId = M('staff')->where($map)->field('roleid role')->find();
        $filter['crm_orderform.id'] = array('EQ', $orderId);
        // role 4 5 6 7 订单审核人
        $this->field = "crm_orderform.id, finance_check_id, dept_check_id, order_type, oprice, cus_id,cus_name,check_status";
        $rst = $orderFormModel->getOrderOneData($filter, $this->field, 'crm_orderform.id');
        $financeCheckArray = explode(",", $this->getRoleStaffIds($this->orderCheckRole));
        if ($rst['check_status'] != 3) {
            $msg['status'] = 400;
            $msg['msg'] = "已审核订单不能重复审核";
            $this->ajaxReturn($msg);
        }
        if(in_array($roleId['role'],$this->financeRole) && (in_array($this->staffId, $financeCheckArray))) {
            $recordContent = ($this->posts['flag'] == 1)
                ? "订单审核通过操作，审核订单号：" . $rst['id'] . "通过时间" . date('Y-m-d H:i:s') . ",操作人：" . session('nickname')
                : "订单审核驳回操作，审核订单号：" . $rst['id'] . "驳回时间" . date('Y-m-d H:i:s') . ",操作人：" . session('nickname');
            $orderChange  = $orderRecordModel->getRecordAddData($rst['id'], $recordContent);
            if ($this->posts['flag'] == 1) {
                $cusChange = array(
                    'cusid'      => $rst['cus_id'],
                    'changetime' => time(),
                    'change_id'  => $this->staffId,
                    'oldname'    => $rst['cus_name'],
                    'change_reason' => "客户订单审核通过更新客户业绩数据，添加了订单金额为：" . $rst['oprice'] . "订单（" . $rst['id'] . ").订单审核人：" . session('nickname')
                );
                // 财务审核通过,订单金额统计到客户业绩中
                $setData['check_status'] = 4;
                $setData['settlement_time'] = (int)$this->posts['settlementTime'];
                $setData['settlement_tips'] = $this->posts['settlementTips'];
                $setData['finance_check_id'] = $this->staffId;
                /* 事务 1 修改订单表字段，2 修改客户表字段 */

                M()->startTrans();
                $rst_1 = M()->table('crm_orderform')->where($filter)->setField($setData);
                if ($rst_1 !== false) {
                    $cusId = $rst['cus_id'];
                    // $cusId 需要更新统计表中的客户id, $condition 获取最新的订单时间和金额总数的条件
                    $statisticsCondition['cid'] = array('EQ', $cusId);
                    $cusMaxOrderTime = M('customer')->where($statisticsCondition)->field('max_order_time')->find();//获取客户当前的最大订单保护时间
                    $statistics = $orderFormModel->getCusOrderPerformance($cusId);// 获取近期该客户的业绩情况
                    $maxOrderTime = $statistics['max_order_time'];// 最大的业绩记录时间
                    if ((int)$cusMaxOrderTime['max_order_time'] > (int)$maxOrderTime) {
                        // 防止提交审核的订单时间保护时间比现在的小
                        $statistics['max_order_time'] = (int)$cusMaxOrderTime['max_order_time'];
                    } else {
                        $statistics['max_order_time'] = (int)$statistics['max_order_time'];
                    }
                    $cusType = M('customer')->where(array('cid' => $statistics['cid']))->field('cid, cus_pid, cname')->find();
                    /* 判断客户是否为子公司，如果为子公司，更新总公司的订单保护期*/
                    if (empty($cusType['cus_pid'])) {
                        $rst_2 = M()->table('crm_customer')->where($statisticsCondition)->setField($statistics);

                        if ($rst_2 !== false) {
                            // 提交后台记录数据
                            $addRst1 = M()->table('crm_order_change_record')->add($orderChange);
                            $addRst2 = M()->table('crm_cuschangerecord')->add($cusChange);
                            if ($addRst1 !== false && $addRst2 !== false) {
                                M()->commit();
                            } else {
                                M()->rollback();
                                $msg['status'] = 400;
                                $msg['msg'] = "审核失败，请联系管理员";
                                $this->ajaxReturn($msg);
                            }
                            // 根据订单类型 订单类型 1正常销货 2预收 3应收借物 4免费样品 5借物退库 6借物销货 7退货 8退款
                            $addData = array(
                                'cus_order_id'  => $rst['id'],
                                'settle_time'   => (int)$this->posts['settlementTime'],// 传来的值
                                'settle_price'  => $rst['oprice'],
                                'settle_id'     => session('staffId')
                            );
                            switch ((int)$rst['order_type']) {
                                case 1 :
                                    // 正常销货： 订单号 结算类型  结算日期 金额：订单金额
                                    // 更新订单状态, 财务结算完毕：1待确认信息 2结算中 3结算完毕 4 预收款未使用 5 预收款已使用
                                    $settlementStatus = "3";
                                    $addData['settle_type'] = "1";
                                    $rst['orderType'] = "正常销货";
                                    $msg = $this->settlementTrans($settlementStatus, $filter, $addData, $rst);
                                    break;
                                case 2 :
                                    // 预收款
                                    // 计入客户预收款类型 完结  后续有预收款冲订单结算金额,settlement status改变
                                    $settlementStatus = "3";
                                    $addData['settle_type'] = "2";
                                    $rst['orderType'] = "预收货款";
                                    $msg = $this->settlementTrans($settlementStatus, $filter, $addData, $rst);
                                    break;
                                case 3 :
                                    // 应收情况
                                    // 返回状态,提示填写应收金额。执行对应逻辑
                                    $settlementStatus = "2";
                                    $rst = $this->resetOrderStatus($settlementStatus,$filter);
                                    if ($rst !== false) {
                                        $msg['status'] = 204;
                                        $msg['msg'] = "应收账单,需要您确定是否有到款并填写对应信息";
                                        // 返回订单对应详情
                                        $productFilter['order_id'] = array('EQ', $addData['cus_order_id']);
                                        $productData = M('orderproduct')->where($productFilter)
                                            ->field('product_id,product_name,product_num,product_price,product_total_price,product_type')
                                            ->select();
                                        $msg['data'] = $productData;
                                    } else {
                                        $msg['status'] = 406;
                                        $msg['msg'] = "该应收单号的订单状态更新失败：系统订单号：" . $rst['id'];
                                    }
                                    /*$settlementStatus = "2";
                                    $addData['settle_type'] = "2";
                                    $rst['orderType'] = "应收账单";
                                    $msg = $this->settlementTrans($settlementStatus, $filter, $addData, $rst);*/
                                    break;
                                case 6 :
                                    // 借物发货
                                    $settlementStatus = "2";
                                    $rst = $this->resetOrderStatus($settlementStatus,$filter);
                                    if ($rst !== false) {
                                        $msg['status'] = 204;
                                        $msg['msg'] = "应收账单,需要您确定是否有到款并填写对应信息";
                                        // 返回订单对应详情
                                        $productFilter['order_id'] = array('EQ', $addData['cus_order_id']);
                                        $productData = M('orderproduct')->where($productFilter)
                                            ->field('product_id,product_name,product_num,product_price,product_total_price,product_type')
                                            ->select();
                                        $msg['data'] = $productData;
                                    } else {
                                        $msg['status'] = 406;
                                        $msg['msg'] = "该应收单号的订单状态更新失败：系统订单号：" . $rst['id'];
                                    }

                                    break;
                                case 7 :
                                    // 退货 扣除业绩
                                    // 更新订单状态
                                    $settlementStatus = "3";
                                    $addData['settle_type'] = "3";
                                    $rst['orderType'] = "退货";
                                    $msg = $this->settlementTrans($settlementStatus, $filter, $addData, $rst);
                                    break;
                                default :
                                    // 默认情况 完结 业绩统计表不添加数据
                                    $orderSettlementStatusData['settlement_status'] = "3";
                                    $orderSettlementRst_1 = M('orderform')->where($filter)->setField($orderSettlementStatusData);
                                    if ($orderSettlementRst_1 !== false) {
                                        $msg = array(
                                            'status' => 202,
                                            'msg'    => "其他订单,不计入业绩统计中"
                                        );
                                    } else {
                                        $msg = array(
                                            'status' => 500,
                                            'msg'    => "订单完结状态修改失败,联系管理员"
                                        );
                                    }
                                    break;
                            }
                        } else {
                            M()->rollback();
                            $msg['status'] = 401;
                            $msg['msg'] = "审核失败,如有误,请联系管理员";
                        }
                    } else {
                        $rst_2 = M()->table('crm_customer')->where($statisticsCondition)->setField($statistics);
                        $statisticsCondition_2['cid'] = array('eq', $cusType['cus_pid']);
                        $statistics_2 = array(
                            'cid'   => $cusType['cus_pid'],
                            'max_order_time' => (int)$maxOrderTime
                        );
                        $rst_3 = M()->table('crm_customer')->where($statisticsCondition_2)->setField($statistics_2);
                        if (($rst_2 !== false) && ($rst_3 !== false)) {
                            // 提交后台记录数据
                            $addRst1 = M()->table('crm_order_change_record')->add($orderChange);
                            $addRst2 = M()->table('crm_cuschangerecord')->add($cusChange);
                            if ($addRst1 !== false && $addRst2 !== false) {
                                M()->commit();
                            } else {
                                M()->rollback();
                                $msg['status'] = 400;
                                $msg['msg'] = "审核失败，请联系管理员";
                                $this->ajaxReturn($msg);
                            }
                            // 根据订单类型 订单类型 1正常销货 2预收 3应收借物 4免费样品 5借物退库 6借物销货 7退货 8退款
                            $addData = array(
                                'cus_order_id'  => $rst['id'],
                                'settle_time'   => (int)$this->posts['settlementTime'],
                                'settle_price'  => $rst['oprice'],
                                'settle_id'     => session('staffId')
                            );
                            switch ((int)$rst['order_type']) {
                                case 1 :
                                    // 正常销货： 订单号 结算类型  结算日期 金额：订单金额
                                    // 更新订单状态,财务结算完毕：1待确认信息 2结算中 3结算完毕 4 预收款未使用 5 预收款已使用
                                    $settlementStatus = "3";
                                    $addData['settle_type'] = "1";
                                    $rst['orderType'] = "正常销货";
                                    $msg = $this->settlementTrans($settlementStatus, $filter, $addData, $rst);
                                    break;
                                case 2 :
                                    // 预收款
                                    // 计入客户预收款类型 完结  后续有预收款冲订单结算金额,settlement status改变
                                    $settlementStatus = "3";
                                    $addData['settle_type'] = "2";
                                    $rst['orderType'] = "预收款";
                                    $msg = $this->settlementTrans($settlementStatus, $filter, $addData, $rst);
                                    break;
                                case 3 :
                                    // 应收情况
                                    // 返回状态,提示填写应收金额。执行对应逻辑
                                    $settlementStatus = "2";
                                    $rst = $this->resetOrderStatus($settlementStatus,$filter);
                                    if ($rst !== false) {
                                        $msg['status'] = 204;
                                        $msg['msg']  = "应收账单,需要您确定是否有到款并填写对应信息";
                                        $productFilter['order_id'] = array('EQ', $addData['cus_order_id']);
                                        $productData = M('orderproduct')->where($productFilter)
                                            ->field('product_id,product_name,product_num,product_price,product_total_price')
                                            ->select();
                                        $msg['data'] = $productData;
                                    } else {
                                        $msg['status'] = 406;
                                        $msg['msg'] = "该应收单号的订单状态更新失败：系统订单号：" . $rst['id'];
                                    }
                                    /*$settlementStatus = "2";
                                    $addData['settle_type'] = "2";
                                    $rst['orderType'] = "应收账单";
                                    $msg = $this->settlementTrans($settlementStatus, $filter, $addData, $rst);*/
                                    break;
                                case 4 :
                                    // 预收款
                                    // 计入客户预收款类型 完结  后续有预收款冲订单结算金额,settlement status改变
                                    $settlementStatus = "3";
                                    $addData['settle_type'] = "2";
                                    $rst['orderType'] = "免费样品";
                                    $msg = $this->settlementTrans($settlementStatus, $filter, $addData, $rst);
                                    break;

                                case 6 :
                                    // 借物发货
                                    $settlementStatus = "2";
                                    $rst = $this->resetOrderStatus($settlementStatus,$filter);
                                    if ($rst !== false) {
                                        $msg['status'] = 204;
                                        $msg['msg'] = "应收账单,需要您确定是否有到款并填写对应信息";
                                        // 返回订单对应详情
                                        $productFilter['order_id'] = array('EQ', $addData['cus_order_id']);
                                        $productData = M('orderproduct')->where($productFilter)
                                            ->field('product_id,product_name,product_num,product_price,product_total_price,product_type')
                                            ->select();
                                        $msg['data'] = $productData;
                                    } else {
                                        $msg['status'] = 406;
                                        $msg['msg'] = "该应收单号的订单状态更新失败：系统订单号：" . $rst['id'];
                                    }

                                    break;
                                case 7 :
                                    // 退货 扣除业绩
                                    // 更新订单状态
                                    $settlementStatus = "3";
                                    $addData['settle_type'] = "3";
                                    $rst['orderType'] = "退货";
                                    $msg = $this->settlementTrans($settlementStatus, $filter, $addData, $rst);
                                    break;
                                default :
                                    // 默认情况 完结 业绩统计表不添加数据
                                    $orderSettlementStatusData['settlement_status'] = "3";
                                    $orderSettlementRst_1 = M('orderform')->where($filter)->setField($orderSettlementStatusData);
                                    $msg = ($orderSettlementRst_1 !== false) ? array(
                                        'status' => 202,
                                        'msg'    => "其他订单,不计入业绩统计中"
                                    ) : array(
                                        'status' => 500,
                                        'msg'    => "订单完结状态修改失败,联系管理员"
                                    );
                                    break;
                            }
                        } else {
                            M()->rollback();
                            $msg['status'] = 401;
                            $msg['msg'] = "审核失败,如有误,请联系管理员";
                        }
                    }
                } else {
                    $msg['status'] = 402;//无权审核
                    $msg['msg'] = "审核失败,如有误,请联系管理员";
                }
            } else {
                M()->startTrans();
                $orderRecordRst = M()->table('crm_order_change_record')->add($orderChange);
                if ($orderRecordRst === false) {
                    M()->rollback();
                    $this->returnAjaxMsg('订单驳回失败', 400);
                }
                $setData['check_status'] = 2;
                $setData['finance_check_id'] = $this->staffId;
                $setData['finance_feedback'] = $this->posts['content'] . "(驳回时间：" . date("Y-m-d H:i:s") . ")";
                $orderChangeRst = M()->table('crm_orderform')->where($filter)->setField($setData);
                if ($orderChangeRst === false) {
                    M()->rollback();
                    $this->returnAjaxMsg('订单驳回失败', 400);
                }
                M()->commit();
                $msg['status'] = 201;
                $msg['msg']    = "订单驳回成功";
            }
        } else {
            $msg['status'] = 502;//无权审核
            $msg['msg'] = "无审核的权限,如有误,请联系管理员解决";
        }
        $this->ajaxReturn($msg);
    }


    /**
     * @name addUnqualified
     * @abstract 订单驳回页面
     * 接口数据：orderId
     * 方式：get
     * 进行订单驳回操作的页面
     */
    public function addUnqualified()
    {
        $orderId = I('get.orderId');
        $this->assign('order_id', $orderId);
        $this->display();
    }
    /**
     * 财务审核通过
     * 1判断订单类型：正常销货 应收 退货 预收款
     * 1）预收款情况：
     *  客户账户 表： i客户id  订单号  日期  金额 用途   多笔预收款累加
     *  订单完结
     * 2）退货情况
     * 订单表 完结（不走退货流程,认为已经到货才确认）
     * 退的钱可以冲应收（另外的应收流程）
     * @todo
     * 退钱的时候,涉及到之前的订单的业绩,如何处理
     * 3）正常销货情况
     * 订单表完结
     * 统计表 订单号 结算方式 日期  金额记录    订单表记录财务状态完结
     * 4）应收情况
     * 订单表 状态变为应收中
     * 统计表 订单号结算方式 日期 金额记录 判定金额总和与订单金额是否吻合
     *
    */


    /**
     * 应收结算记录添加
    */
    public function addFinanceRecord()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $settleTime = (!empty($this->posts['settleTime'])) ? (int)$this->posts['settleTime'] : time(); // 结算日期
            $orderCountFilter['crm_orderform.id'] = array('EQ', $this->posts['orderId']); // 订单系统编号
            $this->field = "crm_orderform.id, oprice, sum(b.settle_price) done_price, settlement_status";

            $orderInfo = D('orderform')->getOrderCollectionDataByFind($this->field, $orderCountFilter); // 订单金额 已经结算金额
            // 结算金额与订单金额对比
            if ($orderInfo['oprice'] > $orderInfo['done_price']) {
                $addData = array(
                    'cus_order_id' => $this->posts['orderId'],
                    'settle_time'  => $settleTime,
                    'settle_id'    => session('staffId')
                ); // 添加的数据
                $total = count(I('post.addData'));
                $addPost = I('post.addData');
                $perLength = I('post.inputNum');
                $n = $total / $perLength;
                $priceT = 0;//提交的订单结算总金额。
                for ($k = 0; $k < $n; $k++) {
                    for ($q = 0; $q <  $perLength; $q ++ ) {
                        $settleData[$k][$q] = $addPost[$k * $perLength + $q];
                    }
                    $kd[$k] = array_column($settleData[$k], 'value', 'name');
                    $kd[$k]['cus_order_id'] = $addData['cus_order_id'];
                    $kd[$k]['settle_time']  = empty($kd[$k]['settle_time']) ? time() : strtotime($kd[$k]['settle_time']);
                    $kd[$k]['settle_id']    = $addData['settle_id'];
                    $kd[$k]['single_price'] = $kd[$k]['product_price'];
                    $kd[$k]['total_num']    = $kd[$k]['product_num'];
                    $kd[$k]['product_num']  = $kd[$k]['settle_num'];
                    if (empty($kd[$k]['settle_price']) && empty($kd[$k]['product_num'])) {
                        unset($kd[$k]);
                    }
                    $priceT += $kd[$k]['settle_price'];
                }
                if(count($kd) == 0) {
                    $msg['status'] = 400;
                    $msg['msg'] = "您输入的数据有误";
                    $this->ajaxReturn($msg);
                }
                $kd = array_values($kd);// key 重置 防止数据库批量添加数据失败


                $rst = $orderInfo['oprice'] - ($priceT + $orderInfo['done_price']); // 本次结算金额与剩余未结算对比
                if ($rst < 0) {
                    $msg['status'] = 302;
                    $msg['msg'] = "您录入的金额高于订单总金额,请核查以往记录,重新添加";
                } elseif ($rst == 0) {
                    // 判断订单类型 ：应收订单完结改为3
                    if ($orderInfo['settlement_status'] == '2') {
                        // 应收订单完结并且添加记录
                        $orderformSettlementStatus = "3";
                        $res_1 = $this->resetOrderStatus($orderformSettlementStatus, $orderCountFilter);
                        if ($res_1 !== false) {
                            $orderSettlementRst_2 = M('order_collection')->addAll($kd);
                            if ($orderSettlementRst_2 !== false) {
                                $msg['status'] = 200;
                                $msg['msg'] = "该订单为" . $orderInfo['orderType'] . "订单,添加还款记录成功,系统已记录对应信息：还款金额：" . $priceT . ",将计入当日业绩统计,统计根据订单类型计算,剩余未还：" . $rst;
                            } else {
                                $msg['status'] = 300;
                                $msg['msg'] = "审核通过,订单结算信息记录失败,订单系统内部编号:" . $orderInfo['id'] .",请联系管理员";
                            }
                        } else {
                            $msg['status'] = 303;
                            $msg['msg'] = "应收订单结算流程结束失败,订单系统内部编号:" . $orderInfo['id'] .",请联系管理员";
                        }
                    } else {
                        $msg['status'] = 400;
                        $msg['msg'] = "订单非应收类型,不能进行添加结算记录操作,如果订单结算信息错误,请前往修改操作";
                    }
                } else {
                    $orderSettlementRst_2 = M('order_collection')->addAll($kd);
                    if ($orderSettlementRst_2 !== false) {
                        $msg['status'] = 200;
                        $msg['msg'] = "该订单为" . $orderInfo['orderType'] . "订单,添加还款记录成功,系统已记录对应信息：还款金额：" . $priceT . ",将计入当日业绩统计,统计根据订单类型计算,剩余未还：" . $rst;
                    } else {
                        $msg['status'] = 300;
                        $msg['msg'] = "审核通过, 订单结算信息记录失败,订单系统内部编号:" . $orderInfo['id'] . ",请联系管理员";
                    }
                }
            } else {
                $msg['status'] = 300;
                $msg['msg'] = "该订单已经结算完毕,所有应收费用已收齐,不能重复录入应收还款";
            }
            $this->ajaxReturn($msg);
        } elseif (IS_GET) {
            // 订单单号 总金额 已结算 未结算
            // 订单已结算业绩记录
            // 未结款项
            $orderId = I('get.orderId');
            $filter['crm_orderform.id'] = array('EQ', $orderId);// 订单表信息
            $this->field = "crm_orderform.id, crm_orderform.order_id, oprice, ifnull(sum(b.settle_price), 0) done_price, settlement_status";
            $orderInfo = D('orderform')->getOrderCollectionDataByFind($this->field, $filter);

            $productFilter['order_id'] = array('EQ', $orderId);
            $orderProductModel = new OrderproductModel();
            $this->field = "crm_orderproduct.product_id,crm_orderproduct.product_type,crm_orderproduct.product_name,crm_orderproduct.product_num,
                            crm_orderproduct.product_price,crm_orderproduct.product_total_price, ifnull(sum(c.settle_price),0) settled_price,
                            ifnull(sum(c.product_num),0) settled_num";
            $productData = $orderProductModel->getOrderProductSettleData($this->field, $productFilter);
            $sql = $orderProductModel->getLastSql();
            foreach($productData as &$val) {
                $val['un_price'] = round($val['product_total_price'] - $val['settled_price'], 2);
                $val['un_number'] = $val['product_num'] - $val['settled_num'];
            }
            $map['cus_order_id'] = array('EQ', $orderId);
            $this->field = "a.*, cus_order_id, h.collection_type settle_type, settle_type settle_type_id, i.name settle_id, settle_price,
             from_unixtime(settle_time) settle_time,b.cus_name, b.order_id, b.pic_name, b.oprice";
            $settlementData = D('order_collection')->getCollectionData($this->field, $map, 'a.id', 0, 1000);

            $typeData = M('order_collection_type')->field('id,collection_type')->select();
            $data = array(
                'settlementData' => $settlementData,
                'typeData'       => $typeData,
                'orderInfo'      => $orderInfo,
                'productData'    => $productData
            );
            $this->ajaxReturn($data);
        }
    }

    /**
     * checkedOrderManagement 已审核通过的财务管理页面
     * post传值 orderT 订单类型 orderP 订单业绩类型 orderS:订单结算状态
     * 返回datatable json 数组
     */

    public function checkedOrderManagement()
    {
        $orderCheckedStatus = "4";
        if (IS_POST) {
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $indexStr = $this->getSearchIndex($this->sqlCondition['search'],SPL_MATCH_ANY, "order", true);
            // 下拉菜单传值
            $orderSelFilter['orderT'] = $this->posts['orderT'];
            $orderSelFilter['orderP'] = $this->posts['orderP'];
            $orderSelFilter['orderS'] = $this->posts['orderS'];
            if ($this->posts['orderT']) {
                $map['order_type'] = array('EQ', $this->posts['orderT']);
            }
            if ($this->posts['orderP']) {
                $map['static_type'] = array('EQ', $this->posts['orderP']);
            }
            if ($this->posts['orderS']) {
                $map['settlement_status'] = array('EQ', $this->posts['orderS']);
            }
            // where条件
            $map['check_status'] = array('EQ', $orderCheckedStatus); //审核状态

            /** 后台传输局到前台
            @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
            $count = M('orderform')->where($map)->count();

            if ($indexStr !== null) {
                $map['crm_orderform.id'] = array('in', $indexStr);
            }
            $recordsFiltered = M('orderform')->where($map)->count();
            $this->field = "crm_orderform.id,order_id,oprice,ifnull(sum(k.settle_price),0) done_price,cus_name,d.order_type_name order_type,e.performance_type_name static_type,settlement_time,pic_name,ifnull(l.settlement_type_name,\"未处理\") settlement_stat, otime";
            $orderContents = D('orderform')->getOrderDataUseGroup($this->field, $map, 'crm_orderform.id', $this->sqlCondition['order'] , $this->sqlCondition['start'], $this->sqlCondition['length']);
            foreach ($orderContents as $key => &$val) {
                $val['DT_RowId']          = $val['id'];
                $val['DT_RowClass']       = 'gradeX';
                $val['otime']             = date("Y-m-d", $val['otime']);
                $val['settlement_time']   = date("Y-m-d", $val['settlement_time']);
                $val['oprice']            = $val['oprice'] . "元";
                $val['done_price']        = $val['done_price'] . "元";
            }
            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $orderContents);
            $this->ajaxReturn($this->output);
        } else {
            $settlementStatus = M('order_settlement_status')->select();
            $orderType = M('order_type')->select();
            $orderPerformance = M('order_performance_type')->select();
            $this->assign(array(
                'orderS' => $settlementStatus,
                'orderT' => $orderType,
                'orderP' => $orderPerformance
            ));
            $this->display();
        }
    }

    /**
     * rejectOrder 订单驳回方法 驳回订单 订单状态为2 订单业绩表删除 客户业绩更新
     * 已审核的订单驳回方法，要删除结算数据
     * @todo 该方法在与生产、物流对接后，考虑弃用或者优化。优化主要考虑：订单的下一步流程回退。（已生产、发货的，不能回退）
     *
     */
    public function rejectOrder()
    {
        if (IS_POST) {
            $orderId = I('post.orderId');
            $orderformFilter['id'] = array('EQ', $orderId);// 订单表查询条件
            $orderInfo = M('orderform')->where($orderformFilter)->field('id, cus_id, check_status, settlement_time,cpo_id')->find();
            // 设置orderform表中该订单为不合格
            $setData['check_status'] = "2";
            $setData['settlement_time'] = time();
            $orderformDataSet = array(
                'check_status'    => '2',
                'settlement_time' => time(),
                'finance_feedback' => I('post.textContent')
            );
            $stockOutModel = new StockAuditOutModel();
            $productionPlanModel = new ProductionPlanModel();
            $productionFilter['order_id'] = ['EQ', $orderInfo['id']];
            $productionFilter['is_del'] = ['EQ', 0];
            $productionData = $productionPlanModel->where($productionFilter)->field('id')->find();
            if ($productionData) {
                $msg['status'] = 403;
                $msg['msg'] = "订单驳回失败，该单据有生产单关联，删除生产单后才能驳回已审核订单";
                $this->ajaxReturn($msg);
            }
            $stock['action_order_number'] = ['eq', $orderInfo['cpo_id']];
            $stock['is_del'] = ['EQ', 0];
            $stockData = $stockOutModel->where($stock)->field('product_id')->find();
            if ($stockData) {
                $msg['status'] = 403;
                $msg['msg'] = "订单驳回失败，该单据已经有发货记录,不能驳回已审核订单";
                $this->ajaxReturn($msg);
            }
            M()->startTrans();
            $rst = M()->table('crm_orderform')->where($orderformFilter)->setField($orderformDataSet);
            // order_collection 删除该订单数据
            if ($rst !== false) {
                $orderCollectionFilter['cus_order_id'] = array('EQ', $orderId);
                $collectionInfo = M('order_collection')
                    ->field('crm_order_collection.id, settle_type,settle_price,settle_time,settle_id, a.name settle_name')
                    ->join('LEFT JOIN crm_staff a ON a.id = settle_id')
                    ->where($orderCollectionFilter)
                    ->select();
                $rst_2 = M()->table('crm_order_collection')->where($orderCollectionFilter)->delete();
                if ($rst_2 !== false) {
                    // 更新客户状态
                    $cusFilter['cid'] = array('EQ', $orderInfo['cus_id']);
			        $orderFormModel = new OrderformModel();
                    $statistics = $orderFormModel->getCusOrderPerformance($orderInfo['cus_id']);// 获取近期该客户的业绩情
                    unset($statistics['max_order_time']);
                    $rst_3 = M()->table('crm_customer')->where($cusFilter)->setField($statistics);
                    if ($rst_3 !== false) {
                        M()->commit();
                        $changeContent = "订单为" . $orderId . "的订单状态由审核通过改为未通过,审核时间由" . date('Y-m-d H:i:s',$orderInfo['settlement_time']) ."更改为". date('Y-m-d H:i:s',$orderformDataSet['settlement_time']);
                        $changeContent .= ",订单业绩统计表中删除了订单号为本单号的所有数据:";
                        for($i = 0; $i < count($collectionInfo); $i++) {
                            switch ($collectionInfo[$i]['settle_type']) {
                                case 1 :
                                    $collectionInfo[$i]['settle_method'] = "收款结算";
                                    break;
                                case 2 :
                                    $collectionInfo[$i]['settle_method'] = "预收款";
                                    break;
                                case 3 :
                                    $collectionInfo[$i]['settle_method'] = "退货款";
                                    break;
                                case 6 :
                                    $collectionInfo[$i]['settle_method'] = "退货冲销";
                                    break;
                                case 7 :
                                    $collectionInfo[$i]['settle_method'] = "收款冲销";
                                    break;
                            }
                            $changeContent .= $collectionInfo[$i]['id'] .
                                "结算时间" . date('Y-m-d H:i:s',$collectionInfo[$i]['settle_time']) .
                                ",产品型号：" . $collectionInfo[$i]['product_name'] .
                                ",产品价格：" . $collectionInfo[$i]['single_price'] .
                                ",总数量："   . $collectionInfo[$i]['total_num'] .
                                ",折算数量：" . $collectionInfo[$i]['product_num'] .
                                ",结算金额：" . $collectionInfo[$i]['settle_price'] .
                                ",结算类型：" . $collectionInfo[$i]['settle_method'] .
                                ",结算日期" . date('Y-m-d H:i:s', $collectionInfo[$i]['settle_time']) .
                                ",结算人" .$collectionInfo[$i]['settle_name'];
                        }
                        $orderChangeRecord = array(
                            'order_id' => $orderId,
                            'finance_id' => session('staffId'),
                            'change_time' => time(),
                            'change_content' => $changeContent
                        );
                        M('order_change_record')->add($orderChangeRecord);
                        $msg['status'] = 200;
                        $msg['msg'] = "订单驳回成功";
                    } else {
                        M()->rollback();
                        $msg['status'] = 402;
                        $msg['msg'] = "订单对应客户的业绩情况更新失败";
                    }
                } else {
                    M()->rollback();
                    $msg['status'] = 400;
                    $msg['msg'] = "订单驳回失败,删除统计表数据出错,事务回滚";
                }
            } else {
                M()->rollback();
                $msg['status'] = 401;
                $msg['msg'] = "订单驳回失败,更新订单状态失败,事务回滚";
            }
            $this->ajaxReturn($msg);
        }
    }

    /**
     * @name editFinanceRecord
     * @abstract 编辑结算信息
     *
     */
    public function editFinanceRecord()
    {
        if (IS_POST) {
            // 提交了修改信息（校验修改内容是否符合情况）
            $this->posts = I('post.');// 结算信息
            $this->posts['settleT'] = strtotime($this->posts['settleT']);
            $collectionCondition['crm_order_collection.id'] = $this->posts['collectionId'];
            $checkData = D('order_collection')->getCollectionByFind($this->field, $collectionCondition, 'crm_order_collection.id');
            if ($checkData['settle_price'] < $this->posts['settleP']) {
                // 提交的结算总价高于原来的,不允许
                $msg['status'] = 400;
                $msg['msg']    = "您键入的结算金额已经超过了原结算金额,不能修改,如需增加当日该产品的结算金额,请点击结算按钮";
                $this->ajaxReturn($msg);
            }
            if ($checkData['product_num'] == $this->posts['settleN']  && $checkData['settle_price'] == $this->posts['settleP'] && $checkData['settle_time'] == $this->posts['settleT'] && $checkData['settle_type'] == $this->posts['settleE']) {
                $msg['status'] = 401;
                $msg['msg']    = "您好像未做修改";
                $this->ajaxReturn($msg);
            }
            $updData = array(
                'id'           => $this->posts['collectionId'],
                'settle_type'  => $this->posts['settleE'],
                'settle_price' => $this->posts['settleP'],
                'settle_time'  => $this->posts['settleT'],
                'product_num'  => $this->posts['settleN']
            );
            $updContent = "";// 修改内容
            if ($checkData['settle_price'] != $this->posts['settleP']) {
                // 结算金额进行了修改
                $updContent .= "<br>结算金额进行了修改：由" . $checkData['settle_price'] . "修改为" . $updData['settle_price'];
            }
            if ($checkData['settle_time'] != $this->posts['settleT']) {
                $updContent .= "<br>结算时间进行了修改：结算时间由：" . date("Y-m-d H:i:s", $checkData['settle_time']) . "改为" .date("Y-m-d H:i:s", $updData['settle_time']);
            }
            if ($checkData['settle_type'] != $this->posts['settleE']) {
                $updContent .= "<br>结算类型进行了修改：结算类型编号由" . $checkData['settle_type'] . "变为" . $updData['settle_type'];
            }
            if ($checkData['product_num'] != $this->posts['settleN']) {
                $updContent .= "<br>结算业绩折算产品数量进行了修改：由" . $checkData['product_num'] . "变为" . $updData['product_num'];
            }
            $addData = array(
                'collection_id' => $updData['id'],
                'update_id'     => session('staffId'),
                'update_content' => $updContent,
                'update_time'  => time()
            );
            M()->startTrans();
            $condition['id'] = array('EQ', $this->posts['collectionId']);
            $rst_1 = M()->table('crm_order_collection')->where($condition)->setField($updData);
            if ($rst_1 !== false) {
                $rst_2 = M()->table('crm_order_collection_record')->add($addData);
                if ($rst_2 !== false) {
                    M()->commit();
                    $map['crm_orderform.id'] = array('eq', $checkData['cus_order_id']);
                    $this->field = "oprice,ifnull(sum(h.settle_price),0) done_price";
                    $newData = D('orderform')->getOrderOneData($map, $this->field, 'crm_orderform.id');

                    if ($newData['oprice'] > $newData['done_price']) {
                        $orderChange['settlement_status'] = "2";
                        M('orderform')->where($map)->setField($orderChange);
                    }
                    $msg['status'] = 200;
                    $msg['msg']  ="修改成功";
                } else {
                    M()->rollback();
                    $msg['status'] = 402;
                    $msg['msg']  ="修改失败";
                }
            } else {
                M()->rollback();
                $msg['status'] = 401;
                $msg['msg']  ="失败,错误401";
            }
            $this->ajaxReturn($msg);
        } elseif (IS_GET) {
            // 根据get的订单号查询订单信息
            $orderId = I('get.orderId');
            $map['cus_order_id'] = array('EQ', $orderId);
            // 结算信息
            $this->field = "a.*, cus_order_id, h.collection_type settle_type, 
                        settle_type settle_type_id, i.name settle_id,
                        settle_price,from_unixtime(settle_time) settle_time,
                        b.cus_name,b.order_id,b.pic_name,b.oprice";
            $settlementData = D('order_collection')->getCollectionData($this->field, $map, 'a.id', 0, 50);
            /*$settlementData = M('order_collection')
                ->field('crm_order_collection.*, cus_order_id, a.collection_type settle_type,
                        settle_type settle_type_id, b.name settle_id,
                        settle_price,from_unixtime(settle_time) settle_time,
                        c.cus_name,c.order_id,c.pic_name,c.oprice')
                ->join('LEFT JOIN crm_order_collection_type a ON a.id = crm_order_collection.settle_type')
                ->join('LEFT JOIN crm_staff b ON b.id = crm_order_collection.settle_id')
                ->join('LEFT JOIN crm_orderform c ON c.id = crm_order_collection.cus_order_id')
                ->where($map)
                ->select();*/
            $typeData = M('order_collection_type')->field('id,collection_type')->select();
            $data = array(
                'settlementData' => $settlementData,
                'typeData'       => $typeData
            );
            $this->ajaxReturn($data);
        }

    }

    /**结算结果信息*/
    public function showPerformanceResult()
    {
        if(IS_POST) {
            // $rst = M('staff')->where($map)->field('roleid')->find();
            $collectionModel = new OrderCollectionModel();
            $this->posts = I('post.');
            if (isset($this->posts['deptIdchange'])) {
                $deptId = (int)$this->posts['deptIdchange'];
                // 点击不同部门后显示该部门下所有有销售业务的子部门（没有递归的原因是有些子部门没有销售业务）
                switch ($deptId) {
                    case 3 :
                        $deptIds = self::DEPT_SALE;
                        break;
                    case 4 :
                        $deptIds = self::DEPT_SERVICE;
                        break;
                    case 44 :
                        $deptIds = self::DEPT_MARKET;
                        break;
                    default :
                        $deptIds = self::DEPT_CUSTOMER;
                        break;
                }
                $staffIdsFilter['deptid'] = array('in', $deptIds);
                $staffIds = M('staff')->field('id,name')->where($staffIdsFilter)->select();
                $this->ajaxReturn($staffIds);
            }

            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];

            // 排序
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $indexStr = $this->getSearchIndex($this->sqlCondition['search'], SPL_MATCH_ANY, "order",true);
            if ($indexStr !== null) {
                $map['b.id'] = array('in', $indexStr);
            }

            // 下拉菜单传值
            $orderAudit = array(4,5,6,7,8);
            $roleId = (int)session('roleId');
            if (in_array($roleId, $orderAudit)) {
                $this->orderIds = $this->getStaffIds((string)session('staffId'), 'order_child_id', "");
            }
            $thismonth = date('m');
            $thisyear  = date('Y');
            $startDay  = $thisyear . '-' . $thismonth - 1 . '-1';
            $timeLimit1 = empty(I('post.timeLimit1')) ? strtotime($startDay) : strtotime(I('post.timeLimit1'));
            $timeLimit2 = empty(I('post.timeLimit2')) ? time() : strtotime(I('post.timeLimit2'));
            if ($timeLimit2 - $timeLimit1 <= 0) {
                $this->output = $this->getDataTableOut($draw, 0, 0, array());
                $this->ajaxReturn($this->output);
            }
            $map['settle_time'] = array(array('egt', $timeLimit1), array('elt', $timeLimit2));
            $deptLimit = I('post.deptLimit');
            $staffLimit = I('post.staffLimit');
            if (empty($staffLimit)) {
                switch ((int)$deptLimit) {
                    case 3 :
                        $deptIds = self::DEPT_SALE;
                        break;
                    case 4 :
                        $deptIds = self::DEPT_SERVICE;
                        break;
                    case 44 :
                        $deptIds = self::DEPT_MARKET;
                        break;
                    default :
                        $deptIds = self::DEPT_CUSTOMER;
                        break;
                }
                $staffIdsFilter['deptid'] = array('in', $deptIds);
            } else {
                $staffIdsFilter['id'] = array('eq', (int)$staffLimit);
            }
            $staffIds = M('staff')->field('id,name')->where($staffIdsFilter)->select();
            $map['b.picid'] = array('IN', getPrjIds($staffIds,'id'));
            $type = (int)$this->posts['dateT'];
            switch($type) {
                case 2 :
                    $this->sqlCondition['group'] = 'b.picid';
                    break;
                case 3 :
                    $this->sqlCondition['group'] = 'b.cus_id';
                    break;
                default:
                    break;
            }
            list($count, $countFilter,$data) = $collectionModel->getCollectionStatistics($type, $map, $this->sqlCondition);
            $this->output = $this->getDataTableOut($draw, $count, $countFilter, $data);
            $this->output['statistics'] = $collectionModel->getStatisticsAmount($map);
            $this->ajaxReturn($this->output);


        } else {
            $orderAudit = array(4, 5, 6, 7, 8);
            $roleId  = (int)session('roleId');
            $deptIds = "3,4,44";// 市场部 客服部 销售部iD
            $deptFilter['id'] = array('IN', $deptIds);
            $dept = M('dept')->field('id, name')->where($deptFilter)->select();

            $this->assign('dept', $dept);

            // $this->orderIds:权限下的人名
            if (!in_array($roleId, $orderAudit)) {
                $this->orderIds = $this->getStaffIds((string)session('staffId'), 'order_child_id', "");
                if ($this->orderIds) {
                    $staffIdsFilter['id'] = array('IN', $this->orderIds);
                    $staffIds = M('staff')->field('id,name')->where($staffIdsFilter)->select();
                } else {
                    $staffIds = array();
                }
            } else {
                // 所有订单负责人
                $deptIdsOrder = self::DEPT_CUSTOMER;
                $staffIdsFilter['deptid'] = array('in', $deptIdsOrder);
                $staffIds = M('staff')->field('id,name')->where($staffIdsFilter)->select();
                $ownStaffId['id'] = array('eq', $this->staffId);
                array_push($staffIds,array('id'=> $this->staffId, 'name' => session('nickname')));
            }
            $this->assign('staffIds', $staffIds);
            $this->display();
        }
    }

    /**
     * 导出excel 禁用
     * @todo 说明：数据sql中未进行更新，中间industrial_material_screen变成了stock+material两张表
     */
    private function exportExcel()
    {
        $thismonth = date('m');
        $thisyear  = date('Y');
        $startDay  = $thisyear . '-' . $thismonth - 1 . '-1';
        $timeLimit1 = empty(I('post.timeLimit1')) ? strtotime($startDay) : strtotime(I('post.timeLimit1'));
        $timeLimit2 = empty(I('post.timeLimit2')) ? time() : strtotime(I('post.timeLimit2'));
        if ($timeLimit2 - $timeLimit1 <= 0) {
            $this->ajaxReturn(false);
        }
        $map['settle_time'] = array(array('egt', $timeLimit1), array('elt', $timeLimit2));
        $deptLimit = I('post.deptLimit');
        $staffLimit = I('post.staffLimit');
        if (empty($staffLimit)) {
            switch ((int)$deptLimit) {
                case 3 :
                    $deptIds = self::DEPT_SALE;
                    break;
                case 4 :
                    $deptIds = self::DEPT_SERVICE;
                    break;
                case 44 :
                    $deptIds = self::DEPT_MARKET;
                    break;
                default :
                    $deptIds = self::DEPT_CUSTOMER;
                    break;
            }
            $staffIdsFilter['deptid'] = array('in', $deptIds);
        } else {
            $staffIdsFilter['id'] = array('eq', (int)$staffLimit);
        }
        $staffIds = M('staff')->field('id,name')->where($staffIdsFilter)->select();
        $map['b.picid'] = array('IN', getPrjIds($staffIds,'id'));

        $rst1 = M('order_collection')
            ->alias('a')
            ->field('a.id id, b.id sys_id, from_unixtime(a.settle_time) settle_time,b.cus_name, b.pic_name, e.name dept, b.order_id k_order_id, 
                    a.product_id, a.product_name, a.single_price, a.product_num, a.settle_price, g.settle_name settlement_name, 
                    h.collection_type settle_type, q.performance_type_name performance_type')
            ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
            ->join('LEFT JOIN crm_customer c ON b.cus_id = c.cid')
            ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
            ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
            ->join('LEFT JOIN crm_order_invoice f ON f.type_id = b.invoice_type')
            ->join('LEFT JOIN crm_settlementlist g ON g.settle_id = b.settlement_method')
            ->join('LEFT JOIN crm_order_collection_type h ON h.id = a.settle_type')
            ->join('LEFT JOIN crm_order_performance_type q ON q.type_id = b.static_type')
            ->where($map)
            ->order('a.settle_time')
            ->select();
        $rst1Title = array(
          '结算系统编号', '订单编号', '结算时间', '客户名','业务','部门','K3编号','产品系统编号','产品名','出售价格','数量','结算金额','订单结算类型','本次结算方式','业绩类型'
        );
        array_unshift($rst1, $rst1Title);
        $rst2 = M('order_collection')
            ->alias('a')
            ->field('b.pic_name,e.name dept, count(distinct b.order_id) order_num_all,
                     sum(case  
                                when a.product_id = 15003  then 0
                                when settle_type = 3  then (-1)*settle_price
                                else settle_price end) settle_all_price,
                     sum(case   when settle_type = 3 then settle_price
                                else 0 end) settle_back_price,
                     sum(case   when a.product_id = 15002 then settle_price
                                else 0 end) settle_research_price,
                     sum(case   when a.product_id = 15001 then settle_price
                                else 0 end) cartography_fee,
                     sum(case   when a.product_id = 15004 then settle_price
                                else 0 end) maintenance_fees,
                     sum(case   when a.product_id = 15003 then settle_price
                                else 0 end) settle_pre_price,
                     sum(case   when a.product_id = 15000 then settle_price
                                else 0 end) shipping_costs,
                     sum(case   when p.platform_id = 6 then 0
                                when a.product_id not in (15000,15001,15002,15003,15004) then settle_price
                                else 0 end) settle_normal_price,
                     sum(case   when p.platform_id = 6 then 0
                                when a.product_id not in (15000,15001,15002,15003,15004) and q.id = 3 then settle_price
                                else 0 end) value_price,
                     sum(case   when p.platform_id = 6 then 0
                                when a.product_id not in (15000,15001,15002,15003,15004) and q.id = 2 then settle_price
                                else 0 end) marketing_price,
                     sum(case   when p.platform_id = 6 then 0
                                when settle_type = 6 and p.platform_id != 6 then settle_price
                                else 0 end) back_price,
                     sum(case   when p.platform_id = 6 then 0
                                when settle_type = 8 and p.platform_id != 6 then settle_price
                                else 0 end) sale_price,
                     sum(case   when p.platform_id = 6 then 0
                                when settle_type = 9 and p.platform_id != 6 then settle_price
                                else 0 end) performance_price,
                     sum(if (p.platform_id not in (6), product_num, 0)) sale_num_all,
                     sum(if (p.platform_id = 4,product_num,0)) parts_num,
                     sum(if (p.platform_id not in(4,6),product_num,0)) product_nums')
            ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
            ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
            ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
            ->join('LEFT JOIN crm_industrial_seral_screen p ON p.product_id = a.product_id')
            ->join('LEFT JOIN crm_order_performance_type q ON q.type_id = b.static_type')
            ->where($map)
            ->group('b.picid')
            ->order('dept')
            ->select();
        $rst2Title = array(
            '业务', '部门', '结算订单数量', '结算总金额','退货','研发费',
            '制图费','维修费', '预收款','运费','货款总额','价值业绩','市场拓展',
            '退货还款','折价还款',"工资抵账",'总出货量', '配件数', '产品数'
        );

        array_unshift($rst2, $rst2Title);

        $rst3 = M('order_collection')
            ->alias('a')
            ->field('b.cus_name,b.pic_name,e.name dept, count(distinct b.order_id) order_num_all,
                     sum(case  
                                when a.product_id = 15003  then 0
                                when settle_type = 3  then (-1)*settle_price
                                else settle_price end) settle_all_price,
                     sum(case   when settle_type = 3 then settle_price
                                else 0 end) settle_back_price,
                     sum(case   when a.product_id = 15003 then settle_price
                                else 0 end) settle_pre_price,
                     sum(case   when a.product_id = 15002 then settle_price
                                else 0 end) settle_research_price,
                     sum(case   when a.product_id = 15001 then settle_price
                                else 0 end) cartography_fee,
                     sum(case   when a.product_id = 15004 then settle_price
                                else 0 end) maintenance_fees,
                     sum(case   when a.product_id = 15000 then settle_price
                                else 0 end) shipping_costs,
                     sum(case   when p.platform_id = 6 then 0
                                when a.product_id not in (15000,15001,15002,15003,15004) then settle_price
                                else 0 end) settle_normal_price,
                     sum(case   when p.platform_id = 6 then 0
                                when a.product_id not in (15000,15001,15002,15003,15004) and q.id = 3 then settle_price
                                else 0 end) value_price,
                     sum(case   when p.platform_id = 6 then 0
                                when a.product_id not in (15000,15001,15002,15003,15004) and q.id = 2 then settle_price
                                else 0 end) marketing_price,
                     sum(case   when p.platform_id = 6 then 0
                                when settle_type = 6 and p.platform_id != 6 then settle_price
                                else 0 end) back_price,
                     sum(case   when p.platform_id = 6 then 0
                                when settle_type = 8 and p.platform_id != 6 then settle_price
                                else 0 end) sale_price,
                     sum(case   when p.platform_id = 6 then 0
                                when settle_type = 9 and p.platform_id != 6 then settle_price
                                else 0 end) performance_price,
                     sum(if (p.platform_id not in (6), product_num, 0)) sale_num_all,
                     sum(if(p.platform_id = 4,product_num,0)) parts_num,
                     sum(if(p.platform_id not in(4,6),product_num,0)) product_nums')
            ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
            ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
            ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
            ->join('LEFT JOIN crm_industrial_seral_screen p ON p.product_id = a.product_id')
            ->join('LEFT JOIN crm_order_performance_type q ON q.type_id = b.static_type')
            ->where($map)
            ->group('b.cus_id')
            ->order('b.cus_id')
            ->select();
        $rst3Title = array(
            '客户','业务', '部门', '结算订单数量', '结算总金额','退货','研发费',
            '制图费','维修费', '预收款','运费','货款总额','价值业绩','市场拓展',
            '退货还款','折价还款',"工资抵账",'总出货量', '配件数', '产品数'
        );

        array_unshift($rst3, $rst3Title);
        $excelData = array(
            '1' => $rst1,
            '2' => $rst2,
            '3' => $rst3
        );
        $timeSet = array(
            'start' => date("YmdHi",$timeLimit1),
            'end'   => date("YmdHi",$timeLimit2)
        );
        $rt = $this->createXLS($timeSet, $excelData);
        $msg['msg'] =  ($rt['res'] !== false) ? 200 : 400;
        $msg['name'] = $rt['name'];
        $this->ajaxReturn($msg);
    }
    /*生成excel文件
    */
    private function createXLS($time, $data)
    {
        $filename = "performance_static" . $time['start'] . "__" . $time['end'];
        ini_set('max_execution_time', '0');
        Vendor('PHPExcel.PHPExcel');//引入类
        $filename = str_replace('.xlsx', '', $filename) . '.xlsx';
        $phpexcel = new \PHPExcel();
        $phpexcel->getProperties()
            ->setCreator("Maxu Dwin")
            ->setLastModifiedBy("Maxu Dwin")
            ->setTitle("DWIN_MAXU_STATISTICS")
            ->setSubject("statistics")
            ->setDescription("performance of employee")
            ->setKeywords("statistics")
            ->setCategory("报表");
        $phpexcel->getSecurity()->setLockWindows(true);
        $phpexcel->getSecurity()->setLockStructure(true);
        $phpexcel->getSecurity()->setWorkbookPassword("dwin_set_2002_hunan_beijing");
        $phpexcel->setActiveSheetIndex(0);
        $phpexcel->getActiveSheet()->fromArray($data['1']);
        $phpexcel->getActiveSheet()->setTitle('结算业绩基础数据');
        $phpexcel->createSheet();
        $phpexcel->setActiveSheetIndex(1);
        $phpexcel->getActiveSheet()->fromArray($data['2']);
        $phpexcel->getActiveSheet()->setTitle('按业务统计');
        $phpexcel->createSheet();
        $phpexcel->setActiveSheetIndex(2);
        $phpexcel->getActiveSheet()->fromArray($data['3']);
        $phpexcel->getActiveSheet()->setTitle('按客户统计');
        $objwriter = \PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/excelUpload/";
        $rst['res'] = $objwriter->save($rootPath . $filename);
        $rst['data'] = $objwriter;
        $rst['name'] = str_replace('.xlsx', '', $filename);
        return $rst;
    }
    /*下载*/
    private function downLoad()
    {
        $filename = I('get.fname');
        $filePath = WORKING_PATH . UPLOAD_ROOT_PATH . "/excelUpload/" .$filename.'.xlsx';
        import('Org.Net.Http');
        Http::download($filePath, $filename . ".xlsx");
    }

    protected function getBonusData($dateType, $start, $length, $order, $map)
    {
        // 业务所属部门 ：$deptSale 销售部 $deptOnline 客服部 $deptMarketing 市场部
        $deptSale      = "(3,11,24,25,26,27,28,29,30,31,32)";
        $deptOnline    = "(4,9,18,43,49,50,51)";
        $deptMarketing = "(44,45,46,47,48)";
        $deptSaleArr      = explode("," ,substr($deptSale, 1, strlen($deptSale) - 2));
        $deptOnlineArr    = explode("," ,substr($deptOnline, 1, strlen($deptOnline) - 2));
        $deptMarketingArr = explode("," ,substr($deptMarketing, 1, strlen($deptMarketing) - 2));
        if (empty($dateType)) {
            $count = M('order_collection')
                ->alias('a')
                ->join('left join crm_orderform b ON a.cus_order_id = b.id')
                ->where($map)
                ->count();
            $recordsFiltered = $count;
            /**业绩奖金计算方式
             * 销售部门
             * 技术服务费 * 0.15
             * 市场拓展业绩 单件业绩
             *
            */
            $rst = M('order_collection')
                ->alias('a')
                ->field("a.id id, b.id sys_id, from_unixtime(a.settle_time) settle_time,
                    if (LENGTH(b.cus_name) < 7,b.cus_name, REPLACE(b.cus_name,SUBSTRING(b.cus_name,3,4),'****')) cus_name,
                    b.pic_name, e.name dept, b.cpo_id k_order_id, a.product_id, a.product_name, a.single_price, a.product_num,
                    a.settle_price, g.settle_name settlement_name, h.collection_type settle_type, q.performance_type_name performance_type,
                    /*业绩奖金*/
                    ROUND(CASE 
                        WHEN d.deptid IN {$deptSale} THEN /*销售部门计算*/ 
                        CASE
                            WHEN q.id = 1 THEN
                                /*q.id = 1 技术服务费业绩：折算业绩 * 0.15*/
                                (
                                    CASE
                                        WHEN a.product_id IN (15001, 15002, 15004) THEN
                                            /*以上三种为研发费、制图费等费用的id*/
                                            (
                                                CASE /*计算以上费用的业绩奖金：汇票需要乘系数*/
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        a.settle_price * 0.99 * 0.15
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        a.settle_price * 0.98 * 0.15
                                                    ELSE
                                                        a.settle_price * 0.15
                                                    END
                                            )
                                        ELSE
                                        /*其他的都不算业绩，不属于技术服务费*/
                                            0
                                        END
                                )
                            WHEN q.id = 2 THEN
                                /*q.id = 2 市场拓展业绩，单件计算价格*/
                                (
                                    CASE
                                    WHEN w.platform_id NOT IN (4,6) /*分类为产品，（非配件、非技术服务费）*/THEN
                                        /*汇票乘系数后计算计件的费用*/
                                        (
                                            CASE
                                            WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                if((w.performance - 0.1 * (w.price - a.single_price * 0.99)) * a.product_num < 0, 0,(w.performance - 0.1 * (w.price - a.single_price * 0.99)) * a.product_num)
                                            WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                if((w.performance - 0.1 * (w.price - a.single_price * 0.98)) * a.product_num < 0, 0,(w.performance - 0.1 * (w.price - a.single_price * 0.98)) * a.product_num)
                                            ELSE
                                                if((w.performance - 0.1 * (w.price - a.single_price)) * a.product_num < 0, 0, (w.performance - 0.1 * (w.price - a.single_price)) * a.product_num)
                                            END
                                        )
                                    ELSE
                                        0
                                    END
                                )
                            ELSE
                                /*价值业绩计算：利润率*/
                                (
                                    CASE
                                        WHEN w.platform_id NOT IN (6) 
                                        THEN
                                        /*汇票乘系数*/
                                        (
                                            CASE
                                                WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                    if(((a.single_price - w.cost) * 0.8 / w.cost - 0.2) * 0.99 * 0.2 * settle_price < 0, 0, ((a.single_price - w.cost)*0.8 / w.cost - 0.2) * 0.99 * 0.2 * settle_price)
                                                WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                    if(((a.single_price - w.cost) * 0.8 / w.cost - 0.2) * 0.98 * 0.2 * settle_price < 0, 0, ((a.single_price - w.cost)*0.8 / w.cost - 0.2) * 0.98 * 0.2 * settle_price)
                                                ELSE
                                                    if (((a.single_price - w.cost) * 0.8 / w.cost - 0.2) * 0.2 * settle_price < 0, 0, ((a.single_price - w.cost)*0.8 / w.cost - 0.2) * 0.2 * settle_price)
                                            END
                                        )
                                    ELSE
                                        0
                                    END
                                )
                        END 
                        ELSE 0 END, 2) sale_bonus,
                        /*客服计算利润率后按照利润规模计算奖金*/ 
                            ROUND(CASE
                                        WHEN settle_type IN (1, 7) THEN /*算业绩的类型(正常收款+收取的应收费用)*/
                                            /*运费无利润、研发费利润100%、产品配件利润0.8乘以税前利润金额*/
                                            (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        WHEN settle_type IN (3) THEN /*扣除业绩的类型(退货)*/
                                        (-1) * (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        ELSE 0 END,2) online_profit")
                ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
                ->join('LEFT JOIN crm_customer c ON b.cus_id = c.cid')
                ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
                ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
                ->join('LEFT JOIN crm_order_invoice f ON f.type_id = b.invoice_type')
                ->join('LEFT JOIN crm_settlementlist g ON g.settle_id = b.settlement_method')
                ->join('LEFT JOIN crm_order_collection_type h ON h.id = a.settle_type')
                ->join('LEFT JOIN crm_order_performance_type q ON q.type_id = b.static_type')
                ->join('LEFT JOIN crm_industrial_seral_screen w ON w.product_id = a.product_id')
                ->order($order)
                ->where($map)
                ->limit($start, $length)
                ->select();
            foreach ($rst as $key =>$val) {
                $info[$key]['DT_RowId']        = $val['id'];
                $info[$key]['DT_RowClass']     = 'gradeX';
                $info[$key]['settle_time']     = $val['settle_time'];
                $info[$key]['cus_name']        = $val['cus_name'];
                $info[$key]['pic_name']        = $val['pic_name'];
                // $info[$key]['k_order_id']   = $val['k_order_id'];
                $info[$key]['product_name']    = $val['product_name'];
                $info[$key]['product_num']     = $val['product_num'];
                //$info[$key]['single_price']  = $val['single_price'];
                $info[$key]['settle_price']    = $val['settle_price'];
                $info[$key]['csource']         = $val['csource'];
                $info[$key]['online_profit']   = $val['online_profit'];
                $info[$key]['sale_bonus']      = $val['sale_bonus'];
                $info[$key]['dept']            = $val['dept'];
                $info[$key]['performance_type'] = $val['performance_type'];
                $info[$key]['settlement_name'] = $val['settlement_name'];
                $info[$key]['order_id']        = $val['k_order_id'];
                $info[$key]['settle_type']     = $val['settle_type'];
            }
            $info = empty($info) ? array() : $info;
        } else {
            if ($dateType == 2) {
                $rst = M('order_collection')
                    ->alias('a')
                    ->field("b.pic_name,
                        e.name dept,
                        count(DISTINCT b.cpo_id) order_num_all,
                        sum(
                            IF (
                                p.platform_id NOT IN (6),
                                `product_num`,
                                0
                            )
                        ) sale_num_all,
                        sum(
                            IF (
                                p.platform_id = 4,
                                `product_num`,
                                0
                            )
                        ) parts_num,
                        sum(
                            IF (
                                p.platform_id NOT IN (4, 6),
                                `product_num`,
                                0
                            )
                        ) product_nums,
                        sum(
                            CASE
                            WHEN a.product_id in (15000,15003) THEN
                                0
                            WHEN settle_type = 3 THEN
                                (- 1) * settle_price
                            ELSE
                                settle_price
                            END
                        ) settle_all_price,
                        sum(
                            CASE
                            WHEN settle_type = 3 THEN
                                settle_price
                            ELSE
                                0
                            END
                        ) settle_back_price,
                        sum(
                            CASE
                            WHEN a.product_id = 15003 THEN
                                settle_price
                            ELSE
                                0
                            END
                        ) settle_pre_price,
                        sum(
                            CASE
                            WHEN a.product_id IN (15001, 15002, 15004) THEN
                                settle_price
                            ELSE
                                0
                            END
                        ) tech_fee,
                        sum(
                            CASE
                            WHEN a.product_id = 15000 THEN
                                settle_price
                            ELSE
                                0
                            END
                        ) shipping_costs,
                        sum(
                            CASE
                            WHEN p.platform_id = 6 THEN
                                0
                            WHEN a.product_id NOT IN (
                                15000,
                                15001,
                                15002,
                                15003,
                                15004
                            )
                            AND q.id = 3 THEN
                                settle_price
                            ELSE
                                0
                            END
                        ) value_price,
                        sum(
                            CASE
                            WHEN p.platform_id = 6 THEN
                                0
                            WHEN a.product_id NOT IN (
                                15000,
                                15001,
                                15002,
                                15003,
                                15004
                            )
                            AND q.id = 2 THEN
                                settle_price
                            ELSE
                                0
                            END
                        ) marketing_price,
                        sum(
                            CASE
                            WHEN p.platform_id = 6 THEN
                                0
                            WHEN settle_type = 6
                            AND p.platform_id != 6 THEN
                                settle_price
                            ELSE
                                0
                            END
                        ) back_price,
                        sum(
                            CASE
                            WHEN p.platform_id = 6 THEN
                                0
                            WHEN settle_type = 8
                            AND p.platform_id != 6 THEN
                                settle_price
                            ELSE
                                0
                            END
                        ) sale_price,
                        sum(
                            CASE
                            WHEN p.platform_id = 6 THEN
                                0
                            WHEN settle_type = 9
                            AND p.platform_id != 6 THEN
                                settle_price
                            ELSE
                                0
                            END
                        ) performance_price,
                        ROUND(
                        sum(
                            /*奖金求和
                            *	部门：销售  客服
                            * 结算类型 1 7 ，3 ，其他。
                            * 
                            */
                        CASE
                            WHEN d.deptid IN {$deptSale} THEN 
                            /*业务*/
                                CASE
                                WHEN settle_type IN (1, 7) THEN/*算业绩的类型(正常收款+收取的应收费用)*/
                                    CASE
                                    WHEN q.id = 1 THEN
                                        /*费用业绩：折算业绩*0.15*/
                                        (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        a.settle_price * 0.99 * 0.15
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        a.settle_price * 0.98 * 0.15
                                                    ELSE
                                                        a.settle_price * 0.15
                                                    END
                                                )
                                            ELSE
                                                0
                                            END
                                        )
                                    WHEN q.id = 2 THEN
                                        /*市场拓展业绩，单件计算价格*/
                                        (
                                            CASE
                                            WHEN w.platform_id NOT IN (4, 6) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        if((w.performance - 0.1 * (w.price - a.single_price * 0.99)) * a.product_num < 0, 0, (w.performance - 0.1 * (w.price - a.single_price * 0.99)) * a.product_num)
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        if((w.performance - 0.1 * (w.price - a.single_price * 0.98)) * a.product_num < 0, 0, (w.performance - 0.1 * (w.price - a.single_price * 0.99)) * a.product_num)
                                                    ELSE
                                                        if((w.performance - 0.1 * (w.price - a.single_price)) * a.product_num < 0, 0, (w.performance - 0.1 * (w.price - a.single_price)) * a.product_num)
                                                    END
                                                )
                                            ELSE
                                                0
                                            END
                                        )
                                    ELSE
                                        /*价值业绩计算：利润率*/
                                        (
                                            CASE
                                            WHEN w.platform_id NOT IN (6) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        if(((a.single_price - w.cost) * 0.8 / w.cost - 0.2) * 0.99 * 0.2 * settle_price < 0,0,((a.single_price - w.cost) / w.cost - 0.2) * 0.99 * 0.2 * settle_price)
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        if(((a.single_price - w.cost) * 0.8 / w.cost - 0.2) * 0.98 * 0.2 * settle_price < 0, 0,((a.single_price - w.cost) / w.cost - 0.2) * 0.98 * 0.2 * settle_price)
                                                    ELSE
                                                        if(((a.single_price - w.cost) * 0.8 / w.cost - 0.2) * 0.2 * settle_price < 0, 0, ((a.single_price - w.cost) / w.cost - 0.2) * 0.2 * settle_price)
                                                    END
                                                )
                                            ELSE
                                                0
                                            END
                                        )
                                    END /*结束settle_type in(1,7)*/ 
                                WHEN settle_type IN (3) THEN/*开始退货业绩计算*/
                                    (-1)*
                                    (CASE
                                    WHEN q.id = 1 THEN
                                        /*费用业绩：折算业绩*0.15*/
                                        (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        a.settle_price * 0.99 * 0.15
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        a.settle_price * 0.98 * 0.15
                                                    ELSE
                                                        a.settle_price * 0.15
                                                    END
                                                )
                                            ELSE
                                                0
                                            END
                                        )
                                    WHEN q.id = 2 THEN
                                        /*市场拓展业绩，单件计算价格*/
                                        (
                                            CASE
                                            WHEN w.platform_id NOT IN (4, 6) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        if((w.performance - 0.1 * (w.price - a.single_price * 0.99)) * a.product_num < 0,0,(w.performance - 0.1 * (w.price - a.single_price * 0.99)) * a.product_num)
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        if((w.performance - 0.1 * (w.price - a.single_price * 0.98)) * a.product_num < 0,0,(w.performance - 0.1 * (w.price - a.single_price * 0.98)) * a.product_num)
                                                    ELSE
                                                        if((w.performance - 0.1 * (w.price - a.single_price)) * a.product_num < 0,0, (w.performance - 0.1 * (w.price - a.single_price)) * a.product_num)
                                                    END
                                                )
                                            ELSE
                                                0
                                            END
                                        )
                                    ELSE
                                        /*价值业绩计算：利润率*/
                                        (
                                            CASE
                                            WHEN w.platform_id NOT IN (6) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        if(((a.single_price - w.cost) * 0.8 / w.cost - 0.2) * 0.99 * 0.2 * settle_price < 0, 0, ((a.single_price - w.cost) / w.cost - 0.2) * 0.99 * 0.2 * settle_price)
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        if(((a.single_price - w.cost) * 0.8 / w.cost - 0.2) * 0.98 * 0.2 * settle_price < 0,0,((a.single_price - w.cost) / w.cost - 0.2) * 0.98 * 0.2 * settle_price)
                                                    ELSE
                                                        if(((a.single_price - w.cost) * 0.8 / w.cost - 0.2) * 0.2 * settle_price < 0, 0, ((a.single_price - w.cost) / w.cost - 0.2) * 0.2 * settle_price)
                                                    END
                                                )
                                            ELSE
                                                0
                                            END
                                        )
                                    END)
                                ELSE
                                    0
                                END
                        ELSE/*部门*/
                        0 END
                        ),4) ks /*ks 销售部业绩奖金（其他部门计算结果为0）*/,
                            /*客服部业绩奖金（利润规模乘以系数）*/
                            ROUND(CASE 
                                WHEN
                                    sum(CASE
                                        WHEN settle_type IN (1, 7) THEN /*算业绩的类型(正常收款+收取的应收费用)*/
                                            /*运费无利润、研发费利润80%、产品配件利润0.8乘以税前利润金额*/
                                            (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        WHEN settle_type IN (3) THEN /*扣除业绩的类型(正常收款+收取的应收费用)*/
                                        (-1) * (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        ELSE 0 END) < 10000 THEN 0
                                WHEN 
                                    25000 > sum(CASE
                                        WHEN settle_type IN (1, 7) THEN /*算业绩的类型(正常收款+收取的应收费用)*/
                                            /*运费无利润、研发费利润80%、产品配件利润0.8乘以税前利润金额*/
                                            (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        WHEN settle_type IN (3) THEN /*扣除业绩的类型(正常收款+收取的应收费用)*/
                                        (-1) * (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        ELSE 0 END) >= 10000 THEN
                                    0.01 * sum(CASE
                                        WHEN settle_type IN (1, 7) THEN /*算业绩的类型(正常收款+收取的应收费用)*/
                                            /*运费无利润、研发费利润80%、产品配件利润0.8乘以税前利润金额*/
                                            (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        WHEN settle_type IN (3) THEN /*扣除业绩的类型(正常收款+收取的应收费用)*/
                                        (-1) * (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        ELSE 0 END)
                                WHEN 
                                    50000 > sum(CASE
                                        WHEN settle_type IN (1, 7) THEN /*算业绩的类型(正常收款+收取的应收费用)*/
                                            /*运费无利润、研发费利润80%、产品配件利润0.8乘以税前利润金额*/
                                            (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        WHEN settle_type IN (3) THEN /*扣除业绩的类型(正常收款+收取的应收费用)*/
                                        (-1) * (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        ELSE 0 END) >= 25000 
                                THEN
                                    0.015 * sum(CASE
                                        WHEN settle_type IN (1, 7) THEN /*算业绩的类型(正常收款+收取的应收费用)*/
                                            /*运费无利润、研发费利润80%、产品配件利润0.8乘以税前利润金额*/
                                            (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        WHEN settle_type IN (3) THEN /*扣除业绩的类型(正常收款+收取的应收费用)*/
                                        (-1) * (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        ELSE 0 END)
                                WHEN 
                                    200000 > sum(CASE
                                        WHEN settle_type IN (1, 7) THEN /*算业绩的类型(正常收款+收取的应收费用)*/
                                            /*运费无利润、研发费利润80%、产品配件利润0.8乘以税前利润金额*/
                                            (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        WHEN settle_type IN (3) THEN /*扣除业绩的类型(正常收款+收取的应收费用)*/
                                        (-1) * (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        ELSE 0 END) >= 100000 THEN
                                    0.02 * sum(CASE
                                        WHEN settle_type IN (1, 7) THEN /*算业绩的类型(正常收款+收取的应收费用)*/
                                            /*运费无利润、研发费利润80%、产品配件利润0.8乘以税前利润金额*/
                                            (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        WHEN settle_type IN (3) THEN /*扣除业绩的类型(正常收款+收取的应收费用)*/
                                        (-1) * (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        ELSE 0 END) 
                                WHEN 
                                    300000 > sum(CASE
                                        WHEN settle_type IN (1, 7) THEN /*算业绩的类型(正常收款+收取的应收费用)*/
                                            /*运费无利润、研发费利润80%、产品配件利润0.8乘以税前利润金额*/
                                            (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 18 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        WHEN settle_type IN (3) THEN /*扣除业绩的类型(正常收款+收取的应收费用)*/
                                        (-1) * (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        ELSE 0 END) >=200000 THEN
                                    0.02 * sum(CASE
                                        WHEN settle_type IN (1, 7) THEN /*算业绩的类型(正常收款+收取的应收费用)*/
                                            /*运费无利润、研发费利润80%、产品配件利润0.8乘以税前利润金额*/
                                            (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        WHEN settle_type IN (3) THEN /*扣除业绩的类型(正常收款+收取的应收费用)*/
                                        (-1) * (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        ELSE 0 END)
                                ELSE
                                    0.02 *sum(CASE
                                        WHEN settle_type IN (1, 7) THEN /*算业绩的类型(正常收款+收取的应收费用)*/
                                            /*运费无利润、研发费利润80%、产品配件利润0.8乘以税前利润金额*/
                                            (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 1) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 1 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 1 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        WHEN settle_type IN (3) THEN /*扣除业绩的类型(正常收款+收取的应收费用)*/
                                        (-1) * (
                                            CASE
                                            WHEN a.product_id IN (
                                                15001,
                                                15002,
                                                15004
                                            ) THEN
                                                /*汇票乘系数*/
                                                (
                                                    CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                    END
                                                )
                                            WHEN a.product_id IN (15000,15003) THEN
                                                0
                                            ELSE
                                                (CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost) * 0.8) * a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98 - w.cost) * 0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost) * 0.8 * a.product_num
                                                END)
                                            END)
                                        ELSE 0 END)
                                END,4) online_salary,ROUND(sum(CASE
                                                    WHEN b.settlement_method IN ('JF05', 'JF16') THEN
                                                        ((a.single_price * 0.99 - w.cost)*0.8)* a.product_num
                                                    WHEN b.settlement_method IN ('HP01', 'HP02') THEN
                                                        (a.single_price * 0.98- w.cost)*0.8 * a.product_num
                                                    ELSE
                                                        (a.single_price - w.cost)*0.8* a.product_num
                                                END),4) salary_total,d.deptid")
                ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
                ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
                ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
                ->join('LEFT JOIN crm_industrial_seral_screen p ON p.product_id = a.product_id')
                ->join('LEFT JOIN crm_order_performance_type q ON q.type_id = b.static_type')
                ->join('LEFT JOIN crm_industrial_seral_screen w ON w.product_id = a.product_id')
                ->where($map)
                ->group('b.picid')
                ->limit($start, $length)
                ->order($order)
                ->select();
                $countD = M('order_collection')
                    ->alias('a')
                    ->field('b.picid')
                    ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
                    ->where($map)
                    ->group('b.picid')
                    ->select();
                $count = count($countD) ? count($countD) : 0;
                $recordsFiltered = $count;
                foreach ($rst as $key =>$val) {
                    $info[$key]['DT_RowId']          = $val['a.id'];
                    $info[$key]['DT_RowClass']       = 'gradeX';
                    $info[$key]['pic_name']          = $val['pic_name'];
                    $info[$key]['dept']              = $val['dept'];
                    $info[$key]['order_num_all']     = $val['order_num_all'];
                    $info[$key]['sale_num_all']      = $val['sale_num_all'];
                    $info[$key]['parts_num']         = $val['parts_num'];
                    $info[$key]['product_nums']      = $val['product_nums'];
                    $info[$key]['settle_all_price']  = $val['settle_all_price'];
                    $info[$key]['settle_back_price'] = $val['settle_back_price'];
                    $info[$key]['tech_fee']          = $val['tech_fee'];
                    $info[$key]['shipping_costs']    = $val['shipping_costs'];
                    $info[$key]['settle_pre_price']  = $val['settle_pre_price'];
                    $info[$key]['value_price']       = $val['value_price'];
                    $info[$key]['marketing_price']   = $val['marketing_price'];
                    $info[$key]['back_price']        = $val['back_price'];
                    $info[$key]['sale_price']        = $val['sale_price'];
                    $info[$key]['performance_price'] = $val['performance_price'];
                    if (in_array($val['deptid'], $deptSaleArr) !== false) {
                        $info[$key]['ks'] = $val['ks'];
                    } elseif (in_array($val['deptid'], $deptOnlineArr) !== false) {
                        $info[$key]['ks'] = $val['online_salary'];
                    } else {
                        $info[$key]['ks'] = 0;
                    }
                }
                $info = empty($info) ? "" : $info;
            } else {
                $info = array();
                $count = 0;
                $recordsFiltered = 0;
            }
        }
        $returnData = array(
            'count' => $count,
            'info'  => $info,
            'recordsFiltered' => $recordsFiltered
        );
        return $returnData;
    }

    public function showBusinessBonus()
    {
        if (IS_POST) {
            $orderCollectionModel = new OrderCollectionModel();
            $this->posts = I('post.');
            if (isset($this->posts['deptIdchange'])) {
                $deptId = (int)$this->posts['deptIdchange'];
                // 点击不同部门后显示该部门下所有有销售业务的子部门（没有递归的原因是有些子部门没有销售业务）
                switch ($deptId) {
                    case 3 :
                        $deptIds = self::DEPT_SALE;
                        break;
                    case 4 :
                        $deptIds = self::DEPT_SERVICE;
                        break;
                    case 44 :
                        $deptIds = self::DEPT_MARKET;
                        break;
                    default :
                        $deptIds = self::DEPT_CUSTOMER;
                        break;
                }
                $staffIdsFilter['deptid'] = array('in', $deptIds);
                $staffIds = M('staff')->field('id,name')->where($staffIdsFilter)->select();
                $this->ajaxReturn($staffIds);
            }

            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $index_str = $this->getSearchIndex($this->sqlCondition['search'], SPL_MATCH_ANY,'order',true);

            if ($index_str !== null) {
                $map['b.id'] = array('in', $index_str);
            }
            // 下拉菜单传值
            $orderAudit = array(4,5,6,7,8);
            $roleId = (int)session('roleId');
            if (in_array($roleId, $orderAudit)) {
                $this->orderIds = $this->getStaffIds((string)session('staffId'), 'order_child_id', "");
            }
            $thismonth = date('m');
            $thisyear  = date('Y');
            $startDay  = $thisyear . '-' . $thismonth - 1 . '-1';
            $timeLimit1 = empty(I('post.timeLimit1')) ? strtotime($startDay) : strtotime(I('post.timeLimit1'));
            $timeLimit2 = empty(I('post.timeLimit2')) ? time() : strtotime(I('post.timeLimit2'));
            if ($timeLimit2 - $timeLimit1 <= 0) {
                $this->output = $this->getDataTableOut($draw,0,0, array());
                $this->ajaxReturn($this->output);
            }
            $map['settle_time'] = array(array('egt', $timeLimit1), array('elt', $timeLimit2));
            $deptLimit = I('post.deptLimit');
            $staffLimit = I('post.staffLimit');
            if (empty($staffLimit)) {
                switch ((int)$deptLimit) {
                    case 3 :
                        $deptIds = self::DEPT_SALE;
                        break;
                    case 4 :
                        $deptIds = self::DEPT_SERVICE;
                        break;
                    case 44 :
                        $deptIds = self::DEPT_MARKET;
                        break;
                    default :
                        $deptIds = self::DEPT_CUSTOMER;
                        break;
                }
                $staffIdsFilter['deptid'] = array('in', $deptIds);
            } else {
                $staffIdsFilter['id'] = array('eq', (int)$staffLimit);
            }
            $staffIds = M('staff')->field('id,name')->where($staffIdsFilter)->select();
            $map['b.picid'] = array('IN', getPrjIds($staffIds,'id') ."," . session('staffId'));
            // where条件
            //$time1 = strtotime("2017-12-01 00:00:00");
            //$map['settlement_time'] = array('GT', $time1); //结算时间
            //$map['a.product_name'] = array('neq', "运费"); //结算时间
            //$map['settlement_status'] = array('IN', (string)$orderType); //结算状态

            /** 后台传输局到前台
            @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
            $returnRes = $this->getBonusData($this->posts['dateT'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $map);
            $this->output = $this->getDataTableOut($draw,$returnRes['count'], $returnRes['recordsFiltered'],$returnRes['info']);
            $this->ajaxReturn($this->output);

        } else {
            $orderAudit = array(4, 5, 6, 7, 8);
            $roleId  = (int)session('roleId');
            $deptIds = "3,4,44";// 市场部 客服部 销售部iD
            $deptFilter['id'] = array('IN', $deptIds);
            $dept = M('dept')->field('id, name')->where($deptFilter)->select();

            $this->assign('dept', $dept);

            // $this->orderIds:权限下的人名
            if (!in_array($roleId, $orderAudit)) {
                $this->orderIds = $this->getStaffIds((string)session('staffId'), 'order_child_id', "");
                if ($this->orderIds) {
                    $staffIdsFilter['id'] = array('IN', $this->orderIds);
                    $staffIds = M('staff')->field('id,name')->where($staffIdsFilter)->select();
                } else {
                    $staffIds = array();
                }
            } else {
                // 所有订单负责人
                $deptIdsOrder = "3,4,11,18,,24,25,26,27,28,29,30,31,32,43,44,45,46,47,48,49,50,51,54";
                $staffIdsFilter['deptid'] = array('in', $deptIdsOrder);
                $staffIds = M('staff')->field('id,name')->where($staffIdsFilter)->select();
            }
            $this->assign('staffIds', $staffIds);
            $this->display();
        }
    }

    public function showProduct()
    {
        if(IS_AJAX){
            $this->posts = I('post.');
            $draw  = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $orderType = $this->posts['prj_order_type'];
            if (!empty(I('post.costData'))) {
                if (I('post.costData') == 1) {
                    $screenData   = M('screen_category')->field('id,name,pid')->select();
                    $result       = getTree($screenData, $orderType, 1, 'pid');
                    $resultString = $result ? getPrjIds($result, 'id') . "," . $orderType : $orderType;
                    $map['parent_id'] = array('IN', $resultString);
                    if(!empty($this->sqlCondition['search'])){
                        if(strlen($this->sqlCondition['search']) < 3){
                            $this->ajaxReturn(false);
                        } else {
                            $map['product_name']  = array('like',"%" . $this->sqlCondition['search'] ."%");
                        }
                    }
                    $count = M('material')->where($map)->count();
                    $recordsFiltered = $count;
                    $result = M('material')
                        ->alias('a')
                        ->join('LEFT JOIN `crm_screen_category` c ON c.id = a.parent_id ')
                        ->field('c.name,a.product_id,a.product_name,a.product_number, a.parent_id,a.cost, a.price, a.performance,a.statistics_shipments_flag,a.statistics_performance_flag')
                        ->limit($this->sqlCondition['start'], $this->sqlCondition['length'])
                        ->where($map)
                        ->order($this->sqlCondition['order'])
                        ->select();
                    if (count($result) != 0) {
                        foreach($result as $key => &$val) {
                            $val['DT_RowId']          = $val['product_id'];
                        }
                    }
                    $this->output = $this->getDataTableOut($draw,$count,$recordsFiltered,$result);
                } else {
                    $this->output = $this->getDataTableOut($draw, 0, 0, array());
                }
            } else {
                if(!empty($orderType) && !empty($search)){
                    if(!empty($search)){
                        if(strlen($search) < 3){
                            $this->ajaxReturn(false);
                        } else {
                            $where['product_name']  = array('like',"%" . $search . "%");
                        }
                    }
                }

                $screenData  = M('screen_category')->field('id,name,pid')->select();
                $result = getTree($screenData,$orderType, 1, 'pid');
                $result = $result ? getPrjIds($result, 'id') . "," . $orderType : $orderType;
                $where['parent_id'] = array('IN',$result);
                //总数
                $count = M('material')->where($where)->count();
                $recordsFiltered = $count;
                $result = M('material')
                    ->alias('a')
                    ->join('LEFT JOIN `crm_screen_category` as c ON c.id = a.parent_id ')
                    ->field('c.name,a.product_id,a.product_name,a.product_number,a.parent_id,a.price,a.cost,a.performance')
                    ->limit($this->sqlCondition['start'], $this->sqlCondition['length'])
                    ->where($where)
                    ->order($this->sqlCondition['order'])
                    ->select();
                if (count($result) != 0) {
                    foreach($result as $key => &$val) {
                        $val['DT_RowId'] = $val['product_id'];
                    }
                }
                $this->output = $this->getDataTableOut($draw,$count,$recordsFiltered,$result);
            }
            $this->ajaxReturn($this->output);
        } else {
            $auditor = M('staff') -> where(['id' => ['IN', '65,223,214']]) -> select();
            $screenData  = M('screen_category')->field('id,name,pid')->select();
            $screenData  = getTree($screenData, 0, 0, 'pid');
            $this->assign(array(
                'screenData'  => $screenData,
                'auditor'     => $auditor
            ));
            $this->display();
        }
    }


    public function editProduct()
    {
        $this->posts = I('post.');
        $count = count($this->posts['ids']);
        $auditor = explode('_', I('post.auditor'));
        $auditor_id = $auditor[0];
        $auditor_name = $auditor[1];
        M()->startTrans();
        $unChangeData = array();
        for ($i = 0; $i < $count; $i++) {
            $filter[$i]['product_id'] = array('EQ', $this->posts['ids'][$i]);
            $oldData[$i] = M('material')
                ->alias('a')
                ->field('a.product_id, a.product_name, a.cost, a.price, a.performance, a.statistics_shipments_flag shipments, a.statistics_performance_flag perform_flag')
                ->where($filter[$i])->find();
            if ($oldData[$i]['product_id'] == $this->posts['ids'][$i] && $oldData[$i]['cost'] == $this->posts['cost'][$i] && $oldData[$i]['price'] == $this->posts['price'][$i] && $oldData[$i]['performance'] == $this->posts['performance'][$i] && $oldData[$i]['shipments'] == $this->posts['shipments'][$i] && $oldData[$i]['perform_flag'] == $this->posts['performStatistics'][$i]) {
                array_push($unChangeData, $oldData[$i]);
                unset($oldData[$i]);
                unset($this->posts['ids'][$i]);
                unset($this->posts['cost'][$i]);
                unset($this->posts['price'][$i]);
                unset($this->posts['performance'][$i]);
                unset($this->posts['shipments'][$i]);
                unset($this->posts['performStatistics'][$i]);
            } else
            {
                $changeData[$i]['product_id']        = $oldData[$i]['product_id'];
                $changeData[$i]['product_name']      = $oldData[$i]['product_name'];
                $changeData[$i]['changemanid']       = session('staffId');
                $changeData[$i]['changemanname']     = session('nickname');
                $changeData[$i]['auditor_id']        = $auditor_id;
                $changeData[$i]['auditor_name']      = $auditor_name;
                $changeData[$i]['create_time']       = time();
                $changeData[$i]['oldprice']          = $oldData[$i]['price'];
                $changeData[$i]['oldperformance']    = $oldData[$i]['performance'];
                $changeData[$i]['oldcost']           = $oldData[$i]['cost'];
                $changeData[$i]['oldperform_flag']   = $oldData[$i]['perform_flag'];
                $changeData[$i]['oldshipment_flag']  = $oldData[$i]['shipments'];
                $changeData[$i]['newprice']          = $this->posts['price'][$i] == $oldData[$i]['price']                    ? null : $this->posts['price'][$i];
                $changeData[$i]['newperformance']    = $this->posts['performance'][$i] == $oldData[$i]['performance']        ? null : $this->posts['performance'][$i];
                $changeData[$i]['newcost']           = $this->posts['cost'][$i] == $oldData[$i]['cost']                      ? null : $this->posts['cost'][$i];
                $changeData[$i]['newperform_flag']   = $this->posts['performStatistics'][$i] == $oldData[$i]['perform_flag'] ? null : $this->posts['performStatistics'][$i];
                $changeData[$i]['newshipment_flag']  = $this->posts['shipments'][$i] == $oldData[$i]['shipments']            ? null : $this->posts['shipments'][$i];
                // 表示是财务审核
                $changeData[$i]['audit_type']  = 2;
                $changeData[$i]['action_type'] = 1;
            }
        }
        if (count($oldData) == 0) {
            $msg['status'] = 200;
            $msg['msg'] = "无数据修改";
            $this->ajaxReturn($msg);
        } else {
            $this->posts['ids']               = array_values($this->posts['ids']);
            $this->posts['cost']              = array_values($this->posts['cost']);
            $this->posts['price']             = array_values($this->posts['price']);
            $this->posts['performance']       = array_values($this->posts['performance']);
            $this->posts['shipments']         = array_values($this->posts['shipments']);
            $this->posts['performStatistics'] = array_values($this->posts['performStatistics']);
            $changeData                 = array_values($changeData);
            $count_2 = count($changeData);
            $rst_1 = M()->table('crm_prochangerecord')->addAll($changeData);
            if ($rst_1 !== false) {
                M()->commit();
                $msg = ($count_2 == 1) ? array(
                    'status' => 200,
                    'msg'    => "编辑成功"
                ) :  array(
                    'status' => 200,
                    'msg'    => "批量编辑成功"
                );

            } else {
                M()->rollback();
                $msg = ($count_2 == 1) ? array(
                    'status' => 400,
                    'msg'    => "编辑失败"
                ) : array(
                    'status' => 400,
                    'msg'    => "批量编辑失败"
                );
            }
            $this->ajaxReturn($msg);
        }
    }

    /**
     * 产品数据审核
     */
    public function productAudit()
    {
        if(IS_POST){
            $params = I('post.');
            $mapTableData = $this->dataTable($params);
            $model = new ProchangerecordModel();
            $map = $this->mySearch($params['mySearch']);
            $map['audit_type'] = ['EQ',2];
            $mapTableData['map'] = array_merge($map, $mapTableData['map']);
            $data['draw'] = (int) $params['draw'];
            $data['recordsTotal'] = $model  -> where($map)-> count();
            $data['recordsFiltered'] = $model -> where($mapTableData['map']) -> count();
            $data['data'] = $model -> index($mapTableData['map'], $params['start'], $params['length'], $mapTableData['order']);

            foreach ($data['data'] as $key => &$value) {
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            }
            $this->ajaxReturn($data);
        }else{
            $this->display();
        }
    }

    public function myProductAudit()
    {
        if(IS_POST){
            $params = I('post.');
            $mapTableData = $this->dataTable($params);
            $model = new ProchangerecordModel();
            $map = $this->mySearch($params['mySearch']);
            $map['audit_type'] = ['EQ',2];
            $mapTableData['map'] = array_merge($map, $mapTableData['map']);
            $data['draw'] = (int) $params['draw'];
            $data['recordsTotal'] = $model  -> where($map)-> count();
            $data['recordsFiltered'] = $model -> where($mapTableData['map']) -> count();
            $data['data'] = $model -> index($mapTableData['map'], $params['start'], $params['length'], $mapTableData['order']);

            foreach ($data['data'] as $key => &$value) {
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            }
            $this->ajaxReturn($data);
        }else{
            $this->display();
        }
    }

    /**
     * 组装datatables前端自定义搜索参数
     * @param $mySearch
     * @return array
     */
    protected function mySearch($mySearch)
    {
        $arr = [];
        foreach ($mySearch as $key => $value) {
            if ($value == 'myID'){
                $value = session('staffId');
            }
            if ($value == 'myName'){
                $value = session('nickname');
            }
            $arr[$key] = ['EQ', $value];
        }
        return $arr;
    }

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


    public function showCustomer()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $indexStr = $this->getCustomerInfo($this->posts['cusName']);
            if (empty($indexStr)) {
                $this->ajaxReturn(false);
            }
            $filter['cid'] = array('IN', $indexStr);
            $this->field = "cname,cid,uid,ifnull(b.name,'现无负责人') u_name,from_unixtime(addtime) add_time";
            $cusData = M('customer')->field($this->field)
                ->join('LEFT JOIN crm_staff b ON b.id = crm_customer.uid')
                ->where($filter)
                ->limit(0,20)
                ->select();
            $this->ajaxReturn($cusData);
        } else {
            $this->display();
        }
    }
}


