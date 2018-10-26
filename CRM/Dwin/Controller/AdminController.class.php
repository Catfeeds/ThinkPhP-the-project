<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/5/4
 * Time: 下午5:35
 */
namespace Dwin\Controller;

use Dwin\Model\StaffChangeModel;
use Dwin\Model\StaffContactModel;
use Dwin\Model\StaffContractModel;
use Dwin\Model\StaffDepartureModel;
use Dwin\Model\StaffEducationModel;
use Dwin\Model\StaffInfoModel;
use Dwin\Model\StaffInfoRecordModel;
use Think\Upload;

class AdminController extends CommonController
{
    /**
     *提交数据返回状态
     * 200 成功 400 失败 403 禁止操作
     */
    const SUCCESS_STATUS = 200;
    const FAIL_STATUS = 400;
    const FORBIDDEN_STATUS = 403;

    const ON_JOB = 1;
    const UN_JOB = 0;

    const RETURN_TABLES = 1;
    const RETURN_OTHER = 2;

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * @name index
     * @access public
     * @abstract 在职员工基本信息页面
     */
    public function index()
    {
        $staffInfoModel = new StaffInfoModel();
        if (IS_POST) {
            $this->posts = I('post.');
            if (!empty($this->posts['flag']) && $this->posts['flag'] == self::RETURN_TABLES) {
                $this->sqlCondition = $this->getSqlCondition($this->posts);
                $this->whereCondition['on_job'] = ['EQ', self::ON_JOB];

                $count = M('staff_info')->where($this->whereCondition)->count('id');
                if ($this->sqlCondition['search']) {
                    $c = new \SphinxClient();
                    $c->setServer('localhost', 9312);
                    $c->setMatchMode(SPH_MATCH_ALL);
                    $c->setLimits(0,1000);
                    $datai = $c->Query(trim($this->sqlCondition['search']), 'staff_info');
                    $index = array_keys($datai['matches']);
                    $c->close();
                    $primaryIds = implode(',', $index);
                    $data['pri'] = $datai;
                    if ($primaryIds) {
                        $this->whereCondition['id'] = ['IN', $primaryIds];
                    }
                }

                $data = $staffInfoModel->index($this->sqlCondition, $this->whereCondition);
                $sql = $staffInfoModel->getLastSql();
                foreach ($data as &$val) {
                    $val['DT_RowId'] = $val['employee_id'];
                }
                $filterCount = M('staff_info')->where($this->whereCondition)->count('id');

                $this->output = $this->getDataTableOut($this->posts['draw'], $count, $filterCount, $data);
                $this->output['search'] = $sql;
                $this->ajaxReturn($this->output);
            } elseif (!empty($this->posts['flag']) && $this->posts['flag'] == self::RETURN_OTHER) {
                $returnArray = ['contactData', 'eduData', 'employeeDevelopment', 'punishData', 'contractData', 'postChange'];
                $employeeId = $this->posts['employee_id'];
                $this->returnAjaxMsg('成功返回', self::SUCCESS_STATUS, $staffInfoModel->getStaffDataById($employeeId, $returnArray));

            } else {
                $this->returnAjaxMsg('非法操作，未回传数据', self::FORBIDDEN_STATUS);
            }
        } else {
            $this->display();
        }
    }


    /**
     * 联系方式展示页
     */
    public function contractIndex()
    {
        if (IS_POST){
            $params = I('post.');
            $tableData = $this->dataTable($params);
            $model = M('staff_contract');
            $data['draw'] = (int) $params['draw'];
            $data['recordsTotal'] = $model -> count();
            $data['recordsFiltered'] = $model -> where($tableData['map']) -> count();
            $data['data'] = $model
                 -> field("id, employee_id, `name`, contract_id, contract_type, FROM_UNIXTIME(start_time, '%Y-%m-%d') start_time, end_time, sign_count, FROM_UNIXTIME(probation_start_time, '%Y-%m-%d') probation_start_time, FROM_UNIXTIME(probation_end_time, '%Y-%m-%d') probation_end_time, FROM_UNIXTIME(update_time, '%Y-%m-%d') update_time, duration, tips")
                 -> where($tableData['map'])
                 -> order($tableData['order'])
                 -> limit($params['start'], $params['length'])
                 -> select();
            $this->ajaxReturn($data);
        }else{
            $this->display();
        }
    }

    /**
     * 人员异动展示页
     */
    public function changeIndex()
    {
        if (IS_POST){
            $params = I('post.');
            $tableData = $this->dataTable($params);
            $model = M('staff_change');
            $data['draw'] = (int) $params['draw'];
            $data['recordsTotal'] = $model -> count();
            $tableData['map']['is_del'] = ['eq', StaffChangeModel::NO_DEL];
            $data['recordsFiltered'] = $model -> where($tableData['map']) -> count();
            $data['data'] = $model
                -> where($tableData['map'])
                -> order($tableData['order'])
                -> limit($params['start'], $params['length'])
                -> select();

            foreach ($data['data'] as $key => &$value) {
                $value['change_time'] = date('Y-m-d H:i:s', $value['change_time']);
                $value['exec_time'] = date('Y-m-d H:i:s', $value['exec_time']);
            }
            $this->ajaxReturn($data);
        }else{
            $this->display();
        }
    }

    /**
     * @name employee
     * @access public
     * @abstract 添加新员工 基本信息 简历 照片  联系方式 学历 面试情况等一次上传。
     * @todo :contact eduction两部分的信息需加上employee_id
     */

    public function addEmployee()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $staffInfoModel = new StaffInfoModel();
//            $staffContactModel = new StaffContactModel();
//            $staffEduModel = new StaffEducationModel();
            if (!empty($this->posts['flag']) && $this->posts['flag'] == 'selectInfo') {
                $deptInfo = M('dept')->select();
                $dept = getTree($deptInfo, 0, 0, 'parent_id');
                $postInfo = M('auth_role')->field('role_id, role_name name')->select();
                $this->returnAjaxMsg('OK', self::SUCCESS_STATUS, compact('dept', 'postInfo'));
            }

            if (!empty($this->posts['flag']) && $this->posts['flag'] == 'postStaffData') {

                $uniqueCheckRst = $staffInfoModel->checkUnique($this->posts['form']['employee_id']);
                if ($uniqueCheckRst['status'] != self::SUCCESS_STATUS) {
                    $this->ajaxReturn($uniqueCheckRst);
                }
                $staffInfoData = $staffInfoModel->getAddData($this->posts['form']);
                if ($staffInfoData['status'] != self::SUCCESS_STATUS) {
                    $this->ajaxReturn($staffInfoData);
                }
//                $staffContactData = $staffContactModel->getAddData($this->posts['contact']);
//
//                if ($staffContactData['status'] != self::SUCCESS_STATUS) {
//                    $this->ajaxReturn($staffContactData);
//                }
//                $staffEduData = $staffEduModel->getAddData($this->posts['education']);
//
//                if ($staffEduData['status'] != self::SUCCESS_STATUS) {
//                    $this->ajaxReturn($staffEduData);
//                }
//                $addEmployeeRst = $staffInfoModel->addEmployeeTrans($staffInfoData['data'], $staffContactData['data'], $staffEduData['data']);
                $addEmployeeRst = $staffInfoModel->addEmployeeTrans($staffInfoData['data']);
                $this->ajaxReturn($addEmployeeRst);
            } else {
                $this->returnAjaxMsg('FORBIDDEN', self::FORBIDDEN_STATUS);
            }
        } else {
            $this->display();
        }

    }


    public function uploadEmployeeFile()
    {
        $this->posts = I('post.');
        $uploadArray = ['uploadPhoto', 'uploadResume', 'uploadPapers'];
        if (!empty($this->posts['flag']) && (in_array($this->posts['flag'], $uploadArray))) {
            $staffInfoModel = new StaffInfoModel();
            $uploadRst = $staffInfoModel->uploadData($this->posts['flag'], $this->posts['index'], $_FILES);
            $this->ajaxReturn($uploadRst);
        } else {
            $this->returnAjaxMsg('FORBIDDEN', self::FORBIDDEN_STATUS);
        }
    }

    /**
     * @name editEmployee
     * @access public
     * @abstract 编辑员工信息 根据编辑的flag编辑不同信息（基本、联系方式、学历等）
     *
     * @todo 修改员工数据  有部门、职位异动，添加异动记录。
     */

    public function editEmployee()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $staffInfoModel = new StaffInfoModel();
            if (!empty($this->posts['employee_id'])) {
                $returnArr = ['basicData'];
                $data = $staffInfoModel->getStaffDataById($this->posts['employee_id'], $returnArr);
                if ($data[$returnArr[0]]) {
                    $toTimestampArr = ['entry_time', 'birthday'];
                    foreach ($data[$returnArr[0]] as $key => &$value) {
                        if (in_array($key, $toTimestampArr)) {
                            $value = strtotime($value);
                            $value *= 1000;
                        }
                    }
                    $this->returnAjaxMsg('获取编辑前信息成功', self::SUCCESS_STATUS, $data);
                } else {
                    $this->returnAjaxMsg('获取数据出错', self::FAIL_STATUS, $data);
                }
            }
            if (!empty($this->posts['form'])) {
                $data = I('post.form');
                $toTimestampArr = ['entry_time', 'birthday'];
                foreach ($data as $key => &$value) {
                    if (in_array($key, $toTimestampArr)) {
                        $value /= 1000;
                    }
                }
                $oldEmployeeId = M('staff_info') -> find($data['id'])['employee_id'];
                $employeeIsChange = $data['employee_id'] != $oldEmployeeId;
                $staffInfoModel->startTrans();
                $employeeIdUpdate = true;
                if ($employeeIsChange){
                    $employeeIdUpdate = $staffInfoModel -> changeEmployeeId($oldEmployeeId, $data['employee_id']);
                }
                $data['update_time'] = time();
                $recordModel = new StaffInfoRecordModel();
                $recordUpdate = $recordModel->putEmployee($data);
                $infoUpdate = $staffInfoModel->save($data);
                if ($infoUpdate && $recordUpdate && $employeeIdUpdate) {
                    $status = self::SUCCESS_STATUS;
                    $msg = '修改成功';
                    $staffInfoModel->commit();
                } else {
                    $staffInfoModel->rollback();
                    if (!$infoUpdate) {
                        $msg = '更新失败';
                    }
                    if (!$recordUpdate) {
                        $msg = '记录更新失败';
                    }
                    if (!$recordUpdate) {
                        $msg = '员工编号更新失败';
                    }
                    $status = self::FAIL_STATUS;
                }
                $this->ajaxReturn([
                    'status' => $status,
                    'msg' => $msg
                ]);
            }
        } elseif (IS_GET) {
            $params = I('get.');
            $this->assign('employee_id', $params['employee_id']);
            $this->display('addEmployee');
        } else {
            die('非法操作');
        }
    }
    /**
     * 获取员工id接口
     * 需要获取 company 信息
     */
    public function getEmployeeId()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $array = ['北京', '湖南'];
            if (!empty(session('staffId')) && !empty($this->posts['company']) && in_array($this->posts['company'], $array)) {
                $idInfo = $this->getOrderNumber('employee_id');

                $employeeId = $this->posts['company'] == '北京' ? "M" . date('Y') . $idInfo['orderId'] : "H" . date('Y') . $idInfo['orderId'];
                $this->returnAjaxMsg('返回员工ID', self::SUCCESS_STATUS, $employeeId);
            } else {
                if (!empty($this->posts['employee_id'])) {
                    $staffInfoModel = new StaffInfoModel();
                    $uniqueCheckRst = $staffInfoModel->checkUnique($this->posts['employee_id']);
                    $this->ajaxReturn($uniqueCheckRst);
                }
                $this->returnAjaxMsg('非法操作', self::FORBIDDEN_STATUS);
            }

        } else {
            die('非法操作');
        }
    }

    /**
     * 获取简历路径
     */
    public function getEmployeeResume()
    {
        if (IS_GET) {
            $employeeId = I('get.employee_id');
            if ($employeeId) {
                $staffInfoModel = new StaffInfoModel();
                $urlData = $staffInfoModel->getResumeUrl($employeeId);
                if ($urlData) {
                    $flag = file_exists($_SERVER['DOCUMENT_ROOT'] . $urlData);
                    if ($flag) {
                        $this->returnAjaxMsg('简历路径获取成功', self::SUCCESS_STATUS, $urlData);
                    } else {
                        $this->returnAjaxMsg('简历路径有误', self::FAIL_STATUS, $urlData);
                    }
                } else {
                    $this->returnAjaxMsg('可能没有上传简历、或者路径错误', self::FAIL_STATUS);
                }

            } else {
                $this->returnAjaxMsg('员工信息错误', self::FORBIDDEN_STATUS);
            }

        } else {
            die('非法操作');
        }
    }


    public function uploadFile()
    {
        if (IS_POST) {
            $staffInfoModel = new StaffInfoModel();

            $uploadArray = ['uploadPhoto', 'uploadResume', 'uploadPapers'];
            if (!empty($this->posts['flag']) && (in_array($this->posts['flag'], $uploadArray))) {

                $uploadRst = $staffInfoModel->uploadData($this->posts['flag'], $this->posts['index'], $_FILES);
                $this->ajaxReturn($uploadRst);

            } else {
                $this->returnAjaxMsg('false', self::FORBIDDEN_STATUS);
            }
        } else {
            die('非法操作');
        }

    }

    /**
     * 新增员工离职
     * @param $id   int     staff_info id
     * @param $data array   staff_departure表的字段
     * @return json: status, msg
     */
    public function postDeparture($id)
    {
        $map = [
            'employee_id' => ['EQ', $id]
        ];
        if (IS_POST) {
            $params = I('post.data');
            $params['auditor'] = session('nickname');
            $params['update_time'] = time();
            M()->startTrans();
            $staffInfoUpdate = M('staff_info')->where($map)->save(['on_job' => self::UN_JOB]);
            $departureUpdate = M('staff_departure')->add($params);
            if ($staffInfoUpdate && $departureUpdate) {
                M()->commit();
                $this->ajaxReturn([
                    'status' => self::SUCCESS_STATUS,
                    'msg' => '提交成功'
                ]);
            } else {
                M()->rollback();
                $this->ajaxReturn([
                    'status' => self::FAIL_STATUS,
                    'msg' => '更新失败'
                ]);
            }
        } else {
            $staffInfo = M('staff_info')->field("name, department, position, employee_id, FROM_UNIXTIME(entry_time,'%Y-%m-%d') entry_time")->where($map)->find();
            $this->assign(compact('staffInfo'));
            $this->display();
        }
    }

    /**
     * 新增员工异动
     * @param $id   int     staff_info employee_id
     * @param $data     array       staff_change表的主体字段
     * @return json: status, msg
     */
    public function postChange($id)
    {
        if (IS_POST) {
            $params = I('post.data');
            $params['change_time'] /= 1000;
            $params['exec_time'] /= 1000;
            $params['auditor'] = session('nickname');
            $params['update_time'] = time();
            if ($params['change_type'] != "试用期转正" && $params['change_new_dept'] == $params['change_old_dept'] && $params['change_new_position'] == $params['change_old_position'] && (empty($params['change_new_salary'] && $params['change_new_dept'] != 0))){
                $this->ajaxReturn([
                    'status' => self::FAIL_STATUS,
                    'msg' => '你没有提交任何改动'
                ]);
            }else{
                $data = [
                    'department' => $params['change_new_dept'],
                    'position' => $params['change_new_position'],
                ];
                $map = [
                    'employee_id' => ['EQ', $params['employee_id']]
                ];
                M()->startTrans();
                $infoUpdate = M('staff_info')->where($map)->save($data) === false ? false : true;
                $changeUpdate = M('staff_change')->add($params) === false ? false : true;
                $res = $infoUpdate && $changeUpdate;
                if ($res === false) {
                    M()->rollback();
                } else {
                    M()->commit();
                }
            }
            if ($res) {
                $this->ajaxReturn([
                    'status' => self::SUCCESS_STATUS,
                    'msg' => '添加成功'
                ]);
            } else {
                $this->ajaxReturn([
                    'status' => self::FAIL_STATUS,
                    'msg' => '添加失败'
                ]);
            }
        } else {
            $map = [
                'employee_id' => ['EQ', $id]
            ];
            $deptInfo = M('dept')->select();
            $dept = getTree($deptInfo, 0, 0, 'parent_id');
            $postInfo = M('auth_role')->field('role_id, role_name name')->select();
            $selectInfo = compact('dept', 'postInfo');
            $staffInfo = M('staff_info')->field("name, department, position, employee_id, FROM_UNIXTIME(entry_time, '%Y-%m-%d') entry_time")->where($map)->find();
            $this->assign(compact('staffInfo', 'selectInfo'));
            $this->display();
        }
    }

    /**
     * 修改员工异动
     */
    public function editChange()
    {
        $staffChangeModel = new StaffChangeModel();
        if (IS_POST) {
            $params = I('post.');

            if(empty($params['id'])){
                $this->returnAjaxMsg("参数错误",400);
            }
            $data = $staffChangeModel->editChange($params);
            $this->ajaxReturn($data);
        } else {
            $id = I('get.id');
            $changeData = $staffChangeModel->find($id);

            $deptInfo = M('dept')->select();
            $dept = getTree($deptInfo, 0, 0, 'parent_id');
            $postInfo = M('auth_role')->field('role_id, role_name name')->select();
            $deptInfo = compact('dept', 'postInfo');

            $this->assign([
                'deptInfo' => $deptInfo,  // 部门列表
                'changeData' => $changeData // 异动信息
            ]);
            $this->display();
        }
    }

    /**
     * 删除员工异动信息
     */
    public function delChange(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                $this->returnAjaxMsg("参数错误",400);
            }
            $staffChangeModel = new StaffChangeModel();
            $data = $staffChangeModel->delChange($id);
            $this->ajaxReturn($data);
        }else{
            die("非法");
        }
    }

    /**
     * 展示合同管理
     * @param $id   int     staff_info employee_id
     * @return json     该用户所有的合同信息
     */
    public function getContract()
    {
        if (IS_POST) {
            $map['employee_id'] = ['eq', I('get.id')];
            $contractModel = new StaffContractModel();
            $data = $contractModel->getContract($map);
            if (empty($data)) {
                $this->returnAjaxMsg('数据为空', self::FAIL_STATUS);
            } else {
                $timestampArr = ['start_time', 'end_time', 'probation_start_time', 'probation_end_time', 'update_time'];
                foreach ($data as $key1 => &$value1) {
                    $value1['flag'] = 'get';
                    foreach ($value1 as $key2 => &$value2) {
                        if (in_array($key2, $timestampArr)){
                            $value2 *= 1000;
                        }
                    }
                }
                $this->returnAjaxMsg('ok', self::SUCCESS_STATUS, $data);
            }
        } else {
            $id = I('get.id');
            $map['employee_id'] = ['eq', I('get.id')];
            $staffInfo = M('staff_info') -> field('name, department, position, employee_id')  -> where($map) -> find();
            $this->assign(compact('staffInfo','id'));
            $this->display('contract');
        }
    }

    /**
     * 修改合同信息
     * @param  $data  经过修改后的完整的该用户所有的合同数据
     */
    public function putContract()
    {
        if (IS_POST) {
            $contractModel = new StaffContractModel();
            $updateData = $contractModel->getEditData(I('post.data'));
            if ($updateData['status'] == self::FAIL_STATUS) {
                $this->ajaxReturn($updateData);
            } else {
                $updateRst = $contractModel->editContract($updateData['data'], $updateData['msg']);
                $this->ajaxReturn($updateRst);
            }
        }
    }

    /**
     * 删除合同
     * @param $id int id
     */
    public function delContract()
    {
        if (IS_POST) {
            $delId = I('post.id');
            $contractModel = new StaffContractModel();
            $delRst = $contractModel->deleteData($delId);
            $this->ajaxReturn($delRst);
        }
    }

    /**
     * 新增合同
     * @param $data     staff_contract表字段
     */
    public function addContract()
    {
        if (IS_POST) {
            $contractModel = new StaffContractModel();
            $addData = $contractModel->getAddData(I('post.data'));
            if ($addData['status'] == self::FAIL_STATUS) {
                $this->ajaxReturn($addData);
            }
            $addRst  = $contractModel->addContract($addData['data'], $addData['msg']);
            $this->ajaxReturn($addRst);
        }
    }

    /**
     * 根据表获取的基础信息
     * @param $table    string 表
     * @param $id       职员编号
     * @param array $JSTimestampArr     array   需要转换js时间戳字段的数组
     * @param array $strTimeArr     array   需要转换成字符串形式的时间戳字段的数组
     */
    private function baseGet($table, $id, $JSTimestampArr = [], $strTimeArr = [])
    {
        $map = [
            'employee_id' => ['EQ', $id]
        ];
        $data = M($table) -> where($map) -> select();
        foreach ($data as $key => &$value) {
            foreach ($value as $key1 => &$value1) {
                if (in_array($key1, $JSTimestampArr)){
                    $value1 *= 1000;
                }
                if (in_array($key1, $strTimeArr)){
                    $value1 = date('Y-m-d H:i:s', $value1);
                }
            }
            $value['flag'] = 'get';
        }
        $this->ajaxReturn([
            'data' => $data,
        ]);
    }

    /**
     * 联系人管理
     * @param $id int   staff_info employee_id
     * @param $data     staff_contact表基础字段
     */
    public function putContact()
    {
        $contactModel = new StaffContactModel();
        $updateData = $contactModel->getEditData(I('post.data'));
        if ($updateData['status'] == self::FAIL_STATUS) {
            $this->ajaxReturn($updateData);
        } else {
            $updateRst = $contactModel->editContract($updateData['data'], $updateData['msg']);
            $this->ajaxReturn($updateRst);
        }
    }

    /**
     * 删除联系人
     * @params $id
     */
    public function delContact()
    {
        if (IS_POST) {
            $delId = I('post.id');
            $contractModel = new StaffContactModel();
            $delRst = $contractModel->deleteData($delId);
            $this->ajaxReturn($delRst);
        }
    }

    /**
     * 新增联系人
     * @param $data     staff_contact表字段
     */
    public function addContact()
    {
        if (IS_POST) {
            $contactModel = new StaffContactModel();
            $addData = $contactModel->getAddData(I('post.data'));
            if ($addData['status'] == self::FAIL_STATUS) {
                $this->ajaxReturn($addData);
            }
            $addRst  = $contactModel->addContact($addData['data'], $addData['msg']);
            $this->ajaxReturn($addRst);
        }
    }

    /**
     * 根据id获取该员工的联系人详情
     * @param $id   职员编号
     * @return  返回职员编号对应职员的全部联系人信息
     */
    public function getContact($id)
    {
        if (IS_POST){
            $this->baseGet('staff_contact', $id);
        }else{
            $this->assign(compact('id'));
            $this->display('contact');
        }
    }


    /**
     * 更新教育信息
     * @param   data    staff_education表字段
     *
     */
    public function putEducation()
    {
        $eduModel = new StaffEducationModel();
        $updateData = $eduModel->getEditData(I('post.data'));
        if ($updateData['status'] == self::FAIL_STATUS) {
            $this->ajaxReturn($updateData);
        } else {
            $updateRst = $eduModel->editEdu($updateData['data'], $updateData['msg']);
            $this->ajaxReturn($updateRst);
        }
    }

    /**
     * 新增教育信息
     * @param $data     staff_education表字段
     */
    public function addEducation()
    {
        if (IS_POST) {
            $eduModel = new StaffEducationModel();
            $addData = $eduModel->getAddData(I('post.data'));
            if ($addData['status'] == self::FAIL_STATUS) {
                $this->ajaxReturn($addData);
            }
            $addRst  = $eduModel->addEdu($addData['data'], $addData['msg']);
            $this->ajaxReturn($addRst);
        }
    }

    /**
     * 删除教育信息
     * @param $id   对应staff_education表中的id
     */
    public function delEducation()
    {
        if (IS_POST) {
            $delId = I('post.id');
            $eduModel = new StaffEducationModel();
            $delRst = $eduModel->deleteData($delId);
            $this->ajaxReturn($delRst);
        }
    }

    /**
     * 根据职员编号获取教育信息
     * @param $id
     */
    public function getEducation($id)
    {
        if (IS_POST){
            $this->baseGet('staff_education', $id);
        }else{
            $name = M('staff_info') -> where(['employee_id' => ['EQ', $id]]) -> getField('name');
            $this->assign(compact('id', 'name'));
            $this->display('education');
        }
    }

    /**
     * 删除员工发展
     * @param $id string    staff_info employee_id
     */
    public function delDevelop($id)
    {
        $model = M('staff_develop');
        $recordModel = new StaffInfoRecordModel();
        $model -> startTrans();
        $developUpdate = $model -> delete($id);
        $recordUpdate = $recordModel -> delDevelopRecord($id);
        if ($developUpdate !== false && $recordUpdate !== false){
            $msg = '删除成功';
            $status = self::SUCCESS_STATUS;
            $model -> commit();
        }else{
            $msg = '删除失败';
            $status = self::FAIL_STATUS;
            $model -> rollback();
        }
        $this->ajaxReturn([
            'status' => $status,
            'msg' => $msg
        ]);
    }

    /**
     * 更新员工发展信息
     * @param $data staff_develop表中的字段
     */
    public function putDevelop()
    {
        $params = I('post.data');
        $recordModel = new StaffInfoRecordModel();
        $model = M('staff_develop');
        $recordModel -> startTrans();
        $recordUpdate = $recordModel -> putDevelopRecord($params);
        $params['add_name'] = session('nickname');
        $params['add_time'] = time();
        $developUpdate = $model -> save($params);
        if ($developUpdate !== false && $recordUpdate !== false){
            $msg = '修改成功';
            $status = self::SUCCESS_STATUS;
            $model -> commit();
        }else{
            $msg = '修改失败';
            $status = self::FAIL_STATUS;
            $model -> rollback();
        }
        $this->ajaxReturn([
            'status' => $status,
            'msg' => $msg
        ]);
    }

    /**
     * 新增员工发展信息
     * @param   $data   staff_develop表字段
     */
    public function postDevelop()
    {
        $params = I('post.data');
        $recordModel = new StaffInfoRecordModel();
        $model = M('staff_develop');
        $recordModel -> startTrans();
        $recordUpdate = $recordModel -> postDevelopRecord($params);
        $params['add_name'] = session('nickname');
        $params['add_time'] = time();
        $developUpdate = $model -> add($params);
        if ($developUpdate !== false && $recordUpdate !== false){
            $msg = '新增成功';
            $status = self::SUCCESS_STATUS;
            $model -> commit();
        }else{
            $msg = '新增失败';
            $status = self::FAIL_STATUS;
            $model -> rollback();
        }
        $this->ajaxReturn([
            'status' => $status,
            'msg' => $msg
        ]);
    }

    /**
     * 获取奖惩信息
     * @param $id   employee_id
     * @return  返回staff_punish_rewards表对应employee_id的信息
     */
    public function getPunish($id)
    {
        if (IS_POST){
            $timestampArr = ['record_time'];
            $this->baseGet('staff_punish_rewards', $id, $timestampArr);
        }else{
            $this->assign(compact('id'));
            $this->display('punish');
        }
    }

    /**
     * 获取员工发展信息
     * @param $id   employee_id
     * @rerun   返回staff_develop表对应employee_id的信息
     */
    public function getDevelop($id)
    {
        if (IS_POST){
            $strTimeArr = ['add_time'];
            $this->baseGet('staff_develop', $id, [], $strTimeArr);
        }else{
            $this->assign(compact('id'));
            $this->display('develop');
        }
    }

    /**
     * 新增奖惩记录
     * @param   $data   staff_punish_rewards表字段
     */
    public function postPunish()
    {
        $params = I('post.data');
        $recordModel = new StaffInfoRecordModel();
        $model = M('staff_punish_rewards');
        $recordModel -> startTrans();
        $recordUpdate = $recordModel -> postPunishRecord($params);
        $params['add_name'] = session('nickname');
        $params['add_time'] = time();
        $map['employee_id'] = ['eq', $params['employee_id']];
        $params['name'] = M('staff_info')->where($map)->find()['name'];
        $developUpdate = $model -> add($params);
        if ($developUpdate !== false && $recordUpdate !== false){
            $msg = '新增成功';
            $status = self::SUCCESS_STATUS;
            $model -> commit();
        }else{
            $msg = '新增失败';
            $status = self::FAIL_STATUS;
            $model -> rollback();
        }
        $this->ajaxReturn([
            'status' => $status,
            'msg' => $msg
        ]);
    }

    /**
     * 删除奖惩记录
     * @param $id   staff_punish_rewards id
     */
    public function delPunish($id)
    {
        $model = M('staff_punish_rewards');
        $recordModel = new StaffInfoRecordModel();
        $model -> startTrans();
        $developUpdate = $model -> delete($id);
        $recordUpdate = $recordModel -> delPunishRecord($id);
        if ($developUpdate !== false && $recordUpdate !== false){
            $msg = '删除成功';
            $status = self::SUCCESS_STATUS;
            $model -> commit();
        }else{
            $msg = '删除失败';
            $status = self::FAIL_STATUS;
            $model -> rollback();
        }
        $this->ajaxReturn([
            'status' => $status,
            'msg' => $msg
        ]);
    }

    /**
     * 更新奖惩记录
     * @param   $data   staff_punish_rewards字段
     */
    public function putPunish()
    {
        $params = I('post.data');
        $recordModel = new StaffInfoRecordModel();
        $model = M('staff_punish_rewards');
        $recordModel -> startTrans();
        $recordUpdate = $recordModel -> putPunishRecord($params);
        $params['add_name'] = session('nickname');
        $params['add_time'] = time();
        $developUpdate = $model -> save($params);
        if ($developUpdate !== false && $recordUpdate !== false){
            $msg = '修改成功';
            $status = self::SUCCESS_STATUS;
            $model -> commit();
        }else{
            $msg = '修改失败';
            $status = self::FAIL_STATUS;
            $model -> rollback();
        }
        $this->ajaxReturn([
            'status' => $status,
            'msg' => $msg
        ]);
    }

    /**
     * 员工合同提醒和试用期到期提醒
     * @param   $type   1: 合同到期提醒   2: 试用期到期提醒
     * @param   $time   1: 一个月后     2: 两个月后
     */
    public function contractExpire()
    {
        dump(date('Y-m-t',strtotime('next month',time)));
        if (IS_POST){
            $map = [];
            if (I('post.type') == 1){
                $field = 'max_end_time';
            }
            if (I('post.type') == 2){
                $field = 'max_probation_end_time';
                $map['info.is_probation'] = ['EQ', 1];
            }
            if (I('post.time') == 1){
//                $time = strtotime('+1 months', time());
                $time = date('Y-m-t',strtotime('+1 month',time()));
            }
            if (I('post.time') == 2){
//                $time = strtotime('+2 months', time());
                $time = date('Y-m-t',strtotime('+2 month',time()));
            }
            $page = I('post.page');
            $map['info.on_job'] = ['EQ', 1];
            $data = M('staff_contract')
                 -> alias('contract')
                 -> field('contract.*, max(contract.end_time) max_end_time, max(contract.probation_end_time) max_probation_end_time')
                 -> join('left join crm_staff_info as info on contract.employee_id = info.employee_id')
                 -> where($map)
                 -> group('contract.employee_id')
                 -> having("$field < $time")
                 -> order($field, 'desc')
                 -> page($page, 10)
                 -> select();
            $total = M('staff_contract')
                -> alias('contract')
                -> field('max(contract.end_time) max_end_time, max(contract.probation_end_time) max_probation_end_time')
                -> join('left join crm_staff_info as info on contract.employee_id = info.employee_id')
                -> where($map)
                -> group('contract.employee_id')
                -> having("$field < $time")
                -> select();
            $total = count($total);
            $timestampArr = ['start_time', 'end_time', 'probation_start_time', 'probation_end_time', 'max_end_time', 'max_probation_end_time'];
            foreach ($data as $key1 => &$value1) {
                foreach ($timestampArr as $key2 => $value2) {
                    $value1[$value2] = date('Y-m-d', $value1[$value2]);
                }
            }
            $this->ajaxReturn([
                'data' => $data,
                'total' => $total
            ]);
        }else{
            $this->display();
        }
    }

    /**
     * 试用期到期提醒
     */
    public function probationExpire()
    {
        if (IS_POST){
            $this->contractExpire();
        }else{
            $this->display();
        }
    }

    /**
     * 试用期延期
     * @param $id   staff_contract id
     * @param $time    新试用期结束时间
     */
    public function prolongProbation($id)
    {
        $time = I('post.time');
        $time = strtotime($time);
        $res = M('staff_contract') -> save(['id' => $id, 'probation_end_time' => $time]);
        if ($res){
            $this->ajaxReturn([
                'status' => self::SUCCESS_STATUS,
                'msg' => '修改成功'
            ]);
        }else{
            $this->ajaxReturn([
                'status' => self::FAIL_STATUS,
                'msg' => '修改失败',
            ]);
        }
    }

    /**
     * 试用期转正
     * @param $id   employee_id
     */
    public function becomeMember($id)
    {
        $map = [
            'employee_id' => ['EQ', $id],
            'is_probation' => ['EQ', 1]
        ];
        $res = M('staff_info') -> where($map) -> save(['is_probation' => 0]);
        if ($res){
            $this->ajaxReturn([
                'status' => self::SUCCESS_STATUS,
                'msg' => '修改成功'
            ]);
        }else{
            $this->ajaxReturn([
                'status' => self::FAIL_STATUS,
                'msg' => '修改失败',
            ]);
        }
    }

	/** 离职员工管理*/
    public function departureIndex()
    {
        $staffDepartModel = new StaffDepartureModel();
        if (IS_POST) {
            $this->posts = I('post.');
            if (!empty($this->posts['flag']) && $this->posts['flag'] == self::RETURN_TABLES) {
                $this->sqlCondition = $this->getSqlCondition($this->posts);
                $this->whereCondition['_string'] = "1 = 1";

                $count = M('staff_departure')->where($this->whereCondition)->count('id');
                if ($this->sqlCondition['search']) {
                    $this->whereCondition['info.name|depart.position|depart.department|departure_type|departure_reason'] = ['like', '%' . $this->sqlCondition['search'] . '%'];
                }

                $data = $staffDepartModel->getIndexData($this->whereCondition, $this->sqlCondition);

                $filterCount = M('staff_departure')->alias('depart')->where($this->whereCondition)
                    ->join('LEFT JOIN crm_staff_info info ON depart.employee_id = info.employee_id')->count('depart.id');

                $this->ajaxReturn($this->getDataTableOut($this->posts['draw'], $count, $filterCount, $data));
            } elseif (!empty($this->posts['flag']) && $this->posts['flag'] == self::RETURN_OTHER) {
                $staffInfoModel = new StaffInfoModel();
                $returnArray = ['basicData', 'contactData', 'eduData', 'employeeDevelopment', 'punishData','complainData'];
                $employeeId = $this->posts['employee_id'];
                $this->returnAjaxMsg('成功返回', self::SUCCESS_STATUS, $staffInfoModel->getStaffDataById($employeeId, $returnArray));
            } else {
                $this->returnAjaxMsg('非法操作，未回传数据', self::FORBIDDEN_STATUS);
            }
        } else {
            $this->display('departureIndex');
        }

    }

    /**
     * 修改员工离职内容
     */
    public function editDeparture(){
        $departureModel = new StaffDepartureModel();
        if (IS_POST) {
            $params = I('post.');
            if(empty($params['id'])){
                $this->returnAjaxMsg("参数不全",200);
            }

            $data = $departureModel->editDeparture($params);
            $this->ajaxReturn($data);
        } else {
            $id = I('get.id');
            $departureData = $departureModel->find($id);

            $this->assign([
                'departureData' => $departureData, // 离职信息
            ]);
            $this->display();
        }
    }

    public function delDeparture(){
        $departureModel = new StaffDepartureModel();
        if (IS_POST) {
            $id = I('post.id');
            if(empty($id)){
                $this->returnAjaxMsg("参数不全",200);
            }

            $data = $departureModel->delDeparture($id);
            $this->ajaxReturn($data);
        } else {
            die("非法");
        }
    }

    /**
     * 离职员工复职
     */
    public function reinstateEmployee()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $staffInfoModel = new StaffInfoModel();
            $rst = $staffInfoModel->reinstate($this->posts['employee_id']);
            $this->ajaxReturn($rst);

        } else {
            $this->returnAjaxMsg('错误',self::FORBIDDEN_STATUS);
        }
    }

    protected function dataTable($params, $_map = [])
    {
        $dataField = [];
        $searchAble = [];
        foreach ($params['columns'] as $k => $v) {
            if (isset($_map[$v['data']])){
                $dataField[] = $_map[$v['data']];
            }else{
                $dataField[] = $v['data'];
            }
            if ($v['searchable'] == 'true'){
                if (isset($_map[$v['data']])){
                    $searchAble[] = $_map[$v['data']];
                }else{
                    $searchAble[] = $v['data'];
                }
            }
        }
        $order = $dataField[$params['order'][0]['column']] . ' ' . $params['order'][0]['dir'];
        if ($params['search']['value'] == ''){
            $map = [];
        }else{
            $searchAble = rtrim(implode('|', $searchAble), '|');
            $word = $params['search']['value'];
            $map = [$searchAble => ['LIKE',"%".$word."%"]];
        }
        return [
            'order' => $order,
            'map' => $map,
        ];
    }

    /**
     * 打印异动表单
     * @param $id   staff_change id
     */
    public function staffChangeForm($id)
    {
        $data = M('staff_change') -> find($id);
        $data['entry_time'] = date('Y-m-d', M('staff_info') -> where(['employee_id' => ['EQ', $data['employee_id']]]) -> getField('entry_time'));
        $data['exec_time'] = date('Y-m-d', $data['exec_time']);
        $data['year'] = date('Y', $data['change_time']);
        $data['month'] = date('m', $data['change_time']);
        $data['day'] = date('d', $data['change_time']);
        $this->assign(compact('data'));
        $this->display();
    }

    /**
     * 打印转正表单
     * @param $id   staff_change    id
     */
    public function regularStaffForm($id)
    {
        $data = M('staff_change') -> find($id);
//        dump($data);
        $contract = M('staff_contract') -> where(['employee_id' => ['EQ', $data['employee_id']]]) -> order('update_time desc') -> find();
//        dump($contract);
//        die;

        $data['entry_time'] = date('Y-m-d', M('staff_info') -> where(['employee_id' => ['EQ', $data['employee_id']]]) -> getField('entry_time'));
        $data['exec_time'] = date('Y-m-d', $data['exec_time']);
        $data['year'] = date('Y', $data['change_time']);
        $data['month'] = date('m', $data['change_time']);
        $data['day'] = date('d', $data['change_time']);
        $data['probation_start_time'] = date('Y-m-d', $contract['probation_start_time']);
        $data['probation_end_time'] = date('Y-m-d', $contract['probation_end_time']);
        $this->assign(compact('data'));
        $this->display();
    }

    /**
     * 打印离职证明
     * @param $id   staff_departure id
     */
    public function departureForm($id)
    {
        $data = M('staff_departure') -> find($id);
        $data = array_merge($data, M('staff_info') -> where(['employee_id' => ['EQ', $data['employee_id']]]) -> find());
        $data['departure_time'] = explode('-', date('Y-m-d', $data['departure_time']));
        $data['entry_time'] = explode('-', date('Y-m-d', $data['entry_time']));
        $field = $data['is_probation'] == 1 ? 'probation_end_time' : 'end_time';
        $contract = M('staff_contract') -> where(['employee_id' => ['EQ', $data['employee_id']]]) -> order("$field desc") -> find();
        if ($data['is_probation'] == 1){
            $data['start_time'] = $contract['probation_start_time'];
            $data['end_time'] = $contract['probation_end_time'];
        }else{
            $data['start_time'] = $contract['start_time'];
            $data['end_time'] = $contract['end_time'];
        }
        $data['start_time'] = explode('-', date('Y-m-d', $data['start_time']));
        $data['end_time'] = explode('-', date('Y-m-d', $data['end_time']));
        $this->assign(compact('data'));
        $this->display();
    }

    /**
     * 保存文件信息导入的Excel
     * @throws \PHPExcel_Reader_Exception
     */
    public function StaffInfoExcelUpload()
    {
        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/fileUpload/" . "temp/";
        if (!file_exists($rootPath)) {
            mkdir($rootPath);
        }
        $saveName = uniqid('productExcel');
        $cfg = [
            'autoSub' => false,
            'saveName' => $saveName,
            'rootPath' => $rootPath, // 保存根路径
            'replace'  => true,
        ];
        $upload = new \Think\Upload($cfg);// 实例化上传类

        // 上传单个文件
        $info   =   $upload->uploadOne($_FILES['file']);
        if(!$info) {// 上传错误提示错误信息
            $this->ajaxReturn([
                'status' => self::FAIL_STATUS,
                'msg' => $upload->getError(),
            ]);
        }else{// 上传成功 获取上传文件信息
            $this->importInfoFromExcel($rootPath . $saveName . '.' . $info['ext']);
        }
    }

    public function importStaffInfoFromExcel()
    {
        $this->display();
    }

    /**
     * 从Excel中导入员工信息
     * @param $filePath
     * @throws \PHPExcel_Reader_Exception
     */
    public function importInfoFromExcel($filePath)
    {
        Vendor('PHPExcel.PHPExcel');//引入类
        $reader = \PHPExcel_IOFactory::createReader('Excel2007');
        $PHPExcel = $reader->load($filePath); // 文档名称
        $sheetData = $PHPExcel->getActiveSheet()->toArray(null, true, true, true);
        $sheetData = array_values($sheetData);
        $titleArr = array_shift($sheetData);
        $newTitleArr = [];
        $map = [
            'employee_id' => '职员编号',
            'name' => '姓名',
            'department' => '部门',
            'position' => '职位',
            'sex' => '性别',
            'education' => '教育程度',
            'birth_place' => '籍贯',
            'nation' => '民族',
            'politics_status' => '政治面貌',
            'id_card_no' => '身份证号',
            'age' => '年龄',
            'census_type' => '户口类别',
            'census_place' => '户口所在地',
            'marital_status' => '婚姻状况',
            'health_status' => '健康状况',
            'working_time' => '参加工作时间',
            'auditor' => '录入人',
            'entry_time' => '入职时间',
            'birthday' => '生日',
            'update_time' => '更新时间',
            'on_job' => '是否在职',
            'is_probation' => '是否在试用期',
        ];

        foreach ($map as $key => $value) {
            $index = array_search($value, $titleArr);
            if ($index !== false) {
                $newTitleArr[$index] = $key;
            }
        }
        $data = [];
        $model = M('staff_info');
        $model -> startTrans();
        $res = true;
        $timestampArr = ['entry_time', 'birthday', 'update_time'];
        foreach ($sheetData as $key1 => &$value1) {
            $data[$key1] = [];
            foreach ($value1 as $key2 => $value2) {
                if ($newTitleArr[$key2] != '' && $value2 != '' && $value2 != '***'){
                    if (in_array($newTitleArr[$key2], $timestampArr)){
                        $data[$key1][$newTitleArr[$key2]] = strtotime($value2);
                    }else{
                        $data[$key1][$newTitleArr[$key2]] = trim($value2);
                    }
                }
            }
            $isAdd = M('staff_info') -> where(['employee_id' => ['EQ', $data[$key1]['employee_id']], 'name' => ['EQ', $data[$key1]['name']]]) -> count() == 0 ? true : false;
            $data[$key1]['is_add'] = $isAdd;
            if ($isAdd){
                $data[$key1]['add_time'] = time();
                $res = M('staff_info') -> add($data[$key1]);
            }else{
                $res = M('staff_info') -> where(['employee_id' => ['EQ', $data[$key1]['employee_id']], 'name' => ['EQ', $data[$key1]['name']]]) -> save($data[$key1]);
            }
            if ($res === false){
                $flag = $data[$key1];
                break;
            }
        }
        unlink($filePath);
        if ($res !== false){
            $model -> commit();
            $this->ajaxReturn([
                'status' => self::SUCCESS_STATUS,
                'msg'    => '上传更新系统人员信息成功',
            ]);
        }else{
            $model -> rollback();
            $this->ajaxReturn([
                'status' => self::FAIL_STATUS,
                'msg' => '上传失败',
                'data' => $flag
            ]);
        }
    }

    protected function exportData($map, $timestampArr,$config)
    {
        $title = [];
        $field = '';
        foreach ($map as $key => $value) {
            $title[] = $value;
            if (in_array($key, $timestampArr)){
                $field .= "from_unixtime($key,'%Y-%m-%d') $key". ',';
            }else{
                $field .= $key . ',';
            }
        }
        $field = rtrim($field, ',');
        $letterArr = range('A', 'Z');
        Vendor('PHPExcel.PHPExcel');//引入类
        $excel = new \PHPExcel();
        $excel->getProperties()
            ->setCreator(session('nickname'))
            ->setLastModifiedBy("Dwin")
            ->setTitle($config['title'])
            ->setSubject("statistics")
            ->setDescription($config['description'])
            ->setKeywords("statistics")
            ->setCategory($config['category']);
        $excel->getSecurity()->setLockWindows(true);
        $excel->getSecurity()->setLockStructure(true);
        $excel->getSecurity()->setWorkbookPassword("dwin_set_2002_hunan_beijing");
        $sheet = $excel -> getActiveSheet();
        foreach ($title as $key => $value) {
            $sheet->setCellValue($letterArr[$key] . 1,$value);
        }
        $data = M($config['table'])
            -> field($field)
            ->where($config['map'])
            -> select();
        foreach ($data as $key1 => $value1) {
            $value1 = array_values($value1);
            foreach ($value1 as $key2 => $value2) {
                $sheet->setCellValue($letterArr[$key2] . ($key1 + 2), ''.$value2);
            }
        }
        $objwriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $fileName = uniqid($config['prefix']). '_' . date('YmdH') . '.xlsx';
        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/excel/";
        $objwriter->save($rootPath . $fileName);
        $this->returnAjaxMsg('导出成功',self::SUCCESS_STATUS, UPLOAD_ROOT_PATH  . '/excel/' . $fileName);
    }
    /**
     * 导出员工信息
     */
    public function exportStaffInfo()
    {
        $map = [
            'employee_id' => '职员编号',
            'name' => '姓名',
            'department' => '部门',
            'position' => '职位',
            'sex' => '性别',
            'education' => '教育程度',
            'birth_place' => '籍贯',
            'nation' => '民族',
            'politics_status' => '政治面貌',
            'id_card_no' => '身份证号',
            'age' => '年龄',
            'census_type' => '户口类别',
            'census_place' => '户口所在地',
            'marital_status' => '婚姻状况',
            'health_status' => '健康状况',
            'working_time' => '参加工作时间',
            'auditor' => '录入人',
            'entry_time' => '入职时间',
            'birthday' => '生日',
            'update_time' => '更新时间',
            'on_job' => '是否在职',
            'is_probation' => '是否在试用期',
        ];
        $timestampArr = ['entry_time', 'birthday', 'update_time'];
        $config = [
            'title'         => "DWIN_STAFF_INFO",
            'description'   => "staff_info",
            'category'      => "人员信息",
            'table'         => 'staff_info',
            'map'           => ['on_job' => ['eq', 1]],
            'prefix'        => 'staffInfo'
        ];
        $this->exportData($map, $timestampArr, $config);
    }

    public function exportStaffPunish()
    {
        $map = [
            'employee_id' => '职员编号',
            'name'        => '姓名',
            'type'        => '奖/惩',
            'record_time' => '时间',
            'reason'      => '原因',
            'fee'         => '奖惩金额',
            'score'       => '+/-分',
            'add_name'    => '添加人',
            'add_time'    => '添加时间'
        ];
        $config = [
            'title'         => "DWIN_PUNISH",
            'description'   => "staff_punish_rewards",
            'category'      => "奖惩记录",
            'table'         => 'staff_punish_rewards',
            'map'           => ['add_time'=>['gt', strtotime(date('Y',time()).'-01-01 00:00:00')]],
            'prefix'        => 'staffPunish'
        ];
        $timestampArr = ['record_time', 'add_time'];
        $this->exportData($map,$timestampArr,$config);
    }

    /**
     * 导出员工信息
     */
    public function exportStaffChange()
    {
        $map = [
            'employee_id' => '职员编号',
            'name'        => '姓名',
            'change_type' => '异动类型',
            'change_time' => '异动时间',
            'change_old_dept' => '异动前部门',
            'change_new_dept' => '异动后部门',
            'change_old_position' => '异动前职位',
            'change_new_position' => '异动后职位',
            'change_old_salary' => '异动前薪资',
            'change_new_salary' => '异动后薪资',
            'exec_time' => '执行时间',
            'update_time' => '更新时间'
        ];
        $config = [
            'title'         => "DWIN_STAFF_CHANGE",
            'description'   => "staff_change",
            'category'      => "人员异动",
            'table'         => 'staff_change',
            'map'           => ['update_time'=>['gt', strtotime(date('Y',time()).'-01-01 00:00:00')]],
            'prefix'        => 'staffchange'
        ];
        $timestampArr = ['change_time', 'exec_time', 'update_time'];
        $this->exportData($map, $timestampArr, $config);
    }

    /**
     * 导出员工信息
     */
    public function exportStaffDeparture()
    {
        $map = [
            'employee_id' => '职员编号',
            'name' => '姓名',
            'department' => '部门',
            'position' => '职位',
            'departure_time' => '离职时间',
            'departure_type' => '离职类型',
            'departure_reason' => '离职原因',
            'tips' => '备注',
            'update_time' => '提交时间'
        ];
        $config = [
            'title'         => "DWIN_STAFF_DEPARTURE",
            'description'   => "staff_departure",
            'category'      => "人员异动",
            'table'         => 'staff_departure',
            'map'           => ['update_time'=>['gt', strtotime(date('Y',time()).'-01-01 00:00:00')]],
            'prefix'        => 'staffDeparture'
        ];
        $timestampArr = ['departure_time', 'update_time'];
        $this->exportData($map, $timestampArr, $config);
    }

    /**
     * 修改员工编号时判断是否重复
     * @param $employee_id
     * @param $name
     */
    public function checkEmployeeIdEdit($employee_id, $name)
    {
        $res = M('staff_info') -> where(['employee_id' => ['EQ', $employee_id], 'name' => ['EQ', $name]]) -> count() > 1 ? false : true;
        if ($res){
            $this->ajaxReturn([
                'status' => self::SUCCESS_STATUS,
                'msg' => 'ok'
            ]);
        }else{
            $this->ajaxReturn([
                'status' => self::FAIL_STATUS,
                'msg' => 'id重复'
            ]);
        }
    }
}
