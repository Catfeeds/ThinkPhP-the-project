<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>奖励公示</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.0/animate.min.css" rel="stylesheet">
    <!--<link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">-->
    <!--<link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">-->
    <!--<link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">-->
    <!--<link href="/Public/html/css/animate.min.css" rel="stylesheet">-->
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">

    <style>
        body{
            color:black;
        }

    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="ibox float-e-margins">
        <div class="ibox-content">
            <div class="title">
                <h4>奖励公示</h4>
            </div>
            <div class="table-responsive">
                <table id="staff" class="table table-bordered table-hover table-striped">
                    <thead>
                    <th>员工编号</th>
                    <th>员工姓名</th>
                    <th>记录时间</th>
                    <th>奖励事由</th>
                    <th>奖金</th>
                    <th>分数</th>
                    <th>填表人</th>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.1.0/jquery.form.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/3.3.6/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/dataTables.bootstrap.min.js"></script>
<!--<script src="/Public/html/js/jquery-1.11.3.min.js"></script>-->
<!--<script src="/Public/html/js/vue.js"></script>-->
<!--<script src="/Public/html/js/jquery.form.js"></script>-->
<!--<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>-->
<!--<script src="/Public/html/js/plugins/jeditable/jquery.jeditable.js"></script>-->
<!--<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>-->
<!--<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>-->
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/index.js"></script>
<script>
    var table = $('#staff'). DataTable({
        ajax: {
            type: 'post'
        },
        serverSide: true,
        order:[[2, 'desc']],
        columns: [
            {data: 'employee_id'},
            {data: 'name'},
            {data: 'record_time'},
            {data: 'reason'},
            {data: 'fee'},
            {data: 'score'},
            {data: 'add_name'}
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

</script>
</body>
</html>