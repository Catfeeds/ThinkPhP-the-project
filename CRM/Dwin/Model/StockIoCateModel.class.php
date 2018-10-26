<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/13
 * Time: 9:51
 */
namespace Dwin\Model;
use Think\Model;
class StockIoCateModel extends Model
{
    protected $_map = [
        'fenlei' => 'type',
        'fenleiming' => 'cate_name',
    ];

    protected $_validate = [
        ['type','require','请选择分类'],
        ['cate_name','require','清输入类名'],
    ];

    /**
     * 获得所有出入库分类信息
     * @return array
     */
    public function index()
    {
        $data = [];
        $data['iCate'] = $this -> where(['type' => 1]) -> select();
        $data['oCate'] = $this -> where(['type' => 2]) -> select();
        return $data;
    }
}