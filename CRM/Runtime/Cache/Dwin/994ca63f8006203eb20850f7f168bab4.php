<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>111111</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdn.bootcss.com/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">
    <style type="text/css">
        body {
            color: black!important;
        }
        .selected{
            background-color: #2a83cf !important;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins" id="orders">
                <div class="ibox-title">
                    <h5>出库单出库记录</h5>
                    <div class="ibox-content">
                        <button class="btn btn-success btn-outline" type="button" id="record-audit-btn">出库记录审核</button>
                    </div>

                    <!--<div class="ibox-tools"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></div>
                    <div class="ibox-content">
                        <form class="form-inline">
                            <select class='form-control' name="orderT" id="orderT" style="width:10%; margin-right:0!important;">
                                <option value="3">所有订单</option>
                                <option value="2">待出库订单</option>
                                <option value="1">个人负责出库订单</option>
                            </select>
                            <select class='form-control' name="orderLimit" id="orderLimit" style="width:10%; margin-right:0!important;">
                                <option value="1">正常销货单</option>
                                <option value="2">借物发货销货单</option>
                            </select>
                            <button class="btn btn-success btn-outline" type="button" id="stock-out-btn">出库单录入</button>
                            <button class="btn btn-success btn-outline" type="button" id="market-out-btn">销售出库单录入</button>
                        </form>
                    </div>-->
                </div>

                <div class="ibox-content">
                    <div class="table-responsive1">
                        <table class="table table-bordered table-striped dataTables-orderTable">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="ibox-content" id="alertBox">
                    <el-dialog title="其他出库单审核：" class="selsctDialog" :visible.sync="dialogVisible" width="30%">
                        <el-button type="primary" @click="auditRecord(2)">合 格</el-button>
                        <el-button type="primary" @click="auditRecord(1)" style="margin-left: 54px">不合格</el-button>
                    </el-dialog>
                </div>

               <!-- <div class="ibox-content">
                    <div class="table-responsive1" id="detailsModel">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#audit" aria-controls="audit" role="tab" data-toggle="tab">审核日志</a></li>
                            <li role="presentation"><a href="#productionPlan" aria-controls="material" role="tab" data-toggle="tab">生产订单</a></li>
                            <li role="presentation"><a href="#productData" aria-controls="material" role="tab" data-toggle="tab">订单产品</a></li>
                            <li role="presentation"><a href="#stockOutLog" aria-controls="stock" role="tab" data-toggle="tab">出库记录</a></li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="audit">
                                <table class="table table-striped table-bordered table-hover table-full-width dataTables-productionList" id="audit_table" >
                                    <thead>
                                    <tr>
                                        <th>变更日期</th>
                                        <th>变更内容</th>
                                        <th>变更人</th>
                                        <th>订单ID</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="v in auditorTableData" >
                                        <td>{{v.change_time}}</td>
                                        <td>{{v.content}}</td>
                                        <td>{{v.name}}</td>
                                        <td>{{v.order_id}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="productionPlan">
                                <table class="table table-striped table-bordered table-hover table-full-width dataTables-productionList">
                                    <thead>
                                    <tr>
                                        <th>创建时间</th>
                                        <th>生产单号</th>
                                        <th>生产线</th>
                                        <th>物料型号</th>
                                        <th>生产数量</th>
                                        <th>备库方式</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="v in productionPlanTableData">
                                        <td>{{v.create_time}}</td>
                                        <td>{{v.production_order}}</td>
                                        <td>{{v.production_line_name}}</td>
                                        <td>{{v.product_name}}</td>
                                        <td>{{v.production_plan_number}}</td>
                                        <td>{{v.stock_cate_name}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="productData">
                                <table class="table table-striped table-bordered table-hover table-full-width dataTables-productData">
                                    <thead>
                                    <tr>
                                        <th>单号</th>
                                        <th>产品分类</th>
                                        <th>产品名</th>
                                        <th>应发数量</th>
                                        <th>已出库数量</th>
                                        <th>待审核出库数</th>
                                        <th>出库状态</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="v in productData">
                                        <td>{{v.product_id}}</td>
                                        <td>{{v.product_id}}</td>
                                        <td>{{v.product_name}}</td>
                                        <td>{{v.product_num}}</td>
                                        <td>{{v.stock_out_num}}</td>
                                        <td>{{v.stock_out_uncheck_num}}</td>
                                        <td><span v-if="v.pro_stock_status">{{v.pro_stock_status | productStockOutStatus}}</span></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="stockOutLog">
                                <table class="table table-striped table-bordered table-hover table-full-width dataTables-stockOutLog">
                                    <thead>
                                    <tr>
                                        <th>产品名</th>
                                        <th>出库单号</th>
                                        <th>出库仓库</th>
                                        <th>出库数量</th>
                                        <th>审核状态</th>
                                        <th>审核人</th>
                                        <th>出库人</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="v in stockOutLogData">
                                        <td>{{v.product_name}}</td>
                                        <td>{{v.audit_order_number}}</td>
                                        <td>{{v.warehouse_name}}</td>
                                        <td>{{v.num}}</td>
                                        <td><span v-if="v.audit_status">{{v.audit_status | stockStatus}}</span></td>
                                        <td>{{v.auditor_name}}</td>
                                        <td>{{v.proposer_name}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>-->
            </div>
        </div>
    </div>
</div>
</body>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/jeditable/jquery.jeditable.js"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/dwin/finance/common_finance.js"></script>
<script src="https://cdn.bootcss.com/element-ui/2.3.6/index.js"></script>

<script>
    var controller = "/Dwin/Stock";
    var orderTableDiv = $(".dataTables-orderTable");
    var orderTableTBodyDiv = $(".dataTables-orderTable tbody");
    var addOutStockLogBtn = $("#stock-out-btn");
    var addOtherFormStockBtn = $("#market-out-btn");
    var auditRecordBtn = $("#record-audit-btn");
    var oTable;
    var audit_status = <?php echo (json_encode($auditMap)); ?>;  //出库类型名称
    var selectedID = null;
    var auditStatus = null;

    var vm = new Vue({
        el: '#alertBox',
        data: function () {
            return {
                dialogVisible:false,
                bandname : 1,  // 在html中调用vm里面的数据需要以这种方式调用{{bandname}}}

        }
        },
        computed : {
        },
        methods : {
            // 可以将function写在这里
            auditRecord(status) {
                var vm = this
                var data = {
                    'id': selectedID,
                    'status':status
                }
                $.post('<?php echo U("Dwin/Stock/auditStockOut");?>', data , function (res) {
                    console.log(res)
                    if(res.status == 200){
                        oTable.ajax.reload()
                        vm.dialogVisible = false
                    }
                    layer.msg(res.msg)
                })
            }
        },
        filters : {
            // 支持 key=>value 的形式改变显示map的值
            /*stockStatus: function (status) {
                var arr = ['', '未审核', '审核通过', '审核不通过'];
                return arr[status]
            },
            productStockOutStatus : function (status) {
                var arr = ['未出库','出库中','出库完毕'];
                return arr[status];
            }*/
        }
    });
    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'none';
        oTable = orderTableDiv.DataTable({
            "paging"       : true,
            "autoWidth"    : false,
            "pagingType"   : "full_numbers",
            "lengthMenu"   : [5, 10, 20, 35, 50],
            "bDeferRender" : true,
            "processing"   : true,
            "searching"    : true, //是否开启搜索
            "serverSide"   : true,//开启服务器获取数据
            "searchable"   : false,
            "ajax"         : { // 获取数据
                "url"   : controller + "/stockRecordList",
                "type"  : 'post',
                'data'  : {
                    // 通过原生的js获取id中值
                    /*'pendingData' : function () {
                        return document.getElementById('orderT').value;
                    },
                    'orderLimit' : function () {
                        return document.getElementById('orderLimit').value;
                    }*/
                }
            },
            "columns" :[ //定义列数据来源
                {'title' : "出库单编号",   'data' : 'stock_no','class' : 'orderDetail'},
                {'title' : "物料编号",'data' : 'product_no'},
                {'title' : "物料名称",'data' : 'product_number'},
                {'title' : "出库数量",'data' : 'num'},
                {'title' : "库房库存剩余数量",'data' : 'stock_number'},
                {'title' : "库房锁库数量",'data' : 'o_audit'},
                {'title' : "库房待出库数量",'data' : 'out_processing'},
                {'title' : "审核状态", "data": 'status',render: function (data){return audit_status[+data]}},

                // {'title' : "业务员",     'data' : "staname"},
                // {'title' : "业务员电话",  'data' : "staff_phone"},
                // {'title' : "客户",       'data' : "cusname"},
                // {'title' : "财务审核状态",   'data' : 'check_status_name'},
                // {'title' : "出库状态",   'data' : "stock_out_status"},
                // {'title' : "快递方式",   'data' : "log_type"},
                // {'title' : "发货仓库",   'data' : "ware_house"},
                // {'title' : "是否分批出库", 'data' : "is_batch_delivery"},
                // {'title' : "生产物流备注", 'data' : "logistices_tip"}
                // // 自定义列   {'title':"负责人",'data':null,'class':"align-center"}
            ],
            "columnDefs"   : [ //自定义列
                /*{
                    "targets" : 7,
                    "data" : "status",
                    render : function(data, type, row) {
                        data = parseInt(data);
                        var arr = ['未处理', '出库中', '出库完毕','未对接订单,无需处理'];
                        var html = "";
                        switch (data) {
                            case 0 :
                                html = "<span style='color:red'>";
                                break;
                            case 1 :
                                html = "<span style='color:blue'>";
                                break;
                            case 2 :
                                html = "<span style='color:green'>";
                                break;
                            default:
                                html = "<span>";
                        }
                        html += arr[data] + "</span>";
                        return html;
                    }
                },*/
                /*{
                    "targets" : 10,
                    "data" : "is_batch_delivery",
                    render : function(data, type, row) {
                        data = parseInt(data);
                        var arr = ['不分批', '分批'];
                        var html = "";
                        switch (data) {
                            case 0 :
                                html = "<span style='color:red'>";
                                break;
                            case 1 :
                                html = "<span style='color:green'>";
                                break;
                            default:
                                html = "<span>";
                        }
                        html += arr[data] + "</span>";
                        return html;
                    }
                }*/
            ]
        });
    });

    // 当数据发生修改的时候，直接调用ajax 取最新数据
    /*$("#orderT").on('change', function () {
        oTable.ajax.reload();
    });

    $("#orderLimit").on('change', function () {
        oTable.ajax.reload();
    });*/

    // 点击审核按钮，弹出弹框
    auditRecordBtn.on('click', function (e) {
        if (selectedID) {
            if(audit_status > 0){
                vm.$message({
                    showClose: true,
                    message: '该项审核已通过,不能再次审核！！',
                    type: 'warning'
                });
            }else{
                vm.dialogVisible = true
            }
        }else {
            layer.alert('请选中待出库的订单');
        }
    });


    /* //判断点击某一行中的某一项，调用ajax请求
    orderTableTBodyDiv.on('click', 'td', function (e) {
        var index = $(this)[0]['cellIndex'];
        if (index == 0) {   // 依靠列的序号，第一列
            e.stopPropagation();
            var id = $(this).parent().attr('id');
            layer.open({
                type   : 2,
                title  : '销售单据',
                area   : ['100%', '100%'],
                content: "/Dwin/Customer/showInvoiceDetail/orderId/" + id //iframe的url
            })
        }
    });*/

    // 点击高亮
    // changeCss('orderTable', 1);
    /*addOutStockLogBtn.on('click',function () {
        selectedID = orderTableTBodyDiv.find('.selected').attr('id');
        console.log(selectedID);
        if (selectedID) {
            var index = layer.open({
                type: 2,
                title: '出库单录入',
                content: "addStockOut/orderId/" + selectedID,
                area: ['90%', '90%'],
                end: function () {
                    oTable.ajax.reload()
                }
            });
        }else {
            layer.alert('请选中待出库的订单');
        }
    });*/


    // 销售出库单录入 点击表格外的按钮获取表格中当前点击项的数据，然后调用ajax
    /*addOtherFormStockBtn.on('click',function () {
        selectedID = orderTableTBodyDiv.find('.selected').attr('id');
        if (selectedID) {
            var index = layer.open({
                type: 2,
                title: '出库单录入',
                content: "/Dwin/Stock/createStockOutOrderform?id=" + selectedID,
                area: ['90%', '90%'],
                end: function () {
                    oTable.ajax.reload()
                }
            });
        }else {
            layer.alert('请选中待出库的订单');
        }
    });*/


    // 创建一个vm 对象，包含当前页面中的弹框。
    /*var vm = new Vue({
        el: '#detailsModel',
        data: function () {
            return {
                auditorTableData : [],
                productionPlanTableData : [],
                productData : [],
                stockOutLogData : []
            }
        },computed : {
        },
        filters : {
            stockStatus: function (status) {
                var arr = ['', '未审核', '审核通过', '审核不通过'];
                return arr[status]
            },
            productStockOutStatus : function (status) {
                var arr = ['未出库','出库中','出库完毕'];
                return arr[status];
            }
        }
    });*/

    // 点击某一行，获取当前行的数据
    orderTableTBodyDiv.on( 'click', 'tr', function () {
        var recordData = oTable.row(this).data();
        selectedID = recordData.id;        // 入库记录主键
        auditStatus = recordData.status;   // 入库记录审核状态
        console.log(recordData)

        // 在选中行中添加selected 的class
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        }
        else {
            oTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
        // 调用接口，往当前接口传参，
        // $.post('showPendingOrderList',{'orderId': selectedOrder}, function (res) {
        //     vm.auditorTableData = res['orderRecordData'];
        //     vm.productionPlanTableData = res['productionPlanData'];
        //     vm.productData  = res['productData'];
        //     vm.stockOutLogData = res['stockOutData'];
        // });
    });



</script>
</html>