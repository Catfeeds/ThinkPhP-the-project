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
       table tr{
           height: 35px;
       }
       table tr td:nth-child(1):hover{
           background-color: blue
       }
       .cell{
           padding: 0;
       }
       .condent .has-gutter{
           display: none
       }
       /* 自适应 */
       .dataTables_scrollHeadInner{
            width: 100% !important;
        }
        .dataTables_scrollHeadInner table{
            width: 100% !important;
        }
        .dataTables_scrollBody table{
            width: 100% !important;
        }
        .borColor{
            border: 1px solid #1c84c6;
            color: #1c84c6;
        }
        .deleteHead .has-gutter{
            display: none !important;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="ibox float-e-margins">
        <div class="ibox-content">
            <div class="ibox-content" id="app" v-loading="loading">
                <div class="title">
                    <h4>湖南迪文有限公司采购物料进度</h4>
                    <div style="margin-bottom: 10px">
                        <button class="btn btn-xs btn-outline btn-success refresh">刷 新</button>
                        <button class="btn btn-xs btn-outline btn-success details_staff"><span class="glyphicon glyphicon-cloud-plus"></span>新 增</button>
                        <button class="btn btn-xs btn-outline btn-success revamp_staff"><span class="glyphicon glyphicon-align-pencil"></span>修 改</button>
                        <select name="" id="go_back" @change="chang_goBack" class="btn btn-xs btn-outline borColor">
                            <option value="2">未出库完成</option>
                            <option value="1">已出库完成</option>
                        </select>
                        <el-input placeholder="请输入内容" size="mini" v-model="condition"  style="width: 300px;float: right;">
                            <el-button slot="append" @click="getData_ScheduleList()" icon="el-icon-search"></el-button>
                        </el-input>
                    </div>
                </div>
                <div>
                    <el-table ref="multipleTable" :row-style="rowClass" :header-cell-style="{background:'skyblue'}" class="one_table_input" highlight-current-row :data="product" :span-method="undeliveredPortion" tooltip-effect="dark" style="width: 100%" border  @row-click="Selected_tables">
                            <el-table-column v-if="false" label="id" prop="id" type="index" align="center" header-align="center" width="50"></el-table-column>
                            <el-table-column label="序号" type="index" align="center" header-align="center" width="50"></el-table-column>
                            <el-table-column label="采购日期" prop="create_time"  align="center" header-align="center"></el-table-column>
                            <el-table-column label="代码" prop="product_no" align="center" header-align="center"> </el-table-column>
                            <el-table-column label="物料名称" prop="product_number" align="center" header-align="center"></el-table-column>
                            <el-table-column label="承诺交期" prop="deliver_time" align="center" header-align="center"></el-table-column>
                            <el-table-column label="创建人" prop="name" align="center" header-align="center"> </el-table-column>
                            <el-table-column label="订单量" prop="number" align="center" header-align="center"> </el-table-column>
                            <el-table-column label="累计到货量" prop="stock_in_num" align="center" header-align="center"></el-table-column>
                            <el-table-column label="未到部分" align="center" width="550%" header-align="center">
                                <template slot-scope="scope">
                                <el-table ref="multipleTable" :class="[scope.row.schedule_data[0]? '':'deleteHead']" id="insider" :header-cell-style="{background:'skyblue'}" :data="scope.row.schedule_data" tooltip-effect="dark" style="width: 100%;" border>
                                    <el-table-column label="预计到货时间" align="center" header-align="center" width="160%">
                                        <template slot-scope="scope">
                                            <el-date-picker
                                                style="width: 100%;"
                                                v-model="scope.row.estimated_arrive_time"
                                                type="date"
                                                readonly
                                                value-format="timestamp"
                                                format="yyyy-MM-dd"
                                                placeholder="预计到货时间">
                                            </el-date-picker>
                                        </template>    
                                    </el-table-column>
                                    <el-table-column label="预计到货数量"  prop="estimated_arrive_num" align="center" header-align="center"></el-table-column>
                                   <el-table-column  label="是否发货" prop="is_send" align="center" header-align="center">
                                        <template slot-scope="scope">
                                            {{scope.row.is_send == '1'?'已发出':'未发出'}}
                                        </template>
                                    </el-table-column>
                                    <el-table-column  label="单号" prop="no" align="center" header-align="center"></el-table-column>  
                                </el-table>
                            </template>
                        </el-table-column>
                        <el-table-column label="订单状态" prop="product_status" align="center" header-align="center"></el-table-column>
                    </el-table>
                    <!-- 分页 -->
                    <div v-show="product.length">
                        <el-pagination @size-change="handleSizeChange" background @current-change="handleCurrentChange" align="center" :current-page="current.current_page" :page-sizes="[5,10,20,30,50,100]" :page-size="current.page_sizes" layout="total, sizes, prev, pager, next, jumper" :total="current.total" style="margin-top:15px"></el-pagination>
                    </div>
                    <!-- 分页 END -->
                </div>
                <!-- 审核 弹框 -->
                <el-dialog title="其他出库单审核：" class="selsctDialog" :visible.sync="dialogVisible" width="30%">
                   <el-button type="primary" @click="eleClick_that(2)">合 格</el-button>
                   <el-button type="primary" @click="eleClick_that(1)" style="margin-left: 54px">不合格</el-button>
               </el-dialog>
                <!-- 审核 添加 ADD -->
                <el-dialog title="采购订单进度新增：" class="selsctDialog" @close="dialog_addClose" :visible.sync="dialogVisible_Add" width="80%">
                    <!-- 基本信息 -->
                    <el-row>
                        <el-col :span="10" :offset="1" align="left">
                            <p>
                                <b>物料名称：</b>{{happer_day.product_number}}
                            </p>
                        </el-col>
                        <el-col :span="10" :offset="3" align="left">
                            <b>承诺交期：</b>{{happer_day.deliver_time}}
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="10" :offset="1" align="left">
                            <p>
                                <b>订单量：</b>{{happer_day.number}}
                            </p>
                        </el-col>
                        <el-col :span="10" :offset="3" align="left">
                            <b>累计到货量：</b>{{happer_day.stock_in_num}}
                        </el-col>
                    </el-row>
                    <el-table ref="multipleTable"  :header-cell-style="{background:'skyblue'}" id="add_input" :data="save_product_data" tooltip-effect="dark" style="width: 100%;" border>
                        <el-table-column label="预计到厂时间" align="center" header-align="center">
                            <template slot-scope="scope">
                                <el-date-picker
                                    style="width: 100%;"
                                    v-model="scope.row.estimated_arrive_time"
                                    type="date"
                                    disabled
                                    value-format="timestamp"
                                    format="yyyy-MM-dd"
                                    placeholder="创建时间：">
                                </el-date-picker>
                            </template>
                        </el-table-column>
                        <el-table-column label="预计到货数量"  align="center" header-align="center">
                            <template slot-scope="scope">
                                <el-input v-model="scope.row.estimated_arrive_num" disabled></el-input>
                            </template>
                        </el-table-column>
                        <el-table-column  label="是否发出" align="center" header-align="center" width="200%">
                            <template slot-scope="scope">
                                    <el-radio v-model="scope.row.is_send" disabled  label="1">已发出</el-radio>
                                    <el-radio v-model="scope.row.is_send" disabled label="2">未发出</el-radio>
                            </template>
                        </el-table-column>
                        <el-table-column  label="单号" align="center" header-align="center">
                            <template slot-scope="scope">
                                <el-input v-model="scope.row.no" disabled class="add_input"></el-input>
                            </template>
                        </el-table-column>
                    </el-table>
                    <!-- 新增一行 -->
                    <el-table ref="multipleTable"  :header-cell-style="{background:'skyblue'}" v-show="add_click" class="condent" :data="save_product_data1" tooltip-effect="dark" style="width: 100%;" border>
                        <el-table-column  align="center" class="has-gutter" header-align="center">
                            <template slot-scope="scope">
                                    <el-date-picker
                                    style="width: 100%;"
                                    v-model="scope.row.estimated_arrive_time"
                                    type="date"
                                    value-format="timestamp"
                                    format="yyyy-MM-dd"
                                    placeholder="创建时间：">
                                </el-date-picker>
                            </template>
                        </el-table-column>
                        <el-table-column  align="center" header-align="center">
                            <template slot-scope="scope">
                                <el-input v-model="scope.row.estimated_arrive_num" ></el-input>
                            </template>
                        </el-table-column>
                        <el-table-column align="center" header-align="center" width="200%">
                            <template slot-scope="scope">
                                    <el-radio v-model="scope.row.is_send" label="1">已发出</el-radio>
                                    <el-radio v-model="scope.row.is_send" label="2">未发出</el-radio>
                            </template>
                        </el-table-column>
                        <el-table-column align="center" header-align="center">
                            <template slot-scope="scope">
                                <el-input v-model="scope.row.no" class="add_input"></el-input>
                            </template>
                        </el-table-column>
                    </el-table>
                    <p style="margin-top: 10px">
                        <el-button size="mini" type="danger" @click="handleDelete">新增</el-button>
                        <el-button size="mini" type="success" @click="handleDelete_successSave">保存</el-button>
                        <el-button size="mini" type="success" @click="dialogVisible_Add = false">取消</el-button>
                    </p>
               </el-dialog>
               <!-- 修改 edit -->
               <el-dialog title="采购订单进度修改：" class="selsctDialog" @close="dialog_editClose" :visible.sync="dialogVisible_edit" width="80%">
                    <!-- 基本信息 -->
                    <el-row>
                        <el-col :span="10" :offset="1" align="left">
                            <p>
                                <b>物料名称：</b>{{happer_day.product_number}}
                            </p>
                        </el-col>
                        <el-col :span="10" :offset="3" align="left">
                            <b>承诺交期：</b>{{happer_day.deliver_time}}
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="10" :offset="1" align="left">
                            <p>
                                <b>订单量：</b>{{happer_day.number}}
                            </p>
                        </el-col>
                        <el-col :span="10" :offset="3" align="left">
                            <b>累计到货量：</b>{{happer_day.stock_in_num}}
                        </el-col>
                    </el-row>
                    <el-table ref="multipleTable"  :header-cell-style="{background:'skyblue'}" :data="save_product_data" tooltip-effect="dark" style="width: 100%;" border>
                        <el-table-column label="预计到厂时间" align="center" header-align="center">
                            <template slot-scope="scope">
                                    <el-date-picker
                                    style="width: 100%;"
                                    v-model="scope.row.estimated_arrive_time"
                                    type="date"
                                    value-format="timestamp"
                                    format="yyyy-MM-dd"
                                    placeholder="创建时间：">
                                </el-date-picker>
                            </template>
                        </el-table-column>
                        <el-table-column label="预计到货数量"  align="center" header-align="center">
                            <template slot-scope="scope">
                                <el-input v-model="scope.row.estimated_arrive_num" ></el-input>
                            </template>
                        </el-table-column>
                        <el-table-column  label="是否发出" align="center" header-align="center" width="200%">
                            <template slot-scope="scope">
                                    <el-radio v-model="scope.row.is_send" label="1">已发出</el-radio>
                                    <el-radio v-model="scope.row.is_send" label="2">未发出</el-radio>
                            </template>
                        </el-table-column>
                        <el-table-column  label="单号" align="center" header-align="center">
                            <template slot-scope="scope">
                                <el-input v-model="scope.row.no" class="add_input"></el-input>
                            </template>
                        </el-table-column>
                        <el-table-column  label="操作" align="center" width="180%" header-align="center">
                            <template slot-scope="scope">
                                <el-button size="mini" type="success" @click="handleDelete_editSave(scope.$index,scope.row)">保存</el-button>
                                <el-button size="mini" type="warning" @click="handleDelete_deleteBUT(scope.$index,scope.row)">删除</el-button>
                            </template>
                        </el-table-column>
                    </el-table>
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
    var elseId
    var else_id
    var contractId
    var else_status
    // var id
    var ApplyData
    $('tbody').on('click', 'tr', function () {
        elseData = table.row(this).data();
        if(elseData != undefined){
            elseId = elseData.id;   // 订单主键
            else_id = elseData.bom_id      //订单编号
            else_status = elseData.audit_status
            $('tr').removeClass('selected')
            $(this).addClass('selected')
            $.post('/Dwin/Stock/stockOutProduceMaterial', {id: elseId}, function (res) {
                for(var j = 0;j < res.data.length;j++){
                    for(var i = 0;i < repMap.length;i++){
                        if(res.data[j].rep_pid == repMap[i].rep_id){
                            res.data[j].rep_pid = repMap[i].repertory_name
                        }
                    } 
                }
                vm.bomInfo = res.data
            })
            $.post('/Dwin/Stock/getStockOutRecord', {id: elseId},function (res) { 
                for(var j = 0;j < res.data.length;j++){
                    res.data[j].create_time = vm.formatDateTime(res.data[j].create_time)
                    res.data[j].update_time = vm.formatDateTime(res.data[j].update_time)
                }
                vm.record_info = res.data
            })
        }
    })
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                loading:true,
                add_click:false,
                record_info:[],
                bomInfo:[],
                product:[], 
                product_son:[],
                // 分页
                current: {
                    current_page: 1,
                    page_sizes: 10,
                    total: 0
                },
                condition:'',
                dialogVisible:false,
                dialogVisible_Add:false,
                dialogVisible_edit:false,
                happer_day:[],
                options_picking_kind:[
                    {
                        id:1,
                        name:"已发出"
                    },
                    {
                        id:2,
                        name:"未发出"
                    }
                ],
                select_handleID:'',//id
                select_orderID:'',//order_pid
                save_product_data:[],
                save_product_data1:[],
                dialogVisible_delete:false,
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
                upLoadData:{
                    id:''
                }
            }
        },
        created () {
            this.getData_ScheduleList()
        },
        watch:{
        },
        methods: {
            // 变色
            rowClass (row, index) {
                return { "background-color": "#fff"}
            },
            //  修改  保存
            handleDelete_editSave(index,row){
                row.estimated_arrive_time = row.estimated_arrive_time / 1000
                $.post('<?php echo U("/Dwin/Purchase/editOrderSchedule");?>',row,function (res) { 
                    if(res.msg == 200){
                        location.reload()
                    }else{
                        row.estimated_arrive_time = row.estimated_arrive_time * 1000
                    }
                    layer.msg(res.msg)
                 })
            },
            // 修改  删除
            handleDelete_deleteBUT(index,row){
                var data = {
                    id:row.id
                }
                $.post('<?php echo U("/Dwin/Purchase/delOrderSchedule");?>',data,function (res) { 
                    if(res.status == 200){
                        vm.save_product_data.splice(index,1)
                        location.reload()
                    }
                    layer.msg(res.msg)
                 })
            },
            // 修改关闭回调
            dialog_editClose(){
                this.getData_ScheduleList()
            },
            // 新增保存
            handleDelete_successSave(){
                var scheduleData = []
                for(var i = 0;i < vm.save_product_data1.length;i++){
                    this.save_product_data1[i].estimated_arrive_time = this.save_product_data1[i].estimated_arrive_time / 1000
                    scheduleData.push(this.save_product_data1[i])
                    
                }
                var data = {
                    // orderId:this.select_orderID,
                    orderProductId:this.select_handleID,
                    scheduleData:scheduleData
                }
                $.post('<?php echo U("/Dwin/Purchase/addOrderSchedule");?>',data,function (res) {
                    if(res.status == 200){
                        location.reload()
                    }else{
                        for(var i = 0;i < vm.save_product_data1.length;i++){    
                            vm.save_product_data1[i].estimated_arrive_time = Number(vm.save_product_data1[i].estimated_arrive_time) * 1000
                        }
                        layer.msg(res.msg)
                    }
                })
            },
            // 新增
            handleDelete(){
                var OBJ = {
                    'estimated_arrive_time':new Date(),
                    'estimated_arrive_num':'',
                    'is_send':'',
                    'no':''
                }
                this.add_click = true
                Vue.set(this.save_product_data1,this.save_product_data1.length,OBJ)
                var newNotData_ = document.getElementsByClassName('el-table__empty-block')
                newNotData_[newNotData_.length - 2].setAttribute('style','display:none')
            },
            // 新增关闭回调
            dialog_addClose(){
                this.save_product_data1 = []
            },
            // 选中一行
            Selected_tables(row,event,column){
                this.save_product_data = []
                this.select_handleID = row.id
                this.select_orderID = row.order_pid
                for(var i = 0;i < row.schedule_data.length;i++){
                    this.save_product_data.push(row.schedule_data[i])
                }
                this.happer_day = row
            },
            handleSizeChange(val) {
                this.current.page_sizes = val
                this.getData_ScheduleList()
            },
            handleCurrentChange(val) {
                this.current.current_page = val
                this.getData_ScheduleList()
            },
            // 已出 、 未出 筛选
            chang_goBack(){
                this.getData_ScheduleList();
            },
            // 请求数据
            getData_ScheduleList(){
                this.product_son = []
                this.product.length = 0
                var data = {
                    current_page: this.current.current_page,
                    page_sizes: this.current.page_sizes,
                    total: this.current.current_page,
                    condition:this.condition,
                    stockType:document.getElementById('go_back').value
                }
                $.post('<?php echo U("/Dwin/Purchase/orderScheduleList");?>',data,function (res) {
                    if(res.status !== 200){
                        layer.msg('请求数据出错！')
                        return false
                    }
                    vm.product = res.data.data
                    for(var i = 0; i < res.data.data.length;i++){
                        vm.product_son.push(res.data.data.schedule_data)
                        vm.product[i].create_time = vm.formatDateTime(vm.product[i].create_time)
                        vm.product[i].deliver_time = vm.formatDateTime(vm.product[i].deliver_time)
                        for(var j = 0;j < vm.product[i].schedule_data.length;j++){
                            vm.product[i].schedule_data[j].estimated_arrive_time = vm.product[i].schedule_data[j].estimated_arrive_time * 1000
                        }
                    }
                    vm.current.current_page = Number(res.data.current_page)
                    vm.current.page_sizes = Number(res.data.page_sizes)
                    vm.current.page_sizes = Number(res.data.page_sizes)
                    vm.current.total = Number(res.data.total)
                    vm.loading = false
                })
            },
            // 单元格合并
            undeliveredPortion({ row, column, rowIndex, columnIndex }){
                // if(row == 1){
                //     return[1,2]
                // }
                // if(rowIndex == 9){
                //     return[1,4]
                // }

            },
            // 审核确定
            eleClick_that(vul){
                var vm = this
                var data = {
                    'ApplyId': ApplyId,
                    'status':vul
                }
                $.post('<?php echo U("/Dwin/Stock/auditOtherStockOutApply");?>', data , function (res) {
                    if(res.status == 200){
                        table.ajax.reload()
                        vm.dialogVisible = false
                    }
                    layer.msg(res.msg)
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
                            window.open(res.data.file_url);
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
                    return y + '-' + m + '-' + d;  
                }else{
                    return ''
                }
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
        for(let i in statusMap){
            vm.options_del.push({'value':String(i), 'label':statusMap[i]})
        }
        for(let i in auditMap){
            vm.options_audit.push({'value':String(i), 'label':auditMap[i]})
        }
        $.post('<?php echo U("getBomIdList");?>', {'bom_id':''} , function (res) {
            for(var i = 0;i < res.data.length;i++){
                vm.BomNUM.push({'value':res.data[i].bom_id , 'label':res.data[i].bom_id})
            }
        })
        vm.dialogUpdataExecl = true 
    })
    // 新增
    $('.details_staff').on('click', function () {
        if (vm.select_handleID === ''){
            layer.msg('请选择一个物料信息')
        } else {
           vm.dialogVisible_Add = true
            if(vm.product){
                for(var i = 0;i < vm.save_product_data.length;i++){
                    $("#add_input .add_input").attr("disabled",true)
                }
            }
        }
    })
    // 领料出库单 修改
    $('.revamp_staff').on('click', function () {
        if(vm.select_handleID === ''){
            layer.msg('请选择一个物料信息')
        }else{
           vm.dialogVisible_edit = true
        }
    })
    // BOM  新增
    $('.edit_staff').on('click', function () { 
        var index = layer.open({
            type: 2,
            title: '湖南迪文有限公司新增出库申请单',
            content: '/Dwin/Stock/createOtherStockOutApply',
            area: ['90%', '90%'],
            shadeClose:true,
            end: function () {
                table.ajax.reload()
            }
        }) 
    })
    // 刷新
    $('.refresh').on('click', function () {
        vm.getData_ScheduleList()
    })
    // 审核
    $('.audit_staff').on('click', function () {
        if (elseId === undefined){
            layer.msg('请选择一家供应商')
        } else {
            if(else_status >= '3'){
                vm.$message({
                    showClose: true,
                    message: '该项审核已通过,不能再次审核！！',
                    type: 'warning'
                });
            }else{
                vm.dialogVisible = true
            }
        }
    })
    // 删除出库申请单
    $('.delete_staff').on('click', function () {
        if (ApplyId === undefined){
            layer.msg('请选择一家供应商')
        } else {
            if(Apply_status == '2'){
                layer.msg('该订单审核已通过,不能删除BOM！')
            }else{
                var data = {
                    'applyId' : ApplyId,
                }
                layer.confirm('确认删除?', function (aaa) {
                    $.post('<?php echo U("/Dwin/Stock/createOtherStockOut");?>', data, function (res) {
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