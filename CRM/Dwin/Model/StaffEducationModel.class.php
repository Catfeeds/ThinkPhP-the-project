<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/5/5
 * Time: 上午9:54
 */

namespace Dwin\Model;


use Think\Model;

class StaffEducationModel extends Model
{
    /**
     *提交数据返回状态
     * 200 成功 400 失败 403 禁止操作
     */
    const SUCCESS_STATUS   = 200;
    const FAIL_STATUS      = 400;
    const FORBIDDEN_STATUS = 403;


    public function getEditData($postData)
    {
        $changeData = [];
        $msg = [];
        $changeArray = [
            'graduate_school',
            'period', 'major',
            'education','language_level',
            'computer_level', 'other_level',
            'paper_upload'
        ];
        for ($i = 0; $i < count($postData); $i++) {
            $filter[$i]['id'] = ['EQ', $postData[$i]['id']];
            $oldData[$i] = $this->getOneDataByFind('*', $filter[$i]);
            $data[$i]    = checkChange($oldData[$i], $postData[$i], $changeArray);
            if (in_array(true, $data[$i]['flag'])) {
                $changeData[$i] = $postData[$i];
                $msg[$i] = $data[$i]['msg'];
            }
        }
        if (count($changeData) != 0) {
            return $msg = ['msg' => array_values($msg), 'status' => self::SUCCESS_STATUS,'data' => array_values($changeData)];
        } else {
            return $msg = ['msg' => '无修改数据', 'status' => self::FAIL_STATUS];
        }
    }

    public function editEdu($updData, $msg)
    {
        $staffInfoRecordModel = new StaffInfoRecordModel();
        if (count($updData) == 0) {
            return dataReturn('未更新数据', self::FAIL_STATUS);
        }
        $this->startTrans();
        $addData = [];
        for ($i = 0; $i < count($updData); $i++) {
            $updRst[$i] = $this->save($updData[$i]);
            $addData[$i] = $staffInfoRecordModel->getAddData($msg[$i],'修改联系方式数据');
            if ($updRst[$i] === false) {
                $this->rollback();
                return $msg = dataReturn('编辑失败，联系管理', self::FAIL_STATUS);
            }
        }

        $addRst = $staffInfoRecordModel->addAll($addData);
        if ($addRst === false) {
            $this->rollback();
            return $data = dataReturn('编辑失败，联系管理', self::FAIL_STATUS);
        }
        $this->commit();
        return $data = dataReturn( '编辑成功', self::SUCCESS_STATUS);
    }


    public function deleteData($deleteId)
    {
        $infoRecordModel = new StaffInfoRecordModel();
        $map['id'] = ['EQ', $deleteId];
        $delContent = $this->where($map)->field('*')->find();
        $msg = "";
        foreach($delContent as $key => $value) {
            $msg .= $key . ":" . $value . ",";
        }
        $this->startTrans();
        $delRst = $this->delete($deleteId);
        $addData = $infoRecordModel->getAddData($msg, '删除联系记录数据');
        $addRst = $infoRecordModel->add($addData);
        if ($delRst !== false && $addRst!== false) {
            $this->commit();
            return $data = dataReturn('删除成功',self::SUCCESS_STATUS);
        } else {
            $this->rollback();
            return $data = dataReturn('删除失败', self::FAIL_STATUS);
        }

    }

    public function getAddData($postData)
    {
        $returnData = [
            'employee_id'      => $postData['employee_id'],
            'graduate_school'  => $postData['graduate_school'],
            'period'           => $postData['period'],
            'major'            => $postData['major'],
            'education'        => $postData['education'],
            'language_level'   => $postData['language_level'],
            'computer_level'   => $postData['computer_level'],
            'other_level'      => $postData['other_level'],
            'papers_upload'    => $postData['papers_upload'],
            'papers_upload_status' => $postData['papers_upload_status'],
            'auditor'          => session('staffId'),
            'update_time'      => time()
        ];
        if (empty($returnData['employee_id'])) {
            return dataReturn('数据提交有误', self::FAIL_STATUS);
        } else {
            $msg = "提交联系方式操作：员工编号" . $returnData['employee_id'] . "时间" . date('Y-m-d', $returnData['']);
            $arr = [
                'graduate_school' => '毕业院校',
                'period'          => '在校时间',
                'major'           => '学位',
                'education'       => '教育程度',
                'language_level'  => '语言水平',
                'computer_level'  => '电脑水平',
                'other_level'     => '其他水平',
                'papers_upload'   => '简历路径',
                'papers_upload_status' => '简历状态'
            ];
            foreach($arr as $key => $item) {
                if ($returnData[$key]) {
                    $msg .= $item . ":" . $returnData[$key];
                }
            }
            return dataReturn($msg, self::SUCCESS_STATUS, $returnData);
        }
    }
    public function addEdu($contract, $msg)
    {
        $infoRecordModel = new StaffInfoRecordModel();
        $addContractRst  = $this->add($contract);
        $recordContent   = $infoRecordModel->getAddData($msg, "新增学历");
        $addRecordRst    = $infoRecordModel->add($recordContent);

        if ($addContractRst !== false && $addRecordRst !== false) {
            $this->commit();
            return $data = dataReturn('新增成功', self::SUCCESS_STATUS);
        } else {
            $this->rollback();
            return $data = dataReturn('新增失败', self::FAIL_STATUS);
        }
    }

//    public function getAddData($postData, $employeeId)
//    {
//        $addArr = [];
//        if (empty($employeeId)) {
//            return $data = dataReturn('没有员工编号，未提交数据', self::FORBIDDEN_STATUS);
//        }
//        for($i = 0; $i < count($postData); $i++) {
//            if ($postData[$i]['flag'] == 'add') {
//                $addArr[$i]['employee_id']      = $employeeId;
//                $addArr[$i]['graduate_school']  = $postData[$i]['graduate_school'];
//                $addArr[$i]['period']           = $postData[$i]['period'];
//                $addArr[$i]['major']            = $postData[$i]['major'];
//                $addArr[$i]['education']        = $postData[$i]['education'];
//                $addArr[$i]['language_level']   = $postData[$i]['language_level'];
//                $addArr[$i]['computer_level']   = $postData[$i]['computer_level'];
//                $addArr[$i]['other_level']      = $postData[$i]['other_level'];
//                $addArr[$i]['papers_upload']    = $postData[$i]['papers_upload'];
//                $addArr[$i]['papers_upload_status'] = $postData[$i]['papers_upload_status'];
//                $addArr[$i]['auditor']          = session('staffId');
//                $addArr[$i]['update_time']      = time();
//            }
//        }
//        $returnData = count($addArr) == 0
//            ? dataReturn('没有新提交的教育信息', self::FAIL_STATUS, $addArr)
//            : dataReturn('有教育信息', self::SUCCESS_STATUS, $addArr);
//        return $returnData;
//    }

//    public function addEduData($contact)
//    {
//        $infoRecordModel = new StaffInfoRecordModel();
//        $recordContent = [];
//        foreach ($contact as $key => $value) {
//            $record[$key]['content'] = session('nickname') . "提交了员工教育信息，员工编号："
//                . $value['employee_id'] . '，添加时间' . date('Y-m-d H:i:s') . ",添加内容：就读院校："
//                . $value['graduate_school'] . "，学历：" . $value['education'] . "，专业：" . $value['major'] . "，英语水平：" . $value['language_level'];
//            $recordContent[$key] = $infoRecordModel->getData($record[$key],'新增教育经历');
//        }
//        $addRst['eduAdd'] = $this->addAll($contact);
//        $addRst['record']     = $infoRecordModel->addAll($recordContent);
//        return $addRst;
//    }

    public function getDataById($field, $filter,$order = 'employee_id', $start = 0, $length = 100, $group = 'employee_id')
    {
        return $this->where($filter)->field($field)->limit($start, $length)->order($order)->group($group)->select();
    }
    public function getOneDataByFind($field, $filter, $order = 'employee_id')
    {
        return $this->where($filter)->field($field)->order($order)->find();
    }
}