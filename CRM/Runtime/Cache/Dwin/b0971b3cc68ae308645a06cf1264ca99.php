<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客户列表-数据表格</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;
        }

        td {
            cursor: pointer!important;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>待审核列表</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </div>
                </div>
                <div class="ibox-content">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                        <tr>
                            <th>选择</th>
                            <th>客户名称</th>
                            <th>行业</th>
                            <th>联系记录</th>
                            <th>客户创建时间</th>
                            <th>客户级别</th>
                            <th>申请人</th>
                            <th>待审核类型</th>
                            <th>审核人</th>
                            <th>审核进度</th>
                            <th>类似客户</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($data2)): $i = 0; $__LIST__ = $data2;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr class="gradeX">
                                <td class="center"><input type="checkbox" name="checkBox2" class="checkValue"
                                                          dat="<?php echo ($vol["cid"]); ?>" data="<?php echo ($vol["auditorid"]); ?>" value="<?php echo ($vol["cid"]); ?>">
                                </td>
                                <td class="mouseOn cusDetail" data="<?php echo ($vol["cid"]); ?>"><?php echo ($vol["cname"]); ?></td>
                                <td data="<?php echo ($vol["ctype"]); ?>"><?php echo ($vol["indus"]); ?></td>
                                <td class="mouseOn saleList" data="<?php echo ($vol["cid"]); ?>"><?php echo ($vol["recordnum"]); ?></td>
                                <td><?php echo (date('Y-m-d H:i:s',$vol["addtime"])); ?></td>
                                <td><?php echo ($vol["clevel"]); ?>级</td>
                                <td><?php echo ($vol["uname"]); ?></td>
                                <td class="center">
                                    <?php switch($vol["type"]): case "1": ?>新客户创建<?php break;?>
                                        <?php case "2": ?>老客户申请<?php break;?>
                                        <?php case "3": ?>信息修改<?php break; endswitch;?>
                                </td>
                                <td class="center"><?php echo ($vol["auditorname"]); ?></td>
                                <td class="center">
                                    <?php switch($vol["auditstatus"]): case "1": echo ($vol["auditorname"]); ?>未审核<?php break;?>
                                        <?php case "2": ?>总经理未审核<?php break;?>
                                        <?php case "4": ?>审核不通过<?php break; endswitch;?>
                                </td>
                                <td>
                                    <?php echo ($vol["similar"]); ?>
                                </td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                    <input class="btn btn-outline btn-success" type="button" id="checkCus" value="客户审核"
                           onclick="jqchk('checkBox2');" style="width: 10%; text-align: center;">
                    <input class="hidden" type="hidden" id="role" value="<?php echo (session('staffId')); ?>">
                </div>
                <div class="ibox-content">
                    <input type="text" name="cusName" id="cusNameForSearch">
                    <span>(数据库内客户搜索，支持客户名称查询)</span>
                    <table class="table table-striped table-bordered table-hover ">
                        <thead>
                        <tr>
                            <th>客户名称</th>
                            <th>行业</th>
                            <th>客户负责人</th>
                            <th>客户创建时间</th>
                        </tr>
                        </thead>
                        <tbody id="listof">
                        </tbody>
                    </table>
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
<script src="/Public/html/js/dwin/customer/common_func.js"></script>
<script>
    $(document).ready(function() {
        $(".dataTables-example").dataTable({
            'autoWidth' : false
        });
    });
    var controller = "/Dwin/Customer";

</script>
<script src="/Public/html/js/dwin/customer/business_aud.js"></script>
</body>
</html>