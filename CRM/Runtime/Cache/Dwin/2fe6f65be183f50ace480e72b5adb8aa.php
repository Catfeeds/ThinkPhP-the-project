<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
       <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdn.bootcss.com/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">

    <style>
        body{
            color:black;
        }
        .selected{
            background: #d0d27e!important;
        }
        #staff th,td{
            white-space: nowrap!important;
        }
        .el-table thead{
            color:black!important;
        }

        .el-table td, .el-table th{
            padding-top: 2px!important;
            padding-bottom: 2px!important;
        }
        .el-pagination__jump{
            color:black!important;
        }
        .table2000{
            width: 2000px;
        }
        #staff_wrapper{
            overflow: hidden;
        }
        .dataTables_scrollBody thead{
            visibility: hidden;
        }
        div.dataTables_scrollBody table{
            margin-top: -25px!important;
            margin-left: 1px;
        }
        .tab-pane{
            overflow: auto;
        }
        .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
            padding:3px!important;
        }

        /* 一级弹层 */
        
        #main { 
            text-align:center; 
        } 
        #fullbg { 
            background-color:gray; 
            left:0; 
            opacity:0.5; 
            position:absolute; 
            top:0; 
            z-index:3; 
            filter:alpha(opacity=50); 
            -moz-opacity:0.5; 
            -khtml-opacity:0.5; 
        } 
        #dialog { 
            background-color:#fff; 
            border:5px solid rgba(0,0,0, 0.4); 
            height:170px; 
            left:50%; 
            margin:-200px 0 0 -200px; 
            padding:1px; 
            position:fixed !important; /* 浮动对话框 */ 
            position:absolute; 
            top:60%; 
            width:357px; 
            z-index:5; 
            border-radius:5px; 
            display:none; 
        } 
        #dialog p { 
            margin:0 0 12px; 
            height:24px; 
            line-height:24px; 
            background:#CCCCCC; 
        } 
        #dialog p.close { 
            text-align:right; 
            padding-right:10px; 
        } 
        #dialog p.close a { 
            color:#fff; 
            text-decoration:none; 
        } 
        #dialog div{
            height: 100%;
            width: 100%;
            text-align: center;
            line-height: 160px;
        }
        .dia_but1{
            height: 50px;
            width: 100px;
            font-size: 15px;
            text-align: center;
            line-height: 50px;
            font-weight: bold;
            margin-left: 44px;
        }
        /* 二级弹层 */
        
        #main_secondTime { 
            text-align:center; 
        } 
        #fullbg_secondTime { 
            background-color:gray; 
            left:0; 
            opacity:0.5; 
            position:absolute; 
            top:0; 
            z-index:3; 
            filter:alpha(opacity=50); 
            -moz-opacity:0.5; 
            -khtml-opacity:0.5; 
        } 
        #dialog_secondTime { 
            background-color:#fff; 
            border:5px solid rgba(0,0,0, 0.4); 
            height:500px; 
            left:37%; 
            margin:-200px 0 0 -200px; 
            padding:1px; 
            position:fixed !important; /* 浮动对话框 */ 
            position:absolute; 
            top:40%; 
            width:800px; 
            z-index:5; 
            border-radius:5px; 
            display:none; 
        } 
        #dialog_secondTime p { 
            margin:0 0 12px; 
            height:24px; 
            line-height:24px; 
            background:#CCCCCC; 
        } 
        #dialog_secondTime p.close { 
            text-align:right; 
            padding-right:10px; 
        } 
        #dialog_secondTime p.close a { 
            color:#fff; 
            text-decoration:none; 
        } 
        #dialog_secondTime div{
            height: 100%;
            width: 100%;
            text-align: center;
            line-height: 160px;
        }
        #changeData tr{
            height: 60px;
        }
        .tb_with{
            width: 150px;
        }
        #changeData button{
            width: 70px;
            height: 35px;
            font-weight: bold;
            margin-top: 10px;
        }
        .head_t{
            background-color: #CCCCCC;
            font-weight: bold;
            font-size: 15px;
            height: 50px;
        }
        .head_but{
            width: 100px;
        }
        /* 二级 再弹框 */
        #main_thereTime { 
            text-align:center; 
        } 
        #fullbg_thereTime { 
            background-color:gray; 
            left:0; 
            opacity:0.5; 
            position:absolute; 
            top:0; 
            z-index:6; 
            filter:alpha(opacity=50); 
            -moz-opacity:0.5; 
            -khtml-opacity:0.5; 
        } 
        #dialog_thereTime { 
            background-color:#fff; 
            border:5px solid rgba(0,0,0, 0.4); 
            height:300px; 
            left:44%; 
            margin:-200px 0 0 -200px; 
            padding:1px; 
            position:fixed !important; /* 浮动对话框 */ 
            position:absolute; 
            top:55%; 
            width:600px; 
            z-index:7; 
            border-radius:5px; 
            display:none; 
        } 
        #dialog_thereTime p { 
            margin:0 0 12px; 
            height:24px; 
            line-height:24px; 
            background:#CCCCCC; 
        } 
        #dialog_thereTime p.close { 
            text-align:right; 
            padding-right:10px; 
        } 
        #dialog_thereTime p.close a { 
            color:#fff; 
            text-decoration:none; 
        } 
        ul li{           
            padding:0;
            margin:0;
            list-style:none;
        }
        .ul_name{
            float: left;
            width: 30%;
            text-align: center
        }
        .ul_name li{
            width: 100%;
            height: 45px;
            line-height: 45px;
            text-align: center;
            border: 1px solid #000;
        }
        .ul_val{
            float: left;
            width: 385px;
            padding: 0;
            text-align: center
        }
        .ul_val li{
            width: 100%;
            height: 45px;
            line-height: 45px;
            border: 1px solid #000;
        }
        .close div{
            float: left;
            width: 500px;
            height: 50px;
        }
        .love_button{
            background-color: #ccc;
            font-weight: bold;
            cursor:pointer;
        }
        .love_button:hover{
            background-color: #909399;
        }
        #main_revamp { 
            text-align:center; 
        } 
        #fullbg_revamp { 
            background-color:gray; 
            left:0; 
            opacity:0.5; 
            position:absolute; 
            top:0; 
            z-index:3; 
            filter:alpha(opacity=50); 
            -moz-opacity:0.5; 
            -khtml-opacity:0.5; 
        } 
        #dialog_revamp { 
            background-color:#fff; 
            border:5px solid rgba(0,0,0, 0.4); 
            height:600px; 
            left:35%; 
            margin:-200px 0 0 -200px; 
            padding:1px; 
            position:fixed !important; /* 浮动对话框 */ 
            position:absolute; 
            top:35%; 
            width:900px; 
            z-index:5; 
            border-radius:5px; 
            display:none; 
        } 
        #dialog_revamp p { 
            margin:0 0 12px; 
            height:24px; 
            line-height:24px; 
            background:#CCCCCC; 
        } 
        #dialog_revamp p.close { 
            text-align:right; 
            padding-right:10px; 
        } 
        #dialog_revamp p.close a { 
            color:rgb(102, 96, 96); 
            text-decoration:none; 
        } 
        /* 修改 */
        /* 修改 选择框 */
        #main_select { 
            text-align:center; 
        } 
        #fullbg_select { 
            background-color:gray; 
            left:0; 
            opacity:0.5; 
            position:absolute; 
            top:0; 
            z-index:3; 
            filter:alpha(opacity=50); 
            -moz-opacity:0.5; 
            -khtml-opacity:0.5; 
        } 
        #dialog_select { 
            background-color:#fff; 
            border:5px solid rgba(0,0,0, 0.4); 
            height:500px; 
            left:50%; 
            margin:-200px 0 0 -200px; 
            padding:1px; 
            position:fixed !important; /* 浮动对话框 */ 
            position:absolute; 
            top:40%; 
            width:500px; 
            z-index:5; 
            border-radius:5px; 
            display:none; 
        } 
        #dialog_select p { 
            margin:0 0 12px; 
            height:24px; 
            line-height:24px; 
            background:#CCCCCC; 
        } 
        #dialog_select p.close { 
            text-align:right; 
            padding-right:10px; 
            z-index: 6;
        } 
        #dialog_select p.titles { 
            font-size: 17px;
            font-weight: bold;
            text-align: left;
            padding-left: 15px;
            height: 45px;
            line-height: 45px;
            background-color: #fff;
            border-bottom: 1px solid #ccc;
        } 
        #dialog_select p.close a { 
            color:rgb(102, 96, 96); 
            text-decoration:none; 
        } 
        .select_is{
            padding: 0;
            width: 600px;
            height: 500px;
        }
        .select_is li{
            height:43px;
            width: 81%;
            cursor:pointer;
            line-height: 43px;
            text-align: left;
            font-size: 17px;
            padding-left: 20px;
            /* font-weight: bold; */
        }
        .select_is li:hover{
            background-color: #ccc;
            font-weight: bold;
            text-align: center;
        }
        /* 修改  编辑框 */
        #main_revamp { 
            text-align:center; 
        } 
        #fullbg_revamp { 
            background-color:gray; 
            left:0; 
            opacity:0.5;
            width: 100%;
            position:absolute; 
            top:0; 
            z-index:3; 
            filter:alpha(opacity=50); 
            -moz-opacity:0.5; 
            -khtml-opacity:0.5; 
        } 
        #dialog_revamp { 
            background-color:#fff; 
            border:5px solid rgba(0,0,0, 0.4); 
            height:600px; 
            left:17%; 
            margin:-200px 0 0 -200px; 
            padding:1px; 
            position:fixed !important; /* 浮动对话框 */ 
            position:absolute; 
            top:35%; 
            width:1292px; 
            z-index:5; 
            border-radius:5px; 
            display:none; 
        } 
        #dialog_revamp p { 
            margin:0 0 12px; 
            height:24px; 
            line-height:24px; 
            background:#CCCCCC; 
        } 
        #dialog_revamp p.close { 
            text-align:right; 
            padding-right:10px; 
        } 
        #dialog_revamp p.close a { 
            color:rgb(102, 96, 96); 
            text-decoration:none; 
        } 
        .input_sty{
            overflow:auto;
            background-attachment:fixed;
            background-repeat:no-repeat;
            border-style:solid; 
            border-color:#FFFFFF;
        }
        .cell{
            text-align: left
        }
        .has-gutter tr{
            height: 35px;
            font-size: 15px;
        }
        .has-gutter tr th{
            background-color: #bbb;
        }
        .tab_title{
            height: 40px;
            text-align: left;
            padding-left: 20px;
            font-size: 15px;
            line-height: 40px;
        }
        .present_button{
            padding: 6px 25px;
            margin: 15px 25px;
            font-weight: bold;
        }
        .body_text textarea{
            border: none;
            resize: none;
            height: 100%;
            text-align: center;
            border:0;
            outline:none;
        }

        /* 确定按钮 */
        
        #main_sureToSubmit { 
            text-align:center; 
        } 
        #fullbg_sureToSubmit { 
            background-color:gray; 
            left:0; 
            opacity:0.5; 
            position:absolute; 
            top:0; 
            z-index:5; 
            filter:alpha(opacity=50); 
            -moz-opacity:0.5; 
            -khtml-opacity:0.5; 
        } 
        #dialog_sureToSubmit { 
            background-color:#fff; 
            border: none;
            border:5px solid rgba(0,0,0, 0.4); 
            height:170px; 
            left:50%; 
            margin:-200px 0 0 -200px; 
            padding:1px; 
            position:fixed !important; /* 浮动对话框 */ 
            position:absolute; 
            top:60%; 
            width:357px; 
            z-index:7; 
            border-radius:5px; 
            display:none; 
        } 
        #dialog_sureToSubmit p { 
            margin:0 0 12px; 
            height:24px; 
            line-height:24px; 
            background:#CCCCCC; 
        } 
        #dialog_sureToSubmit p.close { 
            text-align:right; 
            padding-right:10px; 
        } 
        #dialog_sureToSubmit p.close a { 
            color:#fff; 
            text-decoration:none; 
        }
        .dia_but2{
            height: 50px;
            width: 100px;
            border-radius:10%;
            border: none;
            margin: 55px 0px;
            font-size: 15px;
            background-color:#E9D98C; 
            text-align: center;
            line-height: 50px;
            font-weight: bold;
            margin-left: 44px;
        }
        /* 修改 表头 */
        .th_style{
            height: 40px;
            background-color: #ccc;
        }
        .el-button{
            margin: 4px 9px; 
        }
        .selsctDialog .el-button:hover{
            background-color: darkblue;
            border:1px solid #fff;  
        }
        .pdfobject-container { height: 500px;}
        .pdfobject { border: 1px solid #666; }
        .dialog_two .el-dialog__body{
            padding: 30px 3px !important;
        }
        /* 上传附件 */
        .uploadResume .el-upload .el-upload__input{
           display: none !important;
       }
       .uploadResume .el-upload-list{
           display: none
       }

       /* 修改下拉 */
       .float-e-margins .btn {
            margin-bottom: 3px !important;
        }
       .ele-BUT{
            display: inline-block;
            font-size: 12px;
            height: 21px;
            color: #1c84c6;
            border: 1px solid #1c84c6;
            border-radius:3px;
       }
       .form-control{
           padding: 0;
       }
       /* 自适应 */
       .dataTables_scrollHeadInner{
            width: 100% !important;
        }
        .dataTables_scrollHeadInner table{
            width: 100% !important;
        }
        .dataTables_scrollBody table{
            width: 100% !important;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="ibox float-e-margins">
        <div class="ibox-content">
                <div class="title">
                        <h4>供应商不合格列表</h4>
                        <div>
                            <button class="btn btn-xs btn-outline btn-success refresh">刷 新</button>
                            <!-- <button class="btn btn-xs btn-outline btn-success add_staff"><span class="glyphicon glyphicon-plus"></span>添 加</button> -->
                            <!-- <button class="btn btn-xs btn-outline btn-success edit_staff"><span class="glyphicon glyphicon-edit"></span>供应商信息修改</button> -->
                            <select class="form-control chosen-select btn-outline ele-BUT push_down" id="select_vol" name="userId" id="useId" style="width:11%;" tabindex="2">
                                <option value="-1" style="background-color: skyblue;" disabled>--供应商信息修改--</option>
                            </select>
                            <button class="btn btn-xs btn-outline btn-success details_staff"><span class="glyphicon glyphicon-align-justify"></span>供应商信息详情</button>
                            <!-- <button class="btn btn-xs btn-outline btn-success audit_staff" @click="fun()"><span class="glyphicon glyphicon-adjust"></span>审 核</button> -->
                            <!-- <button class="btn btn-xs btn-outline btn-success contract_staff"><span class="glyphicon glyphicon-list-alt"></span>采购合同</button> -->
                        </div>
                    </div>
            <div class="table-responsive">
                <table id="staff" class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th>供应商编号</th>
                        <th>供应商名称</th>
                        <th>营业执照</th>
                        <th>营业范围</th>
                        <th>企业性质</th>
                        <th>法人代表</th>
                        <th>开户行</th>
                        <th>账号</th>
                        <th>审核状态</th>
                        <th>质控审核状态</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="ibox-content" id="app">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#contact" aria-controls="contact" role="tab" data-toggle="tab">供应商联系信息</a></li>
                <li role="presentation"><a href="#education" aria-controls="education" role="tab" data-toggle="tab">地址信息</a></li>
                <li role="presentation"><a href="#qualification" aria-controls="qualification" role="tab" data-toggle="tab">资质信息</a></li>
                <li role="presentation"><a href="#development" aria-controls="development" role="tab" data-toggle="tab">获奖情况</a></li>
                <li role="presentation"><a href="#punish" aria-controls="punish" role="tab" data-toggle="tab">客户情况</a></li>
                <li role="presentation"><a href="#contract" aria-controls="contract" role="tab" data-toggle="tab">合作信息</a></li>
                <li role="presentation"><a href="#change" aria-controls="change" role="tab" data-toggle="tab">股权结构</a></li>
                <li role="presentation"><a href="#financeData" aria-controls="financeData" role="tab" data-toggle="tab">财务信息</a></li>
                <li role="presentation"><a href="#teamData" aria-controls="teamData" role="tab" data-toggle="tab">团队信息</a></li>
                <li role="presentation"><a href="#audit" aria-controls="audit" role="tab" data-toggle="tab">质控评估状态</a></li>
            </ul>
            <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="contact">
                        <table class="table table-striped table-hover table-border">
                            <tr>
                                <th>联系人</th>
                                <th>姓名</th>
                                <th>电话</th>
                                <th>手机</th>
                                <th>传真</th>
                                <th>电子邮件</th>
                            </tr> 
                            <tr v-for="item in contact">
                                <td>{{item.contact_position  || ''}}</td>
                                <td>{{item.contact  || ''}}</td>
                                <td>{{item.telephone  || ''}}</td>
                                <td>{{item.phone  || ''}}</td>
                                <td>{{item.fax  || ''}}</td>
                                <td>{{item.e_mail  || ''}}</td>
                            </tr>
                        </table>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="education">
                        <table class="table table-striped table-hover table-border">
                            <tr>
                                <th>地址信息</th>
                                <th>地址描述</th>
                            </tr>
                            <tr v-for="item in address">
                                <td>{{item.address  || ''}}</td>
                                <td>{{item.addr_description  || ''}}</td>
                            </tr>
                        </table>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="qualification">
                            <table class="table table-striped table-hover table-border">
                                <tr>
                                    <th>资质名称</th>
                                    <th>颁发机构</th>
                                    <th>生效时间</th>
                                    <th>失效时间</th>
                                    <th>证书状态</th>
                                    <th>证书名称</th>
                                    <th>证书预览</th>
                                </tr>
                                <tr v-for="item in certification">
                                    <td>{{item.cer_name  || ''}}</td>
                                    <td>{{item.issuing_authority  || ''}}</td>
                                    <td>{{formatDateTime(item.start_time)  || ''}}</td>
                                    <td>{{formatDateTime(item.stop_time)  || ''}}</td>
                                    <td v-if="item.file_status === '0'">未上传</td>
                                    <td v-else-if="item.file_status === '1'">已上传</td>
                                    <td v-else>未知</td>
                                    <td>{{item.file_name || ''}}</td>
                                    <td>
                                        <el-button type="success" size="mini" @click="awardsLookUp(item)">预览证书</el-button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <div role="tabpanel" class="tab-pane" id="financeData">
                            <table class="table table-striped table-hover table-border">
                                <tr>
                                    <th>近两年盈利业绩</th>
                                    <th>资产总额</th>
                                    <th>主营收入</th>
                                    <th>净利润</th>
                                    <th>利润率</th>
                                </tr>
                                <tr v-for="item in finance">
                                    <td>{{item.finance_year  || ''}}</td>
                                    <td>{{item.total_assets  || ''}}</td>
                                    <td>{{item.main_income  || ''}}</td>
                                    <td>{{item.net_profit  || ''}}</td>
                                    <td>{{item.profit_rat  || ''}}</td>
                                </tr>
                            </table>
                        </div>
                    <div role="tabpanel" class="tab-pane" id="teamData">
                            <table class="table table-striped table-hover table-border">
                                <tr>
                                    <th>类别</th>
                                    <th>人员数量</th>
                                    <th>备注</th>
                                </tr>
                                <tr v-for="item in team">
                                    <td>{{item.team_cate  || ''}}</td>
                                    <td>{{item.team_number  || ''}}</td>
                                    <td>{{item.tips  || ''}}</td>
                                </tr>
                            </table>
                        </div>
                    <div role="tabpanel" class="tab-pane" id="development">
                            <table class="table table-striped table-hover table-border">
                                <tr>
                                    <th>获奖名称</th>
                                    <th>颁发机构</th>
                                    <th>获奖时间</th>
                                    <th>记录人</th>
                                    <th>证书状态</th>
                                    <th>证书名称</th>
                                    <th>证书预览</th>
                                </tr> 
                                <tr v-for="item in awards">
                                    <td>{{item.awards_name  || ''}}</td>
                                    <td>{{item.issuing_authority  || ''}}</td>
                                    <td>{{formatDateTime(item.validity_time)  || ''}}</td>
                                    <td>{{item.name  || ''}}</td>
                                    <td v-if="item.file_status === '0'">未上传</td>
                                    <td v-else-if="item.file_status === '1'">已上传</td>
                                    <td v-else>未知</td>
                                    <td>{{item.file_name || ''}}</td>
                                    <td>
                                        <el-button type="success" size="mini" @click="previewAwardPdfLookUp(item)">预览证书</el-button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    
                    <div role="tabpanel" class="tab-pane" id="punish">
                            <table class="table table-striped table-hover table-border">
                                <tr>
                                    <th>客户名称</th>
                                    <th>项目</th>
                                    <th>联系人</th>
                                    <th>电话</th>
                                    <th>实施时间</th>
                                    <th>项目金额</th>
                                </tr> 
                                <tr v-for="item in customer">
                                    <td>{{item.cus_name  || ''}}</td>
                                    <td>{{item.main_project  || ''}}</td>
                                    <td>{{item.main_contact  || ''}}</td>
                                    <td>{{item.main_phone  || ''}}</td>
                                    <td>{{formatDateTime(item.project_exec_time)  || ''}}</td>
                                   <td>{{item.project_amount  || ''}}</td>
                                </tr>
                            </table>
                        </div>
    
                    <div role="tabpanel" class="tab-pane" id="contract">
                        <table class="table table-striped table-hover table-border">
                            <tr>
                                <th>机构名称</th>
                                <th>项目</th>
                                <th>联系人</th>
                                <th>电话</th>
                                <th>时间</th>
                                <th>项目金额</th>
                            </tr>
                            <tr v-for="item in cooperation">
                                <td>{{item.institution_name  || ''}}</td>
                                <td>{{item.main_project  || ''}}</td>
                                <td>{{item.main_contact  || ''}}</td>
                                <td>{{item.main_phone  || ''}}</td>
                                <td>{{formatDateTime(item.project_exec_time)  || ''}}</td>
                                <td>{{item.project_amount  || ''}}</td>
                            </tr>
                        </table>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="change">
                        <!-- <table class="table table-striped table-hover table-border table2000"> -->
                        <table class="table table-striped table-hover table-border">
                            <tr>
                                <th>股东名称</th>
                                <th>持股比例</th>
                                <th>变更时间</th>
                            </tr>
                            <tr v-for="item in equity">
                                <td>{{item.shareholder_name  || ''}}</td>
                                <td>{{item.shareholding_ratio  || ''}}</td>
                                <td>{{formatDateTime(item.update_time)  || ''}}</td>
                            </tr>
                        </table>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="audit">
                        <table class="table table-striped table-hover table-border">
                            <tr>
                                <th>审核名称</th>
                                <th>审核人</th>
                                <th>审核时间</th>
                                <th>备注</th>
                                <th style="width: 100px">审核状态</th>
                                <th>证书名称</th>
                                <th>证书预览</th>
                            </tr>
                            <tr v-for="item in audit">
                                <td>{{item.type_name  || ''}}</td>
                                <td>{{item.name || ''}}</td>
                                <td>{{formatDateTime(item.audit_time) || ''}}</td>
                                <td>{{item.tips || ''}}</td>
                                <td v-if="item.status === '0'">未审核</td>
                                <td v-else-if="item.status === '1'">不合格</td>
                                <td v-else-if="item.status === '2'">合 格</td>
                                <td v-else-if="item.status === null">未知</td>
                                <td>{{item.file_name || ''}}</td>
                                <td>
                                    <el-button type="success" size="mini" @click="previewSecondAuditPdfLookUp(item)">预览证书</el-button>
                                </td>
                    </tr>
                        </table>
                    </div>
                </div>

            <!-- 一级审核 -->
            <div id="main">
                <div id="fullbg"></div> 
                <div id="dialog"> 
                    <p class="close"><a href="#" onclick="closeBg();">关闭</a></p> 
                    <div>
                        <button class="dia_but1">合  格</button>
                        <button class="dia_but1">不合格</button>
                    </div> 
                </div> 
            </div>

            <!-- 二级级审核 -->
            <el-dialog title="选择审核的内容：" :visible.sync="dialogTableVisible" width="60%">
                <el-table :data="secondLevel" :highlight-current-row="true">
                    <el-table-column property="type_name" label="审核名称" width="160px"></el-table-column>
                    <el-table-column property="name" label="审核人"></el-table-column>
                    <el-table-column property="audit_time" label="审核时间"></el-table-column>
                    <el-table-column property="tips" label="备注"></el-table-column>
                    <el-table-column property="status" label="状态">
                        <template slot-scope="scope">
                                <div v-if="scope.row.status === '0'">未审核</div>
                                <div v-else-if="scope.row.status === '1'">不合格</div>
                                <div v-else-if="scope.row.status === '2'">合 格</div>
                                <div v-else-if="scope.row.status === null">未知</div>
                        </template>
                    </el-table-column>
                    <el-table-column property="address" label="操作">
                        <template slot-scope="scope">
                            <el-button type="warning" @click="approve(scope.$index,scope.row.status,scope.row.id,scope.row.tips,scope.row.file_id)">审 核</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-dialog>
            <!-- 二级 审核 再弹框 -->
            <el-dialog title="质控审核确认：" :visible.sync="dialogTableVisible_Movers" width="60%">
                    <el-table :data="inssfsdf">
                      <el-table-column property="type_name" label="审核名称" width="170"></el-table-column>
                      <el-table-column label="审核备注" width="400">
                          <template  slot-scope="scope">
                              <el-input type="textarea" v-model="two_tips"></el-input>
                          </template>
                      </el-table-column>
                      <el-table-column label="上传附件"  width="100">
                          <template  slot-scope="scope">
                              <el-upload
                                  class="uploadResume"
                                  action="<?php echo U('/dwin/purchase/upload');?>"
                                  :data="upLoadData"
                                  :on-success="papersUploadSuccess"
                                  :on-error="uploadError"
                                  :auto-upload="true"
                                  >
                                  <el-button size="small" type="primary" @click="clickUpdata_award()">上传证书</el-button>
                                  <span style="margin-left: 10px;color:green" v-if="updata_howSUCCESS">上传成功</span>
                                  <span style="margin-left: 10px;color:red" v-if="updata_howERRER">上传失败</span>
                              </el-upload>
                          </template>
                      </el-table-column>
                      <el-table-column label="操作">
                          <template  slot-scope="scope">
                              <el-button type="warning" size="mini" @click="checke_yes(1)">不合格</el-button>
                              <el-button type="success" size="mini" @click="checke_yes(2)">合<span>&nbsp;&nbsp;</span> 格</el-button>
                          </template>
                      </el-table-column>
                    </el-table>
                  </el-dialog>
            <!-- 修改选项 弹框 -->
            <el-dialog title="请选择你所修改的项：" class="selsctDialog" :visible.sync="dialogVisible" width="35%">
                <el-row>
                    <el-col :span="8" :offset="1">
                        <el-button type="primary" @click="eleClick_that(0)">基本信息</el-button>
                    </el-col>
                    <el-col :span="8">
                        <el-button type="primary" @click="eleClick_that(1)">地址信息</el-button>
                    </el-col>
                    <el-col :span="7">
                        <el-button type="primary" @click="eleClick_that(2)">获奖情况</el-button>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="8" :offset="1">
                        <el-button type="primary" @click="eleClick_that(4)">股权信息</el-button>
                        
                    </el-col>
                    <el-col :span="8">
                        <el-button type="primary" @click="eleClick_that(5)">财务信息</el-button>
                        
                    </el-col>
                    <el-col :span="7">
                        <el-button type="primary" @click="eleClick_that(6)">资质认证</el-button>
                        
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="8" :offset="1">
                        <el-button type="primary" @click="eleClick_that(3)">客户信息</el-button>
                    </el-col>
                    <el-col :span="8">
                            <el-button type="primary" @click="eleClick_that(7)">团队信息</el-button>
                    </el-col>
                    <el-col :span="7">
                        <el-button type="primary" @click="eleClick_that(8)">合作情况</el-button>
                    </el-col>
                </el-row>
                <el-row style="margin-bottom: 24px">
                    <el-col :span="22"  :offset="1">
                        <el-button type="primary" @click="eleClick_that(9)" style="width: 97%;">联系人信息</el-button>
                    </el-col>
                </el-row>
            </el-dialog>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="/Public/html/js/jquery.form.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/jeditable/jquery.jeditable.js"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="https://cdn.bootcss.com/element-ui/2.3.6/index.js"></script>

<script>
    var table = $('#staff'). DataTable({
        ajax: {
            url:'supplierUnQualifiedIndex',
            type: 'post',
            data: {
                flag: 1
            },
        },
        "scrollY": 440,
        "scrollX": false,
        "scrollCollapse": true,
        "destroy"      : true,
        "paging"       : true,
        "autoWidth"	   : false,
        "pageLength": 25,
        serverSide: true,
        // order:[[21, 'desc']],
        columns: [
            {searchable: true, data: 'supplier_id'},
            {searchable: true, data: 'supplier_name'},
            {searchable: true, data: 'business_licence'},
            {searchable: true, data: 'business_scope'},
            {searchable: false, data: 'enterprise_cate'},
            {searchable: false, data: 'legal_name'},
            {searchable: false, data: 'account_bank'},
            {searchable: true, data: 'account_number'},
            {searchable: true, data: 'audit_status', render: function (data){return ['未审核', '不合格','合格'][+data]}},
            {searchable: true, data: 'second_audit', render: function (data){return ['未审核', '审核不合格','合格完成'][+data]}}
        ],
        oLanguage: {
            "oAria": {
                "sSortAscending": " - click/return to sort ascending",
                "sSortDescending": " - click/return to sort descending"
            },
            "LengthMenu": "显示 _MENU_ 记录",
            "ZeroRecords": "对不起，查询不到任何相关数据",
            "EmptyTable": "未有相关数据",
            "LoadingRecords": "正在加载数据-请等待...",
            "Info": "当前显示 _START_ 到 _END_ 条，共 _TOTAL_ 条记录。",
            "InfoEmpty": "当前显示0到0条，共0条记录",
            "InfoFiltered": "（数据库中共为 _MAX_ 条记录）",
            "Processing": "<img src='../resources/user_share/row_details/select2-spinner.gif'/> 正在加载数据...",
            "Search": "搜索：",
            "Url": "",
            "Paginate": {
                "sFirst": "首页",
                "sPrevious": " 上一页 ",
                "sNext": " 下一页 ",
                "sLast": " 尾页 "
            }
        }
    })
    var currentId
    var current_id
    var current_status
    // var id
    var currentData
    $('tbody').on('click', 'tr', function () {
        currentData = table.row(this).data();
        currentId = currentData.id;
        current_id = currentData.supplier_id
        current_status = currentData.audit_status
        $('tr').removeClass('selected')
        $(this).addClass('selected')
        vm.getButtonTitle(currentId)
    })
   // 修改 下拉
    var ollddBoot = false
    $('.push_down').on('click', function (vul) {
        if(ollddBoot){
            var layerOpen = Number(this.options[this.options.selectedIndex].value)
           vm.eleClick_that(layerOpen)
        }
        ollddBoot = !ollddBoot 
    })
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                centerDialogVisible:false,
                dialogTableVisible:false,
                updata_howSUCCESS:false,
                updata_howERRER:false,
                dialogTableVisible_Movers:false,
                address: [],
                awards:[],
                audit: [],
                certification: [],
                contact: [],
                cooperation: [],
                customer: [],
                equity: [],
                finance: [],
                team: [],
                recordFramework:'',
                employeeDevelopment: {
                    allData: [],
                    currentData: [],
                    pageSize: 10,
                    page: 1,
                    total: 0
                },
                punish: {
                    allData: [],
                    currentData: [],
                    pageSize: 10,
                    page: 1,
                    total: 0
                },
                inssfsdf :[],
                data_save : {
                    supplierPid : '',
                    status : '',
                    id : '',
                    tips : '',
                    file_id : ''
                },
                upLoadData:{
                    type:'1'
                },
                two_tips:'',
                secondLevel:[],
                selectButton:[
                    '供应商基本信息',
                    '供应商地址信息',
                    '供应商获奖情况',
                    '供应商客户信息',
                    '供应商股权信息',
                    '供应商财务信息',
                    '供应商资质认证信息',
                    '供应商团队信息',
                    '供应商合作情况信息',
                    '供应商联系人信息'
                ],
                // 修改 页面title名定义
                cols:[],
                tableData:[],
                base_head:[],
                base_title:[ "供应商编号",  "供应商名称", "营业执照", "营业范围", "企业性质", "法人代表", "开户行", "账号", "创建人", "审核状态"],
                address_title:['地址信息','地址描述'],
                contact_title:["联系人","姓名","电话","手机","传真","电子邮件"],
                certification_title:["资质名称","颁发机构", "起止时间","上传证书状态"],
                awards_title:["获奖名称","颁发机构","时间","记录人","上传证书状态"],
                customer_title:["客户名称","项目","联系人","电话","实施时间","项目金额"],
                equity_title:["股东名称","持股比例","变更时间"],
                finance_title:["近两年盈利业绩","资产总额","主营收入","净利润","利润率"],
                team_title:["类别","人员数量","备注"],
                cooperation_title:["机构名称","项目","联系人","电话","时间","项目金额"],
                tbody_tr:[],
                tbody_show:'',
                this_type:'',
                indexs:'',
                dialogVisible:false,
                uploadID:''
            }
        },
        created(){
            this.creatEle_option()
        },
        watch:{
            dialogTableVisible_Movers:function(newVUE,oldVUE){
                if(!newVUE){
                    this.two_tips = ''
                }
            },
            dialogTableVisible:function(newValue,oldValue){
                if(!newValue){
                    location.reload()
                }
            }
        },
        methods: {
            // 修改项 select
            creatEle_option(){
                for (var i=0;i<this.selectButton.length;i++){
                    var option = document.createElement('option')
                    option.innerHTML = this.selectButton[i]
                    option.setAttribute('value', i)
                    $('#select_vol').append(option);
                }
            },
            // 获取数据 调用方法 封装
            getButtonTitle (currentId){
                $.post('/Dwin/Purchase/getSupplierAllMsg', {id: currentId}, function (res) {
                    vm.address = res.data.address
                    vm.awards = res.data.awards
                    vm.audit = res.data.audit
                    vm.certification = res.data.certification
                    vm.contact = res.data.contact
                    vm.cooperation = res.data.cooperation
                    vm.customer = res.data.customer
                    vm.equity = res.data.equity
                    vm.finance = res.data.finance
                    vm.team   = res.data.team
                })
            },
            // 资质文件 预览LOOK
            awardsLookUp(item){
                if(item.file_url == null||item.file_url == ''){
                    layer.msg('没有找到文件！')
                }else{
                    if(item.file_type == 'pdf'){
                        window.open('<?php echo U("previewCerPdf", [], "");?>/id/' + item.id)
                    }else{
                        window.open(item.file_url)
                    }
                }
            },
            // 奖励证书 预览LOOK
            previewAwardPdfLookUp(item){
                if(item.file_url == null||item.file_url == ''){
                    layer.msg('没有找到文件！')
                }else{
                    if(item.file_type == 'pdf'){
                        window.open('<?php echo U("previewAwardPdf", [], "");?>/id/' + item.id)
                    }else{
                        window.open(item.file_url)
                    }
                    
                }
            },
            // 质控审核 预览LOOK
            previewSecondAuditPdfLookUp(item){
                if(item.file_url == null||item.file_url == ''){
                    layer.msg('没有找到文件！')
                }else{
                    if(item.file_type == 'pdf'){
                        window.open('<?php echo U("previewSecondAuditPdf", [], "");?>/id/' + item.id)
                    }else{
                        window.open(item.file_url)
                    }
                }
            },
            changeDevelopPage: function (page) {
                this.employeeDevelopment.page = page
                var start = (this.employeeDevelopment.page - 1) * this.employeeDevelopment.pageSize
                var end = page * this.employeeDevelopment.pageSize
                this.employeeDevelopment.currentData = this.employeeDevelopment.allData.slice(start, end)
            },
            changePunishPage: function (page) {
                this.punish.page = page
                var start = (this.punish.page - 1) * this.punish.pageSize
                var end = page * this.punish.pageSize
                this.punish.currentData = this.punish.allData.slice(start, end)
            },
            checke_yes(vul){
                vm.data_save = {
                    supplierPid : currentId,
                    status : vul,
                    id : this.uploadID,
                    tips : this.two_tips,
                    file_id : vm.data_save.file_id
                }
                var data = vm.data_save
                $.ajax({
                    url:'/dwin/purchase/secondAuditSupplier',
                    type:'post',
                    dataType:'json',
                    data:data,
                    success:function (res) {
                        if(res.status === 200){
                            layer.msg('审核状态修改成功')
                            vm.dialogTableVisible_Movers = false
                        }else{
                            layer.msg(res.msg)
                        }
                    }
                })
            },
            checke_no(){
                vm.data_save.status = 1
                var data = vm.data_save
                $.ajax({
                    url:'/dwin/purchase/secondAuditSupplier',
                    type:'post',
                    dataType:'json',
                    data:data,
                    success:function (res) {
                        if(res.status === 200){
                            layer.msg('审核修改成功！')
                            table.ajax.reload()
                        }else{
                            layer.msg(res.msg)
                        }
                    }
                })
            },
            // 点击审核再弹框
            approve(index,status,id,tips,file_id){
                this.upLoadData.type = ''
                this.inssfsdf.length = 0
                if(status === '0'){
                    this.inssfsdf.push(this.secondLevel[index])
                    vm.dialogTableVisible_Movers = true
                    this.upLoadData.type = index + 3
                    this.uploadID = id
                    this.updata_howSUCCESS = false
                    this.updata_howERRER = false
                }else{
                    layer.msg('当前状态不让修改！')
                }
            },
            //  点击上传 资质文件
            clickUpdata_award(){
         
             },
             uploadError(){},
            //  上传文件  成功回调  => 资质认证
            papersUploadSuccess (res,file) {
                this.updata_howSUCCESS = false
                this.updata_howERRER = false
                console.log(res)
                if(res.status == 200){
                    this.data_save.file_id = res.data.id
                    this.updata_howSUCCESS = true
                }else{
                    this.updata_howERRER = true
                }
                layer.msg(res.msg)
            },
            // 选择选中项 => ajax => 渲染页面
            eleClick_that (floor) {
                if(floor === 0){
                    var index = layer.open({
                        type: 2,
                        title: '修改供应商基本信息',
                        content: '/Dwin/Purchase/getSupplier?id=' + currentId,
                        area: ['90%', '90%'],
                        shadeClose:true,
                        end: function () {
                            table.ajax.reload()
                            vm.getButtonTitle(currentId)
                        }
                    })
                } else if (floor === 1) {
                    var index = layer.open({
                        type: 2,
                        title: '修改供应商地址信息',
                        content: '/Dwin/Purchase/getAddress?id= ' + currentId,
                        area: ['90%', '90%'],
                        shadeClose:true,
                        end: function () {
                            table.ajax.reload()
                            vm.getButtonTitle(currentId)
                        }
                    })
                } else if (floor === 2) {
                    var index = layer.open({
                        type: 2,
                        title: '修改供应商获奖信息',
                        content: '/Dwin/Purchase/getAwards?id= ' + currentId,
                        area: ['90%', '90%'],
                        shadeClose:true,
                        end: function () {
                            table.ajax.reload()
                            vm.getButtonTitle(currentId)
                        }
                    })
                } else if (floor === 3) {
                    var index = layer.open({
                        type: 2,
                        title: '修改供应商客户信息',
                        content: '/Dwin/Purchase/getCustomer?id= ' + currentId,
                        area: ['90%', '90%'],
                        shadeClose:true,
                        end: function () {
                            table.ajax.reload()
                            vm.getButtonTitle(currentId)
                        }
                    })
                } else if (floor === 4) {
                    var index = layer.open({
                        type: 2,
                        title: '修改供应商股权信息',
                        content: '/Dwin/Purchase/getEquity?id= ' + currentId,
                        area: ['90%', '90%'],
                        shadeClose:true,
                        end: function () {
                            table.ajax.reload()
                            vm.getButtonTitle(currentId)
                        }
                    })
                } else if (floor === 5) {
                    var index = layer.open({
                        type: 2,
                        title: '修改供应商财务信息',
                        content: '/Dwin/Purchase/getFinance?id= ' + currentId,
                        area: ['90%', '90%'],
                        shadeClose:true,
                        end: function () {
                            table.ajax.reload()
                            vm.getButtonTitle(currentId)
                        }
                    })
                }else if (floor === 6) {
                    var index = layer.open({
                        type: 2,
                        title: '修改供应商资质认证信息',
                        content: '/Dwin/Purchase/getCertification?id= ' + currentId,
                        area: ['90%', '90%'],
                        shadeClose:true,
                        end: function () {
                            table.ajax.reload()
                            vm.getButtonTitle(currentId)
                        }
                    })
                } else if (floor === 7) {
                    var index = layer.open({
                        type: 2,
                        title: '修改供应商团队信息',
                        content: '/Dwin/Purchase/getTeam?id= ' + currentId,
                        area: ['90%', '90%'],
                        shadeClose:true,
                        end: function () {
                            table.ajax.reload()
                            vm.getButtonTitle(currentId)
                        }
                    })
                }else if (floor === 8) {
                    var index = layer.open({
                        type: 2,
                        title: '修改供应商合作信息',
                        content: '/Dwin/Purchase/getCooperation?id= ' + currentId,
                        area: ['90%', '90%'],
                        shadeClose:true,
                        end: function () {
                            table.ajax.reload()
                            vm.getButtonTitle(currentId)
                        }
                    })
                }else if (floor === 9) {
                    var index = layer.open({
                        type: 2,
                        title: '修改供应商联系人信息',
                        content: '/Dwin/Purchase/getContact?id= ' + currentId,
                        area: ['90%', '90%'],
                        shadeClose:true,
                        end: function () {
                            table.ajax.reload()
                            vm.getButtonTitle(currentId)
                        }
                    })
                }
            },
            // 时间戳转化为时间
            formatDateTime:function (timeStamp) { 
                if(timeStamp != null&&timeStamp != 0){
                    var date = new Date();
                    date.setTime(timeStamp * 1000);
                    var y = date.getFullYear();    
                    var m = date.getMonth() + 1;    
                    m = m < 10 ? ('0' + m) : m;    
                    var d = date.getDate();    
                    d = d < 10 ? ('0' + d) : d;    
                    var h = date.getHours();  
                    h = h < 10 ? ('0' + h) : h;  
                    var minute = date.getMinutes();  
                    var second = date.getSeconds();  
                    minute = minute < 10 ? ('0' + minute) : minute;    
                    second = second < 10 ? ('0' + second) : second;   
                    // return y + '-' + m + '-' + d+' '+h+':'+minute+':'+second;  
                    return y + '-' + m + '-' + d;  
                }else{
                    return ''
                }
            }
        }
    })
    // 刷新
    $('.refresh').on('click', function () {
        table.order([[5, 'desc']])
        table.ajax.reload()
    })
    //关闭  一级审核 遮罩 h
    function closeBg() {
        $("#fullbg,#dialog").hide();
    }
    //关闭 二级审核弹框 遮罩
    function closeBg_secondTime() {
        $("#fullbg_secondTime,#dialog_secondTime").hide();
    }
    //关闭 二级审核 再弹框 遮罩
    function closeBg_thereTime() {
        $("#fullbg_thereTime,#dialog_thereTime").hide();
    }
    //关闭 修改 选择层 遮罩
    function closeBg_select() {
        $("#fullbg_select ,#dialog_select ").hide();
    }
    //关闭 修改 编辑层 遮罩 select
    function closeBg_revamp() {
        $("#fullbg_revamp,#dialog_revamp").hide();
        vm.tbody_tr = []
        vm.indexs = ''
    }
    //关闭 最终确认 
    function closeBg_sureToSubmit() {
        $("#fullbg_sureToSubmit,#dialog_sureToSubmit").hide(); 
        vm.tbody_tr = []
    }
    // 添加
    $('.add_staff').on('click', function () {
        var index = layer.open({
            type: 2,
            title: '添加供应商信息',
            content: '/Dwin/Purchase/addSupplier?',
            area: ['90%', '90%'],
            shadeClose:true,
            end: function () {
                table.ajax.reload()
            }
        })
    })
    // 修改
    $('.edit_staff').on('click', function () {
        if (current_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            vm.dialogVisible = true
        }
    })
    // 详情页
    $('.details_staff').on('click', function () {
        if (current_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            var index = layer.open({
                type: 2,
                title: '供应商信息详情',
                content: '/Dwin/Purchase/supplierDetail?id=' + currentId,
                area: ['90%', '90%'],
                shadeClose:true,
                shift:1,
                end: function () {
                    table.ajax.reload()
                }
            })
        }
    })
    // 采购合同
    $('.contract_staff').on('click', function () {
        if (current_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            console.log(current_status)
            if (current_status == 2) {
                var index = layer.open({
                    type: 2,
                    title: '采购合同信息',
                    content: '/Dwin/Purchase/addContract?supplierId=' + currentId,
                    area: ['90%', '90%'],
                    shadeClose:true,
                    end: function () {
                        table.ajax.reload()
                    }
                })
            }else if(current_status == 1){
                layer.msg('该供应商审核不合格！')
            }else if(current_status == 0){
                layer.msg('该供应商还没审核！')
            }
        }
    })
    // 审核
    $('.audit_staff').on('click', function () {
        if (current_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            var data = {
                'id' : currentId,
                'type' : 'audit'
            }
            $.ajax({
                url:'/dwin/purchase/supplierOtherMsg',
                type:'post',
                dataType:'json',
                data:data,
                success:function (res) {
                    if(res.status === 200){
                        // var save_status = res.data.
                        var getData = res.data
                        vm.secondLevel = getData
                    }
                }
            })
                vm.dialogTableVisible = true
            }
    })
    // 合格
    $('#dialog button').on('click', function () {
        if($(this).html() === '合  格'){
            var data={"id":currentId,"status":1};
            ajax_pack('/dwin/purchase/firstAuditSupplier','post',data)
        }else{
            var data={"id":currentId,"status":2};
            ajax_pack('/dwin/purchase/firstAuditSupplier','post',data)
        }
    })
    // ajax封装
    function ajax_pack(url,way,data){
        $.ajax({
            url:url,
            type:way,
            dataType:'json',
            data:data,
            success:function (res) {
                if(res.status === 200){
                    $('.refresh').on('click', function () {
                        table.order([[5, 'desc']])
                        table.ajax.reload()
                    })
                }else{
                    $("#fullbg,#dialog").hide()
                    layer.msg(res.msg)
                }
            }
        })
    }

</script>
</body>
</html>