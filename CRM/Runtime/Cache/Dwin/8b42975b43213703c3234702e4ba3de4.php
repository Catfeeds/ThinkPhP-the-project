<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>员工信息编辑</title>
    <script src="/Public/Admin/js/jsAddresss.js"></script>
    <link rel="shortcut icon" href="favicon.ico"> <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type='text/css'>

    </style>
</head>
<body>
<div class="title"><h2>记录可查看时间限制</h2></div>
<form method="post" enctype="multipart/form-data">
    <div class="main">
        <br/>
        <div class="short-input ue-clear">
            <label>当前时间限制：<?php if($data["timelimit"] == ''): ?>未设置<?php else: echo ($data["timelimit"]); ?>天<?php endif; ?></label>
        </div>
        <div class="short-input ue-clear">
            <label>记录可查看时间修改为(单位：天)：</label>
            <input class="form-control" id="limitTime" name="timeLimit" type="number" style="width: 15%;">
        </div>
        <input class="btn btn-outline btn-success" type="button" id="changeT" value="修改" style="width: 7%; text-align: center;">
    </div>
</form>
<script src="/Public/Admin/js/jquery.js"></script>
<script src="/Public/Admin/js/common.js"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script type="text/javascript">
    $("#changeT").on('click', function() {
        var _newT = $("#limitTime").val();
        if (_newT != "") {
            $.ajax({
                type : 'POST',
                url  : "/Dwin/System/editRecordTime",
                data : {
                    timelimit : _newT
                },
                success : function (msg) {
                    if (msg == 1) {
                        layer.msg(
                            '设置成功',
                            {icon : 6},
                            function () {
                                window.location.reload();
                            }
                        );
                    } else {
                        layer.msg(
                            '设置失败',
                            {icon : 5},
                            function () {
                                window.location.reload();
                            }
                        );
                    }
                }
            });
        } else {
            layer.msg(
                '还没有设置！',
                {
                    icon : 6,
                    time : 1000
                }
            );
        }
    });
</script>
</body>
</html>