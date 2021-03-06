<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出入库记录</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdn.bootcss.com/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;
        }

        .hiddenDiv {
            display: none;
        }

        .selected {
            background: #d0d27e !important;
        }
        #app .el-row{
            margin: 20px 0;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>产品出入库登记</h5>
                </div>
                <div class="ibox-content">
                    <form class="form-inline">
                        <div class="col-xs-1">
                            <button type="button" class="btn btn-outline btn-success i_stock"><span
                                    class="glyphicon glyphicon-log-in"></span>入库
                            </button>
                        </div>
                        <div class="col-xs-1">
                            <button type="button" class="btn btn-primary processAudit auditPass">审核</button>
                        </div>
                        <div class="col-xs-1">
                            <button type="button" class="btn btn-warning del">删除</button>
                        </div>
                        <div class="col-xs-3">
                            <label for="warehouse">库房筛选</label>
                            <select name="" id="warehouse" class="form-control audit_type">
                                <option value="">所有</option>
                                <?php if(is_array($repoData)): $i = 0; $__LIST__ = $repoData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["rep_id"]); ?>"><?php echo ($vol["repertory_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                        <div class="col-xs-3">
                            <label>审核所有</label>
                            <select name="" id="status" class="form-control audit_type">
                                <option value="1,2">所有</option>
                                <option value="1">待审核</option>
                                <option value="2">审核通过</option>
                            </select>
                        </div>
                        <button class="btn btn-primary showVue" type="button">显示高级搜索</button>
                    </form>
                    <div id="app" class="form-inline" v-if="show">
                        <el-row>
                            <el-col :span="8" :offset="10">
                                <el-radio v-model="condition" label="AND">AND</el-radio>
                                <el-radio v-model="condition" label="OR">OR</el-radio>
                            </el-col>
                        </el-row>
                        <el-row v-for="aa in item">
                            <el-col :span="2" :offset="8">
                                <label>{{aa.label}}</label>
                            </el-col>
                            <el-col :span="2">
                                <select v-model="aa.map" class="form-control">
                                    <option value="EQ" >等于</option>
                                    <option value="GT" >大于</option>
                                    <option value="EGT" >大于等于</option>
                                    <option value="LT" >小于</option>
                                    <option value="ELT" >小于等于</option>
                                    <option value="NEQ" >不等于</option>
                                    <option value="LIKE" >模糊查询</option>
                                </select>
                            </el-col>
                            <el-col :span="4">
                                <input type="text" class="form-control" :placeholder="aa.label" v-model="aa.value">
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="8" :offset="11">
                                <button class="btn btn-primary" @click="search">搜索</button>
                            </el-col>
                        </el-row>
                    </div>

                    <div class="ibox-content" style="margin-top: 15px;">
                        <table id="table" class="table table-striped table-bordered table-full-width" width="100%">
                            <thead>
                            <tr>
                                <th>单据编号</th>
                                <th>物料型号</th>
                                <th>库房</th>
                                <th>数量</th>
                                <th>出/入库类型</th>
                                <th>单据备注</th>
                                <th>制单人</th>
                                <th>审核人</th>
                                <th>审核备注</th>
                                <th>订单号</th>
                                <th>更新时间</th>
                                <th>审核状态</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="https://cdn.bootcss.com/element-ui/2.3.6/index.js"></script>
<script>
    var curentId = "<?php echo session('staffId');?>";
    var table = $('#table').DataTable({
        serverSide: true,
        ajax: {
            type: 'post',
            data: {
                repertory_id: function () {
                    return document.getElementById('warehouse').value;
                },
                status: function () {
                    return $('#status').val()
                }
            }
        },
        order: [[10, 'desc']],
        columns: [
            {data: 'audit_order_number', searchable: true},
            {data: 'product_name', searchable: true},
            {data: 'warehouse_name', searchable: false},
            {data: 'num', searchable: false},
            {data: 'cate_name', searchable: false},
            {data: 'tips', searchable: false},
            {data: 'proposer_name', searchable: false},
            {data: 'auditor_name', searchable: false},
            {
                data: 'audit_tips', searchable: false, render: function (data, a, row) {
                    if (row.audit_status === '1' && row.auditor === curentId) {
                        return '<input class="form-control tips" placeholder="输入审核备注">'
                    } else {
                        return data
                    }
                }
            },
            {data: 'action_order_number', searchable: false},
            {data: 'update_time', searchable: false},
            {data: 'audit_status', render: function (data) {
                var arr = ['', '未审核', '审核通过', '审核不通过']
                    return arr[data]
                }}
        ]
    });
    var productName, id;
    $('.i_stock').on('click', function () {
        layer.open({
            type: 2,
            title: "请选择要入库的产品",
            area: ['100%', '100%'],
            content: "<?php echo U('production/chooseProduct');?>",
            btn: ['确定', '取消'],
            yes: function (index) {
                openIStock(productInfo);
                layer.close(index)
            }
        });
    });

    function openIStock(productInfo) {
        layer.open({
            type: 2,
            title: "",
            area: ['70%', '70%'],
            content: "/Dwin/Stock/addAudit?product_name=" + productInfo.product_name + '&product_id=' + productInfo.product_id + '&type=' + 1,
            end: function () {
                table.ajax.reload();
            }
        });
    }
    var selectedData = null
    $('tbody').on('click', 'tr', function () {
        $(this).toggleClass('selected')
        selectedData = table.rows('.selected').data()

    })
    $('.processAudit').on('click', function () {
        if (selectedData === null) {
            return layer.msg('你没有选择要审核的项')
        }
        var lock = false;
        layer.confirm('是否通过?', {
            btn: ['通过', '不通过']
        }, function (index, layero) {
            if (lock === false) {
                lock = true;
                submitAudit(2);
                layer.close(index);
            }
        }, function (index) {
            if (lock === false) {
                lock = true;
                submitAudit(3);
                layer.close(index)
            }

        });

    });

    function submitAudit(res) {
        var str = res === 2 ? '通过' : '不通过'
        if (selectedData.length === 0) {
            layer.msg('你没有选择要审核的项')
        } else {
            var lock = false;
            layer.confirm('确认' + str + '该申请?', function (index) {
                $('.processAudit').attr('disabled', true)
                if (lock === false) {
                    lock = true;
                    var allData = table.data();
                    var data = [];
                    selectedData.each(function (v,i,arr) {
                        if (v.auditor === curentId && v.audit_status === '1') {
                            var index = allData.indexOf(v)
                            var obj = {
                                auditID: v.id,
                                audit_status: res,
                                tips: $('tbody tr').eq(index).find('.tips').val()
                            }
                            data.push(obj)
                        }
                    })
                    $.post('editStockAuditStatusMulti', {data: data}, function (res) {
                        $('.processAudit').attr('disabled', false)
                        layer.msg(res.msg);
                        table.ajax.reload();
                        layer.close(index);
                    })
                }

            })
        }
    }

    $('table').on('processing.dt', function () {
        $('tr').removeClass('selected')
        selectedData = null
    })
    $('form select').on('change', function () {
        table.settings()[0].ajax.data = {
            repertory_id: $('#warehouse').val(),
            status: $('#status').val()
        };
        table.ajax.reload()
    })

    $('.del').on('click', function () {
        if (selectedData !== null){
            layer.confirm('确认删除?', function (index) {
                var obj = []
                selectedData.each(function (v) {
                    obj.push(v)
                })
                $.post('delStockInItem', {data: obj}, function (res) {
                    if (res.status > 0){
                        table.ajax.reload()
                    }
                    layer.msg(res.msg)
                })

                layer.close(index)
            })
        } else {
            layer.msg('请至少选择一行')
        }
    })

</script>
<script>
    var vm = new Vue({
        el: '#app',
        data: function () {
            return {
                show: false,
                condition: 'AND',
                itemK: [
                    'audit_order_number',
                    'product_name',
                    'type',
                    'proposer_name',
                    'auditor_name',
                    'action_order_number',
                    'putin_production_line_name'
                ],
                itemV: [
                    '单据编号',
                    '物料型号',
                    '入库类型',
                    '制单人',
                    '审核人',
                    '订单号',
                    '入库产线'
                ],
                item:[]
            }
        },
        created: function () {
            var obj
            for (var i = 0; i < this.itemK.length; i++){
                obj = {}
                obj.key = this.itemK[i]
                obj.label = this.itemV[i]
                obj.value = ''
                obj.map = 'EQ'
                this.item.push(obj)
            }
        },
        methods: {
            search: function () {
                var newItem = JSON.parse(JSON.stringify(this.item));
                for (var i = newItem.length-1; i >= 0; i--){
                    if (!newItem[i].value){
                        newItem.splice(i, 1)
                    }
                }
                var obj = {
                    option    : this.condition,
                    condition : newItem
                }
                table.settings()[0].ajax.data.vueData = obj;
                table.ajax.reload()
            }
        }
    })
    $('.showVue').on('click', function () {
        if (vm.show) {
            table.settings()[0].ajax.data.vueData = [];
            table.ajax.reload();
            $(this).text('显示高级搜索')
        }else {
            table.settings()[0].ajax.data.vueData = [];
            table.ajax.reload();
            $(this).text('隐藏高级搜索')
        }
        vm.show = !vm.show
    })
</script>
</body>
</html>