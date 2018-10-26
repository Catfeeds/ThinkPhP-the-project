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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">

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
       .notSelectData{
            color:#e4393c;text-align: center;font-size: 15px
       }
       .change-css{
            cursor: pointer;
            text-align: center!important;
            line-height: 20px;
            background-color:whitesmoke;
        }
        .active-css{
            background-color: lightskyblue;
            border-radius: 2px;
        }
        .active-css-child{
            color:red!important;
            line-height: 20px;
        }
        .hover-css{
            background-color: gainsboro;
            color:dimgray!important;
            border-radius: 2px;
            line-height: 20px;
        }
        .but_floatFood{
            display: inline-block !important;
            width: auto;
            min-height: 30px;
            margin: 0 1%;
            line-height: 30px;
        }
        li.active > a{
            background-color: #1c84c6 !important;
            color: #fff !important;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="ibox float-e-margins">
        <div class="ibox-content">
                <div class="title_top">
                        <h4>采购物料BOM列表</h4>
                        <div style="display: inline-block;width:100%;margin-top: 3px;">
                            <div class="fa-hover col-md-1 col-sm-3 change-css active-css but_floatFood" data-id="0" onclick="outbound(1)" style="margin:0 1% 0 0"><a href="javascript:;" class="active-css-child"><i class="fa fa-tv">bom未审核列表</i></a></div>
                            <div class="fa-hover col-md-1 col-sm-3 change-css but_floatFood" data-id="1" onclick="outbound(3)"><a href="javascript:;"><i class="fa fa-tv">bom不合格列表</i></a></div>
                            <div class="fa-hover col-md-1 col-sm-3 change-css but_floatFood" data-id="2" onclick="outbound(2)"><a href="javascript:;"><i class="fa fa-tv">bom合格列表</i></a></div>
                            <div class="fa-hover col-md-1 col-sm-3 change-css but_floatFood" data-id="3" onclick="outbound(4)"><a href="javascript:;"><i class="fa fa-tv">禁用列表</i></a></div>
                            <input type="hidden" id="orderType" value="0">
                        </div>
                        <button class="btn btn-xs btn-outline btn-success refresh">刷 新</button>
                        <button class="btn btn-xs btn-outline btn-success edit_staff"><span class="glyphicon glyphicon-plus"></span>BOM新增</button>
                        <p>
                            <button class="btn btn-xs btn-outline btn-success audit_staff"><span class="glyphicon glyphicon-adjust"></span>BOM审核</button>
                            <button class="btn btn-xs btn-outline btn-success delete_staff"><span class="glyphicon glyphicon-remove"></span>删除Bom</button>
                            <button class="btn btn-xs btn-outline btn-success revamp_staff"><span class="glyphicon glyphicon-edit"></span>BOM修改</button>
                        </p>
                        <p>
                            <button class="btn btn-xs btn-outline btn-success forbidden_staff"><span class="glyphicon glyphicon-ban-circle"></span>bom禁用</button>
                        </p>
                        <p>
                            <button class="btn btn-xs btn-outline btn-success delete_staff"><span class="glyphicon glyphicon-remove"></span>删除Bom</button>
                            <button class="btn btn-xs btn-outline btn-success revamp_staff"><span class="glyphicon glyphicon-edit"></span>BOM修改</button>
                        </p>
                        <p>
                            <button class="btn btn-xs btn-outline btn-success forbidden_staff"><span class="glyphicon glyphicon-ok-circle"></span>bom启用</button>
                        </p>
                        <button class="btn btn-xs btn-outline btn-success msg_staff"><span class="glyphicon glyphicon-align-justify"></span>bom详情</button>
                        <button class="btn btn-xs btn-outline btn-success details_staff"><span class="glyphicon glyphicon-cloud-upload"></span>导入Execl</button>
                        <button class="btn btn-xs btn-outline btn-success indent_staff"><span class="glyphicon glyphicon-cloud-download"></span>导出Execl</button>
                    </div>
            <div class="table-responsive">
                <table id="staff" class="table table-bordered table-hover table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>物料编号</th>
                            <th>物料型号</th>
                            <th>物料名称</th>
                            <th>BOM编号</th>
                            <th>BOM组别</th>
                            <th>仓库名称</th>
                            <th>审核状态</th>
                            <th>创建人</th>
                            <th>更新人</th>
                            <th>创建时间</th>
                            <th>更新时间</th>
                            <th>备注说明</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="ibox-content" id="app">
                <ul class="nav nav-tabs" role="tablist" v-if="unData">
                    <li role="presentation" class="active"><a href="#contact" aria-controls="contact" role="tab" data-toggle="tab">物料清单</a></li>
                    <li role="presentation"><a href="#resume" aria-controls="resume" role="tab" data-toggle="tab">bom履历</a></li>
                </ul>
                <div class="tab-content" v-if="unData">
                    <div role="tabpanel" class="tab-pane active" id="contact">
                        <table class="table table-striped table-hover table-border">
                            <tr>
                                <th>物料编号</th>
                                <th>物料型号</th>
                                <th>物料名称</th>
                                <th>物料数量</th>
                                <th>更新时间</th>
                                <th>备注说明</th>
                            </tr>
                            <tr v-for="item in bomInfo">
                                <td>{{item.product_no  || ''}}</td>
                                <td>{{item.product_name  || ''}}</td>
                                <td>{{item.product_number  || ''}}</td>
                                <td>{{item.num  || ''}}</td>
                                <td>{{formatDateTime(item.update_time)|| ''}}</td>
                                <td>{{item.tips || ''}}</td>
                            </tr>
                        </table>
                        <div v-show="bomInfo.length">
                            <el-pagination 
                                @size-change="handleSizeChange" background 
                                @current-change="handleCurrentChange" align='center' 
                                :page-sizes="[5,10,20]" 
                                :current-page="current.pageNo" 
                                :page-size="current.pageSize" 
                                :total="current.total" 
                                layout="total, sizes, prev, pager, next, jumper" 
                                style="margin-top:15px">
                            </el-pagination>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="resume">
                        <table class="table table-striped table-hover table-border">
                            <tr>
                                <th>操作姓名</th>
                                <th>操作类型</th>
                                <th>操作时间</th>
                                <th>操作内容</th>
                            </tr>
                            <tr v-for="item in logData">
                                <td>{{item.create_name  || ''}}</td>
                                <td>{{logType[item.type]  || ''}}</td>
                                <td>{{formatDateTime(item.create_time)  || ''}}</td>
                                <td>{{item.content  || ''}}</td>
                            </tr>
                        </table>
                        <div v-show="logData.length">
                            <el-pagination 
                                @size-change="handleSizeChange_log" background 
                                @current-change="handleCurrentChange_log" align='center' 
                                :page-sizes="[5,10,20]" 
                                :current-page="log.pageNo" 
                                :page-size="log.pageSize" 
                                :total="log.total" 
                                layout="total, sizes, prev, pager, next, jumper" 
                                style="margin-top:15px">
                            </el-pagination>
                        </div>
                    </div>
                </div>
                <div class="notSelectData"  v-else>
                    当前没有选中或无数据
                </div>
                <!-- 审核 弹框 -->
                <el-dialog
                    title="BOM审核："
                    :visible.sync="dialogVisible"
                    width="30%"
                    >
                    <span>该Bom审核是否通过？</span>
                    <span slot="footer" class="dialog-footer">
                        <el-button @click="eleClick_that(1)">不通过</el-button>
                        <el-button type="primary" @click="eleClick_that(2)">通过</el-button>
                    </span>
                </el-dialog>
                <!-- 审核 execl -->
                <el-dialog title="BOM EXECL上传：" class="selsctDialog" :visible.sync="dialogExecl" width="30%">
                    <el-upload
                        class="uploadResume"
                        action="<?php echo U('uploadByexecl');?>"
                        :on-success="papersUploadSuccess"
                        :on-error="uploadError"
                        :auto-upload="true"
                        >
                        <el-button size="small" type="primary">导入BOM Execl</el-button>
                    </el-upload>
                    </el-dialog>
                
                <!-- 审核 下载Execl -->
                <el-dialog title="导出BOM EXECL：" size="mini" class="selsctDialog" style="padding:0" :visible.sync="dialogUpdataExecl" width="40%">
                    <div>
                        <span> 审核状态：</span>
                        <el-select v-model="dialog.auditMSG" placeholder="请选择" required>
                            <el-option
                                v-for="item in options_audit"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                            </el-option>
                        </el-select>
                    </div>
                    <div  style="margin-top: 20px">
                        <span>BOM组别：</span>
                        <el-select v-model="dialog.groung_status" placeholder="请选择">
                            <el-option
                                v-for="item in options_status"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                            </el-option>
                        </el-select>
                    </div>
                    <div style="margin-top: 20px">
                        <span>BOM编号：</span>
                        <el-select
                            v-model="dialog.bomNumber"
                            multiple
                            filterable
                            remote
                            reserve-keyword
                            placeholder="请输入关键词"
                            :remote-method="select_BomNUM"
                            :loading="loading">
                            <el-option
                            v-for="item in BomNUM"
                            :key="item.bom_id"
                            :label="item.bom_id"
                            :value="item.bom_id">
                            </el-option>
                        </el-select>
                        <!-- <el-select v-model="dialog.bomNumber" @visible-change="select_BomNUM($event,dialog.groung_status,dialog.auditMSG,row)" multiple filterable placeholder="请选择BOM编号(可多选)">
                            <el-option
                                v-for="item in BomNUM"
                                :key="item.bom_id"
                                :label="item.bom_id"
                                :value="item.bom_id">
                            </el-option>
                        </el-select> -->
                    </div>
                    <div  style="margin-top: 20px">
                        <el-button type="primary" @click="updataExeclBUT">下载 Execl</el-button>
                    </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/index.js"></script>

<script>
    var statusMap = <?php echo (json_encode($statusMap)); ?>;  //已  删除、未
    var groupMap = <?php echo (json_encode($groupMap)); ?>;     //成品
    var auditMap = <?php echo (json_encode($auditMsg)); ?>;     // 未成品
    var logType = <?php echo (json_encode($logType)); ?>;     // 操作类型
    var table = $('#staff'). DataTable({
        ajax: {
            type: 'post',
            data: {
                function(){
                    if(obj){
                        obj.bomId=""
                        obj.bom_id=undefined
                        obj.bom_status=''
                        obj.orderData=[]
                        vm.bomInfo = []
                        vm.logData = []
                        vm.unData = false
                    }
                },
                bom_status: function () {
                    return document.getElementById('orderType').value;
                },
                flag: 1
            }
        },
        "scrollY": false,
        "scrollX": false,
        "scrollCollapse": true,
        "destroy"      : true,
        "paging"       : true,
        "autoWidth"	   : false,
        "pageLength": 10,
        serverSide: true,
        // order:[[21, 'desc']],
        columns: [
            {searchable: true, data: 'product_no'},
            {searchable: true, data: 'product_name'},
            {searchable: true, data: 'product_number'},
            {searchable: true, data: 'bom_id'},
//            {searchable: true, data: 'bom_type',render: function (data){return ['','成品', '半成品','元器件'][+data]}},
            {searchable: true, data: 'bom_type_name'},
            {searchable: true, data: 'repertory_name'},
            {searchable: false, data: 'bom_status',render: function (data){return auditMap[+data]}},
            {searchable:  true, data:  'name'},
            {searchable:  true, data:  'update_name'},
            {searchable:  true, data:  'create_time',render: function(data){return vm.formatDateTime(data)}},
            {searchable:  true, data:  'update_time',render: function(data){return vm.formatDateTime(data)}},
            {searchable:  true, data:  'remark'},
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
    var obj = {
        bomId:"",
        bom_id:"",
        bom_status:'',
        orderData:[],
        selectedObj:{},
        tabClickDiv : $('.change-css'),
        tabActiveDiv : $(".active-css"),
        kElement : document.getElementById("orderType"),
        tabMouseEnter: function (val) {
            var that = this;
            this.initData(that,false);
            this.tabClickDiv.removeClass('hover-css');
            this.tabClickDiv.children('a').removeClass('hover-css');
            val.children('a').addClass('hover-css');
            val.addClass('hover-css');
        },
        tabMouseLeave:function() {
            var that = this;
            this.initData(that,false);
            this.tabClickDiv.removeClass('hover-css');
            this.tabClickDiv.children('a').removeClass('hover-css');
        },
        tabClick:function (val) {
            var that = this;
            this.initData(that,false);
            this.tabClickDiv.removeClass('active-css');
            this.tabClickDiv.children('a').removeClass('active-css-child');
            val.children('a').addClass('active-css-child');
            val.addClass('active-css');
            var tmpOrderType = document.getElementById("orderType");
            tmpOrderType.value = $(".active-css").attr('data-id');
            table.ajax.reload();
            this.destroyData();
        },
        tbodyTrClick:function (val,that) {
            this.initData(that,true);
            $('tr').removeClass('selected')
            val.addClass('selected')
            // vm.getButtonTitle(this.bomId)
        },
        destroyData: function () {
            vm.contact = [];
            vm.orderId = "";
            this.orderId       = "",
            this.bomId      = "",
            this.bom_id    = '',
            this.bom_status = '',
            this.orderData    = [],
            this.selectedObj   = {},
            this.tabClickDiv   =  "",
            this.tabActiveDiv  =  "",
            this.kElement      =  ""
        },
        initData : function (that, flag) {
            this.tabClickDiv =  $('.change-css');
            this.tabActiveDiv =  $(".active-css")
            this.kElement =  document.getElementById("orderType")
            if (flag === true) {
                if(table.row(that).data()){
                    obj.orderData  = table.row(that).data();
                    obj.bomId = obj.orderData.id;
                    obj.bom_id    = obj.orderData.bom_id;   // 订单主键
                    obj.bom_status = obj.orderData.bom_status
                    $.post('/Dwin/bom/bomSumMsg', {id: obj.bomId}, function (res) {
                        vm.unData = true
                        vm.save_bomInfo = res.data.bomSumData
                        vm.current.total = res.data.bomSumData.length
                        vm.bomInfo = vm.save_bomInfo.slice(0,vm.current.pageSize)
                        vm.save_logData = res.data.logData
                        vm.log.total = res.data.logData.length
                        vm.logData = vm.save_logData.slice(0,vm.log.pageSize)
                    })
                }
            }
        }
    }
    obj.tabClickDiv.on('mouseenter', function () {
        obj.selectedObj = $(this);
        obj.tabMouseEnter(obj.selectedObj);
    });
    obj.tabClickDiv.on('mouseleave', function () {
        obj.tabMouseLeave();
    });
    obj.tabClickDiv.on('click', function () {
        obj.selectedObj = $(this);
        obj.tabClick(obj.selectedObj);
    });
    $('tbody').on('click', 'tr', function () {
        var that = this;
        var objThis = $(this);
        obj.tbodyTrClick(objThis,that);
    })
    // 切换操作行
    $('.title_top').children('p:eq(0)').css('display',"inline")
    $('.title_top').children('p:eq(0)').siblings('p').css('display',"none")
    function outbound(val){
        switch(val){
            case val:
                $('.title_top').children('p:eq('+ (val - 1) +')').css('display',"inline")
                $('.title_top').children('p:eq('+ (val - 1) +')').siblings('p').css('display',"none")
                break
        }
    }
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                bomInfo:[],
                logData:[],
                unData:false,
                dialogVisible:false,
                dialogVisible_delete:false,
                dialogExecl:false,
                dialogUpdataExecl:false,
                dialog:{
                    bom_id:'',
                    auditMSG:2,
                    groung_status:1,
                    del_status:0,
                    bomNumber:[]
                },
                BomNUM: [],
                options_status:[],
                options_del:[],
                options_audit:[],
                loading:true,
                logType:logType,
                upLoadData:{
                    id:''
                },
                bomUrl : "",
                save_bomInfo:[],
                save_logData:[],
                current: {
                    pageNo: 1,
                    pageSize:5,
                    total: 0
                },
                log: {
                    pageNo: 1,
                    pageSize:5,
                    total: 0
                },
            }
        },
        watch:{
            
        },
        created() {
           
        },
        methods: {
            // 每页显示几条
            handleSizeChange (val) {
                this.current.pageSize = val
                // 开始  = 页 * 条 -1 
                var start = (this.current.pageNo * this.current.pageSize) - 1;
                var end = start + val;
                if(start >= this.current.total - 1){
                    start = this.current.total - (this.current.total % val) 
                    end = this.current.total
                    this.bomInfo = this.save_bomInfo.slice(start,end);
                    return false
                }
                if(end > this.current.total - 1){
                    end = this.current.total
                }
                this.bomInfo = this.save_bomInfo.slice(start, end);
            },
            // 当前页数
            handleCurrentChange (val) {
                this.current.pageNo = val;
                var start = (this.current.pageNo - 1) * this.current.pageSize;
                var end = val * this.current.pageSize;
                if(start >= this.current.total - 1){
                    start = 0
                    end = this.current.total + 1 
                    alert(start,end)
                    this.bomInfo = this.save_bomInfo.slice(start,end);
                    return false
                }
                if(end > this.current.total - 1){
                    end = this.current.total
                }
                this.bomInfo = this.save_bomInfo.slice(start, end);
            },
            // 每页显示几条   logType
            handleSizeChange_log (val) {
                this.log.pageSize = val
                // 开始  = 页 * 条 -1 
                var start = (this.log.pageNo * this.log.pageSize) - 1;
                var end = start + val;
                if(start >= this.log.total - 1){
                    start = this.log.total - (this.log.total % val) 
                    end = this.log.total
                    this.logData = this.save_logData.slice(start,end);
                    return false
                }
                if(end > this.log.total - 1){
                    end = this.log.total
                }
                this.logData = this.save_logData.slice(start, end);
            },
            // 当前页数
            handleCurrentChange_log (val) {
                this.log.pageNo = val;
                var start = (this.log.pageNo - 1) * this.log.pageSize;
                var end = val * this.log.pageSize;
                if(start >= this.log.total - 1){
                    start = 0
                    end = this.log.total + 1 
                    alert(start,end)
                    this.bomInfo = this.save_logData.slice(start,end);
                    return false
                }
                if(end > this.log.total - 1){
                    end = this.log.total
                }
                this.bomInfo = this.save_logData.slice(start, end);
            },
            // 导出Excal 搜索
            select_BomNUM(htmlText){
                if(this.dialog.groung_status && this.dialog.auditMSG){
                    $.post('<?php echo U("getBomIdList");?>', {'bom_id':htmlText,'status':this.dialog.groung_status,'group':this.dialog.auditMSG} , function (res) {
                        if(res.status == 200){
                            vm.BomNUM = res.data
                            vm.loading = false
                        }
                    })
                }
                else
                {
                    layer.msg('请填写完整以上两个条件')
                }
            },
            // 审核确定
            eleClick_that(vul){
                var vm = this
                var data = {
                    'bomId': obj.bomId,
                    'status':vul
                }
                $.post('<?php echo U("auditBom");?>', data , function (res) {
                    if(res.status == 200){
                        table.ajax.reload()
                        vm.dialogVisible = false
                    }
                    layer.msg(res.msg)
                })
            },
             // 获取编号
             getBomNumber_num:function(item) {
                $.get('createBomId',function (res) {
                    if(res.status==200){
                        vm.dialog.bom_id=res.data.bomIdString
                        vm.$message({
                            showClose: true,
                            message: res.msg,
                            type: 'success'
                        });
                    }else{
                        vm.$message({
                            showClose: true,
                            message: res.msg,
                            type: 'error'
                        });
                    }
                    
                })
            },
            // 下载 Execl
            updataExeclBUT () {
                var data = {
                    'bom_status':vm.dialog.auditMSG,
                    'is_del':vm.dialog.del_status,
                    'bomArr' : vm.dialog.bomNumber,
                    'bom_type':vm.dialog.groung_status
                }
                var index = layer.load('正在生成xlsx文件');
                $.post('exportToExcel', data, function (res) {
                    layer.close(index);
                    if (res.status != 200) {
                        vm.$message({
                            showClose:true,
                            message:res.msg,
                            type:'success'
                        })
                    } else {
                        if (res.data.file_url) {
                            vm.bomUrl = res.data.file_url;
                            layer.confirm('是否确认下载？', {
                                btn: ['确认','取消'] 
                            }, function(){
                                window.open(vm.bomUrl);
                                layer.msg(res.msg)
                            }, function(){
                                layer.msg('下载取消')
                            });
                        } else {
                            vm.$message({
                                showClose:true,
                                message:res.msg,
                                type:'success'
                            })
                        }
                    }
                })
            },
            // 上传 EXECL =>
            papersUploadSuccess: function (res) {
                layer.msg(res.msg)
            },
            // 上传 Execl 失败
            uploadError (res) {
                layer.msg(res.msg)
            },
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
            }
        }
    })
    // updata Execl
    $('.indent_staff').on('click', function () {
        vm.options_del.length = 0
        vm.options_audit.length = 0
        vm.options_status.length = 0
        vm.BomNUM.length = 0
        vm.dialog.auditMSG = ''
        vm.dialog.del_status  = ''
        vm.dialog.bomNumber = ''
        vm.dialog.groung_status = ''
        for(let i in groupMap){
            vm.options_status.push({'value':String(i), 'label':groupMap[i]})
        }
        // for(let i in statusMap){
        //     vm.options_del.push({'value':String(i), 'label':statusMap[i]})
        // }
        for(let i in auditMap){
            vm.options_audit.push({'value':String(i), 'label':auditMap[i]})
        }
        vm.dialogUpdataExecl = true 
    })
    // 点击 Execl
    $('.details_staff').on('click', function () {
        if (obj.bom_id === undefined || !obj.bom_id){
            layer.msg('请选择一个BOM项')
        } else {
            vm.dialogExecl = true
        }
    })
    // BOM  修改
    $('.revamp_staff').on('click', function () {
        if(obj.bom_id == undefined || !obj.bom_id){
            layer.msg('请选择一个BOM项')
        }else{
            if(obj.bom_status == '2'){
                layer.msg('当前项已启用，不能修改！')
            }else{
                var index = layer.open({
                    type: 2,
                    title: '湖南迪文有限公司Bom修改',
                    content: '/Dwin/Bom/editBom?bomId='+ obj.bomId,
                    area: ['90%', '90%'],
                    shadeClose:true,
                    end: function () {
                        table.ajax.reload()
                    }
                }) 
            }
        }
    })
    // BOM  详情
    $('.msg_staff').on('click', function () {
        if(obj.bom_id == undefined || !obj.bom_id){
            layer.msg('请选择一个BOM项')
        }else{
            var index = layer.open({
                type: 2,
                title: '湖南迪文有限公司Bom修改',
                content: '/Dwin/Bom/bomAllMsg?bomId='+ obj.bomId,
                area: ['90%', '90%'],
                shadeClose:true,
                end: function () {
                    table.ajax.reload()
                }
            }) 
        }
    })
    // BOM  新增
    $('.edit_staff').on('click', function () { 
        var index = layer.open({
            type: 2,
            title: '湖南迪文有限公司Bom',
            content: '/Dwin/Bom/createBom',
            area: ['90%', '90%'],
            shadeClose:true,
            end: function () {
                table.ajax.reload()
            }
        }) 
    })
    // 刷新
    $('.refresh').on('click', function () {
        table.order([[5, 'desc']])
        table.ajax.reload()
    })
    // 审核
    $('.audit_staff').on('click', function () {
        if (obj.bom_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            if(obj.bom_status == '0'){
                vm.dialogVisible = true
            }else if(obj.bom_status == '1'){
                vm.$message({
                    showClose: true,
                    message: '该项审核不通过,请去修改！',
                    type: 'warning'
                });
            }else if(obj.bom_status == '2'){
                vm.$message({
                    showClose: true,
                    message: '该项审核已通过,不能再次审核！',
                    type: 'warning'
                });
            }
        }
    })
    // 删除Bom
    $('.delete_staff').on('click', function () {
        if (obj.bom_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            if(obj.bom_status == '2'){
                layer.msg('该订单审核已通过,不能删除BOM！')
            }else{
                var data = {
                    'bomId' : obj.bomId,
                }
                layer.confirm('确认删除?', function (aaa) {
                    $.post('<?php echo U("/Dwin/bom/deleteBom");?>', data, function (res) {
                        if (res.status == 200) {
                            table.ajax.reload()
                        }
                        layer.msg(res.msg)
                    })
                })
            }
        }
    })
    // 禁用Bom/解冻
    $('.forbidden_staff').on('click', function () {
        if (obj.bom_id === undefined){
            layer.msg('请选择一家供应商')
        } else {
            var bom_status1_ = document.getElementById('orderType').value
            var data = {
                'bomId':obj.bomId
            }
            console.log(bom_status1_ + '----' + data)
            if(bom_status1_==3){
                layer.confirm('确定启用吗？',{
                    btn:['启用','取消']
                },function(){
                    $.post('<?php echo U("/Dwin/bom/bomRelieveForbidden");?>', data, function (res) {
                        if (res.status == 200) {
                            table.ajax.reload()
                        }
                        layer.msg(res.msg)
                    })
                })
            }else{
                layer.confirm('确定禁用吗？',{
                    btn:['禁用','取消']
                },function () {
                    $.post('<?php echo U("/Dwin/bom/bomForbidden");?>', data, function (res) {
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