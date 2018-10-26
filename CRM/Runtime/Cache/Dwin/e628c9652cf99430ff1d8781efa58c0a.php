<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>账户管理</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="ibox float-e-margins">
        <div class="ibox-content">
            <div class="title">
                <h4>账户管理</h4>
            </div>
            <div class="table-responsive">
                <table id="staff" class="table table-bordered table-hover table-striped">
                    <thead>
                    <th>姓名</th>
                    <th>电话</th>
                    <th>密码错误次数</th>
                    <th>账号状态</th>
                    <th>操作</th>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
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
            type: 'post'
        },
        serverSide: true,
        pageLength: 15,
        order:[[3, 'desc']],
        columns: [
            {data: 'name'},
            {data: 'phone'},
            {data: 'errorcount'},
            {data: 'loginstatus'},
            {data: null, render: function (a,b,row) {
                if (row.loginstatus == 3){
                    return '<button class="btn btn-info unlock">解锁</button>'
                }
                return ''
                }}

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

    $('#staff').on('click', '.unlock', function () {
        var id = table.row($(this).parents('tr')).data().id

        layer.confirm('确认解锁并清零错误次数?', function (index) {
            $.post('unlockStaffAccount', {id: id}, function (res) {
                layer.msg(res.msg)
                table.ajax.reload()
            })
            layer.close(index)
        })
    })
</script>
</body>
</html>