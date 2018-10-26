<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出入库记录</title>
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
                    <h5>产品出入库登记</h5>
                </div>
                <div class="ibox-content">
                    <form class="form-inline">
                        <div class="col-xs-1">
                            <button type="button" class="btn btn-outline btn-sm btn-success o_stock"><span class="glyphicon glyphicon-log-out"></span>出库</button>
                        </div>
                        <div class="col-xs-1">
                            <button type="button" class="btn btn-outline  btn-info btn-sm audit_btn" id="checkButton"><span class="glyphicon glyphicon-grain"></span>审核</button>
                        </div>
                        <div class="col-xs-1">
                            <button type="button" class="btn btn-outline  btn-warning btn-sm" id="del">删除</button>
                        </div>
                        <div class="col-xs-3">
                            <label for="warehouse">库房筛选</label>
                            <select name="" id="warehouse" class="form-control audit_type">
                                <option value="">所有</option>
                                <?php if(is_array($repoData)): $i = 0; $__LIST__ = $repoData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["rep_id"]); ?>"><?php echo ($vol["repertory_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                        <div class="col-xs-3">
                            <label for="audit-type">审核状态筛选</label>
                            <select name="" id="audit-type" class="form-control audit_type">
                                <option value="">所有</option>
                                <option value="1">未审核</option>
                                <option value="2">审核通过</option>
                                <option value="3">审核驳回</option>
                            </select>
                        </div>
                    </form>

                    <div class="ibox-content" style="margin-top: 15px;">
                        <table id="table" class="table table-striped table-bordered table-full-width" width="100%">
                            <thead>
                            <tr>
                                <th>单据编号</th>
                                <th>物料型号</th>
                                <th>数量</th>
                                <th>库房</th>
                                <th>出库类型</th>
                                <th>销货单号</th>
                                <th>快递单号</th>
                                <th>出库备注</th>
                                <th>审核标志</th>
                                <th>制单人</th>
                                <th>审核人</th>
                                <th>审核备注</th>
                                <th>更新时间</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
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
                                        <th>快递单号</th>
                                        <th>出库人</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="v in stockOutLogData">
                                        <td>{{v.audit_order_number}}</td>
                                        <td><span v-if="v.audit_status">{{v.audit_status | stockOutStatus}}</span></td>
                                        <td>{{v.update_time}}</td>
                                        <td>{{v.product_name}}</td>
                                        <td>{{v.warehouse_name}}</td>
                                        <td>{{v.num}}</td>
                                        <td>{{v.action_order_number}}</td>
                                        <td>{{v.express_number}}</td>
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
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    var table
    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'throw'
        table = $('#table').DataTable({
            serverSide: true,
            ajax: {
                type: 'post',
                data: {
                    status : function () {
                        return document.getElementById('audit-type').value;
                    },
                    repertory_id : function () {
                        return document.getElementById('warehouse').value;
                    }
                }
            },
            order: [[12, 'desc']],
            columns: [
                {data: 'audit_order_number', searchable: true},
                {data: 'product_name', searchable: true},
                {data: 'num', searchable: false},
                {data: 'warehouse_name', searchable: false},
                {data: 'cate_name', searchable: false},
                {data: 'action_order_number', searchable: true},
                {data: 'express_number', searchable: true},
                {data: 'tips', searchable: false},
                {data: 'audit_status', searchable: false},
                {data: 'proposer_name', searchable: false},
                {data: 'auditor_name', searchable: false},
                {data: 'audit_tips', searchable: false},
                {data: 'update_time', searchable: false}
            ],
            "columnDefs"   : [ //自定义列
                {
                    "targets" : 8,
                    "data" : "audit_status",
                    "render" : function(data, type, row) {
                        var arr = ['', '未审核', '审核通过', '审核不通过'];
                        return arr[data];
                    }
                },
                {
                    "targets" : 11,
                    "data"    : "audit_tips",
                    "render"  : function(data, type, row) {
                        if (row.audit_status == 1) {
                            return '<input class="form-control tips">'
                        }
                        return data
                    }
                }]
        });
        $('.audit_type').on('change',function () {
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
        var productName,id;
        $('.o_stock').on('click', function () {
            layer.open({
                type: 2,
                title: "请选择要出库的产品",
                area : ['100%', '100%'],
                content: "<?php echo U('production/chooseProduct');?>",
                btn: ['确定', '取消'],
                yes: function (index) {
                    openOStock(productInfo);
                    layer.close(index)
                }
            });
        });
    });

    function openOStock(productInfo) {
        layer.open({
            type: 2,
            title: "",
            area : ['80%', '80%'],
            content: "/Dwin/Stock/addStockOutWithoutOrder?product_name="+productInfo.product_name+'&productId='+productInfo.product_id+'&type='+2,
            end: function () {
                table.ajax.reload();
            }
        });
    }

    $('#del').on('click', function () {
        multiData = [];
        var selectRows = table.rows('.selected-highlight').data().length;
        if (selectRows) {
            for (var p = 0; p < selectRows; p++) {
                multiData[p] = table.rows('.selected-highlight').data()[p];
            }
            var clickTime = 0;
            var that = this
            layer.confirm('选中了' + selectRows + '行数据,是否删除?',
                function () {
                    $(that).attr('disabled', true)
                    clickTime += 1;
                    if(clickTime === 1) {
                        $.ajax({
                            url  : 'delStockOutItem',
                            type : 'post',
                            data : {
                                data : multiData
                            },
                            success : function (res) {
                                $(that).attr('disabled', false)
                                if (res.status > 0){
                                    table.ajax.reload();
                                }
                                layer.msg(res.msg)
                            }
                        });
                    }
                })
        } else {
            layer.msg("您没有选中要删除的入库单据");
        }
    });

</script>
</body>
</html>