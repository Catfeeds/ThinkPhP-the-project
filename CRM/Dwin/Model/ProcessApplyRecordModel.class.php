<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/23
 * Time: 上午11:48
 */

namespace Dwin\Model;


use Think\Model;

class ProcessApplyRecordModel extends Model
{
    public function getRecordAddData($applicationAddData, $appId,$reason,$flag = 0)
    {
        $map['flow_link_id'] = ['eq', $applicationAddData['flow_app_current_status_id']];
        $linkInfo = M('process_link')->where($map)->field("*")->find();
        $data = [
            'flow_app_id'        => $appId,
            'flow_node_id'       => $applicationAddData['flow_app_current_status_id'],
            'flow_node_name'     => $linkInfo['flow_link_name'],
            'flow_record_staff'  => session('staffId'),
            'flow_app_operation' =>  $flag,
            'flow_app_reason'    => $reason,
            'flow_record_addtime' => time(),
            'flow_recor_is_del'  => 0,
        ];
        return $data;
    }

    public function getRecordData($id)
    {
        $map['flow_app_id'] = ['eq', $id];
        $map['flow_record_is_del'] = ['eq', 0];
        $field = "crm_staff.name record_staff,from_unixtime(flow_record_addtime) record_time, flow_node_name node_name, flow_app_operation operation, flow_app_reason reason";
        return $this->alias('record')->where($map)
            ->join('LEFT JOIN crm_staff ON record.flow_record_staff = crm_staff.id')
            ->field($field)->select();
    }

}