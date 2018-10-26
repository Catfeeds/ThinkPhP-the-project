<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 设置字符集
header('Content-Type:text/html;charset=utf-8');

// 开启调试模式 开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);

// 当前文件所在的工作目录
define('WORKING_PATH', str_replace('\\', '/', __DIR__));

// 文件上传目录
define('UPLOAD_ROOT_PATH', '/Public/Upload');

//控制目录安全文件的开关
define('BUILD_DIR_SECURE', true);//默认为true，表示开启自动生成目录安全文件

// 定义应用目录
//define('APP_PATH','./Application/');

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// ok!