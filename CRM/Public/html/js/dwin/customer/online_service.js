/**
 * Created by ml on 2017/8/25.
 */
checkBoxSel('onlineSale');

var layera,layerb;
showAllcontent(layera, 'onlineSale', 'descrip');
showAllcontent(layerb, 'onlineSale', 'solvePro');
$("#chOnline").on('click', function() {
    checkList('onlineCheckBox','roleOnline','checkOnlineService');
});