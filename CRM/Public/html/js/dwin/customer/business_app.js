/**
 * Created by ml on 2017/8/25.
 */
var layera;
var co;

function submitData(cid, au, flag) 
{
     $.ajax({
            type : 'POST',
            url  : controller + '/applicationOk',
            data : {
                cid       : cid,
                auditorid : au,
                flag      : flag
            },
            success : function (msg) {
                layer.msg(msg['msg'] + " 状态码" + msg['status'],
                    {
                        icon : 6,
                        time : 1000
                    },
                    function () {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    });
            }
        });
}
$("#cusApplication").on('click', function() {
    var au = $("#auditssss").val();
    var cid = $("#idOfCus").text();
    var relationId = $.trim($('#cusSubCheck').text());

    if (au == "") {
        layer.alert(
            '未指定业务审核人',
            {
                icon : 5
            }
        );
        return false;
    } else {
        // 是否有关联公司，做不同处理
        var flag = (relationId.length > 3) ? 1 : 2;
        if (flag == 1) {
            layer.confirm('该公司有关联公司，确认申请将一同申请为您的客户',
                {
                    btn : ['确认', '取消']
                },
                function () {
                    submitData(cid, au, flag);
                });
        } else {
            layer.confirm('是否确认申请该客户？',
                {
                    btn: ['确认', '取消']
                },
                function () {
                    submitData(cid, au, flag);
                });
        }
       
    }
});

showAllcontent('layera', 'contactRecord', 'cusContact');
showAllcontent('layera', 'prjUpdateList', 'prjUpdContent');
showAllcontent('layera', 'onlineSale', 'cusAsk');
showAllcontent('layera', 'onlineSale', 'onAnswer');
showAllcontent('layera', 'saler', 'descrip');
showAllcontent('layera', 'saler', 'solvePro');

showRecords('numOfCont', 'table-contacter');
showRecords('numOfOrder', 'table-order');
showRecords('numOfContactRecord', 'table-contactRecord');
showRecords('numOfPrj', 'table-prjUpdateList');
showRecords('numOfOnline', 'table-onlineSale');
showRecords('numOfSale', 'table-saler');