<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出入库类别管理</title>
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
        form{
            padding-left: 50px;
        }
        form .form-group{
            margin-left: 50px;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h3>备货方式管理</h3>
                </div>
                <div class="ibox-content">
                    <div class="">
                        <form action="<?php echo U('stockCate');?>" method="post" class="form-inline" id="add">
                            <h4>新增备货方式</h4>
                            <div class="form-group">
                                <label class="">备货方式</label>
                                <input type="text" class="form-control" name="stock_cate_name" placeholder="请输入新的备货方式">
                            </div>
                            <button type="submit" class="btn btn-primary form-group">新增备货方式</button>
                        </form>
                    </div>
                    <hr>
                    <div class=" row" style="margin-top: 15px;">
                        <div class="col-xs-6 col-xs-offset-3">
                            <table id="table1" class="table table-striped table-bordered table-full-width" width="100%">
                                <thead>
                                <tr>
                                    <th>id</th>
                                    <th>备货方式</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($data)): foreach($data as $key=>$i): ?><tr>
                                            <td><?php echo ($i["id"]); ?></td>
                                            <td><?php echo ($i["stock_cate_name"]); ?></td>
                                            <td>
                                                <button class="btn btn-info edit" data-id="<?php echo ($i["id"]); ?>">编辑</button>
                                                <button class="btn btn-info save" data-id="<?php echo ($i["id"]); ?>" style="display: none">保存</button>
                                                <button class="btn btn-danger del" data-id="<?php echo ($i["id"]); ?>">删除</button>
                                            </td>
                                        </tr><?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/Admin/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/jquery.form.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    $(function () {
        $('#add').ajaxForm({
            success: complete, // 这是提交后的方法
            dataType: 'json',
            data:{
                method: 'add'
            }
        });

        function complete(data) {
            if (data.status > 0) {
                layer.msg(data.msg, {icon: 6, time: 1500, shade: 0.1}, function (index) {
                    layer.close(index);
                    index = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
                    location.reload()
                });
            } else {
                layer.msg(data.msg, {icon: 5, time: 1500, shade: 0.1}, function (index) {
                    layer.close(index);
                });
                return false;
            }
        }

    });
    $('.del').on('click',function () {
        var $this = $(this);
        layer.confirm('确认删除？', {
            btn: ['确认','取消'] //按钮
        }, function(){
            $.ajax({
                type: 'post',
                data: {
                    id: $this.attr('data-id'),
                    method: 'del'
                },
                success: function (data) {
                    if (data.status > 0) {
                        layer.msg(data.msg, {icon: 6, time: 1500, shade: 0.1}, function (index) {
                            layer.close(index);
                            parent.layer.close(index);
                            location.reload()

                        });
                    } else {
                        layer.msg(data.msg, {icon: 5, time: 1500, shade: 0.1}, function (index) {
                            layer.close(index);
                        });
                    }
                }
            })
        }, function(){

        });

    });
    $('.edit').on('click',function () {
        $(this).next('.save').show();
        $(this).hide();
        var oldCateName = $(this).parent('td').prev('td').text();
        var input = '<input class="form-control" value="' + oldCateName + '">';
        $(this).parent('td').prev('td').html(input);
        $(this).parent('td').prev('td').attr('data-oldCateName', oldCateName);
    });
    $('.save').on('click',function () {
        $(this).prev('.edit').show();
        $(this).hide();
        var $td = $(this).parent('td').prev('td');
        var cateId = $(this).attr('data-id');
        var cateName = $td.children('input').val();
        $.ajax({
            type: 'post',
            data: {
                id: cateId,
                stock_cate_name: cateName,
                method: 'edit'
            },
            success: function (data) {
                if (data.status == 1) {
                    $td.html(cateName);
                    layer.msg(data.msg, {icon: 6, time: 1500, shade: 0.1}, function (index) {
                        layer.close(index);
                        parent.layer.close(index);
                        location.reload()
                    });
                } else {
                    $td.html($td.attr('data-oldCateName'));
                    layer.msg(data.msg, {icon: 5, time: 1500, shade: 0.1}, function (index) {
                        layer.close(index);
                    });
                }
            }
        })
    })
</script>
</body>
</html>