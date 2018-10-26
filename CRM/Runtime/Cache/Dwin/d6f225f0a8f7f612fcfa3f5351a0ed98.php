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
            border-top: none;
            margin-bottom: 50px;
        }
        .foot_table tr{
            height: 35px;
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
    </style>
</head>
<body class="gray-bg">
    <el-form ref="form" :model="form" label-width="150px" size="medium" @submit.native.prevent v-loading="loading">
        <div class="ibox-content" id="app" style="position: absolute">
            <el-row :getters="24">
                <el-col :span="22" :offset="1">

                    <!-- 表头 -->
                    <el-row>
                        <el-col :span="5">
                            <img :src="imgURL" alt="">
                        </el-col>
                        <el-col :span="12" :offset="6" class="companyName">
                            湖南迪文科技有限公司采购合同
                        </el-col>
                    </el-row>
                    <el-row class="row_top">
                        <el-col :span="15" :offset="1">
                            <b>需方：</b>{{form.demand_side}}
                        </el-col>
                        <el-col :span="7">
                                <b>合同编号：</b>{{form.contract_id}}
                        </el-col>
                    </el-row>
                    <el-row class="row_top">
                        <el-col :span="15" :offset="1">
                                <b>供方：</b>{{form.supplier_name}}
                        </el-col>
                        <el-col :span="7">
                                <b>签订时间：</b>{{form.signing_time}}
                        </el-col>
                    </el-row>
                    <el-row class="row_top">
                        <el-col :span="15" :offset="1" style="font-weight:bold">
                            一、物料名称、型号、单位、金额、供货时间及数量
                        </el-col>
                        <el-col :span="7">
                                <b>签订地点：</b>{{form.signing_place}}
                        </el-col>
                    </el-row>
                    <table class="table table-striped table-hover table-bordered" border style="margin-bottom: 0">
                            <tbody>
                                <tr  class="deal_cent">
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
                                        {{item.product_id}}
                                    </td>
                                    <td>
                                        {{item.sort_id}}
                                    </td>
                                    <td>
                                        {{item.product_number}}
                                    </td>
                                    <td>
                                        {{item.product_no}}
                                    </td>
                                    <td>
                                        {{item.unit}}
                                    </td>
                                    <td>
                                        {{item.purchase_number}}
                                    </td>
                                    <td>
                                        {{item.purchase_single_price}}
                                    </td>
                                    <td>
                                        {{item.purchase_price}}
                                    </td>
                                    <td>
                                        {{item.deliver_time}}
                                    </td>
                                </tr>
                                <tr>
                                    <!-- 右左 上下-->
                                    <td colspan="5" rowspan="3"><b>备注：含税16%</b></td>
                                    <td  colspan="4"><b>合计金额：</b> {{form.total_amount}}</td>
                                </tr>
                                <tr>
                                    <td  colspan="4" rowspan="2"><b>合计大写金额：</b> {{form.capital_amount}}</td>
                                </tr>
                                <tr></tr>
                            </tbody>
                        </table>
                        <table style="height: 450px;margin-top: 10px" >
                            <tbody class="toeTable_tr">
                                <tr>
                                    <td><b>二、交货期限：</b>供方使用特快专递按交货期供货，若有延误须在下单当天内与需方协商，如未经需方同意而无法交货或供货时断时续，超出时间按每日扣除合同总金额的0.5%计算，并追究供方由此给需方造成的影响，如需方需延迟交货当以书面形式通知供方。</td>
                                </tr>
                                <tr>  
                                    <td><b>三、运输方式及运输费用：</b>供方负责运输以及运输费用。</td>
                                </tr>
                                <tr> 
                                    <td>
                                            <b>四、交货地点及收货人：</b>{{form.trading_location}} ; {{form.receiver}}（收）电话：{{form.receiving_phone}}
                                    </td>
                                </tr>
                                <tr>     
                                    <td>
                                            <b>五、包装方式：</b>由供方负责，需保证需方收到货后物品无损；包装物不回收。
                                    </td>
                                </tr>
                                <tr> 
                                    <td>
                                            <b>六、质量要求及验收标准：</b>供方对产品提供合格证、需方提供验收标准和技术标准文档。经需方检验合格之日起算，质保期为一年。
                                    </td>
                                </tr>
                                <tr> 
                                    <td>
                                            <b>七、返修规定：</b>需方按规定判定的不良品由供方负责维修，不能修理的下次订单补齐或者进行退换，返修发生的一切费用都由供方承担；供方应保证十个工作日内将返修品送到需方加工厂，如超过期限，每日扣除货款总额的3%；超过期限五个工作日的视为供方的违约，需方可视情况解除合同并要求供方赔偿损失。
                                    </td>
                                </tr>
                                <tr> 
                                    <td>
                                            <b>八、结算方式：</b> {{form.billing_method}}。
                                    </td>
                                </tr>
                                <tr> 
                                    <td>
                                            <b>九、售后服务及承诺：</b>供方产品质保期内产品出现质量问题，供方无偿负责退换，终身维护。因产品的质量问题导致需方蒙受损失，需方有权向供方主张一切赔偿。
                                    </td>
                                </tr>
                                <tr> 
                                    <td>
                                            <b>十、违约责任：</b>违约方应主动协商解决，并承担违约责任赔偿另一方的经济损失；若供方违约，自愿承担合同总金额10%的违约金。
                                    </td>
                                </tr>
                                <tr> 
                                    <td>
                                            <b>十一、解决纠纷方式：</b>合同发生争议时，双方协商解决；协商不成的由合同签订地法院受理管辖。
                                    </td>
                                </tr>
                                <tr> 
                                    <td>
                                            <b>十二、其他约定事项：</b>本合同未规定的内容，双方协商觉得有必要增加合同条款的，可以增加合同条款，其效力等同于该合同。
                                    </td>
                                </tr>
                                <tr> 
                                    <td>
                                            <b>十三、本合同一式两份，经双方签字盖章后生效，传真件或扫描件均有效。</b>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="foot_table">
                            <tbody>
                                <tr>
                                    <td style="width: 50%">
                                            <b>供方：</b>{{form.supplier_name}}
                                    </td>
                                    <td>
                                            <b>需方：</b>{{form.demand_side}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                            <b>单位名称：</b>{{form.supplier_name}}
                                    </td>
                                    <td>
                                            <b>单位名称：</b>{{form.demand_side}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                            <b>单位地址：</b>{{form.supply_address}}
                                    </td>
                                    <td>
                                            <b>单位地址：</b>{{form.demand_address}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                            <b>法定代表：</b>{{form.supplier_representative}}
                                    </td>
                                    <td>
                                            <b>法定代表：</b>{{form.purchaser_representative}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                            <b>电话：</b>{{form.supplier_phone}}
                                    </td>
                                    <td>
                                            <b>电话：</b>{{form.purchaser_phone}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                            <b>传真：</b>{{form.supplier_fax}}
                                    </td>
                                    <td>
                                            <b>传真：</b>{{form.purchaser_fax}}
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
                    id:'',
                    supplier_pid:'',
                    contract_id:'DG-20180709',  //合同编码
                    signing_time:'',
                    signing_place:'',
                    total_amount:'1.0',
                    demand_side:'北京迪文',
                    supplier_name:'',
                    supply_address:'',
                    supplier_representative:'',
                    purchaser_representative:'',
                    supplier_phone:'',
                    purchaser_phone:'',
                    purchaser_fax:'',
                    supplier_fax:'',
                    trading_location:'',
                    receiver:'',
                    receiving_phone:'',
                    billing_method:'',
                    capital_amount:'壹元',
                },
                product:[
                    {
                        'sort_id':1,
                        'product_number' : '1',
                        'product_name' : '1',
                        'product_id' : '1',
                        'product_no' : '1',
                        'unit' : '1',
                        'purchase_number' : '1',
                        'purchase_single_price' : '1',
                        'purchase_price' : '1',
                        'deliver_time' : '1'
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
                $.post('<?php echo U("/Dwin/Purchase/getContractAllMsg");?>', data ,function(res){
                    if(res.status == 200){
                        this.loading = true
                        vm.form = res.data.contract
                        vm.form.signing_time = vm.formatDateTime(vm.form.signing_time)
                        vm.product = res.data.product
                        for(var i = 0; i < vm.product.length; i++){
                            vm.product[i].deliver_time = vm.formatDateTime(vm.product[i].deliver_time)
                        }
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