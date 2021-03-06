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
    <link href="https://cdn.bootcss.com/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">
    <style>
       
    </style>
</head>
<body>
<div id="app" v-loading="loading">
    <h3 style="margin: 20px;" class="text-center">供应商合作信息</h3>
    <table class="table table-striped table-hover table-bordered">
        <tr>
            <th>合作机构名</th>
            <th>合作主要项目</th>
            <th>主要联系人</th>
            <th>联系人电话</th>
            <th>项目执行时间</th>
            <th>项目金额(万元)</th>
            <th>操作</th>
        </tr>
        <tr v-for="(item, index) in cooperation" v-if="flag!=='del'">
            <td>
                <el-input v-model="item.institution_name" placeholder="合作机构名"></el-input>
            </td>
            <td>
                <el-input v-model="item.main_project" placeholder="合作主要项目"></el-input>
            </td>
            <td>
                <el-input v-model="item.main_contact" placeholder="主要联系人"></el-input>
            </td>
            <td>
                <el-input v-model="item.main_phone"  placeholder="联系人电话"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input>
            </td>
            <td>
                <el-date-picker
                    v-model="item.project_exec_time"
                    type="date"
                    value-format="timestamp" 
                    format="yyyy-MM-DD"
                    format="yyyy 年 MM 月 dd 日"
                    placeholder="项目执行时间">
                </el-date-picker>
            </td>
            <td>
                <el-input v-model="item.project_amount"  placeholder="项目金额"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input>
            </td>
            <td style="width: 12%;">
                <button class="btn btn-warning" @click="delCooperation(index)" v-if="flag == 'get'">删除</button>
                <button class="btn btn-primary" @click="saveCooperation(index)" v-if="flag == 'get'">保存</button>
            </td>
        </tr>
    </table>
    <button class="btn btn-info" @click="addCooperation" style="margin-left: 50px;">新增合作信息</button>
    <button class="btn btn-info" @click="allSaveContact" style="margin-left: 50px;">保存所有数据</button>
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
<script src="https://cdn.bootcss.com/element-ui/2.3.6/index.js"></script>
<script>
    var id = '<?php echo ($id); ?>';
    var vm = new Vue({
        el: "#app",
        data: function () {
            var vm = this
            
            return {
                loading: true,
                id:id,
                num:0,
                flag:'get',
                reads:'yes',
                cooperation:[]
            }
        },
        created: function () {
            this.getData()
        },
        methods: {
            getData: function () {
                var vm = this;
                this.loading = true;
                $.post('<?php echo U("/Dwin/Purchase/getCooperation");?>', {'id' : id}, function (res) {
                    if(res.status == 200){
                        vm.loading = false
                        for(var i=0;i<res.data.length;i++){
                            res.data[i].project_exec_time = res.data[i].project_exec_time * 1000
                        }
                        vm.cooperation = res.data
                        vm.num = vm.cooperation.length
                    }
                })
            },
            delCooperation: function (index) {
                var indexs = index + 1
                var vm = this
                if(indexs > vm.num){
                    this.cooperation.splice(index,1)
                }else if(indexs <= vm.num){
                    if(this.cooperation.length > vm.num){
                        layer.msg('请先保存修改的内容或删除')
                    }else{
                        var data = {
                            'id' : id,
                            'type':'cooperation',
                            'data' : this.cooperation[index]
                        }
                        layer.confirm('确认删除?', function (aaa) {
                            $.post('<?php echo U("/Dwin/Purchase/delSupplierOtherMsg");?>', data, function (res) {
                                if (res.status == 200) {
                                    vm.getData()
                                    location.reload();
                                }
                                layer.msg(res.msg)
                            })
                        })
                    }
                }
            },
            // 提交数据 保存
            saveCooperation: function (index) {
                var vm = this
                this.cooperation[index].project_exec_time = this.cooperation[index].project_exec_time / 1000
                var data = {
                    'id' : id,
                    'type':'cooperation',
                    'data' : this.cooperation[index]
                }
                $.post('<?php echo U("/Dwin/Purchase/editOrAddSupplierOneMsg");?>', data, function (res) {
                    if(res.status == 200){
                        vm.getData();
                        location.reload();
                    }
                    layer.msg(res.msg)
                })  
            },
            // 新增一行空数据
            addCooperation: function () {
                // 判断是否重复新增
                if(this.cooperation[this.cooperation.length - 1] != undefined){
                    if(this.cooperation[this.cooperation.length - 1].institution_name){
                        var obj = {}
                        this.cooperation.push(obj)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    var obj = {}
                    this.cooperation.push(obj)
                }
                
            },
            // 提交所有数据
            allSaveContact () {
                var vm = this
                // 修改的数据
                var allAmend = this.cooperation.slice(0,vm.num)
                for (var i = 0;i<allAmend.length;i++) {
                    allAmend[i].project_exec_time = allAmend[i].project_exec_time / 1000
                }
                // 新增的数据
                var allAdd = this.cooperation.slice(vm.num)
                for (var i = 0;i<allAdd.length;i++) {
                    allAdd[i].project_exec_time =  allAdd[i].project_exec_time / 1000
                }
                var data = []
                var params = {
                    'id' : id,
                    'type':'cooperation',
                    'editData': allAmend,
                    'addData': allAdd
                }
                $.post('<?php echo U("/Dwin/Purchase/editSupplierMsg");?>', params, function (res) {
                    if(res.status == 200){
                        vm.getData();
                    }
                    layer.msg(res.msg)
                })  
            }
        }
    })
</script>
</body>
</html>