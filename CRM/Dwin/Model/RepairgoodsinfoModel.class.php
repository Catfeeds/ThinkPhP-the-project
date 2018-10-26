<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/10/13
 * Time: 下午2:00
 */

namespace Dwin\Model;
use Think\Model;
class RepairgoodsinfoModel extends Model
{
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

        $editedData['rid']   = $oldData['rid'];
        return $editedData;
    }

    /**
     * 修改单条物料信息
     * @param $postData
     * @return array
     */
    public function editProduct($postData){
        if(empty($postData['rid'])){
            return ["参数错误",2];
        }

        $data = self::getNewField($postData);

        $oldData = $this->find($data['rid']);
        $editData = self::compareData($oldData, $data);

        if(!$editData){
            return ['无数据修改',1];
        }

        $res = $this->save($editData);
        if($res === false){
            return [$this->getError(),2];
        }
        return ["数据修改成功",0];
    }

    /**
     * 修改多条物料信息
     * @param $postData
     * @return array
     */
    public function editProductMany($postData){
        $postData = array_filter($postData);
        if(empty($postData)){
            return ["无数据修改",1];
        }

        $flag = 1; // 标记是否有修改的数据
        foreach ($postData as $key => $value){
            list($msg, $code) = self::editProduct($value);
            if($code == 2){
                return [$msg, 2];
            }
            if($code == 0){
                $flag = 0; // 标记已有修改数据
            }
        }
        if($flag == 1){
            return ["无数据修改",1];
        }

        return ["数据修改成功",0];
    }
}