<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/5/5
 * Time: 上午9:33
 */

namespace Dwin\Model;


use Think\Model;

use Think\Upload;

class StaffInfoModel extends Model
{
    const SUCCESS_STATUS   = 200;
    const FAIL_STATUS      = 400;
    const FORBIDDEN_STATUS = 403;

    const ON_JOB = 0;
    const NO_JOB = 1;

    public function checkUnique($employeeId)
    {
        if (empty($employeeId) || $employeeId == '系统自动生成') {
            return $msg = dataReturn('没有员工编号，请您点击员工编号生成按钮或者手动填写一个编号', self::FORBIDDEN_STATUS);
        }
        $filter['employee_id'] = ['EQ', $employeeId];
        $uniqueRst = $this->where($filter)->field('employee_id')->find();
        return empty($uniqueRst['employee_id']) ? dataReturn('无重复',self::SUCCESS_STATUS) : dataReturn('有重复员工编号，请自动生成或换一个编号',self::FAIL_STATUS);
    }

    /**
     * @name getAddData
     *
    */
    public function getAddData($postData) {
        foreach ($postData as $key => $value) {
            trim($value);
        }

        $uniqueCheck = $this->checkUnique($postData['employee_id']);
        if ($uniqueCheck['status'] != self::SUCCESS_STATUS) {
            return $uniqueCheck;
        }
        $addData = [
            'employee_id'   => $postData['employee_id'],
            'name'          => $postData['name'],
            'department'    => $postData['department'],
            'position'      => $postData['position'],
            'sex'           => $postData['sex'],
            'entry_time'    => $postData['entry_time'] / 1000,
            'birthday'      => $postData['birthday'] / 1000,
            'education'     => $postData['education'],
            'birth_place'   => $postData['birth_place'],
            'nation'        => $postData['nation'],
            'politics_status' => $postData['politics_status'],
            'id_card_no'    => $postData['id_card_no'],
            'census_type'   => $postData['census_type'],
            'census_place'  => $postData['census_place'],
            'marital_status' => $postData['marital_status'],
            'health_status' => $postData['health_status'],
            'working_time'  => $postData['working_time'],
            'photo'         => $postData['photo'],
            'photo_status'  => $postData['photo_status'],
            'resume'        => $postData['resume'],
            'resume_status' => $postData['resume_status'],
            'auditor'       => session('nickname'),
            'add_time'      => time(),
            'update_time'   => time(),
            'on_job'        => 1
        ];
        return $data = dataReturn('数据正常', self::SUCCESS_STATUS, $addData);
    }

    public function addEmployeeData($info)
    {
        $info['is_probation'] = 1;
        $infoRecordModel = new StaffInfoRecordModel();
        $addRst['addEmployee'] = $this->add($info);
        $addRst['record'] = $infoRecordModel->postEmployee($info);
        return $addRst;
    }

    public function addEmployeeTrans($info)
    {
        $eduModel     = new StaffEducationModel();
        $contactModel = new StaffContactModel();
        M()->startTrans();
        $staffInfoAddRst = $this->addEmployeeData($info);
        if (in_array(false, $staffInfoAddRst)) {
            M()->rollback();
            return $return = dataReturn('提交员工信息失败', self::FAIL_STATUS);
        }

//        if (count($contact) != 0) {
//            $staffContactRst = $contactModel->addContact($contact);
//            if (in_array(false, $staffContactRst)) {
//                M()->rollback();
//                return $return = dataReturn('提交员工信息失败', self::FAIL_STATUS);
//            }
//        }
//
//        if (count($education) != 0) {
//            $educationAddRst = $eduModel->addEduData($education);
//
//            if (in_array(false, $educationAddRst)) {
//                M()->rollback();
//                return $return = dataReturn('提交员工信息失败', self::FAIL_STATUS);
//            }
//        }
        M()->commit();
        return $return = dataReturn('提交员工信息成功', self::SUCCESS_STATUS);
    }

    public function uploadData($uploadType,$uploadIndex, $fileData)
    {
        // 文件上传类配置项
        // 检测根目录是否存在，不存在创建
        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/employeeData/" . $uploadType . "/";
        if (!file_exists($rootPath)) {
            mkdir($rootPath);
        }
        $ext = $uploadType == 'uploadPhoto'
            ? ['gif','jpg', 'jpeg', 'bmp']
            : ['gif', 'jpg', 'jpeg', 'bmp', 'doc', 'docx','pdf'];
        $cfg = [
            'rootPath' => $rootPath, // 保存根路径
            'mimes'    => array('image/jpeg', 'image/gif', 'text/plain' ,'audio/mpeg', 'application/x-rar-compressed', 'application/zip','image/bmp', 'application/msword', 'application/pdf', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.ms-office','application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'replace'  => true,
            'exts'     => $ext
        ];
        # 实例化上传类
        $uploadModel = new Upload($cfg);
        # 上传
        $uploadRst = $uploadModel->upload();
        $data = [];
        if (!$uploadRst) {
            // 返回错误信息
            $data['error_info'] = $uploadModel->getError();
            return $msg = ['status' => self::FAIL_STATUS,'data' => $data];
        } else {
            // 返回成功信息

            foreach ($uploadRst as $item) {
                $data['filePath'] = UPLOAD_ROOT_PATH . "/employeeData/" . $uploadType . "/" . trim($item['savepath'] . $item['savename'], '.');
                $data['fileName'] = $fileName = $fileData['file']['name'];
                if ($uploadType == 'uploadPapers') {
                    $data['index'] = (int)$uploadIndex;
                }
                return $msg = ['status' => self::SUCCESS_STATUS, 'data' => $data];
            }
        }
    }


    /**
     * 读取表数据
     * @todo 后续时间要改成int 10
    */
    public function getStaffData($field, $where, $start, $limit, $order)
    {
        return $this->field($field)->where($where)->limit($start, $limit)->order($order)->select();
    }

    public function index($sqlCondition, $whereCondition)
    {
        $field  = "employee_id,name,department,position,sex,education,birth_place,nation,politics_status";
        $field .= ',id_card_no,age,census_type,census_place,marital_status,health_status,working_time';
        $field .= ',photo,photo_status,resume,census_place,resume_status,auditor,from_unixtime(entry_time,\'%Y-%m-%d\') entry_time';
        $field .= ',DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(0), INTERVAL birthday SECOND),\'%Y-%m-%d\') birthday, from_unixtime(add_time,\'%Y-%m-%d %T\') add_time, on_job, is_probation';
        $sqlCondition['order'] .= ",employee_id DESC";
        $data = $this->getStaffData($field, $whereCondition, $sqlCondition['start'],$sqlCondition['length'], $sqlCondition['order']);

        return $data;
    }

    public function getOneDataByFind($field, $filter, $order='employee_id') {
        return $this->where($filter)->field($field)->order($order)->find();
    }

    public function getStaffDataById($id, $returnDataSet = ['basicData', 'contactData', 'eduData','employeeDevelopment', 'punishData','complainData'])
    {
        $eduModel     = new StaffEducationModel();
        $contactModel = new StaffContactModel();
        $developModel = new StaffDevelopModel();
        $rewardsModel = new StaffPunishRewardsModel();
        $contractModel = new StaffContractModel();
        $postChangeModel = new StaffChangeModel();
        $filter['employee_id'] = array('EQ', $id);
        $arraySel = ['basicData', 'contactData', 'eduData','employeeDevelopment', 'punishData','contractData','postChange','complainData'];
        $returnData = [];
        foreach ($returnDataSet as $key => $item) {
            if (in_array($item,$arraySel)) {
                switch ($item) {
                    case $arraySel[0] :
                        $field = "id, employee_id,name,department,position,sex,education,birth_place,nation,politics_status";
                        $field .= ',id_card_no,age,census_type,census_place,marital_status,health_status,working_time';
                        $field .= ',photo,photo_status,resume,census_place,resume_status,auditor,from_unixtime(entry_time,\'%Y-%m-%d\') entry_time';
                        $field .= ',from_unixtime(birthday) birthday, from_unixtime(update_time,\'%Y-%m-%d\') update_time, on_job';
                        $returnData[$item] = $this->getOneDataByFind($field, $filter);
                        break;
                    case $arraySel[1] :
                        $field = 'employee_id, phone, mail, member,contact,auditor,from_unixtime(update_time,\'%Y%m%d\') update_time,id';
                        $returnData[$item] = $contactModel->getDataById($field, $filter);
                        break;
                    case $arraySel[2] :
                        $field = 'employee_id, graduate_school, period, major,education,language_level,computer_level,from_unixtime(update_time,\'%Y%m%d\') update_time,id';
                        $field .= ',other_level, papers_upload, papers_upload_status,auditor,id';
                        $returnData[$item] = $eduModel->getDataById($field, $filter);
                        break;
                    case $arraySel[3] :
                        $field = 'employee_id,record_type,detail_information,add_name,from_unixtime(add_time,\'%Y%m%d\') add_time,id';
                        $returnData[$item] = $developModel->getDataById($field, $filter);
                        break;
                    case $arraySel[4] :
                        $field = 'employee_id,name,type,from_unixtime(record_time) record_time,reason,fee,score,from_unixtime(add_time) add_time,add_time,id';
                        $returnData[$item] = $rewardsModel->getDataById($field, $filter);
                        break;
                    case $arraySel[5] :
                        $field = 'employee_id,name,contract_id,contract_type,from_unixtime(start_time,\'%Y%m%d\') start_time,from_unixtime(end_time,\'%Y%m%d\') end_time,
                        sign_count,from_unixtime(probation_start_time,\'%Y%m%d\') probation_start_time,from_unixtime(probation_end_time,\'%Y%m%d\') probation_end_time,
                        duration,auditor,id,update_time,tips';
                        $returnData[$item] = $contractModel->getDataById($field, $filter);
                        break;
                    case $arraySel[6] :
                        $field = 'employee_id,name,change_type,from_unixtime(change_time,\'%Y%m%d\') change_time,change_old_dept,change_new_dept,change_old_position,change_new_position,
                        change_old_salary,from_unixtime(exec_time,\'%Y%m%d\') exec_time,change_new_salary, auditor,tips,id,from_unixtime(update_time,\'%Y%m%d\') update_time';
                        $returnData[$item] = $postChangeModel->getDataById($field, $filter);
                        break;
                    default :
                        break;
                }
            }
        }
        return $returnData;
    }

    public function getResumeUrl($employeeId)
    {
        $filter['employee_id'] = ['EQ', $employeeId];
        $filed = 'resume';
        return $data = $this->getOneDataByFind($filed, $filter)[$filed];
    }

    public function reinstate($id)
    {
        $field['employee_id'] = ['EQ', $id];
        $map['employee_id'] = ['EQ', $id];
        $field['on_job'] = 1;
        $field['update_time'] = time();
        $msg = "员工复职操作，操作人：" . session('nickname') . "操作时间：" . date('Y-m-d H:i:s', time());
        $staffRecordModel = new StaffInfoRecordModel();
        $addData = $staffRecordModel->getAddData($msg, '员工复职');
        $this->startTrans();
        $updRst = $this->where($map)->save($field);
        $addRst = $staffRecordModel->add($addData);
        if ($updRst !== false && $addRst !== false) {
            $this->commit();
            return dataReturn('成功复职', self::SUCCESS_STATUS);
        } else {
            $this->rollback();
            return dataReturn('复职失败', self::FAIL_STATUS);
        }
    }

    /**
     * 修改员工id后修改其他相关联的员工id
     * @param $oldId
     * @param $newId
     * @return bool
     */
    public function changeEmployeeId($oldId, $newId)
    {
        $map = ['employee_id' => ['EQ', $oldId]];
        $data = ['employee_id' => $newId];
        $tableArr = ['staff_change', 'staff_contact', 'staff_contract', 'staff_departure', 'staff_develop', 'staff_education', 'staff_punish_rewards'];
        foreach ($tableArr as $item) {
            $res = M($item) -> where($map) -> save($data);
            if ($res === false){
                return false;
            }
        }
        return true;
    }

}