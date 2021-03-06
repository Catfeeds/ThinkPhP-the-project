<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>产品结算管理</title>
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
        .hiddenDiv{
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
                    <h5>产品业绩统计基础数据管理</h5>
                </div>
                <div class="ibox-content">
                    <span id="costSaveSpan"></span>
                    <div class="col-sm-3" id="prj_order_type" style="margin-left: -12px;">
                        <select name="id" id="parentCategory" class="form-control">
                            <?php if(is_array($screenData)): $i = 0; $__LIST__ = $screenData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["id"]); ?>"><?php echo (str_repeat("&emsp;&emsp;",$vol["level"]*2)); echo ($vol["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                    <div class=" col-sm-4 form-inline text-right">
                        <label for="">提交产品修改前,请先选择审核人</label>
                        <select name="" class="form-control chooseAuditor" id="">
                            <option value="">请选择审核人</option>
                            <?php if(is_array($auditor)): foreach($auditor as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>_<?php echo ($vo["name"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="col-sm-3 hiddenDiv" id="prj_order_type2" style="margin-left: -12px;">
                        <select name="id" id="parentCategory2" class="form-control">
                            <?php if(is_array($screenData)): $i = 0; $__LIST__ = $screenData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["id"]); ?>"><?php echo (str_repeat("&emsp;&emsp;",$vol["level"]*2)); echo ($vol["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                    <button style="float: right; margin-right: 40px;" type="button" class="btn btn-outline btn-primary" id="editCostBtn">业绩统计基础数据编辑</button>
                    <div class="ibox-content" style="margin-top: 15px;">
                        <table id="table" class="table table-striped table-bordered table-full-width" width="100%">
                            <thead>
                            <tr>
                                <th>编号</th>
                                <th>产品型号</th>
                                <th>所属分类</th>
                                <th>报价</th>
                                <th>单价业绩</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <table id="costTable" class="table table-striped table-bordered table-hover">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/Admin/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    var controller = "/Dwin/Finance";
    $(document).ready(function(){
        var table = $('#table').DataTable({
            "paging"       : true,
            "autoWidth"    : false,
            "pagingType"   : "full_numbers",
            "lengthMenu"   : [10, 15, 20, 100],
            "bDeferRender" : true,
            "searching"    : true, //是否开启搜索
            "serverSide"   : true,
            "ajax": {
                "url": controller + "/showProduct",
                "type": "post",
                "data":{
                    "prj_order_type" : function () {
                        return document.getElementById('parentCategory').value;
                    }
                }
            },
            "columns": [
                { "data" : "product_name",   "title" : "产品型号", "defaultContent" : ""},
                { "data" : "product_number", "title" : "产品名",   "defaultContent" : ""},
                { "data" : null,             "title" : "所属分类", "defaultContent" : ""},
                { "data" : "price",          "title" : "产品报价", "defaultContent" : ""},
                { "data" : "performance",    "title" : "单件业绩", "defaultContent" : ""}
            ],
            "columnDefs"   : [
                {
                    "targets" : 2,
                    "data" : 'name',
                    "render" : function(data, type, row) {
                        var html = row.name;
                        html += '<input type="hidden" value='+ row.parent_id +'>';
                        return html;
                    }
                }
            ],
            "language": {
                "sProcessing"     : "处理中...",
                "sLengthMenu"     : "显示 _MENU_ 项结果",
                "sZeroRecords"    : "没有匹配结果",
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
                    "sPrevious" : "上页",
                    "sNext"     : "下页",
                    "sLast"     : "末页"
                },
                "oAria"           : {
                    "sSortAscending"  : ": 以升序排列此列",
                    "sSortDescending" : ": 以降序排列此列"
                }
            }
        });
    });
    //刷新ajax
    $("#parentCategory").on('change', function () {
        var oTable = $("#table").DataTable();
        oTable.ajax.reload();
    });
    $("#parentCategory2").on('change', function () {
        var oTable = $("#costTable").DataTable();
        oTable.ajax.reload();
    });

    var editCostBtn   = $("#editCostBtn");
    var costTableDiv  = $("#costTable");
    var costSaveSpan  = $("#costSaveSpan");
    var ediCostOneBtn = (".edit-cost-btn");
    var oTable;
    function costBtnDraw(selector)
    {
        if ($("#table_wrapper").css('display') == 'none') {
            selector.DataTable({
                "destroy"      : true,
                "paging"       : true,
                "autoWidth"    : true,
                "pagingType"   : "full_numbers",
                "lengthMenu"   : [10, 20, 50, 100],
                "bDeferRender" : true,
                "searching"    : true, //是否开启搜索
                "serverSide"   : true,
                "ajax" : {
                    "url"  : controller + "/showProduct",
                    "type" : "post",
                    "data" : {
                        "costData" : 1,
                        "prj_order_type" : function () {
                            return document.getElementById('parentCategory2').value;
                        }
                    }
                },
                "columns": [
                    { "data" : "product_name",                "title" : "产品", "defaultContent" : ""},
                    { "data" : "price",                       "title" : "报价", "defaultContent" : ""},
                    { "data" : "performance",                 "title" : "单件业绩", "defaultContent" : ""},
                    { "data" : "cost",                        "title" : "成本", "defaultContent" : ""},
                    { "data" : "statistics_performance_flag", "title" : "是否统计业绩", "defaultContent" : ""},
                    { "data" : "statistics_shipments_flag",   "title" : "是否统计出货量", "defaultContent" : ""},
                    { "data" : null,                          "title" : "操作", "defaultContent" : "<button class='btn btn-success btn-outline btn-xs edit-cost-btn' type='button'>单件修改</button>"}
                ],
                "columnDefs"   : [
                    {
                        "targets" : 1,
                        "data"    : 'price',
                        "render"  : function(data, type, row) {
                            var html = '<input type="text" class="form-control" style="width: 70%" value=' + row.price + '>';
                            return html;
                        }
                    },
                    {
                        "targets" : 2,
                        "data"    : 'performance',
                        "render"  : function(data, type, row) {
                            var html = '<input type="text" class="form-control" style="width: 70%" value=' + row.performance + '>';
                            return html;
                        }
                    },
                    {
                        "targets" : 3,
                        "data"    : 'cost',
                        "render"  : function(data, type, row) {
                            var html = '<input type="text" class="form-control" style="width: 70%" value=' + row.cost + '>';
                            return html;
                        }
                    },
                    {
                        "targets" : 4,
                        "data"    : 'statistics_performance_flag',
                        "render"  : function(data, type, row) {
                            var html = '<select name="performance_flag" class="form-control">';
                            if (row.statistics_performance_flag == 1) {
                                html += '<option value="1" selected>是</option>'
                                        + '<option value="0">否</option>';
                            } else {
                                html += '<option value="1" >是</option>'
                                    + '<option value="0" selected>否</option>';
                            }
                            html += '</select>';
                            return html;
                        }
                    },
                    {
                        "targets" : 5,
                        "data"    : 'statistics_shipments_flag',
                        "render"  : function(data, type, row) {
                            var html = '<select name="performance_flag" class="form-control">';
                            if (row.statistics_shipments_flag == 1) {
                                html += '<option value="1" selected>是</option>'
                                    + '<option value="0">否</option>';
                            } else {
                                html += '<option value="1" >是</option>'
                                    + '<option value="0" selected>否</option>';
                            }
                            html += '</select>';
                            return html;
                        }
                    }
                ],
                "language": {
                    "sProcessing": "处理中...",
                    "sLengthMenu": "显示 _MENU_ 项结果",
                    "sZeroRecords": "没有匹配结果",
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
                        "sPrevious": "上页",
                        "sNext": "下页",
                        "sLast": "末页"
                    },
                    "oAria": {
                        "sSortAscending": ": 以升序排列此列",
                        "sSortDescending": ": 以降序排列此列"
                    }
                }});
            costSaveSpan.html("");
            costSaveSpan.html("<button class='btn btn-primary btn-outline btn-sm' id='costSaveAll' type='button'>批量保存</button>");
        } else {
            oTable = selector.DataTable();
            oTable.destroy();
            selector.children("thead").empty();
            selector.children("tbody").empty();
            costSaveSpan.html("");
        }
    }
    editCostBtn.on('click', function () {
        $("#batch-edit-btn").css("display", "none");
        $("#table_wrapper").toggleClass("hiddenDiv");
        $("#prj_order_type").toggleClass('hiddenDiv');
        $("#prj_order_type2").toggleClass('hiddenDiv');
        costBtnDraw(costTableDiv);
    });

    var priceArray = [], idArray = [], performanceArr = [], costArr = [], shipmentsArr = [], performSArr = [];
    var costSaveAllBtn = $("#costSaveAll");
    function getCostData(costSaveAllBtn) {
        costSaveAllBtn.attr('disabled', true);
        priceArray     = [];
        idArray        = [];
        performanceArr = [];
        costArr        = [];
        shipmentsArr   = [];
        performSArr    = [];// 数组初始为空
        $("#costTable tbody tr").each(function() {
            //报价
            priceArray.push($(this).find('td').eq(1).children().val());
            //单件业绩
            performanceArr.push($(this).find('td').eq(2).children().val());
            //成本
            costArr.push($(this).find('td').eq(3).children().val());
            //是否出货量统计
            shipmentsArr.push($(this).find('td').eq(5).children().val());
            //是否业绩统计
            performSArr.push($(this).find('td').eq(4).children().val());
            //id
            idArray.push($(this).context.id);
        });
        var auditor = $('.chooseAuditor').val();
        if (auditor === ''){
            layer.msg('请选择审核员');
            costSaveAllBtn.attr('disabled', false);
            return false
        }
        $.ajax({
            type : 'POST',
            url  : controller + '/editProduct',
            data : {
                ids               : idArray,
                price             : priceArray,
                performance       : performanceArr,
                cost              : costArr,
                performStatistics : performSArr,
                shipments         : shipmentsArr,
                auditor           : auditor
            },
            success : function (msg) {
                layer.msg(msg['msg']);
                costSaveAllBtn.attr('disabled', false);
            }
        });
    }

    costSaveSpan.on('click',"#costSaveAll", function () {
        var thisIndex = $(this);
        thisIndex.attr('disabled', true);
        getCostData(thisIndex);
    });
    costTableDiv.on('click', '.edit-cost-btn', function () {
        var thisIndex = $(this);
        thisIndex.attr('disabled',true);
        priceArray     = [];
        idArray        = [];
        performanceArr = [];
        costArr        = [];
        shipmentsArr   = [];
        performSArr    = [];
        var trIndex = $(this).parent().parent();

        //报价
        priceArray.push(trIndex.find('td').eq(1).children().val());
        //单件业绩
        performanceArr.push(trIndex.find('td').eq(2).children().val());
        //成本
        costArr.push(trIndex.find('td').eq(3).children().val());
        //是否出货量统计
        shipmentsArr.push(trIndex.find('td').eq(5).children().val());
        //是否业绩统计
        performSArr.push(trIndex.find('td').eq(4).children().val());
        //id
        idArray.push(trIndex[0].id);
        var auditor = $('.chooseAuditor').val();
        if (auditor === ''){
            layer.msg('请选择审核员');
            thisIndex.attr('disabled',false);

            return false
        }
        $.ajax({
            type : 'POST',
            url  : controller + '/editProduct',
            data : {
                ids               : idArray,
                price             : priceArray,
                performance       : performanceArr,
                cost              : costArr,
                performStatistics : performSArr,
                shipments         : shipmentsArr,
                auditor           : auditor
            },
            success : function (msg) {
                layer.msg(msg['msg']);
                thisIndex.attr('disabled',false);
            }
        });
    });
</script>
</body>
</html>