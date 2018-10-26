<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/13
 * Time: 9:51
 */
namespace Dwin\Model;
use Think\Model;
class IndustrialSeralScreenModel extends Model
{
    const PRODUCTION_CATE = 3;  //生产入库分类id
    const REWORK_CATE     = 13;     // 返工入库分类id

    protected $_auto = [
        ['update_time', 'time', 3, 'function'],
    ];

    /**
     * 获得所有产品信息
     * @param $map      array   搜索条件
     * @param $start    string  起始行
     * @param $length   string  结果长度
     * @param $order    string  排序
     * @return array
     */
    public function index($map = [], $start = '0', $length = '10', $order = '')
    {
        $level = M('auth_role') -> query("SELECT MAX(stock_level) AS max FROM crm_auth_role WHERE FIND_IN_SET('".session('staffId')."',staff_ids)")[0]['max'];
        $warehouse = M('repertorylist') -> field('rep_id') -> where(['display_level' => ['ELT', $level]]) -> select();
        $repList = [];
        foreach ($warehouse as $key => $value) {
            $repList[] = $value['rep_id'];
        }
        $map['warehouse_number'][] = ['IN', $repList];
        return $this
             -> where($map)
             -> limit($start,$length)
             ->field('*,stock_number + o_audit all_number')
             -> order($order)
             -> select();
    }

    /**
     * 同上,计数用
     * @param array $map
     * @return mixed
     */
    public function indexCount($map = [])
    {
        $level = M('auth_role') -> query("SELECT MAX(stock_level) AS max FROM crm_auth_role WHERE FIND_IN_SET('".session('staffId')."',staff_ids)")[0]['max'];
        $warehouse = M('repertorylist') -> field('rep_id') -> where(['display_level' => ['ELT', $level]]) -> select();
        $repList = [];
        foreach ($warehouse as $key => $value) {
            $repList[] = $value['rep_id'];
        }
        $map['warehouse_number'][] = ['IN', $repList];
        return $this
            -> where($map)
            -> count();
    }

    public function productIndex($map = [], $start = '0', $length = '10', $order = '')
    {
        return $this
            -> alias('product')
            -> field('product.*, screen_cate.name cate_name')
            -> join('left join crm_screen_category as screen_cate on product.parent_id = screen_cate.id')
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
    public function updateProducingNumber($product_id, $number, $isSMT = false)
    {
        if($isSMT){
            return true;
        }
        $productionPlanModel = new ProductionPlanModel();
        $producingNumber = $productionPlanModel -> getAllProducingNumber(['product_id' => ['EQ', $product_id]]) + $number;
        $res = $this->where(['product_id' => ['EQ', $product_id]]) -> save(['production_number' => $producingNumber, 'update_time' => time()]);
        if ($res !== false){
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
        // 定义修改表和产品表的映射关系
        $map = [
            'newproduct_number' => 'product_number',
            'newcost' => 'cost',
            'newparent_id' => 'parent_id',
            'newprice' => 'price',
            'newperformance' => 'performance',
            'newperform_flag' => 'statistics_performance_flag',
            'newshipment_flag' => 'statistics_shipments_flag',
            'newwarehouse_number' => 'warehouse_number',
            'newwarehouse_name' => 'warehouse_name',
            'create_time' => 'create_time',
            'newplatform_id' => 'platform_id',
            'newcate' => 'cate',
            'newproduct_no' => 'product_no'
        ];
        unset($info['audit_status']);
        $data = [];
        foreach ($info as $key => $value) {
            if ($value != null){  // 过滤空的字段,避免null把数据覆盖了
                $data[$map[$key]] = $value;
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
    public function productNoIsUnique($product_no)
    {
        if (!$product_no){
            return 0;
        }
        $map = [
            'product_no' => ['EQ', $product_no],
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
        $map = [
            'newproduct_number'   => 'product_number',
            'product_name'        => 'product_name',
            'newplatform_id'      => 'platform_id',
            'newcate'             => 'cate',
            'newparent_id'        => 'parent_id',
            'newshipment_flag'    => 'statistics_shipments_flag',
            'newperform_flag'     => 'statistics_performance_flag',
            'newwarehouse_number' => 'warehouse_number',
            'newwarehouse_name'   => 'warehouse_name',
            'create_time'         => 'createTime',
            'auditor_name'        => 'auditorName',
            'auditor_id'          => 'auditorId',
            'changemanid'         => 'proposer_id',
            'changemanname'       => 'proposer_name',
            'action_type'         => 'actionType',
            'newproduct_no'       => 'product_no',
        ];
        $result = [];
        foreach ($map as $key => $value) {
            $result[$value] = $data[$key];
        }
        if ($this->add($result) === false){
            return false;
        }
        return true;
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
}