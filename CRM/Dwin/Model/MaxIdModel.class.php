<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/4/12
 * Time: 10:55
 */
namespace Dwin\Model;
use Think\Model;

class MaxIdModel extends Model
{
    public function getMaxId($table)
    {
//        $this->startTrans();
        $maxId     = $this -> lock(true) -> where(['table' => ['EQ', $table]]) -> getField('max_id');
        $updateRst = $this -> lock(true) -> where(['table' => ['EQ', $table]]) -> save(['max_id' => $maxId + 1]);
        if ($updateRst !== false) {
//            $this->commit();
            $this->lock(false);
            return $maxId;
        }else{
            return false;
        }
    }

    /**
     * 新增一条记录
     * @param $table
     * @return bool
     */
    public function insert($table)
    {
        $field = 'id';
        if ($table == 'industrial_seral_screen') {
            $field = 'product_id';
        }
        $data = [
            'table' => $table,
            'max_id' => M($table) -> lock(true) -> max($field) + 1,
        ];
        $res = $this->add($data);
        if ($res !== false){
            return true;
        }else{
            return false;
        }
    }
}