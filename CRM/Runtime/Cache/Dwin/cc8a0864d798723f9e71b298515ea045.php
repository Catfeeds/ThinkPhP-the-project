<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存管理</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;
        }

        .hiddenDiv {
            display: none;
        }
        .selected-highlight {
            color:red;
            background-color: #FFFFCC !important;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>出入库审核管理</h5>
                </div>
                <div class="ibox-content">
                    <form class="form-inline">
                        <div class="col-xs-2">
                            <label for="warehouse">库房</label>
                            <select name="" id="warehouse" class="form-control audit_type">
                                <option value="">所有</option>
                                <?php if(is_array($repertoryList)): $i = 0; $__LIST__ = $repertoryList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["rep_id"]); ?>"><?php echo ($vol["repertory_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                        <div class="col-xs-2">
                            <button class="btn btn-sm btn-outline btn-success" type="button" id="checkButton">审核</button>
                        </div>
                    </form>
                    <div class="ibox-content" style="margin-top: 15px;">
                        <table id="table" class="table table-striped table-bordered table-full-width" width="100%">
                            <thead>
                            <tr>
                                <th>出库编号</th>
                                <th>订单编号</th>
                                <th>产品型号</th>
                                <th>库房</th>
                                <th>出库数量</th>
                                <th>出库类型</th>
                                <th>出库备注</th>
                                <th>状态</th>
                                <th>审核备注</th>
                                <th>更新时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="ibox-content">
                        <div class="table-responsive1" id="detailsModel">
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#productData" aria-controls="productData" role="tab" data-toggle="tab">订单产品信息</a></li>
                                <li role="presentation"><a href="#stockOutLog" aria-controls="stock" role="tab" data-toggle="tab">出库记录</a></li>
                            </ul>
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="productData">
                                    <table class="table table-striped table-bordered table-hover table-full-width dataTables-productData">
                                        <thead>
                                        <tr>
                                            <th>单号</th>
                                            <th>产品名</th>
                                            <th>订购数量</th>
                                            <th>出库待审数</th>
                                            <th>已出库数</th>
                                            <th>剩余未出库</th>
                                            <th>出库状态</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="v in productData">
                                            <td>{{v.order_id}}</td>
                                            <td>{{v.product_name}}</td>
                                            <td>{{v.product_num}}</td>
                                            <td>{{v.stock_out_uncheck_num}}</td>
                                            <td>{{v.stock_out_num}}</td>
                                            <td>{{v.stock_un_num}}</td>
                                            <td>{{v.stock_status}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="stockOutLog">
                                    <table class="table table-striped table-bordered table-hover table-full-width dataTables-stockOutLog">
                                        <thead>
                                        <tr>
                                            <th>出库单号</th>
                                            <th>审核状态</th>
                                            <th>更新时间</th>
                                            <th>产品名</th>
                                            <th>出库仓库</th>
                                            <th>出库数量</th>
                                            <th>订单号</th>
                                            <th>审核人</th>
                                            <th>出库人</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="v in stockOutLogData">
                                            <td>{{v.action_order_number}}</td>
                                            <td><span v-if="v.audit_status">{{v.audit_status | stockOutStatus}}</span></td>
                                            <td>{{v.update_time}}</td>
                                            <td>{{v.product_name}}</td>
                                            <td>{{v.audit_order_number}}</td>
                                            <td>{{v.warehouse_name}}</td>
                                            <td>{{v.num}}</td>
                                            <td>{{v.auditor_name}}</td>
                                            <td>{{v.proposer_name}}</td>
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
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'throw'
    });
    var table = $('#table').DataTable({
        serverSide: true,
        ajax: {
            type: 'post',
            data: {
                repertory_id : function () {
                    return document.getElementById('warehouse').value;
                }
            }
        },
        order: [[1, 'desc']],
        columns: [
            {data: 'audit_order_number', searchable: true},
            {data: 'action_order_number', searchable: true},
            {data: 'product_name',       searchable: true},
            {data: 'warehouse_name',   searchable: false},
            {data: 'num',                searchable: false},
            {data: 'cate_name',          searchable: false},
            {data: 'tips',               searchable: false},
            {data: 'audit_status',       searchable: false},
            {data: 'audit_tips',         searchable: false},
            {data: 'update_time',        searchable: false}
        ],
        "columnDefs"   : [ //自定义列
            {
                "targets" : 7,
                "data" : "audit_status",
                "render" : function(data, type, row) {
                    var arr = ['', '未审核', '审核通过', '审核不通过'];
                    return arr[data];
                }
            },
            {
                "targets" : 8,
                "data"    : "audit_tips",
                "render"  : function(data, type, row) {
                    if (row.audit_status == 1) {
                        return '<input class="form-control tips">'
                    }
                    return data
                }
            }
        ]
    });
    $('.audit_type').on('change', function () {
        table.ajax.reload();
    });
    var vm = new Vue({
        el: '#detailsModel',
        data: function () {
            return {
                productData : [],
                stockOutLogData : []
            }
        },
        filters : {
        stockStatus: function (status) {
            var arr = ['未审核', '审核通过', '审核不通过'];
            return arr[status]
        },
        batchDeliveryStatus : function (status) {
            var arr = ['不分批','分批'];
            return arr[status];
        },
        stockOutStatus : function (status) {
            var arr = ['', '未审核', '审核通过','审核不通过'];
            return arr[status];
        }
    }
    });
    var tableTBody = $("#table tbody");
    var selectedOrder;
    tableTBody.on('click',"input", function (e) {
        e.stopPropagation();
    });
    var multiData = [];
    $('#checkButton').click( function () {
        multiData = [];
        $("#table tbody tr").each(function () {
            table.row(this).data().audit_tips = $(this).find('input').val();
        });
        var selectRows = table.rows('.selected-highlight').data().length;
        if (selectRows) {
            for (var p = 0; p < selectRows; p++) {
                multiData[p] = table.rows('.selected-highlight').data()[p];
            }
            var clickTime = 0;
            var that = this
            layer.confirm('选中了' + selectRows + '行数据,是否已核查内容有效，批量审核？',
                {
                    btn  : ['通过', '驳回'],
                    icon : 6,
                    end : function () {
                        table.ajax.reload();
                    }
                },function () {
                    $(that).attr('disabled', true)
                    clickTime += 1;
                    if(clickTime === 1) {
                        $.ajax({
                            url  : 'checkStockOutAudit',
                            type : 'post',
                            data : {
                                auditFlag : 1,
                                auditData : multiData
                            },
                            success : function (ajaxData) {
                                $(that).attr('disabled', false)
                                layer.msg(ajaxData['msg']);
                            }
                        });
                    }
                }, function () {
                    $(that).attr('disabled', true)
                    clickTime += 1;
                    if(clickTime === 1) {
                        $.ajax({
                            url  : 'checkStockOutAudit',
                            type : 'post',
                            data : {
                                auditFlag : 2,
                                auditData : multiData
                            },
                            success : function (data) {
                                $(that).attr('disabled', false)
                                layer.msg(data['msg']);
                            }
                        });
                    }
                });
        } else {
            layer.msg("您没有选中要审核的入库单据");
        }
    });
    tableTBody.on('click', 'tr', function () {
        $(this).toggleClass('selected-highlight');

        selectedOrder = $(this).attr('id');
        $.post('showStockOutAuditList',{'orderId': selectedOrder}, function (res) {
            vm.productData     = res['productData'];
            vm.stockOutLogData = res['stockOutData'];
        });
    });
    // 通过审核
    tableTBody.on("click", ".audit_pass", function () {
        var id = table.row($(this).parents('tr')).data().id;
        var that = this
        $(this).attr('disabled', true)
        $.ajax({
            url:'editStockAuditStatus',
            type:'post',
            data:{
                auditID: id,
                audit_tips: $(this).parents('tr').children('td').children('.tips').val(),
                audit_status: 2
            },
            success:function (data) {
                $(that).attr('disabled', false)
                layer.msg(data.msg, {icon: 1, time: 1500, shade: 0.1}, function (index) {
                    layer.close(index);
                    table.ajax.reload();
                });
                return false;
            }
        })
    });

    //未通过审核
    tableTBody.on("click", ".audit_fail", function () {
        var id = table.row($(this).parents('tr')).data().id;
        var that = this
        $(this).attr('disabled', true)
        $.ajax({
            url:'editStockAuditStatus',
            type:'post',
            data:{
                auditID: id,
                audit_tips: $(this).parents('tr').children('td').children('.tips').val(),
                audit_status: 3
            },
            success:function (data) {
                $(that).attr('disabled', false)
                layer.msg(data.msg, {icon: 1, time: 1500, shade: 0.1}, function (index) {
                    layer.close(index);
                    table.ajax.reload();
                });
                return false;
            }
        })
    })

</script>
</body>
</html>