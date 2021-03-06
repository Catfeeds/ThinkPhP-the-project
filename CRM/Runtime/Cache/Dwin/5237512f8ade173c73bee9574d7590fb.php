<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出库单录入</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
<style>
    body{
        color:black!important;
    }
</style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content" id="app">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>出库单据提交</h5>
                    <div class="ibox-tools"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></div>
                </div>
                <div class="ibox-content">
                    <template v-if="productInfo">
                        <table class="table table-striped table-bordered table-hover dataTables-orderBasic">
                            <thead>
                            <tr>
                                <th>产品型号</th>
                                <th>总库存</th>
                                <th>剩余库存</th>
                                <th>销货待出库</th>
                                <th>出库中数量</th>
                                <th>库房</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{productInfo.product_name || ''}}</td>
                                <td>{{productInfo.stock_true_num || ''}}</td>
                                <td>{{productInfo.stock_number || ''}}</td>
                                <td>{{productInfo.stock_o_audit || ''}}</td>
                                <td>{{productInfo.out_processing || ''}}</td>
                                <td>{{productInfo.warehouse_number || ''}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </template>
                    <template v-else>

                        <table class="table table-striped table-bordered table-hover dataTables-orderBasic">
                            <thead>
                            <tr>
                                <th>订单编号</th>
                                <th>负责人</th>
                                <th>出库仓库</th>
                                <th>订单状态</th>
                                <th>财务审核日期</th>
                                <th>出库状态</th>
                                <th>是否分批发货</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{orderData.cpo_id || ''}}</td>
                                <td>{{orderData.staname || ''}}</td>
                                <td>{{orderData.ware_house || ''}}</td>
                                <td>{{orderData.check_status_name || ''}}</td>
                                <td>{{orderData.finance_audit_time || ''}}</td>
                                <td><span v-if="orderData.stock_out_status">{{orderData.stock_out_status | stockStatus}}</span></td>
                                <td><span v-if="orderData.is_batch_delivery">{{orderData.is_batch_delivery | batchDeliveryStatus}}</span></td>
                            </tr>
                            </tbody>
                        </table>
                        <table class="table table-striped table-bordered table-hover dataTables-orderBasic">
                            <thead>
                            <tr>
                                <th>产品名</th>
                                <th>分类</th>
                                <th>采购数量</th>
                                <th>剩余库存</th>
                                <th>待出库存</th>
                                <th>已出库数量</th>
                                <th>出库待审核数</th>
                                <th>出库状态</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="val in productData">
                                <td>{{val.product_name}}</td>
                                <td>{{val.product_type}}</td>
                                <td>{{val.product_num}}</td>
                                <td>{{val.stock_number}}</td>
                                <td>{{val.o_audit}}</td>
                                <td>{{val.stock_out_num}}</td>
                                <td>{{val.stock_out_uncheck_num}}</td>
                                <td><span v-if="val.pro_stock_status">{{val.pro_stock_status | stockOutStatus}}</span></td>
                            </tr>
                            </tbody>
                        </table>
                    </template>
                </div>

                <form class="form-horizontal m-t" id="edit_stock" method="post">
                <div class="col-md-12">
                        <div class="col-md-12">
                            <div class="ibox-title">
                                <h6>出库记录单据</h6>
                            </div>
                            <div class="ibox-content">
                                <table class="table table-bordered table-striped">
                                    <tr>
                                        <th>订单号</th>
                                        <th style="text-align: center">出库信息录入</th>
                                    </tr>
                                    <tr>
                                        <td><input type="text" class="form-control" :value="orderData.cpo_id" name="action_order_number" disabled></td>
                                        <td>
                                            <table class="table table-bordered table-striped">
                                                <tr>
                                                    <th>出库类型</th>
                                                    <th>出库物料</th>
                                                    <th>仓库</th>
                                                    <th>数量</th>
                                                    <th>快递单号</th>
                                                    <th>出库备注</th>
                                                    <th>审核人</th>
                                                </tr>
                                                <tr v-for="(item,index) in productData">
                                                    <td>
                                                        <select name="cateArr" id="category" class="form-control" v-model="item.selectedOutType" v-validate="'required'" @change="selectCate(item,index)">
                                                            <option value="" hidden>请选择</option>
                                                            <option :value="cate.cateArr" v-for="cate in cateData">{{cate.cate_name}}</option>
                                                        </select>
                                                    </td>
                                                    <td nowrap="true">
                                                        <span>{{item.product_name}}</span>
                                                        <input type="hidden" class="form-control" :value="item.product_name" name="product_id" disabled>
                                                    </td>
                                                    <td>
                                                        <select name="cateArr" id="repertory" class="form-control" v-model="item.selectedWarehouse" v-validate="'required'" @change="selectCate(item,index)">
                                                            <option value="" hidden>请选择仓库</option>
                                                            <option v-for="val in item.warehouse" :value="val.warehouseArr">{{val.warehouse_name}}</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input id="num" name="num" class="form-control" type="number" v-model="item.num" v-validate="'required'" @input="numValidate(index)" placeholder="请填写出库数量">
                                                    </td>
                                                    <td>
                                                        <input id="deliveryNumber" name="deliveryNumber" class="form-control" type="text" v-model="deliverNum" placeholder="没有单号请写无">
                                                    </td>
                                                    <td>
                                                        <input id="tips" name="tips" class="form-control" type="text" v-model="item.tips">
                                                    </td>
                                                    <td>
                                                        <select name="auditor" class="form-control" v-model="item.selectedAuditName" v-validate="'required'" @change="selectCate(item,index)">
                                                            <option value="" hidden>请选择审核人</option>
                                                            <option :value="audit.auditArr" v-for="audit in auditData">{{audit.name}}</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                </div>

                </form>
                <div class="col-md-12">
                    <button class="btn btn-success btn-outline" @click="submit" :disabled="loading">提交出库单</button>
                </div>
                    <!--<button class="btn btn-primary" @click="submit">提交所有出库记录</button>-->
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="/Public/html/js/vee-validate.js"></script>
<script src="/Public/html/js/bluebird.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/dwin/WdatePicker.js"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    /**
     * @todo : 验证输入的入库数量是否合法（库存比对、采购数量比对）
     *         提交的类别、仓库、审核人信息传递 id + "_" + name格式：如 1_马旭、1_销售出库  0001_成品库
     *         传递数据字段名称：
     *         product_id num tips proposerArr auditorArr warehouseArr cateArr action_order_number
     * */
    Vue.use(VeeValidate);
    var orderData = <?php echo (json_encode($orderData )); ?>;
    var productData = <?php echo (json_encode($productData )); ?>;
    var productInfo = <?php echo (json_encode($productInfo )); ?>;
    console.log(productData)
    var auditData = <?php echo (json_encode($auditor )); ?>;
    var cateData = <?php echo (json_encode($cate )); ?>;
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                deliverNum  : '',
                orderData   : orderData,
                productData : productData,
                auditData   : auditData,
                productInfo : productInfo,
                cateData    : cateData,
                loading     : false
            }
        },
        created: function(){
            if(this.cateData != null){
                for (var i = 0; i < this.cateData.length; i++){
                    this.cateData[i].cateArr = this.cateData[i].id + '_' + this.cateData[i].cate_name;
                }
                for (i = 0; i < this.productData.length; i++){
                    this.productData[i].selectedOutType = '12_销售出库';
                    this.productData[i].selectedAuditName = '243_成品库';
                    this.productData[i].selectedWarehouse = 'K004_成品库-A';
                    this.productData[i].cateArr = this.productData[i].selectedOutType;
                    this.productData[i].auditor = this.productData[i].selectedAuditName;
                    this.productData[i].warehouseArr = this.productData[i].selectedWarehouse;
                    this.productData[i].action_order_number = this.orderData.cpo_id;
                    for (var j = 0; j < this.productData[i].warehouse.length; j++){
                        this.productData[i].warehouse[j].warehouseArr = this.productData[i].warehouse[j].warehouse_number + '_' + this.productData[i].warehouse[j].warehouse_name;
                    }
                    if (this.productData[i].pro_stock_status == 2){
                        this.productData[i].num = 0
                    }
                }
                for (i = 0; i < this.auditData.length; i++){
                    this.auditData[i].auditArr = this.auditData[i].id + '_' + this.auditData[i].name
                }
            }
        },
        computed : {
        },
        methods : {
            selectCate : function (item,index) {
                item.auditor      = item.selectedAuditName;
                item.warehouseArr = item.selectedWarehouse;
                item.cateArr      = item.selectedOutType;
            },
            addDataList: function () {
                this.dataList.push({})
            },
            delDataList: function () {
                this.dataList.pop()
            },
            numValidate: function (index) {
                var product_num = +this.productData[index].product_num;
                var stock_number = +this.productData[index].stock_number;
                var stock_o_audit = +this.productData[index].o_audit;
                var stock_out_num = +this.productData[index].stock_out_num;
                var stock_out_uncheck_num = +this.productData[index].stock_out_uncheck_num;
                var currentNumber = +this.productData[index].num;
                var elseNum = product_num - (stock_out_num + stock_out_uncheck_num);
                if (elseNum < 0) {
                    elseNum = 0;
                }
                if (currentNumber > elseNum){
                    layer.msg('出库数量不得大于未出库数量')
                } else if (currentNumber > stock_number + stock_o_audit){
                    layer.msg('库存数量不足')
                } else if (currentNumber < 0){
                    layer.msg('出库数量只能大于等于0')
                }else {
                    return true
                }
                this.productData[index].num = null
            },
            submit: function () {
                this.loading = true
                var obj = this.productData;
                var vm = this
                this.productData.forEach(function (v,i) {
                    Vue.set(vm.productData[i], 'deliverNum', vm.deliverNum)
                })
                this.$validator.validateAll().then(function (res) {
                    if (res === true){
                         $.post("<?php echo U('addStockOut');?>",
                             {
                                 productData :obj
                             }, function (data) {
                             layer.msg(data['msg'], function () {
                                 if (data['status'] == 200) {
                                     layer.closeAll();
                                 }
                             });
                             vm.loading = false
                         })
                    } else {
                        layer.msg('表单中有未填项');
                        vm.loading = false
                        return false
                    }
                })
            }
        },
        filters : {
            stockStatus: function (status) {
                var arr = ['未处理', '出库中', '出库完成', '审核不通过'];
                return arr[status]
            },
            batchDeliveryStatus : function (status) {
                var arr = ['不分批','分批'];
                return arr[status];
            },
            stockOutStatus : function (status) {
                var arr = ['未出库','出库中','出库完毕'];
                return arr[status];
            }
        }
    });
</script>
</body>
</html>