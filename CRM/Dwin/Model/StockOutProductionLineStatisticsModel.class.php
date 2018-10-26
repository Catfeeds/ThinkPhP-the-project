<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/9/28
 * Time: 下午4:18
 */

namespace Dwin\Model;
use Think\Model;

class StockOutProductionLineStatisticsModel extends model{
    public function addStatistics($data){
        $data['date'] = strtotime(date("Ymd"));
        $data['month'] = date("m");
        $data['year'] = date("Y");
        $res = $this->add($data);
        if($res === false){
            return [false, $this->getError()];
        }
        return [true, "新增成功"];
    }

    public function syncStatistics($lineId, $number){
        $lineModel = new ProductionLineModel();

        $time = strtotime(date("Ymd"));

        // 判断当前生产线是否与上级
        $lineData = $lineModel->find($lineId);

        $line = empty($lineData['pid']) ? $lineId : $lineData['pid'];

        $statisticsData = [];
        $data = $this->where(['date' => $time])->find();
        if(empty($data)){
            $statisticsData['line' . $line] = $number;
            list($code, $msg) = self::addStatistics($statisticsData);
            if($code === false){
                return [400, $msg];
            }
        }else {
            if(empty($data['line' . $line])){
                $statisticsData['line' . $line] = $number;
            }else {
                $statisticsData['line' . $line] = $data['line' . $line] + $number;
            }
            $statisticsData['id'] = $data['id'];
            $res = $this->save($statisticsData);
            if($res === false){
                return [400, $this->getError()];
            }
        }
        return [200, "同步成功"];
    }
}