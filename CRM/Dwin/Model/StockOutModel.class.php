<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/7/31
 * Time: 上午10:40
 */

namespace Dwin\Model;
use Think\Model;
class StockOutModel extends Model{
    /* 审核状态：0-未审核 1-审核不合格 2 质控审核完毕 3 库房审核完毕*/
    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_UNQUALIFIED = 1;     // 审核不合格
    const TYPE_QUALIFIED = 2;     // 质控审核完毕
    const TYPE_STOCK_QUALIFIED = 3;       // 库房审核完毕

    const IS_DEL = 1; // 已被删除
    const NO_DEL = 0; // 有效

    const SUCCESS_STATUS = 200;
    const FAIL_STATUS    = 404;
    const FORBIDDEN_STATUS = 403;

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
     * create by chendd 通过crm_stock_out 中的id查找
     * @param $stockId
     * @return mixed
     */
    public function findByStockId($stockId){
        $map['is_del'] = ['eq', self::NO_DEL];
        $map['id'] = ['eq', $stockId];
        $data = $this->field("*")->where($map)->find();
        return $data;
    }

    /**
     * 其他出库类型新增
     * @param $postData
     * @return array
     */
    public function addOtherStockOut($postData){
        $data = self::getNewField($postData);

        if(empty($data)){
            return [-1, [], "未添加出库单据的基本信息"];
        }

        if(empty($data['cate']) || empty($data['cate_name']) || empty($data['cate']) || empty($data['cate']))
        $createId = new MaxIdModel();
        $stockOutId = $id = $createId->getMaxId('stock_out');;
        if(!$stockOutId){
            return [-2, [], "生成出库编号失败"];
        }

        $data['update_time'] = time();
        $data['create_time'] = time();
        $data['create_name'] = session("nickname");
        $data['create_id'] = session("staffId");
        $data['audit_status'] = self::TYPE_NOT_AUDIT;
        $data['is_del'] = self::NO_DEL;
        $data['stock_out_id'] = "DW" . date("Ymd") . $stockOutId;

        $res = $this->add($data);

        if(!$res){
            return [-2, [], $this->getError()];
        }

        session("stockOutId", $res);
        return [0, $res, "新增出库单据基本信息成功"];
    }

    /**
     * 新增出库单（其他出库类型）所有信息
     * @param $postData
     * @return array
     */
    public function addOtherStockOutAllMsg($postData){
        $this->startTrans();
        $materialModel = new StockMaterialModel();
        // 查出当前订单需要新建多少张出库单
        $repPidArr = array_unique(array_column($postData['material'],'rep_pid'));
        foreach ($repPidArr as $k => $v){
            if(empty($v)){
                return dataReturn("请填写出库类别", 401);
                break;
            }
            list($code, $data, $msg) = self::addOtherStockOut($postData['stock']);
            if($code != 0 ){
                $this->rollback();
                return dataReturn($msg, 402);
            }

            $materialData = [];
            $statusstr = '';

            // 新增出库单物料信息
            foreach ($postData['material'] as $key => $item){
                if($item['rep_pid'] == $v){
                    list($code, $data, $msg) = $materialModel->addStockMaterial($item, StockMaterialModel::TYPE_STOCK_OUT);

                    if($code == 0 ){
                        $statusstr = self::SUCCESS_STATUS;
                        $materialData[] = $data;
                    }
                    if($code == -2){
                        $this->rollback();
                        return dataReturn($msg, 402);
                    }
                }
            }

            if(empty($statusstr)){
                $this->rollback();
                return dataReturn($msg, 403);
            }
            $res = $materialModel->addAll($materialData);
            if($res === false){
                $this->rollback();
                return dataReturn($materialModel->getError(), 404);
            }
        }
        $this->commit();
        return dataReturn("出库单生成成功", 200);
    }

    /**
     * 修改出库单基本信息
     * @param $params
     * @return array
     */
    public function editStockBaseMsg($params){
        $data = self::getNewField($params);
        if (empty($data)) {
            return [-1, "无修改数据提交"];
        }

        $oldData = $this->field("*")->find($data['id']);

        if($oldData['audit_status'] == self::TYPE_QUALIFIED){
            return [-2, '当前出库单已审核通过，不可修改'];
        }
        $editData = $this->compareData($oldData, $data);
        if ($editData === false) {
            return [-1, "无数据修改"];
        } else {
            $createData = $this->create($editData);
            if(!$createData){
                return[-2, $this->getError()];
            }
            $saveData = $this->save($editData);
            if (!$saveData){
                return[-2, $this->getError()];
            }
            return [0, "数据修改成功"];
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

        $editedData['audit_status'] = self::TYPE_NOT_AUDIT;
        $editedData['id']    = $oldData['id'];
        $editedData['update_time']  = time();
        return $editedData;
    }

    /**
     * create by chendd 修改其他出库类型 出库单
     * @param $editMaterialData
     * @param $stockData
     * @return array
     */
    public function editStockByPurchase($editMaterialData,$stockData){

        $this->startTrans();
        $materialModel = new StockMaterialModel();

        $statusStr = '';
        foreach ($editMaterialData as $key => $item){
            list($code, $materiaDataOne, $msg) = $materialModel->editMaterialByStockOutPurchase($item);
            if($code == 0){
                $statusStr = self::SUCCESS_STATUS;
                $saveRst = $materialModel->save($materiaDataOne);
                if ($saveRst === false) {
                    $this->rollback();
                    return dataReturn($materialModel->getError(), self::FAIL_STATUS);
                    break;
                }
            }

            if($code == -2){
                $this->rollback();
                return dataReturn($msg, self::FAIL_STATUS);
                break;
            }
        }
        // 两边都没有修改才是没有修改
        list($stockCode, $stockMsg) = self::editStockBaseMsg($stockData);

        if($statusStr == "" && $stockCode == -1){
            $this->rollback();
            return dataReturn($msg, self::FAIL_STATUS);
        }
        if($stockCode == -2){
            $this->rollback();
            return dataReturn($stockMsg, self::FAIL_STATUS);
        }

        $this->commit();
        return dataReturn("入库单修改成功", self::SUCCESS_STATUS);
    }
}