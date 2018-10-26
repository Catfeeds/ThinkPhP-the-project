<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/6/6
 * Time: 10:04
 */

namespace Dwin\Model;


use Think\Model;

class ResearchModel extends Model
{
    public function getPrjBasic($prjId)
    {
        return $this->join('LEFT JOIN crm_staff AS s ON crm_research.builderid = s.id')
                    ->join('LEFT JOIN crm_staff AS a ON crm_research.auditorid = a.id')
                    ->join('LEFT JOIN crm_dept AS b ON crm_research.projectdepartment = b.id')
                    ->join('LEFT JOIN crm_customer AS cus ON crm_research.customerid = cus.cid')
                    ->join('LEFT JOIN crm_resjixiao AS jx ON crm_research.proid = jx.prjid')
                    ->field('crm_research.*,s.name AS buildname,b.name AS deptname,
                        a.name AS auditname,cus.cname AS cusname,cus.ctype')
                    ->find($prjId);
    }

    public function addChangeRecord($msg)
    {
        if ($msg != 2 && $msg != 3 && $msg != 4 && $msg != 5) {

            $rst = $this->add($msg);
            if ($rst != false) {
                return 1;
            } else return 7;
        } else return 6;
    }


}