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
            height: 35px;
            line-height: 35px;
            text-align: left;
            padding-left: 10px;
            font-size: 15px;
        }
        .el-autocomplete{
            width: 100%;
        }
        .el-button--primary {
            float: left;
        }
        .deal_cent th{
            text-align: center
        }
        .add_button_new {
            text-align: left
        }
        .borderE4393C{
            border: 1px solid #e4393c;
        }
        .el-form-item__content{
            text-align: left !important
        }
    </style>
</head>
<body>
    <div id="app" style="text-align: center">
        <h1>采购物料入库信息</h1>
        <br><br>
        <el-row>
            <el-col :span="22" :offset="1">
                <el-form ref="form" :model="form" label-width="110px" size="medium" @submit.native.prevent v-loading="loading">
                    <div class="head_thead" style="font-weight: bold;">一、采购订单的基本情况</div>
                    <!-- GO -->
                    <el-row>
                        <el-col :span="8" :offset="1">
                            <el-form-item label="供应方名称：">
                                <el-input v-model="supplier_name" readonly="readonly"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8" :offset="5">
                            <el-form-item label="采购订单编号：">
                                <el-input v-model="purchase_order_id" readonly="readonly"></el-input>
                            </el-form-item>
                        </el-col>
                        
                    </el-row>
                    <el-row>
                        <el-col :span="8" :offset="1">
                            <el-form-item label="采购模式：">
                                <el-input v-model="purchase_mode" readonly="readonly"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8" :offset="5">
                            <el-form-item label="采购方式：">
                                <el-input v-model="purchase_type" readonly="readonly"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <br>

                    <table class="table table-striped table-hover table-bordered" border style="margin-bottom: 0">
                            <div class="head_thead">二、采购单购买的基本情况</div>
                            <tbody>
                                <tr class="deal_cent">      
                                    <th style="width: 140px;">物料型号</th>      
                                    <th style="width: 150px;">购买数量</th>
                                    <th style="width: 140px;">入库数量</th>  
                                </tr>
                                <tr v-for="(item, index) in product">
                                    <td>
                                        {{item.product_name}}
                                    </td>
                                    <td>
                                        {{item.allnum}}
                                        <!-- <el-input v-model="item.product_no"  placeholder="型号"></el-input> -->
                                    </td>
                                    <td>
                                        {{item.allinnum}}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <br>
                    <table class="table table-striped table-hover table-bordered" border style="margin-bottom: 0">
                            <div class="head_thead">三、添加所需的表单</div>
                            <tbody>
                                <tr class="deal_cent">   
                                    <th style="width: 70px">物料编号</th>      
                                    <th style="width: 140px;">物料型号</th>      
                                    <th style="width: 140px;">入库数量</th>      
                                    <th style="width: 150px;">库房</th>
                                    <th style="width: 150px;">不合格入库房</th>
                                </tr>
                                <tr v-for="(item, index) in product_tow">
                                    <td>
                                        <!-- <el-input v-model="index" placeholder="序号" readonly="readonly"></el-input> -->
                                        {{item.product_no}}
                                    </td>
                                    <td>
                                        {{item.product_name}}
                                    </td>
                                    <td>
                                        <!-- {{item.num}} -->
                                        <el-input v-model="item.num"    placeholder="请输入入库数量" @keyup.native="calculationAmount(item.num,index,$event)"></el-input>
                                    </td>
                                    <!-- :class="{on:index==guigeSpan}" -->
                                    <td>
                                            <el-select v-model="item.default_rep_id" placeholder="请选择物料入库仓库" filterable>
                                                <el-option
                                                    v-for="key1 in selectInfo.warehouse"
                                                    :key="key1.warehouse_id"
                                                    :label="key1.warehouse_name"
                                                    :value="key1.warehouse_id">
                                                </el-option>
                                            </el-select>
                                        <!-- <el-autocomplete
                                            class="inline-input"
                                            filterable
                                            v-model="item.warehouse"
                                            :fetch-suggestions="querySearch_default"
                                            placeholder="请选择库房"
                                        ></el-autocomplete> -->
                                    </td>
                                    <td>
                                        <el-select v-model="item.fail_rep_id" placeholder="请选择物料不合格入库仓库" filterable>
                                            <el-option
                                                v-for="key1 in selectInfo.warehouse"
                                                :key="key1.warehouse_id"
                                                :label="key1.warehouse_name"
                                                :value="key1.warehouse_id">
                                            </el-option>
                                        </el-select>
                                        <!-- <el-input v-model="item.fail_rep_id"  placeholder="不合格入库房"></el-input> -->
                                        <!-- {{item.warehouse_id}} -->
                                        <!-- <el-autocomplete
                                            class="inline-input"
                                            filterable
                                            v-model="item.fail_rep_id"
                                            :fetch-suggestions="querySearch_Notdefault"
                                            placeholder="请输入内容"
                                        ></el-autocomplete> -->
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    <!-- GO -->
                    <br>
                    <el-row>
                        <el-col :span="8" :offset="2">
                            <el-form-item label="审核人：" required>
                                <!-- <el-input v-model="form.auditor_name"></el-input> -->
                                <el-select v-model="stock.auditor"  @change="auditorSelect()"  placeholder="请选择审核人">
                                    <el-option
                                            v-for="item in selectInfo.auditor"
                                            :key="item.auditor_id"
                                            :label="item.auditor_name"
                                            :value="item.auditor_id">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="7" :offset="3">
                            <el-form-item label="付款日期：" required>
                                <!-- <el-input v-model="form.signing_time"></el-input> -->
                                <el-date-picker
                                style="width: 100%;"
                                v-model="stock.pay_time"
                                type="date"
                                value-format="timestamp" 
                                format="yyyy-MM-dd"
                                placeholder="选择日期">
                                </el-date-picker>
                            </el-form-item>
                            <!-- <el-form-item label="付款日期：">
                                <el-input v-model="form.create_time"></el-input>
                            </el-form-item> -->
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="8" :offset="2">
                            <el-form-item label="保管人：" required>
                                    <el-select v-model="stock.keep_id"  @change="keeperSelect()"  placeholder="请选择保管人">
                                        <el-option
                                                v-for="item in selectInfo.staff"
                                                :key="item.id"
                                                :label="item.name"
                                                :value="item.id">
                                        </el-option>
                                    </el-select>
                                <!-- <el-input v-model="form.purchase_type"></el-input> -->
                                <!-- <el-autocomplete
                                    class="inline-input"
                                    filterable
                                    v-model="form.keepArr"
                                    :fetch-suggestions="querySearch_custody"
                                    placeholder="请输入内容"
                                    @select="handleSelect_custody"
                                ></el-autocomplete> -->
                            </el-form-item>
                        </el-col>
                        <el-col :span="7" :offset="3">
                                <el-form-item label="对方单据：" required>
                                        <el-input v-model="stock.other_bill" placeholder="请输入单据" ></el-input>
                                    </el-form-item>
                            </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="7" :offset="2">
                                <el-form-item label="验收人：" required>
                                        <el-select v-model="stock.check_id"  @change="checkerSelect()"  placeholder="请选择验收人">
                                            <el-option
                                                    v-for="item in selectInfo.staff"
                                                    :key="item.id"
                                                    :label="item.name"
                                                    :value="item.id">
                                            </el-option>
                                        </el-select>
                                        <!-- <el-input v-model="form.id"></el-input> -->
                                        <!-- <el-input v-model="form.order_time"></el-input> -->
                                        <!-- <el-autocomplete
                                            class="inline-input"
                                            filterable
                                            v-model="form.acceptance_pop"
                                            :fetch-suggestions="querySearch_check"
                                            placeholder="请输入内容"
                                            @select="handleSelect_check"
                                        ></el-autocomplete> -->
                                    </el-form-item>
                            
                        </el-col>
                    </el-row>
                    <!-- END -->
                    <br><br>
                    <el-button type="success" @click="onSubmit(form)">提交入库</el-button>
                    <br><br>
                        
                </el-form>
            </el-col>
        </el-row>
            
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
<script src="https://cdn.bootcss.com/element-ui/2.3.6/index.js"></script>
<script>
    var orderData = <?php echo (json_encode($order)); ?>;
    var productData = <?php echo (json_encode($product)); ?>;
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                loading:false,
                form :{
                    auditArr:'',
                    pay_time:'',
                    acceptance_pop:'',
                    keepArr:'',
                    touching_pop:'',
                    salesman_pop:''
                },
                supplier_name:orderData.supplier_name,
                purchase_mode:orderData.purchase_mode,
                purchase_type:orderData.purchase_type,
                purchase_order_id:orderData.purchase_order_id,
                selectInfo:[],
                product:[
                    // {
                        // 'allnum'  :  全部数量（采购数量）
                        // 'allinnum' :  全部入库数量（合格入库数）
                        // 'surplusnum' : 剩余多少（可入库数量）
                        // "product_id" : 物料id（不渲染，需添加时传值）
                        // "product_no" : 物料编号（渲染）
                        // "product_name" : 物料型号（渲染）
                        // 'warehouse_id' : 物料默认入库（渲染，与selectInfo中warehouse数据组合，下拉框）

                    // }
                ],
                product_tow:[],
                stock : {
                    'source_id':'',
                    'other_bill':'',
                    'pay_time':'',
                    "supplier_id": '',
                    "dept_id"    : '',
                    "auditor"    : '',
                    "check_id"   : '',
                    "keep_id"    : '',
                    "type_id"    : ''
                },
                balance_default:[],     //库房
                // balance_Notdefault:[
                //     {'value':'0001'},
                //     {'value':'0002'},
                //     {'value':'0003'},
                //     {'value':'0004'}
                // ],   //不合格库房 
                // balance_auditor:[],         //审核人 
                // balance_check:[],       //验收   
                // balance_touching:[],     //制单  
                // balance_salesman:[],     //业务员
                // balance_custody:[],      //保管
                // saveID_auditor:'',         //审核人 
                // saveID_check:'',       //验收   
                // saveID_touching:'',     //制单  
                // saveID_salesman:'',     //业务员
                // saveID_custody:''      //保管
            }
        },
        created:function () {
            this.product = productData
            this.product_tow = productData
            this.stock.source_id = orderData.id
        },
        mounted:function() {
            $.post('<?php echo U("Dwin/Stock/getSelectInfo");?>',function (res) {
                vm.selectInfo = res.data
            })
        },
        methods :{
            // 判断输入的入库数量 和 采购数量  比较
            calculationAmount(value,index,$event){
                if(Number(value) > this.product[index].allnum){
                    layer.msg('填写入库数不能大于采购数量')
                     this.product_tow[index].num = value.slice(0,value.length - 1)
                }   
            },
            // 审核人
            auditorSelect: function () {
                var id = this.stock.auditor;
                var auditorIds = this.selectInfo.auditor;
                var aud_name = "";
                for (var i = 0; i < auditorIds.length; i++){
                    if (id === auditorIds[i].auditor_id){
                        aud_name = auditorIds[i].auditor_name
                    }
                }
                Vue.set(this.stock, 'auditorArr', id + '_' + aud_name)
            },
            keeperSelect: function () {
                var id = this.stock.keep_id;
                var auditorIds = this.selectInfo.staff;
                var aud_name = "";
                for (var i = 0; i < auditorIds.length; i++){
                    if (id === auditorIds[i].id){
                        aud_name = auditorIds[i].name
                    }
                }
                Vue.set(this.stock, 'keepArr', id + '_' + aud_name)
            },
            checkerSelect: function () {
                var id = this.stock.check_id;
                var staffIds = this.selectInfo.staff;
                var aud_name = "";
                for (var i = 0; i < staffIds.length; i++){
                    if (id === staffIds[i].id){
                        aud_name = staffIds[i].name
                    }
                }
                Vue.set(this.stock, 'checkArr', id + '_' + aud_name)
            },

            // 提交
            onSubmit: function (form) {
                var timeSave = []
                timeSave.push(Number(vm.stock.pay_time) / 1000)
                var materialSave = []
                for(var i = 0;i<vm.product_tow.length;i++){
                    var materialSaveOBJ = {}
                    materialSaveOBJ = {
                        'product_id' : vm.product_tow[i].product_id,
                        'product_no' : vm.product_tow[i].product_no,
                        'num' : vm.product_tow[i].num,
                        'default_rep_id' : vm.product_tow[i].default_rep_id,
                        'fail_rep_id' : vm.product_tow[i].fail_rep_id
                    }
                    materialSave.push(materialSaveOBJ)
                }
                
                var data = {
                    'base':{
                        'source_id'  :  vm.stock.source_id,
                        'other_bill' : vm.stock.other_bill,
                        'pay_time'   : timeSave[0],
                        'auditorArr'   : vm.stock.auditorArr,
                        'keepArr'    : vm.stock.keepArr,
                        'checkArr'   : vm.stock.checkArr
                    },
                    'material' : materialSave
                }
                $.post('<?php echo U("/Dwin/Stock/addStockInWithPurchase");?>', data , function (res) {
                    if(res.status){
                        if(res.status == 200){
                            // 关闭弹框 刷新父页面
                            layer.msg(res.msg,{
                                time:1000
                            }, function () {
                                layer.close(layer.index);
                                var index = parent.layer.getFrameIndex(window.name);
                                parent.layer.close(index);
                            })
                        }
                        layer.msg(res.msg)

                    }else{
                        layer.msg(res)
                    }
                }) 
            }
        }
    })
</script>
</html>