<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/13
 * Time: 9:51
 */
namespace Dwin\Model;
use Think\Model;
class MaterialModel extends Model
{
    const PRODUCTION_CATE = 3;  //生产入库分类id
    const REWORK_CATE     = 13;     // 返工入库分类id

    const TYPE_PRODUCE = 1; // 生产
    const TYPE_PURCHASE = 2; // 外购

    const STATUS_ACTIVE = 0; // 有效
    const STATUS_FORBIDDEN = 1; // 禁用

    static public $statusMap = [
        self::STATUS_ACTIVE => "有效",
        self::STATUS_FORBIDDEN => "禁用",
    ];

    protected $_auto = [
        ['update_time', 'time', 3, 'function'],
    ];

    private $map = [
        'newproduct_number' => 'product_number',
        'newcost'           => 'cost',
        'newparent_id'      => 'parent_id',
        'newprice'          => 'price',
        'newperformance'    => 'performance',
        'newperform_flag'   => 'statistics_performance_flag',
        'newshipment_flag'  => 'statistics_shipments_flag',
        'newwarehouse_number' => 'warehouse_id',
        'newwarehouse_name'   => 'warehouse_name',
        'newplatform_id'    => 'platform_id',
        'newcate'           => 'cate',
        'newproduct_no'     => 'product_no',
        'new_material_type' => 'material_type',
        'product_name'      => 'product_name'
    ];

    public function getOrderData($sqlCondition)
    {
        $field = "product.product_id,
                  product.product_name,
                  product.product_no,
                  stock.stock_number,
                  stock.warehouse_name,
                  stock.warehouse_number,
                  rep.warehouse_manager_id manager_ids, 
                  rep.logistics_staff_id";

        $map['warehouse_number'] = ['eq', 'K004'];
        $map['product.product_name|product.product_number|product.product_no'] = ['like', "%" . $sqlCondition . "%"];
        $data = $this->alias('product')
            ->field($field)
            ->join('LEFT JOIN crm_stock stock ON product.product_id = stock.product_id')
            ->join('LEFT JOIN crm_repertorylist rep ON rep.rep_id = warehouse_number')
            ->where($map)
            ->limit(0,20)
            ->select();
        return $data;
    }

    /**
     * 获得物料信息
     * @param $map      array   搜索条件
     * @param $start    string  起始行
     * @param $length   string  结果长度
     * @param $order    string  排序
     * @return array
     */
    public function index($map = [], $start = '0', $length = '10', $order = '')
    {

        $time = mktime(0,0,0,date('m'),1,date('Y'));
        $field = "
            material.product_id,
            material.product_no,
            material.product_name,
            material.product_number,
            material.material_type,
            material.rework_number,
            material.production_number,
            material.safety_number,
            sum(stock_number) + sum(o_audit) all_number,
            sum(stock_number) stock_number,
            sum(o_audit) o_audit,
            sum(i_audit) i_audit,
            sum(out_processing) out_processing,
            material.safety_number - stock_number warning_number,
            from_unixtime(max(stock.update_time)) update_time,
            r.repertory_name,
            stock.warehouse_number";

        return $this
            ->alias('material')
            ->where($map)
            ->limit($start,$length)
            ->field($field)
            ->join('LEFT JOIN crm_stock stock ON stock.product_id = material.product_id')
            ->join('left join crm_repertorylist r on r.rep_id = stock.warehouse_number')
//            ->join("LEFT JOIN crm_stock_in_record i_record ON (material.product_id = i_record.product_id and i_record.status = 2 and i_record.create_time > {$time})")
//            ->join("LEFT JOIN crm_stock_out_record o_record ON (material.product_id = o_record.product_id and o_record.status = 2 and i_record.create_time > {$time})")
            ->group('material.product_id')
            ->order($order)
            ->select();
    }


    /**
     * 同上,计数用
     * @param array $map
     * @return mixed
     */
    public function indexCount($map = [])
    {

        if (empty($map['warehouse_number'])) {
            $repertoryModel = new RepertorylistModel();
            $repList = $repertoryModel->getMrpWarehouseWithRight(session('staffId'));
            $repIds = getPrjIds($repList,'rep_id');
            $map['warehouse_number'] = ['IN', $repIds];
        }
        return $this
            -> where($map)
            -> count();
    }

    /**
     * 产品列表页
     * @auditor yang
     * @updater maxu
     * @abstract 原来为industry_screen 分表后进行字段调整。
     *
    */
    public function productIndex($map = [], $start = '0', $length = '10', $order = '')
    {
        return $this
            -> alias('product')
            -> field('product.*, screen_cate.name cate_name,repertory_name,platform.name platform_name')
            -> join('left join crm_screen_category as screen_cate on product.parent_id = screen_cate.id')
            -> join('left join crm_screen_category as platform on product.platform_id = platform.id')
            -> join('LEFT JOIN crm_repertorylist rep ON rep.rep_id = product.warehouse_id')
            -> where($map)
            -> limit($start,$length)
            -> order($order)
            -> select();
    }

    /**
     * 新增审核
     * @param $audit array 审核的信息
     * @return bool
     */
    public function addAudit($audit)
    {
        $auditModel = new StockAuditModel();
        $fieldName = $audit['type'] == 1 ? 'i_audit' : 'o_audit';
        $auditNum = $auditModel -> getAuditNumber($audit['type'], $audit['product_id']) + $audit['num'];
        $audit[$fieldName] = $auditNum;
        if ($this -> create($audit) !== false){
            if ($this -> where(['product_id' => $audit['product_id']]) -> save($audit) !== false){
                return true;
            }
        }
        return false;
    }

    /**
     * 修改审核状态
     * @param $audit
     * @param $audit_status
     * @return bool
     */
    public function editAudit($audit, $audit_status)
    {
        $auditModel = new StockAuditModel();
        $product = $this -> find($audit['product_id']);
        $stock_number = $product['stock_number'];
        $auditNum = $auditModel -> getAuditNumber($audit['type'], $audit['product_id']) - $audit['num'];

        // 计算月出入库数
        $map = [
            ['update_time' => ['GT',strtotime('-1 months')]],
            ['type' => ['EQ', $audit['type']]],
            ['audit_status' => ['EQ', $audit_status]],
            ['product_id' => ['EQ', $product['product_id']]],
            ['is_del' => ['EQ', 0]]
        ];
        $monthStockNumber = (int) $auditModel
            ->where($map)
            ->sum('num');

        // 审核通过
        if ($audit_status == 2){
            $monthStockNumber += $audit['num'];

            // 判断出入库
            if ($audit['type'] == 1) {
                $stock_number += $audit['num'];
                $fieldName = 'i_audit';
                $monthStockField = 'month_i_stock';
                if ($audit['cate'] == self::PRODUCTION_CATE){
                    $productionLineName = M('production_plan') -> where(['production_order' => ['EQ', $audit['action_order_number']]]) -> getField('production_line_name');
                    if ($productionLineName == 'SMT线'){
                        $stock_number -= $audit['num'];
                    }
                }
            } else {
                $stock_number -= $audit['num'];
                $fieldName = 'o_audit';
                $monthStockField = 'month_o_stock';
                if ($stock_number < 0){
                    return false;
                }
            }
        }
        //审核不通过
        if ($audit_status == 3){
            if ($audit['type'] == 1) {
                $fieldName = 'i_audit';
            } else {
                $fieldName = 'o_audit';
            }
        }

        $data = [
            'product_id' => $audit['product_id'],
            'stock_number' => $stock_number,
            $fieldName => $auditNum,
            $monthStockField => $monthStockNumber,
        ];
        // 如果生产入库或返工入库审核入库不通过的情况
        if ($audit_status == 3) {
            if ($audit['cate'] == self::PRODUCTION_CATE) {
                $data['production_number'] = $product['production_number'] + $audit['num'];
            }
            if ($audit['cate'] == self::REWORK_CATE){
                $data['rework_number'] = $product['rework_number'] + $audit['num'];
            }
        }
        return $this -> where(['product_id' => ['EQ', $audit['product_id']]]) -> save($data) === false ? false : true;
    }

    /**
     * 删除入库审核的回滚方法
     * @param $audit
     * @return bool
     */
    public function editAuditRollback($audit)
    {
        $product = $this -> find($audit['product_id']);
        $putData = [
            'update_time' => time(),
        ];
        $isSMT =  M('production_plan') -> where(['production_order' => ['EQ', $audit['action_order_number']]]) -> getField('production_line_name') == 'SMT线';
        $lastMonthTimestamp = strtotime('-1 months', $audit['update_time']);
        // 判断审核状态
        if ($audit['audit_status'] == 2) {

            // 判断出入库类型
            if ($audit['type'] == 1){
                if (!$isSMT){
                    if ($audit['update_time'] >= $lastMonthTimestamp){
                        $putData['month_i_stock'] = $product['month_i_stock'] - $audit['num'];
                    }
                    $putData['stock_number'] = $product['stock_number'] - $audit['num'];
                }
                if ($audit['cate'] == self::PRODUCTION_CATE){
                    $putData['production_number'] = $product['production_number'] + $audit['num'];
                }
                if ($audit['cate'] == self::REWORK_CATE){
                    $putData['rework_number'] = $product['rework_number'] + $audit['num'];
                }
            }else{
                if ($audit['update_time'] >= $lastMonthTimestamp) {
                    $putData['month_o_stock'] = $product['month_o_stock'] - $audit['num'];
                }
                $putData['stock_number'] = $product['stock_number'] + $audit['num'];
            }

        } elseif ($audit['audit_status'] == 1 && $audit['type'] == 1){
            if ($audit['cate'] == self::PRODUCTION_CATE){
                $putData['production_number'] = $product['production_number'] + $audit['num'];
            }
            $putData['i_audit'] = $product['i_audit'] - $audit['num'];
            if ($audit['cate'] == self::REWORK_CATE){
                $putData['rework_number'] = $product['rework_number'] + $audit['num'];
            }
        }

        return $this -> where(['product_id' => ['EQ', $audit['product_id']]]) -> save($putData) === false ? false : true;
    }

    /**
     * 更新某产品正在生产的数量
     * @param $product_id
     * @param $number int 增加或减少的数量
     * @param null $isSMT   bool    判断是否是SMT生产引起的生产更新,是的话直接返回true
     * @return bool
     */
    public function updateProducingNumber($product_id, $number)
    {

        $productionPlanModel = new ProductionPlanModel();
        $producingNumber = $productionPlanModel -> getAllProducingNumber(['product_id' => ['EQ', $product_id]]) + $number;
        $res = $this->where(['product_id' => ['EQ', $product_id]]) -> save(['production_number' => $producingNumber, 'update_time' => time()]);
        if ($res !== false) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * 更新产品信息
     * @param $id   int     产品id
     * @param $info     array   修改的信息
     * @return bool
     */
    public function updateProductInfo($id, $info)
    {
        unset($info['audit_status']);
        $data = [];
        foreach ($info as $key => $value) {
            if ($value != null && !empty($value)){  // 过滤空的字段,避免null把数据覆盖了
                $data[$this->map[$key]] = $value;
            }
        }
        $condition['product_id'] = array('EQ', $id);
        $res = $this->where($condition) -> save($data);
        return $res !== false ? true : false;
    }

    /**
     * product_name检查重复
     * @param $product_name   string  产品名
     * @param $warehouse_number string  仓库编号
     * @return bool 合法返回true, 不合法返回false
     */
    public function productNameIsUnique($product_name, $warehouse_number)
    {
        $map = [
            'product_name' => ['EQ', $product_name],
            'warehouse_number' => ['EQ', $warehouse_number]
        ];
        $data = $this->where($map) -> count();
        if ($data != 0){
            return false;
        }
        return true;
    }

    /**
     * 物料代码的检查重复
     * @param $product_no
     * @return int
     */
    public function productNoIsUnique($fieldName, $product_no)
    {
        $map = [
            $fieldName => ['EQ', $product_no],
        ];
        $data = $this->where($map) -> count();
        return $data;
    }

    /**
     * 新增产品
     * @param array prochangerecord表对应的数据
     * @return bool
     */
    public function addProduct($data)
    {
        $result = [];
        foreach ($this->map as $key => $value) {
            if ($data[$key]) {
                $result[$value] = $data[$key];
            }
        }

        $addRst = $this->add($this->create($result));
        if ($addRst === false) {
            return false;
        }
        return $addRst;
    }

    /**
     * 根据productName获取productId
     * @param $productName
     * @return mixed
     */
    public function getProductIDbyProductName($productName)
    {
        return $this->where(['product_name' => ['EQ', $productName]]) -> getField('product_id');
    }

    /**
     * 根据$flag 更新对应型号的库存数量方法
     * @name updateWithFlag
     * @access public
     * @param string $flag   更新标识
     * @param array  $filter 更新条件
     * @param int    $num    更新数量
     * @return boolean
     * @abstract：
     * 1 addOrder      添加订单减少可发货库存，增加待出库库存
     * 2 rejectOrder   驳回订单增加可发货库存，减少待出库库存
     * 3 addStockOut   添加出库记录，增加出库中库存，减少待出库库存
     * 4 stockOutTrue  审核出库通过，减少出库中库存
     * 4 stockOutFalse 审核出库不通过，减少出库中库存，增加待出库库存
     * @todo 生产入库等相关 分支
     */
    public function updateWithFlag($flag, $filter, $num)
    {
        switch ($flag) {

            case "addOrder" :
                $screenUpdateData['stock_number'] = array('exp',"stock_number - {$num}");
                $screenUpdateData['o_audit']      = array('exp',"o_audit + {$num}");
                $screenUpdateData['update_time']  = time();
                break;
            case "rejectOrder" :
                $screenUpdateData['stock_number'] = array('exp',"stock_number + {$num}");
                $screenUpdateData['o_audit']      = array('exp',"o_audit - {$num}");
                $screenUpdateData['update_time']  = time();
                break;
            case "addStockOut" :
                $screenUpdateData['o_audit']        = array('exp',"o_audit - {$num}");
                $screenUpdateData['out_processing'] = array('exp',"out_processing + {$num}");
                $screenUpdateData['update_time']    = time();
                break;
            case "addStockOutWithOutOrder" :
                $screenUpdateData['stock_number']   = array('exp',"stock_number - {$num}");
                $screenUpdateData['out_processing'] = array('exp',"out_processing + {$num}");
                $screenUpdateData['update_time']    = time();
                break;
            case "stockOutTrue" :
                $stockOutModel = new StockAuditOutModel();

                $productInfo = $this->field('product_id')->where($filter)->find();
                $monthStockNumber = (int) $stockOutModel->getStockOutNumWithTimeLimit(strtotime('-1 months'), $productInfo['product_id']);
                $screenUpdateData['out_processing'] = array('exp',"out_processing - {$num}");
                $screenUpdateData['month_o_stock']  = $monthStockNumber + $num;
                $screenUpdateData['update_time']    = time();
                break;
            case "stockOutNoActionOrderFalse" :
                $stockOutModel = new StockAuditOutModel();

                $productInfo = $this->field('product_id')->where($filter)->find();
                $monthStockNumber = (int) $stockOutModel->getStockOutNumWithTimeLimit(strtotime('-1 months'), $productInfo['product_id']);
                $screenUpdateData['stock_number']   = array('exp',"stock_number + {$num}");
                $screenUpdateData['out_processing'] = array('exp',"out_processing - {$num}");
                $screenUpdateData['month_o_stock']  = $monthStockNumber;
                $screenUpdateData['update_time']    = time();
                break;
            case "stockOutFalse" :
                $stockOutModel = new StockAuditOutModel();

                $productInfo = $this->field('product_id')->where($filter)->find();
                $monthStockNumber = (int) $stockOutModel->getStockOutNumWithTimeLimit(strtotime('-1 months'), $productInfo['product_id']);
                $screenUpdateData['o_audit']        = array('exp',"o_audit + {$num}");
                $screenUpdateData['out_processing'] = array('exp',"out_processing - {$num}");
                $screenUpdateData['month_o_stock']  = $monthStockNumber;
                $screenUpdateData['update_time']    = time();
                break;
            case "stockReworkOutTrue" :
                $screenUpdateData['out_processing'] = array('exp',"out_processing - {$num}");
                $screenUpdateData['rework_number']  = array('exp', "rework_number + {$num}");
                break;
            case "rollbackPassedStockOutNoOrder" :
                $screenUpdateData['stock_number'] = array('exp', "stock_number + {$num}");
                $screenUpdateData['update_time']    = time();
                break;
            case "rollbackPassedStockOutHasOrder" :
                $screenUpdateData['o_audit'] = array('exp', "o_audit + {$num}");
                $screenUpdateData['update_time']    = time();
                break;
            case "rollbackPassedReworkOut" :
                $screenUpdateData['stock_number'] = array('exp', "stock_number + {$num}");
                $screenUpdateData['rework_number'] = array('exp', "rework_number - {$num}");
                $screenUpdateData['update_time']    = time();
                break;
            case "addStockIn" :
                $screenUpdateData['_audit'] = array('exp',"i_audit - {$num}");
                $screenUpdateData['update_time']     = time();
                break;
            default :
                break;
        }
        if (empty($screenUpdateData)) {
            return false;
        } else {
            return $updateRst = $this->where($filter)->setField($screenUpdateData);
        }
    }

    /**
     * @param array $stockAuditUpdateData 要更新的库存记录状态数据
     * @param array $stockLog 查询得到对应记录的库存出库数量
     * @return bool|int|string
    */
    public function updateWithStockOutData($stockAuditUpdateData, $stockLog)
    {
        $productFilter['product_name']     = ['EQ', $stockLog['product_name']];
        $productFilter['warehouse_number'] = ['EQ', $stockLog['warehouse_number']];

        if ((int)$stockAuditUpdateData['audit_status'] != 2) {
            $flag = !empty($stockLog['action_order_number']) ? 'stockOutFalse' : 'stockOutNoActionOrderFalse';
        } else {
            $flag = ($stockLog['cate'] == 7) ? 'stockReworkOutTrue' :'stockOutTrue';
        }
        return $this->updateWithFlag($flag, $productFilter, $stockLog['num']);
    }

    public function rollbackStockOutData($stockOutData)
    {

    }

    /**
     * @param $productNo
     * @return array
     */
    public function checkIsset($productNo){
        return $this->where(['product_no' => $productNo])->find();

    }

    /**
     * 获取全部物料列表页信息
     */
    public function getList($condition, $start, $length, $order){
        $map = [];
        $recordMap = $map;

        if(strlen($condition) != 0){
            $map['crm_material.product_no'] = ['like', "%" . $condition . "%"];
            $map['crm_material.product_name']=['like', "%" . $condition . "%"];
            $map['crm_material.product_number']=['like', "%" . $condition . "%"];
            $recordMap['_logic'] = 'OR';
        }

        $data =  $this->field("*")
            ->limit($start, $length)
            ->where($recordMap)
            ->order($order)
            ->select();
        /** 后台传输局到前台
        @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
        $count = $this->where($map)->count();
        $recordsFiltered = $this->where($recordMap)->count();

        return [$data,$count,$recordsFiltered];
    }
}