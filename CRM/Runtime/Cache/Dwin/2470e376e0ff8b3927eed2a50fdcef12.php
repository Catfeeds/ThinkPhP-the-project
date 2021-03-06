<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.4/theme-chalk/index.css" rel="stylesheet">
    <link rel="stylesheet" href="/Public/Dwin/Customer/css/addOrderTest.css">
</head>
<body>
<div id="app">
    <div class="step-2" v-if="step == 2">
        <div class="form-inline">
            <input v-model="searchCusKeyWord" type="text" class="form-control" placeholder="输入客户名称,点击搜索">
            <button type="button" class="btn btn-primary" @click="searchCus">搜索</button>
        </div>
        <table class="table table-striped table-hover table-border">
            <tr>
                <th>id</th>
                <th>姓名</th>
                <th>操作</th>
            </tr>
            <tr v-for="item in cusSearchRes">
                <td>{{item.cus_id}}</td>
                <td>{{item.cus_name}}</td>
                <td>
                    <button class="btn btn-info" @click="chooseCus(item.cus_id)">选择</button>
                </td>
            </tr>
        </table>
    </div>

    <div class="step-3 form-inline" v-if="step == 3">
        <div class="cusInfo" v-if="orderType != 5">
            <h4>客户名称</h4>
            <div class="myRow">
                <div class="item">
                    <input class="form-control" type="text" v-model="orderData.cusName" readonly>
                </div>
            </div>
        </div>
        <div class="orderInfo">
            <h4>订单基本信息</h4>
            <div class="myRow">
                <div class="item">
                    <label>订单编号: </label>
                    <input type="text" v-validate="'required'" v-model="orderData.orderNumber" readonly class="form-control">
                </div>
                <div class="item">
                    <label>订单名称</label>
                    <input type="text" :class="{error: errors.has('orderName')}" data-vv-as="订单名称" v-validate="'required'" name="orderName" v-model="orderData.orderName" class="form-control">
                    <span>{{ errors.first('orderName') }}</span>
                </div>
                <div class="item">
                    <label>销货单类型</label>
                    <input type="text" class="form-control" readonly v-model="orderTypeText">
                </div>
                <div class="item">
                    <label>业绩类型</label>
                    <el-select v-validate="'required'" :class="{error: errors.has('staticType')}" name="staticType" data-vv-as="业绩类型" filterable v-model="orderData.staticType" placeholder="请选择">
                        <el-option
                                v-for="item in selectInfo.performanceType"
                                :key="item.type_id"
                                :label="item.performance_type_name"
                                :value="item.type_id">
                        </el-option>
                    </el-select>
                    <span>{{ errors.first('staticType') }}</span>
                </div>
                <div class="item">
                    <label>业务员</label>
                    <input v-validate="'required'" type="text" v-model="selectInfo.staffData.staffname" class="form-control" readonly>
                </div>
                <div class="item">
                    <label>业务员电话</label>
                    <input v-validate="'required'" data-vv-as="业务员电话" :class="{error: errors.has('staffphone')}" name="staffphone" type="text" v-model="selectInfo.staffData.staffphone" class="form-control">
                    <span>{{ errors.first('staffphone') }}</span>
                </div>
                <div class="item" v-if="orderType != 5">
                    <label>收件人选择</label>
                    <el-select filterable placeholder="请选择" v-model="contact.selected" @change="selectContact">
                        <el-option
                                v-for="(item, index) in contact"
                                :key="index"
                                :label="item.contact_name"
                                :value="index">
                        </el-option>
                    </el-select>
                </div>
            </div>
        </div>
        <div class="expressInfo">
            <h4>物流信息</h4>
            <div class="myRow">
                <div class="item">
                    <label>运输方式</label>
                    <el-select v-validate="'required'" name="logisticsType" data-vv-as="运输方式" :class="{error: errors.has('logisticsType')}" filterable v-model="orderData.logisticsType" placeholder="请选择">
                        <el-option
                                v-for="item in selectInfo.logisticsType"
                                :key="item.type_id"
                                :label="item.logistics_type_name"
                                :value="item.type_id">
                        </el-option>
                    </el-select>
                    <span>{{ errors.first('logisticsType') }}</span>
                </div>
                <div class="item">
                    <label>运费支付方式</label>
                    <el-select v-validate="'required'" name="freightPaymentMethod" data-vv-as="运费支付方式" :class="{error: errors.has('freightPaymentMethod')}" filterable v-model="orderData.freightPaymentMethod" placeholder="请选择">
                        <el-option
                                v-for="item in selectInfo.freightPayMethod"
                                :key="item.type_id"
                                :label="item.name"
                                :value="item.type_id">
                        </el-option>
                    </el-select>
                    <span>{{ errors.first('freightPaymentMethod') }}</span>
                </div>
                <div class="item">
                    <label>收货人</label>
                    <el-autocomplete
                            v-validate="'required'"
                            class="inline-input"
                            v-model="orderData.receiver"
                            :fetch-suggestions="autoCompleteContactName"
                            placeholder="请输入内容"
                            name="receiver"
                            data-vv-as="收货人"
                            :class="{error: errors.has('receiver')}"
                    ></el-autocomplete>
                    <span>{{ errors.first('receiver') }}</span>
                </div>
                <div class="item">
                    <label>收货人电话</label>
                    <el-autocomplete
                            v-validate="'required'"
                            class="inline-input"
                            v-model="orderData.orderPhone"
                            :fetch-suggestions="autoCompleteContactPhone"
                            placeholder="请输入内容"
                            name="orderPhone"
                            data-vv-as="收货人电话"
                            :class="{error: errors.has('orderPhone')}"
                    ></el-autocomplete>
                    <span>{{ errors.first('orderPhone') }}</span>
                </div>
                <div class="item">
                    <label>收货地址</label>
                    <el-autocomplete
                            v-validate="'required'"
                            class="inline-input"
                            v-model="orderData.orderAddress"
                            :fetch-suggestions="autoCompleteAddress"
                            placeholder="请输入内容"
                            name="orderAddress"
                            data-vv-as="收货地址"
                            :class="{error: errors.has('orderAddress')}"
                    ></el-autocomplete>
                    <span>{{ errors.first('orderAddress') }}</span>
                </div>
            </div>
            <div class="myRow radio">
                <div>
                    <label>是否分批发货</label>
                    <br>
                    <el-radio name="isBatchDelivery" :class="{error: errors.has('isBatchDelivery')}" data-vv-as="这个" v-validate="'required'" v-model="orderData.isBatchDelivery" label="1">是</el-radio>
                    <el-radio name="isBatchDelivery" :class="{error: errors.has('isBatchDelivery')}" v-model="orderData.isBatchDelivery" label="0">否</el-radio>
                    <span>{{ errors.first('isBatchDelivery') }}</span>
                </div>
                <div>
                    <label>是否需要下生产单</label>
                    <br>
                    <el-radio name="productionStatus" :class="{error: errors.has('productionStatus')}" data-vv-as="这个" v-validate="'required'" v-model="orderData.productionStatus" label="1">是</el-radio>
                    <el-radio name="productionStatus" :class="{error: errors.has('productionStatus')}" v-model="orderData.productionStatus" label="0">否</el-radio>
                    <span>{{ errors.first('productionStatus') }}</span>
                </div>
            </div>
            <div class="myRow">
                <div class="item">
                    <label >物流备注</label>
                    <textarea v-validate="'required'" name="logisticsTip" :class="{error: errors.has('logisticsTip')}" data-vv-as="这个" class="form-control" cols="40" v-model="orderData.logisticsTip"></textarea>
                    <span>{{ errors.first('logisticsTip') }}</span>
                </div>
            </div>
        </div>
        <div class="productInfo">
            <h4>产品信息</h4>
            <table class="table table-border table-hover table-striped">
                <tr>
                    <th>产品类别</th>
                    <th>产品名</th>
                    <th>数量</th>
                    <th>发货仓库</th>
                    <th>操作</th>
                </tr>
                <tr v-for="(product,index) in productData">
                    <td>
                        <select v-validate="'required'" class="form-control" v-model="product.productType" :name="'ptype' + index" :class="{error: errors.has('ptype' + index)}" data-vv-as="产品类别">
                            <option value="" hidden>请选择</option>
                            <option :value="item.type_id" v-for="item in selectInfo.prodType">{{item.prod_type_name}}</option>
                        </select>
                        <span>{{ errors.first('ptype' + index) }}</span>
                    </td>
                    <td>{{product.product_name}}</td>
                    <td>
                        <input v-validate="'required'" :class="{error: errors.has('productNum' + index)}" data-vv-as="产品数量" :name="'productNum' + index" type="number" class="form-control" v-model="product.productNum">
                        <span>{{ errors.first('productNum' + index) }}</span>
                    </td>
                    <td>{{product.warehouse_name}}</td>
                    <td>
                        <button class="btn btn-warning" @click="productData.splice(index, 1)">删除</button>
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        <el-popover
                                ref="add_product"
                                placement="right"
                                width="400"
                                trigger="click">
                            <div class="form-inline">
                                <input type="text" class="form-control" placeholder="请输入产品名" v-model="searchProduct.name" @input="searchingProduct">
                            </div>
                            <table class="table table-striped table-hover table-bordered">
                                <tr>
                                    <th>产品名</th>
                                    <th>库存数量</th>
                                    <th>仓库</th>
                                </tr>
                                <tr v-for="item in searchProductRes" @click="addProduct(item)">
                                    <td>{{item.product_name}}</td>
                                    <td>{{item.stock_number}}</td>
                                    <td>{{item.warehouse_name}}</td>
                                </tr>
                            </table>

                        </el-popover>

                        <el-button v-popover:add_product type="primary">新增产品</el-button>
                    </td>
                </tr>
            </table>
        </div>
        <div class="financeInfo">
            <h4>财务信息</h4>
            <div class="myRow">
                <div class="item">
                    <label>订单总金额</label>
                    <input v-validate="'required'" type="text" class="form-control" readonly v-model="totalPrice">
                </div>
                <div class="item">
                    <label>结算方式</label>
                    <el-select v-validate="'required'" filterable v-model="orderData.settlementMethod" placeholder="请选择" name="settlementMethod" :class="{error: errors.has('settlementMethod')}" data-vv-as="结算方式">
                        <el-option
                                v-for="item in selectInfo.settleType"
                                :key="item.seid"
                                :label="item.sename"
                                :value="item.seid">
                        </el-option>
                    </el-select>
                    <span>{{ errors.first('settlementMethod') }}</span>
                </div>
                <div class="item">
                    <label>订单时间</label>
                    <el-date-picker
                            v-validate="'required'"
                            v-model="orderData.orderTime"
                            type="datetime"
                            placeholder="选择订单时间"
                            readonly
                    >
                    </el-date-picker>
                </div>
                <div class="item">
                    <label>发票方式</label>
                    <el-select v-validate="'required'" filterable v-model="orderData.invoiceSituation" placeholder="请选择" name="invoiceSituation" :class="{error: errors.has('invoiceSituation')}" data-vv-as="发票方式">
                        <el-option
                                v-for="item in selectInfo.invoiceSituation"
                                :key="item.type_id"
                                :label="item.name"
                                :value="item.type_id">
                        </el-option>
                    </el-select>
                    <span>{{ errors.first('invoiceSituation') }}</span>

                </div>
                <div class="item">
                    <label>发票类型</label>
                    <el-select v-validate="'required'" filterable v-model="orderData.invoice" placeholder="请选择" name="invoice" :class="{error: errors.has('invoice')}" data-vv-as="发票类型">
                        <el-option
                                v-for="item in selectInfo.invoiceT"
                                :key="item.type_id"
                                :label="item.name"
                                :value="item.type_id">
                        </el-option>
                    </el-select>
                    <span>{{ errors.first('invoice') }}</span>
                </div>
            </div>
            <div class="myRow">
                <div class="item">
                    <label>发票收件人</label>
                    <el-autocomplete
                            v-validate="'required'"
                            class="inline-input"
                            v-model="orderData.invoiceName"
                            :fetch-suggestions="autoCompleteContactName"
                            placeholder="请输入内容"
                            name="invoiceName" :class="{error: errors.has('invoiceName')}" data-vv-as="发票收件人"
                    ></el-autocomplete>
                    <span>{{ errors.first('invoiceName') }}</span>
                </div>
                <div class="item">
                    <label>发票收件人电话</label>
                    <el-autocomplete
                            v-validate="'required'"
                            class="inline-input"
                            v-model="orderData.invoicePhone"
                            :fetch-suggestions="autoCompleteContactPhone"
                            placeholder="请输入内容"
                            name="invoicePhone" :class="{error: errors.has('invoicePhone')}" data-vv-as="发票收件人电话"
                    ></el-autocomplete>
                    <span>{{ errors.first('invoicePhone') }}</span>
                </div>
                <div class="item">
                    <label>发票接收地址</label>
                    <el-autocomplete
                            v-validate="'required'"
                            class="inline-input"
                            v-model="orderData.invoiceAddress"
                            :fetch-suggestions="autoCompleteAddress"
                            placeholder="请输入内容"
                            name="invoiceAddress" :class="{error: errors.has('invoiceAddress')}" data-vv-as="发票接收地址"
                    ></el-autocomplete>
                    <span>{{ errors.first('invoiceAddress') }}</span>
                </div>
            </div>
            <div class="myRow">
                <div class="item">
                    <label>财务备注</label>
                    <textarea class="form-control" cols="40" v-model="orderData.financeTip" ></textarea>
                </div>
            </div>
        </div>
        <div class="submit">
            <button class="btn btn-primary" @click="submitOrder">添加订单</button>
            <button class="btn btn-info" @click="saveOrder">保存订单</button>
        </div>
    </div>
</div>
<script src="/Public/Admin/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="/Public/html/js/bluebird.js"></script>
<script src="/Public/html/js/vee-validate.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vee-validate/2.0.6/locale/zh_CN.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.4/index.js"></script>
<script>
    var selectInfo = <?php echo (json_encode($orderInfo )); ?>;
    var data = <?php echo (json_encode($data )); ?>;
    VeeValidate.Validator.localize('zh_CN', __vee_validate_locale__zh_CN);
    Vue.use(VeeValidate);
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                step: 2,
                contact: null,
                orderType: '4',
                cusAddress: [],
                selectInfo: selectInfo,
                searchCusKeyWord: '',
                cusSearchRes: [],
                productData: [],
                searchProduct: {
                    name: ''
                },
                searchProductRes: [],
                orderData: {
                    warehouseManagerIds: [],
                    logisticsStaffIds: [],
                    orderTime: Date.parse(new Date),
                    orderType: 4,
                    order_type: 4
                },
                customerInfo: null
            }
        },
        computed: {
            orderTypeText: function () {
                return '免费样品'
            },
            totalPrice: function () {
                return 0
            }
        },
        methods: {
            searchCus: function (){
                $.post('addOrder', {
                    customerkeyword: vm.searchCusKeyWord,
                    flag: 1
                }, function (res) {
                    if (res.status == 200){
                        if (res.data.length == 0){
                            layer.msg('未找到客户,请检查客户名称')
                        }
                        vm.cusSearchRes = res.data
                    }else {
                        layer.msg(res.msg)
                    }
                })
            },
            chooseCus: function (id) {
                $.post('addOrder', {
                    cusId: id,
                    flag: 2
                }, function (res) {
                    vm.contact = res.contact;
                    vm.customerInfo = res.customerInfo;
                    vm.cusAddress = res.cusAddress;
                    vm.orderData.orderId = res.orderIdInfo.order_id;
                    vm.orderData.orderNumber = 'CPO' + res.orderIdInfo.order_id;
                    vm.orderData.cusId = res.customerInfo.cus_id;
                    vm.orderData.cusName = res.customerInfo.cus_name;
                    vm.selectInfo = res.orderInfo;
                    vm.step = 3;
                })
            },
            addProduct: function (item) {
                function Item(product){
                    this.product_id = product.product_id;
                    this.productId = product.product_id;
                    this.productName  = product.product_name;
                    this.product_name  = product.product_name;
                    this.warehouse_name  = product.warehouse_name;
                    this.warehouseName  = product.warehouse_name;
                    this.warehouse_number  = product.warehouse_number;
                    this.warehouseNumber  = product.warehouse_number;
                    this.productType = '';
                    this.productTotalPrice = 0;
                }
                var obj = new Item(item)
                this.productData.push(obj)
            },
            searchingProduct: function () {
                $.post('addOrder', {
                    flag: 3,
                    productName: vm.searchProduct.name
                }, function (res) {
                    vm.searchProductRes = res
                })
            },
            selectContact: function (index) {
                var contact = this.contact[index];
                this.orderData.receiver = contact.contact_name;
                this.orderData.orderPhone  = contact.contact_phone;
            },
            autoCompleteAddress: function(queryString, cb) {
                if (this.orderType == 5){
                    return cb([])

                }
                var restaurants = [];
                for (var i = 0; i < this.cusAddress.length; i++){
                    restaurants[i] = {};
                    restaurants[i].value = this.cusAddress[i]
                }
                cb(restaurants);
            },
            autoCompleteContactName: function (queryString, cb) {
                if (this.orderType == 5){
                    return cb([])
                }
                var restaurants = [];
                for (var i = 0; i < this.contact.length; i++){
                    restaurants[i] = {};
                    restaurants[i].value = this.contact[i].contact_name
                }
                cb(restaurants);
            },
            autoCompleteContactPhone: function (queryString, cb) {
                if (this.orderType == 5){
                    return cb([])
                }
                var restaurants = [];
                for (var i = 0; i < this.contact.length; i++){
                    restaurants[i] = {};
                    restaurants[i].value = this.contact[i].contact_phone
                }
                cb(restaurants);
            },

            submitOrder: function () {
                if (vm.productData.length == 0){
                    layer.msg('尚未选择产品');
                    return false
                }
                layer.confirm('确认提交财务审核?', {icon: 3, title:'提示'}, function(index){
                    vm.$validator.validateAll().then(function (res) {
                        vm.orderData.staffName = vm.selectInfo.staffData.staffname;
                        vm.orderData.staffPhone = vm.selectInfo.staffData.staffphone;
                        vm.orderData.logisticsStaffIds = vm.orderData.logisticsStaffIds.join();
                        vm.orderData.warehouseManagerIds = vm.orderData.warehouseManagerIds.join();
                        if (res) {
                            $.post('addOrder', {
                                flag: 4,
                                orderData: vm.orderData,
                                productData: vm.productData
                            },function (res) {
                                if (res.status == 200){
                                    layer.msg('提交成功', function () {
                                        var index = parent.layer.getFrameIndex(window.name);
                                        parent.layer.close(index);
                                    });
                                } else {
                                    layer.msg(res.msg)
                                }
                            })
                        }else {
                            layer.msg('有空的必填项')
                        }
                    });
                    layer.close(index);
                });
            },
            saveOrder: function () {
                if (vm.productData.length == 0){
                    layer.msg('尚未选择产品');
                    return false
                }
                layer.confirm('保存订单,暂不提交财务审核?', {icon: 3, title:'提示'}, function(index){
                    vm.$validator.validateAll().then(function (res) {
                        vm.orderData.staffName = vm.selectInfo.staffData.staffname;
                        vm.orderData.staffPhone = vm.selectInfo.staffData.staffphone;
                        vm.orderData.logisticsStaffIds = vm.orderData.logisticsStaffIds.join();
                        vm.orderData.warehouseManagerIds = vm.orderData.warehouseManagerIds.join();
                        vm.orderData.saveFlag = true;
                        if (res) {
                            $.post('addOrder', {
                                flag: 4,
                                orderData: vm.orderData,
                                productData: vm.productData
                            },function (res) {
                                if (res.status == 200){
                                    layer.msg('提交成功', function () {
                                        var index = parent.layer.getFrameIndex(window.name);
                                        parent.layer.close(index);
                                    });
                                } else {
                                    layer.msg(res.msg)
                                }
                            })
                        }else {
                            layer.msg('有空的必填项')
                        }
                    });
                    layer.close(index);
                });
            }
        }
    })
</script>
</body>
</html>