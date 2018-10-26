/**
 * Created by ml on 2017/8/25.
 */
function showAllcontent(layera, className, clickName) {
    $('.dataTables-' + className + ' tbody').on('mouseover','.' + clickName, function () {
        var content = $(this).attr('data');
        layera = layer.tips( content , this,{
            tips : [1, '#3595CC'],
            area : '500px',
            time : 100000
        });
        return layera;
    });
    $('.dataTables-' + className + ' tbody').on('mouseout', '.' + clickName, function () {
        layer.close(layera);
    });
}

function dataTabelset(className, length) {
    $(".dataTables-" + className).dataTable({
        'bFilter'        : true,
        'bLengthChange'  : false,
	    'autoWidth'      : false,
        'bInfo'          :　true,
        'iDisplayLength' :　length
    });
}

// 客户申请 客户详情页面点击切换显示的内容
function noneTable(name) {
    $("." + name).css('display', 'none');
}
function showRecords (idname, classname) {
    $("#" + idname).on('click', function () {

        noneTable('table-contacter');
        noneTable('table-order');
        noneTable('table-contactRecord');
        noneTable('table-prjUpdateList');
        noneTable('table-onlineSale');
        noneTable('table-saler');
        noneTable('table-cusfile');
        if ($("." + classname).css('display', 'none')) {
            $("." + classname).css('display', 'block');
        }
    });
}

// checkBox获取数据
function jqchk(checkboxname)
{
    var chk = [];
    $("input[name=" + checkboxname + "]:checked").each(function() {
        chk['value'] = $(this).val();
        chk['data']  = $(this).attr('data');
        chk['dat']   = $(this).attr('dat');
    });
    return chk;
}
var chk_value = [], chk_data = [],dat = [];
function Multichk(checkboxname)
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

function checkBoxSel(dataTableClass) {
    
    $(".dataTables-" + dataTableClass + " input:checkbox").on('click', function(e){
        var oTables = $('.dataTables-' + dataTableClass + '').DataTable();
        if ($(this).prop('checked')) {
            oTables.$('tr.selected').removeClass('selected');
            $(".dataTables-" + dataTableClass + " tbody tr").each(function () {
                $(this).find("input[type='checkbox']").prop('checked', false);
            });
        } else {
            $(this).parent().parent().addClass('selected');
            $(".dataTables-" + dataTableClass + " tbody tr").each(function () {
                $(this).find("input[type='checkbox']").prop('checked', false);
            });
            $(this).prop('checked', true);
        }
    });

    $(".dataTables-" + dataTableClass + " tbody tr").on('click', function () {
        var oTables = $('.dataTables-' + dataTableClass + '').DataTable();
        var check = $(this).find("input[type='checkbox']").prop('checked');
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        } else {
            oTables.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }

        if(check){
            $(".dataTables-" + dataTableClass + " tbody tr").each(function () {
                $(this).find("input[type='checkbox']").prop('checked', false);
            });
        } else {
            $(".dataTables-" + dataTableClass + " tbody tr").each(function () {
                $(this).find("input[type='checkbox']").prop('checked', false);
            });
            $(this).find("input[type='checkbox']").prop('checked', true);
        }
    });
}
function MultiCheckBoxSel()
{
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
        if(flag) {
            check[0].checked = false;
            $(this).css('background-color', co);
        } else {
            check[0].checked = true;
            $(this).css('background-color',"yellow");
        }
    }
});
}
function checkList(checkboxName, roleID, method)
{
    if ($("input:checkbox[name='" + checkboxName + "']").is(':checked')) {

        var id = $("#" + roleID).val();//本人id
        var checkValues = jqchk(checkboxName);
        /*var auid = ;//创建项目的业务员id
        var pid = chk_dat[0]; //每条记录id*/
        $.ajax({
            type : 'POST',
            url : controller + "/checkRole",
            data : {userId : id},
            success : function (msg) {
                if (msg['status'] != 8) {
                    if (id != checkValues['data']) {
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
                                        prjid : checkValues['value'],
                                        auid  : checkValues['data'],
                                        conid : checkValues['dat'],
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
                                        prjid : checkValues['value'],
                                        auid  : checkValues['data'],
                                        conid : checkValues['dat'],
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
                                    prjid : checkValues['value'],
                                    auid  : checkValues['data'],
                                    conid : checkValues['dat'],
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
                                    prjid : checkValues['value'],
                                    auid  : checkValues['data'],
                                    conid : checkValues['dat'],
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

$(".noCheckYet").css('color','black');
$(".checkNot").css('color','red');
$(".checkYes").css('color','blue');
