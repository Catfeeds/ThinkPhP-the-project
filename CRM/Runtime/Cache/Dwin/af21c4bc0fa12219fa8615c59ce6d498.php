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
    <h3 style="margin: 20px;" class="text-center">供应商团队信息</h3>
    <table class="table table-striped table-hover table-bordered">
        <tr>
            <th>人员类型</th>
            <th>团队人数</th>
            <th>备注</th>
            <th>操作</th>
        </tr>
        <tr v-for="(item, index) in team" v-if="flag!=='del'">
            <td style="width: 18%;">
                <el-select v-model="item.team_cate" placeholder="请选择团队类型">
                    <el-option
                        v-for="items in options"
                        :key="items.value"
                        :label="items.label"
                        :value="items.value">
                    </el-option>
                  </el-select>
            </td>
            <td>
                <el-input v-model="item.team_number" placeholder="团队人数"></el-input>
            </td>
            <td>
                <el-input type="textarea" v-model="item.tips" placeholder="备注"></el-input>
            </td>
            <td style="width: 12%;">
                <button class="btn btn-warning" @click="delTeam(index)" v-if="flag == 'get'">删除</button>
                <button class="btn btn-primary" @click="saveTeam(index)" v-if="flag == 'get'">保存</button>
            </td>
        </tr>
    </table>
    <button class="btn btn-info" @click="addTeam" style="margin-left: 50px;">新增团队信息</button>
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
                flag:'get',
                team:[],
                num:0,
                options:[{
                    value: '专业技术人员',
                    label: '专业技术人员'
                    }, {
                    value: '销售人员',
                    label: '销售人员'
                    }, {
                    value: '其他人员',
                    label: '其他人员'
                }]
            }
        },
        created: function () {
            this.getData()
        },
        methods: {
            getData: function () {
                var vm = this;
                this.loading = true;
                $.post('<?php echo U("/Dwin/Purchase/getTeam");?>', {'id' : id}, function (res) {
                    console.log(res)
                    if(res.status == 200){
                        vm.loading = false
                        vm.team = res.data
                        vm.num = vm.team.length
                    }
                })
            },
            // 删除
            delTeam: function (index) {
                var indexs = index + 1
                var vm = this
                if(indexs > vm.num){
                    this.team.splice(index,1)
                }else if(indexs <= vm.num){
                    if(this.team.length > vm.num){
                        layer.msg('请先保存修改的内容或删除')
                    }else{
                        var data = {
                            'id' : id,
                            'type':'team',
                            'data' : this.team[index]
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
            // 提交数据 保存
            saveTeam: function (index) {
                var vm = this
                var data = {
                    'id' : id,
                    'type':'team',
                    'data' : this.team[index]
                }
                console.log(data)
                $.post('<?php echo U("/Dwin/Purchase/editOrAddSupplierOneMsg");?>', data, function (res) {
                    if(res.status == 200){
                        vm.getData()
                        location.reload();
                    }
                    layer.msg(res.msg)
                })  
            },
            // 新增一行空数据
            addTeam: function () {
                // 判断是否重复新增
                if(this.team[this.team.length - 1] != undefined){
                    if(this.team[this.team.length - 1].team_cate){
                        var obj = {}
                        this.team.push(obj)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    var obj = {}
                    this.team.push(obj)
                }
                
            },
            allSaveContact () {
                var vm = this
                // 修改的数据
                var allAmend = this.team.slice(0,vm.num)
                // 新增的数据
                var allAdd = this.team.slice(vm.num)
                var data = []
                var params = {
                    'id' : id,
                    'type':'team',
                    'editData': allAmend,
                    'addData': allAdd
                }
                $.post('<?php echo U("/Dwin/Purchase/editSupplierMsg");?>', params, function (res) {
                    if(res.status == 200){
                        layer.msg(res.msg)
                        vm.getData();
                    }else{
                        layer.msg(res.msg)
                    }
                })  
            }
        }
    })
</script>
</body>
</html>