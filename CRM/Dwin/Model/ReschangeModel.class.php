<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/6/8
 * Time: 9:06
 */

namespace Dwin\Model;


use Think\Model;

class ReschangeModel extends Model
{
    public function findChangeRecord($posts, $id, $postTime)
    {
        /*---------------------表单提交数据判断修改内容----------------------------*/
        $msg['projid'] = $posts['prjId'];
        $msg['changetime'] = $postTime;
        $msg['postId'] = $id;

        $posts = array_filter($posts);

        $posts['oldPrjPrice'] = setValue('oldPrjPrice', $posts);
        $posts['oldPrjMaint'] = setValue('oldPrjMaint', $posts);
        $posts['oldPrjTemp']  = setValue('oldPrjTemp', $posts);
        $posts['oldPrjPcb']   = setValue('oldPrjPcb', $posts);
        $posts['oldDocWrite'] = setValue('oldDocWrite', $posts);
        $posts['oldCodeDesign'] = setValue('oldCodeDesign', $posts);
        $posts['oldBonus'] = setValue('oldBonus', $posts);
        $posts['newPrjPrice'] = setValue('newPrjPrice', $posts);
        $posts['newPrjMaint'] = setValue('newPrjMaint', $posts);
        $posts['newPrjTemp']  = setValue('newPrjTemp', $posts);
        $posts['newPrjPcb']   = setValue('newPrjPcb', $posts);
        $posts['newDocWrite'] = setValue('newDocWrite', $posts);
        $posts['newCodeDesign'] = setValue('newCodeDesign', $posts);
        if ($posts['auditId'] != $posts['oldAuditId']) {
            $msg['oldauditorid'] = $posts['oldAuditId'];
            $msg['newauditorid'] = $posts['auditId'];
        }
        if (isset($posts['newDeliveryTime']) && ($posts['newDeliveryTime'] != $posts['oldDeliveryTime'])) {
            $msg['oldDeliveryTime'] = strtotime($posts['oldDeliveryTime']);
            $msg['newDeliveryTime'] = strtotime($posts['newDeliveryTime']);
        }
        if (isset($posts['newPrjNeeds']) && $posts['newPrjNeeds'] != $posts['oldPrjNeeds']) {
            $msg['oldPrjNeeds'] = $posts['oldPrjNeeds'];
            $msg['newPrjNeeds'] = $posts['newPrjNeeds'];
        }
        if (isset($posts['newBonus']) && $posts['newBonus'] != $posts['oldBonus']) {
            $msg['oldBonus']    = $posts['oldBonus'];
            $msg['newBonus']    = $posts['newBonus'];
            if (isset($posts['newPrjPrice']) && $posts['newPrjPrice'] != $posts['oldPrjPrice']) {
                $msg['oldPrjPrice'] = $posts['oldPrjPrice'];
                $msg['newPrjPrice'] = $posts['newPrjPrice'];
            }
            if (isset($posts['newPrjMaint']) && $posts['newPrjMaint'] != $posts['oldPrjMaint']) {
                $msg['oldPrjMaint'] = $posts['oldPrjMaint'];
                $msg['newPrjMaint'] = $posts['newPrjMaint'];
            }
            if (isset($posts['newPrjTemp']) && $posts['newPrjTemp'] != $posts['oldPrjTemp']) {
                $msg['oldPrjTemp']  = $posts['oldPrjTemp'];
                $msg['newPrjTemp']  = $posts['newPrjTemp'];
            }
            if (isset($posts['newPrjPcb']) && $posts['newPrjPcb'] != $posts['oldPrjPcb']) {
                $msg['oldPrjPcb']   = $posts['oldPrjPcb'];
                $msg['newPrjPcb']   = $posts['newPrjPcb'];
            }
            if (isset($posts['newDocWrite']) && $posts['newDocWrite'] != $posts['oldDocWrite']) {
                $msg['newDocWrite'] = $posts['newDocWrite'];
                $msg['oldDocWrite'] = $posts['oldDocWrite'];
            }
            if (isset($posts['newCodeDesign']) && $posts['newCodeDesign'] != $posts['oldCodeDesign']) {
                $msg['oldCodeDesign'] = $posts['oldCodeDesign'];
                $msg['newCodeDesign'] = $posts['newCodeDesign'];
            }
        }
        if (isset($posts['newJXVal'])) {
            foreach ($posts['newJXVal'] as $key => $val) {
                if (empty($val)) {
                    $flag = 1;
                }
            }
            if (isset($flag) && $flag == 1) {
                return 2; //2: 绩效分配不一致
            } else {
                $msg['oldPartner']  = json_encode($posts['oldPartner']);
                $msg['newPartner']  = json_encode($posts['newPartner']);
                $msg['oldPartName'] = json_encode($posts['oldPartName']);
                $msg['newPartName'] = json_encode($posts['newPartName']);
                $msg['oldJXVal']    = json_encode($posts['oldJXVal']);
                $msg['newJXVal']    = json_encode($posts['newJXVal']);
            }
        }
        if (isset($msg['oldauditorid']) || isset($msg['oldBonus']) || isset($msg['oldDeliveryTime']) || isset($msg['oldJXVal']) || isset($msg['oldPrjNeeds']))
        {
            return $msg;
        } else {
            return 5; // 5:没有进行变更，不执行下面的逻辑
        }
    }
}