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
        <h1>湖南迪文科技有限公司出库申请单下推</h1>
        <br><br><br>
        <el-form ref="form" :model="form" label-width="150px" size="medium" @submit.native.prevent v-loading="loading" :rules="rules">
            <el-row>
                <el-col :span="10" :offset="1">
                    <el-form-item label="出库单编号：" prop="stock_out_id">
                        <el-input v-model="form.stock_out_id" style="width: 60%;" readonly></el-input>
                        <el-button type="primary" plain @click="getNumber()">获取编号</el-button>
                    </el-form-item>
                </el-col>
                <el-col :span="8"  v-show="false">
                    <el-form-item label="申请单编号：">
                        <el-input v-model="form.source_id" style="width: 100%;" readonly></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8" >
                    <el-form-item label="领料类型：" required>
                            <!-- <el-input v-model="form.picking_kind" style="width: 100%;" readonly></el-input> -->
                        <el-select v-model="form.picking_kind" filterable placeholder="请选择" style="width: 100%;">
                            <el-option
                                v-for="item in options_picking_kind"
                                disabled
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item> 
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="8" :offset="1">
                    <el-form-item label="申领部门：" prop="picking_dept_name">
                            <el-input v-model="form.picking_dept_name" style="width: 100%;" readonly></el-input>
                            <el-input v-show="false" v-model="form.picking_dept_id" style="width: 100%;" readonly></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8" :offset="2">
                        <!-- <span style="font-size: 14px;color: #606266;font-weight: bold;margin-left: -19%;">签订地点：湖南</span> -->
                        <el-form-item label="申请时间：" required>
                                <el-date-picker
                                style="width: 100%;"
                                v-model="form.apply_time"
                                type="date"
                                disabled
                                value-format="timestamp"
                                format="yyyy-MM-dd"
                                placeholder="选择申请日期">
                              </el-date-picker>
                        </el-form-item>
                    </el-col>
            </el-row>
            <el-row>
                <el-col :span="8" :offset="1">
                        <el-form-item label="出库类型：" required>
                                <!-- <el-input v-model="form.picking_dept_name" style="width: 100%;" placeholder="请填写工程项目"></el-input> -->
                                 <el-input v-if="false" v-model="form.purchase_cate_id" style="width: 100%;" placeholder="请填写工程项目"></el-input>
                                 <el-input v-if="type_show" v-model="form.purchase_cate_name" diabled style="width: 100%;" placeholder="请填写工程项目"></el-input>
                                 <el-select v-model="purchase_cate_name001" v-if="!type_show"  value-key="id" filterable placeholder="请选择" style="width: 100%;">
                                     <el-option
                                     v-for="item in options_purchase_cate_name"
                                     :key="item.id"
                                     :label="item.value"
                                     :value="item"
                                     >
                                 </el-option>
                             </el-select>
                         </el-form-item> 
                    
                </el-col>
                <el-col :span="8" :offset="2">
                        <el-form-item label="选单号：" prop="choose_no">
                            <el-input v-model="form.choose_no" style="width: 100%;" placeholder="请填写选单号"></el-input>
                        </el-form-item>
                    </el-col>
            </el-row>
            <el-row>
                <el-col :span="8" :offset="1">
                    <el-form-item label="用途：">
                        <el-input v-model="form.purpose" style="width: 100%;" placeholder="请填写用途"></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8" :offset="2">
                        <el-form-item label="打印次数：">
                            <el-input v-model="form.printing_times" style="width: 100%;" disabled></el-input>
                        </el-form-item>
                    </el-col>
            </el-row>
            <el-row>
                <el-col :span="20" :offset="1">
                        <el-form-item label="工程项目：">
                                <el-input v-model="form.engine_item" style="width: 100%;" placeholder="请填写工程项目"></el-input>
                        </el-form-item>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :span="22" :offset="1">
                        <table class="table table-striped table-hover table-bordered" border style="margin-bottom: 0">
                                <div class="head_thead">一、产品名称、型号、单位、金额、需求时间及申请数量</div>
                                <tbody>
                                    <tr  class="deal_cent">
                                        <th v-show="false">source_id</th>      
                                        <th v-show="false">ID</th>      
                                        <th >物料名称</th>      
                                        <th >物料型号</th>      
                                        <th >物料编号</th>     
                                        <th >库存</th>     
                                        <!-- <th>单位</th> -->
                                        <th>申请数量</th>
                                        <!-- <th>单价(元)</th> -->
                                        <!-- <th>总金额(元)</th> -->
                                        <!-- <th style="width: 80px;">需求时间</th> -->
                                        <th>出货仓库</th>
                                        <th>备注</th>
                                        <!-- <th >备注</th> -->
                                        <!-- <th style="width: 80px;">操作</th> -->
                                    </tr>
                                    <tr v-for="(item, index) in product">
                                        <td v-show="false">
                                            <el-input v-model="item.source_id" ></el-input>
                                        </td>
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
                                            {{Number(item.stock_number) + Number(item.o_audit)}}
                                            <!-- {{Number(item.stock_number) + Number(item.o_audit)}} -->
                                        </td>
                                        <td>
                                            {{item.num}}
                                            <!-- <el-input v-model="item.num" @keyup.native="calculationAmount(index)" placeholder="数量" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input> -->
                                        </td>
                                        <td>
                                                <el-select v-model="item.rep_pid" value-key="id"  @change ="select_rep_pid(index,item)" filterable placeholder="请选择">
                                                    <el-option
                                                        v-for="item in options_rep_pid"
                                                        :key="item.id"
                                                        :label="item.repertory_name"
                                                        :value="item.rep_id"
                                                        >
                                                    </el-option>
                                                </el-select>
                                            <!-- <el-input v-model="item.stock_out_num" placeholder="请输入" type="textarea"></el-input> -->
                                        </td>
                                        <td>
                                            <el-input v-model="item.tips" placeholder="请输入备注" type="textarea"></el-input>
                                        </td>
                                        <!-- <td>
                                            <button class="btn btn-warning" @click="delawards11(index)">删除</button>
                                        </td> -->
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
                                    <!-- <el-button v-popover:add_product type="primary">新增产品</el-button> -->
                            </div>
                        
            <br>
            <el-row>
                    <el-col :span="7">
                        <el-form-item label="负责人：" required>  
                            <el-input v-if="false" v-model="form.charge_id" style="width: 100%;"></el-input>
                            <el-input v-if="false" v-model="form.charge_name" style="width: 100%;"></el-input>
                            <el-select v-model="charge_name001" value-key="id" filterable placeholder="请选择">
                                <el-option
                                    v-for="item in options_charge_name"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item"
                                    >
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="7" :offset="1">
                        <el-form-item label="制单人：" required>
                                <el-input v-if="false" v-model="form.create_id" style="width: 100%;"></el-input>
                                <!-- <el-input v-if="false" v-model="form.create_name" style="width: 100%;"></el-input> -->
                                <el-input v-model="form.create_name" placeholder="请输入" type="text" readonly></el-input>
                            <!-- <el-select v-model="form.create_name" value-key="id" filterable placeholder="请选择">
                                <el-option
                                    v-for="item in options_create_name"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item"
                                    >
                                </el-option>
                            </el-select> -->
                        </el-form-item>
                </el-col>
                <el-col :span="7" :offset="1">
                    <el-form-item label="审核人：" required>
                            <el-input v-if="false" v-model="form.audit_id" style="width: 100%;"></el-input>
                            <el-input v-if="false" v-model="form.audit_name" style="width: 100%;"></el-input>
                        <el-select v-model="audit_name001" value-key="id" filterable placeholder="请选择">
                            <el-option
                                v-for="item in options_audit_name"
                                :key="item.id"
                                :label="item.name"
                                :value="item"
                                >
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="7">
                        <el-form-item label="领料人：" required>
                            <el-input v-if="false" v-model="form.collect_id" style="width: 100%;"></el-input>
                            <el-input v-if="false" v-model="form.collect_name" style="width: 100%;"></el-input>
                            <el-select v-model="collect_name001" value-key="id" filterable placeholder="请选择">
                                <el-option
                                    v-for="item in options_collect_name"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item"
                                    >
                                </el-option>
                            </el-select>
                        </el-form-item>
                </el-col>
                <el-col :span="7" :offset="1">
                    <el-form-item label="发货人：" required>
                            <el-input v-if="false" v-model="form.send_id" style="width: 100%;"></el-input>
                            <el-input v-if="false" v-model="form.send_name" style="width: 100%;"></el-input>
                        <el-select v-model="send_name001" value-key="id" filterable placeholder="请选择">
                            <el-option
                                v-for="item in options_send_name"
                                :key="item.id"
                                :label="item.name"
                                :value="item"
                                >
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="7" :offset="1">
                        <el-form-item label="记账人：" required>
                            <el-input v-if="false" v-model="form.account_id" style="width: 100%;"></el-input>
                            <el-input v-if="false" v-model="form.account_name" style="width: 100%;"></el-input>
                            <el-select v-model="account_name001" value-key="id" filterable placeholder="请选择">
                                <el-option
                                    v-for="item in options_account_name"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item"
                                    >
                                </el-option>
                            </el-select>
                        </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="7">
                    <el-form-item label="业务员：" required>
                            <el-input v-if="false" v-model="form.business_id" style="width: 100%;"></el-input>
                            <el-input v-model="form.business_name" style="width: 100%;" readonly></el-input>
                            <!-- <el-select v-model="business_name001" value-key="id" filterable placeholder="请选择">
                                <el-option
                                    v-for="item in options_business_name"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item"
                                    >
                                </el-option>
                            </el-select> -->
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col>
                    <el-form-item label="备注：">
                        <el-input type="textarea" v-model="form.tips" style="width: 100%;"></el-input>
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
    var repMap = <?php echo (json_encode($repMap)); ?>;  //仓库名称
    var pickingType = <?php echo (json_encode($pickingType)); ?>;  //领料类型
    var auditTypeMap = <?php echo (json_encode($auditTypeMap)); ?>;  //审核类型
    var outOfTreasuryType = <?php echo (json_encode($outOfTreasuryType)); ?>;  //其他出库类型中的出库类别
    var staffData = <?php echo (json_encode($staffData)); ?>;  //员工列表
    var applyData = <?php echo (json_encode($applyData)); ?>;  //申请单数据
    var deptData = <?php echo (json_encode($deptData)); ?>;  //部门列表
    var materialData = <?php echo (json_encode($materialData)); ?>;  //申请单物料数据
    var create_name = <?php echo (json_encode($create_name)); ?>;  //创建人
    var cate_id = <?php echo (json_encode($cate_id)); ?>;  //当前出库单类型ID
    var cate_name = <?php echo (json_encode($cate_name)); ?>;  //出库类型名称
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                loading:true,
                serial_Number:'1',  //序号
                type_show:false,
                form :{
                    id:'',
                    stock_out_id:'',
                    source_id:'',
                    picking_kind:'',
                    // total_amount :'0',
                    // capital_amount:'零',
                    apply_time:0,
                    cate_name:'',
                    create_id:'',
                    create_name:'',
                    audit_name:'',
                    send_name:'',
                    account_name:'',
                    business_name:'',
                    charge_name:'',
                    purpose:'',
                    choose_no:'',
                    purchase_cate_name:'',
                    purchase_cate_id:'',
                    charge_id:'',
                    collect_id:'',
                    audit_id:'',
                    send_id:'',
                    account_id:'',
                    business_id:'',
                    tips:''
                },
                purchase_cate_name001:'',
                charge_name001:'',
                audit_name001:'',
                collect_name001:'',
                send_name001:'',
                account_name001:'',
                business_name001:'',
                searchProduct: {
                    name: ''
                },
                searchProductRes:[],
                options_picking_kind:[],
                options_source_type:[],
                options_apply_dept_name:[],
                options_rep_pid:[],
                options_purchase_cate_name:[],
                options_create_name:[],
                options_audit_name:[],
                options_collect_name:[],
                options_send_name:[],
                options_business_name:[],
                timeout:  null,
                product:[],
                initial_row:[],
                add_operate:[],
                edit_operate:[],
                rules:{
                    stock_out_id:[{required:true,message:'编号不能为空',trigger:'change'}],
                    choose_no:[{required:true,message:'选单号不能为空',trigger:'change'}],
                    picking_dept_name:[{required:true,message:'申领部门不能为空',trigger:'change'}]
                }
            }
        },
        created : function () {
            this.loading = false
            this.initial_row.length = 0
            this.options_picking_kind.length = 0
            this.options_apply_dept_name.length = 0
            for(let key in pickingType){
                this.options_picking_kind.push({'value':key,'label':pickingType[key]})
                if(applyData.picking_kind == key){
                    this.form.picking_kind = pickingType[key]
                    if(key == 205){
                        this.type_show = true
                        this.form.purchase_cate_name = outOfTreasuryType[206]
                    }else{
                        this.type_show = false
                    }
                }
            }
            console.log('this.options_picking_kind[9-1].label',this.options_picking_kind)
            this.form.apply_id = applyData.apply_id
            this.form.id = applyData.id
            this.form.source_id = applyData.id
            
            // this.form.picking_kind = this.options_picking_kind[9-1].label
            this.form.picking_dept_name = applyData.apply_dept_name
            this.form.picking_dept_id = applyData.apply_dept_id
            this.form.apply_time =  applyData.apply_time
            // this.form.total_amount = applyData.total_amount
            // this.form.capital_amount = applyData.capital_amount
            this.form.business_name = applyData.create_name
            this.form.business_id = applyData.create_id
            this.form.apply_time =  this.form.apply_time * 1000

            // 物料赋值
            for(let i in materialData){
                var obJ = {
                    source_id:'',
                    product_id:'',
                    product_number:'',
                    product_name:'',
                    product_no:'',
                    unite:'',
                    num:'',
                    price:'',
                    total_price:'',
                    demand_time:'',
                    rep_pid:'',
                    tips:''
                }
                this.product.push(obJ)
                this.product[i].source_id = materialData[i].apply_id
                this.product[i].product_id = materialData[i].product_id
                this.product[i].product_number = materialData[i].product_number
                this.product[i].product_name = materialData[i].product_name
                this.product[i].product_no = materialData[i].product_no
                // this.product[i].unite = materialData[i].unite
                this.product[i].num = materialData[i].num
                // this.product[i].price = materialData[i].price
                this.product[i].tips = materialData[i].tips
                this.product[i].stock_number = materialData[i].stock_number
                this.product[i].o_audit = materialData[i].o_audit
                // this.product[i].total_price = materialData[i].total_price
                // this.product[i].demand_time = materialData[i].demand_time
                for(let key in repMap){
                    if(materialData[i].default_rep_id == key){
                        this.product[i].rep_pid = repMap[key].rep_id
                    }
                }
            }



            for(var i = 0;i < this.product.length;i++){
                this.product[i].demand_time = this.product[i].demand_time * 1000
            }
            for(var i = 0;i< this.product.length;i++){
                this.initial_row.push(this.product[i])
            }
            // 类别
            for(var key in outOfTreasuryType){
                this.options_purchase_cate_name.push({'id':key,'value':outOfTreasuryType[key]})
            }
            //     console.table(this.form.picking_kind)
            //  if(this.form.picking_kind == '生产改件'){
            //     console.table(this.form.picking_kind)
            // }
            // this.form.picking_kind = this.options_picking_kind[9-1].label
            this.options_purchase_cate_name.id = 206

            this.form.cate_name = cate_name
            this.form.create_id =  cate_id
            this.form.create_name = create_name
            this.options_rep_pid = repMap

            this.options_audit_name = staffData
            this.options_collect_name = staffData
            this.options_send_name = staffData
            this.options_account_name = staffData
            this.options_business_name = staffData
            this.options_charge_name = staffData
        },
        mounted() {
        },
        methods :{
            // 获取编号
            getNumber(){
                this.loading = true;
                var data = {
                    'source_kind':cate_id
                }
                $.post('<?php echo U("Dwin/Stock/createStockOutId");?>',data, function (res) {
                    if(res.status == 200){
                        vm.loading = false
                        vm.form.stock_out_id = res.data.idString 
                        vm.form.id = res.data.id
                    }else{
                        layer.msg(res.msg)
                        vm.loading = false
                    }
                })
            },
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
                    this.total_price = '0'
                }
                var obj = new Item(item)
                this.product.push(obj)
                for(var i = 0; i<vm.product.length ; i++){
                    vm.product[i].sort_id = i + 1
                }
            },
            // 仓库下拉
            select_rep_pid(index,row){
                var data = {    
                    materialId:row.product_id,
                    repId:row.rep_pid
                }
                $.post('<?php echo U("Dwin/Stock/getStockMsgOne");?>',data,function(res){
                    if(res.status == 200){ 
                        Vue.set(vm.product,index,{
                            o_audit:res.data.o_audit,
                            stock_number:res.data.stock_number,
                            product_id:row.product_id,
                            product_name:row.product_name,
                            product_no:row.product_no,
                            product_number:row.product_number,
                            rep_pid:row.rep_pid,
                            tips:row.tips,
                            num: materialData[index].num
                        });
                        // if((Number(res.data.stock_number) + Number(res.data.o_audit)) < Number(row.num)){
                        //     row.num = 0
                        // }else{
                        //     row.num = materialData[index].num
                        // }
                    }else{
                        vm.product[index].o_audit = 0
                        vm.product[index].stock_number = 0
                        // row.num = 0
                        layer.msg(res.msg)
                    }
                })
            },
            // 删除
            delawards11 (index) {   
                if(this.initial_row == 0 || this.product.length > this.initial_row.length){
                    vm.form.total_amount = vm.form.total_amount - this.product[index].total_price
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
                        }
                    })
                }
            }, 
            // 提交
            onSubmit(form){
                var judge_up = true
                if(judge_up){

                    form.audit_name = vm.audit_name001.name
                    form.audit_id = vm.audit_name001.id


                    form.collect_name = vm.audit_name001.name
                    form.collect_id = vm.collect_name001.id


                    form.send_name = vm.send_name001.name
                    form.send_id = vm.send_name001.id


                    form.account_name = vm.account_name001.name
                    form.account_id = vm.account_name001.id


                    // form.business_name = vm.business_name001.name
                    // form.business_id = vm.business_name001.id


                    form.charge_name = vm.charge_name001.name
                    form.charge_id = vm.charge_name001.id


                    form.purchase_cate_name = vm.purchase_cate_name001.value
                    form.purchase_cate_id = vm.purchase_cate_name001.id
                    vm.form.apply_time = vm.form.apply_time / 1000
                    this.form.picking_kind = this.options_picking_kind[9-1].value
                    var productSave1 = []
                    for(let i = 0;i < vm.product.length;i++){
                        productSave1.push({
                            'source_id':vm.product[i].source_id,
                            'product_id':vm.product[i].product_id,
                            'product_no':vm.product[i].product_no,
                            'num':vm.product[i].num,
                            'rep_pid':vm.product[i].rep_pid
                        })
                    }
                    var data = {
                        'stock' : form,
                        'material' : productSave1
                    }
                
                    $.post('<?php echo U("Dwin/Stock/createOtherStockOut");?>', data , function (res) {
                        if(res.status == 200){
                            // 关闭弹框 刷新父页面
                            layer.close(layer.index);
                            window.parent.location.reload();
                        }else{
                            vm.form.apply_time = vm.form.apply_time * 1000
                        }
                        layer.msg(res.msg)
                })
                }
            },
            // 时间戳转化为时间
            formatDateTime:function (timeStamp) {
                if(timeStamp != ''&&timeStamp != 0){
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
                }else{
                    return ''
                }
            }
        }
    })
</script>
</html>