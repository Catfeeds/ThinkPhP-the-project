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
        .companyName{
            height: 51px;
            text-align: center;
            line-height: 51px;
            font-size: 22px;
            font-weight: bold;
            font-family: 微软雅黑
        }
        .row_top{
            margin-top: 9px;
            text-align: left
        }
        .deal_cent th{
          text-align: center
        }
        .but_print{
            position: fixed;
            right:20px;
            bottom: 50px;
            z-index: 13;
        }
    </style>
</head>
<body>
    <div id="app" style="text-align: center">
        <el-form ref="form" :model="form" label-width="170px" size="medium" @submit.native.prevent v-loading="loading">
          <el-button type="success"  @click="CloseAfterPrint" v-show="print" class="but_print">打印</el-button>
          <!-- 表头 -->
          <el-row>
              <el-col :span="5">
                  <img :src="imgURL" alt="">
              </el-col>
              <el-col :span="13" :offset="5" class="companyName">
                  湖南迪文科技有限公司其他出库单详情
              </el-col>
          </el-row> 
          <br> 

          <el-row class="row_top">
              <el-col :span="15" :offset="2">
                  <b>申领部门：</b>{{form.picking_dept_name}}
              </el-col>
              <el-col :span="6">
                      <b>出库编号：</b>{{form.stock_out_id}}
              </el-col>
          </el-row>
          <el-row class="row_top">
              <el-col :span="15" :offset="2">
                      <b>领料类型：</b>{{form.picking_kind}}
              </el-col>
              <el-col :span="6">
                      <b>审核时间：</b>{{form.audit_time}}
              </el-col>
          </el-row>
          <el-row class="row_top">
              <el-col :span="15" :offset="2">
                      <b>工程项目：</b>{{form.engine_item}}
              </el-col>
              <el-col :span="6">
                      <b>出库类别：</b>{{form.purchase_cate_name}}
              </el-col>
          </el-row>
          <el-row class="row_top">
              <el-col :span="15" :offset="2">
                      <b>选单号：</b>{{form.choose_no}}
              </el-col>
              <el-col :span="6">
                      <b>打印次数：</b>{{form.printing_times}}
              </el-col>
          </el-row>
          <el-row class="row_top">
              <el-col :span="22" :offset="2">
                      <b>用途：</b>{{form.purpose}}
              </el-col>
          </el-row>

          <!-- <el-row>
                <el-col :span="8" :offset="1">
                    <el-form-item label="出库单编号：">
                      {{form.stock_out_id}}
                        <el-input v-model="form.stock_out_id" style="width: 100%;" readonly></el-input>
                        <el-input v-if="false" v-model="form.id" style="width: 170px;" readonly></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8" :offset="2">
                    <el-form-item label="领料类型：">
                      {{form.picking_kind}}
                             <el-input v-model="form.picking_kind" style="width: 100%;" readonly></el-input>
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
                    <el-form-item label="申领部门：">
                      {{form.picking_dept_name}}
                            <el-input v-model="form.picking_dept_name" style="width: 100%;" readonly></el-input>
                            <el-input v-show="false" v-model="form.picking_dept_id" style="width: 100%;" readonly></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8" :offset="2">
                   <span style="font-size: 14px;color: #606266;font-weight: bold;margin-left: -19%;">签订地点：湖南</span>
                    <el-form-item label="审核时间：">
                      {{form.audit_time}}
                      <el-date-picker
                                style="width: 100%;"
                                v-model="form.audit_time"
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
                    <el-form-item label="用途：">
                      {{form.purpose}}
                         <el-input v-model="form.purpose" style="width: 100%;" placeholder="请填写用途"></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8" :offset="2">
                        <el-form-item label="选单号：">
                          {{form.choose_no}}
                            <el-input v-model="form.choose_no" style="width: 100%;" placeholder="请填写选单号"></el-input> 
                        </el-form-item>
                    </el-col>
            </el-row>
            <el-row>
                <el-col :span="8" :offset="1">
                    <el-form-item label="工程项目：">
                      {{form.engine_item}}
                             <el-input v-model="form.engine_item" style="width: 100%;" placeholder="请填写工程项目"></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8" :offset="2">
                        <el-form-item label="打印次数：">
                          {{form.printing_times}}
                             <el-input v-model="form.printing_times" style="width: 100%;" disabled></el-input>
                        </el-form-item>
                    
                    </el-col>
            </el-row>
             <el-row>
                <el-col :span="8" :offset="1">
                    <el-form-item label="出库类别：">
                      {{form.purchase_cate_name}}
                            <el-input v-if="false" v-model="form.purchase_cate_id" style="width: 100%;" placeholder="请填写工程项目"></el-input>
                            <el-input v-if="false" v-model="form.purchase_cate_name" style="width: 100%;" placeholder="请填写工程项目"></el-input>
                            <el-select v-model="purchase_cate_name001" value-key="id" filterable placeholder="请选择" style="width: 100%;">
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
            </el-row> -->
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
                                        <!-- <th>单位</th> -->
                                        <th>申请数量</th>
                                        <!-- <th>单价(元)</th> -->
                                        <!-- <th>总金额(元)</th> -->
                                        <!-- <th style="width: 80px;">需求时间</th> -->
                                        <!-- <th>备注</th> -->
                                        <th>出货仓库</th>
                                        <th >备注</th>
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
                                        <!-- <td> -->
                                            <!-- {{item.unite}} -->
                                            <!-- <el-input v-model="item.unite" placeholder="单位"></el-input> -->
                                        <!-- </td> -->
                                        <td>
                                            {{item.num}}
                                            <!-- <el-input v-model="item.num" @keyup.native="calculationAmount(index)" placeholder="数量" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input> -->
                                        </td>
                                        <!-- <td>
                                            {{item.price}}
                                             <el-input v-model="item.price" @keyup.native="calculationAmount(index)"  placeholder="单价"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input>
                                        </td>
                                        <td>
                                            {{item.total_price}}
                                            <el-input v-model="item.total_price" placeholder="金额"></el-input>
                                        </td>
                                         <td>
                                            <el-date-picker
                                                style="width: 135px;"
                                                v-model="item.demand_time"
                                                type="date"
                                                disabled
                                                value-format="timestamp"
                                                format="yyyy-MM-dd"
                                                placeholder="选择申请日期">
                                            </el-date-picker>
                                        </td> -->
                                        <!-- <td>
                                            <el-input v-model="item.tips" placeholder="请输入" type="textarea" readonly></el-input>
                                        </td> -->
                                        <td>
                                          {{item.rep_pid}}
                                                <!-- <el-select v-model="item.rep_pid" value-key="id" filterable placeholder="请选择">
                                                    <el-option
                                                        v-for="item in options_rep_pid"
                                                        :key="item.id"
                                                        :label="item.repertory_name"
                                                        :value="item.rep_id"
                                                        >
                                                    </el-option>
                                                </el-select> -->
                                            <!-- <el-input v-model="item.stock_out_num" placeholder="请输入" type="textarea"></el-input> -->
                                        </td>
                                        <td>{{item.tips}}
                                            <!-- <el-input v-model="item.tips" placeholder="请输入" type="textarea"></el-input> -->
                                        </td>
                                        <!-- <td>
                                            <button class="btn btn-warning" @click="delawards11(index)">删除</button>
                                        </td> -->
                                    </tr>
                                </tbody>
                            </table>
                            
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
                    <el-col :span="7" :offset="2" class="row_top">
                        <b>负责人：</b>{{form.charge_name}}
                        <!-- <el-form-item label="负责人：">
                          
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
                        </el-form-item> -->
                    </el-col>
                    <el-col :span="7" :offset="1"  class="row_top">
                        <b>制单人：</b>{{form.create_name}}
                        <!-- <el-form-item label="制单人：">
                          
                                <el-input v-if="false" v-model="form.create_id" style="width: 100%;"></el-input>
                                <el-input v-if="false" v-model="form.create_name" style="width: 100%;"></el-input>
                                <el-input v-model="form.create_name" placeholder="请输入" type="text" readonly></el-input>
                            <el-select v-model="form.create_name" value-key="id" filterable placeholder="请选择">
                                <el-option
                                    v-for="item in options_create_name"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item"
                                    >
                                </el-option>
                            </el-select>
                        </el-form-item> -->
                </el-col>
                <el-col :span="6" :offset="1"  class="row_top">
                    <b>审核人：</b>{{form.audit_name}}
                    <!-- <el-form-item label="审核人：">
                      {{form.audit_name}}
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
                    </el-form-item>-->
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="7"  :offset="2" class="row_top">
                    <b>领料人：</b>{{form.collect_name}}
                       <!-- <el-form-item label="领料人：">
                          {{form.collect_name}}
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
                        </el-form-item> -->
                </el-col>
                <el-col :span="7" :offset="1"  class="row_top">
                    <b>发货人：</b>{{form.send_name}}
                     <!--<el-form-item label="发货人：">
                      {{form.send_name}}
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
                    </el-form-item> -->
                </el-col>
                <el-col :span="6" :offset="1"  class="row_top">
                    <b>制单人：</b>{{form.account_name}}
                        <!--<el-form-item label="记账人：">
                          {{form.account_name}}
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
                        </el-form-item> -->
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="7" :offset="2"  class="row_top">
                    <b>业务员：</b>{{form.business_name}}
                    <!-- <el-form-item label="业务员：">
                      
                            <el-input v-if="false" v-model="form.business_id" style="width: 100%;"></el-input>
                            <el-input v-model="form.business_name" style="width: 100%;" readonly></el-input>
                            <el-select v-model="business_name001" value-key="id" filterable placeholder="请选择">
                                <el-option
                                    v-for="item in options_business_name"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item"
                                    >
                                </el-option>
                            </el-select>
                    </el-form-item> -->
                </el-col>
            </el-row>
            <el-row>
                <el-col :offset="2" class="row_top">
                    <b>备注：</b>{{form.tips}}
                     <!-- <el-form-item label="备注：">
                      
                       <el-input type="textarea" v-model="form.tips" style="width: 100%;"></el-input>
                    </el-form-item> -->
                </el-col>
            </el-row>
            
            <br><br>
            <!-- <el-button type="success" @click="onSubmit(form)">提 交</el-button> -->
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
    var id = <?php echo (json_encode($id)); ?>;  //其他出库类型基本信息
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                imgURL:'/Public/Admin/images/dwinlogo.png',
                loading:true,
                serial_Number:'1',  //序号
                form :{
                    id:'',
                    stock_out_id:'',
                    source_id:'',
                    picking_kind:'',
                    // apply_dept_name:'',
                    total_amount :'0',
                    capital_amount:'零',
                    // apply_reason:'',
                    audit_time:0,
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
                // create_name001:'',
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
                options_charge_name:[],
                options_send_name:[],
                options_business_name:[],
                options_account_name:[],
                timeout:  null,
                product:[],
                initial_row:[],
                add_operate:[],
                edit_operate:[],
                print:true
                
            }
        },
        created : function () {
            this.loading = false
            this.initial_row.length = 0
            this.options_picking_kind.length = 0
            this.options_apply_dept_name.length = 0
          $.post('<?php echo U("/Dwin/Stock/otherStockOutAllMsg");?>',{id:id},function(res){
            // 'stockData'  // 其他出库类型出库单基本信息
            // 'materialData' // 其他出库类型出库单物料信息
            // 'outOfTreasuryType'  // 其他出库类型中的出库类别
            // 'repMap'   // 其他出库名称
            // 'staffData'  // 公司员工map
            // 'deptData'   // 部门map
            // "cate_id"  // 当前出库单类型id
            // "cate_name"  // 出库类型名称
            // "auditMap" 
            // 'pickingType'  // 领料信息
            vm.form = res.data.stockData
            vm.form.tips = res.data.stockData.tips
            vm.form.audit_time = vm.formatDateTime(vm.form.audit_time)
            for(var key in res.data.pickingType){
              if(vm.form.picking_kind == key){
                vm.form.picking_kind = res.data.pickingType[key]
              }
            }
            // 物料赋值
            for(let i = 0; i < res.data.materialData.length;i++){
                var obJ = {
                    source_id:'',
                    product_id:'',
                    product_number:'',
                    product_name:'',
                    product_no:'',
                    num:'',
                    rep_pid:''
                }
                vm.product.push(obJ)
                for(let x = 0;x<res.data.repMap.length;x++){
                    if(res.data.materialData[i].rep_pid == res.data.repMap[x].rep_id){
                      res.data.materialData[i].rep_pid = res.data.repMap[x].repertory_name
                    }
                }
                
            }
            vm.product = res.data.materialData
          })
        },
        mounted() {
        },
        methods :{
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
        },
        CloseAfterPrint(){ 
            this.print = false
            if (confirm("是否打印")==true){ 
              if(tata=document.execCommand("print")){
                  window.close();
                  this.print = true
                  $.post('<?php echo U("/Dwin/Stock/editStockOutOtherPrintTime");?>',{id:id},function (res) {
                      location.reload()
                  })
              }else setTimeout("CloseAfterPrint();",1000);
            }
        }
      }
    })
</script>
</html>