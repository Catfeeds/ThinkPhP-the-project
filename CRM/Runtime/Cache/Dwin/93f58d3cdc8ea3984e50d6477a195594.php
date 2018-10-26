<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="Bookmark" href="/Public/favicon.ico">
    <link rel="Shortcut Icon" href="/Public/favicon.ico"/>
    <!--[if lt IE 9]>
    <script type="text/javascript" src="/Public/hui/lib/html5shiv.js"></script>
    <script type="text/javascript" src="/Public/hui/lib/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="/Public/hui/static/h-ui/css/H-ui.min.css"/>
    <link rel="stylesheet" type="text/css" href="/Public/hui/static/h-ui.admin/css/H-ui.admin.css"/>
    <link rel="stylesheet" type="text/css" href="/Public/hui/lib/Hui-iconfont/1.0.8/iconfont.css"/>
    <link rel="stylesheet" type="text/css" href="/Public/hui/static/h-ui.admin/skin/default/skin.css" id="skin"/>
    <link rel="stylesheet" type="text/css" href="/Public/hui/static/h-ui.admin/css/style.css"/>
    <link rel="stylesheet" href="/Public/html/css/dwin/index/base.css" />
    <link rel="stylesheet" href="/Public/html/css/dwin/index/index.css" />
    <!--[if IE 6]>
    <script type="text/javascript" src="/Public/hui/lib/DD_belatedPNG_0.0.8a-min.js"></script>
    <script>DD_belatedPNG.fix('*');</script>
    <![endif]-->
    <title>DWIN_ERP</title>
    <style>
        .logo img {
            vertical-align: top;
            height: 100%;
        }
        .crm-name {
            font-style: italic;
            color: #b4d9ff;
            font-family :"DejaVu Sans Mono";
            font-weight: 600;
            font-size: large;
            /*padding-top:10px;*/
            display: flex;
            align-items: center;
        }
        .msg-txt{
            color:white;
            font-weight: 400;
        }
        .msg-txt a:hover {
            color:red!important;
        }
        .navheader {
            display: flex;
            justify-content: space-between;
        }
        .msg-txt {
            margin-left: 100px;
        }
        .msg-txt:hover{
            color: red;
            text-decoration: none;
        }
        .Hui-aside .menu_dropdown li span{cursor:pointer; padding-left:15px; display:block;font-weight: bold; margin:0}
        .Hui-aside .menu_dropdown li span i{ font-weight: normal}
        .Hui-aside .menu_dropdown dd ul{padding:3px 8px}
        .Hui-aside .menu_dropdown dd li{line-height:32px}
        .Hui-aside .menu_dropdown dd li span{line-height:32px;padding-left:26px; border-bottom:none; font-weight:normal}
        .Hui-aside .menu_dropdown li span:hover{text-decoration:none}
        .Hui-aside .menu_dropdown li.current span,.menu_dropdown li.current span:hover{background-color:rgba(255,255,255,0.2)}

        .Hui-aside .menu_dropdown dt{color:#333}/*左侧二级导航菜单*/
        .Hui-aside .menu_dropdown dt:hover{color:#148cf1}
        .Hui-aside .menu_dropdown dt:hover [class^="icon-"]{ color:#7e8795}
        .Hui-aside .menu_dropdown li span{color:#666;border-bottom: 1px solid #e5e5e5}
        .Hui-aside .menu_dropdown li span:hover{color:#148cf1;background-color:#fafafa}
        .Hui-aside .menu_dropdown li.current span,.menu_dropdown li.current span:hover{color:#148cf1}
        .Hui-aside .menu_dropdown dt .Hui-iconfont{ color:#a0a7b1}
        .Hui-aside .menu_dropdown dt .menu_dropdown-arrow{ color:#b6b7b8}

    </style>
</head>
<body>
<header class="navbar-wrapper">
    <div class="navbar navbar-fixed-top" style="background: #2a83cf;">
        <div class="container-fluid navheader">

            <div class="crm-name">            <a class="logo navbar-logo f-l mr-10 hidden-xs" href="javascript:;">
                <img src="/Public/Admin/images/dwinlogo.png" alt=""></a>
                <a class="logo navbar-logo-m f-l mr-10 visible-xs" href="/"></a>
                <a aria-hidden="false" class="nav-toggle Hui-iconfont visible-xs" href="javascript:;">&#xe667;</a>迪文管理系统</div>
            <nav id="" class="nav navbar-nav navbar-userbar hidden-xs">
                <ul class="cl">
                    <li>
                        <div class="msg-num1"></div>
                    </li>
                    <li style="margin-left: 100px;">(<?php echo ($data["postname"]); ?>-<?php echo ($data["deptname"]); ?>)</li>
                    <li class="dropDown dropDown_hover">
                        <a href="#" class="dropDown_A"><?php echo ($data["nickname"]); ?> <i class="Hui-iconfont">&#xe6d5;</i></a>
                        <ul class="dropDown-menu menu radius box-shadow">
                            <li><a href="javascript: ;" class="edit" data-href="/Dwin/Index/editPhone"  data-title="修改电话">修改电话</a></li>
                            <li><a href="javascript: ;" class="edit" data-href="/Dwin/Index/editPwd" data-title="修改密码">修改密码</a></li>
                            <li><a href="#" class="exit">退出</a></li>
                        </ul>
                    </li>
                    <!--<li id="Hui-msg"><a href="#" title="消息"><span class="badge badge-danger">!</span><i-->
                            <!--class="Hui-iconfont" style="font-size:18px">&#xe68a;</i></a></li>-->
                </ul>
            </nav>
        </div>
    </div>
</header>
<aside class="Hui-aside">
    <div class="menu_dropdown bk_2">
        <dl id="cus-module">
            <dt><i class="Hui-iconfont">&#xe616;</i> 销售客户管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Customer/showBusinessList" data-title="业务列表">业务列表</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/cus/common" data-title="公共客户池">公共客户池</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Customer/showOrderList/k/1"
                           id="orderSel" class="up-select myToggle"
                           data-title="订单管理">订单管理 <i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></a>
                        <ul style="display: none">
                            <li>
                                <a href="javascript:;" data-href="/Dwin/Customer/showOrderList/k/1"
                                   data-title="个人订单">个人订单</a>
                            </li>
                            <li>
                                <a href="javascript:;" data-href="/Dwin/Customer/addOrder"
                                   data-title="订单提交">订单提交</a>
                            </li>
                            <li>
                                <a href="javascript:;" data-href="/Dwin/Customer/showOrderList/k/2"
                                   data-title="下属订单">下属订单</a>
                            </li>
                            <li>
                                <a href="javascript:;" data-href="/Dwin/Customer/showPerformanceResult"
                                   data-title="销售业绩">销售业绩</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Customer/getRecordMsg" data-title="联系记录管理">联系记录管理</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Customer/assignCustomer" data-title="客户转移">客户转移</a>
                    </li>

                    <li>
                        <a href="javascript:;" data-href="/Dwin/Customer/showCallbackResult" data-title="客户满意度">客户满意度</a>
                    </li>
                    <li class="CusAudit">
                        <a href="javascript:;" data-href="/Dwin/cus/audit" data-title="客户审核">客户审核</a>
                    </li>
                    <li class="CusAudit">
                        <a href="javascript:;" data-href="/Dwin/Customer/getKpiCus" data-title="KPI客户">KPI客户</a>
                    </li>

                    <li>
                        <a href="javascript:;" data-href="/Dwin/Customer/addCusContactRecordIndex" id="Sel" class="up-select myToggle" data-title="市场部管理">市场部管理 <i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></a>
                        <ul style="display: none">
                            <li>
                                <a href="javascript:;" data-href="/Dwin/Customer/addCusContactRecordIndex" data-title="拜访记录">拜访记录</a>
                            </li>
                            <li>
                                <a href="javascript:;" data-href="/Dwin/Customer/cusContactStatis" data-title="拜访统计">拜访统计</a>
                            </li>
                            <li><a href="javascript:;" data-href="/Dwin/Customer/addSampleOrder" data-title="免费样品">免费样品</a></li>
                            <li><a href="javascript:;" data-href="/Dwin/Customer/showMarketCusStatistics" data-title="有效公共客户统计">有效公共客户统计</a></li>
                        </ul>
                    </li>
                </ul>
            </dd>
        </dl>
        <dl id="prj-module">
            <dt><i class="Hui-iconfont">&#xe616;</i> 研发项目管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Research/addPublicPrj" class="myToggle" data-title="立项管理">立项管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></a>
                        <ul style="display:none">
                            <li>
                                <a href="javascript:;" data-href="/Dwin/Research/addPublicPrj" data-title="新项目公示">新项目公示</a>
                            </li>
                            <li>
                                <a href="javascript:;" data-href="/Dwin/Research/addProject" data-title="新项目立项">新项目立项</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:;" class="up-select" data-href="/Dwin/own/doing/1" data-title="项目管理">项目管理</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Research/showAllPrjNow" data-title="项目公示">项目公示</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/public/app" data-title="项目申请">项目申请</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/prj/audit" data-title="项目审核">项目审核</a>
                    </li>
                </ul>
            </dd>
        </dl>
        <dl id="pur-module">
            <dt><i class="Hui-iconfont">&#xe616;</i> 采购管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>
                    <li>
                        <span class="myToggle" data-title="供应商管理" href="javascript:;">供应商管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></span>
                        <ul style="display: none;">
                            <li><a data-href="/Dwin/Purchase/supplierIndex" href="javascript:;" data-title="供应商名录">供应商名录</a></li>
                            <li><a data-href="/Dwin/Purchase/supplierChargeList" href="javascript:;" data-title="供应商负责人变更">负责人变更</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Purchase/contractIndex" data-title="合同管理">合同管理</a>

                    </li>
                    <li>
                        <span class="myToggle" data-title="采购合同管理" href="javascript:;">订单管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></span>
                        <ul style="display: none;">
                            <li><a data-href="/Dwin/Purchase/orderIndex" data-title="订单列表" href="javascript:;">订单列表</a></li>
                            <li>
                                <a data-href="/Dwin/Purchase/orderScheduleList" data-title="物料采购进度" href="javascript:;">物料采购进度</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </dd>
        </dl>
        <dl id="fin-module">
            <dt><i class="Hui-iconfont">&#xe616;</i> 财务管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Finance/showCustomer" data-title="客户查询">客户查询</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Finance/showOrderList" data-title="订单列表">订单列表</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Finance/showOrderAudit" data-title="审核管理">审核管理</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Finance/checkedOrderManagement" data-title="结算管理">结算管理</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Finance/showPerformanceResult" data-title="业绩统计">业绩统计</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Finance/showProduct" data-title="产品数据管理">产品数据管理</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Finance/productAudit" data-title="产品数据审核">产品数据审核</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Finance/myProductAudit" data-title="我的产品数据审核">我的产品数据审核</a>
                    </li>
                </ul>
            </dd>
        </dl>
        <dl id="stock-module">
            <dt><i class="Hui-iconfont">&#xe616;</i> 库存管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Stock/stockIndex" data-title="库存管理系统">即时库存</a>
                    <li>
                    <li>


                    <span class="myToggle" data-title="入库管理" href="javascript:;">出库管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></span>
                    <ul style="display: none;">
                        <li><a data-href="/Dwin/Stock/createOtherStockOutApply" data-title="出库申请" href="javascript:;">出库申请</a></li>
                        <li>
                            <span class="myToggle" data-title="出库登记" href="javascript:;">出库制单<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></span>
                            <ul style="display: none;">
                                <li>
                                    <a data-href="/Dwin/Stock/showPendingOrderList" data-title="销售出库" href="javascript:;">销售出库</a>
                                </li>
                                <li>
                                    <a data-href="/Dwin/Stock/productionOrderIndex" data-title="生产领料出库" href="javascript:;">生产领料出库</a></li>
                                <li><a data-href="/Dwin/Stock/otherStockOutApplyList" data-title="其他出库" href="javascript:;">其他出库</a></li>
                            </ul>
                        </li>
                        <li><a data-href="/Dwin/Stock/stockRecordQualifiedList" data-title="出库记录" href="javascript:;">出库记录</a></li>
                        <!-- <li><a data-href="/Dwin/Stock/stockOutQualifiedList" data-title="已出库单据" href="javascript:;">已出库单据</a></li> -->
                        <li><a data-href="/Dwin/Stock/stockOutingList" data-title="出库单据" href="javascript:;">出库单据</a></li>
                    </ul>
                    </li>
                    <li>
                        <span class="myToggle" data-title="入库管理" href="javascript:;">入库管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></span>
                        <ul style="display: none;">
                            <li>
                                <span class="myToggle" data-title="入库登记" href="javascript:;">入库制单<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></span>
                                <ul style="display: none;">
                                    <li>
                                        <a data-href="/Dwin/Stock/productionTaskIndex" data-title="生产入库" href="javascript:;">生产入库</a>
                                    </li>
                                    <li>
                                        <a data-href="/Dwin/Stock/purchaseOrderIndex" data-title="外购入库" href="javascript:;">外购入库</a></li>
                                    <li><a data-href="/Dwin/Stock/otherTypeIndex" data-title="其他入库" href="javascript:;">其他入库</a></li>
                                    <li><a data-href="/Dwin/Stock/otherStockInApplyIndex" data-title="售后入库" href="javascript:;">售后入库</a></li>
                                </ul>
                            </li>
                            <li>
                                <span class="myToggle" data-title="入库登记" href="javascript:;">入库审核<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></span>
                                <ul style="display: none;">
                                    <li><a data-href="/Dwin/Stock/purchaseAuditIndex" data-title="入库登记" href="javascript:;">外购质控审核</a></li>
                                    <li><a data-href="/Dwin/Stock/showStockInAuditIndex" data-title="入库审核" href="javascript:;">入库库房审核</a></li>
                                </ul>
                            </li>
                            <li><a data-href="/Dwin/Stock/showStockInRecord" data-title="入库审核" href="javascript:;">入库记录</a></li>
                        </ul>
                    </li>
                    <li>
                        <span class="myToggle" data-title="调拨管理" href="javascript:;">调拨管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></span>
                        <ul style="display: none;">
                            <li>
                                <span class="myToggle" data-title="调拨制单" href="javascript:;">调拨制单<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></span>
                                <ul style="display: none;">
                                    <li><a data-href="/Dwin/Stock/productionTransferIndex" data-title="生产调拨" href="javascript:;">生产调拨</a></li>
                                    <li><a data-href="/Dwin/Stock/addStockTransfer" data-title="调拨制单" href="javascript:;">直接制单</a></li>
                                </ul>
                            </li>
                            <li>
                                <span class="myToggle" data-title="调拨审核" href="javascript:;">调拨审核<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></span>
                                <ul style="display: none;">
                                    <li><a data-href="/Dwin/stock/showAuditTransferList" data-title="制单审核" href="javascript:;">制单审核</a></li>
                                    <li><a data-href="/Dwin/stock/showAuditTransferListSecond" data-title="出库审核" href="javascript:;">物流审核</a></li>
                                </ul>
                            </li>
                            <li><a data-href="/Dwin/stock/showTransferList" data-title="入库审核" href="javascript:;">调拨记录</a></li>
                        </ul>
                    </li>
                </ul>
            </dd>
        </dl>
        <dl id="mrp-module">
            <dt><i class="Hui-iconfont">&#xe616;</i> MRP管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Stock/index" class="myToggle" data-title="成品库库存查询">成品库库存查询</a>
                        <!--<ul style="display:none">-->
                            <!--<li><a data-href="/Dwin/Stock/index" data-title="库存管理" href="javascript:;">库存查询</a></li>-->
                            <!--<li><a data-href="/Dwin/Stock/alarm" data-title="成品库存报警" href="javascript:;">库存报警</a></li>-->
                            <!--<li>-->
                                <!--<a data-href="/Dwin/Stock/showPendingOrderList" data-title="出库管理" class="myToggle">出库管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></a>-->
                                <!--<ul style="display:none">-->
                                    <!--<li><a data-href="/Dwin/Stock/showPendingOrderList" data-title="销货单出库登记" href="javascript:;">销货单出库登记</a></li>-->
                                    <!--<li><a data-href="/Dwin/Stock/showStockOutRecord" data-title="出库记录" href="javascript:;">出库记录</a>-->
                                    <!--<li><a data-href="/Dwin/Stock/showStockOutAuditList" data-title="出库审核" href="javascript:;">出库审核</a>-->
                                    <!--</li>-->
                                <!--</ul>-->
                            <!--</li>-->
                            <!--<li>-->
                                <!--<a data-href="/Dwin/Stock/showRecord" data-title="入库管理" href="javascript:;" class="myToggle">入库管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></a>-->
                                <!--<ul style="display: none;">-->
                                    <!--<li><a data-href="/Dwin/Stock/showRecord" data-title="入库登记" href="javascript:;">入库登记</a></li>-->
                                    <!--<li><a data-href="/Dwin/Stock/auditList" data-title="入库审核" href="javascript:;">入库审核</a></li>-->
                                <!--</ul>-->
                            <!--</li>-->
                        <!--</ul>-->
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Production/productionPlan" class="myToggle" data-title="生产任务管理">生产任务管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></a>
                        <ul style="display:none">
                            <li><a data-href="/Dwin/Production/productionPlan" data-title="生产任务管理" href="javascript:;">生产任务管理</a></li>
                            <li><a data-href="/Dwin/Production/productionIndex" data-title="生产单管理" href="javascript:;">单据单下推</a></li>
                            <li><a data-href="/Dwin/Production/productionOrderIndex" data-title="生产计划" href="javascript:;">生产计划</a></li>
                            <li><a data-href="/Dwin/Production/productionTaskIndex" data-title="生产任务" href="javascript:;">生产任务</a></li>

                            <li><a data-href="/Dwin/Production/showCompleteProductionPlan" data-title="完工生产任务单" href="javascript:;">完工生产任务单</a></li>

                            <li><a data-href="/Dwin/Production/stockOutStatistics" data-title="生产报表统计" href="javascript:;">生产报表统计</a></li>
                        </ul>
                    </li>
                    <li><a data-href="/Dwin/Stock/showWarehouse" data-title="仓库管理" href="javascript:;">仓库管理</a>
                    <li><a data-href="/Dwin/Production/productionLineList" data-title="生产班组管理" href="javascript:;">班组管理</a>
                </ul>
            </dd>
        </dl>
        <dl id="online-module">
            <dt><i class="Hui-iconfont">&#xe616;</i> 客户服务中心<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/OnlineService/showCustomer"
                           data-title="客服记录添加">客服记录添加</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/OnlineService/showServiceList" data-title="客服记录">近期客服记录</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/OnlineService/showCallbackInfo" data-title="满意度调查">满意度调查</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/OnlineService/getCallbackCustomer"
                           data-title="客户回访">客户回访</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/OnlineService/countServicePerformance"
                           data-title="客服数据统计">客服数据统计</a>
                    </li>
                </ul>
            </dd>
        </dl>
        <dl id="sale-module">
            <dt><i class="Hui-iconfont">&#xe616;</i> 质控管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>
                    <li>
                        <span class="myToggle" data-title="物料管理" href="javascript:;">物料管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></span>

                        <ul style="display: none">

                            <li>
                                <a href="javascript:;" data-href="/Dwin/Product/getProduct" data-title="物料列表">物料列表</a>
                            </li>
                            <li>
                                <a href="javascript:;" data-href="/Dwin/Product/getEditRequest" data-title="物料审核">审核管理</a>
                            </li>
                            <!--<li>-->
                                <!--<a href="javascript:;" data-href="/Dwin/Product/getMyEditRequest" data-title="物料编辑记录">物料编辑记录</a>-->
                            <!--</li>-->
                            <li><a data-href="/Dwin/Bom/materialAllMsgList" data-title="替换物料管理" href="javascript:;">替换物料管理</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:;" class="myToggle" data-href="/Dwin/SaleService/showSaleRepairing" data-title="售后管理">售后管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></a>
                        <ul style="display: none">
                            <li>
                                <a href="javascript:;" data-href="/Dwin/SaleService/showCustomer" data-title="客户查询">客户查询</a>
                            </li>
                            <li>
                                <a href="javascript:;" data-href="/Dwin/SaleService/showAllUnrecord" data-title="待审单据">待审单据</a>
                            </li>
                            <li>
                                <a data-href="/Dwin/SaleService/callbackRes" data-title="二次返修调查" href="javascript:;">二次返修调查</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <span class="myToggle" data-title="BOM管理" href="javascript:;">BOM管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></span>
                        <ul style="display: none;">
                            <li><a data-href="/Dwin/Bom/createBom" data-title="新增BOM" href="javascript:;">新增BOM</a></li>
                            <li><a data-href="/Dwin/Bom/bomIndex" data-title="BOM列表" href="javascript:;">BOM列表</a></li>

                        </ul>
                    </li>

                </ul>
            </dd>
        </dl>
        <dl id="admin-module">
            <dt><i class="Hui-iconfont">&#xe616;</i> 行政人事管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>

                    <li>
                        <a href="javascript:;" data-href="/Dwin/Admin/index" data-title="人员管理">人员管理</a>
                    </li>

                    <li>
                        <a href="javascript:;" data-href="/Dwin/Admin/contractIndex" data-title="合同管理">合同管理</a>
                    </li>

                    <li>
                        <a href="javascript:;" data-href="/Dwin/Admin/departureIndex" data-title="离职管理">离职管理</a>
                    </li>

                    <li>
                        <a href="javascript:;" data-href="/Dwin/Admin/changeIndex" data-title="人员异动">人员异动</a>
                    </li>

                    <li>
                        <a href="javascript:;" data-href="/Dwin/System/showStaff" data-title="系统账户管理">系统账户管理</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/System/calendar" data-title="工作日编辑">工作日编辑</a>
                    </li>
                </ul>
            </dd>
        </dl>
        <dl id="file-module">
            <dt><i class="Hui-iconfont">&#xe616;</i> 文件管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>

                    <li>
                        <a href="javascript:;" data-href="/Dwin/File/fileIndexTech" data-title="工艺文件">工艺文件</a>
                    </li>

                    <li>
                        <a href="javascript:;" data-href="/Dwin/File/fileIndexPdf" data-title="公司制度">公司制度</a>
                    </li>

                    <li>
                        <a href="javascript:;" data-href="/Dwin/File/fileIndexDownload" data-title="文件下载">文件下载</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/File/fileUploadAuthManagerIndex" data-title="文件上传权限管理">文件上传权限管理</a>
                    </li>
                </ul>
            </dd>
        </dl>
        <dl>
            <dt><i class="Hui-iconfont">&#xe616;</i> 日常办公<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Office/productionDelayComplain" data-title="生产延期投诉">生产延期投诉</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Office/productionQualityComplain" data-title="生产质量投诉">生产质量投诉</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Office/serviceQualityComplain" data-title="服务质量投诉">服务质量投诉</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Office/award" data-title="奖励公示">奖励公示</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Office/punish" data-title="处罚公示">处罚公示</a>
                    </li>
                </ul>
            </dd>
        </dl>
        <dl id="public-module">
            <dt><i class="Hui-iconfont">&#xe616;</i> 流程审批<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/Process/index" data-title="系统优化审批工作流">系统优化审批流程</a>
                    </li>
                </ul>
            </dd>
        </dl>
        <dl id="sys-module">
            <dt><i class="Hui-iconfont">&#xe616;</i> 系统管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
            <dd>
                <ul>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/System/showDept" data-title="部门管理">部门管理</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/System/showPosition" data-title="职位管理">职位管理</a>
                    </li>

                    <li>
                        <a href="javascript:;" data-href="/Dwin/System/editRole" data-title="审核权限管理">审核权限管理</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/System/editRecordTime"
                           data-title="记录可查看时间">记录可查看时间</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/System/showCusIndus" data-title="行业编辑">行业编辑</a>
                    </li>

                    <li>
                        <a href="javascript:;" data-href="/Dwin/System/stockIoCate" data-title="出入库分类管理">出入库分类管理</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/System/stockCate" data-title="备货方式管理">备货方式管理</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/System/productionCompany"
                           data-title="生产公司管理">生产公司管理</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/System/productionLine" data-title="生产线管理">生产线管理</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-href="/Dwin/System/accountManager" data-title="账号管理">账号管理</a>
                    </li>
                </ul>
            </dd>
        </dl>
    </div>
</aside>
<div class="dislpayArrow hidden-xs"><a class="pngfix" href="javascript:;;" onClick="displaynavbar(this)"></a>
</div>
<section class="Hui-article-box">
    <div id="Hui-tabNav" class="Hui-tabNav hidden-xs">
        <div class="Hui-tabNav-wp">
            <ul id="min_title_list" class="acrossTab cl">
                <li class="active">
                    <span title="我的桌面" data-href="welcome.html">我的桌面</span>
                    <em></em></li>
            </ul>
        </div>
        <div class="Hui-tabNav-more btn-group"><a id="js-tabNav-prev" class="btn radius btn-default size-S"
                                                  href="javascript:;"><i class="Hui-iconfont">&#xe6d4;</i></a><a
                id="js-tabNav-next" class="btn radius btn-default size-S" href="javascript:;"><i class="Hui-iconfont">&#xe6d7;</i></a>
        </div>
    </div>
    <div id="iframe_box" class="Hui-article">
        <div class="show_iframe">
            <div style="display:none" class="loading"></div>
            <iframe scrolling="yes" frameborder="0" src="crmhome"></iframe>
        </div>
    </div>
</section>

<div class="contextMenu" id="Huiadminmenu">
    <ul>
        <li id="closethis">关闭当前</li>
        <li id="closeall">关闭全部</li>
    </ul>
</div>
<!--_footer 作为公共模版分离出去-->
<script type="text/javascript" src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="/Public/hui/lib/layer/2.4/layer.js"></script>
<script type="text/javascript" src="/Public/hui/static/h-ui/js/H-ui.min.js"></script>
<script type="text/javascript" src="/Public/hui/static/h-ui.admin/js/H-ui.admin.js"></script> <!--/_footer 作为公共模版分离出去-->

<!--请在下方写此页面业务相关的脚本-->
<script type="text/javascript" src="/Public/hui/lib/jquery.contextmenu/jquery.contextmenu.r2.js"></script>
<script type="text/javascript">
    var moduleSys    =  $("#sys-module");
    var moduleAdmin  =  $("#admin-module");
    var moduleSale   =  $("#sale-module");
    var moduleOnline =  $("#online-module");
    var moduleMRP    =  $("#mrp-module");
    var moduleFin    =  $("#fin-module");
    var modulePrj    =  $("#prj-module");
    var moduleCus    =  $("#cus-module");
    var modulePur    =  $("#pur-module");
    $('.myToggle').on('click', function () {
        $(this).next('ul').slideToggle()
        $(this).next('ul').parent().siblings().children('ul').hide(500)
    });
    $('.Hui-aside dt').on('click',function () {
        $('.Hui-aside ul li').children('ul').hide();
    })
    function getMsgCount() {
        //ajax
        //当前控制器是Index
        $.get("<?php echo U('Index/getMsgCount');?>", function (data) {
            //修改未读数量的显示
            if (data != "") {
                var innerHtml = '';
                if (data['cusCount']) {
                    innerHtml += '<a class="msg-txt  cusCount" date_src="/Dwin/Customer/showCustomerAudit" href="javascript:;">待审核客户：' + data['cusCount'] + '</a>';
                }
                if (data['prjCount']) {
                    innerHtml += '<a class="msg-txt  prjCount" date_src="/Dwin/Research/showPrjAudit" href="javascript:;">待审核项目：' + data['prjCount'] + ' </a>';
                }
                if (data['orderCount']) {
                    innerHtml += '<a class="msg-txt  orderCount" date_src="/Dwin/Finance/showOrderAudit" href="javascript:;">待审订单：' + data['orderCount'] + ' </a>';
                }
                if (data['prjProgressCount']) {
                    innerHtml += '<a class="msg-txt  prjAudit" date_src="/Dwin/Research/showProgressAuditList" href="javascript:;">待审项目进度：' + data['prjProgressCount'] + '</a>';
                }
                if (data['saleCount']) {
                    innerHtml += '<a class="msg-txt  saleCount" href="javascript:;" date_src="/Dwin/Customer/showUnCheckServiceList">待审售后记录：' + data['saleCount'] + '</a>';
                }
                if (data['onlineCount']) {
                    innerHtml += '<a class="msg-txt  onlineCount" href="javascript:;" date_src="/Dwin/Customer/showUnCheckOnlineList">待审客服记录：' + data['onlineCount'] + '</a>';
                }
                if (data['prjUNum']) {
                    innerHtml += '<a class="msg-txt  selPrjCount" href="javascript:;" date_src="/Dwin/Research/showPublicPrj">研发可申请项目数：' + data['prjUNum'] + '</a>';
                }
                if (data['editCusCount']) {
                    innerHtml += '<a class="msg-txt  editCusCount" href="javascript:;" date_src="/Dwin/Customer/showCusEditApply">客户名修改申请：' + data['editCusCount'] + '</a>';
                }
                if (data['repairCount']) {
                    innerHtml += '<a class="msg-txt  repairCount" href="javascript:;" date_src="/Dwin/SaleService/showOwnUnrecord">客户售后单：' + data['repairCount'] + '</a>';
                }
                if (data['removeCount']) {
                    innerHtml += '<a class="msg-txt  repairCount" href="javascript:;" date_src="/Dwin/Customer/showRemoveList">客户放弃申请：' + data['removeCount'] + '</a>';
                }
            }
            $('.msg-num1').html("");
            $(".msg-num1").append(innerHtml);
        });

        $(".msg-num1").off('click').on('click', 'a', function () {
            layer.open({
                type: 2,
                area: ['90%', '90%'],
                shadeClose: true,
                title: "待办事项",
                content: $(this).attr('date_src')
            });
        });
    }
    $(function () {
        //声明反复性定时器
        //  setInterval('getMsgCount()',5000);
    });

    // 退出登录
    $('.exit').click(function(){
        layer.confirm('确定退出系统?',
            {
                icon : 6
            },
            function(){
                window.location.href = "/Dwin/Public/logout";
            });
    });
    $('.edit').on('click', function () {
        layer.open({
            type: 2,
            area: ['50%', '70%'],
            title: $(this).attr('data-title'),
            content: $(this).attr('data-href')
        });
    });

    // 判断权限
    $(document).ready(function() {
        $.ajax({
            type: 'POST',
            url: '/Dwin/Index/checkPostInfo',
            data: {flag: 1},
            success: function (ajaxData) {
                if (ajaxData['sys'] === 1) { moduleSys.html('');}
                if (ajaxData['saleservice'] === 1) { moduleSale.html('');}
                if (ajaxData['online'] === 1) { moduleOnline.html('');}
                if (ajaxData['finance'] === 1) { moduleFin.html('');}
                if (ajaxData['project'] === 1) { modulePrj.html('');}
                if (ajaxData['customer'] === 1) { moduleCus.html('');}
                if (ajaxData['production'] === 1) { moduleMRP.html('');}
                if (ajaxData['admin'] === 1) { moduleAdmin.html('');}
                if (ajaxData['purchase'] === 1) { modulePur.html('');}
            }
        });
    });
</script>
</body>
</html>