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

class PurchaseContractModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

    const TYPE_NOT_AUDIT = 0;      // 未审核
    const TYPE_UNQUALIFIED = 1;     // 不合格
    const TYPE_QUALIFIED = 2;       // 总经理审核合格
    const TYPE_LAY_QUALIFIED = 3;       // 法务审核合格


    const FILE_IS_UPLOAD = 1; // 文件已上传
    const FILE_NO_UPLOAD = 0; // 文件未上传

    public static $auditStatus = [
        self::TYPE_NOT_AUDIT => '未审核',
        self::TYPE_UNQUALIFIED => '不合格',
        self::TYPE_QUALIFIED => '总经理审核合格',
        self::TYPE_LAY_QUALIFIED => '法务审核合格',
    ];

    static protected $insert;
    static protected $notDel = 0;
    static protected $isDel  = 1;

    protected $_validate = array(
        array("contract_id","require","合同编号不能为空!"),
        array("contract_id","","合同编号必须唯一!",1,"unique"),
        array("signing_time","require","签订时间不能为空!"),
        array("signing_place","require","签订地点不能为空!"),
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
    );

    // 字段重构
    function getNewField($params){
        $fieldData = $this->getDbFields();
        $data = [];
        foreach ($fieldData as $key => $field){
            if(isset($params[$field])){
                $data[$field] = $params[$field];
            }
        }
        return $data;
    }

    public function getAddData($params)
    {
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], '没有提交新增数据'];
        }
        // 对数据进行验证非空验证
        if(empty($data['id']) || empty($data['contract_id']) || empty($data['signing_time']) || empty($data['signing_place']) || empty($data['receiver']) || empty($data['trading_location']) || empty($data['receiving_phone']) || empty($data['billing_method']) || empty($data['demand_side']) || empty($data['demand_address']) || empty($data['purchaser_representative']) || empty($data['purchaser_phone']) || empty($data['supplier_name']) || empty($data['supply_address'])|| empty($data['supplier_representative'])|| empty($data['supplier_phone'])){
            return [-2, [], "请将数据填写完成"];
        }

        if(!empty($data['total_amount'])){
            list($code, $msg, $amount) = get_amount($data['total_amount']);
            if($code != self::$successStatus){
                return [-1, [], $msg];
            }else {
                $data['capital_amount'] = $amount;
            }
        }else {
            $data['total_amount'] = 0;
            $data['total_amount'] = "零元";
        }

        if (!empty($data['file_id'])){
            $data['is_return_contract'] = self::FILE_IS_UPLOAD;
        }

        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');
        $data['charger']    = session('staffId');
        $data['charge_name']    = session('nickname');
        $data = $this->create($data);
        if ($data) {
            return [0, $data, '数据实例化成功'];
        } else {
            return [-2, [], $this->getError()];
        }
    }


    private function compareData($oldData, $editedData)
    {
        if(!empty($editedData['file_id'])){
            $editedData['is_return_contract'] = self::FILE_IS_UPLOAD;
        }else {
            unset($editedData['is_return_contract']);
        }

        // 先把不存在当前表里面的字段剔除，然后在与原先的数据做对比
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

        $editedData['audit_status'] == self::TYPE_NOT_AUDIT;
        $editedData['id']           = $oldData['id'];
        $editedData['contract_id']  = $oldData['contract_id'];
        $editedData['supplier_pid'] = $oldData['supplier_pid'];
        $editedData['update_time']  = time();
        $editedData['update_id']    = session('staffId');
        return $editedData;
    }
    public function getEditData($params)
    {
        if (empty($params)) {
            return [-1, [], "无修改数据提交"];
        }

        if(!empty($params['total_amount'])){
            list($code, $msg, $amount) = get_amount($params['total_amount']);
            if($code != self::$successStatus){
                return [-1, [], $msg];
            }
            $params['capital_amount'] = $amount;
        }else {
            $data['total_amount'] = 0;
            $data['total_amount'] = "零元";
        }

        $oldData = $this->field("*")->find($params['id']);

        if($oldData['audit_status'] == self::TYPE_QUALIFIED){
            return [-2, [], '当前合同已审核通过，不可修改'];
        }
        $editData = $this->compareData($oldData, $params);

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


    public function getContractWithId($contractId)
    {
        $map['crm_purchase_contract.is_del'] = ['EQ', self::$notDel];
        $map['crm_purchase_contract.id'] = ['eq', $contractId];
        return $this->field("crm_purchase_contract.*,crm_staff.name,crm_file_upload.file_name,crm_file_upload.path as file_url")
            ->join("left join crm_staff on crm_staff.id = crm_purchase_contract.create_id")
            ->join("left join crm_file_upload on crm_file_upload.id = crm_purchase_contract.file_id")
            ->where($map)->find();
    }

    public function getContractData($contractId, $returnArr)
    {
        $returnArrSet = ['contract', 'product'];
        $data = [];
        foreach ($returnArr as $key => $item) {
            if (in_array($item, $returnArrSet)) {
                switch ($item) {
                    case "contract" :
                        $data[$item]  = $this->getContractWithId($contractId);
                        break;
                    case "product" :
                        $contractProductModel = new PurchaseContractProductModel();
                        $data[$item]  = $contractProductModel->getProductWithPId($contractId);
                        break;
                    default :
                        break;
                }
            }
        }
        return $data;
    }

    /**
     * 添加合同基本信息
     * @param $data
     * @return array
     */
    public function addContract($data)
    {
        list($code, $addData, $msg) = $this->getAddData($data);
        if ($code != 0) {
            return [$msg, self::$failStatus];
        } else {
            $rst = $this->add($addData);
            if ($rst === false) {
                return [$this->getError(), self::$failStatus];
            } else {
                session('contractPid', $data['id']);
                return ["新增合同成功", self::$successStatus];
            }
        }
    }


    /**
     * 添加合同基本信息和合同内的产品
     * @param $postData
     * @return array
     */
    public function addContractTrans($postData)
    {
        try {
            $this->startTrans();
            list($msg, $status) = $this->addContract($postData['contract']);
            if ($status == self::$failStatus) {
                $this->rollback();
                return dataReturn($msg, self::$failStatus);
            }
            $contractProductModel = new PurchaseContractProductModel();
            list($message, $status) = $contractProductModel->addContractProduct($postData['product']);
            if ($status != self::$successStatus) {
                $this->rollback();
                return dataReturn($message, self::$failStatus);
            }
            $this->commit();
            return dataReturn('添加合同成功', self::$successStatus);
        } catch (\Exception $exception) {
            return dataReturn($exception->getMessage(), self::$failStatus);
        }
    }

    public function editContract($data)
    {
        try {
            list($code, $editData, $msg) = $this->getEditData($data);
            if ($code != 0) {
                return [$msg, $code];
            }
            $editRst = $this->save($editData);
            if ($editRst === false) {
                return [$this->getError(), -2];
            }
            return ["ok", 0];
        } catch (\Exception $exception) {
            return [$exception->getMessage(), -2];
        }
    }

    public function editContractTrans($postData)
    {
        try {
            $this->startTrans();
            $money = 0;

            $contractProductModel = new PurchaseContractProductModel();
            $editCode = 0;
            if(!empty($postData['edit_product'])){
                $money += array_sum(array_column($postData['edit_product'], "purchase_price"));

                list($editMsg, $editCode) = $contractProductModel->editContractProduct($postData['edit_product']);
                if ($editCode == -2) {
                    $this->rollback();
                    return dataReturn($editMsg, self::$failStatus);
                }
            }

            if (!empty($postData['new_product'])){
                $money += array_sum(array_column($postData['new_product'], "purchase_price"));

                session('contractPid', $postData['contract']['id']);
                list($msg, $status) = $contractProductModel->addContractProduct($postData['new_product']);
                if ($status != self::$successStatus) {
                    $this->rollback();
                    return dataReturn($msg, self::$failStatus);
                }
            }
            $postData['contract']['total_amount'] = $money;
            list($msg, $code) = $this->editContract($postData['contract']);
            if ($code == -2) {
                $this->rollback();
                return dataReturn($msg, self::$failStatus);
            }

            if($code == -1 && ($editCode == -1 || empty($postData['edit_product'])) && empty($postData['new_product'])){
                return dataReturn('数据未发生修改', 400);
            }

            $this->where(['id' => $postData['contract']['id']])->setField(["audit_status" => self::TYPE_NOT_AUDIT]);
            $this->commit();
            return dataReturn('合同修改成功', self::$successStatus);
        } catch (\Exception $exception) {
            return dataReturn($exception->getMessage(), self::$failStatus);
        }

    }

    /**
     * 判断时候可以
     * @param $id  合同表ID
     * @param $status  审核状态
     * @return array
     */
    public function validityCheck($id, $status)
    {
        $validityData = $this->field('audit_status,file_id')->where(['id' => $id])->find();

        if($status == self::TYPE_LAY_QUALIFIED){
            if (empty($validityData['file_id'])) {
                return dataReturn('回传合同附件还未上传至系统', self::$failStatus);
            }
        }

        if($validityData['audit_status'] == $status){
            return dataReturn('审核状态未发生改变，请重新再审核', self::$failStatus);
        }

        $res = $this->where(['id' => $id])->setField(['audit_status' => $status, 'update_id' => session("staffId"), "update_time" => time()]);
        if(!$res){
            return dataReturn($this->getError(), self::$failStatus);
        }
        return dataReturn('ok', self::$successStatus);
    }

    /**
     * 判断是否可以上传合同附件
     */
    public function checkUploadAuth($id){
        $contractData = $this->where(['id' => $id])->field('audit_status')->find();
        switch ($contractData['audit_status']){
            case self::TYPE_NOT_AUDIT :
                $code = self::$failStatus;
                $msg  = '总经理审核完成之后才可以上传附件';
                break;
            case self::TYPE_UNQUALIFIED :
                $code = self::$failStatus;
                $msg  = '总经理审核完成之后才可以上传附件';
                break;
            case self::TYPE_QUALIFIED :
                $code = self::$successStatus;
                $msg  = 'ok';
                break;
            case self::TYPE_LAY_QUALIFIED :
                $code = self::$failStatus;
                $msg  = '审核已完成，不可以修改合同附件';
                break;
            default:
                $code = self::$failStatus;
                $msg  = '总经理审核完成之后才可以上传附件';
                break;
        }
        return [$code, $msg];
    }

    /**
     * 获取合同列表页信息
     */
    public function getList($condition, $start, $length, $order, $map = []){
        $map['c.is_del'] = ['eq', self::$notDel];
        $recordMap = $map;
        if(strlen($condition) != 0){
            $where['c.contract_id'] = ['like', "%" . $condition . "%"];
            $where['c.supplier_name']=['like', "%" . $condition . "%"];
            $where['_logic'] = 'OR';
            $recordMap['_complex'] = $where;
        }

        $data =  $this->alias("c")
            ->field("c.*,s.name,u.file_name,u.path as file_url,IF(IFNULL(count( o.id ),0),1 ,0) AS order_status ")
            ->join("left join crm_staff s on s.id = c.create_id")
            ->join("left join crm_file_upload u on u.id = c.file_id")
            ->join("left join crm_purchase_order o on o.contract_pid = c.id and o.is_del = " . PurchaseOrderModel::$notDel)
            ->limit($start, $length)
            ->where($recordMap)
            ->group("c.id")
            ->order($order)
            ->select();

        /** 后台传输局到前台
        @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
        $count = $this->alias("c")
            ->join("left join crm_staff s on s.id = c.create_id")
            ->join("left join crm_file_upload u on u.id = c.file_id")
//            ->join("left join crm_purchase_order o on o.contract_pid = c.id and o.is_del = " . PurchaseOrderModel::$notDel)
            ->where($map)
            ->count();
        $recordsFiltered = $this->alias("c")
            ->join("left join crm_staff s on s.id = c.create_id")
            ->join("left join crm_file_upload u on u.id = c.file_id")
//            ->join("left join crm_purchase_order o on o.contract_pid = c.id and o.is_del = " . PurchaseOrderModel::$notDel)
            ->where($recordMap)
            ->count();

        return [$data,$count,$recordsFiltered];
    }

    /**
     * 删除合同中一个物料
     * @param $contractId
     * @param $productId
     * @return array
     */
    public function delContractProduct($contractId, $productId){
        $contractData = $this->getContractWithId($contractId);
        if($contractData['audit_status'] == self::TYPE_QUALIFIED || $contractData['audit_status'] == self::TYPE_LAY_QUALIFIED){
            return dataReturn("当前合同已审核合格，不能删除其物料信息", 400);
        }
        $contractDataNum = $this->where(['id' => $contractId,'is_del' => self::$notDel])->count();
        if($contractDataNum > 1){
            return dataReturn("当前合同物料只剩下一个，不能删除其物料信息",400);
        }
        $this->startTrans();
        $contractProductModel = new PurchaseContractProductModel();
        $res = $contractProductModel->where(['id' => $productId])->setField(['is_del' => self::$isDel]);
        if($res === false){
            $this->rollback();
            return dataReturn($contractProductModel->getError(), 400);
        }
        $productData = $contractProductModel->where(['id' => $productId])->find();
        $totalAmount = $contractData['total_amount'] - $productData['purchase_price'];
        if(!empty($totalAmount)){
            list($code, $msg, $amount) = get_amount($totalAmount);
            if($code != self::$successStatus){
                $this->rollback();
                return dataReturn($msg, 400);
            }else {
                $capitalAmount = $amount;
            }
        }else {
            $totalAmount = 0;
            $capitalAmount = "零元";
        }

        $this->where(['id' => $contractId])->setField(['total_amount' => $totalAmount, 'capital_amount' => $capitalAmount]);
        $this->commit();
        return dataReturn("删除物料合同成功", 200, [
            'total_amount' => $totalAmount,
            'capital_amount' => $capitalAmount
        ]);
    }

    /**
     * 删除一个合同
     * @param $contractId
     * @return array
     */
    public function delContract($contractId){
        $contractData = $this->getContractWithId($contractId);
        if($contractData['audit_status'] != PurchaseContractModel::TYPE_UNQUALIFIED){
            return dataReturn("只可以删除审核状态为不合格的合同", 400);
        }
        $this->startTrans();
        $contractRes = $this->where(['id' => $contractId])->setField(['is_del' => self::$isDel]);
        if($contractRes === false){
            $this->rollback();
            return dataReturn($this->getError(), 400);
        }
        $contractProductModel = new PurchaseContractProductModel();
        $res = $contractProductModel->where(['contract_pid' => $contractId, 'is_del' => self::$notDel])->setField(['is_del' => self::$isDel]);
        if($res === false){
            $this->rollback();
            return dataReturn($contractProductModel->getError(), 400);
        }
        $this->commit();
        return dataReturn("删除成功", 200);
    }
}