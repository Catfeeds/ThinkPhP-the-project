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
        /* input {
            width: 100%;
            height: 100%;
            display: block;
            outline: none;
            border: none !important
        } */
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
        .text_cen th{
            text-align: center
        }
    </style>
</head>
<body>
    <div id="app" style="text-align: center">
        <h1>湖南迪文科技有限公司采购订单</h1>
        <br><br><br>
        <el-form ref="form" :model="form" label-width="150px" size="medium" @submit.native.prevent v-loading="loading">
            <el-row>
                <el-col :span="10">
                    <el-form-item label="供方：">
                            <el-input v-model="form.supplier_name" readonly="readonly"></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="10" :offset="2">
                    <el-form-item label="合同编号：">
                        <el-input v-model="form.purchase_order_id" style="width: 63%;" readonly="readonly"></el-input>
                        <el-button type="primary" plain @click="getNumber()">获取编号</el-button>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="10">
                    <!-- <span style="font-size: 14px;color: #606266;font-weight: bold;margin-left: -4%;">需方：湖南迪文科技有限公司</span> -->
                    <el-form-item label="需方："  readonly="readonly">
                        <el-input readonly="readonly" v-model="form.demand_side"></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="10"  :offset="2">
                    <el-form-item label="签订时间：">
                        <el-input v-model="form.signing_time"  readonly="readonly"></el-input>
                        <!-- <el-date-picker
                        style="width: 100%;"
                        v-model="form.signing_time"
                        type="date"
                        value-format="timestamp" 
                        format="yyyy 年 MM 月 dd 日"
                        placeholder="选择日期">
                        </el-date-picker> -->
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="10"   style="text-align: left">
                    <!-- <span style="font-size: 14px;color: #606266;font-weight: bold;margin-left: -19%;">签订地点：湖南</span> -->
                    <el-form-item label="选购方式：">
                        <el-select v-model="purchase_type" placeholder="请选择" style="border: 1px solid #efefef">
                            <el-option
                                v-for="item in purchase_type_list"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="10"  :offset="2" style="text-align: left">
                    <!-- <span style="font-size: 14px;color: #606266;font-weight: bold;margin-left: -19%;">签订地点：湖南</span> -->
                    <el-form-item label="选购模式：">
                            <el-select v-model="purchase_mode" placeholder="请选择"  style="border: 1px solid #efefef">
                                <el-option
                                    v-for="item in purchase_mode_list"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                                </el-option>
                            </el-select>
                    </el-form-item>
                </el-col>
                </el-row>
                <el-row>
                        <el-col :span="10">
                            <p></p>
                        </el-col>
                        <el-col :span="10"  :offset="2">
                            <!-- <span style="font-size: 14px;color: #606266;font-weight: bold;margin-left: -19%;">签订地点：湖南</span> -->
                            <el-form-item label="签订地点：">
                                <el-input v-model="form.signing_place" readonly="readonly"></el-input>
                            </el-form-item>
                        </el-col>
                        </el-row>
            <el-row :gutter="20">
                <el-col :span="22" :offset="1">
                        <table class="table table-striped table-hover table-bordered" border style="margin-bottom: 0">
                                <div class="head_thead">一、产品名称、型号、单位、金额、供货时间及数量</div>
                                <tbody>
                                    <tr class="text_cen">
                                        <th v-show="false">ID</th>      
                                        <th style="width: 70px">序号</th>           
                                        <th style="width: 150px;">外部编号</th>      
                                        <th style="width: 150px;">型号</th>
                                        <th>单位</th>
                                        <th>数量</th>
                                        <th>单价(元)</th>
                                        <th>金额(元)</th>
                                        <th style="width: 120px;">交货日期</th>
                                    </tr>
                                    <tr v-for="(item, index) in product">
                                        <td v-show="false">
                                            <el-input v-model="item.product_id" ></el-input>
                                        </td>
                                        <td>
                                            {{item.sort_id}}
                                            <!-- <el-input v-model="item.sort_id" readonly="readonly"></el-input> -->
                                        </td>
                                        <td>
                                            {{item.product_name}}
                                        </td>
                                        <td>
                                            {{item.product_no}}
                                            <!-- <el-input v-model="item.product_no"  placeholder="型号"></el-input> -->
                                        </td>
                                        <td>
                                            {{item.unit}}
                                            <!-- <el-input v-model="item.unit" placeholder="单位"></el-input> -->
                                        </td>
                                        <td>
                                            {{item.purchase_number}}
                                            <!-- <el-input v-model="item.purchase_number" @keyup.native="calculationAmount(index)" placeholder="数量" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input> -->
                                        </td>
                                        <td>
                                            {{item.purchase_single_price}}
                                            <!-- <el-input v-model="item.purchase_single_price" @keyup.native="calculationAmount(index)"  placeholder="单价"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input> -->
                                        </td>
                                        <td>
                                            {{item.purchase_price}}
                                            <!-- <el-input v-model="item.purchase_price" placeholder="金额"  readonly="readonly"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input> -->
                                        </td>
                                        <td>
                                            {{item.deliver_time}}
                                            <!-- <el-input v-model="item.deliver_time" placeholder="交货日期"></el-input> -->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                    <br>
                    <el-row :gutter="20">
                        <el-col :span="15">
                            <p style="text-align: left">
                                <!-- <el-button @click="adds" type="primary">添加</el-button> -->
                            </p>
                            <p style="font-size: 16px;font-weight: bold;">备注：含税16%</p>
                        </el-col>
                        <el-col :span="9" label-width="150px">
                            <el-row>
                                <el-col :span="20" label>
                                    <el-form-item label="合计金额:">
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
                    </el-row>
                        
                        
               
                <!-- 文字表格 -->
                <table class="table table-striped table-hover table-bordered" border>
                        <div class="head_thead">二、合同修改内容</div>
                        <tbody>
                            <tr  class="text_cen">
                                <th>一、交货地点：</th>
                                <th>二、收货人：</th>
                                <th>三、收货人电话：</th>
                                <th>四、结算方式：</th>
                            </tr>
                            <tr>
                                <td>
                                    {{form.trading_location}}
                                    <!-- <el-input v-model="form.trading_location" readonly="readonly"></el-input> -->
                                </td>
                                <td>
                                    {{form.receiver}}
                                    <!-- <el-input v-model="form.receiver"  readonly="readonly"></el-input> -->
                                </td>
                                <td>
                                    {{form.receiving_phone}}
                                    <!-- <el-input v-model="form.receiving_phone"  readonly="readonly"></el-input> -->
                                </td>
                                <td>
                                    {{form.billing_method}}
                                    <!-- <el-input v-model="form.billing_method"  readonly="readonly"></el-input> -->
                                    <!-- <el-autocomplete
                                    class="inline-input"
                                    v-model="form.billing_method"
                                    :fetch-suggestions="querySearch_balance"
                                    placeholder="请输入内容"
                                    ></el-autocomplete> -->
                                </td>
                            </tr>
                        </tbody>
                </table>

            <br>
            <el-row>
                <el-col :span="10">
                    <el-form-item label="供方：">
                        <el-input v-model="form.supplier_name" readonly="readonly"></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="10" :offset="2">
                    <el-form-item label="需方：">
                        <el-input  v-model="form.demand_side" readonly="readonly"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="10">
                    <el-form-item label="单位名称：">
                        <el-input v-model="form.supplier_name"  readonly="readonly"></el-input>
                        <!-- <el-autocomplete
                            class="inline-input"
                            v-model="form.supplier_name"
                            :fetch-suggestions="querySearch"
                            placeholder="请输入供方名称"
                        ></el-autocomplete> -->
                    </el-form-item>
                </el-col>
                 <el-col :span="10" :offset="2">
                    <el-form-item label="单位名称：">
                            <!-- v-model="form.demand_side" -->
                        <el-input  value="湖南迪文科技有限公司" readonly="readonly"></el-input>
                    </el-form-item>
                    <!-- <span style="font-size: 14px;color: #606266;font-weight: bold;margin-left: -4%;">单位名称：湖南迪文科技有限公司</span> -->
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="10">
                    <el-form-item label="单位地址：">
                        <el-input v-model="form.supply_address" readonly="readonly"></el-input>
                        <!-- <el-autocomplete
                            class="inline-input"
                            v-model="form.supply_address"
                            :fetch-suggestions="querySearch_U"
                            placeholder="请输入供方单位地址"
                        ></el-autocomplete> -->
                    </el-form-item>
                </el-col>
                 <el-col :span="10"  :offset="2">
                    <el-form-item label="单位地址：">
                            <!-- v-model="form.demand_address" -->
                        <el-input v-model="form.demand_address" readonly="readonly"></el-input>
                    </el-form-item>
                    <!-- <span style="font-size: 14px;color: #606266;font-weight: bold;margin-left: -4%;">单位地址：湖南省常德市桃源县漳江创业园创业大道8号</span> -->
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="10">
                    <el-form-item label="法定代表：">
                        <el-input v-model="form.supplier_representative" readonly="readonly"></el-input>
                        <!-- <el-autocomplete
                            class="inline-input"
                            v-model="form.supplier_representative"
                            :fetch-suggestions="querySearch_law"
                            placeholder="请输入供方法人代表"
                            @select="handleSelect_of"
                        ></el-autocomplete> -->
                    </el-form-item>
                </el-col>
                <el-col :span="10"  :offset="2">
                    <el-form-item label="法定代表：">
                        <el-input v-model="form.purchaser_representative" readonly="readonly"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="10">
                    <el-form-item label="电话：">
                        <el-input v-model="form.supplier_phone" readonly="readonly"></el-input>
                        <!-- <el-autocomplete
                            class="inline-input"
                            v-model="form.supplier_phone"
                            :fetch-suggestions="querySearch_call"
                            placeholder="请输入电话"
                        ></el-autocomplete> -->
                    </el-form-item>
                </el-col>
                <el-col :span="10" :offset="2">
                    <el-form-item label="电话：">
                        <el-input v-model="form.purchaser_phone"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="10">
                    <el-form-item label="传真：">
                        <el-input v-model="form.supplier_fax" readonly="readonly"></el-input>
                        <!-- <el-autocomplete
                            class="inline-input"
                            v-model="form.supplier_fax"
                            :fetch-suggestions="querySearch_fax"
                            placeholder="请输入传真"
                        ></el-autocomplete> -->
                    </el-form-item>
                </el-col>
                <el-col :span="10" :offset="2">
                    <el-form-item label="传真：">
                        <el-input v-model="form.purchaser_fax"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <br><br>
            <el-button type="success" @click="onSubmit(form)">提交订单</el-button>
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
<script src="https://cdn.bootcss.com/element-ui/2.3.6/index.js"></script>
<script>
    var data = JSON.parse('<?php echo (json_encode($data)); ?>');
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                loading:true,
                form :{
                    id:'',
                    supplier_pid:'',
                    purchase_order_id:'',  //订单编码
                    signing_time: '',
                    total_amount:'',
                    demand_side:'',
                    signing_place:'',
                    supplier_name:'',
                    supply_address:'',
                    demand_address:'',
                    supplier_representative:'',
                    purchaser_representative:'',
                    supplier_phone:'',
                    purchaser_phone:'',
                    purchaser_fax:'',
                    supplier_fax:'',
                    trading_location:'',
                    receiver:'',
                    receiving_phone:'',
                    billing_method:''
                },
                searchProduct: {
                    name: ''
                },
                purchase_type:'赊购',
                // 选购方式
                purchase_type_list:[
                    {
                        label:'赊购',
                        value:'赊购'
                    }
                ],
                purchase_mode:'普通采购',
                // 选购模式
                purchase_mode_list:[
                    {
                        label:'普通采购',
                        value:'普通采购'
                    }
                ],
                searchProductRes: [],
                data:data,
                timeout:  null,
                deliver_time_Save:[],
                signing_time_Save:[],
                product:[],
                getID:''
            }
        },
        created () {
            this.loading = false
            console.log(data)
            this.form = data.contract
            this.signing_time_Save.push(this.form.signing_time)
            this.form.signing_time = this.formatDateTime(this.form.signing_time)
            this.product = data.product
            for(var i = 0;i < this.product.length;i++){
                this.deliver_time_Save.push(this.product[i].deliver_time) 
                this.product[i].deliver_time = this.formatDateTime(this.product[i].deliver_time)
            }
        },
        mounted() {
            
        },
        methods :{
            // 获取编号
            getNumber(){
                this.loading = true;
                $.get('<?php echo U("/Dwin/Purchase/createorderId");?>', function (res) {
                    if(res.status == 200){
                        vm.form.purchase_order_id = res.data.orderIdString
                        vm.getID = res.data.id
                    }
                    layer.msg(res.msg)
                    vm.loading = false
                })
            },
            // 提交
            onSubmit(form){
                var data = {
                    'id':this.getID,
                    'contractId' : form.id,
                    'orderId': form.purchase_order_id,
                    'purchaseMode':this.purchase_mode,
                    'purchaseType':vm.purchase_type
                }
                $.post('<?php echo U("Dwin/Purchase/createOrderWithContract");?>', data , function (res) {
                    if(res.status == 200){
                        // 关闭弹框 刷新父页面
                        layer.close(layer.index);
                        window.parent.location.reload();
                    }
                    layer.msg(res.msg)
                })
                
            },
            // 时间戳转化为时间
            formatDateTime:function (timeStamp) { 
                if(timeStamp != null&&timeStamp != 0){
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