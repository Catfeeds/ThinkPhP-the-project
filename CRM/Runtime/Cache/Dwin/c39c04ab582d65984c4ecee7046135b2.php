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
            margin: 50px 0 0 50px;
        }
    </style>
</head>
<body>
<main id="app" v-loading="loading">
<el-row>
    <el-form>
        <el-form-item label="选择时间段">

            <el-date-picker
                    v-model="timeRange"
                    type="daterange"
                    range-separator="至"
                    start-placeholder="开始日期"
                    end-placeholder="结束日期"
                    @change="timeRangeChange"
            >
            </el-date-picker>
        </el-form-item>
    </el-form>
</el-row>
    <el-table
            border
            stripe
            :data="tableData"
            style="width: 100%">
        <el-table-column type="expand">
            <template slot-scope="detail">
                <el-table
                        border
                        stripe
                :data="detail.row.detail"
                >
                    <el-table-column type="expand">
                        <template slot-scope="detail">
                            <el-table
                                    border
                                    stripe
                                    :data="detail.row.records"
                            >
                                <el-table-column
                                        label="主题"
                                        prop="theme">
                                </el-table-column>
                                <el-table-column
                                        label="内容">
                                    <template slot-scope="item">
                                        <el-popover
                                                placement="top"
                                                width="400"
                                                trigger="hover">
                                            <el-row v-html="replaceBrFilter(item.row.content)"></el-row>
                                            <el-row slot="reference">{{item.row.content.length < 20 ? item.row.content : item.row.content.slice(0, 20) + '...'}}</el-row>
                                        </el-popover>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        label="时间"
                                        width="100"
                                        prop="ctime">
                                </el-table-column>
                            </el-table>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="客户"
                            prop="cname">
                    </el-table-column>
                    <el-table-column
                            label="行业"
                            prop="industry_name">
                    </el-table-column>
                    <el-table-column
                            label="负责人"
                            width="100"
                            prop="staff_name">
                    </el-table-column>
                    <el-table-column
                            label="联系次数"
                            width="50"
                            prop="contact_count">
                    </el-table-column>
                </el-table>
            </template>
        </el-table-column>
        <el-table-column
                label="联系人员"
                prop="name">
        </el-table-column>
        <el-table-column
                label="联系次数"
                prop="contactcount">
        </el-table-column>
        <el-table-column
                label="联系客户数"
                prop="cuscount">
        </el-table-column>
    </el-table>
    <el-pagination
            layout="prev, pager, next"
            :total="+total">
    </el-pagination>
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
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                loading: false,
                timeRange: [],
                tableData: [],
                where: {
                    page: 0
                },
                total: 0
            }
        },
        created: function (){
            this.getInfo()
        },
        methods: {
            getInfo: function () {
                var vm = this
                this.loading = true
                $.post('', {where: this.where}, function (res) {
                    console.log(res);
                    vm.tableData = res.data
                    vm.total = res.total
                    vm.loading = false
                })
            },
            timeRangeChange: function () {
                this.where.startTime = this.timeRange && this.timeRange[0].valueOf() / 1000
                this.where.endTime = this.timeRange && this.timeRange[1].valueOf() / 1000
                this.getInfo()
            },
            replaceBrFilter: function (v) {
                if (v){
                    return v.replace(/\r\n|\n/g, '<br>')
                }
            }
        }
    })
</script>
</body>
</html>