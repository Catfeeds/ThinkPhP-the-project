<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>客服业绩统计</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        .selected{
            background-color: #5BC0DE !important;
        }
        body{
            color:black;
        }
        .active a{
            background-color: #1c84c6 !important;
            color: #fff !important;
        }
    </style>
</head>
<body>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins" id="order-div">
                <div class="tabs-container">
                    <div class="ibox-title">
                        <h5>客服/FAE 数据统计</h5>
                        <div class="ibox-tools"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></div>
                    </div>
                    <div>
                        <form class="form-inline">
                            <label for="startMouth">选择月份</label>
                            <input type="text" class="" id="startMouth">
                        </form>
                    </div>
                    <div class="ibox-content">
                        <ul class="nav nav-tabs">
                            <li class="active"><a data-toggle="tab" href="#tab-1" aria-expanded="true" id="settle2">有效客服记录统计</a>
                            </li>
                            <li class=""><a data-toggle="tab" href="#tab-2" aria-expanded="false">本月100元以上业绩客户</a>
                            </li>
                            <li class=""><a data-toggle="tab" href="#tab-3" aria-expanded="false" id="settle3">统计结果</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div id="tab-1" class="tab-pane active">
                                <div class="panel-body">
                                    <div class="table-responsive1">
                                        <table class="table table-striped table-bordered table-hover dataTables-online-service" id="onlineData">
                                            <thead>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                        <!--   <input class="btn btn-outline btn-success" type="button" id="orderCheck" value="审核选中项" />-->
                                    </div>
                                </div>
                            </div>
                            <div id="tab-2" class="tab-pane">
                                <div class="panel-body">
                                    <div class="table-responsive2">

                                        <table class="table table-striped table-bordered table-hover dataTables-online-statistics" id="cusStatistics">
                                            <thead>
                                            <th>客户</th>
                                            <th>业绩（元）</th>
                                            <th>业务</th>
                                            <th>部门</th>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                        <!--   <input class="btn btn-outline btn-success" type="button" id="orderCheck" value="审核选中项" />-->
                                    </div>
                                </div>
                            </div>

                            <div id="tab-3" class="tab-pane">
                                <div class="panel-body">
                                    <div class="table-responsive2">
                                        <table class="table table-striped table-bordered table-hover dataTables-online-service" id="saleStatistics">
                                            <thead>
                                            <th>业务</th>
                                            <th>职位</th>
                                            <th>部门</th>
                                            <th>本月100元以上业绩客户数</th>
                                            <th>本月有效客服记录</th>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                        <!--   <input class="btn btn-outline btn-success" type="button" id="orderCheck" value="审核选中项" />-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/jeditable/jquery.jeditable.js"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/plugins/laydate/laydate.js"></script>
<script>
    var startMouth;
    var onlineDataTable;
    var cusStatisticsData;
    var saleStatisticsData;
    var month = [];
    for (var i = 0; i < 12; i++){
        if (i in [1,3,5,7,8,10,12]){
            month.push(31)
        }else if (i === 2){
            month.push(28)
        }else {
            month.push(30)
        }
    }
    var currentMouth = new Date().getMonth() - 1;
    var min = month[currentMouth -1] + month[currentMouth -2];
    laydate.render({
        elem: '#startMouth',
        type: 'month',
        max: 0,
        min: -min,
        done: function (data) {
            console.log(data);
            startMouth = data;
            onlineDataTable.settings();
            onlineDataTable.ajax.reload();
        }
    });

    var controller = "/Dwin/OnlineService";
    var onlineDataDiv = $("#onlineData");
    var cusStatisticsDataTable = $("#cusStatistics").DataTable({
        data: cusStatisticsData,
        destroy:true,
        columns: [
            {data: 'cname'},
            {data: 'count'},
            {data: 'name'},
            {data: 'dept'}
        ]
    });
    var saleStatisticsDataTable = $("#saleStatistics").DataTable({
        data: saleStatisticsData,
        destroy: true,
        columns: [
            {data: 'name'},
            {data: 'role_name'},
            {data: 'dept'},
            {data: 'cus_num'},
            {data: 'service_num'}
        ]
    });
    onlineDataTable = onlineDataDiv.DataTable({
        "destory"      : true,
        "paging"       : true,
        "pagingType"   : "full_numbers",
        "lengthMenu"   : [10, 20, 35, 50],
        "bDeferRender" : true,
        "processing"   : true,
        "searching"    : true, //是否开启搜索
        "serverSide"   : true,//开启服务器获取数据
        "ajax"         : {  //获取数据
            "url"   : controller + "/countServicePerformance",
            "type"  : 'post',
            "data"  : {
                dataT : 1,
                startTime: function () {
                    if (startMouth == undefined){
                        return ''
                    }
                    return startMouth
                }
            }
        },
        "columns" :[ //定义列数据来源
            {'title' : "客户",        'data'      : 'c_id'},
            {'title' : "服务<br>时间", 'data': "service_time"},
            {'title' : "客户问题",     'data' : "cus_quesition"},
            {'title' : "解决方案",    'data' : "online_solution"},
            {'title' : "客服工程师",  'data' : "staff_name"}
        ],
        "columnDefs"   : [ //自定义列
            {
                "targets" : 2,
                "data" : "cus_question",
                "render" : function(data, type, row) {
                    var html = "";
                    if (row.cus_question.length > 15) {
                        html += "<span data = '" + row.cus_question + "'>" + row.cus_question.substring(0, 15) + "...</span>";
                    } else {
                        html += "<span>" + row.cus_question.substring(0, 15) + "</span>";
                    }
                    return html;
                }
            },
            {
                "targets" : 3,
                "data" : "online_solution",
                "render" : function(data, type, row) {
                    var html = "";
                    if (row.online_solution.length > 15) {
                        html += "<span data = '" + row.online_solution + "'>" + row.online_solution.substring(0, 15) + "...</span>";
                    } else {
                        html += "<span>" + row.online_solution.substring(0, 15) + "</span>";
                    }
                    return html;
                }
            }
        ],
        "language"     : { // 定义语言
        "sProcessing"     : "加载中...",
            "sLengthMenu"     : "每页显示 _MENU_ 条记录",
            "sZeroRecords"    : "没有匹配的结果",
            "sInfo"           : "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
            "sInfoEmpty"      : "显示第 0 至 0 项结果，共 0 项",
            "sInfoFiltered"   : "(由 _MAX_ 项结果过滤)",
            "sInfoPostFix"    : "",
            "sSearch"         : "搜索:",
            "sUrl"            : "",
            "sEmptyTable"     : "表中数据为空",
            "sLoadingRecords" : "载入中...",
            "sInfoThousands"  : ",",
            "oPaginate"       : {
            "sFirst"    : "首页",
                "sPrevious" : "上一页",
                "sNext"     : "下一页",
                "sLast"     : "末页"
        },
        "oAria"           : {
            "sSortAscending"  : ": 以升序排列此列",
                "sSortDescending" : ": 以降序排列此列"
        }
    }
    });
    onlineDataTable.on('xhr', function (e, settings, json, xhr) {
        if (json.status < 0){
            return layer.msg(json.msg)
        }
        cusStatisticsData = json.kehu100yuan;
        saleStatisticsData = json.staffData;
        saleStatisticsDataTable = $("#saleStatistics").DataTable({
            data: saleStatisticsData,
            destroy:true,
            order:[[3,'desc']],
            columns: [
                {data: 'name'},
                {data: 'role_name'},
                {data: 'dept'},
                {data: 'cus_num'},
                {data: 'service_num'}
            ]
        });
        cusStatisticsDataTable = $("#cusStatistics").DataTable({
            data: cusStatisticsData,
            destroy:true,
            order:[[2,'desc']],
            columns: [
                {data: 'cname'},
                {data: 'count'},
                {data: 'name'},
                {data: 'dept'}
            ]
        });
    })


</script>
</html>