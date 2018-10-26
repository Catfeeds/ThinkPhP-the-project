<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>单据</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {color:black;}
        table .invoice-table1 thead tr th{text-align: left;}
    </style>
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox-content p-xl">
                    <div class="row">
                        <div class="col-sm-12">
                            <h4>订单基本信息</h4>
                            <table class="table invoice-table1">
                                <thead >
                                <tr style="text-align: left;">
                                    <th>
                                        <strong>单据编号：</strong><?php echo ($data["order_id"]); ?>
                                    </th>
                                    <th>订单时间：<?php echo (date('Y-m-d H:i:s',$data["otime"])); ?></th>
                                    <th>销货单类型：
                                        <?php switch($data["order_type"]): case "1": ?>正常销货<?php break;?>
                                            <?php case "2": ?>预收<?php break;?>
                                            <?php case "3": ?>应收借物<?php break;?>
                                            <?php case "4": ?>免费样品<?php break;?>
                                            <?php case "5": ?>借物退库<?php break;?>
                                            <?php case "6": ?>借物销货<?php break;?>
                                            <?php case "7": ?>退货<?php break;?>
                                            <?php case "8": ?>退款<?php break; endswitch;?>
                                    </th>
                                    <th>业绩类型：
                                        <?php switch($data["static_type"]): case "1": ?>技术服务费<?php break;?>
                                            <?php case "2": ?>市场拓展业绩<?php break;?>
                                            <?php case "3": ?>价值业绩<?php break; endswitch;?>
                                    </th>
                                    <th>业务员：<?php echo ($data["pic_name"]); ?></th>
                                    <th>业务电话:<?php echo ($data["pic_phone"]); ?></th>
                                </tr>
                                <tr>
                                    <th>客户名称：<?php echo ($data["cus_name"]); ?></th>
                                    <th>
                                        快递方式：
                                        <?php switch($data["logistics_type"]): case "1": ?>顺丰航空<?php break;?>
                                            <?php case "2": ?>顺丰陆运<?php break;?>
                                            <?php case "3": ?>德邦陆运<?php break;?>
                                            <?php case "4": ?>德邦快递<?php break;?>
                                            <?php case "6": ?>跨越<?php break;?>
                                            <?php case "7": ?>跨越次日达<?php break; endswitch;?>
                                    </th>
                                    <th>运费支付方式：
                                        <?php switch($data["freight_payment_method"]): case "1": ?>到付<?php break;?>
                                            <?php case "2": ?>寄付<?php break; endswitch;?>
                                    </th>
                                    <th>发货仓库：<?php echo ($data["ware_house"]); ?></th>
                                </tr>
                                <tr>
                                    <th>收货人：<?php echo ($data["receiver"]); ?></th>
                                    <th>收货人联系方式：<?php echo ($data["receiver_phone"]); ?></th>
                                    <th colspan="3">订单收货地址：<?php echo ($data["receiver_addr"]); ?></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="well m-t"><strong>订单备注：</strong> <?php echo ($data["logistices_tip"]); ?>
                    </div>
                    <div class="table-responsive m-t">
                        <h4>订单产品信息</h4>
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th>产品</th>
                                    <th>编号</th>
                                    <th>型号</th>
                                    <th>单价</th>
                                    <th>数量</th>
                                    <th>总价</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($prod)): $i = 0; $__LIST__ = $prod;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr>
                                    <td>
                                        <div><strong><?php echo ($vol["prodtype"]); ?></strong></div>
                                    </td>
                                    <td><?php echo ($vol["product_id"]); ?></td>
                                    <td><?php echo ($vol["product_name"]); ?></td>
                                    <td>&yen;<?php echo ($vol["product_price"]); ?></td>
                                    <td><?php echo ($vol["product_num"]); ?></td>
                                    <td>&yen;<?php echo ($vol["product_total_price"]); ?></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- /table-responsive -->

                    <table class="table invoice-total">
                        <tbody>
                            <tr>
                                <td><strong>总计：</strong></td>
                                <td>&yen;<?php echo ($data["oprice"]); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <h4>发票情况</h4>
                    <table class="table invoice-table1">
                        <thead >
                        <tr style="text-align: left;">
                            <th>
                                <strong>
                                    结算方式：
                                </strong>
                                <?php echo ($data["settle_name"]); ?>
                            </th>
                            <th>
                                <strong>票货情况：</strong>
                                <?php switch($data["invoice_situation"]): case "6": ?>现开<?php break;?>
                                    <?php case "5": ?>换货<?php break;?>
                                    <?php case "4": ?>发票待开<?php break;?>
                                    <?php case "3": ?>累开<?php break; endswitch;?>
                            </th>
                            <th>付款方式：
                                <?php switch($data["invoice_situation"]): case "1": ?>增票<?php break;?>
                                    <?php case "2": ?>普票<?php break;?>
                                    <?php case "3": ?>换货<?php break;?>
                                    <?php case "4": ?>营业税票<?php break;?>
                                    <?php case "5": ?>收据<?php break; endswitch;?>
                            </th>
                            <th>发票收件人：<?php echo ($data["invoice_contact"]); ?></th>
                            <th>收件人电话：<?php echo ($data["invoice_phone"]); ?></th>
                            <th>发票接收地址：<?php echo ($data["invoice_addr"]); ?></th>
                        </tr>
                        </thead>
                    </table>
                    <div class="well m-t"><strong>财务备注：</strong> <?php echo ($data["finance_tip"]); ?>
                    </div>
                </div>
            </div>
            <input type="button" value="打印" id="print">
        </div>
    </div>
    <script src="/Public/html/js/jquery.min.js?v=2.1.4"></script>
    <script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/Public/html/js/content.min.js?v=1.0.0"></script>
    <script type="text/javascript">
        $("#print").on('click', function (){
            $("#print").css('display', 'none');
            window.print();
        });
        //window.print();
    </script>
</body>
</html>