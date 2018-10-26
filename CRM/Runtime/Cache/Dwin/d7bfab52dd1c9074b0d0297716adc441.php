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
        /*.table-responsive{*/
            /*height: 400px;*/
            /*overflow: auto;*/
        /*}*/
        .dataTables_scroll{
            overflow: auto !important;
        }
        .dataTables_scrollHead{
            overflow: initial !important;
        }
        .dataTables_scrollBody{
            overflow:initial !important;
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
        /* .el-dialog__body{
            text-align: center
        } */
        /* 上传 */
        .uploadResume .el-upload .el-upload__input{
           display: none !important;
       }
       table tr{
           height: 35px;
       }
       /* #staff tr td:nth-child(1):hover{
           background-color: blue
       } */
       #staff tr td:hover{
           background-color: #ccc
       }
       .colorBG{
           background-color: red
       }

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
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="ibox float-e-margins">
        <div class="ibox-content">
                <div class="title">
                        <h4>负责人名下客户数量</h4>
                        <div>
                            <button class="btn btn-xs btn-outline btn-success refresh">刷 新</button>
                            <!-- <button class="btn btn-xs btn-outline btn-success edit_staff"><span class="glyphicon glyphicon-plus"></span>其他出库新增</button> -->
                            <!-- <button class="btn btn-xs btn-outline btn-success revamp_staff"><span class="glyphicon glyphicon-align-justify"></span>修 改</button> -->
                            <!-- <button class="btn btn-xs btn-outline btn-success details_staff"><span class="glyphicon glyphicon-cloud-upload"></span>情 页</button> -->
                            <!-- <button class="btn btn-xs btn-outline btn-success delete_staff"><span class="glyphicon glyphicon-remove"></span>删 除</button> -->
                            <!-- <button class="btn btn-xs btn-outline btn-success indent_staff"><span class="glyphicon glyphicon-print"></span>下载/打印</button> -->
                            <!-- <button class="btn btn-xs btn-outline btn-success audit_staff"><span class="glyphicon glyphicon-adjust"></span>审核</button> -->
                            <!-- <select class="form-control chosen-select btn-outline ele-BUT push_down" id="select_vol" name="userId" style="width:9%;" tabindex="2">
                                <option value="">--筛选类型--</option>
                                <?php if(is_array($cateMap)): $i = 0; $__LIST__ = $cateMap;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($vol); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select> -->
                        </div>
                    </div>
            <div class="table-responsive">
                <table id="staff" class="table table-bordered table-hover table-striped">
                    <thead></thead>
                    <tbody></tbody>
                </table>
            </div>
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
            type: 'post',
            data: {
                flag: 1
            }
        },
        "scrollY": 400,
        "scrollX": true,
        "scrollCollapse": true,
        "destroy"      : true,
        "paging"       : true,
        "autoWidth"	   : true,
        "pageLength": 25,
        serverSide: true,
        // order:[[21, 'desc']],    
        columns: [                                                                                                                       
            {title:'负责人',searchable: true, data: 'name'},
            {title:'客户总数',searchable: false, data: 'customers'},
            {title:'kpi客户数',searchable: false, data: 'kpi_customers'},
            {title:'去除cus_pid有值的客户数',searchable: false, data: 'main_customers'},
            {title:'部门名称',searchable: true, data: 'dep_name'}
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
</script>
</body>
</html>