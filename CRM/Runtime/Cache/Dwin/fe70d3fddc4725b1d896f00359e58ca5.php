<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客户列表-数据表格</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;
        }
        td{
            cursor:pointer;
        }
        .el-table--fit{
            width: 50% !important;
        }
        .el-dialog{
            width: 70% !important;
        }
        .el-dialog__body{
            padding-top:0 !important; 
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>客户转移</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </div>
                </div>
                <div class="ibox-content">
                    <input class="btn btn-outline btn-success" type="button" id="assignAll" value="转移所有" style="width: 10%; text-align: center;">
                    <input class="btn btn-outline btn-success" type="button" id="assignSel" value="转移选中" style="width: 10%; text-align: center;">
                </div>
                <div class="ibox-content">
                    <select class="form-control chosen-select" name="userId" id="useId" style="width:30%;" tabindex="2">
                        <option value="">请选择需要转移客户的业务员姓名</option>
                        <?php if(is_array($totalIds)): $i = 0; $__LIST__ = $totalIds;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["id"]); ?>"><?php echo ($vol["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                    <span>================================>>></span>
                    <select class="form-control chosen-select" name="userIdto" id="useId_2" style="width:30%;" tabindex="2">
                        <option value="">请选择把客户转移给哪位业务员</option>
                        <?php if(is_array($totalIds)): $i = 0; $__LIST__ = $totalIds;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["id"]); ?>"><?php echo ($vol["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                    <table class="table table-striped table-bordered table-hover dataTables-assignTable">
                        <tbody>
                        <tr class="gradeX">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <input type="hidden" name="k" id="k" value="">
                        </tbody>
                    </table>
            </div>
            </div>
            <div class="ibox-content" id="app" style="width:100%">
                <el-dialog  title="转移客户"  :visible.sync="dialogTableVisible" :modal-append-to-body="false">
                    <div>
                        <el-table :data="upData" style="padding-top：0;float:left;" height="270">
                            <el-table-column property="cname" label="上级公司名称" width="200"></el-table-column>
                            <el-table-column property="name" label="业务员" width="100"></el-table-column>
                        </el-table>
                        <el-table :data="loadData" style="padding-top：0;float:left;" height="270">
                            <el-table-column property="cname" label="下级公司名称" width="200"></el-table-column>
                            <el-table-column property="name" label="业务员" width="100"></el-table-column>
                        </el-table>
                    </div>
                    <div style="text-align:center;margin-top:8px">
                        <el-button size="mini" @click="dialogTableVisible = false" style="margin-top:10px">取 消</el-button>
                        <el-button size="mini" id="transfar" onclick="transfar()">转 移</el-button>
                    </div>
                </el-dialog>
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
<script src="/Public/html/js/vue.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/index.js"></script>
<script>
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                gridData:'',
                dialogTableVisible:false,
                upData :[],
                loadData :[],
            }
        },
        created(){
        },
        methods: {

        }
    })

    var controller = "/Dwin/Customer";
    var selected = [];
    var co;
    var kid = $("#k").val();
    console.log(<?php echo ($vol); ?>)

    $(document).ready(function() {

        $(".dataTables-assignTable").dataTable({
            "sPaginationType": "bootstrap",
            "paging"       : true,
            "pagingType"   : "full_numbers",
            "lengthMenu"   : [5, 10, 25, 50],
            "bDeferRender" : true,
            "processing"   : true,
            "searching"    : true, //是否开启搜索
            "serverSide"   : true,//开启服务器获取数据
            "ajax"         :{ // 获取数据
                "url"   : controller + "/showAssignData",
                "type"  : 'post',
                data    : {
                    k : function () {
                     return document.getElementById('useId').value;
                    }
                }
            },
            "columns"      :[ //定义列数据来源
                {'title' : "客户名称", 'data':"cname"},//隐藏
                {'title' : "客户创建人", 'data' : "fname"},
                {'title' : "客户创建时间", 'data' : "builder_time"},
                {'title' : "客户状态", 'data' : "au_status"}
                /* {'title':"负责人",'data':null,'class':"align-center"} // 自定义列   {'title':"负责人",'data':null,'class':"align-center"} // 自定义列*/
            ],
            "rowCallback"  : function( row, data ) {
                if ( $.inArray(data.DT_RowId, selected) !== -1 ) {
                    $(row).addClass('selected');
                }
            },
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
    });
     //inintTable END

    $("#useId").on('change', function () {
       var oTable1 = $(".dataTables-assignTable").DataTable();
       oTable1.ajax.reload();
    });

    $(".dataTables-assignTable tbody").on('mouseover', function() {
        co = $(this).css('background-color');
        return co;
    });
    $('.dataTables-assignTable tbody').on('click', 'tr', function () {
        var id = this.id;
        console.log(id)
        var index = $.inArray(id, selected);
        if ( index === -1 ) {
            selected.push( id );
            $(this).css('background-color','yellow');
        } else {
            selected.splice( index, 1 );
            $(this).css('background-color',co);
        }
        $(this).toggleClass('selected');
    });

    // 转移所以
    $("#assignAll").on('click', function () {
        $(this).attr('disabled', 'disabled');
        var fromId = $('#useId').val();
        var toId   = $('#useId_2').val();
        var fromName = $('#useId option:selected').text();
        var toName = $('#useId_2 option:selected').text();
        if (fromId == '') {
            layer.alert('选择被转移客户的业务员');
            $("#assignAll").attr('disabled', false);
            return false;
        }
        if (toId == '') {
            layer.alert('请选择把客户转移给哪位业务员');
            $("#assignAll").attr('disabled', false);
            return false;
        }
        layer.confirm(
            '确定把' + fromName + '的所有客户都转移给' + toName + '?',
            {
                btn : ['确定','返回'],
                end : function () {
                        $("#assignAll").attr('disabled', false);
                    }
            },
            function (){
                $.ajax({
                    type : 'POST',
                    url  : controller + "/assignAll",
                    data : {
                        flag : 0,
                        fId : fromId,
                        tId : toId
                    },
                    success :function (msg) {
                        if (msg['status'] == 200) {
                            layer.msg(msg['msg'],
                                {
                                    time : 2000
                                },
                                function(){
                                    var oTable1 = $(".dataTables-assignTable").DataTable();
                                    oTable1.ajax.reload();
                                    selected = [];
                                    $("#assignAll").attr('disabled', false);
                            });
                        } else {
                            layer.msg(msg['msg']);
                            $("#assignAll").attr('disabled', false);
                        }
                    }
                });
            },
            function () {
                $("#assignAll").attr('disabled', false);
            });
    });

    // 转移选中
    $("#assignSel").on('click', function () {
     $(this).attr('disabled', 'disabled');
        var fromId = $('#useId').val();
        var toId = $('#useId_2').val();
        var fromName = $('#useId option:selected').text();
        var toName = $('#useId_2 option:selected').text();
        if (fromId == '') {
            layer.alert('选择被转移客户的业务员');
            $("#assignSel").attr('disabled', false);
            return false;
        }
         if (toId == '') {
            layer.alert('请选择把客户转移给哪位业务员');
            $("#assignSel").attr('disabled', false);
            return false;
        }
        if (selected[0] == undefined) {
            layer.alert('没有选中要迁移的客户');
            $("#assignSel").attr('disabled', false);
            return false;
        } else {
            layer.confirm('确定把选中的' + fromName + '的客户转移给' + toName + '?',
                {
                    btn : ['确定','返回'],
                    end : function () {
                        $("#assignSel").attr('disabled', false);
                    }
                },
                function () {
                    $.ajax({
                        type : 'POST',
                        url  : controller + "/assignAll",
                        data : {
                            flag:0,
                            fId : fromId,
                            tId : toId,
                            sel : selected
                        },
                        success :function (msg) {
                            if (msg.status == 200) {
                                layer.msg(msg['msg'],
                                    {
                                        time : 2000
                                    },
                                    function(){
                                        var oTable1 = $(".dataTables-assignTable").DataTable();
                                        oTable1.ajax.reload();
                                        selected.splice(0,selected.length);
                                        $("#assignSel").attr('disabled', false);
                                });
                            }else if(msg.status == 201){
                                vm.dialogTableVisible = true
                                vm.upData = msg.data.upData
                                vm.loadData = msg.data.loadData
                                layer.msg(msg.msg)
                            }else {
                                layer.msg(msg.msg);
                                $("#assignSel").attr('disabled', false);
                            }
                        }
                    });
                },
                function () {
                    $("#assignSel").attr('disabled', false);
                });
        }
    });
    // 转移
    function transfar() {
        $(this).attr('disabled', 'disabled');
        var fromId = $('#useId').val();
        var toId = $('#useId_2').val();
        $.ajax({
            type : 'POST',
            url  : controller + "/assignAll",
            data : {
                flag:1,
                fId : fromId,
                tId : toId,
                sel : selected,
                upData:vm.upData,
                loadData:vm.loadData,
            },
            success :function (data) {
                if(data.status == 200){
                    layer.msg(data.msg,
                        {
                            time : 2000
                        },
                        function(){
                            var oTable1 = $(".dataTables-assignTable").DataTable();
                            oTable1.ajax.reload();
                            selected.splice(0,selected.length);
                            $("#assignSel").attr('disabled', false);
                        });
                }
                vm.dialogTableVisible = false
                layer.msg(data.msg)
            }
        })
    }
</script>
</body>
</html>