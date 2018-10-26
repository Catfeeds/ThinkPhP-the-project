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
        .companyName{
            height: 51px;
            text-align: center;
            line-height: 51px;
            font-size: 22px;
            font-weight: bold;
            font-family: 微软雅黑
        }
        .but_print{
            position: fixed;
            right:20px;
            bottom: 50px;
            z-index: 13;
        }
        .row_top{
            margin-top: 9px;
            text-align: left
        }
    </style>
</head>
<body>
    <div id="app" style="text-align: center">
        <!-- 表头 -->
        <el-button type="success"  @click="CloseAfterPrint" v-show="print" class="but_print">打印</el-button>
        <el-row>
                <el-col :span="7">
                    <img :src="imgURL" alt="">
                </el-col>
                <el-col :span="11" :offset="5" class="companyName">
                    湖南迪文科技有限公司销售出库详情
                </el-col>
            </el-row> 
        <el-row>
            <br>

            <el-col :span="22" :offset="1">
                    <el-form ref="form" :model="form" label-width="120px" size="medium" @submit.native.prevent v-loading="loading">
                            <el-row class="row_top">
                                <el-col :span="14"  :offset="2">
                                        <b>销售出库单号：</b>{{form.stock_out_id}}
                                </el-col>
                                <el-col :span="6">
                                        <b>快递单号：</b>{{form.express_no}}
                                        <!-- <b>购买单位：</b>{{form.cus_name}} -->
                                </el-col>
                            </el-row>
                            <el-row  class="row_top">
                                <el-col :span="14" :offset="2">
                                        <!-- <b>源单类型：</b>{{stockOutType[form.source_kind]}} -->
                                        <b>源单类型：</b>{{form.cate_name}}
                                </el-col>
                                <el-col :span="6">
                                        <b>选单号：</b>{{form.choose_no}}
                                </el-col>
                            </el-row>
                            <el-row :gutter="20" >
                                <el-col :span="24">
                                        <!-- 表格 -->
                                        <br>
                                            <div>
                                                <el-table ref="multipleTable" :data="product" tooltip-effect="dark" style="width: 100%" @selection-change="handleSelectionChange" border>
                                                <el-table-column v-if="false" label="id" prop="id" type="index" align="center" header-align="center" width="50"></el-table-column>
                                                <el-table-column v-if="false" label="source_id" prop="id" type="index" align="center" header-align="center" width="50"></el-table-column>
                                                <el-table-column v-if="false" label="product_id" prop="product_id" align="center" header-align="center"></el-table-column>
                                                <el-table-column label="序号" type="index" width="50px" :index="indexMethod"> </el-table-column>
                                                <el-table-column label="物料名称" prop="product_number" align="center" header-align="center"> </el-table-column>
                                                <el-table-column label="物料型号" prop="product_name" align="center" header-align="center"> </el-table-column>
                                                <el-table-column label="物料编号" prop="product_no" align="center" header-align="center"></el-table-column>
                                                <el-table-column label="待出数量" prop="out_processing" align="center" header-align="center"></el-table-column>
                                                <el-table-column label="锁库数量" prop="o_audit" align="center" header-align="center"></el-table-column>
                                                <el-table-column label="库存数量" prop="stock_number" align="center" header-align="center"></el-table-column>
                                                <el-table-column  label="需求量" prop="product_num" type="index" align="center" header-align="center" width="50"></el-table-column>
                                                <el-table-column  label="已出数量" prop="used_num" type="index" align="center" header-align="center" width="50"></el-table-column>
                                                <el-table-column label="出库数量" prop="num"  width="100px" align="center" header-align="center" type="text">
                                                </el-table-column>
                                                <el-table-column label="出货仓库" prop="rep_pid" width="150px" align="center" header-align="center" type="text"> 
                                                </el-table-column>
                                                </el-table>
                                            </div>
                                    <br>
                            <el-row class="row_top"> 
                                <el-col :span="7" :offset="1">
                                    <b>业务员：</b> {{form.pic_name}}
                                </el-col>
                                <el-col :span="7" :offset="1">
                                    <b>制单人：</b>  {{form.create_name}}
                                </el-col>
                                <el-col :span="7" :offset="1">
                                    <b>发货人：</b> {{form.send_name}}
                                </el-col>
                            </el-row>
                            <el-row  class="row_top">
                                <el-col :span="7"  :offset="1">
                                    <b>审核人：</b> {{form.audit_name}}
                                </el-col>
                                <el-col :span="7" :offset="1">
                                    <b>创建时间：</b> {{form.create_time}}
                                </el-col>
                                <el-col :span="7" :offset="1">
                                    <b>保管人：</b> {{form.keep_name}}
                                </el-col>
                            </el-row>
                            <el-row class="row_top">
                                <el-col :span="22" :offset="1">
                                    <b>入库备注：</b> {{form.tips}}
                                </el-col>
                            </el-row>
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
    var id = <?php echo (json_encode($id)); ?>;
    var stockOutType = <?php echo (json_encode($stockOutType)); ?>;
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                loading:true,
                GeTATry:false,
                imgURL:'/Public/Admin/images/dwinlogo.png',
                form :{
                        stock_out_id:'',    //出库单编号',
                        source_id:'',   //源单主键',
                        tips:'',    //入库备注',
                        // xin
                        send_name:'',   //发货人
                        send_id:'',   //发货人id
                        printing_times:'',  //打印次数',
                        audit_id:'',    //审核人ID',
                        audit_name:'',  //审核人姓名',
                        keep_id:'',     //保管人id',
                        keep_name:'',   //保管人姓名',
                        create_id:'',   //制单人id',
                        create_name:'',     //制单人姓名',
                        create_time:new Date(),     //创建时间',
                        choose_no:'',   //选单号',
                        cate_name:'',   //出库类型名称',
                        cate_id:'',     //出库类型id',
                        source_kind:'',     //源单类型',
                        express_no:'',  //快递单号',
                        

                        // jiu
                        // logistices_tip:'',  //物流信息
                        pic_name:'',    //业务员姓名',
                        finance_check_id:'', //记账人id
                        finance_check_name:'',  //记账人
                        // invoice_situation:'', //票或情况
                        // cus_name:'',   //购买单位
                        // receiver:'',   //联系人
                        // receiver_phone:'',  //收货人电话
                        // settlement_time:'',  //收货时间
                        // invoice_addr:"",    //发货地址
                        // odetail:'',     //摘要
                        // freight_payment_method:'',   //运费支付
                        // static_type:'',     //业务类型
                        // order_type:'',      //营销方式
                        // receiver_addr:'',    //收货地址
                        // delivery_ware_house:'',  //订单仓库
                        // deliverytime:'',    //交货日期

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
                // 保存 product
                // product_comparison:[]
                // 选中三个值 ，新增数据
                searchProduct: {
                    name: ''
                },
                searchProductRes: [],
                save_productNum:[],
                print:true
                
            }
        },
        created : function () {
            this.loading = false
            this.product_comparison = [] 
            this.save_productNum.length = 0 
            $.post('<?php echo U("stockOutOrderMsg");?>',{'id':id},function (res) { 
                if(res.status == 200){
                    vm.form.pic_name = res.data.formData.pic_name,    //业务员姓名',
                    vm.form.invoice_situation = res.data.formData.invoice_situation, //票或情况
                    vm.form.cus_name = res.data.formData.cus_name,   //购买单位
                    vm.form.receiver = res.data.formData.receiver,   //联系人
                    vm.form.receiver_phone = res.data.formData.receiver_phone,  //收货人电话
                    vm.form.settlement_time = vm.formatDateTime(res.data.formData.settlement_time),  //收货时间
                    vm.form.invoice_addr = res.data.formData.invoice_addr,    //发货地址
                    vm.form.odetail = res.data.formData.odetail,     //摘要
                    vm.form.freight_payment_method = res.data.formData.freight_payment_method,   //运费支付
                    vm.form.static_type = res.data.formData.static_type,     //业务类型
                    vm.form.order_type = res.data.formData.order_type,      //营销方式
                    vm.form.receiver_addr = res.data.formData.receiver_addr    //收货地址
                    for(let x = 0;x<res.data.repMap.length;x++){
                        if(res.data.formData.delivery_ware_house == res.data.repMap[x].rep_id){
                            vm.form.rep_pid = res.data.repMap[x].repertory_name
                        }
                    } 
                    vm.form.deliverytime =  vm.formatDateTime(res.data.formData.deliverytime),    //交货日期

                    vm.form.tips = res.data.orderformData.tips,   
                    vm.form.source_id = res.data.orderformData.id,    //源单主键' 
                    vm.form.logistices_tip = res.data.formData.logistices_tip, //物流信息

                    //  xin
                    vm.form.id = res.data.orderformData.id   //主键ID
                    vm.form.stock_out_id = res.data.orderformData.stock_out_id     //单编号
                    vm.form.send_name = res.data.orderformData.send_name   //发货人
                    vm.form.send_id = res.data.orderformData.send_id   //发货人id
                    vm.form.printing_times = res.data.orderformData.printing_times  //打印次数',
                    vm.form.audit_id = res.data.orderformData.audit_id    //审核人ID',
                    vm.form.audit_name = res.data.orderformData.audit_name  //审核人姓名',
                    vm.form.keep_id = res.data.orderformData.keep_id     //保管人id',
                    vm.form.keep_name = res.data.orderformData.keep_name   //保管人姓名',
                    vm.form.create_id = res.data.orderformData.create_id   //制单人id',
                    vm.form.create_name = res.data.orderformData.create_name     //制单人姓名',
                    vm.form.create_time = vm.formatDateTime(res.data.orderformData.create_time),     //创建时间',
                    vm.form.choose_no = res.data.orderformData.choose_no   //选单号',
                    vm.form.cate_name = res.data.cate_name   //出库类型名称',
                    vm.form.cate_id = res.data.orderformData.cate_id     //出库类型id',
                    vm.form.source_kind = res.data.orderformData.source_kind     //源单类型',
                    vm.form.express_no = res.data.orderformData.express_no  //快递单号',   

                    //记账人
                    vm.form.finance_check_id = res.data.formData.finance_check_id //记账人
                    for(var i = 0;i< res.data.staffData.length;i++){
                        if(vm.form.finance_check_id == res.data.staffData[i].id){
                            vm.form.finance_check_name = res.data.staffData[i].name
                        }
                    }

                    //人员信息
                    vm.options_audit_name = res.data.staffData
                    vm.options_keep_name = res.data.staffData
                    vm.options_send_name = res.data.staffData
                    vm.audit_name001 = {
                        'name':res.data.orderformData.audit_name,
                        'id':res.data.orderformData.audit_id
                    }
                    vm.keep_name001 = {
                        'name':res.data.orderformData.keep_name,
                        'id':res.data.orderformData.keep_id
                    }
                    vm.send_name001 = {
                        'name':res.data.orderformData.send_name,
                        'id':res.data.orderformData.send_id
                    }

                    // 出库下拉 
                    vm.options_rep_pid = res.data.repMap


                    // 物料赋值   二
                    for(let i in res.data.materialData){
                        var obJ = {
                            source_id:'',
                            product_id:'',
                            product_number:'',
                            product_name:'',
                            product_no:'',
                            out_processing:'',
                            o_audit :'',
                            stock_number:'',
                            num  :'',
                            // product_type :'',
                            rep_pid:'',
                            used_num:'',
                            product_num:''
                        }

                        vm.product.push(obJ)
                        vm.product[i].id = res.data.materialData[i].id
                        vm.product[i].source_id = res.data.materialData[i].source_id
                        vm.product[i].product_id = res.data.materialData[i].product_id
                        vm.product[i].product_number = res.data.materialData[i].product_number
                        vm.product[i].product_name = res.data.materialData[i].product_name
                        vm.product[i].product_no = res.data.materialData[i].product_no
                        vm.product[i].o_audit = res.data.materialData[i].o_audit
                        vm.product[i].stock_number = res.data.materialData[i].stock_number
                        vm.product[i].out_processing = res.data.materialData[i].out_processing
                        vm.product[i].product_type = res.data.materialData[i].product_type
                        vm.product[i].used_num = res.data.materialData[i].used_num
                        vm.product[i].product_num = res.data.materialData[i].product_num
                        vm.product[i].num = res.data.materialData[i].num
                        vm.save_productNum.push(res.data.materialData[i].num)
                        for(let x = 0;x<res.data.repMap.length;x++){
                            if(res.data.materialData[i].rep_pid == res.data.repMap[x].rep_id){
                                vm.product[i].rep_pid = res.data.repMap[x].repertory_name
                            }
                        }
                    }
                    vm.product_comparison.push(res.data.materialData.length)
                }
             })

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
                debugger
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
                        }
                    }else{
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
                        'stock_out_id' : form.stock_out_id,           //'出库单编号',
                        'source_id' : form.source_id,           //'源单主键',
                        'cate_id' : form.cate_id,           //'出库类型id',
                        // 'cate_name' : form.cate_name,           //'出库类型名称',
                        'source_kind' : form.source_kind,          //'源单类型',
                        // 'printing_times' : form.printing_times,           //'打印次数',
                        'express_no' : form.express_no,           //'快递单号',
                        'choose_no' : form.choose_no,           //'选单号',
                        'create_id' : form.create_id,           //'制单人id',
                        'create_name' : form.create_name,           //'制单人姓名'
                        'create_time' : String((new Date(form.create_time)).getTime() / 1000),           //'创建时间',
                        'tips' : form.tips,           //'入库备注',
                        'audit_name' : vm.audit_name001.name,
                        'audit_id' : vm.audit_name001.id,

                        'keep_name' : vm.keep_name001.name,
                        'keep_id' : vm.keep_name001.id,

                        'send_name' : vm.send_name001.name,
                        'send_id' : vm.send_name001.id
                    }
                    // 时间小数点判断  截取
                    for(var i = 0;i < baseMsg.length ; i++){
                        if(baseMsg.create_time == '.'){
                            baseMsg.create_time = baseMsg.create_time.substring(0,10) 
                        }
                    }
                    for(var i = 0;i < baseMsg.length;i++){
                        if(baseMsg[i] == ''){
                            if(baseMsg.printing_times != ''&&baseMsg.tips != ''){
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
                                    // 关闭弹框 刷新父页面
                                    layer.msg(res.msg)
                                    location.reload();
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