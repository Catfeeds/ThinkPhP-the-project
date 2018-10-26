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
       
    </style>
</head>
<body>
<div id="app" v-loading="loading">
    <h3 style="margin: 20px;" class="text-center">供应商联系人信息</h3>
    <table class="table table-striped table-hover table-bordered">
        <tr>
            <th>联系人职位</th>
            <th>联系人姓名</th>
            <th>电话</th>
            <th>手机</th>
            <th>电子邮箱</th>
            <th>传真号</th>
            <th>操作</th>
        </tr>
        <tr v-for="(item, index) in contact" v-if="flag!=='del'">
            <td>
                <el-select v-model="item.contact_position" placeholder="请选择联系人职位">
                    <el-option
                        v-for="items in options"
                        :key="items.value"
                        :label="items.label"
                        :value="items.value">
                    </el-option>
                </el-select>
            </td>
            <td>
                <el-input v-model="item.contact" placeholder="联系人姓名"></el-input>
            </td>
            <td>
                <el-input v-model="item.telephone" placeholder="电话"></el-input>
            </td>
            <td>
                <el-input v-model="item.phone" @change="upperCase($event)"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46"  placeholder="手机"></el-input>
            </td>
            <td>
                <el-input v-model="item.e_mail"  @change="check($event)"  placeholder="电子邮箱"></el-input>
            </td>
            <td>
                <el-input v-model="item.fax"  @change="upperFax($event)"   placeholder="传真号"></el-input>
            </td>
            <td style="width: 130px;">
                <button class="btn btn-warning" @click="delContact(index)" v-if="flag == 'get'">删除</button>
                <button class="btn btn-primary" @click="saveContact(index)" v-if="flag == 'get'">保存</button>
            </td>
        </tr>
    </table>
    <button class="btn btn-info" @click="addContact" style="margin-left: 50px;">新增联系人信息</button>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/index.js"></script>
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
                num:0,
                contact:[],
                options:[{
                    value: '公司负责人',
                    label: '公司负责人'
                    }, {
                    value: '区域负责人',
                    label: '区域负责人'
                    }, {
                    value: '客户经理',
                    label: '客户经理'
                }]
            }
        },
        created: function () {
            this.getData()
        },
        methods: {
            // ==============校验   GO================
            // 手机验证
            upperCase (event) {
                if(event){
                    for(var i = 0;i < event.length;i++){
                        if(event.charCodeAt(i) > 255){
                            this.$message({
                                showClose: true,
                                message: '手机号码有误，请检查手机号是否正确！',
                                type: 'warning'
                            }); 
                        }
                    }
                }
            },
            // 传真验证
            upperFax (event) {
                if(event){
                    if(!(/^[+]{0,1}(\d){1,3}[ ]?([-]?((\d)|[ ]){1,12})+$/.test(event))){
                        this.$message({
                            showClose: true,
                            message: '输入传真有误，请检查传真输入是否正确！！',
                            type: 'warning'
                        });
                        return false; 
                    } 
                }
            },
            // 电子邮件
            check (event){
                if(event){
                    var regex = /^([0-9A-Za-z\-_\.]+)@([0-9a-z]+\.[a-z]{2,3}(\.[a-z]{2})?)$/g;
                    if ( regex.test( event ) )
                    {
                        var user_name = event.replace( regex, "$1" );
                        var domain_name = event.replace( regex, "$2" );
                        var alert_string = "您输入的电子邮件地址合法\n\n";
                        alert_string += "用户名：" + user_name + "\n";
                        alert_string += "域名：" + domain_name;
                        this.$message({
                            showClose: true,
                            message: alert_string,
                            type: 'success'
                        });
                        return true;
                    }
                    else
                    {
                        this.$message({
                            showClose: true,
                            message: '您输入的电子邮件地址不合法！',
                            type: 'warning'
                        });
                    }
                }
            },
            // ==============校验   END================
            getData: function () {
                var vm = this;
                this.loading = true;
                $.post('<?php echo U("/Dwin/Purchase/getContact");?>', {'id' : id}, function (res) {
                    if(res.status == 200){
                        vm.loading = false
                        vm.contact = res.data
                        vm.num = vm.contact.length
                    }
                })
            },
            // 删除
            delContact: function (index) {
                var indexs = index + 1
                var vm = this
                if(indexs > vm.num){
                    this.contact.splice(index,1)
                }else if(indexs <= vm.num){
                    if(this.contact.length > vm.num){
                        layer.msg('请先保存修改的内容或删除')
                    }else{
                        var data = {
                            'id' : id,
                            'type':'contact',
                            'data' : this.contact[index]
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
            saveContact: function (index) {
                var vm = this
                var data = {
                    'id' : id,
                    'type':'contact',
                    'data' : this.contact[index]
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
            addContact: function () {
                 // 判断是否重复新增
                 if(this.contact[this.contact.length - 1] != undefined){
                    if(this.contact[this.contact.length - 1].contact_position){
                        var obj = {}
                        this.contact.push(obj)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    var obj = {}
                    this.contact.push(obj)
                }
            },
            // 提交所以数据
            allSaveContact () {
                var vm = this
                // 修改的数据
                var allAmend = this.contact.slice(0,vm.num)
                // 新增的数据
                var allAdd = this.contact.slice(vm.num)
                var data = []
                var params = {
                    'id' : id,
                    'type':'contact',
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