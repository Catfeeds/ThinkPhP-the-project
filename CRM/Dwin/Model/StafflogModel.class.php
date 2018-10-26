<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/5/5
 * Time: 上午9:54
 */

namespace Dwin\Model;


use Think\Model;

class StafflogModel extends Model
{

    /**
     *提交数据返回状态
     * 200 成功 400 失败 403 禁止操作
     */
    const SUCCESS_STATUS   = 200;
    const FAIL_STATUS      = 400;
    const FORBIDDEN_STATUS = 403;
    public function getData($map, $field, $start, $length, $order='id desc')
    {
        return $this->alias('log')->join('LEFT JOIN crm_staff staff ON staff.id = log.staffid')
            ->where($map)->field($field)->limit($start, $length)->order($order)->select();
    }

    public function getIndexData($map, $sqlCondition)
    {
        $field = 'staff.name staff_name,staff.username staff_account,from_unixtime(log.request_time) request_time';
        $field .= ',log.remote_addr request_ip, auth_rule_name request_module, user_agent device, request_method,request_uri url';
        $data = $this->getData($map, $field, $sqlCondition['start'], $sqlCondition['length'], $sqlCondition['order']);
        return $data;
    }

}