<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/13
 * Time: 9:51
 */
namespace Dwin\Model;
use Think\Model;
class StockModel extends Model
{
    const PRODUCTION_CATE = 3;  //生产入库分类id
    const REWORK_CATE     = 13; // 返工入库分类id
    const IN_TYPE = 1;
    const OUT_TYPE = 2;

    protected $_auto = [
        ['update_time', 'time', 3, 'function'],
    ];

    public function getOrderData($sqlCondition)
    {
        $field = "stock.product_id,product.product_name,product.product_no,stock.stock_number,stock.warehouse_name,stock.warehouse_number,
        rep.warehouse_manager_id manager_ids, rep.logistics_staff_id";
        $map['warehouse_number'] = ['eq', 'KOO4'];
        $map['product.product_name|product.product_number|product.product_no'] = ['like', "%" . $sqlCondition . "%"];
        $data = $this->alias('stock')
            ->field($field)
            ->join('LEFT JOIN crm_material product ON product.product_id = stock.product_id')
            ->join('LEFT JOIN crm_repertorylist rep ON rep.rep_id = warehouse_number')
            ->where($map)
            ->limit(0, 50)
            ->select();
        return $data;
    }

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
        $level = M('auth_role')->query("SELECT MAX(stock_level) AS max FROM crm_auth_role WHERE FIND_IN_SET('" . session('staffId') . "',staff_ids)")[0]['max'];
        $warehouse = M('repertorylist')->field('rep_id')->where(['display_level' => ['ELT', $level]])->select();
        $repList = [];
        foreach ($warehouse as $key => $value) {
            $repList[] = $value['rep_id'];
        }
        $map['warehouse_number'][] = ['IN', $repList];
        return $this
            ->where($map)
            ->limit($start, $length)
            ->field('*,stock_number + o_audit all_number')
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
        $level = M('auth_role')->query("SELECT MAX(stock_level) AS max FROM crm_auth_role WHERE FIND_IN_SET('" . session('staffId') . "',staff_ids)")[0]['max'];
        $warehouse = M('repertorylist')->field('rep_id')->where(['display_level' => ['ELT', $level]])->select();
        $repList = [];
        foreach ($warehouse as $key => $value) {
            $repList[] = $value['rep_id'];
        }
        $map['warehouse_number'][] = ['IN', $repList];
        return $this
            ->where($map)
            ->count();
    }

    public function productIndex($map = [], $start = '0', $length = '10', $order = '')
    {
        return $this
            ->alias('product')
            ->field('product.*, screen_cate.name cate_name')
            ->join('left join crm_screen_category as screen_cate on product.parent_id = screen_cate.id')
            ->where($map)
            ->limit($start, $length)
            ->order($order)
            ->select();
    }


    public function getStockNumberWithRepAndPid($repertoryId, $productId)
    {
        $map['product_id'] = ['EQ', $productId];
        $map['warehouse_number'] = ['EQ', $repertoryId];
        $data = $this->where($map)->find();
        $num = $data ? (int)($data['stock_number'] + $data['o_audit']) : 0;
        return $num;
    }
    /**
     * 新增审核
     * @param $audit array 审核的信息
     * @return bool
     * @弃用
     */
    public function addAudit($audit)
    {
        $auditModel = new StockAuditModel();
        $fieldName = 'i_audit';
        $auditNum = $auditModel->getAuditNumber($audit['type'], $audit['product_id']) + $audit['num'];
        $audit[$fieldName] = $auditNum;
        if ($this->create($audit) !== false) {
            $map['product_id'] = ['eq', $audit['product_id']];
            $map['warehouse_number'] = ['eq', $audit['warehouse_number']];
            if ($this->where($map)->save($audit) !== false) {
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
        $materialModel = new MaterialModel();
        $materialMap['product_id'] = ['EQ', $audit['product_id']];
        $product = $materialModel->where($materialMap)->field('*')->find();
        $map['product_id'] = ['eq', $audit['product_id']];
        $map['warehouse_number'] = ['eq', $audit['warehouse_number']];
        $stock = $this->where($map)->field("*")->find();
        $stock_number = $stock['stock_number'];
        $auditNum = $auditModel->getAuditNumber($audit['type'], $audit['product_id']) - $audit['num'];

        // 计算月出入库数
        $map = [
            ['update_time' => ['GT', strtotime('-1 months')]],
            ['type' => ['EQ', $audit['type']]],
            ['audit_status' => ['EQ', $audit_status]],
            ['product_id' => ['EQ', $stock['product_id']]],
            ['warehouse_number' => ['EQ', $audit['warehouse_number']]],
            ['is_del' => ['EQ', 0]]
        ];
        $monthStockNumber = (int)$auditModel
            ->where($map)
            ->sum('num');

        // 审核通过
        if ($audit_status == 2) {
            $monthStockNumber += $audit['num'];

            // 判断出入库 type = 1 入库
            if ($audit['type'] == self::IN_TYPE) {
                $stock_number += $audit['num'];
                $fieldName = 'i_audit';
                $monthStockField = 'month_i_stock';
            } else {
                $stock_number -= $audit['num'];
                $fieldName = 'o_audit';
                $monthStockField = 'month_o_stock';
            }
        }
        //审核不通过
        if ($audit_status == 3) {
            if ($audit['type'] == 1) {
                $fieldName = 'i_audit';
            } else {
                $fieldName = 'o_audit';
            }
        }

        $data = [
            'product_id'       => $audit['product_id'],
            'warehouse_number' => $audit['warehouse_number'],
            'stock_number'     => $stock_number,
            $fieldName         => $auditNum,
            $monthStockField   => $monthStockNumber,
        ];
        // 如果生产入库或返工入库审核入库不通过的情况
        if ($audit_status == 3) {
            if ($audit['cate'] == self::PRODUCTION_CATE) {
                $materialData['production_number'] = $product['production_number'] + $audit['num'];
            }
            if ($audit['cate'] == self::REWORK_CATE) {
                $materialData['rework_number'] = $product['rework_number'] + $audit['num'];
            }
        }
        $stockUpdRst = $this->where($map)->save($data);
        if ($stockUpdRst === false) {
            $this->error = "更新库存出错";
            return false;
        } else {
            if (!empty($materialData)) {
                $materialUpdRst = $materialModel->where($materialMap)->save($materialData);
                if ($materialUpdRst === false) {
                    $this->error = "更新物料信息出错";
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
    }

    /**
     * 删除入库审核的回滚方法
     * @param $audit
     * @return bool
     */
    public function editAuditRollback($audit)
    {
        $map['product_id'] = ['eq', $audit['product_id']];
        $map['warehouse_number'] = ['eq', $audit['warehouse_id']];
        $product = $this->where($map)->field("*")->find();
        $putData = [
            'update_time' => time(),
        ];
        $isSMT = M('production_plan')->where(['production_order' => ['EQ', $audit['action_order_number']]])->getField('production_line_name') == 'SMT线';
        $lastMonthTimestamp = strtotime('-1 months', $audit['update_time']);
        // 判断审核状态
        if ($audit['audit_status'] == 2) {

            // 判断出入库类型
            if ($audit['type'] == self::IN_TYPE) {
                if (!$isSMT) {
                    if ($audit['update_time'] >= $lastMonthTimestamp) {
                        $putData['month_i_stock'] = $product['month_i_stock'] - $audit['num'];
                    }
                    $putData['stock_number'] = $product['stock_number'] - $audit['num'];
                }
                if ($audit['cate'] == self::PRODUCTION_CATE) {
                    $materialData['production_number'] = $product['production_number'] + $audit['num'];
                }
                if ($audit['cate'] == self::REWORK_CATE) {
                    $materialData['rework_number'] = $product['rework_number'] + $audit['num'];
                }
            } else {
                if ($audit['update_time'] >= $lastMonthTimestamp) {
                    $putData['month_o_stock'] = $product['month_o_stock'] - $audit['num'];
                }
                $putData['stock_number'] = $product['stock_number'] + $audit['num'];
            }

        } elseif ($audit['audit_status'] == 1 && $audit['type'] == 1) {
            if ($audit['cate'] == self::PRODUCTION_CATE) {
                $materialData['production_number'] = $product['production_number'] + $audit['num'];
            }
            $putData['i_audit'] = $product['i_audit'] - $audit['num'];
            if ($audit['cate'] == self::REWORK_CATE) {
                $materialData['rework_number'] = $product['rework_number'] + $audit['num'];
            }
        }

        $stockUpdRst = $this->where($map)->save($putData);
        if ($stockUpdRst === false) {
            return false;
        } else {
            if (!empty($materialData)) {
                $materialFilter['product_id'] = $map['product_id'];
                $rst = M()->table('crm_material')->where($materialFilter)->save($materialData);
                if ($rst === false) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
    }

    /**
     * 更新某产品正在生产的数量
     * @param $product_id
     * @param $number int 增加或减少的数量
     * @param null $isSMT bool    判断是否是SMT生产引起的生产更新,是的话直接返回true
     * @return bool
     */
    public function updateProducingNumber($product_id, $number, $isSMT = false)
    {
        if ($isSMT) {
            return true;
        }
        $productionPlanModel = new ProductionPlanModel();
        $producingNumber = $productionPlanModel->getAllProducingNumber(['product_id' => ['EQ', $product_id]]) + $number;
        $res = $this->where(['product_id' => ['EQ', $product_id]])->save(['production_number' => $producingNumber, 'update_time' => time()]);
        if ($res !== false) {
            return true;
        } else {
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
            if ($value != null) {  // 过滤空的字段,避免null把数据覆盖了
                $data[$map[$key]] = $value;
            }
        }
        $condition['product_id'] = ['EQ', $id];
        $res = $this->where($condition)->save($data);
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
        $data = $this->where($map)->count();
        if ($data != 0) {
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
        if (!$product_no) {
            return 0;
        }
        $map = [
            'product_no' => ['EQ', $product_no],
        ];
        $data = $this->where($map)->count();
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
        if ($this->add($result) === false) {
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
        return $this->where(['product_name' => ['EQ', $productName]])->getField('product_id');
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
                $stockUpdateData['stock_number'] = array('exp',"stock_number - {$num}");
                $stockUpdateData['o_audit']      = array('exp',"o_audit + {$num}");
                $stockUpdateData['update_time']  = time();
                break;
            case "rejectOrder" :
                $stockUpdateData['stock_number'] = array('exp',"stock_number + {$num}");
                $stockUpdateData['o_audit']      = array('exp',"o_audit - {$num}");
                $stockUpdateData['update_time']  = time();
                break;
            case "addStockOut" :
                $stockUpdateData['o_audit']        = array('exp',"o_audit - {$num}");
                $stockUpdateData['out_processing'] = array('exp',"out_processing + {$num}");
                $stockUpdateData['update_time']    = time();
                break;
            case "addStockOutWithOutOrder" :
                $stockUpdateData['stock_number']   = array('exp',"stock_number - {$num}");
                $stockUpdateData['out_processing'] = array('exp',"out_processing + {$num}");
                $stockUpdateData['update_time']    = time();
                break;
            case "stockOutTrue" :
//                $stockOutModel = new StockAuditOutModel();

//                $productInfo = $this->field('product_id')->where($filter)->find();
//                $monthStockNumber = (int) $stockOutModel->getStockOutNumWithTimeLimit(strtotime('-1 months'), $productInfo['product_id']);
                $stockUpdateData['out_processing'] = array('exp',"out_processing - {$num}");
//                $stockUpdateData['month_o_stock']  = $monthStockNumber + $num;
                $stockUpdateData['update_time']    = time();
                break;
            case "stockOutNoActionOrderFalse" :
//                $stockOutModel = new StockAuditOutModel();

//                $productInfo = $this->field('product_id')->where($filter)->find();
//                $monthStockNumber = (int) $stockOutModel->getStockOutNumWithTimeLimit(strtotime('-1 months'), $productInfo['product_id']);
                $stockUpdateData['stock_number']   = array('exp',"stock_number + {$num}");
                $stockUpdateData['out_processing'] = array('exp',"out_processing - {$num}");
//                $stockUpdateData['month_o_stock']  = $monthStockNumber;
                $stockUpdateData['update_time']    = time();
                break;
            case "stockOutFalse" :
//                $stockOutModel = new StockAuditOutModel();
//                $productInfo = $this->field('product_id')->where($filter)->find();
//                $monthStockNumber = (int) $stockOutModel->getStockOutNumWithTimeLimit(strtotime('-1 months'), $productInfo['product_id']);
                $stockUpdateData['o_audit']        = array('exp',"o_audit + {$num}");
                $stockUpdateData['out_processing'] = array('exp',"out_processing - {$num}");
//                $stockUpdateData['month_o_stock']  = $monthStockNumber;
                $stockUpdateData['update_time']    = time();
                break;
            case "stockReworkOutTrue" :
                $stockUpdateData['out_processing'] = array('exp',"out_processing - {$num}");
                $materialUpdateData['rework_number']  = array('exp', "rework_number + {$num}");

                break;
            case "rollbackPassedStockOutNoOrder" :
                $stockUpdateData['stock_number'] = array('exp', "stock_number + {$num}");
                $stockUpdateData['update_time']    = time();
                break;
            case "rollbackPassedStockOutHasOrder" :
                $stockUpdateData['o_audit'] = array('exp', "o_audit + {$num}");
                $stockUpdateData['update_time']    = time();
                break;
            case "rollbackPassedReworkOut" :
                $stockUpdateData['stock_number'] = array('exp', "stock_number + {$num}");
                $stockUpdateData['update_time']    = time();
                $materialUpdateData['rework_number'] = array('exp', "rework_number - {$num}");
                break;
            case "addStockIn" :
                $stockUpdateData['i_audit'] = array('exp',"i_audit - {$num}");
                $stockUpdateData['update_time']     = time();
                break;
            case "checkStockIn" :
                $stockUpdateData['stock_number'] = array('exp', "stock_number + {$num}");
                $stockUpdateData['update_time']  = time();
                break;
            default :
                break;
        }
        if (empty($stockUpdateData)) {
            return false;
        } else {
            if (empty($materialUpdateData)) {
                return $updateRst = $this->where($filter)->setField($stockUpdateData);
            } else {
                $upd1 = $this->where($filter)->setField($stockUpdateData);
                if ($upd1 === false) {
                    return false;
                } else {
                    $map['product_id'] = $filter['product_id'];
                    return $upd2 = M()->table('crm_material')->where($map)->setField($materialUpdateData);
                }
            }

        }
    }


    /**
     * @param array $stockAuditUpdateData 要更新的库存记录状态数据
     * @param array $stockLog 查询得到对应记录的库存出库数量
     * @return bool|int|string
     */
    public function updateWithStockOutData($stockAuditUpdateData, $stockLog)
    {
        $productFilter['product_id'] = ['EQ', $stockLog['product_id']];
        $productFilter['warehouse_number'] = ['EQ', $stockLog['warehouse_number']];

        if ((int)$stockAuditUpdateData['audit_status'] != 2) {
            $flag = !empty($stockLog['action_order_number']) ? 'stockOutFalse' : 'stockOutNoActionOrderFalse';
        } else {
            $flag = ($stockLog['cate'] == 7) ? 'stockReworkOutTrue' : 'stockOutTrue';
        }
        return $this->updateWithFlag($flag, $productFilter, $stockLog['num']);
    }

    public function addDataWithMaterialId($productId)
    {
        $repertoryModel = new RepertorylistModel();
        $repMap['cate_flag'] = ['EQ', 1];
        $repMap['is_del'] = ['EQ', 0];
        $addData = $repertoryModel->where($repMap)
            ->field('rep_id warehouse_number, repertory_name warehouse_name')->select();
        foreach ($addData as &$addDatum) {
            $addDatum['product_id'] = $productId;
            $addDatum['update_time'] = time();
        }
        $addRst = $this->addAll($addData);
        return $addRst === false ? false : true;

    }

    /**
     * 针对一个库 ，一种物料进行修改库存分配，进行锁库操作  目前针对 其他出库类型的申请单
     * @param $productId
     * @param $warehouseNumber
     * @param $num
     * @return array
     */
    public function editStockNum($productId, $warehouseNumber, $num){
        if (empty($num)) {
            return [- 1, "出库数量不能为空"];
        }

        $stockData = $this->where(['warehouse_number' => $warehouseNumber, "product_id" => $productId])->find();

        if (empty($stockData)) {
            return [- 2, "未录入库存数据中"];
        }

        $field['update_time'] = time();
        $field['stock_number'] = $stockData['stock_number'] - $num;
        $field['o_audit'] = $stockData['o_audit'] + $num;

        $res = $this->where(['id' => $stockData['id']])->setField($field);
        if (!$res) {
            return [- 2, $this->getError()];
        }
        return [0, "同步更新crm_stock 数据成功"];
    }

    /**
     * 更新出库信息 生产领料出库
     * @param $productId
     * @param $warehouseNumber
     * @param $num
     * @param $type  1 => 新增出库  2=>减少出库
     * @return array
     */
    public function stockOutToUpdateStock($productId, $warehouseNumber, $num, $type = 1)
    {
        if (empty($num)) {
            return [- 1, "更新数量不能为空"];
        }

        $stockData = $this->where(['warehouse_number' => $warehouseNumber, "product_id" => $productId])->find();

        if (empty($stockData)) {
            return [- 2, "未录入库存数据中"];
        }

        if($type == 1){
            if($num > ($stockData['o_audit'] + $stockData['stock_number'])){
                return [- 2, "库存不足"];
            }

            $updateNum = $stockData['out_processing'] + $num;
            $stockNumber = $stockData['stock_number'] - $num;
        }else {
            $updateNum = $stockData['out_processing'] - $num;
            $stockNumber = $stockData['stock_number'] + $num;
        }

        $field['out_processing'] = $updateNum;
        $field['stock_number'] = $stockNumber;
        $field['update_time'] = time();
        $res = $this->where(['id' => $stockData['id']])->setField($field);
        if (!$res) {
            return [- 2, $this->getError()];
        }

        return [0, "同步更新crm_stock 数据成功"];
    }

    /**
     * 更新出库信息 销售出库单 （里面参杂了其他出库单的(删除整个其他出库单)，如要修改，请注意是否影响其他出库单）
     * @param $productId
     * @param $warehouseNumber
     * @param $num
     * @param $type  1 => 新增出库  2=>减少出库
     * @return array
     */
    public function orderFormStockOutToUpdateStock($productId, $warehouseNumber, $num, $type = 1)
    {
        if (empty($num)) {
            return [- 1, "更新数量不能为空"];
        }

        $stockData = $this->where(['warehouse_number' => $warehouseNumber, "product_id" => $productId])->find();

        if (empty($stockData)) {
            return [- 2, "未录入库存数据中"];
        }

        if($type == 1){
            if($num > ($stockData['o_audit'] + $stockData['stock_number'])){
                return [- 2, "库存不足"];
            }

            $updateNum = $stockData['out_processing'] + $num;
            $stockNumber = $stockData['o_audit'] - $num;
        }else {
            $updateNum = $stockData['out_processing'] - $num;
            $stockNumber = $stockData['o_audit'] + $num;
        }

        $field['out_processing'] = $updateNum;
        $field['o_audit'] = $stockNumber;
        $field['update_time'] = time();
        $res = $this->where(['id' => $stockData['id']])->setField($field);
        if (!$res) {
            return [- 2, $this->getError()];
        }

        return [0, "同步更新crm_stock 数据成功"];
    }

    /**
     * 修改领料(生产)出库单出库信息，回退原来库存待出库数量，更新目前库房内待出库数量, 与锁库数量 （里面参杂了其他出库单的(修改其他出库单物料出库的库房)，如要修改，请注意是否影响其他出库单）
     * @param $productId
     * @param $beforeWarehouseNumber
     * @param $nowWarehouseNumber
     * @param $beforeNum
     * @param $nowNum
     * @return array
     */
    public function updateStockOutToUpdateStock($productId, $beforeWarehouseNumber, $nowWarehouseNumber, $beforeNum, $nowNum)
    {
        if (empty($num)) {
            return [- 1, "更新数量不能为空"];
        }

        $stockBeforeData = $this->where(['warehouse_number' => $beforeWarehouseNumber, "product_id" => $productId])->find();
        $stockNowData = $this->where(['warehouse_number' => $nowWarehouseNumber, "product_id" => $productId])->find();

        if (empty($stockBeforeData) || empty($stockNowData)) {
            return [- 2, "未录入库存数据中"];
        }

        if($nowNum > ($stockNowData['stock_number'] + $stockNowData['o_audit'])){
            return [- 2, "库存不足"];
        }

        $this->where(['id' => $stockBeforeData['id']])->setField(["out_processing" => $stockBeforeData['out_processing'] - $beforeNum, "update_time" => time(), 'stock_number' => $stockBeforeData['stock_number'] + $beforeNum]);
        $this->where(['id' => $stockNowData['id']])->setField(["out_processing" => $stockNowData['out_processing'] + $nowNum, "update_time" => time(), "stock_number" => $stockNowData['stock_number'] - $nowNum]);
        return [0, "同步更新crm_stock 数据成功"];
    }


    /**
     * 修改销售出库单出库信息，回退原来库存待出库数量，更新目前库房内待出库数量, 与锁库数量
     * @param $productId
     * @param $beforeWarehouseNumber
     * @param $nowWarehouseNumber
     * @param $beforeNum
     * @param $nowNum
     * @return array
     */
    public function updateOrderFormStockOutToUpdateStock($productId, $beforeWarehouseNumber, $nowWarehouseNumber, $beforeNum, $nowNum)
    {
        if (empty($num)) {
            return [- 1, "更新数量不能为空"];
        }

        $stockBeforeData = $this->where(['warehouse_number' => $beforeWarehouseNumber, "product_id" => $productId])->find();
        $stockNowData = $this->where(['warehouse_number' => $nowWarehouseNumber, "product_id" => $productId])->find();

        if (empty($stockBeforeData) || empty($stockNowData)) {
            return [- 2, "未录入库存数据中"];
        }

        if($nowNum > ($stockNowData['stock_number'] + $stockNowData['o_audit'])){
            return [- 2, "库存不足"];
        }

        $this->where(['id' => $stockBeforeData['id']])->setField(["out_processing" => $stockBeforeData['out_processing'] - $beforeNum, "update_time" => time(), 'stock_number' => $stockBeforeData['o_audit'] + $beforeNum]);
        $this->where(['id' => $stockNowData['id']])->setField(["out_processing" => $stockNowData['out_processing'] + $nowNum, "update_time" => time(), "stock_number" => $stockNowData['o_audit'] - $nowNum]);
        return [0, "同步更新crm_stock 数据成功"];
    }

    /**
     * @param $repId
     * @param $productId
     * @return array
     */
    public function getStockMsgByRepIDAndMaterialId($repId, $productId){
        $data = $this->where(['warehouse_number' => $repId, 'product_id' => $productId])->find();

        if(empty($data)){
            return dataReturn("当前物料在该库房中无数据",400);
        }

        return dataReturn("获取数据成功", 200, $data);
    }

    /**
     * 回退物料
     * @param $productId
     * @param $warehouseId
     * @param $num
     * @return array
     */
    public function rollBackStockNum($productId, $warehouseId, $num){
        $stockData = $this->where(['warehouse_number' => $warehouseId, "product_id" => $productId])->find();

        if (empty($stockData)) {
            return [- 2, "未录入库存数据中"];
        }

        $field['update_time'] = time();
        $field['stock_number'] = $stockData['stock_number'] + $num;

        $res = $this->where(['id' => $stockData['id']])->setField($field);
        if (!$res) {
            return [- 2, $this->getError()];
        }
        return [0, "同步更新crm_stock 数据成功"];
    }
}