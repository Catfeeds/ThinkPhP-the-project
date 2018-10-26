<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM--联系记录详情</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">

</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>新部门添加</h5>
                    <div class="ibox-tools"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></div>
                </div>
                <div class="ibox-content">
                    <form class="form-horizontal m-t" id="addSonDeptForm">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">新部门名称：</label>
                            <div class="col-sm-4">
                                <input id="addDeptName" name="deptName" class="form-control" type="text">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">所属部门：</label>
                            <div class="col-sm-4">
                                <select name="department" id="parentDept" class="form-control">
                                    <option value="">请选择</option>
                                    <?php if(is_array($dept)): $i = 0; $__LIST__ = $dept;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["id"]); ?>"><?php echo (str_repeat("&emsp;&emsp;",$vol["level"]*2)); echo ($vol["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-3">
                                <button class="btn btn-primary" type="button" id="addSonDept">提交</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script src="/Public/Admin/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/dwin/WdatePicker.js"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>


    $("#addSonDept").on('click', function () {
        var parentId = $("#parentDept option:selected").val();
        var addDeptName = $("#addDeptName").val();
        if ( typeof(parentId) == "undefined") { layer.alert("上级部门不能为空", { icon : 5 }); return false; }
        if ( addDeptName == "") { layer.alert("您不能添加一个名字为空的部门", { icon : 5 }); return false; }
        $("#addStaffSubmit").attr("disabled",'disabled');
        var indexLoad = layer.load(1, {shade : [0.1, '#fff']});
        $.ajax({
            type : 'POST',
            url  : '/Dwin/System/addDept',
            end  : function () {
                window.location.reload();
            },
            data : {
                pid : parentId,
                addName : addDeptName
            },
            success : function (msg) {
                layer.close(indexLoad);
                if(msg == 2) {
                    layer.msg(
                        '提交成功',
                        {
                            icon : 6,
                            time : 1000
                        },
                        function () {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                        }
                    );
                } else {
                    layer.alert("提交出错");
                    $("#addStaffSubmit").attr("disabled", false);
                    return false;
                }
            },
            error : function (error) {
                layer.alert(error);
	 	        $("addStaffSubmit").attr('disabled', false);
            }
        });
    });
</script>
</body>
</html>