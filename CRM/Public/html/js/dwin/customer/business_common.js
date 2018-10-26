/**
 * Created by ml on 2017/8/25.
 */
/**遍历函数获取选中行的ID 
 *     *@param return string id;
 *         **/
function getSelectedValue()
{
    var id;
    $(".dataTables-common tbody tr").each(function () {
        if ($(this).hasClass('selected')) {
            id = $(this).attr('id');
        }
    });
    return id;
}

$("#BusApplication").on('click', function() {
    var selecetVal = getSelectedValue();
    if (selecetVal) {   
        layer.open({
            type: 2,
            title: '客户申请',
            end : function () {
                var oTables = $('.dataTables-common').DataTable();
                oTables.ajax.reload();
            },
            area: [ '100%', '100%'],
            content: controller + "/businessApplication/cusId/" + selecetVal //iframe的url
        });
    } else {
        layer.alert('请选中客户');
    }
});

$("#common_add").on('click', function() {
    layer.open({
        type : 2,
        title : '公共客户添加',
        end : function () {
            window.location.reload();
        },
        area : ['90%', '90%'],
        content : controller + "/addCommonCustomer"
    });
});

$("#common_import").on('click', function () 
{
    layer.open({
        type : 2,
        title : '批量导入公共客户',
        end : function () {
            window.location.reload();
        },
        area : ['50%', '50%'],
        content : controller + "/importCus"    
    });
});
