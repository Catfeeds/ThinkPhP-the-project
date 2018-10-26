<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>项目列表-数据表格</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;
        }
        .ibox-title{
            padding-top: 7px !important;
        }
        .selected{
            background-color: #A6EDEC !important;
        }
        .mouseOn{
            cursor:pointer;
            background-color: #A6EDEC!important;
        }
        .dataTables-prjList{
            white-space: nowrap !important;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <div class="col-sm-2">
                        <select name="prj-range" class='form-control' id="prj-range">
                            <option value="1">个人项目</option>
                            <option value="2">下属项目</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <select name="prj-type" class='form-control' id="prj-type">
                            <option value="1">进展中项目</option>
                            <option value="2">验收中项目</option>
                            <option value="3">月完成项目</option>
                        </select>
                    </div>
                    <div class="col-sm-3" id="prj-time-limit">
                        <select name="time-limit" class='form-control' id="time-limit"> 
                            <option value="1">24小时项目进度记录</option>
                            <option value="7">1周项目进度记录</option>
                        </select>
                    </div>
                </div>
                <div class="ibox-content">
                    <table class="table table-striped table-bordered table-hover dataTables-prjList">
                        <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
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
                    <span id="updBtn"></span>
                    <span id="chgBtn"></span>
                    <span id="actBtn"></span>
                </div>
            </div>
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <table class="table table-striped table-bordered table-hover dataTables-unCheck">
                        <thead>
                        <tr>
                            <td colspan="10"> <h4>审核中项目</h4></td>
                        </tr>
                        <tr>
                            <th>参与人</th>
                            <th>部门</th>
                            <th>项目名称</th>
                            <th>总绩效</th>
                            <th>起止日期</th>
                            <th>客户</th>
                            <th>立项人</th>
                            <th>审核状态</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($audit)): $i = 0; $__LIST__ = $audit;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr class="gradeX">
                                <td><?php echo ($vol["pname"]); ?></td>
                                <td>
                                    <?php echo ($vol["deptname"]); ?>
                                </td>
                                <td  class="prjDetail mouseOn" data=<?php echo ($vol["proid"]); ?>><?php echo ($vol["proname"]); ?></td>
                                <td class="performOfPrj mouseOn" data=<?php echo ($vol["proid"]); ?>><?php echo ($vol["performbonus"]); ?></td>
                                <td><?php echo (date('Y-m-d',$vol["protime"])); ?>——<?php echo (date('Y-m-d',$vol["deliverytime"])); ?></td>
                                <td><?php echo ($vol["cusname"]); ?></td>
                                <td><?php echo ($vol["buildname"]); ?></td>
                                <td>
                                    <?php switch($vol["auditstatus"]): case "1": echo ($vol["auditname"]); ?>未审<?php break;?>
                                        <?php case "2": ?>总经理未审<?php break;?>
                                        <?php case "4": ?>审核不通过<?php break; endswitch;?>
                                </td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                    <input class="hidden" type="hidden" id="rolestaff" value="<?php echo (session('staffId')); ?>">
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    var controller = "/Dwin/Research";
    function showBtn(range, type)
    {
        var prj_range = range;
        var prj_type  = type;
        $.ajax({
                type : 'POST',
                url  : controller + "/showOwnPrj",
                data : {
                    prj_range : prj_range,
                    prj_type  : prj_type
                },
                success : function (ajaxData) {
                    var time_html = "", upd_span = "", chg_span = "", act_span = "";
                    if (ajaxData == 1) {
                        $("#prj-time-limit").html(time_html);
                        upd_span += '<input class="btn btn-outline btn-success" type="button" id="updPrj" value="进度更新" style="width: 10%; text-align: center;">';
                        $("#updBtn").html(upd_span);
                    } else if (ajaxData == 2) {
                        // 无更新权限，有变更权限
                        chg_span += '<input class="btn btn-outline btn-success" type="button" id="chgPrj" value="项目变更" style="width: 10%; text-align: center;">';
                        act_span += '<input class="btn btn-outline btn-success" type="button" id="actPrj" value="项目验收" style="width: 10%; text-align: center;">';
                        $("#chgBtn").html(chg_span);
                        $("#actBtn").html(act_span);                            
                    } else if(ajaxData == 4) {
                        chg_span += '<input class="btn btn-outline btn-success" type="button" id="chgPrj" value="项目变更" style="width: 10%; text-align: center;">';
                        act_span += '<input class="btn btn-outline btn-success" type="button" id="comPrj" value="项目完成" style="width: 10%; text-align: center;">';
                        $("#chgBtn").html(chg_span);
                        $("#actBtn").html(act_span);
                    } else if(ajaxData == 3) {
                        upd_span += '<input class="btn btn-outline btn-success" type="button" id="updPrj" value="进度更新" style="width: 10%; text-align: center;">';
                        chg_span += '<input class="btn btn-outline btn-success" type="button" id="chgPrj" value="项目变更" style="width: 10%; text-align: center;">';
                        act_span += '<input class="btn btn-outline btn-success" type="button" id="actPrj" value="项目验收" style="width: 10%; text-align: center;">';
                        $("#updBtn").html(upd_span);
                        $("#chgBtn").html(chg_span);
                        $("#actBtn").html(act_span);
                    }
                }
            });
    }

/**
 * [checkBoxSel 点击选中行]
 * @param  {[string]} dataTableClass [表格dataTables-dataTablesClass]
 * @return {[mix]}                [无返回值，添加行选择高亮]
 */
function checkBoxSel(dataTableClass) {
    $(".dataTables-" + dataTableClass + " tbody").on('click', 'tr', function (e) {
        e.stopPropagation();
        var oTables = $('.dataTables-' + dataTableClass + '').DataTable();
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        } else {
            oTables.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });
}

/**
 * [getSelectedValue 获取表中选中行的值]
 * @param  {[string]} dataTableClass [表格dataTables-dataTablesClass]
 * @return {[string]} id               [行id的值]
 */
function getSelectedValue(dataTableClass)
{
    var id;
    $(".dataTables-" + dataTableClass + " tbody tr").each(function () {
        if ($(this).hasClass('selected')) {
            id = $(this).attr('id');
        }
    });
    return id;
}

/**
 * [buttonAction 点击按钮触发事件，添加进度记录和项目变更使用]
 * @param  {[string]} dataTablesClass [表格类名]
 * @param  {[string]} method1         [接口名]
 * @param  {[string]} method2         [打开的页面的接口名]
 * @param  {[string]} message         [无权限显示的信息]
 * @param  {[string]} layer_title     [页面标题]
 * @return {[none]}                 [无返回值]
 * @todo [点击按钮后的关闭后触发的事件，目前为刷新，后续需要修改]
 */
function buttonAction(dataTablesClass, method1, method2, message, layer_title) {
    var checkId = getSelectedValue(dataTablesClass);
    if (checkId) {
        $.ajax({
            type : 'POST',
            url  : controller + "/" + method1,
            data : {
                proid : checkId
            },
            success : function (msg) {
                if (msg == 1) {
                    layer.open({
                        type  : 2,
                        title : layer_title,
                        area  : ['90%', '80%'],
                        end   : function () {
                            location.reload();
                        },
                        content : controller + "/" + method2 + "/id/" + checkId
                    });
                } else {
                    layer.msg(message, {icon : 5});
                }
            }
        });
    } else {
        layer.alert('请选中项目');
        return false;
    }
}

    $(document).ready(function() {
        var range    = $("#prj-range").val();
        var prj_type = $("#prj-type").val();
        showBtn(range, prj_type);
        $(".dataTables-unCheck").dataTable({
            'bFilter' : true,
            'bLengthChange' : false,
            'bInfo' :　true,
            'bAutoWidth': false,
            'iDisplayLength' :　5
        });

        $(".dataTables-prjList").dataTable({
            "paging"       : true,
            "pagingType"   : "full_numbers",
            "lengthMenu"   : [10, 15, 20, 100],
            "bDeferRender" : true,
            "processing"   : true,
            "searching"    : true, //是否开启搜索
            "serverSide"   : true,//开启服务器获取数据
            "ajax"         : {    //获取数据
                "url"   : controller + "/showOwnDeliveryPrj",
                "type"  : 'post',
                "data"  : {
                    "prj_range": function () {
                        return document.getElementById('prj-range').value;
                    },
                    "prj_type" : function () {
                        return document.getElementById('prj-type').value;
                    },
                    "time_limit" : function () {
                        return document.getElementById('time-limit').value;
                    }
                }
            },
            "columns"      :[ //定义列数据来源
                 {'title' : "参与人",       'data' : 'pname'},
                 {'title' : "部门",         'data' : "dept_name"},
                 {'title' : "项目名称",     'data' : "prj_name"},
                 {'title' : "进度记录",     'data' : "prj_record"},                 
                 {'title' : "项目绩效",     'data' : 'prj_price'},
                 {'title' : "项目变更记录", 'data' : 'change_num'},
                 {'title' : "启动时间",     'data' : 'start_time'},
                 {'title' : "预计完成时间", 'data' : 'delivery_time'},
                 {'title' : "内部验收时间", 'data' : 'prjdtime'},
                 {'title' : "实际完成时间", 'data' : 'complete_time'},
                 {'title' : "验收人",       'data' : 'builder_name'},
                 {'title' : "客户",         'data' : 'cus_name'}
                /* {'title':"负责人",'data':null,'class':"align-center"} // 自定义列   {'title':"负责人",'data':null,'class':"align-center"} // 自定义列*/
            ],
            "columnDefs"   : [ //自定义列
                {
                    "targets" : 0,
                    "data" : 'pname',
                    "render" : function(data, type, row) {
                        var html = row.pname;
                        return html;
                    }
                },
                {
                    "targets" : 3,
                    "data" : 'prj_record',
                    "render" : function(data, type, row) {
                        if (row.prj_record == 0) {
                            html = '<span style="color:red;">无更新记录</span>';
                        } else {
                            html = row.prj_record + "条";
                        }
                        return html;
                    }
                },
                {
                    'targets' : 5,
                    'data'    : 'change_num',
                    'render'  : function (data, type, row) {
                        if (row.change_num != 0) {
                            // 显示记录
                            var innerHtml = row.change_num + "条";
                        } else {
                            // 无记录
                            var innerHtml = "无记录";
                        }
                        return innerHtml;
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
        });
    });

    checkBoxSel('prjList');//调用函数，点击选中行
    $("#prj-range").on('change', function () {

        $("#updBtn").html("");
        $("#chgBtn").html("");
        $("#actBtn").html("");
        var range    = $("#prj-range").val();
        var prj_type = $("#prj-type").val();
        showBtn(range, prj_type);  
        if ($("#prj-type").val() == 1) {
            $("#prj-time-limit").html("");
            var time_limit = '<select name="time-limit" class="form-control" id="time-limit">' + 
                                '<option value="1">24小时项目进度记录</option>' +
                                '<option value="7">1周项目进度记录</option>' +
                             '</select>';
            $("#prj-time-limit").html(time_limit);
        } else {
            $("#prj-time-limit").html("");
            var time_limit = '<select name="time-limit" class="form-control" id="time-limit">' + 
                                '<option value="30">30日内进度记录</option>' +
                             '</select>';
            $("#prj-time-limit").html(time_limit);
        }
        var oTables = $(".dataTables-prjList").DataTable();
        oTables.ajax.reload();
    });

    $("#prj-type").on('change', function () {

        $("#updBtn").html("");
        $("#chgBtn").html("");
        $("#actBtn").html("");
        var range    = $("#prj-range").val();
        var prj_type = $("#prj-type").val();
        showBtn(range, prj_type); 
        if ($(this).val() != 1) {
            $("#prj-time-limit").html("");
            var time_limit = '<select name="time-limit" class="form-control" id="time-limit">' + 
                                '<option value="30">30日内进度记录</option>' +
                             '</select>';
            $("#prj-time-limit").html(time_limit);
        } else {
            $("#prj-time-limit").html("");
            var time_limit = '<select name="time-limit" class="form-control" id="time-limit">' + 
                                '<option value="1">24小时项目进度记录</option>' +
                                '<option value="7">1周项目进度记录</option>' +
                             '</select>';
            $("#prj-time-limit").html(time_limit);
        }
        var oTables = $(".dataTables-prjList").DataTable();
        oTables.ajax.reload(); 
    });
    $("#time-limit").on('change', function () {
        var oTables = $(".dataTables-prjList").DataTable();
        oTables.ajax.reload(); 
    });


    $(".prjPublic").on('click', function () {
        layer.open({
            type  : 2,
            title : '项目公示信息提交',
            area : ['100%', '100%'],
            content: controller + "/addPublicPrj"
        });
    });

    $(".prjAdd").on('click', function () {
        layer.open({
            type  : 2,
            title : '项目公示信息提交',
            area : ['100%', '100%'],
            content: controller + "/addProject"
        });
    });



/*---------------------------------js显示截至时间-------------------------------*/
var now = Date.parse(new Date()).toString();
now = now.substr(0, 10);
var jiezhi = [];
var ch = [];
$(".setTime").each(function(i) {
    jiezhi[i] = $(this).attr('data');
    ch[i] = (jiezhi[i] - now)/3600/24;
    ch[i] = ch[i].toFixed(0);
    if (ch[i] >= 0) {
        $(this).text("还剩" + ch[i] + "天");
    } else {
        ch[i] = -1 * ch[i];
        $(this).text("超时" + ch[i] + "天");
    }
});
/*------------------------------------------end------------------------------------*/






// 进度更新按钮
$(document).on('click','#updPrj', function () {
    buttonAction('prjList', 'updateProject','updateProject','非项目参与人不能更新进度','项目进度更新');
});

// 项目变更按钮
$(document).on('click','#chgPrj', function() {
    buttonAction('prjList', 'checkChange', 'changeProject','您不是项目验收人', '项目变更');
});

// 项目验收
$(document).on('click','#actPrj', function() {
    var checkId = getSelectedValue('prjList');
    if (checkId) {
        $.ajax({
            type : 'POST',
            url  : controller + "/checkChange",
            data : {
                proid : checkId
            },
            success : function (msg) {
                if (msg == 1) {
                    layer.confirm(
                        '确定进入验收阶段？',
                        {
                            btn : ['验收', '再想想']
                        },
                        function () {
                            $.ajax({
                                type : 'POST',
                                url : controller + "/accessProject",
                                data : {
                                    proid : checkId
                                },
                                success : function (rst) {
                                    if (rst == 1) {
                                        layer.msg(
                                            '项目已处于验收状态',
                                            {
                                                icon : 6,
                                                time : 1000
                                            },
                                            function () {
                                                window.location.reload();
                                            }
                                        );
                                    } else {
                                        layer.msg(
                                            '您不是项目验收人',
                                            {
                                                icon : 5,
                                                time : 1000
                                            },
                                            function() {
                                                window.location.reload();
                                            }
                                        );
                                    }
                                }
                            });
                        },
                        function () {
                            layer.msg('ok',
                                {
                                    icon : 5,
                                    time : 1000
                                });
                        }
                    );

                } else {
                    layer.msg('您不是项目验收人', {icon : 5});
                }
            }
        });
    } else {
        layer.alert('请选中项目');return false;
    }
});

// 项目完成
$(document).on('click','#comPrj', function() {
    var checkId = getSelectedValue('prjList');
    if (checkId) {
        $.ajax({
            type : 'POST',
            url  : controller + "/checkChange",
            data : {
                proid : checkId
            },
            success : function (msg) {
                if (msg == 1) {
                    layer.confirm(
                        '项目验收通过，提交完成？',
                        {
                            btn : ['完成', '再想想']
                        },
                        function () {
                            $.ajax({
                                type : 'POST',
                                url : controller + "/completePrj",
                                data : {
                                    proid : checkId
                                },
                                success : function (rst) {
                                    if (rst == 1) {
                                        layer.msg(
                                            '项目完成',
                                            {
                                                icon : 6,
                                                time : 1000
                                            },
                                            function () {
                                                window.location.reload();
                                            }
                                        );
                                    } else {
                                        layer.msg(
                                            '出问题了，联系开发人员',
                                            {
                                                icon : 5,
                                                time : 1000
                                            },
                                            function() {
                                                window.location.reload();
                                            }
                                        );
                                    }
                                }
                            });
                        },
                        function () {
                            layer.msg('ok',
                                {
                                    icon : 5,
                                    time : 1000
                                });
                        }
                    );

                } else {
                    layer.msg('您不是项目验收人', {icon : 5});
                }
            }
        });
    } else {
        layer.alert('请选中项目');return false;
    }
});

/*-------------------------layer层查看进度和变更*----------------------------------*/
 $(".dataTables-prjList tbody").on('mouseenter','td' ,function(e) {
    var cellindex = $(this).parent();
    var thisIndex = $(this);
    var prjId = cellindex.attr('id');
    var tdIndex = cellindex['context']['cellIndex'];
    if (tdIndex == 2) {
        $(this).addClass('mouseOn');
    } else if (tdIndex == 3) {
        $(this).addClass('mouseOn');
        $.ajax({
            type : 'POST',
            url  : controller + "/showPrjUpdateList",
            data : {
                id : prjId,
                time_limit : $("#time-limit").val()
            },
            success : function (ajaxData) {
                if (ajaxData) {
                    layer.tips('项目进度记录：' + ajaxData, thisIndex,
                    {
                        tips : [1, '#3595CC'],
                        area : '500px'
                    });
                }
            }
        });
    } else if (tdIndex == 4) {
        $(this).addClass('mouseOn');
        $.ajax({
            type : 'POST',
            url  : controller + "/showPerformanceDetail",
            data : {
                prj_id : prjId
                },
            success : function (ajaxData) {
                if (ajaxData) {
                    layer.tips("绩效分配：<br/>" + ajaxData, thisIndex,
                    {
                        tips : [1, '#3595CC'],
                        area : '100px',
                    });
                }
            }
        });
    } else if (tdIndex == 5) {
        $(this).addClass('mouseOn');
        $.ajax({
            type : 'POST',
            url  : controller + "/showPrjChangeList",
            data : {
                prj_id : prjId
                },
            success : function (ajaxData) {
                if (ajaxData) {
                    layer.tips("变更记录内容：<br/>" + ajaxData, thisIndex,
                    {
                        tips : [1, '#3595CC'],
                        area : '600px'
                    });
                }
            }
        });
    } else {
        return false;
    }
});
$(".dataTables-prjList tbody").on('mouseleave','td' ,function(e) {
    $(this).removeClass('mouseOn');
    layer.closeAll('tips');
});
$('.dataTables-prjList tbody').on('click','td', function(e) {
    var cellindex = $(this).parent();
    var thisIndex = $(this);
    var prjId = cellindex.attr('id');
    var tdIndex = cellindex['context']['cellIndex'];
    if (tdIndex == 2) {
        e.stopPropagation();
        layer.open({
            type: 2,
            title: '项目详情',
            area: ['100%', '100%'],
            content: controller + "/showProjectDetail/id/" + prjId //iframe的url
        }); 
    }
});

/**----------------------------------end-----------------------------------------**/
</script>
</body>
</html>