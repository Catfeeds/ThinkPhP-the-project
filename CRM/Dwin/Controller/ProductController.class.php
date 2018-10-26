<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/26
 * Time: 13:18
 */
namespace Dwin\Controller;

use Dwin\Model\MaterialBomSubModel;
use Dwin\Model\MaterialModel;
use Dwin\Model\ProductAddAuditModel;
use Dwin\Model\ProchangerecordModel;
use Dwin\Model\ProductionOrderModel;
use Dwin\Model\RepertorylistModel;
use Dwin\Model\ScreenCategoryModel;
use Dwin\Model\StockModel;
use Think\Exception;

class ProductController extends CommonController
{
    const SUCCESS_STATUS = 200;
    const FAIL_STATUS = -1;
    const AUDIT_ROLE = [1,2,34,39];
    const EDIT_ACTION_TYPE = 1; // 代表修改操作
    const ADD_ACTION_TYPE = 2;  // 代表新增操作

    //添加分类
    public function addCategory()
    {
        if (IS_POST) {
            $posts = I('post.');
            $map['id'] = array('EQ', (int)$posts['id']);
            $levelInfo = M('screen_category')->where($map)->field('level')->find();
            $data = array(
                'name'          => $posts['name'],
                'pid'           => $posts['id'],
                'level'         => $levelInfo['level'] + 1,
//                'product_id'    => 1,
//                'performance'   => $posts['performance'],
            );
            $rst = M('screen_category')->add($data);
            $msg = $rst ? 1 : 2;
            $this->ajaxReturn($msg);
        } else {
            $screenData  = M('screen_category')->field('id,name,pid')->select();
            $screenData  = getTree($screenData, 0, 0, 'pid');
            $this->assign(array(
                'screenData'  => $screenData,
            ));
            $this->display();
        }
    }
    /**
     * 请求产品列表（已更新）
     */
    public function getProduct()
    {
        if (IS_POST){
            $params = I('post.');
            $_map = [
                'product_id' => 'product.product.id',
            ];
            $mapTableData = $this->dataTable($params, $_map);
            $model = new MaterialModel();
            $data['draw'] = (int) $params['draw'];

            $data['recordsTotal'] = $model -> count();

            $status = isset($params['status']) ? $params['status'] : 0;
            switch ($status){
                case MaterialModel::STATUS_ACTIVE:
                    $mapTableData['map']['status'] = MaterialModel::STATUS_ACTIVE;
                    break;
                case MaterialModel::STATUS_FORBIDDEN:
                    $mapTableData['map']['status'] = MaterialModel::STATUS_FORBIDDEN;
                    break;
                default :
                    break;
            }

            $data['recordsFiltered'] = $model -> indexCount($mapTableData['map']);
            $data['data'] = $model -> productIndex($mapTableData['map'], $params['start'], $params['length'], $mapTableData['order']);

            $this->ajaxReturn($data);
        }else{
            $auditor = $this->getAuditor(self::AUDIT_ROLE);
            $screenData  = M('screen_category')->field('id,name,pid')->select();
            $screenData  = getTree($screenData, 0, 0, 'pid');
            $warehouse  = M('repertorylist')->field('rep_id,repertory_name')->select();
            $this->assign(compact('screenData', 'warehouse', 'auditor'));
            $this->display();
        }
    }

    /**
     * 物料禁用
     */
    public function productForbidden(){
        if(IS_POST){
            $productId = I("post.product_id");
            if(empty($productId)){
                $this->returnAjaxMsg("参数不全",400);
            }

            // 判断当前物料是否存在在bom中
            $bomModel = new MaterialBomSubModel();
            $bomData = $bomModel->field("bom_pid")->where(['product_id' => $productId, "is_del" => MaterialBomSubModel::NO_DEL])->select();

            if(!empty($bomData)){

                return dataReturn("当前物料在有效bom中有被使用，无法禁用",400);

                $bomPidStr = implode(',', array_column($bomData,"bom_pid"));
                $map['bom_pid'] = ['in', $bomPidStr];
                $map['is_del'] = ['eq', ProductionOrderModel::$notDel];

                $productModel = new ProductionOrderModel();
                $productData = $productModel->where($map)->select();
                if(!empty($productData)){
                    return dataReturn("当前物料在生产计划中有被使用，无法禁用",400);
                }
            }

            $materialModel = new MaterialModel();
            $res = $materialModel->where(['product_id' => $productId])->setField(['status' => MaterialModel::STATUS_FORBIDDEN]);
            if($res === false){
                $this->returnAjaxMsg("物料禁用失败",400);
            }
            $this->returnAjaxMsg("物料禁用成功",200);
        }else {
            die("非法");
        }
    }

    /**
     * 查看修改产品的请求
     * @param $mySearch     mySearch方法返回值
     */
    public function getEditRequest()
    {
        if(IS_POST){
            $params = I('post.');
            $mapTableData = $this->dataTable($params);
            $model = new ProchangerecordModel();
            $map = $this->mySearch($params['mySearch']);
            if ($map['action_type'][1] == 'all'){
                unset($map['action_type']);
            }
            $mapTableData['map'] = array_merge($map, $mapTableData['map']);
            $data['draw'] = (int) $params['draw'];
            $data['recordsTotal'] = $model  -> where($map)-> count();
            $data['recordsFiltered'] = $model -> where($mapTableData['map']) -> count();
            $data['data'] = $model -> index($mapTableData['map'], $params['start'], $params['length'], $mapTableData['order']);
            foreach ($data['data'] as $key => &$value) {
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $value['product_name'] = !empty($value['product_name']) ? $value['product_name'] : $value['newproduct_name'];
                $value['info'] = $model->getChangeContent($value);
            }
            $this->ajaxReturn($data);
        }else{
            $this->display();
        }
    }

    /**
     * 查看当前登录用户的修改产品的请求
     * @param $mySearch     mySearch方法返回值
     */
    public function getMyEditRequest()
    {
        if(IS_POST){
            $params = I('post.');
            $mapTableData = $this->dataTable($params);
            $model = new ProchangerecordModel();
            $map = $this->mySearch($params['mySearch']);
            if ($map['action_type'][1] == 'all'){
                unset($map['action_type']);
            }
            $mapTableData['map'] = array_merge($map, $mapTableData['map']);
            $data['draw'] = (int) $params['draw'];
            $data['recordsTotal'] = $model  -> where($map)-> count();
            $data['recordsFiltered'] = $model -> where($mapTableData['map']) -> count();
            $data['data'] = $model -> index($mapTableData['map'], $params['start'], $params['length'], $mapTableData['order']);

            foreach ($data['data'] as $key => &$value) {
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $value['product_name'] = !empty($value['product_name']) ? $value['product_name'] : $value['newproduct_name'];
                $value['info'] = $model->getChangeContent($value);
            }
            $this->ajaxReturn($data);
        }else{
            $this->display('getMyEditRequest');
        }
    }


    /**
     * 新增新增产品申请
     */
    public function postAddProductRequest()
    {
        $materialCateModel = new ScreenCategoryModel();
        $screenData  = $materialCateModel->getBaseInfo();
        if (IS_POST) {
            $params = I('post.');
            $this->checkProductNoIsUnique($params['product_no']);
            $auditModel = new ProchangerecordModel();
            $data = $auditModel->addDataTransform($params);
            $res  = $auditModel->addProductAddAudit($data);
            if ($res !== false) {
                $this->returnAjaxMsg('提交新物料信息成功，审核后生效', self::SUCCESS_STATUS);
            } else {
                $this->returnAjaxMsg($auditModel->getError(), self::FAIL_STATUS);
            }
        } else {
            $repertoryModel = new RepertorylistModel();
            $screenData  = getTree($screenData, 0, 0, 'pid');
            $repoList    = $repertoryModel->getRepBaseInfo();
            $auditor     = $this->getAuditor(self::AUDIT_ROLE);
            $this->assign(compact('screenData', 'repoList', 'auditor'));
            $this->display();
        }
    }

    /**
     * 新增修改产品请求
     */
    public function postEditProductRequest()
    {
        $params = I('post.');
        $changeRecordModel = new ProchangerecordModel();
        $productModel = new MaterialModel();

        foreach ($params['data'] as $key => &$value) {
            // 填充数据, 检查未变更的数据
            $material =  $productModel->find($value['product_id']);
            $rst = $changeRecordModel->checkUniqueApply($value['product_id']);
            if ($rst) {
                $this->returnAjaxMsg($material['product_no'] . "有未处理修改申请，请修改后再来提交新变更内容", self::FAIL_STATUS);
            }
            $value = $changeRecordModel->addEditDataTransform($value, $params['auditor'], $material);

            $eq = $material['warehouse_id'] == $value['warehouse_id'] && $material['material_type'] == $value['material_type'] && $material['product_number'] == $value['product_num'] && $material['parent_id'] == $value['cate_num'];
            if ($eq) {
                unset($params['data'][$key]);
            }
        }
        if (count($params['data']) == 0) {
            $this->returnAjaxMsg( "未做更改", self::FAIL_STATUS);
        }else{
            $res = $changeRecordModel->addProductChangeAuditMulti(array_values($params['data']));
            if ($res === false) {
                $this->returnAjaxMsg( "修改失败", self::FAIL_STATUS);
            } else {
                $this->returnAjaxMsg( "成功", self::SUCCESS_STATUS);
            }
        }
    }

    /**
     * 审核新增产品请求
     * @param $id  int  请求id
     * @param $status   状态信息
     */
    public function patchAddProductRequest()
    {
        $model = new ProchangerecordModel();
        if(IS_POST){
            $status = (int)I('post.status');
            $id = (int)I('post.id');
            $res = $model -> changeAuditStatus($id, $status);
            if ($res) {
                $this->returnAjaxMsg('审核新增物料请求成功', self::SUCCESS_STATUS);
            } else {
                $this->returnAjaxMsg($model->getError(), self::FAIL_STATUS);
            }
        }
    }

    /**
     * 审核修改产品请求
     * @param $id int 请求id
     * @param   $status     状态信息
     */
    public function patchEditProductRequest()
    {
        $model = new ProchangerecordModel();
        if(IS_POST){
            $status = (int)I('post.status');
            $id     = (int)I('post.id');
            $res = $model->changeAuditStatus($id, $status);
            if ($res) {
                $this->returnAjaxMsg('审核成功', self::SUCCESS_STATUS);
            } else {
                $this->returnAjaxMsg( $model->getError(), self::FAIL_STATUS);
            }
        }
    }

    /**
     * 修改新增产品请求内容
     * @param $id int 请求id
     */
    public function putAddProductRequest($id)
    {
        $materialCateModel = new ScreenCategoryModel();
        $screenData  = $materialCateModel->getBaseInfo();
        if (IS_POST) {
            $params = I('post.');
            $auditModel = new ProchangerecordModel();
            $checkRst = $auditModel->checkEditAuth($id);
            if ($checkRst === false) {
                $this->returnAjaxMsg($auditModel->getError(),self::FAIL_STATUS);
            }
            $res = $auditModel -> updateAddAudit($params, $id);
            if ($res != false) {
                $this->returnAjaxMsg('提交申请成功,等待审核通过', self::SUCCESS_STATUS);
            } else {
                $this->returnAjaxMsg($auditModel->getError(), self::FAIL_STATUS);
            }
        } else {
            $screenData  = getTree($screenData, 0, 0, 'pid');
            $repoList = M('repertorylist') -> select();
            $product = M('prochangerecord') -> find($id);
            $auditor = $this->getAuditor(self::AUDIT_ROLE);
            $url = U('', compact('id'));
            $this->assign(compact('screenData', 'repoList', 'auditor', 'product', 'url'));
            $this->display();
        }
    }

    /**
     * 修改修改产品请求内容
     * @param $id int 请求id
     */
    public function putEditProductRequest($id)
    {
        $model = new ProchangerecordModel();
        $new = $model->getBaseInfoWithJoin($id);
        if(IS_POST){
            $params = I('post.');
            $checkRst = $model->checkEditAuth($id);
            if ($checkRst === false) {
                $this->returnAjaxMsg($model->getError(),self::FAIL_STATUS);
            }
            $editRst = $model->editRecord($params, $id);

            if ($editRst !== false){
                $this->returnAjaxMsg('修改成功',self::SUCCESS_STATUS);
            } else {
                $this->returnAjaxMsg('修改失败',self::FAIL_STATUS);
            }
        } else {
            $materialCateModel = new ScreenCategoryModel();
            $cate = $materialCateModel->getBaseInfo();
            $repoList = M('repertorylist') -> select();
            $new['create_time'] = date('Y-m-d H:i:s', $new['create_time']);
            $url = U('', compact('id'));
            $this->assign(compact('new', 'old', 'cate', 'repoList', 'url'));
            $this->display();
        }
    }


    /**
     * 删除修改产品请求
     * @param $id  int  请求id
     */
    public function deleteEditProductRequest($id)
    {
        if (IS_POST){
            $changeModel = new ProchangerecordModel();
            $checkRst = $changeModel->checkDeleteAuth($id);
            if ($checkRst === false) {
                $this->returnAjaxMsg($changeModel->getError(),self::FAIL_STATUS);
            }
            $delRst = $changeModel->deleteRecord($id);

            if ($delRst!== false){
                $this->returnAjaxMsg('删除成功',self::SUCCESS_STATUS);
            } else {
                $this->returnAjaxMsg('删除失败',self::FAIL_STATUS);
            }
        }
    }



    /**
     * 检查物料代码是否重复
     * @param $productNo
     * @param $type     string      分add和edit 2种情况
     * @return bool
     */
    public function checkProductNoIsUnique($productNo)
    {
        $proChangeModel = new ProchangerecordModel();
        $count = M('material')->where(['product_no' => ['EQ', $productNo]]) -> count();

        if ($count != 0) {
            $this->ajaxReturn([
                'status' => self::FAIL_STATUS,
                'msg' => '物料代码重复',
            ]);

            $map['newproduct_no'] = ['eq', $productNo];
            $map['check_status']  = ['eq' , 1];
            $data = $proChangeModel->where($map)->find();
            if ($data) {
                $this->ajaxReturn([
                    'status' => self::FAIL_STATUS,
                    'msg' => '提交的记录对应的物料代码存在待处理情况，请处理后再提交新变更/新增记录'
                ]);
            }
        }


    }

    protected function dataTable($params, $_map = [])
    {
        $dataField = [];
        $searchAble = [];
        foreach ($params['columns'] as $k => $v) {
            if (isset($_map[$v['data']])){
                $dataField[] = $_map[$v['data']];
            }else{
                $dataField[] = $v['data'];
            }
            if ($v['searchable'] == 'true'){
                if (isset($_map[$v['data']])){
                    $searchAble[] = $_map[$v['data']];
                }else{
                    $searchAble[] = $v['data'];
                }
            }
        }
        $order = $dataField[$params['order'][0]['column']] . ' ' . $params['order'][0]['dir'];
        if ($params['search']['value'] == ''){
            $map = [];
        }else{
            $searchAble = rtrim(implode('|', $searchAble), '|');
            $word = $params['search']['value'];
            $map = [$searchAble => ['LIKE',"%".$word."%"]];
        }
        return [
            'order' => $order,
            'map' => $map,
        ];
    }

    /**
     * 组装datatables前端自定义搜索参数
     * @param $mySearch
     * @return array
     */
    protected function mySearch($mySearch)
    {
        $arr = [];
        foreach ($mySearch as $key => $value) {
            if ($value == 'myID') {
                $value = session('staffId');
            }
            if ($value == 'myName') {
                $value = session('nickname');
            }
            $arr[$key] = ['EQ', $value];
        }
        return $arr;
    }

    /**
     * 根据仓库id获取仓库名称
     * @param $id int 仓库id
     * @return  string  仓库名
     */
    protected function getWarehouseName($id)
    {
        $res = M('repertorylist') -> find($id);
        return $res['repertory_name'];
    }

    /**
     * 根据角色查询对应的所有职员
     * @param $role_ids mixed   role_id的集合,可以是数组或者字符串
     * @return array    符合条件的职员
     */
    protected function getAuditor($role_ids)
    {
        $res = M('auth_role') -> where(['role_id' => ['IN', $role_ids]]) -> select();
        $role_ids = [];
        foreach ($res as $key => $value) {
            $role_ids = array_merge(explode(',',$value['staff_ids']), $role_ids);
        }
        $map = ['id' => ['IN', $role_ids], 'loginstatus' => ['NEQ','1']];
        $staffs = M('staff') -> field('id, name') -> where($map) -> select();
        return $staffs;
    }


    /**
     * 保存文件信息导入的Excel
     * @throws \PHPExcel_Reader_Exception
     */
    public function productExcelUpload()
    {
        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/fileUpload/" . "temp/";
        if (!file_exists($rootPath)) {
            mkdir($rootPath);
        }
        $saveName = uniqid('productExcel');
        $cfg = [
            'autoSub' => false,
            'saveName' => $saveName,
            'rootPath' => $rootPath, // 保存根路径
            'replace'  => true,
        ];
        $upload = new \Think\Upload($cfg);// 实例化上传类

        // 上传单个文件
        $info   =   $upload->uploadOne($_FILES['file']);
        if(!$info) {// 上传错误提示错误信息
            $this->ajaxReturn([
                'status' => self::FAIL_STATUS,
                'msg' => $upload->getError(),
            ]);
        }else{// 上传成功 获取上传文件信息
            $this->importProductInfoFromExcel($rootPath . $saveName . '.' . $info['ext']);
        }
    }

    /**
     * 从Excel中导入产品信息到product_import_temp
     * @param $filePath
     * @throws \PHPExcel_Reader_Exception
     */
    public function importProductInfoFromExcel($filePath)
    {
        Vendor('PHPExcel.PHPExcel');//引入类
        $reader = \PHPExcel_IOFactory::createReader('Excel2007');
        $PHPExcel = $reader->load($filePath); // 文档名称
        $sheetData = $PHPExcel->getActiveSheet()->toArray(null, true, true, true);
        $sheetData = array_values($sheetData);
        $titleArr = array_shift($sheetData);
        $newTitleArr = [];
        $map = ['product_name' => '产品名唯一', 'platform_id' => '关联分类表id', 'cate' => '默认存的', 'parent_id' => '关联分类表 level=2级的id', 'price' => '报价（单件）', 'cost' => '成本', 'performance' => '单件业绩', 'batch_price1' => '报价（批量）', 'product_number' => '产品名不唯一', 'statistics_shipments_flag' => '是否统计出货量', 'statistics_performance_flag' => '是否统计业绩', 'stock_number' => '库存数量', 'production_number' => '正在生产数量', 'rework_number' => '正在返工数量', 'update_time' => '更新时间', 'warehouse_name' => '仓库名', 'warehouse_number' => '仓库编号', 'i_audit' => '待入库', 'o_audit' => '待出库', 'mouth_i_stock' => '月入库', 'mouth_o_stock' => '月出库', 'safety_number' => '安全数量', 'product_no' => '物料编号', 'standby_number' => '备库数量', 'tips' => '备注', 'out_processing' => '出库中数量',];
        $cateArrTemp = M('screen_category') -> select();
        $cateArr = [];
        foreach ($cateArrTemp as $key => $value) {
            $cateArr[$value['id']] = $value['name'];
        }
        foreach ($map as $key => $value) {
            $index = array_search($value, $titleArr);
            if ($index !== false){
                $newTitleArr[$index] = $key;
            }
        }
        $data = [];
        $uid = session('staffId');
        $batch = time();
        foreach ($sheetData as $key1 => $value1) {
            $data[$key1] = [];
            foreach ($value1 as $key2 => $value2) {
                if ($newTitleArr[$key2]){
                    $data[$key1][$newTitleArr[$key2]] = $value2;
                }
            }
            $data[$key1]['uid'] = $uid;
            $data[$key1]['parent_name'] = $cateArr[$data[$key1]['parent_id']];
            $data[$key1]['batch'] = $batch;
        }
        unlink($filePath);
        $res = M('product_import_temp') -> addAll($data) === false ? false : true;
        $temp = M('product_import_temp') -> field('batch') -> where(['uid' => ['EQ', session('staffId')]]) -> group('batch') -> select();
        $batches = [];
        foreach ($temp as $key => $value) {
            $batches[] =  date('Y-m-d H:i:s', $value['batch']);
        }
        if ($res){
            $this->ajaxReturn([
                'status' => self::SUCCESS_STATUS,
                'msg' => '上传成功',
                'data' => compact('batches')
            ]);
        }else{
            $this->ajaxReturn([
                'status' => self::FAIL_STATUS,
                'msg' => '上传失败',
            ]);
        }

    }

    /**
     *  从Excel中导入产品信息到product_import_temp后选择导入数据的主页面
     */
    public function importProductInfo()
    {
        if (IS_POST){
            $params = I('post.');
            $mapTableData = $this->dataTable($params);
            $mapTableData['map']['batch'] = strtotime($params['batch']);
            $model = M('product_import_temp');
            $data['draw'] = (int)$params['draw'];
            $data['recordsTotal'] = $model->count();
            $data['recordsFiltered'] = $model->where($mapTableData['map'])->count();
            $data['data'] = $model
                -> where($mapTableData['map'])
                -> order($mapTableData['order'])
                -> limit($params['start'], $params['length'])
                -> select();
            $productModel = new MaterialModel();
            foreach ($data['data'] as $key => &$value) {
                $value['batch'] = date('Y-m-d H:i:s', $value['batch']);
                $value['method_type'] =  $productModel -> where(['product_no' => ['EQ', $value['product_no']]]) -> count() == 0 ? '新增' : '修改';
            }
            $this->ajaxReturn($data);
        }else{
            $temp = M('product_import_temp') -> field('batch') -> where(['uid' => ['EQ', session('staffId')]]) -> group('batch') -> select();
            $batches = [];
            foreach ($temp as $key => $value) {
                $batches[] =  date('Y-m-d H:i:s', $value['batch']);
            }
            $this->assign(compact('batches'));
            $this->display();
        }
    }

    /**
     * 根据id删除Excel导入产品数据行
     * @param $id
     */
    public function delProductImportItem($id)
    {
        $res = M('product_import_temp') -> delete($id);
        if ($res == false){
            $this->ajaxReturn([
                'status' => self::FAIL_STATUS,
                'msg' => '删除失败'
            ]);
        }else{
            $this->ajaxReturn([
                'status' => self::SUCCESS_STATUS,
                'msg' => '删除成功'
            ]);
        }
    }

    /**
     *  根据上传批次删除对应Excel导入产品数据行
     */
    public function delProductImportBatch()
    {
        $map = [
            'uid' => ['EQ', session('staffId')],
            'batch' => ['EQ', strtotime(I('post.batch'))],
        ];
        $res = M('product_import_temp') -> where($map) -> delete();
        if ($res === false){
            $this->ajaxReturn([
                'status' => self::FAIL_STATUS,
                'msg' => '删除失败'
            ]);
        }else{
            $this->ajaxReturn([
                'status' => self::SUCCESS_STATUS,
                'msg' => '删除成功'
            ]);
        }
    }

    /**
     * 将选中批次的产品导入数据导入到产品表
     */
    public function saveImportProductInfo()
    {
        $map = [
            'uid' => ['EQ', session('staffId')],
            'batch' => ['EQ', strtotime(I('post.batch'))],
        ];
        $importModel = M('product_import_temp');
        $data = $importModel-> where($map) -> select();
        $productModel = new MaterialModel();
        $productModel -> startTrans();
        $productUpdate = true;
        foreach ($data as $key => $value) {
            $map = [
                'product_no' => ['EQ', $value['product_no']]
            ];
            foreach ($value as $key1 => $value1) {
                if ($value == ''){
                    unset($value[$key1]);
                }
            }
            $isAdd = $productModel -> where($map) -> count() == 0 ? true : false;
            if ($isAdd){
                $productUpdate = $productModel -> add($value) ;
            }else{
                $productUpdate = $productModel -> where($map) -> save($value);
            }
            if ($productUpdate === false){
                break;
            }
        }
        $importUpdate = $importModel -> where($map) -> delete();
        if ($productUpdate !== false && $importUpdate !== false){
            $productModel -> commit();
            $status = self::SUCCESS_STATUS;
            $msg = '更新成功';
        }else{
            $productModel -> rollback();
            $status = self::FAIL_STATUS;
            if ($productUpdate === false){
                $msg = '产品更新失败';
            }else{
                $msg = '临时存储数据删除失败';
            }
        }
        $this->ajaxReturn([
            'status' => $status,
            'msg' => $msg
        ]);
    }

    /**
     * 将产品信息导出到Excel
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function exportProductInfo()
    {
        $map = ['product_name' => '产品名唯一', 'platform_id' => '关联分类表id', 'cate' => '默认存的', 'parent_id' => '关联分类表 level=2级的id', 'price' => '报价（单件）', 'cost' => '成本', 'performance' => '单件业绩', 'batch_price1' => '报价（批量）', 'product_number' => '产品名不唯一', 'statistics_shipments_flag' => '是否统计出货量', 'statistics_performance_flag' => '是否统计业绩', 'stock_number' => '库存数量', 'production_number' => '正在生产数量', 'rework_number' => '正在返工数量', 'update_time' => '更新时间', 'warehouse_name' => '仓库名', 'warehouse_number' => '仓库编号', 'i_audit' => '待入库', 'o_audit' => '待出库', 'mouth_i_stock' => '月入库', 'mouth_o_stock' => '月出库', 'safety_number' => '安全数量', 'product_no' => '物料编号', 'standby_number' => '备库数量', 'tips' => '备注', 'out_processing' => '出库中数量',];
        $title = [];
        $field = '';
        foreach ($map as $key => $value) {
            $title[] = $value;
            $field .= $key . ',';
        }
        $field = rtrim($field, ',');
        $letterArr = range('A', 'Z');
        Vendor('PHPExcel.PHPExcel');//引入类
        $excel = new \PHPExcel();
        $excel->getProperties()
            ->setCreator(session('nickname'))
            ->setLastModifiedBy("Dwin")
            ->setTitle("DWIN_STAFF_INFO")
            ->setSubject("statistics")
            ->setDescription("staff_info")
            ->setKeywords("statistics")
            ->setCategory("人员信息");
        $excel->getSecurity()->setLockWindows(true);
        $excel->getSecurity()->setLockStructure(true);
        $excel->getSecurity()->setWorkbookPassword("dwin_set_2002_hunan_beijing");
        $sheet = $excel -> getActiveSheet();
        foreach ($title as $key => $value) {
            $sheet->setCellValue($letterArr[$key] . 1,$value);
        }
        $data = M('material')
            -> field($field)
             -> page(1,10)
            -> select();
        foreach ($data as $key1 => $value1) {
            $value1 = array_values($value1);
            foreach ($value1 as $key2 => $value2) {
                $sheet->setCellValue($letterArr[$key2] . ($key1 + 2), $value2);
            }
        }
        $objwriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $fileName = uniqid('productInfo'). '_' . date('YmdH') . '.xlsx';
        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/excel/";
        $objwriter->save($rootPath . $fileName);
        $this->returnAjaxMsg('导出成功',self::SUCCESS_STATUS, UPLOAD_ROOT_PATH  . '/excel/' . $fileName);
    }

    // ===========================

    /**
     * 展示页面  里面有上传Excel 和 下载 excel
     */
    public function importProductInfoEx(){
        if (IS_POST){
            $params = I('post.');
            $mapTableData = $this->dataTable($params);
            $mapTableData['map']['batch'] = strtotime($params['batch']);
            $mapTableData['map']['is_del'] = ['eq', 0];
            $model = M('material_excel_info');
            $data['draw'] = (int)$params['draw'];
            $data['recordsTotal'] = $model->count();
            $data['recordsFiltered'] = $model->where($mapTableData['map'])->count();
            $data['data'] = $model
                -> where($mapTableData['map'])
                -> order($mapTableData['order'])
                -> limit($params['start'], $params['length'])
                -> select();
            $productModel = new MaterialModel();

            // 库房列表
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();
            $repIdArr = array_column($repMap,'repertory_name', "rep_id");

            foreach ($data['data'] as $key => &$value) {
                $value['batch'] = date('Y-m-d H:i:s', $value['batch']);
                $value['method_type'] =  $productModel -> where(['product_no' => ['EQ', $value['product_no']]]) -> count() == 0 ? '新增' : '修改';
                $value['warehouse_name'] = $repIdArr[$value['warehouse_id']];
            }
            unset($value);
            $this->ajaxReturn($data);
        }else{
            $temp = M('material_excel_info') -> field('batch') -> where(['uid' => ['EQ', session('staffId')], 'is_del' => ['eq', 0]]) -> group('batch') -> select();
            $batches = [];
            foreach ($temp as $key => $value) {
                $batches[] =  date('Y-m-d H:i:s', $value['batch']);
            }
            $this->assign(compact('batches'));
            $this->display();
        }
    }

    /**
     * 保存文件信息导入的Excel
     * @throws \PHPExcel_Reader_Exception
     */
    public function productExcelUploadEx()
    {
        $data = self::importExcel($_FILES['file']);
        if($data['status'] != 200){
            $this->ajaxReturn($data);
        }
        $result = self::readExcelToShow($data['data']);
        $this->ajaxReturn($result);
    }

    /**
     * 读取Excel信息存入crm_material 中，当前弃用
     * @param $filePath
     * @return array
     * @throws \PHPExcel_Reader_Exception
     */
    public function readExcelToSystem($filePath){
        Vendor('PHPExcel.PHPExcel');//引入类
        $reader = \PHPExcel_IOFactory::createReader('Excel2007');
        $PHPExcel = $reader->load($filePath); // 文档名称
        $sheetData = $PHPExcel->getActiveSheet()->toArray(null, true, true, true);
        $sheetData = array_values($sheetData);
        unset($sheetData[0]);  //删除标题，有一些不需要删除，目前不知道为什么

        if(empty($sheetData)){
            return dataReturn("Excel中数据为空",400);
        }
        $productNoExcel = array_unique(array_column($sheetData, 'A'));

        $materialModel = new MaterialModel();
        $stockModel = new StockModel();
//        $map['product_no'] = ['in', "'" . implode("','",$productNoExcel) . "'"];
        $map['product_no'] = ['in', implode(",",$productNoExcel)];
        $materialData = $materialModel->field('product_no,product_number,product_name,material_type,warehouse_id')->where($map)->select();
        $productNoSystem = array_column($materialData,"product_no");
        if(empty($productNoSystem)){
            $productNoSystem = [];
        }

        $productNo = array_diff($productNoExcel,$productNoSystem);

        // 读取库房信息
        $repertoryListModel = new RepertorylistModel();
        $repMap = $repertoryListModel->getStockOutList();
        $repIdArr = array_column($repMap,'rep_id');

        $materialModel->startTrans();
        try{
            foreach ($sheetData as $key => $value){
                $material = [
                    'product_no' => (string)$value['A'],
                    'product_number' => $value['B'],
                    'product_name' => $value['C'],
                    'material_type' => $value['D'],
                    'warehouse_id' => $value['E'],
                ];

                if(in_array($material['product_no'], $productNoExcel)){
                    $productNoExcel = array_merge(array_diff($productNoExcel, array($material['product_no'])));
                    if(!in_array($material['warehouse_id'],$repIdArr)){
                        $materialModel->rollback();
                        return dataReturn("库房不存在", 400);
                    }

                    if(in_array($material['product_no'], $productNo)){
                        // 当前物料新增
                        $productId = $materialModel->add($material);
                        if(empty($productId)){
                            $materialModel->rollback();
                            return dataReturn($materialModel->getError(), 400);
                        }

                        $addStockArr = [];
                        foreach ($repMap as $k => $v){
                            if($v['rep_id'] != $value['E']) {
                                $addStockArr[] = [
                                    'product_id' => $productId,
                                    "warehouse_number" => $v['rep_id'],
                                    'warehouse_name' => $v['repertory_name'],
                                    "stock_number" => 0,
                                    'o_audit' => 0,
                                    'i_audit' => 0,
                                    'out_processing' => 0,
                                    "update_time" => time()
                                ];
                            }else {
                                $addStockArr[] = [
                                    'product_id' => $productId,
                                    "warehouse_number" => $v['rep_id'],
                                    'warehouse_name' => $v['repertory_name'],
                                    "stock_number" => $value['F'],
                                    'o_audit' => 0,
                                    'i_audit' => 0,
                                    'out_processing' => 0,
                                    "update_time" => time()
                                ];
                            }
                        }

                        $stockRes = $stockModel->addAll($addStockArr);
                        if(!$stockRes){
                            $materialModel->rollback();
                            return dataReturn($stockModel->getError(), 400);
                        }
                    }else {
                        // 修改当前物料
                        $materialOneData = $materialModel->where(['product_no' => $value['A']])->find();
                        if($materialOneData['product_number'] != $material['product_number'] || $materialOneData['product_name'] != $material['product_name'] || $materialOneData['material_type'] != $material['material_type'] || $materialOneData['warehouse_id'] != $material['warehouse_id']){
                            $materialRes = $materialModel->where(["product_id" => $materialOneData['product_id']])->setField($material);
                            if(!$materialRes){
                                $materialModel->rollback();
                                return dataReturn($materialModel->getError(), 400);
                            }
                        }

                        $whereMap = [];
                        $whereMap['product_id'] = ['eq', $materialOneData['product_id']];
                        $whereMap['warehouse_number'] = ['neq', $material['warehouse_id']];
                        $stockModel->where(['product_id'=> $materialOneData['product_id'], 'warehouse_number' => $material['warehouse_id']])->setField(['stock_number' => $value['F']]);

                        $where = [];
                        $where['product_id'] = ['eq', $materialOneData['product_id']];
                        $where['warehouse_number'] = ['neq', $material['warehouse_id']];
                        $stockModel->where($where)->setField(['stock_number' => 0, 'o_audit' => 0, 'i_audit' => 0, 'out_processing' => 0]);
                    }
                }
            }

            $materialModel->commit();
            return dataReturn("数据同步成功",200);

        }catch(\Exception $exception){
            $materialModel->rollback();
            return dataReturn($exception->getMessage(), 400);
        }
    }

    /**
     * 读取文件内容，存入中间表中
     * @param $filePath
     * @return array
     * @throws \PHPExcel_Reader_Exception
     */
    public function readExcelToShow($filePath){
        Vendor('PHPExcel.PHPExcel');//引入类
        $reader = \PHPExcel_IOFactory::createReader('Excel2007');
        $PHPExcel = $reader->load($filePath); // 文档名称
        $sheetData = $PHPExcel->getActiveSheet()->toArray(null, true, true, true);
        $sheetData = array_values($sheetData);
        unset($sheetData[0]);  //删除标题，有一些不需要删除，目前不知道为什么

        if(empty($sheetData)){
            return dataReturn("Excel中数据为空",400);
        }

        $time = time();
        $materialData = [];
        foreach ($sheetData as $key => &$value){
            $material = [
                'product_no' => (string)$value['A'],
                'product_number' => $value['B'],
                'product_name' => $value['C'],
                'cate' => $value['D'],
                'material_type' => ($value['E'] == "生产") ? MaterialModel::TYPE_PRODUCE : MaterialModel::TYPE_PURCHASE,
                'warehouse_id' => $value['F'],
                'stock_number' => $value['G'],
                'batch' => $time,
                'uid' => session("staffId")
            ];
            $materialData[] = $material;
        }
        $res = D("material_excel_info")->addAll($materialData);
        if(!$res){
            return dataReturn(D("material_excel_info")->getError(),400);
        }
        return dataReturn("文件上传成功",200, date('Y-m-d H:i:s', $time));
    }

    /**
     * 上传文件接口
     * @param $file
     * @return array
     */
    public function importExcel($file){
        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "excel";
        // 判断是否存在当前文件夹，如果没有就创建
        if (!file_exists($rootPath)) {
            mkdir($rootPath, 0777,true);
        }
        $saveName = uniqid('productExcel');
        $cfg = [
            'autoSub' => false,
            'saveName' => $saveName,
            'rootPath' => $rootPath, // 保存根路径
            'replace'  => true,
        ];
        $upload = new \Think\Upload($cfg);// 实例化上传类

        // 上传单个文件
        $info   =   $upload->uploadOne($file);
        if(!$info) {// 上传错误提示错误信息
            return dataReturn($upload->getError(), 400);
        }else{// 上传成功 获取上传文件信息
            return dataReturn("文件上传成功",200, $rootPath . $saveName . '.' . $info['ext']);
        }
    }

    /**
     * 获取当前时间批次的数据全部导入crm_material 中
     */
    public function saveImportMaterialData(){
        if(IS_POST){
            $batch = I('post.batch');
            if (empty($batch) || $batch == 'NaN'){
                $this->returnAjaxMsg("参数不全",400);
            }
            $map = [
                'uid' => ['EQ', session('staffId')],
                'batch' => ['EQ', strtotime($batch)],
                'is_del' => ['EQ', 0],
            ];

            $data = D("material_excel_info")->where($map)->select();
            if(empty($data)){
                $this->returnAjaxMsg("当前时间节点数据为空",400);
            }
            $productNoExcel = array_unique(array_column($data, 'product_no'));

            $materialModel = new MaterialModel();
            $stockModel = new StockModel();
//            $map['product_no'] = ['in', "'" . implode("','",$productNoExcel) . "'"];
            $map['product_no'] = ['in', implode(",",$productNoExcel)];
            $materialData = $materialModel->field('product_no,product_number,product_name,material_type,warehouse_id')->where($map)->select();
            $productNoSystem = array_column($materialData,"product_no");
            if(empty($productNoSystem)){
                $productNoSystem = [];
            }

            $productNo = array_diff($productNoExcel,$productNoSystem);

            // 读取库房信息
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();
            $repIdArr = array_column($repMap,'rep_id');

            $materialModel->startTrans();
            try{
                foreach ($data as $key => $value){
                    $material = [
                        'product_no' => $value['product_no'],
                        'product_number' => $value['product_number'],
                        'product_name' => $value['product_name'],
                        'material_type' => $value['material_type'],
                        'cate' => $value['cate'],
                        'warehouse_id' => $value['warehouse_id'],
                        'update_time' => time()
                    ];

                    if(in_array($material['product_no'], $productNoExcel)){
                        $productNoExcel = array_merge(array_diff($productNoExcel, array($material['product_no'])));
                        if(!in_array($material['warehouse_id'],$repIdArr)){
                            $materialModel->rollback();
                            $this->returnAjaxMsg("库房不存在", 400);
                        }

                        if(in_array($material['product_no'], $productNo)){
                            // 当前物料新增
                            $productId = $materialModel->add($material);
                            if(empty($productId)){
                                $materialModel->rollback();
                                $this->returnAjaxMsg($materialModel->getError(), 400);
                            }

                            $addStockArr = [];
                            foreach ($repMap as $k => $v){
                                if($v['rep_id'] != $value['warehouse_id']) {
                                    $addStockArr[] = [
                                        'product_id' => $productId,
                                        "warehouse_number" => $v['rep_id'],
                                        'warehouse_name' => $v['repertory_name'],
                                        "stock_number" => 0,
                                        'o_audit' => 0,
                                        'i_audit' => 0,
                                        'out_processing' => 0,
                                        "update_time" => time()
                                    ];
                                }else {
                                    $addStockArr[] = [
                                        'product_id' => $productId,
                                        "warehouse_number" => $v['rep_id'],
                                        'warehouse_name' => $v['repertory_name'],
                                        "stock_number" => $value['F'],
                                        'o_audit' => 0,
                                        'i_audit' => 0,
                                        'out_processing' => 0,
                                        "update_time" => time()
                                    ];
                                }
                            }

                            $stockRes = $stockModel->addAll($addStockArr);
                            if(!$stockRes){
                                $materialModel->rollback();
                                $this->returnAjaxMsg($stockModel->getError(), 400);
                            }
                        }else {
                            // 修改当前物料
                            $materialOneData = $materialModel->alias("m")
                                ->field("m.*,s.stock_number")
                                ->join("left join crm_stock s on s.product_id = m.product_id")
                                ->where(['product_no' => ['eq', $value['product_no']]])
                                ->find();
                            if($materialOneData['product_number'] != $material['product_number'] || $materialOneData['product_name'] != $material['product_name'] || $materialOneData['material_type'] != $material['material_type'] || $materialOneData['warehouse_id'] != $material['warehouse_id'] || $materialOneData['cate'] != $material['cate']){
                                $materialRes = $materialModel->where(["product_id" => $materialOneData['product_id']])->setField($material);
                                if(!$materialRes){
                                    $materialModel->rollback();
                                    $this->returnAjaxMsg($materialModel->getError(), 400);
                                }
                            }

                            if($materialOneData['warehouse_id'] != $material['warehouse_id'] || $materialOneData['stock_number'] != $material['stock_number']){
                                $whereMap = [];
                                $whereMap['product_id'] = ['eq', $materialOneData['product_id']];
                                $whereMap['warehouse_number'] = ['neq', $material['warehouse_id']];
                                $stockModel->where(['product_id'=> $materialOneData['product_id'], 'warehouse_number' => $material['warehouse_id']])->setField(['stock_number' => $value['stock_number'], "update_time"]);

                                $where = [];
                                $where['product_id'] = ['eq', $materialOneData['product_id']];
                                $where['warehouse_number'] = ['neq', $material['warehouse_id']];
                                $stockModel->where($where)->setField(['stock_number' => 0, 'o_audit' => 0, 'i_audit' => 0, 'out_processing' => 0, 'update_time' => time()]);
                            }
                        }
                    }
                }

                $materialModel->commit();
                $this->returnAjaxMsg("数据同步成功",200);

            }catch(\Exception $exception){
                $materialModel->rollback();
                $this->returnAjaxMsg($exception->getMessage(), 400);
            }
        }else {
            die("非法");
        }
    }

    /**
     * 删除当前时间批次下所有的数据
     */
    public function delMaterialBatch(){
        if(IS_POST){
            $batch = I('post.batch');
            if(empty($batch) || $batch == "NaN"){
                $this->returnAjaxMsg("参数不全",400);
            }
            $map = [
                'uid' => ['EQ', session('staffId')],
                'batch' => ['EQ', strtotime($batch)],
                'is_del' => ['EQ', 0]
            ];
            $res = M('material_excel_info')->where($map)->setField(['is_del' => 1]);
            if (!$res){
                $this->returnAjaxMsg("删除失败",400);
            }else{
                $this->returnAjaxMsg("删除成功",200);
            }
        }else {
            die("非法");

        }
    }

    /**
     * 根据ID删除某一条数据
     */
    public function delMaterialBatchById(){
        if (IS_POST){
            $id = I('post.id');
            if(empty($id)){
                $this->returnAjaxMsg("参数不全",400);
            }
            $res = M("material_excel_info")->where(['id' => $id])->setField(['is_del' => 1]);
            if (!$res){
                $this->returnAjaxMsg("删除失败",400);
            }else{
                $this->returnAjaxMsg("删除成功",200);
            }
        }else{
            die("非法");
        }
    }

    /**
     * 导出当前时间批次下的数据
     */
    public function exportMaterialByBatch(){
        if(IS_POST){
            $batch = I('post.batch');
            if(empty($batch) || $batch == "NaN"){
                $this->returnAjaxMsg("参数不全",400);
            }
            $map = [
                'uid' => ['EQ', session('staffId')],
                'batch' => ['EQ', strtotime($batch)],
                'is_del' => ['EQ', 0]
            ];

            $data = D("material_excel_info")->where($map)->select();
            if(empty($data)){
                $this->returnAjaxMsg("当前筛选条件下无数据，请修改！",400);
            }

            Vendor('PHPExcel.PHPExcel');//引入类
            Vendor('PHPExcel.PHPExcel_IOFactory');//引入类
//        Vendor('PHPExcel.Writer.Excel5');  // 后缀是xls
            Vendor('PHPExcel.Writer.Excel2007'); // 后缀是xlsx

            $objPHPExcel = new \PHPExcel();                        //初始化PHPExcel(),不使用模板

            $objActSheet = $objPHPExcel->getActiveSheet();

            //这里是设置单元格的内容
            $objActSheet->setCellValue("A1","物料编号");
            $objActSheet->setCellValue("B1","物料名称");
            $objActSheet->setCellValue("C1","物料型号");
            $objActSheet->setCellValue("D1","物料分类");
            $objActSheet->setCellValue("E1","物料属性");
            $objActSheet->setCellValue("F1","默认库房id");
            $objActSheet->setCellValue("G1","库房库存");
            $objActSheet->setCellValue("H1","导入时间");

            $i = 2;
            foreach ($data as $k=>$v){
                $v['material_type']  = ($v['material_type'] == MaterialModel::TYPE_PURCHASE) ? "生产" : "外购";
                //这里是设置单元格的内容
                $objActSheet->setCellValue("A".$i,$v['product_no']);
                $objActSheet->setCellValue("B".$i,$v['product_number']);
                $objActSheet->setCellValue("C".$i,$v['product_name']);
                $objActSheet->setCellValue("D".$i,$v['cate']);
                $objActSheet->setCellValue("E".$i,$v['material_type']);
                $objActSheet->setCellValue("F".$i,$v['warehouse_id']);
                $objActSheet->setCellValue("G".$i,$v['stock_number']);
                $objActSheet->setCellValue("H".$i,date("Y-m-d H:i:s",$v['batch']));
                $i++;
            }

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $fileName = "物料上传数据". '_' . date('Ymd') . '.xlsx';
            // $fileName = iconv('utf-8', 'gb2312', $fileName);//文件名称


            // 1.保存至本地Excel表格
            $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/excel/";
            if (!file_exists($rootPath)) {
                mkdir($rootPath, 0777,true);
            }
            $objWriter->save($rootPath . $fileName);

            $this->returnAjaxMsg("下载成功",200,[
                'file_url' => UPLOAD_ROOT_PATH . "/excel/" . $fileName
            ]);

        }else {
            die("非法");
        }
    }
}