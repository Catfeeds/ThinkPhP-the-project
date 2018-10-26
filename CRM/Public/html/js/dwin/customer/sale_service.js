/**
 * Created by ml on 2017/8/25.
 */
checkBoxSel('sServiceList');
var layera,layerb;
showAllcontent(layera, 'sServiceList', 'descrip');
showAllcontent(layerb, 'sServiceList', 'solvePro');
$("#chService").on('click', function() {
    checkList('serviceCheckBox','roleSer','checkSaleService');
});