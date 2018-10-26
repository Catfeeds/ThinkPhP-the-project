<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>出入库统计</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/element-ui/2.3.4/theme-chalk/index.css" rel="stylesheet">
    <style>
        body{
            padding: 10px 0 0 10px;
        }
    </style>
</head>
<body>
<div class="wrapper wrapper-content">
    <form class="form-inline" id="app">
        <button type="button" class="btn btn-primary" @click="changeSearchType('date')" :disabled="searchType=='date'">按天搜索</button>
        <button type="button" class="btn btn-primary" @click="changeSearchType('month')" :disabled="searchType=='month'">按月搜索</button>
        <el-date-picker
                v-model="startTime"
                :type="searchType"
                value-format="timestamp"
                @change="dataTableAjaxReload"
                placeholder="选择起始时间">
        </el-date-picker>
        <el-date-picker
                v-model="endTime"
                :type="searchType"
                @change="dataTableAjaxReload"
                value-format="timestamp"
                placeholder="选择结束时间">
        </el-date-picker>
        <br>
        <button type="button" class="btn btn-primary" @click="changeViewType('date')" :disabled="viewType=='date'">按天查看</button>
        <button type="button" class="btn btn-primary" @click="changeViewType('month')" :disabled="viewType=='month'">按月查看</button>
    </form>
    <table class="table-striped table table-hover table-bordered " id="table">
        <thead>
            <tr>
                <th>日期</th>
                <th>生产线</th>
                <th>SMT线</th>
                <th>装配线</th>
                <th>装配线</th>
                <th>装配线</th>
            </tr>
        </thead>
    </table>
</div>
<script src="/Public/Admin/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/dwin/WdatePicker.js"></script>
<script src="/Public/html/js/plugins/laydate/laydate.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="https://cdn.bootcss.com/element-ui/2.3.4/index.js"></script>
<script>
    var table = $('#table').DataTable({
        serverSide: true,
        ajax: {
            type: 'post',
            data: {
                search_type: 'date',
                view_type: 'date'
            }
        },
        order:[[0,'desc']],
        columns: [
            {data: 'date',searchable:false},
            {data: 'line1',searchable:false},
            {data: 'line2',searchable:false},
            {data: 'line3',searchable:false},
            {data: 'line4',searchable:false},
            {data: 'line5',searchable:false}
        ]
    });
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                searchType: 'date',
                viewType: 'date',
                startTime: null,
                endTime: null
            }
        },
        methods: {
            changeSearchType: function (str) {
                this.searchType = str;
                this.startTime = null;
                this.endTime = null;
            },
            changeViewType: function (str) {
                this.viewType = str;
                if (str == 'month'){
                    this.changeSearchType(str)
                }
                this.dataTableAjaxReload();
            },
            dataTableAjaxReload: function () {
                table.settings()[0].ajax.data = {
                    start_time: this.startTime,
                    end_time: this.endTime,
                    search_type: this.searchType,
                    view_type: this.viewType
                };
                table.ajax.reload()
            }
        }
    });


</script>
</body>
</html>