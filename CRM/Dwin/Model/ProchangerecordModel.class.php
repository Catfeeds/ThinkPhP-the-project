<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/22
 * Time: 17:42
 */
namespace Dwin\Model;
use function PHPSTORM_META\map;
use Think\Model;
class ProchangerecordModel extends Model
{
    const SUCCESS_STATUS = 200;
    const FAIL_STATUS = -1;

    const EDIT_ACTION_TYPE = 1; // 代表修改操作
    const ADD_ACTION_TYPE = 2;  // 代表新增操作
    protected $_map = [
        'cate_num'          => 'newparent_id',
        'parent_id'         => 'oldparent_id',
        'warehouse_num'     => 'newwarehouse_number',
        'warehouse_number'  => 'oldwarehouse_number',
        'product_num'       => 'newproduct_number',
        'product_number'    => 'oldproduct_number',
        'warehouse_name'    => 'oldwarehouse_name',
        'xinplateform_id'   => 'newplatform_id',
        'platform_id'       => 'oldplatform_id',
        'actionType'        => 'action_type',
        'auditType'         => 'audit_type',
        'product_No'        => 'newproduct_no',
        'product_no'        => 'oldproduct_no',
        'material_type_new' => 'new_material_type',
        'material_type_old' => 'old_material_type',
    ];
    static private $addMap = [
        'product_number'              => 'newproduct_number',
        'product_name'                => 'product_name',
        'platform_id'                 => 'newplatform_id',
        'cate'                        => 'newcate',
        'parent_id'                   => 'newparent_id',
        'statistics_shipments_flag'   => 'newshipment_flag',
        'statistics_performance_flag' => 'newperform_flag',
        'createTime'                  => 'create_time',
        'auditorName'                 => 'auditor_name',
        'auditorId'                   => 'auditor_id',
        'proposer_id'                 => 'changemanid',
        'proposer_name'               => 'changemanname',
        'actionType'                  => 'action_type',
        'auditType'                   => 'audit_type',
        'product_no'                  => 'newproduct_no',
        'material_type_new'           => 'new_material_type',
        'warehouse_num'               => 'newwarehouse_number',
        'warehouse_name'              => 'newwarehouse_name'
    ];

    /**
     * @param array $map
     * @param string $start
     * @param string $length
     * @param string $order
     * @return mixed
     */
    public function index($map = [], $start = '0', $length = '10', $order = '')
    {
        return $this
            -> where($map)
            -> limit($start,$length)
            -> order($order)
            -> select();
    }

    public function indexCount($map = [])
    {
        return $this
            -> where($map)
            -> count();
    }

    static private function strConnect($str, $data, $oldData, $content, $type)
    {
        if ($type == self::EDIT_ACTION_TYPE) {
            if ($data) {
                $str .= $content . "修改" . $data . ";原" . $oldData . "<br>";
            }
        } else {
            if ($data) {
                $str .= $content . ":" . $data . ";<br>";
            }
        }
        return $str;
    }
    public function getChangeContent($data)
    {
        $str = "";
        if ($data['audit_type'] == 1) {
            $str .= "质控物料信息审核：";
            if ($data['action_type'] == self::EDIT_ACTION_TYPE) {
                $str .= "修改信息：<br>";
                $str .= "物料编号：" . $data['newproduct_no'] . ";物料型号:" . $data['product_name'] .  "<br>";

            } else {
                $str .= "新增信息：<br>";
                $str .= "物料编号：" . $data['newproduct_no'] . ";物料型号:" . $data['product_name'] .  "<br>";
            }

        } else {
            $str .= "财务物料信息审核：";
            if ($data['action_type'] == self::EDIT_ACTION_TYPE) {
                $str .= "修改信息：<br>";
                $str .= "物料编号：" . $data['newproduct_no'] . ";物料型号:" . $data['product_name'] .  "<br>";
            } else {
                $str .= "新增信息：<br>";
                $str .= "物料编号：" . $data['newproduct_no'] . ";物料型号:" . $data['product_name'] .  "<br>";
            }
        }

        $str = self::strConnect($str, $data['newproduct_number'] , $data['oldproduct_number'], '物料名称', $data['action_type']);
        $str = self::strConnect($str, $data['newparent_id'] , $data['oldparent_id'], '分类id',$data['action_type']);
        $str = self::strConnect($str, $data['newwarehouse_number'] , $data['oldwarehouse_number'], '默认库房编号',$data['action_type']);
        $str = self::strConnect($str, $data['newwarehouse_name'] , $data['oldwarehouse_name'], '默认库房',$data['action_type']);
        $str = self::strConnect($str, $data['new_material_type'] , $data['old_material_type'], '物料属性id', $data['action_type']);
        $str = self::strConnect($str, $data['newshipment_flag'] , $data['oldshipment_flag'], '物料属性id', $data['action_type']);
        $str = self::strConnect($str, $data['newperform_flag'] , $data['newperform_flag'], '物料属性id', $data['action_type']);
        $str = self::strConnect($str, $data['newcost'] , $data['oldcost'], '物料成本', $data['action_type']);
        $str = self::strConnect($str, $data['newprice'] , $data['oldprice'], '物料报价', $data['action_type']);
        return $str;

    }


    /**
     * 添加一个产品修改申请
     * @param $params   array   修改后的产品信息
     * @return bool
     */
    public function addProductChangeAudit($params)
    {
        $productModel = new MaterialModel();
        $product = $productModel -> find($params['product_id']);
        $data = array_merge($this->create($params), $this->create($product));
        if ($this->add($data) !== false){
            return true;
        }else{
            return false;
        }
    }


    public function addDataTransform($params)
    {
        $id = $params['parent_id'];
        //获取一级分类
        $params['platform_id'] = M('screen_category')->find($id)['level'] <= 1 ? $id : M('screen_category')->find($id)['pid'];
        $params['cate'] = M('screen_category')->find($id)['name'];
        $params['auditorId']        = explode('_', $params['auditor'])[0];
        $params['auditorName']      = explode('_', $params['auditor'])[1];
        $params['proposer_name']    = session('nickname');
        $params['proposer_id']      = session('staffId');
        $params['createTime']       = time();
        $params['auditType']        = 1;
        $params['actionType']       = self::ADD_ACTION_TYPE;
        return $params;
    }


    /**
     * 添加一个产品新增申请
     * @param $params   array   新增的产品信息
     * @return bool
     */
    public function addProductAddAudit($params)
    {
        $data = [];
        foreach (self::$addMap as $key => $value) {
            if ($params[$key] != null){
                $data[$value] = $params[$key];
            }
        }
        if ($this->add($data) === false){
            $this->error = "添加新物料失败";
            return false;
        }
        return true;
    }

    /**
     * 修改审核状态
     * @param $id
     * @param $status
     * @return bool
     */
    public function changeAuditStatus($id, $status)
    {
        // 审核更新数据
        $data = [
            'change_time' => time(),
            'audit_status' => $status
        ];
        $change = $this->find($id);
        if ($change['audit_status'] != 1){
            $this->error = '该请求已审核完毕';
            return false;
        }

        if ($status == 2){
            $this->startTrans();
            $productModel = new MaterialModel();

            $auditUpdate = $this->where(['id' => ['EQ', $id]]) -> save($data);
            if ($auditUpdate === false) {
                $this->rollback();
                $this->error = '申请通过失败';
                return false;
            }
            // 根据action_type不同执行不同操作
            $productNoIsUnique = $productModel -> productNoIsUnique('product_no',$change['newproduct_no']);
            if ($change['action_type'] == 1 && $productNoIsUnique <= 1) {
                $productUpdate = $productModel -> updateProductInfo($change['product_id'], $change);
                if ($productUpdate === false) {
                    $this->rollback();
                    $this->error = '产品信息更新失败';
                    return false;
                }
            }
            if ($change['action_type'] == 2 && $productNoIsUnique == 0) {
                $productUpdate = $productModel -> addProduct($change);
                if ($productUpdate === false) {
                    $this->rollback();
                    $this->error = '产品信息更新失败';
                    return false;
                }
                $stockModel = new StockModel();
                $addRst = $stockModel->addDataWithMaterialId($productUpdate);
                if ($addRst === false) {
                    $this->rollback();
                    $this->error = '库房数据提交失败';
                    return false;
                }
            }
            $this->commit();
            return true;

        } else {
            $res = $this->where(['id' => ['EQ',$id]]) -> save($data);
            if ($res  !== false){
                return true;
            }else{
                $this->error = '修改失败';
                return false;
            }
        }
    }

    /**
     * 添加多个产品修改审核
     * @param $params
     * @return bool
     */
    public function addProductChangeAuditMulti($params)
    {
        $this->startTrans();
        $productModel = new MaterialModel();
        $data = [];
        foreach ($params as $key => $value) {
            $product = $productModel -> find($value['product_id']);
            $data[] = array_merge($this->create($value), $this->create($product));
        }
        if ($this->addAll($data) !== false){
            $this->commit();
            return true;
        }else{
            $this->rollback();
            return false;
        }
    }

    /**
     * 更新新增信息
     * @param $params   更新后的新增请求
     * @param $id
     * @return bool
     */
    public function updateAddAudit($params, $id)
    {
        $parent_id = $params['parent_id'];
        //获取一级分类
        $params['newplatform_id'] = M('screen_category')->find($parent_id)['level'] <= 1 ? $parent_id : M('screen_category')->find($parent_id)['pid'];
        $params['cate'] = M('screen_category') -> find($parent_id)['name'];
        $params['auditor_id'] = explode('_',$params['auditor'])[0];
        $params['auditor_name'] = explode('_',$params['auditor'])[1];
        $params['proposer_name'] = session('nickname');
        $params['proposer_id'] = session('staffId');
        $params['create_time'] = time();
        $params['audit_status'] = 1;
        $data = [];
        foreach (self::$addMap as $key => $value) {
            if ($params[$key] != null){
                $data[$value] = $params[$key];
            }
        }
        if ($this->where(['id' => ['EQ',$id]])->save($data) === false){
            return false;
        }
        return true;
    }

    public function checkUniqueApply($productId)
    {
        $map['product_id'] = ['EQ', $productId];
        $map['audit_status'] = ['eq', 1];
        return $this->where($map)->find();
    }

    public function addEditDataTransform($data, $auditor, $material)
    {
        $auditorArr     = explode('_', $auditor);
        $auditorId   = $auditorArr[0];
        $auditorName = $auditorArr[1];
        $time        = time();
        $data['newplatform_id'] = M('screen_category')->find($data['cate_num'])['level'] <= 1 ? $data['cate_num'] : M('screen_category')->find($data['cate_num'])['pid'];
        $data['newcate'] = M('screen_category') -> find($data['cate_num'])['name'];
        $data['newproduct_no'] = $material['product_no'];
        $data['changemanname'] = session('nickname');
        $data['changemanid']   = session('staffId');
        $data['create_time']   = $time;
        $data['auditor_id']    = $auditorId;
        $data['auditor_name']  = $auditorName;
        $data['actionType']    = self::EDIT_ACTION_TYPE;
        $data['auditType']     = 1;
        return $data;
    }



    /**
     * 新增编辑物料信息的请求，审核后有效
    */
    public function addEditMaterialTrans($params)
    {
        $productModel = new MaterialModel();

        foreach ($params['data'] as $key => &$value) {
            // 填充数据, 检查未变更的数据
            $material =  $productModel->find($value['product_id']);
            $rst = $this->checkUniqueApply($value['product_id']);
            if ($rst) {
                return dataReturn($material['product_no'] . "有未处理修改申请，请修改后再来提交新变更内容", self::FAIL_STATUS);
            }
            $value = $this->addEditDataTransform($value, $params['auditor'], $material);

            $eq = $material['warehouse_id'] == $value['warehouse_id'] && $material['material_type'] == $value['material_type'] && $material['product_number'] == $value['product_num'] && $material['parent_id'] == $value['cate_num'];
            if ($eq) {
                unset($params['data'][$key]);
            }
        }

        if (count($params['data']) == 0){
            return dataReturn( "未做更改", self::FAIL_STATUS);
        }else{
            $res = $this->addProductChangeAuditMulti(array_values($params['data']));
            if ($res === false) {
                return dataReturn( "修改失败", self::FAIL_STATUS);
            } else {
                return dataReturn( "成功", self::SUCCESS_STATUS);
            }
        }
    }

    public function checkDeleteAuth($id)
    {
        $map['id'] = ['EQ', (int)$id];
        $info = $this->where($map)->field("*")->find();
        if ($info['audit_status'] == 2) {
            $this->error = "已审核数据禁止删除";
            return false;
        }
        if ($info['changemanid'] != session('staffId') && $info['auditor_id'] != session('staffId')) {
            $this->error = "非提交人和审核人不允许删除";
            return false;
        }
        return true;
    }

    public function deleteRecord($id)
    {
        return $this->delete($id);
    }


    public function getBaseInfoWithJoin($id)
    {
        return $this
            -> field('audit.*, newcate.name newname, oldcate.name oldname')
            -> alias('audit')
            -> join('left join crm_screen_category as newcate on audit.newparent_id = newcate.id')
            -> join('left join crm_screen_category as oldcate on audit.oldparent_id = oldcate.id')
            -> where(['audit.id' => (int)$id])
            -> find();
    }

    public function checkEditAuth($id)
    {
        $map['id'] = ['EQ', (int)$id];
        $info = $this->where($map)->field("*")->find();
        if ($info['audit_status'] == 2) {
            $this->error = "已审核数据禁止编辑";
            return false;
        }
        if ($info['changemanid'] != session('staffId') && $info['auditor_id'] != session('staffId')) {
            $this->error = "非提交人和审核人不允许修改";
            return false;
        }
        return true;
    }

    public function editRecord($param, $id)
    {
        $map = ['id' => ['EQ', (int)$id]];
        $param['audit_status'] = 1;
        $param['update_time'] = time();
        $param['newcate'] = M('screen_category') -> find($param['newparent_id'])['name'];
        return $res = $this->where($map)->save($param);
    }
}