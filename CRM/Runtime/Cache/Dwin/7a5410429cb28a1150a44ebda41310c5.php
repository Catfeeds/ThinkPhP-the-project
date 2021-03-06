<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>公司组织架构列表-数据表格</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;;
        }

    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins col-sm-8">
                <div class="ibox-title">
                    <h5>公司部门信息</h5>
                    <a style="float: right;" href="javascript:;" id="addDept"><i class="fa fa-plus"></i> 添加部门</a>
                </div>
                <div class="ibox-content">
                    <table class="table table-bordered table-striped dataTables-DeptList">
                        <thead>
                        <tr>
                            <!--         <th>编号</th>-->
                            <th>部门</th>
                            <th>修改|删除</th>
                        </tr>
                        </thead>
                        <tbody >
                        <?php if(is_array($dept)): $i = 0; $__LIST__ = $dept;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr class="grade" >
                                <?php if($vol["level"] < 5): ?><!--<td style="padding: 0px;" ><?php echo ($vol["id"]); ?></td>-->
                                    <td><?php echo (str_repeat("&emsp;&emsp;",$vol["level"]*3)); echo ($vol["name"]); ?></td>
                                    <td>
                                        <a class="edit" data="<?php echo ($vol["id"]); ?>" lv="<?php echo ($vol["level"]); ?>"><i class="fa fa-pencil-square-o"></i></a>&emsp;&emsp;&emsp;
                                        <a class="delete" data="<?php echo ($vol["id"]); ?>" lv="<?php echo ($vol["level"]); ?>"><i class="fa fa-trash"></i></a>
                                    </td><?php endif; ?>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
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
$(document).ready( function () {
    var height1 = $(window).height() - 220;
    $(".dataTables-DeptList").DataTable({
        "scrollY" : height1,
        "scrollX" : true,
        "scrollCollapse" : true,
        "paging" : false,
        "ordering" : false,
        "info" : false,
        'searching' : false

    });
});
$(".edit").on("click", function () {
    var id = $(this).attr('data');
    var lv = $(this).attr('lv');
    layer.open({
        type: 2,
        title: '修改部门名称',
        area: ['80%', '80%'],
        content: "/Dwin/System/editDept/dId/" + id //iframe的url
    });
});

$(".delete").on("click", function () {
    var id = $(this).attr('data');
    var lv = $(this).attr('lv');
    layer.confirm('解散选择的部门（包括下辖部门）？',
        {
            icon  : 3,
            title :'alert',
            btn   : ['确认', '再想想']
        },
        function(){
            $.ajax({
                type : 'POST',
                url  : '/Dwin/System/changeDept',
                data : {
                    dId : id,
                    dLevel : lv
                },
                success : function (msg) {
                    if (msg == 2) {
                        layer.msg('该部门已删除',function () {
                            window.location.reload();
                        });

                    } else {
                        layer.msg('删除失败');
                    }
                } 
            });
        }
    );
});
$("#addDept").on('click', function () {
    layer.open({
        type: 2,
        title: '添加新部门',
        end : function () {
            window.location.reload();
        },
        area: ['60%', '60%'],
        content: "/Dwin/System/addDept"
    });
});
</script>
</body>
</html>