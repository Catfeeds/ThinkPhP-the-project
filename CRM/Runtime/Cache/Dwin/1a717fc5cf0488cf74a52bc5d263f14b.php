<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM--客户编辑</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/Public/html/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body { color: black;}
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>客户信息修改</h5>
                    <div class="ibox-tools"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></div>
                </div>
                <div class="ibox-content">
                    <form class="form-horizontal m-t" id="editAddr">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">客户名称：</label>
                            <div class="col-sm-4">
                                <input name="companyName" class="form-control" type="text" value="<?php echo ($data["cname"]); ?>" readonly="readonly" >
                                <input name="cid" class="form-control" type="hidden" value="<?php echo ($data["cid"]); ?>">
                                <input name="oldCompanyName" class="form-control" type="hidden" value="<?php echo ($data["cname"]); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">上级公司：</label>
                            <div class="col-sm-4">
                                <select name="parentCus" id="parentCus" class="chosen-select">
                                    <option value="">无上级</option>
                                    <?php if(is_array($ownCus)): $i = 0; $__LIST__ = $ownCus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option name="<?php echo ($vo["name"]); ?>" value="<?php echo ($vo["id"]); ?>" hassubinfo="true" <?php if(($vo["id"]) == $data["cus_pid"]): ?>selected<?php endif; ?>><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">行业：</label>
                            <div class="col-sm-4">
                                <select name="cType" id="cType" class="form-control required">
                                    <option value="">请选择</option>
                                    <?php if(is_array($industry)): $i = 0; $__LIST__ = $industry;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option name="<?php echo ($vol["name"]); ?>" value="<?php echo ($vol["id"]); ?>" hassubinfo="true" <?php if(($vol["id"]) == $data["indid"]): ?>selected<?php endif; ?>><?php echo (str_repeat("&emsp;&emsp;",$vol["level"]*2)); echo ($vol["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                </select> <label class="col-sm-12 control-label">（原行业:<?php echo ($data["indusname"]); ?>)</label>
                                <input name="oldCType" class="form-control" type="hidden" value="<?php echo ($data["ctype"]); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">网址：</label>
                            <div class="col-sm-4">
                                <input name="Website" class="form-control" type="text" value="<?php echo ($data["website"]); ?>">
                                <input name="oldWeb" class="form-control" type="hidden" value="<?php echo ($data["website"]); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">首要联系人：</label>
                            <div class="col-sm-4">
                                <input name="contactName" id="contactName" class="form-control required" maxlength="30" type="text" value="<?php echo ($data["cphonename"]); ?>">
                                <input name="oldContactName" class="form-control" type="hidden" value="<?php echo ($data["cphonename"]); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">联系人职位：</label>
                            <div class="col-sm-4">
                                <input name="contactPosition" class="form-control" type="text" value="<?php echo ($data["cphoneposition"]); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">电话：</label>
                            <div class="col-sm-4">
                                <input name="companyPhone" class="form-control required" minlength="11" maxlength="40" type="text" value="<?php echo ($data["cphonenumber"]); ?>">
                                <input name="oldcompanyPhone" class="form-control" type="hidden" value="<?php echo ($data["cphonenumber"]); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">地址一：</label>
                            <div class="col-sm-4">
                                <input name="addr1" class="form-control required" type="text" value="<?php echo ($data["addr"]["0"]); ?>">
                                <input name="oaddr1" class="form-control" type="hidden" value="<?php echo ($data["addr"]["0"]); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">地址二：</label>
                            <div class="col-sm-4">
                                <input name="addr2" class="form-control" type="text" value="<?php echo ($data["addr"]["1"]); ?>">
                                <input name="oaddr2" class="form-control" type="hidden" value="<?php echo ($data["addr"]["1"]); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">地址三：</label>
                            <div class="col-sm-4">
                                <input  name="addr3" class="form-control" type="text" value="<?php echo ($data["addr"]["2"]); ?>">
                                <input name="oaddr3" class="form-control" type="hidden" value="<?php echo ($data["addr"]["2"]); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label  class="col-sm-3 control-label">审核人：</label>
                            <div class="col-sm-4">
                            <select class="form-control" name="audi" id="authorSele">
                                <?php if(is_array($audi)): $i = 0; $__LIST__ = $audi;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["id"]); ?>" <?php if(($vol["id"]) == $data["auditorid"]): ?>selected<?php endif; ?>><?php echo ($vol["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-3">
                                <button class="btn btn-primary" type="submit" id="cusCSubmit">提交</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/Public/html/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/Public/html/js/plugins/validate/messages_zh.min.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    $(".chosen-select").chosen({
        no_results_text : "没搜索到您输入的客户",
        search_contains : true
    });
    var editCusSubmitBtn = $("#cusCSubmit");
    var submitForm = $("#editAddr");
    submitForm.validate({
        submitHandler: function(form) {
            var datas = $("#editAddr").serializeArray();
            editCusSubmitBtn.attr('disabled', 'disabled');
            $.ajax({
                type : 'POST',
                url : '/Dwin/Customer/editCustomer',
                data : datas,
                success : function (msg) {
                    if (msg['status'] == 2) {
                        console.log(msg);
                        layer.msg(msg['msg'],
                            {
                                icon : 5,
                                time : 500
                            },
                            function () {
                                var index = parent.layer.getFrameIndex(window.name);
                                parent.layer.close(index);
                            }
                        );
                    } else {
                        layer.msg( msg['msg'],
                            {
                                icon : 6,time:500
                            }
                        );
                        $("#cusCSubmit").attr('disabled', false);
                    }
                }
            });
        }
    });
</script>
</body>
</html>