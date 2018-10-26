<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客户列表-数据表格</title>
    <link href="/Public/html/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- Data Tables -->
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body {
            color: black;
        }
        td{
            cursor:pointer;
        }
	   .selected{
            background-color: gray !important;
        }
        .ibox-title {
            padding-top: 7px;
        }
        .td-width-setting{
            width:20%;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>公共客户列表</h5>
                </div>
                <div class="ibox-content">
                    <a href="javascript:;" id="common_add">
                        <button type="button" class="btn btn-warning btn-sm btn-outline">
                            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 公共客户录入
                        </button>
                    </a>
                    <a href="javascript:;" id="common_import">
                        <button type="button " class="btn btn-warning btn-sm btn-outline">
                            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 批量录入
                        </button>
                    </a>
                    <input class="btn btn-outline btn-sm btn-success" type="button" id="BusApplication" value="客户申请" onclick="jqchk('checkBox2');" style="width: 10%; text-align: center;">
                    <span id="authDiv"></span>
                    <table class="table table-striped table-bordered table-hover dataTables-common">
                       <!--  <tbody>
                       <?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><tr>
                               <td class="center"><input type="checkbox" name="checkBox2" class="checkValue" dat="<?php echo ($vol["cid"]); ?>" data="<?php echo ($vol["auditorid"]); ?>" value="<?php echo ($vol["cid"]); ?>" ></td>
                               <td><?php echo ($vol["cname"]); ?></td>
                               <td><?php echo ($vol["indus"]); ?></td>
                               <td class="center"><?php echo ($vol["province"]); ?></td>
                               <td class="center"><?php echo ($vol["clevel"]); ?>级</td>
                               <td class="center"><?php echo (date('Y-m-d H:i:s',$vol["addtime"])); ?></td>
                               <td class="center"><?php echo ($vol["name"]); ?></td>
                               <td>
                                   <?php if(!empty($vol["sub_name"])): ?>上级公司：<?php echo ($vol["sub_name"]); endif; ?>
                                   <?php if(!empty($vol["son_name"])): ?>子公司：<?php echo ($vol["son_name"]); endif; ?>
                               </td>
                           </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                       </tbody> -->
                    </table>

                    <input class="hidden" type="hidden" id="role" value="<?php echo (session('staffId')); ?>">
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
    var controller = "/Dwin/Customer";
    $(document).ready(function()
    {
        var authFlag = <?php echo ($auth); ?>;
        var delDiv = $("#authDiv");

        var table = $(".dataTables-common").DataTable({
            "paging"       : true,
            "autoWidth"    : false,
            "pagingType"   : "full_numbers",
            "lengthMenu"   : [10, 15, 20, 100],
            "bDeferRender" : true,
            "processing"   : true,
            "searching"    : true, //是否开启搜索
            "serverSide"   : true,//开启服务器获取数据
            "ajax"         :{ // 获取数据
                "url"   : controller + "/showCommonCustomerList",
                "type"  : 'post'
            },
            "columns"      :[ //定义列数据来源
                {'title' : "客户名称", 'data' : "cus_name", 'class' : "td-width-setting"},
                {'title' : "行业", 'data':"indus"},
                {'title' : "所在地", 'data' : "province",'class' : 'td-width-setting'},
                {'title' : "创建时间", 'data' : "add_time"},
                {'title' : "创建人", 'data' : "builder_name"},
                {'title' : "关联公司", 'data' : "sub_name"}
                /* {'title':"负责人",'data':null,'class':"align-center"} // 自定义列   {'title':"负责人",'data':null,'class':"align-center"} // 自定义列*/
            ],
            "language"     : { // 定义语言
                "sProcessing"     : "加载中...",
                "sLengthMenu"     : "每页显示 _MENU_ 条记录",
                "sZeroRecords"    : "没有匹配的结果",
                "sInfo"           : "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                "sInfoEmpty"      : "显示第 0 至 0 项结果，共 0 项",
                "sInfoFiltered"   : "(由 _MAX_ 项结果筛选)",
                "sInfoPostFix"    : "",
                "sSearch"         : "搜索:",
                "sUrl"            : "",
                "sEmptyTable"     : "表中数据为空",
                "sLoadingRecords" : "载入中...",
                "sInfoThousands"  : ",",
                "oPaginate"       : {
                    "sFirst"    : "首页",
                    "sPrevious" : "上一页",
                    "sNext"     : "下一页",
                    "sLast"     : "末页"
                },
                "oAria"           : {
                    "sSortAscending"  : ": 以升序排列此列",
                    "sSortDescending" : ": 以降序排列此列"
                }
            }
        });//table end
        if (authFlag == 200) {
            var html = '<input class="btn btn-outline btn-success btn-sm" type="button" id="CustomerDel" value="删除客户" onclick="jqchk(\'checkBox2\');" style="width: 10%; text-align: center;">';
            delDiv.html(html);
        }
        delDiv.on('click',"#CustomerDel", function () {
            layer.open({
                type: 2,
                skin: 'layui-layer-rim', //加上边框
                area: ['80%', '80%'], //宽高
                anim: 1,
                end: function () {
                    table.ajax.reload();
                },
                shadeClose : true,
                content: '/Dwin/Customer/getDelDataList'
            });
        });

        $(".dataTables-common tbody").on('click',"tr", function () {
            var oTables = $('.dataTables-common').DataTable();
            if ( $(this).hasClass('selected') ) {
                $(this).removeClass('selected');
            } else {
                oTables.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        });
    });

</script>
<script src="/Public/html/js/dwin/customer/business_common.js"></script>

</body>
</html>