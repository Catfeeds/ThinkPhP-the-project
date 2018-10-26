<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/7/12
 * Time: 下午3:17
 */

namespace Dwin\Model;

use Think\Model;

class FileUploadModel extends Model
{
    /* 文件归属状态机 */
    const TYPE_UNKNOW    = 0; //未知
    const TYPE_SUPPLIER  = 1; //供应商相关
    const TYPE_COUSTOMER = 2; //客户相关
    const TYPE_EMPLOYEE  = 3; //员工相关

    /* 供应商相关文件类型，用于填写路径 和 描述 uploadFile 方法中入参 $path 和 $description */
    const TYPE_CERTIFICATION     = 1; //供应商资质证书
    const TYPE_AWARDS            = 2; //供应商奖金证书
    const TYPE_SYSTEM_ATTEST     = 3; //审核步骤中体系认证上传附件
    const TYPE_SYSTEM_FRAMEWORK  = 4; //审核步骤中体系架构上传附件
    const TYPE_QUALITY_REPOSRT   = 5; //审核步骤中品质、RoHS测试报告上传附件
    const TYPE_SITE_AUDIT        = 6; //审核步骤中现场认证上传附件
    const TYPE_CONTRACT          = 7; //合同附件

    /**
     * 上传文件公共方法
     * @param $file 所需要上传的文件
     * @param $relativePath  上传文件的相对路径
     * @param $fromType 文件的归属
     * @param $description 文件的描述
     * @return array
     */
    public function uploadFile($file, $relativePath, $fromType, $description)
    {

        /* $_FILE = array(1) {
            ["file"]=>array(5) {
                    ["name"]=> "test.pdf"
                    ["type"]=> "application/pdf"
                    ["tmp_name"]=> "/private/var/tmp/phptmNjoS"
                    ["error"]=> ''
                    ["size"]=> 29422
                 }
        }*/

        $path = WORKING_PATH . $relativePath;

        // 判断是否存在当前文件夹，如果没有就创建
        if (!file_exists($path)) {
            mkdir($path, 0777,true);
        }


        // 判断当前登录人是否有权进行文件上传
        $staffFileInfo = M('staff') -> field('max_upload_file_size, allowed_upload_type') -> find(session('staffId'));
        if(!in_array($file['file']['type'],explode(',', $staffFileInfo['allowed_upload_type']))){
            return [402,'您没有权限上传此类文件', []];
        }
        if($file['file']['size'] > $staffFileInfo['max_upload_file_size']){
            return [403,'您所上传的文件大小超过您的权限所限制的', []];
        }

        $upload = new \Think\Upload();// 实例化上传类
        $upload->rootPath  =    $path; // 设置附件上传根目录
//        $upload->maxSize   =    $staffFileInfo['max_upload_file_size'] ;// 设置附件上传大小
//        $upload->mimes     =    explode(',', $staffFileInfo['allowed_upload_type']);// 设置附件上传类型

        // 上传单个文件
        $info   =   $upload->uploadOne($file['file']);

        /*$info =>  array(9) {
                ["name"]=> "test.pdf"
                ["type"]=> "application/pdf"
                ["size"]=> 29422
                ["key"]=> ''
                ["ext"]=> "pdf"
                ["md5"]=> "a03e7ff4accb859cefde5e4efb9ac99a"
                ["sha1"]=> "52089d6a64b514c79d7cd0eab8ee7f939d1af852"
                ["savename"]=> "5b4f063b3ec14.pdf"
                ["savepath"] => "2018-07-18/"
        }*/
        if(!$info) {
            // 上传错误提示错误信息
            return [401, $upload->getError(),[]];
        }else{
            // 上传成功 获取上传文件信息
            $data = [];
            $data['staff_id'] = session('staffId');
            $data['file_name'] = $info['name'];
            $data['path'] = $relativePath . $info['savepath'] . $info['savename'];
            $data['user_id'] = session('staffId');
            $data['from_type'] = $fromType;
            $data['type'] = $info['type'];
            $data['size'] = $info['size'];
            $data['description'] = $description;
            $data['create_time'] = time();
            $data['update_time'] = time();

            // 将所上传信息储存至crm_file_upload表中
            $id = $this->add($data);
            if($id > 0){
                return [200, '文件上传成功', $id];
            }else {
                return [404,'文件上传数据库失败', []];
            }
        }
    }
}