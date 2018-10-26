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
        <h1>生产料单bom确认</h1>
        <br><br><br>
        <el-row>
            <el-col :span="22" :offset="1">
                    <el-form ref="form" :model="form" label-width="120px" size="medium" @submit.native.prevent v-loading="loading">
                            <el-row>
                                <el-col :span="8" :offset="1">
                                    <el-form-item label="计划单号：" repuire>
                                            <el-input v-model="form.production_code" style="width: 100%;" readonly></el-input>
                                    </el-form-item> 
                                </el-col>
                            </el-row>
                            
                            <el-row :gutter="20">
                                <el-col :span="24">
                                        <div>
                                            <br>
                                            <p class="title_name_product"><b>bom料单确认 </b></p>
                                            <el-table ref="multipleTable" :data="product" tooltip-effect="dark" style="width: 100%" border>
                                                <el-table-column label="物料编号" align="center" header-align="center" width="150px">
                                                    <template slot-scope="scope"  class="productClass">
                                                        <el-select id="opper"

                                                                   v-if="scope.row.replace_data[1] || false"
                                                                   v-model="scope.row.product_no"
                                                                   value-key="id"
                                                                   @clear="delete_selest(scope.$index,scope.row)"
                                                                   @visible-change="change_product($event,scope.$index,scope.row)"
                                                                   placeholder="请选择">
                                                            <el-option disabled
                                                                :value="1"
                                                                style="background-color: #92cc6d"
                                                                >
                                                                <span style="width: 100px;float: left; color: blue;text-align: left" >物料型号</span>
                                                                <span style="width: 200px;float: left;color: blue;text-align: left">物料名称</span>
                                                                <span style="width: 300px;float: left;color: blue;text-align: left">物料编码</span>
                                                            </el-option>
                                                            <el-option
                                                            v-for="item in options_replace_product"
                                                            :key="item.product_id"
                                                            :label="item.product_no"
                                                            width="200px"
                                                            :value="item">
                                                            <span style="float: left;width:100px;text-align: left">{{ item.product_no }}</span>
                                                            <span style="float: left;width: 200px;text-align: left">{{ item.product_name }}</span>
                                                            <span style="float: left;width: 300px;text-align: left">{{ item.product_number}}</span>
                                                            <span v-if="false">{{ item.warehouse_id}}</span>
                                                        </el-option>
                                                    </el-select>
                                                    <div v-else>{{scope.row.replace_data[0].product_no}}</div>
                                                    </template>
                                                </el-table-column>
                                                <el-table-column label="物料名称" prop="product_name" align="center" header-align="center"> </el-table-column>
                                                <el-table-column label="物料型号" prop="product_number" align="center" header-align="center"> </el-table-column>
                                                <el-table-column label="数量" prop="one_num" align="center" header-align="center"> </el-table-column>
                                                <el-table-column label="出货仓库" width="150px" align="center" header-align="center" type="text">
                                                    <template slot-scope="scope">
                                                        <el-select  v-model="scope.row.rep_pid" value-key="id" @change ="select_rep_pid(scope.$index,scope.row)" filterable placeholder="请选择出货仓库">
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
                                                <el-table-column label="库存" prop="stock_number" align="center" header-align="center"> </el-table-column>
                                            </el-table>
                                        </div>
                            <br>
                           <br>
                            <el-button type="success" @click="onSubmit(form)">提 交</el-button>
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
    var productionOrderData = <?php echo (json_encode($productionOrderData)); ?>;  //领料订单源单信息
    var materialData = <?php echo (json_encode($materialData)); ?>;  //申请单物料数据
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                loading:true,
                connt_savePRO:{},
                GeTATry:false,
                harness:0,   //套
                form :productionOrderData,
                options_rep_pid : [],
                options_replace_product:[],
                product:[]
            }
        },
        created : function () {
            this.loading = false
            // 物料赋值
            this.options_rep_pid = repMap
            for(let i in materialData){
                var obJ = {
                    product_id    :materialData[i].product_id,
                    product_no    :materialData[i].product_no,
                    product_name  :materialData[i].product_name,
                    product_number:materialData[i].product_number,
                    stock_number  :materialData[i].stock_number,
                    total_num     :materialData[i].total_num,
                    replace_data  :materialData[i].replace_data,
                    one_num       :materialData[i].one_num,
                    num           :materialData[i].total_num,
                    rep_pid       :materialData[i].rep_pid,
                }
                this.product.push(obJ)
            }

        },
        methods :{
            // 清空 替代物料
            delete_selest:function(index,row){
                this.product[index].product_id = ''
                this.product[index].product_name = ''
                this.product[index].product_number = ''
                this.product[index].product_no = ''
                this.product[index].rep_pid = ''
                this.select_rep_pid(index,row)
            },
            // 选中 selsct 时
            change_product:function(callback,index,row){
                this.options_replace_product = []
                if(row.replace_data.length == 0){

                }else{
                    // 下拉 显示
                    if(callback){
                        this.connt_savePRO = {
                            id : this.product[index].product_id,
                            name : this.product[index].product_name,
                            number : this.product[index].product_number,
                            no : this.product[index].product_no,
                            rep: this.product[index].rep_pid
                        }
                        this.product[index].product_id = ''
                        this.product[index].product_name = ''
                        this.product[index].product_number = ''
                        this.product[index].product_no = ''
                        this.product[index].rep_pid = ""
                        this.options_replace_product =  row.replace_data
                    }
                    // 下拉 隐藏
                    if(!callback){
                        if(!this.product[index].product_no){
                            this.product[index].product_id = this.connt_savePRO.id
                            this.product[index].product_name = this.connt_savePRO.name
                            this.product[index].product_number = this.connt_savePRO.number
                            this.product[index].product_no = this.connt_savePRO.no
                            this.product[index].rep_pid = this.connt_savePRO.rep
                        }else{
                            console.log(row.product_no)
                            this.product[index].product_id = row.product_no.product_id
                            this.product[index].product_name = row.product_no.product_name
                            this.product[index].product_number = row.product_no.product_number
                            this.product[index].rep_pid = row.product_no.warehouse_id
                            //   所以赋值都要放在 下面一行（product_no）代码的上方   !!!!!!
                            this.product[index].product_no = row.product_no.product_no
                            this.options_replace_product.length = 0
                            this.select_rep_pid(index,row)
                        }
                    }
                }
            },
            // 仓库下拉
            select_rep_pid:function (index,row){
                var materialId = ''
                var repID = '' 
                if(row.product_id){
                    materialId = row.product_id
                }else{
                    materialId = row.product_id
                }
                repID = row.rep_pid
                for(var k = 0 ;k < repMap.length;k++){
                    if(row.rep_pid === repMap[k].repertory_name){
                        repID = repMap[k].rep_id
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

            // 提交
            onSubmit :function(form){

                    var num_json = true
                    var new_material  = []
                    for(var i = 0; i<this.product.length ;i++){
                        if(this.product[i].total_num == '' || this.product[i].total_num == '0' || !this.product[i].rep_pid){
                            num_json = false
                        }else{
                            new_material.push({
                                'source_id':this.form.id,
                                'product_id':this.product[i].product_id,
                                'product_no':this.product[i].product_no,
                                'product_name':this.product[i].product_no,
                                'product_number':this.product[i].product_no,
                                'num':this.product[i].total_num,
                                'rep_pid':this.product[i].rep_pid,
                            })
                        }
                    }
                    if(num_json){
                        if(new_material.length == 0){
                            layer.msg('物料bom数据有误2')
                        }else{
                            var data = {
                                'order_id':form.id,
                                'product_data' : new_material
                            }
                            var clickTime = 0;
                            var layerIndex = layer.confirm(
                                '确定要提交吗？', function() {
                                    $.post('<?php echo U("/Dwin/Production/productionOrderCreateBom");?>', data , function (res) {
                                        clickTime ++;
                                        if (1 === clickTime) {
                                            if(res.status == 200){
                                                // 关闭弹框 刷新父页面
                                                layer.msg(res.msg)
                                                var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                                parent.layer.close(index)
                                            }else{
                                                layer.msg(res.msg)
                                            }
                                        } else {
                                            layer.msg("禁止重复提交");
                                        }

                                    })
                            })
                        }
                        
                    } else {
                        layer.msg('物料bom数据有误')
                    }
                    
                    
                    
                
            },
            // 获取三个产品值
            // searchingProduct: function() {
            //     $.post('<?php echo U("Dwin/Purchase/getProductMsg");?>', {'condition':this.searchProduct.name}, function(res) {
            //         vm.searchProductRes = res.data
            //     })
            // },
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