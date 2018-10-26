<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/3/15
 * Time: 下午1:59
 */

namespace Dwin\Model;


use Think\Model;

class OrderproductModel extends Model
{

    public function getOrderProductAddData($postsData, $orderId)
    {
        $productData = array();
        foreach($postsData as $val) {
            $productData[] = [
                'order_id'            => $orderId,
                'product_name'        => $val['productName'],
                'product_type'        => $val['productType'],
                'product_id'          => (int)$val['productId'],
                'product_price'       => $val['productSinglePrice'],
                'product_num'         => $val['productNum'],
                'product_total_price' => $val['productTotalPrice']
            ];
        }
        return $productData;
    }

    public function getOrderProductSettleData($field, $where)
    {
        return $data = $this->field($field)
            ->where($where)
            ->join('LEFT JOIN 
                              crm_order_collection c 
                        ON
                              c.cus_order_id = crm_orderproduct.order_id
                        AND 
                              c.product_id = crm_orderproduct.product_id')
            ->group('crm_orderproduct.product_id,,crm_orderproduct.product_price')
            ->select();
    }

    /**
     * 根据orderform id 获取订单中产品信息
     * @param $order_id
     * @return  array
     */
    public function getItemByOrderID($order_id)
    {
        $map = [
            'order_id' => ['EQ', $order_id]
        ];

        $field = "
            product.product_id,
            product.product_name,
            order_product.product_num,
            sum(stock.stock_number) stock_number,
            order_product.produced_number,
            order_product.status";
        $data = $this
             -> alias('order_product')
             -> field($field)
             -> join("left join crm_stock as stock on order_product.product_id = stock.product_id")
             -> join("left join crm_material as product on order_product.product_id = product.product_id")
             -> where($map)
             ->group('stock.product_id')
             -> select();
        $productionPlanModel = new ProductionPlanModel();
        foreach ($data as $key => $value) {
            $map['product_id'] = ['EQ', $value['product_id']];
            $data[$key]['producing_number'] = $productionPlanModel -> getAllProductionNumber($map);
        }
        return $data;
    }

    /**
     * @param $data     array   多个生产计划
     * @param $order_id     int
     * @param int $status
     * @return bool
     */
    public function editStatusWhenAddProductionPlan($data, $order_id, $status = 2)
    {
        $this->startTrans();
        $map = [
            'order_id' => ['EQ', $order_id]
        ];
        foreach ($data as $key => $value) {
            $map['product_id'] = ['EQ', $value['product_id']];
            $res = $this->where($map)->save(['status' => $status]);
            if ($res === false){
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }

    /**
     * 根据订单id,获取当前库存数量 锁库数量及 出库中数量。
    */
    public function getOrderProductData($filter, $field, $start = 0, $length = 100, $order = 'order_id asc')
    {
         $productData = $this->where($filter)
            ->field($field)
            ->join("LEFT JOIN crm_stock product ON product.product_id = crm_orderproduct.product_id and product.warehouse_number = 'K004'")
            ->limit($start, $length)
            ->order($order)
            ->select();
        return $productData;
    }

    /*
     * @todo 测试改id带来的影响
     */
    public function getStockOutUpdData($stockLog)
    {
//        $orderProductFilter['product_name'] = ['EQ', $stockLog['product_name']];
        $orderProductFilter['product_id'] = ['EQ', $stockLog['product_id']];
        $orderProductFilter['order_id']     = ['EQ', (int)str_replace('CPO',"", $stockLog['action_order_number'])];
        return $this->field('id,product_name,stock_out_uncheck_num,stock_out_num')->where($orderProductFilter)->select();
    }

    /**
     * @param array $updateData 审核的出库数据
     * @param array $stockLog 对应出库单据号的数量、型号、仓库
     * @param array $orderProductData 产品表读取的一条对应订单号的产品数据
    */
    public function getUpdDataWithStockAuditData($updateData, $stockLog, $orderProductData)
    {

        if ($updateData['audit_status'] == 2) {
            if ($stockLog['num'] >= $orderProductData['stock_out_uncheck_num']) {
                $orderProductData['stock_out_num'] += $orderProductData['stock_out_uncheck_num'];
                $stockLog['num'] -= $orderProductData['stock_out_uncheck_num'];
                $orderProductData['stock_out_uncheck_num'] = 0;
                $orderProductData['pro_stock_status'] = 2;
            } else {
                $orderProductData['stock_out_num'] += $stockLog['num'];
                $orderProductData['stock_out_uncheck_num'] -= $stockLog['num'];
                $stockLog['num'] = 0;
            }
        } else {
            if ($stockLog['num'] >= $orderProductData['stock_out_uncheck_num']) {
                $stockLog['num'] -= $orderProductData['stock_out_uncheck_num'];
                $orderProductData['stock_out_uncheck_num'] = 0;
                $orderProductData['pro_stock_status'] = 1;
            } else {
                $orderProductData['stock_out_uncheck_num'] -= $stockLog['num'];
                $stockLog['num'] = 0;
            }
        }
        return $orderProductData;
    }

    public function rollbackWithStock($stockLog)
    {
        $filter['order_id'] = ['eq', str_replace($stockLog['action_order_number'], '', 'CPO')];
        $filter['product_id'] = ['eq', $stockLog['product_id']];
        $productData = $this->getStockOutUpdData($stockLog);
        $proNum = count($productData);
        if ($stockLog['audit_status'] == 1) {
            for($i = 0; $i < $proNum; $i++) {

                if ($stockLog['num'] >= $productData[$i]['stock_out_uncheck_num']) {
                    $productData[$i]['stock_out_uncheck_num'] = 0;
                    $stockLog['num'] -= $productData[$i]['stock_out_uncheck_num'];
                } else {
                    $productData[$i]['stock_out_uncheck_num'] -= $stockLog['num'];
                    $stockLog['num'] = 0;
                }
                $updRst[$i] = $this->save($productData[$i]);
                $updRst[$i] = $updRst[$i] === false ? false : true;
            }
        } elseif ($stockLog['audit_status'] == 2) {
            for($i = 0; $i < $proNum; $i++) {
                if ($stockLog['num'] >= $productData[$i]['stock_out_num']) {
                    $productData[$i]['stock_out_num'] = 0;
                    $stockLog['num'] -= $productData[$i]['stock_out_num'];
                } else {
                    $productData[$i]['stock_out_num'] -= $stockLog['num'];
                    $stockLog['num'] = 0;
                }
                $updRst[$i] = $this->save($productData[$i]);
                $updRst[$i] = $updRst[$i] === false ? false : true;
            }
        } else {
            $updRst = [true,true];
        }

        return $updRst;

    }

    public function updateDataWithStockAddData($orderProUpdFilter, $num)
    {
        $orderProUpdData['pro_stock_status']      = 1;
        $orderProUpdData['stock_out_uncheck_num'] = ['exp', "stock_out_uncheck_num + {$num}"];
        return $orderProUpdRst = $this->where($orderProUpdFilter)->setField($orderProUpdData);
    }

    /**
     * 下销货单影响库存，添加产品事务
    */
    public function addProductTrans($productData)
    {
        $productModel = new StockModel();
        $updRst = [];
        $updFlag = [];
        foreach ($productData as $key => $val) {
            $filter['product_id']       = ['EQ', $val['product_id']];
            $filter['warehouse_number'] = ['EQ', 'K004'];
            $updRst[$key] = $productModel->updateWithFlag('addOrder', $filter, $val['product_num']);
            $updFlag[$key] = $updRst[$key] === false ? false : true;
        }
        $addRst = $this->addAll($productData);
        return $rst = ['add' => $addRst, 'upd' => $updFlag];
    }

    public function delProductTrans($map)
    {
        $productModel = new StockModel();
        $updRst = [];
        $updFlag = [];
        $productData = $this->where($map)->field('product_id, product_num')->select();
        foreach ($productData as $key => $val) {
            $filter['product_id']       = ['EQ', $val['product_id']];
            $filter['warehouse_number'] = ['EQ', 'K004'];
            $updRst[$key] = $productModel->updateWithFlag('rejectOrder', $filter, $val['product_num']);
            $updFlag[$key] = $updRst[$key] === false ? false : true;
        }

        $delRst = $this->where($map)->delete();

        return $rst = ['del' => $delRst, 'upd' => $updFlag];
    }

    /**
     * 获取当前订单下所有物料信息  针对出库单！！！
     * @param $orderId
     * @param $where
     * @return mixed
     */
    public function getOrderProductMsgById($orderId, $where = []){
        $map['p.order_id'] = ['eq', $orderId];
        $where['so.source_id'] = ['eq', $orderId];
        $where['so.is_del'] = ['eq', StockOutOrderformModel::NO_DEL];

        // 获取当前销售单下物料对应的已下推数量
        $orderFormModel = new StockOutOrderformModel();
        $sql = $orderFormModel->alias("so")
            ->field("IFNULL( SUM( r.num ), 0 ) AS number,r.product_id")
            ->join("left join crm_stock_out_record r ON r.is_del = " . StockOutRecordModel::NO_DEL . " AND r.source_id = so.id ")
            ->where($where)
            ->group("r.product_id")
            ->select(false);

        $data = $this->alias("p")
            ->field("p.id,p.order_id,p.product_id,m.product_name,p.product_type,m.product_number, product_num, ifnull(y.number,0) as used_num, m.product_no, cs.warehouse_number as rep_pid,cs.stock_number,cs.o_audit,cs.out_processing")
            ->join("left join crm_material m on m.product_id = p.product_id")
//            ->join("left join crm_stock_out_orderform so on p.order_id = so.source_id and so.is_del = " . StockOutOrderformModel::NO_DEL)
            ->join("left join ( " . $sql . ") y on y.product_id = p.product_id")
//            ->join("left join crm_stock_material sm on sm.source_id = so.id and sm.is_del = " . StockMaterialModel::NO_DEL . " and sm.type = " . StockMaterialModel::TYPE_STOCK_OUT)
            ->join("left join crm_stock cs on cs.product_id = p.product_id and cs.warehouse_number = 'K004'")
            ->where($map)
            ->group("p.product_id")
            ->select();
        return $data;
    }

    /**
     * 获取当前订单下所有物料信息 并且查出其入库完成和未入库完成数据
     * @param $orderId
     * @param $map
     * @return mixed
     */
    public function getOrderProductAuditMsgById($orderId, $map = []){

        $auditSql = "SELECT
                    IFNULL( SUM( r.num ), 0 ) AS number,
                    r.product_id
                FROM
                    crm_stock_out_orderform o
                    LEFT JOIN crm_stock_out_record r ON r.is_del = " . StockOutRecordModel::NO_DEL . " 
                    AND r.source_id = o.id 
                WHERE
                    o.source_id = '$orderId' 
                    AND o.is_del = " . StockOutOrderformModel::NO_DEL ." 
                    AND o.audit_status = " . StockOutOrderformModel::TYPE_STOCK_QUALIFIED . "
                    GROUP BY r.product_id";
        $noAuditSqlsql = "SELECT
                    IFNULL( SUM( r.num ), 0 ) AS number,
                    r.product_id
                FROM
                    crm_stock_out_orderform o
                    LEFT JOIN crm_stock_out_record r ON r.is_del = " . StockOutRecordModel::NO_DEL . " 
                    AND r.source_id = o.id 
                WHERE
                    o.source_id = '$orderId' 
                    AND o.is_del = " . StockOutOrderformModel::NO_DEL ." 
                    AND o.audit_status != " . StockOutOrderformModel::TYPE_STOCK_QUALIFIED . "
                    GROUP BY r.product_id";
        $map['p.order_id'] = ['eq', $orderId];
        $data = $this->alias("p")
            ->field("p.id,p.order_id,p.product_price,p.product_total_price,p.product_id,m.product_name,p.product_type,m.product_number, sum(p.product_num) as product_num, ifnull(y.number,0) as audit_num, ifnull(n.number,0) as no_audit_num, m.product_no, 
                (case
                    when p.product_num = y.number then 2
                    when y.number = 0 and n.number = 0 then 0
                    else 1
                end) as audit_status                
                ")
            ->join("left join crm_material m on m.product_id = p.product_id")
            ->join("left join ( " . $auditSql . ") y on y.product_id = p.product_id")
            ->join("left join ( " . $noAuditSqlsql . ") n on n.product_id = p.product_id")
            ->where($map)
            ->group("p.product_id")
            ->select();
        return $data;
    }

    public function getList($condition, $start, $length, $order){
        $map['co.is_del'] = ['eq', OrderformModel::NO_DEL];
        $map['co.stock_status'] = ['neq', OrderformModel::TYPE_OUT_ALL];
        $map['co.order_type'] = ['in', '1,2,3,4,6'];
        $recordMap = $map;
        if(strlen($condition) != 0){
            $where['cp.product_name'] = ['like', "%" . $condition . "%"];
//            $where['_logic'] = 'OR';
            $recordMap['_complex'] = $where;
        }

        $data =  $this->alias("cp")
            ->field("cp.*,cm.product_no,co.order_type,co.stock_status,co.cpo_id,co.pic_name,co.settlement_method,(cp.product_num - cp.stock_out_uncheck_num - cp.stock_out_num) as num")
            ->join("left join crm_orderform co on co.id = cp.order_id")
            ->join("left join crm_material cm on cm.product_id = cp.product_id")
            ->limit($start, $length)
            ->where($recordMap)
            ->order($order)
            ->select();
        /** 后台传输局到前台
        @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
        $count = $this->alias("cp")
            ->join("crm_orderform co on co.id = cp.order_id")
            ->where($map)
            ->count();
        $recordsFiltered = $this->alias("cp")
            ->join("crm_orderform co on co.id = cp.order_id")
            ->where($recordMap)
            ->count();

        return [$data,$count, $recordsFiltered];
    }
}