<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/6/7
 * Time: 10:56
 */

namespace Dwin\Model;


use Think\Model;

class ResjixiaoModel extends Model
{
    // 查询绩效方法
    public function getPrjJX($prjId)
    {
        $condi['prjid'] = array('EQ', $prjId);// 项目ID查询条件
        return $this->where($condi)
            ->join('LEFT JOIN crm_research AS r ON prjid = r.proid')
            ->join('LEFT JOIN crm_staff AS s ON pid = s.id')
            ->field('crm_resjixiao.*,r.proname,r.performbonus')
            ->select();
    }




}