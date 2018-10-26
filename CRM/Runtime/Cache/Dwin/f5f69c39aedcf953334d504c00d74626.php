<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客户列表-数据表格</title>
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

        tbody td {
            cursor: pointer;
        }

        .cus-24 {
            color: red;
        }

        .selected {
            background-color: yellow !important;
        }

        .businessBtn {
            width: 10%;
            text-align: center;
        }

        .chosen-customer-type {
            color: blue;
        }

        .ibox-title {
            padding-top: 7px;
        }

        .chosen-select {
            width: 100%;
        }

        .wenzi {
            color: red;
        }

        .kpi {
            color: #2804f7;
            position: relative;
        }

        .kpi span:after {
            position: absolute;
            top: -16px;
            left: -7px;
            color: red;
            content: 'KPI';
            border: 1px solid red;
            border-radius: 5px;
            padding: 0 3px;
            font-size: 12px;
        }
    </style>
</head>
<body class="gray-bg"><!--
<button type="button" class="btn btn-default refresh-btn" aria-label="Left Align">
    <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
</button>-->
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <div class="fa-hover col-sm-4 col-sm-2">
                        <select class='chosen-select  chosen-customer-type' id="cus-type-selecter">
                            <option value="cus-1">个人客户</option>
                            <option value="cus-2">下属客户</option>
                        </select>
                    </div>
                    <div class="fa-hover col-sm-4 col-sm-2">
                        <select class='chosen-select  chosen-customer-type' id="cus-contact-time">
                            <option value="1">24h内更新记录客户</option>
                            <option value="7">7日内更新记录客户</option>
                            <option value="30">客户丢失预警菜单</option>
                            <option value="200">所有客户</option>
                        </select>
                    </div>
                    <div class="fa-hover col-sm-4 col-sm-2">

                    </div>

                    <div class="fa-hover col-sm-4 col-sm-2">

                    </div>
                    <input type="hidden" name="k" id="k" value="1">
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                        <div class="fa-hover col-sm-2 col-sm-1">
                            <a href="javascript:;" id="cusAdd">
                                <button type="button" class="btn btn-warning btn-sm"><span
                                        class="glyphicon glyphicon-plus" aria-hidden="true"></span> 新客户添加
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="ibox-content" id="table-cus-div">
                    <span style="color:red" id="information"></span></br>
                    <span id="resets"></span>
                    <button class="btn btn-xs btn-outline btn-danger" type="button" id="emptyPid">解除关联</button>&nbsp;
                    <button class="btn btn-xs btn-outline btn-danger" type="button" id="searchCus">客户检索</button>&nbsp;
                    <button class="btn btn-xs btn-outline btn-danger" type="button" id="removeSel">放弃客户</button>&nbsp;
                    <button class="btn btn-xs btn-outline btn-danger" type="button" id="statistics">客户数量</button>&nbsp;
                    <input class="btn btn-xs btn-outline btn-success" type="button" id="addOrder" value="添加订单">&nbsp;
                    <input class="btn btn-xs btn-outline btn-success" type="button" id="addContact" value="添加记录">&nbsp;
                    <input class="btn btn-xs btn-outline btn-success" type="button" id="addContacter" value="添加联系人">&nbsp;
                    <input class="btn btn-xs btn-outline btn-success" type="button" id="changeCus" value="修改信息">&nbsp;
                    <input class="btn btn-xs btn-outline btn-success" type="button" id="changeCusName" value="名称变更">
                    <input class="btn btn-xs btn-outline btn-success" type="button" id="setKpi" value="设为KPI">
                    <button type="button" class="btn btn-info btn-outline btn-xs" name="cus-type-btn" id="cus-type-filter" value="1">显示子公司
                    </button>
                    <button type="button" class="btn btn-info btn-outline btn-xs" name="cus-kpi-btn" id="cus-kpi-filter" value="">显示KPI客户</button>
                    <table class="table table-striped table-bordered table-hover dataTables-Business">
                        <tbody>
                        <tr class="gradeX">
                            <td class="mouseOn cusDetail"></td>
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
            </div>
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>待审核列表</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </div>
                </div>
                <div class="ibox-content">
                    <table class="table table-striped table-bordered table-hover dataTables-unChecked">
                        <thead>
                        <tr>
                            <th>选择</th>
                            <th>客户名称</th>
                            <th>行业</th>
                            <th>联系记录</th>
                            <th>客户创建时间</th>
                            <th>客户级别</th>
                            <th>申请人</th>
                            <th>待审核类型</th>
                            <th>审核人</th>
                            <th>审核进度</th>

                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($data2)): $i = 0; $__LIST__ = $data2;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr class="gradeX">
                                <td class="center"><input type="checkbox" name="checkBox2" class="checkValue"
                                                          dat="<?php echo ($vol["cid"]); ?>" data="<?php echo ($vol["auditorid"]); ?>" value="<?php echo ($vol["cid"]); ?>">
                                </td>
                                <td class="mouseOn cusDetail" data="<?php echo ($vol["cid"]); ?>"><?php echo ($vol["cname"]); ?></td>
                                <td data="<?php echo ($vol["cid"]); ?>"><?php echo ($vol["indus"]); ?></td>
                                <td class="mouseOn saleList" data="<?php echo ($vol["cid"]); ?>"><?php echo ($vol["recordnum"]); ?></td>
                                <td><?php echo (date('Y-m-d H:i:s',$vol["addtime"])); ?></td>
                                <td><?php echo ($vol["clevel"]); ?>级</td>
                                <td><?php echo ($vol["uname"]); ?></td>
                                <td class="center">
                                    <?php switch($vol["type"]): case "1": ?>新客户创建<?php break;?>
                                        <?php case "2": ?>老客户申请<?php break;?>
                                        <?php case "3": ?>信息修改<?php break; endswitch;?>
                                </td>
                                <td class="center"><?php echo ($vol["auditorname"]); ?></td>
                                <td class="center">
                                    <?php switch($vol["auditstatus"]): case "1": ?>审核人未审核<?php break;?>
                                        <?php case "2": ?>总经理未审核<?php break;?>
                                        <?php case "4": ?>审核不通过<?php break; endswitch;?>
                                </td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                    <input class="btn btn-outline btn-success" type="button" id="removeSel2" value="客户放弃"
                           style="width: 10%; text-align: center;">
                    <input class="btn btn-outline btn-success" type="button" id="changeCus2" value="修改客户信息"
                           style="width: 10%; text-align: center;">
                    <input class="btn btn-outline btn-success" type="button" id="addContact2" value="添加业务记录"
                           style="width: 10%; text-align: center;">&emsp;
                    <input class="hidden" type="hidden" id="role" value="<?php echo (session('staffId')); ?>">
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/Public/html/js/demo/form-advanced-demo.min.js"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/dwin/customer/common_func.js"></script>
<script>
    var controller = "/Dwin/Customer";
    var k = $("#k").val();
    var dataTablesBusiness = $(".dataTables-Business");
    var cusTBody = $(".dataTables-Business tbody");
    var cusTypeSel = $("#cus-type-selecter");//个人客户或下属客户
    var infoTip = $("#information");//提示信息
    var cusContactTime = $("#cus-contact-time");// 联系记录时间及预警菜单
    var resetHtml = $("#resets");
    var oTable;
    var oTables;
    var tableCusDiv = $("#table-cus-div");

    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'none';
        $(".dataTables-unChecked").dataTable({
            "autoWidth": false
        });
        oTables = msgTable();
        $("tbody span .unCheck").css('color', 'red');
        $("tbody span .allRecord").css('color', 'blue');
    }); //inintTable END

    // 点击获取对应数据
    cusTypeSel.on('change', function () {
        var innerHtml = "";
        resetHtml.html(innerHtml);
        /*if(cusTypeSel.val() == 'cus-1' && cusContactTime.val() == 200) {
            resetHtml.html('<input type="button" name="resetButton1" id="resetButton" value="批量修改行业提交" onclick="butOnlick();"/>');
        }*/
        if (cusContactTime.val() == 30) {
            infoTip.html("");
            infoTip.html('<span>客户丢失预警菜单：3周无联系记录提醒，新客户8个月无订单提醒、老客户3月无订单提醒</span>');
        } else {
            infoTip.html("");
            /*infoTip.html('<span>未修改行业的同事，请选择个人所有客户，选择合适的行业分类后，点击批量修改行业后修改客户的行业分类，该功能下月中旬关闭</span>');*/
        }
        oTable = dataTablesBusiness.DataTable();
        oTable.ajax.reload();
    });

    function warningTable() {
        dataTablesBusiness.DataTable({
            "destroy": true,
            "paging": true,
            "autoWidth": false,
            "pagingType": "full_numbers",
            "lengthMenu": [10, 15, 20, 100],
            "bDeferRender": true,
            "processing": true,
            "searching": true, //是否开启搜索
            "serverSide": true,//开启服务器获取数据
            "ajax": { // 获取数据
                "url": controller + "/showBusinessData",
                "type": 'post',
                "data": {
                    "k": function () {
                        return document.getElementById('cus-type-selecter').value;
                    },
                    "s": function () {
                        return document.getElementById('cus-contact-time').value;
                    },
                    "hasChild": function () {
                        return document.getElementById('cus-type-filter').value;
                    },
                    "kpiFlag": function () {
                        return document.getElementById('cus-kpi-filter').value;
                    }
                }
            },
            "columns": [ //定义列数据来源
                {'title': "客户名称", 'data': "cname", 'class': "mouseOn cusDetail"},
                {'title': "行业", 'data': "indus"},
                {'title': "联系记录", 'data': "countrecord"},
                {'title': "采购记录", 'data': "titotal"},
                {'title': "联系记录保护期", 'data': 'max_contact_time'},
                {'title': "订单保护期", 'data': 'max_order_time'},
                {'title': "负责人", 'data': 'pname'},
                {'title': "客户类型", 'data': 'has_order'}
                /* {'title':"负责人",'data':null,'class':"align-center"} // 自定义列   {'title':"负责人",'data':null,'class':"align-center"} // 自定义列*/
            ],
            "columnDefs": [ //自定义列
                {
                    "targets": 0,
                    "data": 'cname',
                    "render": function (data, type, row) {
                        if (row.kpi_flag == 1) {
                            return '<div class="kpi"><span>' + data + '</span></div>'
                        }
                        return data;
                    }
                },
                {
                    "targets": 3,
                    "data": 'amount',
                    "render": function (data, type, row) {
                        if (row.amount['all']) {
                            if (!row.amount['checked']) {
                                row.amount['checked'] = 0;
                            }
                            var html = '<span class="unCheck" style="color:red;">' + row.amount['checked'] + '</span>/<span class="allRecord" style="color:blue;">' + row.amount['all'] + '</span>元';
                        } else {
                            var html = "0.00元";
                        }
                        return html;
                    }
                },
                {
                    "targets": 7,
                    "data": 'has_order',
                    "render": function (data, type, row) {
                        if (data == 1) {
                            var html = '<span style="color:red;">老客户</span>';
                        } else {
                            var html = '<span style="color:blue;">新客户</span>';
                        }
                        return html;
                    }
                }
            ],
            "language": { // 定义语言
                "sProcessing": "加载中...",
                "sLengthMenu": "每页显示 _MENU_ 条记录",
                "sZeroRecords": "没有匹配的结果",
                "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
                "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
                "sInfoPostFix": "",
                "sSearch": "搜索:",
                "sUrl": "",
                "sEmptyTable": "表中数据为空",
                "sLoadingRecords": "载入中...",
                "sInfoThousands": ",",
                "oPaginate": {
                    "sFirst": "首页",
                    "sPrevious": "上一页",
                    "sNext": "下一页",
                    "sLast": "末页"
                },
                "oAria": {
                    "sSortAscending": ": 以升序排列此列",
                    "sSortDescending": ": 以降序排列此列"
                }
            }
        });//table end
    }

    function msgTable() {
        dataTablesBusiness.DataTable({
            "destroy": true,
            "paging": true,
            "autoWidth": false,
            "pagingType": "full_numbers",
            "lengthMenu": [10, 15, 20, 100],
            "bDeferRender": true,
            "processing": true,
            "searching": true, //是否开启搜索
            "serverSide": true, //开启服务器获取数据
            "ajax": { // 获取数据
                "url": controller + "/showBusinessData",
                "type": 'post',
                "data": {
                    "k": function () {
                        return document.getElementById('cus-type-selecter').value;
                    },
                    "s": function () {
                        return document.getElementById('cus-contact-time').value;
                    },
                    "hasChild": function () {
                        return document.getElementById('cus-type-filter').value;
                    },
                    "kpiFlag": function () {
                        return document.getElementById('cus-kpi-filter').value;
                    }
                }
            },
            "columns": [ //定义列数据来源
                {
                    'title': "客户名称", 'data': 'cname', 'class': "mouseOn cusDetail", render: function (data, type, row) {
                        if (row.kpi_flag == 1) {
                            return '<div class="kpi"><span>' + data + '</span></div>'
                        }
                        return data
                    }
                },
                {'title': "行业", 'data': "indus"},
                {'title': "联系记录", 'data': "countrecord"},
                {'title': "客服记录", 'data': 'sumonline'},
                {'title': "售后记录", 'data': 'sumservice'},
                {'title': "项目进度", 'data': 'project'},
                {'title': "4月/12月采购金额", 'data': 'titotal', 'class': 'orderAction'},
                {'title': "订单保护期", 'data': 'level'},
                {'title': "负责人", 'data': 'pname'}
                /* {'title':"负责人",'data':null,'class':"align-center"} // 自定义列   {'title':"负责人",'data':null,'class':"align-center"} // 自定义列*/
            ],
            "columnDefs": [ //自定义列
                {
                    "targets": 0,
                    "data": 'cname',
                    "render": function (data, type, row) {
                        var html = row.cname;
                        return html;
                    }
                },
                {
                    "targets": 3,
                    "data": 'sumonline',
                    "render": function (data, type, row) {
                        var html = '<span class="unCheck" style="color:red;">' + row.uncheckonline + '</span>/<span class="allRecord" style="color:blue;">' + row.sumonline + '</span>条';
                        return html;
                    }
                },
                {
                    "targets": 5,
                    "data": 'amount',
                    "render": function (data, type, row) {
                        if (row.amount['all']) {
                            if (!row.amount['checked']) {
                                row.amount['checked'] = 0;
                            }
                            var html = '<span class="unCheck" style="color:red;">' + row.amount['checked'] + '</span>/<span class="allRecord" style="color:blue;">' + row.amount['all'] + '</span>元';
                        } else {
                            var html = "0.00元";
                        }
                        return html;
                    }
                }
            ],
            "language": { // 定义语言
                "sProcessing": "加载中...",
                "sLengthMenu": "每页显示 _MENU_ 条记录",
                "sZeroRecords": "没有匹配的结果",
                "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
                "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
                "sInfoPostFix": "",
                "sSearch": "搜索:",
                "sUrl": "",
                "sEmptyTable": "表中数据为空",
                "sLoadingRecords": "载入中...",
                "sInfoThousands": ",",
                "oPaginate": {
                    "sFirst": "首页",
                    "sPrevious": "上一页",
                    "sNext": "下一页",
                    "sLast": "末页"
                },
                "oAria": {
                    "sSortAscending": ": 以升序排列此列",
                    "sSortDescending": ": 以降序排列此列"
                }
            }
        });//table end
    }

    //@todo
    var cusChildFilter = $("#cus-type-filter");
    cusChildFilter.click(function (e) {
        if ($(this).val() == 1) {
            $(this).val("");
            $(this).text("隐藏子公司");
        } else {
            $(this).val(1);
            $(this).text("显示子公司");
        }
        oTable = dataTablesBusiness.DataTable();
        oTable.ajax.reload();
    });
    var kpiFilter = $('#cus-kpi-filter');
    kpiFilter.click( function () {

        if ($(this).val() == 1) {
            $(this).val("");
            $(this).text("仅显示KPI客户");
        } else {
            $(this).val(1);
            $(this).text("显示所有客户");
        }
        oTable = dataTablesBusiness.DataTable();
        oTable.ajax.reload();
    });
    cusContactTime.on('change', function () {
        var innerHtml = "";
        resetHtml.html(innerHtml);
        if (cusTypeSel.val() == 'cus-1' && $(this).val() == 200) {
            /*resetHtml.html('<input type="button" name="resetButton1" id="resetButton" value="批量修改行业提交" onclick="butOnlick();"/>');*/
            oTable = dataTablesBusiness.DataTable();
            oTable.destroy();
            dataTablesBusiness.empty();
            oTables = msgTable();
        }
        if (cusContactTime.val() == 30) {
            infoTip.html("");
            infoTip.html('<span>客户丢失预警菜单：3周无联系记录提醒，新客户6个月无订单提醒、老客户3月无订单提醒</span>');
            oTable = dataTablesBusiness.DataTable();
            oTable.destroy();
            dataTablesBusiness.empty();
            oTables = warningTable();
        } else {
            infoTip.html("");
            /*infoTip.html('<span>请选择个人所有客户，选择合适的行业分类后，点击批量修改行业后修改客户的行业分类，该功能下月中旬关闭</span>');*/
            oTable = dataTablesBusiness.DataTable();
            oTable.destroy();
            dataTablesBusiness.empty();
            oTables = msgTable();
        }
    });
    $("#searchCus").click( function () {
        layer.prompt({title: '请输入检索的客户名称（请正确输入）', formType: 2}, function(text, index){
            $.ajax({
                type : 'POST',
                url : controller + "/checkCusMsg",
                data : {
                    name : text
                },
                success : function (res) {
                    layer.close(index);
                    layer.msg('查重结果：'+ res['msg']);
                }
            });

        });
    });
    $("#emptyPid").click( function () {
        var id;
        $(".dataTables-Business tbody tr").each(function () {
            if ($(this).hasClass('selected')) {
                id = $(this).attr('id');
            }
        });
        if (id) {
            layer.confirm('确定解除该公司与上级公司的关联？', {
                btn: ['确定','返回'] //按钮
            }, function(){
                $.ajax({
                    type : 'POST',
                    url : controller + "/resetCusPid",
                    data : {
                        cusId : id
                    },
                    success : function (res) {
                        layer.msg(res.msg);
                    }
                })
            })
        } else {
            layer.msg('请选中要解除与上级公司关联的客户');
        }
    })
    $("#statistics").click(function () {
        layer.open({
            type  : 2,
            title : "权限下客户数量查看",
            end : function () {
                $(".dataTables-Business tbody tr").each(function () {
                    if ($(this).hasClass('selected')) {
                        $(this).removeClass('selected');
                    }
                });
            },
            area : ['80%', '80%'],
            content: controller + "/customerNumberList" //iframe的url
        });
    })
</script>
<script src="/Public/html/js/dwin/customer/business_list.js"></script>
</body>
</html>