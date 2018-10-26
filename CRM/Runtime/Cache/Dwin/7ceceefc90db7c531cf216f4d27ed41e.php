<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存报警</title>
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

        .hiddenDiv {
            display: none;
        }
        .yellowBg {
            color:red;
            background: skyblue !important;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>库存报警</h5>
                </div>
                <div class="ibox-content">
                    <div class="ibox-content" style="margin-top: 15px;">
                        <table id="table" class="table table-striped table-bordered table-full-width" width="100%">
                            <thead>
                            <tr>
                                <th>产品id</th>
                                <th>型号</th>
                                <th>库存数量</th>
                                <th>安全库存</th>
                                <th>报警数量</th>
                                <th>待入库</th>
                                <th>待出库</th>
                                <th>在生产数量</th>
                                <th>在返工数量</th>
                                <th>更新时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
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
    $(function () {
        var auth = <?php echo ($editAble); ?>;
        var $table = $('#table').DataTable({
            serverSide: true,
            ajax: {
                type: 'post'
            },
            order: [[9, 'desc']],
            columns: [
                {data: 'product_id', searchable: true},
                {data: 'product_name', searchable: true},
                {data: 'stock_number', searchable: false},
                {data: 'safety_number', searchable: false},
                {data: 'warning_number' , searchable: false },
                {data: 'i_audit', searchable: false},
                {data: 'o_audit', searchable: false},
                {data: 'production_number', searchable: false},
                {data: 'rework_number', searchable: false},
                {data: 'update_time', searchable: false},
                {
                    data: null,
                    defaultContent: "<button class='btn btn-success btn-xs edit_btn' type='button'>修改</button> <button class='btn btn-success btn-xs save_btn' type='button' style='display: none'>保存</button>",
                    orderable: false
                }
            ]
        });

        $table.on('draw',function () {
            if (!auth){
                $('.btn').attr('disabled', true)
            }
            var $trs = $(this).children('tbody').children('tr');
            $.each($trs, function (k,v) {
                var securityTd = $(v).children('td').eq(4);
                var alarmTd = $(v).children('td').eq(5);
                var stockTd = $(v).children('td').eq(3);
                if (securityTd.html() !== '' && stockTd.html() !== ''){
                    if (+securityTd.html() >= +stockTd.html()){
                        alarmTd.html(securityTd.html() - stockTd.html());
                        $(v).addClass('yellowBg')
                    }
                }
            })
        });

        $('#table').on('click', '.edit_btn', function () {
            if (!auth){
                return false
            }
            $(this).next('.save_btn').show();
            $(this).hide();
            var securityTd = $(this).parents('tr').children('td').eq(4);
            var securityValue = securityTd.text();
            securityTd.attr('data-securityValue', securityValue);
            var securityInput = '<input type="number" class="form-control" value="' + securityValue + '" style="width: 100%">';
            securityTd.html(securityInput);
        });
        $('#table').on('click', '.save_btn', function () {
            if (!auth){
                return false
            }
            $(this).prev('.edit_btn').show();
            $(this).hide();
            var tr = $(this).parents('tr');
            var securityTd = $(this).parents('tr').children('td').eq(4);
            var securityValue = securityTd.children('input').val();
            var product_id = $(this).parents('tr').children('td').eq(0).html();
            var product_number = $(this).parents('tr').children('td').eq(3).html();
            $.ajax({
                type: 'post',
                data: {
                    chanpinid: product_id,
                    anquanshuliang: securityValue,
                    method: 'edit'
                },
                success: function (data) {
                    if (data.status > 0) {
                        securityTd.html(securityValue);
                        layer.msg(data.msg, {icon: 6, time: 1500, shade: 0.1}, function (index) {
                            layer.close(index);
                        });
                    } else {
                        securityTd.html(securityTd.attr('data-securityValue'));
                        layer.msg(data.msg, {icon: 5, time: 1500, shade: 0.1}, function (index) {
                            layer.close(index);
                        });
                    }
                    $table.ajax.reload();
                }
            })
        })
    })


</script>
</body>
</html>