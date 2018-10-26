<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/7/28
 * Time: 下午3:14
 */
namespace Dwin\Model;


use Think\Model;

class MaterialSubstituteModel extends Model{
    const IS_DEL = 1;
    const NO_DEL = 0;

    const TYPE_ALTERNATIVE_SCOPE_ALL = 1; // 能够替代所有
    const TYPE_ALTERNATIVE_SCOPE_SOME = 2; // 能够替代所有

    public static $scopeMap = [
        self::TYPE_ALTERNATIVE_SCOPE_ALL  => "替代所有",
        self::TYPE_ALTERNATIVE_SCOPE_SOME => "替代部分",
    ];

    /**
     * 新增一个
     * @param $productId
     * @param $productNo
     * @param $substitutedId
     * @param string $tips
     * @param int $alternativeScope
     * @return array
     */
    public function addSubstitute($productId, $productNo, $substitutedId, $tips = '', $alternativeScope = 1){
        if(empty($productId) || empty($productNo) || empty($substitutedId)){
            return [-2, [], "替代数据不完整，请明确数据"];
        }
        $data = [];
        $data['product_id'] = $productId;
        $data['product_no'] = $productNo;
        $data['substituted_id'] = $substitutedId;
        $data['tips'] = $tips;
        $data['alternative_scope'] = $alternativeScope;
        $data['update_time'] = time();
        return [0, $data, "数据返回完整"];
    }

    /**
     * 新增多个
     * @param $productId    被替代物主键
     * @param $productNo    被替代物编号
     * @param $replaceData  替代物料信息
     * @return array
     */
    public function addSubstituteMany($productId, $productNo, $replaceData){
        $data = [];
        foreach ($replaceData as $key => $item){
            $tips = isset($replaceData['tips']) ? $replaceData['tips'] : "";
            $alternativeScope = isset($replaceData['alternative_scope']) ? $replaceData['alternative_scope'] : self::TYPE_ALTERNATIVE_SCOPE_ALL;
            list($code, $returnData, $msg) = self::addSubstitute($productId, $productNo, $item['substituted_id'], $tips, $alternativeScope);
            if($code != 0){
                return dataReturn($msg,400);
            }
            $data[] = $returnData;
        }
        $res = $this->addAll($data);
        if(!$res){
            return dataReturn($this->getError(),400);
        }
        return dataReturn("替代物料添加成功",200);
    }


    /**
     * 修改单条数据
     * @param $replaceData
     * @return array
     */
    public function editSubstitute($replaceData){
        if(empty($replaceData['id']) || empty($replaceData['substituted_id'])){
            return [-2, [], "替代数据不完整，请明确数据"];
        }

        $oldData = $this->find($replaceData['id']);
        if($oldData['substituted_id'] == $replaceData['substituted_id']){
            return [-1, [], "替代数据未发生改变"];
        }
        $data['id'] = $replaceData['id'];
        $data['substituted_id'] = $replaceData['substituted_id'];
        if(isset($replaceData['tips'])){
            $data['tips'] = $replaceData['tips'];
        }
        if(isset($replaceData['alternative_scope'])){
            $data['alternative_scope'] = $replaceData['alternative_scope'];
        }
        $data['update_time'] = time();


        $res = $this->save($data);
        if(!$res){
            return [-2, [], $this->getError()];
        }
        return [0, $res, "数据保存成功"];
    }

    /**
     * 修改多条数据
     * @param $replaceData
     * @return array
     */
    public function editSubstituteMany($replaceData){
        $status = '';
        foreach ($replaceData as $key => $item){
            list($code, $returnData, $msg) = self::editSubstitute($item);
            if($code == -2){
                return [$msg, $code];
            }
            if($code == 0){
                $status = 200;
            }
        }
        if(empty($status)){
            return ["数据未发生改变", -1];
        }
        return ["数据修改成功", 0];
    }

    /**
     * 找一个物料所有的替代物
     * @param $productId
     * @param $type 1=>一个prodcut_id  2=> 一个数组
     * @return array
     */
    public function findSubstituteByProductId($productId, $type = 1){
        if($type == 1){
            $map['ms.product_id'] = ['eq' , $productId];
        }else {
            $map['ms.product_id'] = ['in' , $productId];
        }
        $map['ms.is_del'] = ['eq' , MaterialSubstituteModel::NO_DEL];
        return $this->alias("ms")
            ->field("ms.*, material.product_name, material.product_number, material.product_no, material.warehouse_id")
            ->join("left join crm_material material on material.product_id = ms.substituted_id")
            ->where($map)
            ->select();
    }
}
