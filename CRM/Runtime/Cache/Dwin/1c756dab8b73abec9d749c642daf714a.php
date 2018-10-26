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
                <div class="ibox-title">
                    <h5>可申请项目列表</h5>
                    <div class="ibox-tools"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></div>
                </div>
                <div class="ibox-content">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                        <tr>
                            <th>选择</th>
                            <th>项目名称</th>
                            <th>研发类型</th>
                            <th>项目需求</th>
                            <th>绩效</th>
                            <th>项目起止时间</th>
                            <th>立项人</th>
                            <th>客户</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr class="gradeX">
                                <td class="center">
                                    <input type="hidden" name="nId" id="nId" value="<?php echo ($_GET['n']); ?>">
                                    <input type="hidden" name="staffId" id="staffId" value="<?php echo (session('staffId')); ?>">
                                    <input type="checkbox" name="prjCheckBox3" class="checkValue" data="<?php echo ($vol["auditorid"]); ?>" value="<?php echo ($vol["proid"]); ?>" >
                                </td>
                                <td data="<?php echo ($vol["proid"]); ?>" class="pubDetail"><?php echo ($vol["proname"]); ?></td>
                                <td  data="<?php echo ($vol["proid"]); ?>"><?php echo ($vol["pname"]); ?></td>
                                <td class="mouseOn publicPrj" data="<?php echo ($vol["proneeds"]); ?>"><?php echo (subtext($vol["proneeds"],15)); ?></td>
                                <td>绩效总金额<?php echo ($vol["performbonus"]); ?>元</td>
                                <td ><?php echo (date('Y-m-d',$vol["protime"])); ?>——<?php echo (date('Y-m-d',$vol["deliverytime"])); ?></td>
                                <td class="center"><?php echo ($vol["buildname"]); ?></td>
                                <td><?php echo ($vol["cusname"]); ?></td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                    <input class="btn btn-outline btn-success" type="button" id="selectPrj" value="项目申请" onclick="jqchk2('prjCheckBox3');" style="width: 10%; text-align: center;">
                </div>
            </div>
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                        <tr>
                            <td colspan="10"> <h4>公示中项目列表</h4></td>
                        </tr>
                        <tr>
                            <th>项目名称</th>
                            <th>项目需求</th>
                            <th>研发类型</th>
                            <th>参与人</th>
                            <th>总绩效</th>
                            <th>起止日期</th>
                            <th>客户</th>
                            <th>立项人</th>
                            <th>状态</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($prjPub)): $i = 0; $__LIST__ = $prjPub;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr class="gradeX">
                                <td class="pubDetail_2 mouseOn" data=<?php echo ($vol["proid"]); ?>><?php echo ($vol["proname"]); ?></td>
                                <td  class="publicPrj mouseOn" data="<?php echo ($vol["proneeds"]); ?>"><?php echo (subtext($vol["proneeds"],10)); ?></td>
                                <td><?php echo ($vol["pname"]); ?></td>
                                <td><?php echo ($vol["staffname"]); ?></td>
                                <td><?php echo ($vol["performbonus"]); ?></td>
                                <td><?php echo (date('Y-m-d',$vol["protime"])); ?>——<?php echo (date('Y-m-d',$vol["deliverytime"])); ?></td>
                                <td><?php echo ($vol["cusname"]); ?></td>
                                <td><?php echo ($vol["buildname"]); ?></td>
                                <td>
                                    <?php switch($vol["auditstatus"]): case "1": echo ($vol["auditname"]); ?>未审<?php break;?>
                                        <?php case "2": ?>立项前公示<?php break;?>
                                        <?php case "4": ?>审核不通过<?php break; endswitch;?>
                                </td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
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
            'bLengthChange' : true,
            'bInfo' :　true,
            'bAutoWidth': false,
            'iDisplayLength' :　10
        });
        var layera;
        $('.dataTables-example tbody').on('mouseover','.publicPrj', function () {
            var content = $(this).attr('data');
            layera = layer.tips( content , this,{
                tips : [1, '#3595CC'],
                area : '500px',
                time : 100000
            });
            return layera;
        });
        $('.dataTables-example tbody').on('mouseout','.publicPrj', function () {
            layer.close(layera);
        });
    });

    var chk_value = [], chk_data = [], chk_dat = [];
    function jqchk2(checkName)
    {
        chk_value = [];
        chk_data = [];
        chk_dat = [];
        $("input[name=" + checkName + "]:checked").each(function() {
            chk_value.push($(this).val());
            chk_data.push($(this).attr('data'));
            chk_dat.push($(this).attr('dat'));
        });
    }
    function buttonAction1(checkName, method2, layertitle) {
        if ($("input:checkbox[name='" + checkName + "']").is(':checked')) {
            //id : 登录人 auid：审核人 pid 项目id
            var pid = chk_value[0];
            layer.open({
                type : 2,
                title : layertitle,
                area : ['90%', '80%'],
                end        : function () {
                    location.reload();
                },
                content : controller + "/" + method2 + "/id/" + pid //iframe的url
            });
        } else {
            layer.alert('请选中项目');return false;
        }
    }
    $("#selectPrj").on('click', function () {
        buttonAction1('prjCheckBox3', 'showPubPrjDetail','项目申请');
    });

    $(".pubDetail").on('click', function (e) {
        var prjid = $(this).attr('data');
        e.stopPropagation();
        layer.open({
            type: 2,
            title: '可申请项目',
            area: ['100%', '100%'],
            end        : function () {
                location.reload();
            },
            content: controller + "/showPubPrjDetail/id/" + prjid //iframe的url
        });
    });

    $(".pubDetail_2").on('click', function (e) {
        var prjid = $(this).attr('data');
        e.stopPropagation();
        layer.open({
            type: 2,
            title: '项目立项前公示',
            area: ['100%', '100%'],
            end        : function () {
                location.reload();
            },
            content: controller + "/showPubPrjDetail/id/" + prjid //iframe的url
        });
    });
    var controller = "/Dwin/Research";
</script>
<script src="/Public/html/js/dwin/research/research.js"></script>
</body>
</html>