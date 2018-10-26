<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>产品管理</title>
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
        .ele-BUT{
            display: inline-block;
            font-size: 12px;
            height: 21px;
            width: auto;
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
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h3>产品管理</h3>
                </div>
                <div class="ibox-content">
                    <div class="form-inline pull-left">
                        <button class="btn btn-success add_screen_cate btn-sm">添加物料类别</button>&emsp;
                        <button class="btn  btn-sm btn-primary addProduct">添加新物料</button>&emsp;
                        <div style="display: inline;" class="encapsulated">
                            <button class="btn btn-sm btn-info piliangedit">批量修改</button>
                            <button class="btn btn-sm btn-success piliangsave" style="display: none">批量保存</button>&emsp;
                            <select name="" class="form-control chooseAuditor" id="">
                                <option value="">请选择修改审核人</option>
                                <?php if(is_array($auditor)): foreach($auditor as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>_<?php echo ($vo["name"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; ?>
                            </select>
                        </div>
                        <select class="form-control chosen-select btn-outline push_down" name="userId" id="useId" tabindex="2">
                            <option value="0">--有效--</option>
                            <option value="1">--禁用--</option>
                        </select>
                    </div>
                    <div class="ibox-content" style="margin-top: 15px;">
                        <table id="table" class="table table-striped table-bordered table-full-width" width="100%">
                            <thead>
                            <tr>
                                <th>物料代码</th>
                                <th>产品名</th>
                                <th>规格型号</th>
                                <th>一级分类</th>
                                <th>二级分类</th>
                                <th>物料属性</th>
                                <th>默认仓库</th>
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
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    var warehouseArr = <?php echo (json_encode($warehouse)); ?>;
    var tableDiv = $('#table');
    var table = tableDiv.DataTable({
       serverSide: true,
       ajax: {
           type: 'post',
           data: {
                status: function () {
                    return document.getElementById('useId').value;
                }
           }
       },
       columns: [
           {data: 'product_no',searchable:true},
           {data: 'product_number',searchable:true},
           {data: 'product_name',searchable:true},
           {data: 'platform_name',searchable:true},
           {data: 'cate_name',searchable:false},
           {data: 'material_type',searchable:true},
           {data: 'repertory_name',searchable:true},
           {data: null,defaultContent: "<button class='btn btn-success btn-xs edit' type='button'>修改</button> <button class='btn btn-success btn-xs save hiddenDiv' type='button'>保存</button>",orderable: false}
       ],
        'columnDefs': [
            {
                'targets': 5,
                "data": 'tips',
                "render": function (data, type, row) {
                    arr = ['','生产', '外购'];
                    return arr[data];
                }
            }
        ]
    });

    tableDiv.on('click','.edit',function () {
        var tr = $(this).parents('tr');
        var row = table.row(tr).data();
        var productNumberInput = $('<input class="form-control product_number">');
        productNumberInput.val(row.product_number);
        tr.children('td').eq(1).html(productNumberInput);

        var cate=  '<select class="form-control platform">';
        cate += '<option >未选择</option>';
        cate += '<?php if(is_array($screenData)): $i = 0; $__LIST__ = $screenData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?>';
        if(row.parent_id == <?php echo ($vol["id"]); ?>) {
            cate += '<option value="<?php echo ($vol["id"]); ?>" selected><?php echo (str_repeat("&emsp;&emsp;",$vol["level"]*2)); echo ($vol["name"]); ?></option>';
        }else{
            cate += '<option value="<?php echo ($vol["id"]); ?>" ><?php echo (str_repeat("&emsp;&emsp;",$vol["level"]*2)); echo ($vol["name"]); ?></option>';
        }
        cate += '<?php endforeach; endif; else: echo "" ;endif; ?>';
        cate += '</select>';

        var material = "<select class=\"form-control platform\">";
        if (1 === parseInt(row.material_type)) {
            material += "<option value='1' selected>生产</option>";
            material += "<option value='2'>外购</option>";
        } else {
            material += "<option value='1'>生产</option>";
            material += "<option value='2' selected>外购</option>";
        }
        material += "</select>";

        var repertory = '<select class="form-control platform">';

        for(var i = 0; i < warehouseArr.length; i++) {
            if(row.warehouse_id == warehouseArr[i].rep_id) {
                repertory += '<option value="' + warehouseArr[i].rep_id + '" selected>' + warehouseArr[i].repertory_name + '</option>';
            }else{
                repertory += '<option value="' + warehouseArr[i].rep_id + '">' + warehouseArr[i].repertory_name + '</option>';
            }
        }
        repertory += '</select>';

        tr.children('td').eq(4).html(cate);
        tr.children('td').eq(5).html(material);
        tr.children('td').eq(6).html(repertory);

        $(this).hide();
        $(this).next('.save').show()
    })
    //有效、禁用
    $('.push_down').on('change', function (value) {
        table.ajax.reload()
        var enc_ = document.getElementsByClassName('encapsulated')[0];
        if(value.target.value == 1){
            enc_.setAttribute('style','display:none')
        }else{
            enc_.setAttribute('style','display:inline')
        }
    })

    // 单个保存
    tableDiv.on('click', '.save', function () {
        var tr = $(this).parents('tr');
        var auditor = $('.chooseAuditor').val();

        if (auditor === ''){
            layer.msg('请选择审核员');
            return false;
        }
        var arr = [];
        var obj = {
            product_id    : table.row(tr).data().product_id,
            product_num   : tr.children('td').eq(1).children('.product_number').val(),
            cate_num      : tr.children('td').eq(4).children('.platform').val(),
            material_type_new : tr.children('td').eq(5).children('.platform').val(),
            warehouse_id  : tr.children('td').eq(6).children('.platform').val()
        };
        arr.push(obj);
        var data = {
            data: arr,
            auditor: auditor,
            multi: 0
        };
        $.post('postEditProductRequest', data, function (res) {
            console.log(res);
            layer.msg(res.msg);
            table.ajax.reload(null, false);
        })
    });

    // 批量修改
    $('.piliangedit').on('click', function () {
        $(this).hide();
        $('.piliangsave').show();
        $('.edit').click()
    });

    // 批量保存
    $('.piliangsave').on('click', function () {

        var tr = $('tbody tr');
        var arr = [];

        var auditor = $('.chooseAuditor').val();
        if (auditor === ''){
            layer.msg('请选择审核员');
            return false
        }

        $.each(tr, function (k, v) {
            var obj = {};
            obj.product_num   = $(v).children('td').eq(1).children('.product_number').val();
            obj.product_id    = table.row(v).data().product_id;
            obj.cate_num      = $(v).children('td').eq(4).children('.platform').val();
            obj.product_id    = table.row(v).data().product_id;
            obj.material_type_new = $(v).children('td').eq(5).children('.platform').val();
            obj.warehouse_id  = $(v).children('td').eq(6).children('.platform').val();
            arr.push(obj);
        });
        var data = {
            data: arr,
            auditor: auditor,
            multi: 1
        };
        var $this = $(this);
        $.post('postEditProductRequest', data, function (res) {
            $this.hide();
            $('.piliangedit').show();
            layer.msg(res.msg);
            table.ajax.reload(null, false);
        })
    });

    // 添加产品
    $('.addProduct').on('click',function () {
        layer.open({
            type: 2,
            title: '新增物料型号',
            content: "<?php echo U('postAddProductRequest');?>",
            area: ['80%', '100%'],
            end: function(index, layero){
                layer.close(index); //如果设定了yes回调，需进行手工关闭
                table.ajax.reload();
            }
        })
    });

    // 添加产品类别
    $('.add_screen_cate').on('click',function () {
        layer.open({
            type: 2,
            title: '新增',
            content: "<?php echo U('addCategory');?>",
            area: ['80%', '100%'],
            end: function(index, layero){
                layer.close(index); //如果设定了yes回调，需进行手工关闭
                table.ajax.reload();
            }
        })
    });


    table.on('draw', function () {
        $('.piliangsave').hide();
        $('.piliangedit').show();
    })

</script>
</body>
</html>