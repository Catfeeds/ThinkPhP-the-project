<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/8/10
 * Time: 下午4:14
 */
namespace Dwin\Model;


use Think\Model;

class BomReplaceLogModel extends Model{

    const IS_DEL = 1; // 已删除
    const NO_DEL = 0; // 未删除

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

}