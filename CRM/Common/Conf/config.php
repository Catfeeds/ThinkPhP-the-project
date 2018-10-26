<?php
return array(
    //'配置项'=>'配置值'

    // 模块对比
    'MODULE_LIST' => array('Home', 'Dwin'),

    // 数据库相关配置
    //        'DB_TYPE'           =>  'mysql',     // 数据库类型
    //        'DB_HOST'           =>  '192.168.2.113', // 服务器地址
    //        'DB_NAME'           =>  'dwin',          // 数据库名
    //        'DB_USER'           =>  'sun',      // 用户名
    //        'DB_PWD'            =>  'dwin.com.cn',          // 密码
    //        'DB_PORT'           =>  '3306',        // 端口
    //        'DB_PREFIX'         =>  'crm_',   // 数据库表前缀

    'DB_TYPE'           =>  'mysql',     // 数据库类型
    'DB_HOST'           =>  '192.168.20.102', // 服务器地址
    'DB_NAME'           =>  'dwin',          // 数据库名
    'DB_USER'           =>  'yangchao',      // 用户名
    'DB_PWD'            =>  'yangchao@dwin.com.cn',          // 密码
    'DB_PORT'           =>  '3306',        // 端口
    'DB_PREFIX'         =>  'crm_',   // 数据库表前缀


    // 默认设定
    'DEFAULT_MODULE'        =>  'Dwin',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Public', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'login', // 默认操作名称

    //'TOKEN_ON'      =>    true,
    /* URL设置 */
    'URL_CASE_INSENSITIVE'  =>  true,   // 默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'             =>  2,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式

    // 显示跟踪信息,开发环境开启
    'SHOW_PAGE_TRACE' => true,


    // 开启路由
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(
        'crm'                         => 'Index/index',
        'crmhome'                     => 'Index/home',
        'fk'                          => 'Index/feedBack',
        'affiche'                     => 'Index/affiche',
        'cus/common'                  => 'Customer/showCommonCustomerList',
        'cus/audit'                   => 'Customer/showCustomerAuditList',
        'cus/level'                   => 'Customer/showCusLevel',
        'own/doing/:k'                => 'Research/showOwnPrj',
        'own/addPubPrj'               => 'Research/addPublicPrj',
        'own/addPrj'                  => 'Research/addProject',
        'own/delivery'                => 'Research/showOwnDeliveryPrj',
        'own/done'                    => 'Research/showOCPrj',
        'own/audit'                   => 'Research/showOwnPrjAudit',
        'public/app'                  => 'Research/showPublicPrj',
        'public/all/:k'               => 'Research/showAllPrjNow',
        'prj/audit'                   => 'Research/showPrjAudit',
    ),
);