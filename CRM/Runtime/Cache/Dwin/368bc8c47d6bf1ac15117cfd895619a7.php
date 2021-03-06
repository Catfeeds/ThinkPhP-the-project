<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.4/theme-chalk/index.css" rel="stylesheet">

    <style>
        body{
            padding: 20px 0 0 30px;
        }
        .question{
            font-weight: bold;
            padding: 10px 0;
        }
        .answer{
            margin-bottom: 5px;
        }
        .form-control{
            width: 200px;
        }
        .detail{
            border: 1px solid #ccc;
            padding: 15px;
        }

    </style>
</head>
<body>
<main id="app">
    <div style="display: flex;align-items: center;margin-bottom: 20px;">
        <label style="margin-right: 10px;">选择时间批次</label>
        <select class="form-control" v-model="timeBatch" @change="changeTimeBatch">
            <option value="" hidden>可选择时间批次</option>
            <option value="all">全部时间</option>
            <option :value="item.timestamp" v-for="item in timeBatchData">{{item.time}}</option>
        </select>
    </div>
    <div class="fanxiu">
        <div>有二次返修的客户在当前数据中的的比例: {{dataTable.ratio}}
            <button class="btn btn-info" style="display:inline-block" @click="checkHasQ4">点击查看</button></div>
            <button type="button" style="display:inline-block" class="btn btn-info" @click="exportData">点击导出</button>
    </div>
    <el-table
            v-loading="loading"
            :data="dataTable.data"
            style="width: 100%"
            @sort-change="sortChange"
            @cell-click="clickRow"
    >
        <el-table-column
                prop="cus_name"
                label="客户"
                sortable="'custom'"
                >
        </el-table-column>
        <el-table-column
                prop="u_name"
                label="客服负责人"
                sortable="'custom'"
                >
        </el-table-column>
        <el-table-column
                prop="assign_time"
                :formatter="timeFormatter"
                label="分配时间"
                sortable="'custom'"
        >
        </el-table-column>
        <el-table-column
                prop="question_4flag"
                label="是否有二次返修情况"
                sortable="'custom'"
        >
        </el-table-column>
        <el-table-column
                prop="question_5flag"
                label="备注"
                sortable="'custom'"
        >
        </el-table-column>
    </el-table>
    <div>
        <el-pagination
                :current-page="dataTable.page"
                :page-sizes="[10, 20, 30]"
                :page-size="dataTable.pageSize"
                layout="total, sizes, pager, jumper"
                :total="+dataTable.total"
                @current-change="changePage"
                @size-change="changePageSize"
        >
        </el-pagination>
    </div>
</main>
<script src="/Public/Admin/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.4/index.js"></script>

<script>
    var timeBatchData = <?php echo (json_encode($timeBatch )); ?>;
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                callback: [],
                detail: '',
                showDetail: false,
                timeBatchData: timeBatchData,
                timeBatch: '',
                showDoubleBack: false,
                dataTable: {
                    order: ['assign_time', 'desc'],
                    page: 1,
                    pageSize: 10,
                    total: 0,
                    where: {}
                },
                loading: true
            }
        },
        methods: {
            getInfo: function () {
                this.loading = true;
                var vm = this;
                $.post('', vm.dataTable, function (res) {
                    vm.dataTable = Object.assign(vm.dataTable, res);
                    vm.loading = false;
                    vm.dataTable.flag = null
                })
            },
            changePage: function (page) {
                this.dataTable.page = page;
                this.getInfo()
            },
            changePageSize: function (size) {
                this.dataTable.pageSize = size;
                this.getInfo()
            },
            sortChange: function (col) {
                if (col.prop == null){
                    return false
                }
                var arr = [col.prop];
                if (col.order[0] === 'd'){
                    arr.push('desc')
                } else {
                    arr.push('asc')
                }
                this.dataTable.order = arr;
                this.getInfo()
            },
            clickRow: function (row, col, cell) {
                this.detail = row;
                this.showDetail = true
            },
            changeTimeBatch: function () {
                this.dataTable.where = {
                    assign_time: ['EQ', this.timeBatch]
                };
                if (this.timeBatch === 'all'){
                    this.dataTable.where = [];
                    this.dataTable.page = 1;
                }
                this.getInfo()
            },
            checkHasQ4: function () {
                this.dataTable.flag = 1;
                this.dataTable.page = 1;
                this.getInfo()
            },
            exportData: function () {
                var vm = this;
                $.post('exportCallback', vm.dataTable, function (res) {
                    if (res.status == 200) {
                        var index = layer.confirm("生成完毕，确认下载？",{
                            btn:['是','否']
                        }, function () {
                            layer.close(index);
                            window.open(res.data.file_url,'_blank');
                        }, function () {
                            layer.close(index);
                        })
                    }
                    console.log(res);
                })
            },
            timeFormatter: function (row,col,input) {
                var d = new Date(input*1000);
                var year = d.getFullYear();
                var month = d.getMonth() + 1;
                var day = d.getDate() <10 ? '0' + d.getDate() : '' + d.getDate();
                var hour = d.getHours();
                var minutes = d.getMinutes();
                var seconds = d.getSeconds();
                return  year+ '-' + month + '-' + day + ' ' + hour + ':' + minutes + ':' + seconds;
            }
        },
        created: function () {
            this.getInfo();
        },
        filters: {
            date: function (input) {
                var d = new Date(input*1000);
                var year = d.getFullYear();
                var month = d.getMonth() + 1;
                var day = d.getDate() <10 ? '0' + d.getDate() : '' + d.getDate();
                var hour = d.getHours();
                var minutes = d.getMinutes();
                var seconds = d.getSeconds();
                return  year+ '-' + month + '-' + day + ' ' + hour + ':' + minutes + ':' + seconds;
            }
        }
    })
</script>
</body>
</html>