<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>市场部公共池客户录入有效客户统计-数据表格</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.4/theme-chalk/index.css" rel="stylesheet">
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
                            <!--<a href="javascript:;" id="cusAdd">-->
                            <!--<button type="button" class="btn btn-sm btn-outline btn-success" id="statistic">-->
                              <!--<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 数据统计-->
                            <!--</button>-->
                            <!--</a>-->
                        </div>
                    </div>
                </div>

                <div class="ibox-content" id="table-cus-div">
                    <main id="app">
                        <el-row>
                            <el-form>
                                <el-form-item label="统计时间段筛选">
                                    <el-date-picker
                                            v-model="timeRange"
                                            type="daterange"
                                            range-separator="至"
                                            start-placeholder="开始日期"
                                            end-placeholder="结束日期"
                                            @change="timeRangeChange">
                                    </el-date-picker>
                                </el-form-item>
                            </el-form>
                        </el-row>
                    </main>
                    <span style="color:red" id="information"></span></br>
                    <span id="resets"></span>
                    <table class="table table-striped table-bordered table-hover dataTables-CusStatistics">
                        <tbody>
                        <tr class="gradeX">
                        </tr>
                        </tbody>
                    </table>

                </div>
                <div class="col-xs-12 ibox-content">
                </div>
                <!--<div id="main" style="width: 600px;height:400px;"></div>-->
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>

<script src="/Public/html/js/vue.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.4/index.js"></script>
<script src="/Public/html/js/plugins/jeditable/jquery.jeditable.js"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/dwin/customer/common_func.js"></script>
<script>
    var controller = "/Dwin/Customer";
    var statisticCus = $(".dataTables-CusStatistics");
    var oTables;
    var limitDate = new Date();
    var startTime = Date.parse(limitDate.getFullYear() + "-" + (limitDate.getMonth() - 1) + '-1')/1000;
    var endTime = parseInt(limitDate.getTime()/1000);
    function msgTable()
    {
        statisticCus.DataTable({
            "destroy"      : true,
            "paging"       : true,
            "autoWidth"	   : false,
            "pagingType"   : "full_numbers",
            "lengthMenu"   : [10, 20, 50, 100],
            "bDeferRender" : true,
            "processing"   : true,
            "searching"    : true, //是否开启搜索
            "serverSide"   : true, //开启服务器获取数据
            "ajax"         :{ // 获取数据
                "url"   : controller + "/showMarketCusStatistics",
                "type"  : 'post',
                "data"  : {
                    startT : startTime,
                    endT   : endTime
                }
            },
            "columns" :[ //定义列数据来源
                {'title' : "市场部", 'data' : "staff_name"},
                {'title' : "提交客户数",  'data':"cus_sale_num"},
                {'title' : "被采纳客户数",   'data' : "cus_market_num"}
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
    function showDetail(dataTable, cfg, tr, url)
    {
        var table = dataTable.DataTable();
        var row = table.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
        } else {
            table.$('tr.show').removeClass('shown');
            $(".dataTables-CusStatistics tbody tr").removeClass('shown');
            var sonTable;
            $.ajax({
                type : 'post',
                url  : url,
                data : {
                    staff_id : cfg['id'],
                    startT   : cfg['startTime'],
                    endT     : cfg['endTime']
                },
                success :function (res) {
                    if (res.status == 200) {
                        var ajaxData = res.data;
                        sonTable =
                            '<table class="table table-bordered table-condensed table-striped table-hover">' +
                            '<tbody>' +
                                '<tr>' +
                                    '<th>创建客户</th>'+
                                    '<th>创建时间</th>'+
                                    '<th>现负责人</th>'+
                                '</tr>';

                        for(var i = 0; i < ajaxData.length; i ++)
                        {
                            sonTable +=
                                '<tr>' +
                                '<td rowspan="1" width="25%">' + ajaxData[i]['cus_name'] + '</td>' +
                                '<td>' + ajaxData[i]['add_time'] + '</td>' +
                                '<td>' + ajaxData[i]['u_name'] + '</td>' +
                                '</tr>';
                        }
                        sonTable += "</tbody></table>";
                        row.child(sonTable).show();
                        tr.addClass('shown');
                    }

                }
            });
        }
    }

    $(document).ready(function() {
        $.fn.dataTable.ext.errMode = 'none';

        var vm = new Vue({
            el: '#app',
            data: function () {
                return {
                    loading: false,
                    timeRange: []
                }
            },
            methods: {
                timeRangeChange: function () {
                    console.log(vm);
                    var oTable= statisticCus.DataTable();
                    oTable.settings()[0].ajax.data = {
                        startT : this.timeRange && this.timeRange[0].valueOf() / 1000,
                        endT   : this.timeRange && this.timeRange[1].valueOf() / 1000
                    };
                    oTable.ajax.reload();
                }
            }
        });

        oTables = msgTable();
        $("tbody span .unCheck").css('color','red');
        $("tbody span .allRecord").css('color','blue');
        statisticCus.on('click', 'tbody td', function () {
            var tr = $(this).closest('tr');
            var cfg = [];
            cfg['id'] = $(this).parent().attr('id');
            cfg['startTime'] = vm.timeRange.length ? vm.timeRange[0].valueOf() / 1000 : startTime;
            cfg['endTime']   = vm.timeRange.length ?  vm.timeRange[1].valueOf() / 1000 : endTime;
            showDetail(statisticCus,cfg, tr, "/Dwin/Customer/showMarketCusStatistics");
        });

    }); //initTable END

</script>
</body>
</html>