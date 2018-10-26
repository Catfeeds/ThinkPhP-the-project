<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM--添加客户维修单</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/Public/html/css/plugins/jasny/jasny-bootstrap.min.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="/Public/html/css/plugins/select2/select2.min.css" rel="stylesheet">
	<style type="text/css">
        body {
            color: black;
        } 
        .chosen-select{ 
            color : black!important;
        }   
    </style>
</head>

<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins" id="orders">
                <div class="ibox-title">
                    <h5>售后维修记录添加</h5>
                    <div class="ibox-tools"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive1">
                        <form id="addSaleRepairingForm1" method="post">
                            <table class="table table-bordered table-striped" id = "basictable1">                               
                                <tr>
                                    <th colspan="12">售后单基本信息</th>
                                </tr>
                                <tr>                                
                                    <th colspan="2">
                                        售后单号：<input class="form-control" name="sale_number" id="sale_number" value="<?php echo ($orderNumber); ?>" type="text" readonly>
                                    </th>                                
                                    <th colspan="2">
                                        快递单号：<input style="" class="form-control" name="courier_number" id="courier_number" type="text">
                                    </th>                                
                                    <th colspan="2">
                                        是否有售后维修单：
                                        <select class="form-control" name="is_repairorder" id="is_repairorder">
                                            <option value="是">是</option>
                                            <option value="否">否</option>                                            
                                        </select> 
                                    </th>
                                    <th colspan="2" id="zhuijia1">
                                        客户：&nbsp; 
                                        <select id="select2_sample" name="customer" style="width:75%;" onchange="findClass();addr();"></select>
                                    </th>
                                    <th colspan="2" id="zhuijia">
                                        业务员:<input type="text" aaa="" class="form-control" id="saleman" name="saleman" value="">
                                    </th>                                                                      
				                </tr>                                
                                <tr>                                                                         
                                    <th colspan="12">
                                        返回地址：<textarea class="form-control" name="reback_address" id="reback_address" cols="30" rows="3"></textarea>
                                    </th>
                                </tr>                                                                    
                            </table>                                                                                                      
                            <table class="table table-bordered table-striped dataTables-list" id="table1">
                                <thead>
                                    <tr>
                                        <th>产品类别</th>
                                        <th>产品型号</th>                                        
                                        <th>数量（件）</th>
                                        <th>条码日期</th>
                                        <th>客户反馈问题</th>                                        
                                        <th>售后方式</th>
                                        <th><button class="btn btn-primary btn-circle" type="button" id="cusAdd"><i class="fa fa-plus"></i></button></th> 
                                    </tr>
                                </thead>
                                <tbody>                                    
                                </tbody>                                                            
                            </table>                        
                            <table class="table table-bordered table-striped dataTables-list">
                                <button type="button" id="addbasicinfo" class="btn btn-outline btn-success" style="float: left">添加基本信息</button>
                            </table>
                        </form>
                        <form id="addSaleRepairingForm2" method="post">
                            <table class="table table-bordered table-striped dataTables-list" id = "table2">
                                <label class="fa-hover col-md-1 col-sm-1">维修单信息</label>                    
                            </table>
                            <table class="table table-bordered table-striped dataTables-list">    
                                <tr>
                                    <th style="width: 160px;">人工费用(元)：<textarea class="form-control" id="rgmoney" name = "rgmoney">0</textarea></th>
                                    <th>
                                        备注信息：<textarea class="form-control" name="note" id="note"></textarea>
                                    </th>
                                </tr>                   
                            </table>
                            <table class="table table-bordered table-striped dataTables-list" id = "table3">
                                <label class="fa-hover col-md-2 col-sm-2">发货信息&nbsp;<button type="button" id="sendgoods" style="background-color: #1AB394;" class="btn btn-default btn-xs "><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>&nbsp;<button type="button" id="delgoods" style="background-color: #1AB394;" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></label>
                            </table>
                            <button class="btn btn-outline btn-success" id="addauditrecord" type="button">添加记录并提交审核</button>
                            <button class="btn btn-outline btn-success" id="addsalerecord" type="button">添加维修记录</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/jasny/jasny-bootstrap.min.js"></script>
<script src="/Public/html/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/Public/html/js/plugins/validate/messages_zh.min.js"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="/Public/html/js/dwin/WdatePicker.js"></script>
<script src="/Public/html/js/dist/js/select2.min.js"></script>
<script>
var productData = <?php echo (json_encode($productCate)); ?>;
var controller = "/Dwin/SaleService";
//读取客户地址
function addr(){
    var Id = $("select[name='customer'] option:selected").attr("value");
    $.ajax({  
        url:"/Dwin/SaleService/readAddr",  
        type:"post",  
        timeout:"1000",
        dataType:'json',  
        data:{id:Id},  
        success:function(data){
            if(data == 1){
                $("#reback_address").text("");
            }else{
                $("#reback_address").text(""); 
                $("#reback_address").text(JSON.parse(data[0]['addr']));    
            }                                              
        }  
    });  
} 

//客户模糊搜索
$(document).ready(function(){
    $("#select2_sample").select2({
        ajax: {
            delay   : 500,
            url     : "/Dwin/SaleService/addSelect",//请求的API地址
            dataType: 'json',//数据类型
            data    : function(params){
                return { q : params.term} //此处是最终传递给API的参数
            }, function(data){
                return data;
            }//返回的结果
        }
    });//启动select2
});
 
//客户对应业务员二级联动
function findClass(){  
    var Id = $("select[name='customer'] option:selected").attr("value");        
    $.ajax({  
        url:"/Dwin/SaleService/secondMove",  
        type:"post",   
        data:{id:Id},  
        success:function(data){
            console.log(data);
            if(data == 1){ 
                $("#zhuijia").remove();
                $("#zhuijia1").after('<th colspan="2" id="zhuijia">'+
                                    '<select aaa="" class="chosen-select" name="saleman" onchange="checkSaleman();" id="saleman"><option value="">业务员未知，不填写此项</option><?php if(is_array($res)): $i = 0; $__LIST__ = $res;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["id"]); ?>"><?php echo ($vol["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>'+
                                '</th>');  
                $(".chosen-select").chosen({
                    no_results_text: "",//搜索无结果时显示的提示
                    search_contains:true,   //关键字模糊搜索，设置为false，则只从开头开始匹配
                    allow_single_deselect:true, //是否允许取消选择
                    max_selected_options:6,  //当select为多选时，最多选择个数
                    width:'100%'
                });                                                        
            }else{
                $("#zhuijia").html("");
                var html = '业务员:<input type="text" aaa="" class="form-control" id="saleman" name="saleman" value="">';
                $("#zhuijia").html(html);

                $("#saleman").attr('aaa',""); 
                $("#saleman").attr("value","");                         
                var salemanId = data[0]['id'];  
                var salemanName = data[0]['name']; 
                $("#saleman").attr('aaa',salemanId); 
                $("#saleman").attr("value",salemanName); 
                
            }                                        
        }  
    });  
} 
//根据输入的业务员查找staff对应id，name
function checkSaleman()
{
    var id = $("#saleman").val();       
    $("#saleman").attr('aaa',"");                          
    $("#saleman").attr('aaa',id);  
}

//动态添加维修品表格行数cusadd按钮关联添加维修人表
var fnNum = 0;
$(document).ready(function() {  
    $("#addauditrecord").attr('disabled', true);
    $("#cusAdd").on("click", function(){
        var html_pre = '<tr>' +
            '<td>'+
            '<select name="product_category'+ fnNum +'" id="product_category'+ fnNum +'" class="form-control cate-select"><?php if(is_array($proCate)): $i = 0; $__LIST__ = $proCate;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vol["id"]); ?>"><?php echo ($vol["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>'+
            '</td>'+
            '<td>'+
            '<select name="product_name'+ fnNum +'"' +
            ' id="product_name' + fnNum + '" ' +
            ' class="chosen-select  trchange"><option>必选</option>';
        for (var k = 0; k < productData.length; k++) {
            html_pre += '<option value="' + productData[k].product_id +'">' + productData[k].product_name + '</option>';
        }
        html_pre +='</select>'+
            '</td>'+
            '<td>'+
            '<div class="input-group">'+
            '<input type="text" class="form-control" placeholder="" aria-describedby="basic-addon1" name="num'+ fnNum +'" id="num'+ fnNum +'">'+
            '</div>'+
            '</td>'+
            '<td>'+
            '<div class="input-group">'+
            '<input class="form-control" type="text" name="barcode_date'+ fnNum +'" id="barcode_date'+ fnNum +'">'+
            '</div>'+
            '</td>'+
            '<td>'+
            '<div class="input-group">'+
            '<textarea class="form-control" name="customer_question'+ fnNum +'" id="customer_question'+ fnNum +'" cols="30" rows="3"></textarea>'+
            '</div>'+
            '</td>'+
            '<td>'+
            '<select class="form-control" name="sale_mode'+ fnNum +'" id="sale_mode'+ fnNum +'">' +
            '<?php if(is_array($shmethod)): $i = 0; $__LIST__ = $shmethod;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?>' +
            '<option value="<?php echo ($vol["id"]); ?>"><?php echo ($vol["name"]); ?></option>'+
            '<?php endforeach; endif; else: echo "" ;endif; ?>'+
            '</select>'+
            '</td>'+
            '<td>'+
            '<div class="input-group">'+
            '<button class="btn btn-primary btn-circle" type="button" onclick=\'deleteTrRow(this);\' id="del"><i class="fa fa-times"></i></button>'+
            '</div>'+
            '</td>' +
            '</tr>';
        $("#table1").append(html_pre);

            $(".chosen-select").chosen({
                no_results_text: "",//搜索无结果时显示的提示
                search_contains:true,   //关键字模糊搜索，设置为false，则只从开头开始匹配
                allow_single_deselect:true, //是否允许取消选择
                max_selected_options:6,  //当select为多选时，最多选择个数
                width:'100%'
            });
            /*$('#table1').on('change', '.cate-select', function () {
                var thisSel = $(this);
                var id = $(this).val();
                console.log(id);
                $.ajax({
                    type : 'POST',
                    url  : "/Dwin/SaleService/addSaleRepairing",
                    data : {
                        method : "getProd",
                        cate_id : id
                    },success :function (returnData) {
                        var html = "";
                        for (var i = 0; i < returnData.length; i++) {
                            html += "<option value='" + returnData[i]['product_name'] + "'>" + returnData[i]['product_name'] + "</option>";
                        }
                        console.log(html);
                        thisSel.parent().next().children().append(html);
                        $(".chosen-select").chosen({
                            no_results_text: "",//搜索无结果时显示的提示
                            search_contains:true,   //关键字模糊搜索，设置为false，则只从开头开始匹配
                            allow_single_deselect:true, //是否允许取消选择
                            max_selected_options:6,  //当select为多选时，最多选择个数
                            width:'100%'
                        });
                    }
                });
            });*/
            $("#table2").append(                  
                    '<tr>'+                                        
                        '<td style="width: 180px;">'+
                            '<div class="input-group">'+
                                '<span style="font-weight: bold;font-size: 18px;">产品型号'+
                                    '<button type="button" onclick="addnow('+fnNum+');" style="background-color: #1AB394; margin-left:5px;" class="btn btn-default btn-xs "><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>'+
                                    '<button type="button" onclick="removenow('+fnNum+');" style="background-color: #1AB394; margin-left:5px;" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>'+
                                '</span>'+
                            '</div>'+
                            '<div class="input-group">'+
                                '<input style="width: 200px;" type="text" class="form-control" placeholder="" aria-describedby="basic-addon1" name="rproduct_name'+ fnNum +'" id="rproduct_name'+ fnNum +'">'+ 
                            '</div>' +                                          
                        '</td>' +                                          
                        '<td>'+
                            '<table class="table table-bordered table-striped dataTables-list" id = "smalltable'+ fnNum +'">'+
                                '<thead>'+
                                    '<th>维修员</th>' +                                          
                                    '<th>数量（件）</th>'+
                                    '<th>状态</th>'+
                                    '<th>故障现象</th>'+ 
                                    '<th>维修方式</th>'+
                                    '<th>故障类型</th>'+
                                    '<th>维修费用(元)</th>'+
                                    '<th>计价工资</th>'+
                                '</thead>'+
                                                                               
                            '</table>' +
                        '</td>'+                                                                            
                    '</tr>'
            );
            fnNum++;
            
    }); 
    return fnNum;
});

//动态删除维修品表格当前行数
function deleteTrRow(tr){
    $(tr).parent().parent().parent().remove();
    $("#table2").children().children().last("tr").remove();
    fnNum--;
    return fnNum;
}

//onchange获取select的值
$('.dataTables-list').on('change','.trchange',function(){
    for(var i = 0;i<=fnNum;i++ ){
        var a = $('#'+'product_name'+ i).find("option:selected").text();        
        $('#'+'rproduct_name'+ i).attr("value",a);       
    }

});
           
//追加当前维修人行内部表格
var anumber = 0;
function addnow(fnNum){
    //获取select赋值给隐藏的td    
    var b = $("#"+ "rproduct_name" + fnNum).val(); 
    var c = $("#"+ "num" + fnNum).val(); 
    $("#"+ "smalltable" + fnNum).append(
        '<tr>'+
            '<td style="display:none;">'+
                '<div class="input-group">'+
                    '<input type="text" class="form-control" placeholder="" name="a_name'+ anumber +'" id="a_name'+ anumber +'">'+
                '</div>'+
            '</td>'+
            '<td>'+
                '<div class="input-group">'+
                    '<select class="chosen-select"  name="reperson_name'+ anumber +'"  id="reperson_name'+ anumber +'">'+
                    '<?php if(is_array($result3)): $i = 0; $__LIST__ = $result3;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol123): $mod = ($i % 2 );++$i;?>'+   
                        '<option value="<?php echo ($vol123["id"]); ?>"><?php echo ($vol123["name"]); ?></option>'+
                    '<?php endforeach; endif; else: echo "" ;endif; ?>'+
                    '</select>'+  
                '</div>'+
            '</td>'+
            '<td>'+
                '<div class="input-group">'+
                    '<input style="width:80px;" type="text" class="form-control" placeholder="" name="re_num'+ anumber +'" id="re_num'+ anumber +'">'+
                '</div>'+
            '</td>'+
            '<td>'+
                '<select class="form-control" name="re_status'+ anumber +'" style="width: 110px;" id="re_status'+ anumber +'">'+
                    '<?php if(is_array($rst)): $i = 0; $__LIST__ = $rst;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?>'+
                        '<option value="<?php echo ($vol["id"]); ?>"><?php echo ($vol["name"]); ?></option>'+
                    '<?php endforeach; endif; else: echo "" ;endif; ?>'+
                '</select>'+
            '</td>'+
            '<td>'+
                '<div class="input-group" style="float: left;">'+
                    '<textarea class="form-control" id="situation" name="situation'+ anumber +'" ></textarea>'+
                '</div>'+
            '</td>'+
            '<td>'+
                '<div class="input-group">'+                                                                   
                    '<select class="form-control" name="re_mode'+ anumber +'"  id="re_mode'+ anumber +'">'+ 
                    '<?php if(is_array($rst1)): $i = 0; $__LIST__ = $rst1;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?>'+  
                        '<option value="<?php echo ($vol["id"]); ?>"><?php echo ($vol["name"]); ?></option>'+
                    '<?php endforeach; endif; else: echo "" ;endif; ?>'+
                    '</select>'  + 
                '</div>'+
                '<div class="input-group" style="float: left;">'+
                    '<textarea class="form-control" placeholder="附加说明（可不填）" id="mode_info" name="mode_info'+ anumber +'" ></textarea>'+
                '</div>'+
            '</td>'+
            '<td>'+                
                '<div class="input-group">'+
                        '<select class="form-control" name="reperson_question'+ anumber +'" id="reperson_question'+ anumber +'">' +  
                            '<?php if(is_array($rst2)): $i = 0; $__LIST__ = $rst2;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?>'+  
                                '<option value="<?php echo ($vol["id"]); ?>"><?php echo ($vol["name"]); ?></option>'+
                            '<?php endforeach; endif; else: echo "" ;endif; ?>'+
                        '</select>'+                                                
                    '</div>'+                                        
                    '<div class="input-group">'+
                        '<textarea class="form-control" placeholder="费用明细" id="fault_info" name="fault_info'+ anumber +'" ></textarea>'+
                    '</div>'+
            '</td>'+
            '<td>'+
                '<div class="input-group">' +                                               
                    '<input type="text" class="form-control" placeholder="￥0.00" name="piece_wage'+ anumber +'" onchange="changebutton();" id="piece_wage'+ anumber +'">'+
                '</div>'+
            '</td>'+
            '<td>'+
                '<div class="input-group">' +                                               
                    '<input type="text" class="form-control" placeholder="￥0.00" name="meter_piece'+ anumber +'" id="meter_piece'+ anumber +'">'+
                '</div>'+
            '</td>'+
        '</tr>'
        );
    $('#'+'a_name'+ anumber).attr("value",b);
    $('#'+'re_num'+ anumber).attr("value",c);
    $(".chosen-select").chosen({
        no_results_text: "",//搜索无结果时显示的提示
        search_contains:true,   //关键字模糊搜索，设置为false，则只从开头开始匹配
        allow_single_deselect:true, //是否允许取消选择
        max_selected_options:6,  //当select为多选时，最多选择个数
        width:'100%'
    });
    anumber++;
    return anumber;
}

//删除当前维修人行内部表格
function removenow(fnNum) {    
    $("#"+ "smalltable" + fnNum).children().children().last("tr").remove();
    anumber--;
    return anumber;
}

//添加发货信息行数
var gnumber = 0;
$("#sendgoods").on('click',function(){   
    $("#table3").append(                  
            '<tr>'+                                        
                '<td style="width: 180px;">'+
                    '<div class="input-group">'+
                        '<span style="font-weight: bold;font-size: 18px;">发货批次</span>'+
                    '</div>'+
                    '<div class="input-group">'+                       
                        '<input type="text" class="form-control" placeholder="" aria-describedby="basic-addon1" name="bactch'+ gnumber +'" id="bactch'+ gnumber +'" value="'+(gnumber+1)+'">'+ 
                    '</div>' +                                          
                '</td>' + 
                '<td style="width: 180px;">'+
                    '<div class="input-group">'+
                        '<span style="font-weight: bold;font-size: 18px;">快递单号</span>'+
                    '</div>'+
                    '<div class="input-group">'+
                        '<input type="text" class="form-control" placeholder="" aria-describedby="basic-addon1" name="track_number'+ gnumber +'" id="track_number'+ gnumber +'">'+ 
                    '</div>' +                                          
                '</td>' +                                          
                '<td>'+
                    '<table class="table table-bordered table-striped dataTables-list" id = "goodstable'+ gnumber +'">'+
                        '<thead>'+
                            '<th>产品型号</th>'+
                            '<th>数量（件）</th>'+
                        '</thead>'+
                        
                    '</table>' +
                '</td>'+                                                                            
            '</tr>'
            );
        for (var i = 0; i < fnNum; i++) {
            var a = $('#'+'product_name'+ i).find("option:selected").text();        
            var b = $('#'+'num'+ i).val();
            $("#"+ 'goodstable' + gnumber).append(
                '<tr>'+
                    '<td>'+
                        '<div class="input-group">'+
                            '<input type="text" class="form-control" placeholder="" aria-describedby="basic-addon1" name="reproduct_name'+ i +'" id="reproduct_name'+ i +'">'+
                        '</div>'+
                    '</td>'+
                    '<td>'+
                        '<div class="input-group">'+
                            '<input type="text" class="form-control" placeholder="" aria-describedby="basic-addon1" name="send_num'+ i +'" id="send_num'+ i +'">'+
                        '</div>'+
                    '</td>'+
                '</tr>'
                );
            $('#'+'reproduct_name'+ i).attr("value",a);
            $('#'+'send_num'+ i).attr("value",b);
           
        }    
    gnumber++;
    return gnumber;
});

//删除发货信息行数
$("#delgoods").on('click',function(){    
    $("#table3").children().children().last("tr").remove();
    gnumber--;
    return gnumber;
});

function checkMsg(idname)
{
    var data = $("#" + idname + "").val();
    return data;
}
//改变按钮状态
function changebutton(){
    $("#addauditrecord").attr('disabled', false);
}

//addbasicinfo
$("#addbasicinfo").on('click', function () { 
    $("#addbasicinfo").attr('disabled', true);
    //获取当前选中的客户名
    var text = $("select[name='customer'] option:selected").text();  
    
    var data = [];
    data.push(checkMsg('courier_number'), checkMsg('is_repairorder'), checkMsg('reback_address'), checkMsg('product_category0'), checkMsg('product_name0'), checkMsg('num0'), checkMsg('barcode_date0'), checkMsg('sale_mode0'));
    var index = $.inArray("", data);
    if (index !== -1) {
        layer.alert("基本信息填写不完整，请查验后提交");
        $("#addbasicinfo").attr('disabled', false);
        return false;
    }
    var a = $("#saleman").attr('aaa');
    var data1 = $("#addSaleRepairingForm1").serializeArray(); 
    layer.confirm('只提交基本信息？',
        {
            icon  : 3,
            title :'确认',
        shadeClose : true,
            btn   : ['是的', '再想想']
        },
    function (){   
        $.ajax({
                type : 'POST',
                url  : "/Dwin/SaleService/addSaleRepairing",
                data : {
                aaa  : a,
                cusname : text,
                data1: data1,
                fnNum  : fnNum,
                flag : 0
            },
            success : function (msg) {
                if(msg == 1) {
                    layer.msg("ok,提交成功",
                        {
                            icon : 6,
                            time : 1000
                        },
                        function () {
                            window.parent.location.reload();
                        }
                    );
                } else {
                    layer.alert("请填写产品信息");
                    $("#addbasicinfo").attr('disabled', false);
                }
                }
            });
        },function(){
            $("#addbasicinfo").attr('disabled', false);
        });
}); 
//需要审核
//addauditrecord
$(document).on('click','#addauditrecord', function () {
    $("#addauditrecord").attr('disabled', true); 
    var data = [];
    data.push(checkMsg('sale_number'), checkMsg('courier_number'), checkMsg('is_repairorder'), checkMsg('reback_address'), checkMsg('product_category0'), checkMsg('num0'), checkMsg('barcode_date0'), checkMsg('sale_mode0'), checkMsg('a_name0'), checkMsg('reperson_name0'), checkMsg('re_num0'), checkMsg('re_status0'), checkMsg('re_mode0'), checkMsg('reperson_question0'), checkMsg('bactch0'), checkMsg('track_number0'), checkMsg('reproduct_name0'), checkMsg('send_num0'));
    var index = $.inArray("", data);
    if (index !== -1) {
        layer.alert("信息填写不完整，请查验后提交");
        $("#addauditrecord").attr('disabled', false);
        return false;
    }
    var text = $("select[name='customer'] option:selected").text();
    var data1 = $("#addSaleRepairingForm1").serializeArray();  
    var datas = $("#addSaleRepairingForm2").serializeArray();    
    var a = $("#saleman").attr('aaa');
    $.ajax({
            type  : 'POST',
            url   : "/Dwin/SaleService/addSaleRepairing",
            data  : {
            data1 : data1,
            datas : datas,
            aaa   : a,
            cusname : text, 
            flag  : 1,
            fnNum   : fnNum,
            money   : 1,
            anumber : anumber, 
            gnumber : gnumber,                  
        },
            success : function (msg) {
                if(msg == 1) {
                    layer.msg("ok,提交成功",
                        {
                            icon : 6,
                            time : 1000
                        },
                    function () {
                        window.parent.location.reload();
                    }
                    );
                } else if(msg == 2) {
                    layer.msg("提交出错",
                        {
                            icon : 5,
                            time : 1000
                        },
                    function () {
                        window.parent.location.reload();
                    }
                    );
                }
            },            
        });
});

//addsalerecord
$(document).on('click','#addsalerecord', function () {
    $("#addsalerecord").attr('disabled', true); 
    var data = [];
    data.push(checkMsg('sale_number'), checkMsg('courier_number'), checkMsg('is_repairorder'), checkMsg('reback_address'), checkMsg('product_category0'), checkMsg('num0'), checkMsg('barcode_date0'), checkMsg('sale_mode0'), checkMsg('a_name0'), checkMsg('reperson_name0'), checkMsg('re_num0'), checkMsg('re_status0'), checkMsg('re_mode0'), checkMsg('reperson_question0'), checkMsg('bactch0'), checkMsg('track_number0'), checkMsg('reproduct_name0'), checkMsg('send_num0'));
    var index = $.inArray("", data);
    if (index !== -1) {
        layer.alert("信息填写不完整，请查验后提交");
        $("#addsalerecord").attr('disabled', false);
        return false;
    }
    var text = $("select[name='customer'] option:selected").text();
    var data1 = $("#addSaleRepairingForm1").serializeArray();  
    var datas = $("#addSaleRepairingForm2").serializeArray();    
    var a = $("#saleman").attr('aaa');
    $.ajax({
            type : 'POST',
            url  : "/Dwin/SaleService/addSaleRepairing",
            data : {
            data1:data1,
            datas: datas,
            aaa  : a,
            cusname : text, 
            flag  : 1,
            money : 0,
            fnNum   : fnNum,
            anumber : anumber, 
            gnumber : gnumber,                  
        },
            success : function (msg) {
                if(msg == 1) {
                    layer.msg("ok,提交成功",
                        {
                            icon : 6,
                            time : 1000
                        },
                    function () {
                        window.parent.location.reload();
                    }
                    );
                } else if(msg == 2) {
                    layer.msg("提交出错",
                        {
                            icon : 5,
                            time : 1000
                        },
                    function () {
                        window.parent.location.reload();
                    }
                    );
                }
            },            
        });
}); 


</script>
</body>
</html>