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
        <h1>湖南迪文有限公司新增物料BOM</h1>
        <br><br><br>
        <el-form ref="form" :model="form" size="medium" label-width="150px" @submit.native.prevent v-loading="loading">
            <el-row>
                <el-col :span="22" :offset="1">
                    <p style="text-align: left;"><b>一、获取bom编号：</b></p>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="生产物料型号：" required>
                                <el-input type="text" v-model="form.product_no" style="width: 200px;" readonly></el-input>
                                <el-input type="text" v-model="form.product_id" v-if="false"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="7">
                            <el-button type="success" size="medium" v-popover:add_product_no>获取生产物料型号</el-button>
                        </el-col>
                        <el-popover ref="add_product_no" placement="right" width="70%" trigger="click">
                        <div class="form-inline">
                            <input type="text" class="form-control" placeholder="请输入产品名" v-model="search.pur_no" @input="searchingProduct_no">
                        </div>
                        <table class="table table-striped table-hover table-bordered" style="max-height:200px">
                            <tr>
                                    <th>物料编号</th>
                                    <th>产品名称</th>
                                    <th>产品型号</th>
                                </tr>
                                <tr v-for="item in searchProductRes_no" @click="addProduct_no(item)">
                                    <td>{{item.product_no}}</td>
                                    <td>{{item.product_number}}</td>
                                    <td>{{item.product_name}}</td>
                                </tr>
                            </table>
                        </el-popover>
                    </el-row>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="BOM编号：" required>
                                <el-input type="text" v-model="form.bom_id" style="width: 200px;" readonly></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="6">
                            <el-button type="success" size="medium" @click="getBomNumber_num()">获取编号</el-button>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="bom组别名称：" required>
                                <el-select v-model="form.bom_type" placeholder="请选择">
                                    <el-option
                                        v-for="(item,index) in options_sex"
                                        :key="index"
                                        :label='item'
                                        :value='index'>
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <br>
                    <p style="text-align: left;"><b>二、添加物料信息：</b></p>
                    <table class="table table-border table-hover table-striped addTable_name">
                        <tr>
                            <th v-if="false">ID</th>
                            <th>物料编号</th>
                            <th>产品名称</th>
                            <th>数量</th>
                            <th>操作</th>
                        </tr>
                        <tr v-for="(product,index) in productData">
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
                                <el-button type="warning"  class="btn btn-warning" @click="productData.splice(index, 1)">删除</el-button>
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
    var groupMap = <?php echo (json_encode($groupMap)); ?>;
    console.log(groupMap)
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
                   bom_type_name:''
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
                options_sex:groupMap
            }
        },
        created () {
            this.loading = false
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
            addProduct_no: function (row) {
                vm.form.product_id = row.product_id
                vm.form.product_no = row.product_no
                vm.form.product_name = row.product_name
                vm.search.pur_no = ""
                vm.searchingProduct_no = ""
            },
            // 获取编号
            getBomNumber_num:function() {
                $.get('createBomId',function (res) {
                    if(res.status==200){
                        vm.form.bom_id=res.data.bomIdString
                        vm.form.id=res.data.id
                        vm.$message({
                            showClose: true,
                            message: res.msg,
                            type: 'success'
                        });
                    }else{
                        vm.$message({
                            showClose: true,
                            message: res.msg,
                            type: 'error'
                        });
                    }
                    
                })
            },
            // 提交
            onSubmit(form){
                for(key in this.options_sex){
                    if(form.bom_type == key){
                        form.bom_type_name = this.options_sex[key]
                    }
                }
                var data = {
                    'bom':form,
                    'bomSub':vm.productData
                }
                $.post('createBom', data , function (res) {
                    if(res.status==200){
                        vm.form = {
                            id:'',
                            product_id:'',
                            product_no:'',
                            bom_id:'',
                            bom_type:'',
                            bom_type_name:''
                        }
                        vm.productData = []

                    }
                    layer.msg(res.msg)
                })
            }
        }
    })
</script>
</html>