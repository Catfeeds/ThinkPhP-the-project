<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 17-5-10
 * Time: 上午11:54
 */

namespace Dwin\Controller;

use Dwin\Model\IndustrialSeralScreenModel;
use Dwin\Model\OnlineserviceModel;
use Dwin\Model\OrderCollectionModel;
use Dwin\Model\ProductionPlanModel;
use Dwin\Model\StaffModel;
use Think\Controller;

class TestController extends CommonController
{
    const TAB_1 = 1;
    const TAB_2 = 2;
    const TAB_3 = 3;
    const DEPT = '4,18,43,49,50,51';
    public function info()
    {
        phpinfo();
    }
    //客服业绩统计
    public function countServicePerformance(){
        die;
        $onlineModel = new OnlineserviceModel();
        $customer    = new CustomerController();
        $staffModel  = new StaffModel();
        //本月起始时间
        $monthStart=mktime(0,0,0,date('m'),1,date('Y'));
        $roleFilter['role_id'] = array('IN', $customer::ONLINE_DEPT);
        $onlineIds = D('auth_role')->getRoleList('staff_ids', $roleFilter, 'staff_ids',0, 500);
        $onlineIds = getPrjIds(array_filter($onlineIds),'staff_ids');

        $map['crm_staff.id'] = array('IN', $onlineIds . ",1");
        $map['crm_staff.loginstatus'] = array('NEQ', "1");
        $this->field = "crm_staff.id,crm_staff.name,GROUP_CONCAT(c.role_name) role_name, b.name dept";

        $data = $staffModel->getStaffInfo($this->field, $map, 0, 50, 'crm_staff.id');

        $staffIds = getPrjIds($data, 'id');

        //查询属于客服部门的所有人
        $onlineFilter['server_id'] = array('IN', $staffIds);
        $onlineFilter['austatus']   = array('EQ', "2");
        $onlineFilter['crm_onlineservice.addtime']    = array('gt', $monthStart);
        if(IS_AJAX){
            //业务
            $this->posts        = I('post.');
            $draw         = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $this->field = "cname c_id, content cus_question,answercontent online_solution, s.name staff_name,from_unixtime(crm_onlineservice.addtime) service_time";
            $count = $onlineModel->where($onlineFilter)->count();

            if ($this->sqlCondition['search']) {
                $onlineFilter['c_id|staff_name'] = array('like', "%" . $this->sqlCondition['search'] . "%");
            }
            $filterRecord = $onlineModel->where($onlineFilter)->count();
            $serviceData = $onlineModel->getServiceList($onlineFilter, $this->field, $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);
            $this->output = $this->getDataTableOut($draw, $count, $filterRecord, $serviceData);
            $this->ajaxReturn($this->output);
        } else{

            foreach ($data as &$val) {
                $val['cus_num'] = 0;
                $val['service_num'] = 0;
            }
            $where['b.picid']       = array('IN', $staffIds);
            $where['a.settle_time'] = array('GT', $monthStart);
            /* order collection data where staffIds in online positions and settle_time gt start of this month */
            $result = M('order_collection')
                ->alias('a')
                ->field('b.cus_id, round(
                    sum(case  
                        when settle_type in (1,7) and a.product_id in (15001,15002,15004) and p.statistics_performance_flag = 1
                            then (case
                                    when b.settlement_method in (\'JF05\', \'JF16\')
                                        then settle_price * 0.99
                                    when b.settlement_method in (\'HP01\', \'HP02\') 
                                         then settle_price * 0.98
                                else settle_price end)
                        when settle_type in (3,8) and a.product_id in (15001,15002,15004) and p.statistics_performance_flag = 1
                            then (case
                                    when b.settlement_method in (\'JF05\', \'JF16\')
                                        then settle_price * 0.99 * (-1)
                                    when b.settlement_method in (\'HP01\', \'HP02\') 
                                         then settle_price * 0.98 * (-1)
                                else settle_price * (-1) end)
                        when settle_type in (1,7) and a.product_id not in (15000,15001,15002,15003,15004) and p.statistics_performance_flag = 1 
                            then 
                                (case
                                    when b.settlement_method in (\'JF05\', \'JF16\')
                                        then settle_price * 0.99
                                    when b.settlement_method in (\'HP01\', \'HP02\') 
                                         then settle_price * 0.98
                                else settle_price end)
                        when settle_type in (3,8) and a.product_id not in (15000,15001,15002,15003,15004) and p.statistics_performance_flag = 1
                            then 
                                (case
                                    when b.settlement_method in (\'JF05\', \'JF16\')
                                        then settle_price * 0.99
                                    when b.settlement_method in (\'HP01\', \'HP02\') 
                                         then settle_price * 0.98
                                else settle_price end) * (-1)
                        else 0 end),2) settle_normal_price, b.picid, d.name, e.name as dept,k.cus_pid,k.cname')
                ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
                ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
                ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
                ->join('LEFT JOIN crm_industrial_seral_screen p ON p.product_id = a.product_id')
                ->join('LEFT JOIN crm_customer k ON k.cid = b.cus_id')
                ->where($where)
                ->group('cus_id')
                ->select();

            $cusArr = array();
            foreach($result as $value) {
                $cusArr[] = $value['cus_id'];
            }
            foreach ($result as $item) {

                if (!empty($item['cus_pid'])) {
                    if (!in_array($item['cus_pid'], $cusArr)) {
                        // 总公司没有结算记录
                        $cusFilter['cid'] = array('EQ', $item['cus_pid']);
                        $addData = M('customer')->field("cid cus_id, cname")->where($cusFilter)->find();
                        $addData['name'] = $item['name'];
                        $addData['dept'] = $item['dept'];
                        $addData['settle_normal_price'] = 0;
                        $addData['cus_pid'] = null;
                        $addData['picid'] = $item['picid'];
                        array_push($result, $addData);
                        array_push($cusArr, $item['cus_pid']);
                    }
                }
            }

            /* 客户涉及到子公司，把子公司的业绩追加到总公司 */
            for ($i = 0; $i < count($result); $i++) {
                $result[$i]['count'] = $result[$i]['settle_normal_price'];
                for ($j = 0; $j < count($result); $j++) {
                    if ($result[$i]['cus_id'] == $result[$j]['cus_pid']) {
                        $result[$i]['count'] += $result[$j]['settle_normal_price'];
                    }
                }
            }
            /* 去除掉子公司 */
            $statistics = array();
            for ($p = 0; $p < count($result); $p++) {
                if (empty($result[$p]['cus_pid'])) {
                    $statistics[$p]['picid']  = $result[$p]['picid'];
                    $statistics[$p]['name']  = $result[$p]['name'];
                    $statistics[$p]['cname'] = $result[$p]['cname'];
                    $statistics[$p]['dept']  = $result[$p]['dept'];
                    $statistics[$p]['count'] = $result[$p]['count'];
                }
            }
            $statistics = array_values($statistics);


            /* 客户服务记录 */
            $onlineModel = new OnlineserviceModel();
            $this->field = "count(crm_onlineservice.id) service_num, server_id";
            $serviceData = $onlineModel->getServiceListWithGroup($onlineFilter, $this->field, 'crm_onlineservice.addtime', 0,10000, 'server_id');

            /* 追加产生业绩的客户数量到客服部门各个员工。 */
            foreach ($data as &$val) {
                foreach ($statistics as $v) {
                    if ($v['picid'] == $val['id']) {
                        $val['cus_num'] += 1;
                    }
                }
                foreach ($serviceData as $p) {
                    if ($p['server_id'] == $val['id']) {
                        $val['service_num'] = $p['service_num'];
                    }
                }
            }
            //$data = array_fill_keys(implode(',', $serviceIds),"picid" );
            $this->assign('data', $statistics);
            $this->assign('staffData', $data);
            $this->display();
        }
    }

    public function product()
    {


        $wuliao = M('mis_wlmx') -> select();
        $_map = [
            'wlmc'   =>  'product_number',
            'wlxh'   =>  'product_name',
            'lb'     => 'cate',
            'ckbh'   => 'warehouse_number',
            'kcsl'   => 'stock_number',
            'udate'  => 'update_time',
            'bksl'   => 'standby_number',
            'zfgsl'  => 'rework_number',
            'safety_kc' => 'safety_number',
            'wlbh'   => 'product_no',
            'bz'     => 'tips',

        ];
        $productModel = M('industrial_seral_screen');
        $productModel -> startTrans();
        $res = true;
        foreach ($wuliao as $key => $item) {
            $data = [];
            foreach ($_map as $old => $new) {
                if ($item[$old] != null){
                    $data[$new] = rtrim($item[$old]);
                }
            }
            $update_time = strtotime($data['update_time']);
            if ($update_time == false){
                $arr = explode(' ',$data['update_time']);
                array_pop($arr);
                $update_time = strtotime(implode($arr));
            }
            $data['product_name'] = rtrim($data['product_name'], '?');
            $data['product_number'] = rtrim($data['product_number'], '?');
            $data['update_time'] = $update_time;
            if ($data['warehouse_number'] == '2'){
                $data['warehouse_number'] = 'K004';
                $data['warehouse_name'] = '成品库-A';
            }elseif ($data['warehouse_number'] == '3'){
                $data['warehouse_number'] = 'K001';
                $data['warehouse_name'] = '元器件A库';
            }elseif ($data['warehouse_number'] == '4'){
                $data['warehouse_number'] = 'K002';
                $data['warehouse_name'] = '元器件B库';
            }else{
                $data['warehouse_number'] = 'K004';
                $data['warehouse_name'] = '成品库-A';
            }
            $map = ['product_name' => ['EQ', $data['product_name']]];
            $have = $productModel -> where($map) -> select();
            if (count($have) != 0){
                foreach ($have as $key2 => $value2){
                    $map = ['product_id' => ['EQ',$value2['product_id']]];
                    if ($productModel -> where($map) -> save($data) === false){
                        $res = false;
                        break;
                    }
                }
            }else{
                if ($productModel -> add($data) === false){
                    $res = false;
                    break;
                }
            }
        }
        if ($res === false){
            $productModel -> rollback();
            dump(false);
        }else{
            $productModel -> commit();
            dump(true);
        }
    }

    public function production()
    {
        die;

        $map = [
            'scdh' => 'production_order',
            'ywy' => 'staff_name',
            'wlbh' => 'product_no',
            'wlmc' =>  'product_number',
            'wlxh' =>  'product_name',
            'scx' => 'production_line_name',
            'scsl' => 'production_plan_number',
            'xdrq' => 'create_time',
            'yjscjq' => 'delivery_time',
            'wgrq' => 'complete_time',
            'tsyq' => 'tips',
            'bhfs' => 'stock_cate_name',
            'rksl' => 'production_number',
            'scts' => 'SCTS',
            'wtcms' => 'WTCMS',
            'zdr' => 'ZDR',
            'zdrq' => 'ZDRQ',
            'lb' => 'LB',
            'yqts' => 'YQTS',
        ];
        $oldData = M('mis_scrwd') -> select();
        $newData = [];
        foreach ($oldData as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                if (in_array($key2, ['xdrq', 'yjscjq', 'wgrq', 'qlrj', 'shrq', 'zdrq', 'scqrrq'])) {
                    $value2 = strtotime($value2);
                }
                if ($map[$key2] != '') {
                    $newData[$key1][$map[$key2]] = rtrim($value2);
                }
            }
            if (strpos($newData[$key1]['product_name'], '&')){
                $newData[$key1]['product_name'] = explode('&', $newData[$key1]['product_name'])[0];
            }
            if (strpos($newData[$key1]['product_number'], '&')){
                $newData[$key1]['product_number'] = explode('&', $newData[$key1]['product_number'])[0];
            }

            $newData[$key1]['production_status'] = 1;
            if ($value1['shbz'] == 1) {
                $newData[$key1]['production_status'] = 2;
            }
            if ($value1['qlbz'] == 1) {
                $newData[$key1]['production_status'] = 4;
            }
            if ($value1['scqrbz'] == 1) {
                $newData[$key1]['production_status'] = 3;
            }
            if ($value1['wgbz']) {
                $newData[$key1]['production_status'] = 5;
            }

            if (rtrim($value1['bhfs']) == '备库'){
                $newData[$key1]['stock_cate'] = 1;
            }elseif(rtrim($value1['bhfs']) == '应收'){
                $newData[$key1]['stock_cate'] = 2;
            }elseif(rtrim($value1['bhfs']) == '标准'){
                $newData[$key1]['stock_cate'] = 3;
            }else{
                $newData[$key1]['stock_cate'] = 0;
            }

            if (rtrim($value1['scx']) == '生产线'){
                $newData[$key1]['production_line'] = 1;
            }elseif(rtrim($value1['scx']) == 'SMT线'){
                $newData[$key1]['production_line'] = 2;
            }elseif(rtrim($value1['scx']) == '装配线'){
                $newData[$key1]['production_line'] = 3;
            }else{
                $newData[$key1]['production_line'] = 0;
            }

            if ($newData[$key1]['production_plan_number'] == $newData[$key1]['production_number']){
                $newData[$key1]['production_status'] = 5;
            }

            $newData[$key1]['product_id'] = $this->getProductIDByProductName($newData[$key1]['product_name']);

            if ($newData[$key1]['production_status'] == 3){
                $newData[$key1]['production_plan_rest_number'] = $newData[$key1]['production_plan_number'] - $newData[$key1]['production_number'];
                if ($newData[$key1]['production_plan_rest_number'] <= 0){
                    $newData[$key1]['production_plan_rest_number'] = 0;
                    $newData[$key1]['production_status'] = 5;

                }
            }else{
                $newData[$key1]['production_plan_rest_number'] = $newData[$key1]['production_plan_number'];
            }
            $newData[$key1]['production_company'] = 1;
            $newData[$key1]['staff_id'] = $this->getStaffIdByStaffName($newData[$key1]['staff_name']);
        }
        $model = M('production_plan');
        $model -> startTrans();
        foreach ($newData as $key => $value) {
            if ($model -> add($value) === false){
                $model -> rollback();
                die('false');
            }
        }
        $model -> commit();
        dump('true');
    }

    public function rukudan()
    {
        die;
        $map = [
            'id' => 'audit_order_number',
            'wlbm' => 'product_no',
            'wlmc' =>  'product_number',
            'wlxh' =>  'product_name',
            'xh' =>  'product_name',
            'lb' => 'LB',
            'rksl' => 'num',
            'rkdh' => 'action_order_number',
            'bz' => 'tips',
            'shbz' => 'audit_tips',
            'shrq' => 'update_time',
            'rkrq' => 'create_time',
            'jbr' => 'auditor_name',
            'lrr' => 'proposer_name',
            'kg' => 'putin_production_line_id'
        ];
        $model = M('stock_audit');
        $model -> startTrans();
        $oldData = M('mis_rkd') -> select();
        $newData = [];
        $staffArr = [];
        $wareArr = [];
        $productIds = [];
        foreach ($oldData as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                if (in_array($key2, ['rkrq', 'shrq', 'lrrq'])) {
                    $value2 = strtotime($value2);
                }
                if ($map[$key2] != '') {
                    $newData[$key1][$map[$key2]] = rtrim($value2);
                }
            }
            $newData[$key1]['type'] = 1;
            $newData[$key1]['audit_status'] = 2;
            $newData[$key1]['cate'] = 3;
            $newData[$key1]['cate_name'] = '生产入库';
            if (!array_key_exists($newData[$key1]['auditor_name'], $staffArr)){
                $staffArr[$newData[$key1]['auditor_name']] = $this->getStaffIdByStaffName($newData[$key1]['auditor_name']);
            }
            if (!array_key_exists($newData[$key1]['proposer_name'], $staffArr)){
                $staffArr[$newData[$key1]['proposer_name']] = $this->getStaffIdByStaffName($newData[$key1]['proposer_name']);
            }
            if (!array_key_exists($newData[$key1]['product_name'], $wareArr)){
                $wareArr[$newData[$key1]['product_name']] = $this->getWarehouseInfo($newData[$key1]['product_name']);
            }
            if (!array_key_exists($newData[$key1]['product_name'], $productIds)){
                $productIds[$newData[$key1]['product_name']] = $this->getProductIDByProductName($newData[$key1]['product_name']);
            }
            $newData[$key1]['auditor'] = $staffArr[$newData[$key1]['auditor_name']];
            $newData[$key1]['proposer'] = $staffArr[$newData[$key1]['proposer_name']];
            $newData[$key1]['product_id'] = $productIds[$newData[$key1]['product_name']];
            $newData[$key1]['warehouse_name'] = $wareArr[$newData[$key1]['product_name']]['warehouse_name'];
            $newData[$key1]['warehouse_number'] = $wareArr[$newData[$key1]['product_name']]['warehouse_number'];
            $newData[$key1]['putin_production_line_id'] = substr($newData[$key1]['putin_production_line_id'], -1, 1);
            $newData[$key1]['putin_production_line_name'] = '生产线' . $newData[$key1]['putin_production_line_id'];
            if ($newData[$key1]['audit_tips'] == '0'){
                $newData[$key1]['audit_tips'] = '';
            }
        }

        foreach ($newData as $key => $value) {
            if ($model -> add($value) === false){
                $model -> rollback();
                die('false');
            }
        }
        $model -> commit();
        dump('true');
    }

    public function chukudan()
    {
        die;
        $map = [
            'id' => 'audit_order_number',
            'wlbh' => 'product_no',
            'wlmc' =>  'product_number',
            'xh' =>  'product_name',
            'lb' => 'LB',
            'rksl' => 'num',
            'rkdh' => 'action_order_number',
            'bz' => 'tips',
            'shbz' => 'audit_tips',
            'shrq' => 'update_time',
            'ckrq' => 'create_time',
            'jbr' => 'auditor_name',
            'lrr' => 'proposer_name',
            'kg' => 'putin_production_line_id'

        ];
        $model = M('stock_audit');
        $model -> startTrans();
        $oldData = M('rkd_mis') -> select();
        $newData = [];
        $staffArr = [];
        $wareArr = [];
        $productIds = [];
        foreach ($oldData as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                if (in_array($key2, ['rkrq', 'shrq', 'lrrq'])) {
                    $value2 = strtotime($value2);
                }
                if ($map[$key2] != '') {
                    $newData[$key1][$map[$key2]] = rtrim($value2);
                }
            }
            $newData[$key1]['type'] = 1;
            $newData[$key1]['audit_status'] = 2;
            $newData[$key1]['cate'] = 3;
            $newData[$key1]['cate_name'] = '生产入库';
            if (!array_key_exists($newData[$key1]['auditor_name'], $staffArr)){
                $staffArr[$newData[$key1]['auditor_name']] = $this->getStaffIdByStaffName($newData[$key1]['auditor_name']);
            }
            if (!array_key_exists($newData[$key1]['proposer_name'], $staffArr)){
                $staffArr[$newData[$key1]['proposer_name']] = $this->getStaffIdByStaffName($newData[$key1]['proposer_name']);
            }
            if (!array_key_exists($newData[$key1]['product_name'], $wareArr)){
                $wareArr[$newData[$key1]['product_name']] = $this->getWarehouseInfo($newData[$key1]['product_name']);
            }
            if (!array_key_exists($newData[$key1]['product_name'], $productIds)){
                $productIds[$newData[$key1]['product_name']] = $this->getProductIDByProductName($newData[$key1]['product_name']);
            }
            $newData[$key1]['auditor'] = $staffArr[$newData[$key1]['auditor_name']];
            $newData[$key1]['proposer'] = $staffArr[$newData[$key1]['proposer_name']];
            $newData[$key1]['product_id'] = $productIds[$newData[$key1]['product_name']];
            $newData[$key1]['warehouse_name'] = $wareArr[$newData[$key1]['product_name']]['warehouse_name'];
            $newData[$key1]['warehouse_number'] = $wareArr[$newData[$key1]['product_name']]['warehouse_number'];
            $newData[$key1]['putin_production_line_id'] = substr($newData[$key1]['putin_production_line_id'], -1, 1);
            $newData[$key1]['putin_production_line_name'] = '生产线' . $newData[$key1]['putin_production_line_id'];
            if ($newData[$key1]['audit_tips'] == '0'){
                $newData[$key1]['audit_tips'] = '';
            }
        }

        foreach ($newData as $key => $value) {
            if ($model -> add($value) === false){
                $model -> rollback();
                die('false');
            }
        }
        $model -> commit();
        dump('true');
    }

    public function productionPlanAudit()
    {
        die;
        $map = [
//            'id' => 'id',
            'scdh' => 'production_order',
            'shlx' => 'audit_type_name',
            'sm' => 'explain',
            'bz' => 'tips',
            'shr' => 'auditor_name',
            'shrq' => 'update_time'
        ];
        $model = M('production_plan_audit');
        $model -> startTrans();
        $oldData = M('mis_scshlog') -> select();
        $newData = [];
        foreach ($oldData as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                if (in_array($key2, ['shrq'])) {
                    $value2 = strtotime($value2);
                }
                if ($map[$key2] != '') {
                    $newData[$key1][$map[$key2]] = rtrim($value2);
                }
            }
            if ($newData[$key1]['audit_type_name'] == '单据审核'){
                $newData[$key1]['audit_type'] = 2;
            }elseif ($newData[$key1]['audit_type_name'] == '产线确认'){
                $newData[$key1]['audit_type'] = 3;
            }elseif ($newData[$key1]['audit_type_name'] == '完工确认'){
                $newData[$key1]['audit_type'] = 5;
            }
            $newData[$key1]['audit_result'] = 1;
            $newData[$key1]['auditor'] = $this->getStaffIdByStaffName($newData[$key1]['auditor_name']);
        }
        $model -> startTrans();
        foreach ($newData as $key => $value) {
            if ($model -> add($value) === false){
                $model -> rollback();
                die('false');
            }
        }
        $model -> commit();
        dump('true');
    }

    public function updateProducingNumber()
    {
        $product = new IndustrialSeralScreenModel();
        $plan = new ProductionPlanModel();
        $product -> startTrans();
        $allProduct = $product -> field('product_id') -> select();
        foreach ($allProduct as $key => $value) {
            $map = ['product_id' => ['EQ', $value['product_id']]];
            $sum = $plan -> getAllProducingNumber($map);
            $res = $product -> where(['product_id' => $value['product_id']]) -> save(['production_number' => $sum]);
            if ($res === false){
                $product -> rollback();
                die('false');
            }
        }
        $product -> commit();
        dump('true');
    }

    protected function getStaffIdByStaffName($staffName)
    {
        $map = ['name' => ['EQ', $staffName]];
        $res = M('staff') -> where($map) -> getField('id');
        if ($res == null){
            $newData = M('staff') -> find();
            $newData['salt'] = '';
            $newData['loginstatus'] = 1;
            $newData['name'] = $staffName;
            $newData['username'] = $staffName;
            unset($newData['id']);
            M('staff') -> add($newData);
        }
        return $res;
    }

    protected function getWarehouseInfo($productName)
    {
        $map = [
            'product_name' => ['EQ', $productName]
        ];
        $res = M('industrial_seral_screen') -> field('warehouse_name, warehouse_number') -> where($map) -> find();
        return $res;
    }

    protected function getProductIDByProductName($productName)
    {
        $map = [
            'product_name' => ['EQ', $productName]
        ];
        $res = M('industrial_seral_screen') -> where($map) -> getField('product_id');
        if ($res == null){
            $res = 0;
        }
        return $res;
    }

    /**
     * 合同表时间转时间戳    效果不好,暂时不要用
     */
    public function staffContract()
    {die;
        $model = M('staff_contract');
        $data = $model -> select();
        $arr = ['start_time', 'end_time', 'probation_start_time', 'probation_end_time', 'update_time'];
        foreach ($data as $key1 => &$value1) {
            foreach ($value1 as $key2 => &$value2) {
                if (in_array($key2, $arr)){
                    $value2 = strtotime($value2);
                }
            }
            $model -> save($value1);
        }
        echo 1;
    }


    public function changePosi()
    {
//        $arr = [1,2,4,3,5,8,10,11];
//        $a = [2,121,34,11,8];
//        $b = array_intersect($a,$arr);
//        dump($b);
//        dump(date("Y-m-d H:i:s",time()));
        phpinfo();
    }


}
