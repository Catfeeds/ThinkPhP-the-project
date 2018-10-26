/**
 * Created by ml on 2017/8/25.
 */
var layera;
var co;

dataTabelset('contacter', 5);
dataTabelset('orderList', 5);
dataTabelset('contactRecord', 5);
dataTabelset('prjUpdateList', 5);
dataTabelset('onlineSale', 5);
dataTabelset('saler', 5);
dataTabelset('cusfile', 5);
showAllcontent('layera', 'contactRecord', 'cusContact');
showAllcontent('layera', 'prjUpdateList', 'prjUpdContent');
showAllcontent('layera', 'onlineSale', 'cusAsk');
showAllcontent('layera', 'onlineSale', 'onAnswer');
showAllcontent('layera', 'saler', 'descrip');
showAllcontent('layera', 'saler', 'solvePro');

$(".table-cusfile tr td").click(function(){
    var check    = $(this).find("input[type='checkbox']");
    if(check[0]){
        var flag = check[0].checked;
        if(flag){
            check[0].checked = false;
            $(this).css('background-color', co);
        }else{
            check[0].checked = true;
            $(this).css('background-color', "yellow");
        }
    }
});

$(".showFile").on('click', function () {
    var fid = $(this).attr('data');
    window.location.href = controller + "/download/id/" +　fid;
});
$("#uploadButton").on('click', function () {
    var id  = $(this).attr('data');
    $.ajax({
        type : 'POST',
        url  : controller + '/checkCusUName',
        data : {
            cusId : id
        },
        success :function (msg) {
            if (msg == 1) {
                layer.alert("仅客户负责人可添加");
            } else if (msg == 2) {
                layer.open({
                    type       : 2,
                    title      : '客户文件上传',
                    area       : ['90%', '70%'],
                    closeBtn   : 0,
                    shade      : [0.8, '#393D49'],
                    shadeClose : true,
                    end        : function () {
                        location.reload();
                    },
                    content    : controller + "/uploadCusFile/cusId/" + id//iframe的url
                });
            }
        }
    });
});

showRecords('numOfCont', 'table-contacter');
showRecords('numOfOrder', 'table-order');
showRecords('numOfContactRecord', 'table-contactRecord');
showRecords('numOfPrj', 'table-prjUpdateList');
showRecords('numOfOnline', 'table-onlineSale');
showRecords('numOfSale', 'table-saler');
showRecords('numOfFile', 'table-cusfile');
