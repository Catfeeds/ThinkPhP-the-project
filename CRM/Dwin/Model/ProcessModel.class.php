<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/23
 * Time: 上午9:07
 */

namespace Dwin\Model;


use Think\Model;

class ProcessModel extends Model
{
    public function getProcessData()
    {
        return $this->field('*')
            ->select();
    }
}