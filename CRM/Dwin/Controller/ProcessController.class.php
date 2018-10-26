<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/4/21
 * Time: 下午5:53
 */
namespace Dwin\Controller;

use Dwin\Model\ProcessApplyRecordModel;
use Dwin\Model\ProcessModel;
use Dwin\Model\ProcessApplicationModel;

class ProcessController extends \Dwin\Controller\CommonController
{
    const SUCCESS_STATUS = 200;
    const NO_AUTH_STATUS = 403;
    const FAIL_STATUS    = 400;
    const AUDIT_ROLE     = "6,7";
    public function index()
    {
        if (IS_POST) {
            $processAppModel = new ProcessApplicationModel();
            $this->posts = I('post.');
            if (isset($this->posts['flag']) && $this->posts['flag'] == 1) {
                $this->sqlCondition = $this->getSqlCondition($this->posts);
                $count = $processAppModel->count();
                $recordsFiltered = $processAppModel->count();
                $map = "1=1";
                $this->field = "flow_app_id id,from_unixtime(app.flow_app_addtime) app_time,staff.name app_staff,app.flow_app_title app_title,app.flow_app_content app_content,";
                $this->field .= "link.flow_link_name link_name,process.flow_name process_name,from_unixtime(app.flow_app_update_time) app_update_time,app.flow_app_status app_status";
                $data = $processAppModel->getProcessAppData($map, $this->field, $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);
                foreach ($data as &$val) {
                    $val['DT_RowId'] = $val['id'];
                }
                $this->output = $this->getDataTableOut($this->posts['draw'], $count, $recordsFiltered, $data);
                $this->ajaxReturn($this->output);
            }

            if (isset($this->posts['flag']) && $this->posts['flag'] == 2) {
                $processRecordModel = new ProcessApplyRecordModel();
                $recordData = $processRecordModel->getRecordData($this->posts['applicationId']);
                $authData   = $processAppModel->getAuth($this->posts['applicationId']);
                $authFlag = $authData['status'] == 200 ? true : false;
                $data = [
                    'recordData' => $recordData,
                    'authFlag'   => $authFlag
                ];
                $this->ajaxReturn($data);
            }
            $this->ajaxReturn($msg = [
                'status' => self::NO_AUTH_STATUS,
                'msg'    => "非法请求，无权访问"
            ]);

        } else {
            $this->display();
        }
    }

    public function addApplication()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $processApplicationModel = new ProcessApplicationModel();
            $addData = $processApplicationModel->getProcessAddData($this->posts);
            $addRst  = $processApplicationModel->addProcessTrans($addData);
            $this->ajaxReturn($addRst);

        } else {
            $processModel = new ProcessModel();
            $processData  = $processModel->getProcessData();
            $auditIds = $this->getRoleStaffIds(self::AUDIT_ROLE);

            $map['id'] = ['in', $auditIds];
            $map['loginstatus'] = ['neq','3'];

            $auditorArr = M('staff')->where($map)->field('id,name')->select();
            $this->assign(compact('processData','auditorArr'));
            $this->display();
        }

    }


    public function editApplication()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $processAppModel = new ProcessApplicationModel();
            $msg = $processAppModel->updateStatus($this->posts['processId'], $this->posts['flag'], $this->posts['reason']);
            $this->ajaxReturn($msg);
        } else {
            $this->display();
        }
    }
}