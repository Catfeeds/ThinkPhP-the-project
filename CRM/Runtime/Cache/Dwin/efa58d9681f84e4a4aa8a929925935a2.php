<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>生产任务</title>
    <link href="/Public/html/css/bootstrap.min14ed.css" rel="stylesheet">
    <!--<link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.4/theme-chalk/index.css" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;
            font-size: 12px;
        }
        .selected{
            background-color: #fbec88 !important;
        }

        .nav-tabs>li>a{
            color: #555;
        }
        .nav-tabs>.active>a{
            color: #000!important;
        }
        tr{
            white-space: nowrap!important;
        }
        tbody td{
            padding-top: 2px!important;
            padding-bottom: 2px!important;
        }
        .btn{
            margin-right: 1em;
        }
        .ibox{
            padding:20px;
        }
        .el-select-option-span{
            float: left;font-size: 12px;
        }
        .el-form-item__label{
            width: 140px!important;
        }
        .el-form-item__content{
            margin-left: 140px!important;
        }
        /*.delayComplain{*/
            /*display: none;*/
        /*}*/
        /*.dataTables_scrollHeadInner{*/
            /*width: 100%!important;*/
        /*}*/
        /*.dataTables_scrollHeadInner table{*/
            /*width: 100%!important;*/
        /*}*/

        .span-info{
            font-weight: 500;
            color:cornflowerblue;
        }
        .span-warning{
            font-weight: 500;
            color:red;
        }
        .nav-tabs>.active>a {
            background-color: #1c84c6 !important;
            color: #fff !important;
            font-weight: bold;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins" id="productionDiv">
                <div class="ibox-content" >
                    <h3>生产任务</h3>
                    <div class="span-warning" id="buttonVm">
                        <form class="form-inline">
                            <!-- <el-button-group> -->
                                <el-button type="primary" size='mini' icon="el-icon-refresh" @click="refreshAction()">刷新</el-button>
                                <el-button type="primary" size='mini' icon="el-icon-d-arrow-right" @click="getPreStock()">下推</el-button>
                            <!-- </el-button-group> -->
                            <label for="production_line">班组筛选</label>
                            <select name="" id="production_line" class="form-control change-data" size="mini">
                                <option value="">所有</option>
                                <?php if(is_array($lineData)): $i = 0; $__LIST__ = $lineData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["id"]); ?>"><?php echo ($vol["group_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                            <label for="production_status">生产进度筛选</label>
                            <select name="" id="production_status" class="form-control change-data" size="mini">
                                <option value="0,1,2">所有</option>
                                <option value="0,1">生产中</option>
                                <option value="2">已完工</option>
                            </select>
                        </form>
                        <el-dialog
                                title="生产入库制单"
                                :visible.sync="centerDialogVisible"
                                width="80%"
                                style="background-color: lightskyblue;"
                                center>
                            <el-form ref="form" label-width="130px">
                                <el-alert
                                        title="填写点击确定后提交入库单，审核后生效"
                                        type="success"
                                        show-icon
                                >
                                </el-alert>
                                <el-form-item label="源单编号:">
                                    <span class="span-info">{{stock.source_id_string}}</span>
                                </el-form-item>
                                <el-form-item label="最大入库数:">
                                    <span class="span-warning">{{checkInfo}}(本单据最大的入库数量)</span>
                                </el-form-item>
                                <el-form-item label="审核人:">
                                    <el-select v-model="stock.auditor_id"  @change="auditorSelect()" required placeholder="请选择审核人">
                                        <el-option
                                                v-for="item in selectInfo.auditor"
                                                :key="item.auditor_id"
                                                :label="item.auditor_name"
                                                :value="item.auditor_id">
                                        </el-option>
                                    </el-select>
                                </el-form-item>
                                <el-form-item label="生产班组:">
                                    <span>{{stock.task_group_name}}</span>
                                </el-form-item>
                                <el-form-item label="入库备注:">
                                    <el-input type="textarea" v-model="stock.tips"></el-input>
                                </el-form-item>
                            </el-form>
                            <table class="table table-striped table-hover table-bordered">
                                <tr>
                                    <th>物料型号</th>
                                    <th>入库数量</th>
                                    <th>默认入库仓库</th>
                                    <th>不良入库仓库</th>
                                </tr>
                                <tr>
                                    <td>{{stockMaterial.product_no}}</td>
                                    <td>
                                        <el-input
                                                v-model="stockMaterial.num"
                                                type="number"
                                        >
                                        </el-input>
                                    </td>
                                    <td>
                                        <el-select v-model="stockMaterial.default_rep_id" placeholder="请选择合格后进入的仓库">
                                            <el-option
                                                    v-for="key1 in selectInfo.warehouse"
                                                    :key="key1.warehouse_id"
                                                    :label="key1.warehouse_name"
                                                    :value="key1.warehouse_id">
                                            </el-option>
                                        </el-select>
                                    </td>
                                    <td>
                                        <el-select v-model="stockMaterial.fail_rep_id" placeholder="请选择不良进入的仓库">
                                            <el-option
                                                    v-for="key2 in selectInfo.warehouse"
                                                    :key="key2.warehouse_id"
                                                    :label="key2.warehouse_name"
                                                    :value="key2.warehouse_id">
                                            </el-option>
                                        </el-select>
                                    </td>
                                </tr>
                            </table>
                            <el-button @click="cancelSubmit">取 消</el-button>
                            <el-button type="primary" @click="submitStock">确 定</el-button>
                            </span>
                        </el-dialog>
                    </div>

                    <div class="table-responsive1">
                        <table class="table table-striped table-bordered table-hover table-full-width dataTables-productionList" id="productionPlan">
                            <thead>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive1" id="detailsModel">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#source" aria-controls="material" role="tab" data-toggle="tab">生产计划</a></li>
                            <li role="presentation"><a href="#task" aria-controls="material" role="tab" data-toggle="tab">完工入库情况</a></li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="source">
                                <table class="table table-striped table-bordered table-hover table-full-width dataTables-productionList">
                                    <thead>
                                    <tr>
                                        <th>计划单号</th>
                                        <th>生产型号</th>
                                        <th>物料编码</th>
                                        <th>生产数量</th>
                                        <th>已分配任务数量</th>
                                        <th>计划开始</th>
                                        <th>计划完成</th>
                                        <th>bom编号</th>
                                        <th>备注</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>{{orderTableData.production_code}}</td>
                                        <td>{{orderTableData.product_name}}</td>
                                        <td>{{orderTableData.product_no}}</td>
                                        <td>{{orderTableData.plan_number}}</td>
                                        <td>{{orderTableData.assign_number}}</td>
                                        <td>{{orderTableData.plan_start_time}}</td>
                                        <td>{{orderTableData.plan_end_time}}</td>
                                        <td>{{orderTableData.bom_id}}</td>
                                        <td>{{orderTableData.tips}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="task">
                                <table class="table table-striped table-bordered table-hover table-full-width dataTables-productionList">
                                    <thead>
                                    <tr>
                                        <th>入库单号</th>
                                        <th>产线班组</th>
                                        <th>生产物料</th>
                                        <th>物料型号</th>
                                        <th>入库数量</th>
                                        <th>库房</th>
                                        <th>更新时间</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="v in stockTableData">
                                        <td>{{v.stock_in_id}}</td>
                                        <td>{{v.production_group_name}}</td>
                                        <td>{{v.product_no}}</td>
                                        <td>{{v.product_name}}</td>
                                        <td>{{v.num}}</td>
                                        <td>{{v.repertory_name}}</td>
                                        <td>{{v.update_time}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="/Public/html/js/jquery.form.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<!--<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>-->
<!--<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/js/dataTables.bootstrap.min.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/dwin/finance/common_finance.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.4/index.js"></script>
<script>
    var oTable;
    var controller = "/Dwin/Production";
    var tableDiv = $("#productionPlan");
    var selectedID,selectedOrder;

    $(document).ready(function() {
        tableDiv.on('mouseenter','tbody td', function () {
            var tdIndex = $(this).parent()['context']['cellIndex'];
            if (tdIndex === 9) {
                var dataTips = $(this).find('span').attr('data');
                var num = $(this).parent();
                if (dataTips) {
                    layer.tips(
                        dataTips, num, {
                            tips: [1, '#3595CC'],
                            area: '900px',
                            time: 100000
                        });
                }
            } else {
                return false;
            }
        });
        tableDiv.delegate('tbody td', 'mouseleave',function(e) {
            layer.closeAll('tips');
        });

        oTable = tableDiv.DataTable({
        "scrollY": 400,
        // "scrollX": true,
        "scrollCollapse": true,
        "destroy"      : true,
        "autoWidth"	   : false,
        "lengthMenu"   : [10, 25, 50, 100],
        "bDeferRender" : true,
        "processing"   : true,
        "searching"    : true, //是否开启搜索
        "serverSide"   : true, //开启服务器获取数据
        "ajax"         :{ // 获取数据
            "url"   : controller + "/productionTaskIndex",
            "type"  : 'post',
            "data"  : {
                "lineLimit" : function () {
                    return document.getElementById('production_line').value;
                },
                'statusLimit' : function () {
                    return document.getElementById('production_status').value;
                }
            }
        },
        "order": [[ 7, "desc" ]],
        "columns": [
            {data:'task_id', title:'任务单号'},
            {data:'task_id', title:'源单'},
            {data:'task_line_name', title:'生产线'},
            {data:'task_group_name', title:'班组'},
            {data:'product_no', title:'物料编码'},
            {data:'task_number', title:'任务数'},
            {data:'complete_quantity', title:'完工数'},
            {data:'start_t', title:'开始时间'},
            {data:'end_t', title:'计划完成时间'},
            {data:'production_status', title:'状态'},
            {data:'actual_t', title:'实际完工'},
            {data:'tips' , title:'备注'}
        ],
        "columnDefs": [ //自定义列
            {
                "targets": 9,
                "data": 'tips',
                "render": function (data, type, row) {
                    arr = ['未生产','生产中', '完工'];
                    return arr[data];
                }
            }
        ],
        'fnRowCallback':function(nRow,aData,iDisplayIndex,iDisplayIndexFull){
            /*
                nRow:每一行的信息 tr  是Object
                aData：行 index
            */
            for(let key in nRow){
                var AD_sa = nRow['childNodes'][nRow['childNodes'].length - 3]
                if(AD_sa.innerText == '未生产'){
                    $(AD_sa).css('color','blue')
                }else if(AD_sa.innerText == '生产中'){
                    $(AD_sa).css('color','red')
                }else{
                    $(AD_sa).css('color','green')
                }
            }
        }
        });
        var vm0 = new Vue({
            el: "#buttonVm",
            data : function () {
                return {
                    preStock : [],
                    selectInfo : [],
                    stock : [],
                    stockMaterial : [],
                    taskUpdData : [],
                    centerDialogVisible : false,
                    selectData : []
                }
            },
            computed : {
                checkInfo : function () {
                    if (parseInt(this.stockMaterial.num) > (parseInt(this.preStock.task_number) - parseInt(this.preStock.complete_quantity))) {
                        this.stockMaterial.num = parseInt(this.preStock.task_number) - parseInt(this.preStock.complete_quantity);
                        this.$notify({
                            title: '您提交的入库数量超出了源单数量',
                            message: '警告',
                            type: 'warning'
                        });
                    }
                    this.taskUpdData.num = this.stockMaterial.num;
                    return parseInt(this.preStock.task_number) - parseInt(this.preStock.complete_quantity);
                }
            },
            methods: {
                refreshAction: function () {
                    oTable.ajax.reload(null, false);
                },
                getPreStock : function () {
                    this.preStock = [];
                    this.stock = [];
                    this.stockMaterial = [];
                    var numValidate = true;
                    var tmpTableData = {};
                    var tmpBaseData = {};
                    var tmpUpdData = {};
                    if ((this.selectData.complete_quantity - this.selectData.task_number) >= 0) {
                        numValidate = false;
                    }

                    if (numValidate) {
                        this.preStock = this.selectData;

                        tmpTableData.product_id = this.selectData.product_id;
                        tmpTableData.product_no = this.selectData.product_no;
                        tmpTableData.num = this.selectData.task_number - this.selectData.complete_quantity;
                        tmpTableData.default_rep_id = this.selectData.default_rep_id;
                        tmpTableData.fail_rep_id = this.selectData.default_rep_id;


                        tmpBaseData.source_id = this.selectData.id;
                        tmpBaseData.lineArr   = this.selectData.task_line  + "_" + this.selectData.task_line_name;
                        tmpBaseData.groupArr  = this.selectData.task_group + "_" + this.selectData.task_group_name;
                        tmpBaseData.task_group_name  = this.selectData.task_group_name;
                        tmpBaseData.source_id_string = this.selectData.task_id;
                        tmpBaseData.tips = this.selectData.tips;

                        tmpUpdData.id  = this.selectData.id;
                        tmpUpdData.num = this.selectData.task_number - this.selectData.complete_quantity;

                        this.taskUpdData = tmpUpdData;
                        this.stock = tmpBaseData;
                        this.stockMaterial = tmpTableData;
                        if (this.preStock.id) {
                            $.post('/Dwin/Stock/getSelectInfo', {
                                taskId : 'getSelect'
                            },function (res) {
                                if (res.status !== 200) {
                                    layer.msg(res.msg);
                                    vm0.centerDialogVisible = false;
                                    return false;
                                }
                                vm0.selectInfo = [];
                                vm0.selectInfo   = res.data;
                                vm0.centerDialogVisible = true;
                            });
                        } else {
                            layer.msg('您没选中要下推入库的生产任务');
                        }
                    } else {
                        layer.msg('已入库完毕');
                    }
                },
                selectionArr : function (id, arr, obj, objName) {
                    var name = "";
                    for (var i = 0; i < arr.length; i++) {
                        if (id === arr[i].id) {
                            name = arr[i].name;
                        }
                    }
                    Vue.set(obj,objName, id + "_" + name);
                },
                warehouseSelect: function(){
                    var id = this.stockMaterial.warehouse_id;
                    var warehouse = this.selectInfo.warehouse;
                    var name = '';
                    for (var i = 0; i < warehouse.length; i++){
                        if (id === warehouse[i].warehouse_id){
                            name = warehouse[i].warehouse_name
                        }
                    }
                    Vue.set(this.stockMaterial, 'warehouseArr', id + '_' + name)
                },
                auditorSelect: function () {
                    this.selectionArr(this.stock.auditor_id, this.selectInfo.auditor, this.stock, 'auditorArr');
                    var id = this.stock.auditor_id;
                    var auditor = this.selectInfo.auditor;
                    var name = '';
                    for (var i = 0; i < auditor.length; i++){
                        if (id === auditor[i].auditor_id){
                            name = auditor[i].auditor_name
                        }
                    }
                    Vue.set(this.stock, 'auditorArr', id + '_' + name)
                },
                checkSelect: function () {
                    this.selectionArr(this.stock.check_id, this.selectInfo.staff, this.stock, 'checkArr');
                },
                keepSelect : function () {
                    this.selectionArr(this.stock.keep_id, this.selectInfo.staff, this.stock, 'keepArr');
                },
                beforeSubmit : function(){
                    if (!vm0.stock.auditorArr) {
                        layer.msg('未选择审核人');
                        return false;
                    }
                    if (!vm0.stockMaterial.default_rep_id) {
                        layer.msg('未选择默认仓库');
                        return false;
                    }
                    if (!vm0.stockMaterial.fail_rep_id) {
                        layer.msg('未选择不良仓库');
                        return false;
                    }
                    if (!this.stockMaterial.num) {
                        layer.msg('入库数量有问题');
                        return false;
                    }
                    if (this.stockMaterial.num < 0 || this.stockMaterial.num > (parseInt(this.preStock.task_number) - parseInt(this.preStock.complete_quantity))) {
                        layer.msg('入库数非法');
                        return false;
                    }
                    return true;
                },
                cancelSubmit : function () {
                    this.centerDialogVisible = false;
                },
                submitStock : function () {
                    var flag = true;
                    if (!flag) {
                        return false;
                    } else {
                        $.ajax({
                            url : "/Dwin/Stock/addStockInWithProduction",
                            type : 'post',
                            data : {
                                base : this.stock,
                                material : this.stockMaterial,
                                updTask : this.taskUpdData
                            },
                            success: function (res) {
                                if (res.status == 200) {
                                    vm0.centerDialogVisible = false;
                                    oTable.ajax.reload(null, false);
                                    vm0.$notify({
                                        title: '成功',
                                        message: '成功添加',
                                        type: 'success'
                                    });
                                } else {
                                    vm0.$notify({
                                        title: '报错',
                                        message: res.msg,
                                        type: 'warning'
                                    });
                                }
                            }
                        })
                    }

                }
            }
        });

        var planTBody = $("#productionPlan tbody");
        planTBody.on("click", "tr", function () {

            $("tr").removeClass('selected');
            $(this).addClass('selected');
            vm0.selectData = oTable.row(this).data();
            selectedID = oTable.row(this).data().id;
            selectedOrder = oTable.row(this).data().production_order;
            $.post('getRelationDataWithTaskId', {'productionTaskId': selectedID}, function (res) {
                vm.orderTableData = res.sourceOrder;
                vm.stockTableData = res.stockInData;
            });
        });
    });
    $(".change-data").on('change', function () {
        oTable.ajax.reload();
    });
    $('.refresh').on('click', function () {
        oTable.ajax.reload();
    });

    // selectInfo 下拉选择内容
    // stockMaterial 入库产品信息
    var vm = new Vue({
        el: '#detailsModel',
        data: function () {
            return {
                orderTableData : [],
                stockTableData : []
            }
        },
        filters: {
            auditType: function (data) {
                var arr = ['单据审核', '产线确认'];
                return arr[data-2]
            },
            auditResult: function (data) {
                var arr = ['通过', '不通过'];
                return arr[data-1]
            }
        }
    });

    // 当dataTables变动时取消选中
    $('table').on('processing.dt', function () {
        selectedID = undefined;
        $('tr').removeClass('selected')
    });

</script>
</body>
</html>