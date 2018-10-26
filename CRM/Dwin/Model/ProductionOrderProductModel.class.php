<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/9/21
 * Time: 上午9:02
 */

namespace Dwin\Model;
use think\Exception;
use Think\Model;

class ProductionOrderProductModel extends Model{
    const NO_DEL = 0;
    const IS_DEL = 1;
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
        if(empty($data['order_pid']) || empty($data['product_id']) || empty($data['num'])){
            return [-2, [], "请将数据填写完成"];
        }

        $data['push_num'] = 0;
        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['create_name'] = session("nickname");

        $data = $this->create($data);
        if (!$data) {
            return [-2, [], $this->getError()];
        }
        return [0, $data, '数据实例化成功'];
    }

    /**
     * 重构生产计划下bom物料组成
     * @param $orderId
     * @param $postData
     * @return array
     */
    public function createBom($orderId, $postData){
        $data = [];
        foreach ($postData as $k => $v){
            $v['order_pid'] = $orderId;
            list($code, $oneData, $msg) = self::getAddData($v);
            if($code != 0){
                return dataReturn($msg, 400);
            }
            $data[] = $oneData;
        }

        $check = $this->where(['is_del' => self::NO_DEL, 'order_pid' => $orderId])->select();
        if(!empty($check)){
            return dataReturn('已下推BOM实际组成物料', 400);
        }

        $this->startTrans();
        $res = $this->addAll($data);
        if ($res === false){
            $this->rollback();
            return dataReturn($this->getError(),400);
        }

        $orderModel = new ProductionOrderModel();
        $orderRes = $orderModel->where(['id' => $orderId])->setField(['audit_status' => ProductionOrderModel::TYPE_NOT_AUDIT]);
        if($orderRes === false){
            $this->rollback();
            return dataReturn($orderModel->getError(),400);
        }
        $this->commit();
        return dataReturn("重构bom物料成功",200);
    }



    public function getProductionProductById($id, $map = []){
        if (is_array($id)){
            $map['p.order_pid'] = ['in', implode(',', array_filter($id))];
        }else {
            $map['p.order_pid'] = ['eq', $id];
        }
        $map['p.is_del'] = ['eq', self::NO_DEL];

        $data = $this->alias("p")
            ->field("p.*, m.warehouse_id")
            ->join("left join crm_material m on m.product_id = p.product_id")
            ->where($map)
            ->select();
        return $data;
    }

    public function getProductionProductByIdArr($id, $map = []){
        $map['p.order_pid'] = ['in', implode(',', array_filter($id))];
        $field = "p.id,
                  p.order_pid,
                  p.product_id,
                  p.num,p.push_num,
                  p.create_id,
                  p.create_time,
                  p.order_pid, 
                  m.warehouse_id,
                  m.product_no,
                  m.product_name,
                  m.product_number,
                  cpo.production_code,
                  cpo.product_no produce_name,
                  ifnull(stock.stock_number,0) stock_number";
        $data = $this->alias("p")
            ->field($field)
            ->join("left join crm_material m on m.product_id = p.product_id")
            ->join("left join crm_production_order cpo on cpo.id = p.order_pid")
            ->join("LEFT JOIN crm_stock stock on m.warehouse_id = stock.warehouse_number and p.product_id = stock.product_id")
            ->where($map)
            ->select();
        return $data;
    }
}