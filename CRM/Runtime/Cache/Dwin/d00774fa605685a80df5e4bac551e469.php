<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客户满意度调查-数据表格</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
        <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {  color: black;  }
        tbody td{  cursor:pointer;  }
	.cus-24{ color:red;}
	.selected{
            background-color: yellow !important;
        }
    .chosen-customer-type {
        color : blue;
    }
    .ibox-title {
        padding-top: 7px;
    }
    .chosen-select{
        width : 100%;
    }
        .table-td-text{
            font-size:1.3em;
            font-weight: 200;
        }
        .table-td-text p{
            padding:20px 0 ;
        }
        .table-td-text p:hover{
            cursor:default;
        }
        .radio:hover{
            color:darkblue;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                </div>
                <div class="ibox-content" id="table-cus-div">
                    <ul style="list-style:inherit">
                        <li><h3>本次分配回访客户数：<span id="total"><?php echo ($data["total"]); ?></span></h3></li>
                        <li><h3>已回访客户数：<span id="done"><?php echo ($data["done"]); ?></span></h3></li>
                        <li><h3>待回访客户数：<span id="none"><?php echo ($data["none"]); ?></span></h3></li>
                    </ul>
                    <div><h2 id="timeset"></h2></div>
                    <button class="btn btn-primary btn-rounded btn-block" id="getCustomerInfo" href="javascript:;" style="margin:90px 0;font-size:2em;"><i class="fa fa-info-circle"></i> 抽取客户</button>
                    <form id="callbackSubmit" style="display: none;">
                        <table class="table">
                            <tr>
                                <th class="text-center">
                                    <h2>客户满意度调查</h2>
                                    <input type="hidden" name="id" id="id" class="required">
                                </th>
                            </tr>
                            <tr class="table-td-text">
                                <td><b>当前客户</b><span id="cus_name"></span></td>
                            </tr>
                            <tr class="table-td-text">
                                <td>
                                    <div>业务联系记录</div>
                                    <div id="record"></div>
                                </td>
                            </tr>
                            <tr class="table-td-text">
                                <td>
                                    <div>客户联系方式查看</div>
                                    <div id="contact-number"></div>
                                    <div id="contact-number-phone"></div>
                                </td>
                            </tr>
                            <tr class="table-td-text">
                                <td>
                                    <p>贵公司对迪文的服务是否满意？</p>
                                    <input type="hidden" name="question_1" value="贵公司对迪文的服务是否满意？">
                                    <div class="radio radio-success radio-inline">
                                        <input class="radio-success required" type="radio" name="question_1flag" id="question-r1" value="满意">
                                        <label for="question-r1">
                                            A 满意
                                        </label>
                                    </div>
                                    <div class="radio radio-inline">
                                        <input class="radio-success required" type="radio" name="question_1flag" id="question-r2" value="一般">
                                        <label for="question-r2">
                                            B 一般
                                        </label>
                                    </div>
                                    <div class="radio radio-danger radio-inline">
                                        <input class="radio-success required" type="radio" name="question_1flag" id="question-r3" value="不满意">
                                        <label for="question-r3">
                                            C 不满意
                                        </label>
                                    </div>
                                    <div class="radio radio-inline">
                                        <input class="radio-success required" type="radio" name="question_1flag" id="question-r4" value="不愿接受调查">
                                        <label for="question-r4">
                                            D 不愿接受调查
                                        </label>
                                    </div>
                                    <textarea name="question_1tip" row="3" class="form-control" placeholder="客户不满意主要原因..."></textarea>
                                </td>
                            </tr>
                            <tr class="table-td-text">
                                <td>
                                    <p id="question_2">业务员给贵公司的服务，您觉得还有什么需要改进的？</p>
                                    <input type="hidden" name="question_2" value="业务员给贵公司的服务，您觉得还有什么需要改进的？">
                                    <div class="radio radio-success radio-inline">
                                        <input class="radio-success required" type="radio" name="question_2flag" id="question2-r1" value="不错">
                                        <label for="question2-r1">
                                            A 不错
                                        </label>
                                    </div>
                                    <div class="radio radio-inline">
                                        <input class="radio-success required" type="radio" name="question_2flag" id="question2-r2" value="一般">
                                        <label for="question2-r2">
                                            B 一般般
                                        </label>
                                    </div>
                                    <div class="radio radio-danger radio-inline">
                                        <input class="radio-success required" type="radio" name="question_2flag" id="question2-r3" value="不怎么样">
                                        <label for="question2-r3">
                                            C 不怎么样
                                        </label>
                                    </div>
                                    <div class="radio radio-inline">
                                        <input class="radio-success required" type="radio" name="question_2flag" id="question2-r4" value="记不清">
                                        <label for="question2-r4">
                                            D 记不清了
                                        </label>
                                    </div>
                                    <textarea name="question_2tip" row="3" class="form-control required" placeholder="客户反馈意见..."></textarea>
                                </td>
                            </tr>
                            <tr class="table-td-text">
                                <td>
                                    <p id="question_3">新一代CPU T5 2017年量产了，并推出了很多挺棒的产品，比如DGUS的升级，我们的业务有介绍给您吗？</p>
                                    <input type="hidden" name="question_3" value="新一代CPU T5 2017年量产了，并推出了很多挺棒的产品，比如DGUS的升级，我们的业务有介绍给您吗？">
                                    <div class="radio radio-success radio-inline">
                                        <input class="radio-success required" type="radio" name="question_3flag" id="question3-r1" value="有">
                                        <label for="question3-r1">
                                            A 有
                                        </label>
                                    </div>
                                    <div class="radio radio-inline">
                                        <input class="radio-success required" type="radio" name="question_3flag" id="question3-r2" value="没有">
                                        <label for="question3-r2">
                                            B 没有
                                        </label>
                                    </div>
                                    <div class="radio radio-danger radio-inline">
                                        <input class="radio-success required"  type="radio" name="question_3flag" id="question3-r3" value="受访人不了解具体情况">
                                        <label for="question3-r3">
                                            C 受访人不了解具体情况
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr class="table-td-text">
                                <td>
                                    <p id="question_4">是否有二次返修情况</p>
                                    <input type="hidden" name="question_4" value="是否有二次返修情况">
                                    <div class="radio radio-success radio-inline">
                                        <input class="radio-success required" type="radio" name="question_4flag" id="question4-r1" value="有">
                                        <label for="question4-r1">
                                            A 有
                                        </label>
                                    </div>
                                    <div class="radio radio-inline">
                                        <input class="radio-success required" type="radio" name="question_4flag" id="question4-r2" value="没有">
                                        <label for="question4-r2">
                                            B 没有
                                        </label>
                                    </div>
                                    <div class="radio radio-inline">
                                        <input class="radio-success required" type="radio" name="question_4flag" id="question4-r3" value="未调查">
                                        <label for="question4-r3">
                                            C 未调查
                                        </label>
                                    </div>
                                    <div class="radio radio-inline">
                                        <input class="radio-success required" type="radio" name="question_4flag" id="question4-r4" value="未回答/不清楚">
                                        <label for="question4-r4">
                                            D 客户未回答/不清楚
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="btn btn-outline btn-info" id="submit-btn">回访结束，提交记录</button>
                        <button type="button" class="btn btn-outline btn-warning" id="resetBtn">信息有误，重新抽取</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/Public/html/js/demo/form-advanced-demo.min.js"></script>
<script src="/Public/html/js/plugins/jeditable/jquery.jeditable.js"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/Public/html/js/plugins/validate/messages_zh.min.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/dwin/customer/common_func.js"></script>
<script>
    var controller = "/Dwin/OnlineService";
    var getCusInfoBtn = $("#getCustomerInfo");
    var cusName       = $("#cus_name");
    var callbackId    = $("#id");
    var recordDiv     = $("#record");
    var contactDiv    = $("#contact-number");
    var contactNumDiv = $("#contact-number-phone");
    var submitForm    = $("#callbackSubmit");
    var submitBtn     = $("#submit-btn");
    var resetBtn      = $("#resetBtn");

    getCusInfoBtn.on('click', function () {
        getCusInfoBtn.attr('disabled', true);
        $.ajax({
            type : 'post',
            url : controller + "/getCallbackCustomer",
            data : {
                flag : 1
            },
            success : function (ajaxData) {
                if (ajaxData['status'] != 200) {
                    if (ajaxData['status'] == 100) {
                        layer.msg(ajaxData['msg']);
                        var timeLimit = ajaxData['data'];
                        var m,s;
                        var intervalId = setInterval(function () {
                            if (timeLimit > 0) {
                                timeLimit -= 1;
                                m = Math.floor(timeLimit / 60 % 60);
                                s = Math.floor(timeLimit % 60);
                                $("#timeset").html("距离下一次可以抽取客户还有：" + m + ":" + s);
                            } else {
                                clearInterval(intervalId);
                                layer.msg('可以抽取客户');
                                getCusInfoBtn.attr('disabled', false);
                                $("#timeset").html("");
                            }
                        }, 1000);
                    } else {
                        layer.msg(ajaxData['msg']);
                        getCusInfoBtn.attr('disabled', false);
                    }
                } else {
                    contactNumDiv.html("");
                    var callbackIdData = ajaxData['data']['callback']['id'];
                    callbackId.val(callbackIdData);
                    // 进入回访流程，相关内容显示
                    submitForm.css('display','block');
                    cusName.html("");
                    cusName.html(ajaxData['data']['callback']['cus_name']);
                    recordDiv.html("");
                    var sonTable =
                        '<table class="table table-condensed table-striped table-hover">' +
                        '<tbody>';
                    for(var i = 0; i < ajaxData['data']['contact'].length; i ++)
                    {

                        sonTable +=
                            '<tr>' +
                            '<td rowspan="6" width="25%"><b>联系时间</b>：' + ajaxData['data']['contact'][i]['posttime'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>' + ((ajaxData['data']['contact'][i]['contact'] == null) ? "" : ('<b>联系人</b>：' + ajaxData['data']['contact'][i]['contact'] + '&emsp;联系方式：' + ajaxData['data']['contact'][i]['contact_num'])) + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td><b>主题</b>：' + ajaxData['data']['contact'][i]['theme'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td><b>类型</b>：' + ajaxData['data']['contact'][i]['ctype'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td><b>时间</b>：' + ajaxData['data']['contact'][i]['posttime'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td><b>联系内容</b>：' + ajaxData['data']['contact'][i]['content'] + '</td>'+
                            '</tr>';
                    }
                    sonTable += "</table>";
                    recordDiv.html(sonTable);
                    contactDiv.html('');
                    var contactContent =
                        "<select class='form-control required' name='contact_name' id='contact-select' style='width:300px;'>" +
                            "<option value=''>请选择本次回访的客户联系人</option>";
                    for(var j = 0; j < ajaxData['data']['phone'].length; j++){
                        contactContent += "<option value='" + ajaxData['data']['phone'][j]['name'] + "' data='" + ajaxData['data']['phone'][j]['phone'] + "'>" + ajaxData['data']['phone'][j]['name'] + "</option>";
                    }
                    contactContent += "</select>";
                    contactDiv.html(contactContent);
                    layer.msg(ajaxData['msg']);
                }
            }
        });
    });
    function clearForm(form) {
        // iterate over all of the inputs for the form
        // element that was passed in
        $(':input', form).each(function() {
            var type = this.type;
            var tag  = this.tagName.toLowerCase(); // normalize case
            // it's ok to reset the value attr of text inputs,
            // password inputs, and textareas
            if (type == 'text' || type == 'password' || tag == 'textarea')
                this.value = "";
            // checkboxes and radios need to have their checked state cleared
            // but should *not* have their 'value' changed
            else if (type == 'checkbox' || type == 'radio')
                this.checked = false;
            // select elements need to have their 'selectedIndex' property set to -1
            // (this works for both single and multiple select elements)
            else if (tag == 'select')
                this.selectedIndex = -1;
        });
    }
    $(document).ready(function () {
        submitForm.validate({
            submitHandler: function(form) {
                submitBtn.attr('disabled', true);
                var data = $(form).serializeArray();
                console.log(data);
                $.ajax({
                    type : 'POST',
                    url  : controller + '/addCallbackRecord',
                    data : data,
                    success : function (msg) {
                        if(msg['status'] == 200) {
                            layer.msg(msg['msg'],
                                {
                                    icon : 6,
                                    time : 1000
                                },
                                function () {
                                //提交成功，清空数据
                                    callbackId.val("");
                                    submitForm.css('display','none');
                                    cusName.html("");
                                    recordDiv.html("");
                                    contactDiv.html("");
                                    contactNumDiv.html("");
                                    clearForm(submitForm);
                                    submitBtn.attr("disabled", false);
                                    getCusInfoBtn.attr('disabled', false);
                                    $("#total").html(msg['data']['total']);
                                    $("#done").html(msg['data']['done']);
                                    $("#none").html(msg['data']['none']);
                                }
                            );
                        } else {
                            layer.alert(msg['msg']);
                            submitBtn.attr("disabled", false);
                        }
                    },
                    error : function (error) {
                        layer.alert(error);
                        submitBtn.attr("disabled", false);
                    }
                });
            }
        });
        contactDiv.on('change', '#contact-select', function () {
            if ($(this).val()) {
                contactNumDiv.html("");
                var contactNumContent = "联系方式：" + $("#contact-select").find('option:selected').attr('data');
                contactNumContent += "<input type='hidden' name='contact_number' value='" + $("#contact-select").find('option:selected').attr('data') + "'>"
                contactNumDiv.html(contactNumContent);
            } else {
                contactNumDiv.html("");
            }
        });

        resetBtn.on('click', function () {
            console.log(3);
            layer.prompt({title: '请输入放弃回访该客户的原因，点击确定重新抽取', formType: 2}, function(text, index){
                layer.close(index);
                $.ajax({
                    type : 'POST',
                    url  : controller + '/randCusByOnline',
                    data : {
                        callId : $('#id').val(),
                        textContent : text
                    },
                    success : function(data) {
                        layer.msg(data['msg']);
                        if (returnData['status'] == 200) {
                            layer.msg(data['msg'], function () {
                                callbackId.val("");
                                submitForm.css('display','none');
                                cusName.html("");
                                recordDiv.html("");
                                contactDiv.html("");
                                contactNumDiv.html("");
                                clearForm(submitForm);
                                submitBtn.attr("disabled", false);
                                getCusInfoBtn.attr('disabled', false);
                            });
                        } else {
                            layer.alert(data['msg']);
                        }
                    }
                });
            });
        });
    });
</script>
</body>
</html>