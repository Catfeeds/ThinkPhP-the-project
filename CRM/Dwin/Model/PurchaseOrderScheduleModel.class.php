<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/8/15
 * Time: 上午10:08
 */

namespace Dwin\Model;


use Think\Exception;
use Think\Model;
class PurchaseOrderScheduleModel extends model{
    const IS_DEL = 1;
    const NO_DEL = 0;

    const TYPE_IS_SEND = 1; // 已发货
    const TYPE_NO_SEND = 2; // 未发货

    public static $sendMap = [
        self::TYPE_IS_SEND => '已发货',
        self::TYPE_NO_SEND => '未发货'
    ];

    /**
     * create by  chendd 去除非表中字段
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
        $editedData['update_id']    = session('staffId');
        return $editedData;
    }

    public function getAddData($orderId, $orderProductId, $params){
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], '没有提交新增数据'];
        }

        // 判空处理
        if(empty($data['estimated_arrive_time']) || empty($data['estimated_arrive_num']) || empty($data['is_send'])){
            return [-2, [], '请将数据填写完整'];
        }
        $data['source_id'] = $orderId;
        $data['source_pid'] = $orderProductId;
        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['create_name']  = session('nickname');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');
        return [0, $data, "数据过滤成功"];
    }

    public function createScheduleMany($orderId,$orderProductId,$scheduleData){
        $data = [];
        foreach ($scheduleData as $key => $value){
            list($code, $scheduleOneData, $msg) = self::getAddData($orderId, $orderProductId, $value);
            if($code != 0){
                return dataReturn($msg,400);
                break;
            }
            $data[] = $scheduleOneData;
        }

        $res = $this->addAll($data);
        if(!$res){
            return dataReturn($this->getError(), 400);
        }
        return dataReturn("新增采购进度记录成功", 200);
    }

    public function editData($params)
    {
        if (empty($params)) {
            return [- 1, "无修改数据提交"];
        }

        $oldData = $this->field("*")->find($params['id']);


        $editData = $this->compareData($oldData, $params);

        if ($editData === false) {
            return [- 1, '无数据修改'];
        } else {
            $data = $this->save($editData);
            if (!$data) {
                return [- 2, $this->getError()];
            }
            return [0, '修改物料采购进度数据成功'];
        }
    }

    /**
     * 物流订单列表
     * @param $condition
     * @param $start
     * @param $length
     * @param $map
     * @param $stockMap
     * @return array
     */
    public function getList($condition, $start, $length, $stockMap, $map = []){
        $map['o.is_del'] = ['eq', PurchaseOrderModel::$notDel];
        if(strlen($condition) != 0){
            $where['p.product_no'] = ['like', "%" . $condition . "%"];
//            $where['p.product_name'] = ['like', "%" . $condition . "%"];
            $where['p.product_number'] = ['like', "%" . $condition . "%"];
            $where['_logic'] = 'OR';
            $map['_complex'] = $where;
        }

        $orderModel = new PurchaseOrderModel();

        $data = $orderModel->alias("o")
            ->field("p.id, p.order_pid, o.create_id,s.name, o.create_time,p.product_no,p.product_id,product_number,p.number,p.deliver_time, ifnull(sum(sm.num),0) as stock_in_num")
            ->join("crm_purchase_order_product p on p.order_pid = o.id and p.is_del = " . PurchaseOrderProductModel::$notDel)
            ->join("left join crm_stock_in_purchase si on si.source_id = p.order_pid and si.is_del = " . StockInPurchaseModel::NO_DEL)
            ->join("left join crm_stock_material sm on sm.source_id = si.id and p.product_id = sm.product_id and sm.type = " . StockMaterialModel::TYPE_STOCK_IN . " and sm.is_del = " . StockMaterialModel::NO_DEL)
            ->join("left join crm_staff s on s.id = o.create_id")
            ->limit($start, $length)
            ->where($map)
            ->order("p.update_time desc")
            ->group("p.product_id")
            ->having($stockMap)
            ->select();

        $countData = $orderModel->alias("o")
            ->field("p.id, o.create_time,p.product_no,p.product_id,product_number,p.number,p.deliver_time, ifnull(sum(sm.num),0) as stock_in_num")
            ->join("crm_purchase_order_product p on p.order_pid = o.id and p.is_del = " . PurchaseOrderProductModel::$notDel)
            ->join("left join crm_stock_in_purchase si on si.source_id = p.order_pid and si.is_del = " . StockInPurchaseModel::NO_DEL)
            ->join("left join crm_stock_material sm on sm.source_id = si.id and p.product_id = sm.product_id and sm.type = " . StockMaterialModel::TYPE_STOCK_IN . " and sm.is_del = " . StockMaterialModel::NO_DEL)
            ->where($map)
            ->group("p.product_id")
            ->having($stockMap)
            ->select();
        $count = count($countData);

        // 获取订单下方物料表的主键
        $productIdArr = array_column($data, 'id');
        $scheduleData = [];
        if(!empty($productIdArr)){
            $scheduleData = self::getOrderScheduleMsg($productIdArr);
        }

        foreach ($data as $key => &$value){
            if($value['stock_in_num'] == $value['number']){
                $value['product_status'] = '入库完成';
            }else {
                $value['product_status'] = '未入库完成';
            }
            $value['no_arrive_num'] = $value['number'] - $value['stock_in_num'];  // 未到货数量
            $value['schedule_data'] = [];

            foreach ($scheduleData as $k => $v){
                if($v['source_pid'] == $value['id']){
                    $value['schedule_data'][] = $v;
                }
            }
        }
        unset($value);

        return [$data,$count];
    }


    /**
     * 根据crm_order_product 的 ID 获取物料的采购进度
     * @param $id
     * @param $map
     * @param int $type  1 => $id为数据  2 => $id为数字
     * @return mixed
     */
    public function getOrderScheduleMsg($id, $type = 1, $map = []){
        if($type == 1){
            $map['s.source_pid'] = ['in', $id];
        }else {
            $map['s.source_pid'] = ['eq', $id];
        }
        $map['s.is_del'] = ['eq', self::NO_DEL];
        $scheduleData = $this->alias('s')
            ->field("*")
            ->where($map)
            ->select();
        return $scheduleData;
    }
}