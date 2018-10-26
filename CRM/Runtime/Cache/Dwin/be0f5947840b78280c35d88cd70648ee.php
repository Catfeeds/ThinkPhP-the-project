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
        <h1>湖南迪文有限公司替换物料修改</h1>
        <br><br><br>
        <el-form ref="form" :model="form" size="medium" label-width="100px" @submit.native.prevent v-loading="loading">
            <el-row>
                <el-col :span="22" :offset="1">
                    <el-row>
                        <el-col :span="24" style="margin-top: 10px">
                            <p style="text-align: left;"><b>一、物料基本信息：</b></p>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="物料编号：">
                                <el-input type="text" v-model="form.product_no" style="width: 230px;" readonly></el-input>
                                <el-input type="text" v-model="form.product_id" v-if="false"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="物料型号：">
                                <el-input type="text" v-model="form.product_name" style="width: 230px;" readonly></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="物料名称：">
                                <el-input type="text" v-model="form.product_number" style="width: 230px;" readonly></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>


                    <p style="text-align: left;"><b>二、替换物料信息：</b></p>
                    <table class="table table-border table-hover table-striped addTable_name">
                        <tr>
                            <th v-if="false">ID</th>
                            <th>物料编号</th>
                            <th>物料型号</th>
                            <th>产品名称</th>
                            <th>替代范围</th>
                            <th>备注</th>
                            <th>操作</th>
                        </tr>
                        <tr v-for="(product,index) in productData">
                            <td v-if="false">{{product.substituted_id}}</td>
                            <td>{{product.product_no}}</td>
                            <td>{{product.product_name}}</td>
                            <td>{{product.product_number}}</td>
                            <td>
                                <el-select v-model="product.alternative_scope" placeholder="请选择">
                                    <el-option
                                        v-for="item in options"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value">
                                    </el-option>
                                </el-select>
                            </td>
                            <td>
                                <el-input type="textarea" v-model="product.tips"></el-input>
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
                                <th v-if="false">ID</th>
                                <th>产品名称</th>
                                <th>产品型号</th>
                            </tr>
                            <tr v-for="item in searchProductRes" @click="addProduct(item)">
                                <td>{{item.product_no}}</td>
                                <td  v-if="false">{{item.product_id}}</td>
                                <td>{{item.product_number}}</td>
                                <td>{{item.product_name}}</td>
                            </tr>
                        </table>
                    </el-popover>
                    <p class="add_bomName">
                        <el-button v-popover:add_product type="success">新增物料</el-button>
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
    var materialData = <?php echo (json_encode($materialData)); ?>;
    var substituteData = <?php echo (json_encode($substituteData)); ?>;
    var scopeMap = <?php echo (json_encode($scopeMap)); ?>;
    console.log(materialData)
    console.log(substituteData)
    console.log(scopeMap)
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
               form:{
                   product_id:'',
                   product_no:'',
                   product_name:'',
                   product_number:''
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
                options:[],
                newReplaceData:[],
                editReplaceData:[],
                initial_row:[]
            }
        },
        created () {
            this.loading = false
            this.form = materialData
            this.productData = substituteData
            for(let key in scopeMap){
                this.options.push({'value':key,'label':scopeMap[key]})
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
                // 不添加相同的
                for(var i = 0;i < this.productData.length;i++){
                    if(this.productData[i].substituted_id == item.product_id){
                        judge = false
                    }
                }
                if(judge){
                    var obj = {
                        'tips':'',
                        'alternative_scope':'',
                        'product_number': item.product_number,
                        'product_name': item.product_name,
                        'substituted_id' : item.product_id,
                        'product_no' : item.product_no
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
            // 输入搜索
            searchingProduct_no: function () {
                $.post('/Dwin/Stock/getProductMsg', {
                    condition:vm.search.pur_no
                }, function (res) {
                    if (res.status == 200) {
                        vm.searchProductRes_no = res.data
                    }
                })
            },
            // 选中
            addProduct_no: function (item) {
                vm.form.product_id = item.product_id
                vm.form.product_no = item.product_no
                vm.form.product_name = item.product_name
                vm.search.pur_no = ""
                vm.searchingProduct_no = ""
            },
            // 删除
            delawards11 (index) { 
                if(this.productData.length > this.initial_row.length){
                    vm.productData.splice(index,1)
                }else{
                    // 删除
                    var data = {
                        'id':this.productData[index].id
                    }
                    $.post('<?php echo U("Dwin/bom/delMaterialReplace");?>',data,function(res){
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
                // 判断数据是修改还是新增还是删除
                for(var j = 0;j<this.productData.length;j++){
                    if(this.productData[j].id == undefined){   // 说明不存在
                        this.newReplaceData.push(this.productData[j])
                    }else{
                        this.editReplaceData.push(this.productData[j])
                    }
                }
                var data = {
                    'productNo' : vm.form.product_no,
                    'productId' : vm.form.product_id,
                    'editReplaceData' : this.editReplaceData,
                    'newReplaceData' : this.newReplaceData
                }
                $.post('<?php echo U("Dwin/bom/editMaterialReplace");?>', data , function (res) {
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
            }
        }
    })
</script>
</html>