<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客服通话列表-数据表格</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body { color: black;  }
        .mouseOn{ cursor:pointer;  }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>客服通话</h5>
                    <div class="ibox-tools"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></div>
                </div>
                <div class="ibox-content">
                    <table class="table table-striped table-bordered table-hover dataTables-onlineSale">
                        <thead>
                        <tr>
                            <th>客户名</th>
                            <th>行业</th>
                            <th>来电人</th>
                            <th>负责人</th>
                            <th>联系内容</th>
                            <th>回复内容</th>
                            <th>通话时间</th>
                            <th>客服</th>
                            <th>审核状态</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr class="gradeX">
                                <td><?php echo ($vol["cname"]); ?></td>
                                <td><?php echo ($vol["indusname"]); ?></td>
                                <td><?php echo ($vol["caller"]); ?></td>
                                <td><?php echo ($vol["dname"]); ?></td>
                                <td class="cusAsk mouseOn" data="<?php echo ($vol["content"]); ?>"><?php echo (subtext($vol["content"],10)); ?></td>
                                <td class="onAnswer mouseOn" data="<?php echo ($vol["answercontent"]); ?>"><?php echo (subtext($vol["answercontent"],10)); ?></td>
                                <td><?php echo (date('Y-m-d H:i',$vol["addtime"])); ?></td>
                                <td><?php echo ($vol["sname"]); ?></td>
                                <td>
                                    <?php switch($vol["austatus"]): case "1": ?><span class="noCheckYet">未审核</span><?php break;?>
                                        <?php case "2": ?><span class="checkYes">有效</span><?php break;?>
                                        <?php case "3": ?><span class="checkNot">无效</span><?php break; endswitch;?>
                                </td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/jeditable/jquery.jeditable.js"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    function showAllcontent(layera, className, clickName) {
        $('.dataTables-' + className + ' tbody').on('mouseover','.' + clickName, function () {
            var content = $(this).attr('data');
            layera = layer.tips( content , this,{
                tips : [1, '#3595CC'],
                area : '500px',
                time : 100000
            });
            return layera;
        });
        $('.dataTables-' + className + ' tbody').on('mouseout', '.' + clickName, function () {
            layer.close(layera);
        });
    }
    function dataTabelset(className, length) {
        $(".dataTables-" + className).dataTable({
            'bFilter' : true,
            'bLengthChange' : false,
            'bInfo' :　true,
            'iDisplayLength' :　length
        });
    }
    $(document).ready(function()
    {
        var layera;
        dataTabelset('onlineSale',10);
        showAllcontent('layera','onlineSale','cusAsk');
        showAllcontent('layera','onlineSale','onAnswer');
    });

    $(".noCheckYet").css('color','black');
    $(".checkNot").css('color','red');
    $(".checkYes").css('color','blue');
    $("td").on('mouseover',function() {
        co = $(this).css('background-color');
        return co;
    });
    $(".mouseOn").on('mouseover', function () {
        co = $(this).css('background-color');
        $(this).css('color', 'blue');
        $(this).css('background-color', 'yellow');
        return co;
    });
    $(".mouseOn").on('mouseout', function () {
        $(this).css('color', 'black');
        $(this).css('background-color', co);
    });
</script>
</body>
</html>