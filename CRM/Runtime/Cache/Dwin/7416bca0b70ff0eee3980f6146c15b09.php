<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客户满意度调查-数据表格</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
        <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {  color: black;  }
        tbody td{  cursor:pointer;  }
	.cus-24{ color:red;}
	.selected{
            background-color: yellow !important;
        }
    .chosen-customer-type {
        color : blue;
    }
    .ibox-title {
        padding-top: 7px;
    }
    .chosen-select{
        width : 100%;
    }
        .profile-tip{
            font-size:1.3em;
            font-weight: 400;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">

		            <input type="hidden" name="k" id="k" value="1">
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                        <div class="fa-hover col-sm-2 col-sm-1">
                            <a href="javascript:;" id="cusAdd">
                            <button type="button" class="btn btn-sm btn-outline btn-success" id="statistic">
                              <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 数据统计
                            </button>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="ibox-content" id="table-cus-div">
                    <span style="color:red" id="information"></span></br>
                    <span id="resets"></span>
                    <table class="table table-striped table-bordered table-hover dataTables-Callback">
                        <tbody>
                        <tr class="gradeX">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>

                </div>
                <div class="col-xs-12 ibox-content">
                    <p class='profile-tip'>本期已经回访客户共计<?php echo ($all); ?>家，其中<?php echo ($un); ?>家出现了不满意评价。</p>
                </div>
                <!--<div id="main" style="width: 600px;height:400px;"></div>-->
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/Public/html/js/demo/form-advanced-demo.min.js"></script>
<script src="/Public/html/js/plugins/jeditable/jquery.jeditable.js"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/dwin/customer/common_func.js"></script>
<script>
    var controller = "/Dwin/Customer";
    var k = $("#k").val();
    var dataTablesCallback = $(".dataTables-Callback");

    var oTables;
    var tableCusDiv = $("#table-cus-div");
    dataTablesCallback.on('mouseenter','tbody td', function () {
        var tdIndex = $(this).parent()['context']['cellIndex'];
        if (tdIndex === 5 || tdIndex === 7) {
            var dataTips = $(this).find('span').attr('data');
            var num = $(this).parent();
            if (dataTips) {
                layer.tips(
                    dataTips, num, {
                        tips: [1, '#3595CC'],
                        area: '900px',
                        time: 100000
                    });
            }
        } else {
            return false;
        }
    });
    dataTablesCallback.delegate('tbody td', 'mouseleave',function(e) {
        layer.closeAll('tips');
    });
    function msgTable()
    {
        dataTablesCallback.dataTable({
            "destroy"      : true,
            "paging"       : true,
            "autoWidth"	   : false,
            "pagingType"   : "full_numbers",
            "lengthMenu"   : [10, 15, 20, 100],
            "bDeferRender" : true,
            "processing"   : true,
            "searching"    : true, //是否开启搜索
            "serverSide"   : true, //开启服务器获取数据
            "ajax"         :{ // 获取数据
                "url"   : controller + "/showCallbackResult",
                "type"  : 'post'
                /*"data"  : {
                    "type_limit": function () {
                        return document.getElementById('cus-type-selecter').value;
                    },
                    "uid_limit" : function () {
                        return document.getElementById('cus-res-selecter').value;
                    }
                }*/
            },
            "columns" :[ //定义列数据来源
                {'title' : "客户名称", 'data' : "cus_name", 'class' : "mouseOn cusDetail"},
                {'title' : "满意情况",  'data':"satisfied_flag"},
                {'title' : "联系人",   'data' : "contact_name"},
                {'title' : "业务员",   'data' : "u_name"},
                {'title' : "迪文服务", 'data' : "question_1flag"},
                {'title' : "反馈意见", 'data' : "question_1tip"},
                {'title' : "业务服务", 'data' : "question_2flag"},
                {'title' : "反馈意见", 'data' : "question_2tip"},
                {'title' : "新品推广", 'data' : "question_3flag"}
            ],
            "columnDefs"   : [ //自定义列
                {
                    "targets" : 5,
                    "data"    : 'question_1tip',
                    "render"  : function(data, type, row) {
                        var html = "";
                        if (row.question_1tip.length > 6) {
                            html = '<span class="unCheck" style="color:red;" data="' + row.question_1tip + '">' + row.question_1tip.substring(0,6) + '...</span>';
                        } else {
                            html = '<span>' + row.question_1tip + '</span>';
                        }

                        return html;
                    }
                },
                {
                    "targets" : 7,
                    "data"    : 'question_2tip',
                    "render"  : function(data, type, row) { var html = "";
                        if (row.question_2tip.length > 6) {
                            html = '<span class="unCheck" style="color:red;" data="' + row.question_2tip + '">' + row.question_2tip.substring(0,6) + '...</span>';
                        } else {
                            html = '<span>' + row.question_2tip + '</span>';
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
        });//table end
    }
    $(document).ready(function() {
        $.fn.dataTable.ext.errMode = 'none';
        oTables = msgTable();
        $("tbody span .unCheck").css('color','red');
        $("tbody span .allRecord").css('color','blue');
        $("#statistic").on('click' , function () {
            $(this).attr('disabled', true);

            layer.open({
                type: 2,
                area: ['100%', '100%'],
                end :function () {
                    $("#statistic").attr('disabled', false);
                },
                title: '回访结果统计',
                content: '/Dwin/Customer/countCallback'
            });
        });
    }); //inintTable END

</script>
</body>
</html>