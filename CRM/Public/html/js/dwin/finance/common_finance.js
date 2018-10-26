/**
 * Created by ml on 2017/9/25.
 */

/**改变单元格样式 
* @param string className:datatables's className;
* @param int    tdNum:td's index*/
function changeCss (className, tdNum){
    $(".dataTables-" + className + " tbody").on('mouseover', 'td', function () {
        var tdIndex = $(this).parent()['context']['cellIndex'];
        if(tdNum == tdIndex) {
            $(this).addClass('selected');
            $(this).css('cursor','pointer');
        }
    });
    $(".dataTables-" + className + " tbody").on('mouseout', 'td', function () {
         $(this).removeClass('selected');
    });
}


/**onmouseover 详情
 *鼠标移动查看详细不合格原因
 * @param string className:datatables's className;
 * @param strign url:ajax'url
 * @param int    k:td'index*/
function showNumDetail(className, url, k)
{
    $(".dataTables-" + className + " tbody").on('mouseover','td' ,function(e) {
        var index   = $(this).parent();
        var thisTd  = $(this);
        var orderId = $(this).parent()[0].id;
        var tdIndex = index['context']['cellIndex'];
        if (tdIndex == k) {
            e.stopPropagation();
            var checkText = $(this).text();
            if (checkText == "不合格") {
            	$.ajax({
                    type : 'GET',
                    url  : url + "/id/" + orderId,
                    success : function (ajaxData) {
                        var contents = "";
                        switch (k) {
                            case 12 :
                            	contents = !(ajaxData) ? '未填写不合格原因' : "订单不合格原因：<br />" + (!ajaxData.deptfeedback ? "" : ajaxData.deptfeedback) + (!ajaxData.financefeedback ? "" : ajaxData.financefeedback);
                            	layer.tips(contents, thisTd,
                            		{
                            			tips : [1, '#3595CC'],
                        				area : '500px'
                            		});
                                break;
                        }
                    }
                });
            } else {
            	return false;
            }
        } else {
        	return false;
        }
    });

    $(".dataTables-" + className + " tbody").on('mouseout','td' ,function(e) {
        layer.closeAll('tips');
    });
}

/**遍历函数获取选中行的ID 
 *     *@param return string id;
 *         **/
function getSelectedValue()
{
    var id;
    $(".dataTables-orderList tbody tr").each(function () {
        if ($(this).hasClass('selected')) {
            id = $(this).attr('id');
        }
    });
    return id;
}
