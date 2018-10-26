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
    <h3 style="margin: 20px;" class="text-center">供应商客户信息</h3>
    <table class="table table-striped table-hover table-bordered">
        <tr>
            <th>客户名</th>
            <th>主要项目</th>
            <th>项目主要联系人</th>
            <th>项目联系人电话</th>
            <th>项目执行时间</th>
            <th>项目金额(万元)</th>
            <th>操作</th>
        </tr>
        <tr v-for="(item, index) in customer" v-if="flag!=='del'">
            <td>
                <el-input v-model="item.cus_name" placeholder="客户名"></el-input>
            </td>
            <td>
                <el-input v-model="item.main_project" placeholder="主要项目"></el-input>
            </td>
            <td  style="width:200px;">
                <el-input v-model="item.main_contact" placeholder="项目主要联系人"></el-input>
            </td>
            <td  style="width:160px;">
                <el-input v-model="item.main_phone" placeholder="项目联系人电话"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46"  ></el-input>
            </td>
            <td style="width:130px;">
                <el-date-picker
                    v-model="item.project_exec_time"
                    type="date"
                    value-format="timestamp" 
                    format="yyyy 年 MM 月 dd 日"
                    placeholder="项目执行时间">
                </el-date-picker>
            </td>
            <td  style="width: 130px;">
                <el-input v-model="item.project_amount"  placeholder="项目金额(万元)"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46"  ></el-input>
            </td>
            <td style="width: 130px;">
                <button class="btn btn-warning" @click="delCustomer(index)" v-if="flag == 'get'">删除</button>
                <button class="btn btn-primary" @click="saveCustomer(index)" v-if="flag == 'get'">保存</button>
            </td>
        </tr>
    </table>
    <button class="btn btn-info" @click="addCustomer" style="margin-left: 50px;">新增客户信息</button>
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
                customer:[]
            }
        },
        created: function () {
            this.getData()
        },
        methods: {
            getData: function () {
                var vm = this;
                this.loading = true;
                $.post('<?php echo U("/Dwin/Purchase/getCustomer");?>', {'id' : id}, function (res) {
                    if(res.status == 200){
                        vm.loading = false
                        for(var i=0;i<res.data.length;i++){
                            res.data[i].project_exec_time =  res.data[i].project_exec_time * 1000
                        }
                        vm.customer = res.data
                        vm.num = vm.customer.length
                    }
                })
            },
            // 单行删除
            delCustomer: function (index) {
                var indexs = index + 1
                var vm = this
                if(indexs > vm.num){
                    this.customer.splice(index,1)
                }else if(indexs <= vm.num){
                    if(this.customer.length > vm.num){
                        layer.msg('请先保存修改的内容或删除')
                    }else{
                        var data = {
                            'id' : id,
                            'type':'customer',
                            'data' : this.customer[index]
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
            saveCustomer: function (index) {
                var vm = this
                this.customer[index].project_exec_time =  this.customer[index].project_exec_time / 1000
                var data = {
                    'id' : id,
                    'type':'customer',
                    'data' : this.customer[index]
                }
                $.post('<?php echo U("/Dwin/Purchase/editOrAddSupplierOneMsg");?>', data, function (res) {
                    if(res.status == 200){
                        vm.getData()
                        var index = parent.layer.getFrameIndex(window.name)
                        parent.layer.close(index)
                    }else{
                        this.customer[index].project_exec_time =  this.customer[index].project_exec_time * 1000
                    }
                    layer.msg(res.msg)
                })  
            },
            // 新增一行空数据
            addCustomer: function () {
                // 判断是否重复新增
                if(this.customer[this.customer.length - 1] != undefined){
                    if(this.customer[this.customer.length - 1].cus_name){
                        var obj = {}
                        this.customer.push(obj)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    var obj = {}
                    this.customer.push(obj)
                }
            },
             // 保存所有数据
             allSaveContact () {
                var vm = this
                // 修改的数据
                var allAmend = this.customer.slice(0,vm.num)
                for (var i = 0;i<allAmend.length;i++) {
                    allAmend[i].project_exec_time = allAmend[i].project_exec_time / 1000
                }
                // 新增的数据
                var allAdd = this.customer.slice(vm.num)
                for (var i = 0;i<allAdd.length;i++) {
                    allAdd[i].project_exec_time = allAdd[i].project_exec_time / 1000
                }
                var data = []
                var params = {
                    'id' : id,
                    'type':'customer',
                    'editData': allAmend,
                    'addData': allAdd
                }
                $.post('<?php echo U("/Dwin/Purchase/editSupplierMsg");?>', params, function (res) {
                    if(res.status == 200){
                        vm.getData();
                        var index = parent.layer.getFrameIndex(window.name)
                        parent.layer.close(index)
                    }else{
                        for (var i = 0;i<allAmend.length;i++) {
                            allAmend[i].project_exec_time = allAmend[i].project_exec_time * 1000
                        } 
                        for (var i = 0;i<allAdd.length;i++) {
                            allAdd[i].project_exec_time = allAdd[i].project_exec_time * 1000
                        }
                    }
                    layer.msg(res.msg)
                })  
            }
        }
    })
</script>
</body>
</html>