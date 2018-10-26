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

class PurchaseSupplierModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

    static protected $insert;

    // 是否删除
    static protected $notDel = 0;  //未删除
    static protected $isDel = 1;   //已删除

    // 审核状态
    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_UNQUALIFIED = 1;     // 不合格
    const TYPE_QUALIFIED = 2;       // 部门合格
    const TYPE_MANAGER_QUALIFIED = 3; // 总经理合格


    // 是否上市
    const NO_LISTED = 0; //未上市
    const IS_LISTED = 1; //已上市

    // 企业性质
    const BELONG_TO_STATS       = 1;  //国有
    const BELONG_TO_PRIVATE     = 2;  //民营
    const BELONG_TO_TEAM        = 3;  //合资合作
    const BELONG_TO_FOREIGN     = 4;  //外资
    const BELONG_TO_GOVERNMENT  = 5;  //政府机构

    public static $enterpriseCateMap = [
        self::BELONG_TO_STATS        =>  '国有',
        self::BELONG_TO_PRIVATE      =>  '民营',
        self::BELONG_TO_TEAM         =>  '合资合作',
        self::BELONG_TO_FOREIGN      =>  '外资',
        self::BELONG_TO_GOVERNMENT   =>  '政府机构',
    ];

    public static $listedMap = [
        self::IS_LISTED => '已上市',
        self::NO_LISTED => '未上市'
    ];

    public static $auditTypeMap = [
        self::TYPE_NOT_AUDIT   => '未审核',
        self::TYPE_UNQUALIFIED => '不合格',
        self::TYPE_QUALIFIED   => '部门合格',
        self::TYPE_MANAGER_QUALIFIED   => '总经理合格',
    ];

    public static $supplierOtherMsg = [
        'base'          => '基本信息',
        'address'       => '地址信息',
        'contact'       => '联系人信息',
        'certification' => '资质认证信息',
        'awards'        => '获奖情况',
        'customer'      => '客户信息',
        'equity'        => '股权信息',
        'finance'       => '财务信息',
        'team'          => '团队信息',
        'cooperation'   => '合作情况信息',
        'audit'         => '质控评估状态',
    ];

    //数据验证
    protected $_validate = array(
        array("supplier_name","require","供应商名称不能为空!"),
        array("supplier_name","","供应商名称必须唯一!",1,"unique"),
        array("supplier_id","require","供应商编号不能为空!"),
        array("supplier_id","","供应商编号必须唯一!",1,"unique"),
        array("legal_name","require","法人代表不能为空!"),
        array("registered_capital","require","注册资本不能为空!"),
        array("paid_up_capital","require","实收资本不能为空!"),
        array("business_scope","require","营业范围不能为空!"),
        array("business_licence","require","营业执照号码不能为空!"),
        array("start_date","require","营业起止生效不能为空!"),
        array("end_date","require","营业起止失效时间不能为空!"),
        array("is_listed","require","是否上市不能为空!"),
        array("account_bank","require","开户行不能为空!"),
        array("account_number","require","开户账号不能为空!"),
        array("enterprise_cate","require","企业性质不能为空!"),
    );

    /**
     * 去除非此表的字段数据
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
     * 新增数据
     * @param $params
     * @return array|bool
     */
    public function getAddData($params)
    {
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], '没有提交新增数据'];
        }

        // 对数据进行验证非空验证
        if(empty($data['id']) || empty($data['supplier_name']) || empty($data['supplier_id']) || empty($data['legal_name']) || empty($data['registered_capital']) || empty($data['paid_up_capital']) || empty($data['business_scope']) || empty($data['business_licence']) || empty($data['start_date']) || empty($data['end_date']) || !isset($data['is_listed']) || empty($data['account_bank']) || empty($data['account_number']) || empty($data['enterprise_cate'])){
            return [-2, [], "请将数据填写完成"];
        }

        if($data['start_date'] == 'NaN' || $data['end_date'] == 'NaN' || ($data['start_date'] >= $data['end_date'])){
            return [-2, [], "营业起止时间填写不规范或则未填写"];
        }

        $data['supplier_name'] = preg_replace('# #','',$data['supplier_name']);
        $data['audit_status'] = self::TYPE_NOT_AUDIT;
        $data['create_time']  = time();
        $data['creater']    = session('staffId');
        $data['update_time']  = time();
        $data['updater']    = session('staffId');
        $data['charger'] = session("staffId");
        $data['charge_name'] = session("nickname");
        $data = self::checkStockCode($data);
        if($data === false){
            return [-2, [], '已上市的供应商必须填写股票代码'];
        }

        $data = $this->create($data);
        if (!$data) {
            return [-2, [], $this->getError()];
        }
        return [0, $data, '数据实例化成功'];
    }

    /**
     * 创建实例化之后的修改数据
     * @param $params
     * @return bool|mixed
     */
    public function getEditData($params)
    {
        if (empty($params)) {
            return [-1, [], '无修改数据提交'];
        }

        $oldData = $this->field("*")->find($params['id']);

        $data = $this->getNewField($params);

        if((isset($data['start_date']) && ($data['start_date'] == 'NaN' || empty($data['start_date']))) || (isset($data['end_date']) && ($data['end_date'] == 'NaN' || empty($data['end_date']))) || ($data['start_date'] >= $data['end_date'])){
            return [-2, [], "营业起止时间填写不规范或则未填写"];
        }

        $editedData = self::checkStockCode($data);
        if($editedData === false){
            return [-2, [], '已上市的供应商必须填写股票代码'];
        }

        $editData = $this->compareData($oldData, $editedData);

        if ($editData === false) {
            return [-3, [], '无数据修改'];
        } else {
            $createData = $this->create($editData);
            if(!$createData){
                return [-4, [], $this->getError()];
            }
            return [0, $createData, '数据实例化成功'];
        }
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
        $editedData['updater']    = session('staffId');
        return $editedData;
    }

    /**
     * 通过判断是否上市来判断股票代码的值
     */
    public function checkStockCode($editedData){
        if(isset($editedData['is_listed'])){
            if($editedData['is_listed'] == self::NO_LISTED){
                $editedData['stock_code'] = '';
            }else {
                if (empty($editedData['stock_code'])){
                    return false;
                }
            }
        }
        return $editedData;
    }

    /**
     * 添加供应商 全部信息
     * @param $addData
     * @return array
     */
    public function addSupplier($addData)
    {
        $this->startTrans();
        list($code, $supplier, $msg) = $this->getAddData($addData['supplierData']);
        if ($code != 0) {
            $this->rollback();
            return dataReturn($msg, self::$failStatus);
        }
        try {
            $baseAddRst = $this->add($supplier);
            if ($baseAddRst === false) {
                $this->rollback();
                $this->error = "添加失败后回滚，联系管理";
                return dataReturn($this->getError(), self::$failStatus);
            }else {
                // 将供应商的主键存入session中
                session('supplierPid', $supplier['id']);

                // 添加供应商成功后，往审核表中插入二级审核类别的记录
                $varibaleModel = new VariableTypeModel();
                $auditTypeData = $varibaleModel->getOneKindtype(VariableTypeModel::TYPE_SUPPLIER_AUDIT);
                $auditModel = new PurchaseSupplierAuditModel();
                list($auditCode, $auditMsg) = $auditModel->createSupplierAuditMsg($supplier['id'], $auditTypeData);
                if($auditCode === false){
                    $this->rollback();
                    return dataReturn($auditMsg, self::$failStatus);
                }
            }
        } catch (\Exception $exception) {
            $this->rollback();
            return dataReturn("请联系管理，报错信息：" . $exception->getMessage(), self::$failStatus);
        }

        $param = ['address', 'contact', 'certification', 'awards', 'customer', 'equity', 'finance', 'team', 'cooperation'];
        list($message, $status) = $this->addWithParam($addData, $param);
//        if (!empty($addData['addressData'])) {
//            $addressModel = new PurchaseSupplierAddressModel();
//            $addressRst = $addressModel->addAddress($addData['addressData']);
//            if ($addressRst['status'] == self::$fail_status) {
//                $this->rollback();
//                return $addressRst;
//            }
//        }
        if ($status === self::$failStatus) {
            $this->rollback();
            return dataReturn($message, self::$failStatus);
        } else {
            $this->commit();
            return dataReturn('添加完毕,部门审核后生效', self::$successStatus);
        }
    }

    /**
     * @param $addData 整个添加数据
     * @param $param  ['address', 'contact', 'certification', 'awards', 'customer', 'equity', 'finance', 'team', 'cooperation']
     * @return array
     * 添加供应商信息
     * 供应商表数据 supplierData
     * 供应商联系表 supplierContactData
     * 供应商地址表 addressData
     * 供应商资质认证 certificationData
     * 供应商获奖信息 awardsData
     * 供应商客户信息 customerData
     * 供应商公司股权结构 equityData
     * 供应商财务情况 financeData
     * 供应商团队情况 teamData
     * 供应商合作情况 cooperationData
     */
    protected function addWithParam($addData, $param)
    {
        for ($i = 0; $i < count($param); $i++) {
            if (!empty($addData[$param[$i] . 'Data'])) {
                list($msg, $status) = $this->addSupplierOtherMsgMany($param[$i], $addData[$param[$i] . 'Data']);
                if ($status == self::$failStatus){
                    return [$msg, self::$failStatus];
                }
            }
        }
        return ["新增成功", self::$successStatus];
    }

    public function checkValue($postData){
        $flag = true;
        foreach ($postData as $k => $item){
            if(!empty(array_filter(array_values($item)))){
                $flag = false;
            }
        }
        return $flag;
    }

    /**
     * @param $type  供应商信息类型
     * @param $postData 所要添加的数组 (多条数据， 二维数组)
     * @return array
     */
    public function addSupplierOtherMsgMany($type, $postData){
        switch ($type){
            case 'address':
                $modelName = new PurchaseSupplierAddressModel();
                if(count($postData) < 1){
                    return ["地址数据不能为空", self::$failStatus];
                }
                break;
            case 'contact':
                if(count($postData) < 1){
                    return ["地址数据不能为空", self::$failStatus];
                }
                $modelName = new PurchaseSupplierContactModel();
                break;
            case 'certification':
                $modelName = new PurchaseSupplierCertificationModel();
                break;
            case 'awards':
                $modelName = new PurchaseSupplierAwardsModel();
                break;
            case 'customer':
                $modelName = new PurchaseSupplierCustomerModel();
                break;
            case 'equity':
                $check = self::checkValue($postData);
                if($check){
                    return ["股权数据未添加", self::$successStatus];
                }
                $modelName = new PurchaseSupplierEquityModel();
                break;
            case 'finance':
                $check = self::checkValue($postData);
                if($check){
                    return ["近两年财务数据未添加", self::$successStatus];
                }
                $modelName = new PurchaseSupplierFinanceModel();
                break;
            case 'team':
                $check = self::checkValue($postData);
                if($check){
                    return ["团队数据未添加", self::$successStatus];
                }
                $modelName = new PurchaseSupplierTeamModel();
                break;
            case 'cooperation':
                $modelName = new PurchaseSupplierCooperationModel();
                break;
            default:
                return ['访问数据表出现错误', self::$failStatus];
        }
        try {
            $data = [];
            foreach ($postData as $k => $item){
                list($code, $res, $msg) = $modelName->getAddData($item);
                if ($code != 0) {
                    return [$msg, self::$failStatus];
                }
                $data[] = $res;
            }
            $rst = $modelName->addAll($data);
            if ($rst === false) {
                return [$modelName->getError(), self::$failStatus];
            } else {
                return ["ok", self::$successStatus];
            }
        } catch (\Exception $exception) {
            return [$exception->getMessage(), self::$failStatus];
        }
    }

    /**
     * 获取供应商基本信息
     * @param $id
     * @return array
     */
    public function getSupplierWithId($id)
    {
        $baseMap['is_del'] = ['EQ', self::$notDel];
        $baseMap['id'] = ['EQ', $id];
        $data = $this->field("*")->where($baseMap)->find();
        return $data;
    }

    /**
     * @param $supplierId
     * @param $returnArr
     * @return array
     * 获取供应商多个类型的数据
     */
    public function getSupplierData($supplierId, $returnArr)
    {
//        $returnArrSet = ['base', 'address', 'contact', 'certification', 'awards', 'customer', 'equity', 'finance', 'team', 'cooperation', 'audit'];
        $data = [];
        foreach ($returnArr as $key => $item) {
            if (in_array($item, array_keys(self::$supplierOtherMsg))) {
                $data[$item] = self::getSupplierOtherData($supplierId, $item);
            }
        }
        return $data;
    }

    /**
     * 获取供应商单个类型的数据
     * @param $supplierId
     * @param $type
     * @return array|mixed
     */
    public function getSupplierOtherData($supplierId, $type){
        switch ($type) {
            case "base" :
                $data  = $this->getSupplierWithId($supplierId);
                break;
            case "address" :
                $addressModel = new PurchaseSupplierAddressModel();
                $data  = $addressModel->getAddressWithPId($supplierId);
                break;
            case "contact" :
                $contactModel = new PurchaseSupplierContactModel();
                $data  = $contactModel->getContactWithPId($supplierId);
                break;
            case "certification" :
                $certificationModel = new PurchaseSupplierCertificationModel();
                $data = $certificationModel->getCertificationWithPId($supplierId);
                break;
            case "awards" :
                $awardsModel   = new PurchaseSupplierAwardsModel();
                $data   = $awardsModel->getAwardsWithPId($supplierId);
                break;
            case "customer" :
                $customerModel = new PurchaseSupplierCustomerModel();
                $data   = $customerModel->getCustomerWithPId($supplierId);
                break;
            case "equity" :
                $equityModel   = new PurchaseSupplierEquityModel();
                $data   = $equityModel->getEquityWithPId($supplierId);
                break;
            case "finance" :
                $financeModel  = new PurchaseSupplierFinanceModel();
                $data   = $financeModel->getFinanceWithPId($supplierId);
                break;
            case "team" :
                $teamModel     = new PurchaseSupplierTeamModel();
                $data   = $teamModel->getTeamWithPId($supplierId);
                break;
            case "cooperation" :
                $cooperationModel = new PurchaseSupplierCooperationModel();
                $data = $cooperationModel->getCooperationWithPId($supplierId);
                break;
            case "audit" :
                $auditModel = new PurchaseSupplierAuditModel();
                $data = $auditModel->getAuditWithPId($supplierId);
                break;
            default :
                $data = [];
                break;
        }
        return $data;
    }

    /**
     * 修改供应商相关多条数据
     * @param $postData 所要修改的数据 （多条数据， 二维数组）
     * @param $supplierId
     * @param $type
     * @return array|mixed
     */
    public function editSupplierMsgMany($postData, $type, $supplierId){
        switch ($type) {
            case "base" :
                $data  = $this->editSupplier($postData, $supplierId);
                return $data;
                break;
            case "address" :
                $model = new PurchaseSupplierAddressModel();
                break;
            case "contact" :
                $model = new PurchaseSupplierContactModel();
                break;
            case "certification" :
                $model = new PurchaseSupplierCertificationModel();
                break;
            case "awards" :
                $model   = new PurchaseSupplierAwardsModel();
                break;
            case "customer" :
                $model = new PurchaseSupplierCustomerModel();
                break;
            case "equity" :
                $model   = new PurchaseSupplierEquityModel();
                break;
            case "finance" :
                $model  = new PurchaseSupplierFinanceModel();
                break;
            case "team" :
                $model     = new PurchaseSupplierTeamModel();
                break;
            case "cooperation" :
                $model = new PurchaseSupplierCooperationModel();
                break;
            default :
                $data = ['修改数据类型未获取',self::$failStatus];
                break;
        }
        // 修改多条数据
        try {
            $returnRst = '';
            $msg = '修改成功';

            for($i = 0; $i < count($postData); $i++) {
                list($code, $data, $msg) = $model->getEditData($postData[$i]);
                if($code == 0){
                    $returnRst = self::$successStatus;
                    $saveRst = $model->save($data);
                    if ($saveRst === false) {
                        return dataReturn($model->getError(), self::$failStatus);
                        break;
                    }
                }

                if($code == -2){
                    return dataReturn($msg, self::$failStatus);
                    break;
                }
            }
            if(empty($returnRst)){
                return dataReturn($msg, self::$failStatus);
            }

            return dataReturn("数据修改成功", self::$successStatus);
        } catch (\Exception $exception) {
            return dataReturn($exception->getMessage(), self::$failStatus);
        }
    }

    /**
     * @param $id 删除当前数据的id
     * @param $type 删除当前数据的类型
     * @param $supplierPid 供应商id
     * @return array
     */
    public function delSupplierOtherMsg($id, $type, $supplierPid){
        switch ($type) {
            case "address" :
                $model = new PurchaseSupplierAddressModel();
                $addresData = $model->where(['supplier_pid' => $supplierPid, 'is_del' => self::$notDel])->select();
                if(count($addresData) == 1){
                    return dataReturn('地址信息不能为空',self::$failStatus);
                }
                break;
            case "contact" :
                $model = new PurchaseSupplierContactModel();
                $contactData = $model->where(['supplier_pid' => $supplierPid, 'is_del' => self::$notDel])->select();
                if(count($contactData) == 1){
                    return dataReturn('联系人信息不能为空',self::$failStatus);
                }
                break;
            case "certification" :
                $model = new PurchaseSupplierCertificationModel();
                break;
            case "awards" :
                $model   = new PurchaseSupplierAwardsModel();
                break;
            case "customer" :
                $model = new PurchaseSupplierCustomerModel();
                break;
            case "equity" :
                $model   = new PurchaseSupplierEquityModel();
                break;
            case "finance" :
                $model  = new PurchaseSupplierFinanceModel();
                break;
            case "team" :
                $model     = new PurchaseSupplierTeamModel();
                break;
            case "cooperation" :
                $model = new PurchaseSupplierCooperationModel();
                break;
            default :
                return dataReturn('修改数据类型未获取',self::$failStatus);
                break;
        }
        $this->startTrans();
        $res  = $model->where(['id' => $id])->setField(['is_del' => self::$isDel]);
        if($res === false){
            $this->rollback();
            return dataReturn('删除失败', self::$failStatus);
        }

        // 重置供应商审核状态
        list($res, $message) = self::resetAuditStatus($supplierPid);
        if(!$res){
            $this->rollback();
            return dataReturn($message, self::$failStatus);
        }

        $this->commit();
        return dataReturn('删除成功', self::$successStatus);
    }

    /**
     * 修改或添加单条供应商其他信息的数据
     * @param $postData  当前修改的数据   （单条数据， 一维数组）
     * @param $type      当前修改数据的类型
     * @param $supplierPid    供应商id
     * @return array
     */
    public function eidtOraddSupplierOtherOneMsg($postData, $type, $supplierPid){
        switch ($type) {
            case "address" :
                $model = new PurchaseSupplierAddressModel();
                break;
            case "contact" :
                $model = new PurchaseSupplierContactModel();
                break;
            case "certification" :
                $model = new PurchaseSupplierCertificationModel();
                break;
            case "awards" :
                $model   = new PurchaseSupplierAwardsModel();
                break;
            case "customer" :
                $model = new PurchaseSupplierCustomerModel();
                break;
            case "equity" :
                $model   = new PurchaseSupplierEquityModel();
                break;
            case "finance" :
                $model  = new PurchaseSupplierFinanceModel();
                break;
            case "team" :
                $model     = new PurchaseSupplierTeamModel();
                break;
            case "cooperation" :
                $model = new PurchaseSupplierCooperationModel();
                break;
            default :
                return dataReturn('修改数据类型未获取',self::$failStatus);
                break;
        }
        $this->startTrans();

        // 重置供应商审核状态
        list($res, $message) = self::resetAuditStatus($supplierPid);
        if($res === false){
            $this->rollback();
            return dataReturn($message, self::$failStatus);
        }

        if(empty($postData['id'])){
            session('supplierPid', $supplierPid);
            list($code, $data, $msg) = $model->getAddData($postData);
            if($code == 0){
                $addRes = $model->add($data);
                if($addRes === false){
                    $this->rollback();
                    return dataReturn($this->getError(), self::$failStatus);
                }

                $this->commit();
                return dataReturn('修改成功', self::$successStatus);
            }else {
                $this->rollback();
                return dataReturn($msg, self::$failStatus);
            }
        }else{
            list($code, $data, $msg)  = $model->getEditData($postData);
            if($code == 0){
                $saveRst = $model->save($data);
                if ($saveRst === false) {
                    $this->rollback();
                    return dataReturn($this->getError(), self::$failStatus);
                }
            }else {
                $this->rollback();
                $status = self::$failStatus;
                return dataReturn($msg, $status);
            }
            $this->commit();
            return dataReturn('修改成功', self::$successStatus);
        }


    }

    /**
     * 修改供应商基本信息
     * @param $param
     * @return array
     */
    public function editSupplier($param)
    {
        try {
            list($code, $supplier, $msg) = $this->getEditData($param);
            if ($code != 0) {
                return dataReturn($msg, self::$failStatus);
            }
            $baseAddRst = $this->save($supplier);
            if ($baseAddRst === false) {
                return dataReturn($this->getError(), self::$failStatus);
            }
            return dataReturn('修改成功', self::$successStatus);
        } catch (\Exception $exception) {
            return dataReturn("请联系管理，报错信息：" . $exception->getMessage(), self::$failStatus);
        }
    }

    /**
     * 供应商列表
     * @param $condition
     * @param $start
     * @param $length
     * @param $order
     * @param $type 1 => 待部门审核  2 => 待质控审核  3 => 审核完成 4 => 审核不合格 5 总经理审核
     * @param $map
     * @return array
     */
    public function getList($condition, $start, $length, $order, $type , $map = []){
        $map['p.is_del'] = ['eq', self::$notDel];

        switch ($type) {
            case 1 :
//                $having = 'group_concat(DISTINCT(a.status)) = '. PurchaseSupplierAuditModel::TYPE_NOT_AUDIT;
                $map['p.audit_status'] = ['eq', self::TYPE_NOT_AUDIT];
                break;
            case 2 :
//                $having = "group_concat(DISTINCT(a.status)) in ('0', '0,2' , '2,0')";
//                $having = 'group_concat(DISTINCT(a.status)) != '. PurchaseSupplierAuditModel::TYPE_QUALIFIED;
                $map['p.audit_status'] = ['eq', self::TYPE_QUALIFIED];
                break;
            case 3 :
                $having = "REPLACE(group_concat(DISTINCT(a.status)),',','') in ('02' , '20', '2')";
//                $having = 'group_concat(DISTINCT(a.status)) = '. PurchaseSupplierAuditModel::TYPE_QUALIFIED;
                $map['p.audit_status'] = ['eq', self::TYPE_MANAGER_QUALIFIED];
                break;
            case 4 :
                $having = [];
                $whereMap['a.status'] = ['eq', PurchaseSupplierAuditModel::TYPE_UNQUALIFIED];
                $whereMap['p.audit_status'] = ['eq', self::TYPE_UNQUALIFIED];
                $whereMap['_logic'] = 'OR';
                $map['_complex'] = $whereMap;
                break;
            case 5 :
                $having = "REPLACE(group_concat(DISTINCT(a.status)),',','') in ('02' , '20', '2')";
//                $having = 'group_concat(DISTINCT(a.status)) = '. PurchaseSupplierAuditModel::TYPE_QUALIFIED;
                $map['p.audit_status'] = ['eq', self::TYPE_QUALIFIED];
                break;
            default:
                $having = [];
                break;
        }

        $recordMap = $map;

        if(strlen($condition) != 0){
            $where['p.supplier_name'] = ['like', "%" . $condition . "%"];
            $where['p.supplier_id']=['like', "%" . $condition . "%"];
            $where['_logic'] = 'OR';
            $recordMap['_complex'] = $where;
        }

        $data =  $this->alias("p")
            ->field("p.*,crm_staff.name,(
                    CASE
                        WHEN REPLACE(group_concat(DISTINCT(a.status)),',','')  = '0' THEN 0 
                        WHEN REPLACE(group_concat(DISTINCT(a.status)),',','')  = '2' THEN 2 
                        WHEN REPLACE(group_concat(DISTINCT(a.status)),',','')  = '20' THEN 2 
                        WHEN REPLACE(group_concat(DISTINCT(a.status)),',','')  = '02' THEN 2 
                    ELSE 1
                    END 
                  ) AS second_audit,group_concat(DISTINCT(a.status))")
            ->join("left join crm_staff on crm_staff.id = p.creater")
            ->join("left join crm_purchase_supplier_audit a on a.supplier_pid = p.id")
            ->limit($start, $length)
            ->where($recordMap)
            ->group("a.supplier_pid")
            ->having($having)
            ->order($order)
            ->select();

        /** 后台传输局到前台
        @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
        $count = $this->alias("p")
            ->join("left join crm_staff on crm_staff.id = p.creater")
            ->join("left join crm_purchase_supplier_audit a on a.supplier_pid = p.id")
            ->where($map)
            ->group("a.supplier_pid")
            ->having($having)
            ->select();
        $recordsFiltered = $this->alias("p")
            ->join("left join crm_staff on crm_staff.id = p.creater")
            ->join("left join crm_purchase_supplier_audit a on a.supplier_pid = p.id")
            ->where($recordMap)
            ->group("a.supplier_pid")
            ->having($having)
            ->select();

        return [$data,count($count),count($recordsFiltered)];
    }

    /**
     * 重置供应商的审核状态为未审核
     * @param $supplierPid
     * @return array
     */
    public function resetAuditStatus($supplierPid){
        try{
            $this->where(['id' => $supplierPid])->setField(["audit_status" => self::TYPE_NOT_AUDIT]);

//            $auditModel = new PurchaseSupplierAuditModel();
//            $auditModel->where(['supplier_pid' => $supplierPid])->setField(["status" => self::TYPE_NOT_AUDIT]);
            return [true, "重置供应商状态成功"];
        }catch (\Exception $exception){
            return [false, $exception->getMessage()];
        }

    }

    /**
     * 获取负责人的名下的供应商列表信息
     * @param $condition
     * @param $start
     * @param $length
     * @param $order
     * @param $chargeId
     * @return array
     */
    public function getSupplierChargeList($condition, $start, $length, $order, $chargeId){
        if(empty($chargeId)){
            return [[], 0 ,0];
        }

        $map['p.is_del'] = ['eq', self::$notDel];
        $map['p.charger'] = ["eq", $chargeId];
        $recordMap = $map;

        if(strlen($condition) != 0){
            $where['p.supplier_name'] = ['like', "%" . $condition . "%"];
            $where['p.supplier_id']=['like', "%" . $condition . "%"];
            $where['_logic'] = 'OR';
            $recordMap['_complex'] = $where;
        }

        $data = $this->alias("p")
            ->field("p.*,(
                    CASE
                        WHEN REPLACE(group_concat(DISTINCT(a.status)),',','')  = '0' THEN 0 
                        WHEN REPLACE(group_concat(DISTINCT(a.status)),',','')  = '2' THEN 2 
                        WHEN REPLACE(group_concat(DISTINCT(a.status)),',','')  = '20' THEN 2 
                        WHEN REPLACE(group_concat(DISTINCT(a.status)),',','')  = '02' THEN 2 
                        ELSE 1
                    END 
                  ) AS second_audit")
            ->join("left join crm_purchase_supplier_audit a on a.supplier_pid = p.id")
            ->group("a.supplier_pid")
            ->where($recordMap)
            ->limit($start, $length)
            ->order($order)
            ->select();

        $count = $this->alias("p")
            ->where($map)
            ->count();
        $recordsFiltered = $this->alias("p")
            ->where($recordMap)
            ->count();

        return [$data, $count,$recordsFiltered];
    }
}