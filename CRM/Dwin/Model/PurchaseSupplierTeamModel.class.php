<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/6/15
 * Time: 下午2:18
 */

namespace Dwin\Model;

use Think\Exception;
use Think\Model;

class PurchaseSupplierTeamModel extends Model
{
    static protected $successStatus = 200;
    static protected $failStatus    = 400;
    static protected $notDel = 0;
    static protected $isDel  = 1;

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

    //数据验证
    protected $_validate = array(
        array("team_cate","require","团队人员类别不能为空!"),
        array("team_number","require","团队人员数量不能为空!"),
        array("tips","require","团队人员备注不能为空!"),
    );

    public function getAddData($params)
    {
        $data = $this->getNewField($params);
        if (empty($data)) {
            return [-1, [], '没有提交新增数据'];
        }
        if(empty($data['team_cate']) || empty($data['team_number'])){
            return [-2, [], "请将数据填写完整"];
        }

        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');
        $data['supplier_pid'] = session('supplierPid');

        $data = $this->create($data);
        if(!$data){
            return [-2, [], $this->getError()];
        }else {
            return [0, $data, '数据实例化成功'];
        }
    }

    public function getEditData($params)
    {
        if (empty($params)) {
            $this->error = "无修改数据提交";
            return [-1, []];
        }

        $oldData = $this->field("*")->find($params['id']);
        $data = $this->getNewField($params);

        $editData = $this->compareData($oldData, $data);
        if ($editData === false) {
            $this->error = "无数据修改";
            return [-1, []];
        } else {
            $createData = $this->create($editData);
            if(!$createData){
                $this->error = $this->getError();
                return[-2,[]];
            }
            return [0, $createData];
        }
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

        $editedData['id']    = $oldData['id'];
        $editedData['supplier_pid'] = $oldData['supplier_pid'];
        $editedData['update_time']  = time();
        $editedData['update_id']    = session('staffId');
        return $editedData;
    }

    public function getTeamWithPId($supplierId)
    {
        $map['supplier_pid'] = ['EQ', $supplierId];
        $map['is_del'] = ['EQ', self::$notDel];
        return $this
            ->field("crm_purchase_supplier_team.*,crm_staff.name")
            ->join("left join crm_staff on crm_staff.id = crm_purchase_supplier_team.create_id")
            ->where($map)
            ->select();

    }
}