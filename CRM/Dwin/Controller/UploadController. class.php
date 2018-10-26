<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/5/7
 * Time: 上午9:24
 */

class UploadController extends \Dwin\Controller\CommonController
{

    const SUCCESS_STATUS   = 200;
    const FAIL_STATUS      = 400;
    const FORBIDDEN_STATUS = 403;
    public function index()
    {
        $this->posts = I('post.');

        if (IS_POST) {
            // 文件上传类配置项
            // 检测根目录是否存在，不存在创建
            $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/employeeData/" . $uploadType . "/";
            if (!file_exists($rootPath)) {
                mkdir($rootPath);
            }
            $ext = $uploadType == 'uploadPhoto'
                ? ['gif','jpg', 'jpeg', 'bmp']
                : ['gif', 'jpg', 'jpeg', 'bmp', 'doc', 'docx','pdf'];
            $cfg = [
                'rootPath' => $rootPath, // 保存根路径
                'mimes'    => array('image/jpeg', 'image/gif', 'text/plain' ,'audio/mpeg', 'application/x-rar-compressed', 'application/zip','image/bmp', 'application/msword', 'application/pdf', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.ms-office'),
                'replace'  => true,
                'exts'     => $ext
            ];
            # 实例化上传类
            $uploadModel = new Upload($cfg);
            # 上传
            $uploadRst = $uploadModel->upload();
            $data = [];
            if (!$uploadRst) {
                // 返回错误信息
                $data['error_info'] = $uploadModel->getError();
                return $msg = ['status' => self::FAIL_STATUS,'data' => $data];
            } else {
                // 返回成功信息

                foreach ($uploadRst as $item) {
                    $data['filePath'] = UPLOAD_ROOT_PATH . "/employeeData/" . $uploadType . "/" . trim($item['savepath'] . $item['savename'], '.');
                    $data['fileName'] = $fileName = $fileData['file']['name'];
                    if ($uploadType == 'uploadPapers') {
                        $data['index'] = (int)$uploadIndex;
                    }
                    return $msg = ['status' => self::SUCCESS_STATUS, 'data' => $data];
                }
            }
        } else {
            die('非法操作');
        }
    }

}