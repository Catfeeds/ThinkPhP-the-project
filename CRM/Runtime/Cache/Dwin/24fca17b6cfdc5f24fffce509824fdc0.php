<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">

    <style>
        body{
            color:black;
        }
        .selected{
            background: #d0d27e!important;
        }
        .el-table thead{
            color:black!important;
        }

        .el-table td, .el-table th{
            padding-top: 2px!important;
            padding-bottom: 2px!important;
        }
        .el-pagination__jump{
            color:black!important;
        }

        .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
            padding:3px!important;
        }
        
        li.active > a{
            background-color: #1c84c6 !important;
            color: #fff !important;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="ibox float-e-margins">
        <div class="ibox-content">
            <div class="title">
                <h4>离职记录</h4>
                <div>
                    <button class="btn btn-xs btn-outline btn-success resume"><span class="glyphicon glyphicon-picture"></span>查看简历</button>
                    <button class="btn btn-xs btn-outline btn-success reinstatement"><span class="glyphicon glyphicon-picture"></span>员工复职</button>
                    <button class="btn btn-xs btn-outline btn-success addContract"><span class="glyphicon glyphicon-edit"></span>编辑信息</button>
                    <button class="btn btn-xs btn-outline btn-success print"><span class="glyphicon glyphicon-picture"></span>打印离职证明</button>
                    <button class="btn btn-xs btn-outline btn-success remove"><span class="glyphicon glyphicon-remove"></span>删除信息</button>
                    <button class="btn btn-xs btn-outline btn-success exportStaffDeparture"><span class="glyphicon glyphicon-tree-conifer"></span>导出到Excel</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="staff" class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th>职员编号</th>
                        <th>姓名</th>
                        <th>部门</th>
                        <th>职位</th>
                        <th>入职时间</th>
                        <th>离职时间</th>
                        <th>离职类型</th>
                        <th>离职原因</th>
                        <th>备注</th>
                        <th>录入人</th>
                        <th>录入时间</th>
                    </tr>

                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
        <div class="ibox-content" id="app">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">基本信息</a></li>
                <li role="presentation"><a href="#contact" aria-controls="contact" role="tab" data-toggle="tab">联系方式</a></li>
                <li role="presentation"><a href="#education" aria-controls="education" role="tab" data-toggle="tab">学历信息</a></li>
                <li role="presentation"><a href="#development" aria-controls="development" role="tab" data-toggle="tab">员工发展</a></li>
                <li role="presentation"><a href="#punish" aria-controls="punish" role="tab" data-toggle="tab">奖惩记录</a></li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped table-hover table-border">
                        <tr>
                            <th>编号</th>
                            <th>姓名</th>
                            <th>性别</th>
                            <th>学历</th>
                            <th>籍贯</th>
                            <th>民族</th>
                            <th>政治面貌</th>
                        </tr>
                        <tr>
                            <td>{{basic.employee_id}}</td>
                            <td>{{basic.name}}</td>
                            <td>{{basic.sex}}</td>
                            <td>{{basic.education}}</td>
                            <td>{{basic.birth_place}}</td>
                            <td>{{basic.nation}}</td>
                            <td>{{basic.politics_status}}</td>
                        </tr>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="contact">
                    <table class="table table-striped table-hover table-border">
                        <tr>
                            <th>电话</th>
                            <th>邮箱</th>
                            <th>家庭成员</th>
                            <th>家庭成员联系方式</th>
                        </tr>
                        <tr v-for="item in contact">
                            <td>{{item.phone}}</td>
                            <td>{{item.mail}}</td>
                            <td>{{item.member}}</td>
                            <td>{{item.contact}}</td>
                        </tr>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="education">
                    <table class="table table-striped table-hover table-border">
                        <tr>
                            <th>毕业学校</th>
                            <th>在校时间</th>
                            <th>专业</th>
                            <th>学历</th>
                            <th>外语水平</th>
                            <th>计算机水平</th>
                            <th>其他证书水平</th>
                            <th>证件上传状态</th>
                        </tr>
                        <tr v-for="item in education">
                            <td>{{item.graduate_school}}</td>
                            <td>{{item.period}}</td>
                            <td>{{item.major}}</td>
                            <td>{{item.education}}</td>
                            <td>{{item.language_level}}</td>
                            <td>{{item.computer_level}}</td>
                            <td>{{item.other_level}}</td>
                            <td>{{item.paper_upload_status}}</td>
                        </tr>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="development">
                    <el-table
                            :data="employeeDevelopment.currentData"
                            height="240"
                            border
                            style="width: 100%;font-size:10px;color:black;padding:3px 0!important;">
                        <el-table-column
                                prop="employee_id"
                                label="职员编号"
                                width="100">
                        </el-table-column>
                        <el-table-column
                                prop="record_type"
                                label="记录类别"
                                width="150">
                        </el-table-column>
                        <el-table-column
                                prop="detail_information"
                                label="详细信息">
                        </el-table-column>
                        <el-table-column
                                prop="add_name"
                                label="记录人"
                                width="80">
                        </el-table-column>
                        <el-table-column
                                prop="add_time"
                                label="记录日期"
                                width="180">
                        </el-table-column>
                    </el-table>
                    <el-pagination
                            @current-change="changeDevelopPage"
                            :page-size="employeeDevelopment.pageSize"
                            layout="prev, pager, next, jumper"
                            :total="employeeDevelopment.total">
                    </el-pagination>
                </div>
                <div role="tabpanel" class="tab-pane" id="punish">
                    <el-table
                            :data="punish.currentData"
                            height="240"
                            border
                            style="width: 100%;font-size:12px;color:black;padding:3px 0!important;">
                        <el-table-column
                                prop="employee_id"
                                label="职员编号"
                                width="100">
                        </el-table-column>
                        <el-table-column
                                prop="name"
                                label="姓名"
                                width="100">
                        </el-table-column>
                        <el-table-column
                                prop="type"
                                label="奖惩类别"
                                width="100">
                        </el-table-column>
                        <el-table-column
                                prop="record_time"
                                label="日期"
                                width="180">
                        </el-table-column>
                        <el-table-column
                                prop="reason"
                                label="事由">
                        </el-table-column>
                        <el-table-column
                                prop="fee"
                                label="罚金/奖金"
                                width="100">
                        </el-table-column>
                        <el-table-column
                                prop="score"
                                label="扣分"
                                width="100">
                        </el-table-column>
                    </el-table>
                    <el-pagination
                            @current-change="changePunishPage"
                            :page-size="punish.pageSize"
                            layout="prev, pager, next, jumper"
                            :total="punish.total">
                    </el-pagination>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/index.js"></script>

<script>
    var table = $('#staff'). DataTable({
        ajax: {
            type: 'post',
            url : '/Dwin/Admin/departureIndex',
            data: {
                flag: 1
            }
        },
        "pageLength": 25,
        serverSide: true,
        order:[[9, 'desc']],
        columns: [
            {data: 'emp_id'},
            {data: 'name'},
            {data: 'depart'},
            {data: 'posi'},
            {data: 'work_time'},
            {data: 'depart_time'},
            {data: 'departure_t'},
            {data: 'departure_r'},
            {data: 'tip'},
            {data: 'aud'},
            {data: 'update_t'}
        ],
        oLanguage: {
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
            "Paginate": {
                "sFirst": "首页",
                "sPrevious": " 上一页 ",
                "sNext": " 下一页 ",
                "sLast": " 尾页 "
            }
        }
    })
    var currentId
    var id
    var currentData
    $('tbody').on('click', 'tr', function () {
        currentData = table.row(this).data();
        currentId = currentData.emp_id;
        id = currentData.id;
        $('tr').removeClass('selected');
        $(this).addClass('selected');
        $.post(
            '/Dwin/Admin/departureIndex',
            {
                flag: 2,
                employee_id: currentId
            }, function (res) {
            vm.contact = res.data.contactData;
            vm.basic   = res.data.basicData;
            vm.education = res.data.eduData;
            vm.employeeDevelopment.allData = res.data.employeeDevelopment;
            vm.employeeDevelopment.currentData = res.data.employeeDevelopment.slice(0, vm.employeeDevelopment.pageSize);
            vm.employeeDevelopment.total = vm.employeeDevelopment.allData.length;
            vm.punish.allData = res.data.punishData;
            vm.punish.currentData = vm.punish.allData.slice(0, vm.punish.pageSize);
            vm.punish.total = vm.punish.allData.length;
        })
    });
    $('table').on('processing.dt', function () {
        currentId = undefined;
        id = undefined;
        $('tr').removeClass('selected');
    });
    $('.print').on('click', function () {
        if (id){
            window.open('departureForm?id=' + id)
        } else {
            layer.msg('请选择一行')
        }
    })
    $('tbody').on('dblclick', 'tr', function () {
        var id = table.row(this).data().employee_id
        var index = layer.open({
            type: 2,
            title: '员工信息',
            content: '/dwin/admin/editEmployee?employee_id=' + id,
            area: ['90%', '90%'],
            shadeClose:true,
            end :function () {
                table.ajax.reload( null, false );
            }
        })
    })

    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                basic: [],
                contact: [],
                education: [],
                employeeDevelopment: {
                    allData: [],
                    currentData: [],
                    pageSize: 10,
                    page: 1,
                    total: 0
                },
                punish: {
                    allData: [],
                    currentData: [],
                    pageSize: 10,
                    page: 1,
                    total: 0
                }
            }
        },
        methods: {
            changeDevelopPage: function (page) {
                this.employeeDevelopment.page = page;
                var start = (this.employeeDevelopment.page - 1) * this.employeeDevelopment.pageSize;
                var end = page * this.employeeDevelopment.pageSize;
                this.employeeDevelopment.currentData = this.employeeDevelopment.allData.slice(start, end);
            },
            changePunishPage: function (page) {
                this.punish.page = page;
                var start = (this.punish.page - 1) * this.punish.pageSize;
                var end = page * this.punish.pageSize;
                this.punish.currentData = this.punish.allData.slice(start, end);
            }
        }
    });
    // 复职
    $('.reinstatement').on('click', function () {
        var lock = false;
        if (currentId === undefined){
            layer.msg('请选择一名员工')
        } else {
            var index = layer.confirm('是否执行员工复职流程？', {
                btn: ['确认','取消'] //按钮
            }, function() {
                if (lock === false) {
                    lock = true;
                    $.ajax({
                        type : 'post',
                        url  : '/Dwin/Admin/reinstateEmployee',
                        data : {
                            employee_id : currentId
                        }, success:function (res) {
                            layer.msg(res.msg, function () {
                                table.ajax.reload();
                                layer.close(index);
                            });

                        }
                    })
                } else {
                    layer.msg('不要重复提交数据');
                }
            }, function(){
                layer.msg('ok');
            });
        }
    });


    // 查看简历
    $('.resume').on('click', function () {
        if (currentId === undefined){
            layer.msg('请选择一名员工')
        } else {
            window.open(currentData.resume)
        }
    });
    // 从Excel导出
    $('.exportStaffDeparture').on('click', function () {
        $(".exportStaffDeparture").attr('disabled', true);
        var index = layer.load('正在生成xlsx文件');
        $.post('exportStaffDeparture', {}, function (res) {
            layer.close(index);
            $(".exportStaffDeparture").attr('disabled', false);
            if (res.status == 403) {
                layer.msg(res.msg);
            } else {
                if (res.data) {
                    window.open(res.data);
                } else {
                    layer.msg(res.msg);
                }
            }
        })
    })

    // 删除
    $('.remove').on('click', function () {
        if (currentId === undefined){
            layer.msg('请选择一名员工')
        } else {
            layer.confirm('确认删除?', function (index) {
                $.post('delDeparture', {id: id}, function (res) {
                    if (res.status == 200) {
                        table.draw(true);
                    }
                    layer.msg(res.msg)
                })
                layer.close(index)
            })
        }
    })
    // 修改 
    $('.addContract').on('click', function () {
        if (currentId === undefined){
            layer.msg('请选择一名员工')
        } else {
            var index = layer.open({
                type: 2,
                title: '离职人员编辑',
                shadeClose:true,
                content: '/dwin/admin/editDeparture/id/' + id,
                area: ['100%', '80%'],
                end: function () {
                    table.draw(true);
                }
            })
        }
    })
</script>
</body>
</html>