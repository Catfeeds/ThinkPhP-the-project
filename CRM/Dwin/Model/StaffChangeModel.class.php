<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/5/5
 * Time: 上午9:54
 */

namespace Dwin\Model;


use Think\Exception;
use Think\Model;

class StaffChangeModel extends Model
{

    const IS_DEL = 1;
    const NO_DEL = 0;
    /**
     *提交数据返回状态
     * 200 成功 400 失败 403 禁止操作
     */
    const SUCCESS_STATUS   = 200;
    const FAIL_STATUS      = 400;
    const FORBIDDEN_STATUS = 403;


    public function getDataById($field, $filter,$order = 'employee_id', $start = 0, $length = 100)
    {
        return $this->where($filter)->field($field)->limit($start,$length)->order($order)->select();
    }

    public function getOneDataByFind($field,$filter,$order = 'employee_id')
    {
        return $this->where($filter)->field($field)->order($order)->find();
    }

    /**
     * 去除非此表的字段数据
     * @param $params
     * @return array
     */
    public function getNewField($params){
        $fieldData = $this->getDbFields();
        $data = [];
        foreach ($fieldData as $key => $field){
            if(isset($params[$field])){
                $data[$field] = $params[$field];
            }
        }
        return $data;
    }

    private function compareData($oldData, $editedData)
    {
        // 先把不存在当前表里面的字段剔除，然后在与原先的数据做对比
        foreach ($editedData as $key => $val) {
            if ($val == $oldData[$key]) {
                unset($editedData[$key]);
            } else {
                continue;
            }
        }

        if(empty($editedData)){
            return false;
        }

        $editedData['id']   = $oldData['id'];
        $editedData['update_time']  = time();
        $editedData['auditor']    = session('nickname');
        return $editedData;
    }

    /**
     * 修改员工异动信息
     * @param $postData
     * @return array
     */
    public function editChange($postData){
        $changeData = $this->find($postData['id']);

        $postData = $this->getNewField($postData);
        if (empty($postData)) {
            return dataReturn('没有提交新增数据',400);
        }
        $data = [
            'department' => $postData['change_new_dept'],
            'position' => $postData['change_new_position'],
        ];
        $map = [
            'employee_id' => ['EQ', $postData['employee_id']]
        ];

        $editData = $this->compareData($changeData, $postData);
        if(!$editData){
            return dataReturn('没有提交新增数据',400);
        }

        try{
            $this->startTrans();
            $infoModel = new StaffInfoModel();
            $infoUpdate = $infoModel->where($map)->save($data);
            if($infoUpdate === false){
                $this->rollback();
                return dataReturn($infoModel->getError(),400);
            }

            $changeUpdate = $this->save($editData);
            if($changeUpdate === false){
                $this->rollback();
                return dataReturn($this->getError(),400);
            }
            $this->commit();
            return dataReturn("修改员工异动信息成功",200);
        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(),400);
        }
    }

    public function delChange($id){
        $changeData = $this->find($id);
        $data = [
            'department' => $changeData['change_old_dept'],
            'position' => $changeData['change_old_position'],
        ];
        $map = [
            'employee_id' => ['EQ', $changeData['employee_id']]
        ];

        try{
            $this->startTrans();
            $infoModel = new StaffInfoModel();
            $infoUpdate = $infoModel->where($map)->save($data);
            if($infoUpdate === false){
                $this->rollback();
                return dataReturn($infoModel->getError(),400);
            }

            $changeUpdate = $this->where(['id' => $changeData['id']])->setField(['is_del' => self::IS_DEL]);
            if($changeUpdate === false){
                $this->rollback();
                return dataReturn($this->getError(),400);
            }
            $this->commit();
            return dataReturn("修改员工异动信息成功",200);
        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(),400);
        }
    }

}