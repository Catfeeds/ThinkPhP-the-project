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
            background: #b4b65c!important;
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
        /* .el-dialog__body{
            text-align: center
        } */
        /* 上传 */
        .uploadResume .el-upload .el-upload__input{
           display: none !important;
       }
       table tr{
           height: 35px;
       }
       #staff tr td:hover{
           background-color: #ccc
       }
       .colorBG{
           background-color: red
       }
       .float-e-margins .btn {
            margin-bottom: 3px !important;
        }
       .ele-BUT{
           display: inline-block;
           font-size: 12px;
           height: 21px;
            color: #1c84c6;
            border: 1px solid #1c84c6;
            border-radius:3px;
       }
       .form-control{
           padding: 0;
       }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="ibox float-e-margins">
        <div class="ibox-content">
                <div class="title">
                        <h4>湖南迪文有限公司已出库单列表</h4>
                        <div>
                            <button class="btn btn-xs btn-outline btn-success refresh">刷 新</button>
                            <button class="btn btn-xs btn-outline btn-success details_staff"><span class="glyphicon glyphicon-cloud-upload"></span>详 情</button>
                            <!-- <button class="btn btn-xs btn-outline btn-success audit_staff"><span class="glyphicon glyphicon-adjust"></span>入库完成</button> -->
                            <!-- <button class="btn btn-xs btn-outline btn-success edit_staff"><span class="glyphicon glyphicon-plus"></span>其他出库新增</button> -->
                            <!-- <button class="btn btn-xs btn-outline btn-success delete_staff"><span class="glyphicon glyphicon-remove"></span>其他出库删除</button> -->
                            <!-- <button class="btn btn-xs btn-outline btn-success revamp_staff"><span class="glyphicon glyphicon-align-justify"></span>其他出库修改</button> -->
                            <button class="btn btn-xs btn-outline btn-success rollback_staff"><span class="glyphicon glyphicon-print"></span>回退物料</button>
                            <button class="btn btn-xs btn-outline btn-success rollbackAll_staff"><span class="glyphicon glyphicon-print"></span>回退单据</button>
                            <button class="btn btn-xs btn-outline btn-success indent_staff"><span class="glyphicon glyphicon-print"></span>下载单据</button>
                            <select class="form-control chosen-select btn-outline ele-BUT push_down" id="select_vol" name="userId" id="useId" style="width:9%;" tabindex="2">
                                <option value="">--筛选类型--</option>
                                <?php if(is_array($cateMap)): $i = 0; $__LIST__ = $cateMap;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($vol); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
            <div class="table-responsive">
                <table id="staff" class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th>出库单编号</th>
                            <th>出库单类型</th>
                            <th>出库状态</th>
                            <th>创建人</th>
                            <th>审核人</th>
                            <th>创建时间</th>
                            <th>发货人</th>
                            <th>源单编号</th>
                            <th>打印次数</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="ibox-content" id="app">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation"><a href="#contact" aria-controls="contact" role="tab" data-toggle="tab">出库物料单</a></li>
                    <li role="presentation"><a href="#record" aria-controls="record" role="tab" data-toggle="tab">获取出库记录</a></li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="contact">
                        <table class="table table-striped table-hover table-border">
                            <tr>
                                <th>物料编号</th>
                                <th>物料型号</th>
                                <th>物料名称</th>
                                <th>默认出入仓库</th>
                                <th>制单出入库数量</th>
                                <th>创建时间</th>
                                <th>更新时间</th>
                            </tr>
                            <tr v-for="item in bomInfo">
                                <td>{{item.product_no  || ''}}</td>
                                <td>{{item.product_name  || ''}}</td>
                                <td>{{item.product_number  || ''}}</td>
                                <td>{{item.qualified_repertory_name  || ''}}</td>
                                <td>{{item.num  || ''}}</td>
                                <td>{{formatDateTime(item.create_time) || ''}}</td>
                                <td>{{formatDateTime(item.update_time) || ''}}</td>
                            </tr>
                        </table>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="record">
                        <table class="table table-striped table-hover table-border">
                            <tr>
                                <th>物料编号</th>
                                <th>出库库房</th>
                                <th>出库数量</th>
                                <th>审核状态</th>
                                <th>创建时间</th>
                                <th>更新时间</th>
                            </tr>
                            <tr v-for="item in record_info">
                                <td>{{item.product_no  || ''}}</td>
                                <td>{{item.qualified_repertory_name  || ''}}</td>
                                <td>{{item.num  || ''}}</td>
                                <td>{{this.recordAuditMap[item.status]  || ''}}</td>
                                <td>{{formatDateTime(item.create_time)  || ''}}</td>
                                <td>{{formatDateTime(item.update_time) || ''}}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <!-- 审核 弹框 -->
                <!-- <el-dialog title="其他出库单审核：" class="selsctDialog" :visible.sync="dialogVisible" width="30%">
                   <el-button type="primary" @click="eleClick_that(2)">合 格</el-button>
                   <el-button type="primary" @click="eleClick_that(1)" style="margin-left: 54px">不合格</el-button>
               </el-dialog> -->
               <!-- 审 ==================== 核 -->
               <el-dialog
                    title="提示"
                    :visible.sync="dialogVisible"
                    width="30%"
                    >
                    <span>入库是否完成？</span>
                    <span slot="footer" class="dialog-footer">
                        <el-button @click="dialogVisible = false">取 消</el-button>
                        <el-button type="primary" @click="reviewButton">确 定</el-button>
                    </span>
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
    var audit_status = <?php echo (json_encode($auditMap)); ?>;  //出库单状态
    var repMap = <?php echo (json_encode($repMap)); ?>;  //库房名称
    var cateMap = <?php echo (json_encode($cateMap)); ?>;  //出库单类型
    var recordAuditMap = <?php echo (json_encode($recordAuditMap)); ?>;  //出库单类型
    var table = $('#staff'). DataTable({
        ajax: {
            type: 'post',
            data: {
                source_kind () {
                    elseId = undefined
                    var select = document.getElementById('select_vol')
                    if(select.value == 0){
                        return ''
                    }else{
                        return Number(select.value)
                    }
                },
                flag: 1
            }
        },
        "scrollY": 400,
        "scrollX": true,
        "scrollCollapse": true,
        "destroy"      : true,
        "paging"       : true,
        "autoWidth"	   : true,
        "pageLength": 25,
        serverSide: true,
        // order:[[21, 'desc']],
        columns: [                                                                                                                       
            {searchable: true, data: 'stock_out_id'},
            {searchable: false, data: 'source_kind',render: function (data){if(data != null){return cateMap[+data]}else{return ' '}}},
            {searchable: false, data: 'audit_status',render: function (data){if(data != null){return audit_status[+data]}else{return ' '}}},
            {searchable: true, data: 'create_name'},
            {searchable: true, data: 'audit_name'},
            {searchable: true, data:  'create_time',render: function(data){return vm.formatDateTime(data)}},
            {searchable: true, data: 'send_name'},
            {searchable: true, data: 'source_id'},
            {searchable: true, data: 'printing_times'},
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
    var elseId
    var else_id
    var contractId
    var else_status
    var source_kind
    var ApplyData
    $('tbody').on('click', 'tr', function () {
        elseData = table.row(this).data();
        // contractId = ApplyData.contract_pid
        elseId = elseData.id;   // 订单主键
        else_id = elseData.bom_id      //订单编号
        else_status = elseData.audit_status
        source_kind  = elseData.source_kind;
        $('tr').removeClass('selected')
        $(this).addClass('selected')
        $.post('/Dwin/Stock/stockOutOtherMaterial', {id: elseId}, function (res) {
            vm.bomInfo = res.data
        })
        $.post('/Dwin/Stock/getStockOutRecord', {id: elseId},function (res) { 
            vm.record_info = res.data
        })
    })
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                recordAuditMap:recordAuditMap,
                record_info:[],
                bomInfo:[],
                dialogVisible:false,
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
                },
                purchaseUrl:''
            }
        },
        methods: {
           // 合格/不合格
           reviewButton(){
                var data={"id":elseId,"source_kind":source_kind}
                $.post('<?php echo U("/Dwin/Stock/auditWholeStockOut");?>', data , function (res) {
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
    // 出库单下推
    $('.push_down').on('change', function () {
        table.ajax.reload()
    })
    // updata Execl
    $('.indent_staff').on('click', function () {
        if (elseId === undefined){
            layer.msg('请选择一个出库申请单')
            return false;
        }
        var index = layer.confirm("确认打印该单据？",{
            btn  : ['确认', '取消'],
            icon : 6
        }, function () {
            layer.open({
                type: 2,
                title: '单据打印',
                shadeClose: true,
                end: function () {
                    table.ajax.reload(false, null);
                },
                area: ['220mm', '110mm'],
                content: 'printOutHtml?id=' + elseId + "&source_kind=" + source_kind //iframe的url
            });
            layer.close(index);
        });
    })
    // 回退
    $('.rollback_staff').on('click', function () {
        if (elseId === undefined){
            layer.msg('请选择一个出库单')
            return false;
        }
        var index = layer.confirm("该出库单已出库是否要去回退？",{
            btn  : ['确认', '取消'],
            icon : 5
        }, function () {
            layer.open({
                type: 2,
                title: '出库单回退',
                shadeClose: true,
                area: ['90%','90%'],
                content: '/Dwin/Stock/rollBackMaterial?id=' + elseId +"&source_kind=" + source_kind,
                end: function () {
                    table.draw(false);
                },
            });
            layer.close(index);
        });
    })
    // 回退单据
    $('.rollbackAll_staff').on('click', function () {
        if (elseId === undefined){
            layer.msg('请选择一个出库单据')
            return false;
        }
        var index = layer.confirm("确定要回退整个单据？",{
            btn  : ['确认', '取消'],
            icon : 5
        }, function () {
            var data={"id":elseId,"source_kind":source_kind}
            $.post('/Dwin/Stock/rollBackStockOut', data , function (res) {
                if(res.status == 200){
                    table.draw(false);
                    layer.close(index);
                }else{
                    layer.msg(res.msg)
                }
            })
        });
    })
    // 详情页
    $('.details_staff').on('click', function () {
        if (elseId === undefined){
            layer.msg('请选择一个出库申请单')
        } else {
            var index = layer.open({
                type: 2,
                title: '湖南迪文有限公司出库单详情',
                content: '/Dwin/Stock/getStockOutDetail?id=' + elseId +'&source_kind=' + source_kind,
                area: ['90%', '90%'],
                shadeClose:true,
                end: function () {
                    table.ajax.reload()
                }
            })    
        }
    })
    // 其他出库单 修改
    $('.revamp_staff').on('click', function () {
        if(elseId == undefined){
            layer.msg('请选择一个出库申请单')
        }else{
            if(else_status == '3'){
                layer.msg('该库房审核已完毕，不能修改！')
            }else{
                var index = layer.open({
                    type: 2,
                    title: '湖南迪文有限公司出库申请单修改',
                    content: '/Dwin/Stock/editOtherStockOut?id='+ elseId,
                    // content: '/Dwin/Stock/editOtherStockOutApply?id='+ ,
                    area: ['90%', '90%'],
                    shadeClose:true,
                    end: function () {
                        table.ajax.reload()
                    }
                }) 
            }
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
        table.order([[5, 'desc']])
        table.ajax.reload()
    })
    // 审核
    $('.audit_staff').on('click', function () {
        if (elseId === undefined){
            layer.msg('请选择一家供应商')
        } else {
            vm.dialogVisible = true
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