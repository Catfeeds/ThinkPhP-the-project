<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出入库记录</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;
            margin: 0!important;
            padding-left: 10mm;
            padding-right: 10mm;
        }
        @page one{
            size: 196mm 93mm;

        }
        .onePage {
            height: 80mm;
            width: 196mm;
        }
        .oneBill{
            height: 80mm;
            width: 196mm;
            padding: 0;
        }
        .title {
            width: 100%;
            text-align: center;
            font-size: 17px;
        }
        .baseMsg {
            min-height: 25px;
            line-height: 25px;
            width: 100%;
            font-size: 13px;
        }
        .materialMsg {
            border:1px solid black;
            width: 100%;
            min-height: 22px;
            line-height: 22px;
            text-align: center;
            border-collapse: collapse;
            font-size: 14px;
        }
        .materialMsg tr {
            width:100%;
            height: 25px;
            border:1px solid black;
        }
        .materialMsg td {
            border:1px solid black;
        }

        .userMsg {
            min-height: 22px;
            line-height: 22px;
            width: 100%;
            font-size: 13px;
        }

        .userMsg tr{
            width:100%;
            height: 30px;
        }

        .td1{
            width: 12%;
        }
        .td2{
            width: 38%;
        }
        .td3{
            width: 12%;
        }
        .td4{
            width: 38%;
        }
        .td5{
            width: 12%;
        }
        .td6{
            width: 38%;
        }
        .td7{
            width: 12%;
        }
        .td8{
            width: 38%;
        }
    </style>
</head>
<body class="gray-bg">
<div><button id="print">打印</button><?php echo ($fileUrl); ?>

</div>


<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/index.js"></script>
<script>
    PDFJS.cMapUrl = 'cmaps/'
    PDFJS.cMapPacked = true
    $("#print").on('click', function () {
        $("#print").css('display', 'none');
//        $(this).css('display','none');
        window.print();
    })
</script>
</body>
</html>