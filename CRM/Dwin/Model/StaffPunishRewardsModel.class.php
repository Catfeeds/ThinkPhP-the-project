<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/5/5
 * Time: 上午9:54
 */

namespace Dwin\Model;


use Think\Model;

class StaffPunishRewardsModel extends Model
{
    public function getDataById($field, $filter,$order = 'employee_id', $start = 0, $length = 100)
    {
        return $this->where($filter)->field($field)->limit($start,$length)->order($order)->select();
    }
}