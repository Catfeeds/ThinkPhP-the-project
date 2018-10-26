<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/6/14
 * Time: 16:32
 */

namespace Dwin\Model;


use Think\Model;

class ScreenCategoryModel extends Model
{

    /**
     * 获取物料分类基本信息:id 主键 name 名字 pid 上级id
    */
    public function getBaseInfo()
    {
        return $this->field('id,name,pid')->select();
    }
}
