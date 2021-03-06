<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>生产线列表-数据表格</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;;
        }
        .grade{
            width: 80% !important;
        }
        li{
            height: 35px;
            border-bottom: 1px solid #e7e7e7;
            line-height: 35px;
        }
        ul{
            margin: 0 !important;
        }
        .float-e-margins{
            background-color: #fff;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins col-sm-8">
                <div class="ibox-title">
                    <h5>生产线列表</h5>
                    <!-- <a style="float: right;" href="javascript:;" id="addDept"><i class="fa fa-plus"></i> 添加生产线</a> -->
                </div>
                <div class="ibox-content" id='app' style="height:100%">
                    <ul style="list-style-type:none;padding: 0;border: none">
                        <li style="width:30%;float:left"><b>产线分组名</b></li>
                        <li style="width:25%;float:left;text-align: center"><b>负责人</b></li>
                        <li style="width:25%;float:left;text-align: center"><b>产能</b></li>
                        <li style="text-align:center">
                            修改|删除
                        </li>
                    </ul>
                    <div v-for="(item, index) in tableData" :key="index">
                        <ul style="list-style-type:none;padding: 0  ">
                            <li style="width:30%;float:left">{{item.production_line}}</li>
                            <li style="width:25%;float:left;text-align: center">{{item.responsible_name}}</li>
                            <li style="width:25%;float:left;text-align: center">{{item.manufacturability}}</li>
                            <li v-for="(items, index) in item.child_line_data" :key="index">
                                <ul style="list-style-type:none">
                                    <li style="width:30%;float:left">{{items.production_line}}</li>
                                    <li style="width:25%;float:left;text-align: center">{{items.responsible_name}}</li>
                                    <li style="width:25%;float:left;text-align: center">{{items.manufacturability}}</li>
                                    <li style="width:20%;float:left;text-align:center;border-left: 1px solid #e7e7e7;">
                                        <a class="edit" @click="editProduction(items)"><i class="fa fa-pencil-square-o"></i></a>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a class="delete" @click="deleteProduction(items)"><i class="fa fa-trash"></i></a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <el-dialog title="生产线编辑：" :visible.sync="dialogFormVisible" style="margin-top:0" :modal-append-to-body="false">
                        <el-form :model="form"  label-width="100px">
                            <el-row>
                                <el-col :span="9" :offset="2">
                                    <el-form-item label="产线名：">
                                        <el-input v-model="form.production_line" auto-complete></el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="9" :offset="2">
                                    <el-form-item label="日产能：">
                                        <el-input v-model="form.manufacturability" auto-complete></el-input>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                            <el-row :offset="1">
                                <el-col :span="9" :offset="2">
                                    <el-form-item label="负责人：">
                                            <el-select v-model="form.responsible_id" placeholder="请选择">
                                                <el-option
                                                    v-for="item in staffMap"
                                                    :key="item.id"
                                                    :label="item.name"
                                                    :value="item.id">
                                                </el-option>
                                            </el-select>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </el-form>
                        <div slot="footer" class="dialog-footer" style="text-align:center">
                            <el-button @click="dialogFormVisible = false">取 消</el-button>
                            <el-button type="primary" @click="dialogFormVisible_BUT(form)">保 存</el-button>
                        </div>
                    </el-dialog>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/index.js"></script>
<script>
var staffMap = <?php echo (json_encode($staffMap)); ?>;
var vm = new Vue({
    el: '#app',
    data: function () {
        return {
            dialogFormVisible:false,
            tableData:{},
            staffMap:staffMap,
            form:{}
        }
    },
    created() {
        this.getData()
    },
    methods:{
        getData () {
            $.post('/Dwin/production/productionLineList', {} , function (res) {
                if(res.status == 200){
                    vm.tableData = res.data
                }else{
                    layer.msg(res.msg)
                }
            })
        },
        deleteProduction(item){
            if(item){
                $.post('/Dwin/Production/delProductionLine', {id:item.id} , function (res) {
                    if(res.status == 200){
                        vm.getData()
                    }
                    layer.msg(res.msg)
                })
            }
        },
        editProduction(item){
            if(item){
                vm.form = item
                vm.dialogFormVisible = true
            }
        },
        dialogFormVisible_BUT(form){
            $.post('/Dwin/Production/editProductionLine', form , function (res) {
                if(res.status == 200){
                    vm.dialogFormVisible = false
                    vm.getData()
                }
                layer.msg(res.msg)
            })
        }
    }
})
</script>
</body>
</html>