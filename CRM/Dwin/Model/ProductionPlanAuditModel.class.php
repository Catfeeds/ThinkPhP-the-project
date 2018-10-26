<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/17
 * Time: 16:38
 */
namespace Dwin\Model;
use Think\Model;
class ProductionPlanAuditModel extends Model
{
    const SUCCESS_STATUS = 1;
    const FAIL_STATUS = -1;

    protected $_auto = [
        ['create_time', 'time', 1, 'function'],
        ['update_time', 'time', 3, 'function'],
    ];

    /**
     * 获取该订单的所有审核记录, 齐料确认与单据产线确认分开2个type
     * @param $order string 生产订单号
     * @return array
     */
    public function getAuditByOrder($order)
    {
        $map = ['production_order' => $order, 'audit_type' => ['NEQ', 4]];
        $data = $this
             -> where($map)
             -> select();
        return $data;
    }

    /**
     * 新增审核记录
     * @param $params   审核请求信息
     * @return bool
     */
    public function addProductionPlanAudit($params)
    {
        $params['auditor_name'] = getStaffNameByStaffID($params['auditor']);
        if ($params['production_status'] == 2){
            $params['audit_type_name'] = '单据审核';
        }elseif ($params['production_status'] == 3){
            $params['audit_type_name'] = '产线确认';
        }elseif ($params['production_status'] == 5){
            $params['audit_type_name'] = '完工确认';
        }elseif ($params['production_status'] == 4){
            $params['audit_type_name'] = '齐料确认';
        }
        $params['audit_type'] = $params['production_status'];
        $audit = $this->create($params);
        if ($audit !== false) {
            if ($this->add()){
                return $audit;
            }
        }
        $this->error = '添加审核记录失败';
        return false;
    }

    /**
     * 更新订单审核
     * @param $params   审核信息
     * @return bool
     * todo SMT审核不影响在生产产品数量。
     */
    public function editProductionPlanAudit($params, $plan)
    {
        // 设置生产单状态
        if ($plan['production_status'] == 1){               // 单据审核
            $params['production_status'] = 2;
        }else if ($plan['production_status'] == 2){         // 齐料审核
            $params['production_status'] = 4;
        }else if ($plan['production_status'] == 4){         // 产线确认
            $params['production_status'] = 3;
        }else if ($plan['production_status'] == 3){         // 生产完成
            $this->error = "生产中，禁止继续审核";
            return false;
        }else{
            $this->error = '状态异常';
            return false;
        }
        $this->startTrans();
        $productionPlanModel = new ProductionPlanModel();
        $productPlan = $productionPlanModel->where(['production_order' => $params['production_order']])->find();
        if ($productPlan['audit_status'] == 400){
            $this->error = '该生产计划已经失效';
            return false;
        }
        // 如果单据审核没有通过, 设置这个生产计划状态为400
        if ($params['production_status'] == 2 && $params['audit_result'] == 2){
            $params['production_status'] = 400;
        }
        $audit = $this->addProductionPlanAudit($params);
        if ($audit !== false) {
            // 产线确认后需要更新产品表中正在生产数
            if ($audit['audit_type'] == 3) {

                $productModel = new MaterialModel();
                $res = $productModel->updateProducingNumber($productPlan['product_id'], $productPlan['production_plan_number']);
                if (!$res) {
                    $this->error = '产品表正在生产数量更新失败';
                    return false;
                }
            }
            // 更新生产计划的状态

            $res = $productionPlanModel
                ->where(['production_order' => ['EQ', $audit['production_order']]])
                ->save(['production_status' => $params['production_status']]);
            if ($res !== false) {
                $this->commit();
                return true;
            } else {
                $this->rollback();
                $this->error = '产品计划状态修改失败';
            }
        } else {
            $this->error = '审计记录添加失败';
        }

        $this->rollback();
        return false;
    }
}