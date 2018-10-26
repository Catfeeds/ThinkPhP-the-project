<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/17
 * Time: 上午11:56
 */

namespace Dwin\Model;


use Think\Model;

class StockMaterialModel extends Model
{
    /* 审核状态：1 待审 2 通过 3 不通过*/
    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_UNQUALIFIED = 1;     // 不合格
    const TYPE_QUALIFIED = 2;       // 合格

    const SUCCESS_STATUS = 200;
    const FAIL_STATUS    = 404;
    const FORBIDDEN_STATUS = 403;

    const TYPE_STOCK_IN = 1; //入库物料信息
    const TYPE_STOCK_OUT = 2; //出库物料信息

    const IS_DEL = 1; // 已被删除
    const NO_DEL = 0; // 有效

    protected $_validate = array(
        array("source_id","require","出入库源单主键不能为空!",1),
        array("product_id","require","物料表主键不能为空!",1),
        array("product_no","require","物料编号不能为空!",1),
        array("num","require","出入库数量不能为空!",1),
        array("type","require","出入库类型不能为空!",1),
//        array("unqualified_rep_pid","require","默认质检不合格入库仓库不能为空!",1)
    );

    protected $autoRule = [
        ['create_time','time',3,'function'],
        ['update_time','time',3,'function'], // 对update_time字段在更新的时候写入当前时间戳
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
     * addStockIn 添加入库单据（基本表信息）
     * Created by
     * User: ma xu
     * Time: 2018.07.09
     * updateTime: 0728
     * @param $postsData
     * @return array|bool
     */
    public function getAddStockMaterial($materialData, $sourceId)
    {
        $stockData = [];
        foreach($materialData as $materialDatum) {
            $stockData[] = array(
                'source_id'            => $sourceId,
                'product_id'           => $materialDatum['product_id'],
                'product_no'           => $materialDatum['product_no'],
                'num'                  => $materialDatum['num'],
                'rep_pid'              => $materialDatum['default_rep_id'],
                'unqualified_rep_pid'  => $materialDatum['fail_rep_id'],
                'type'                 => 1
            );
        }
        foreach($stockData as $key => &$datum) {
            if (!$datum['num']) {
                unset($stockData[$key]);
            }
        }
        if (!count($stockData)) {
            $this->error = "空数据";
            return false;
        }

        return array_values($stockData);
    }


    /**
     * validateMaterialWithRule 字段验证（基本表信息）
     * Created by
     * User: ma xu
     * Time: 2018.07.28
     * @param $postsData
     * @return array|bool
     */
    public function validateMaterialWithRule($data)
    {
        $arr = [];
        foreach ($data as $datum) {
            $tmp = $this->auto($this->autoRule)->create($datum);
            if ($tmp === false) {
                return $tmp;
            }
            $arr[] = $tmp;
        }
        return $arr;
    }


    public function getInsertStockDataWithSourceId($sourceId, $sourceTable)
    {
        $map['mat.source_id'] = ['eq', $sourceId];
        $field = "
            mat.id,
            mat.source_id,
            mat.product_id,
            mat.num insert_num,
            mat.rep_pid insert_rep_id,
            task.batch,
            task.cate cate_id";
        return $this->alias('mat')
            ->field($field)
            ->join("LEFT JOIN {$sourceTable} task ON task.id = mat.source_id")
            ->where($map)
            ->select();
    }

    /**
     * create by chendd 新增物料信息
     * @param $postData
     * @param $type
     * @return array|bool
     */
    public function addStockMaterial($postData, $type = self::TYPE_STOCK_IN)
    {
        $data = self::getNewField($postData);
        if(empty($data)){
            return [-1, [], "未添加单据的基本信息"];
        }
        if(empty($data['product_id']) || empty($data['product_no']) || empty($data['num']) || empty($data['rep_pid'])){
            return [-2, [], "请将数据填写完整"];
        }

        if($type == self::TYPE_STOCK_IN){
            if(empty($data['unqualified_rep_pid'])){
                return [-2, [], "请将数据填写完整"];
            }
            $data['source_id'] = session("stockInId");
        }else {
            $data['source_id'] = session("stockOutId");
        }

        $data['type'] = $type;
        $data['create_time'] = time();
        $data['update_time'] = time();
        $rst = $this->create($data);
        if($rst){
            return [0, $rst, "实例化单据基本信息成功"];

        }else {
            return [-2, [], $this->getError()];
        }
    }

    /**
     * 出库单专用  根据出库单id查找当前出库单下方物料
     * @param $stockId
     * @param array $map
     * @return mixed
     */
    public function selectByStockId($stockId, $map = []){
        if(empty($map)){
            $map['m.type'] = ['eq', self::TYPE_STOCK_OUT];
        }
        $map['m.is_del'] = ['eq', self::NO_DEL];
        $map['m.source_id'] = ['eq', $stockId];
        $data = $this->alias("m")
            ->field("m.*, q.repertory_name as qualified_repertory_name, un.repertory_name as unqualified_repertory_name, cm.product_name, cm.product_number,cs.stock_number,cs.o_audit,cs.out_processing")
            ->join("left join crm_repertorylist q on q.rep_id = m.rep_pid")
            ->join("left join crm_repertorylist un on un.rep_id = m.unqualified_rep_pid")
            ->join("left join crm_material cm on cm.product_id = m.product_id")
            ->join("left join crm_stock cs on m.rep_pid = cs.warehouse_number and cs.product_id = m.product_id")
            ->where($map)->select();
        return $data;
    }

    /**
     * 出库单专用  根据出库单id查找当前出库单下方物料
     * @param $stockId
     * @param array $map
     * @return mixed
     */
    public function selectBaseMsgByStockOutId($stockId, $map = []){
        $map['m.type'] = ['eq', self::TYPE_STOCK_OUT];
        $map['m.is_del'] = ['eq', self::NO_DEL];
        $map['m.source_id'] = ['eq', $stockId];
        $data = $this->alias("m")
            ->field("m.*, cm.product_name,cm.product_number, q.repertory_name")
            ->join("left join crm_material cm on cm.product_id = m.product_id")
            ->join("left join crm_repertorylist q on q.rep_id = m.rep_pid")
            ->where($map)
            ->select();
        return $data;
    }

    /**
     * 入库单专用  根据出库单id查找当前出库单下方物料
     * @param $stockId
     * @param array $map
     * @return mixed
     */
    public function selectBaseMsgByStockInId($stockId, $map = []){
        $map['m.type'] = ['eq', self::TYPE_STOCK_IN];
        $map['m.is_del'] = ['eq', self::NO_DEL];
        $map['m.source_id'] = ['eq', $stockId];
        $data = $this->alias("m")
            ->field("m.*, cm.product_name,cm.product_number, q.repertory_name")
            ->join("left join crm_material cm on cm.product_id = m.product_id")
            ->join("left join crm_repertorylist q on q.rep_id = m.rep_pid")
            ->where($map)
            ->select();
        return $data;
    }

    /**
     * 目前暂时不用
     * @param $params
     * @return array
     */
    public function editMaterialByPurchase($params){
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], "无修改数据提交"];
        }

        $oldData = $this->field("*")->find($data['id']);


        $stockRecordModel = new StockInRecordModel();
        $recordData = $stockRecordModel->getNumByMaterialId($oldData['id']);

        if($data['num'] < $recordData['total_num']){
            return [-2, [], "修改后的入库数量不能低于已入库数量"];
        }

        $editData = $this->compareData($oldData, $data);

        if ($editData === false) {
            return [-1, [], "无数据修改"];
        }else if($editData == -1) {
            return [-2, [], "只能对入库数量进行修改"];
        } else {
            $createData = $this->create($editData);
            if(!$createData){
                return[-2,[], $this->getError()];
            }
            return [0, $createData, "数据实例化成功"];
        }
    }

    /**
     * 修改其他出库类型的出库单基本信息
     * @param $params
     * @return array
     */
    public function editMaterialByStockOutPurchase($params){
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], "无修改数据提交"];
        }

        $oldData = $this->field("*")->find($data['id']);


        $stockRecordModel = new StockOutRecordModel();
        $recordData = $stockRecordModel->getNumByMaterialId($oldData['id']);

        if($data['num'] > $recordData['num']){
            return [-2, [], "修改后的出库库数量不能低于已出库数量"];
        }

        $editData = $this->compareData($oldData, $data);

        $stockModel = new StockModel();
        if(isset($editData['rep_id'])){
            $num = isset($editData['num']) ? $editData['num'] : $oldData['num'];
            $productNo = isset($editData['product_no']) ? $editData['product_no'] : $oldData['product_no'];
            $productId = isset($editData['product_id']) ? $editData['product_id'] : $oldData['product_id'];
            //判断修改后的库房是否有相应库存
            $stockData = $stockModel->where(['product_id' => $productId, 'warehouse_number' => $editData['rep_id']])->find();
            if(empty($stockData) || $stockData['stock_number'] < $num){
                $this->rollback();
                return dataReturn("物料编号：" . $productNo . "仓库数量不够", 400);
            }
        }

        if ($editData === false) {
            return [-1, [], "无数据修改"];
        }else if($editData == -1) {
            return [-2, [], "只能对出库数量进行修改"];
        } else {
            $createData = $this->create($editData);
            if(!$createData){
                return[-2,[], $this->getError()];
            }
            return [0, $createData, "数据实例化成功"];
        }
    }

    /**
     * 比较修改前后所修改的数据
     * @param $oldData
     * @param $editedData
     * @return bool
     */
    public function compareData($oldData, $editedData)
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

        $editedData['id']           = $oldData['id'];
        $editedData['update_time']  = time();
        return $editedData;
    }

    /**
     * 获取一个入库单中所有产品的信息
     * @param $stockId
     * @param $map
     * @return mixed
     */
    public function getAllMaterialMsg($stockId, $map=[]){
        $map['m.type'] = ['eq', self::TYPE_STOCK_IN];
        $map['m.is_del'] = ['EQ', self::NO_DEL];
        $map['m.source_id'] = ['EQ', $stockId];
        $data = $this->alias("m")
            ->field("m.id,m.product_id,m.product_no,num,cm.product_name,unqualified_rep_pid fail_rep_id,rep_pid default_rep_id")
            ->join("left join crm_material cm on cm.product_id = m.product_id")
            ->where($map)
            ->select();
        return $data;
    }

    public function getMaterialWithStockInNum($stockId, $map= [])
    {
        $map['m.is_del'] = ['EQ', self::NO_DEL];
        $map['m.source_id'] = ['EQ', $stockId];
        $data = $this->alias("m")
            ->field("m.id,m.product_id,m.product_no,m.num -ifnull(sum(record.num),0) num,cm.product_name,m.unqualified_rep_pid fail_rep_id,m.rep_pid default_rep_id,rep1.repertory_name fail_rep_name,rep2.repertory_name default_rep_name")
            ->join("LEFT JOIN crm_material cm on cm.product_id = m.product_id")
            ->join('LEFT JOIN crm_stock_in_record record ON record.source_pid = m.id')
            ->join('LEFT JOIN crm_repertorylist rep1 ON rep1.rep_id = m.unqualified_rep_pid')
            ->join('LEFT JOIN crm_repertorylist rep2 ON rep2.rep_id = m.rep_pid')
            ->where($map)
            ->group("m.product_id")
            ->select();
        return $data;
    }

    /**
     * 删除入库单物料
     * @param $materialId
     * @return array
     */
    public function deleteStockMaterial($materialId){
        $materialData = $this->find($materialId);

        $stockModel = new StockInModel();
        $stockData = $stockModel->find($materialData['source_id']);
        if($stockData['audit_status'] != StockInModel::TYPE_UNQUALIFIED){
            return dataReturn("当前订单不是不合格订单，不可删除", 400);
        }

        $stockRecordModel = new StockInRecordModel();
        $recordData = $stockRecordModel->getNumByMaterialId($materialId);
        if(!empty($recordData['num'])){
            return dataReturn("已有入库物料，不可删除此物料", 400);
        }
        $this->startTrans();

        try {
            $this->where(['id' => $materialId])->setField(['is_del' => self::IS_DEL]);
            $orderProductModel = new PurchaseOrderproductModel();
            list($message, $status) = $orderProductModel->updateStockInNum($materialData['source_id'], $materialData['product_no'], 0, $materialId);
            if ($status != 0){
                $this->rollback();
                return dataReturn($message, 403);
            }
            $this->commit();
            return dataReturn("删除物料信息成功", 200);
        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(), 400);
        }

    }

    public function delTrans($sourceId,$type)
    {
        $map['source_id'] = ['eq', $sourceId];
        $map['type'] = ['eq', $type];
        $data['is_del'] = self::IS_DEL;
        $rst = $this->where($map)->setField($data);
        if ($rst === false) {
            $this->error = "删除失败";
            return false;
        }
        return $rst;
    }
}