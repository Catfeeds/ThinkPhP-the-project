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

class PurchaseOrderProductModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus = 400;

    static protected $insert;
    static public $notDel = 0;
    static public $isDel  = 1;

    protected $_validate = array(
        array("order_pid","require","订单主键不能为空!"),
        array("product_id","require","物料表主键不能为空!"),
        array("product_no","require","物料编号不能为空!"),
        array("product_name","require","物料型号不能为空!"),
        array("product_number","require","物料名称不能为空!"),
        array("number","require","购买数量不能为空!"),
        array("single_price","require","购买单价不能为空!"),
        array("total_price","require","总价值不能为空!"),
        array("sort_id","require","排序编号不能为空!"),
        array("deliver_time","require","交货时间不能为空!"),
    );

    // 去除非表中字段
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
        // 数据重构
        $data = $this->getNewField($params);

        if (empty($params)) {
            return [-1, [], '没有提交新增数据'];
        }

        // 对数据进行验证非空验证
        if(empty($data['product_id']) || empty($data['product_no']) || empty($data['product_name']) || empty($data['product_number']) || empty($data['number']) || empty($data['single_price']) || empty($data['total_price']) || empty($data['sort_id'])){
            return [-2, [], "请将数据填写完成"];
        }

        if($data['deliver_time'] == 'NaN'){
            return [-2, [], "交货时间填写不规范或则未填写"];
        }

        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');
        $data['order_pid']    = session('orderPid');

        $data = $this->create($data);

        if(!$data){
            return [-2, [], $this->getError()];
        }else {
            return [0, $data, '数据实例化成功'];
        }
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
        $editedData['id']   = $oldData['id'];
        $editedData['order_pid'] = $oldData['order_pid'];
        $editedData['update_time']  = time();
        $editedData['update_id']    = session('staffId');
        return $editedData;
    }

    public function getEditData($params)
    {
        $data  = self::getNewField($params);
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
            $editData = $this->create($editData);
            if(!$editData){
                return[-2,[], $this->getError()];
            }
            return [0, $editData, '数据实例化成功'];
        }
    }

    public function addOrderProduct($postData)
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


    public function getProductWithPId($orderId)
    {
        $map['a.is_del'] = ['EQ', self::$notDel];
        $map['a.order_pid'] = ['EQ', $orderId];
        return $this->alias('a')->field('a.*')
            ->where($map)
            ->join('LEFT JOIN crm_purchase_order purchase ON purchase.id = a.order_pid')
            ->select();
    }

    public function editOrderProduct($postData)
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
              return dataReturn("ok", self::$successStatus);
        } catch (\Exception $exception) {
            return [$exception->getMessage(), self::$failStatus];
        }
    }

    /**
     * 获取订单物料的基本信息
     * @param $orderId
     * @return mixed
     */
    public function getAllMaterialBaseMsg($orderId){
        $map['p.is_del'] = ['EQ', self::$notDel];
        $map['p.order_pid'] = ['EQ', $orderId];
        $data = $this->alias('p')->where($map)->select();
        return $data;
    }

    /**
     * 获取一个订单中所有产品的信息  针对入库单获取物料信息！！
     */
    public function getAllMaterialMsg($orderId){
        $map['p.is_del'] = ['EQ', self::$notDel];
        $map['p.order_pid'] = ['EQ', $orderId];
        $field = "
            p.sort_id,
            p.id,
            p.product_id,
            p.single_price,
            p.total_price,
            p.number,
            crm_material.product_no,
            crm_material.product_name,
            crm_material.product_number,
            p.number allnum,
            ifnull(sum(st.num),0) as allinnum,
            (p.number-ifnull(sum(st.num),0)) as surplusnum, 
            crm_material.warehouse_id
        ";
        $data = $this->alias("p")
            ->field($field)
            ->join("LEFT JOIN crm_material on crm_material.product_id = p.product_id")
            ->join('LEFT JOIN crm_stock_in_purchase pur ON pur.source_id = p.order_pid')
            ->join('LEFT JOIN crm_stock_material st ON ((st.source_id = pur.id and p.product_id = st.product_id) and st.type = ' . StockMaterialModel::TYPE_STOCK_IN . ")")
            ->where($map)
            ->group("p.product_id")
            ->order("p.sort_id asc")
            ->select();
        return $data;
    }

    public function getPreMaterial($orderId)
    {
        $data = $this->getAllMaterialMsg($orderId);
        foreach ($data as &$datum) {
            $datum['default_rep_id'] = $datum['warehouse_id'];
            $datum['fail_rep_id'] = "K20";
            $datum['num'] = $datum['surplusnum'];
        }
        return $data;
    }

    /**
     * 判断当前添加物料数量是否超过订单中的数量
     * @param $postData  当前入库单物料信息
     * @param $orderId   订单
     * @param $stockId   入库单id  如果传了入库单主键，就标明是修改
     * @return bool;
     */
    public function validateMaterialNum($postData, $orderId, $stockId = ''){
        // 计数初始化
        $numArr = [];
        foreach ($postData as $key=>$item) {
            if (isset($numArr[$item['product_id']])) {
                $numArr[$item['product_id']]  += $item['num'];
            } else {
                $numArr[$item['product_id']]  = $item['num'];
            }
        }


        $productData = $this->getAllMaterialMsg($orderId);


        //如果存在入库单id，说需要对这个进行修改，这个时候需要查出当前入库单有多少，然后减去
        $materialKeyData = [];
        if(!empty($stockId)){
            $materialModel = new StockMaterialModel();
            $materialData = $materialModel->getAllMaterialMsg($stockId);
            if(!empty($materialData)){
                $materialKeyData = array_column($materialData, "allnum", "product_no");
            }
        }
        foreach($numArr as $k => $v) {
            foreach($productData as $index => $item) {
                if(!empty($materialKeyData) && !empty($materialData[$item['product_id']])){
                    if(isset($numArr[$item['product_id']]) && ($numArr[$item['product_id']] > ($item['surplusnum'] + $materialData[$item['product_id']]))){
                        $this->error = "编号为" . $v['product_no'] . "的产品数额大于订单所剩数额";
                        return false;
                    }
                }else {
                    if ($k == $item['product_id']) {
                        if ($v > $item['surplusnum']) {
                            $this->error = "物料编号为" . $item['product_no'] . "(" . M('material')->find($item['product_id'])['product_name']
                                . ")的入库数超过了采购单可入库数量，录入数"
                                . $v . ",该单据可入库数：" . $item['surplusnum'];
                            return false;
                        }
                    }
                }

            }
        }
        return true;
    }


    /**
     * 修改和新增后对订单物料表中已入库数量进行修改
     * @param $orderId   订单编号
     * @param $productNo 物料编号
     * @param $number   新增或修改后的数额
     * @param $materialId  入库单物料表主键  如果传了入库单物料表主键，就标明是修改
     * @return array
     */
    public function updateStockInNum($orderId, $productNo, $number, $materialId = ''){
        if(!empty($materialId)){
            $data = $this->find($materialId);
            $number = $number - $data['num'];
        }
        $map['order_pid'] = ['eq', $orderId];
        $map['is_del'] = self::$notDel;
        $map['product_no'] = $productNo;
        $result = $this->where($map)->select();
        foreach ($result as $ke => $va){
            if($number > ($va['number'] - $va['stock_in_number'])){
                $stockInNumber = $va['number'];
                $number = $number - ($va['number'] - $va['stock_in_number']);
                $return = $this->where(['id' => $va['id']])->setField(['stock_in_number' => $stockInNumber]);
                if(!$return){
                    return [$this->getError(), -1];
                }
            } else {
                $number = $va['stock_in_number'] + $number;
                if($number >= 0){
                    $return = $this->where(['id' => $va['id']])->setField(['stock_in_number' => $number]);
                    if(!$return){
                        return [$this->getError(), -2];
                    }
                    break;
                }else {
                    $return = $this->where(['id' => $va['id']])->setField(['stock_in_number' => 0]);
                    if(!$return){
                        return [$this->getError(), -3];
                    }
                }
            }
        }

        return ["修改成功", 0 ];
    }

    public function delProductAll($orderid){
        $res = $this->where(['is_del' => self::$notDel, 'order_pid' => $orderid])->setField(['is_del' => self::$isDel]);
        if(!$res){
            return [$this->getError(), -2];
        }
        return["删除成功", 0];
    }

    /**
     * 获取订单物料的采购信息
     * @param $id
     * @param array $map
     * @return mixed
     */
    public function getOrderProductMsgOne($id, $map = []){
        $productModel = new PurchaseOrderProductModel();
        $map['p.id'] = ['eq', $id];
        $map['p.is_del'] = ['eq', PurchaseOrderProductModel::$notDel];

        $data =  $productModel->alias("p")
            ->field("p.*, ifnull(sum(sm.num),0) as stock_in_num")
            ->join("left join crm_stock_in si on si.source_id = p.order_pid and si.audit_status = " . StockInModel::TYPE_STOCK_QUALIFIED)
            ->join("left join crm_stock_material sm on sm.source_id = si.id and p.product_id = sm.product_id and m.type = " . StockMaterialModel::TYPE_STOCK_IN)
            ->where($map)
            ->group("p.id")
            ->find();

        if($data['stock_in_num'] == $data['number']){
            $data['product_status'] = '入库完成';
        }else {
            $data['product_status'] = '未入库完成';
        }
        return $data;
    }
}