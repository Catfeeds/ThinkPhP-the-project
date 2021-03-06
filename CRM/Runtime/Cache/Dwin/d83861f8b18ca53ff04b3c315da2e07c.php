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
        .head_thead {
            height: 35px;
            line-height: 35px;
            text-align: left;
            padding-left: 10px;
            font-size: 15px;
        }
        .el-autocomplete {
            width: 100%;
        }
        .el-button--primary {
            float: left;
        }
        .deal_cent th {
            text-align: center
        }
        .add_button_new {
            text-align: left
        }
        .borderE4393C{
            border: 1px solid #e4393c;
        }
        .el-form-item__content{
            text-align: left !important
        }
    </style>
</head>
<body>
    <div id="app" style="text-align: center">
        <h1>采购物料入库单下推</h1>
        <br>
        <el-row>
            <el-col :span="22" :offset="1">
            <el-form ref="form" :model="form" label-width="100px" size="medium" @submit.native.prevent v-loading="loading">
            <div class="head_thead" style="font-weight: bold;">一、入库单基本信息</div>
                <div class="jumbotron">
                    <table class="table table-striped table-hover table-bordered" border style="margin-bottom: 0">
                        <tbody>
                        <tr class="deal_cent">
                            <td><label>单据编号：</label><span>{{baseInfo.stock_in_id}}</span></td>
                            <td><label>入库类型：</label><span>{{baseInfo.cate_name}}</span></td>
                            <td><label>采购单编号：</label><span>{{baseInfo.purchase_order_id}}</span></td>
                            <td><label>入库批次：</label><span>{{baseInfo.batch}}</span></td>
                            <td><label>供应商：</label><span>{{baseInfo.supplier_name}}</span></td>
                        </tr>
                        <tr class="deal_cent">
                            <td><label>付款时间：</label><span>{{baseInfo.pay_time}}</span></td>
                            <td><label>采购模式：</label><span>{{baseInfo.purchase_mode}}</span></td>
                            <td><label>采购类型：</label><span>{{baseInfo.purchase_type}}</span></td>
                            <td><label>更新时间：</label><span>{{baseInfo.update_time}}</span></td>
                        </tr>
                        <tr>
                            <td colspan="5"><label>入库说明：</label><span>{{baseInfo.tips}}</span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <table class="table table-striped table-hover table-bordered" border style="margin-bottom: 0">
                        <div class="head_thead">二、采购入库待质检物料</div>
                        <tbody>
                            <tr class="deal_cent">   

                                <th style="width: 140px;">物料编号</th>      
                                <th style="width: 140px;">物料型号</th>      
                                <th style="width: 150px;">质检合格入库库房</th>
                                <th style="width: 150px;">不合格库房</th>
                                <th style="width: 70px">待检数量</th>
                                <th style="width: 150px;">质检合格数</th>
                            </tr>
                            <tr v-for="(item, index) in inspection">

                                <td>
                                    {{item.product_no}}
                                </td>
                                <td>
                                    {{item.product_name}}
                                </td>
                                <td>
                                    {{item.default_rep_name}}
                                </td>
                                <td>
                                    {{item.fail_rep_name}}
                                </td>
                                <td>
                                    {{item.num}}
                                </td>
                                <td>
                                    <el-input v-model="item.success_num"  placeholder="请输入合格数量"></el-input>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <!-- GO -->
                <br>
                <br>
                <el-button type="success" @click="onSubmit()">质检下推库房入库</el-button>
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
    var id = <?php echo ($id); ?>;
    var vm = new Vue({
        el: '#app',
        data : function(){
            return {
                loading:false,
                form:{},
                baseInfo : {},
                supplier_name:'',
                purchase_order_id:'',
                purchase_mode:'',
                purchase_type:'', 
                success_num:'',
                house:'',
                notHouse:'',
                zong_num:0,
                stockId:'',
                not_zong_num:0,
                inspection:{
                    // num:'',
                    // product_no:'',
                    // product_name:'',
                    // default_rep_id:'',
                    // fail_rep_id:'',
                    // success_num:0
                },
                centerDialogVisible:false
            }
        },
        created:function () {
           
        },
        mounted:function() {
            $.post('<?php echo U("/Dwin/Stock/addRecordWithPurchaseStockIn");?>' , {
                flag:'get',
                id:id
            } ,function (res) {
                vm.baseInfo = res.data.base;
                vm.stockId = res.data.base.id;
                vm.supplier_name = res.data.base.supplier_name;
                vm.purchase_order_id = res.data.base.purchase_order_id;
                vm.purchase_mode = res.data.base.purchase_mode;
                vm.purchase_type = res.data.base.purchase_type;
                vm.inspection = res.data.material;
                vm.house = vm.inspection[0].default_rep_id;
                vm.notHouse = vm.inspection[0].fail_rep_id;
                for(var i = 0;i < vm.inspection.length;i++){
                    vm.inspection[i]['success_num'] = vm.inspection[i].num
                }
            })
        },
        methods :{
            onSubmit : function () {
                var no = 0
                vm.zong_num = 0
                vm.not_zong_num = 0
                var prosperity = true
                for(var i = 0;i<vm.inspection.length;i++){
                    if(Number(vm.inspection[i].num) < Number(vm.inspection[i].success_num)){
                        layer.msg('物料的合格数量不能大于待检数量')
                        prosperity = false
                    }
                    if(prosperity){
                        vm.zong_num =  vm.zong_num +  Number(vm.inspection[i].success_num)   //合格
                        no = no + Number(vm.inspection[i].num)    //总数
                        vm.not_zong_num = vm.not_zong_num + (no - vm.zong_num)  //不合格
                        vm.inspection[i]['fail_num'] = String(Number(vm.inspection[i].num) - Number(vm.inspection[i].success_num))

                    }
                }
                if(prosperity){
                    this.centerDialogVisible = true
                }
                this.onlyButtonOnSubmit();
            },
            // 确认
            onlyButtonOnSubmit : function(){
                var data = {
                    'flag':'post',
                    'stockId':vm.stockId,
                    'material':vm.inspection
                };
                $.post('<?php echo U("/Dwin/Stock/addRecordWithPurchaseStockIn");?>' , data , function (res) {
                    if(res.status == 200){
                        centerDialogVisible = false
                        var index = parent.layer.getFrameIndex(window.name)
                        parent.layer.close(index)
                    }
                    layer.msg(res.msg)
                })
            }

        }
    })
</script>
</html>