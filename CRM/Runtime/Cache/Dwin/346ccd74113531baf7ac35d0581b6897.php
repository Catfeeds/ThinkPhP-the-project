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
        body{
            color:black;
        }
        .ibox-content{
            padding: 0;
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
            margin-top: 9px
        }
        .toeTable_tr tr td{
            margin: 5px 0 !important;
            padding-left: 5px
        }
        .foot_table{
            width: 100%;
            /* border: 1px solid #e7e7e7; */
            border-top: none;
            margin-bottom: 50px;
        }
        .foot_table tbody tr td:nth-child(2){
            width: 50%;
        }
        .foot_table tr{
            height: 35px;
            /* border: 1px solid #e7e7e7; */
        }
        .foot_table tr td{
            padding-left: 45px
        }
        .but_print{
            position: fixed;
            right:20px;
            bottom: 50px;
            z-index: 13;
        }
        .layui-layer-title{
            border-bottom:none
        }
        .text_cen th{
            text-align: center
        }
    </style>
</head>
<body class="gray-bg">
    <el-form ref="form" :model="form" label-width="150px" size="medium" @submit.native.prevent v-loading="loading">
        <div class="ibox-content" id="app">
            <el-button type="success"  @click="CloseAfterPrint" v-show="print" class="but_print">打印</el-button>
            <el-row :getters="24">
                <el-col :span="22" :offset="1">

                    <!-- 表头 -->
                    <el-row>
                        <el-col :span="5">
                            <img :src="imgURL" alt="">
                        </el-col>
                        <el-col :span="12" :offset="6" class="companyName">
                            湖南迪文科技有限公司采购订单
                        </el-col>
                    </el-row>
                    <el-row class="row_top">
                        <el-col :span="15" :offset="1">
                            <b>需方：</b>湖南迪文科技有限公司
                            <!-- {{form.demand_side}} -->
                        </el-col>
                        <el-col :span="7">
                                <b>订单编号：</b>{{form.purchase_order_id}}
                        </el-col>
                    </el-row>
                    <el-row class="row_top">
                        <el-col :span="15" :offset="1">
                                <b>供方：</b>{{form.supplier_name}}
                        </el-col>
                        <el-col :span="7">
                                <b>签订时间：</b>{{form.order_time}}
                        </el-col>
                    </el-row>
                    <el-row class="row_top">
                        <!-- <el-col :span="15" :offset="1">
                                 <b>采购模式：</b>{{form.purchase_mode}}
                        </el-col> -->
                        <el-col :span="7" :offset="16">
                                <b>采购方式：</b>{{form.purchase_type}}
                        </el-col>
                    </el-row>
                    <el-row class="row_top">
                        <el-col :span="15" :offset="1" style="font-weight:bold">
                            一、产品名称、型号、单位、金额、供货时间及数量
                        </el-col>
                        <el-col :span="7">
                                <b>采购模式：</b>{{form.purchase_mode}}
                        </el-col>
                    </el-row>
                    <table class="table table-striped table-hover table-bordered" border style="margin-bottom: 0">
                            <tbody>
                                <tr  class="text_cen">
                                    <th v-show="false">ID</th>      
                                    <th style="width: 70px">序号</th>           
                                    <th style="width: 150px;">物料名称</th>      
                                    <th style="width: 150px;">物料型号</th>
                                    <th>购买数量</th>
                                    <th>已入库数量</th>
                                    <th>购买单价(元)</th>
                                    <th>金额(元)</th>
                                </tr>
                                <tr v-for="(item, index) in product" style="text-align: center">
                                    <td v-show="false">
                                        <!-- <el-input v-model="item.product_id" ></el-input> -->
                                        {{item.product_id}}
                                    </td>
                                    <td>
                                        <!-- <el-input v-model="item.sort_id" readonly="readonly"></el-input> -->
                                        {{item.sort_id}}
                                    </td>
                                    <td>
                                        <!-- <el-input v-model="item.product_number" placeholder="外部编号"></el-input> -->
                                        {{item.product_number}}
                                    </td>
                                    <td>
                                        {{item.product_no}}
                                        <!-- <el-input v-model="item.product_no"  placeholder="型号"></el-input> -->
                                    </td>
                                    <td>
                                        <!-- <el-input v-model="item.unit" placeholder="单位"></el-input> -->
                                        {{item.number}}
                                    </td>
                                    <td>
                                        <!-- <el-input v-model="item.purchase_number" @keyup.native="calculationAmount(index)" placeholder="数量" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input> -->
                                        {{item.stock_in_number}}
                                    </td>
                                    <td>
                                        <!-- <el-input v-model="item.purchase_single_price" @keyup.native="calculationAmount(index)"  placeholder="单价"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input> -->
                                        {{item.single_price}}
                                    </td>
                                    <td>
                                        <!-- <el-input v-model="item.purchase_price" placeholder="金额"  readonly="readonly"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input> -->
                                        {{item.total_price}}
                                    </td>
                                </tr>
                                <tr>
                                    <!-- 右左 上下-->
                                    <td colspan="5" rowspan="3"><b>备注：</b>含税16%</b></td>
                                    <td  colspan="4"><b>合计金额：</b> {{form.total_amount}}</td>
                                </tr>
                                <tr>
                                    <td  colspan="4" rowspan="2"><b>合计大写金额：</b> {{form.capital_amount}}</td>
                                </tr>
                                <tr></tr>
                            </tbody>
                        </table>
                       <!-- 文字表格 -->
                       <br>
                       <br>
                    <table class="table table-striped table-hover table-bordered" border>
                        <el-row class="row_top">
                            <el-col :span="15" :offset="1" style="font-weight:bold">
                                    <b>二、合同修改内容</b>
                            </el-col>
                            <!-- <el-col :span="7">
                                    <b>采购模式：</b>{{form.purchase_mode}}
                            </el-col> -->
                        </el-row>
                        <!-- <div class="head_thead" style="padding-left: 60px"><b>二、合同修改内容</b></div> -->
                        <tbody>
                            <tr  class="text_cen">
                                <th>一、交货地点：</th>
                                <th>二、收货人：</th>
                                <th>三、收货人电话：</th>
                                <th>四、结算方式：</th>
                            </tr>
                            <tr style="text-align: center">
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
                                </td>
                            </tr>
                        </tbody>
                    </table>
                        <table class="foot_table">
                            <tbody>
                                <!-- <tr>
                                    <td style="width: 50%">
                                            <b>供方：</b>{{form.supplier_name}}
                                    </td>
                                    <td>
                                            <b>需方：</b>{{form.demand_side}}
                                    </td>
                                </tr>-->
                                <tr>
                                    <td>
                                            <b>供方单位名称：</b>{{form.supplier_name}}
                                    </td>
                                    <td>
                                            <b>需方单位名称：</b>湖南迪文科技有限公司
                                            <!-- {{form.demand_side}} -->
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                            <b>供方单位地址：</b>{{form.supply_address}}
                                    </td>
                                    <td>
                                            <b>需方单位地址：</b>{{form.demand_address}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                            <b>供方法定代表：</b>{{form.supplier_representative}}
                                    </td>
                                    <td>
                                            <b>需方法定代表：</b>{{form.purchaser_representative}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                            <b>供方电话：</b>{{form.supplier_phone}}
                                    </td>
                                    <td>
                                            <b>需方电话：</b>{{form.purchaser_phone}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                            <b>供方传真：</b>{{form.supplier_fax}}
                                    </td>
                                    <td>
                                            <b>需方传真：</b>{{form.purchaser_fax}}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    <!-- BOX END -->
                </el-col>
            </el-row>
        </div>
    </el-form>
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
    var id = '<?php echo ($id); ?>' 
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                imgURL:'/Public/Admin/images/dwinlogo.png',
                form :{
                    supplier_name:'',               //'供方名称',
                    purchase_order_id:'',           // '订单编号',
                    order_time:'',              // '订单时间',
                    total_amount:'',                //L  '总金额',
                    capital_amount:'',              // '大写总金额',
                    receiver:'',                // '收货人',
                    trading_location:'',                //  '交货地点',
                    receiving_phone:'',             //'收货电话',
                    billing_method:'',              //'结算方式',
                    supply_address:'',              // '供方地址',
                    supplier_representative:'',     //'供方法定代表',
                    supplier_phone:'',              // '供方代表电话',
                    supplier_fax:'',                //'供方代表传真',
                    demand_address:'',              // '需方地址',
                    purchaser_representative:'',    // '需方法定代表',
                    purchaser_phone:'',             // '需方电话',
                    purchaser_fax:'',               // '需方传真',
                    purchase_mode:'',               // '采购模式',
                    purchase_type:''                // '采购方式',
                },
                product:[
                    {
                        sort_id:'',
                        product_number:'',
                        product_name:'',
                        number:'',
                        stock_in_number:'', 
                        single_price:'',
                        total_price:''
                    }
                ],
                loading:false,
                print:true
            }
        },
        created() {
            this.getData_fun()
        },
        methods: {
            getData_fun(){
                var data = {
                    'id' : id
                }
                $.post('<?php echo U("/Dwin/Purchase/getOrderMsg");?>', data ,function(res){
                    if(res.status == 200){
                        this.loading = true
                        vm.form = res.data.order
                        vm.product = res.data.product
                    }
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
            },
            CloseAfterPrint(){
                this.print = false
                if(tata=document.execCommand("print")){
                    window.close();
                    this.print = true
                }else setTimeout("CloseAfterPrint();",1000);
            }
        }
    })
</script>
</body>
</html>