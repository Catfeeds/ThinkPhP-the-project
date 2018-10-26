<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/5/17
 * Time: 15:37
 */
namespace Dwin\Model;
use Think\Model;

class FileRecordModel extends Model
{
    public function addFile($params, $table, $id)
    {
        if ($id === false){
            return false;
        }
        $data = [
            'type' => '新增',
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'add_time' => time(),
            'table' => $table
        ];
        $data['content'] = $data['staff_name'] . $data['type'] . '数据于' . $table . ', 新增行id为' . $id .', 时间' . date('Y-m-d H:i:s');
        return $this->add($data);
    }

    public function editFileRecord($params, $table)
    {
        $oldData = M($table) -> find($params['id']);

        $data = [
            'type' => '修改',
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'add_time' => time(),
            'table' => $table
        ];
        $data['content'] = $data['staff_name'] . $data['type'] . '数据于' . $table . ', 新增行id为' .', 时间' . date('Y-m-d H:i:s') . ', 修改内容为: ';

        foreach ($params as $key => $value) {
            if ($value != $oldData[$key]){
                $data['content'] .= "$key 字段, 旧数据为$oldData[$key], 新数据为$value ; ";
            }
        }

        return $this->add($data);
    }

    public function delFileRecord($id, $table)
    {
        $data = [
            'type' => '删除',
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'add_time' => time(),
            'table' => $table
        ];
        $data['content'] = $data['staff_name'] . $data['type'] . "$table 表 id为 $id 的数据, ".', 时间' . date('Y-m-d H:i:s');
        return $this->add($data);
    }

    public function downloadFileRecord($id, $table)
    {
        $data = [
            'type' => '下载',
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'add_time' => time(),
            'table' => $table
        ];
        $data['content'] = "用户 {$data['staff_name']} 下载了$table 表中id为$id 的文件, 时间" . date('Y-m-d H:i:s');
        return $this->add($data);
    }

    public function previewPdfFileRecord($id, $table)
    {
        $data = [
            'type' => '预览',
            'staff_id' => session('staffId'),
            'staff_name' => session('nickname'),
            'add_time' => time(),
            'table' => $table
        ];
        $data['content'] = "用户 {$data['staff_name']} 预览了的$table 表中id为$id 的文件, 时间" . date('Y-m-d H:i:s');
        return $this->add($data);
    }
}