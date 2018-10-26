<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>流程审批公示页面</title>
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
        .selected{
            background: #d2d250!important;
        }
        .btnRow{
            padding: 1em 0;
            display: flex;
            justify-content: center;
        }
        .btnRow .btn{
            margin-right: 1em;
        }
        .btnRow input{
            width: 300px!important;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h3>流程审批</h3>
                </div>
                <div class="ibox-content" style="overflow: hidden;">
                    <form class="form-inline" style="display: flex;align-items: center;">
                        <button class="btn btn-info" type="button" id="addApplication">添加新申请</button>
                    </form>
                </div>
                <div class="ibox-content" style="margin-top: 15px;">
                    <table id="table" class="table table-striped table-bordered table-hover table-full-width" width="100%">
                        <thead>
                        <tr>
                            <th>申请时间</th>
                            <th>申请人</th>
                            <th>申请标题</th>
                            <th>申请内容</th>
                            <th>当前状态</th>
                            <th>当前流程节点</th>
                            <th>流程标题</th>
                            <th>更新时间</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row" id="app">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content" style="overflow: hidden;">
                    <div v-if="recordData === ''" class="text-center h4">
                        请选择要审批的流程
                    </div>
                    <div v-else>
                        <div class="btnRow" v-show="authFlag">
                            <button class="btn btn-primary" @click="submit(1)">通过</button>
                            <button class="btn btn-warning" @click="submit(2)">驳回</button>
                            <input type="text" class="form-control" placeholder="通过/驳回理由" v-model="reason">
                        </div>
                        <table class="table table-striped table-hover table-border table-bordered">
                            <tr>
                                <th>节点名</th>
                                <th>状态</th>
                                <th>审核备注</th>
                                <th>操作人</th>
                                <th>操作时间</th>
                            </tr>
                            <tr v-for="item in recordData">
                                <td>{{item.node_name}}</td>
                                <td>{{item.operation | statusFilter}}</td>
                                <td>{{item.reason}}</td>
                                <td>{{item.record_staff}}</td>
                                <td>{{item.record_time}}</td>
                            </tr>
                        </table>
                    </div>
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
<script src="/Public/html/js/vue.js"></script>
<script>
    $.fn.dataTable.ext.errMode = 'none';
    var table = $('#table').DataTable({
       serverSide: true,
       ajax: {
           url: 'index',
           type: 'post',
           data : {
               flag : 1
           }
       },
        order:[[0,'desc']],
       columns: [
           {data: 'app_time', searchable:true},
           {data: 'app_staff',searchable:true},
           {data: 'app_title',searchable:false},
           {data: 'app_content',searchable:false},
           {data: 'app_status', searchable:false},
           {data: 'link_name', searchable:false},
           {data: 'process_name',searchable:false},
           {data: 'app_update_time',searchable:false}
       ],
        "columnDefs"   : [ //自定义列
            {
                "targets" : 4,
                "data" : "audit_status",
                "render" : function(data, type, row) {
                    var arr = ['新添加', '运行中', '完成终止'];
                    return arr[data];
                }
            },
            {
                "targets" : 3,
                "data" : "app_content",
                "render" : function(data, type, row) {
                    var html = (data.length > 20)
                        ?  '<span class="unCheck" style="color:blue;" data="' + row.app_content + '">' + row.app_content.substring(0,20) + '...</span>'
                        : '<span style="color:blue;">' + row.app_content + '</span>';
                    return html;
                }
            }
        ]
    });
    var dataTablesDiv = $("#table");
    dataTablesDiv.on('mouseenter','tbody td', function () {
        var tdIndex = $(this).parent()['context']['cellIndex'];
        if (tdIndex == 3) {
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

    dataTablesDiv.delegate('tbody td', 'mouseleave',function(e) {
        layer.closeAll('tips');
    });
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                recordData: '',
                authFlag: false,
                id: '',
                reason: ''
            }
        },
        methods: {
            submit: function (flag) {
                $.post('editApplication', {
                    processId: vm.id,
                    flag: flag,
                    reason: vm.reason
                }, function (res) {
                    if (res.status == 200){
                        table.ajax.reload();
                        $.post('', {
                            flag: 2,
                            applicationId: vmid
                        },function (res) {
                            vm.recordData = res.recordData;
                            vm.authFlag = res.authFlag;
                        })
                    }
                    layer.msg(res.msg)
                })
            }
        },
        filters: {
            statusFilter: function (v) {
                var arr = ['已提交', '已通过', '已驳回'];
                return arr[+v]
            }
        }
    });

    $('table').on('click','tr',function () {
        $('tr').removeClass('selected');
        $(this).addClass('selected');
        var id = table.row(this).data().id;
        $.post('', {
            flag: 2,
            applicationId: id
        },function (res) {
            vm.recordData = res.recordData;
            vm.authFlag = res.authFlag;
            vm.id = id;
        })
    });
    $('#addApplication').on('click',function () {
        layer.open({
            type : 2,
            title:"提交新申请",
            area: ['70%', '70%'],
            content : "addApplication",
            end : function () {
                table.ajax.reload();
            }
        });
    });

</script>
</body>
</html>