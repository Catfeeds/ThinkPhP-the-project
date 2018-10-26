<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/8/1
 * Time: 下午3:44
 */
namespace Dwin\Model;


use think\Exception;
use Think\Model;

class StockInOtherApplyMaterialModel extends Model
{

    static protected $successStatus = 200;
    static protected $failStatus = 400;

    const IS_DEL = 1; // 已被删除
    const NO_DEL = 0; // 有效

    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_UNQUALIFIED = 1;     // 审核不通过
    const TYPE_QUALIFIED = 2;       // 审核通过

    public static $auditTypeMap = [
        self::TYPE_NOT_AUDIT   => '未审核',
        self::TYPE_UNQUALIFIED => '不合格',
        self::TYPE_QUALIFIED   => '合格',
    ];



    public function getAddDataWithAfterSale($afterSaleData,$primaryPId)
    {
        $material = [];
        foreach ($afterSaleData as  $afterSaleDatum) {
            $tmp = [
                'apply_id'       => $primaryPId,
                'product_id'     => $afterSaleDatum['product_id'],
                'product_number' => $afterSaleDatum['product_number'],
                'product_name'   => $afterSaleDatum['product_name'],
                'product_no'     => $afterSaleDatum['product_no'],
                'num'            => $afterSaleDatum['num'],
                'tips'           => $afterSaleDatum['tips'],
                'default_rep_id' => $afterSaleDatum['warehouse_number'],
                'create_id'      => $afterSaleDatum['proposer'],
                'create_time'    => $afterSaleDatum['proposer_name'],
                'update_id'      => $afterSaleDatum['proposer'],
                'update_time'    => $afterSaleDatum['update_time']
            ];
            $material[] = $tmp;
        }
        return $material;
    }

    public function findApplyMaterialWithPid($pid)
    {
        $map['a.apply_id'] = ['EQ', $pid];
        $field = "a.id,
                  a.apply_id source_id,
                  a.product_id,
                  a.product_number,
                  a.product_name,
                  a.product_no,
                  a.num shipment_num,
                  a.default_rep_id shipment_rep_pid,
                  a.tips shipment_tips,
                  a.default_rep_id,
                  rep.repertory_name rep_name";
        return $this->alias('a')
            ->where($map)
            ->field($field)
            ->join("LEFT JOIN crm_repertorylist rep ON rep.rep_id = default_rep_id")
            ->select();
    }

    public function getUpdData($data)
    {
        $materialData = [];
        foreach ($data as $datum) {
            $tmp = [
                'id' => $datum['id'],
                'num' => $datum['shipment_num'],
                'default_rep_id' => $datum['default_rep_id'],
                'tips' => $datum['shipment_tips']
            ];
            $materialData[] = $tmp;
        }
        return $materialData;
    }
    public function updateDataTrans($data)
    {
        $materialData = $this->getUpdData($data);
        $this->startTrans();
        foreach ($materialData as $materialDatum) {
            $map['id'] = ['eq', $materialDatum['id']];
            $rst = $this->where($map)->setField($materialDatum);
            if (false === $rst) {
                $this->rollback();
                $this->error = "更新失败";
                return false;
            }
        }
        $this->commit();
        return true;
    }

    public function delMaterial($sourceId)
    {
        $map['apply_id'] = ['eq', $sourceId];
        $data['is_del'] = self::IS_DEL;
        $rst = $this->where($map)->setField($data);
        if (false === $rst) {
            $this->rollback();
            $this->error = "更新失败";
            return false;
        }
        return true;
    }
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

    public function getAddData($params)
    {
        // 数据重构
        $data = $this->getNewField($params);

        if (empty($data)) {
            return [-1, [], '没有提交新增数据'];
        }

        // 对数据进行验证非空验证
        if(empty($data['product_id']) || empty($data['product_no']) || empty($data['product_name']) || empty($data['product_number']) || empty($data['num']) || empty($data['price']) || empty($data['total_price']) || empty($data['unite']) || empty($data['demand_time'])){
            return [-2, [], "请将数据填写完成"];
        }
        if($data['demand_time'] == 'NaN'){
            return [-2, [], "需求日期填写不规范或则未填写"];
        }
        $materialModel = new MaterialModel();
        $materialData = $materialModel->where(['product_id' => $data['product_id']])->find();

        // 处理库存数据
        $stockModel = new StockModel();
        list($code, $msg) = $stockModel->editStockNum($data['product_id'], $materialData['warehouse_id'], $data['num']);
        if($code != 0){
            return [-2, $data, $msg];
        }

        $data['default_rep_id'] = $materialData['warehouse_id'];
        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');
        $data['apply_id']    = session('applyId');

        $data = $this->create($data);

        if(!$data){
            return [-2, [], $this->getError()];
        }else {
            return [0, $data, '数据实例化成功'];
        }
    }

    /**
     * 添加申请书多个物料
     * @param $postData
     * @return array
     */
    public function addApplyMaterial($postData)
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
        } catch (\Exception $exception) {
            return [$exception->getMessage(), self::$failStatus];
        }
    }

    /**
     * 返回修改物料数据
     * @param $params
     * @return array
     */
    public function getEditData($params)
    {
        $data  = self::getNewField($params);
        if (empty($data)) {
            return [-1, [], "无修改数据提交"];
        }

        if(isset($data['demand_time']) && ($data['demand_time'] == 'NaN' || empty($data['demand_time']))){
            return [-2, [], "需求日期填写不规范或则未填写"];
        }

        $oldData = $this->field("*")->find($data['id']);
        $editData = $this->compareData($oldData, $data);
        if ($editData === false) {
            return [-1, [], '无数据修改'];
        } else {
            $editData = $this->create($editData);
            if(!$editData){
                if(isset($editData['num'])){
                    $num = $editData['num'] - $oldData['num'];
                    $stockModel = new StockModel();
                    list($code, $msg) = $stockModel->editStockNum($oldData['product_id'], $data['default_rep_id'], $num);
                    if($code != 0){
                        return [-2, $data, $msg];
                    }
                }
                return[-2,[], $this->getError()];
            }
            return [0, $editData, '数据实例化成功'];
        }
    }

    /**
     * 修改申请单多个物料
     * @param $postData
     * @return array
     */
    public function editApplyMaterial($postData)
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
     * 查找一个申请单中所有物料信息
     * @param $applyId
     * @param $map
     * @return array
     */
    public function getMsgByApplyId($applyId, $map = []){
        $map['m.is_del'] = ['eq', self::NO_DEL];
        $map['m.apply_id'] = ['eq', $applyId];
        $data = $this->alias("m")
            ->field("m.*,cm.product_name, cm.product_number, r.num as stock_out_number,cs.stock_number,cs.o_audit,cs.out_processing")
            ->join("left join crm_material cm on cm.product_id = m.product_id")
            ->join("left join crm_stock_out_record r on r.source_pid = m.id and r.status = " . StockOutRecordModel::TYPE_QUALIFIED)
            ->join("left join crm_stock cs on m.default_rep_id = cs.warehouse_number and m.product_id = cs.product_id")
            ->where($map)
            ->select();
        return $data;
    }

    /**
     * 重构申请单物料数据
     * @param $applyId
     * @return array
     */
    public function getMaterialByApplyId($applyId){
        $data = self::getMsgByApplyId($applyId);

        $returnData = [];
        foreach ($data as $key => $item){
            $returnData[$item['product_id']] = $item;
        }
        return $returnData;
    }
}