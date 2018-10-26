<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM--项目更新列表</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/plugins/chosen/chosen.css" rel="stylesheet">
    <link href="/Public/html/css/plugins/jasny/jasny-bootstrap.min.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins" id="orders">
                <div class="ibox-content">
                    <div class="table-responsive1">                                               
                            <table class="table invoice-table1">
                                <label class="fa-hover col-md-2 col-sm-2">基本信息</label>
                                <thead>
                                    <tr> 
                                        <th>客户名称 : <?php echo ($saleRecordBasicData["cusname"]); ?></th>
                                        <th>返回地址 : <?php echo ($saleRecordBasicData["reback_address"]); ?></th>
                                    </tr>
                                    <tr>
                                        <th>收货单号 : <?php echo ($saleRecordBasicData["courier_number"]); ?></th>
                                        <th>收货时间 : <?php echo (date('Y-m-d H:i:s',$saleRecordBasicData["repair_date"])); ?></th>
                                    </tr>
                                    <tr>
                                        <input type="hidden" id="yid" value="<?php echo ($saleRecordBasicData["yid"]); ?>">
                                        <input type="hidden" id="isshow" value="<?php echo ($saleRecordBasicData["is_show"]); ?>">
                                        <input type="hidden" name="sid" id="salesid" value="<?php echo ($saleRecordBasicData["sid"]); ?>">
                                    </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th>产品型号</th>
                                    <th>类别</th>
                                    <th>数量</th>
                                    <th>条码日期</th>
                                    <th>客户反馈问题</th>
                                    <th>售后处理方式</th>
                                </tr>
                                <?php if(is_array($repairProductInfo)): $i = 0; $__LIST__ = $repairProductInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><tr>
                                        <td><?php echo ($list["product_name"]); ?></td>
                                        <td><?php echo ($list["product_category"]); ?></td>
                                        <td><?php echo ($list["num"]); ?></td>
                                        <td><?php echo ($list["barcode_date"]); ?></td>
                                        <td><?php echo ($list["customer_question"]); ?></td>
                                        <td><?php echo ($list["sale_type"]); ?></td>
                                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                            </table> 
                        <form id="addSaleRepairingForm1" method="post">
                            <table class="table invoice-table1 table-bordered">    
                                <label class="fa-hover col-md-2 col-sm-2">维修单信息</label>
                                <?php if(is_array($result)): $i = 0; $__LIST__ = $result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr>                                        
                                    <td>
                                        <div class="input-group" style="font-weight: bold;text-align: center;">产品型号</div>
                                        <div class="input-group" style="margin-top: 5px;"><?php echo ($vol["product_name"]); ?></div>
                                    </td>                                          
                                    <td>
                                        <table class="table invoice-table1 table-bordered">
                                            <thead>                                        
                                                <th>数量（件）</th>
                                                <th>维修员</th>
                                                <th>检测故障现象</th>                                                
                                                <th>故障类型</th>
                                                <th>维修反馈</th>
                                                <th>维修费用(元)</th>
                                                <th>费用明细</th>
                                            </thead>
                                            <?php if(is_array($result1)): $i = 0; $__LIST__ = $result1;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol1): $mod = ($i % 2 );++$i; if($vol["product_name"] == $vol1["product_name"] ): ?><tr>
                                                <td><?php echo ($vol1["re_num"]); ?></td>
                                                <td><?php echo ($vol1["reperson_name"]); ?></td>
                                                <td><?php echo ($vol1["situation"]); ?></td>
                                                <td><?php echo ($vol1["reperson_question"]); ?></td> 
                                                <td><?php echo ($vol1["re_mode"]); ?><br><?php echo ($vol1["mode_info"]); ?></td>
                                                <td><?php echo ($vol1["piece_wage"]); ?></td>
                                                <td><?php echo ($vol1["fault_info"]); ?></td>
                                            </tr>
                                            <?php else: endif; endforeach; endif; else: echo "" ;endif; ?>             
                                        </table>
                                    </td>                                                                           
                                </tr><?php endforeach; endif; else: echo "" ;endif; ?>                                
                            </table>
                            <table class="table table-bordered table-striped" style="margin-top: 0px;"> 
                                <tr>
                                    <th>备注信息：<?php echo ($note); ?></th>  
                                    <th>费用共计：<span style="color: red;"><?php echo ($money); ?>元</span> &nbsp; <span style="font-weight: normal;">(维修费用：<?php echo ($money1); ?>元&nbsp; 人工费用 ：<?php echo ($rgmoney); ?>元)</span></th>
                                </tr>
                            </table>  
                            <table class="table table-bordered table-striped dataTables-list" id="table1">   
                                <label id="fahuo" class="fa-hover col-md-2 col-sm-2">发货信息</label>
                                <tr>
                                    <th>发货批次</th>
                                    <th>快递单号</th>
                                    <th>发货时间</th>
                                    <th>产品型号</th>
                                    <th>数量</th>
                                </tr> 
                                <?php if(is_array($result3)): $i = 0; $__LIST__ = $result3;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr>
                                    <td><?php echo ($vol["bactch"]); ?></td>
                                    <td><?php echo ($vol["track_number"]); ?></td>
                                    <?php if(empty($vol["send_date"])): ?><td></td>                                      
                                    <?php else: ?> 
                                    <td><?php echo (date('Y-m-d H:i:s',$vol["send_date"])); ?></td><?php endif; ?>
                                    <td><?php echo ($vol["product_name"]); ?></td>
                                    <td><?php echo ($vol["send_num"]); ?></td>
                                </tr>
                                <input type="hidden" name="flag" id="flag" value="<?php echo ($flag); ?>"><?php endforeach; endif; else: echo "" ;endif; ?>                   
                            </table>  
                            <table class="table table-bordered table-striped dataTables-list" id="table2">   
                                <label id="ruku" class="fa-hover col-md-2 col-sm-2">入库信息</label>  
                                <tr>
                                    <th>入库时间</th>
                                    <th>产品型号</th>
                                    <th>入库数量</th>
                                </tr> 
                                <?php if(is_array($result4)): $i = 0; $__LIST__ = $result4;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr>
                                    <?php if(empty($vol["send_date"])): ?><td></td>                                      
                                    <?php else: ?> 
                                    <td><?php echo (date('Y-m-d H:i:s',$vol["send_date"])); ?></td><?php endif; ?>
                                    <td><?php echo ($vol["product_name"]); ?></td>
                                    <td><?php echo ($vol["send_num"]); ?></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; ?>                 
                            </table>                         
                            <button class="btn btn-outline btn-success" id="salecontract" type="button">打印维修合同</button>
                            <input type="hidden" id="user_name" value="<?php echo (session('staffId')); ?>">
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
<script>
//判断显示哪个表格
var flag = $("#flag").val();
if(flag == 1){
    $("#table2").html("");
    $("#ruku").html("");
}else{
    $("#table1").html("");
    $("#fahuo").html("");
}

//打印合同按钮
var username = $("#user_name").val();
var yid = $("#yid").val();
var isshow = $("#isshow").val();
if((username == yid)&&(isshow == 0)){
    $("#salecontract").attr("disabled",false);
}else{
    $("#salecontract").attr("disabled",true);
}
//打印维修合同
$(document).on('click','#salecontract',function () {
    var sid = $("#salesid").val();
    layer.open({
        type: 2,
        title: '维修合同',
        shadeClose : true,
        area: ['80%', '100%'],
        content: "/Dwin/SaleService/saleContract/sid/" + sid//iframe的url
    });
});
</script>
</body>
</html>