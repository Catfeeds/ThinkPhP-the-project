<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>公司客户行业性质列表-数据表格</title>
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
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>客户行业分类</h5>
                    <a style="float: right;" href="javascript:;" id="addIndus"><i class="fa fa-plus"></i> 添加新行业类型</a>
                </div>
                <div class="fa-hover col-md-2 col-sm-6"></div>
                <div class="ibox-content">
                    <table class="table table-bordered table-striped datatables-indus">
                        <thead>
                        <tr>
                            <!--         <th>编号</th>-->
                            <th>部门</th>
                            <th style="text-align: center;">修改名称  |  删除</th>
                        </tr>
                        </thead>
                        <tbody >
                        <?php if(is_array($indus)): $i = 0; $__LIST__ = $indus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr class="grade" >
                                <!--<td style="padding: 0px;" ><?php echo ($vol["id"]); ?></td>-->
                                <td style="padding: 0px;"><?php echo (str_repeat("&emsp;&emsp;",$vol["level"]*2)); echo ($vol["name"]); ?></td>
                                <td style="padding: 0;text-align: center" width="30%">
                                    <a class="btn btn-white btn-bitbucket edit" style="padding:0 10px 0 10px;" data="<?php echo ($vol["id"]); ?>" lv="<?php echo ($vol["level"]); ?>"><i class="fa fa-pencil-square-o"></i></a>&emsp;&emsp;&emsp;
                                    <a class="btn btn-white btn-bitbucket delete" style="padding:0 10px 0 10px;"  data="<?php echo ($vol["id"]); ?>" lv="<?php echo ($vol["level"]); ?>"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/Admin/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>

$(document).ready(function() {
        $('.datatables-indus').dataTable({
            'sort' : false,
            "searching" :false,
            'paging' : false,
            "scrollY": "400px",
            "scrollCollapse": "true"
        } );
    } );

$(".edit").on("click", function () {
    var id = $(this).attr('data');
    layer.open({
        type: 2,
        title: '修改行业名称',
	shadeClose : true,
        end : function () {
                window.location.reload();
            },
        area: ['60%', '60%'],
        content: "/Dwin/System/editIndus/dId/" + id //iframe的url
    });
});

$(".delete").on("click", function () {
    var id = $(this).attr('data');
    var lv = $(this).attr('lv');
    layer.confirm('删除选择的行业类型？',
        {
            icon  : 3,
            title :'alert',
	    shadeClose : true,
            end : function () {
                window.location.reload();
            },
            btn   : ['是的', '再想想']
        },
        function(){
            $.ajax({
                type : 'POST',
                url : "/Dwin/System/delIndus",
                data : {
                    id : id
                },
                success : function(msg) {
                    if (msg == 2) {
                        layer.msg('删除成功',
		    	function () {
			window.location.reload();
		    });
                    } else {
                        layer.msg('删除失败', function () {
		    	window.location.reload();
		    });
                    }
                }

            });
        },
        function () {}
    );
});

$("#addIndus").on('click', function () {
    layer.open({
        type: 2,
	shadeClose : true,
        end : function () {
                window.location.reload();
            },
        title: '添加行业类型',
        area: ['60%', '60%'],
        content: "/Dwin/System/addIndus" //iframe的url
    });
});
</script>
</body>
</html>