<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/17
 * Time: 上午11:56
 */

namespace Dwin\Model;


use Think\Model;

class StockAuditOutModel extends Model
{
    /* 审核状态：1 待审 2 通过 3 不通过*/
    const AUDIT_PENDING  = '1';
    const AUDIT_PASS     = '2';
    const AUDIT_FAIL     = '3';
    /* type 库存记录类别：1入库 2 出库*/
    const OUT_TYPE = '2';

    /* 返工出库 */
    const REWORK_CATE = 7;

    const SUCCESS_STATUS = 200;
    const FAIL_STATUS    = 404;
    const FORBIDDEN_STATUS = 403;

    public function index($field, $map = [], $start = '0', $length = '10', $order = '')
    {
        return $this
            -> alias('audit')
            -> field($field)
            -> join('left join crm_staff as proposer on audit.proposer = proposer.id')
            -> join('left join crm_staff as auditor on audit.auditor = auditor.id')
            -> where($map)
            -> limit($start,$length)
            -> order($order)
            -> select();
    }
    /**
     * 比对订单产品表待出库数量和出库记录表中数量
     * 返回不同型号的产品的出库数量
     * @params array $productBasicData 产品表数据：包括product_name,product_num,stock_out_uncheck_num,stock_out_num
     * @param  array $stockOutData 产品出库记录：包括 product_name,product_num,stock_out_uncheck_num,stock_out_num
     * @return array $stockOutData 多传一个can_insert_num
     */
    protected function compareStockData($productBasicData, $stockOutData)
    {
        if (count($stockOutData) != 0) {
            for ($i = 0; $i < count($productBasicData); $i++) {
                for ($j = 0; $j < count($stockOutData); $j++) {
                    if ($productBasicData[$i]['product_id'] == $stockOutData[$j]['product_id']) {
                        $stockOutData[$j]['can_insert_num'] = $productBasicData[$i]['product_num'] - $stockOutData[$j]['stock_out_uncheck_num'] - $stockOutData[$j]['stock_out_num'];
                    }
                }
            }
        } else {
            for ($i = 0; $i < count($productBasicData); $i++) {
                $stockOutData[$i]['product_id'] = $productBasicData[$i]['product_id'];
                $stockOutData[$i]['product_name'] = $productBasicData[$i]['product_name'];
                $stockOutData[$i]['stock_out_uncheck_num'] = 0;
                $stockOutData[$i]['stock_out_num'] = 0;
                $stockOutData[$i]['can_insert_num'] = $productBasicData[$i]['product_num'];
            }
        }

        return $stockOutData;
    }

    /**
     * public getStockRemainingDataByOrderId
     * 根据订单表单据编号，1获取出库记录中已经审核通过的和待审核的记录，根据product_name group
     *                  2获取订单产品表中出库数量和未出库数量
     *                  3调用compareStockData方法比对数据
     *                  4返回该订单的产品数据（可入库数量、已入库数量等）
     * @param string $id orderform's cpo_id
     * @param array $stockData order's stockData which contain stock_out_num,can_inset_num etc
     *
     */
    public function getStockRemainingDataByOrderId($id)
    {
        $stockFilter['action_order_number'] = array('EQ', $id);
        $field = "product_id,
                  product_name,
                  sum(case when audit_status = '1' then num else 0 end) stock_out_uncheck_num,
                  sum(case when audit_status = '2' then num else 0 end) stock_out_num";
        $stockData = $this->alias('audit')
            -> field($field)
            -> where($stockFilter)
            -> group('product_id')
            -> select();
        $orderProductLimit['order_id'] = ['EQ', str_replace('CPO', '', $id)];
        $this->field = "product_id,
                        product_name,
                        sum(product_num) product_num,
                        sum(ifnull(stock_out_uncheck_num, 0)) stock_out_uncheck_num,
                        sum(ifnull(stock_out_num, 0)) stock_out_num";
        $productBasicData = M('orderproduct')->where($orderProductLimit)->field($this->field)->group('product_name')->select();
        $stockData = $this->compareStockData($productBasicData, $stockData);
        return $stockData;
    }

    public function getStockAdd($postsData) {
        $stockData = array();
        for ($i = 0; $i < count($postsData); $i++) {
            $condition[$i]['product_id'] = array('EQ', $postsData[$i]['product_id']);
            $this->field = "product_id,product_name,product_number,product_no";
            $productData[$i] = M('industrial_seral_screen')->where($condition[$i])->field($this->field)->find();
            $stockData[$i] = array(
                'product_id'         => $postsData[$i]['product_id'],
                'product_number'     => $productData[$i]['product_number'],
                'product_no'         => $productData[$i]['product_no'],
                'product_name'       => $productData[$i]['product_name'],
                'num'                => (int)$postsData[$i]['num'],
                'type'               => self::OUT_TYPE,
                'tips'               => $postsData[$i]['tips'],
                'audit_status'       => self::AUDIT_PENDING,
                'proposer'           => session('staffId'),
                'proposer_name'      => session('nickname'),
                'auditor'            => getStringId($postsData[$i]['auditor']),
                'auditor_name'       => getStringChar($postsData[$i]['auditor']),
                'warehouse_name'     => getStringChar($postsData[$i]['warehouseArr']),
                'warehouse_number'   => getStringId($postsData[$i]['warehouseArr']),
                'update_time'        => time(),
                'create_time'        => time(),
                'cate_name'          => getStringChar($postsData[$i]['cateArr']),
                'cate'               => getStringId($postsData[$i]['cateArr']),
                'action_order_number'=> $postsData[$i]['action_order_number'],
                'express_number'     => empty($postsData[$i]['deliverNum']) ? "无单号" : $postsData[$i]['deliverNum'],
            );
        }
        return $stockData;
    }
    /**
     * @param array $postsData 提交的入库信息
     * @param array $stockBasicData 订单对应已入库、入库中、未入库信息
     * @return array $returnData 返回可以添加的数据、不能添加的数据以及原因。
     */
    public function getStockAddData($postsData, $stockBasicData)
    {

        $unAddMsg  = "";
        $addData   = array();
        $unAddData = array();
        $stockData = $this->getStockAdd($postsData);
        $stockData = array_values($stockData);
        //return $data = ['1'=> $stockData, '2' => $stockBasicData];
        for ($m = 0; $m < count($stockData); $m++) {
            $stockData[$m]['insert_flag'] = true;
            for ($n = 0; $n < count($stockBasicData); $n++) {
                if ($stockData[$m]['product_id'] == $stockBasicData[$n]['product_id']) {
                    $stockData[$m]['insert_flag'] = ($stockData[$m]['num'] <= $stockBasicData[$n]['can_insert_num']) ? true : false;
                    // 防止同一型号有多个，造成非法提交：can_insert = 2; $stockData[0]['num'] = 2; $stockData[1]['num'] = 2;防止key=1的数据提交。
                    $stockBasicData[$n]['can_insert_num'] -= $stockData[$m]['num'];
                }
            }
        }
        $field = "rep_id,repertory_name, warehouse_manager_id";
        for ($i = 0; $i < count($stockData); $i++) {
            if ($stockData[$i]['insert_flag']) {
                //@todo 后续可能改为物流员。现在是库管员
                $map['rep_id'] = ['eq', $stockData[$i]['warehouse_number']];
                $data = M('repertorylist')->where($map)->field($field)->find();
                if (in_array($stockData[$i]['proposer'], explode(',', $data['warehouse_manager_id']))) {
                    if ($stockData[$i]['num']) {
                        $addData[] = $stockData[$i];
                    }
                } else {
                    $unAddData[] = $stockData[$i];
                    $unAddMsg .= $stockData[$i]['product_name'] . "的出库信息未添加，当前提交人非" . $stockData[$i]['warehouse_name'] . "库管<br>";
                }
            } else {
                $unAddData[] = $stockData[$i];
                $unAddMsg .= $stockData[$i]['product_name'] . "的出库信息未添加，出库数量：" . $stockData[$i]['num'] . "超出了可出库数量 " . "<br>";
            }
        }
        $returnData = array(
            'addData'   => $addData,
            'unAddData' => $unAddData,
            'unAddMsg'  => $unAddMsg
        );
        return $returnData;
    }

    /**
     * 1 Submit material outbound records(multi-)
     * 2 Update The number of pending approvals data in orderproduct
     * 3 check orderform status and update
     *
     */
    public function insertStockOutData($addData)
    {
        $stockModel        = new StockModel();
        $orderProductModel = new OrderproductModel();
        $stockLogModel     = new StockLogModel();
        $stockRecord = [];
        M()->startTrans();
        $insertStockOutRecordRst = $this->addAll($addData);
        if ($insertStockOutRecordRst === false) {
            M()->rollback();
            return false;
        }

        for ($i = 0; $i < count($addData); $i++) {

            $productFilter[$i] = array(
                'product_id'     => ['EQ', $addData[$i]['product_id']],
                'warehouse_number' => ['EQ', $addData[$i]['warehouse_number']]
            );

            if (!empty($addData[$i]['action_order_number'])) {
                $orderProUpdFilter[$i] = array(
                    'product_id' => ['EQ', $addData[$i]['product_id']],
                    'order_id'   => ['EQ', str_replace("CPO", "", $addData[$i]['action_order_number'])]
                );
                $orderProUpdRst[$i] = $orderProductModel->updateDataWithStockAddData($orderProUpdFilter[$i], $addData[$i]['num']);
                if ($orderProUpdRst[$i] === false) {
                    M()->rollback();
                    return false;
                }
                $orderUpdData['stock_status'] = 1;
                $orderUpdFilter['cpo_id'] = array('EQ', $addData[$i]['action_order_number']);
                $orderUpdRst = M()->table('crm_orderform')->where($orderUpdFilter)->save($orderUpdData);
                if ($orderUpdRst === false) {
                    M()->rollback();
                    return false;
                }
                $flag = "addStockOut";
            } else {
                $flag = 'addStockOutWithOutOrder';
            }

            $updateRst[$i] = $stockModel->updateWithFlag($flag, $productFilter[$i], $addData[$i]['num']);
            if ($updateRst[$i] === false) {
                M()->rollback();
                return false;
            }
            $stockRecord[$i] = $stockLogModel->getAddData('出库记录提交，提交人：' . session('nickname'), '提交出库');
        }
        $recordRst = $stockLogModel->addAll($stockRecord);
        if ($recordRst === false) {
            M()->rollback();
            return false;
        }
        M()->commit();
        return true;
    }

    public function getAuthority($authData, $staffId)
    {
        $pendingIds = getPrjIds($authData,'id');
        $condition['crm_stock_audit_out.id'] = ['IN', $pendingIds];
        $field = "audit_order_number,audit_status,auditor,type";
        $checkData = $this->getStockData($field,$condition,0,100, 'action_order_number');
        foreach($checkData as $key => $val) {
            if ($val['audit_status'] == self::AUDIT_PASS) {
                return $msg = [
                    'status'=> 400,
                    'msg'   => $val["audit_order_number"] . "，该单已经审核通过，禁止重复审核，中断了本次提交过程"
                ];
            }
            if ($val['auditor'] != $staffId) {
                return $msg = [
                    'status'=> 400,
                    'msg'   => $val['audit_order_number'] . ", 该单审核人非当前登录用户，无权审核，中断了本次审核过程"
                ];
            }
            if ($val['type'] != self::OUT_TYPE) {
                return $msg = [
                    'status'=> 400,
                    'msg'   => $val['audit_order_number'] . ", 该单据非出库单，无权审核，中断了本次审核过程"
                ];
            }
        }
        return $msg = [
            'status' => 200,
            'msg'    => "有权审核"
        ];
    }

    /**
     * @param array $data post 提交的数据
     * @return array $updateData stock_audit表审核数据（批量，通过或者驳回）
     */
    public function getStockUpdateData($data)
    {
        $updateData = array();
        foreach ($data['auditData'] as $index => $item) {
            $updateData[$index]['id'] = (int)$item['id'];
            $updateData[$index]['audit_status'] = ($data['auditFlag'] == 1) ? self::AUDIT_PASS : self::AUDIT_FAIL;
            $updateData[$index]['audit_tips'] = $item['audit_tips'];
        }
        return $updateData;
    }

    public function getStockOutData($filter)
    {
        $map['audit_status'] = ['IN', self::AUDIT_PENDING];
        return $stockOutData = $this->where($filter)
            ->field('from_unixtime(update_time) update_time, express_number,audit_status,product_name,audit_order_number,action_order_number,warehouse_name,auditor_name,proposer_name,num')
            ->select();
    }

    public function getStockData($field, $map, $start, $length, $order)
    {
        return $this->field($field)
            ->where($map)
            ->join('left join crm_staff as proposer on proposer = proposer.id')
            ->join('left join crm_staff as auditor on auditor = auditor.id')
            ->limit($start, $length)
            ->order($order)
            ->select();
    }

    public function getOneStockLog($map,$field,$order='id',$group = 'id')
    {
        return $this->where($map)->field($field)->order($order)->group($group)->find();
    }

    /**
     * 审核入库记录
     * @param array $updateData
     * @return array $msg 返回结果
     *
     */
    public function checkStockOutData($updateData)
    {
        /**
         * 更新入库表
         * 更新产品采购表
         * 更新订单表
         * 更新库存表
         */
        $stockModel        = new StockModel();
        $orderProductModel = new OrderproductModel();
        $stockLogModel     = new StockLogModel();
        $stockRecord = [];
        M()->startTrans();
        $stockField = "product_name,warehouse_number,num,action_order_number,cate,product_id";
        for($i = 0; $i < count($updateData); $i++) {
            $stockFilter[$i]['id'] = ['EQ', $updateData[$i]['id']];
            $stockRst[$i] = $this->where($stockFilter[$i])->save($updateData[$i]);
            if ($stockRst[$i] === false) {
                M()->rollback();
                return $msg = [
                    'status' => 400,
                    'msg'    => "批量审核失败"
                ];
            }
            $stockLog[$i] = $this->getOneStockLog($stockFilter[$i], $stockField,'id','id');
            $productUpdRst[$i] = $stockModel->updateWithStockOutData($updateData[$i], $stockLog[$i]);

            if ($productUpdRst[$i] === false) {
                M()->rollback();
                return $msg = [
                    'status' => 400,
                    'msg'    => "批量审核失败"
                ];
            }

            $orderProductData[$i] = $orderProductModel->getStockOutUpdData($stockLog[$i]);
            $productNum[$i]       = count($orderProductData[$i]);

            /* 同意申请 更新产品采购表 */
            for ($j = 0; $j < $productNum[$i]; $j++) {
                $orderProductData[$i][$j]  = $orderProductModel->getUpdDataWithStockAuditData($updateData[$i], $stockLog[$i], $orderProductData[$i][$j]);

                $productProductRst[$i][$j] = $orderProductModel->save($orderProductData[$i][$j]);

                if ($productProductRst[$i][$j] === false) {
                    M()->rollback();
                    return $msg = [
                        'status' => 400,
                        'msg'    => "批量审核失败"
                    ];
                }
            }
            $stockRecord[$i] = $stockLogModel->getAddData('出库记录审核，审核人：' . session('nickname'), '审核出库');
        }
        $recordRst = $stockLogModel->addAll($stockRecord);
        if ($recordRst === false) {
            M()->rollback();
            return dataReturn('审核失败3', 400);
        }
        M()->commit();
        return $msg = [
            'status' => 200,
            'msg'    => "批量审核完毕"
        ];
    }


    public function getStockOutNumWithTimeLimit($limitTime, $productId)
    {
        $map = [
            ['update_time'   => ['GT', $limitTime]],
            ['type'          => ['EQ', '2']],
            ['audit_status'  => ['EQ', '2']],
            ['product_id'    => ['EQ', $productId]],
        ];
        return $monthStockNumber = (int) $this->where($map)->sum('num');
    }


    /**
     * 1 更新出库记录表 is_del = 1
     * 2 根据对应记录更新库存
     * 3 根据对应记录更新订单出库信息
     *
    */
    public function deleteTrans($data)
    {
        $this->startTrans();
        $orderProductModel = new OrderproductModel();
        $stockModel      = new StockModel();
        $stockLogModel = new StockLogModel();
        //$stockRecord = [];
        for ($i = 0; $i < count($data); $i ++) {
            $outData = $this->field('*')->find($data[$i]);
            $productFilter['product_id']     = ['EQ', $outData['product_id']];
            $productFilter['warehouse_number'] = ['EQ', $outData['warehouse_number']];
            switch ($outData['audit_status']) {
                case self::AUDIT_PENDING  :
                    $flag = $outData['action_order_number'] ? 'stockOutFalse' : 'stockOutNoActionOrderFalse';
                    break;
                case self::AUDIT_PASS :
                    if ($outData['action_order_number']) {
                        $flag = 'rollbackPassedStockOutHasOrder';
                    } else {
                        $flag = $outData['cate'] == self::REWORK_CATE ? 'rollbackPassedReworkOut': 'rollbackPassedStockOutNoOrder';
                    }
                    break;
                case self::AUDIT_FAIL :
                    $flag = "";
                    break;
                default :
                    $flag = "";
                    break;
            }
            $returnRst = $stockModel->updateWithFlag($flag, $productFilter,$outData['num']);
            //
            if ($returnRst === false) {
                $this->rollback();
                return dataReturn('删除失败1',self::FAIL_STATUS);
            }

            if ($outData['action_order_number']) {
                $orderProductReturnRst = $orderProductModel->rollbackWithStock($outData);
                if (in_array(false, $orderProductReturnRst)) {
                    $this->rollback();
                    return dataReturn('删除失败2',self::FAIL_STATUS);
                }
            }

            $stockRecord[$i] = $stockLogModel->getAddData('出库记录删除，删除人：' . session('nickname'), '删除出库');
        }
        $map['id'] = ['IN', $data];
        $saveRst = $this->where($map)->save(['is_del' => 1]);
        if ($saveRst === false) {
            $this->rollback();
            return dataReturn('失败3', self::FAIL_STATUS);
        }
        $addRst = $stockLogModel->add($stockRecord);
        if ($addRst === false) {
            $this->rollback();
            return dataReturn('失败4', self::FAIL_STATUS);
        }
        $this->commit();
        return dataReturn('删除成功', self::SUCCESS_STATUS);

    }

}