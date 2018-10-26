<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/9/29
 * Time: 上午10:18
 */
namespace Dwin\Model;
use Think\Model;

class MaterialBomLogModel extends model{
    const TYPE_ADD = 1; // BOM新增操作
    const TYPE_EDIT = 2; // BOM修改操作
    const TYPE_AUDIT = 3; // BOM审核操作
    const TYPE_DEL = 4; // BOM删除操作
    const TYPE_DEL_MATERIAL = 5; // BOM物料删除操作
    const TYPE_FORBIDDEN = 6; // BOM禁用操作

    public static $bomTypeMap = [
        self::TYPE_ADD => "BOM新增操作",
        self::TYPE_EDIT => "BOM修改操作",
        self::TYPE_AUDIT => "BOM审核操作",
        self::TYPE_DEL => "BOM删除操作",
        self::TYPE_DEL_MATERIAL => "BOM物料删除操作",
        self::TYPE_FORBIDDEN => "BOM禁用操作",
    ];

    /**
     * 添加bom操作履历
     * @param $bomId
     * @param $type
     * @param $content
     * @return  array
     */
    public function createBomLog($bomId, $type, $content){
        $data['create_id'] = session("staffId");
        $data['create_name'] = session("nickname");
        $data['create_time'] = time();
        $data['type'] = $type;
        $data['bom_pid'] = $bomId;
        $data['content'] = $content;
        $res = $this->add($data);
        if($res === false){
            return [$this->getError(), 400];
        }
        return ["创建履历成功",200];
    }
}