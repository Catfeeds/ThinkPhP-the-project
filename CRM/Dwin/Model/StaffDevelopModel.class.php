<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/5/5
 * Time: 上午9:54
 */

namespace Dwin\Model;


use Think\Model;

class StaffDevelopModel extends Model
{
    public function getDataById($field, $filter,$order = 'add_time desc', $start = 0, $length = 100)
    {
        return $this->where($filter)->field($field)->limit($start,$length)->order($order)->select();
    }
}