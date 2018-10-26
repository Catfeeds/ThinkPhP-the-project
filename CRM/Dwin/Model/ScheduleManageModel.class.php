<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/6/14
 * Time: 16:32
 */

namespace Dwin\Model;


use Think\Model;

class ScheduleManageModel extends Model
{

    public function selectScheduleData($where,$field)
    {

        return $data = $this->where($where)->field($field)->select();
    }

    public function setScheduleData($where,$updateData)
    {
        return $data = $this->where($where)->setField($updateData);
    }
}
