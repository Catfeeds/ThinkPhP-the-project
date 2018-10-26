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

class PurchaseContractProductModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

    static protected $insert;
    static public $notDel = 0;
    static public $isDel  = 1;

    protected $_validate = array(
        array("contract_pid","require","供应商合同主键不能为空!"),
        array("product_id","require","物料表主键不能为空!"),
        array("product_no","require","物料编号不能为空!"),
        array("product_name","require","物料型号不能为空!"),
        array("product_number","require","物料名称不能为空!"),
        array("purchase_number","require","购买数量不能为空!"),
        array("purchase_single_price","require","物料单价不能为空!"),
        array("purchase_price","require","物料总金额不能为空!"),
        array("sort_id","require","排序编号不能为空!"),
        array("unit","require","单位不能为空!"),
        array("deliver_time","require","交货时间不能为空!"),
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

    public function getAddData($params)
    {
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], '没有提交新增数据'];
        }

        // 对数据进行验证非空验证
        if(empty($data['product_id']) || empty($data['product_no']) || empty($data['product_name']) || empty($data['product_number']) || empty($data['purchase_number']) || empty($data['purchase_single_price']) || empty($data['purchase_price']) || empty($data['sort_id']) || empty($data['unit']) || empty($data['deliver_time'])){
            return [-2, [], "请将数据填写完成"];
        }

        if($data['deliver_time'] == 'NaN'){
            return [-2, [], "交货时间填写不规范或则未填写"];
        }

        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');
        $data['contract_pid'] = session('contractPid');

        $data = $this->create($data);
        if(!$data){
            return [-2, [], $this->getError()];
        }else {
            return [0, $data, '数据实例化成功'];
        }
    }


    private function compareData($oldData, $editedData)
    {
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

        $editedData['id']   = $oldData['id'];
        $editedData['update_time']  = time();
        $editedData['update_id']    = session('staffId');
        return $editedData;
    }
    public function getEditData($params)
    {
        $data = self::getNewField($params);
        if (empty($data)) {
            return [-1, [], "无修改数据提交"];
        }

        if(isset($data['deliver_time']) && ($data['deliver_time'] == 'NaN' || empty($data['deliver_time']))){
            return [-2, [], "交货时间填写不规范或则未填写"];
        }

        $oldData = $this->field("*")->find($data['id']);
        $editData = $this->compareData($oldData, $data);
        if ($editData === false) {
            return [-1, [], '无数据修改'];
        } else {
            $createData = $this->create($editData);
            if(!$createData){
                return[-2,[], $this->getError()];
            }
            return [0, $editData, '数据实例化成功'];
        }
    }

    public function addContractProduct($postData)
    {
        try {
            $data = [];
            for($i = 0; $i < count($postData); $i++) {
                list($code, $res, $msg) = $this->getAddData($postData[$i]);
                if ($code != 0) {
                    return [$msg, self::$failStatus];
                }
                $data[$i] = $res;
            }
            $rst = $this->addAll($data);
            if ($rst === false) {
                return [$this->getError(), self::$failStatus];
            } else {
                return ["ok", self::$successStatus];
            }
        } catch (Exception $exception) {
            return [$exception->getMessage(), self::$failStatus];
        }
    }

    /**
     * 查询物料基本信息
     * @param $contractPid
     * @return mixed
     */
    public function getProductWithPId($contractPid)
    {
        $map['is_del'] = ['EQ', self::$notDel];
        $map['contract_pid'] = ['EQ', $contractPid];
        return $this->field("crm_purchase_contract_product.*,crm_staff.name")
            ->join("left join crm_staff on crm_staff.id = crm_purchase_contract_product.create_id")
            ->where($map)->select();
    }

    public function editContractProduct($postData)
    {
        try {
            $returnRst = '';
            $msg = "ok";

            for($i = 0; $i < count($postData); $i++) {
                list($code, $data, $msg) = $this->getEditData($postData[$i]);
                if($code == 0){
                    $returnRst = self::$successStatus;
                    $saveRst = $this->save($data);
                    if ($saveRst === false) {
                        return [$this->getError(), -2];
                        break;
                    }
                }

                if($code == -2){
                    return [$msg, -2];
                    break;
                }
            }
            if(empty($returnRst)){
                return [$msg, -1];
            }

            return ["修改合同成功", 0];
        } catch (\Exception $exception) {
            return [$exception->getMessage(), -2];
        }
    }

    /**
     * 删除产品
     */
    public function delContractProduct($postData){
        try{
            $this->where(['id' => $postData['id']])->setField(['is_del' => self::$notDel]);
            return dataReturn("ok", self::$successStatus);
        }catch (Exception $exception) {
            return dataReturn($exception->getMessage(), self::$failStatus);
        }
    }

}