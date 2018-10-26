<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/16
 * Time: 11:45
 */
namespace Dwin\Model;
use Dwin\Controller\ProductionController;
use Think\Model;
class ProductionPlanModel extends Model
{
//    protected $_map = [
//        'beihuofangshi' => 'stock_cate',
//        'chanpinid' => 'product_id',
//        'shengchanshuliang' => 'production_plan_number',
//        'shengchandi' => 'production_company',
//        'shengchanxian' => 'production_line',
//        'jiaohuoriqi' => 'delivery_time',
//        'beizhu' => 'tips',
//        'weidachengshuoming' => 'fail_explain'
//    ];
    const ORDER_WAIT = 1;
    const STOCK_WAIT = 2;
    const PRODUCTION_WAIT  = 4;
    const PRODUCTION_DOING = 3;
    const PRODUCTION_DONE  = 5;
    const IS_PRODUCTION = "1,2,3,4";

    /**生产计划助理是否下推到生产任务**/
    const SCHEDULED_STATUS_PENDING = "0,1";
    const SCHEDULED_STATUS_DONE = "2";
    public static $processMap = [
        self::SCHEDULED_STATUS_PENDING => '排产中',
        self::SCHEDULED_STATUS_DONE    => '排产完毕'
    ];
    protected $_validate = [
        ['stock_cate','require','请选择备货方式'],
        ['product_id','require','请选择生产的产品'],
        ['product_number','require','请输入生产的产品的数量'],
        ['production_place','require','请选择生产地'],
        ['production_line','require','请选择生产线'],
    ];


    public function getCompleteProductionData($map, $start, $length, $order='complete_time desc')
    {
        $field = "plan.id,
            production_order,
            staff_name,
            stock_cate_name,
            plan.product_name,
            production_line_name,
            production_plan_number,
            plan.production_number,
            from_unixtime(create_time,'%y-%m-%d') create_time,
            from_unixtime(delivery_time,'%y-%m-%d') delivery_time,
            from_unixtime(complete_time) complete_time,
            production_status,
            ifnull(plan.tips,'无') tips";
        $map['production_status'] = 5;
        $map['complete_time'] = ['gt', strtotime('2017-05-01')];
        $data = $this->alias('plan')
            ->field($field)
            ->where($map)
            ->order($order)
            ->limit($start, $length)
            ->select();
        return $data;
    }

    public function countCompletedNum($map)
    {
        $map['production_status'] = 5;
        $map['complete_time'] = ['gt', strtotime('2017-05-01')];
        $data = $this->alias('plan')
            ->join('LEFT JOIN crm_industrial_seral_screen as product on plan.product_id = product.product_id')
            ->where($map)
            ->count('plan.id');
        return $data;
    }

    /**
     * 获取生产任务单信息（连库存表查剩余库存和在生产数量）
    */
    public function getProductionDataWithStock($map, $start, $length, $order='create_time asc')
    {
        $field = "plan.id,
                  production_order,
                  plan.product_id,
                  staff_name,
                  stock_cate_name,
                  plan.product_name,
                  production_line_name,
                  production_plan_number,
                  plan.production_number,
                  from_unixtime(create_time,'%y-%m-%d') create_time,
                  from_unixtime(delivery_time,'%y-%m-%d') delivery_time,
                  production_status,ifnull(plan.tips,'无') tips,
                  delivery_time yqts,
                  sum(stock.stock_number) stock_number,
                  sum(stock.o_audit) o_audit,
                  product.production_number product_num, 
                  product.production_number production_plan_rest_num";
        $data = $this->alias('plan')
            ->field($field)
            ->join("LEFT JOIN crm_stock as stock on plan.product_id = stock.product_id")
            ->join("LEFT JOIN crm_material as product on plan.product_id = product.product_id")
            ->where($map)
            ->order($order)
            ->group('plan.id,plan.product_id')
            ->limit($start, $length)
            ->select();
        return $data;
    }


    public function index($map, $start = 0, $length = 10, $order = 'create_time desc')
    {
        $field = "plan.*, company.production_company";
        return $this
             -> alias('plan')
             -> field($field)
             -> join('left join crm_production_company as company on plan.production_company = company.id')
             -> where($map)
             -> order($order)
             -> limit($start, $length)
             -> select();
    }

    public function indexCount($map)
    {
        return $this
            -> alias('plan')
            -> field('plan.*, product.product_name, staff.name staff_name, company.production_company, cate.stock_cate_name')
            -> join('left join crm_industrial_seral_screen as product on plan.product_id = product.product_id')
            -> join('left join crm_staff as staff on plan.staff_id = staff.id')
            -> join('left join crm_production_company as company on plan.production_company = company.id')
            -> join('left join crm_stock_cate as cate on plan.stock_cate = cate.id')
            -> where($map)
            -> count();
    }

    /**
     * 获取订单的所有详细信息
     * @param $map  array 搜索条件
     * @param $field string 字段
     * @return array
     */
    public function getPlanInfo($map,$field)
    {
        $data = $this
             -> alias('plan')
             -> field($field)
             -> join('left join crm_staff as proposerTable on plan.staff_id = proposerTable.id')
             -> join('left join crm_staff as auditorTable on plan.staff_id = auditorTable.id')
             -> join('left join crm_stock_cate as cate on plan.stock_cate = cate.id')
             -> join('left join crm_industrial_seral_screen as product on plan.product_id = product.product_id')
             -> join('left join crm_production_company as company on plan.production_company = company.id')
             -> join('left join crm_production_line as line on plan.production_line = line.id')
             -> where($map)
             -> find();
        return $data;
    }
    /**
     * 新增生产计划
     * @return bool
     */
    public function addProductionPlan()
    {
        if ($this->create() !== false){
            $this->production_status = 1;
            $this->staff_id          = session('staffId');
            $this->staff_name        = session('nickname');
            $this->production_line_name = M('production_line') -> find($this->production_line)['production_line'];
            $this->stock_cate_name   = M('stock_cate') -> find($this->stock_cate)['stock_cate_name'];
            $productInfo             = M('material') -> field('product_name, product_number') -> find($this->product_id);
            $this->product_name      = $productInfo['product_name'];
            $this->product_number    = $productInfo['product_number'];
            $this->create_time       = time();
            $this->production_plan_rest_number = $this->production_plan_number;
            $this->delivery_time     = strtotime('+1 day', strtotime($this->delivery_time)) - 1;
            if ($this->create_time > $this->delivery_time){
                $this->error = '交货日期不合法';
                return false;
            }
            if ($this->add() !== false){
                return true;
            }else{
                $this->error = '添加失败';
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 添加多个生产计划
     * @param $params   array   多个生产计划
     * @return bool
     */
    public function addProductionPlanMulti($params)
    {
        $this->startTrans();
        foreach ($params as $key => $value) {
            if ($this->create($value) !== false){
                $this->production_status = 1;
                $this->staff_id = session('staffId');
                $this->staff_name = session('nickname');
                $this->production_line_name = M('production_line') -> find($this->production_line)['production_line'];
                $this->stock_cate_name = M('stock_cate') -> find($this->stock_cate)['stock_cate_name'];
                $productInfo = M('material') -> field('product_name, product_number') -> find($this->product_id);
                $this->product_name = $productInfo['product_name'];
                $this->product_number = $productInfo['product_number'];
                $this->create_time = time();
                $this->create_time = time();
                $this->production_plan_rest_number = $this->production_plan_number;
                $this->delivery_time =  strtotime('+1 day', strtotime($this->delivery_time)) - 1;
                if ($this->create_time > $this->delivery_time){
                    $this->error = '交货日期不合法';
                    $this->rollback();
                    return false;
                }
                $this->order_id = $value['order_id'];
                if ($this->add() === false){
                    $this->rollback();
                    $this->error = '添加第'.($key+1).'项失败';
                    return false;
                }
            }else{
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }

    /**
     * 从销货单添加生产计划
     * @param $data     array   新增内容
     * @param $orderID  int     订单id
     * @return bool
     */
    public function addProductionPlanFromOrder($data, $orderID)
    {
        $orderFormModel = new OrderformModel();
        $orderProductModel = new OrderproductModel();
        $this->startTrans();
        $productionPlanUpdate = $this->addProductionPlanMulti($data);
        // 将order form表中的状态改为待生产或正在生产
        $orderFormUpdate = $orderFormModel -> where(['id' => ['EQ', $orderID]]) -> save(['production_status' => 2]);
        // 将order product表中的状态改为待生产
        $orderProductUpdate = $orderProductModel -> editStatusWhenAddProductionPlan($data, $orderID, 2);
        if ($productionPlanUpdate === false){
            $this->rollback();
            return false;
        }
        if ($orderFormUpdate === true && $orderProductUpdate === false){
            $this->rollback();
            $this->error = '订单状态更新失败';
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 查找某型号产品所有的正在生产(production_plan_rest_number)的数量
     * @param $map  array   查找条件
     * @return int  该产品正在生产数量
     */
    public function getAllProducingNumber($map)
    {
        $map['production_status'] = ['EQ', 3];
        $map['production_line']   = ['NEQ', 2];
        $map['is_del']            = ['EQ', 0];
        // 某型号生产总数
        $sum = $this
             -> where($map)
             -> sum('production_plan_rest_number');
        return $sum = $sum == null ? 0 : $sum;
    }

    /**
     * 查找某型号所有处于生产阶段的总数量
     * @param $map
     * @return mixed
     */
    public function getAllProductionNumber($map)
    {
        $map['production_status'] = ['in', [1,2,3,4]];
        $map['is_del'] = ['EQ', 0];
        // 某型号生产总数
        $sum =  $this
            -> where($map)
            -> sum('production_plan_number');
        if ($sum == null){
            $sum = 0;
        }
        return $sum;
    }

    /**
     * 修改生产计划
     * @param int $id
     * @param array $data     修改后的新数据
     * @return bool
     */
    public function update($id,$data)
    {
        foreach ($data as $key => $value) {
            if (empty($value)){
                unset($data[$key]);
            }
        }
        $recordModel = new ProductionPlanRecordModel();
        $recordUpdate = $recordModel -> editProductionPlanRecord($id, $data);
        $data['delivery_time'] = strtotime('+23 hours', strtotime($data['delivery_time']));
        $thisUpdate = null;
        $plan = $this->find($id);
        if ($plan['create_time'] >= $data['delivery_time']){
            $this->error = '交货日期不合法';
            $thisUpdate = false;
        }
        $res = $this->where(['id' => $id]) -> save($data);
        if ($res !== false){
            $thisUpdate =  true;
        }else{
            $this->error = '更新失败';
        }
        return $recordUpdate && $thisUpdate;

    }

    /**
     * 根据生产计划订单获取已入库的总数
     * @param $action_order_number
     * @return int
     */
    public function getProductionInputNumber($action_order_number)
    {
        $model = new StockAuditModel();
        $res = $model
            -> where(['type' => '1', 'audit_status' => '2', 'action_order_number' => $action_order_number, '_logic' => 'AND'])
            -> sum('num');
        return $res;
    }

    /**
     * 提交生产入库对计划的更新
     * @param $order_number string 生产计划订单号
     * @param $num
     * @return bool $
     */
    public function addAudit($order_number, $num)
    {
        $auditModel = new StockAuditModel();
        $plan = $this->where(['production_order' => $order_number]) -> find();
        $production_i_audit_number = $auditModel->getAuditNumber(StockAuditModel::IN_TYPE, $plan['product_id'], $order_number) + $num;
        $production_number = $auditModel->getAuditNumber(StockAuditModel::IN_TYPE, $plan['product_id'], $order_number ,StockAuditModel::AUDIT_PASS);
        $production_plan_rest_number = $this -> where(['production_order' => $order_number]) -> getField('production_plan_number') - $production_i_audit_number - $production_number;
        $data = [
            'production_i_audit_number' => $production_i_audit_number,
            'production_plan_rest_number' => $production_plan_rest_number,
        ];
        return $this -> where(['production_order' => $order_number]) -> save($data);
    }

    /**
     * 生产入库通过
     * @param array $audit    申请信息
     * @return bool
     */
    public function auditPass($audit)
    {
        $orderProductModel = new OrderproductModel();
        $orderFormModel = new OrderformModel();
        $auditModel = new StockAuditModel();

        $plan = $this->where(['production_order' => ['EQ',$audit['action_order_number']]]) -> find();
        $production_number = $auditModel -> getAuditNumber('1', $audit['product_id'], $audit['action_order_number'], 2);
        $data = [
            'production_number' => $production_number,
            'production_plan_rest_number' => $plan['production_plan_number'] - $production_number
        ];
        $orderProductUpdate = true;
        $orderFormUpdate = true;
        // 判断此生产计划是否有销货单
        if ($plan['order_id'] != ''){
            $map = [
                'product_id' => ['EQ', $plan['product_id']],
                'order_id' => ['EQ', $plan['order_id']],
                'status' => ['EQ', 2],

            ];
            // 得到已生产总数
            $oldOrderProduct = $orderProductModel -> where($map) -> select();
            $orderProduct = $oldOrderProduct;
            $currentNumber = (int) $audit['num'];
            if($currentNumber < 0 ){
                return false;
            }
            while ($currentNumber > 0){
                foreach ($orderProduct as $key => &$value) {
                    if ($value['status'] == '1'){
                        continue;
                    }
                    $value['rest_number'] = $value['product_num'] - $value['produced_number'];
                    if ($value['rest_number'] == $currentNumber){
                        $value['produced_number'] = $value['product_num'];
                        $value['status'] = '1';
                        $currentNumber = 0;
                        break;
                    }elseif($value['rest_number'] > $currentNumber){
                        $value['produced_number'] += $currentNumber;
                        $currentNumber = 0;
                        break;
                    }else{
                        $value['produced_number'] = $value['product_num'];
                        $value['status'] = '1';
                        $currentNumber -= $value['rest_number'];
                    }
                }
                $allProductComplete = true;
                foreach ($orderProduct as $key => $value12312) {
                    if ($value12312['status'] != 1){
                        $allProductComplete = false;
                        break;
                    }
                }
                if ($allProductComplete){
                    $currentNumber = 0;
                }
            }

            foreach ($orderProduct as $key => $value2) {
                $res = $orderProductModel -> save($value2);
                if ($res === false){
                    $orderProductUpdate = false;
                    break;
                }
            }

            // 检查本订单的所有产品是否生产完成
            $orderProductList = $orderProductModel -> where(['order_id' => ['EQ', $plan['order_id']]]) -> select();
            $allComplete = true;
            foreach ($orderProductList as $key => $value) {
                if ($value['status'] != 1){
                    $allComplete = false;
                    break;
                }
            }
            $orderFormUpdateData = ['production_status' => '2'];
            if ($allComplete){
                $orderFormUpdateData['production_status'] = 4;
            }
            $orderFormUpdate = $orderFormModel -> where(['id' => ['EQ', $plan['order_id']]]) -> save($orderFormUpdateData);
        }
        if ($production_number == $plan['production_plan_number']) {
            $data['production_status'] = 5;
            $data['complete_time'] = time();
        }
        $productPlanUpdate = $this->where(['production_order' => ['EQ', $audit['action_order_number']]]) -> save($data);

        // 判断今天是否有统计
        $currentDate = strtotime('-1 day', strtotime(date('Y-m-d')));
        $lastTime = M('putin_production_line_statistics') -> max('date');
        $statisticsUpdate = true;
        if ($currentDate > $lastTime){
            while ($currentDate != $lastTime){
                $lastTime = strtotime('+1 day', $lastTime);
                $map = [
                    'update_time' => ['BETWEEN', [$lastTime, strtotime('+1 day', $lastTime)]],
                    'audit_status' => ['EQ', '2'],
                    'type' => ['EQ', '1'],
                    'is_del' => ['EQ', 0],
                ];
                $row = M('stock_audit') -> field('sum(num) sum, putin_production_line_id') -> where($map) -> group('putin_production_line_id') -> select();
                $data = [];
                foreach ($row as $key => $value) {
                    $line = 'line' . $value['putin_production_line_id'];
                    $data[$line] = $value['sum'];
                }
                $data['date'] = $lastTime;
                $data['month'] = date('m', $lastTime);
                $data['year']  = date('Y', $lastTime);
                $statisticsUpdate = M('putin_production_line_statistics') -> add($data);
            }
        }

        if ($productPlanUpdate !== false && $orderProductUpdate !== false && $orderFormUpdate !== false && $statisticsUpdate !== false){
            return true;
        }else{
            if ($productPlanUpdate === false){
                $this->error = '生产计划修改失败';
            }else{
                $this->error = '销货单信息修改失败';
            }
            if ($statisticsUpdate === false){
                $this->error = '生产统计更新失败';
            }
            return false;
        }


    }

    /**
     * 回滚未审核的入库请求
     * @param $audit    申请信息
     * @return bool
     */
    public function deleteAudit($audit)
    {
        $plan = $this->where(['production_order' => ['EQ',$audit['action_order_number']]]) -> find();
        $planSaveData = [
            'production_plan_rest_number' => $plan['production_plan_rest_number'] + $audit['num'],
            'production_i_audit_number' => $plan['production_i_audit_number'] - $audit['num'],
            'production_status' => 3,
            'complete_time' => null,
        ];
        return $this->where(['production_order' => ['EQ', $audit['action_order_number']]]) -> save($planSaveData) === false ? false : true;
    }

    /**
     * 回滚已通过的入库请求
     * @param $audit    申请信息
     * @return bool
     */
    public function auditPassRollback($audit)
    {
        $orderProductModel = new OrderproductModel();
        $orderFormModel = new OrderformModel();

        $plan = $this->where(['production_order' => ['EQ',$audit['action_order_number']]]) -> find();
        $production_number = $plan['production_number'] - $audit['num'];
        $planSaveData = [
            'production_number' => $production_number,
            'production_plan_rest_number' => $plan['production_plan_rest_number'] + $audit['num'],
        ];
        $orderProductUpdate = true;
        $orderFormUpdate = true;
        // 判断是否有销货单的回滚
        if ($plan['order_id'] != ''){
            $map = [
                'product_id' => ['EQ', $plan['product_id']],
                'order_id' => ['EQ', $plan['order_id']],
            ];
            // 得到已生产总数
            $oldOrderProduct = $orderProductModel -> where($map) -> select();
            $orderProduct = $oldOrderProduct;
            $currentNumber = (int) $audit['num'];
            if($currentNumber < 0 ){
                return false;
            }
            while ($currentNumber > 0){
                foreach ($orderProduct as $key => &$value) {
                    if ($value['produced_number'] == $currentNumber){
                        $value['produced_number'] = 0;
                        $value['status'] = 2;
                        $currentNumber = 0;
                        break;
                    }elseif($value['produced_number'] > $currentNumber){
                        $value['produced_number'] -= $currentNumber;
                        $value['status'] = 2;
                        $currentNumber = 0;
                        break;
                    }else{
                        $currentNumber -= $value['produced_number'];
                        $value['produced_number'] = 0;
                        $value['status'] = 2;
                    }
                }
                $allProductEmpty = true;
                foreach ($orderProduct as $key => $value12312) {
                    if ($value12312['produced_number'] != 0){
                        $allProductEmpty = false;
                        break;
                    }
                }
                if ($allProductEmpty){
                    $currentNumber = 0;
                }
            }
            foreach ($orderProduct as $key => $value2) {
                $res = $orderProductModel -> save($orderProduct[$key]);
                if ($res === false){
                    $orderProductUpdate = false;
                    break;
                }
            }

            $orderProductList = $orderProductModel -> where(['order_id' => ['EQ', $plan['order_id']]]) -> select();
            $isPendingStatus = true;
            foreach ($orderProductList as $key => $value) {
                if ($value['status'] != 2){
                    $isPendingStatus = false;
                    break;
                }
            }
            $orderFormUpdateData = ['production_status' => 2];
            if ($isPendingStatus){
                $orderFormUpdateData['production_status'] = 0;
            }
            $orderFormUpdate = $orderFormModel -> where(['id' => ['EQ', $plan['order_id']]]) -> save($orderFormUpdateData) === false ? false : true;
        }

        $planSaveData['production_status'] = 3;
        $planSaveData['complete_time'] = null;

        $productPlanUpdate = $this->where(['production_order' => ['EQ', $audit['action_order_number']]]) -> save($planSaveData) === false ? false : true;

        //统计数据的回滚
        $date = date('Y-m-d', $audit['update_time']);
        $field = 'line' . $audit['putin_production_line_id'];
        $map = [
            'date' => ['EQ', $date]
        ];
        $hasRecord = M('putin_production_line_statistics') -> where($map) -> count();
        if ($hasRecord){
            $statisticsSaveData = [
                $field => M('putin_production_line_statistics') -> where($map) -> find()[$field] - $audit['num']
            ];
            $statisticsUpdate = M('putin_production_line_statistics') -> where($map) -> save($statisticsSaveData) === false ? false : true;
        }else{
            $statisticsUpdate = true;
        }

        if ($productPlanUpdate === true && $orderProductUpdate === true && $orderFormUpdate === true && $statisticsUpdate === true){
            return true;
        }else{
            if ($statisticsUpdate === false){
                $this->error = '生产统计更新失败';
            }
            if ($productPlanUpdate === false){
                $this->error = '生产计划更新失败';
            }
            if ($orderProductUpdate === false){
                $this->error = '订单产品数量更新失败';
            }
            if ($orderFormUpdate === false){
                $this->error = '订单更新失败';
            }
            return false;
        }
    }

    /**
     * 生产入库不通过
     * @param $audit array  生产入库申请内容
     * @return bool
     */
    public function auditFail($audit)
    {
        $auditModel = new StockAuditModel();
        $plan = $this->where(['production_order' => $audit['action_order_number']]) -> find();
        $production_i_audit_number = $plan['production_i_audit_number'] - $audit['num'];
        $production_number = $auditModel -> getAuditNumber('1', $audit['product_id'], $audit['action_order_number'], 2);
        $data = [
            'production_i_audit_number' => $production_i_audit_number,
            'production_number' => $production_number,
            'production_plan_rest_number' => $plan['production_plan_number'] - $production_number - $production_i_audit_number
        ];
        return $this->where(['production_order' => $audit['action_order_number']]) -> save($data) === false ? false : true;
    }

    /**
     * 删除有销货单的生产计划
     * @param   int     生产计划id
     */
    public function delOrderPlan($id)
    {
        $plan = $this->find($id);
        $orderFormModel = new OrderformModel();
        $orderProductModel = new OrderproductModel();
        $this -> save(['id' => $id, 'is_del' => 1]);
        $count = $this->where(['order_id' => ['EQ', $plan['order_id']]]) -> count();
        if ($count === 0){
            $orderFormModel -> save(['id' => $plan['order_id'], 'production_status' => 0]);
            $orderProductModel -> where(['order_id' => ['EQ', $plan['order_id']]]) -> save(['status' => 0]);
        }
    }
    /**
     * 删除有销货单的生产计划
     * @param   int     $id 生产计划id
     * @return bool 删除结果是否成功
     *
     */
    public function delProductionPlanTrans($id)
    {
        $this->startTrans();
        $info = $this->find($id);
        if ($info['order_id']) {
            $orderFormModel = new OrderformModel();
            $orderProductModel = new OrderproductModel();
            $orderSaveRst = $orderFormModel -> save(['id' => $info['order_id'], 'production_status' => 0]);
            if ($orderSaveRst === false) {
                $this->rollback();
                $this->error = "销货单状态修改出错";
                return false;
            }
            $orderProductRst = $orderProductModel -> where(['order_id' => ['EQ', $info['order_id']]]) -> save(['status' => 0]);
            if ($orderProductRst === false) {
                $this->rollback();
                $this->error = "销货单产品修改出错";
                return false;
            }
        }
        $delRst = $this->save(['id' => $id, 'is_del' => 1]);
        if ($delRst === false) {
            $this->rollback();
            $this->error = "生产单据删除失败";
            return false;
        }

        $productionOrderModel = new ProductionOrderModel();
        $productionOrderData = $productionOrderModel->getRelationOrderData($id);
        $orderIds = getPrjIds($productionOrderData,'id');
        $produceOrderDel = $productionOrderModel->where(['id' => ['in', $orderIds]])->save(['is_del' => ProductionOrderModel::$isDel]);
        if ($produceOrderDel === false) {
            $this->rollback();
            $this->error = "生产计划单据删除失败";
            return false;
        }
        $this->commit();
        return true;

    }

    /**
     * 可修改生产中的生产计划
     * 生产计划修改
     * @param array $data 新数据
     * @param  array $productionPlan   旧数据
     * @return bool
     */
    public function editPlan1($data, $productionPlan)
    {
        $productionRelationModel = new ProductionRelationModel();
        $orderIds = $productionRelationModel->getPlanIdsWithPlanId($productionPlan['id']);
        if ($orderIds['status'] == ProductionRelationModel::$successStatus) {
            $this->error = "该生产单据已经下推了生产计划，如要删除，请先回退对应的生产领料单并删除对应的生产计划";
            return false;
        }

        if ($data['production_plan_number'] < $productionPlan['production_number']){
            $this->error = '生产数量不得大于已出库数量';
            return false;
        }
        $data['production_plan_rest_number'] = ($data['production_plan_number'] - $productionPlan['production_plan_number']) + $productionPlan['production_plan_rest_number'];
        if ($data['production_plan_rest_number'] <= 0){
            $data['production_plan_rest_number'] = 0;
            $data['production_status'] = self::PRODUCTION_DONE;
        }
        $orderProductUpdate = true;
        if ($productionPlan['order_id'] != '') {
            $orderModel = new OrderformModel();
            $orderProductUpdate = $orderModel -> editOrderProduct($productionPlan, array_merge($productionPlan, $data));
            $this->error = $orderModel -> getError();
        }
        $thisUpdate =  $this->update($productionPlan['id'], $data) === false ? false : true;
        return $thisUpdate && $orderProductUpdate;
    }

    /**
     * 不可修改生产中的生产计划
     * 生产计划修改
     * @param $data     新数据
     * @param $productionPlan   旧数据
     * @return bool
     */
    public function editPlan2($data, $productionPlan)
    {
        $data['production_plan_rest_number'] = ($data['production_plan_number'] - $productionPlan['production_plan_number']) + $productionPlan['production_plan_rest_number'];
        if ($data['production_plan_rest_number'] <= 0){
            $data['production_plan_rest_number'] = 0;
            $data['production_status'] = self::PRODUCTION_DONE;
        }
        if($productionPlan['production_status'] == self::PRODUCTION_DOING){
            $this->error = '正在生产中, 不可更改';
            return false;
        }
        $this -> startTrans();
        $orderProductUpdate = true;
        if ($data['product_id'] != $productionPlan['product_id'] && $productionPlan['production_status'] != self::PRODUCTION_DOING){
            $productModel = new MaterialModel();
            $product = $productModel -> field('product_id, product_name, product_number') -> find($data['product_id']);
            $data = array_merge($data, $product);
            if ($productionPlan['order_id'] != '') {
                $orderModel = new OrderformModel();
                $orderProductUpdate = $orderModel -> editOrderProduct($productionPlan, array_merge($productionPlan, $data));
                $this->error = $orderModel -> getError();
            }
        }

        $recordModel = new ProductionPlanRecordModel();
        $planUpdate = $this -> update($productionPlan['id'], $data);
        $recordUpdate = $recordModel -> editProductionPlanRecord($productionPlan['id'], $data);
        $productModel = new MaterialModel();

        $productUpdate = $productModel -> updateProducingNumber($productionPlan['product_id'], 0);
        $res = $planUpdate && $productUpdate && $recordUpdate && $orderProductUpdate;
        if ($res) {
            $this -> commit();
            return true;
        } else {
            $this -> rollback();
            return false;
        }
    }

    public function getProductingIdWithOrderId($cpoOrderId)
    {
        $map['order_id'] = ['EQ', $cpoOrderId];
        $map['is_del']  = ['EQ', 0];
        $data = $this->where($map)->field('product_id')->select();
        return $idArr = explode(',', getPrjIds($data,'product_id'));
    }


    /**
     * @abstract 获取还在合并、拆分订单状态，生成生产计划的生产订单。
     * @return array 返回待生成生产计划的生产订单
     * @todo 基于这些订单，以型号为单位，组成预计的生产计划 生产计划的起止时间问题需要自动生成并解决。起止时间问题解决后，需要面临数量分配问题（尽量不差分，合并）=> 后续会拆分生产。
     *
    */
    public function getProcessingProduction($condition,$map = [])
    {

        $map['plan.is_del'] = ['EQ', 0];
        $map['plan.production_status'] = ['EQ', self::PRODUCTION_DOING];
//        $map['plan.scheduled_status'] = ['IN', self::SCHEDULED_STATUS_PENDING];
        $start = $condition['start'];
        $length = $condition['length'];
        $order  = $condition['order'];
        $field = "plan.id,
                  plan.production_order,
                  plan.staff_name,
                  plan.stock_cate,
                  plan.stock_cate_name,
                  plan.product_id,
                  product.product_no,
                  plan.product_name,
                  plan.production_line_name,
                  plan.production_plan_number,
                  plan.production_plan_rest_number,
                  plan.production_number,
                  plan.production_line,
                  plan.production_number,
                  from_unixtime(plan.create_time,'%y-%m-%d') create_time,
                  from_unixtime(plan.delivery_time,'%y-%m-%d') delivery_time,
                  plan.production_status,
                  ifnull(plan.tips,'无') tips,
                  plan.delivery_time yqts,
                  ifnull(plan_total_number,0),
                  if(ifnull(plan_total_number,0)>production_plan_number,production_plan_number,ifnull(plan_total_number,0)) actual_number,
                  production_plan_number - if(ifnull(plan_total_number,0)>production_plan_number,production_plan_number,ifnull(plan_total_number,0)) rest_num,
                  plan.scheduled_status";

        $count = $this->alias('plan')->where($map)->count();
        $filterCount = $count;
        if (trim($condition['search'])) {
            $map['plan.production_order|plan.staff_name|plan.stock_cate_name|plan.product_name|plan.production_line_name|plan.tips'] = ['LIKE', "%" . trim($condition['search'] . "%")];
            $filterCount = $this->alias('plan')->where($map)->count();
        }
        $data = $this->alias('plan')
            ->field($field)
            ->join("LEFT JOIN crm_stock as stock on plan.product_id = stock.product_id")
            ->join("LEFT JOIN crm_material as product on plan.product_id = product.product_id")
            ->join("LEFT JOIN 
                    (select relation.production_plan_id,relation.production_order_id,sum(ifnull(orde.plan_number,0)) plan_total_number
                        FROM crm_production_relation relation LEFT JOIN crm_production_order orde ON orde.id = relation.production_order_id and orde.is_del = 0 group by relation.production_plan_id) rel ON rel.production_plan_id = plan.id")
            ->where($map)
            ->order($order)
            ->group('plan.id')
            ->limit($start, $length)
            ->select();

        $bomModel = new MaterialBomModel();
        foreach($data as &$datum) {
            $datum['bom'] = $bomModel->getBomDataWithProductId($datum['product_id']);
        }
        return [$count, $filterCount, $data];
    }

    public function getProcessingProductionNum()
    {
        $map['is_del'] = ['EQ', 0];
        $map['production_status'] = ['EQ', self::PRODUCTION_DOING];
        $map['scheduled_status'] = ['IN', '0,1'];
        return $this->where($map)->count();
    }

    public function getProcessingPlanData($start, $length, $order, $map, $field)
    {
        $data = $this->alias('plan')
            ->field($field)
            ->join("LEFT JOIN crm_stock as stock on plan.product_id = stock.product_id")
            ->join("LEFT JOIN crm_material as product on plan.product_id = product.product_id")
            ->join("LEFT JOIN crm_production_relation rel ON rel.production_plan_id = plan.id")
            ->join("LEFT JOIN crm_production_order ord ON ord.id = rel.production_order_id and ord.is_del = 0")
            ->where($map)
            ->order($order)
            ->group('plan.id')
            ->limit($start, $length)
            ->select();
        return $data;
    }

    /*
     * 业务、生产下的生产单还未处理或处理中的单据根据产线、型号分组合并待下生产计划数量*/
    public function getPreProductionOrder($config)
    {
        $map['is_del'] = ['EQ', 0];
        $map['production_status'] = ['EQ', self::PRODUCTION_DOING];
        $map['_string'] = 'actual_number < production_plan_number';
        $start  = $config['start'];
        $length = $config['length'];
        $order  = $config['order'];
        $field = "
                a.product_id,
                b.product_no,
                b.product_name,
                sum(a.production_plan_number - a.actual_number) plan_number,
                from_unixtime(min(a.create_time),'%y-%m-%d') plan_start_time,
                from_unixtime(max(a.delivery_time),'%y-%m-%d') plan_end_time,
                a.production_line_name production_line_name,
                a.production_line_name production_line,
                group_concat(a.id) plan_pid,
                group_concat(a.production_order) production_order";

        return $this->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_material b ON a.product_id = b.product_id')
            ->where($map)->group('product_id, production_line')->limit($start, $length)->order($order)->select();
    }

    /**
     * 计数（按照型号、产线）*/
    public function getPreOrderNum()
    {
        $map['is_del'] = ['EQ', 0];
        $map['production_status'] = ['EQ', self::PRODUCTION_DOING];
        $map['_string'] = 'actual_number < production_plan_number';
        return $k = $this->field('product_id')->where($map)->count('distinct product_id');
    }

    /**
     * 根据生产单id获取默认要更新的数量
    */
    public function getPreUpdateData($planIds)
    {
        $map['is_del'] = ['EQ', 0];
        $map['production_status'] = ['EQ', self::PRODUCTION_DOING];
        $map['_string'] = 'actual_number < production_plan_number';
        $map['a.id'] = ['IN', $planIds];
        $field = 'a.id,a.product_id,a.product_name,b.product_no,a.production_plan_number - a.actual_number num,production_order';
        return $this->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_material b ON a.product_id = b.product_id')
            ->where($map)
            ->select();
    }

    public function updateNumWithProductionOrder($updateId, $num, $flag)
    {
        $filter['a.id'] = ['EQ', $updateId];
        $data = $this->alias('a')->where($filter)
            ->field('a.production_plan_number - sum(ifnull(ord.plan_number,0)) can_update_num,a.production_order')
            ->join("LEFT JOIN crm_production_relation rel ON rel.production_plan_id = a.id")
            ->join("LEFT JOIN crm_production_order ord ON ord.id = rel.production_order_id and ord.is_del = 0")
            ->group('a.id')
            ->select()[0];
        if ($data['can_update_num'] < $num) {
            $this->error = "有问题，生产单号" . $data['production_order'] . "可生成生产计划数量不合法1";
            return false;
        }
        switch ($flag) {
            case "addProductionOrder" :
                $updateData['actual_number'] = array('exp', "actual_number + {$num}");
                $updateData['scheduled_status'] = $data['can_update_num'] == $num ? 2 : 1;
                break;
            default :
                break;
        }
        if (empty($updateData)) {
            $this->error = "有问题，生产单号" . $data['production_order'] . "要更新的数据为空";
            return false;
        } else {
            return $this->alias('a')->where($filter)->setField($updateData) === false ? false : true;
        }

    }


    public function getPlanInfoWithOrderId($orderId)
    {
        $productionRelationModel = new ProductionRelationModel();
        $planIdData = $productionRelationModel->getPlanIdsWithOrderId($orderId);
        if ($planIdData['status'] !== 200) {
            return [];
        } else {
            $map['plan.id'] = ['IN', $planIdData['data']];
            $map['plan.is_del'] = ['eq', 0];
            $field = "plan.id,
                  plan.production_order,
                  plan.staff_name,
                  plan.stock_cate,
                  plan.stock_cate_name,
                  plan.product_id,
                  product.product_no,
                  plan.product_name,
                  plan.production_line_name,
                  plan.production_plan_number,
                  plan.production_line,
                  plan.production_number,
                  from_unixtime(plan.create_time,'%y-%m-%d') create_time,
                  from_unixtime(plan.delivery_time,'%y-%m-%d') delivery_time,
                  plan.production_status,
                  ifnull(plan.tips,'无') tips,
                  plan.delivery_time yqts,
                  if(sum(ifnull(ord.plan_number,0))>production_plan_number,production_plan_number,sum(ifnull(ord.plan_number,0))) actual_number,
                  production_plan_number - if(sum(ifnull(ord.plan_number,0))>production_plan_number,production_plan_number,sum(ifnull(ord.plan_number,0))) rest_num,
                  plan.scheduled_status";
            return $data = $this->getProcessingPlanData(0,100, 'plan.id desc',$map, $field);

        }

    }

    /**
     * 自动写入入库记录时，获取对应planIds的可入库数量。与入库数据整合，生成入库记录数据。
    */
    public function getPlanDataWithPlanIds($planIds)
    {
        $map['plan.id'] = ['IN', $planIds];
        $field = "plan.id,
                plan.product_id,
                production_order,
                plan.create_time,
                production_plan_number,
                sum(ifnull(in_record.num,0)) num, 
                production_plan_number - sum(ifnull(in_record.num,0)) can_insert_num
                ";
        return  $this->alias('plan')
            ->field($field)
            ->where($map)
            ->join('LEFT JOIN crm_stock_audit in_record ON in_record.action_order_number = plan.production_order')
            ->group('plan.id')
            ->order('plan.create_time asc')
            ->select();
    }

    /**
     * 根据入库单入库数量维护生产状态
    */
    public function resetPlanProductionStatus($planIds)
    {
        $map['plan.id'] = ['IN', $planIds];
        $field = "plan.id,
                plan.product_id,
                production_order,
                plan.create_time,
                production_plan_number,
                sum(ifnull(in_record.num,0)) num, 
                production_plan_number - sum(ifnull(in_record.num,0)) can_insert_num
                ";
        $planRst = $this
            ->alias('plan')
            ->field($field)
            ->where($map)
            ->join('LEFT JOIN crm_stock_audit in_record ON in_record.action_order_number = plan.production_order and in_record.is_del = 0')
            ->group('plan.id')
            ->select();
        foreach ($planRst as $value) {
            $filterData['id'] = ['eq',$value['id']];
            $updPlanData['production_plan_rest_number'] = $value['can_insert_num'];
            $updPlanData['production_number'] = $value['num'];
            if ($value['can_insert_num'] <= 0) {
                $updPlanData['production_status'] = self::PRODUCTION_DONE;
                $updPlanData['complete_time'] = time();
            } else {
                $updPlanData['production_status'] = self::PRODUCTION_DOING;
            }

            $updPlanRst = $this->where($filterData)->setField($updPlanData);
            if ($updPlanRst === false) {
                $this->error = "更新生产单生产状态失败，联系管理2";
                return false;
            }
        }
        return true;
    }

}