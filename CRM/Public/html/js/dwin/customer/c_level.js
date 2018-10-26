/**
 * Created by ml on 2017/8/25.
 */
$.ajax({
    type : 'POST',
    url : cnt + '/checkAuditRole',
    data : {id : $("#role").val()},
    success : function (msg) {
        if (msg == 1) {
            $("#reLevel").attr('disabled', 'disabled');
        } else {
            $("#reLevel").attr('disabled', false);
        }
    }
});

$("#reLevel").on('click', function() {
    $("#reLevel").attr('disabled', 'disabled');
    $.ajax({
        type : 'POST',
        url : cnt + '/resetLevel',
        data : {id :$("#role").val()},
        success : function (msg) {
            if (msg == 1) {
                layer.alert('第一季度不能重置客户等级',
                    {
                        icon : 5
                    },
                    function () {
                        window.location.reload();
                    }
                );
            } else {
                if (msg == 3) {
                    layer.alert('重置无变化！');
                    $("#reLevel").attr('disabled', false);
                } else {
                    layer.alert('重置成功！');
                    window.location.reload();
                }
            }
        }
    });
});