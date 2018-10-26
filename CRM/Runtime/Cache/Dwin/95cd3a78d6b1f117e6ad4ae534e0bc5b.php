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
        .el-row{
            margin-bottom: 10px;
        }
        .cenNo{
            text-align: left
        }
    </style>
</head>
<body>
    <div id="app" style="text-align: center">
        <h1>湖南迪文科技有限公司领料单回退</h1>
        <br><br><br>
        <el-row>
            <el-col :span="22" :offset="1">
                    <el-form ref="form" :model="form" label-width="120px" size="medium" @submit.native.prevent v-loading="loading">
                            <el-row>
                                <el-col :span="7" :offset="1"  class="cenNo">
                                    <span>销售单号：</span>{{form.stock_out_id}}
                                </el-col>
                                <el-col :span="7" :offset="1"  class="cenNo">
                                    <span>领料部门：</span>{{form.picking_dept_name}}
                                </el-col>
                                <el-col :span="7" :offset="1"  class="cenNo">
                                    <span>
                                       出库类别名称： 
                                    </span>{{form.purchase_cate_name}}
                                </el-col>
                            </el-row>
                            <el-row>
                                <el-col :span="7" :offset="1" class="cenNo">
                                    <span>对方科目：</span>{{form.other_subject}}
                                </el-col>
                                <el-col :span="7" :offset="1" class="cenNo">
                                    <span>
                                            创建时间：
                                    </span>{{form.create_time}}
                                </el-col>
                                <el-col :span="7" :offset="1" class="cenNo">
                                    <span>
                                            选单号：
                                    </span>{{form.choise_no}}
                                </el-col>
                            </el-row>
                            <el-row>
                                <el-col :span="7" :offset="1" class="cenNo">
                                    <span>
                                            领料类型：
                                    </span>{{form.picking_kind}}
                                </el-col>
                                <el-col :span="7" :offset="9" class="cenNo">
                                    <b>打印次数：</b>{{form.printing_times}}
                                </el-col>
                            </el-row>
                            
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
        
                                            <p class="title_name_product"><b>三、出货单物料信息 </b></p>
                                            <p style="text-align: left" class="pp">
                                                <span><b>本次出库数量（套）</b></span><el-input v-model="harness" style="width:15%" readonly></el-input>
                                                <span>
                                                    &nbsp;&nbsp;
                                                </span>
                                                <el-button type="warning" @click="segment_go()">部分回退</el-button>
                                            </p>
                                            <el-table ref="multipleTable" :data="product" tooltip-effect="dark" style="width: 100%" @selection-change="handleSelectionChange" border>
                                            <el-table-column v-if="false" label="id" prop="id" type="index" align="center" header-align="center" width="50"></el-table-column>
                                            <el-table-column v-if="false" label="source_id" prop="id" type="index" align="center" header-align="center" width="50"></el-table-column>
                                            <el-table-column label="序号" width="50px" type="index" :index="indexMethod"> </el-table-column>
                                            <el-table-column v-if="false" label="product_id" prop="product_id" align="center" header-align="center"></el-table-column>
                                            <el-table-column v-if="false" label="product_no" prop="product_no" align="center" header-align="center"></el-table-column>
                                            <el-table-column v-if="false" label="substituted_id" prop="substituted_id" align="center" header-align="center"></el-table-column>
                                            <el-table-column label="物料编号" prop="substituted_no" align="center" header-align="center"></el-table-column>
                                            <el-table-column label="物料名称" prop="substituted_number" align="center" header-align="center"> </el-table-column>
                                            <el-table-column label="物料型号" prop="substituted_name" align="center" header-align="center"> </el-table-column>
                                            <el-table-column label="库存"  align="center" header-align="center">
                                                <template slot-scope="scope">
                                                    <div>{{Number(scope.row.stock_number) + Number(scope.row.o_audit)}}</div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column label="一套所用量" prop="one_num" align="center" header-align="center"> </el-table-column>
                                            <el-table-column label="出库数量" align="center" header-align="center" type="text">
                                                    <template slot-scope="scope">
                                                        {{scope.row.num}}
                                                    </template>
                                            </el-table-column>
                                            <el-table-column label="出货仓库" align="center" header-align="center" type="text">
                                                <template slot-scope="scope">
                                                        {{scope.row.rep_pid}}
                                                </template>    
                                            </el-table-column>
                                            </el-table>
                                        </div>
                                        <el-dialog title="部分回退：" class="cenNo" :visible.sync="dialogFormVisible_segment">
                                                
                                        <el-form-item label="当前出库的套：" class="cenNo">
                                            {{harness}}
                                            </el-form-item>
                                        <el-form-item label="回退数量：" class="cenNo">
                                                <el-input v-model="segment_num" @keyup.native="num_InputKey(segment_num)"></el-input>
                                            </el-form-item>
                                            <div slot="footer" class="dialog-footer">
                                                <el-button @click="dialogFormVisible_segment = false">取 消</el-button>
                                                <el-button type="primary" @click="segment_gooK()">回  退</el-button>
                                            </div>
                                        </el-dialog>                                    
                            <br>
                            <el-row> 
                                    <el-col :span="7" :offset="1" class="cenNo">
                                        <span>
                                                制单人： 
                                        </span>{{form.create_name}}
                                    </el-col>
                                    <el-col :span="7" :offset="1" class="cenNo">
                                        <span>
                                                发货人：
                                        </span>{{form.send_name}}
                                    </el-col>
                                    <el-col :span="7" :offset="1" class="cenNo">
                                        <span>
                                                领料人：
                                        </span>{{form.collect_name}}
                                    </el-col>
                                </el-row>
                                <el-row>
                                    <el-col :span="7" :offset="1" class="cenNo" >
                                        <span>
                                                审核人：
                                        </span>{{form.audit_name}}
                                    </el-col>
                                    <el-col :span="7" :offset="1" class="cenNo" >
                                        <span>
                                                记账人：
                                        </span>{{form.account_name}}
                                    </el-col>
                                </el-row>
                                <el-row>
                                        <el-col :span="23" :offset="1" class="cenNo">
                                            <span>
                                                备注：
                                            </span>{{form.tips}
                                        </el-col>
                                </el-row><br>
                                <br>
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
    console.log('repMap',repMap)
    console.log('staffData',staffData)
    console.log('deptData',deptData)
    console.log('orderMaterialData',orderMaterialData)
    console.log('pickingType',pickingType)
    console.log('produceData',produceData)
    console.log('materialData',materialData)
    console.log('cate_id',cate_id)
    console.log('cate_name',cate_name)
    console.log('productionOrderData',productionOrderData)
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                loading:true,
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
                // 部分回退
                segment_num:'',
                pro_id:'',
                indexSave:'',
                dialogFormVisible_segment:false
            }
        },
        created : function () {
            this.productionOrderData1 = []
            this.product_comparison.length = 0
            this.productionOrderData1.push(productionOrderData)
            this.form.purchase_cate_name = cate_name
            this.form.id = produceData.id
            this.form.source_id = produceData.source_id
            console.log(this.form.source_id)
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
                console.log(materialData[i])
                this.product[i].replace_data = materialData[i].replace_data
                for(let x = 0;x<repMap.length;x++){
                    if(materialData[i].rep_pid == repMap[x].rep_id){
                        this.product[i].rep_pid = repMap[x].repertory_name
                    }
                } 
                // 循环将 replace_data 第一个数填入 显示
                console.log(this.product[i])
                if(this.product[i].replace_data.length == 1){
                    // only one list
                    this.product[i].substituted_id = this.product[i].replace_data[0].substituted_id
                    this.product[i].substituted_no = this.product[i].replace_data[0].product_no
                    this.product[i].substituted_name = this.product[i].replace_data[0].product_name
                    this.product[i].substituted_number = this.product[i].replace_data[0].product_number
                }else{
                    // more list
                    for(var p = 0;p < this.product[i].replace_data.length;p++){
                        // debugger
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
            // 部分数量检测
            num_InputKey(value){
                if(value >=  this.harness){
                    layer.msg('部分回退回退数量不能大于等于申请数量')
                    this.segment_num = ''
                }
            },
            // 部分回退
            segment_go () {
                this.dialogFormVisible_segment = true
            },
            // 部分 
            segment_gooK(){
                var data = {
                    'id':produceData.id,
                    'num':this.segment_num
                }
                $.post('/Dwin/Stock/rollBacKProductMaterial',data,function(res){
                    if(res.status == 200){
                        this.dialogFormVisible_segment = false
                    }
                    layer.msg(res.msg)
                })
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
                    if(value > minValue){
                        this.harness = ''
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
            },
            // 输入判断
            calculationAmount (index,row) {
                console.log(row)
                console.log('套数套数套数套数套数',this.harness)
                //  出库量 》 (库存 - 已出库量) / 套数
                if(Number(row.num) > (Number(row.o_audit) + Number(row.stock_number) - Number(row.used_num)) / Number(this.harness)){
                    row.num = ""
                    layer.msg("出货数量不能大于库存数量，请再次确认填写！")
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