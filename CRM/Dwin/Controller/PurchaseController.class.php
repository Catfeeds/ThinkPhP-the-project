<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/6/13
 * Time: 上午11:00
 */
namespace Dwin\Controller;

use Dwin\Model\FileRecordModel;
use Dwin\Model\MaterialModel;
use Dwin\Model\MaxIdModel;
use Dwin\Model\PurchaseContractModel;
use Dwin\Model\PurchaseContractProductModel;
use Dwin\Model\PurchaseOrderModel;
use Dwin\Model\PurchaseOrderProductModel;
use Dwin\Model\PurchaseOrderScheduleModel;
use Dwin\Model\PurchaseSupplierAddressModel;
use Dwin\Model\PurchaseSupplierAuditModel;
use Dwin\Model\PurchaseSupplierAwardsModel;
use Dwin\Model\PurchaseSupplierCertificationModel;
use Dwin\Model\PurchaseSupplierContactModel;
use Dwin\Model\PurchaseSupplierCooperationModel;
use Dwin\Model\PurchaseSupplierCustomerModel;
use Dwin\Model\PurchaseSupplierEquityModel;
use Dwin\Model\PurchaseSupplierFinanceModel;
use Dwin\Model\PurchaseSupplierModel;
use Dwin\Model\PurchaseSupplierTeamModel;
use Dwin\Model\FileUploadModel;
use Dwin\Model\RepertorylistModel;
use Dwin\Model\StaffModel;
use Dwin\Model\StockInModel;
use Dwin\Model\StockInRecordModel;
use Dwin\Model\StockIoCateModel;
use Dwin\Model\StockMaterialModel;
use Guzzle\Http\Message\Request;
use Guzzle\Parser\UriTemplate\PeclUriTemplate;

class PurchaseController extends CommonController
{
    static protected $supplierAuthRoleIdArray = [1,2,34,35];
    static protected $supplierFinalAuthId = [1];
    static protected $contractManagerAuthRole = [1];
    static protected $contractIllegalAuthRole = [1,2,3,11,16];
    static protected $orderAuthRoleIdArray    = [1,2,3,11,16];
    static protected $failStatus    = 400;
    static protected $successStatus = 200;

    static protected $insert;
    static protected $notDel = 0;
    static protected $isDel  = 1;

    public function index()
    {
        $this->display();
    }

    /**
     * 供应商一级审核列表
     */
    public function supplierIndex(){
        $supplier = new PurchaseSupplierModel();
        $checkFlag = arrayIntersect(explode(',',session('deptRoleId')),self::$supplierAuthRoleIdArray);
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $staffLimit = $this->posts['staffLimit'];  // 1=>本人 2=>所有

            $map = [];
            if ($staffLimit == 1){
                $map['p.charger'] = ['eq', session('staffId')];
            }else {
                if(!$checkFlag){
                    // 获取查看的当前职位能查看的供应商
                    $staffStr = self::getStaffRoleAuth();
                    if ($staffStr === false){
                        $this->ajaxReturn([
                            "draw"            => 0,
                            "recordsTotal"    => 0,
                            "recordsFiltered" => 0,
                            "data"            => []
                        ]);
                    }
                    $map['p.charger'] = ["in", $staffStr];
                }
            }

            list($supplierData,$count,$recordsFiltered) = $supplier->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $this->posts['audit_status'], $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $supplierData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign(array(
                'supplierOtherMsg' => PurchaseSupplierModel::$supplierOtherMsg,
                'auditMsg' => PurchaseSupplierModel::$auditTypeMap,
                'secondAuditMsg' => PurchaseSupplierAuditModel::$auditStatus,
                'listedMsg' => PurchaseSupplierModel::$listedMap,
                'checkFlag' => $checkFlag
                ));
            $this->display();
        }

    }

    /**
     * 判断与供应商相关的权限
     * @param $id  供应商id
     * @return array
     */
    protected function checkAuthBySupplierId($id){
        $supplierModel = new PurchaseSupplierModel();

        $data = $supplierModel->find($id);
        if($data['charger'] != session("staffId")){
            return ['您不是当前供应商的负责人，不能对此进行操作', -1];
        }
        return ["正常操作", 0];
    }

    /**
     * 供应商详情
     */
    public function supplierDetail(){
        if(IS_POST) {
            die('非法');
        }else {
            $data = I('get.');
            $supplierId = $data['id'];
            $supplierModel = new PurchaseSupplierModel();

            $returnArr  = ["base", 'address', 'contact', 'certification', 'awards', 'customer', 'equity', 'finance', 'team', 'cooperation', 'audit'];
            $data = $supplierModel->getSupplierData($supplierId, $returnArr);
            $this->assign($data);
            $this->display();
        }
    }

    /**
     * 获取某个供应商所有其他信息
     */
    public function getSupplierAllMsg(){
        if(IS_POST) {
            $data = I('post.');
            $supplierId = $data['id'];

            $supplierModel = new PurchaseSupplierModel();
            $returnArr  = ['address', 'contact', 'certification', 'awards', 'customer', 'equity', 'finance', 'team', 'cooperation', 'audit'];
            $data = $supplierModel->getSupplierData($supplierId, $returnArr);
            $this->returnAjaxMsg('数据返回成功', 200 , $data);
        }else {
            die('非法');
        }

    }

    /**
     * 获取供应商其他某类型信息用于编辑
     * type => 标明是哪种类型的数据
     * ID => 标明是供应商的id
     */
    public function supplierOtherMsg(){
        if(IS_POST){
            $data = I('post.');
            if(empty($data['id']) || empty($data['type'])){
                $this->returnAjaxMsg('参数错误', 401);
            }
            // 判断是否有权限
            list($msg, $code) = self::checkAuthBySupplierId($data['id']);
            if($code != 0){
                $this->returnAjaxMsg($msg, 400);
            }

            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->getSupplierOtherData($data['id'], $data['type']);
            $this->returnAjaxMsg('数据返回成功', 200 , $supplierData);
        }else {
            die('非法');
        }
    }

    /**
     * 修改供应商相关信息
     */
    public function editSupplierMsg(){
        if(IS_POST){
            $data = I('post.');
            if(empty($data['id']) || !isset($data['type'])){
                $this->returnAjaxMsg('参数错误', 401);
            }

            // 判断是否有权限
            list($msg, $code) = self::checkAuthBySupplierId($data['id']);
            if($code != 0){
                $this->returnAjaxMsg($msg, 400);
            }

            $model = M();
            $model->startTrans();
            if(empty($data['editData']) && empty($data['addData'])){
                $model->rollback();
                $this->returnAjaxMsg('信息无变动，请修改后提交', 401);
            }
            $supplier = new PurchaseSupplierModel();

            if(!empty($data['editData'])){
                $supplierData = $supplier->editSupplierMsgMany($data['editData'], $data['type'], $data['id']);
            }
            if(!empty($data['addData'])){
                session('supplierPid', $data['id']);
                list($supplierData['msg'], $supplierData['status']) = $supplier->addSupplierOtherMsgMany($data['type'], $data['addData']);
            }
            if($supplierData['status'] != self::$successStatus){
                $model->rollback();
            }else {
                // 重置供应商审核状态
                list($res, $message) = $supplier->resetAuditStatus($data['id']);
                if(!$res){
                    $model->rollback();
                    $this->returnAjaxMsg($message, self::$failStatus);
                }
                $model->commit();
            }
            $this->ajaxReturn($supplierData);
        }else {
            die('非法');
        }
    }

    /**
     * 修改或新增供应商单条数据
     */
    public function editOrAddSupplierOneMsg(){
        if(IS_POST){
            $data = I('post.');
            if(empty($data['id']) || !isset($data['type']) || !isset($data['data'])){
                $this->returnAjaxMsg('参数错误', 401);
            }

            // 判断是否有权限
            list($msg, $code) = self::checkAuthBySupplierId($data['id']);
            if($code != 0){
                $this->returnAjaxMsg($msg, 400);
            }

            $supplier = new PurchaseSupplierModel();
            $data = $supplier->eidtOraddSupplierOtherOneMsg($data['data'], $data['type'], $data['id']);
            $this->ajaxReturn($data);
        }else {
            die('非法');
        }
    }

    /**
     * 删除供应商其他信息
     */
    public function delSupplierOtherMsg(){
        if(IS_POST){
            $data = I('post.');
            if(!isset($data['id']) || !isset($data['type']) || !isset($data['data'])){
                $this->returnAjaxMsg('参数错误', 401);
            }

            // 判断是否有权限
            list($msg, $code) = self::checkAuthBySupplierId($data['id']);
            if($code != 0){
                $this->returnAjaxMsg($msg, 400);
            }

            $supplier = new PurchaseSupplierModel();
            $data = $supplier->delSupplierOtherMsg($data['data']['id'], $data['type'], $data['id']);
            $this->ajaxReturn($data);
        }else {
            die('非法');
        }
    }

    /**
     * 生成供应商编号
     */
    public function createSupplierId(){
        $createId = new MaxIdModel();
        $id = $createId->getMaxId('supplier');
        if($id){
            $this->returnAjaxMsg('获取编号成功', 200, [
                'id' => $id,
                'supplierIdString' => 'GYS-' . $id
            ]);
        }else {
            $this->returnAjaxMsg('获取编号失败', 401);
        }
    }

    /**
     * 供应商名称唯一性验证
     */
    public function checkSupplierName(){
        if(IS_POST){
            $name = I("post.supplier_name");
            $name = preg_replace('# #','',$name);
            if(strlen($name) == 0){
                $this->returnAjaxMsg("参数不全",400);
            }
            $supplierModel = new PurchaseSupplierModel();
            $data = $supplierModel->where(['supplier_name' => $name])->find();
            if(!empty($data)){
                $this->returnAjaxMsg("供应商名称已存在", 400);
            }
            $this->returnAjaxMsg("供应商名称唯一",200);
        }else {
            die("非法");
        }
    }

    /**
     * 添加供应商信息
     * 供应商表数据 supplierData
     * 供应商地址表 addressData
     * 供应商联系表 supplierContactData
     * 供应商资质认证 certificationData
     * 供应商获奖信息 awardsData
     * 供应商客户信息 customerData
     * 供应商公司股权结构 equityData
     * 供应商财务情况 financeData
     * 供应商团队情况 teamData
     * 供应商合作情况 cooperationData
     *
    */
    public function addSupplier()
    {
        if (IS_POST) {
            $supplierModel = new PurchaseSupplierModel();
            $this->posts = I('post.');
            $return = $supplierModel->addSupplier($this->posts);

            $this->ajaxReturn($return);
        } else {
            $this->display();
        }
    }

    /**
     * 供应商一级审核
     * id => 供应商id
     * status => 审核状态
     */
    public function firstAuditSupplier(){
        if(IS_POST){
            $data = I('post.');
            $id = $data['id'];
            $auditStatus = $data['status'];
            if(empty($id) || !isset($auditStatus)){
                $this->returnAjaxMsg('参数错误', 401);
            }
            $supplier = new PurchaseSupplierModel();
            $auditData = $supplier->where(['id' => $id])->find();

            if($auditData['audit_status'] == PurchaseSupplierModel::TYPE_UNQUALIFIED){
                $this->returnAjaxMsg('当前供应商审核已为不合格，请不要重复审核', 402);
            }elseif ($auditData['audit_status'] == $auditStatus){
                $this->returnAjaxMsg('审核状态相等，请不要重复审核', 402);
            } else {
                $auth = $this->isAuthToOperation($auditStatus == PurchaseSupplierModel::TYPE_MANAGER_QUALIFIED
                    ? self::$supplierFinalAuthId : self::$supplierAuthRoleIdArray);
                // 判断当前登录人是否有权限对此供应商审核
                if(!$auth){
                    $this->returnAjaxMsg('您没有权限审核当前供应商。如有问题请联系管理', 402);
                }
                $supplier->where(["id" => $id])->setField(["audit_status" => $auditStatus, 'updater' => session("staffId"), "update_time" => time()]);
            }

            $this->returnAjaxMsg('审核完成', 200);
        }else {
            die('非法');
        }
    }

    /**
     * 供应商二级审核
     * id => 供应商id
     * status => 审核状态
     * typeId => 审核类别
     * tip => 审核备注
     */
    public function secondAuditSupplier(){
        if(IS_POST){
            $data = I('post.');
            $supplierPid = $data['supplierPid'];
            $auditStatus = $data['status'];
            $id = $data['id'];  // 审核类别  => crm_purchase_supplier_audit 中的ID
            $tip = empty($data['tips']) ? null : $data['tips']; // 备注
            $fileId = empty($data['file_id']) ? null : $data['file_id'];

            if(empty($supplierPid) || !isset($auditStatus) || empty($id)){
                $this->returnAjaxMsg('参数错误', 401);
            }

            // 判断当前登录人是否有权限对此供应商审核
//            $auth = $this->isAuthToOperation('');
//            if(!$auth){
//                $this->returnAjaxMsg('您没有权限审核当前供应商。如果有需要，请向上级提需求', 402);
//            }

            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->where("id = " . $supplierPid)->find();

            switch ($supplierData['audit_status']){
                case PurchaseSupplierModel::TYPE_NOT_AUDIT :
                    $this->returnAjaxMsg('请先进行一级审核', 403);
                    break;
                case PurchaseSupplierModel::TYPE_UNQUALIFIED :
                    $this->returnAjaxMsg('一级审核不合格，无法进行二级审核', 404);
                    break;
                case PurchaseSupplierModel::TYPE_QUALIFIED:
                    $supplierAudit = M('purchase_supplier_audit')->where(['id' => $id])->select();
                    if($supplierAudit['status'] == $auditStatus){
                        $this->returnAjaxMsg('已审核为当前状态，请不要重复审核', 405);
                    }
                    $auditData = [];
                    $auditData['staff_id'] = $this->staffId;
                    $auditData['file_id'] = $fileId;
                    $auditData['status'] = $auditStatus;
                    $auditData['audit_time'] = time();
                    $auditData['tips'] = $tip;
                    M('purchase_supplier_audit')->where(['id' => $id])->setField($auditData);
                    break;
                default:
                    $this->returnAjaxMsg('一级审核存在问题，请回溯', 406);
                    break;
            }

            $this->returnAjaxMsg('审核完成', 200);
        }else {
            die('非法');
        }
    }

    /**
     * 预览资质审核文件
     */
    public function previewSecondAuditPdf()
    {
        $id = I("get.id"); // 资质审核的id
        if(empty($id)){
            $this->error("参数不全");
            die("参数不全");
        }

        // 资质文件信息
        $auditModel = new PurchaseSupplierAuditModel();
        $auditData = $auditModel->find($id);
        if(empty($auditData['file_id'])){
            $this->error("当前资质审核没有上传附件");
            die("当前资质审核没有上传附件");
        }

        $ua = $_SERVER['HTTP_USER_AGENT'];
        $ie = ['compatible', 'Trident', 'MSIE '];
        $viewName = 'previewPdf';
        foreach ($ie as $item) {
            if (strpos($ua, $item) !== false){
                $viewName = 'previewPdfIE';
                break;
            }
        }
        $fileData = M('file_upload') -> find($auditData['file_id']);
        if(empty($fileData['path'])){
            $this->error("当前PDF路径没有找到");
            die("当前PDF路径没有找到");
        }

        if (file_exists(WORKING_PATH . $fileData['path'])){
            // 判断文件的类型，如果不是PDF，直接下载下来
            $type = array_pop(explode('.', $fileData['path']));
            if($type != 'pdf'){
                /*$fp=fopen(WORKING_PATH . $fileData['path'],"r");
                $file_size=filesize(WORKING_PATH . $fileData['path']);
                //下载文件需要用到的头
                Header("Content-type: application/octet-stream");
                Header("Accept-Ranges: bytes");
                Header("Accept-Length:".$file_size);
                Header("Content-Disposition: attachment; filename=" . $fileData['file_name']);
                $buffer=1024;
                $file_count=0;
                //向浏览器返回数据
                while(!feof($fp) && $file_count<$file_size){
                    $file_con=fread($fp,$buffer);
                    $file_count+=$buffer;
                    echo $file_con;
                }
                fclose($fp);
                die;*/

                /*$this->assign([
                    'url' => $fileData['path'],
                    'type' => $type
                ]);
                $this->display("download");*/
                die("文件类型错误");
            }else {
                $recordModel = new FileRecordModel();
                $res = $recordModel -> previewPdfFileRecord($auditData['file_id'], "file_upload");
                if ($res === false){
                    $this->error('预览记录更新失败');
                }
                $this->assign([
                    'url' => $fileData['path'],
                    'type' => $type
                ]);
                if ($viewName == 'previewPdf'){
                    $messy = sha1($fileData['path']);
                    $messy = str_repeat($messy, 5);
                    redirect(U('previewPdfChrome', '', '') . '?fileUrl='. $messy . '&file='. $fileData['path'] . '&path='. $messy);
                }else{
                    $this->display($viewName);
                }
            }
        }else{
            $this->error('找不到文件');
        }
    }

    /**
     * 预览资质证明文件
     */
    public function previewCerPdf(){
        $id = I("get.id"); // 资质审核的id
        if(empty($id)){
            $this->error("参数不全");
            die("参数不全");
        }

        // 资质文件信息
        $cerModel = new PurchaseSupplierCertificationModel();
        $cerData = $cerModel->find($id);
        if(empty($cerData['file_id'])){
            $this->error("当前资质证明没有上传附件");
            die("当前资质证明没有上传附件");
        }

        $ua = $_SERVER['HTTP_USER_AGENT'];
        $ie = ['compatible', 'Trident', 'MSIE '];
        $viewName = 'previewPdf';
        foreach ($ie as $item) {
            if (strpos($ua, $item) !== false){
                $viewName = 'previewPdfIE';
                break;
            }
        }
        $fileData = M('file_upload') -> find($cerData['file_id']);
        if(empty($fileData['path'])){
            $this->error("当前PDF路径没有找到");
            die("当前PDF路径没有找到");
        }

        if (file_exists(WORKING_PATH . $fileData['path'])){
            // 判断文件的类型，如果不是PDF，直接下载下来
            $type = array_pop(explode('.', $fileData['path']));
            if($type != 'pdf'){
                /*$this->assign([
                    'url' => $fileData['path'],
                    'type' => $type
                ]);
                $this->display("download");*/
                die("文件类型错误");
            }else {
                $recordModel = new FileRecordModel();
                $res = $recordModel -> previewPdfFileRecord($cerData['file_id'], "file_upload");
                if ($res === false){
                    $this->error('预览记录更新失败');
                }
                $this->assign([
                    'url' => $fileData['path'],
                    'type' => $type
                ]);
                if ($viewName == 'previewPdf'){
                    $messy = sha1($fileData['path']);
                    $messy = str_repeat($messy, 5);
                    redirect(U('previewPdfChrome', '', '') . '?fileUrl='. $messy . '&file='. $fileData['path'] . '&path='. $messy);
                }else{
                    $this->display($viewName);
                }
            }
        }else{
            $this->error('找不到文件');
        }
    }

    /**
     * 奖状信息预览
     */
    public function previewAwardPdf(){
        $id = I("get.id"); // 资质审核的id
        if(empty($id)){
            $this->error("参数不全");
            die("参数不全");
        }

        // 资质文件信息
        $awardModel = new PurchaseSupplierAwardsModel();
        $awardData = $awardModel->find($id);
        if(empty($awardData['file_id'])){
            $this->error("当前奖状证书没有上传附件");
            die("当前奖状证书没有上传附件");
        }

        $ua = $_SERVER['HTTP_USER_AGENT'];
        $ie = ['compatible', 'Trident', 'MSIE '];
        $viewName = 'previewPdf';
        foreach ($ie as $item) {
            if (strpos($ua, $item) !== false){
                $viewName = 'previewPdfIE';
                break;
            }
        }
        $fileData = M('file_upload') -> find($awardData['file_id']);
        if(empty($fileData['path'])){
            $this->error("当前PDF路径没有找到");
            die("当前PDF路径没有找到");
        }

        if (file_exists(WORKING_PATH . $fileData['path'])){
            // 判断文件的类型，如果不是PDF，直接下载下来
            $type = array_pop(explode('.', $fileData['path']));
            if($type != 'pdf'){
                /*$this->assign([
                    'url' => $fileData['path'],
                    'type' => $type
                ]);
                $this->display("download");*/
                die("文件类型错误");
            }else {
                $recordModel = new FileRecordModel();
                $res = $recordModel -> previewPdfFileRecord($awardData['file_id'], "file_upload");
                if ($res === false){
                    $this->error('预览记录更新失败');
                }
                $this->assign([
                    'url' => $fileData['path'],
                    'type' => $type
                ]);
                if ($viewName == 'previewPdf'){
                    $messy = sha1($fileData['path']);
                    $messy = str_repeat($messy, 5);
                    redirect(U('previewPdfChrome', '', '') . '?fileUrl='. $messy . '&file='. $fileData['path'] . '&path='. $messy);
                }else{
                    $this->display($viewName);
                }
            }
        }else{
            $this->error('找不到文件');
        }
    }

    //=======================================================

    /**
     * 供应商修改负责人列表
     */
    public function supplierChargeList(){
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $supplier = new PurchaseSupplierModel();
            list($supplierData,$count,$recordsFiltered) = $supplier->getSupplierChargeList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'],$this->posts['charger']);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $supplierData);
            $this->ajaxReturn($this->output);
        }else {
            // 人员信息
            $staffModel = new StaffModel();
            $staffData = $staffModel->field("id,name")->select();

            $this->assign(array(
                'auditMsg' => PurchaseSupplierModel::$auditTypeMap, //审核map
                'listedMsg' => PurchaseSupplierModel::$listedMap,  // 是否上市map
                'staffData' => $staffData,
            ));
            $this->display();
        }
    }

    /**
     * 修改供应商负责人
     */
    public function editSupplierCharge(){
        if(IS_POST){
            $data = I("post.");

            if(empty($data['oldChargeId']) || empty($data['newChargeId']) || empty($data['newChargeName']) || empty($data['type'])){
                $this->returnAjaxMsg("参数不全",400);
            }

            $supplierModel = new PurchaseSupplierModel();

            // $data['type']  1 => 修改一个或多个   2 => 修改全部
            if($data['type'] == 1){
                if(empty($data['id'])){
                    $this->returnAjaxMsg("参数不全",400);
                }

                if(is_array($data['id'])){
                    $idStr = implode(',',$data['id']);
                    $map['id'] = ['in' , $idStr];
                    $res = $supplierModel->where($map)->setField(['charger' => $data['newChargeId'], 'charge_name' => $data['newChargeName'], 'update_id' => session('staffId'), 'update_time' => time()]);
                }else {
                    $res = $supplierModel->where(['id' => $data['id']])->setField(['charger' => $data['newChargeId'], 'charge_name' => $data['newChargeName'], 'update_id' => session('staffId'), 'update_time' => time()]);
                }
            }else {
                $res = $supplierModel->where(['charger' => $data['oldChargeId'], 'is_del' => self::$notDel])->setField(['charger' => $data['newChargeId'], 'charge_name' => $data['newChargeName'], 'update_id' => session('staffId'), 'update_time' => time()]);
            }

            if(!$res){
                $this->returnAjaxMsg($supplierModel->getError(), 400);
            }

            $this->returnAjaxMsg("修改供应商负责人成功", 200);
        }else {
            die("非法");
        }
    }

    //=======================================================

    /**
     * 生成合同编号
     */
    public function createContractId(){
        $createId = new MaxIdModel();
        $id = $createId->getMaxId('contract');
        if($id){
            $this->returnAjaxMsg('获取编号成功', 200, [
                'contractIdString' => 'DP' . date("Ymd") . $id,
                'id' => $id
            ]);
        }else {
            $this->returnAjaxMsg('获取编号失败', 401);
        }
    }

    /**
     * 合同列表  未审核
     */
    public function contractIndex(){
        $contract = new PurchaseContractModel();
        $checkFlag = arrayIntersect(explode(',',session('deptRoleId')),self::$supplierAuthRoleIdArray);

        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $status = isset(PurchaseContractModel::$auditStatus[$this->posts['audit_status']])
                ? (int)$this->posts['audit_status']
                : PurchaseContractModel::TYPE_NOT_AUDIT;
            $map['c.audit_status'] = ['eq', $status];

            $staffLimit = $this->posts['staffLimit'];  // 1=>本人 2=>所有
            if ($staffLimit == 1){
                $map['c.charger'] = ['eq', session('staffId')];
            }else {
                if(!$checkFlag){
                    // 获取查看的当前职位能查看的供应商
                    $staffStr = self::getStaffRoleAuth();
                    if ($staffStr === false){
                        $this->ajaxReturn([
                            "draw"            => 0,
                            "recordsTotal"    => 0,
                            "recordsFiltered" => 0,
                            "data"            => []
                        ]);
                    }
                    $map['c.charger'] = ["in", $staffStr];
                }
            }

            if(!arrayIntersect(explode(',',session('deptRoleId')),self::$orderAuthRoleIdArray)){
                // 获取查看的当前职位能查看的供应商
                $staffStr = self::getStaffRoleAuth();
                if ($staffStr === false){
                    $this->ajaxReturn([
                        "draw"            => 0,
                        "recordsTotal"    => 0,
                        "recordsFiltered" => 0,
                        "data"            => []
                    ]);
                }
                $map['c.create_id'] = ["in", $staffStr];
            }

            list($contractData,$count,$recordsFiltered) = $contract->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $contractData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign(array(
                'auditMsg' => PurchaseContractModel::$auditStatus,
                'checkFlag' => $checkFlag
            ));
            $this->display();
        }
    }

    /**
     * 获取合同下所有物料信息
     * @param $id
     */
    public function getContractProduct(){
        if (IS_POST) {
            $id = I("post.id");
            if(empty($id)){
                $this->returnAjaxMsg("参数错误",400);
            }
            $contractProduct = new PurchaseContractProductModel();
            $contractProductData = $contractProduct->getProductWithPId($id);
            $this->returnAjaxMsg("数据返回成功",200,$contractProductData);
        } else {
            die("非法");
        }
    }

    /**
     * 获取一个合同的全部信息
     * @param $id
     */
    public function getContractAllMsg($id){
        if (IS_POST) {
            $supplierContractModel = new PurchaseContractModel();
            $contractData = $supplierContractModel->getContractData($id, ['contract', 'product']);

            $this->returnAjaxMsg("数据返回成功",200,$contractData);
        } else {
            $this->assign(compact('id'));
            $this->display('contract');
        }
    }

    /**
     * 添加合同接口，包含基本信息和购买产品信息
     */
    public function addContract()
    {
        $supplierModel = new PurchaseSupplierModel();
        if (IS_POST) {
            $this->posts = I('post.');
            $contractModel = new PurchaseContractModel();
            $addRst = $contractModel->addContractTrans($this->posts);
            $this->ajaxReturn($addRst);
        } else {
            $supplierId = I('get.supplierId');
            $returnArr  = ['base', 'address', 'contact','audit'];

            $data = $supplierModel->getSupplierData($supplierId, $returnArr);

            if($data['base']['charger'] != session("staffId")){
                die("您不是当前供应商的负责人，不能对此进行操作");
            }

            //判断当前供应商是否通过审核
            if($data['base']['audit_status'] != PurchaseSupplierModel::TYPE_MANAGER_QUALIFIED){
                die("当前供应商未通过全部审核，不能添加合同");
            }

            $res = 0;
            foreach ($data['audit'] as $key => $item){
                if($item['status'] == PurchaseSupplierAuditModel::TYPE_UNQUALIFIED){
                    die("当前供应商未通过审核，不能添加合同");
                }
                if($item['status'] == PurchaseSupplierAuditModel::TYPE_QUALIFIED){
                    $res = 1;
                }
            }
            if($res == 0){
                die("当前供应商未二级审核，不能添加合同");
            }

            $this->assign(compact('data'));
            $this->display();
        }
    }

    /**
     * 修改合同接口，包含基本信息和购买产品信息
     */
    public function editContract()
    {
        $supplierModel = new PurchaseSupplierModel();
        $supplierContractModel = new PurchaseContractModel();
        if (IS_POST) {
            $this->posts = I('post.');
            $rst= $supplierContractModel->editContractTrans($this->posts);
            $this->ajaxReturn($rst);
        } else {
            $contractId = I('get.contractId');
            $contractData = $supplierContractModel->getContractData($contractId, ['contract', 'product']);

            if($contractData['contract']['create_id'] != $this->staffId){
                die("您不是当前合同的创建人，不能对此进行操作");
            }

            if($contractData['contract']['audit_status'] == PurchaseContractModel::TYPE_QUALIFIED || $contractData['contract']['audit_status'] == PurchaseContractModel::TYPE_LAY_QUALIFIED){
                die("审核合格的合同不能被修改");
            }

            $supplierData = $supplierModel->getSupplierData($contractData['contract']['supplier_pid'], ['base', 'address', 'contact']);
            $this->assign(compact('contractData','supplierData'));
            $this->display();
        }
    }

    /**
     * 上传合同附件
     */
    public function uploadContractFile(){
        if(IS_POST){
            $data = I('post.');
            if(empty($data['id'])){
                $this->returnAjaxMsg('参数错误', 401);
            }
            $fileModel = new FileUploadModel();
            $contractModel = new PurchaseContractModel();
            list($code, $msg) = $contractModel->checkUploadAuth($data['id']);

            if ($code != self::$successStatus){
                $this->returnAjaxMsg($msg, $code);
            }

            $description = '合同附件';
            $path = UPLOAD_ROOT_PATH . '/supplier/contract/';
            $arr = explode('.',$_FILES['file']['name']);
            $count = count($arr) - 1;
            $name = $arr[$count];

            if($name != 'pdf'){
                $this->returnAjaxMsg("合同附件只能是pdf格式",400);
            }
            list($code, $msg , $id) = $fileModel->uploadFile($_FILES, $path, $fileModel::TYPE_SUPPLIER, $description);

            if($code != self::$successStatus){
                $this->returnAjaxMsg($msg, $code);
            }

            $res = $contractModel->where(['id' => $data['id']])->setField(['is_return_contract' => PurchaseContractModel::FILE_IS_UPLOAD, 'file_id' => $id]);
            if(!$res){
                $this->returnAjaxMsg($contractModel->getError(), self::$failStatus);
            }
            $this->returnAjaxMsg("合同附件上传成功", self::$successStatus);

        }else {
            die("上传合同附件");
        }
    }

    /**
     * 删除一个合同
     */
    public function delContract(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['contractId'])){
                $this->returnAjaxMsg("参数错误", 400);
            }

            $contractModel = new PurchaseContractModel();
            $res = $contractModel->delContract($data['contractId']);
            $this->ajaxReturn($res);

        }else {
            die("非法");
        }
    }

    /**
     * 删除合同中的物料信息
     */
    public function delContractProduct(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['productId']) || empty($data['contractId'])){
                $this->returnAjaxMsg("参数错误", 400);
            }

            $contractModel = new PurchaseContractModel();
            $res = $contractModel->delContractProduct($data['contractId'], $data['productId']);
            $this->ajaxReturn($res);
        }else {
            die("非法");
        }
    }

    /**
     * 合同审核
     * param id => crm_purchase_contract 中的 id
     * status
     */
    public function auditContract()
    {
        if (IS_POST) {
            $checkData= I('post.');
            if(empty($checkData['id']) || empty($checkData['status'])){
                $this->ajaxReturn('参数错误', self::$failStatus);
            }
            $contractModel = new PurchaseContractModel();
            if ($checkData['status'] == PurchaseContractModel::TYPE_QUALIFIED) {
                // 总经理审核
                $authArray = self::$contractManagerAuthRole;
            } elseif ($checkData['status'] == PurchaseContractModel::TYPE_LAY_QUALIFIED) {
                $authArray = self::$contractIllegalAuthRole;
            } else {
                $authArray = array_merge(self::$contractIllegalAuthRole, self::$contractManagerAuthRole);
            }
            $auth = $this->isAuthToOperation($authArray);
            if (!$auth) {
                $this->returnAjaxMsg('无权审核,如有问题请联系管理员', self::$failStatus);
            }
            $validateRst = $contractModel->validityCheck($checkData['id'], $checkData['status']);
            $this->ajaxReturn($validateRst);
        } else {
            die('非法');
        }
    }



    /**
     * 下载采购合同，生成pdf
     */
    public function uploadContract(){
        $id = I("post.id");
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/Dwin/Public/contractLoad?id=' . $id;
        Vendor('mpdf.mpdf');
        //设置中文编码
        $mpdf=new \mPDF('zh-cn/utf-8','A4', 0, '宋体', 0, 0 ,10,0,0,0);
        $mpdf->useAdobeCJK = true;

        $mpdf->allow_charset_conversion=true;  // Set by default to TRUE
        
        //$html = file_get_contents($url);

        $mpdf->showImageErrors = true;

/*        $picUrl = preg_match_all('/<img.*?src="(.*?)".*?>/is',$html,$array);*/
//        var_dump($picUrl, $array);die;

        $mpdf->SetHTMLHeader( false );
        $mpdf->SetHTMLFooter( false );

//        $html = '';
//        $handle = fopen ($url, "rb");
//        while (!feof($handle)) {
//            $html .= fread($handle, 819200);
//        }
//        fclose($handle);
        $curl = curl_init();
//        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        //执行命令
        $html = curl_exec($curl);
//        $a = json_decode($res);
        //关闭URL请求
//        curl_close($curl);
        $mpdf->WriteHTML($html);
        $fileName = "采购合同.pdf";

        // 1.保存至本地Excel表格
        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/PDF/";
        if (!file_exists($rootPath)) {
            mkdir($rootPath, 777,true);
        }
//        $mpdf->Output($rootPath . $fileName, true);  // 当直接调用接口，能够下载文件，但是不知道为什么使用ajax回调就无法下载
        $mpdf->Output($rootPath . $fileName, "f");  // 保存文件至服务器
        $this->returnAjaxMsg("下载成功", 200, [
            'file_url' => UPLOAD_ROOT_PATH . "/PDF/" . $fileName
        ]);
    }

    /**
     * 预览pdf
     * pdf在线预览方法, 分为chrome和ie两种
     * @param $id
     */
    public function previewPdf()
    {
        $id = I("get.id"); // 合同id
        if(empty($id)){
            die("参数不全");
        }

        // 合同信息
        $contractModel = new PurchaseContractModel();
        $contractData = $contractModel->find($id);
        if(empty($contractData['file_id'])){
            $this->error("当前合同没有上传合同附件");
            die("当前合同没有上传合同附件");
        }

        $ua = $_SERVER['HTTP_USER_AGENT'];
        $ie = ['compatible', 'Trident', 'MSIE '];
        $viewName = 'previewPdf';
        foreach ($ie as $item) {
            if (strpos($ua, $item) !== false){
                $viewName = 'previewPdfIE';
                break;
            }
        }
        $fileData = M('file_upload') -> find($contractData['file_id']);
        if(empty($fileData['path'])){
            $this->error("当前PDF路径没有找到");
            die("当前PDF路径没有找到");
        }
        if (file_exists(WORKING_PATH . $fileData['path'])){
            $recordModel = new FileRecordModel();
            $res = $recordModel -> previewPdfFileRecord($contractData['file_id'], "file_upload");
            if ($res === false){
                $this->error('预览记录更新失败');
            }
            $this->assign([
                'url' => $fileData['path']
            ]);
            if ($viewName == 'previewPdf'){
                $messy = sha1($fileData['path']);
                $messy = str_repeat($messy, 5);
                redirect(U('previewPdfChrome', '', '') . '?fileUrl='. $messy . '&file='. $fileData['path'] . '&path='. $messy);
            }else{
                $this->display($viewName);
            }
        }else{
            $this->error('找不到文件');
        }
    }

    /**
     * 对应Chrome浏览器的预览pdf
     */
    public function previewPdfChrome()
    {
        $this->display("previewPdf");
    }

    // =========================================

    /**
     * 订单列表 未审核
     */
    public function orderIndex(){
        $orderModel = new PurchaseOrderModel();
        $checkFlag = arrayIntersect(explode(',',session('deptRoleId')),self::$orderAuthRoleIdArray);
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $statusArray = [PurchaseOrderModel::TYPE_NOT_AUDIT,PurchaseOrderModel::TYPE_UNQUALIFIED,PurchaseOrderModel::TYPE_QUALIFIED];
            $status = in_array((int)$this->posts['audit_status'],$statusArray)
                ? (int)$this->posts['audit_status']
                : PurchaseOrderModel::TYPE_NOT_AUDIT;
            $map['o.audit_status'] = ['eq', $status];

            $staffLimit = $this->posts['staffLimit'];  // 1=>本人 2=>所有
            if ($staffLimit == 1){
                $map['o.create_id'] = ['eq', session('staffId')];
            }else {
                if(!$checkFlag){
                    // 获取查看的当前职位能查看的供应商
                    $staffStr = self::getStaffRoleAuth();
                    if ($staffStr === false){
                        $this->ajaxReturn([
                            "draw"            => 0,
                            "recordsTotal"    => 0,
                            "recordsFiltered" => 0,
                            "data"            => []
                        ]);
                    }
                    $map['o.create_id'] = ["in", $staffStr];
                }
            }

            list($contractData,$count,$recordsFiltered) = $orderModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $contractData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign(array(
                'auditMsg'  => PurchaseOrderModel::$auditStatus,
                'checkFlag' => $checkFlag
            ));
            $this->display();
        }
    }


    /**
     * 获取一个订单下的物料信息
     */
    public function getOrderProduct(){
        if(IS_POST){
            $orderId = I("post.id");
            $contractProductModel = new PurchaseOrderProductModel();
            $data  = $contractProductModel->getAllMaterialMsg($orderId);
            $this->returnAjaxMsg("获取成功",200, $data);
        }else {
            die("非法");
        }
    }

    /**
     * 生成订单编号
     */
    public function createOrderId(){
        $createId = new MaxIdModel();
        $id = $createId->getMaxId('order');
        if($id){
            $this->returnAjaxMsg('获取编号成功', 200, [
                'orderIdString' => 'CGD-' . $id,
                'id' => $id
            ]);
        }else {
            $this->returnAjaxMsg('获取编号失败', 401);
        }
    }

    /**
     * 获取物料信息
     */
    public function getProductMsg(){
        if(IS_POST){
            $data = I("post.");

            $map = [];
            if(isset($data['condition'])){
                $map['product_no'] = ['LIKE', "%" . $data['condition'] . "%"];
                $map['product_name'] = ['LIKE', "%" . $data['condition'] . "%"];
                $map['product_number'] = ['LIKE', "%" . $data['condition'] . "%"];
                $map['_logic'] = 'or';
            }

            $materialModel = new MaterialModel();
            $materialData = $materialModel->where($map)->field('product_id,product_no,product_name,product_number,warehouse_id')->limit(15)->select();
            $this->returnAjaxMsg('ok',200, $materialData);
        }else {
            die('非法');
        }
    }

    /**
     * 生成订单
     * 通过get方式传参（合同ID），渲染页面
     * 通过ajax请求添加订单相关数据
     */
    public function createOrderWithContract()
    {
        $orderModel = new PurchaseOrderModel();
        $contractModel = new PurchaseContractModel();

        if (IS_POST) {
            $data = I('post.');
            if(empty($data['id']) || empty($data['orderId']) || empty($data['contractId']) || empty($data['purchaseMode']) || empty($data['purchaseType'])){
                $this->returnAjaxMsg('参数不全', 400);
            }

            $rst = $orderModel->createOrderByContract($data['id'], $data['contractId'], $data['orderId'],  $data['purchaseMode'],  $data['purchaseType']);
            $this->ajaxReturn($rst);
        } else {
            $contractId = I('get.contractId');
            $data = $contractModel->getContractData($contractId, ['contract', 'product']);

            // 判断当前操作人是否是创建改合同的人
            if($data['contract']['create_id'] != $this->staffId){
                die("您不是该合同的创建人，不能对此进行操作");
            }

            if(empty($data['contract']['file_id'])) {
                die("当前合同未上传附件，不可以生成订单");
            }

            // 判断当前合同是否已经审核通过
            if($data['contract']['audit_status'] != PurchaseContractModel::TYPE_LAY_QUALIFIED){
                die("当前合同未审核通过");
            }

            //判断当前合同是否已经存在订单
            $orderData = $orderModel->where(['contract_pid' => $data['contract']['id'],'is_del' => PurchaseOrderModel::$notDel])->find();
            if(!empty($orderData)){
                die("当前合同已经存在订单，无需再添加");
            }

            $this->assign(compact('data'));
            $this->display();
        }
    }

    /**
     * 订单详情
     */
    public function getOrderMsg($id){
        if (IS_POST) {
            $orderIdModel = new PurchaseOrderModel();
            $data = $orderIdModel->getOrderData($id, ['order', 'product']);
            $this->returnAjaxMsg("数据返回成功",200, $data);
        } else {
            $this->assign(compact('id'));
            $this->display('order');
        }
    }

    /**
     * 修改订单相关信息
     */
    public function editOrder(){
        if (IS_POST) {
            $this->posts = I('post.');
            $purchaseOrderModel = new PurchaseOrderModel();
            $rst = $purchaseOrderModel->editOrderTrans($this->posts);
            $this->ajaxReturn($rst);
        } else {
            $orderId = I('get.orderId');
            $orderIdModel = new PurchaseOrderModel();
            $data = $orderIdModel->getOrderData($orderId, ['order', 'product']);
            // 判断当前操作人是否是创建改订单的人
            if($data['order']['create_id'] != session('staffId')){
                die('当前用户不可以对此订单进行修改操作');
            }
            if($data['order']['audit_status'] == PurchaseOrderModel::TYPE_QUALIFIED){
                die('当前订单已审核通过，不能在进行修改');
            }

            $this->assign(compact('data'));
            $this->display();
        }
    }

    /**
     * 订单审核
     */
    public function auditOrder(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['orderId']) || empty($data['status'])){
                $this->returnAjaxMsg("参数不全", 401);
            }
            $orderModel = new PurchaseOrderModel();
            $auditData = $orderModel->where(['id' => $data['orderId']])->field('audit_status')->find();
            if($auditData['audit_status'] == PurchaseOrderModel::TYPE_NOT_AUDIT){
                $res = $orderModel->where(['id' => $data['orderId']])->setField(['audit_status' => $data['status'],'update_id' => session("staffId"),'update_time' => time()]);
                if($res){
                    $this->returnAjaxMsg('审核成功', 200);
                }else {
                    $this->returnAjaxMsg('审核失败', 402);
                }
            }else {
                $this->returnAjaxMsg('已审核，不能再次审核', 402);
            }
        }else {
            die('非法');
        }
    }

    /**
     * 删除订单操作
     */
    public function deleteOrder(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['orderId'])){
                $this->returnAjaxMsg("参数不全", 401);
            }

            $orderModel = new PurchaseOrderModel();
            $res = $orderModel->delOrder($data['orderId']);
            $this->ajaxReturn($res);
        }else {
            die('非法');
        }
    }

    /**
     * 删除订单中某个物料
     */
    public function deleteOrderProduct(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['productId']) || empty($data['orderId'])){
                $this->returnAjaxMsg("参数不全", 401);
            }

            $orderModel = new PurchaseOrderModel();
            $res = $orderModel->delOrderProduct($data['orderId'], $data['productId']);
            $this->ajaxReturn($res);
        }else {
            die('非法');
        }
    }

    //===========================================================
    /**
     * 订单下所有物料的进度列表
     */
    public function orderScheduleList(){
        $scheduleModel = new PurchaseOrderScheduleModel();
        if(IS_POST){
            $postData = I('post.');
            if(empty($postData['page_sizes']) || !isset($postData['stockType'])){
                $this->returnAjaxMsg("参数错误", 400);
            }

            $start = $postData['page_sizes'] * ($postData['current_page'] - 1);

            switch ($postData['stockType']){
                case 1:
                    // 入库完成
                    $stockMap = "ifnull(sum(sm.num),0) = p.number";
                    break;
                case 2:
                    //未入库完成
                    $stockMap = "ifnull(sum(sm.num),0) < p.number";
                    break;
                default:
                    $this->returnAjaxMsg("参数错误", 400);
                    break;
            }

            // 判断当前人职位  1-总经理 45-采购部经理 查看全部
            $map = [];
            $checkFlag = arrayIntersect(explode(',',session('deptRoleId')),[1,45]);
            if (!$checkFlag){
                $map['o.create_id'] = ['eq', session("staffId")];
            }

            list($scheduleData,$totalCount) = $scheduleModel->getList($postData['condition'], $start,$postData['page_sizes'], $stockMap, $map);

            $this->returnAjaxMsg("数据获取成功", 200, [
                'data' => $scheduleData,
                'page_sizes' => $postData['page_sizes'],
                'current_page' => $postData['current_page'],
                'total' => $totalCount,
            ]);
        }else {
            $this->assign([
                "sendMap"     => PurchaseOrderScheduleModel::$sendMap,
                "page_sizes"  => 20,
            ]);
            $this->display();
        }
    }

    /**
     * 订单下物料新增采购进度
     */
    public function addOrderSchedule(){
        if(IS_POST){
            $postData = I("post.");

            if(empty($postData['orderId']) || empty($postData['orderProductId']) || empty($postData['scheduleData'])){
                $this->returnAjaxMsg("参数不全",400);
            }
            $scheduleModel = new PurchaseOrderScheduleModel();

            $res = $scheduleModel->createScheduleMany($postData['orderId'], $postData['orderProductId'], $postData['scheduleData']);

            $this->ajaxReturn($res);
        }else{
            $id = I("get.id"); // 当前订单下物料的ID
            if(empty($id)){
                die("参数错误");
            }

            $productModel = new PurchaseOrderProductModel();
            $data = $productModel->getOrderProductMsgOne($id);
            $this->assign([
                "sendMap" => PurchaseOrderScheduleModel::$sendMap,
                'data' => $data
            ]);
        }
    }

    /**
     * 订单下物料修改采购进度
     */
    public function editOrderSchedule(){
        $scheduleModel = new PurchaseOrderScheduleModel();

        if(IS_POST){
            $postData = I("post.");
            if(empty($postData)){
                $this->returnAjaxMsg("参数不全",400);
            }

            list($code, $msg) = $scheduleModel->editData($postData);

            if($code != 0){
                $this->returnAjaxMsg($msg, 400);
            }

            $this->returnAjaxMsg("修改物料采购进度成功", 200);
        }else{
            $productId = I("get.productId"); // 当前订单下物料的ID
            $scheduleId = I("get.scheduleId"); // 当前采购进度记录的ID
            if(empty($id)){
                die("参数错误");
            }
            $productModel = new PurchaseOrderProductModel();
            $data = $productModel->getOrderProductMsgOne($productId);

            $scheduleData = $scheduleModel->find($scheduleId);


            $this->assign([
                "sendMap" => PurchaseOrderScheduleModel::$sendMap,
                'data' => $data,
                'scheduleData' => $scheduleData,
            ]);
            $this->display();

        }
    }

    /**
     * 订单下物料采购进度记录删除
     */
    public function delOrderSchedule(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                $this->returnAjaxMsg("参数错误", 400);
            }
            $scheduleModel = new PurchaseOrderScheduleModel();

            $res = $scheduleModel->where(['id' => $id])->setField(['update_id' => session("staffId"), "update_time" => time(), "is_del" => PurchaseOrderScheduleModel::IS_DEL]);
            if(!$res){
                $this->returnAjaxMsg($scheduleModel->getError(), 400);
            }
            $this->returnAjaxMsg("删除成功", 200);
        }else {
            die("非法");
        }
    }

    //=========================================================


    /**
     * 入库单修改
     */
    public function editStock(){
        $stockModel = new StockInModel();
        $materialModel = new StockMaterialModel();

        // 入库分类map
        $cateModel = new StockIoCateModel();
        $cateMap = $cateModel->index();
        if(IS_POST){
            $postData = I("post.");
            if(empty($postData['editMaterial']) && empty($postData['stock'])){
                $this->returnAjaxMsg("未获取有效参数", self::$failStatus);
            }

            // 入库单修改 只能修改物料的数量，不能对其进行物料的新增和修改其库名
            $stockData = $postData['stock'];
            $editMaterialData = $postData['editMaterial'];
            $data = $stockModel->editStockByPurchase($editMaterialData, $stockData);
            $this->ajaxReturn($data);
        }else {
            $stockId = I('get.stockId');
            $stockData = $stockModel->findByStockId($stockId);
            if($stockData['audit_status'] != $stockModel::TYPE_QUALIFIED){
                die("当前订单不是不合格订单，不可修改");
            }

            if($stockData['create_id'] != session("staffId")){
                die("当前操作者不是制单人本人，所以不可以修改此入库单");
            }

            $map['m.type'] = ['eq', StockMaterialModel::TYPE_STOCK_IN];
            $materialData = $materialModel->selectByStockId($stockId, $map);

            $productModel = new PurchaseOrderProductModel();
            $product = $productModel->getAllMaterialMsg($stockData['source_id']);

            $surplusNum = array_column($product, "surplusnum", $product['product_id']);

            foreach ($materialData as $key => &$value){
                $value['surplusnum'] = 0;
                if(isset($surplusNum[$value['product_id']])){
                    $value['surplusnum'] = $value['num'] + $surplusNum[$value['product_id']];
                }
            }


            //仓库名称map  从crm_repertorylist 表中查出
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getRepInfoList();

            $this->assign([
                'stockData' => $stockData,
                'materialData' => $materialData,
                'cateMap'   => $cateMap,
                'repMap'    => $repMap
            ]);
            $this->display();

        }
    }

    /**
     * 入库单删除
     */
    public function deleteStock(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['stockId'])){
                return $this->returnAjaxMsg("参数获取不全",400);
            }
            $stockId = $data['stockId'];

            $stockModel = new StockInModel();
            // 删除入库单
            $result = $stockModel->deleteStock($stockId);
            $this->ajaxReturn($result);
        }else {
            die("非法");
        }
    }

    /**
     * 入库单物料删除
     */
    public function deleteStockMaterial(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['materialId'])){
                return $this->returnAjaxMsg("参数获取不全",400);
            }
            $materialId = $data['materialId'];

            $stockMaterialModel = new StockMaterialModel();
            // 删除入库单
            $result = $stockMaterialModel->deleteStockMaterial($materialId);
            $this->ajaxReturn($result);
        }else {
            die("非法");
        }
    }

    /**
     * 入库单审核
     */
    public function auditStock(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['stockId']) || empty($data['status']) || !isset($data['audit_tips'])){
                $this->returnAjaxMsg("参数不全", 401);
            }
            $stockModel = new StockInModel();
            $data = $stockModel->where(['id' => $data['stockId']])->field('audit_status')->find();
            if($data['audit_status'] == StockInModel::TYPE_NOT_AUDIT){
                $res = $stockModel->where(['id' => $data['stockId']])->setField(['audit_status' => $data['status'], 'auditor_name' => session('nickname'), 'auditor' => session('staffId'),'audit_tips' => $data['audit_tips'],'update_time' => time()]);
                if($res){
                    $this->returnAjaxMsg('审核成功', 200);
                }else {
                    $this->returnAjaxMsg('审核失败', 402);
                }
            }else {
                $this->returnAjaxMsg('已审核，不能再次审核', 402);
            }
        }else {
            die('非法');
        }
    }

    /**
     * 入库单下推，生成具体每个库的入库单
     */
    public function createRecord(){
        $recordModel = new StockInRecordModel();
        if(IS_POST){
            $postData = I("post.");
            if(empty($postData['stockId']) || empty($postData['cate_id']) || empty($postData['batch']) || empty($postData['material'])){
                $this->returnAjaxMsg("参数不全", 400);
            }

            // 入库单下推
            $res = $recordModel->stockInToRecord($postData['stockId'], $postData['cate_id'], $postData['batch'], $postData['material']);
            $this->ajaxReturn($res);

        }else {
            // 获取入库单id
            $stockId = I('get.id');

            if(empty($stockId)){
                die("参数不全");
            }
            $stockModel = new StockInModel();
            $materialModel = new StockMaterialModel();

            $stockData = $stockModel->findByStockId($stockId);
            $map['m.type'] = ['eq', StockMaterialModel::TYPE_STOCK_IN];
            $materialData = $materialModel->selectByStockId($stockId, $map);
            $this->assign([
                'stockData' => $stockData,
                'materialData' => $materialData,
            ]);
            $this->display();

        }
    }

    /**
     * 上传证书文件接口
     */
    public function upload(){
        if (IS_POST) {
            $data = I('post.');

            if(empty($data['type'])){
                $this->returnAjaxMsg('参数错误', 401);
            }
            $fileModel = new FileUploadModel();

            switch ($data['type']){
                case $fileModel::TYPE_CERTIFICATION :
                    $description = '供应商资质证书';
                    $path = UPLOAD_ROOT_PATH . '/supplier/certification/';
                    break;
                case $fileModel::TYPE_AWARDS :
                    $description = '供应商奖金证书';
                    $path = UPLOAD_ROOT_PATH . '/supplier/awards/';
                    break;
                case $fileModel::TYPE_SYSTEM_ATTEST :
                    $description = '审核步骤中体系认证上传附件';
                    $path = UPLOAD_ROOT_PATH . '/supplier/audit/';
                    break;
                case $fileModel::TYPE_SYSTEM_FRAMEWORK :
                    $description = '审核步骤中体系架构上传附件';
                    $path = UPLOAD_ROOT_PATH . '/supplier/audit/';
                    break;
                case $fileModel::TYPE_QUALITY_REPOSRT :
                    $description = '审核步骤中品质、RoHS测试报告上传附件';
                    $path = UPLOAD_ROOT_PATH . '/supplier/audit/';
                    break;
                case $fileModel::TYPE_SITE_AUDIT :
                    $description = '审核步骤中现场认证上传附件';
                    $path = UPLOAD_ROOT_PATH . '/supplier/audit/';
                    break;
                default :
                    $description = '供应商证书';
                    $path = UPLOAD_ROOT_PATH . '/supplier/';
                    break;
            }

            $description = empty($data['description']) ? $description : $data['description'];

            list($code, $msg , $id) = $fileModel->uploadFile($_FILES, $path, $fileModel::TYPE_SUPPLIER, $description);
            $this->returnAjaxMsg($msg, $code, ['id' => $id]);
        }else {
            die('非法');
        }
    }

    public function getSupplier($id)
    {
        if (IS_POST) {
            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->getSupplierOtherData($id, 'base');
            $this->returnAjaxMsg("数据返回成功",200,$supplierData);
        } else {
            $this->assign(compact('id'));
            $this->display('supplier');
        }
    }

    public function getAddress($id)
    {
        if (IS_POST) {
            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->getSupplierOtherData($id, 'address');
            $this->returnAjaxMsg("数据返回成功",200,$supplierData);
        } else {
            $this->assign(compact('id'));
            $this->display('address');
        }
    }

    public function getContact($id)
    {
        if (IS_POST) {
            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->getSupplierOtherData($id, 'contact');
            $this->returnAjaxMsg("数据返回成功",200,$supplierData);
        } else {
            $this->assign(compact('id'));
            $this->display('contact');
        }
    }

    public function getCertification($id)
    {
        if (IS_POST) {
            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->getSupplierOtherData($id, 'certification');
            $this->returnAjaxMsg("数据返回成功",200,$supplierData);
        } else {
            $this->assign(compact('id'));
            $this->display('certification');
        }
    }

    public function getAwards($id)
    {
        if (IS_POST) {
            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->getSupplierOtherData($id, 'awards');
            $this->returnAjaxMsg("数据返回成功",200,$supplierData);
        } else {
            $this->assign(compact('id'));
            $this->display('awards');
        }
    }

    public function getCustomer($id)
    {
        if (IS_POST) {
            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->getSupplierOtherData($id, 'customer');
            $this->returnAjaxMsg("数据返回成功",200,$supplierData);
        } else {
            $this->assign(compact('id'));
            $this->display('customer');
        }
    }

    public function getEquity($id)
    {
        if (IS_POST) {
            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->getSupplierOtherData($id, 'equity');
            $this->returnAjaxMsg("数据返回成功",200,$supplierData);
        } else {
            $this->assign(compact('id'));
            $this->display('equity');
        }
    }

    public function getFinance($id)
    {
        if (IS_POST) {
            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->getSupplierOtherData($id, 'finance');
            $this->returnAjaxMsg("数据返回成功",200,$supplierData);
        } else {
            $this->assign(compact('id'));
            $this->display('finance');
        }
    }

    public function getTeam($id)
    {
        if (IS_POST) {
            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->getSupplierOtherData($id, 'team');
            $this->returnAjaxMsg("数据返回成功",200,$supplierData);
        } else {
            $this->assign(compact('id'));
            $this->display('team');
        }
    }

    public function getCooperation($id)
    {
        if (IS_POST) {
            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->getSupplierOtherData($id, 'cooperation');
            $this->returnAjaxMsg("数据返回成功",200,$supplierData);
        } else {
            $this->assign(compact('id'));
            $this->display('cooperation');
        }
    }
    public function getAudit($id)
    {
        if (IS_POST) {
            $supplier = new PurchaseSupplierModel();
            $supplierData = $supplier->getSupplierOtherData($id, 'audit');
            $this->returnAjaxMsg("数据返回成功",200,$supplierData);
        } else {
            $this->assign(compact('id'));
            $this->display('audit');
        }
    }


    /**
     * 供应商二级审核列表
     */
    public function supplierSecondAuditIndex(){
        $supplier = new PurchaseSupplierModel();
        $checkFlag = arrayIntersect(explode(',',session('deptRoleId')),self::$supplierAuthRoleIdArray);
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $staffLimit = $this->posts['staffLimit'];  // 1=>本人 2=>所有

            $map = [];
            if ($staffLimit == 1){
                $map['p.charger'] = ['eq', session('staffId')];
            }else {
                if(!$checkFlag){
                    // 获取查看的当前职位能查看的供应商
                    $staffStr = self::getStaffRoleAuth();
                    if ($staffStr === false){
                        $this->ajaxReturn([
                            "draw"            => 0,
                            "recordsTotal"    => 0,
                            "recordsFiltered" => 0,
                            "data"            => []
                        ]);
                    }
                    $map['p.charger'] = ["in", $staffStr];
                }
            }

            list($supplierData,$count,$recordsFiltered) = $supplier->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], 2, $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $supplierData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign(array(
                'supplierOtherMsg' => PurchaseSupplierModel::$supplierOtherMsg,
                'auditMsg' => PurchaseSupplierModel::$auditTypeMap,
                'listedMsg' => PurchaseSupplierModel::$listedMap,
                'checkFlag' => $checkFlag
            ));
            $this->display();
        }

    }

    /**
     * 供应商审核完成列表
     */
    public function supplierQualifiedIndex(){
        $supplier = new PurchaseSupplierModel();
        $checkFlag = arrayIntersect(explode(',',session('deptRoleId')),self::$supplierAuthRoleIdArray);
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $staffLimit = $this->posts['staffLimit'];  // 1=>本人 2=>所有

            $map = [];
            if ($staffLimit == 1){
                $map['p.charger'] = ['eq', session('staffId')];
            }else {
                if(!$checkFlag){
                    // 获取查看的当前职位能查看的供应商
                    $staffStr = self::getStaffRoleAuth();
                    if ($staffStr === false){
                        $this->ajaxReturn([
                            "draw"            => 0,
                            "recordsTotal"    => 0,
                            "recordsFiltered" => 0,
                            "data"            => []
                        ]);
                    }
                    $map['p.charger'] = ["in", $staffStr];
                }
            }

            list($supplierData,$count,$recordsFiltered) = $supplier->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], 3, $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $supplierData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign(array(
                'supplierOtherMsg' => PurchaseSupplierModel::$supplierOtherMsg,
                'auditMsg' => PurchaseSupplierModel::$auditTypeMap,
                'listedMsg' => PurchaseSupplierModel::$listedMap,
                'checkFlag' => $checkFlag
            ));
            $this->display();
        }

    }

    /**
     * 供应商审核不合格列表
     */
    public function supplierUnQualifiedIndex(){
        $supplier = new PurchaseSupplierModel();
        $checkFlag = arrayIntersect(explode(',',session('deptRoleId')),self::$supplierAuthRoleIdArray);
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $staffLimit = $this->posts['staffLimit'];  // 1=>本人 2=>所有

            $map = [];
            if ($staffLimit == 1){
                $map['p.charger'] = ['eq', session('staffId')];
            }else {
                if(!$checkFlag){
                    // 获取查看的当前职位能查看的供应商
                    $staffStr = self::getStaffRoleAuth();
                    if ($staffStr === false){
                        $this->ajaxReturn([
                            "draw"            => 0,
                            "recordsTotal"    => 0,
                            "recordsFiltered" => 0,
                            "data"            => []
                        ]);
                    }
                    $map['p.charger'] = ["in", $staffStr];
                }
            }

            list($supplierData,$count,$recordsFiltered) = $supplier->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], 4, $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $supplierData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign(array(
                'supplierOtherMsg' => PurchaseSupplierModel::$supplierOtherMsg,
                'auditMsg' => PurchaseSupplierModel::$auditTypeMap,
                'listedMsg' => PurchaseSupplierModel::$listedMap,
                'checkFlag' => $checkFlag
            ));
            $this->display();
        }
    }

    /**
     * 合同列表  审核成功
     */
    public function contractIndexAuditAccess(){
        $contract = new PurchaseContractModel();
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $map['c.audit_status'] = ['eq', PurchaseContractModel::TYPE_QUALIFIED];
            if(!arrayIntersect(explode(',',session('deptRoleId')),self::$orderAuthRoleIdArray)){
                // 获取查看的当前职位能查看的供应商
                $staffStr = self::getStaffRoleAuth();
                if ($staffStr === false){
                    $this->ajaxReturn([
                        "draw"            => 0,
                        "recordsTotal"    => 0,
                        "recordsFiltered" => 0,
                        "data"            => []
                    ]);
                }
                $map['c.create_id'] = ["in", $staffStr];
            }

            list($contractData,$count,$recordsFiltered) = $contract->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $contractData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign(array(
                'auditMsg' => PurchaseContractModel::$auditStatus,
            ));
            $this->display();
        }
    }

    /**
     * 合同列表  审核失败
     */
    public function contractIndexAuditFail(){
        $contract = new PurchaseContractModel();
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $map['c.audit_status'] = ['eq', PurchaseContractModel::TYPE_UNQUALIFIED];
            if(!arrayIntersect(explode(',',session('deptRoleId')),self::$orderAuthRoleIdArray)){
                // 获取查看的当前职位能查看的供应商
                $staffStr = self::getStaffRoleAuth();
                if ($staffStr === false){
                    $this->ajaxReturn([
                        "draw"            => 0,
                        "recordsTotal"    => 0,
                        "recordsFiltered" => 0,
                        "data"            => []
                    ]);
                }
                $map['c.create_id'] = ["in", $staffStr];
            }

            list($contractData,$count,$recordsFiltered) = $contract->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $contractData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign(array(
                'auditMsg' => PurchaseContractModel::$auditStatus,
            ));
            $this->display();
        }
    }

    /**
     * 订单列表 审核通过
     */
    public function orderIndexAuditAccess(){
        $orderModel = new PurchaseOrderModel();
        $checkFlag = arrayIntersect(explode(',',session('deptRoleId')),self::$orderAuthRoleIdArray);
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $map['o.audit_status'] = ['eq', PurchaseOrderModel::TYPE_QUALIFIED];
            if(!$checkFlag) {
                // 获取查看的当前职位能查看的供应商
                $staffStr = self::getStaffRoleAuth();
                if ($staffStr === false){
                    $this->ajaxReturn([
                        "draw"            => 0,
                        "recordsTotal"    => 0,
                        "recordsFiltered" => 0,
                        "data"            => []
                    ]);
                }
                $map['o.create_id'] = ["in", $staffStr];
            }
            list($contractData,$count,$recordsFiltered) = $orderModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $contractData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign(array(
                'auditMsg' => PurchaseOrderModel::$auditStatus,
                'checkFlag' => $checkFlag
            ));
            $this->display();
        }
    }

    /**
     * 订单列表 审核失败
     */
    public function orderIndexAuditFail(){
        $orderModel = new PurchaseOrderModel();
        $checkFlag = arrayIntersect(explode(',',session('deptRoleId')),self::$orderAuthRoleIdArray);
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $map['o.audit_status'] = ['eq', PurchaseOrderModel::TYPE_UNQUALIFIED];
            if(!$checkFlag) {
                // 获取查看的当前职位能查看的供应商
                $staffStr = self::getStaffRoleAuth();
                if ($staffStr === false){
                    $this->ajaxReturn([
                        "draw"            => 0,
                        "recordsTotal"    => 0,
                        "recordsFiltered" => 0,
                        "data"            => []
                    ]);
                }
                $map['o.create_id'] = ["in", $staffStr];
            }

            list($contractData,$count,$recordsFiltered) = $orderModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $contractData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign(array(
                'auditMsg' => PurchaseOrderModel::$auditStatus,
                'checkFlag' => $checkFlag
            ));
            $this->display();
        }
    }

}
