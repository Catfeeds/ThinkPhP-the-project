<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>项目列表-数据表格</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">

    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">

    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {  color: black;  }
        td {  cursor:pointer;  }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                        <tr>
                            <th>选择</th>
                            <th>参与人</th>
                            <th>部门</th>
                            <th>项目名称</th>
                            <th>绩效</th>
                            <th>项目变更</th>
                            <th>起止日期</th>
                            <th>客户</th>
                            <th>立项人</th>
                            <th>审核状态</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr class="gradeX">
                                <td class="center">
                                    <input type="checkbox" name="resCheckBox" class="checkValue" data="<?php echo ($vol["auditorid"]); ?>" value="<?php echo ($vol["proid"]); ?>" >
                                </td>
                                <td><?php echo ($vol["pname"]); ?></td>
                                <td class="center">
                                    <?php echo ($vol["deptname"]); ?>
                                </td>
                                <td class="prjDetail mouseOn" data=<?php echo ($vol["proid"]); ?>><?php echo ($vol["proname"]); ?></td>
                                <td class="performOfPrj mouseOn" data=<?php echo ($vol["proid"]); ?>><?php echo ($vol["performbonus"]); ?></td>
                                <td class="changeLayer mouseOn" data="<?php echo ($vol["proid"]); ?>"><?php echo ($vol["cnum"]); ?>条记录</td>
                                <td><?php echo (date('Y-m-d',$vol["protime"])); ?>——<?php echo (date('Y-m-d',$vol["deliverytime"])); ?></td>
                                <td><?php echo ($vol["cusname"]); ?></td>
                                <td><?php echo ($vol["buildname"]); ?></td>
                                <td class="center">
                                    <?php switch($vol["auditstatus"]): case "1": echo ($vol["auditname"]); ?>未审<?php break;?>
                                        <?php case "2": ?>总经理未审<?php break;?>
                                        <?php case "4": ?>审核不通过<?php break; endswitch;?>
                                </td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                    <input class="btn btn-outline btn-success" type="button" id="checkProject" value="项目审核" onclick="jqchk2('resCheckBox');" style="width: 10%; text-align: center;">
                    <input class="hidden" type="hidden" id="rolestaff" value="<?php echo (session('staffId')); ?>">
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    $(document).ready(function() {
        $(".dataTables-example").dataTable({
            'bFilter' : true,
            'bLengthChange' : false,
            'bInfo' :　true,
            'bAutoWidth': false,
            'iDisplayLength' :　5
        });
    });
    var controller = "/Dwin/Research";
</script>
<script src="/Public/html/js/dwin/research/research.js"></script>
</body>
</html>