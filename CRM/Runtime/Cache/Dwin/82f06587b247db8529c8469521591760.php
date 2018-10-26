<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>库存管理</title>
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
        .Excel_but{
            padding: 7px 13px;
            border: 1px solid #e5e6e7;
            background-color: #fff;
            font-weight: bold;
            border-radius: 1px;
            color: inherit;
            margin-left: 20px;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h3>库存管理</h3>
                </div>
                <div class="ibox-content">
                    <label for="">根据库房筛选:
                    <select name="" class="form-control repoSearch" id="repertorySelection">
                        <option value="">全部</option>
                        <?php if(is_array($repoList)): foreach($repoList as $key=>$item): ?><option value="<?php echo ($item["id"]); ?>"><?php echo ($item["name"]); ?></option><?php endforeach; endif; ?>
                    </select>
                    </label>
                    <!-- <label for="" style="margin-left: 20px;" style="border:1px solid #1c84d6"> -->
                        <button type="button" class="Excel_but" onclick="exportToExcel()">
                            导出Excel
                        </button>
                    <!-- </label> -->
                    <div class="ibox-content" style="margin-top: 15px;">
                        <table id="table" class="table table-striped table-bordered table-full-width" width="100%">
                            <thead>
                                <tr>
                                    <th>物料编号</th>
                                    <th>型号</th>
                                    <th>实际库存</th>
                                    <th>剩余库存</th>
                                    <th>锁库数量</th>
                                    <th>出库中数量</th>
                                    <th>待入库</th>
                                    <th>在生产数量</th>
                                    <th>在返工数量</th>
                                    <th>更新时间</th>
                                    <!--<th>操作</th>-->
                                </tr>
                            </thead>
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
<script>
    $(document).ready(function () {
        var repSelection = $("#repertorySelection");
        var table = $('#table').DataTable({
            serverSide: true,
            ajax: {
                url: 'stockIndex',
                type: 'post',
                data: {
                    'repoID': function () {
                        return document.getElementById('repertorySelection').value;
                    }
                }
            },
            order:[[4,'desc']],
            columns: [
                {data : 'product_no',   searchable:true},
                {data : 'product_name', searchable:true},
                {data : 'all_number',    searchable:false},
                {data : 'stock_number',  searchable:false},
                {data : 'o_audit',       searchable:false},
                {data : 'out_processing',searchable:false},
                {data : 'i_audit',       searchable:false},
                {data : 'production_number',searchable:false},
                {data : 'rework_number', searchable:false},
                {data : 'update_time',   searchable:false}
                // {data: null,defaultContent: "<?php if($btn): ?><button style='display: none;' class='btn btn-success btn-xs i-btn' type='button'>入库</button> <button class='btn btn-success btn-xs o-btn' type='button'>出库</button><?php endif; ?>",orderable: false}
            ]
        });

        repSelection.on('change', function () {
            table.ajax.reload();
        });
    });

    function exportToExcel(){
        var rep_ID = document.getElementById('repertorySelection').value;
        $.post('/Dwin/Stock/stockExportToExcel', {rep:rep_ID}, function (res) {
            if (res.status != 200) {
                vm.$message({
                    showClose:true,
                    message:res.msg,
                    type:'success'
                })
            } else {
                if (res.data.file_url) {
                    var bomUrl = res.data.file_url;
                    layer.confirm('是否确认下载？', {
                        btn: ['确认','取消']
                    }, function(){
                        window.open(bomUrl);
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
    }
</script>
</body>
</html>