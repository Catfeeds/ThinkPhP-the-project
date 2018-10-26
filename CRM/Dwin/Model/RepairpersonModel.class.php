<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/6/1
 * Time: 下午4:18
 */

namespace Dwin\Model;


use Think\Model;

class RepairpersonModel extends Model
{
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
        $changeMoney = 0;
        // 先把不存在当前表里面的字段剔除，然后在与原先的数据做对比
        foreach ($editedData as $key => $val) {
            if ($val == $oldData[$key]) {
                unset($editedData[$key]);
            } else {
                if($key == "piece_wage"){
                    // 修改了费用就要重新确认维修费是否可以通过，并且修改总费用
                    $money = $val- $oldData[$key];
                    $changeMoney = $changeMoney + $money;
                }
                continue;
            }
        }

        if(empty($editedData)){
            return [false, 0];
        }

        $editedData['rpid']   = $oldData['rpid'];
        return [$editedData, $changeMoney];
    }

    /**
     * getRepairPersonUpdateData
     * @param array $data product_list which delivery from front;
     * @return array|bool $repairPersonUpdateData;
     */
    public function getRepairPersonUpdateData($data)
    {
        $repairPersonUpdateData = array();
        for ($m = 0; $m < count($data); $m ++) {
            $data[$m]['check_sum'] = 0;
            for ($n = 0; $n < count($data[$m]['data']); $n ++) {
                //@todo 校验$data[$m]['re_sum'] 与 $data[$m'['data']总数
                $data[$m]['check_sum'] += (int)$data[$m]['data'][$n]['re_num'];
                if ((int)$data[$m]['data'][$n]['re_num'] == 0) {
                    return false;
                }
                if (isset($data[$m]['data'][$n]['rpid'])) {
                    /* Assembling Maintenance Product Data */
                    $repairPersonUpdateData[] = array(
                        'rpid'              => (int)$data[$m]['data'][$n]['rpid'],
                        'pid'               => (int)$data[$m]['data'][$n]['pid'],
                        'reperson_id'       => (int)substr($data[$m]['data'][$n]['staffArr'], 0, strpos($data[$m]['data'][$n]['staffArr'], "_")),
                        'reperson_name'     => substr($data[$m]['data'][$n]['staffArr'], strpos($data[$m]['data'][$n]['staffArr'], "_") + 1),
                        'product_name'      => $data[$m]['data'][$n]['product_name'],
                        're_num'            => $data[$m]['data'][$n]['re_num'],
                        'start_date'        => $data[$m]['data'][$n]['start_date'],
                        're_status'         => $data[$m]['data'][$n]['re_status'],
                        're_mode'           => $data[$m]['data'][$n]['re_mode'],
                        'piece_wage'        => $data[$m]['data'][$n]['piece_wage'],
                        'reperson_question' => $data[$m]['data'][$n]['reperson_question'],
                        'situation'         => $data[$m]['data'][$n]['situation'],
                        'meter_piece'       => $data[$m]['data'][$n]['meter_piece'],
                        'fault_info'        => $data[$m]['data'][$n]['fault_info'],
                        'mode_info'         => $data[$m]['data'][$n]['mode_info']
                    );
                }
            }
            if ($data[$m]['check_sum'] > $data[$m]['re_sum']) {
                return false;
            }
        }
        return $repairPersonUpdateData = array_values($repairPersonUpdateData);
    }

    public function getRepairPersonAddData($data, $pid)
    {
        $repairPersonAddData = array();
        for ($m = 0; $m < count($data); $m ++) {
            for ($n = 0; $n < count($data[$m]['data']); $n++) {
                if ((int)$data[$m]['data'][$n]['re_num'] == 0 || !isset($data[$m]['data'][$n]['re_num'])) {
                    return false;
                }
                if ($pid != $data[$m]['data'][$n]['pid']) {
                    return false;
                }
                if (!isset($data[$m]['data'][$n]['rpid'])) {
                    /* Assembling Maintenance Product Data */
                    $repairPersonAddData[] = array(
                        'pid'               => (int)$data[$m]['data'][$n]['pid'],
                        'reperson_id'       => (int)substr($data[$m]['data'][$n]['staffArr'],0,strpos($data[$m]['data'][$n]['staffArr'], "_")),
                        'reperson_name'     => substr($data[$m]['data'][$n]['staffArr'], strpos($data[$m]['data'][$n]['staffArr'], "_") + 1),
                        'product_name'      => $data[$m]['data'][$n]['product_name'],
                        're_num'            => $data[$m]['data'][$n]['re_num'],
                        'start_date'        => $data[$m]['data'][$n]['start_date'],
                        're_status'         => $data[$m]['data'][$n]['re_status'],
                        're_mode'           => $data[$m]['data'][$n]['re_mode'],
                        'piece_wage'        => $data[$m]['data'][$n]['piece_wage'],
                        'reperson_question' => $data[$m]['data'][$n]['reperson_question'],
                        'situation'         => $data[$m]['data'][$n]['situation'],
                        'meter_piece'       => $data[$m]['data'][$n]['meter_piece'],
                        'fault_info'        => $data[$m]['data'][$n]['fault_info'],
                        'mode_info'         => $data[$m]['data'][$n]['mode_info']
                    );
                }
            }
        }
        return $repairPersonAddData = array_values($repairPersonAddData);
    }

    /**
     * 修改单个维修专员的信息
     * @param $postData
     * @return array
     */
    public function editPersonOneMsg($postData){
        if(empty($postData['rpid'])){
            return ["参数错误",2, 0];
        }

        $data = self::getNewField($postData);

        $oldData = $this->find($data['rpid']);
        list($editData, $money) = self::compareData($oldData, $data);
        if(!$editData){
            return ['无数据修改', 1, 0];
        }

        $res = $this->save($editData);
        if($res === false){
            return [$this->getError(), 2, 0];
        }
        return ["数据修改成功", 0, $money];
    }

    /**
     * 修改多个维修专员的维修信息
     * @param $postData
     * @return array
     */
    public function editPersonMsg($postData){
        try{
            $this->startTrans();

            $postData = array_filter($postData);
            if(empty($postData)){
                $this->rollback();
                return dataReturn("无数据提交",400);
            }

            $flag = 1;
            $allChangeMoney = 0;

            foreach($postData as $key => $value){
                list($msg, $code, $money) = self::editPersonOneMsg($value);
                if($code == 2){
                    $this->rollback();
                    return dataReturn($msg, 400);
                }
                if($code == 0){
                    $flag = 0; // 标记已有修改数据
                    $allChangeMoney = $allChangeMoney + $money;
                }
            }
            if($flag == 1){
                $this->rollback();
                return dataReturn("无数据发生成修改",400);
            }

            if ($allChangeMoney != 0){
                // 维修费用发生变化，需要修改基本单的总金额与审核状态
                $id = $postData[0]['pid'];
                $recordModel = new SalerecordModel();
                $oldRecordData = $recordModel->find($id);

                $data = [];
                $data['sid'] = $id;
                $data['sum_fee'] = $oldRecordData['sum_fee'] + $allChangeMoney;
                if($oldRecordData['is_ok'] != 1 && $oldRecordData['is_ok'] != 2){
                    $data['is_ok'] = 2;
                    $data['change_status_time'] = time();
                }
                $res = $recordModel->save($data);
                if($res === false){
                    $this->rollback();
                    return dataReturn($recordModel->getError(),400);
                }
            }

            $this->commit();
            return dataReturn("数据修改成功", 200);

        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(),400);
        }
    }
}