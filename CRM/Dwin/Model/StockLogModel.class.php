<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/13
 * Time: 9:51
 */
namespace Dwin\Model;
use Think\Model;
class StockLogModel extends Model
{
    /**
     * 获取添加内容
     * */
    public function getAddData($content, $type)
    {
        $data = [
            'staff_name' => session('nickname'),
            'content'    => $content,
            'type'       => $type,
            'add_time'   => time()
        ];
        return $data;
    }
}