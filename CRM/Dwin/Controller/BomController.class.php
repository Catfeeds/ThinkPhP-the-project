<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/8/3
 * Time: 上午9:53
 */
namespace Dwin\Controller;

use Dwin\Model\MaterialBomLogModel;
use Dwin\Model\MaterialBomModel;
use Dwin\Model\MaterialBomSubModel;
use Dwin\Model\MaterialModel;
use Dwin\Model\MaterialSubstituteModel;
use Dwin\Model\MaxIdModel;

class BomController extends CommonController
{
    static protected $failStatus    = 400;
    static protected $successStatus = 200;

    /**
     * bom未审核列表
     */
    public function bomIndex(){
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $bomModel = new MaterialBomModel();
            list($contractData,$count,$recordsFiltered) = $bomModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $this->posts['bom_status']);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $contractData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign([
                'auditMsg' => MaterialBomModel::$bomTypeMap,
                'groupMap' => MaterialBomModel::$bomGroupMap,
                "statusMap" => MaterialBomModel::$statusMap,
                "logType" => MaterialBomLogModel::$bomTypeMap, // log操作类型
            ]);
            $this->display();
        }
    }

    /**
     * bom详情页
     */
    public function bomAllMsg(){
        $bomId = I("get.bomId");
        if(empty($bomId)){
            die("参数错误");
        }

        $materialBomModel = new MaterialBomModel();
        $bomMsg = $materialBomModel->findBomBaseMsg($bomId);

        $materialBomSubModel = new MaterialBomSubModel();
        $bomSubMsg = $materialBomSubModel->findBomOtherMsg($bomId);

        $logModel = new MaterialBomLogModel();
        $logData = $logModel->where(['bom_pid' => $bomId])->select();
        $this->assign([
            'bomSub' => $bomSubMsg, // 物料信息
            'bom' => $bomMsg,  // 基本信息
            'groupMap' => MaterialBomModel::$bomGroupMap, // 组别map
            'logData' => $logData,  // log数据
            "logType" => MaterialBomLogModel::$bomTypeMap, // log操作类型
        ]);
        $this->display();
    }

    /**
     * 获取bom下方物料信息
     */
    public function bomSumMsg(){
        if(IS_POST){
            $bomId = I("post.id");
            if(empty($bomId)){
                $this->returnAjaxMsg("参数错误",400);
            }
            $bomSumModel = new MaterialBomSubModel();
            $bomSumData = $bomSumModel->findBomOtherMsg($bomId);

            $logModel = new MaterialBomLogModel();
            $logData = $logModel->where(['bom_pid' => $bomId])->select();

            $this->returnAjaxMsg("返回成功", 200,[
                'logData' => $logData,  // log数据
                'bomSumData' => $bomSumData,  // BOM 物料数据

            ]);

        }else {
            die("非法");
        }
    }

    /**
     * bom 生成
     */
    public function createBom(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['bom']) || empty($data['bomSub'])){
                $this->returnAjaxMsg("参数不全", 400);
            }
            $materialBomModel = new MaterialBomModel();
            $res = $materialBomModel->createBom($data);
            $this->ajaxReturn($res);
        }else {
            $this->assign([
                'groupMap' => MaterialBomModel::$bomGroupMap,
            ]);
            $this->display("createBom");
        }
    }

    /**
     * 生成bom编号
     */
    public function createBomId(){
        $createId = new MaxIdModel();
        $id = $createId->getMaxId('bom');
        if($id){
            $this->returnAjaxMsg('获取编号成功', 200, [
                'bomIdString' => 'BOM'  . $id,
                'id' => $id
            ]);
        }else {
            $this->returnAjaxMsg('获取编号失败', 401);
        }
    }


    /**
     * bom 修改
     */
    public function editBom(){
        $materialBomModel = new MaterialBomModel();
        if(IS_POST){
            $data = I("post.");
            $result = $materialBomModel->editBomAllMsg($data);
            $this->ajaxReturn($result);

        }else {
            $bomId = I("get.bomId");
            if(empty($bomId)){
                die("参数错误");
            }

            $bomMsg = $materialBomModel->findBomBaseMsg($bomId);

            if ($bomMsg['bom_status'] == MaterialBomModel::TYPE_QUALIFIED){
                die("当前bom已启动，不可修改");
            }

            $materialBomSubModel = new MaterialBomSubModel();
            $bomSubMsg = $materialBomSubModel->findBomOtherMsg($bomId);
            $this->assign([
                'bomSub' => $bomSubMsg,
                'bom' => $bomMsg,
                'groupMap' => MaterialBomModel::$bomGroupMap,
            ]);
            $this->display();
        }
    }

    /**
     * 删除bom
     */
    public function deleteBom(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['bomId'])) {
                $this->returnAjaxMsg("参数错误", 400);
            }

            $materialBomModel = new MaterialBomModel();
            $res = $materialBomModel->deleteBom($data['bomId']);
            $this->ajaxReturn($res);
        }else {
            die("非法");
        }
    }

    /**
     * 删除bomSub
     */
    public function deleteBomSub(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['bomSubId']) || empty($data['bomId'])) {
                $this->returnAjaxMsg("参数错误", 400);
            }

            $materialBomSubModel = new MaterialBomSubModel();
            $res = $materialBomSubModel->deleteBomSub($data['bomId'], $data['bomSubId']);
            $this->ajaxReturn($res);
        }else {
            die("非法");
        }
    }

    /**
     * 禁用bom
     */
    public function bomForbidden(){
       if(IS_POST){
           $bomId = I("post.bomId");
           if(empty($bomId)){
               $this->returnAjaxMsg("参数错误",400);
           }

           $bomModel = new MaterialBomModel();
           $bomRes = $bomModel->where(['id' => $bomId])->setField(['bom_status' => MaterialBomModel::TYPE_FORBIDDEN]);
           if(!$bomRes){
               $this->returnAjaxMsg("禁用BOM失败",400);
           }

           // BOM 操作履历
           $logModel = new MaterialBomLogModel();
           list($logMsg, $logCode) = $logModel->createBomLog($bomId, MaterialBomLogModel::TYPE_FORBIDDEN, "BOM被禁用");
           if($logCode != 200){
               M()->rollback();
               return dataReturn($logMsg, 400);
           }

           $this->returnAjaxMsg("禁用BOM成功",200);
       }else {
           die("非法");
       }
    }

    /**
     * 解除bom禁用
     */
    public function bomRelieveForbidden(){
        if(IS_POST){
            $bomId = I("post.bomId");
            if(empty($bomId)){
                $this->returnAjaxMsg("参数错误",400);
            }

            $bomModel = new MaterialBomModel();
            $bomRes = $bomModel->where(['id' => $bomId])->setField(['bom_status' => MaterialBomModel::TYPE_QUALIFIED]);
            if(!$bomRes){
                $this->returnAjaxMsg("解禁BOM失败",400);
            }

            // BOM 操作履历
            $logModel = new MaterialBomLogModel();
            list($logMsg, $logCode) = $logModel->createBomLog($bomId, MaterialBomLogModel::TYPE_FORBIDDEN, "BOM被解禁");
            if($logCode != 200){
                M()->rollback();
                return dataReturn($logMsg, 400);
            }

            $this->returnAjaxMsg("解禁BOM成功",200);
        }else {
            die("非法");
        }
    }




    /**
     * bom审核
     */
    public function auditBom(){
        if(IS_POST){
            $data = I("post.");
            if(empty($data['bomId']) || empty($data['status'])){
                $this->returnAjaxMsg("参数不全", 401);
            }
            M()->startTrans();
            $materialBomModel = new MaterialBomModel();
            $materialData = $materialBomModel->where(['id' => $data['stockId']])->field('bom_status')->find();
            if($materialData['bom_status'] == MaterialBomModel::TYPE_NOT_AUDIT){
                $res = $materialBomModel->where(['id' => $data['bomId']])->setField(['bom_status' => $data['status'], 'update_time' => time(), 'update_id' => session('staffId')]);
                if($res){
                    $logStr = '';
                    switch ($data['status']){
                        case MaterialBomModel::TYPE_QUALIFIED :
                            $logStr = "BOM审核合格";
                            break;
                        case MaterialBomModel::TYPE_UNQUALIFIED :
                            $logStr = "BOM审核不合格";
                            break;
                        default:
                            M()->rollback();
                            $this->returnAjaxMsg("参数不全", 401);
                            break;
                    }
                    // BOM 操作履历
                    $logModel = new MaterialBomLogModel();
                    list($logMsg, $logCode) = $logModel->createBomLog($materialData['bom_pid'], MaterialBomLogModel::TYPE_AUDIT, $logStr);
                    if($logCode != 200){
                        M()->rollback();
                        return dataReturn($logMsg, 400);
                    }
                    M()->commit();
                    $this->returnAjaxMsg('审核成功', 200);
                }else {
                    M()->rollback();
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
     * 读取excel生成bom
     */
    public function uploadByexecl(){
        if (IS_POST){
            // 判断当前登录人是否有权进行文件上传
            $staffFileInfo = M('staff') -> field('max_upload_file_size, allowed_upload_type') -> find(session('staffId'));
            if(!in_array($_FILES['file']['type'],explode(',', $staffFileInfo['allowed_upload_type']))){
                $this->returnAjaxMsg(402,'您没有权限上传此类文件', []);
            }
            if($_FILES['file']['size'] > $staffFileInfo['max_upload_file_size']){
                $this->returnAjaxMsg(403,'您所上传的文件大小超过您的权限所限制的', []);
            }

            $path = WORKING_PATH . UPLOAD_ROOT_PATH . "/bom";

            // 判断是否存在当前文件夹，如果没有就创建
            if (!file_exists($path)) {
                mkdir($path, 0777,true);
            }

            $upload = new \Think\Upload();// 实例化上传类
            $upload->rootPath  =    $path; // 设置附件上传根目录
            // 上传单个文件
            $info   =   $upload->uploadOne($_FILES['file']);

            if(!$info) {
                // 上传错误提示错误信息
                $this->returnAjaxMsg(401, $upload->getError(),[]);
            }
            $materialBomModel = new MaterialBomModel();
            $res = $materialBomModel->readExecl($path . $info['savepath'] . $info['savename']);

//            $path = "/Users/chendongdong/Documents/workspace/dwin/trunk/2-ProductDoc/CRM/Public/Upload/supplier/certification/2018-07-25/nice.xlsx";
//            $res = $materialBomModel->readExecl($path);
            $this->ajaxReturn($res);
        }else {
            die("非法");
        }
    }

    /**
     * 查询bom数据库，生成excel并导出
     */
    public function exportToExcel(){
        $postData = I("post.");
        $materialBomModel = new MaterialBomModel();
        $data = $materialBomModel->exportToExcel($postData);
        $this->ajaxReturn($data);
    }

    /**
     * bom编号列表
     */
    public function getBomIdList(){
        if(IS_POST){
            $bomId = I("post.bom_id");
            $status = I("post.status");
            $group = I("post.group");
            if(!empty($group)){
                $map['bom_type'] = ['eq', $group];
            }
            if(isset(MaterialBomModel::$statusMap[$status])){
                $map['bom_status'] = ['eq', $status];
            }

            $bomModel = new MaterialBomModel();
            $map['bom_id'] = ["like", "%" . $bomId . "%"];
            $map['is_del'] = ["eq", MaterialBomModel::NO_DEL];

            $bomData = $bomModel->where($map)->field("bom_id")->limit(15)->select();
            $this->returnAjaxMsg("获取数据成功", 200, $bomData );
        }else {
            die("非法");
        }
    }

    /**
     * 全部物料的列表
     */
    public function materialAllMsgList(){
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $material = new MaterialModel();
            list($contractData,$count,$recordsFiltered) = $material->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $contractData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign([
                'scopeMap' => MaterialSubstituteModel::$scopeMap
            ]);
            $this->display();
        }
    }

    /**
     * 某个物料的替代物料列表
     */
    public function materialReplaceListByProductId(){
        if(IS_POST){
            $productId = I("post.id");
            $materialSub = new MaterialSubstituteModel();
            $bomSumData = $materialSub->findSubstituteByProductId($productId);
            $this->returnAjaxMsg("返回成功", 200,$bomSumData);
        }else {
            die("非法");
        }
    }

    /**
     * 物料替代新增
     */
    public function addMaterialReplace(){
        if(IS_POST){
            $postData = I("post.");
            if(empty($postData['productNo']) || empty($postData['productId']) || empty($postData['replaceData'])){
                $this->returnAjaxMsg("参数不全", 400);
            }
            $productId = $postData['productId'];  // 被替代物料的物料主键
            $productNo = $postData['productNo'];  // 被替代物料的物料编号
            $replaceData = $postData['replaceData'];  // 替代物料的内容

            $materialSub = new MaterialSubstituteModel();
            $materialSub->startTrans();
            $res = $materialSub->addSubstituteMany($productId, $productNo, $replaceData);
            if($res['status'] != self::$successStatus){
                $materialSub->rollback();
            }else {
                $materialSub->commit();
            }
            $this->ajaxReturn($res);

        }else {
            $productId =I("get.id");
            $materialModel = new MaterialModel();
            $materialData = $materialModel->find($productId);
            $this->assign([
                'materialData' => $materialData,
                'scopeMap' => MaterialSubstituteModel::$scopeMap
            ]);
            $this->display();
        }
    }

    /**
     * 替代物修改
     */
    public function editMaterialReplace(){
        $materialSub = new MaterialSubstituteModel();
        if(IS_POST){
            $postData = I("post.");
            if(empty($postData['productNo']) || empty($postData['productId']) || (empty($postData['newReplaceData']) && empty($postData['editReplaceData']))){
                $this->returnAjaxMsg("参数不全", 400);
            }
            $productId = $postData['productId'];  // 被替代物料的物料主键
            $productNo = $postData['productNo'];  // 被替代物料的物料编号
            $editReplaceData = $postData['editReplaceData'];  // 替代物料的内容
            $newReplaceData = $postData['newReplaceData'];  // 替代物料的内容

            $materialSub->startTrans();
            if(!empty($newReplaceData)){
                $res = $materialSub->addSubstituteMany($productId, $productNo, $newReplaceData);
                if($res['status'] != self::$successStatus){
                    $materialSub->rollback();
                    $this->ajaxReturn($res);
                }
            }

            if(!empty($editReplaceData)){
                list($message, $code) = $materialSub->editSubstituteMany($editReplaceData);
                if($code == -1 && empty($newReplaceData)){
                    $materialSub->rollback();
                    $this->returnAjaxMsg($message,400);
                }
                if($code == -2){
                    $this->returnAjaxMsg($message, 400);
                }
            }
            $materialSub->commit();
            $this->returnAjaxMsg("数据修改成功", 200);
        }else {
            $productId = I("get.id");
            $materialModel = new MaterialModel();
            $materialData = $materialModel->find($productId);
            $substituteData = $materialSub->findSubstituteByProductId($productId);
            $this->assign([
                'materialData' => $materialData,
                'substituteData' => $substituteData,
                'scopeMap' => MaterialSubstituteModel::$scopeMap
            ]);
            $this->display();

        }
    }

    /**
     * 物料替代物删除
     */
    public function delMaterialReplace(){
        if(IS_POST){
            $id = I("post.id");
            if(empty($id)){
                $this->returnAjaxMsg("参数不全", 400);
            }

            $materialSub = new MaterialSubstituteModel();
            $res = $materialSub->where(['id' => $id])->setField(['is_del' => MaterialSubstituteModel::IS_DEL]);
            if(!$res){
                $this->returnAjaxMsg($materialSub->getError(), 400);
            }else {
                $this->returnAjaxMsg("替代物删除成功", 200);
            }
        }else {
            die("非法");
        }
    }

    /**
     * bom审核不合格列表
     */
    public function bomIndexAuditFail(){
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $map['b.bom_status'] = ['eq', MaterialBomModel::TYPE_UNQUALIFIED];
            $bomModel = new MaterialBomModel();
            list($contractData,$count,$recordsFiltered) = $bomModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $contractData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign([
                'auditMsg' => MaterialBomModel::$bomTypeMap,
                'groupMap' => MaterialBomModel::$bomGroupMap,
                "statusMap" => MaterialBomModel::$statusMap,
            ]);
            $this->display();
        }
    }

    /**
     * bom审核通过列表
     */
    public function bomIndexAuditAccess(){
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $map['b.bom_status'] = ['eq', MaterialBomModel::TYPE_QUALIFIED];
            $bomModel = new MaterialBomModel();
            list($contractData,$count,$recordsFiltered) = $bomModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $contractData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign([
                'auditMsg' => MaterialBomModel::$bomTypeMap,
                'groupMap' => MaterialBomModel::$bomGroupMap,
                "statusMap" => MaterialBomModel::$statusMap,
            ]);
            $this->display();
        }
    }

    /**
     * bom禁用列表
     */
    public function bomIndexForbidden(){
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $map['b.bom_status'] = ['eq', MaterialBomModel::TYPE_FORBIDDEN];
            $bomModel = new MaterialBomModel();
            list($contractData,$count,$recordsFiltered) = $bomModel->getList($this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'], $map);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $contractData);
            $this->ajaxReturn($this->output);
        }else {
            $this->assign([
                'auditMsg' => MaterialBomModel::$bomTypeMap,
                'groupMap' => MaterialBomModel::$bomGroupMap,
                "statusMap" => MaterialBomModel::$statusMap,
            ]);
            $this->display();
        }
    }
}