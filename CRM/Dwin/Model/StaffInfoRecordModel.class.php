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
class StaffInfoRecordModel extends Model
{
    private $putArr = [
        'name' => '姓名',
        'employee_id' => '员工编号',
        'nation' => '民族',
        'sex' => '性别',
        'working_time' => '参加工作时间',
        'entry_time' => '入职时间',
        'birthday' => '出生日期',
        'birth_place' => '籍贯',
        'census_place' => '户口所在地',
        'census_type' => '户口类别',
        'politics_status' => '政治面貌',
        'marital_status' => '婚姻状况',
        'health_status' => '健康状况',
        'photo' => '照片',
        'resume' => '简历'
    ];

    public function getAddData($msg, $type)
    {
        $data = [
            'add_time'       => time(),
            'staff_id'       => session('staffId'),
            'staff_name'     => session('nickname'),
            'change_type'    => $type,
            'change_content' => $msg
        ];
        return $data;
    }
    /**
     * 新增员工的记录
     * @param $employee    array    新职员信息
     * @return bool
     */
    public function postEmployee($employee)
    {
        $data = [
            'add_time' => time(),
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'change_type' => '新增'
        ];
        $data['change_content'] = session('nickname') . "提交了新员工信息，员工编号：" . $employee['employee_id'] . '，添加时间' . date('Y-m-d H:i:s');
        return $this -> add($data);
    }

    /**
     * 修改记录
     * @param $employee
     * @return bool|mixed
     */
    public function putEmployee($employee)
    {
        $arrKey = array_keys($this->putArr);
        $str = session('nickname') . "修改了员工信息: ";
        $oldData = M('staff_info') -> where(['employee_id' => ['EQ', $employee['employee_id']]]) -> find();
        $isChange = false;
        foreach ($arrKey as $key => $value) {
            if ($employee[$value] != $oldData[$value]){
                $str .= $this->putArr[$value] . '发生改变, 由' . $oldData[$value] . '改为' . $employee[$value] . ' ; ';
                $isChange = true;
            }
        }
        $data = [
            'add_time' => time(),
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'change_type' => '修改'
        ];
        $str .= '修改时间' . date('Y-m-d H:i:s');
        $data['change_content'] = $str;
        if ($isChange){
            return $this->add($data);
        }else{
            return true;
        }
    }

    public function delDevelopRecord($id)
    {
        $data = M('staff_develop') -> find((int) $id);
        $str = session('nickname') . "删除了员工发展信息: " . json_encode($data);
        $data = [
            'add_time' => time(),
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'change_type' => '删除'
        ];
        $data['change_content'] = $str;
        return $this->add($data);
    }

    public function delPunishRecord($id)
    {
        $data = M('staff_punish_rewards') -> find((int) $id);
        $str = session('nickname') . "删除了员工奖惩信息: " . json_encode($data);
        $data = [
            'add_time' => time(),
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'change_type' => '删除'
        ];
        $data['change_content'] = $str;
        return $this->add($data);
    }

    public function postDevelopRecord($data)
    {
        $str = session('nickname') . "新增了员工发展信息: " . json_encode($data);
        $data = [
            'add_time' => time(),
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'change_type' => '新增'
        ];
        $data['change_content'] = $str;
        return $this->add($data);
    }

    public function postPunishRecord($data)
    {
        $str = session('nickname') . "新增了员工奖惩信息: " . json_encode($data);
        $data = [
            'add_time' => time(),
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'change_type' => '新增'
        ];
        $data['change_content'] = $str;
        return $this->add($data);
    }

    public function putDevelopRecord($data)
    {
        $str = session('nickname') . "修改了员工发展信息: " . json_encode($data);
        $data = [
            'add_time' => time(),
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'change_type' => '修改'
        ];
        $data['change_content'] = $str;
        return $this->add($data);
    }

    public function putPunishRecord($data)
    {
        $str = session('nickname') . "修改了员工奖惩信息: " . json_encode($data);
        $data = [
            'add_time' => time(),
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'change_type' => '修改'
        ];
        $data['change_content'] = $str;
        return $this->add($data);
    }
}