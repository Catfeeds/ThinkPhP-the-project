$(".unCheck").css('color','red');
$(".allPro").css('color','blue');
$(".noCheckYet").css('color','black');
$(".checkNot").css('color','red');
$(".checkYes").css('color','blue');


/*---------------------------------js显示截至时间-------------------------------*/
var now = Date.parse(new Date()).toString();
now = now.substr(0, 10);
var jiezhi = [];
var ch = [];
$(".setTime").each(function(i) {
    jiezhi[i] = $(this).attr('data');
    ch[i] = (jiezhi[i] - now)/3600/24;
    ch[i] = ch[i].toFixed(0);
    if (ch[i] >= 0) {
        $(this).text("还剩" + ch[i] + "天");
    } else {
        ch[i] = -1 * ch[i];
        $(this).text("超时" + ch[i] + "天");
    }
});
/*------------------------------------------end------------------------------------*/


/*---------------------------------点击变更，更新项目------------------------------*/

/*------------------------------------------end------------------------------------*/
var co;
$("td").on('mouseover',function() {
    co = $(this).css('background-color');
    return co;
});
$(".mouseOn").on('mouseover', function () {
    co = $(this).css('background-color');
    $(this).css('color', 'blue');
    $(this).css('background-color', 'yellow');
    return co;
});
$(".mouseOn").on('mouseout', function () {
    $(this).css('color', 'black');
    $(this).css('background-color', co);
});
$("input:checkbox").on('click', function(e){
    e.stopPropagation();
    var colorFlag = $(this).prop('checked');
    if (colorFlag == true){
        $(this).parent().parent('tr').css('background-color', 'yellow');
    } else {
        $(this).parent().parent('tr').css('background-color', co);
    }
});
$("tr").click(function(){
    var check = $(this).find("input[type='checkbox']");
    if(check[0] != undefined){
        var flag = check[0].checked;
        if(flag){
            check[0].checked = false;
            $(this).css('background-color', co);
        }else{
            check[0].checked = true;
            $(this).css('background-color',"yellow");
        }
    }
});

/*-------------------------layer层查看进度和变更*----------------------------------*/
$('.prjDetail').on('click', function(e) {
    e.stopPropagation();
    var prjid = $(this).attr('data');
    layer.open({
        type: 2,
        title: '项目详情',
        area: ['100%', '100%'],
        content: controller + "/showProjectDetail/id/" + prjid //iframe的url
    });
});

$(".performOfPrj").on('click', function(e) {
    var prjid = $(this).attr('data');
    e.stopPropagation();
    layer.open({
        type: 2,
        title: '项目绩效',
        area: ['90%', '63%'],
        content: controller + "/showPerformanceDetail/id/" + prjid //iframe的url
    });
});

var nId = $("#nId").val();
$(".updLayer").on('click',function(e) {
    e.stopPropagation();
    var module = $(this).attr("data");
    var uncheckNum = $(this).attr('data-num');
    if (uncheckNum == 0) {return false;}
    layer.open({
        type: 2,
        title: '项目进度',
        area: ['100%', '80%'],
        end        : function () {
            location.reload();
        },
        content: controller + "/showPrjUpdateList/m/" + module + "/nid/" + nId //iframe的url
    });
});
$(".changeLayer").on('click',function(e) {
    e.stopPropagation();
    var prjids = $(this).attr("data");
    var num = $(this).attr('data-num');
    if (num == 0) { return false; }
    layer.open({
        type: 2,
        title: '项目变更记录',
        area: ['90%', '55%'],
        content: controller + "/showPrjChangeList/m/" + prjids //iframe的url
    });
});
/**----------------------------------end-----------------------------------------**/


/**-------------------项目24小时，7日内的记录数---------------------------------**/

$(".prj-24").on('click', function() {
    window.location.href = controller + "/showOwnPrj/k/1/n/1";
});
$(".prj-7").on('click', function() {
    window.location.href = controller + "/showOwnPrj/k/1/n/7";
});
/**----------------------------------end----------------------------------------**/



/**------------------------------按钮审核记录----------------------------------**/
var chk_value = [], chk_data = [], chk_dat = [];
function jqchk2(checkName)
{
    chk_value = [];
    chk_data = [];
    chk_dat = [];
    $("input[name=" + checkName + "]:checked").each(function() {
        chk_value.push($(this).val());
        chk_data.push($(this).attr('data'));
        chk_dat.push($(this).attr('dat'));
    });
}
function buttonAction(checkName, method1, method2, message, layertitle) {
    if ($("input:checkbox[name='" + checkName + "']").is(':checked')) {
        //id : 登录人 auid：审核人 pid 项目id
        var pid = chk_value[0];
        $.ajax({
            type : 'POST',
            url  : controller + "/" + method1,
            data : {
                proid : pid
            },
            success : function (msg) {
                if (msg == 1) {
                    layer.open({
                        type : 2,
                        title : layertitle,
                        area : ['90%', '80%'],
                        end        : function () {
                            location.reload();
                        },
                        content : controller + "/" + method2 + "/id/" + pid //iframe的url
                    });
                } else {
                    layer.msg(message, {icon : 5});
                }
            }
        });
    } else {
        layer.alert('请选中项目');return false;
    }
}
$("#updPrj").on('click', function () {
    buttonAction('prjCheckBox', 'updateProject','updateProject','非项目参与人不能更新进度','项目进度更新');
});
$('#chgPrj').on('click', function() {
    buttonAction('prjCheckBox', 'checkChange', 'changeProject','您不是项目验收人', '项目变更');
});
$('#actPrj').on('click', function() {
    if ($("input:checkbox[name='prjCheckBox']").is(':checked')) {
        //id : 登录人 auid：审核人 pid 项目id
        var pid = chk_value[0];
        $.ajax({
            type : 'POST',
            url  : controller + "/checkChange",
            data : {
                proid : pid
            },
            success : function (msg) {
                if (msg == 1) {
                    layer.confirm(
                        '确定进入验收阶段？',
                        {
                            btn : ['验收', '再想想']
                        },
                        function () {
                            $.ajax({
                                type : 'POST',
                                url : controller + "/accessProject",
                                data : {
                                    proid : pid
                                },
                                success : function (rst) {
                                    if (rst == 1) {
                                        layer.msg(
                                            '项目已处于验收状态',
                                            {
                                                icon : 6,
                                                time : 1000
                                            },
                                            function () {
                                                window.location.reload();
                                            }
                                        );
                                    } else {
                                        layer.msg(
                                            '您不是项目验收人',
                                            {
                                                icon : 5,
                                                time : 1000
                                            },
                                            function() {
                                                window.location.reload();
                                            }
                                        );
                                    }
                                }
                            });
                        },
                        function () {
                            layer.msg('ok',
                                {
                                    icon : 5,
                                    time : 1000
                                });
                        }
                    );

                } else {
                    layer.msg('您不是项目验收人', {icon : 5});
                }
            }
        });
    } else {
        layer.alert('请选中项目');return false;
    }
});

$("#checkProject").on('click', function() {
    if ($("input:checkbox[name='resCheckBox']").is(':checked')) {
        //id : 登录人 auid：审核人 pid 项目id
        var id = $("#rolestaff").val();
        var auid = chk_data[0];
        var pid = chk_value[0];

        layer.confirm('审核选项',
            {
                btn : ['有效','无效']
            }, function() {
                $.ajax({
                    type : 'POST',
                    url  : controller + '/checkProject',
                    data : {
                        proid : chk_value[0],
                        auid  : auid,
                        k     : 2
                    },
                    success : function(data) {
                        if (data == 2) {
                            layer.msg('操作成功', {icon: 6,time:2000});
                            window.location.reload();
                        } else {
                            layer.msg('好像出错了', {icon: 5,time:2000});
                            window.location.reload();
                        }
                    }
                });
            }, function() {
                $.ajax({
                    type : 'POST',
                    url  : controller + '/checkProject',
                    data : {
                        proid : chk_value[0],
                        auid  : auid,
                        k     : 1
                    },
                    success : function(data) {
                        if (data == 2) {
                            layer.msg('操作成功', {icon: 6,time:2000});
                            window.location.reload();
                        } else {
                            layer.msg('好像出错了', {icon: 5,time:2000});
                            window.location.reload();
                        }
                    }
                });
            });
    } else {
        layer.alert('请选中要审核的项目');
    }
});

/*----------------------------end-----------------------------------*/


$('#submitContent').on('click',function(){
    var _content = $('#updContent').val();
    var _proid   = $('#hiddenPrj').val();
    if (_content == "") {
        layer.alert(
            '提交内容为空！',
            {
                icon : 5,
                time : 1000
            }
        );
        return false;
    }
    $.ajax({
        type : 'POST',
        data : {
            content : _content,
            proid   : _proid
        },
        url : controller + "/updateProjectOk",
        success : function(msg){
            $("input[type=button]").attr('disabled',true);
            if (msg['status'] == 2) {
                layer.msg(
                    '提交成功',
                    {
                        icon : 6,
                        time : 500
                    },
                    function () {
                        window.parent.location.reload();
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    });

            } else if (msg['status'] == 3) {
                layer.msg(
                    '提交失败',
                    {
                        icon : 5,
                        time : 1000
                    },
                    function() {
                        window.location.reload();
                    }
                );
            }
        },
        error : function(){
            layer.msg(
                '提交失败',
                {
                    icon : 5,
                    time : 1000
                },
                function() {
                    window.location.reload();
                }
            );
        }
    });
});
/*-----------------------------------end-----------------------------------------*/

/*-----------------------------------项目更新审核 上级有权限---------------------*/
$("#checkProgress").on('click', function() {

    if ($("input:checkbox[name='progressCheck']").is(':checked')) {
        var id = $("#rolePrj").val();//本人id
        var pid = chk_dat[0]; //每条记录id
        var prjerid = chk_data[0]; //每条记录id

        
        layer.confirm(
            '审核选项',
            {
                btn : ['有效','无效']
            },
            function() {
                $.ajax({
                    type : 'POST',
                    url  : controller + '/checkProgress',
                    data : {
                        prjid : chk_value[0],
                        prjerId : prjerid,
                        conid : pid,
                        k     : 2
                    },
                    success : function(data2) {
                        switch (data2) {
                            case 2 : 
                                layer.msg('审核通过',
                                    {
                                        icon : 6,
                                        time : 500
                                    },
                                    function () {
                                        window.location.reload();
                                    });break;
                            case 3 :
                                layer.msg('审核失败，联系管理员',
                                    {
                                        icon : 6,
                                        time : 500
                                    },
                                    function () {
                                        window.location.reload();
                                    });break;
                            case 4 :
                                layer.msg('无权限审核',
                                    {
                                        icon : 6,
                                        time : 500
                                    },
                                    function () {
                                        window.location.reload();
                                    });break; 
                        }
                    }      
                });
            },
            function() {
                $.ajax({
                    type : 'POST',
                    url  : controller + '/checkProgress',
                    data : {
                        prjid : chk_value[0],
                        prjerId : prjerid,
                        conid : pid,
                        k     : 1
                    },
                    success : function(data3) {
                        switch (data3) {
                            case 2 : 
                                layer.msg('审核成功',
                                    {
                                        icon : 6,
                                        time : 500
                                    },
                                    function () {
                                        window.location.reload();
                                    });break;
                            case 3 :
                                layer.msg('审核失败，联系管理员',
                                    {
                                        icon : 6,
                                        time : 500
                                    },
                                    function () {
                                        window.location.reload();
                                    });break; 
                            case 4 :
                                layer.msg('无权限审核',
                                    {
                                        icon : 6,
                                        time : 500
                                    },
                                    function () {
                                        window.location.reload();
                                    });break; 
                        }
                    }
                });
            });
                    
    } else {
        layer.alert('请选中客户');
    }
});

$('#comPrj').on('click', function() {
    if ($("input:checkbox[name='dePrjCheckBox']").is(':checked')) {
        //id : 登录人 auid：审核人 pid 项目id
        var pid = chk_value[0];
        $.ajax({
            type : 'POST',
            url  : controller + "/checkChange",
            data : {
                proid : pid
            },
            success : function (msg) {
                if (msg == 1) {
                    layer.confirm(
                        '项目验收通过，提交完成？',
                        {
                            btn : ['完成', '再想想']
                        },
                        function () {
                            $.ajax({
                                type : 'POST',
                                url : controller + "/completePrj",
                                data : {
                                    proid : pid
                                },
                                success : function (rst) {
                                    if (rst == 1) {
                                        layer.msg(
                                            '项目完成',
                                            {
                                                icon : 6,
                                                time : 1000
                                            },
                                            function () {
                                                window.location.reload();
                                            }
                                        );
                                    } else {
                                        layer.msg(
                                            '出问题了，联系开发人员',
                                            {
                                                icon : 5,
                                                time : 1000
                                            },
                                            function() {
                                                window.location.reload();
                                            }
                                        );
                                    }
                                }
                            });
                        },
                        function () {
                            layer.msg('ok',
                                {
                                    icon : 5,
                                    time : 1000
                                });
                        }
                    );

                } else {
                    layer.msg('您不是项目验收人', {icon : 5});
                }
            }
        });
    } else {
        layer.alert('请选中项目');return false;
    }
});
/**
 * Created by ml on 2017/6/20.
 */
