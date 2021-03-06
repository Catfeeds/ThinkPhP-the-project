<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM--完工生产计划列表</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;
            font-size: 12px;
        }
        .selected{ 
            background-color: #fbec88 !important;
        }
        td.row-details-open {
            background: url('/Public/Admin/images/details_open.png') no-repeat center center;
            cursor: pointer;
        }
        td.row-details-close {
            background: url('/Public/Admin/images/details_close.png') no-repeat center center;
            cursor:pointer;
        }
        .row-details{
            background: url('/Public/Admin/images/details_open.png') no-repeat center center;
            cursor:pointer;
        }
        .nav-tabs>li>a{
            color: #555;
        }
        .nav-tabs>.active>a{
            color: #000!important;
        }


        .btn{
            margin-right: 1em;
        }
        .nav-tabs>.active>a {
            background-color: #1c84c6 !important;
            color: #fff !important;
            font-weight: bold;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins" id="orders">
                <div class="ibox-content">
                    <h3>完工生产计划列表</h3>
                    <!--<button href="addProductionPlan.html" class="btn btn-info btn-sm addPlan">添加生产计划</button>-->
                    <!--<button class="btn btn-info btn-sm editPlan">修改生产计划</button>-->
                    <!--<button class="btn btn-info btn-sm audit_btn">审核</button>-->
                    <!--<button class="btn btn-info btn-sm i_stock" style="display: none;">入库</button>-->
                    <div class="table-responsive1">
                        <table class="table table-striped table-bordered table-hover table-full-width dataTables-productionList" id="productionPlan">
                            <thead>
                                <tr>
                                    <th>生产单号</th>
                                    <th>业务员</th>
                                    <th>备货方式</th>
                                    <th>型号</th>
                                    <th>生产线</th>
                                    <th>生产数量</th>
                                    <th>下单日期</th>
                                    <th>期望日期</th>
                                    <th>完工日期</th>
                                    <th>状态</th>
                                    <th>特殊要求</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins" >
                <div class="ibox-content">
                    <div class="table-responsive1" id="detailsModel">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#audit" aria-controls="audit" role="tab" data-toggle="tab">审核日志</a></li>
                            <li role="presentation"><a href="#material" aria-controls="material" role="tab" data-toggle="tab">齐料登记</a></li>
                            <li role="presentation"><a href="#stock" aria-controls="stock" role="tab" data-toggle="tab">入库登记</a></li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="audit">
                                <table class="table table-striped table-bordered table-hover table-full-width dataTables-productionList" id="audit_table" >
                                    <thead>
                                    <tr>
                                        <th>生产单号</th>
                                        <th>审核类型</th>
                                        <th>审核意见</th>
                                        <th>审核备注</th>
                                        <th>审核人</th>
                                        <th>审核日期</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for=" v in auditorTableData">
                                        <td>{{v.production_order}}</td>
                                        <td>{{v.audit_type | auditType}}</td>
                                        <td>{{v.audit_result | auditResult}}</td>
                                        <td>{{v.tips}}</td>
                                        <td>{{v.auditor_name}}</td>
                                        <td>{{v.update_time}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="material">
                                <table class="table table-striped table-bordered table-hover table-full-width dataTables-productionList">
                                <thead>
                                <tr>
                                    <th>生产单号</th>
                                    <th>产品名</th>
                                    <th>数量</th>
                                    <th>审核人</th>
                                    <th>备注</th>
                                    <th>更新时间</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="v in materialTableData">
                                    <td>{{v.production_order}}</td>
                                    <td>{{v.product_name}}</td>
                                    <td>{{v.num}}</td>
                                    <td>{{v.manager_name}}</td>
                                    <td>{{v.tips}}</td>
                                    <td>{{v.create_time}}</td>
                                </tr>
                                </tbody>
                                </table>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="stock">
                                <table class="table table-striped table-bordered table-hover table-full-width dataTables-productionList">
                                    <thead>
                                    <tr>
                                        <th>申请日期</th>
                                        <th>产品名</th>
                                        <th>数量</th>
                                        <th>生产单号</th>
                                        <th>库房</th>
                                        <th>备注</th>
                                        <th>审核人</th>
                                        <th>入库时间</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="v in inputStockTableData" >
                                        <td>{{v.create_time}}</td>
                                        <td>{{v.product_name}}</td>
                                        <td>{{v.num}}</td>
                                        <td>{{v.action_order_number}}</td>
                                        <td>{{v.warehouse_number}}</td>
                                        <td>{{v.tips}}</td>
                                        <td>{{v.name}}</td>
                                        <td>{{v.update_time}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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
<script src="/Public/html/js/dwin/finance/common_finance.js"></script>
<script>
    // $('.addPlan').on('click',function () {
    //     var index = layer.open({
    //         type: 2,
    //         title: '添加生产计划',
    //         content: "<?php echo U('addProductionPlan');?>",
    //         area: ['100%', '100%'],
    //         end: function () {
    //             oTable.ajax.reload();
    //         }
    //     })
    // });

var oTable;
$(document).ready(function() {
    oTable = $("#productionPlan").DataTable({
        ajax: { // 获取数据
                "type"  : "post"
            },
        serverSide: true,
        order:[['8', 'desc']],
        columns: [
            {data:'production_order'},
            {data:'staff_name'},
            {data:'stock_cate_name',searchable:false},
            {data:'product_name'},
            {data:'production_line_name',searchable:false},
            {data:'production_plan_number',searchable:false},
            {data:'create_time',searchable:false},
            {data:'delivery_time',searchable:false},
            {data:'complete_time',searchable:false},
            {data:'null',defaultContent:'已完工'},
            {data:'tips',searchable:false, render: function (data,a,row) {
                    var allData = oTable.data();
                    var index = allData.indexOf(row);
                    var className = 'tips' + index;
                    var str = ''
                    if (data == null){
                        data = ''
                    }
                    if (data.length > 10){
                        str = data.slice(0, 10) + '...'
                    } else {
                        str = data
                    }
                    return "<span class='tips' id='" + className + "'>" + str + "</span>"
                }}
                ],
                'fnRowCallback':function(nRow,aData,iDisplayIndex,iDisplayIndexFull){
                    /*
                        nRow:每一行的信息 tr  是Object
                        aData：行 index
                    */
                    for(let key in nRow){
                        var AD_sa = nRow['childNodes'][nRow['childNodes'].length - 2]
                        $(AD_sa).css('color','green')
                    }
                },
        "oLanguage": {
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
            //多语言配置文件，可将oLanguage的设置放在一个txt文件中，例：Javascript/datatable/dtCH.txt
            "Paginate": {
                "sFirst": "首页",
                "sPrevious": " 上一页 ",
                "sNext": " 下一页 ",
                "sLast": " 尾页 "
            }
        }
    });
    $('#productionPlan').on('mouseenter', '.tips', function () {
        var id = $(this).attr('id')
        var data = oTable.cell($(this).parents('td')).data()
        layer.tips(data, '#' + id)
    })
});
    var vm = new Vue({
        el: '#detailsModel',
        data: function () {
            return {
                auditorTableData:[],
                materialTableData:[],
                inputStockTableData:[]
            }
        },
        filters: {
            auditType: function (data) {
                var arr = ['单据审核', '产线确认'];
                return arr[data-2]
            },
            auditResult: function (data) {
                var arr = ['通过', '不通过'];
                return arr[data-1]
            }
        }
    });

var selectedID,selectedOrder;
$("#productionPlan tbody").on("click", "tr", function () {
    $('tr').removeClass('selected')
    $(this).addClass('selected');
    selectedID = oTable.row(this).data().id;
    selectedOrder = oTable.row(this).data().production_order;
    $.post('productionPlanAudit',{'production_order': selectedOrder}, function (res) {
        vm.auditorTableData = res;
    });
    $.post('getPrepareRecordAjax',{'production_order': selectedOrder}, function (res) {
        vm.materialTableData = res;
    });
    $.post('showStockAudit',{'production_order': selectedOrder}, function (res) {
        vm.inputStockTableData = res;
    })
});

</script>
</body>
</html>