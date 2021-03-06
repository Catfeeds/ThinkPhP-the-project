<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存管理</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <!-- Data Tables -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.4/theme-chalk/index.css" rel="stylesheet">
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
        .span-set {
            font-weight: 400;
            font-size: small;
            text-align: center;
        }
        td {white-space: nowrap!important;}
        th {white-space: nowrap!important;}
        tr {white-space: nowrap!important;}
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
        [v-cloak] {
            display: none;
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
                    <button class="btn btn-xs btn-outline btn-success editButton"><span class="glyphicon glyphicon-align-justify"></span>修 改</button>
                    <button class="btn btn-xs btn-outline btn-success deleteButton"><span class="glyphicon glyphicon-remove"></span>删 除</button>
                    <button class="btn btn-xs btn-outline btn-success printButton"><span class="glyphicon glyphicon-print"></span>下载/打印</button>
                    <button class="btn btn-xs btn-outline btn-success checkButton" id="checkButton"><span class="glyphicon glyphicon-adjust" ></span>审核</button>
                    <select class="form-control chosen-select btn-outline ele-BUT push_down audit_type" name="userId" id="stockInType" style="width:7%;" tabindex="2">
                        <option value="3">--生产入库</option>
                        <option value="8">--采购入库</option>
                        <option value="5">--其他入库</option>
                    </select>

                    <div  class="ibox-content" style="margin-top: 15px;">
                        <table id="table" class="table table-striped table-bordered table-full-width" width="100%">
                            <thead>
                            <tr>
                                <th>入库类型</th>
                                <th>入库编号</th>
                                <th>入库批次</th>
                                <th>源单</th>
                                <th>制单人</th>
                                <th>制单时间</th>
                                <th>保管</th>
                                <th>验收</th>
                                <th>审核状态</th>
                                <th>备注</th>
                                <th>更新时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                    <div class="ibox-content">
                        <div v-cloak class="table-responsive1" id="detailsModel">
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#productData" aria-controls="productData" role="tab" data-toggle="tab">入库单情况</a></li>
                            </ul>
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="productData">
                                    <div v-if="productData.length">
                                        <div class="col-md-12">
                                            <el-form :inline="true" ref="form" label-width="80px">
                                                <el-form-item label="打印次数:">
                                                    <span class="span-info">{{baseData.print_time}}</span>
                                                </el-form-item>
                                                <el-form-item label="单据编号:">
                                                    <span class="span-info">{{baseData.stock_in_id}}</span>
                                                </el-form-item>
                                                <el-form-item label="单据类型:">
                                                    <el-input v-model="baseData.cate_name" readonly></el-input>
                                                </el-form-item>
                                                <el-form-item label="制单人:">
                                                    <el-input v-model="baseData.create_name" readonly></el-input>
                                                </el-form-item>
                                                <el-form-item label="保管人:">
                                                    <el-input v-model="baseData.keep_name" readonly></el-input>
                                                </el-form-item>
                                                <el-form-item label="验收人:">
                                                    <el-input v-model="baseData.check_name" readonly></el-input>
                                                </el-form-item>
                                                <el-form-item label="制单时间:">
                                                    <el-input v-model="baseData.c_time" readonly></el-input>
                                                </el-form-item>
                                                <el-form-item  v-if="baseData.production_line_name" label="生产线:">
                                                    <el-input v-model="baseData.production_line_name" readonly></el-input>
                                                </el-form-item>
                                                <el-form-item  v-if="baseData.production_group_name" label="生产班组:">
                                                    <el-input v-model="baseData.production_group_name" readonly></el-input>
                                                </el-form-item>
                                                <el-form-item  v-if="baseData.dept_name" label="部门:">
                                                    <el-input v-model="baseData.dept_name" readonly></el-input>
                                                </el-form-item>
                                                <el-form-item  v-if="baseData.supplier_name" label="供应商:">
                                                    <el-input v-model="baseData.supplier_name" readonly></el-input>
                                                </el-form-item>
                                                <el-form-item  v-if="baseData.stock_in_other_name" label="入库类型:">
                                                    <el-input v-model="baseData.stock_in_other_name" readonly></el-input>
                                                </el-form-item>
                                                <el-form-item  v-if="baseData.other_bill" label="对方单据:">
                                                    <el-input v-model="baseData.other_bill" readonly></el-input>
                                                </el-form-item>
                                                <el-form-item  label="备注:">
                                                    <el-input v-model="baseData.tips" readonly></el-input>
                                                </el-form-item>
                                            </el-form>
                                        </div>
                                        <table class="table table-striped table-bordered table-hover table-full-width dataTables-productData">
                                            <thead>
                                            <tr>
                                                <th>物料代码</th>
                                                <th>产品名称</th>
                                                <th>规格型号</th>
                                                <th>入库数量</th>
                                                <th>入库仓库</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="v in productData">
                                                <td>{{v.product_no}}</td>
                                                <td>{{v.product_number}}</td>
                                                <td>{{v.product_name}}</td>
                                                <td>{{v.num}}</td>
                                                <td>{{v.repertory_name}}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                            <el-form ref="form" label-width="80px">
                                                <el-form-item  label="备注:">
                                                    <span v-html="baseData.tips"></span>
                                                </el-form-item>
                                            </el-form>

                                    </div>
                                    <div v-else class="col-xs-12 span-set">
                                        <span class="selected-highlight">请点击一行后查看出库单详情</span>
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
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/js/dataTables.bootstrap.min.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.4/index.js"></script>
<script>
    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'throw';
        var Height = document.body.clientHeight;

        var table = $('#table').DataTable({
            "scrollY": Height/3.3,
             "scrollX": true,
            "scrollCollapse": true,
            "destroy"      : true,
            "paging"       : true,
            "autoWidth"	   : false,
            "pagingType"   : "full_numbers",
            "lengthMenu"   : [10, 25, 50, 100],
            "bDeferRender" : true,
            "processing"   : true,
            "searching"    : true, //是否开启搜索
            "serverSide"   : true, //开启服务器获取数据
            ajax: {
                type: 'post',
                data: {
                    type : function () {
                        return document.getElementById('stockInType').value;
                    }
                }
            },
            order: [[1, 'desc']],
            columns: [
                {data: 'cate_name', searchable: true},
                {data: 'stock_in_id', searchable: true},
                {data: 'batch',        searchable: false},
                {data: 's_id',       searchable: true},
                {data: 'create_name',   searchable: false},
                {data: 'c_time',                searchable: false},
                {data: 'keep_name',          searchable: false},
                {data: 'check_name',               searchable: false},
                {data: 'audit_status',       searchable: false},
                {data: 'tips',         searchable: false},
                {data: 'update_time',         searchable: false}
            ],
            "columnDefs"   : [ //自定义列
                {
                    "targets" : 8,
                    "data" : "audit_status",
                    "render" : function(data, type, row) {
                        var arr = ['', '未审核', '待审', '完成'];
                        return arr[data];
                    }
                },{
                    "targets" : 9,
                    "data"    : "tips",
                    "render"  : function(data, type, row) {
                        if (data){
                            data =  data.replace(/\r\n|\n/g, '<br>');
                            var allData = table.data();
                            var index = allData.indexOf(row);
                            var className = 'tips' + index;
                            var str = '';
                            if (data.length > 10){
                                str = data.slice(0, 10) + '...'
                            } else {
                                str = data
                            }
                            str = str.replace(/\r\n|\n/g, '<br>');
                            return "<span class='tips' id='" + className + "'>" + str + "</span>"
                        }
                        return data
                    }
                }
            ],"oLanguage": {
                "sProcessing":   "处理中...",
                "sLengthMenu":   "显示 _MENU_ 项结果",
                "sZeroRecords":  "没有匹配结果",
                "sInfo":         "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                "sInfoEmpty":    "显示第 0 至 0 项结果，共 0 项",
                "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
                "sInfoPostFix":  "",
                "sSearch":       "搜索:",
                "sUrl":          "",
                "sEmptyTable":     "表中数据为空",
                "sLoadingRecords": "载入中...",
                "sInfoThousands":  ",",
                "oPaginate": {
                    "sFirst":    "首页",
                    "sPrevious": "上页",
                    "sNext":     "下页",
                    "sLast":     "末页"
                },
                "oAria": {
                    "sSortAscending":  ": 以升序排列此列",
                    "sSortDescending": ": 以降序排列此列"
                }
            }
        });
        $('#table').on('mouseenter', 'span', function () {
            if ($(this).hasClass('tips'))
                var id = $(this).attr('id')
            var data = table.cell($(this).parents('td')).data()
            layer.tips(data, '#' + id, {time: 9999999})
        })
        $('#table').on('mouseleave', 'td', function () {
            layer.closeAll();
        })
        $('.audit_type').on('change', function () {
            table.ajax.reload();
        });
        var vm = new Vue({
            el: '#detailsModel',
            data: function () {
                return {
                    productData : [],
                    baseData : []
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
        $('table').on('processing.dt', function () {
            $('tr').removeClass('selected-highlight')
                vm.productData = []
                vm.baseData = []
        })
        var selectedOrder;
        var tableTBody = $("#table tbody");
        var multiData = [];
        $('.checkButton').click( function () {
            multiData = [];
            var selectRows = table.rows('.selected-highlight').data().length;
            if (selectRows) {
                for (var p = 0; p < selectRows; p++) {
                    multiData[p] = table.rows('.selected-highlight').data()[p];
                }
                var clickTime = 0;
                var that = this
                layer.confirm('选中了' + selectRows + '行数据,是否已核查内容有效，批量审核？',
                    {
                        btn  : ['通过', '取消'],
                        icon : 6,
                        end : function () {
                            table.ajax.reload(false, null);
                        }
                    },function () {
                        $(that).attr('disabled', true);
                        clickTime += 1;
                        if(clickTime === 1) {
                            $.ajax({
                                url  : 'checkStockInRecord',
                                type : 'post',
                                data : {
                                    status : 1,
                                    type : document.getElementById('stockInType').value,
                                    params : multiData
                                },
                                success : function (ajaxData) {
                                    $(that).attr('disabled', false);
                                    layer.msg(ajaxData['msg']);
                                    vm.productData = [];
                                    vm.baseData = [];
                                }
                            });
                        }
                    });
            } else {
                layer.msg("您没有选中要审核的入库单据");
            }
        });
        tableTBody.on('click', 'tr', function () {
            $('tr').removeClass('selected-highlight')
            $(this).addClass('selected-highlight');
            var data = table.row( this ).data();
            obj.selectedRowData = data;
            selectedOrder = data.id;
            $.post('getStockInDetail',{'id': selectedOrder}, function (res) {
                vm.productData     = res.data;
                vm.baseData = data;
            });
        });

        var obj = {
            selectedRowData :[],
            deleteData: function (id,typeId) {
                if (id == undefined && !id) {
                    layer.msg('未选中单据');
                    return false;
                }
                var clickTime = 0;
                layer.confirm("确认删除该单据？",{
                    btn  : ['确认', '取消'],
                    icon : 6,
                    end : function () {
                        table.ajax.reload(false, null);
                    }
                }, function () {
                    $(that).attr('disabled', true);
                    clickTime += 1;
                    if(clickTime === 1) {
                        $.ajax({
                            url  : 'delStockInRecord',
                            type : 'post',
                            data : {
                                recordId : id,
                                recordType: typeId
                            },
                            success : function (ajaxData) {
                                $(that).attr('disabled', false);
                                layer.msg(ajaxData['msg']);
                                vm.productData = [];
                                vm.baseData = [];
                            }
                        });
                    } else {
                        layer.msg('禁止重复提交表单');
                    }
                });
            },
            printData: function (id, typeId) {
                if (id == undefined && !id) {
                    layer.msg('未选中单据');
                    return false;
                }
                var index = layer.confirm("确认打印该单据？",{
                    btn  : ['确认', '取消'],
                    icon : 6
                }, function () {
                    layer.open({
                        type: 2,
                        title: '单据打印',
                        shadeClose: true,
                        end: function () {
                            table.ajax.reload(false, null);
                        },
                        area: ['220mm', '110mm'],
                        content: 'printInHtml?recordId=' + id + "&recordType=" + typeId //iframe的url
                    });
                    layer.close(index);
                });
            }
        }
        $(".deleteButton").click( function () {
            if (!obj.selectedRowData)
                layer.msg('未选择入库单据', function () {return false;});
            obj.deleteData(obj.selectedRowData.id, obj.selectedRowData.cate);
        });
        $(".printButton").click( function () {
            if (!obj.selectedRowData)
                layer.msg('未选择入库单据', function () {return false;});
            obj.printData(obj.selectedRowData.id, obj.selectedRowData.cate);
        });

    });



</script>
</body>
</html>