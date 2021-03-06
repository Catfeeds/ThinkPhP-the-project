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
        .selected{
            background: #d0d27e!important;
        }
        #staff th,td{
            white-space: nowrap!important;
        }
        .el-table thead{
            color:black!important;
        }

        .el-table td, .el-table th{
            padding-top: 2px!important;
            padding-bottom: 2px!important;
        }
        .el-pagination__jump{
            color:black!important;
        }
        /*.table-responsive{*/
            /*height: 400px;*/
            /*overflow: auto;*/
        /*}*/
        .dataTables_scroll{
            overflow: auto !important;
        }
        .dataTables_scrollHead{
            overflow: initial !important;
        }
        .dataTables_scrollBody{
            overflow:initial !important;
        }
        .table2000{
            width: 2000px;
        }
        #staff_wrapper{
            overflow: hidden;
        }
        .dataTables_scrollBody thead{
            visibility: hidden;
        }
        div.dataTables_scrollBody table{
            margin-top: -25px!important;
            margin-left: 1px;
        }
        .tab-pane{
            overflow: auto;
        }
        .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
            padding:3px!important;
        }
        .el-dialog__body{
            text-align: center
        }
        /* 上传 */
        .uploadResume .el-upload .el-upload__input{
           display: none !important;
       }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="ibox float-e-margins">
        <div class="ibox-content">
                <div class="title">
                        <h4>采购物料订单审核不合格列表</h4>
                        <div>
                            <button class="btn btn-xs btn-outline btn-success refresh">刷 新</button>
                            <!-- <button class="btn btn-xs btn-outline btn-success audit_staff"><span class="glyphicon glyphicon-adjust"></span>物料订单审核</button> -->
                            <button class="btn btn-xs btn-outline btn-success edit_staff"><span class="glyphicon glyphicon-edit"></span>编 辑</button>
                            <button class="btn btn-xs btn-outline btn-success details_staff"><span class="glyphicon glyphicon-align-justify"></span>详 情</button>
                            <button class="btn btn-xs btn-outline btn-success delete_staff"><span class="glyphicon glyphicon-remove"></span>删 除</button>
                            <!-- <button class="btn btn-xs btn-outline btn-success indent_staff"><span class="glyphicon glyphicon-log-out"></span>物料订单下推入库</button> -->
                        </div>
                    </div>
            <div class="table-responsive">
                <table id="staff" class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th>订单编号</th>
                            <th>供方名称</th>
                            <th>订单时间</th>
                            <th>总金额</th>
                            <th>收货人</th>
                            <th>交货地点</th>
                            <th>收货电话</th>
                            <th>结算方式</th>
                            <th>供方地址</th>
                            <th>供方法定代表</th>
                            <th>供方代表电话</th>
                            <th>供方代表传真</th>
                            <th>需方地址</th>
                            <th>需方法定代表</th>
                            <th>需方电话</th>
                            <th>需方传真</th>
                            <th>采购模式</th>
                            <th>采购方式</th>
                            <th>审核状态</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="ibox-content" id="app">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#contact" aria-controls="contact" role="tab" data-toggle="tab">物料订单信息</a></li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="contact">
                        <table class="table table-striped table-hover table-border">
                            <tr>
                                <th>序号</th>
                                <th>物料名称</th>
                                <th>物料型号</th>
                                <th>购买数量</th>
                                <th>已入库数量</th>
                                <th>购买单价</th>
                                <th>金额</th>
                            </tr> 
                            <tr v-for="item in contact">
                                <td>{{item.sort_id  || ''}}</td>
                                <td>{{item.product_number  || ''}}</td>
                                <td>{{item.product_name  || ''}}</td>
                                <td>{{item.number  || ''}}</td>
                                <td>{{item.stock_in_number  || ''}}</td>
                                <td>{{item.single_price  || ''}}</td>
                                <td>{{item.total_price  || ''}}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <!-- 审核 弹框 -->
                <el-dialog title="采购订单审核：" class="selsctDialog" :visible.sync="dialogVisible" width="30%">
                   <el-button type="primary" @click="eleClick_that(2)">合 格</el-button>
                   <el-button type="primary" @click="eleClick_that(1)">不合格</el-button>
               </el-dialog>
                <!--删除 弹框 -->
                <el-dialog title="采购订单审核：" class="selsctDialog" :visible.sync="dialogVisible_delete" width="30%">
                   <el-button type="primary" @click="eleClick_that(2)">合 格</el-button>
                   <el-button type="primary" @click="eleClick_that(1)">不合格</el-button>
               </el-dialog>
            </div>
        </div>
    </div>
</div>
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
    var auditMsg = <?php echo (json_encode($auditMsg)); ?>;
    var table = $('#staff'). DataTable({
        ajax: {
            type: 'post',
            data: {
                flag: 1
            },
        },
        "scrollY": 440,
        "scrollX": false,
        "scrollCollapse": true,
        "destroy"      : true,
        "paging"       : true,
        "autoWidth"	   : false,
        "pageLength": 25,
        serverSide: true,
        // order:[[21, 'desc']],
        columns: [
            {searchable: true, data: 'purchase_order_id'},
            {searchable: true, data: 'supplier_name'},
            {searchable: true, data: 'order_time'},
            {searchable: true, data: 'total_amount'},
            {searchable: true, data: 'receiver'},
            {searchable: true, data: 'trading_location'},
            {searchable: false, data: 'receiving_phone'},
            {searchable: false, data: 'billing_method'},
            {searchable: false, data: 'supply_address'},
            {searchable: true, data: 'supplier_representative'},
            {searchable: true, data: 'supplier_phone'},
            {searchable: true, data: 'supplier_fax'},
            {searchable: true, data: 'demand_address'},
            {searchable: true, data: 'purchaser_representative'},
            {searchable: true, data: 'purchaser_phone'},
            {searchable: true, data: 'purchaser_fax'},
            {searchable: true, data: 'purchase_mode'},
            {searchable: true, data: 'purchase_type'},
            {searchable: true, data: 'audit_status', render: function (data){return auditMsg[+data]}}
        ],
        oLanguage: {
            "oAria": {
                "sSortAscending": " - click/return to sort ascending",
                "sSortDescending": " - click/return to sort descending"
            },
            "LengthMenu": "显示 _MENU_ 记录",
            "ZeroRecords": "对不起，查询不到任何相关数据",
            "EmptyTable": "未有相关数据",
            "LoadingRecords": "正在加载数据-请等待...",
            "Info": "当前显示 _START_ 到 _END_ 条，共 _TOTAL_ 条记录。",
            "InfoEmpty": "当前显示0到0条，共0条记录",
            "InfoFiltered": "（数据库中共为 _MAX_ 条记录）",
            "Processing": "<img src='../resources/user_share/row_details/select2-spinner.gif'/> 正在加载数据...",
            "Search": "搜索：",
            "Url": "",
            "Paginate": {
                "sFirst": "首页",
                "sPrevious": " 上一页 ",
                "sNext": " 下一页 ",
                "sLast": " 尾页 "
            }
        }
    })
    var orderId
    var order_id
    var contractId
    var current_status
    // var id
    var orderData
    $('tbody').on('click', 'tr', function () {
        orderData = table.row(this).data();
        if(orderData != undefined){
            contractId = orderData.contract_pid
            orderId = orderData.id;   // 订单主键
            order_id = orderData.purchase_order_id      //订单编号
            current_status = orderData.audit_status
            $('tr').removeClass('selected')
            $(this).addClass('selected')
            $.post('/Dwin/Purchase/getOrderProduct', {id: orderId}, function (res) {
                vm.contact = res.data
            })
        }
        
    })
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                contact:[],
                dialogVisible:false,
                dialogVisible_delete:false,
                orderId:orderId,
                upLoadData:{
                    id:''
                }
            }
        },
        methods: {
            // 审核确定
            eleClick_that(vul){
                var vm = this
                var data = {
                    'orderId': orderId,
                    'status':vul
                }
                $.post('<?php echo U("Dwin/Purchase/auditOrder");?>', data , function (res) {
                    if(res.status == 200){
                        table.ajax.reload()
                        vm.dialogVisible = false
                    }
                    layer.msg(res.msg)
                })
            }
        }
    })
    // 下推入库
    $('.indent_staff').on('click', function () {
        if (order_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            var index = layer.open({
                type: 2,
                title: '采购订单入库',
                content: '/Dwin/Stock/addStockInWithPurchase?orderId=' + orderId,
                area: ['90%', '90%'],
                shadeClose:true,
                end: function () {
                    table.ajax.reload()
                }
            })
        }
    })
    // 点击 订单详情
    $('.details_staff').on('click', function () {
        if (order_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            var index = layer.open({
                type: 2,
                title: '湖南迪文科技有限公司采购订单详情',
                content: '/Dwin/Purchase/getOrderMsg?id=' + orderId,
                area: ['90%', '90%'],
                shadeClose:true,
                end: function () {
                    table.ajax.reload()
                }
            })
        }
    })
    // 点击 编辑
    $('.edit_staff').on('click', function () {
        if (order_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            var index = layer.open({
                type: 2,
                title: '湖南迪文科技有限公司采购订单编辑',
                content: '/Dwin/Purchase/editOrder?orderId=' + orderId,
                area: ['90%', '90%'],
                shadeClose:true,
                end: function () {
                    table.ajax.reload()
                }
            })
        }
    })
    // 刷新
    $('.refresh').on('click', function () {
        table.order([[5, 'desc']])
        table.ajax.reload()
    })
    // 审核
    $('.audit_staff').on('click', function () {
        if (order_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            if(current_status == '0'){
                vm.dialogVisible = true
            }else if(current_status == '1'){
                layer.msg('该项审核不通过')
            }else if(current_status == '2'){
                layer.msg('该项审核已通过')
            }
        }
    })
    // 删除订单
    $('.delete_staff').on('click', function () {
        if (order_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            if(current_status == '2'){
                layer.msg('该订单审核已通过,不能删除订单！')
            }else{
                var data = {
                    'orderId' : orderId,
                }
                layer.confirm('确认删除?', function (aaa) {
                    $.post('<?php echo U("/Dwin/Purchase/deleteOrder");?>', data, function (res) {
                        if (res.status == 200) {
                            table.ajax.reload()
                        }
                        layer.msg(res.msg)
                    })
                })
            }
        }
    })
</script>
</body>
</html>