<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DWIN CRM Login</title>
    <link rel="shortcut icon" href="/Public/favicon.ico">
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style>
        h1.logo-name{color:#00a0e9;font-size:130px;font-weight:300;letter-spacing:-10px;margin-bottom:0}
        .footer {
            position: absolute;
            bottom:0;
            left:0;
            text-align: right;
            width:100%;}
    </style>
</head>
<body class="gray-bg">
    <div class="middle-box text-center loginscreen animated fadeInDown">
        <div>
            <div>
                <h1 class="logo-name">DWIN</h1>
            </div>
            <h3>欢迎使用 DWINCRM</h3>

            <form class="m-t" role="form" id="loginForm" name="forms">
                <div class="form-group">
                    <input id="firstname" name="username" class="form-control" type="text" placeholder="用户名">
                </div>
		        <div class="form-group">
                    <span id=box><input id="password" name="password" class="form-control" type="password" placeholder="密码" style="width: 75%;margin-bottom: 18px; float: left;"></span>
                    <span style="float: left;" id= 'click'><a href="javascript:showps()">显示密码</a></span>
                </div>
                <div class="form-group">
                        <input id="captcha" name="captcha" maxlength="4" class="form-control" style="width: 50%; height:34px;float: left;">
                        <img style="float: left;margin-left: 10px;" src="/Dwin/Public/captcha" onclick="this.src='/Dwin/Public/captcha/t/'+Math.random()" />
                </div>
                <button class="btn btn-primary block full-width m-b"  style="background-color: #00a0e9;" type="button" id="userLogin">登录</button>
            </form>
        </div>
    </div>
</body>
<footer>
    <div class="footer">
        <div class="pull-right">&emsp;ERP version2.0.0 &copy; 2018-2019 版权所有
            <a href="http://www.dwin.com.cn" target="_blank">北京迪文科技有限公司</a>
        </div>
    </div>
</footer>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>

<script type="text/javascript">
/*    $("body").jParticle({
        background: "#141414",
        color: "#E6E6E6"
    });*/
  function showps(){
        if (this.forms.password.type="password") {

            document.getElementById("box").innerHTML="<input id=\"password\" name=\"password\" class=\"form-control\" type=\"text\" placeholder=\"密码\" style=\"width: 75%;margin-bottom: 18px; float: left;\" value="+this.forms.password.value+">";
            document.getElementById("click").innerHTML="<a href=\"javascript:hideps()\">隐藏密码</a>"
        }
    }
    function hideps(){
        if (this.forms.password.type="text") {
            document.getElementById("box").innerHTML="<input id=\"password\" name=\"password\" class=\"form-control\" type=\"password\" placeholder=\"密码\" style=\"width: 75%;margin-bottom: 18px; float: left;\" value="+this.forms.password.value+">";
            document.getElementById("click").innerHTML="<a href=\"javascript:showps()\">显示密码</a>"
        }
    }

    $(document).keyup(function(event){
        if(event.keyCode == 13){
            $("#userLogin").trigger("click");
        }
    });
    function checkPost ()
    {
        if (!loginForm.username.value)
        {
             layer.alert("请填写用户名！",
                { icon : 5 }
            );
            return false;

        }
        if (!loginForm.password.value)
        {
            layer.alert("请输入密码",
                { icon : 5 }
            );
            return false;
        }
        if (!loginForm.captcha.value)
        {
            layer.alert("你的验证码呢",
                { icon : 5 }
            );
            return false;
        }
        return true;
    }
    $("#userLogin").on('click', function () {
        $("#userLogin").attr("disabled","disabled");
        $("#loginForm").attr('disabled',"disabled");
        var rst = checkPost();
        if (rst == false) {
            $("#userLogin").attr("disabled",false);
            return false;
        }
        var indexLoad = layer.load(1, {shade : [0.1, '#fff']});
        $.ajax({
            type : 'POST',
            url  : '/Dwin/Public/loginOk',
            data : $("#loginForm").serializeArray(),
            success : function (msg2) {
                layer.close(indexLoad);
                if (msg2 == 2) {
                    window.location.href = "/Dwin/crm";
                } else if (msg2 == 1) {
                    layer.msg('验证码错误');
                    $("#userLogin").attr("disabled", false);
                } else if (msg2 == 3) {
                    layer.msg('用户名或密码错误');
                    $("#userLogin").attr("disabled", false);
                } else if (msg2 == 4) {
                    window.location.href = "/Dwin/Public/Public/404";
                } else if (msg2 == 5) {
                    layer.msg('账户被锁定了，联系管理员');
                }
            },
            error : function (error) {
                layer.close(indexLoad);
                $("#userLogin").attr("disabled", false);
            }
        });
    });
</script>
</html>