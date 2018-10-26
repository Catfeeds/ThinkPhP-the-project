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
        /* 表头也滑动 */
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
                        <h4>供应商物料合同审核成功列表</h4>
                        <div>
                            <button class="btn btn-xs btn-outline btn-success refresh">刷 新</button>
                            <!-- <button class="btn btn-xs btn-outline btn-success audit_staff"><span class="glyphicon glyphicon-adjust"></span>物料合同审核</button> -->
                            <!-- <button class="btn btn-xs btn-outline btn-success edit_staff"><span class="glyphicon glyphicon-edit"></span>物料合同编辑</button> -->
                            <button class="btn btn-xs btn-outline btn-success details_staff"><span class="glyphicon glyphicon-align-justify"></span>详情</button>
                            <!--<button class="btn btn-xs btn-outline btn-success affix_staff"><span class="glyphicon glyphicon-cloud-upload"></span>上传物料合同附件</button>-->
                            <button class="btn btn-xs btn-outline btn-success indent_staff"><span class="glyphicon glyphicon-log-out"></span>下推采购订单</button>
                            <button class="btn btn-xs btn-outline btn-success preview_staff"><span class="glyphicon glyphicon-log-out"></span>预览合同</button>
                            <button class="btn btn-xs btn-outline btn-success print_staff"><span class="glyphicon glyphicon-log-out"></span>打印合同</button>
                        </div>
                    </div>
            <div class="table-responsive">
                <table id="staff" class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th>合同编号</th>
                        <th>供方名称</th>
                        <!--<th>供方地址</th>-->
                        <th>签订时间</th>
                        <!--<th>签订地点</th>-->
                        <th>总金额</th>
                        <!--<th>收货人</th>-->
                        <!--<th>交货地点</th>-->
                        <!--<th>收货电话</th>-->
                        <th>结算方式</th>
                        <th>供方法定代表</th>
                        <th>供方代表电话</th>
                        <!--<th>供方代表传真</th>-->
                        <th>是否回传合同附件</th>
                        <th>订单状态</th>
                        <th>审核状态</th>
                        <th>合同附件</th>
                    </tr>
                    </thead>
                    <tbody style="text-align: center">
                    </tbody>
                </table>
            </div>
            <div class="ibox-content" id="app">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#contact" aria-controls="contact" role="tab" data-toggle="tab">物料信息</a></li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="contact">
                        <table class="table table-striped table-hover table-border">
                            <tr>
                                <th>产品名</th>
                                <th>型号</th>
                                <th>单位</th>
                                <th>数量</th>
                                <th>单价</th>
                                <th>金额</th>
                                <th>交货日期</th>
                            </tr> 
                            <tr v-for="item in contact">
                                <td>{{item.product_number  || ''}}</td>
                                <td>{{item.product_name  || ''}}</td>
                                <td>{{item.unit  || ''}}</td>
                                <td>{{item.purchase_number  || ''}}</td>
                                <td>{{item.purchase_single_price  || ''}}</td>
                                <td>{{item.issuing_authority  || ''}}</td>
                                <td>{{item.deliver_time  || ''}}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <!-- 审核 弹框 -->
                <el-dialog title="采购合同审核：" class="selsctDialog" :visible.sync="dialogVisible" width="30%">
                   <el-button type="primary" @click="eleClick_that(2)">合 格</el-button>
                   <el-button type="primary" @click="eleClick_that(1)">不合格</el-button>
               </el-dialog>
                <!-- 上传附件 弹框 -->
                <el-dialog title="上传合同附件：" class="selsctDialog" :visible.sync="dialogAffix" width="30%">
                    <el-upload
                    class="uploadResume"
                    action="<?php echo U('/dwin/purchase/uploadContractFile');?>"
                    :data="upLoadData"
                    :on-success="papersUploadSuccess"
                    :on-error="uploadError"
                    :auto-upload="true"
                    >
                    <el-button size="small" type="primary" @click="click_upload">上传合同附件</el-button>
                </el-upload>
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
            {searchable: true, data: 'contract_id'},
            {searchable: true, data: 'supplier_name'},
//            {searchable: true, data: 'supply_address'},
            {searchable: true, data: 'signing_time',render: function(data){return formatDateTime(data)}},
//            {searchable: true, data: 'signing_place'},
            {searchable: false, data: 'total_amount'},
//            {searchable: false, data: 'receiver'},
//            {searchable: false, data: 'trading_location'},
//            {searchable: true, data: 'receiving_phone'},
            {searchable: true, data: 'billing_method'},
            {searchable: true, data: 'supplier_representative'},
            {searchable: true, data: 'supplier_phone'},
//            {searchable: true, data: 'supplier_fax'},
            {searchable: true, class:'suc' , data: 'is_return_contract', render: function (data){return ['未回传', '已回传'][+data]}},
            {searchable: true, data: 'order_status', render: function (data){return ['未生成订单','已生成订单','已生成订单','已生成订单','已生成订单'][+data]}},
            {searchable: true, data: 'audit_status', render: function (data){return ['新提交', '不合格','审核通过'][+data]}},
            {searchable: true, data: 'file_name'}
        ],
        'fnRowCallback':function(nRow,aData,iDisplayIndex,iDisplayIndexFull){
            /*
                nRow:每一行的信息 tr  是Object
                aData：行 index
            */
            for(let key in nRow){
                var ADataStatus = nRow['childNodes'][7].innerText
                if(ADataStatus == '未回传'){
                    $(nRow['childNodes'][7]).css("color",'red')
                }else if(ADataStatus == '已回传'){
                    $(nRow['childNodes'][7]).css("color",'green')
                }
            }

        },
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
    var currentId
    var current_id
    var current_status
    var previewid
    var file_name
    var currentData
    $('tbody').on('click', 'tr', function () {
        currentData = table.row(this).data();
        if(currentData != undefined){
            currentId = currentData.id;
            current_id = currentData.contract_id
            previewid = currentData.id
            file_name = currentData.file_name
            current_status = currentData.audit_status
            $('tr').removeClass('selected')
            $(this).addClass('selected')
            $.post('/Dwin/Purchase/getContractProduct', {id: currentId}, function (res) {
                for(var i = 0;i< res.data.length;i++){
                    res.data[i].deliver_time = formatDateTime(res.data[i].deliver_time)
                }
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
                dialogAffix:false,
                currentId:currentId,
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
                    'id': currentId,
                    'status':vul
                }
                $.post('<?php echo U("Dwin/Purchase/auditContract");?>', data , function (res) {
                    if(res.status == 200){
                        table.ajax.reload()
                        vm.dialogVisible = false
                    }
                    layer.msg(res.msg)
                })
            },
            // 上传附件
            papersUploadSuccess: function (res) {
                layer.msg(res.msg)
                this.dialogAffix = false
            },
            uploadError(res){
                layer.msg(res.msg)
            },
            // 上传附件
            up_contract_affix(){
                var data = {
                    'id' : id,
                    'type':'team',
                    'data' : this.team[index]
                }
                layer.confirm('确认上传?', function (aaa) {
                    $.post('<?php echo U("/Dwin/Purchase/uploadContractFile");?>', data, function (res) {
                        if (res.status == 200) {
                            vm.getData();
                            layer.msg(res.msg)
                        }else{
                            layer.msg(res.msg)
                        }
                    })
                })
            },
            click_upload(){
                vm.upLoadData.id = currentId
            }
        }
    })
    // 生成订单
    $('.indent_staff').on('click', function () {
        if (current_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            if(current_status == '2'){
                var index = layer.open({
                    type: 2,
                    title: '采购生成订单',
                    content: '/Dwin/Purchase/createOrderWithContract?contractId=' + currentId,
                    area: ['90%', '90%'],
                    shadeClose:true,
                    end: function () {
                        table.ajax.reload()
                    }
                })
            }else if(current_status == '0'){
                layer.msg('该合同还未审核')
            }else if(current_status == '1'){
                layer.msg('该合同审核未通过')
            }
        }
    })
    // 点击 合同详情
    $('.details_staff').on('click', function () {
        if (current_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            var index = layer.open({
                type: 2,
                title: '湖南迪文科技有限公司采购合同详情',
                content: '/Dwin/Purchase/getContractAllMsg?id=' + currentId,
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
        if (current_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            if(current_status == '2'){
                layer.msg('该合同审核已通过')
            }else{
                var index = layer.open({
                    type: 2,
                    title: '湖南迪文科技有限公司采购合同编辑',
                    content: '/Dwin/Purchase/editContract?contractId=' + currentId,
                    area: ['90%', '90%'],
                    shadeClose:true,
                    end: function () {
                        table.ajax.reload()
                    }
                })
            }
        }
    })
    // 刷新
    $('.refresh').on('click', function () {
        table.order([[5, 'desc']])
        table.ajax.reload()
    })
    // 审核
    $('.audit_staff').on('click', function () {
        if (current_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            vm.dialogVisible = true
        }
    })
    // 上传合同附件
    $('.affix_staff').on('click', function () {
        if (current_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            vm.dialogAffix = true
        }
    })
    // 预览文件
    $('.preview_staff').on('click', function () {
        if (current_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            if(!file_name){
                layer.msg('没有上传你要浏览的合同文件！')
            }else{
                if (!window.Uint8Array) {
                    layer.msg('旧版本浏览器无法正常显示此页面')
                    return false
                }
                if (current_id){
                    if(previewid){
                        window.open('<?php echo U("previewPdf", [], "");?>/id/' + previewid )
                    }else{
                        layer.msg('合同还没有上传附件！')
                    }
                } else {
                    layer.msg('请选择一行数据') 
                }
            }
        }
    })
    // 打印合同
    $('.print_staff').on('click', function () {
        if (current_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            $.post('<?php echo U("/Dwin/Purchase/uploadContract");?>', {id:current_id}, function (res) {
                layer.msg(res.msg)
                if (res.status == 200) {
                    window.print(res.data.url)
                }
            })
        }
    })
    // 时间戳转化为时间
    function formatDateTime(timeStamp) { 
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
}
</script>
</body>
</html>