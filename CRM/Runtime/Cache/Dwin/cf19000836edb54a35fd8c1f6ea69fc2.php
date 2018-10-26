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
        .productClass .el-scrollbar{
            width: 475px;
        }
        /* #opper{
            width: 600px !important;
        } */
        /* .el-popper{
            width: 50% !important;
        } */
        .el-select-dropdown__item.hover{
            background-color: skyblue
        }
    </style>
</head>
<body>
    <div id="app" style="text-align: center">
        <h1>湖南迪文科技有限公司添加领料单</h1>
        <br><br><br>
        <el-row>
            <el-col :span="22" :offset="1">
                <template>
                    <el-button type="button" @click="openDialog()">分仓出库</el-button>
                    <el-button type="button" @click="stockOut()">直接出库</el-button>
                </template>
                <el-col :span="22" v-if="step != 1">
                    <p class="title_name_product"><b>源单</b></p>
                    <el-table ref="multipleTable" :data="orderBaseData" tooltip-effect="dark"  size="mini" border>
                        <el-table-column label="单据编号" prop="production_code"  align="center" header-align="center"></el-table-column>
                        <el-table-column label="生产型号" prop="product_name"  align="center" header-align="center"></el-table-column>
                        <el-table-column label="物料编号" prop="product_no"  align="center" header-align="center"></el-table-column>
                        <el-table-column label="计划数量" prop="plan_number"  align="center" header-align="center"></el-table-column>
                    </el-table>
                </el-col>


                <el-col :span="22" class="step-1" v-if="step == 1">
                    <p class="title_name_product"><b>领料出库单</b></p>
                    <el-form ref="form" :model="form" label-width="120px" size="medium" @submit.native.prevent v-loading="loading" :rules="rules">
                        <el-row>
                            <el-col :span="11">
                                <el-form-item label="领料单编号：" style="text-align: left" required>
                                    <el-input v-model="form.stock_out_id" style="width: 50%;" readonly></el-input>
                                    <el-input v-if="false" v-model="id" style="width: 65%;" readonly></el-input>
                                    <el-button type="primary" @click="getNumber()">获取编号</el-button>
                                </el-form-item>
                            </el-col>
                            <!--<el-col :span="7"  v-show="false">-->
                                <!--<el-form-item label="申请单编号：">-->
                                    <!--&lt;!&ndash; <el-input v-model="form.id" style="width: 100%;" readonly></el-input> &ndash;&gt;-->
                                    <!--<el-input v-model="form.source_id" style="width: 100%;" readonly></el-input>-->
                                <!--</el-form-item>-->
                            <!--</el-col>-->
                            <el-col :span="8" :offset="1">
                                <el-form-item label="计划单号：" repuire>
                                    <el-input v-model="source_id_string" style="width: 100%;"></el-input>
                                </el-form-item>
                            </el-col>

                        </el-row>
                        <el-row>
                            <el-col :span="7">
                                <el-form-item label="对方科目：" prop="other_subject">
                                    <el-input v-model="form.other_subject" style="width: 100%;"></el-input>
                                </el-form-item>
                            </el-col>
                            <el-col :span="7" :offset="1">
                                <el-form-item label="出库类别名称：">
                                    <el-input v-model="form.purchase_cate_name" style="width: 100%;" readonly="true"></el-input>
                                </el-form-item>
                            </el-col>

                            <el-col :span="7" :offset="1">
                                <el-form-item label="选单号：" prop="choise_no">
                                    <el-input v-model="form.choise_no" style="width: 100%;"></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        <el-row>
                            <!--<el-col :span="7">-->
                                <!--<el-form-item label="领料类型：" required>-->
                                    <!--&lt;!&ndash; <el-input v-if="false" v-model="form.picking_id" style="width: 100%;" ></el-input> &ndash;&gt;-->
                                    <!--&lt;!&ndash; <el-input v-if="false" v-model="form.picking_dept_name" style="width: 100%;" ></el-input> &ndash;&gt;-->
                                    <!--<el-select v-model="form.picking_kind" value-key="id" filterable placeholder="请选择">-->
                                        <!--<el-option-->
                                                <!--v-for="item in options_picking_kind"-->
                                                <!--:key="item.id"-->
                                                <!--:label="item.name"-->
                                                <!--:value="item.id"-->
                                        <!--&gt;-->
                                        <!--</el-option>-->
                                    <!--</el-select>-->
                                    <!--&lt;!&ndash; <el-input v-model="form.picking_kind" style="width: 100%;" ></el-input> &ndash;&gt;-->
                                <!--</el-form-item>-->
                            <!--</el-col>-->
                            <el-col :span="7">
                                <el-form-item label="领料部门：" required>
                                    <el-input v-if="false" v-model="form.picking_dept_id" style="width: 100%;" ></el-input>
                                    <el-input v-if="false" v-model="form.picking_dept_name" style="width: 100%;" ></el-input>
                                    <el-select v-model="picking_dept_name001" value-key="id" filterable placeholder="请选择">
                                        <el-option
                                                v-for="item in options_picking_dept_name"
                                                :key="item.id"
                                                :label="item.name"
                                                :value="item"
                                        >
                                        </el-option>
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="7" :offset="1">
                                <el-form-item label="打印次数：">
                                    <el-input v-model="form.printing_times" style="width: 100%;" disabled></el-input>
                                </el-form-item>

                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="7" >
                                <el-form-item label="创建时间：">
                                    <el-date-picker
                                            style="width: 100%;"
                                            v-model="form.create_time"
                                            type="date"
                                            readonly
                                            value-format="timestamp"
                                            format="yyyy-MM-dd"
                                            placeholder="创建时间：">
                                    </el-date-picker>
                                </el-form-item>
                            </el-col>
                            <el-col :span="15" :offset="1">
                                <el-form-item label="工程项目：">
                                    <el-input v-model="form.engine_item" style="width: 100%;" ></el-input>
                                </el-form-item>

                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="23">
                                <el-form-item label="用途：">
                                    <el-input v-model="form.purpose" style="width: 100%;" ></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </el-form>
                </el-col>
                <el-col :span="22">
                    <p class="title_name_product">物料信息</p>
                    <el-table ref="multipleTable" :data="stockOutPreData" tooltip-effect="dark" size="mini"  @selection-change="handleSelectionChange" border>
                        <el-table-column label="物料编号" prop="product_no"></el-table-column>
                        <el-table-column label="物料型号" prop="product_name"></el-table-column>
                        <el-table-column label="物料名称" prop="product_number"></el-table-column>
                        <el-table-column label="出库库房" prop="warehouse_id"></el-table-column>
                        <el-table-column label="即时库存" prop="stock_number"></el-table-column>
                        <el-table-column label="出库数量" prop="num"></el-table-column>
                        <el-table-column label="生产源单" prop="production_code"></el-table-column>
                        <el-table-column label="生产物料" prop="produce_name"></el-table-column>
                    </el-table>
                </el-col>

                <el-col :span="22" class="step-1" v-if="step == 1">
                    <el-form ref="form" :model="form" label-width="120px" size="medium" @submit.native.prevent v-loading="loading" :rules="rules">
                    <br>
                    <el-row>
                        <el-col :span="7">
                            <el-form-item label="制单人：" required>
                                <el-input v-if="false" v-model="form.create_id" style="width: 100%;"></el-input>
                                <el-input v-if="false" v-model="form.create_name" style="width: 100%;" readonly></el-input>
                                <el-select v-model="create_name001" value-key="id" filterable placeholder="请选择">
                                    <el-option
                                            v-for="item in options_create_name"
                                            :key="item.id"
                                            disabled
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
                        <el-col :span="7" :offset="1">
                            <el-form-item label="领料人：" required>
                                <el-input v-if="false" v-model="form.collect_id" style="width: 100%;"></el-input>
                                <el-input v-if="false" v-model="form.collect_name" style="width: 100%;" readonly></el-input>
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
                    </el-row>
                    <el-row>
                        <el-col :span="7">
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
                        <el-col :span="23">
                            <el-form-item label="备注：">
                                <el-input type="textarea" v-model="form.tips"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row><br>
                    <el-button type="success" @click="onSubmit()">提 交</el-button>
                    <br><br>
                    </el-form>
                </el-col>

                <!-- Form -->
                <el-dialog title="分仓出库仓库选择" :visible.sync="shadeDialogVisible">
                    <el-form :model="form">
                        <div style="margin-top: 20px">
                            <el-radio-group v-model="warehouseSel" size="small" >
                                <div v-for="item in warehouseArr">
                                    <el-radio size="small" border style="margin: 10px;" :label="item">{{item}}</el-radio>
                                </div>
                            </el-radio-group>
                        </div>
                    </el-form>
                    <div slot="footer" class="dialog-footer">
                        <el-button @click="shadeDialogVisible = false">取 消</el-button>
                        <el-button type="primary" @click="confirmRep()">确 定</el-button>
                    </div>
                </el-dialog>
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
var repMap = <?php echo (json_encode($repMap)); ?>;  //仓库名称
    var staffData = <?php echo (json_encode($staffData)); ?>;  //员工列表
    var deptData  = <?php echo (json_encode($deptData)); ?>;  //部门列表
    var productionOrderData = <?php echo (json_encode($productionOrderData)); ?>;  //领料订单源单信息
    var materialData = <?php echo (json_encode($noPushMaterialData)); ?>;  //申请单物料数据
    var pickingType = <?php echo (json_encode($pickingType)); ?>;  //领料类型
    var  cate_id= <?php echo (json_encode($cate_id)); ?>;  //领料类型
    var  cate_name= <?php echo (json_encode($cate_name)); ?>;  //领料类型
    var  create_name= <?php echo (json_encode($create_name)); ?>;  //领料类型
    var  noPushRepMap= <?php echo (json_encode($noPushRepMap)); ?>;  //领料库房

    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                loading:true,
                connt_savePRO:{},
                GeTATry:false,
                shadeDialogVisible:false,
                step:100,
                form :{
                    production_code:'',
                    // xin
                    id:'',
                    stock_out_id:'',
                    tips:'',
                    source_id:[],
                    source_id_string:"",
                    purchase_cate_name:'',
                    other_subject:'',
                    choise_no:'',
                    purpose:'',
                    engine_item:'',
                    picking_dept_id:'',
                    picking_dept_name:'',
                    picking_kind:'',
                    create_name:'',
                    create_id:'',
                    send_name:'',
                    send_id:'',
                    collect_name:'',
                    collect_id:'',
                    audit_name:'',
                    audit_id:'',
                    printing_times:'0'
                },
                
                options_create_name:[],
                options_send_name:[],
                options_collect_name:[],
                options_audit_name:[],
                options_account_name:[],
                options_rep_pid:[],
                options_picking_dept_name:[],
                options_picking_kind:[],
                options_replace_product:[],
                
                send_name001:'',
                collect_name001:'',
                audit_name001:'',
                picking_dept_name001:'',
                create_name001:'',

                product:materialData,// 物料出库信息(按库房拆分）
                orderBaseData:[],//源单数据
                stockOutPreData:[],
                warehouseArr : noPushRepMap,
                warehouseSel:"",
                tmp : [],
                // 验证
                rules:{
                    other_subject:[{ required: true, message: '对方科目不能为空', trigger: 'blur'}],
                    choise_no:[{ required: true, message: '选单号不能为空', trigger: 'blur'}]
                },
                formValidate:{
                    id:'编号',
                    stock_out_id:'单据编号',
                    send_id:'发货人',
                    send_name:'发货人姓名',
                    collect_name:'领料人',
                    collect_id:'领料人姓名',
                    audit_name:'审核人姓名',
                    audit_id:'审核人',
                    picking_dept_id:'领料部门'
                }
            }
        },
        created : function () {
            this.orderBaseData = productionOrderData;
            this.loading = false
            this.form.purchase_cate_name = cate_name
            this.options_picking_dept_name = deptData

            this.form.create_name = create_name
            for(var i = 0 ;i < staffData.length;i++){
                if(create_name == staffData[i].name){
                    this.form.create_id = staffData[i].id
                }
            }
            this.create_name001 = {
                'name':this.form.create_name,
                'id':this.form.create_id
            }

            for(let i in pickingType){
                this.options_picking_kind.push({'name':pickingType[i],'id':i})
            }
            // 物料赋值
            this.options_rep_pid = repMap
            for(let i in this.product){

                for (var j = 0; j< this.product[i].length;j++) {
                    var obj = {
                        id             : this.product[i][j].id,
                        product_id     : this.product[i][j].product_id,
                        product_no     : this.product[i][j].product_no,
                        product_name   : this.product[i][j].product_name,
                        product_number : this.product[i][j].product_number,
                        num            : this.product[i][j].num - this.product[i][j].push_num,
                        order_pid      : this.product[i][j].order_pid,
                        warehouse_id   : this.product[i][j].warehouse_id,
                        production_code   : this.product[i][j].production_code,
                        produce_name   : this.product[i][j].produce_name,
                        stock_number   : this.product[i][j].stock_number,
                    }
                    this.stockOutPreData.push(obj);
                }
            }

            this.options_create_name = staffData,
            this.options_send_name = staffData,
            this.options_collect_name = staffData,
            this.options_audit_name = staffData,
            this.options_account_name = staffData,
            this.options_charge_name = staffData,
            this.options_business_name = staffData

        },
        mounted() {
        },
        methods :{
            openDialog:function() {
                this.shadeDialogVisible = true
            },
            stockOut:function () {
                this.shadeDialogVisible = false;
                this.step = 1;
                this.getSourceIdArray();
            },
            confirmRep:function() {
                this.shadeDialogVisible = false;
                this.stockOutPreData = materialData[this.warehouseSel];
                this.step = 1;
                this.getSourceIdArray();
            },
            // 获取编号
             getNumber:function(){
                this.loading = true;
                var data = {
                    'source_kind':cate_id
                }
                $.post('<?php echo U("/Dwin/Stock/createStockOutId");?>',data, function (res) {
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
            handleSelectionChange:function(val){
                console.log(val)
            },
            inArray:function(searchVal,arr) {
                for (i = 0; i < arr.length; i++) {
                    if ( searchVal === arr[i] ) return true;
                }
                return false;
            },
            getSourceIdArray:function () {
                this.tmp = [];
                var tmpString = [];
                if (!this.stockOutPreData) {
                    layer.msg("选择的库房有问题，没有可出库物料");
                    this.source_id_string = "";
                    this.tmp = [];
                    return false;
                }
                for (var i = 0; i < this.stockOutPreData.length;i++) {
                    if (!this.inArray(this.stockOutPreData[i].order_pid,this.tmp)) {
                        this.tmp.push(this.stockOutPreData[i].order_pid);
                        tmpString.push(this.stockOutPreData[i].production_code);
                    }
                }
                this.source_id_string = tmpString.join(",");
            },
            // 序号
            indexMethod:function(index){
                return index + 1 
            },
            getBaseFormData:function () {
                this.form.source_id = vm.tmp
                this.form.send_name = vm.send_name001.name
                this.form.send_id   = vm.send_name001.id

                this.form.collect_name = vm.collect_name001.name
                this.form.collect_id   = vm.collect_name001.id

                this.form.audit_name = vm.audit_name001.name
                this.form.audit_id   = vm.audit_name001.id

                this.form.picking_dept_name = vm.picking_dept_name001.name
                this.form.picking_dept_id   = vm.picking_dept_name001.id
            },
            getMaterialData :function () {
                var newMaterial = [];
                if (!this.stockOutPreData) return false;
                for (var i = 0; i < this.stockOutPreData.length;i++) {
                    var obj = {
                        id : this.stockOutPreData[i].id,
                        product_id : this.stockOutPreData[i].product_id,
                        product_no : this.stockOutPreData[i].product_no,
                        product_name : this.stockOutPreData[i].product_name,
                        product_number : this.stockOutPreData[i].product_number,
                        num         : parseInt(this.stockOutPreData[i].num),
                        rep_pid     : this.stockOutPreData[i].warehouse_id,
                        order_pid   : this.stockOutPreData[i].order_pid,
                        stock_num   : parseInt(this.stockOutPreData[i].stock_number)
                    };

                    newMaterial.push(obj);
                }
                return newMaterial;
            },
            validateData: function(form) {
                if (!Object.prototype.toString.call(form) === "[object Object]") {
                    layer.msg("表单数据校验失败");
                    return false;
                }
                var array = [];
                for (let j in this.formValidate) {
                    array.push(j);
                }

                for (let i in form) {
                    if (this.inArray(i, array)) {
                        if (!form[i]) {
                            layer.msg("表单数据校验失败：" + this.formValidate[i] + " = " + form[i]);
                            return false;
                        }
                    }
                }
                return true;
            },
            validateMaterial: function (data){
                if (Object.prototype.toString.call(data) === '[object Array]') {
                    if (data.length === 0) {
                        layer.msg("出库物料种类为0，禁止提交");
                        return false;
                    }
                    for (var i = 0; i < data.length; i++) {
                        if (!data[i].stock_num) {
                            layer.msg(data[i].product_no + "无库存");
                            return false;
                        }
                        if (parseInt(data[i].stock_num) < parseInt(data[i].num)) {

                            layer.msg(data[i].product_no + "的库存不足，无法制单，请齐料后再进行制单");
                            return false;
                        }
                        if (!data[i].rep_pid || !data[i].num || !data[i].id || !data[i].product_id) {
                            layer.msg("第" + [i] + "行数据校验未通过，请核实");
                            return false;
                        }
                    }
                    return true;
                } else {
                    layer.msg(Object.prototype.toString.call(data));
                    return false;
                }
            },
            // 提交
            onSubmit : function () {
                this.getBaseFormData();
                var material = this.getMaterialData();


                var flagA = this.validateData(this.form);
                if (!flagA) return false;
                var flagB = this.validateMaterial(material);
                if (flagA * flagB) {
                    var data = {
                        'production':this.form,
                        'material' : material
                    }
                    layer.confirm('确定要提交吗？', function() {
                        $.post('<?php echo U("/Dwin/Stock/createStockOutProduction");?>', data , function (res) {
                            if(res.status == 200){
                                // 关闭弹框 刷新父页面
                                layer.msg(res.msg)
                                // layer.open页面关闭
                                var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                parent.layer.close(index)
                            }else{
                                layer.msg(res.msg);
                                return false;
                            }
                            layer.msg(res.msg)
                        })
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