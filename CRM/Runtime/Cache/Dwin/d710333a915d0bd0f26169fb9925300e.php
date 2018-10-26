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
        .title_name_product{
            font-size:16px;
            text-align: left;
            margin-left:2%; 
        }
    </style>
</head>
<body>
    <div id="app" style="text-align: center">
        <h1>湖南迪文科技有限公司销售出库单编辑</h1>
        <br><br><br>
        <el-row>
            <el-col :span="22" :offset="1">
                    <el-form ref="form" :model="form" label-width="120px" size="medium" @submit.native.prevent v-loading="loading" :rules="rules">
                            <el-row>
                                <el-col :span="8"  :offset="1">
                                    <el-form-item label="销售出库单号："  :offset="1">
                                        <el-input v-model="form.stock_out_id" style="width: 100%;" disabled></el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="10" :offset="2">
                                        <el-form-item label="选单号：" prop="choose_no">
                                            <el-input v-model="form.choose_no" style="width: 100%;"></el-input>
                                        </el-form-item>
                                    <!-- <el-form-item label="购买单位：">
                                            <el-input v-model="form.cus_name" style="width: 100%;" disabled></el-input>
                                    </el-form-item> -->
                                </el-col>
                            </el-row>
                            <el-row>
                                <el-col :span="8"  :offset="1">
                                    <el-form-item label="快递单号：" prop="express_no">
                                        <el-input v-model="form.express_no" style="width: 100%;" placeholder="请填写快递单号"></el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="10" :offset="2">
                                    <el-form-item label="源单类型：">
                                        <el-input v-model="form.source_kind" disabled style="width: 100%;"></el-input>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                            <!-- <el-row>
                                <el-col :span="8"  :offset="1">
                                    <el-form-item label="选单号：" prop="choose_no">
                                        <el-input v-model="form.choose_no" style="width: 100%;"></el-input>
                                    </el-form-item>
                                </el-col>
                            </el-row> -->
                            <el-row>
                                    <el-col :span="23">
                                        <el-form-item label="收货地址：">
                                            <el-input v-model="form.receiver_addr" style="width: 100%;" disabled></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            <el-row>
                                    <el-col :span="23">
                                        <el-form-item label="发货地址：">
                                            <el-input v-model="form.invoice_addr" style="width: 100%;" placeholder="请填写用途" disabled></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            <el-row :gutter="20">
                                <el-col :span="24">
                                            <!-- 表格 -->
                                            <div>
                                                <br>
                                                <p class="title_name_product"><b>一、订单物料信息 </b></p>
                                                <el-table ref="multipleTable" :data="product_old" tooltip-effect="dark" style="width: 100%" @selection-change="handleSelectionChange" border>
                                                <el-table-column v-if="false" label="source_id" prop="id" type="index" align="center" header-align="center" width="50"></el-table-column>
                                                <el-table-column v-if="false" label="product_id" prop="product_id" align="center" header-align="center"></el-table-column>
                                                <el-table-column label="序号" type="index" :index="indexMethod"> </el-table-column>
                                                <el-table-column label="物料名称" prop="product_number" align="center" header-align="center"> </el-table-column>
                                                <el-table-column label="物料型号" prop="product_name" align="center" header-align="center"> </el-table-column>
                                                <el-table-column label="物料编号" prop="product_no" align="center" header-align="center"></el-table-column>
                                                <el-table-column label="待出库数量" prop="out_processing" align="center" header-align="center"></el-table-column>
                                                <el-table-column label="锁库数量" prop="o_audit" align="center" header-align="center"></el-table-column>
                                                <el-table-column label="库存数量" prop="stock_number" align="center" header-align="center"></el-table-column>
                                                <el-table-column  label="需求量" prop="product_num" align="center" header-align="center" width="50"></el-table-column>
                                                <el-table-column  label="已出数量" prop="used_num"  align="center" header-align="center" width="50"></el-table-column>
                                            </el-table>
                                            </div>
                                            
                                            <div>
                                                <br>
                                                <p class="title_name_product"><b>二、出货单物料信息 </b></p>
                                                <el-table ref="multipleTable" :data="product" tooltip-effect="dark"  style="width: 100%" @selection-change="handleSelectionChange" border>
                                                <el-table-column v-if="false" label="id" prop="id" type="index" align="center" header-align="center" width="50"></el-table-column>
                                                <el-table-column v-if="false" label="source_id" prop="id" type="index" align="center" header-align="center" width="50"></el-table-column>
                                                <el-table-column v-if="false" label="product_id" prop="product_id" align="center" header-align="center"></el-table-column>
                                                <el-table-column label="序号" type="index" :index="indexMethod"> </el-table-column>
                                                <el-table-column label="物料名称" prop="product_number" align="center" header-align="center"> </el-table-column>
                                                <el-table-column label="物料型号" prop="product_name" align="center" header-align="center"> </el-table-column>
                                                <el-table-column label="物料编号" prop="product_no" align="center" header-align="center"></el-table-column>
                                                <el-table-column label="待出数量" prop="out_processing" align="center" header-align="center"></el-table-column>
                                                <!-- <el-table-column label="锁库数量" prop="o_audit" align="center" header-align="center"></el-table-column> -->
                                                <el-table-column label="库存数量" align="center" header-align="center">
                                                    <template slot-scope="scope">
                                                        {{Number(scope.row.stock_number) + Number(scope.row.o_audit)}}
                                                    </template>
                                                </el-table-column>
                                                <el-table-column  label="需求量" prop="product_num" align="center" header-align="center" width="50"></el-table-column>
                                                <el-table-column  label="已出数量" prop="used_num"  align="center" header-align="center" width="50"></el-table-column>
                                                <el-table-column label="出库数量" width="100px" align="center" header-align="center" type="text">
                                                        <template slot-scope="scope">
                                                            <el-input type="text" v-model="scope.row.num" @keyup.native="calculationAmount(scope.$index,scope.row)" placeholder="数量" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input>
                                                        </template>
                                                </el-table-column>
                                                <el-table-column label="出货仓库" width="150px" align="center" header-align="center" type="text">
                                                    <template slot-scope="scope">
                                                        <el-select  v-model="scope.row.rep_pid" value-key="id" @visible-change="" @change ="select_rep_pid(scope.$index,scope.row)" filterable placeholder="请选择出货仓库">
                                                                <el-option
                                                                    v-for="item in options_rep_pid"
                                                                    :key="item.rep_id"
                                                                    :label="item.repertory_name"
                                                                    :value="item.rep_id"
                                                                    >
                                                                </el-option>
                                                            </el-select>
                                                    </template>    
                                                </el-table-column>
                                                <el-table-column label="操作">
                                                        <template slot-scope="scope">
                                                            <el-button size="mini" type="danger" @click="handleDelete(scope.$index, scope.row)">删除</el-button>
                                                        </template>
                                                </el-table-column>
                                                </el-table>
                                            </div>
                                            <!-- END -->
                                            <div class="add_button_new">
                                                <el-popover ref="add_product" placement="right" width="700" trigger="click" v-model="GeTATry">
                                                        <div class="form-inline">
                                                            <input type="text" class="form-control" placeholder="请输入产品名" v-model="searchProduct.name" @input="searchingProduct">
                                                        </div>
                                                        <table class="table table-striped table-hover table-bordered">
                                                            <tr>
                                                                <th v-if="false">id</th>
                                                                <th>系统内部编号</th>
                                                                <th>型号</th>
                                                                <th>系统外部编号</th>
                                                                <th>待出库数量</th>
                                                                <th>锁库数量</th>
                                                                <th>库存数量</th>
                                                                <th>需求量</th>
                                                                <th>已出数量</th>
                                                            </tr>
                                                            <tr v-for="item in searchProductRes" @click="addProduct(item)">
                                                                <td v-if="false">{{item.product_id}}</td>
                                                                <td>{{item.product_no}}</td>
                                                                <td>{{item.product_name}}</td>
                                                                <td>{{item.product_number}}</td>
                                                                <td>{{item.out_processing}}</td>
                                                                <td>{{item.o_audit}}</td>
                                                                <td>{{item.stock_number}}</td>
                                                                <td>{{item.product_num}}</td>
                                                                <td>{{item.used_num}}</td>
                                                            </tr>
                                                        </table>
                                                    </el-popover>
                                                    <el-button v-popover:add_product type="primary">新增产品</el-button>
                                            </div>
                                    <br>
                                        
                            <br>
                            <el-row> 
                                <el-col :span="7">
                                    <el-form-item label="业务员：" required>
                                            <el-input  v-model="form.pic_name" style="width: 100%;" disabled></el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="7" :offset="1">
                                        <el-form-item label="制单人：" required>
                                            <el-input v-if="false" v-model="form.create_id" style="width: 100%;"></el-input>
                                            <el-input v-model="form.create_name" disabled style="width: 100%;"></el-input>
                                        </el-form-item>
                                </el-col>
                                <el-col :span="7" :offset="1">
                                    <el-form-item label="发货人：" required>
                                        <el-input v-if="false" v-model="form.send_id" style="width: 100%;"></el-input>
                                        <el-input v-if="false" v-model="form.send_name" style="width: 100%;" readonly></el-input>
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
                            </el-row>
                            <el-row>
                                <el-col :span="7" >
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
                                <el-col :span="7" :offset="1">
                                    <el-form-item label="保管人：" required>
                                        <el-input v-if="false" v-model="form.keep_id" style="width: 100%;"></el-input>
                                        <el-input v-if="false" v-model="form.keep_name" style="width: 100%;" readonly></el-input>
                                        <el-select v-model="keep_name001" value-key="id" filterable placeholder="请选择">
                                            <el-option
                                                v-for="item in options_keep_name"
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
                                        <el-input v-if="false" v-model="form.finance_check_id" style="width: 100%;" readonly></el-input>
                                            <el-input v-model="form.finance_check_name" style="width: 100%;"></el-input>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                            <el-row>
                                <el-col>
                                    <el-form-item label="入库备注：">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/index.js"></script>
<script>
    var formData = <?php echo (json_encode($formData)); ?>;  //出货单源基本信息
    var orderformData = <?php echo (json_encode($orderformData)); ?>;  //订单基本信息
    var materialData = <?php echo (json_encode($materialData)); ?>;  //当前物料剩余数量
    var productData = <?php echo (json_encode($productData)); ?>;  //订单物料信息
    var staffData = <?php echo (json_encode($staffData)); ?>;  //员工列表
    var deptData = <?php echo (json_encode($deptData)); ?>;  //部门列表
    var create_name = <?php echo (json_encode($create_name)); ?>;  //创建人
    var cate_id = <?php echo (json_encode($cate_id)); ?>;  //当前出库单类型ID
    var cate_name = <?php echo (json_encode($cate_name)); ?>;  //出库类型名称
    var repMap = <?php echo (json_encode($repMap)); ?>;  //出库名称
    var stockOutType = <?php echo (json_encode($stockOutType)); ?>;
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                loading:true,
                GeTATry:false,
                form :{
                    id:'',
                    stock_out_id:'',    //出库单编号',
                    source_id:'',   //源单主键',
                    tips:'',    //入库备注',
                    // xin
                    send_name:'',   //发货人
                    send_id:'',   //发货人id
                    audit_id:'',    //审核人ID',
                    audit_name:'',  //审核人姓名',
                    keep_id:'',     //保管人id',
                    keep_name:'',   //保管人姓名',
                    create_id:'',   //制单人id',
                    create_name:'',     //制单人姓名',
                    
                    choose_no:'',   //选单号',
                    source_kind:'',     //源单类型',
                    express_no:'',  //快递单号',

                    // jiu
                    // cus_name:'',   //购买单位
                    receiver_addr:'',    //收货地址
                    invoice_addr:"",    //发货地址
                    finance_check_id:'', //记账人id
                    finance_check_name:'',  //记账人
                },
                audit_name001:'',
                keep_name001:'',
                send_name001:'',
                options_audit_name:[],
                options_keep_name:[],
                options_send_name:[],
                product:[],
                product_old:[],
                options_rep_pid:[],
                // 选中三个值 ，新增数据
                searchProduct: {
                    name: ''
                },
                searchProductRes: [],
                save_productNum:[],
                rules:{
                    express_no:[{required:true,message:'快递单号不能为空',trigger:'blur'}],
                    choose_no:[{required:true,message:'选单号不能为空',trigger:'blur'}],
                    source_kind:[{required:true,message:'选单号不能为空',trigger:'blur'}]
                }
                
            }
        },
        created : function () {
            this.loading = false
            this.product_comparison = [] 
            this.save_productNum.length = 0 
            this.form.pic_name = formData.pic_name,    //业务员姓名',
            this.form.invoice_addr = formData.invoice_addr,    //发货地址
            // this.form.cus_name = formData.cus_name,    //源单主键' 
            this.form.receiver_addr = formData.receiver_addr    //收货地址
            this.form.tips = orderformData.tips,   
            this.form.source_id = orderformData.source_id,    //源单主键' 
            //  xin
            this.form.id = orderformData.id   //主键ID
            this.form.stock_out_id = orderformData.stock_out_id     //单编号
            this.form.send_name = orderformData.send_name   //发货人
            this.form.send_id = orderformData.send_id   //发货人id
            this.form.audit_id = orderformData.audit_id    //审核人ID',
            this.form.audit_name = orderformData.audit_name  //审核人姓名',
            this.form.keep_id = orderformData.keep_id     //保管人id',
            this.form.keep_name = orderformData.keep_name   //保管人姓名',
            this.form.create_id = orderformData.create_id   //制单人id',
            this.form.create_name = orderformData.create_name     //制单人姓名',
            this.form.choose_no = orderformData.choose_no   //选单号',
            this.form.express_no = orderformData.express_no  //快递单号',   

            this.form.source_kind =  stockOutType[orderformData.source_kind]     //源单类型',

            //记账人
            this.form.finance_check_id = formData.finance_check_id //记账人
            for(var i = 0;i< staffData.length;i++){
                if(this.form.finance_check_id == staffData[i].id){
                    this.form.finance_check_name = staffData[i].name
                }
            }
            //人员信息
            this.options_audit_name = staffData
            this.options_keep_name = staffData
            this.options_send_name = staffData
            this.audit_name001 = {
                'name':orderformData.audit_name,
                'id':orderformData.audit_id
            }
            this.keep_name001 = {
                'name':orderformData.keep_name,
                'id':orderformData.keep_id
            }
            this.send_name001 = {
                'name':orderformData.send_name,
                'id':orderformData.send_id
            }
            // 出库下拉 
            this.options_rep_pid = repMap
            // 物料赋值    一
            for(let i in productData){
                var obJ = {
                    source_id:'',
                    product_id:'',
                    product_number:'',
                    product_name:'',
                    product_no:'',
                    out_processing:'',
                    o_audit :'',
                    stock_number:'',
                    product_num:'',
                    used_num:''
                }

                this.product_old.push(obJ)
                this.product_old[i].source_id = productData[i].id
                this.product_old[i].product_id = productData[i].product_id
                this.product_old[i].product_number = productData[i].product_number
                this.product_old[i].product_name = productData[i].product_name
                this.product_old[i].product_no = productData[i].product_no
                this.product_old[i].o_audit = productData[i].o_audit
                this.product_old[i].stock_number = productData[i].stock_number
                this.product_old[i].out_processing = productData[i].out_processing
                this.product_old[i].product_num = productData[i].product_num
                this.product_old[i].used_num = productData[i].used_num
            }

            // 物料赋值   二
            for(let i in materialData){
                var obJ = {
                    source_id:'',
                    product_id:'',
                    product_number:'',
                    product_name:'',
                    product_no:'',
                    out_processing:'',
                    o_audit :'',
                    stock_number:'',
                    num:'',
                    rep_pid:'',
                    product_num:'',
                    used_num:''
                }
                this.product.push(obJ)
                this.product[i].id = materialData[i].id
                this.product[i].source_id = materialData[i].source_id
                this.product[i].product_id = materialData[i].product_id
                this.product[i].product_number = materialData[i].product_number
                this.product[i].product_name = materialData[i].product_name
                this.product[i].product_no = materialData[i].product_no
                this.product[i].o_audit = materialData[i].o_audit
                this.product[i].stock_number = materialData[i].stock_number
                this.product[i].out_processing = materialData[i].out_processing
                this.product[i].num = materialData[i].num
                this.product[i].product_type = materialData[i].product_type
                this.product[i].product_num = materialData[i].product_num
                this.product[i].used_num = materialData[i].used_num
                this.save_productNum.push(materialData[i].num)
                this.product[i].rep_pid = materialData[i].rep_pid
            }
            this.product_comparison.push(materialData.length)
        },
        mounted() {
        },
        methods :{
            handleSelectionChange(val){
                console.log(val)
            },
            // 序号
            indexMethod(index){
                return index + 1 
            },
            // 输入判断
            calculationAmount (index,row) {
                if(Number(row.num - this.save_productNum[index]) > Number(row.o_audit) + Number(row.stock_number) - Number(row.used_num)){
                    row.num = ""
                    layer.msg("出货数量不能大于库存数量，请再次确认填写！")
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
                        vm.product[index].out_processing = res.data.out_processing
                        vm.product[index].o_audit = res.data.o_audit
                        vm.product[index].stock_number = res.data.stock_number
                        if(Number(res.data.stock_number + res.data.o_audit) < Number(row.num)){
                            row.num = ''
                        }else{
                            row.num = materialData[index].num
                        }
                    }else{
                        vm.product[index].out_processing = '0'
                        vm.product[index].o_audit = '0'
                        vm.product[index].stock_number = '0'
                        row.num = ''
                        layer.msg(res.msg)
                    }
                })
            },
            handleDelete(index, row) {
                if(index + 1 > this.product_comparison[0]){
                    // 新添加的
                    this.product.splice(index,1)
                }else{
                    // 原有的
                    var data = {
                        id:row.source_id
                    }
                    $.post('<?php echo U("/Dwin/Stock/delStockOutOrderformMaterial");?>', data ,function(res){
                        layer.msg(res.msg)
                        if(res.status == 200){
                            location.reload()
                        }
                    })
                }
            },
            

            // 提交
            onSubmit(form){
                    var onSubmit_tf = true
                    var baseMsg = {
                        'id':form.id,
                        'stock_out_id' : form.stock_out_id,           //'出库单编号',
                        'source_id' : form.source_id,           //'源单主键',
                        // 'cate_id' : form.cate_id,           //'出库类型id',
                        // 'cate_name' : form.cate_name,           //'出库类型名称',
                        // 'source_kind' : form.source_kind,          //'源单类型',
                        'express_no' : form.express_no,           //'快递单号',
                        'choose_no' : form.choose_no,           //'选单号',
                        'create_id' : form.create_id,           //'制单人id',
                        'create_name' : form.create_name,           //'制单人姓名'
                        'tips' : form.tips,           //'入库备注',
                        'audit_name' : vm.audit_name001.name,
                        'audit_id' : vm.audit_name001.id,

                        'keep_name' : vm.keep_name001.name,
                        'keep_id' : vm.keep_name001.id,

                        'send_name' : vm.send_name001.name,
                        'send_id' : vm.send_name001.id
                    }
                    for(var i = 0;i < baseMsg.length;i++){
                        if(baseMsg[i] == ''){
                            if(baseMsg.tips != ''){
                                layer.msg("请填写完整数据！")
                                onSubmit_tf = false
                            }
                        }
                    }
                    var edit_material  = []
                    var new_material  = []
                    for(let i = 0;i < vm.product.length;i++){
                        if(vm.product[i].num != "" && vm.product[i].num != '0'){
                            if(i + 1 > vm.product_comparison[0]){
                                new_material.push({
                                    'source_id':vm.product[i].source_id,
                                    'product_id':vm.product[i].product_id,
                                    'product_no':vm.product[i].product_no,
                                    'num':vm.product[i].num,
                                    'rep_pid':vm.product[i].rep_pid
                                })
                            }else{
                                edit_material.push({
                                    'id':vm.product[i].id,
                                    'source_id':vm.product[i].source_id,
                                    'product_id':vm.product[i].product_id,
                                    'product_no':vm.product[i].product_no,
                                    'num':vm.product[i].num,
                                    'rep_pid':vm.product[i].rep_pid
                                })
                            }
                        }
                    }
                    if(vm.product.length > new_material.length + edit_material){
                        layer.confirm('确认提交吗？',function(aaa){
                            if(onSubmit_tf){
                                var data = {
                                    'orderform':baseMsg,
                                    'new_material' : new_material,
                                    'edit_material':edit_material
                                }
                                $.post('<?php echo U("/Dwin/Stock/editStockOutOrderform");?>', data , function (res) {
                                    if(res.status == 200){
                                        // 关闭弹框 刷新父页面
                                        layer.msg(res.msg)
                                        location.reload();
                                    }
                                    layer.msg(res.msg)
                                })
                            }
                        })
                    }else{
                        if(onSubmit_tf){
                            var data = {
                                'orderform':baseMsg,
                                'new_material' : new_material,
                                'edit_material':edit_material
                            }
                            $.post('<?php echo U("/Dwin/Stock/editStockOutOrderform");?>', data , function (res) {
                                if(res.status == 200){
                                    // layer.open页面关闭
                                    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                    parent.layer.close(index)
                                }
                                layer.msg(res.msg)
                            })
                        }
                    }
                    
                
            },
            // 获取三个产品值
            searchingProduct: function() {
                    vm.searchProductRes = productData
            },
            // 下拉选中时
            addProduct: function(item) {
                var judgement = true
                var num_box = vm.product.length
                function Item(product) {
                    this.product_id = product.product_id
                    this.product_no = product.product_no
                    this.product_name = product.product_name
                    this.product_number = product.product_number   
                    this.out_processing = product.out_processing
                    this.o_audit = product.o_audit
                    this.stock_number = product.stock_number
                    this.product_num = product.product_num
                    this.used_num = product.used_num
                    
                }
                var obj = new Item(item)
                for(var i = 0 ; i < materialData.length;i++){
                    if(obj.product_id == materialData[i].product_id && judgement){
                        layer.msg('选中物料信息已存在订单中，不可重复添加,请重新选择！')
                        judgement = false
                    }
                }
                if(judgement){
                    this.product.push(obj)
                    this.GeTATry = false
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