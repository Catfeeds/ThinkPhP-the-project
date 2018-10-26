<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/6/15
 * Time: 下午2:18
 */

namespace Dwin\Model;


use think\Exception;
use Think\Model;

class PurchaseOrderModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

    static protected $insert;
    static public $notDel = 0;
    static public $isDel  = 1;

    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_UNQUALIFIED = 1;     // 不合格
    const TYPE_QUALIFIED = 2;       // 合格

    const STOCK_PENDING = 0;
    const STOCK_DOING   = 1;
    const STOCK_DONE    = 2;

    public static $auditStatus = [
        self::TYPE_NOT_AUDIT => '未审核',
        self::TYPE_UNQUALIFIED => '不合格',
        self::TYPE_QUALIFIED => '合格',
    ];

    protected $_validate = array(
        array("contract_pid","require","合同编号不能为空!"),
        array("supplier_pid","require","供应商主键不能为空!"),
        array("purchase_order_id","require","订单编号不能为空!"),
        array("order_time","require","订单时间不能为空!"),
        array("receiver","require","收货人不能为空!"),
        array("trading_location","require","交货地点不能为空!"),
        array("receiving_phone","require","收货地点不能为空!"),
        array("billing_method","require","结算方式不能为空!"),
        array("demand_side","require","需方名称不能为空!"),
        array("demand_address","require","需方地址不能为空!"),
        array("purchaser_representative","require","需方法定代表不能为空!"),
        array("purchaser_phone","require","需方电话不能为空!"),
        array("supplier_name","require","供方名称不能为空!"),
        array("supply_address","require","供方地址不能为空!"),
        array("supplier_representative","require","供方法定代表不能为空!"),
        array("supplier_phone","require","供方电话不能为空!"),
        array("purchase_mode","require","采购模式不能为空!"),
        array("purchase_type","require","采购方式不能为空!"),
    );

    /**
     * 前台传送数据生成订单基本信息
     * @param $params
     * @return array
     */
    public function getAddData($params)
    {
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], '没有提交新增数据'];
        }

        // 对数据进行验证非空验证
        if(empty($data['contract_pid']) || empty($data['supplier_pid']) || empty($data['supplier_name']) || empty($data['purchase_order_id']) || empty($data['order_time']) || empty($data['receiver']) || empty($data['trading_location']) || empty($data['receiving_phone']) || empty($data['billing_method']) || empty($data['demand_side']) || empty($data['demand_address']) || empty($data['purchaser_representative']) || empty($data['purchaser_phone'])|| empty($data['supply_address'])|| empty($data['supplier_representative'])|| empty($data['supplier_phone'])|| empty($data['purchase_mode'])|| empty($data['purchase_type'])){
            return [-2, [], "请将数据填写完成"];
        }

        if($data['order_time'] == 'NaN'){
            return [-2, [], "订单时间填写不规范或则未填写"];
        }


        if(!empty($data['total_amount'])){
            list($code, $msg, $amount) = get_amount($data['total_amount']);
            if($code != self::$successStatus){
                $this->error = $msg;
                return false;
            }else {
                $data['capital_amount'] = $amount;
            }
        }else {
            $data['total_amount'] = 0;
            $data['total_amount'] = "零元";
        }

        // 数据重构
        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');

        $data = $this->create($data);

        if(!$data){
            return [-2, [], $this->getError()];
        }else {
            return [0, $data, '数据实例化成功'];
        }
    }

    // 字段重构
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

    private function compareData($oldData, $editedData)
    {
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

        $editedData['audit_status'] = self::TYPE_NOT_AUDIT;
        $editedData['id']           = $oldData['id'];
        $editedData['contract_id']  = $oldData['contract_id'];
        $editedData['supplier_pid'] = $oldData['supplier_pid'];
        $editedData['update_time']  = time();
        $editedData['update_id']    = session('staffId');
        return $editedData;
    }
    public function getEditData($params)
    {
        $data =self::getNewField($params);
        if (empty($data)) {
            return [-1, [], '无修改数据提交'];
        }

        if(isset($data['order_time']) && ($data['order_time'] == 'NaN' || empty($data['order_time']))){
            return [-2, [], "订单时间填写不规范或则未填写"];
        }


        if(!empty($data['total_amount'])){
            list($code, $msg, $amount) = get_amount($data['total_amount']);
            if($code != self::$successStatus){
                $this->error = $msg;
                return false;
            }
            $data['capital_amount'] = $amount;
        }else {
            $data['total_amount'] = 0;
            $data['total_amount'] = "零元";
        }

        $oldData = $this->field("*")->find($data['id']);

        if($oldData['audit_status'] == self::TYPE_QUALIFIED){
            return [-2, [], '当前订单已审核通过，不可修改'];
        }

        $editData = $this->compareData($oldData, $data);
        if (!$editData) {
            return [-1, [], '无数据修改'];
        } else {
            $data = $this->create($editData);
            if(!$data){
                return [-2, [], $this->getError()];
            }
            return [0, $data, '无修改数据提交'];
        }
    }


    public function getOrderWithId($orderId)
    {
        $map['crm_purchase_order.is_del'] = ['EQ', self::$notDel];
        $map['crm_purchase_order.id'] = ['eq', $orderId];
        return $this->field("crm_purchase_order.*,crm_staff.name")
            ->join("left join crm_staff on crm_staff.id = crm_purchase_order.create_id")
            ->where($map)->find();
    }

    public function getOrderData($orderId, $returnArr)
    {
        $returnArrSet = ['order', 'product'];
        $data = [];
        foreach ($returnArr as $key => $item) {
            if (in_array($item, $returnArrSet)) {
                switch ($item) {
                    case "order" :
                        $data[$item]  = $this->getOrderWithId($orderId);
                        break;
                    case "product" :
                        $contractProductModel = new PurchaseOrderProductModel();
                        $data[$item]  = $contractProductModel->getProductWithPId($orderId);
                        break;
                    default :
                        break;
                }
            }
        }
        return $data;
    }

    public function addOrder($data)
    {
        list($code, $addData, $msg) = $this->getAddData($data);
        if ($code != 0) {
            return [$msg, self::$failStatus];
        } else {
            $rst = $this->add($addData);
            if ($rst === false) {
                return [$this->getError(), self::$failStatus];

            } else {
                session('orderPid', $addData);
                return ["新增订单成功", self::$successStatus];
            }
        }

    }

    /**
     * 根据合同表直接创建订单
     * @param $id  订单ID
     * @param $contractId  合同表主键
     * @param $orderId     订单编号
     * @param $purchaseMode  采购模式
     * @param $purchaseType  采购方式
     * @return array
     */
    public function createOrderByContract($id, $contractId, $orderId, $purchaseMode, $purchaseType){
        $contractModel = new PurchaseContractModel();
        $contractData = $contractModel->find($contractId);
        $this->startTrans();
        $saveData = [
            'id' => $id,
            'contract_pid' => $contractId,
            'purchase_order_id' => $orderId,
            'create_time' => time(),
            'create_id' => session("staffId"),
            'update_time' => time(),
            'order_time' => time(),
            'update_id' => session("staffId"),
            'audit_status' => self::TYPE_NOT_AUDIT,
            'is_del' => self::$notDel,
            'supplier_pid' => $contractData['supplier_pid'],
            'supplier_name' => $contractData['supplier_name'],
            'total_amount' => $contractData['total_amount'],
            'capital_amount' => $contractData['capital_amount'],
            'note' => $contractData['note'],
            'receiver' => $contractData['receiver'],
            'trading_location' => $contractData['trading_location'],
            'receiving_phone' => $contractData['receiving_phone'],
            'billing_method' => $contractData['billing_method'],
            'supply_address' => $contractData['supply_address'],
            'supplier_representative' => $contractData['supplier_representative'],
            'supplier_phone' => $contractData['supplier_phone'],
            'supplier_fax' => $contractData['supplier_fax'],
            'demand_address' => $contractData['demand_address'],
            'purchaser_representative' => $contractData['purchaser_representative'],
            'purchaser_phone' => $contractData['purchaser_phone'],
            'purchaser_fax' => $contractData['purchaser_fax'],
            'purchase_mode' => $purchaseMode,
            'purchase_type' => $purchaseType
        ];

        $resData = $this->create($saveData);
        if(!$resData){
            return dataReturn($this->getError(), 400);
        }

        $res = $this->add($resData);
        if(!$res){
            $this->rollback();
            return dataReturn($this->getError(), 400);
        }

        $contractProductModel = new PurchaseContractProductModel();
        $contractProductData = $contractProductModel->where(['contract_pid' => $contractId, 'is_del' => PurchaseContractProductModel::$notDel])->select();

        $orderProductModel = new PurchaseOrderProductModel();
        $data = [];
        foreach ($contractProductData as $key => $item){
            $saveProductData = [
                'order_pid' => $id,
                'product_id' => $item['product_id'],
                'product_no' => $item['product_no'],
                'product_name' => $item['product_name'],
                'product_number' => $item['product_number'],
                'number' => $item['purchase_number'],
                'stock_in_number' => 0,
                'single_price' => $item['purchase_single_price'],
                'total_price' => $item['purchase_price'],
                'sort_id' => $item['sort_id'],
                'deliver_time' => $item['deliver_time'],
                'is_del' => self::$notDel,
                'create_time' => time(),
                'create_Id' => session("staffId"),
                'update_time' => time(),
                'update_id' => session("staffId"),
            ];
            $resData = $orderProductModel->create($saveProductData);
            if(!$resData){
                $this->rollback();
                return dataReturn($orderProductModel->getError(), 400);
            }

            $data[] = $resData;
        }

        $resOderProduct = $orderProductModel->addAll($data);
        if(!$resOderProduct){
            $this->rollback();
            return dataReturn($orderProductModel->getError(), 400);
        }

        $this->commit();
        return dataReturn("订单生成成功", 200);
    }

    /**
     * 订单详情生成
     * @param $postData
     * @return array
     */
    public function addOrderTrans($postData)
    {
        try {
            $this->startTrans();
            list($msg, $status) = $this->addOrder($postData['order']);
            if ($status == self::$failStatus) {
                $this->rollback();
                return dataReturn($msg, self::$failStatus);
            }
            $orderProductModel = new PurchaseOrderProductModel();
            list($msg, $status) = $orderProductModel->addOrderProduct($postData['product']);
            if ($status == self::$failStatus) {
                $this->rollback();
                return dataReturn($msg, $status);
            }
            $this->commit();
            return dataReturn('添加订单成功', self::$successStatus);
        } catch (\Exception $exception) {
            return dataReturn($exception->getMessage(), self::$failStatus);
        }
    }

    /**
     * 修改订单基本信息
     * @param $data
     * @return array
     */
    public function editOrder($data)
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

    public function editOrderTrans($postData)
    {
        try {
            $this->startTrans();
            $money = 0;

            $editCode = 0;
            $orderProductModel = new PurchaseOrderProductModel();
            if(!empty($postData['edit_product'])){
                $money += array_sum(array_column($postData['edit_product'], "total_price"));

                list($editMsg, $editCode) = $orderProductModel->editOrderProduct($postData['edit_product']);
                if ($editCode == -2) {
                    $this->rollback();
                    return dataReturn($editMsg, self::$failStatus);
                }
            }

            if (!empty($postData['new_product'])){
                $money += array_sum(array_column($postData['new_product'], "total_price"));

                session('orderPid', $postData['order']['id']);
                list($msg, $status) = $orderProductModel->addOrderProduct($postData['new_product']);
                if ($status == self::$failStatus) {
                    $this->rollback();
                    return dataReturn($msg, self::$failStatus);
                }
            }

            $postData['order']['total_amount'] = $money;
            list($msg, $code) = $this->editOrder($postData['order']);
            if ($code == -2) {
                $this->rollback();
                return dataReturn($msg, self::$failStatus);
            }
            if($code == -1 && ($editCode == -1 || empty($postData['edit_product'])) && empty($postData['new_product'])){
                return dataReturn('数据未发生修改', 400);
            }

            $this->where(['id' => $postData['order']['id']])->setField(["audit_status" => self::TYPE_NOT_AUDIT]);
            $this->commit();
            return dataReturn('ok', self::$successStatus);
        } catch (\Exception $exception) {
            return dataReturn($exception->getMessage(), self::$failStatus);
        }
    }

    /**
     * 删除订单
     */
    public function delOrder($orderId){
        try{
            $data = $this->where(['id' => $orderId, 'is_del' => self::$notDel])->field('audit_status')->find();
            if(empty($data)){
                return dataReturn('未找到有效订单数据', 402);
            }
            if($data['audit_status'] != self::TYPE_UNQUALIFIED){
                return dataReturn('非审核不通过的订单不可删除', 402);
            }

            $this->startTrans();
            $res = $this->where(['id' => $orderId])->setField(['is_del' => self::$isDel]);
            if(!$res){
                $this->rollback();
                return dataReturn('订单删除失败', 402);
            }

            $orderProductModel = new PurchaseOrderProductModel();
            list($msg, $code) = $orderProductModel->delProductAll($orderId);
            if($code != 0){
                $this->rollback();
                return dataReturn($msg, 402);
            }
            $this->commit();
            return dataReturn('订单删除成功', 200);
        }catch (Exception $exception) {

            return dataReturn($exception->getMessage(), self::$failStatus);
        }
    }

    /**
     * 删除订单中某个物料
     */
    public function delOrderProduct($orderId, $productId){
        $contractData = $this->getOrderWithId($orderId);
        if($contractData['audit_status'] == self::TYPE_QUALIFIED){
            return dataReturn("当前合同已审核，不能删除其物料信息", 400);
        }
        $orderProductModel = new PurchaseOrderProductModel();
        $productData = $orderProductModel->where(['id' => $productId])->find();
        if(!empty($productData['stock_in_number'])){
            return dataReturn("已入库数量不为0，不可删除", 400);
        }
        $totalAmount = $contractData['total_amount'] - $productData['total_price'];
        if(empty($totalAmount)){
            list($code, $msg, $amount) = get_amount($totalAmount);
            if($code != self::$successStatus){
                return dataReturn($msg, 400);
            }else {
                $capitalAmount = $amount;
            }
        }else {
            $totalAmount = 0;
            $capitalAmount = "零元";
        }


        $this->startTrans();
        $this->where(['id' => $orderId])->setField(['total_amount' => $totalAmount, 'capital_amount' => $capitalAmount]);
        $res = $orderProductModel->where(['id' => $productId])->setField(['is_del' => self::$isDel]);
        if(!$res){
            $this->rollback();
            return dataReturn($orderProductModel->getError(), 400);
        }
        $this->commit();
        return dataReturn("删除物料合同成功", 200, [
            'total_amount' => $totalAmount,
            'capital_amount' => $capitalAmount
        ]);
    }

    /**
     * 获取订单列表页信息
     */
    public function getList($condition, $start, $length, $order, $map= []){
        $map['o.is_del'] = ['eq', self::$notDel];
        $recordMap = $map;

        if(strlen($condition) != 0){
            $where['o.supplier_name'] = ['like', "%" . $condition . "%"];
            $where['o.purchase_order_id']=['like', "%" . $condition . "%"];
            $where['_logic'] = 'OR';
            $recordMap['_complex'] = $where;
        }
        $data =  $this->alias("o")
            ->field("o.*,s.name,c.contract_id,u.file_name, u.path")
            ->join("left join crm_staff s on s.id = o.create_id")
            ->join("left join crm_purchase_contract c on c.id = o.contract_pid")
            ->join("left join crm_file_upload u on u.id = c.file_id")
            ->limit($start, $length)
            ->where($recordMap)
            ->order($order)
            ->select();

        /** 后台传输局到前台
        @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
        $count = $this->alias("o")
            ->join("left join crm_staff s on s.id = o.create_id")
            ->where($map)
            ->count();
        $recordsFiltered = $this->alias("o")
            ->join("left join crm_staff s on s.id = o.create_id")
            ->where($recordMap)
            ->count();

        return [$data,$count,$recordsFiltered];
    }

    public function getDataWithSqlCondition($sqlCondition, $map =[], $alias) {
        $map["$alias.audit_status"] = ['eq', self::TYPE_QUALIFIED];
        $map["$alias.is_del"] = ['EQ' ,self::$notDel];
        $count = $this->alias($alias)
            ->join("LEFT JOIN crm_staff u ON u.id = $alias.create_id")
            ->join("LEFT JOIN crm_purchase_contract c ON c.id = $alias.contract_pid")
            ->where($map)
            ->count();
        $filterCount = $count;
        if (!empty(trim($sqlCondition['search']))) {
            $map["$alias.supplier_name|$alias.purchase_order_id|u.name|c.contract_id"] = ['LIKE', "%" . trim($sqlCondition['search'])];

            $filterCount = $this->alias($alias)
                ->join("LEFT JOIN crm_staff u ON u.id = $alias.create_id")
                ->join("LEFT JOIN crm_purchase_contract c ON c.id = $alias.contract_pid")
                ->where($map)
                ->count();
        }
        $field = "$alias.id,
                  $alias.supplier_name,
                  $alias.purchase_order_id,
                  from_unixtime($alias.create_time) create_time,
                  $alias.create_id,
                  u.name create_name,
                  from_unixtime($alias.update_time) update_time,
                  from_unixtime($alias.order_time) order_time,
                  $alias.billing_method,
                  $alias.purchase_mode,
                  $alias.purchase_type,
                  c.contract_id,
                  $alias.audit_status,
                  $alias.stock_status
              ";
        $data = $this->alias($alias)
            ->field($field)
            ->join("LEFT JOIN crm_staff u ON u.id = $alias.create_id")
            ->join("LEFT JOIN crm_purchase_contract c ON c.id = $alias.contract_pid")
            ->where($map)
            ->order($sqlCondition['order'])
            ->limit($sqlCondition['start'], $sqlCondition['length'])
            ->select();
        return [$count, $filterCount, $data];
    }


    public function resetStockStatus($orderId)
    {
        $purchaseProductModel = new PurchaseOrderProductModel();
        $materialData = $purchaseProductModel->getAllMaterialMsg($orderId);
        if (0 === $materialData['surplusnum']) {
            $data['stock_status'] = PurchaseOrderModel::STOCK_DONE;
        } elseif ($materialData['surplusnum'] == $materialData['allnum']) {
            $data['stock_status'] = PurchaseOrderModel::STOCK_PENDING;
        } else {
            $data['stock_status'] = PurchaseOrderModel::STOCK_DOING;
        }
        $data['id'] = $orderId;
        $map['id'] = ['eq', $orderId];
        $rst = $this->where($map)->setField($data);
        if ($rst === false) {
            $this->error = "修改false";
            return false;
        } else {
            return true;
        }

    }
}