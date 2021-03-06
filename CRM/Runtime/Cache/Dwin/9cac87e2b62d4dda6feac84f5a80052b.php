<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>公司权限分配</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {  color: black;  }
    </style>
</head>
<body class="gray-bg">
<table class="col-md-12">
    <tr>
        <td>  <div class="ibox-content col-md-12 " style="float: left;">
            <div class="fa-hover col-md-6 col-sm-4"><h4>客户审核权限</h4></div>
            <form id="editForm1" name="editForm1">
                <table class="table table-striped table-bordered table-hover" >
                    <thead>
                    <tr><th>员工名单</th><th></th><th>客户审核人</th></tr>
                    </thead>
                    <tbody>
                    <tr class="gradeX">
                        <td>
                            <select class="form-control" name="staffSelect" id="staffList1" multiple="multiple" size="3" style="height: 200px;">
                                <?php if(is_array($staffList_1)): $i = 0; $__LIST__ = $staffList_1;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option name="<?php echo ($vol["sid"]); ?>" value="<?php echo ($vol["sid"]); ?>"><?php echo ($vol["staffname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </td>
                        <td><input type="button" value="<<<" onclick="moveOptions('staffListTo1', 'staffList1')" />
                            <input type="button" value=">>>" onclick="moveOptions('staffList1', 'staffListTo1')" />
                        </td>
                        <td>
                            <select class="form-control" id="staffListTo1" multiple="multiple" size="3" style="height: 200px;">
                                <?php if(is_array($staffList_2)): $i = 0; $__LIST__ = $staffList_2;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["sid"]); ?>"><?php echo ($vol["staffname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <input class="form-control btn-outline btn-success" type="button" value="提交" id="editForm1But">
            </form>
        </div></td>
        <td><div class="ibox-content col-md-12" style="float: left;">
            <div class="fa-hover col-md-6 col-sm-4"><h4>项目审核权限</h4></div>
            <form id="editForm2">
                <table class="table table-striped table-bordered table-hover" >
                    <thead><tr><th>员工名单</th><th></th><th>项目审核人</th></tr></thead>
                    <tbody>
                    <tr class="gradeX">
                        <td>
                            <select class="form-control" name="select1" id="staffList" multiple="multiple" size="3" style="height: 200px;">
                                <?php if(is_array($staffList_3)): $i = 0; $__LIST__ = $staffList_3;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["sid"]); ?>"><?php echo ($vol["staffname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </td>
                        <td>
                            <input type="button" value="<<<" onclick="moveOptions('staffListTo', 'staffList')" />
                            <input type="button" value=">>>" onclick="moveOptions('staffList', 'staffListTo')" />
                        </td>
                        <td>
                            <select class="form-control" id="staffListTo" multiple="multiple" size="3" style="height: 200px;">
                                <?php if(is_array($staffList_4)): $i = 0; $__LIST__ = $staffList_4;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["sid"]); ?>"><?php echo ($vol["staffname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <input class="form-control btn-outline btn-success" type="button" value="提交" id="editForm2But">
            </form>
        </div></td>
    </tr>
</table>
<script src="/Public/Admin/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    function moveOptions(from,to)
    {
        var oldname = $("#"+from+"  option:selected");
        if (oldname.length == 0) {
            return;
        }
        var valueOb = {};
        $("#" + to).find("option").each(function() {
            valueOb[String($(this).val())] = $(this);
        });

        for( var i =0;i< oldname.length; i++){
            if(valueOb[String($(oldname[i]).val())] == undefined){
                $(oldname[i]).clone().appendTo($("#"+to))
                $(oldname[i]).remove();
            }
        }
    }

    $("#editForm1But").on('click', function() {
        $("#editForm1But").attr('disabled', 'disabled');
        var data1 = [];
        $("#staffListTo1 option").each(function(i) {
            data1[i] = $(this).val();
        });
        $.ajax({
            type    : 'POST',
            url     : "/Dwin/System/editRole",
            data    : {
                Ids :data1 },
            success : function(msg)
            {
                if (msg == 1) {
                    layer.msg('ok, roles changed', {
                        icon : 6,
                        time : 500
                    }, function (){
                        window.location.reload();
                    });
                } else {
                    layer.msg('修改失败',
                        {
                            icon : 5,
                            time :500
                        },function () {
                        window.location.reload();
                    });
                }

            }
        });
    });
    $("#editForm2But").on('click', function() {
        $("#editForm2But").attr('disabled', 'disabled');
        var data1 = [];
        $("#staffListTo option").each(function(i) {
            data1[i] = $(this).val();
        });
        $.ajax({
            type    : 'POST',
            url     : "/Dwin/System/editRole",
            data    : {
                Id :data1 },
            success : function(msg)
            {
                if (msg == 1) {
                    layer.msg('ok, roles changed', {
                        icon : 6,
                        time : 500
                    }, function (){
                        window.location.reload();
                    });
                } else {
                    layer.msg('修改失败',
                        {
                            icon : 5,
                            time :500
                        },function () {
                            window.location.reload();
                        });
                }
            }
        });
    });
</script>
</body>
</html>