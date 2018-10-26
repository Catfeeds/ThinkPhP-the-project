<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/6/7
 * Time: 10:59
 */

namespace Dwin\Model;


use Think\Model;

class ResprogressModel extends  Model
{
    public function getPrjProgress($where)
    {
        $updateContents = $this ->where($where)
                                ->join('left join crm_staff AS s ON prjer_id = s.id')
                                ->join('left join crm_research AS res ON res.proid = project_id')
                                ->field('crm_resprogress.*,s.name,res.proname,res.builderid')
                                ->order('posttime DESC')
                                ->select();
        for ($i = 0; $i < count($updateContents); $i++) {
            $updateContents[$i]['prjcontent'] = mb_substr($updateContents[$i]['prjcontent'],0,40);
        }
        return $updateContents;
    }
    public function getCounts($where)
    {

        return $count = $this->where($where)->count('id');
    }

}