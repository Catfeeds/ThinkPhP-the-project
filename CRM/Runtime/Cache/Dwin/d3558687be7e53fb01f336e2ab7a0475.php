<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPI客户</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">
    <link rel="stylesheet" href="/Public/Dwin/Customer/css/getKpiCus.css">
</head>
<body class="gray-bg">
<div id="app">
    <header>
        <div class="myRow">
            <label>筛选条件</label>
            <el-badge :value="auditNum" class="btn btn-primary">
                <button class="btn btn-primary" id="auditKpiCus">审核KPI客户</button>
            </el-badge>
        </div>
        <div class="myRow">
            <el-switch
                    v-model="where.hasChild"
                    active-text="下属客户"
                    inactive-text="个人客户"
                    @change="getData"
            >
            </el-switch>
            <el-select v-model="where.staff" filterable placeholder="选择业务员"  @change="getData">
                <el-option
                        v-for="item in selectData.staff"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id">
                </el-option>
            </el-select>
            <el-select v-model="where.potential" filterable placeholder="选择潜在年用量"  @change="getData">
                <el-option
                        v-for="item in selectData.potential"
                        :key="item"
                        :label="item"
                        :value="item">
                </el-option>
            </el-select>
            <el-select v-model="where.industry" filterable placeholder="客户行业" @change="getData">
                <el-option
                        v-for="item in selectData.industry"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id">
                </el-option>
            </el-select>
        </div>
    </header>
    <main>
        <el-table
                :data="tableData"
                v-loading="loading"
                border
                stripe
                style="width: 100%"
                @cell-click="showDetail"
        >
            <el-table-column
                    class-name="cname"
                    prop="cname"
                    label="客户名"
            >
            </el-table-column>
            <el-table-column
                    prop="annual_order_amount"
                    label="年业绩">
            </el-table-column>
            <el-table-column
                    prop="kpi_background"
                    label="行业背景">
            </el-table-column>
            <el-table-column
                    prop="kpi_application"
                    label="产品应用">
            </el-table-column>
            <el-table-column
                    prop="kpi_potential"
                    label="潜力">
            </el-table-column>
            <el-table-column
                    prop="kpi_auth_tip"
                    label="上级审核批注">
            </el-table-column>
            <el-table-column
                    prop="staff"
                    label="负责人">
            </el-table-column>

        </el-table>
        <el-pagination
                @size-change="changePage"
                @current-change="changePage"
                :current-page="+page.page"
                :page-size="10"
                layout="total, pager, jumper"
                :total="+page.total">
        </el-pagination>
    </main>
</div>
<script src="/Public/Admin/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/index.js"></script>
<script>
var vm = new Vue({
    el: '#app',
    data: function () {
        return {
            where: {
                hasChild: false,
                staff: '',
                potential: '',
                industry: '',
                cname: ''
            },
            page: {
                page: 1,
                pageSize: 10,
                total: 0
            },
            tableData: [],
            selectData: {
                staff: [],
                industry: [],
                potential: ['1-5万台', '5-10万台', '10万台以上']
            },
            loading: true,
            auditNum: 0
        }
    },
    created: function () {
        var vm = this;
        $.post('', {flag: 'getSelectInfo'}, function (res) {
            vm.selectData = res
        });
        this.getData()
    },
    methods: {
        getData: function () {
            this.loading = true;
            var vm = this;
            $.post('', {where: vm.where, page: vm.page}, function (res) {
                vm.tableData = res.data;
                vm.page = res.page;
                vm.auditNum = res.auditNum;
                vm.loading = false;
            })
        },
        changePage: function (page) {
            this.page.page = page;
            this.getData();
        },
        showDetail: function (row) {
            layer.open({
                type: 2,
                title: "",
                area : ['90%', '90%'],
                content: "showBusinessDetail/cusId/" + row.cid,
                end: function () {
                    vm.getData()
                }
            });
        }
    }
})
    $('#auditKpiCus').on('click', function () {
        layer.open({
            type: 2,
            title: "",
            area : ['90%', '90%'],
            content: "<?php echo U('auditKpiCus');?>",
            end: function () {
                vm.getData()
            }
        });
    })
</script>
</body>
</html>