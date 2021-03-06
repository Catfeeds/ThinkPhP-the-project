<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>产品修改审核</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;
        }
        .hiddenDiv{
            display: none;
        }
        a{
            border: none!important;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content tab-content">
                    <form class="form-inline">
                        <label for="">根据审核状态筛选</label>
                        <select name="" class="form-control auditStatusSelect">
                            <option value="1">未审核</option>
                            <option value="2">审核通过</option>
                            <option value="3">审核未通过</option>
                        </select>
                    </form>
                    <table id="productAuditTable" class="table table-striped table-bordered table-full-width" width="100%">
                        <thead>
                        <tr>
                            <th>产品名称</th>
                            <th>产品型号</th>
                            <th>申请人</th>
                            <th>申请时间</th>
                            <th>申请状态</th>
                            <th>审核人</th>
                            <th>查看</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/Admin/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    var auditStatus = $('.auditStatusSelect').val();
    $('.auditStatusSelect').on('change', function () {
        auditStatus = $(this).val();
        productAuditTable.settings()[0].ajax.data.mySearch.audit_status = auditStatus;
        productAuditTable.ajax.reload()
    });
var productAuditTable = $('#productAuditTable').DataTable({
    serverSide: true,
    ajax: {
        type: 'post',
        data: {
            mySearch: {
                audit_status: auditStatus,
                changemanid: 'myID'
            }
        }
    },
    order:[[3,'desc']],
    columns: [
        {data: 'newproduct_number',searchable:true},
        {data: 'product_name',searchable:false},
        {data: 'changemanname',searchable:false},
        {data: 'create_time',searchable:false},
        {
            data: 'audit_status', searchable: false, render: function (data) {
                var arr = ['', '未审核', '审核通过', '审核不通过'];
                return arr[+data];
            }
        },
        {data: 'auditor_name',searchable:false},
        {data: null,render:function (data,type,row) {
                if (row.audit_status != 3){
                    return "<button class='btn btn-success btn-xs info' data-id='"+row.id+"' type='button'>查看详情</button>"
                }else {
                    return "<button class='btn btn-success btn-xs edit' data-id='"+row.id+"' type='button'>修改申请</button>" + "&emsp;<button class='btn btn-danger btn-xs delete' data-id='"+row.id+"' type='button'>删除申请</button>"
                }
            } ,orderable: false}
    ]
});
$('#productAuditTable').on('click', '.info', function () {
    var id = $(this).attr('data-id');
    layer.open({
        type: 2,
        title: "查看详情",
        area : ['70%', '100%'],
        content: '<?php echo U("product/patchEditProductRequest","","");?>' + "/id/"+id,
        end: function () {
            productAuditTable.ajax.reload();
        }
    });
});

    $('#productAuditTable').on('click', '.edit', function () {
        var id = $(this).attr('data-id');
        var auditLayer = layer.open({
            type: 2,
            title: "修改申请",
            area : ['70%', '100%'],
            content: '<?php echo U("product/putEditProductRequest","","");?>' + "/id/"+id,
            end: function () {
                productAuditTable.ajax.reload();
            }
        });
    });
    $('#productAuditTable').on('click', '.delete', function () {
        var id = $(this).attr('data-id');
        layer.confirm('确认删除?数据删除后不可恢复', {icon: 3, title:'确认删除'}, function(index){
            $.post('<?php echo U("product/deleteEditProductRequest","","");?>', {id:id}, function (res) {
                layer.msg(res.msg, function () {
                    if (res.status > 0){
                        productAuditTable.ajax.reload();
                        parent.layer.close(index);
                    }
                });
            });
            layer.close(index);
        });
    })
</script>
</body>
</html>