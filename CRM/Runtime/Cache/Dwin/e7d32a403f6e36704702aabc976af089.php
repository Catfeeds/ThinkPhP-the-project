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
    <h3 style="margin: 20px;" class="text-center">供应商财务信息</h3>
    <table class="table table-striped table-hover table-bordered">
        <tr>
            <th>近两年业绩</th>
            <th>资产总额</th>
            <th>主营业务收入</th>
            <th>净利润</th>
            <th>利润率</th>
            <th>操作</th>
        </tr>
        <tr v-for="(item, index) in finance" v-if="flag!=='del'">
            <td>
                <el-input v-model="item.finance_year" placeholder="近两年业绩"></el-input>
            </td>
            <td>
                <el-input v-model="item.total_assets"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46"  placeholder="总资产(万)"></el-input>
            </td>
            <td style="width: 170px;">
                <el-input v-model="item.main_income"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46"  placeholder="主营业务收入(万)"></el-input>
            </td>
            <td style="width: 170px;">
                <el-input v-model="item.net_profit"  placeholder="净利润"></el-input>
            </td>
            <td style="width: 170px;">
                <el-input v-model="item.profit_rat"  placeholder="利润率"></el-input>
            </td>
            <td style="width: 200px;">
                <button class="btn btn-warning" @click="delFinance(index)" v-if="flag == 'get'">删除</button>
                <button class="btn btn-primary" @click="saveFinance(index)" v-if="flag == 'get'">保存</button>
            </td>
        </tr>
    </table>
    <button class="btn btn-info" @click="addFinance" style="margin-left: 50px;">新增财务信息</button>
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
                finance:[]
            }
        },
        created: function () {
            this.getData()
        },
        methods: {
            getData: function () {
                var vm = this;
                this.loading = true;
                $.post('<?php echo U("/Dwin/Purchase/getFinance");?>', {'id' : id}, function (res) {
                    if(res.status == 200){
                        vm.loading = false
                        vm.finance = res.data
                        vm.num = vm.finance.length
                    }
                })
            },
            // 删除
            delFinance: function (index) {
                var indexs = index + 1
                var vm = this
                if(indexs > vm.num){
                    this.finance.splice(index,1)
                }else if(indexs <= vm.num){
                    if(this.finance.length > vm.num){
                        layer.msg('请先保存修改的内容或删除')
                    }else{
                        var data = {
                            'id' : id,
                            'type':'finance',
                            'data' : this.finance[index]
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
            editFinance: function(index){
                vm.addAble = true
                this.flag = 'zyt'
            },
            // 提交数据 单条数据保存
            saveFinance: function (index) {
                var vm = this
                var data = {
                    'id' : id,
                    'type':'finance',
                    'data' : this.finance[index]
                }
                $.post('<?php echo U("/Dwin/Purchase/editOrAddSupplierOneMsg");?>', data, function (res) {
                    if(res.status == 200){
                        vm.getData()
                        location.reload();
                    }
                    layer.msg(res.msg)
                })  
            },
            // 新增一行空数据
            addFinance: function () {
                // 判断是否重复新增
                if(this.finance[this.finance.length - 1] != undefined){
                    if(this.finance[this.finance.length - 1].finance_year){
                        var obj = {
                        }
                        this.finance.push(obj)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    var obj = {
                    }
                    this.finance.push(obj)
                }
            },
             // 保存所有数据
             allSaveContact () {
                var vm = this
                // 修改的数据
                var allAmend = this.finance.slice(0,vm.num)
                // 新增的数据
                var allAdd = this.finance.slice(vm.num)
                var data = []
                var params = {
                    'id' : id,
                    'type':'finance',
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