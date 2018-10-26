/**
 * Created by ml on 2017/8/25.
 * cus audit.js
 */
MultiCheckBoxSel();
$('.cusDetail').on('click',function (e) {
    var cusid = $(this).attr('data');
    e.stopPropagation();
    layer.open({
        type: 2,
        title: '详情页',
        area: ['100%', '100%'],
        content: controller + "/showBusinessDetail/cusId/" + cusid //iframe的url
    });
});
$(".dataTables-example tbody .saleList").on('mouseover' ,function(e) {
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
                var contents = "";
       
                contents = "客户联系记录：";
                for(var i = 0; i < ajaxData.length; i++) {
                    contents += '时间：' + ajaxData[i]['posttime'] + "&emsp;主题：" + ajaxData[i]['theme'] + "&emsp;联系类型：" + ajaxData[i]['ctype'] + "&emsp;填写人：" + ajaxData[i]['pname'] +  "<br/>详细内容：<br/>" + ajaxData[i]['content'] + "<br/>";
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
        layer.msg(
            '没有记录',
            { time : 500 });
    }
});
$(".dataTables-example tbody .saleList").on('mouseout' ,function(e) {
    layer.closeAll('tips');
});

$("#cusNameForSearch").on('keyup', function () {
    var _name = "";
    var _num = "";
    _name = $("#cusNameForSearch").val();
    _num = $("#cusNum").val();
    if ((_name || _num) != "") {
        $.ajax({
            type : 'POST',
            url  : controller + "/showCustomer",
            data : {
                cusName : _name,
                cusNum  : _num
            },
            success : function (msg) {
                var trInner = "";
                var cid = [], cname = [], addtime =[], counts=[],keyword=[],uname=[];

                for(var i = 0; i < msg.length; i++) {
                    cid[i]     = msg[i]['cid'];
                    cname[i]   = msg[i]['cname'];
                    addtime[i] = msg[i]['addtime'];
                    counts[i]  = msg[i]['counts'];
                    keyword[i] = msg[i]['indusname'];
                    uname[i]   = msg[i]['uname'];
                    trInner   += "<tr class='btnadd' data='"+cid[i]+"'><td>" + cname[i] + "</td><td>" + keyword[i] + "</td><td>" + uname[i] + "</td><td>" + addtime[i] + "</td>"
                        + "</tr>";
                }
                $("#listof").html('');
                $("#listof").append(trInner);
                // 相关按钮功能
            }
        });
    } else {
        layer.msg(
            "输入内容",
            {
                icon : 7,
                time : 500
            }
        );
    }
});
$("#checkCus").on('click', function() {
        if ($("input:checkbox[name='checkBox2']").is(':checked')) {
	    Multichk('checkBox2');
            var id = $("#role").val();//本人id
            var auid = chk_data[0];//
            var pid = chk_dat; //每条记录id
            var pids = pid.toString();
            console.log(pids);
            $.ajax({
                type : 'POST',
                url : controller + "/checkRole",
                data : {userId : id},
                success : function (msg) {
                    if (msg['status'] != 8) {
                            layer.confirm('审核选中的客户',
                                {
                                    btn : ['有效','无效']
                                }, function() {
                                    $.ajax({
                                        type : 'POST',
                                        url  : controller + "/checkCustomer",
                                        data : {
                                            prjid : chk_value[0],
                                            auid  : auid,
                                            conid : pids,
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
                                        url  : controller + "/checkCustomer",
                                        data : {
                                            prjid : chk_value[0],
                                            auid  : auid,
                                            conid : pids,
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
                        layer.confirm('超管审核选项',
                            {
                                btn : ['有效','无效']
                            }, function() {
                                $.ajax({
                                    type : 'POST',
                                    url  : controller + "/checkCustomer",
                                    data : {
                                        prjid : chk_value[0],
                                        auid  : auid,
                                        conid : pids,
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
                                    url  : controller + "/checkCustomer",
                                    data : {
                                        prjid : chk_value[0],
                                        auid  : auid,
                                        conid : pids,
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
});

