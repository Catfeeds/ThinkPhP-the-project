<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/7/24
 * Time: 下午3:51
 */
namespace Dwin\Model;


use Think\Model;

class MaterialBomSubModel extends Model{

    const IS_DEL = 1; // 已删除
    const NO_DEL = 0; // 未删除

    protected $_validate = array(
        array("bom_pid","require","合同编号不能为空!"),
        array("product_id","require","物料主键不能为空!"),
        array("product_no","require","物料编号不能为空!"),
        array("num","require","物料编号不能为空!"),
    );

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
     * 新增多条bom配方物料信息
     * @param $postData
     * @return array
     */
    public function addBomSub($postData){
        $data = self::getNewField($postData);
        if(empty($data)){
            return [-1, [], '没有提交新增数据'];
        }

        if(empty($data['product_id']) || empty($data['product_no']) || empty($data['num'])){
            return [-2, [], "请将数据填写完成，数量不可以为空"];
        }


        $data['bom_pid'] = session('bomId');
        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');
        $data = $this->create($data);

        if(!$data){
            return [-2, [], $this->getError()];
        }else {
            return [0, $data, '数据实例化成功'];
        }
    }

    /**
     * 新增多条bom配方物料信息
     * @param $postData
     * @return array
     */
    public function addBomSubMany($postData){
        $data = [];
        $status = '';
        $msg = '';
        $logData = [];
        foreach ($postData as $key => $item){
            list($code, $returnData, $msg) = self::addBomSub($item);
            if($code == 0){
                $status = 200;
                $data[] = $returnData;
            }
            if($code == -2){
                return [$msg, -2, []];
            }
            $logData[] = "新增物料编号为" . $returnData['product_no'] . "的物料";
        }
        if($status == ''){
            return [$msg, -1, []];
        }
        $res = $this->addAll($data);
        if(!$res){
            return [$this->getError(), -2];
        }
        return ["新增成功", 0, $logData];
    }

    /**
     * 查找一个bom里面的配料信息
     * @param $bomId
     * @return mixed
     */
    public function findBomOtherMsg($bomId){
        $map = [];
        $map['b.is_del'] = ['eq', self::NO_DEL];
        $map['b.bom_pid'] = ['eq', $bomId];
        return $this->alias("b")
            ->field("b.*,m.product_name,m.product_number,cs.stock_number,cs.o_audit,cs.out_processing")
            ->join("left join crm_material m on m.product_id = b.product_id")
            ->join("left join crm_stock cs on cs.product_id = b.product_id and cs.warehouse_number = 'K004'")
            ->where($map)
            ->select();
    }

    public function getBomMaterialWithProduceOrderId($productionOrderId)
    {
       $bomId = M('production_order')->find($productionOrderId)['bom_pid'];
       if ($bomId) {
           $map['a.bom_pid'] = ['eq', $bomId];
           $map['a.is_del']  = ['eq', self::NO_DEL];
           $field = "a.*,
                    bom.bom_id,
                    bom.bom_type_name bom_cate,
                    bom.product_no production_product,
                    group_concat(sub.substituted_no) substituted_no,
                    if (count(sub.id), 1, 0) has_sub";
           return $this->alias('a')
               ->where($map)
               ->field($field)
               ->join('LEFT JOIN crm_material_bom bom ON bom.id = a.bom_pid')
               ->join('LEFT JOIN (SELECT a.*,b.product_no substituted_no from crm_material_substitute a LEFT JOIN crm_material b ON a.substituted_id = b.product_id) sub ON sub.product_id = a.product_id and sub.is_del = 0')
               ->group('a.id')
               ->select();
       }
       return [];
    }

    /**
     * 修改配料信息
     * @param $params
     * @return array
     */
    public function getEditData($params)
    {
        if (empty($params)) {
            return [-1, [], "无修改数据提交", ''];
        }

        $params = self::getNewField($params);

        $oldData = $this->field("*")->find($params['id']);
        list($editData, $logStr) = $this->compareData($oldData, $params);
        if ($editData === false) {
            return [-1, [], '无数据修改', ''];
        } else {
            $createData = $this->create($editData);
            if(!$createData){
                return[-2,[], $this->getError(), ''];
            }
            return [0, $editData, '数据实例化成功', $logStr];
        }
    }

    /**
     * 比较修改前后数据的不同
     * @param $oldData
     * @param $editedData
     * @return array
     */
    private function compareData($oldData, $editedData)
    {
        $logStr = "BOM物料编号为：" . $oldData['product_no'] . "，";
        // 先把不存在当前表里面的字段剔除，然后在与原先的数据做对比
        foreach ($editedData as $key => $val) {
            if ($val == $oldData[$key]) {
                unset($editedData[$key]);
            } else {
                // 处理修改内容，便于存储log
                switch ($key){
                    case "num":
                        $logStr .= "数量由" . $oldData[$key] . "修改为" . $val . '。';
                        break;
                    default :
                        break;
                }
                continue;
            }
        }

        if(empty($editedData)){
            return [false, ''];
        }

        $editedData['id']   = $oldData['id'];
        $editedData['update_time']  = time();
        $editedData['update_id']    = session('staffId');
        return [$editedData, $logStr];
    }

    /**
     * 修改多条bom配料信息
     * @param $postData
     * @return array
     */
    public function editBomSubMany($postData)
    {
        try {
            $returnRst = '';
            $msg = "ok";

            $logData = [];
            for($i = 0; $i < count($postData); $i++) {
                list($code, $data, $msg, $logStr) = $this->getEditData($postData[$i]);
                if($code == 0){
                    $returnRst = 200;
                    $saveRst = $this->save($data);
                    if ($saveRst === false) {
                        return [$this->getError(), -2, []];
                        break;
                    }
                }

                if($code == -2){
                    return [$msg, -2, []];
                    break;
                }

                $logData[] = $logStr;
            }

            if(empty($returnRst)){
                return [$msg, -1, []];
            }

            return ["修改合同成功", 0, $logData];
        } catch (\Exception $exception) {
            return [$exception->getMessage(), -2];
        }
    }

    /**
     * 删除bom下的物料信息
     * @param $bomId
     * @param $bomSubId
     * @return array
     */
    public function deleteBomSub($bomId, $bomSubId){
        $this->startTrans();

        $productModel = new ProductionOrderModel();
        $productData = $productModel->where(['bom_pid' => $bomId, 'is_del' => ProductionOrderModel::$notDel])->select();
        if(!empty($productData)){
            return dataReturn("当前bom在生产计划中有被使用，无法删除",400);
        }

        $bomModel = new MaterialBomModel();
        $bomData = $bomModel->find($bomId);
        if($bomData['bom_status'] == MaterialBomModel::TYPE_QUALIFIED){
            $this->rollback();
            return dataReturn("审核合格的bom不能被删除",400);
        }

        $bomModel->where(['id' => $bomId])->setField(['bom_status' => MaterialBomModel::TYPE_NOT_AUDIT]);

        $bomSumData = $this->find($bomSubId);
        $res = $this->where(['id' => $bomSubId])->setField(['is_del' => self::IS_DEL]);
        if(!$res){
            $this->rollback();
            return dataReturn($this->getError(),400);
        }

        // BOM 操作履历
        $logModel = new MaterialBomLogModel();
        list($logMsg, $logCode) = $logModel->createBomLog($bomId, MaterialBomLogModel::TYPE_DEL_MATERIAL, "BOM物料编号为" . $bomSumData['product_no'] . "删除成功");
        if($logCode != 200){
            $this->rollback();
            return dataReturn($logMsg, 400);
        }

        $this->commit();
        return dataReturn("删除成功", 200);
    }
}