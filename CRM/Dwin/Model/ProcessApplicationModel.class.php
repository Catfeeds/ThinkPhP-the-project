<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/22
 * Time: 下午9:14
 */

namespace Dwin\Model;


use Think\Model;

class ProcessApplicationModel extends Model
{
    const SUCCESS_STATUS = 200;
    const FAIL_STATUS    = 400;

    public function getProcessAddData($postData)
    {
        $linkModel = new ProcessLinkModel();
        $linkId = $linkModel->findProcessBeginId($postData['processId']);

        $data = [
            'flow_app_parent_id'         => $postData['processId'],
            'flow_app_staff_id'          => session('staffId'),
            'flow_app_current_status_id' => $linkId['flow_link_id'],
            'flow_app_title'             => $postData['title'],
            'flow_app_content'           => nl2br($postData['content']),
            'flow_app_status'            => 0,
            'flow_app_target_id'         => $postData['auditor'],
            'flow_app_priority'          => 0,
            'flow_app_addtime'           => time(),
            'flow_app_update_time'       => time(),
            'flow_app_is_del'            => 0
        ];
        return $data;
    }
    public function addProcessTrans($addData)
    {
        M()->startTrans();
        $addRst = $this->add($addData);
        if ($addRst === false) {
            M()->rollback();
            return $msg = [
                'status' => self::FAIL_STATUS,
                'msg'    => "提交失败，添加失败"
            ];
        }
        $recordModel = new ProcessApplyRecordModel();
        $recordAddData = $recordModel->getRecordAddData($addData, $addRst,"提交操作");
        $recordAddRst  = $recordModel->add($recordAddData);
        if ($recordAddRst === false) {
            M()->rollback();
            return $msg = [
                'status' => self::FAIL_STATUS,
                'msg'    => "提交失败，添加失败"
            ];
        }
        M()->commit();
        return $msg = [
            'status' => self::SUCCESS_STATUS,
            'msg'    => "提交成功"
        ];

    }
    public function getProcessAppData($map, $field, $start, $length, $order)
    {
        return $data = $this->alias('app')
            ->field($field)
            ->join('LEFT JOIN crm_process process ON app.flow_app_parent_id = process.flow_id')
            ->join('LEFT JOIN crm_process_link link ON app.flow_app_current_status_id = link.flow_link_id')
            ->join('LEFT JOIN crm_staff staff ON staff.id = app.flow_app_staff_id')
            ->where($map)
            ->order($order)
            ->limit($start, $length)
            ->select();
    }

    public function getAuth($id)
    {
        $auth = $this->field('flow_app_current_status_id,flow_app_status,flow_app_target_id')->find($id);
        if ($auth['flow_app_status'] == 2) {
            return $msg =['status' => 403, 'msg' => '已经完结，无需进行审批'];
        }
        if ($auth['flow_app_target_id'] != session('staffId')) {
            return $msg =['status' => 403, 'msg' => '非审核人，不能审批'];
        }
        return $msg = ['status' => 200, 'msg' => '有权，继续'];
    }

    public function updateStatus($processId, $flag, $reason)
    {
        $processRecordModel = new ProcessApplyRecordModel();
        $authData = $this->getAuth($processId);
        if ($authData['status'] != 200) {
            return $authData;
        }
        $processMsg = $this->alias('app')
            ->field('app.*,link.*')
            ->join('LEFT JOIN crm_process_link link ON app.flow_app_current_status_id = link.flow_link_id')
            ->find($processId);
        $update['flow_app_current_status_id'] = ($flag == 1) ? $processMsg['flow_link_next_id'] : $processMsg['flow_link_prev_id'];
        $update['flow_app_status'] = ($flag == 1)
            ? M('process_link')->field('flow_link_status')->find($processMsg['flow_link_next_id'])['flow_link_status']
            : M('process_link')->field('flow_link_status')->find($processMsg['flow_link_prev_id'])['flow_link_status'];
        $update['flow_app_target_id'] = ($flag == 1) ? 65 : session('staffId');
        $update['flow_app_id'] = $processId;
        $update['flow_app_update_time'] = time();
        $recordData = $processRecordModel->getRecordAddData($update, $processId, $reason, $flag);
        M()->startTrans();
        $condition['flow_app_id'] = ['EQ', $processId];
        $updRst = $this->where($condition)->save($update);
        if ($updRst === false) {
            M()->rollback();
            return $msg = ['status' => 401, 'msg' => '审核失败，回滚'];
        }
        $addRst = $processRecordModel->add($recordData);
        if ($addRst === false) {
            M()->rollback();
            return $msg = ['status' => 402, 'msg' => '审核失败，回滚'];
        }
        M()->commit();
        return $msg = ['status' => 200, 'msg' => '审核完成'];

    }
}