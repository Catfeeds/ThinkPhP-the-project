/**
 * Created by ml on 2017/6/15.
 */


var chk_value = [], chk_data = [],dat = [];
function jqchk(checkboxname)
{
    chk_value = [];
    chk_data  = [];
    chk_dat  = [];
    $("input[name=" + checkboxname + "]:checked").each(function() {
        chk_value.push($(this).val());
        chk_data.push($(this).attr('data'));
        chk_dat.push($(this).attr('dat'));
    });
}
function checkList(checboxName, roleID, method)
{
    if ($("input:checkbox[name='" + checboxName + "']").is(':checked')) {
        var id = $("#" + roleID).val();//本人id
        var auid = chk_data[0];//创建项目的业务员id
        var pid = chk_dat[0]; //每条记录id
        $.ajax({
            type : 'POST',
            url : controller + "/checkRole",
            data : {userId : id},
            success : function (msg) {
                if (msg['status'] != 1) {
                    if (id != auid) {
                        layer.alert('您不是审核人',{icon : 3});
                        return false;
                    } else {
                        layer.confirm('审核选项',
                            {
                                btn : ['有效','无效']
                            }, function() {
                                $.ajax({
                                    type : 'POST',
                                    url  : controller + "/" + method,
                                    data : {
                                        prjid : chk_value[0],
                                        auid  : auid,
                                        conid : pid,
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
                                    url  : controller + "/" + method,
                                    data : {
                                        prjid : chk_value[0],
                                        auid  : auid,
                                        conid : pid,
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
                    }
                } else {
                    layer.confirm('超管审核选项',
                        {
                            btn : ['有效','无效']
                        }, function() {
                            $.ajax({
                                type : 'POST',
                                url  : controller + "/" + method,
                                data : {
                                    prjid : chk_value[0],
                                    auid  : auid,
                                    conid : pid,
                                    k     : 3
                                },
                                success : function(data) {
                                    if (data == 2) {
                                        layer.msg('操作成功', {icon: 6, time:2000});
                                        window.location.reload();
                                    } else {
                                        layer.msg('好像出错了', {icon: 5, time:2000});
                                        window.location.reload();
                                    }
                                }
                            });
                        }, function() {
                            $.ajax({
                                type : 'POST',
                                url  : controller + "/" + method,
                                data : {
                                    prjid : chk_value[0],
                                    auid  : auid,
                                    conid : pid,
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
                }
            }
        });

    } else {
        layer.alert('请选中客户');
    }
}



/***********************************home js*********************************************/
var co;
$(".dataTables-Business tbody").on('mouseover', function() {
    co = $(this).css('background-color');
    return co;
});












$(".cusList").on('click', function(e) {
    var cusid = $(this).attr('data');
    e.stopPropagation();
    layer.open({
        type: 2,
        title: '客户列表',
        area: ['100%', '90%'],
        content: controller + "/showCustomerList/cusId/" + cusid //iframe的url
    });
});



/***********************************end--home js--*********************************************/

/***********************************contrecordlist js--*********************************************/


$("#checkCon").on('click', function() {
    checkList('contactCheckBox','roleCon','checkContact');
});
/***********************************prjUpdateList js--*********************************************/
$("#checkPrj").on('click', function() {
    checkList('prjCheckBox','rolePrj','checkPrj');
});





