<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
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
            padding: 20px 50px 20px;
        }
        .el-select{
            width: 280px;
        }
    </style>
</head>
<body>
<div id="app">
    <h3 style="margin: 20px;" class="text-center"><?php echo ($title); ?></h3>
    <el-form ref="form" :model="form" label-width="100px" @submit.native.prevent v-loading="loading" :rules="rules">
        <el-form-item label="生产单号" v-if="!hideProductionOrder" prop="production_order">
            <el-input  v-model="form.production_order" :disabled="editLvl != 0"></el-input>
        </el-form-item>
        <el-form-item label="投诉事由" prop="complain_reason" v-if="editLvl >= 0">
            <el-input rows="5" v-model="form.complain_reason" :disabled="editLvl != 0" type="textarea"></el-input>
        </el-form-item>
        <el-form-item label="调查人" v-if="editLvl == 3" prop="handler">
            <el-select v-model="form.handler" placeholder="请选择">
                <el-option
                        v-for="item in auditorList"
                        :key="item.name"
                        :label="item.name"
                        :value="item.name">
                </el-option>
            </el-select>
        </el-form-item>

        <el-form-item label="责任人" prop="liable" v-if="editLvl == 1">
            <el-select v-model="form.liable" filterable multiple placeholder="请选择">
                <el-option v-for="item in liableMap" :key="item.id" :label="item.name" :value="item.id"></el-option>
            </el-select>
        </el-form-item>

        <el-form-item label="原因调查" prop="research" v-if="editLvl >= 1">
            <el-input autosize  v-model="form.research" type="textarea" :disabled="editLvl != 1" ></el-input>
        </el-form-item>
        <el-form-item label="处理措施" prop="processes" v-if="editLvl >= 1">
            <el-input autosize  v-model="form.processes" type="textarea" :disabled="editLvl != 1" ></el-input>
        </el-form-item>
        <el-row v-if="editLvl >= 1">
            <el-col :span="10" :offset="4">
                <el-form-item label="调查人">
                    {{form.handler}}
                </el-form-item>
            </el-col>
            <el-col :span="10">
                <el-form-item label="日期">
                    {{form.research_time}}
                </el-form-item>
            </el-col>
        </el-row>
        <el-form-item label="监督人" v-if="editLvl == 1" prop="auditor">
            <el-select v-model="form.auditor" placeholder="请选择">
                <el-option
                        v-for="item in auditorList"
                        :key="item.name"
                        :label="item.name"
                        :value="item.name">
                </el-option>
            </el-select>
        </el-form-item>
        <el-form-item label="措施有效性" prop="processes_validity" v-if="editLvl >= 2">
            <el-input autosize  v-model="form.processes_validity" :disabled="editLvl != 2" type="textarea" ></el-input>
        </el-form-item>
        <el-row v-if="editLvl >= 2">

            <el-col :span="10" :offset="4">
                <el-form-item label="监督人">
                    {{form.auditor}}
                </el-form-item>
            </el-col>
            <el-col :span="10">
                <el-form-item label="日期">
                    {{form.process_time}}
                </el-form-item>
            </el-col>
        </el-row>
        <div class="text-center">
            <el-button type="primary" @click="submitForm">提交</el-button>
        </div>
    </el-form>
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
    var productionOrder = '<?php echo ($productionOrder); ?>';
    var hideProductionOrder = '<?php echo ($hideProductionOrder); ?>' === '1' ? true : false;
    var form = <?php echo ($form); ?> == null ? null : <?php echo ($form); ?>;
    var editLvl = <?php echo ($editLvl); ?>;
    var vm = new Vue({
        el: '#app',
        data: function () {
            return{
                rules: {
                    complain_reason: [{required: true, message: '请输入', trigger: 'blur'}],
                    research: [{required: true, message: '请输入', trigger: 'blur'}],
                    processes: [{required: true, message: '请输入', trigger: 'blur'}],
                    processes_validity: [{required: true, message: '请输入', trigger: 'blur'}],
                    auditor: [{required: true, message: '请选择', trigger: 'change'}],
                    handler: [{required: true, message: '请选择', trigger: 'change'}],
//                    liable: [{required: true, message: '请选择', trigger: 'change'}]
                },
                form: {},
                editLvl: editLvl,
                loading: false,
                hideProductionOrder: hideProductionOrder,
                auditorList: [],
                liableModel : "liableModel",
                liableMap : [],
            }
        },
        created: function () {
            $.post('<?php echo U("getIsStaffMsg");?>', {} , function (res) {
                vm.liableMap = res.data
            })
            if (productionOrder != ''){
                Vue.set(this.form, 'production_order', productionOrder)
            }
            if (form != null){
                this.form = form
            }else {
                Vue.set(this.form, 'status', 0)
            }
            var vm = this
            this.loading = true
            $.post('<?php echo U("getComplainAuditor");?>', {} , function (res) {
                vm.loading = false
                vm.auditorList = res.data
            })
        },
        methods: {
            submitForm: function () {
                var vm = this
                this.$refs['form'].validate(function (res) {
                    if (!res){
                        layer.msg('有空的必填项')
                        return false
                    } else {
                        layer.confirm('确认提交? ', function (index) {
                            $.post('', {data: vm.form}, function (res) {
                                if (res.status == 200){
                                    layer.msg(res.msg, function () {
                                        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                        parent.layer.close(index);
                                    })
                                }else {
                                    layer.msg(res.msg)
                                }
                            })
                            layer.close(index)
                        })
                    }
                });

            }
        }
    })
</script>
</body>
</html>