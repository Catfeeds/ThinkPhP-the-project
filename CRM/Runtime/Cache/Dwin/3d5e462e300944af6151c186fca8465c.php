<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM--生产计划列表</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
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
        tr{
            white-space: nowrap!important;
        }

        .btn{
            margin-right: 1em;
        }
        .prepare{
            display: none;
        }
        .ibox{
            padding:20px;
        }
        .delayComplain{
            display: none;
        }
        .dataTables_scrollHeadInner{
            width: 100%!important;
        }
        .dataTables_scrollHeadInner table{
            width: 100%!important;
        }
        #productionPlan{
            width: 100% !important;
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
                    <h3>生产计划列表</h3>
                    <form class="form-inline">
                        <button type="button" class="btn btn-info btn-sm refresh">刷新</button>
                        <button href="addProductionPlan.html" type="button" class="btn btn-info btn-sm addPlan">添加生产计划</button>
                        <button type="button" class="btn btn-info btn-sm editPlan">修改生产计划</button>
                        <button type="button" class="btn btn-info btn-sm audit_btn">审核</button>
                        <button type="button" class="btn btn-info btn-sm prepare">齐料登记</button>
                        <button type="button" class="btn btn-info btn-sm del">删除</button>
                        <!--<button type="button" class="btn btn-info btn-sm i_stock" style="display: none;">入库</button>-->
                        <button type="button" class="btn btn-info btn-sm export">导出</button>
                        <button type="button" class="btn btn-info btn-sm delayComplain">投诉</button>
                        <label for="production_status">状态筛选</label>
                        <select class="form-control change-data" name="status" id="production_status">
                            <option value="">所有</option>
                            <option value="1">待审核</option>
                            <option value="2">齐料确认中</option>
                            <option value="4">待产线确认</option>
                            <option value="3">生产中</option>
                        </select>
                        <label for="production_line">产线筛选</label>
                        <select name="" id="production_line" class="form-control change-data">
                            <option value="">所有</option>
                            <?php if(is_array($line)): $i = 0; $__LIST__ = $line;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["production_line"]); ?>"><?php echo ($vol["production_line"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </form>

                    <div class="table-responsive1">
                        <table class="table table-striped table-bordered table-hover table-full-width dataTables-productionList" id="productionPlan">
                            <thead>
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
                                    <tr v-for="v in auditorTableData" >
                                        <td>{{v.production_order}}</td>
                                        <td>{{v.audit_type_name}}</td>
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
                                        <th>备注</th>
                                        <th>库房</th>
                                        <th>添加人</th>
                                        <th>更新时间</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="v in materialTableData">
                                        <td>{{v.production_order}}</td>
                                        <td>{{v.product_name}}</td>
                                        <td>{{v.num}}</td>
                                        <td>{{v.tips}}</td>
                                        <td>{{v.warehouse_name}}</td>
                                        <td>{{v.manager_name}}</td>
                                        <td>{{v.create_time}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="stock">
                                <table class="table table-striped table-bordered table-hover table-full-width dataTables-productionList">
                                    <thead>
                                    <tr>
                                        <th>入库日期</th>
                                        <th>入库单号</th>
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
                                    <tr v-for="v in inputStockTableData">
                                        <td>{{v.create_time}}</td>
                                        <td>{{v.audit_order_number}}</td>
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
    var oTable;
    var controller = "/Dwin/Production";
    var tableDiv = $("#productionPlan");
    var addPlanBtn = $('.addPlan');
    var selectedID,selectedOrder,production_status;
    addPlanBtn.on('click',function () {
        var index = layer.confirm('是否选择关联销货单?', {
            btn: ['关联', '不关联，直接下单'],
            // 有销货单回调
            btn1: function () {
                layer.open({
                    type: 2,
                    title: '请输入销货单',
                    content: "<?php echo U('inputSaleOrder');?>",
                    area: ['50%', '50%'],
                    end: function () {
                        var index = layer.open({
                            type: 2,
                            title: '添加生产计划',
                            content: "addProductionPlanByOrder?order_number=" + saleOrder,
                            area: ['100%', '100%'],
                            end: function () {
                                oTable.ajax.reload();
                            }
                        })
                    }
                });
                layer.close(index);
            },
            // 无销货单回调
            btn2: function () {
                var index = layer.open({
                    type: 2,
                    title: '添加生产计划',
                    content: "<?php echo U('addProductionPlan');?>",
                    area: ['80%', '80%'],
                    end: function () {
                        oTable.ajax.reload();
                    }
                })
            }
        })
    });


    $(document).ready(function() {
        tableDiv.on('mouseenter','tbody td', function () {
            var tdIndex = $(this).parent()['context']['cellIndex'];
            if (tdIndex === 11) {
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
        tableDiv.delegate('tbody td', 'mouseleave',function(e) {
            layer.closeAll('tips');
        });

    oTable = tableDiv.DataTable({
        "scrollY": 400,
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
        "ajax"         :{ // 获取数据
            "url"   : controller + "/productionPlan",
            "type"  : 'post',
            "data"  : {
                "production_status": function () {
                    return document.getElementById('production_status').value;
                },
                "production_line" : function () {
                    return document.getElementById('production_line').value;
                }
            }
        },
        "order": [[ 7, "desc" ]],
        "columns": [
            {data:'production_order', title:'生产单号'},
            {data:'staff_name'      , title:'业务员'},
            {data:'stock_cate_name' , title:'备货'},
            {data:'product_name'    , title:'型号'},
            {data:'production_line_name', title:'生产线'},
            {data:'production_plan_number', title:'生产数量'},
            {data:'production_number', title:'入库数量'},
            {data:'create_time', title:'下单日期'},
            {data:'delivery_time', title:'交期要求'},
            {data:'yqts', title:'延期天数'},
            {data:'production_status', title:'状态'},
            {data:'tips', title:'特殊要求'},
            {data:'stock_number', title:'剩余库存'},
            {data:'production_plan_rest_num', title:'生产中'}
        ],
        "columnDefs": [ //自定义列
            {
                "targets": 10,
                "data": 'production_status',
                "render": function (data, type, row) {
                    var arr = ['', '待审核', '齐料确认中', '生产中','待产线确认'];
                    return arr[data];
                }
            },
            {
                "targets": 11,
                "data": 'tips',
                "render": function (data, type, row) {
                    if (row.tips.length > 14) {
                        var html = "<span data='" + row.tips + "'>" + row.tips.substring(0,14) + "...</span>";
                    } else {
                        var html = "<span>" + row.tips + "</span>";
                    }
                    return html;
                }
            }
        ],
        'fnRowCallback':function(nRow,aData,iDisplayIndex,iDisplayIndexFull){
            /*
                nRow:每一行的信息 tr  是Object
                aData：行 index
            */
            for(let key in nRow){
                var AD_ad = nRow['childNodes'][nRow['childNodes'].length - 4]
                if(AD_ad.innerText == '待审核'){
                    $(AD_ad).css('color','blue')
                }else if(AD_ad.innerText == '齐料确认中'){
                    $(AD_ad).css('color','red')
                }else if(AD_ad.innerText == '生产中'){
                    $(AD_ad).css('color','#CC66CC')
                }else if(AD_ad.innerText == '待产线确认'){
                    $(AD_ad).css('color','green')
                }
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
    });
    $(".change-data").on('change', function () {
        oTable.ajax.reload();
    });
    $('.refresh').on('click', function () {
        oTable.ajax.reload();
    });
    $('.editPlan').on('click',function () {
        if (selectedID) {
            var index = layer.open({
                type: 2,
                title: '修改生产计划',
                content: "editProductionPlan/id/" + selectedID,
                area: ['100%', '100%'],
                end :function () {
                    oTable.ajax.reload();
                }
            })
        }else {
            layer.alert('请选中一个生产计划')
        }
    });

    $('.audit_btn').on('click',function () {
        var lock = false;
        if (selectedID) {
            var index = layer.prompt({
                title: '审核备注，填写后确认',
                end:function (){
                    oTable.ajax.reload();
                },
                formType: 2,value: '审核备注：'
            }, function(text, index){
                if (lock === false) {
                    lock = true;
                    layer.close(index);
                    $.ajax({
                        type: 'post',
                        url : "/Dwin/Production/editProductionPlanAudit/id/" + selectedID,
                        data: {
                            'production_order': selectedOrder,
                            'tips': text,
                            'audit_result': 1
                        },
                        success: function (data) {
                            if (data.status > 0) {
                                layer.msg(data.msg, {
                                    icon: 1
                                }, function (index) {
                                    layer.close(index);
                                    index = parent.layer.getFrameIndex(window.name);
                                    parent.layer.close(index)
                                })
                            } else {
                                layer.msg(data.msg, {
                                    icon: 1
                                })
                            }
                        }
                    });
                }

            });
        }else {
            layer.alert('请选中一个生产计划')
        }
    });

    $('.del').on('click',function () {
        var lock = false;
        if (selectedID) {
            if (lock === false) {
                lock = true;
                if (production_status !== 3) {
                    layer.confirm('是否确认删除?', function (index) {
                        $.post('delProductionPlan', {id: selectedID}, function (res) {
                            layer.msg(res.msg);
                            oTable.ajax.reload();
                        })
                    })
                }else {
                    layer.alert('该订单正在生产,不可删除')
                }
            }

        }else {
            layer.alert('请选中一个生产计划')
        }
    });

    $('.i_stock').on('click',function () {
        if (selectedID) {
            var index = layer.open({
                type: 2,
                title: '生产入库',
                content: "addPutinAudit/planId/" + selectedID,
                area: ['70%', '70%'],
                end :function () {
                    oTable.ajax.reload();
                }
            })
        }else {
            layer.alert('请选中一个生产计划')
        }
    });

    $('.prepare').on('click',function () {
        if (selectedID) {
            var index = layer.open({
                type: 2,
                title: '齐料登记',
                content: "editPrepareRecord/production_order_number/" + selectedOrder,
                area: ['70%', '70%'],
                end :function () {

                }
            })
        } else {
            layer.alert('请选中一个生产计划')
        }
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


    $("#productionPlan tbody").on("click", "tr", function () {
        $('.delayComplain').show();
        production_status = oTable.row(this).data().production_status;
        switch (production_status) {
            case 1 :
                $(".audit_btn").val("单据审核");
                break;
                case 2 :
                $(".audit_btn").val("齐料确认");
                break;
                case 4 :
                $(".audit_btn").val("产线确认");
                break;
            default:
                break;
        }
        if (production_status == 3){
            $('.i_stock').show();
            $('.audit_btn').hide();
        }else {
            $('.audit_btn').show();
            $('.i_stock').hide();
        }
        if (production_status != 1 && production_status != 5){
            $('.prepare').show()
        } else {
            $('.prepare').hide()
        }
        $('tr').removeClass('selected')
        $(this).addClass('selected');
        selectedID = production_status = oTable.row(this).data().id;
        selectedOrder = production_status = oTable.row(this).data().production_order;
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

    $('.export').on('click', function () {
        var index = layer.load('正在生成xlsx文件');
        $.post('getProductionPlanExcel',{}, function (res) {
            layer.close(index);
            if (res.status == -1) {
                layer.msg(res.msg);
            } else {
                console.log(res.data);
                window.open(res.data);
            }
        })
    })

    // 当dataTables变动时取消选中
    $('table').on('processing.dt', function () {
        selectedID = undefined;
        $('tr').removeClass('selected')
    })

    $('.delayComplain').on('click', function () {
        if (selectedOrder) {
            var index = layer.open({
                type: 2,
                title: '投诉',
                shadeClose:true,
                content: '<?php echo U("Office/postProductionDelayComplain","","");?>' + '/$productionOrder/' + selectedOrder,
                area: ['50%', '90%'],
                end: function () {
                    table.ajax.reload()
                }
            })
        }else {
            layer.msg('请先选择一行')
        }
    })

</script>
</body>
</html>