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

class StaffDepartureModel extends Model
{
    const IS_DEL = 1;
    const NO_DEL = 0;

    public function getDataById($field, $filter,$order = 'update_time desc', $start = 0, $length = 100)
    {
        return $this->alias('departure')->where($filter)->field($field)->limit($start,$length)->order($order)->select();
    }
    public function getDataByIdWithJoin($field, $filter,$order = 'update_time desc', $start = 0, $length = 100)
    {
        return $this->alias('depart')
            ->join('LEFT JOIN crm_staff_info info ON info.employee_id = depart.employee_id')
            ->where($filter)->field($field)->limit($start,$length)->order($order)->select();
    }

    public function getIndexData($condition, $sqlCondition)
    {
        $field = "depart.employee_id emp_id,
                    info.name,
                    from_unixtime(info.entry_time,'%Y-%m-%d') work_time,
                    ifnull(depart.department,'无') depart, 
                    ifnull(depart.position,'无') posi, 
                    from_unixtime(depart.departure_time,'%Y-%m-%d') depart_time,
                    ifnull(depart.departure_reason,'***') departure_r";
        $field .= ",ifnull(depart.departure_type,'***') departure_t,
                    ifnull(depart.tips,'***') tip, 
                    depart.auditor aud, 
                    from_unixtime(depart.update_time,'%Y-%m-%d') update_t, 
                    depart.id";
        $condition['depart.is_del'] = ['eq', self::NO_DEL];
        $data = $this->getDataByIdWithJoin($field, $condition, $sqlCondition['order'], $sqlCondition['start'], $sqlCondition['length']);
        foreach ($data as &$val) {
            $val['DT_RowId'] = $val['id'];
        }
        return $data;
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
     * 修改离职信息
     * @param $postData
     * @return array
     */
    public function editDeparture($postData){
        $changeData = $this->find($postData['id']);

        $postData = $this->getNewField($postData);
        if (empty($postData)) {
            return dataReturn('没有提交新增数据',400);
        }

        $editData = $this->compareData($changeData, $postData);
        if(!$editData){
            return dataReturn('没有提交新增数据',400);
        }

        $res = $this->save($editData);
        if($res === false){
            return dataReturn($this->getError(),400);
        }
        return dataReturn("修改离职信息成功",200);
    }

    /**
     * 删除离职信息
     * @param $id
     * @return array
     */
    public function delDeparture($id){
        try{
            $this->startTrans();
            $departureData = $this->find($id);
            $departureRes = $this->where(['id' => $id])->setField(['is_del' => self::IS_DEL]);
            if($departureRes === false){
                $this->rollback();
                return dataReturn($this->getError(),400);
            }

            $infoModel = new StaffInfoModel();
            $infoRes = $infoModel->where(['employee_id' => $departureData['employee_id']])->setField(['on_job' => StaffInfoModel::ON_JOB]);
            if($infoRes === false){
                $this->rollback();
                return dataReturn($infoModel->getError(),400);
            }

            $this->commit();
            return dataReturn("删除离职信息成功",200);

        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(),400);
        }
    }
}