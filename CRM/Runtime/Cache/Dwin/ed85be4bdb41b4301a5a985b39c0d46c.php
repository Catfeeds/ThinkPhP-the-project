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
        .el-select-dropdown__item.hover{
            background-color: skyblue
        }
    </style>
</head>
<body>
    <div id="app" style="text-align: center">
        <h1>湖南迪文科技有限公司领料单修改</h1>
        <br><br><br>
        <el-row>
            <el-col :span="22" :offset="1">
                    <el-form ref="form" :model="form" label-width="120px" size="medium" @submit.native.prevent v-loading="loading" :rules="rules">
                            <el-row>
                                <el-col :span="7">
                                    <el-form-item label="销售单号：">
                                        <el-input v-model="form.stock_out_id" style="width: 100%;" disabled></el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="7"  v-show="false">
                                    <el-form-item label="申请单编号：">
                                        <el-input v-model="form.source_id" style="width: 100%;" readonly></el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="7" :offset="1">
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
                                    <!-- <el-form-item label="计划单号：">
                                            <el-input v-model="form.production_code" style="width: 100%;" disabled></el-input>
                                    </el-form-item>  -->
                                </el-col>
                                <el-col :span="7" :offset="1" required>
                                    <el-form-item label="出库类别名称：">
                                        <el-input v-model="form.purchase_cate_name" style="width: 100%;" disabled></el-input>
                                    </el-form-item> 
                                </el-col>
                            </el-row>
                            <el-row>
                                <el-col :span="7">
                                    <el-form-item label="对方科目：" prop="other_subject">
                                        <el-input v-model="form.other_subject" style="width: 100%;" ></el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="7" :offset="1">
                                        <el-form-item label="创建时间：">
                                                <el-date-picker
                                                    style="width: 100%;"
                                                    v-model="form.create_time"
                                                    type="date"
                                                    disabled
                                                    value-format="timestamp"
                                                    format="yyyy-MM-dd"
                                                    placeholder="创建时间：">
                                                </el-date-picker>
                                            </el-form-item>
                                </el-col>
                                <el-col :span="7" :offset="1">
                                    <el-form-item label="选单号：" prop="choise_no">
                                        <el-input v-model="form.choise_no" style="width: 100%;" ></el-input>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                            <el-row>
                                <el-col :span="7">
                                        <el-form-item label="领料类型：" required>
                                                <!-- <el-input v-if="false" v-model="form.picking_id" style="width: 100%;" ></el-input> -->
                                                <!-- <el-input v-if="false" v-model="form.picking_name" style="width: 100%;" ></el-input> -->
                                                <el-select v-model="form.picking_kind" value-key="id" filterable placeholder="请选择">
                                                    <el-option
                                                        v-for="item in options_picking_kind"
                                                        :key="item.id"
                                                        :label="item.name"
                                                        :value="item.id"
                                                        >
                                                    </el-option>
                                                </el-select>
                                                <!-- <el-input v-model="form.picking_kind" style="width: 100%;" ></el-input> -->
                                            </el-form-item> 
                                </el-col>
                                <el-col :span="7" :offset="9">
                                    <!-- <b>打印次数：</b>{{form.printing_times}} -->
                                        <el-form-item label="打印次数：">
                                                <el-input v-model="form.printing_times" style="width: 100%;border:1px solid #fff" disabled></el-input>
                                            </el-form-item> 
                                    
                                </el-col>
                            </el-row>
                            <!-- <el-row>
                                <el-col :span="12">
                                        <el-form-item label="用途：">
                                                <el-input v-model="form.purpose" style="width: 100%;" ></el-input>
                                            </el-form-item>
                                    
                                </el-col>
                                <el-col :span="12">
                                        <el-form-item label="工程项目：">
                                                <el-input v-model="form.engine_item" style="width: 100%;" ></el-input>
                                            </el-form-item> 
                                </el-col>
                            </el-row> -->
                            
                            <el-row :gutter="20">
                                <el-col :span="24">

                                        <!-- 表格 -->
                                        <div>
                                            <br>
                                            <p class="title_name_product"><b>一、已出货信息 </b></p>
                                            <el-table ref="multipleTable" :data="productionOrderData1" tooltip-effect="dark"  @selection-change="handleSelectionChange" border>
                                                <el-table-column label="生产型号" prop="product_name"  align="center" header-align="center"></el-table-column>
                                                <el-table-column label="总计总套数" prop="plan_number"  align="center" header-align="center"></el-table-column>
                                                <el-table-column label="已出套数" prop="used_num"  align="center" header-align="center"></el-table-column>
                                                <el-table-column label="未出套出"  align="center" header-align="center">
                                                    <template slot-scope="scope">
                                                        <div>{{scope.row.plan_number - scope.row.used_num}}</div>
                                                    </template>
                                                </el-table-column>
                                            </el-table>
                                        </div>

                                        <div>
                                                <!-- <p class="title_name_product"><b>二、订单物料信息 </b></p>
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
                                                <el-table-column  label="订单数量" prop="total_num" align="center" header-align="center" width="100"></el-table-column>
                                                <el-table-column  label="已出数量" prop="used_num"  align="center" header-align="center" width="100"></el-table-column>
                                            </el-table>
                                            <br> -->
                                            <p class="title_name_product"><b>三、出货单物料信息 </b></p>
                                            <p style="text-align: left" class="pp">
                                                <span><b>本次出库数量（套）</b></span><el-input-number id="con_red" v-model="harness" controls-position="right" @change="handleChange" :min="0"></el-input-number>
                                            </p>
                                            <el-table ref="multipleTable" :data="product" tooltip-effect="dark" style="width: 100%" @selection-change="handleSelectionChange" border>
                                            <el-table-column v-if="false" label="id" prop="id" type="index" align="center" header-align="center" width="50"></el-table-column>
                                            <el-table-column v-if="false" label="source_id" prop="id" type="index" align="center" header-align="center" width="50"></el-table-column>
                                            <el-table-column label="序号" width="50px" type="index" :index="indexMethod"> </el-table-column>
                                            <el-table-column v-if="false" label="product_id" prop="product_id" align="center" header-align="center"></el-table-column>
                                            <el-table-column v-if="false" label="product_no" prop="product_no" align="center" header-align="center"></el-table-column>
                                            <el-table-column v-if="false" label="substituted_id" prop="substituted_id" align="center" header-align="center"></el-table-column>
                                            <el-table-column label="物料编号" align="center" header-align="center" >
                                                <template slot-scope="scope"  class="productClass">
                                                    <el-select id="opper" v-if="scope.row.replace_data[1]  || false" v-model="scope.row.substituted_no" value-key="id" clearable  @clear="delete_selest(scope.$index,scope.row)" @visible-change="change_product($event,scope.$index,scope.row)" placeholder="请选择">
                                                        <el-option disabled
                                                            :value="1"
                                                            >
                                                            <span style="float: left; color: blue;text-align: center" >物料型号</span>
                                                            <span style="margin-left: 17%;color: blue;text-align: center">物料名称</span>
                                                            <span style="margin-left: 27%;margin-right: 12%;color: blue;text-align: center">物料编码</span>
                                                        </el-option>
                                                        <el-option
                                                        v-for="item in options_replace_product"
                                                        :key="item.substituted_id"
                                                        :label="item.product_no"
                                                        :value="item">
                                                        <span v-if="false">{{ item.substituted_id }}</span>
                                                        <span style="float: left;width: 20%;text-align: left">{{ item.product_no }}</span>
                                                        <span style="float: left;width: 45%;text-align: left">{{ item.product_name }}</span>
                                                        <span style="float: left;width: 35%;text-align: left">{{ item.product_number }}</span>
                                                    </el-option>
                                                </el-select>
                                                <div v-else>{{scope.row.replace_data[0].product_no}}</div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column label="物料名称" prop="substituted_number" align="center" header-align="center"> </el-table-column>
                                            <el-table-column label="物料型号" prop="substituted_name" align="center" header-align="center"> </el-table-column>
                                            <!-- <el-table-column label="订单数量" prop="total_num" align="center" header-align="center"> </el-table-column> -->
                                            <!-- <el-table-column label="已出库数量" prop="used_num" align="center" header-align="center"> </el-table-column> -->
                                            <el-table-column label="库存"  align="center" header-align="center">
                                                <template slot-scope="scope">
                                                    <div>{{Number(scope.row.stock_number) + Number(scope.row.o_audit)}}</div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column label="一套所用量" prop="one_num" align="center" header-align="center"> </el-table-column>
                                            <!-- <el-table-column label="库房余量" prop="stock_number" align="center" header-align="center"> </el-table-column> -->
                                            <!-- <el-table-column label="锁定出库量" prop="o_audit" align="center" header-align="center"> </el-table-column> -->
                                            <el-table-column label="出库数量" width="150px" align="center" header-align="center" type="text">
                                                    <template slot-scope="scope">
                                                        <el-input type="text" v-model="scope.row.num" readonly @keyup.native="calculationAmount(scope.$index,scope.row)" placeholder="数量" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input>
                                                    </template>
                                            </el-table-column>
                                            <el-table-column label="出货仓库" width="150px" align="center" header-align="center" type="text">
                                                <template slot-scope="scope">
                                                    <el-select  v-model="scope.row.rep_pid" value-key="id" @visible-change="" @change ="select_rep_pid(scope.$index,scope.row)" filterable placeholder="请选择出货仓库">
                                                            <el-option
                                                                v-for="item in options_rep_pid"
                                                                :key="item.id"
                                                                :label="item.repertory_name"
                                                                :value="item.rep_id"
                                                                >
                                                            </el-option>
                                                        </el-select>
                                                </template>    
                                            </el-table-column>
                                            <!-- <el-table-column label="操作">
                                                    <template slot-scope="scope">
                                                        <el-button size="mini" type="danger" @click="handleDelete(scope.$index, scope.row)">删除</el-button>
                                                    </template>
                                            </el-table-column> -->
                                            </el-table>
                                        </div>
                                        <!-- END -->
                                        <!-- <div class="add_button_new">
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
                                    <br> -->
                                        
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
                                    <!-- <el-col :span="7" :offset="1">
                                        <el-form-item label="负责人：">
                                            <el-input v-if="false" v-model="form.charge_id" style="width: 100%;"></el-input>
                                            <el-input v-if="false" v-model="form.charge_name" style="width: 100%;" readonly></el-input>
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
                                    </el-col> -->
                                </el-row>
                                <el-row>
                                    <!-- <el-col :span="7">
                                            <el-form-item label="业务员：">
                                                <el-input v-if="false" v-model="form.business_id" style="width: 100%;"></el-input>
                                                <el-input v-if="false" v-model="form.business_name" style="width: 100%;" readonly></el-input>
                                                <el-select v-model="business_name001" value-key="id" filterable placeholder="请选择">
                                                    <el-option
                                                        v-for="item in options_business_name"
                                                        :key="item.id"
                                                        :label="item.name"
                                                        :value="item"
                                                        >
                                                    </el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col> -->
                                        <el-col :span="23">
                                                <el-form-item label="备注：">
                                                    <el-input type="textarea" v-model="form.tips"></el-input>
                                                </el-form-item>
                                        </el-col>
                                </el-row><br>
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
<script src="https://cdn.bootcss.com/element-ui/2.3.6/index.js"></script>
<script>
    var repMap = <?php echo (json_encode($repMap)); ?>;  //仓库名称
    var staffData = <?php echo (json_encode($staffData)); ?>;  //员工列表
    var deptData = <?php echo (json_encode($deptData)); ?>;  //部门列表
    var orderMaterialData = <?php echo (json_encode($orderMaterialData)); ?>;  //领料订单源单信息
    var pickingType = <?php echo (json_encode($pickingType)); ?>;  //领料类型
    var produceData = <?php echo (json_encode($produceData)); ?>;  //领料类型
    var materialData = <?php echo (json_encode($materialData)); ?>;  //申请单物料数据
    var cate_id = <?php echo (json_encode($cate_id)); ?>;  //领料类型
    var cate_name = <?php echo (json_encode($cate_name)); ?>;  //领料类型
    var productionOrderData = <?php echo (json_encode($productionOrderData)); ?>;  //领料类型
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                loading:true,
                connt_savePRO:{},
                GeTATry:false,
                harness:0,
                searchProduct: {
                    name: ''
                },
                searchProductRes:[],
                form :{
                    // production_code:'',
                    // xin
                    stock_out_id:'',
                    tips:'',
                    source_id:'',
                    purchase_cate_name:'',
                    other_subject:'',
                    choise_no:'',
                    // purpose:'',
                    // engine_item:'',
                    picking_dept_id:'',
                    picking_dept_name:'',
                    picking_kind:'',
                    create_time:'',
                    create_name:'',
                    create_id:'',
                    send_name:'',
                    send_id:'',
                    collect_name:'',
                    collect_id:'',
                    audit_name:'',
                    audit_id:'',
                    account_name:'',
                    account_id:'',
                    charge_name:'',
                    charge_id:'',
                    business_name:'',
                    business_id:'',
                    printing_times:'0'

                },
                
                options_create_name:[],
                options_send_name:[],
                options_collect_name:[],
                options_audit_name:[],
                options_account_name:[],
                options_charge_name:[],
                options_business_name:[],
                options_rep_pid:[],
                options_picking_dept_name:[],
                options_picking_kind:[],
                options_replace_product:[],
                
                send_name001:'',
                collect_name001:'',
                audit_name001:'',
                account_name001:'',
                charge_name001:'',
                business_name001:'',
                picking_dept_name001:'',
                create_name001:'',

                product:[],

                product_old:[],
                product_comparison:[],
                productionOrderData1:[],
                rules:{
                    other_subject:[{required:true,message:'对方科目不能为空',trigger:'blur'}],
                    choise_no:[{required:true,message:'选单号不能为空',trigger:'blur'}]
                }
            }
        },
        created : function () {
            this.productionOrderData1 = []
            this.product_comparison.length = 0
            this.productionOrderData1.push(productionOrderData)
            this.form.purchase_cate_name = cate_name
            this.form.id = produceData.id
            this.form.source_id = produceData.source_id
            this.form.stock_out_id = produceData.stock_out_id
            this.form.other_subject = produceData.other_subject
            this.form.choise_no = produceData.choise_no
            this.form.picking_dept_id = produceData.picking_dept_id
            this.form.picking_dept_name = produceData.picking_dept_name
            this.form.picking_kind = produceData.picking_kind
            // for(var i in pickingType){
            //     if(this.form.picking_kind == i){
            //         this.form.picking_kind = pickingType[i]
            //     }
            // }
            this.form.create_time = produceData.create_time * 1000
            
            this.form.create_name = produceData.create_name
            this.form.create_id = produceData.create_id
            this.form.send_name = produceData.send_name
            this.form.send_id = produceData.send_id
            this.form.collect_name = produceData.collect_name
            this.form.collect_id = produceData.collect_id
            this.form.audit_name = produceData.audit_name
            this.form.audit_id = produceData.audit_id
            this.form.account_name = produceData.account_name
            this.form.account_id = produceData.account_id
            this.form.charge_name = produceData.charge_name
            this.form.charge_id = produceData.charge_id
            this.form.business_name = produceData.business_name
            this.form.business_id = produceData.business_id
            

            this.picking_dept_name001 = {
                'name':produceData.picking_dept_name,
                'id':produceData.picking_dept_id
            }
            
            this.create_name001 = {
                'name':produceData.create_name,
                'id':produceData.create_id
            }
            this.send_name001 = {
                'name':produceData.send_name,
                'id':produceData.send_id
            }
            this.collect_name001 = {
                'name':produceData.collect_name,
                'id':produceData.collect_id
            }
            this.audit_name001 = {
                'name': produceData.audit_name,
                'id': produceData.audit_id
            }
            this.account_name001 = {
                'name': produceData.account_name,
                'id': produceData.account_id
            }
            this.charge_name001 = {
                'name': produceData.charge_name,
                'id': produceData.charge_id
            }
            this.business_name001 = {
                'name': produceData.business_name,
                'id': produceData.business_id
            }

            this.form.printing_times = produceData.printing_times
            this.form.tips = produceData.tips

            this.options_picking_dept_name = deptData
            for(let i in pickingType){
                this.options_picking_kind.push({'name':pickingType[i],'id':i})
            }
            
            // bom 物料信息赋值
            this.product_old = orderMaterialData 
            // 套数
            this.harness = produceData.num
            // 物料赋值
            this.options_rep_pid = repMap
            this.product_comparison.push(materialData.length)
            for(let i in materialData){
                var obJ = {
                    id:'',
                    source_id:'',
                    product_id:'',
                    product_no:'',
                    substituted_no:'',
                    substituted_id:'',
                    substituted_name:'',
                    substituted_number:'',
                    total_num:'',
                    used_num:'',
                    one_num:'',
                    stock_number:'',
                    o_audit:'',
                    replace_data:'',
                    num:'',
                    rep_pid:'',
                }
                
                this.product.push(obJ)
                this.product[i].id = materialData[i].id
                this.product[i].source_id = materialData[i].source_id
                this.product[i].product_id = materialData[i].product_id
                this.product[i].product_no = materialData[i].product_no
                this.product[i].product_name = materialData[i].product_name
                this.product[i].product_number = materialData[i].product_number
                this.product[i].total_num = materialData[i].total_num
                this.product[i].used_num = materialData[i].used_num 
                this.product[i].num = materialData[i].num 
                this.product[i].one_num = materialData[i].one_num   
                this.product[i].stock_number = materialData[i].stock_number
                this.product[i].o_audit = materialData[i].o_audit
                this.product[i].tips = materialData[i].tips
                this.product[i].replace_data = materialData[i].replace_data
                for(let x = 0;x<repMap.length;x++){
                    if(materialData[i].rep_pid == repMap[x].rep_id){
                        this.product[i].rep_pid = repMap[x].repertory_name
                    }
                } 
                // 循环将 replace_data 第一个数填入 显示
                if(this.product[i].replace_data.length == 1){
                    // only one list
                    this.product[i].substituted_id = this.product[i].replace_data[0].substituted_id
                    this.product[i].substituted_no = this.product[i].replace_data[0].product_no
                    this.product[i].substituted_name = this.product[i].replace_data[0].product_name
                    this.product[i].substituted_number = this.product[i].replace_data[0].product_number
                }else{
                    // more list
                    for(var p = 0;p < this.product[i].replace_data.length;p++){
                        if(this.product[i].replace_data[p].substituted_id == materialData[i].substituted_id){
                            this.product[i].substituted_id = this.product[i].replace_data[p].substituted_id
                            this.product[i].substituted_no = this.product[i].replace_data[p].product_no
                            this.product[i].substituted_name = this.product[i].replace_data[p].product_name
                            this.product[i].substituted_number = this.product[i].replace_data[p].product_number
                        }
                    }
                }
            }

            this.options_create_name = staffData,
            this.options_send_name = staffData,
            this.options_collect_name = staffData,
            this.options_audit_name = staffData,
            this.options_account_name = staffData,
            this.options_charge_name = staffData,
            this.options_business_name = staffData
            this.loading = false
        },
        mounted() {
        },
        methods :{
            // 清空 替代物料
            delete_selest(index,row){
                this.product[index].substituted_id = ''
                this.product[index].substituted_name = ''
                this.product[index].substituted_number = ''
                this.product[index].substituted_no = ''
                this.select_rep_pid(index,row)
            },
            // 选中 selsct 时
            change_product(callback,index,row){
                this.options_replace_product = []
                if(row.replace_data.length == 0){

                }else{
                    // 下拉 显示
                    if(callback){
                        this.connt_savePRO = {
                            id : this.product[index].substituted_id,
                            name : this.product[index].substituted_name,
                            number : this.product[index].substituted_number,
                            no : this.product[index].substituted_no
                        }
                        this.product[index].substituted_id = ''
                        this.product[index].substituted_name = ''
                        this.product[index].substituted_number = ''
                        this.product[index].substituted_no = ''
                        this.options_replace_product =  row.replace_data
                    }
                    // 下拉 隐藏
                    if(!callback){
                        if(!this.product[index].substituted_no){
                            this.product[index].substituted_id = this.connt_savePRO.id
                            this.product[index].substituted_name = this.connt_savePRO.name
                            this.product[index].substituted_number = this.connt_savePRO.number
                            this.product[index].substituted_no = this.connt_savePRO.no
                        }else{
                            this.product[index].substituted_id = row.substituted_no.substituted_id
                            this.product[index].substituted_name = row.substituted_no.product_name
                            this.product[index].substituted_number = row.substituted_no.product_number
                            this.product[index].substituted_no = row.substituted_no.product_no
                            this.options_replace_product.length = 0
                            this.select_rep_pid(index,row)
                        }
                    }
                }
            },
            handleSelectionChange(val){
                console.log(val)
            },
            // 序号
            indexMethod(index){
                return index + 1 
            },
            // 出库套数
            handleChange(value){
                $('#con_red').attr('style','')
                if(this.product.length > 0){
                    // 找出  总 / 一个用量 的最小数
                    var minValue = Number(this.productionOrderData1[0].plan_number) - Number(this.productionOrderData1[0].used_num);
                    console.log('可以出库的数量== ',minValue)
                    /*
                    * 循环找出未出库量和每行能出库量的比值   那个小
                    */
                    for(let key in this.product){
                        var comeUp_go = Math.floor(Number(this.product[key].o_audit) + Number(this.product[key].stock_number) / Number(this.product[key].one_num));
                        console.log('第 ' + key + 1 + '行可以出库的量',comeUp_go)
                        comeUp_go > minValue? minValue = minValue:minValue = comeUp_go;
                    }
                    console.log('最终的嫩出库量是多少',minValue)
                    if( value != undefined){
                        if(value > minValue){
                            this.harness = ''
                            $('#con_red').attr('style','border: 1px solid red;border-radius: 4px;')
                            for(let key in this.product){
                                this.product[key].num = ''
                            }
                            layer.msg("出库输入量不能大于总套数所用量,最多只能出库"+minValue+"套")
                        }else{
                            for(let key in this.product){
                                this.product[key].num = value * Number(this.product[key].one_num)
                            }
                        }
                    }
                }
            },
            // 输入判断
            calculationAmount (index,row) {
                console.log('套数套数套数套数套数',this.harness)
                //  出库量 》 (库存 - 已出库量) / 套数
                if(Number(row.num) > (Number(row.o_audit) + Number(row.stock_number) - Number(row.used_num)) / Number(this.harness)){
                    row.num = ""
                    layer.msg("出货数量不能大于库存数量，请再次确认填写！")
                }
            },
            // 仓库下拉
            select_rep_pid(index,row){
                var materialId = ''
                var repID = ''
                if(row.substituted_id){
                    materialId = row.substituted_id
                }else{
                    materialId = row.product_id
                }
                for(var k = 0 ;k < repMap.length;k++){
                    if(row.rep_pid === repMap[k].repertory_name){
                        repID = repMap[k].rep_id
                    }else{
                        repID = row.rep_pid
                    }
                }
                var data = {    
                    materialId:materialId,
                    repId:repID
                }
                $.post('<?php echo U("Dwin/Stock/getStockMsgOne");?>',data,function(res){
                    if(res.status == 200){   
                        vm.product[index].o_audit = res.data.o_audit
                        vm.product[index].stock_number = res.data.stock_number
                    }else{
                        vm.product[index].o_audit = 0
                        vm.product[index].stock_number = 0
                        layer.msg(res.msg)
                    }
                    for(var i = 0;i< vm.product.length;i++){
                        vm.product[i].num = ''
                    }
                    vm.harness = ''
                })
            },
            // 删除 物料
            handleDelete(index, row) {
                if(index + 1 > this.product_comparison[0]){
                    // 新添加的
                    this.product.splice(index,1)
                }else{
                    // 原有的
                    var data = {
                        id:row.id,    //出库单物料id
                        productId:row.product_id,           //实际物料product_id
                        produceId:produceData.id      //领料出库单id
                    }
                    $.post('<?php echo U("/Dwin/Stock/delStockOutProduceMaterial");?>', data ,function(res){
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
                    var num_json = true
                    form.create_time = form.create_time / 1000

                    form.send_name = vm.send_name001.name
                    form.send_id = vm.send_name001.id

                    form.collect_name = vm.collect_name001.name
                    form.collect_id = vm.collect_name001.id

                    form.audit_name = vm.audit_name001.name
                    form.audit_id = vm.audit_name001.id

                    form.account_name = vm.account_name001.name
                    form.account_id = vm.account_name001.id

                    form.charge_name = vm.charge_name001.name
                    form.charge_id = vm.charge_name001.id

                    form.business_name = vm.business_name001.name
                    form.business_id = vm.business_name001.id

                    form.picking_dept_name = vm.picking_dept_name001.name
                    form.picking_dept_id = vm.picking_dept_name001.id

                    form.create_name= vm.create_name001.name
                    form.create_id = vm.create_name001.id
                    var new_material  = []
                    var edit_material  = []
                    for(var i = 0; i<this.product.length ;i++){
                        if(this.product[i].id){
                            edit_material.push({
                                'id':this.product[i].id,
                                'source_id':this.product[i].source_id,
                                'product_id':this.product[i].product_id,
                                'product_no':this.product[i].product_no,
                                'num':this.product[i].num,
                                'rep_pid':this.product[i].rep_pid,
                                'substituted_no':this.product[i].substituted_no,
                                'substituted_id':this.product[i].substituted_id
                            })
                        }
                        // }else{
                        //     new_material.push({
                        //         'product_id':this.product[i].product_id,
                        //         'product_no':this.product[i].product_no,
                        //         'num':this.product[i].num,
                        //         'rep_pid':this.product[i].rep_pid,
                        //         'substituted_no':this.product[i].substituted_no,
                        //         'substituted_id':this.product[i].substituted_id
                        //     })
                        // }
                    }
                     
                    for(var o = 0;o<new_material.length;o++){
                        for(var q = 0;q<repMap.length;q++){
                            if(new_material[o].rep_pid == repMap[q].repertory_name){
                                new_material[o].rep_pid = repMap[q].rep_id
                            }
                        }
                    }
                    for(var o = 0;o<edit_material.length;o++){
                        for(var q = 0;q<repMap.length;q++){
                            if(edit_material[o].rep_pid == repMap[q].repertory_name){
                                edit_material[o].rep_pid = repMap[q].rep_id
                            }
                        }
                    }
                    if(onSubmit_tf){
                        if(new_material.length + edit_material.length  == 0){
                            layer.msg('没有可提交的物料,请填写完整物料信息！')
                        }else{
                            var data = {
                                'produce':form,
                                'edit_material' : edit_material,
                                // 'new_material':new_material
                            }
                            layer.confirm('确定要提交吗？',function(aaa){
                                $.post('<?php echo U("/Dwin/Stock/editStockOutProduce");?>', data , function (res) {
                                    if(res.status == 200){
                                        // layer.open页面关闭
                                        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                        parent.layer.close(index)
                                    }else{
                       
                                    }
                                    layer.msg(res.msg)
                                })
                            })
                        }
                        
                    }else{form.create_time = new Date()}
                    
                    
                    
                
            },
            // 获取三个产品值
            searchingProduct: function() {
                // $.post('<?php echo U("Dwin/Purchase/getProductMsg");?>', {'condition':this.searchProduct.name}, function(res) {
                    vm.searchProductRes = orderMaterialData
                // })
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
                for(var i = 0 ; i < this.product.length;i++){
                    if(obj.product_id == this.product[i].product_id && judgement){
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