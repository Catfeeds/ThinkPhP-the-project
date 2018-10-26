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
            padding: 20px;
        }
    </style>
</head>
<body>
<div id="app" v-loading="loading">
    <table class="table table-bordered table-striped table-hover">
        <tr>
            <th>职员编号</th>
            <th>姓名</th>
            <th>入职时间</th>
        </tr>
        <tr>
            <td>{{staffInfo.employee_id}}</td>
            <td>{{staffInfo.name}}</td>
            <td>{{staffInfo.entry_time}}</td>
        </tr>
    </table>
    <el-form ref="form" :rules="rules" :model="form" label-width="120px" :inline="true" @submit.native.prevent>
        <el-row>
            <el-col :span="10">
                <el-form-item label="异动类型" prop="change_type">
                    <el-select v-model="form.change_type" placeholder="请选择">
                        <el-option
                                :key="1"
                                :label="'部门异动'"
                                :value="'部门异动'">
                        </el-option>
                        <el-option
                                :key="2"
                                :label="'职位异动'"
                                :value="'职位异动'">
                        </el-option>
                        <el-option
                                :key="3"
                                :label="'薪资异动'"
                                :value="'薪资异动'">
                        </el-option>
                        <el-option
                                :key="5"
                                :label="'试用期转正'"
                                :value="'试用期转正'">
                        </el-option>
                        <el-option
                                :key="4"
                                :label="'其他'"
                                :value="'其他'">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col :span="10">
                <el-form-item label="异动日期" prop="change_time">
                    <el-date-picker
                            v-model="form.change_time"
                            type="date"
                            value-format="timestamp"
                            placeholder="选择日期">
                    </el-date-picker>
                </el-form-item>
            </el-col>
        </el-row>
        <el-row>
            <el-col :span="10">
                <el-form-item label="异动前部门">
                    <el-select v-model="form.change_old_dept" filterable placeholder="请选择">
                        <el-option
                                v-for="item in selectInfo.dept"
                                :key="item.id"
                                :label="item.name"
                                :value="item.name">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col :span="10">
                <el-form-item label="异动后部门">
                    <el-select v-model="form.change_new_dept" filterable placeholder="请选择">
                        <el-option
                                v-for="item in selectInfo.dept"
                                :key="item.id"
                                :label="item.name"
                                :value="item.name">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
        </el-row>
        <el-row>
            <el-col :span="10">
                <el-form-item label="异动前职位">
                    <el-select v-model="form.change_old_position" filterable placeholder="请选择">
                        <el-option
                                v-for="item in selectInfo.postInfo"
                                :key="item.role_id"
                                :label="item.name"
                                :value="item.name">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col :span="10">
                <el-form-item label="异动后职位">
                    <el-select v-model="form.change_new_position" filterable allow-create placeholder="请选择">
                        <el-option
                                v-for="item in selectInfo.postInfo"
                                :key="item.role_id"
                                :label="item.name"
                                :value="item.name">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
        </el-row>
        <el-row>
            <el-col :span="10">
                <el-form-item label="异动前薪资">
                    <el-input v-model="form.change_old_salary"></el-input>
                </el-form-item>
            </el-col>
            <el-col :span="10">
                <el-form-item label="异动后薪资">
                    <el-input v-model="form.change_new_salary"></el-input>
                </el-form-item>
            </el-col>
        </el-row>
        <el-row>
            <el-col :span="10">
                <el-form-item label="正式执行日期" prop="exec_time">
                    <el-date-picker
                            v-model="form.exec_time"
                            type="date"
                            value-format="timestamp"
                            placeholder="选择日期">
                    </el-date-picker>
                </el-form-item>
            </el-col>
            <el-col :span="10">
                <el-form-item label="备注">
                    <el-input autosize  v-model="form.tips"></el-input>
                </el-form-item>
            </el-col>
        </el-row>
        <el-form-item>
            <el-button type="primary" @click="submit">提交</el-button>
        </el-form-item>
    </el-form>
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
    var staffInfo = <?php echo (json_encode($staffInfo)); ?>;
    var selectInfo = <?php echo (json_encode($selectInfo)); ?>;
    var vm = new Vue({
        el: "#app",
        data: function () {
            return {
                form: {},
                staffInfo: staffInfo,
                rules: {
                    change_type:[{required: true, message:'请选择',trigger: 'blur'}],
                    change_time:[{required: true, message:'请选择',trigger: 'blur'}],
                    exec_time:[{required: true, message:'请选择',trigger: 'blur'}]
                },
                selectInfo: selectInfo,
                loading: false
            }
        },
        created: function(){
            console.log(staffInfo.department)
            Vue.set(this.form, 'change_old_dept', staffInfo.department)
            Vue.set(this.form, 'change_new_dept', staffInfo.department)
            Vue.set(this.form, 'change_old_position', staffInfo.position)
            Vue.set(this.form, 'change_new_position', staffInfo.position)
        },
        methods: {
            submit: function () {
                var vm = this;
                this.$refs['form'].validate(function (res) {
                    if (res){
                        layer.confirm('确认提交?', function (index) {
                            vm.loading = true
                            Object.assign(vm.form, vm.staffInfo)
                            $.post('', {data: vm.form}, function (res) {
                                layer.msg(res.msg)
                                if (res.status == 200){
                                    var index = parent.layer.getFrameIndex(window.name);
                                    parent.layer.close(index);
                                }
                                vm.loading = false
                            })
                        })
                    }else {
                        layer.msg('有未填项')
                        vm.loading = false
                    }
                })
            }
        }
    })
</script>
</body>
</html>