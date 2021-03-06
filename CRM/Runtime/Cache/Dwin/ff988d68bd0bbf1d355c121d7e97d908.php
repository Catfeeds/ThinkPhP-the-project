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
        .add_bomName{
            text-align: left;
            margin-top: 5px;
        }
        .addTable_name{
            margin-bottom: 0px;
            border: 1px solid #ccc;
        }
        .addTable_name tr{
            height: 40px;
        }
        .addTable_name tr td,.addTable_name tr th{
            border: 1px solid #ccc;
            text-align: center
        }
    </style>
</head>
<body>
    <div id="app" style="text-align: center">
        <h1>湖南迪文有限公司BOM修改</h1>
        <br><br><br>
        <el-form ref="form" :model="form" size="medium" label-width="150px" @submit.native.prevent v-loading="loading">
            <el-row>
                <el-col :span="22" :offset="1">
                    <p style="text-align: left;"><b>一、BOM基本信息：</b></p>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="生产物料型号：">
                                <el-input type="text" v-model="form.product_no" style="width: 200px;" disabled></el-input>
                            </el-form-item>
                            <el-form-item label="生产物料型号：" v-if="false">
                                <el-input type="text" v-model="form.id" style="width: 200px;" disabled></el-input>
                            </el-form-item>
                            <el-form-item label="生产物料型号：" v-if="false">
                                <el-input type="text" v-model="form.product_id" style="width: 200px;" disabled></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8" :offset="2">
                            <el-form-item label="最近更新时间：">
                                <!-- {{form.update_time}} -->
                                <el-input type="text" v-model="form.update_time" style="width: 200px;" disabled></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="BOM编号：">
                                <el-input type="text" v-model="form.bom_id" style="width: 200px;" disabled></el-input>
                            </el-form-item>
                            <el-form-item label="BOM编号：" v-if="false">
                                <el-input type="text" v-model="form.bom_type_name" style="width: 200px;" disabled></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8" :offset="2">
                            <el-form-item label="bom组别名称：">
                                <el-select v-model="form.bom_type" placeholder="请选择">
                                    <el-option
                                        v-for="item in options_sex"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <br>
                    <p style="text-align: left;"><b>二、添加物料信息：</b></p>
                    <table class="table table-border table-hover table-striped addTable_name">
                        <tr>
                            <th v-if="false">id</th>
                            <th v-if="false">ID</th>
                            <th>物料编号</th>
                            <th>产品名称</th>
                            <th>数量</th>
                            <th>操作</th>
                        </tr>
                        <tr v-for="(product,index) in productData">
                            <td v-if="false">{{product.id}}</td>
                            <td v-if="false">{{product.product_id}}</td>
                            <td>{{product.product_no}}</td>
                            <td>{{product.product_name}}</td>
                            <td>
                                <el-input
                                        data-vv-as="入库数量"
                                        type="number"
                                        v-model="product.num">
                                </el-input>
                            </td>
                            <td>
                                <el-button type="warning"  class="btn btn-warning" @click="delawards11(index)">删除</el-button>
                            </td>
                        </tr>
                    </table>  
                    <el-popover
                    ref="add_product"
                    placement="right"
                    width="70%"
                    trigger="click">
                    <div class="form-inline">
                        <input type="text" class="form-control" placeholder="请输入产品名" v-model="searchProduct.name" @input="searchingProduct">
                    </div>
                    <table class="table table-striped table-hover table-bordered">
                        <tr>
                                <th>物料编号</th>
                                <th>产品名称</th>
                                <th>产品型号</th>
                            </tr>
                            <tr v-for="item in searchProductRes" @click="addProduct(item)">
                                <td>{{item.product_no}}</td>
                                <td>{{item.product_number}}</td>
                                <td>{{item.product_name}}</td>
                            </tr>
                        </table>
                    </el-popover>   
                    <p class="add_bomName">
                        <el-button v-popover:add_product type="success">新增BOM</el-button>
                    </p>
                <br><br>
                <el-button type="primary" @click="onSubmit(form)"> 提 交 </el-button>
                <br><br>
                </el-col>
            </el-row>
        </el-form>
    </div>
</body>
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
    var groupMap = <?php echo (json_encode($groupMap)); ?>;     //成品
    var bom = <?php echo (json_encode($bom)); ?>;
    var bomSub= <?php echo (json_encode($bomSub)); ?>;
    console.log('bom',bom)
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
               form:{
                   id:'',
                   product_id:'',
                   product_no:'',
                   bom_id:'',
                   bom_type:'',
                   bom_type_name:'',
                   update_time:''
               },
               productData: [],
               searchProduct: {
                    name: ''
                },
                search:{
                    pur_no:''
                },
                searchProductRes:[],
                searchProductRes_no:[],
                options_sex:[],
                initial_row:[],
                add_operate:[],
                edit_operate:[]
            }
        },
        created () {
            this.loading = false
            this.initial_row.length = 0
            for(let i in groupMap){
                this.options_sex.push({'value':String(i), 'label':groupMap[i]})
            }
            this.form.id = bom.id
            this.form.product_id = bom.product_id
            this.form.product_no = bom.product_no
            this.form.bom_id = bom.bom_id
            this.form.bom_type = bom.bom_type
            this.form.bom_type_name = bom.bom_type_name
            this.form.update_time = this.formatDateTime(bom.update_time)
            for(var i = 0;i< bomSub.length;i++){
                this.productData.push(bomSub[i])
            }
            for(var i = 0;i< this.productData.length;i++){
                this.initial_row.push(this.productData[i])
            }
        },
        mounted() {   
        },
        methods :{
            // 选中
            addProduct: function (item) {
                var judge =true
                for(var i = 0;i < this.productData.length;i++){
                    if(this.productData[i].product_id == item.product_id){
                        judge = false
                    }
                }
                if(judge){
                    var obj = {
                        'product_name': item.product_name,
                        'product_id' : item.product_id,
                        'product_no' : item.product_no,
                        'num'  : '0'
                    }
                    vm.searchProduct.name = ""
                    vm.searchProductRes = ''
                    this.productData.push(obj)
                }else{
                    vm.$message({
                        showClose: true,
                        message: '不能添加一样的数据！',
                        type: 'warning'
                    });
                    vm.searchProduct.name = ""
                    vm.searchProductRes = ''
                }
            },
            // 输入搜索
            searchingProduct: function () {
                $.post('/Dwin/Stock/getProductMsg', {
                    condition : vm.searchProduct.name
                }, function (res) {
                    if (res.status == 200) {
                        vm.searchProductRes = res.data
                    }

                })
            },
            // 删除
            delawards11 (index) { 
                if(this.productData.length > this.initial_row.length){
                    vm.productData.splice(index,1)
                }else{
                    // 删除
                    var data = {
                        'bomSubId':this.productData[index].id,
                        "bomId":bom.id
                    }
                    $.post('<?php echo U("Dwin/bom/deleteBomSub");?>',data,function(res){
                        if(res.status == 200){
                            vm.productData.splice(index,1) 
                            vm.$message({
                                showClose:true,
                                message:res.msg,
                                type:'success'
                            })
                        }else{
                            vm.$message({
                                showClose:true,
                                message:res.msg,
                                type:'warning'
                            })
                        }
                    })
                }
             },   
            // 提交
            onSubmit(form){
                this.add_operate.length = 0
                this.edit_operate.length = 0
                // 判断数据是修改还是新增还是删除
                for(var j = 0;j<this.productData.length;j++){
                        if(this.productData[j].id == undefined){   // 说明不存在
                            this.add_operate.push(this.productData[j])
                        }else{
                            this.edit_operate.push(this.productData[j])
                        }
                    }
                    var data = {
                        'bom':this.form,
                        'edit_bom_sub' : this.edit_operate,
                        'new_bom_sub' : this.add_operate
                    }
                    $.post('<?php echo U("Dwin/bom/editBom");?>', data , function (res) {
                        if(res.status == 200){
                            // 关闭弹框 刷新父页面
                            layer.msg(res.msg)
                            location.reload();
                            vm.$message({
                                showClose:true,
                                message:res.msg,
                                type:'success'
                            })
                        }else{
                            vm.$message({
                                showClose:true,
                                message:res.msg,
                                type:'warning'
                            })
                        }
                })
            },
            // 时间戳转化为时间
            formatDateTime:function (timeStamp) { 
                var date = new Date();
                date.setTime(timeStamp * 1000);
                var y = date.getFullYear();    
                var m = date.getMonth() + 1;    
                m = m < 10 ? ('0' + m) : m;    
                var d = date.getDate();    
                d = d < 10 ? ('0' + d) : d;    
                var h = date.getHours();  
                h = h < 10 ? ('0' + h) : h;  
                var minute = date.getMinutes();  
                var second = date.getSeconds();  
                minute = minute < 10 ? ('0' + minute) : minute;    
                second = second < 10 ? ('0' + second) : second;   
                // return y + '-' + m + '-' + d+' '+h+':'+minute+':'+second;  
                return y + '-' + m + '-' + d;  
            }
        }
    })
</script>
</html>