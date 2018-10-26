<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新客户申请</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <script src="/Public/Admin/js/jsAddresss.js"></script>
    <style type="text/css">
        body {
            color: black;}
        .importTip{
            color : red;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <form id="iform">
                <div class="col-sm-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content">
			                <h3>客户录入</h3>
                                <div>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th colspan="5"><span style="color: black;">客户基本信息</span></th>
                                        </tr>
                                        <tr>
                                            <td colspan="1">客户名
                                                <div class="input-group">
                                                    <input name="cname" id="cName" type="text" placeholder="客户全称..." class="form-control proName"/>
                                                </div>
                                            </td>
                                            <td colspan="2">行业分类
                                                <select name="cusType" id="cusType"  data-placeholder="..." class="form-control" tabindex="2">
                                                    <option value="">请选择行业分类</option>
                                                    <?php if(is_array($indus)): $i = 0; $__LIST__ = $indus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option name="<?php echo ($vol["name"]); ?>" value="<?php echo ($vol["id"]); ?>" hassubinfo="true"><?php echo (str_repeat("&emsp;&emsp;",$vol["level"]*2)); echo ($vol["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                                </select>
                                            </td>
                                            <td colspan="2">客户来源
                                                <select id="csource" name="csource" class="form-control">
                                                    <option value="">请选择</option>
                                                    <option value="促销活动">促销活动</option>
                                                    <option value="Email">Email</option>
                                                    <option value="客户介绍">客户介绍</option>
                                                    <option value="独立开发">独立开发</option>
                                                    <option value="媒体宣传">媒体宣传</option>
                                                    <option value="老客户">老客户</option>
                                                    <option value="客服">客服</option>
                                                    <option value="网站">网站</option>
                                                    <option value="展会">展会</option>
                                                </select>
                                            </td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>公司网址<input class="form-control" name="website" id="website" type="text" placeholder="请输入公司网址，没有填无..." /></td>
                                            <td colspan="2">
                                                <span class="importTip">上级公司选择（如果添加的客户为某客户的子公司，请选择对应的客户）</span><br>
                                                <select name="sub_cus" id="sub_cus" class="chosen-select"   tabindex="4" style="width: 100%;">
                                                    <option value="">请选择</option>
                                                    <?php if(is_array($cusList)): $i = 0; $__LIST__ = $cusList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["cid"]); ?>" hassubinfo="true"><?php echo ($vol["cname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                                </select>
                                            </td>
                                            <td colspan="1">首要联系人<input  class="form-control" type="text" name="cusfcontact" id="cPhoneName"></td>
                                            <td>电话<input class="form-control" name="cphonenumber" id="cPhone" type="text"/></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>地址信息<span class="importTip">(地址将作为订单的发票地址或邮寄地址，请认真核实)</span></th>
                                        </tr>
                                        <tr>
                                            <td>公司所在地：<input  class="form-control" style="width: 10%;" type="text" name="city" id="cusCityValue" placeholder="地级市.."></td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>详细地址1<input  class="form-control" type="text"  name="street1" id="street1"></td>
                                        </tr>
                                        <tr>
                                            <td>详细地址2<input  class="form-control" type="text" name="street2" id="street2"></td>
                                        </tr>
                                        <tr>
                                            <td>详细地址3 <input  class="form-control" type="text" name="street3" id="street3"></td>
                                
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                    <h4>备注信息</h4>
                                    <div class="form-group">
                                        <textarea class="form-control" style=" height: 75px; width:100%; font-size: 14px;" name="detail" id="proNeeds"></textarea>
                                    </div>
                                    <p>
                                        <label>审核人：</label>
                                        <select name="auditorid" id="authorSele" class="form-control" style="width:10%;">
                                            <option value="">请选择</option>
                                            <?php if(is_array($arr)): $i = 0; $__LIST__ = $arr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["id"]); ?>"><?php echo ($vol["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                        </select>
                                    </p>
                                    <input class="btn btn-success" type="button" id="btnSubmit" value="提交">
                            </div>
                        </div>
                    </div>
            </form>
        </div>
    </div>
</div>
</body>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/Public/html/js/demo/form-advanced-demo.min.js"></script>
<script src="/Public/html/js/dwin/WdatePicker.js"></script>
<script src="/Public/html/js/area.js"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>


<script type="text/javascript">
    var controller = "/Dwin/Customer";
    $(function(){
        $('#btnSubmit').on('click',function(){
            $("#btnSubmit").attr('disabled', true);
            if ($("#cName").val() == "") { layer.msg('客户名称为空！', {icon: 5});$("#btnSubmit").attr('disabled', false);return false;}
            if ($("#cusType").val() == "") { layer.msg('请选择客户行业分类', {icon: 5});$("#btnSubmit").attr('disabled', false);return false;}
            if ($("#csource").val() == "") { layer.msg('客户来源未选！', {icon: 5});$("#btnSubmit").attr('disabled', false);return false;}
            if ($("#cusCityValue").val() == "") { layer.msg('客户公司所在地必填', {icon: 5});$("#btnSubmit").attr('disabled', false);return false;}
            if ($("#cPhoneName").val() == "") { layer.msg('客户首要联系人为空！', {icon: 5});$("#btnSubmit").attr('disabled', false);return false;}
            if ($("#cPhone").val() == "") { layer.msg('客户联系电话为空,请填写!', {icon: 5});$("#btnSubmit").attr('disabled', false);return false;}
            var uName = $("#cName").val(), uWebsite = $("#website").val(), uPhone = $("#cPhone").val();

            var flag1 =  $("#street1").val();
            var flag2 =  $("#street2").val();
            var flag3 =  $("#street3").val();
            if (!flag1 && !flag2 && !flag3) {
                layer.msg('地址至少填写一个!', {icon: 5});
                $("#btnSubmit").attr('disabled', false);
                return false;
            }
            if (!$("#authorSele").val()) {
                layer.msg('客户审核人未选', {icon: 5});
                $("#btnSubmit").attr('disabled', false);
                return false;
            }

            $.ajax({
                type : 'POST',
                url : controller + "/checkCusMsg",
                data : {
                    name : uName,
                    cPhone  : uPhone
                },
                success : function (msg2) {
                    if (msg2['status'] == 1) {
                        layer.confirm('发现近似名称客户:'+ msg2['msg'],{
                            btn : ['继续添加','返回'],
                            icon: 4
                        }, function (){
                            var data = $('form').serializeArray();
                            layer.confirm('禁止添加重复客户,如果重复请返回继续编辑',{
                                btn : ['添加','返回'],
                                icon: 4
                            },function () {
                                $.ajax({
                                    type : 'POST',
                                    url  : controller + '/addCustomer',
                                    data : data,
                                    success : function(msg1) {
                                        layer.msg(msg1['msg'], function () {
                                            if (msg1['status'] == 200) {
                                                layer.msg(msg1['msg'], {
                                                    icon:1,
                                                    time : 500
                                                }, function () {
                                                    var index = parent.layer.getFrameIndex(window.name);
                                                    parent.layer.close(index);
                                                });
                                            } else {
                                                layer.msg(msg1['msg'],
                                                    {
                                                        icon:5,
                                                        time:500
                                                    }, function () {
                                                        $("#btnSubmit").attr('disabled', false);
                                                });
                                            }
                                        });
                                    }
                                });
                            });
                        }, function (){
                            layer.msg('ok，重新编辑再提交！', {icon: 6});
                            $("#btnSubmit").attr('disabled', false);
                            return false;
                        });
                    } else if (msg2['status'] == 2) {
                        layer.confirm('确定提交客户信息？',
                            {
                                btn : ['确定','返回']
                            }, function() {
                                var data = $('form').serializeArray();
                                $.ajax({
                                    type : 'POST',
                                    url  : controller + '/addCustomer',
                                    data : data,
                                    success : function(msg1) {
                                        if (msg1 == 1) {
                                            var index = parent.layer.getFrameIndex(window.name);
                                            parent.layer.close(index);
                                        } else {
                                            layer.msg('添加成功 ', {icon: 1}, function () {
                                                layer.closeAll();
                                            });
                                        }
                                    }
                                });
                            }, function() {
                                layer.msg('ok，继续编辑再提交吧！', {icon: 6});
                                $("#btnSubmit").attr('disabled', false);
                                return false;
                            });
                    } else {
                        layer.alert(msg2['msg'], {icon: 5});
                        $("#btnSubmit").attr('disabled', false);
                        return false;
                    }
                }
            });
        });

    });

    $(".select-title").on("click",function(){
        $(".select-list").toggle();
        return false;
    });
    $(".select-list").on("click","li",function(){
        var txt = $(this).text();
        $(".select-title").find("span").text(txt);
    });
</script>
</html>