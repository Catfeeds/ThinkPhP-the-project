<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/13
 * Time: 9:51
 */
namespace Dwin\Model;
use Think\Model;
class StockIoRecordModel extends Model
{
    protected $_map = [
        'id'        => 'audit_id'
    ];

    protected $_auto = [
        ['update_time', 'time', 1, 'function']
    ];

    const IS_DEL = 1; // 已删除
    const NO_DEL = 0; // 未删除

    const TYPE_NOT_AUDIT = 0;      // 未审核
    const TYPE_UNQUALIFIED = 1;     // 不合格
    const TYPE_QUALIFIED = 2;       // 合格 已入库

    /**
     * @param $map
     * @param $start
     * @param $length
     * @param $order
     * @return array
     */
    public function index($map, $start, $length, $order)
    {
        $res = $this
            -> alias('record')
            -> field('audit.*, cate.cate_name, staff.name')
            -> join('crm_stock_audit as audit on record.audit_id = audit.id')
            -> join('crm_stock_io_cate as cate on audit.cate = cate.id')
            -> join('crm_staff as staff on audit.proposer = staff.id')
            -> where($map)
            -> limit($start, $length)
            -> order($order)
            -> select();
        return $res;
    }

    public function indexCount($map)
    {
        $res = $this
            -> alias('record')
            -> field('audit.*, cate.cate_name, staff.name')
            -> join('crm_stock_audit as audit on record.audit_id = audit.id')
            -> join('crm_stock_io_cate as cate on audit.cate = cate.id')
            -> join('crm_staff as staff on audit.proposer = staff.id')
            -> where($map)
            -> count();
        return $res;
    }


}