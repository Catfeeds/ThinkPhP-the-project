<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/8/18
 * Time: 上午10:04
 */

namespace Dwin\Model;


use Think\Exception;
use Think\Model;
class PrintingLogModel extends model{
    public function addPrintData($sourceId){
        $data['source_id'] = $sourceId;
        $data['create_id'] = session("staffId");
        $data['create_name'] = session("nickname");
        $data['create_time'] = time();
        $res = $this->add($data);
        if(!$res){
            return false;
        }
        return $res;
    }
}