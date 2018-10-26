<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/5/19
 * Time: 9:23
 */
namespace Dwin\Model;
use Think\Model;

class ProductionPlanRecordModel extends Model
{
    public function editProductionPlanRecord($id, $data)
    {
        $row = [
            'change_type' => '修改',
            'staff_name' => session('staffId'),
            'staff_id' => session('nickname'),
            'add_time' => time(),
        ];
        $row['change_content'] = "{$row['staff_name']}修改了生产计划, 时间: " . date('Y-m-d H:i:s') . '更改内容: ';
        $map = [
            'stock_cate_name' => '备货方式',
            'production_line_name' => '生产线',
            'delivery_time' => '期望交期',
            'tips' => '备注',
            'fail_explain' => '延期说明',
            'production_plan_number' => '生产数量',
        ];
        $oldData = M('production_plan') -> find($id);
        foreach ($data as $key => $value) {
            if ($value != $oldData[$key]) {
                $row['change_content'] .= "{$map[$key]}: 旧数据为: {$oldData[$key]} , 新数据为: {$value}  ;";
            }
        }
        return $this->add($row) === false ? false : true;
    }

    public function delProductionPlanRecord($id)
    {
        $row = [
            'change_type' => '删除',
            'staff_name' => session('staffId'),
            'staff_id' => session('nickname'),
            'add_time' => time(),
        ];
        $row['change_content'] = "{$row['staff_name']}删除了id为 $id 的生产计划, 时间: " . date('Y-m-d H:i:s');
        return $this->add($row);
    }
}