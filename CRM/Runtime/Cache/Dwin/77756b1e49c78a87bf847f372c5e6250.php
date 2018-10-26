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
        input {
            width: 100%;
            height: 100%;
            display: block;
            outline: none;
        }
        .el-table .warning-row {
             background: oldlace;
        }

        .el-table .success-row {
            background: #f0f9eb;
        }
        .head_thead{
            height: 40px;
            line-height: 40px;
            text-align: left;
            padding-left: 10px;
            font-size: 15px;
        }
        .el-autocomplete{
            width: 100%;
        }
        /* .el-button--primary {
            float: left;
        } */
        .add_button_new {
            text-align: left
        }
    </style>
</head>
<body>
    <div id="app" style="text-align: center">
        <h1>湖南迪文科技有限公司新增出库申请单</h1>
        <br><br><br>
        <el-form ref="form" :model="form" label-width="150px" size="medium" @submit.native.prevent v-loading="loading">
            <el-row>
                <el-col :span="10" :offset="1">
                    <el-form-item label="申请单编号：" required>
                        <el-input v-model="form.apply_id" style="width: 57%;" readonly placeholder="申请单编号"></el-input>
                        <el-input v-if="false" v-model="form.id" style="width: 50%;" readonly></el-input>
                        <el-button type="primary" plain @click="getNumber()">获取编号</el-button>
                    </el-form-item>
                </el-col>
                <el-col :span="8" :offset="1">
                    <el-form-item label="领料类型：" required>
                        <el-select v-model="form.picking_kind" filterable placeholder="请选择领料类型" style="width: 100%;">
                            <el-option
                                v-for="item in options_picking_kind"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="8"  :offset="1">
                    <el-form-item label="申领部门：" required>
                            <el-input v-if="false" v-model="form.apply_dept_name" style="width: 50%;" readonly></el-input>
                            <el-input v-if="false" v-model="form.apply_dept_id" style="width: 50%;" readonly></el-input>
                        <el-select v-model="apply_dept_name001" value-key="id" filterable placeholder="请选择申领部门">
                            <el-option
                                v-for="item in options_apply_dept_name"
                                :key="item.id"
                                :label="item.name"
                                :value="item"
                                >
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8" :offset="3">
                        <el-form-item label="申请时间：" required>
                            <el-date-picker
                            v-model="form.apply_time"
                            type="date"
                            value-format="timestamp"
                            format="yyyy-MM-dd"
                            placeholder="选择申请日期"
                            style="width: 100%;"
                            >
                        </el-date-picker>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :span="22" :offset="1">
                        <table class="table table-striped table-hover table-bordered" border style="margin-bottom: 0">
                            <div class="head_thead">一、产品名称、型号、单位、金额、需求时间及申请数量</div>
                            <tbody>
                                <tr  class="deal_cent">
                                    <th v-show="false">ID</th>      
                                    <th >物料名称</th>      
                                    <th >物料型号</th>      
                                    <th >物料编号</th>     
                                    <th>单位</th>
                                    <th>申请数量</th>
                                    <!-- <th>单价(元)</th> -->
                                    <!-- <th>总金额(元)</th> -->
                                    <th >需求时间</th>
                                    <th >备注</th>
                                    <th style="width: 80px;">操作</th>
                                </tr>
                                <tr v-for="(item, index) in product">
                                    <td v-show="false">
                                        <el-input v-model="item.product_id" ></el-input>
                                    </td>
                                    <td>
                                        <!-- <el-input v-model="item.product_number" readonly="readonly"></el-input> -->
                                        {{item.product_number}}
                                    </td>
                                    <td>
                                        {{item.product_name}}
                                    </td>
                                    <td>
                                        {{item.product_no}}
                                        <!-- <el-input v-model="item.product_no"  placeholder="型号"></el-input> -->
                                    </td>
                                    <td>
                                        <el-input v-model="item.unite" style="width: 100px;" placeholder="单位"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="item.num" style="width: 100px;" placeholder="数量" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input>
                                    </td>
                                    <!-- <td>
                                        <el-input v-model="item.price" style="width: 100px;" @keyup.native="calculationAmount(index)"  placeholder="单价"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input>
                                    </td> -->
                                    <!-- <td>
                                        {{item.total_price}}
                                    </td> -->
                                    <td>
                                        <!-- {{item.demand_time}} -->
                                        <el-date-picker
                                        style="width: 135px;"
                                        v-model="item.demand_time"
                                        type="date"
                                        readonly
                                        value-format='timestamp' 
                                        format="yyyy/MM/dd"
                                        placeholder="需求日期">
                                        </el-date-picker>
                                    </td>
                                    <td>
                                        <el-input v-model="item.tips" placeholder="请输入" type="textarea"></el-input>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning" @click="delawards11(index)">删除</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="add_button_new" style="margin-bottom: 5%">
                            <el-popover ref="add_product" placement="right" width="400" trigger="click">
                                    <div class="form-inline">
                                        <input type="text" class="form-control" placeholder="请输入产品名" v-model="searchProduct.name" @input="searchingProduct">
                                    </div>
                                    <el-table :data="searchProductRes" @cell-click="addProduct" highlight-current-row max-height="300" border>
                                        <el-table-column prop="product_id" v-if="false" label="物料编号" width="110"> </el-table-column>
                                        <el-table-column prop="product_no" label="系统内部编号"> </el-table-column>
                                        <el-table-column prop="product_name" label="型号"></el-table-column>
                                        <el-table-column prop="product_number" label="系统外部编号" width="150"></el-table-column>
                                    </el-table>
                                    <!-- <table class="table table-striped table-hover table-bordered">
                                        <tr>
                                            <th v-if="false">id</th>
                                            <th>系统内部编号</th>
                                            <th>型号</th>
                                            <th>系统外部编号</th>
                                        </tr>
                                        <tr v-for="item in searchProductRes" @click="addProduct(item)">
                                            <td v-if="false">{{item.product_id}}</td>
                                            <td>{{item.product_no}}</td>
                                            <td>{{item.product_name}}</td>
                                            <td>{{item.product_number}}</td>
                                        </tr>
                                    </table> -->
                                </el-popover>
                                <el-button v-popover:add_product type="primary" style="float: left;">新增产品</el-button>
                                <!-- <el-row  style="float: right;margin-top: 5px">
                                    <el-col :span="20">
                                        <el-form-item label="申请总金额:">
                                            <el-input v-model="form.total_amount" readonly="readonly"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row> -->
                        </div>
                    <br> 
            <br>
            <el-row>
                <el-col :span="23">
                    <el-form-item label="申请理由：">
                            <el-input type="textarea" v-model="form.apply_reason"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <br><br>
            <el-button type="success" @click="onSubmit(form)">提 交</el-button>
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
    var pickingType = <?php echo (json_encode($pickingType)); ?>;
    var auditTypeMap = <?php echo (json_encode($auditTypeMap)); ?>;
    var outOfTreasuryType = <?php echo (json_encode($outOfTreasuryType)); ?>;
    var staffData = <?php echo (json_encode($staffData)); ?>;
    var deptData = <?php echo (json_encode($deptData)); ?>;
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                loading:true,
                serial_Number:'1',  //序号
                form :{
                    id:'',
                    apply_id:'',
                    picking_kind:'',
                    apply_dept_name:'',
                    apply_dept_id:'',
                    total_amount :'0',
                    apply_reason:'',
                    apply_time:''
                },
                apply_dept_name001:'',
                searchProduct: {
                    name: ''
                },
                searchProductRes:[],
                options_picking_kind:[],
                options_source_type:[],
                options_apply_dept_name:[],
                timeout:  null,
                product:[]
            }
        },
        created : function () {
            this.loading = false
            this.options_picking_kind.length = 0
            this.options_apply_dept_name.length = 0
            for(let key in pickingType){
                this.options_picking_kind.push({'value':key,'label':pickingType[key]})
            }
            this.options_apply_dept_name = deptData
            
        },
        mounted() {
        },
        methods :{
            // 获取三个产品值
            searchingProduct: function() {
                $.post('<?php echo U("Dwin/Purchase/getProductMsg");?>', {'condition':this.searchProduct.name}, function(res) {
                    vm.searchProductRes = res.data
                })
            },
            // 下拉选中时
            addProduct: function(row) {
                function Item(product) {
                    this.product_id = product.product_id
                    this.product_no = product.product_no
                    this.product_name = product.product_name
                    this.product_number = product.product_number
                    this.demand_time = new Date()
                    // this.total_price = '0'
                }
                var obj = new Item(row)
                this.product.push(obj)
                for(var i = 0; i<vm.product.length ; i++){
                    vm.product[i].sort_id = i + 1
                }
            },
            // 获取金额
            calculationAmount (index) {
                if(vm.product[index].num == undefined || vm.product[index].price == undefined){

                }else{
                    vm.product[index].total_price = vm.product[index].num * vm.product[index].price
                    var zong_money = 0;
                    for(var i = 0;i < vm.product.length;i++){
                        zong_money = zong_money + Number(vm.product[i].total_price)
                    }
                    vm.form.total_amount = zong_money;
                }
            },
            // 获取编号
            getNumber(){
                $.post('<?php echo U("Dwin/Stock/createApplyId");?>', function (res) {
                    if(res.status == 200){
                        vm.form.apply_id = res.data.idString
                        vm.form.id = res.data.id
                    }
                    layer.msg(res.msg)
                })
            },
            // 删除
            delawards11 (index) {
                // vm.form.total_amount = vm.form.total_amount - vm.product[index].total_price
                vm.product.splice(index,1)
            },
            // 提交
            onSubmit(form){
                var judge_up = true
                if(judge_up){
                    for(var i = 0;i<vm.product.length;i++){
                        vm.product[i].demand_time = vm.product[i].demand_time / 1000
                    }
                    form.apply_dept_name = vm.apply_dept_name001.name
                    form.apply_dept_id = vm.apply_dept_name001.id
                    vm.form.apply_time = vm.form.apply_time / 1000
                    
                    var data = {
                        'baseMsg' : form,
                        'materialMsg' : vm.product
                    }
                    $.post('<?php echo U("Dwin/Stock/createOtherStockOutApply");?>', data , function (res) {
                        if(res.status == 200){
                            // 关闭弹框 刷新父页面
                            layer.close(layer.index);
                             // layer.open页面关闭
                            // var index=parent.layer.getFrameIndex(window.name);//获取窗口索引
                            // parent.layer.close(index)
                            window.parent.location.reload();
                        }else{
                            for(var i = 0;i<vm.product.length;i++){
                                vm.product[i].demand_time = vm.product[i].demand_time * 1000
                            }  
                            vm.form.apply_time = vm.form.apply_time * 1000
                        }
                        if(res.msg){
                            layer.msg(res.msg)
                        }
                    })
                }
            }
        }
    })
</script>
</html>