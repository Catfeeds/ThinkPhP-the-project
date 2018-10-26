<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/23
 * Time: 上午10:37
 */

namespace Dwin\Model;


use Think\Model;

class ProcessLinkModel extends Model
{
    const BEGIN_STATUS    = 0;
    const RUN_STATUS      = 1;
    const COMPLETE_STATUS = 2;
    public function findProcessBeginId($processId)
    {
        $map['flow_link_parent_id'] = ['EQ', $processId];
        $map['flow_link_status']    = ['EQ', self::BEGIN_STATUS];
        return $this->where($map)->field('*')->find();
    }
}