<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/5/5
 * Time: 上午9:54
 */

namespace Dwin\Model;


use Think\Model;

class StaffContactModel extends Model
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
        $changeArray = ['phone', 'mail', 'member', 'contact'];
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

    public function editContract($updData, $msg)
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
            'employee_id' => $postData['employee_id'],
            'phone'       => $postData['phone'],
            'mail'        => $postData['mail'],
            'member'      => $postData['member'],
            'contact'     => $postData['contact'],
            'update_time' => time(),
            'auditor'     => session('nickname')
        ];
        if (empty($returnData['employee_id'])) {
            return dataReturn('数据提交有误', self::FAIL_STATUS);
        } else {
            $msg = "提交联系方式操作：员工编号" . $returnData['employee_id'] . "时间" . date('Y-m-d', $returnData['']);
            $arr = ['phone' => "电话", 'mail' => 'email', 'member' => '家庭成员', 'contact' => '联系方式'];
            foreach($arr as $key => $item) {
                if ($returnData[$key]) {
                    $msg .= $item . ":" . $returnData[$key];
                }
            }
            return dataReturn($msg, self::SUCCESS_STATUS, $returnData);
        }
    }
    public function addContact($contract, $msg)
    {
        $infoRecordModel = new StaffInfoRecordModel();
        $addContractRst  = $this->add($contract);
        $recordContent   = $infoRecordModel->getAddData($msg, "新增联系数据");
        $addRecordRst    = $infoRecordModel->add($recordContent);

        if ($addContractRst !== false && $addRecordRst !== false) {
            $this->commit();
            return $data = dataReturn('新增成功', self::SUCCESS_STATUS);
        } else {
            $this->rollback();
            return $data = dataReturn('新增失败', self::FAIL_STATUS);
        }
    }


    public function getDataById($field, $filter,$order = 'employee_id', $start = 0, $length = 100, $group = 'employee_id')
    {
        return $this->where($filter)->field($field)->limit($start,$length)->order($order)->group($group)->select();
    }

    public function getOneDataByFind($field,$filter,$order = 'employee_id', $group = 'employee_id')
    {
        return $this->where($filter)->field($field)->order($order)->group($group)->find();
    }

}