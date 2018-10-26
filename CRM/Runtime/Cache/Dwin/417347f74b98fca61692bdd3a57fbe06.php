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
    <h3 style="margin: 20px;" class="text-center">供应商股权信息</h3>
    <table class="table table-striped table-hover table-bordered">
        <tr>
            <th>股东名称</th>
            <th>持股比例</th>
            <th>操作</th>
        </tr>
        <tr v-for="(item, index) in equity">
            <td>
                <el-input v-model="item.shareholder_name" placeholder="股东名称"></el-input>
            </td>
            <td>
                <el-input v-model="item.shareholding_ratio"   @keyup.native="shareholding_repity($event)" placeholder="持股比例"></el-input>
            </td>
            <td style="width: 200px;">
                <button class="btn btn-warning" @click="delEquity(index)" v-if="flag == 'get'">删除</button>
                <button class="btn btn-primary" @click="saveEquity(index)" v-if="flag == 'get'">保存</button>
            </td>
        </tr>
    </table>
    <button class="btn btn-info" @click="addEquity" style="margin-left: 50px;">新增股权信息</button>
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
                addAble:false,
                loading: true,
                id:id,
                num:0,
                flag:'get',
                equity:[]
            }
        },
        created: function () {
            this.getData()
        },
        methods: {
            getData: function () {
                var vm = this;
                this.loading = true;
                $.post('<?php echo U("/Dwin/Purchase/getEquity");?>', {'id' : id}, function (res) {
                    if(res.status == 200){
                        vm.loading = false
                        vm.equity = res.data
                        vm.num = vm.equity.length
                    }
                })
            },
            // 持股比例
            shareholding_repity(event){
                var values = event.srcElement.value
                if(Number(values) > 100){
                    this.$message({
                        showClose: true,
                        message: '请检查输入持股比列是否正确！',
                        type: 'warning'
                    });
                }
            },
            // 单行删除
            delEquity: function (index) {
                var indexs = index + 1
                var vm = this
                if(indexs > vm.num){
                    this.equity.splice(index,1)
                }else if(indexs <= vm.num){
                    if(this.equity.length > vm.num){
                        layer.msg('请先保存修改的内容或删除')
                    }else{
                        var data = {
                            'id' : id,
                            'type':'equity',
                            'data' : this.equity[index]
                        }
                        layer.confirm('确认删除?', function (aaa) {
                            $.post('<?php echo U("/Dwin/Purchase/delSupplierOtherMsg");?>', data, function (res) {
                                if (res.status == 200) {
                                    vm.getData();
                                    location.reload();
                                }
                                layer.msg(res.msg)
                            })
                        })
                    }
                }
            },
            editEquity: function(index){
                vm.addAble = true
                this.flag = 'zyt'
            },
            // 提交数据 保存一行保存
            saveEquity: function (index) {
                var addJoin = true   
                var vm = this
                if(this.equity[index].shareholder_name){
                    if(!this.equity[index].shareholding_ratio){
                        layer.msg('请将数据填写完整！')
                        addJoin = false
                    }
                }else{
                    layer.msg('请将数据填写完整！')
                    addJoin = false
                }
                if(addJoin){
                    var data = {
                        'id' : id,
                        'type':'equity',
                        'data' : this.equity[index]
                    }
                    $.post('<?php echo U("/Dwin/Purchase/editOrAddSupplierOneMsg");?>', data, function (res) {
                        if(res.status == 200){
                            vm.num = vm.equity.length
                        }
                        layer.msg(res.msg)
                    })  
                }
            },
            // 新增一行空数据
            addEquity: function () {
                // 判断是否重复新增
                if(this.equity[this.equity.length - 1] != undefined){
                    if(this.equity[this.equity.length - 1].shareholder_name){
                        var obj = {
                        }
                        this.equity.push(obj)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    var obj = {
                    }
                    this.equity.push(obj)
                }
            },
            // 保存所有数据
            allSaveContact () {
                var vm = this
                // 修改的数据
                var allAmend = this.equity.slice(0,vm.num)
                // 新增的数据
                var allAdd = this.equity.slice(vm.num)
                var data = []
                var addJoin = true
                for(var i = 0;i < allAmend.length;i++){
                    if(this.equity[i].shareholder_name){
                        if(!this.equity[i].shareholding_ratio){
                            layer.msg('请将数据填写完整！')
                            addJoin = false
                        }
                    }else{
                        layer.msg('请将数据填写完整！')
                        addJoin = false
                    }
                }
                for(var i = allAmend.length;i < allAmend.length + allAdd.length;i++){
                    if(this.equity[i].shareholder_name){
                        if(!this.equity[i].shareholding_ratio){
                            layer.msg('请将数据填写完整！')
                            addJoin = false
                        }
                    }else{
                        layer.msg('请将数据填写完整！')
                        addJoin = false
                    }
                }
                if(addJoin){
                    var params = {
                        'id' : id,
                        'type':'equity',
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
        }
    })
</script>
</body>
</html>