/**
 * Created by ml on 2017/8/25.
 */
var co;
var kid;
var busBtn = $(".businessBtn");
checkBoxSel('unChecked');
function cusButtonClick(keyValue, title, method, iframeWidth, iframeHeight)
{
    layer.closeAll();
    busBtn.attr('disabled', true);
    if (keyValue){
        $.ajax({
            type    : 'POST',
            url     : controller + "/checkUser",
            data    : {
                cid : keyValue
            },
            success : function (msg) {   
                switch (msg) {
                    case 1 :
                        layer.alert("仅客户负责人有权执行该操作",
                            {
                                icon : 5,
                                end : function () {
                                    $(".dataTables-Business tbody tr").each(function () {
                                        if ($(this).hasClass('selected')) {
                                            $(this).removeClass('selected');
                                        }
                                    });
                                    busBtn.attr('disabled', false);
                                }
                            } 
                        );
                        break;
                    case 2 :
                        layer.open({
                            type  : 2,
                            title : title,
                            end : function () {
                                $(".dataTables-Business tbody tr").each(function () {
                                    if ($(this).hasClass('selected')) {
                                        $(this).removeClass('selected');
                                    }
                                });
                                busBtn.attr('disabled', false);
                            },
                            area : [iframeWidth, iframeHeight],
                            content: controller + "/" +  method +"/cusId/" + keyValue //iframe的url
                        });
                        break;
                }
            }
        });
    } else {
        layer.alert('请选中客户');
        busBtn.attr('disabled', false);
    }
}
function cusButtonAction(keyValue, title, method, iframeWidth, iframeHeight)
{
    layer.closeAll();
    busBtn.attr('disabled', true);
    if (keyValue){
        layer.open({
            type  : 2,
            title : title,
            end : function () {
                $(".dataTables-Business tbody tr").each(function () {
                    if ($(this).hasClass('selected')) {
                        $(this).removeClass('selected');
                    }
                });
                busBtn.attr('disabled', false);
            },
            area : [iframeWidth, iframeHeight],
            content: controller + "/" +  method +"/cusId/" + keyValue //iframe的url
        });
    } else {
        layer.alert('请选中客户');
        busBtn.attr('disabled', false);
    }
}
tableCusDiv.delegate('tbody tr', 'mouseover', function() {
    co = $(this).css('background-color');
    return co;
});
function changeCss(tdNum, flag){
    if (flag == 1) {
        tableCusDiv.delegate('tbody td', 'mouseover', function () {
            if (cusContactTime.val() != 30) {
                var tdIndex = $(this).parent()['context']['cellIndex'];
                if(tdNum == tdIndex) {
                    $(this).addClass('selected');
                }
            } else {
                return false;
            }
        });
        tableCusDiv.delegate('tbody td', 'mouseleave',  function () {
            $(this).removeClass('selected');
        });
    } else {
        tableCusDiv.delegate('tbody td', 'mouseover', function () {
            if (cusContactTime.val() == 30) {
                var tdIndex = $(this).parent()['context']['cellIndex'];
                if(tdNum == tdIndex) {
                    $(this).addClass('selected');
                }
            } else {
                return false;
            }
        });
        tableCusDiv.delegate('tbody td', 'mouseleave',  function () {
            $(this).removeClass('selected');
        });
    }
}
changeCss(0, 1);
changeCss(2, 1);
changeCss(3, 1);
changeCss(4, 1);
changeCss(5, 1);
changeCss(6, 1);
changeCss(0, 2);
changeCss(2, 2);
changeCss(3, 2);
/**
 * [getCount 将单元格中的内容转化为数字]
 * @param  {[string]} charText      [td里面的text]
 * @return {[int]}  count
* */
function getCount(charText)
{
    return parseInt(charText.substr(charText.indexOf("/") + 1, charText.length - charText.indexOf("/") - 2));
}
/**
 * [showNumDetail 鼠标触发事件 mouseover显示对应内容，mouseout关闭所有tips]
 * @param  {[int]} k      [列的index]
 * @param  {[string]} method [后台方法]
 */

function showNumDetail(k, method, type)
{
    var timer;
    if (type == 1) {
        tableCusDiv.delegate('tbody td', 'mouseenter', function(e) {
            clearTimeout(timer);
            var cellindex = $(this).parent();
            var cusid     = $(this).parent()[0].id;
            var tdIndex   = cellindex['context']['cellIndex'];
            var count     = ($(this).text());
            var num       = $(this).parent();
            kid = document.getElementById('cus-contact-time').value;
            timer = setTimeout(function() {
                //这里触发hover事件
                if (cusContactTime.val() != 30) {
                    if (tdIndex == k) {
                        e.stopPropagation();
                        count = count.indexOf("/") >= 0 ? getCount(count) : parseInt(count);
                        if (count != 0) {
                            $.ajax({
                                type : 'GET',
                                url : controller + "/" + method + "/id/" + cusid + "/k/" + kid,
                                success : function (ajaxData) {
                                    var contents = "";
                                    switch (k) {
                                        case 2 :
                                            contents = "客户联系记录：<br>";
                                            for (var i = 0; i < ajaxData.length; i++) {
                                                contents += '时间：' + ajaxData[i]['posttime'] + "&emsp;主题：" + ajaxData[i]['theme'] + "&emsp;联系类型：" + ajaxData[i]['ctype'] + "&emsp;填写人：" + ajaxData[i]['pname'] + "<br/>详细内容：<br/>" + ajaxData[i]['content'] + "<br/>";
                                            }
                                            break;
                                        case 3 :
                                            contents = "项目进度记录：<br>";
                                            for (var i = 0; i < ajaxData.length; i++) {
                                                contents += '更新时间：' + ajaxData[i]['posttime'] + "&emsp;项目名称：" + ajaxData[i]['prjname'] + "&emsp;填写人：" + ajaxData[i]['prjername'] + "<br/>内容：<br/>" + ajaxData[i]['prjcontent'] + "<br/>";
                                            }
                                            break;
                                        case 5 :
                                            contents = "售后记录：<br>";
                                            for (var i = 0; i < ajaxData.length; i++) {
                                                contents+='售后单号：<span style="color:red;">'+ ajaxData[i]['sale_number']+'</span>&emsp;&emsp;&emsp;&emsp;维修单状态：<span style="color:red;">'+ajaxData[i]['is_ok']+'</span>&emsp;&emsp;&emsp;&emsp;审核状态：<span style="color:red;">'+ajaxData[i]['is_show']+'</span><br/>';
                                                for (var j =0; j<ajaxData[i]['data'].length; j++){
                                                    contents +='更新时间：'+ ajaxData[i]['data'][j]['change_status_time'] +"&emsp;&emsp;&emsp;更新人：" + ajaxData[i]['data'][j]['changemanname'] + "&emsp;&emsp;&emsp;更新内容：" + ajaxData[i]['data'][j]['change_status'] + "<br/>";
                                                }
                                            }
                                            break;
                                        case 4 :
                                            contents = "客服记录：<br>";
                                            for (var i = 0; i < ajaxData.length; i++) {
                                                contents += '时间：' + ajaxData[i]['addtime'] + "&emsp;来电人：" + ajaxData[i]['caller'] + "&emsp;客服：" + ajaxData[i]['pname'] + "&emsp;审核状态：" + ajaxData[i]['austatus'] + "<br/>客户问题：" + ajaxData[i]['content'] + "<br/>" + "处理方式：" + ajaxData[i]['answercontent'] + "<br/>";
                                            }
                                            break;
                                        case 6 :
                                            contents = "采购记录：<br>";
                                            for (var i = 0; i < ajaxData.length; i++) {
                                                contents += 'K3订单号：' + ajaxData[i]['order_K3'] + '&emsp;时间：' + ajaxData[i]['otime'] + '&emsp;类型：<span class="text-danger bg-info">' + ajaxData[i]['order_type'] + "</span>&emsp;票货情况：" + ajaxData[i]['invoice_situation'] + '&emsp;付款方式：' + ajaxData[i]['invoice_type'] + "&emsp;审核进度：<span class='text-danger bg-success'>" + ajaxData[i]['check_status'] + "</span>&emsp;金额：<span class='text-danger bg-info'>" + ajaxData[i]['oprice'] + "（元）</span><br/>";
                                            }
                                            break;
                                    }
                                    if (ajaxData.length) {
                                        layer.tips(
                                            contents, num, {
                                                tips: [1, '#3595CC'],
                                                area: '900px',
                                                time: 100000
                                            });
                                    } else {
                                        return false;
                                    }
                                }
                            });
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }, 1000);
        });
    } else {
        tableCusDiv.delegate('tbody td', 'mouseenter', function(e) {
            clearTimeout(timer);
            var cellindex = $(this).parent();
            var cusid = $(this).parent()[0].id;
            var tdIndex = cellindex['context']['cellIndex'];
            kid = document.getElementById('cus-contact-time').value;
            timer = setTimeout(function() {
                //这里触发hover事件
                if (cusContactTime.val() == 30) {
                    if (tdIndex == k) {
                        e.stopPropagation();
                        var count = ($(this).text());
                        count = count.indexOf("/") >= 0 ? getCount(count) : parseInt(count);
                        if (count != 0) {
                            var num = $(this).parent();
                            $.ajax({
                                type: 'GET',
                                url: controller + "/" + method + "/id/" + cusid + "/k/" + kid,
                                success: function (ajaxData) {
                                    var contents = "";
                                    switch (k) {
                                        case 2 :
                                            contents = "客户联系记录：<br>";
                                            for (var i = 0; i < ajaxData.length; i++) {
                                                contents += '时间：' + ajaxData[i]['posttime'] + "&emsp;主题：" + ajaxData[i]['theme'] + "&emsp;联系类型：" + ajaxData[i]['ctype'] + "&emsp;填写人：" + ajaxData[i]['pname'] + "<br/>详细内容：<br/>" + ajaxData[i]['content'] + "<br/>";
                                            }
                                            break;
                                        case 3 :
                                            contents = "采购记录：<br>";
                                            for (var i = 0; i < ajaxData.length; i++) {
                                                contents += 'K3订单号：' + ajaxData[i]['order_K3'] + '&emsp;时间：' + ajaxData[i]['otime'] + '&emsp;类型：<span class="text-danger bg-info">' + ajaxData[i]['order_type'] + "</span>&emsp;票货情况：" + ajaxData[i]['invoice_situation'] + '&emsp;付款方式：' + ajaxData[i]['invoice_type'] + "&emsp;审核进度：<span class='text-danger bg-success'>" + ajaxData[i]['check_status'] + "</span>&emsp;金额：<span class='text-danger bg-info'>" + ajaxData[i]['oprice'] + "（元）</span><br/>";
                                            }
                                            break;
                                    }
                                    if (ajaxData.length) {
                                        layer.tips(
                                            contents, num, {
                                                tips: [1, '#3595CC'],
                                                area: '900px',
                                                time: 100000
                                            });
                                    }
                                }
                            });
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }, 1000);
        });
    }
    tableCusDiv.delegate('tbody td', 'mouseleave',function(e) {
        layer.closeAll('tips');
        clearTimeout(timer);
    });
}

showNumDetail(2, 'showContactRecordList', 1);
showNumDetail(3, 'showPrjUpdateList', 1);
showNumDetail(4, 'showOnlineServiceList', 1);
showNumDetail(5, 'showSaleServiceList', 1);
showNumDetail(6, 'showSaleOrderList', 1);
showNumDetail(2, 'showContactRecordList', 2);
showNumDetail(3, 'showSaleOrderList', 2);
var unCheckCusTable = $(".dataTables-unChecked tbody .saleList");
unCheckCusTable.on('mouseover' ,function(e) {
    var cellindex = $(this).parent();
    e.stopPropagation();
    var count = ($(this).text());
    var cusid = $(this).attr('data');
    if (count != 0) {
        var num = $(this).parent();
        $.ajax({
            type : 'GET',
            url : controller + "/showContactRecordList/id/" + cusid + "/k/7",
            success : function (ajaxData) {
                var contents;
                contents = "客户联系记录：";
                for(var i = 0; i < ajaxData.length; i++) {
                    contents += '时间：' +
                        ajaxData[i]['posttime'] +
                        "&emsp;主题：" + ajaxData[i]['theme'] +
                        "&emsp;联系类型：" + ajaxData[i]['ctype'] +
                        "&emsp;填写人：" + ajaxData[i]['pname'] +
                        "<br/>详细内容：<br/>" + ajaxData[i]['content'] + "<br/>";
                }
                layer.tips(
                    contents, num, {
                        tips : [1, '#3595CC'],
                        area : '500px',
                        time : 100000
                    }
                );
            }
        });
    } else {
        return false;
    }
});
unCheckCusTable.on('mouseout' ,function(e) {
    layer.closeAll('tips');
});


$("#cusAdd").on('click', function() {
    layer.open({
        type : 2,
        title : '客户添加',
        end : function () {
	    var oTable = $(".dataTables-Business").DataTable();
    
       	    if (selected[0] != undefined) {
                selected.splice(0, selected.length);
            }

            oTable.ajax.reload();
        },
        area : ['90%', '90%'],
        content : controller + "/addCustomer"
    });
});



tableCusDiv.delegate('tbody .cusDetail', 'click', function (e) {
    var cusid = $(this).parent()[0].id;
    e.stopPropagation();
    layer.open({
        type: 2,
        title: '详情页',
	end : function () {
            oTable = $(".dataTables-Business").DataTable();
            if (selected[0] != undefined) {
                selected.splice(0, selected.length);
            }
           
        },
        area: ['100%', '100%'],
        content: controller + "/showBusinessDetail/cusId/" + cusid //iframe的url
    });
});

tableCusDiv.delegate( 'tbody tr', 'click',  function () {
    var oTables = $('.dataTables-Business').DataTable();
    if ( $(this).hasClass('selected') ) {
        $(this).removeClass('selected');
    } else {
        oTables.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    }
});


function getSelectedValue()
{
    var id;
    $(".dataTables-Business tbody tr").each(function () {
        if ($(this).hasClass('selected')) {
            id = $(this).attr('id');
        }
    });
    return id;
}
function abandon(checkboxName, chk_value)
{
    if ($("input:checkbox[name='" + checkboxName +"']").is(':checked')) {
        layer.confirm('确定放弃该客户？',
            {
                btn : ['确定','返回']
            }, function() {
                layer.open({
                    type: 2,
                    title: '客户放弃申请',
                        end : function () {
                            oTable = $(".dataTables-Business").DataTable();
                            oTable.ajax.reload(null,false);
                            if (selected[0] != undefined) {
                                selected.splice(0, selected.length);
                            }
                    },
                    area: ['100%', '100%'],
                    content: controller + "/removeCustomer/cusId/" + chk_value //iframe的url
                });
                // $.ajax({
                //     type : 'POST',
                //     url  : controller + "/removeCustomer",
                //     data : {
                //         id : chk_value
                //         },
                //     success : function(data) {
                //         if (data['status'] == 1) {
                //             layer.msg('操作成功',
                //                 {
                //                     icon: 6,
                //                     time : 500
                //                 },
                //                 function () {
                //                     window.location.reload();
                //                 });
                //         } else if(data['status'] == 2) {
                //             layer.msg('好像出错了',
                //                 {
                //                     icon : 5,
                //                     time : 500
                //                 },
                //                 function () {
                //                     window.location.reload();
                //                 });
                //         } else if(data['status'] == 3) {
                //             layer.msg('仅本人可放弃',
                //                 {
                //                     icon : 5,
                //                     time : 500
                //                 },
                //                 function () {
                //                     window.location.reload();
                //                 });
                //         } else if (data['status'] == 4) {
                //             layer.confirm('该客户为' + data['name'] + '的子公司,如果放弃，该客户的上级公司及对应子公司将被放弃',
                //                 {
                //                     btn : ['确定放弃', '返回']
                //                 },
                //                 function () {
                //                     $.ajax({
                //                         type : 'POST',
                //                         url  : controller + "/removeCustomer",
                //                         data : {
                //                             id : chk_value,
                //                             flag : 4
                //                         },
                //                         success : function (data) {
                //                             if (data['status'] == 2) {
                //                                 layer.msg('操作成功，该客户已被放弃',
                //                                     {
                //                                         icon : 5,
                //                                         time : 500
                //                                     },function () {
                //                                         window.location.reload();
                //                                     }
                //                                 );
                //                             } else {
                //                                 layer.msg('操作失败',
                //                                     {
                //                                         icon : 5,
                //                                         time : 500
                //                                     },function () {
                //                                         window.location.reload();
                //                                     }
                //                                 )
                //                             }
                //                         }
                //                     });
                //                 });
                //
                //         } else if (data['status'] == 5) {
                //             layer.confirm('该客户存在子公司,确定放该客户及下属子公司？',
                //                 {
                //                     btn : ['确定放弃', '返回']
                //                 },
                //                 function () {
                //                     $.ajax({
                //                         type : 'POST',
                //                         url  : controller + "/removeCustomer",
                //                         data : {
                //                             id : chk_value,
                //                             flag : 5
                //                         },
                //                         success : function (data) {
                //                             if (data['status'] == 2) {
                //                                 layer.msg('操作成功，该客户已被放弃',
                //                                     {
                //                                         icon : 5,
                //                                         time : 500
                //                                     },function () {
                //                                         window.location.reload();
                //                                     }
                //                                 );
                //                             } else {
                //                                 layer.msg('操作失败');
                //                             }
                //                         }
                //                     });
                //                 });
                //         }
                //     }
                // });
            }, function() {
                layer.msg('ok', {icon: 6});
                return false;
            });
    } else {
        layer.alert('请选中客户');
    }
}

$("#removeSel2").on('click', function (){
    var chk = jqchk('checkBox2');
    abandon('checkBox2',chk['value']);
});
$("#removeSel").on('click', function() {
    $(this).attr('disabled','disabled');
    var selected = getSelectedValue();
    if (selected) {
        var layerIndex = layer.confirm('确定放弃该客户？',
            {
                btn : ['确定','返回'],
                end : function () {
                    $(".dataTables-Business tbody tr").each(function () {
                        if ($(this).hasClass('selected')) {
                            $(this).removeClass('selected');
                        }
                    });
                    $("#removeSel").attr('disabled', false);
                }
            }, function() {
                layer.close(layerIndex);
                layer.open({
                    type: 2,
                    title: '客户放弃申请',
                    end : function () {
                        oTable = $(".dataTables-Business").DataTable();
                        oTable.ajax.reload(null,false);
                    },
                    area: ['60%', '40%'],
                    content: controller + "/removeCustomer/cusId/" + selected //iframe的url
                });
                // $.ajax({
                //     type : 'POST',
                //     url  : controller + "/removeCustomer",
                //     data : { id : selected },
                //     success : function(data) {
                //         if (data['status'] == 1) {
                //             layer.msg('操作成功',
                //                 {
                //                     icon: 6,
                //                     time : 500
                //                 },
                //                 function () {
                //                     $("#removeSel").attr('disabled',false);
                //                     busTable.ajax.reload();
                //                 });
                //         } else if(data['status'] == 2)
                //         {
                //             layer.msg('好像出错了',
                //                 {
                //                     icon : 5,
                //                     time : 500
                //                 },
                //                 function () {
                //                     $(".dataTables-Business tbody tr").each(function () {
                //                         if ($(this).hasClass('selected')) {
                //                             $(this).removeClass('selected');
                //                         }
                //                     });
                //                     $("#removeSel").attr('disabled', false);
                //                 });
                //         } else if(data['status'] == 3) {
                //             layer.msg('仅本人可放弃',
                //                 {
                //                     icon : 5,
                //                     time : 500
                //                 },
                //                 function () {
                //                     $(".dataTables-Business tbody tr").each(function () {
                //                         if ($(this).hasClass('selected')) {
                //                             $(this).removeClass('selected');
                //                         }
                //                     });
                //                     $("#removeSel").attr('disabled', false);
                //                 });
                //         } else if (data['status'] == 4) {
                //             layer.confirm('该客户为' + data['name'] + '的子公司,如果放弃，该客户的上级公司及对应子公司将被放弃',
                //                 {
                //                     btn : ['确定放弃', '返回']
                //                 },
                //                 function () {
                //                     $.ajax({
                //                         type : 'POST',
                //                         url  : controller + "/removeCustomer",
                //                         data : {
                //                             id : selected,
                //                             flag : 4
                //                         },
                //                         success : function (data) {
                //                             if (data['status'] == 2) {
                //                                 layer.msg('操作成功，该客户已被放弃',
                //                                     {
                //                                         icon : 5,
                //                                         time : 500
                //                                     },function () {
                //                                         $("#removeSel").attr('disabled',false);
                //                                         busTable.ajax.reload();
                //                                     }
                //                                 );
                //                             } else {
                //                                 layer.msg("操作失败",
                //                                 {
                //                                     icon : 6,
                //                                     time : 500
                //                                 }, function () {
                //                                     $(".dataTables-Business tbody tr").each(function () {
                //                                         if ($(this).hasClass('selected')) {
                //                                             $(this).removeClass('selected');
                //                                         }
                //                                     });
                //                                     $("#removeSel").attr('disabled', false);
                //                                 });
                //                             }
                //                         }
                //                     });
                //                 });
                //
                //         } else if (data['status'] == 5) {
                //             layer.confirm('该客户存在子公司,如果放弃，该客户的下属公司将被一同放弃',
                //                 {
                //                     btn : ['确定放弃', '返回']
                //                 },
                //                 function () {
                //                     $.ajax({
                //                         type : 'POST',
                //                         url  : controller + "/removeCustomer",
                //                         data : {
                //                             id : selected,
                //                             flag : 5
                //                         },
                //                         success : function (data) {
                //                             if (data['status'] == 2) {
                //                                 layer.msg('操作成功，该客户已被放弃',
                //                                     {
                //                                         icon : 5,
                //                                         time : 500
                //                                     },function () {
                //                                         $("#removeSel").attr('disabled',false);
                //                                         busTable.ajax.reload();
                //                                     }
                //                                 );
                //                             } else {
                //                                 layer.msg("操作失败",
                //                                 {
                //                                     icon : 6,
                //                                     time : 500
                //                                 }, function () {
                //                                     $(".dataTables-Business tbody tr").each(function () {
                //                                         if ($(this).hasClass('selected')) {
                //                                             $(this).removeClass('selected');
                //                                         }
                //                                     });
                //                                     $("#removeSel").attr('disabled', false);
                //                                 });
                //                             }
                //                         }
                //                     });
                //                 });
                //         }
                //     }
                // });
            }, function() {
                layer.msg('ok', {icon: 6});
                $("#removeSel").attr('disabled', false);
                return false;
            });
    } else {
        layer.alert('请选中客户');
        $("#removeSel").attr('disabled', false);
    }
});
$("#changeCus").on('click', function() {
    var selectedId = getSelectedValue();
    cusButtonClick(selectedId, '修改客户信息', 'editCustomer', '92%', '80%');
});
$("#changeCusName").on('click', function () {
    var selectedId = getSelectedValue();
    cusButtonClick(selectedId, '客户名变更申请', 'editCustomerName', '70%', '70%');
});
$("#addOrder").on('click', function() {
    var selectedId = getSelectedValue();
    cusButtonClick(selectedId, '添加采购订单', 'addOrder', '100%', '100%');
});
$("#addContact").on('click', function() {
    var selectedId = getSelectedValue();
    cusButtonClick(selectedId, '添加联系记录', 'addContactRecords', '92%', '80%');
});

$("#addContacter").on('click', function() {
    var selectedId = getSelectedValue();
    cusButtonClick(selectedId, '添加客户联系人', 'addCusContact', '92%', '80%');
});
$("#changeCus2").on('click', function() {
    var chk = jqchk('checkBox2');
    cusButtonClick(chk['value'], '修改客户信息','editCustomer', '92%', '80%');
});
$("#addContact2").on('click', function() {
    var chk = jqchk('checkBox2');
    cusButtonClick(chk['value'], '添加联系记录','addContactRecords', '92%', '80%');
});
$("#setKpi").on('click', function() {
    var selectedId = getSelectedValue();
    cusButtonAction(selectedId, '添加kpi客户','addKpiCusAudit', '92%', '80%');
});

$("span .unCheck").css('color','red');
$("span .allRecord").css('color','blue');
$(".noCheckYet").css('color','black');
$(".checkNot").css('color','red');
$(".checkYes").css('color','blue');

