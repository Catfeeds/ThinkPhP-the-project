<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>售后维修单</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {color:black;}
        table {border:1px solid black}
        td{text-align: center;   font-family:  "宋体"; word-wrap:  break-word
  	}
    </style>
    <style media="print">
    @page {
      size: A4;  /* auto is the initial value */
      margin: 0mm; /* this affects the margin in the printer settings */
    }
	</style>
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">	    	
	    <table class="table table-bordered" id="table1">                               
            <tr>
                <td colspan="12">
					<div style="text-align: left; float: left;">日期/Date:<br><?php echo (date('Y/m/d',$time)); ?><br></div>
            		<div style="float: left;margin-left: 180px; font-size: 18px;">迪文科技售后服务单<br>AS Form of DWIN<br></div>
            		<div style="text-align: right; float: right;">400-018-9008<br>www.dwin.com.cn<br>dwinhmi@dwin.com.cn</div>	
                </td>
            </tr>
            <?php if(is_array($result1)): $i = 0; $__LIST__ = $result1;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol1): $mod = ($i % 2 );++$i;?><tr>                                
                <td colspan="2">
                    客户名称：<br>CUST
                </td>                                
                <td colspan="2">
                    <?php echo ($vol1["cusname"]); ?>
                </td>                                
                <td colspan="2">
                    联系方式：<br>
                    Contact		                    
                </td>
                <td colspan="2">
                    <input type="text" style="border:none;" class="form-control" name="phonenumber" value="">
                </td>
                <td colspan="2">
                    迪文业务员:<br>
                    Salesman
                </td>
                <td colspan="2">
                    <?php echo ($vol1["salename"]); ?>
                </td>                                                                      
            </tr>                                
            <tr>                                                                         
                <td colspan="2">
                    返回地址:
                </td>
                <td colspan="10">
                	<textarea class="form-control" style="resize:none;overflow:hidden;margin: 0px;width: 100%;height: 100%;border: none" name="reback_address"><?php echo ($vol1["reback_address"]); ?></textarea>
                </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
        	<tr>                                                                         
                <td colspan="1">No.</td>
                <td colspan="3">产品型号/Model</td>
                <td colspan="2">批次号<br>Lot No.</td>
                <td colspan="1">数量<br>Quality</td>
                <td colspan="2">故障现象<br>Description</td>
                <td colspan="2">故障原因及检修情况</td>
                <td colspan="2">备注<br>Remark</td>
            </tr>
            <?php if(is_array($result)): $k = 0; $__LIST__ = $result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($k % 2 );++$k;?><tr>                                                                         
                <td colspan="1" style="width: 100px;"><?php echo ($k); ?></td>
                <td colspan="3" style="width: 150px;"><?php echo ($vol["product_name"]); ?></td>
                <td colspan="2" style="width: 100px;"><?php echo ($vol["barcode_date"]); ?></td>
                <td colspan="1" style="width: 100px;"><?php echo ($vol["send_num"]); ?></td>
                <td colspan="2" style="width: 100px;">
                    <textarea style="text-align: center; resize:none;overflow:hidden;margin: 0px;width: 100%;height: 100%;border: none">
                        <?php if(empty($vol["customer_question"])): ?>检修<?php else: echo ($vol["customer_question"]); endif; ?>
                    </textarea>
                </td>
                <td colspan="2" style="text-align: left; width: 100px;">
                    <textarea style="resize:none;overflow:auto;margin: 0;width: 100%;height: 100px;border: none"><?php echo ($vol["mode_info"]); ?></textarea>
                </td>
                <td colspan="2" style="text-align: left; width: 100px;">
                    <textarea style="resize:none;overflow:auto;margin: 0px;width: 100%;height: 100px;border: none"></textarea>
                </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>           
            <tr id="zhuijia">
            	<td colspan="12" style="text-align: left;">
            		<div><p>迪文保修服务说明：	</p></div>
            		<div><p>1、自购买之日起一年内非客责故障产品，迪文提供免费保修服务。</p></div>
            		<div><p>2、产品超出迪文科技所规定的保修期限或因客户未依据产品使用说明书所指示的工作环境使用、维护、保管所导致的故障和损坏，
            		迪文科技收取人工费用和替换器件费用后提供维修服务。</p></div>
            		<div><p>3、为避免产品核对不清以及售后服务延迟，请务必详细填写本单据随产品返回迪文科技。</p></div>
            		<div><p>4、为避免产品因拒收导致丢失等情况，请您在寄出产品时承担单程邮寄费用。</p></div>
            	</td>
            </tr>		            
        </table>
        <input type="button" class="btn btn-outline btn-success" value="打印" id="print">
	</div>       	
<script src="/Public/html/js/jquery.min.js?v=2.1.4"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script type="text/javascript">
//如果产品型号小于5，自动追加行到5
$(document).ready(function(){
    //自定义调试高度

	var biao = $("#table1").find('tr').length;
	var hang = biao - 5;
	if(hang < 5){
		var a = 5 - hang;
		for (var i = 1; i <= a; i++) {
			$("#zhuijia").before(
				'<tr>'+                                                                         
	                '<td colspan="1">'+(hang+i)+'</td>'+
	                '<td colspan="3"></td>'+
	                '<td colspan="2"></td>'+
	                '<td colspan="1"></td>'+
	                '<td colspan="2"></td>'+
	                '<td colspan="2"></td>'+
	                '<td colspan="2"></td>'+
            	'</tr>'
	    		);
		}
	    	
	}
});

$("#print").on('click', function (){
//    $("#print").css('display', 'none');
    $(function() {
        $('textarea').each(function() {
            $(this).height($(this).prop('scrollHeight'));
            $(this).parent('td').text($(this).val());
            $(this).attr('display','none');
            console.log($(this).val());
        });
    });
    window.print();
});

</script>
</body>
</html>