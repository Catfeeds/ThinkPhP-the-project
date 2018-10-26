<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>公司组织架构列表-数据表格</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;
        }
        .chosen-select{
            color:black;
            font-weight:200;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>公司职位列表</h5>
                <div class="ibox-content">
                    <div class="col-sm-12">
                        <input type="button" class="btn btn-success fa fa-plus" value='添加职位' id="addPosition">&emsp;
                        <input type="button" class="btn btn-success fa fa-plus" value='保存人员分配' id="saveTable">
                    </div>
                    <table class="table table-bordered table-striped dataTables-position">
                        <thead>
                        <tr>
                            <th style="width: 30%">职位</th>
                            <th style="width: 30%">权限</th>
                            <th style="width: 30%">职员</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <form>
                            <?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr class="grade" >
                                <td><?php echo (str_repeat("&emsp;",$vol["level"]*4)); echo ($vol["r_name"]); ?></td>
                                <td><?php echo ($vol["rule_list"]); ?></td>
                                <td>
                                    <select name="orderStaffList" data-placeholder="为该职位选择人员" class="chosen-select" multiple="multiple"  tabindex="4" style="width:100%" data="<?php echo ($vol["id"]); ?>">
                                        <?php if(is_array($userList)): $i = 0; $__LIST__ = $userList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["staff_id"]); ?>" <?php if(in_array(($vo['staff_id']), is_array($vol['user_id'])?$vol['user_id']:explode(',',$vol['user_id']))): ?>selected<?php endif; ?>><?php echo ($vo["staff_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </td>
                               <td>
                                    <a class="edit" data="<?php echo ($vol["id"]); ?>" lv="<?php echo ($vol["level"]); ?>"><i class="fa fa-pencil-square-o">编辑权限</i></a>
                                    <a class="delete" data="<?php echo ($vol["id"]); ?>" lv="<?php echo ($vol["level"]); ?>"><i class="fa fa-trash">删除</i></a>
                                </td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </form>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/Public/html/js/demo/form-advanced-demo.min.js"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>

$("#saveTable").on('click', function () {
    $(this).attr('disabled', true);
    var post_info = [];
    $('tbody tr').each(function () {
        var data = [];
        var selectedData = $(this).find('select').val();
        console.log(selectedData);
        data[1] = (selectedData == null)  ? ""  : selectedData.join();
        data[0] = $(this).find('select').attr('data');
        post_info.push(data);
    });
    layer.confirm('点击确定将提交职位人员分配，请选择',
        {
            icon  : 3,
            title :'alert',
            btn   : ['确定', '再想想']
        },
        function(){
            $.ajax({
                type : 'POST',
                url  : '/Dwin/System/savePositionInfo',
                data : {
                    position_data  : post_info
                },
                success : function (msg) {
                    switch(msg) {
                        case 1 : 
                            layer.msg("职位内员工选择保存失败，请联系开发人员",
                                    {
                                        icon : 5,
                                        time : 500
                                    },
                                    function () {
                                        $("#saveTable").attr('disabled', false);
                                    }
                                );
                            break;
                        case 2 : 
                            layer.msg("职位及职工权限更新完毕",
                                    {
                                        icon : 6,
                                        time : 500
                                    },
                                    function () {
                                        $("#saveTable").attr('disabled', false);
                                    }
                                );
                            break;
                        case 3 : 
                            layer.msg("员工权限更新选择保存失败，请联系开发人员",
                                    {
                                        icon : 5,
                                        time : 500
                                    },
                                    function () {
                                        $("#saveTable").attr('disabled', false);
                                    }
                                );
                            break;
                    }
                }
            });
        },
        function () {
            $("#saveTable").attr('disabled', false);
        }
        );
});

$(".delete").on("click", function () {
    var id = $(this).attr('data');
    layer.confirm('删除该职位及下属职位？',
        {
            icon  : 3,
            title :'alert',
            btn   : ['是的', '再想想']
        },
        function(){
            $.ajax({
                type : 'POST',
                url  : '/Dwin/System/delPosition',
                data : {
                    position_data  : id
                },
                success : function (msg) {
                    if (msg == 2) {
                        layer.msg("删除成功",
                            {
                                icon : 5,
                                time : 500
                            },
                            function () {
                                window.location.reload();
                            }
                        );
                    } else if(msg == 3) {
                        layer.msg("失败,总经理不能删除",
                            {
                                icon : 5,
                                time : 500
                            }
                        );
                    } else {
                        layer.msg("删除失败",
                            {
                                icon : 5,
                                time : 500
                            }
                        );
                    }
                }
            });
        }
    );
});

$(".edit").on('click', function () {
    var id = $(this).attr('data');
    layer.open({
        type: 2,
        title: "",
        end : function () {
            window.location.reload();
        },
        area: ['70%', '70%'],
        content: "/Dwin/System/editPosition/r_id/" + id
    });
});

$("#addPosition").on('click', function () {
    layer.open({
        type: 2,
        title: "",
        end : function () {
            window.location.reload();
        },
        area: ['70%', '70%'],
        content: "/Dwin/System/addPosition"
    });
});
</script>
</body>
</html>