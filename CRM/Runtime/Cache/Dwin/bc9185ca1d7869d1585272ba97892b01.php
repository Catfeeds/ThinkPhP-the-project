<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>最近业务确认订单</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style>
        .order_number{
            cursor: pointer;
            background: #f5f5f5;
        }
        .order_number :hover{
            background: #e8e8e8;
        }
    </style>
</head>
<body>
<div class="wrapper wrapper-content" id="app">
    <table class="table-bordered table table-border table-hover table-striped">
        <tr>
            <th>订单</th>
            <th>审核人姓名</th>
            <th>审核时间</th>
            <th>审核类别</th>
            <th>操作</th>
        </tr>
        <tr v-for="(row, index) in table">
            <td class="order_number" @click="open(row.saleid)">{{row.sale_number}}</td>
            <td>{{row.changemanname}}</td>
            <td>{{row.change_status_time}}</td>
            <td>{{row.status_name}}</td>
            <td>
                <button class="btn btn-warning" @click="deleteIt(index, row.id)">隐藏</button>
            </td>
        </tr>
    </table>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script>
    var table = <?php echo (json_encode($res )); ?>;

    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                table: table
            }
        },
        methods: {
            deleteIt: function (index, id) {
                var vm = this;
                $.post('', {id: id}, function (res) {
                    layer.msg(res.msg);
                    if (res.status > 0) {
                        vm.table.splice(index, 1)
                    }
                })
            },
            open: function (orderID) {
                var url = './editSaleRepairing/sid/' + orderID;
                layer.open({
                    type: 2,
                    title: '维修详情',
                    area: ['100%', '100%'],
                    content: url
                })
            }
        }
    })
</script>
</body>
</html>