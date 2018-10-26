<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/5/14
 * Time: 10:00
 */
namespace Dwin\Controller;

use Dwin\Model\FileRecordModel;
use think\Exception;

class FileController extends CommonController
{
    const SUCCESS_STATUS   = 200;
    const FAIL_STATUS      = 400;
    const FORBIDDEN_STATUS = 403;
    const ALLOW_EDIT_ROLE  = [1, 2];
    const ALLOW_UPLOAD_SPECIAL = [34,35];
    const ALLOW_DOWNLOAD_SPECIAL = [1,8,3,34,35,40,43,45,52];
    const CATEGORY_SPECIAL = "质量信息";

    private $tableMap = ['file_download', 'file_pdf', 'file_tech'];

    /**
     * 文件基础展示方法
     * @param int $table 表名在$this->$tableMap的角标,
     */
    private function fileIndex($table=0)
    {
        $tableID = $table;
        $table = $this->tableMap[(int) $table];
        if (IS_POST){
            $res = [
                'total' => 0,
                'data' => [],
            ];
            $where = I('post.where');
            $map = [];
            foreach ($where['map'] as $key => $value) {
                if ($value != ''){
                    $map[$key] = ['EQ', $value];
                }
                if ($key == 'file_name'){
                    $map['file_name'] = ['LIKE', "%$value%"];
                }
            }
            if ($table == 'file_pdf') {
                unset($map['file_category']);
            }

            if ($map['file_category'][1] == self::CATEGORY_SPECIAL) {
                if (!$this->checkAuthByRole(self::ALLOW_DOWNLOAD_SPECIAL)){
                    $res = [
                        'data' => [],
                        'total' => 0
                    ];
                    $this->ajaxReturn($res);
                }
            }
            $res['data'] = M($table)
                 -> where($map)
                 -> order($where['order'])
                 -> page($where['page'], 10)
                 -> select();
            foreach ($res['data'] as $key => &$value) {
                $value['update_time'] = date('Y-m-d H:i:s', $value['update_time']);
            }
            $res['total'] = M($table)
                -> where($map)
                -> count();
            $this->ajaxReturn($res);
        }else{
            if ($tableID == 0){
                $map = ['code_name' => ['EQ', '文件分类']];
            }
            if ($tableID == 2){
                $map = ['code_name' => ['EQ', '工艺文件分类']];
            }
            $cate = M('code_manage') -> field('category_name') -> where($map) -> select();
            $this->assign(compact('cate', 'tableID'));
            $this->display('fileIndex');
        }
    }

    /**
     * 公司制度展示页
     */
    public function fileIndexPdf()
    {
        $this->fileIndex(1);
    }

    /**
     * 工艺文件下载展示页
     */
    public function fileIndexTech()
    {
        $this->fileIndex(2);
    }

    /**
     * 文件下载展示页
     */
    public function fileIndexDownload()
    {
        $this->fileIndex(0);
    }

    /**
     * 文件的增删改查基础处理方法
     * @param $method   string  get, post, put, del
     * @param $table    int     表编号
     * @param null $id
     */
    private function file($method, $table, $id=null)
    {
        $tableId = $table;
        $table = $this->tableMap[(int) $table];
        if (IS_POST){
            $params = I('post.data');
            if ($method == 'get'){
                $data = M($table) -> find($id);
                if ($data === false){
                    $status  = self::FAIL_STATUS;
                    $msg = '获取失败';
                }else {
                    $status = self::SUCCESS_STATUS;
                    $msg = '获取成功';
                }
            }
            if ($method == 'put') {
                $file = M($table) -> find($params['id']);
                if ($file['update_name'] !== session('nickname') && !$this->checkAuthByRole(self::ALLOW_EDIT_ROLE)){
                    $this->ajaxReturn([
                        'msg' => '只有上传人本人才可以修改',
                        'status' => self::FORBIDDEN_STATUS,
                    ]);
                }
                $params['update_time'] = time();
                $params['update_name'] = session('nickname');
                $recordModel = new FileRecordModel();
                $recordModel -> startTrans();
                $recordUpdate = $recordModel -> editFileRecord($params, $table);
                // 更新文件前先删除掉旧文件
                $oldFilePath = $file['file_url'];
                if ($oldFilePath != $params['file_url']){
                    unlink(WORKING_PATH . UPLOAD_ROOT_PATH .'/'. $oldFilePath);
                }

                $data = M($table) -> save($params);
                if ($data === false || $recordUpdate === false){
                    $recordModel -> rollback();
                    $status  = self::FAIL_STATUS;
                    $msg = '保存失败';
                }else {
                    $recordModel -> commit();
                    $status = self::SUCCESS_STATUS;
                    $msg = '保存成功';
                }
            }
            if ($method == 'post'){
                if ($table == 0) {
                    if ($params['file_category'] == self::CATEGORY_SPECIAL) {
                        if (!$this->checkAuthByRole(self::ALLOW_UPLOAD_SPECIAL)) {
                            $this->returnAjaxMsg('该部分文件上传有职位限制，您的账户无权限上传，如有问题请联系管理', self::FAIL_STATUS);
                        }
                    }
                }
                if ($params['id'] !== ''){
                    $params['update_time'] = time();
                    $params['update_name'] = session('nickname');
                    $model = M($table);
                    $model -> startTrans();
                    $data = $model -> add($params);
                    $recordModel = new FileRecordModel();
                    $recordUpdate = $recordModel -> addFile($params, $table, $data);
                    if ($data === false || $recordUpdate === false){
                        $model -> rollback();
                        $status  = self::FAIL_STATUS;
                        $msg = '添加失败';
                    }else{
                        $model -> commit();
                        $status  = self::SUCCESS_STATUS;
                        $msg = '添加成功';
                    }
                }else{
                    $orderId = $this->getOrderNumber($table)['orderId'];
                    $this->ajaxReturn([
                        'data' => [
                            'id' => $orderId
                        ]
                    ]);
                }
            }
            if ($method == 'del'){
                if ($id){
                    $file = M($table) -> find($id);
                    if ($file['update_name'] !== session('nickname') && !$this->checkAuthByRole(self::ALLOW_EDIT_ROLE)){
                        $this->ajaxReturn([
                            'msg' => '只有上传人本人才可以删除',
                            'status' => self::FORBIDDEN_STATUS,
                        ]);
                    }
                    $recordModel = new FileRecordModel();
                    $recordModel -> startTrans();
                    $recordUpdate = $recordModel -> delFileRecord($id, $table);
                    $data = M($table) -> delete($id);
                    if ($data === false || $recordUpdate === false){
                        $recordModel -> rollback();
                        $status  = self::FAIL_STATUS;
                        $msg = '删除失败';
                    }else{
                        $recordModel -> commit();
                        $status  = self::SUCCESS_STATUS;
                        $msg = '删除成功';
                    }
                }
            }
            $this->ajaxReturn(compact('status', 'msg', 'data'));
        }else{
            $data = M($table) -> find($id);
            $close = 0;
            if ($data['update_name'] !== session('nickname') && $method != 'post' && !$this->checkAuthByRole(self::ALLOW_EDIT_ROLE)){
                $close = 1;
            }
            $info = compact('method', 'id', 'tableId');
            if ($tableId == 0){
                $map = ['code_name' => ['EQ', '文件分类']];
            }
            if ($tableId == 2){
                $map = ['code_name' => ['EQ', '工艺文件分类']];
            }
            $cate = M('code_manage') -> field('category_name') -> where($map) -> select();
            $this->assign(compact('cate', 'info', 'close'));
            $this->display('file');
        }
    }

    /**
     * 检查权限ajax返回
     * @param $table    int
     * @param $id
     */
    public function fileCheckAuth($table, $id)
    {
        $table = $this->tableMap[$table];
        $data = M($table) -> find($id);
        if ($data['update_name'] !== session('nickname') && !$this->checkAuthByRole(self::ALLOW_EDIT_ROLE)){
            $this->ajaxReturn(['res' => 0]);
        }
        $this->ajaxReturn(['res' => 1]);
    }

    /**
     * 获得文件下载详情
     * @param $id
     */
    public function getFile0($id)
    {
        $this->file('get',0, $id);
    }

    /**
     * 获得公司制度pdf详情
     * @param $id
     */
    public function getFile1($id)
    {
        $this->file('get',1, $id);
    }

    /**
     * 获得工艺文件详情
     * @param $id
     */
    public function getFile2($id)
    {
        $this->file('get',2, $id);
    }

    /**
     * 新增文件下载
     */
    public function postFile0()
    {
        $this->file('post',0);
    }

    /**
     * 新增公司制度pdf
     */
    public function postFile1()
    {
        $this->file('post',1);
    }

    /**
     * 新增工艺文件
     */
    public function postFile2()
    {
        $this->file('post',2);
    }

    /**
     * 修改文件下载
     */
    public function putFile0()
    {
        $this->file('put',0);
    }

    /**
     * 修改公司制度
     */
    public function putFile1()
    {
        $this->file('put',1);
    }

    /**
     * 修改工艺文件
     */
    public function putFile2()
    {
        $this->file('put',2);
    }

    /**
     * 删除文件下载
     * @param $id
     */
    public function delFile0($id)
    {
        $this->file('del',0, $id);
    }

    /**
     * 删除公司制度pdf
     * @param $id
     */
    public function delFile1($id)
    {
        $this->file('del',1, $id);
    }

    /**
     * 删除工艺文件
     * @param $id
     */
    public function delFile2($id)
    {
        $this->file('del',2, $id);
    }

    /**
     * 文件上传方法
     * @param $type 表格的角标
     */
    public function fileUpload()
    {
        $type = $this->tableMap[(int) I('post.type')];
        $id = I('post.id');
        $typeArr = [
            'file_download' => ['att_download', 'DBH-'],
            'file_pdf' => ['att_gspdf', 'GSPDF-'],
            'file_tech' => ['att_gyfile', 'GYFBH-'],
        ];
        $dir = $typeArr[$type][0];
        $fileIdPrefix= $typeArr[$type][1];
        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/fileUpload/" . $dir . "/";
        if (!file_exists($rootPath)) {
            mkdir($rootPath);
        }
        $saveName = $id . '_' . date('YmdHis');
        $staffFileInfo = M('staff') -> field('max_upload_file_size, allowed_upload_type') -> find(session('staffId'));
        $cfg = [
            'autoSub' => false,
            'saveName' => $saveName,
            'rootPath' => $rootPath, // 保存根路径
            'replace'  => true,
            'maxSize' => $staffFileInfo['max_upload_file_size'],
            'exts' => ['xls','xlsx','pdf','rar','zip','jpg','png']
//            'mimes' => ['application/zip', 'application/octet-stream', 'application/x-zip-compressed', 'application/x-rar-compressed', 'application/x-7z-compressed'],
//            'mimes' => explode(',', $staffFileInfo['allowed_upload_type']),
        ];
        if ($type == 'file_pdf'){
            if (in_array('application/pdf', explode(',', $staffFileInfo['allowed_upload_type']))){
                $cfg['mimes'] = ['application/pdf'];
            }else{
                $cfg['mimes'] = [];
            }
        }
        $upload = new \Think\Upload($cfg);// 实例化上传类

        // 上传单个文件
        $info   =   $upload->uploadOne($_FILES['file']);
        if(!$info) {// 上传错误提示错误信息
            $this->ajaxReturn([
                'status' => self::FAIL_STATUS,
                'msg'  => $upload->getError(),
                'data' => $_FILES,
                'cfg'  => $cfg
            ]);
        }else{// 上传成功 获取上传文件信息
            $this->ajaxReturn([
                'status' => self::SUCCESS_STATUS,
                'msg' => '上传成功',
                'data' => [
                    'path' => "fileUpload/" . $dir . "/".$info['savename'],
                    'file_name' => $_FILES['file']['name'],
                    'fileIdPrefix' => $fileIdPrefix
                ],
            ]);
        }
    }

    /**
     * pdf在线预览方法, 分为chrome和ie两种
     * @param $id
     */
    public function previewPdf($id)
    {
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $ie = ['compatible', 'Trident', 'MSIE '];
        $viewName = 'previewPDFChrome';
        foreach ($ie as $item) {
            if (strpos($ua, $item) !== false){
                $viewName = 'previewPDFIE';
                break;
            }
        }
        $url = UPLOAD_ROOT_PATH . '/' . M('file_pdf') -> find($id)['file_url'];
        if (file_exists(WORKING_PATH . $url)){
            $recordModel = new FileRecordModel();
            $res = $recordModel -> previewPdfFileRecord($id, 'file_pdf');
            if ($res === false){
                $this->error('预览记录更新失败');
            }
            $this->assign(compact('url'));
            if ($viewName == 'previewPDFChrome'){
                $messy = sha1($url);
                $messy = str_repeat($messy, 5);
                redirect(U('previewPDFChrome', '', '') . '?fileUrl='. $messy . '&file='. $url. '&path='. $messy);
            }else{
                $this->display($viewName);
            }
        }else{
            $this->error('找不到文件');
        }
    }

    public function previewPDFChrome()
    {
        $this->display();
    }

    /**
     * 获取文件下载的下载地址
     * @param $id
     */
    public function getFileUrl0($id)
    {
        $url = UPLOAD_ROOT_PATH . '/' . M('file_download') -> find($id)['file_url'];
        if (M('file_download') -> find($id)['file_category'] == self::CATEGORY_SPECIAL) {

            if (!$this->checkAuthByRole(self::ALLOW_DOWNLOAD_SPECIAL)) {
                $this->error('该部分文件下载有职位限制，您的账户无权限，如有问题请联系管理');
            }
        }
        if (file_exists(WORKING_PATH . $url)){
            $recordModel = new FileRecordModel();
            $res = $recordModel -> downloadFileRecord($id, 'file_download');
            if ($res === false){
                $this->error('下载记录更新失败');
            }
            redirect($url);
        }else{
            $this->error('找不到文件');
        }
    }

    /**
     * 获取工艺文件的下载地址
     * @param $id
     */
    public function getFileUrl2($id)
    {
        $url = UPLOAD_ROOT_PATH . '/' . M('file_tech') -> find($id)['file_url'];
        if (file_exists(WORKING_PATH . $url)){
            $recordModel = new FileRecordModel();
            $res = $recordModel -> downloadFileRecord($id, 'file_tech');
            if ($res === false){
                $this->error('下载记录更新失败');
            }
            redirect($url);
        }else{
            $this->error('找不到文件');
        }
    }

    /**
     * 文件上传权限展示页
     */
    public function fileUploadAuthManagerIndex()
    {
        if (IS_POST){
            $params = I('post.');
            $tableData = $this->dataTable($params);
            $model = M('staff');
            $data['draw'] = (int) $params['draw'];
            $data['recordsTotal'] = $model -> count();
            $data['recordsFiltered'] = $model -> where($tableData['map']) -> count();
            $data['data'] = $model
                -> field("id, name, max_upload_file_size, allowed_upload_type")
                -> where($tableData['map'])
                -> order($tableData['order'])
                -> limit($params['start'], $params['length'])
                -> select();
            $this->ajaxReturn($data);
        }else{
            $this->display();
        }
    }

    /**
     * 修改文件上传权限
     * @param $id
     */
    public function fileUploadAuthManager($id)
    {
        $staff = M('staff') -> field('id, name, max_upload_file_size, allowed_upload_type') -> find($id);
        if (IS_POST){
            $data = I('post.data');
            try {

                $res = M('staff') -> save($data) === false ? false : true;
                if ($res){
                    $this->ajaxReturn([
                        'status' => self::SUCCESS_STATUS,
                        'msg' => '修改成功',
                    ]);
                }else{
                    $this->ajaxReturn([
                        'msg' => '修改失败',
                        'status' => self::FAIL_STATUS,
                    ]);
                }
            } catch (Exception $e) {
                $this->returnAjaxMsg($e->getMessage(),self::FAIL_STATUS);
            }
        }else{
            $this->assign(compact('staff'));
            $this->display();
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
}