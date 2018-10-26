<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/6/14
 * Time: 15:29
 */

namespace Dwin\Model;


use Think\Model;

class OrderformModel extends Model
{
    const IS_DEL = 1;  //已删除
    const NO_DEL = 0;  //未删除

    const TYPE_UNTREATED = 0;  // 未处理
    const TYPE_OUT_OF_REP = 1; // 出库中
    const TYPE_OUT_ALL = 2;    // 出库完成

    public static $stockOutMap = [
        self::TYPE_UNTREATED => "未处理",
        self::TYPE_OUT_OF_REP => "出库中",
        self::TYPE_OUT_ALL => "出库完成",
    ];

    /*可以删除的订单状态：数据表字段为枚举，需要转换类型*/
    const DEL_STATUS = 2;
    /*删除订单返回状态 200为成功 400为符合条件删除失败 401 402 分别为不满足条件禁止删除*/
    const FAIL_STATUS_0 = 400;
    const FAIL_STATUS_1 = 401;
    const FAIL_STATUS_2 = 402;
    const SUCCESS_STATUS = 200;
    const COMPLETE_STATUS = 1;
    const UN_COMPLETE_STATUS = 0;

    /* 订单表审核状态字段*/
    const UN_STATUS      = 2;
    const FINANCE_STATUS = 3;
    const SAVE_STATUS    = 5;
    /* 无客户客户编号*/
    const NON_CUS_ID = 10000000;

    /* 订单不占用库存的数据类型*/
    const UN_CHANGE_STOCK_TYPE = [6,7];
    /*统计客户订单数据*/
    protected $orderStatistics;

    // 订单类型
    const ORDER_TYPE_NORMAL_SALES = 1; // 正常销货
    const ORDER_TYPE_COLLECT_MONEY = 2; // 预收
    const ORDER_TYPE_ACCOUNTS_RECEIVABLE = 3; // 应收借物
    const ORDER_TYPE_FREE_PRODUCT = 4; // 免费样品
    const ORDER_TYPE_BORROW_BACK = 5; // 借物退库
    const ORDER_TYPE_BORROW_SALES = 6; // 借物销货
    const ORDER_TYPE_RETURN_PRODUCT = 7; // 退货
    const ORDER_TYPE_RETURN_MONEY = 8; // 退款

    public static $orderTypeMap = [
        self::ORDER_TYPE_NORMAL_SALES => '正常销货',
        self::ORDER_TYPE_COLLECT_MONEY => '预收',
        self::ORDER_TYPE_ACCOUNTS_RECEIVABLE => '应收借物',
        self::ORDER_TYPE_FREE_PRODUCT => '免费样品',
        self::ORDER_TYPE_BORROW_BACK => '借物退库',
        self::ORDER_TYPE_BORROW_SALES => '借物销货',
        self::ORDER_TYPE_RETURN_PRODUCT => '退货',
        self::ORDER_TYPE_RETURN_MONEY => '退款',
    ];


    /**
     * 根据提交的订单基本数据，获取订单信息
     * @todo 前端传的订单的发货仓库参数有问题，在上一层提交订单数据的方法中进行了处理submitOrder
     * 订单审核状态：1 未审核  2 不通过  3 待财务审核  4 审核通过  5 保存订单
     *  $orderData['check_status'] = $postOrderData['saveFlag'] == true ? 1 : 3;
     */
    public function getOrderAddData($postOrderData, $staffId)
    {
        $orderData = array(
            'id'                      => $postOrderData['orderId'],
            'oname'                   => $postOrderData['orderName'],
            'static_type'             => $postOrderData['staticType'],//@todo 新加字段 修改不合格、查看内容都需要涉及该字段。
            'order_type'              => $postOrderData['orderType'],
            'oprice'                  => $postOrderData['totalPrice'],
            'cus_id'                  => empty($postOrderData['cusId']) ? self::NON_CUS_ID : $postOrderData['cusId'],
            'cus_name'                => $postOrderData['cusName'],
            'logistics_type'          => $postOrderData['logisticsType'],
            'freight_payment_method'  => $postOrderData['freightPaymentMethod'],
            'delivery_ware_house'     => 'K004',
            'receiver'                => $postOrderData['receiver'],
            'receiver_addr'           => $postOrderData['orderAddress'],
            'receiver_phone'          => $postOrderData['orderPhone'],
            'logistices_tip'          => $postOrderData['logisticsTip'],
            'settlement_method'       => $postOrderData['settlementMethod'],
            'settlement_time'         => time(),
            'invoice_situation'       => $postOrderData['invoiceSituation'],
            'invoice_type'            => $postOrderData['invoice'],
            'invoice_contact'         => $postOrderData['invoiceName'],
            'invoice_phone'           => $postOrderData['invoicePhone'],
            'invoice_addr'            => $postOrderData['invoiceAddress'],
            'finance_tip'             => $postOrderData['financeTip'],
            'dept_check_id'           => $staffId,
            'finance_check_id'        => 0,
            'check_status'            => $postOrderData['saveFlag'] == true ? self::SAVE_STATUS : self::FINANCE_STATUS,
            'otime'                   => $postOrderData['orderTime'] / 1000,
            'order_addtime'           => time(),
            'picid'                   => $staffId,
            'pic_name'                => $postOrderData['staffName'],
            'pic_phone'               => $postOrderData['staffPhone'],
            'cpo_id'                  => "CPO" . $postOrderData['orderId'],
            'is_batch_delivery'        => $postOrderData['isBatchDelivery'],
            'production_status'       => ($postOrderData['productionStatus'] == 0) ? 1 : 0,
            'warehouse_manager_ids'   => $postOrderData['warehouseManagerIds'],
            'warehouse_logistics_ids' => $postOrderData['logisticsStaffIds']
        );
        if (in_array((int)$orderData['order_type'], self::UN_CHANGE_STOCK_TYPE)) {
            $orderData['stock_status'] = 4;
        }
        $receivableFilter['receivable_flag'] = array('EQ', 1);
        $receivableField = "settle_id";
        $receivableTypeArr = M('settlementlist')->field($receivableField)->where($receivableFilter)->select();
        $arr = array();
        foreach ($receivableTypeArr as $val) {
            $arr[] = $val['settle_id'];
        }
        if (in_array($orderData['settlement_method'], $arr)) {
            if ($orderData['order_type'] != 6) {
                $orderData['order_type'] = 3;
            }
        }
        return $orderData;
    }


    /**
     * 提交订单方法
     * @param array $postOrderData
     * @param array $postProductData
     * @param int|string $staffId
     * @param  int $flag :4 添加订单 5 修改订单
    */
    public function submitOrder($postOrderData, $postProductData, $staffId, $flag)
    {
        $orderBasic = $this->getOrderAddData($postOrderData, $staffId);

        $validityRst = $this->validityCheck($orderBasic);
        if ($validityRst['status'] != 200) {
            return $validityRst;
        }
        $uniqueCheckRst = $this->uniquenessCheck($orderBasic['id'], $orderBasic['cpo_id']);
        if ($uniqueCheckRst['status'] != 200) {
            return $uniqueCheckRst;
        }
        /**
         *@ todo 1000000为不添加客户ids时，默认设置的客户id,借物等方式提交的为该情形，该数字与getOrderAddData 相对应
         */
        if ($orderBasic['cus_id']) {
            if ($orderBasic['cus_id'] != self::NON_CUS_ID) {
                $customerModel = new CustomerModel();
                $authRst = $customerModel->getOrderAuth($orderBasic['cus_id'], $staffId);
                if ($authRst === false) {
                    return $msg = ['msg' => "无提交该客户订单的权限", 'status' => 403];
                }
            }
        } else {
            if (session('nickname') != $orderBasic['cus_name']) {
                return $msg = ['msg' => "无提交该订单的权限,订单客户名字段有误", 'status' => 403];
            }
        }
        $orderProductModel = new OrderproductModel();
        $orderRecordModel = new OrderChangeRecordModel();
        $orderProductData = $orderProductModel->getOrderProductAddData($postProductData, $orderBasic['id']);


        $content = ($flag == 4)
            ? "新订单添加操作，订单号：" . $orderBasic['id'] . "提交时间" . date('Y-m-d H:i:s') . ",订单添加人：" . session('nickname')
            : "订单修改操作，订单号：" . $orderBasic['id'] . "提交时间" . date('Y-m-d H:i:s') . ",订单修改人：" . session('nickname');
        $recordData = $orderRecordModel->getRecordAddData($orderBasic['id'], $content);

        $orderSubmitRst = ($flag == 4)
            ? $this->addOrderTrans($orderBasic, $orderProductData, $recordData)
            : $this->editOrderTrans($orderBasic, $orderProductData, $recordData);
        return $orderSubmitRst;
    }

    public function validityCheck($orderData)
    {
        $validityNumberArray = [
            'id', 'static_type', 'order_type', 'oprice', 'logistics_type', 'freight_payment_method',
            'invoice_situation', 'invoice_situation', 'invoice_type', 'dept_check_id', 'check_status', 'otime', 'picid',
            'is_batch_devliver', 'production_status'
        ];
        foreach ($orderData as $key => $val) {
            if (in_array($key, $validityNumberArray)) {
                if (!is_numeric($val)) {
                    return $msg = ['msg' => $key . $val . "非法输入", 'status' => 403];
                }
            }
        }
        return $msg = ['status' => 200];
    }

    public function uniquenessCheck($id, $cpoId)
    {
        $orderId = (int)str_replace("CPO", "", $cpoId);
        if ($orderId != (int)$id) {
            return $msg = ['msg' => "非法输入订单号", 'status' => 403];
        }
        $map['id'] = ['EQ', $id];
        $findRst = $this->where($map)->field('id,check_status')->find();
        if ($findRst) {
            if (($findRst['check_status'] == self::UN_STATUS) || $findRst['check_status'] == self::SAVE_STATUS) {
                return $msg = ['msg' => "数据重新提交", 'status' => 200];
            } else {
                return $msg = ['msg' => "该单据编号已录入，请刷新数据重新添加", 'status' => 404];
            }
        }
        return $msg = ['msg' => "单据正常，请继续操作", 'status' => 200];
    }

    /**
     * 添加事务
     * @param array $orderData 订单数据
     * @param array $productData 产品信息
     * @param array $recordData 记录数据
     * @todo 订单类型为退货时，不占用库存。不需要出库，财务审核后结束。
    */
    public function addOrderTrans($orderData, $productData, $recordData)
    {
        $orderProductModel = new OrderproductModel();
        M()->startTrans();
        /* orderform add data*/

        $submitOrderDataRst = $this->add($orderData);

        if ($submitOrderDataRst === false) {
            M()->rollback();
            return dataReturn('提交失败', 400);
        }
        /* order's product add*/

        $delFilter['order_id'] = ['EQ', $orderData['id']];
        $delData = M('orderproduct')->where($delFilter)->find();
        if ($delData) {
            M()->rollback();
            return dataReturn('禁止提交，请联系管理员', 400);
        }
        $addProductTransRst = in_array($orderData['order_type'], self::UN_CHANGE_STOCK_TYPE)
            ? $orderProductModel->addAll($productData)
            : $orderProductModel->addProductTrans($productData);

        if (in_array($orderData['order_type'], self::UN_CHANGE_STOCK_TYPE)) {
            if ($addProductTransRst['add'] === false || in_array(false, $addProductTransRst['upd'])) {
                M()->rollback();
                return dataReturn('提交失败2', 400);
            }
        } else {
            if ($addProductTransRst === false) {
                M()->rollback();
                return dataReturn('提交失败5', 400);
            }
        }


        /* customer oder amount update*/
        $mapStatistics['check_status'] = array('IN', "1,3,4");
        $mapStatistics['cus_id'] = array('EQ', $orderData['cus_id']);
        $data = $this->where($mapStatistics)->field('cus_id cid,sum(case when order_type in (6,7) then (-1) * oprice else oprice end) `total_order_price`')->group('cid')->find();
        $cusUpdFilter['cid'] = array('EQ', $orderData['cus_id']);
        if ($data) {
            $cusUpdRst = M()->table('crm_customer')->where($cusUpdFilter)->setField($data);
            if ($cusUpdRst === false) {
                M()->rollback();
                return $msg = ['msg' => "提交失败", 'status' => 400];
            }
        }


        /* order add message add*/
        $recordAddRst = M()->table('crm_order_change_record')->add($recordData);

        if ($recordAddRst === false) {
            M()->rollback();
            return $msg = ['msg' => "提交失败", 'status' => 400];
        }
        M()->commit();
        $msg = [
            'msg' => '新订单提交提交成功',
            'status' => 200
        ];
        return $msg;
    }

    /* edit order's basic  data
     * edit order's production data
     * add  order change record
    */
    public function editOrderTrans($orderData, $productData, $recordData)
    {
        M()->startTrans();

        $orderProductModel = new OrderproductModel();
        $submitOrderDataRst = $this->save($orderData);

        if ($submitOrderDataRst === false) {
            M()->rollback();
            return dataReturn('编辑失败', 400);
        }


        $delFilter['order_id'] = ['EQ', $orderData['id']];
        $delProductTransRst = $orderProductModel->delProductTrans($delFilter);
        if ($delProductTransRst['del'] === false || in_array(false, $delProductTransRst['upd'])) {
            M()->rollback();
            return dataReturn('提交失败1', 400);
        }
        $addProductTransRst = in_array($orderData['order_type'], self::UN_CHANGE_STOCK_TYPE)
            ? $orderProductModel->addAll($productData)
            : $orderProductModel->addProductTrans($productData);

        if (in_array($orderData['order_type'], self::UN_CHANGE_STOCK_TYPE)) {
            if ($addProductTransRst['add'] === false || in_array(false, $addProductTransRst['upd'])) {
                M()->rollback();
                return dataReturn('提交失败2', 400);
            }
        } else {
            if ($addProductTransRst === false) {
                M()->rollback();
                return dataReturn('提交失败5', 400);
            }
        }


        /* customer oder amount update*/
        $mapStatistics['check_status'] = array('IN', "1,3,4");
        $mapStatistics['cus_id'] = array('EQ', $orderData['cus_id']);
        $data = $this->where($mapStatistics)->field('cus_id cid, sum(case when order_type in (6,7) then (-1) * oprice else oprice end) `total_order_price`')->group('cid')->find();

        $cusUpdFilter['cid'] = array('EQ', $data['cid']);
        $cusUpdRst = M()->table('crm_customer')->where($cusUpdFilter)->setField($data);
        if ($cusUpdRst === false) {
            M()->rollback();
            return dataReturn('提交失败3', 400);
        }

        /* order add message add*/
        $recordAddRst = M()->table('crm_order_change_record')->add($recordData);

        if ($recordAddRst === false) {
            M()->rollback();
            return dataReturn('提交失败4', 400);
        }
        M()->commit();
        return dataReturn('修改成功', 200);
    }

    public function delOrderTrans($id)
    {

        M()->startTrans();
        $productModel = new StockModel();
        $map_1['id'] = array('eq', $id);
        $orderAddTime = M('orderform')->where($map_1)->field('order_addtime')->find()['order_addtime'];
        if ($orderAddTime > 1526827232) {
            $productFilter['order_id'] = ['eq', $id];
            $productData = M('orderproduct')->where($productFilter)
                ->field('product_id,product_name,product_num')
                ->select();
            foreach ($productData as $key => $value) {
                $updateFilter[$key]['product_id'] = ['EQ', $value['product_id']];
                $updateFilter[$key]['warehouse_name'] = ['EQ', 'K004'];
                $updateRst[$key] = $productModel->updateWithFlag('rejectOrder', $updateFilter[$key], $value['product_num']);
                if ($updateRst[$key] === false) {
                    M()->rollback();
                    return dataReturn('订单删除失败', 400);
                }
            }
        }
        $rst_1 = M()->table('crm_orderform')->where($map_1)->save(['is_del' => 1]);
        if ($rst_1 === false) {
            M()->rollback();
            return dataReturn('订单删除失败，事务回滚，联系管理', 401);
        }
//        $delProdFilter['order_id'] = array('EQ', $id);
//        $rst_2 = M()->table('crm_orderproduct')->where($delProdFilter)->delete();
//        if ($rst_2 === false) {
//            M()->rollback();
//            return dataReturn('订单产品数据删除失败,未删除数据', 401);
//        }

        $orderChange = array(
            'finance_id' => $this->staffId,
            'change_time' => time(),
            'order_id' => $id,
            'change_content' => "订单删除操作，订单号：" . $id . "删除时间" . date('Y-m-d H:i:s') . ",订单删除人：" . session('nickname')
        );
        $rst_3 = M()->table('crm_order_change_record')->add($orderChange);

        if ($rst_3 === false) {
            M()->rollback();
            return dataReturn('订单删除失败', 401);
        }
        M()->commit();
        return dataReturn('订单删除成功', 200);

    }

    /**
     * @name getOrderAmount
     * @abstract 统计一定时间内，符合where条件的某客户的订单总金额
     * @param int $cusId
     * @param string $condi orderform'status filter condition
     * @param string $status 1 2 3 4
     * @param int $timeLimit timestrap
     * $param string $priceName 对应的统计得到的价钱的数据字段别名
     * @return array $statistics  下表为cid 和 $priceName
     */
    public function getOrderAmount($cusId, $condi, $status, $timeLimit, $priceName)
    {
        $condition['cus_id'] = array('EQ', $cusId);
        $condition['check_status'] = array($condi, $status);
        $condition['order_addtime'] = array('GT', $timeLimit);
        $condition['is_del'] = array('EQ', 0);
        // $cusId 对应的订单金额、最新订单数
        $statistics = $this->where($condition)
            ->field("cus_id cid, sum(oprice) as `$priceName`")
            ->group('cid')
            ->find();
        return $statistics;
    }

    /**
     * @name countOrderNumber
     * @abstract 统计满足条件的订单数量
     * @param array $where 查询条件
     * @param string $field 统计字段
     * @return int $count 返回数据计数总数。
     */
    public function countOrderNumber($where, $field = "id")
    {
        return $count = $this->where($where)->count($field);
    }

    /**
     * @name getOrderformData
     * @abstract 根据条件返回dataTables所需数据（也可以查询满足条件的订单表数据）
     * @example b发货仓库 c结算方式 d订单类别 e绩效统计方式 f快递类型 g快递付款方式 h发票类别 i开票方式 j审核状态
     * @param array $where 查询条件
     * @param string $field 字段
     * @param string $order 查询的order排序条件
     * @param string $start limit查询的起始位置
     * @param string $length limit查询的长度
     * @param string $primaryKey 主键名称
     * @return array $orderContents
     */
    public function getOrderformData($where, $field, $start, $length, $order, $primaryKey)
    {
        $orderContents = $this
            ->where($where)
            ->field($field)
            ->join('LEFT JOIN `crm_repertorylist` b ON FIND_IN_SET(b.rep_id,delivery_ware_house)')
            ->join('LEFT JOIN `crm_settlementlist` c ON c.settle_id = settlement_method')
            ->join('LEFT JOIN `crm_order_type` d ON d.type_id = crm_orderform.order_type')
            ->join('LEFT JOIN `crm_order_performance_type` e ON e.type_id = crm_orderform.static_type')
            ->join('LEFT JOIN `crm_order_logistics_type` f ON f.type_id = crm_orderform.logistics_type')
            ->join('LEFT JOIN `crm_order_freight_payment_type` g ON g.type_id = crm_orderform.freight_payment_method')
            ->join('LEFT JOIN `crm_order_invoice` h ON h.type_id =crm_orderform.invoice_type')
            ->join('LEFT JOIN `crm_order_invoice_situation` i ON i.type_id = crm_orderform.invoice_situation')
            ->join('LEFT JOIN `crm_order_check_status` j ON j.type_id = crm_orderform.check_status')
            ->limit($start, $length)
            ->order($order)
            ->group('crm_orderform.id')
            ->select();
        foreach ($orderContents as &$val) {
            $val['DT_RowId'] = $val[$primaryKey];
        }
        return $orderContents;
    }

    /**
     * @name getOrderOneData
     * @abstract 根据条件返回dataTables所需数据（也可以查询满足条件的订单表数据）
     * @example b发货仓库 c结算方式 d订单类别 e绩效统计方式 f快递类型 g快递付款方式 h发票类别 i开票方式 j审核状态
     * @param array $where 查询条件
     * @param string $field 字段
     * @param string $order 查询的order排序条件
     * @return array $data
     */
    public function getOrderOneData($where, $field, $order = 'crm_orderform.id')
    {
        $order = empty($order) ? 'crm_orderform.id' : $order;
        $orderContents = $this->where($where)
            ->field($field)
            ->join('LEFT JOIN `crm_repertorylist` b ON FIND_IN_SET(b.rep_id,delivery_ware_house)')
            ->join('LEFT JOIN `crm_settlementlist` c ON c.settle_id = settlement_method')
            ->join('LEFT JOIN `crm_order_type` d ON d.type_id = crm_orderform.order_type')
            ->join('LEFT JOIN `crm_order_performance_type` e ON e.type_id = crm_orderform.static_type')
            ->join('LEFT JOIN `crm_order_logistics_type` f ON f.type_id = crm_orderform.logistics_type')
            ->join('LEFT JOIN `crm_order_freight_payment_type` g ON g.type_id = crm_orderform.freight_payment_method')
            ->join('LEFT JOIN `crm_order_invoice` h ON h.type_id = crm_orderform.invoice_situation')
            ->join('LEFT JOIN `crm_order_invoice_situation` i ON i.type_id = crm_orderform.invoice_type')
            ->join('LEFT JOIN `crm_order_check_status` j ON j.type_id = crm_orderform.check_status')
            ->join('LEFT JOIN `crm_order_collection` k ON k.cus_order_id = crm_orderform.id')
            ->order($order)
            ->group('crm_orderform.id')
            ->select();
        return $orderData = $orderContents[0];
    }

    /**
     * @name getOrderOneDataUseGroup
     * @abstract 根据条件返回dataTables所需数据（也可以查询满足条件的订单表数据）
     * @example b发货仓库 c结算方式 d订单类别 e绩效统计方式 f快递类型 g快递付款方式 h发票类别 i开票方式 j审核状态
     * @param array $where 查询条件
     * @param string $field 字段
     * @param string $order 查询的order排序条件
     * @param string $group 查询分组条件
     * @return $data 长度为1的数组
     */
    public function getOrderOneDataUseGroup($field, $where, $group, $order = "crm_orderform.id")
    {
//        $where['is_del'] = ['eq', 0];
        return $data = $this->field($field)
            ->join('LEFT JOIN `crm_repertorylist` b ON b.rep_id = delivery_ware_house')
            ->join('LEFT JOIN `crm_settlementlist` c ON c.settle_id = settlement_method')
            ->join('LEFT JOIN `crm_order_type` d ON d.type_id = crm_orderform.order_type')
            ->join('LEFT JOIN `crm_order_performance_type` e ON e.type_id = crm_orderform.static_type')
            ->join('LEFT JOIN `crm_order_logistics_type` f ON f.type_id = crm_orderform.logistics_type')
            ->join('LEFT JOIN `crm_order_freight_payment_type` g ON g.type_id = crm_orderform.freight_payment_method')
            ->join('LEFT JOIN `crm_order_invoice` h ON h.type_id = crm_orderform.invoice_situation')
            ->join('LEFT JOIN `crm_order_invoice_situation` i ON i.type_id = crm_orderform.invoice_type')
            ->join('LEFT JOIN `crm_order_check_status` j ON j.type_id = crm_orderform.check_status')
            ->join('LEFT JOIN `crm_order_collection` k ON k.cus_order_id = crm_orderform.id')
            ->where($where)
            ->order($order)
            ->group($group)
            ->find();
    }

    public function getOrderDataUseGroup($field, $where, $group, $order, $start, $length)
    {
//        $where['is_del'] = ['eq', 0];
        return $data = $this->field($field)
            ->join('LEFT JOIN `crm_repertorylist` b ON b.rep_id = delivery_ware_house')
            ->join('LEFT JOIN `crm_settlementlist` c ON c.settle_id = settlement_method')
            ->join('LEFT JOIN `crm_order_type` d ON d.type_id = crm_orderform.order_type')
            ->join('LEFT JOIN `crm_order_performance_type` e ON e.type_id = crm_orderform.static_type')
            ->join('LEFT JOIN `crm_order_logistics_type` f ON f.type_id = crm_orderform.logistics_type')
            ->join('LEFT JOIN `crm_order_freight_payment_type` g ON g.type_id = crm_orderform.freight_payment_method')
            ->join('LEFT JOIN `crm_order_invoice` h ON h.type_id = crm_orderform.invoice_type')
            ->join('LEFT JOIN `crm_order_invoice_situation` i ON i.type_id = crm_orderform.invoice_situation')
            ->join('LEFT JOIN `crm_order_check_status` j ON j.type_id = crm_orderform.check_status')
            ->join('LEFT JOIN `crm_order_collection` k ON k.cus_order_id = crm_orderform.id')
            ->join('LEFT JOIN `crm_order_settlement_status` l ON l.type_id = crm_orderform.settlement_status')
            ->where($where)
            ->order($order)
            ->group($group)
            ->limit($start, $length)
            ->select();
    }

    public function getOrderCollectionDataByFind($field, $where)
    {
        $data = $this->field($field)
            ->join('LEFT JOIN crm_order_collection b ON b.cus_order_id = crm_orderform.id')
            ->where($where)->find();
        return $data;
    }

    /**
     * getCusOrderPerformance 客户的相关订单统计数据（1季度、半年、四个月、1年内的采购金额）
     * @param [int] $cusId 客户的id
     * @return array $statistics 客户的相关订单统计数据
     */
    public function getCusOrderPerformance($cusId)
    {
        $dateSet = array(
            'fourMonth' => strtotime(date("Y-m", strtotime("-3 month"))),
            'quarterly' => strtotime(date("Y-m", strtotime("-2 month"))),
            'halfYearly' => strtotime(date("Y-m", strtotime("-5 month"))),
            'annual' => strtotime(date("Y-m", strtotime("-11 month")))
        );
        $this->orderStatistics['fourMonthAmount'] = $this->getOrderAmount($cusId, 'EQ', '4', $dateSet['fourMonth'], 'four-month_order_amount');
        $this->orderStatistics['quarterlyAmount'] = $this->getOrderAmount($cusId, 'EQ', '4', $dateSet['quarterly'], 'quarterly_order_amount');
        $this->orderStatistics['halfYearlyAmount'] = $this->getOrderAmount($cusId, 'EQ', '4', $dateSet['halfYearly'], 'half-yearly_order_amount');
        $this->orderStatistics['annualAmount'] = $this->getOrderAmount($cusId, 'EQ', '4', $dateSet['annual'], 'annual_order_amount');
        $this->orderStatistics['totalAmount'] = $this->getOrderAmount($cusId, 'IN', '1,3,4', $dateSet['fourMonth'], 'total_order_price');
        $condition['cus_id'] = array('EQ', $cusId);
        $condition['check_status'] = array('EQ', "4");
        // $maxOrderTime = 订单表中最大的订单时间
        $maxOrderTime = $this->getOrderOneDataUseGroup('max(order_addtime) max_order_time', $condition, 'cus_id');
        $statistics = array(
            'cid' => $cusId,
            'four-month_order_amount' => $this->orderStatistics['fourMonthAmount']['four-month_order_amount'] == null ? 0 : $this->orderStatistics['fourMonthAmount']['four-month_order_amount'],
            'quarterly_order_amount' => $this->orderStatistics['quarterlyAmount']['quarterly_order_amount'] == null ? 0 : $this->orderStatistics['quarterlyAmount']['quarterly_order_amount'],
            'half-yearly_order_amount' => $this->orderStatistics['halfYearlyAmount']['half-yearly_order_amount'] == null ? 0 : $this->orderStatistics['halfYearlyAmount']['half-yearly_order_amount'],
            'annual_order_amount' => $this->orderStatistics['annualAmount']['annual_order_amount'] == null ? 0 : $this->orderStatistics['annualAmount']['annual_order_amount'],
            'total_order_price' => $this->orderStatistics['totalAmount']['total_order_price'] == null ? 0 : $this->orderStatistics['totalAmount']['total_order_price'],
            'max_order_time' => $maxOrderTime['max_order_time'] == null ? 0 : (int)$maxOrderTime['max_order_time']
        );
        return $statistics;
    }

    /**
     * @name updateOrderData
     * @param mixed $where 更新条件
     * $param array $updateData 修改的数据（需要包含主键）
     * @return mixed $rst
     */
    public function updateOrderData($where, $updateData)
    {
        return $this->where($where)->setField($updateData);

    }

    /**
     * @name deleteOrder
     * @abstract deleteOrder删除订单方法 只能删个人负责的不合格订单，订单删除后还要删除产品表数据
     * @param int $id 订单的主键Id
     * $param int $staffId 操作人的Id
     * @return array $rst 成功会返回200状态码成功后删除对应的产品数据
     */
    public function deleteOrder($id, $staffId)
    {
        $where['crm_orderform.id'] = array('eq', $id);
        $field = "picid,check_status";
        $order = 'crm_orderform.id';
        $checkData = $this->getOrderOneData($where, $field, $order);
        /* 判断负责人 => 判断订单状态（除不合格的订单外都不能删除）*/
        if ($staffId == $checkData['picid']) {
            if (self::DEL_STATUS == (int)$checkData['check_status']) {
                $rst = $this->where($where)->delete();
                if ($rst != false) {
                    $msg = array(
                        'msg' => "删除成功",
                        'status' => self::SUCCESS_STATUS
                    );
                } else {
                    $msg = array(
                        'msg' => "删除失败",
                        'status' => self::FAIL_STATUS_0
                    );
                }
            } else {
                $msg = array(
                    'msg' => "删除失败,只能删除不合格订单",
                    'status' => self::FAIL_STATUS_1
                );
            }
        } else {
            // 删除的人与订单负责人不同不能删除
            $msg = array(
                'msg' => "删除失败，您不是订单负责人",
                'status' => self::FAIL_STATUS_2
            );
        }
        return $msg;
    }


    /*以前写 不写进文档，CustomerController有使用*/
    public function getOrderList($where, $field)
    {
        return $this->where($where)
            ->join('crm_customer AS cus ON cus.cid = cus_id')
            ->join('crm_staff AS sta ON sta.id = picid')
            ->field($field)
            ->select();
    }

    /*以前写 不写进文档，未找到使用的地方*/
    public function getAuditList($map)
    {
        return $this->where($map)
            ->join('LEFT JOIN `crm_repertorylist` rep ON rep.rep_id = delivery_ware_house')
            ->join('LEFT JOIN `crm_settlementlist` sett ON sett.settle_id = settlement_method')
            ->join('LEFT JOIN `crm_customer` cus ON cus.cid = cus_id')
            ->join('LEFT JOIN `crm_staff` sta ON sta.id = picid')
            ->field('crm_orderform.id,order_id k3_id,oname,order_type,oprice cur_num,cus.cname cusname,
                            logistics_type log_type,freight_payment_method,rep.repertory_name ware_house,sett.settle_name,invoice_situation inv_situation,
                            invoice_type inv_type,check_status audit_status,otime time,sta.name staffname,sta.phone staff_phone')
            ->select();
    }

    /**
     * 根据订单号获取订单id
     * @param $order  string  订单号
     * @return int  订单id
     */
    public function getOrderIdByOrderNumber($order)
    {
        $map = [
            'cpo_id' => ['EQ', $order]
        ];
        $data = $this
            ->where($map)
            ->getField('id');
        return $data;
    }

    public function getOrderPendingDataById($id, $cpoId, $returnDataSet = ['stockOutData', 'productionPlanData', 'productData', 'orderRecordData'])
    {
        $orderRecordModel = new OrderChangeRecordModel();
        $orderProductModel = new OrderproductModel();
        $stockRecordModel = new StockAuditOutModel();
        $productionPlanModel = new ProductionPlanModel();
        $stockOutOrderformModel = new StockOutOrderformModel();
        $orderFilter['crm_orderform.id'] = array('EQ', $id);
        $recordFilter['order_id'] = array('EQ', (int)$id);
        $stockFilter['action_order_number'] = array('EQ', $cpoId);
        $arraySel = ['stockOutData', 'productionPlanData', 'productData', 'orderRecordData', 'orderData'];
        foreach ($returnDataSet as $key => $item) {
            if (in_array($item, $arraySel)) {
                switch ($item) {
                    case "orderData" :
                        $field = "crm_orderform.id,cpo_id,from_unixtime(settlement_time) finance_audit_time,pic_name staname,";
                        $field .= "pic_phone staff_phone,if (LENGTH(cus_name) < 7, cus_name, REPLACE(cus_name,SUBSTRING(cus_name,3,4),\"****\")) cusname,j.check_type_name check_status_name,";
                        $field .= "stock_status stock_out_status,f.logistics_type_name log_type,group_concat(distinct b.repertory_name) ware_house,";
                        $field .= "is_batch_delivery,logistices_tip";
                        $orderDetailData[$item] = $this->getOrderOneData($orderFilter, $field);
                        break;
                    case "productData" :
                        $orderDetailData[$item] = $orderProductModel->getOrderProductAuditMsgById($id);
                        break;
                    case "orderRecordData" :
                        $field = "staff.name, from_unixtime(change_time) change_time,change_content content,order_id";
                        $orderDetailData[$item] = $orderRecordModel->getOrderChangeRecord($recordFilter, $field);
                        break;
                    case "productionPlanData" :
                        $orderDetailData[$item] = $productionPlanModel->index($recordFilter);
                        break;
                    case "stockOutData" :
//                        $orderDetailData[$item] = $stockRecordModel->getStockOutData($stockFilter);
                        // 修改后从crm_stock_material中获取数据
                        $orderDetailData[$item] = $stockOutOrderformModel->getAllMaterialMsgByOrderId($id);

                        break;
                    default :
                        break;
                }
            }
        }
        return $orderDetailData;
    }

    public function updateStatusWithStockLog($updateData)
    {
        $stockModel = new StockAuditOutModel();
        $orderProductModel = new OrderproductModel();
        $this->startTrans();
        $stockField = "action_order_number";
        $filter['id'] = array('in', getPrjIds($updateData, "id"));

        $stockOrderArr = $stockModel->where($filter)->field($stockField)->group('action_order_number')->select();
        $orderIdArray = array();
        foreach ($stockOrderArr as $val) {
            $orderIdArray[] = (int)str_replace("CPO", "", $val['action_order_number']);
        }

        $orderProductField = "product_num - stock_out_num un_num";
        for ($i = 0; $i < count($orderIdArray); $i++) {
            $updateId[$i]['order_id'] = array('EQ', (int)$orderIdArray[$i]);
            $orderProductData = $orderProductModel->where($updateId[$i])->field($orderProductField)->select();

            foreach ($orderProductData as $v) {
                $flag[$i][] = ($v['un_num'] <= 0) ? self::COMPLETE_STATUS : self::UN_COMPLETE_STATUS;
            }
            $orderUpdateData[$i]['id'] = (int)$orderIdArray[$i];
            $orderUpdateData[$i]['stock_status'] = in_array(self::UN_COMPLETE_STATUS, $flag[$i]) ? 1 : 2;
            $orderUpdCondition[$i]['id'] = ['EQ', $orderIdArray[$i]];
            $orderUpdRst[$i] = $this->where($orderUpdCondition[$i])->save($orderUpdateData[$i]);
            if ($orderUpdRst[$i] === false) {
                $this->rollback();
                return $msg = [
                    'status' => self::FAIL_STATUS_1,
                    'msg' => "提交审核成功，订单表状态修改失败"
                ];
            }
        }
        $this->commit();

        return $msg = [
            'status' => self::SUCCESS_STATUS,
            'msg' => "提交审核成功，订单表状态修改成功",
            'sql' => $this->getLastSql()
        ];
        /**
         * 根据更新的出库单号，获取订单ids
         */
        //$stockModel->
    }


    public function getOwnOrderData($staffId, $time)
    {
        $map['picid'] = ['eq', $staffId];
        $map['order_addtime'] = ['GT', $time];
        $data = $this->where($map)->field('cpo_id')->select();
        $orderString = getPrjIds($data, 'cpo_id');
        return $orderString;
    }

    public function getOrderIndexData($map, $sqlCondition)
    {
        $map['crm_orderform.is_del'] = ['EQ', 0];
        $field = "crm_orderform.cpo_id,
                  crm_orderform.id,
                  oname,
                  order_type,
                  oprice cur_num,
                  cus_name cusname,
                  d.order_type_name,
                  f.logistics_type_name log_type,
                  g.freight_payment_name freight_payment_method,
                  group_concat(b.repertory_name) ware_house,
                  c.settle_name,
                  i.invoice_situation_name inv_situation,
                  h.invoice_name inv_type,
                  j.check_type_name audit_status,
                  from_unixtime(otime) time,
                  pic_name staname,
                  pic_phone staff_phone,
                  stock_status,
                  production_status";

        return $orderContents = $this->getOrderformData($map, $field, $sqlCondition['start'], $sqlCondition['length'], $sqlCondition['order'], 'id');
    }


    /**
     * @param $oldData
     * @param $newData
     * @return bool
     */
    public function editOrderProduct($oldData, $newData)
    {
        $orderProductModel = new OrderproductModel();
        // 检查订单中有没有这个产品
        $map = [
            'order_id' => ['EQ', $newData['order_id']],
            'product_id' => ['EQ', $newData['product_id']],
        ];
        $productExists = $orderProductModel->where($map)->count() == 0 ? false : true;

        $data = [
            'old_product_id' => $oldData['product_id'],
            'new_product_id' => $newData['product_id'],
            'old_production_number' => $oldData['production_plan_number'],
            'new_production_number' => $newData['production_plan_number'],
        ];
        if (!$productExists) {
            $this->error = '更改产品不在订单中';
            return false;
        }
        $map = [
            'order_id' => ['EQ', $oldData['order_id']],
            'product_id' => ['EQ', $oldData['product_id']],
        ];
        $oldProduct = $orderProductModel->where($map)->select();
        // 检查是否有该产品的销货单生产计划
        $status = M('production_plan') -> where($map) -> count == 0 ? 0 : 2;
        foreach ($oldProduct as $key => $oldProductItem) {
            $oldProductItem['status'] = $status;
            $res = $orderProductModel->save($oldProductItem);
            if ($res === false) {
                $this->error = '保存失败';
                return false;
            }
        }

        // 新生产计划的新增
        $map = [
            'order_id' => ['EQ', $newData['order_id']],
            'product_id' => ['EQ', $newData['product_id']],
        ];
        $currentNumber = $data['new_production_number'];
        $newProduct = $orderProductModel->where($map)->select();
        while ($currentNumber > 0){
            foreach ($newProduct as $key => &$newProductItem) {
                if ($newProductItem['status'] == '1'){
                    continue;
                }
                $newProductItem['rest_number'] = $newProductItem['product_num'] - $newProductItem['produced_number'];
                if ($newProductItem['rest_number'] == $currentNumber){
                    $newProductItem['produced_number'] = $newProductItem['product_num'];
                    $newProductItem['status'] = '1';
                    $currentNumber = 0;
                    break;
                }elseif($newProductItem['rest_number'] > $currentNumber){
                    $newProductItem['produced_number'] += $currentNumber;
                    $currentNumber = 0;
                    break;
                }else{
                    $newProductItem['produced_number'] = $newProductItem['product_num'];
                    $newProductItem['status'] = '1';
                    $currentNumber -= $newProductItem['rest_number'];
                }
            }

            $allProductComplete = true;
            foreach ($newProduct as $key => $value12312) {
                if ($value12312['status'] != 1){
                    $allProductComplete = false;
                    break;
                }
            }
            if ($allProductComplete){
                $currentNumber = 0;
            }
        }
        foreach ($newProduct as $key => $value2) {
            $res = $orderProductModel -> save($newProduct[$key]);
            if ($res === false){
                return false;
            }
        }
        return true;
    }

    public function getOrderformDataById($id, $map = []){
        $map["o.is_del"] = ['eq', self::NO_DEL];
        $map["o.id"] = ['eq', $id];
        return $this->alias("o")
            ->field("o.*,l.logistics_type_name")
            ->join("left join crm_order_logistics_type l on l.type_id = o.logistics_type")
            ->where($map)
            ->find();
    }
}
