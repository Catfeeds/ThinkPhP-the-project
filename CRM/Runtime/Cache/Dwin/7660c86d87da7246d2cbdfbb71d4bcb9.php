<?php if (!defined('THINK_PATH')) exit();?><html>
<head>
    <style>
        .materialMsg {
            border:1px solid black;
            width: 100%;
            min-height: 30px;
            line-height: 30px;
            border-collapse: collapse;
            font-size: 14px;
        }
        .materialMsg tr {
            height: 30px;
            border:1px solid black;
        }
        .materialMsg td {
            height: 30px;
            border:1px solid black;
        }
        .foot_table{
            border:1px solid black;
            width: 100%;
            min-height: 30px;
            line-height: 30px;
            text-align: left;
            border-collapse: collapse;
            font-size: 14px;
        }
        .foot_table tr {
            width: 100%;
            border:1px solid black;
        }
        .foot_table td {
            width: 50%;
            border:1px solid black;
        }
    </style>
</head>
<body style="width:100%;">
    <table style="width: 100%; padding: 0 5%;">
        <tr style="height: 51px; line-height: 51px;">
            <td width="50%"><img src="/Public/Admin/images/dwinlogo.png" alt=""></td>
            <td width="50%" style="font-size: 22px"><b>湖南迪文科技有限公司采购合同</b></td>
        </tr>
    </table>
    <div style="margin: 0 5%">
        <table width="100%">
            <tr>
                <td width="3%"></td>
                <td width="55%" colspan="3"><b>需方：</b><?php echo $contract['demand_side']; ?></td>
                <td width="40%" colspan="2"><b>合同编号：</b><?php echo $contract['contract_id']; ?></td>
            </tr>
            <tr>
                <td width="3%"></td>
                <td width="57%" colspan="3"><b>供方：</b><?php echo $contract['supplier_name']; ?></td>
                <td width="40%" colspan="2"><b>签订时间：</b><?php echo date("Y-m-d",$contract['signing_time']); ?></td>
            </tr>
            <tr>
                <td width="3%"></td>
                <td width="57%" colspan="3"><b>一、物料名称、型号、单位、金额、供货时间及数量</b></td>
                <td width="40%" colspan="2"><b>签订地点：</b><?php echo $contract['signing_place']; ?></td>
            </tr>
        </table>
        <table class="materialMsg">
            <tr>
                <td>序号</td>
                <td>外部编号</td>
                <td>型号</td>
                <td>单位</td>
                <td>数量</td>
                <td>单价(元)</td>
                <td>金额(元)</td>
                <td>交货日期</td>
            </tr>
            <?php foreach($product as $k => $v){ ?>
            <tr>
                <td><?php echo $v['sort_id']; ?></td>
                <td><?php echo $v['product_number']; ?></td>
                <td><?php echo $v['product_no']; ?></td>
                <td><?php echo $v['unit']; ?></td>
                <td><?php echo $v['purchase_number']; ?></td>
                <td><?php echo $v['purchase_single_price']; ?></td>
                <td><?php echo $v['purchase_price']; ?></td>
                <td><?php echo date("Y-m-d",$v['deliver_time']); ?></td>
            </tr>
            <?php } ?>
            <tr>
                <td></td>
                <td>以下为空</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <!-- 右左 上下-->
                <td rowspan="2" colspan="4"><b>备注：含税16%</b></td>
                <td colspan="2"><b>合计金额：</b></td>
                <td colspan="2"><?php echo $contract['total_amount']; ?></td>
            </tr>
            <tr>
                <td colspan="2"><b>合计大写金额：</b></td>
                <td colspan="2"><?php echo $contract['capital_amount']; ?></td>
            </tr>
        </table>
        <table style="height: 450px;margin-top: 10px" >
            <tr>
                <td><b>二、交货期限：</b>供方使用特快专递按交货期供货，若有延误须在下单当天内与需方协商，如未经需方同意而无法交货或供货时断时续，超出时间按每日扣除合同总金额的0.5%计算，并追究供方由此给需方造成的影响，如需方需延迟交货当以书面形式通知供方。</td>
            </tr>
            <tr>
                <td><b>三、运输方式及运输费用：</b>供方负责运输以及运输费用。</td>
            </tr>
            <tr>
                <td>
                    <b>四、交货地点及收货人：</b><?php echo $contract['trading_location']; ?>;<?php echo $contract['receiver']; ?>（收）电话：<?php echo $contract['receiving_phone']; ?>
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
                    <b>八、结算方式：</b> </b><?php echo $contract['billing_method']; ?>。
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
        </table>
        <table class="foot_table">
        <tbody>
        <tr>
            <td>
                <b>供方：</b><?php echo $contract['supplier_name']; ?>
            </td>
            <td>
                <b>需方：</b><?php echo $contract['demand_side']; ?>
            </td>
        </tr>
        <tr>
            <td>
                <b>单位名称：</b><?php echo $contract['supplier_name']; ?>
            </td>
            <td>
                <b>单位名称：</b><?php echo $contract['demand_side']; ?>
            </td>
        </tr>
        <tr>
            <td>
                <b>单位地址：</b><?php echo $contract['supply_address']; ?>
            </td>
            <td>
                <b>单位地址：</b><?php echo $contract['demand_address']; ?>
            </td>
        </tr>
        <tr>
            <td>
                <b>法定代表：</b><?php echo $contract['supplier_representative']; ?>
            </td>
            <td>
                <b>法定代表：</b><?php echo $contract['purchaser_representative']; ?>
            </td>
        </tr>
        <tr>
            <td>
                <b>电话：</b><?php echo $contract['supplier_phone']; ?>
            </td>
            <td>
                <b>电话：</b><?php echo $contract['purchaser_phone']; ?>
            </td>
        </tr>
        <tr>
            <td>
                <b>传真：</b><?php echo $contract['supplier_fax']; ?>
            </td>
            <td>
                <b>传真：</b><?php echo $contract['purchaser_fax']; ?>
            </td>
        </tr>
        </tbody>
    </table>
    </div>
</body>
</html>


<!--
<!doctype html>
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
    </style>
</head>
<body class="gray-bg">
<el-form ref="form" :model="form" label-width="150px" size="medium" @submit.native.prevent v-loading="loading">
    <div class="ibox-content" id="app" style="position: absolute">
        <el-button type="success"  @click="CloseAfterPrint" v-show="print" class="but_print">打印</el-button>
        <el-row :getters="24">
            <el-col :span="22" :offset="1">

                &lt;!&ndash; 表头 &ndash;&gt;
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
                            &lt;!&ndash; <el-input v-model="item.product_id" ></el-input> &ndash;&gt;
                            {{item.product_id}}
                        </td>
                        <td>
                            &lt;!&ndash; <el-input v-model="item.sort_id" readonly="readonly"></el-input> &ndash;&gt;
                            {{item.sort_id}}
                        </td>
                        <td>
                            &lt;!&ndash; <el-input v-model="item.product_number" placeholder="外部编号"></el-input> &ndash;&gt;
                            {{item.product_number}}
                        </td>
                        <td>
                            {{item.product_no}}
                            &lt;!&ndash; <el-input v-model="item.product_no"  placeholder="型号"></el-input> &ndash;&gt;
                        </td>
                        <td>
                            &lt;!&ndash; <el-input v-model="item.unit" placeholder="单位"></el-input> &ndash;&gt;
                            {{item.unit}}
                        </td>
                        <td>
                            &lt;!&ndash; <el-input v-model="item.purchase_number" @keyup.native="calculationAmount(index)" placeholder="数量" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input> &ndash;&gt;
                            {{item.purchase_number}}
                        </td>
                        <td>
                            &lt;!&ndash; <el-input v-model="item.purchase_single_price" @keyup.native="calculationAmount(index)"  placeholder="单价"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input> &ndash;&gt;
                            {{item.purchase_single_price}}
                        </td>
                        <td>
                            &lt;!&ndash; <el-input v-model="item.purchase_price" placeholder="金额"  readonly="readonly"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46" ></el-input> &ndash;&gt;
                            {{item.purchase_price}}
                        </td>
                        <td>
                            {{item.deliver_time}}
                        </td>
                    </tr>
                    <tr>
                        &lt;!&ndash; 右左 上下&ndash;&gt;
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

                &lt;!&ndash; BOX END &ndash;&gt;
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
    var data = JSON.parse('<?php echo (json_encode($data)); ?>');
    // console.log(data)
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
            this.loading = true
            this.form = data.contract

            this.form.signing_time = this.formatDateTime(this.form.signing_time)
            this.product = data.product
            for(var i = 0; i < this.product.length; i++){
                this.product[i].deliver_time = this.formatDateTime(this.product[i].deliver_time)
            }
        },
        methods: {
            // 时间戳转化为时间
            formatDateTime:function (timeStamp) {
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
</html>-->