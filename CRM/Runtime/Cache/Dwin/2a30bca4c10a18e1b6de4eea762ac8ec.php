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
        <h1>湖南迪文科技有限公司出库申请单修改</h1>
        <br><br><br>
        <el-form ref="form" :model="form" label-width="150px" size="medium" @submit.native.prevent v-loading="loading">
            <el-row>
                <el-col :span="7" :offset="1">
                    <el-form-item label="申请单编号：">
                        <el-input v-model="form.apply_id" style="width: 100%;" disabled></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="7" :offset="3">
                    <el-form-item label="领料类型：">
                        <el-select v-model="form.picking_kind" filterable placeholder="请选择">
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
                <el-col :span="7" :offset="1">
                    <el-form-item label="申领部门：">
                        <el-select v-model="form.apply_dept_name" value-key="id" filterable placeholder="请选择">
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
                                            <el-input style="width: 100px;" v-model="item.unite" placeholder="单位"></el-input>
                                        </td>
                                        <td>
                                            <el-input style="width: 100px;" v-model="item.num" placeholder="数量" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input>
                                        </td>
                                        <!-- <td>
                                            <el-input style="width: 100px;" v-model="item.price" @keyup.native="calculationAmount(index)"  placeholder="单价"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input>
                                        </td> -->
                                        <!-- <td>
                                            {{item.total_price}}
                                        </td> -->
                                        <td>
                                            <el-date-picker
                                            style="width: 135px;"
                                            v-model="item.demand_time"
                                            type="date"
                                            readonly
                                            value-format="timestamp" 
                                            format="yyyy-MM-dd"
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
                            <div class="add_button_new">
                                <el-popover ref="add_product" placement="right" width="400" trigger="click">
                                        <div class="form-inline">
                                            <input type="text" class="form-control" placeholder="请输入产品名" v-model="searchProduct.name" @input="searchingProduct">
                                        </div>
                                        <table class="table table-striped table-hover table-bordered">
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
                                        </table>
                                    </el-popover>
                                    <el-button v-popover:add_product type="primary">新增产品</el-button>
                            </div>
                    <br>
                    <!-- <el-row :gutter="20">
                        <el-col :span="10">
                            <p></p>
                        </el-col>
                        <el-col :span="9" label-width="150px" :offset="2">
                            <el-row>
                                <el-col :span="20" label>
                                    <el-form-item label="申请总金额:">
                                        <el-input v-model="form.total_amount" readonly="readonly"></el-input>
                                    </el-form-item>
                                </el-col>
                               </el-row>
                               <el-row>
                                <el-col :span="20">
                                    <el-form-item label="合计金额（大写）:">
                                        <el-input v-model="form.capital_amount" disabled></el-input>
                                    </el-form-item>
                                 </el-col>
                               </el-row>
                        </el-col>
                    </el-row> -->
                        
            <br>
            <el-row>
                    <el-col :span="10">
                        <!-- <span style="font-size: 14px;color: #606266;font-weight: bold;margin-left: -19%;">签订地点：湖南</span> -->
                        <el-form-item label="申请时间：">
                                <el-date-picker
                                v-model="form.apply_time"
                                type="date"
                                value-format="timestamp"
                                format="yyyy-MM-dd"
                                placeholder="选择申请日期">
                              </el-date-picker>
                        </el-form-item>
                    </el-col>
                    <el-col :span="10"  :offset="2">
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
    var applyData = <?php echo (json_encode($applyData)); ?>;
    var materialData = <?php echo (json_encode($materialData)); ?>;
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                loading:true,
                serial_Number:'1',  //序号
                form :{
                    source_type:'0',
                    apply_id:'',
                    picking_kind:'',
                    apply_dept_name:'',
                    total_amount :'0',
                    capital_amount:'零',
                    apply_reason:'',
                    apply_time:0
                },
                searchProduct: {
                    name: ''
                },
                searchProductRes:[],
                options_picking_kind:[],
                options_source_type:[],
                options_apply_dept_name:[],
                timeout:  null,
                product:[],
                initial_row:[],
                add_operate:[],
                edit_operate:[]
            }
        },
        created : function () {
            this.loading = false
            this.initial_row.length = 0
            this.options_picking_kind.length = 0
            this.options_apply_dept_name.length = 0
            for(let key in pickingType){
                this.options_picking_kind.push({'value':key,'label':pickingType[key]})
            }
            this.options_apply_dept_name = deptData
            this.form.apply_time = Number(this.form.apply_time)
            this.form = applyData
            this.form.apply_time =  this.form.apply_time * 1000
            this.product = materialData
            for(var i = 0;i < this.product.length;i++){
                this.product[i].demand_time = this.product[i].demand_time * 1000
            }
            for(var i = 0;i< this.product.length;i++){
                this.initial_row.push(this.product[i])
            }
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
            addProduct: function(item) {
                function Item(product) {
                    this.product_id = product.product_id
                    this.product_no = product.product_no
                    this.product_name = product.product_name
                    this.product_number = product.product_number
                    this.demand_time = new Date()
                    // this.total_price = '0'
                }
                var obj = new Item(item)
                this.product.push(obj)
                for(var i = 0; i<vm.product.length ; i++){
                    vm.product[i].sort_id = i + 1
                }
            },
            // 获取金额
            calculationAmount (index) {
                if(vm.product[index].num == undefined || vm.product[index].price == undefined){

                }else{
                    // vm.product[index].total_price = vm.product[index].num * vm.product[index].price
                    var zong_money = 0;
                    for(var i = 0;i < vm.product.length;i++){
                        zong_money = zong_money + Number(vm.product[i].total_price)
                    }
                    vm.form.total_amount = zong_money;
                }
            },
            // 删除
            delawards11 (index) {   
                if(this.initial_row == 0 || this.product.length > this.initial_row.length){
                    // vm.form.total_amount = vm.form.total_amount - this.product[index].total_price
                    vm.product.splice(index,1)
                }else{
                    // 删除
                    var data = {
                        'applyId':this.product[index].apply_id,
                        'materialId':this.product[index].id
                    }
                    $.post('<?php echo U("Dwin/Stock/delOtherApplyMaterial");?>',data,function(res){
                        if(res.status == 200){
                            layer.msg(res.msg)
                            vm.product.splice(index,1) 
                            // // 总金额减数
                            // vm.form.total_amount = res.data.total_amount
                            // vm.form.capital_amount = res.data.capital_amount
                        }
                    })
                }
            }, 
            // 提交
            onSubmit(form){
                var judge_up = true
                var k = -1
                var list_num = 0
                if(judge_up){
                    this.product.forEach(function (e) {
                        for(var key in e){
                            if(key == 'product_no' || key == 'product_name' || key == 'product_number' || key == 'num' || key == 'demand_time'){
                                list_num++
                            }
                        }
                        if(judge_up){
                            if(list_num < 5){
                                layer.msg('产品名称、型号、单位、金额、需求时间及数量都是必填项,请填写完整！')
                                judge_up = false
                            }
                        }
                        list_num = 0
                    })
                }
                if(judge_up){
                    this.add_operate.length = 0
                    this.edit_operate.length = 0
                     // 判断数据是修改还是新增还是删除
                    for(var i = 0;i<vm.product.length;i++){
                        vm.product[i].demand_time = Number(vm.product[i].demand_time) / 1000
                    }
                    vm.form.apply_time = vm.form.apply_time / 1000
                    for(var j = 0;j<this.product.length;j++){
                        if(this.product[j].id == undefined){   // 说明不存在
                            this.add_operate.push(this.product[j])
                        }else{
                            this.edit_operate.push(this.product[j])
                        }
                    }
                    var data = {
                        'apply':this.form,
                        'edit_material' : this.edit_operate,
                        'new_material' : this.add_operate
                    }
                    $.post('<?php echo U("Dwin/Stock/editOtherStockOutApply");?>', data , function (res) {
                        if(res.status == 200){
                            // 关闭弹框 刷新父页面
                            location.reload();
                        }else{
                            for(var i = 0;i<vm.product.length;i++){
                                vm.product[i].demand_time = vm.product[i].demand_time * 1000
                            }  
                            vm.form.apply_time = vm.form.apply_time * 1000
                        }
                        layer.msg(res.msg)
                })
                }
            }
        }
    })
</script>
</html>