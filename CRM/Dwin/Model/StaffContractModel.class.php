<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/5/5
 * Time: 上午9:54
 */

namespace Dwin\Model;


use Think\Model;

class StaffContractModel extends Model
{

    /**
     *提交数据返回状态
     * 200 成功 400 失败 403 禁止操作
     */
    const SUCCESS_STATUS   = 200;
    const FAIL_STATUS      = 400;
    const FORBIDDEN_STATUS = 403;



    public function getContract($map) {
        $field = "*";
        return $this->getDataById($field, $map,'employee_id', 0, 10);
    }

    public function getEditData($postData)
    {
        $changeData = [];
        $msg = [];
        $changeArray = ['contract_type', 'start_time', 'end_time','sign_count', 'probation_start_time', 'probation_end_time', 'duration'];
        for ($i = 0; $i < count($postData); $i++) {
            $filter[$i]['id'] = ['EQ', $postData[$i]['id']];
            $oldData[$i] = $this->getOneDataByFind('*', $filter[$i]);
            $data[$i] = checkChange($oldData[$i], $postData[$i], $changeArray);

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

    public function editContract($updData, $msg)
    {
        $staffInfoRecordModel = new StaffInfoRecordModel();
        if (count($updData) == 0) {
            return dataReturn('未更新数据', self::FAIL_STATUS);
        }
        $changeArray = ['start_time', 'end_time', 'probation_start_time', 'probation_end_time', 'update_time'];
        $this->startTrans();
        $addData = [];
        for ($i = 0; $i < count($updData); $i++) {
            foreach ($updData[$i] as $key => &$value) {
                if (in_array($key, $changeArray)) {
                    $value /= 1000;
                }
            }
            $updRst[$i] = $this->save($updData[$i]);
            $addData[$i] = $staffInfoRecordModel->getAddData($msg[$i],'修改合同数据');
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
        $delContent = $this->getContract($map)[0];
        $msg = "";
        foreach($delContent as $key => $value) {
            $msg .= $key . ":" . $value . ",";
        }
        $this->startTrans();
        $delRst = $this->delete($deleteId);
        $addData = $infoRecordModel->getAddData($msg, '删除合同数据');
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
            'employee_id'   => $postData['employee_id'],
            'name'          => $postData['name'],
            'contract_id'   => $postData['contract_id'],
            'contract_type' => $postData['contract_type'],
            'start_time'    => $postData['start_time'],
            'end_time'      => $postData['end_time'],
            'sign_count'    => $postData['sign_count'],
            'probation_start_time' => $postData['probation_start_time'],
            'probation_end_time'   => $postData['probation_end_time'],
            'duration'  => $postData['duration'],
            'tips'      => $postData['tips'],
            'update_time' => time(),
            'auditor'   => session('nickname')
        ];
        if (empty($returnData['employee_id']) || empty($returnData['name']) || empty($returnData['contract_id'])) {
            return dataReturn('数据提交有误', self::FAIL_STATUS);
        } else {
            $msg = "提交合同操作：员工及合同编号" . $returnData['employee_id'] . "起止时间" . date('Y-m-d', $returnData['start_time']) . "-" . date('Y-m-d', $returnData['end_time']);
            $msg .= "试用期起止：". date('Y-m-d', $returnData['probation_start_time']) . "-" . date('Y-m-d', $returnData['probation_end_time']);
            return dataReturn($msg, self::SUCCESS_STATUS, $returnData);
        }
    }
    public function addContract($contract, $msg)
    {
        $infoRecordModel = new StaffInfoRecordModel();
        $changeArray = ['start_time', 'end_time', 'probation_start_time', 'probation_end_time'];
        foreach ($contract as $key => &$value) {
            if (in_array($key, $changeArray)){
                $value /= 1000;
            }
        }

        $addContractRst  = $this->add($contract);
        $recordContent   = $infoRecordModel->getAddData($msg, "新增合同数据");
        $addRecordRst    = $infoRecordModel->add($recordContent);

        if ($addContractRst !== false && $addRecordRst !== false) {
            $this->commit();
            return $data = dataReturn('新增合同成功', self::FAIL_STATUS);
        } else {
            $this->rollback();
            return $data = dataReturn('新增合同失败', self::FAIL_STATUS);
        }
    }

    public function getDataById($field, $filter,$order = 'employee_id', $start = 0, $length = 100)
    {
        return $this->where($filter)->field($field)->limit($start,$length)->order($order)->select();
    }

    public function getOneDataByFind($field,$filter,$order = 'employee_id')
    {
        return $this->where($filter)->field($field)->order($order)->find();
    }

}