<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM HOME</title>
    <link rel="shortcut icon" href="/Public/favicon.ico">
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">

    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        body{
            color:black;
        }
        .ibox-content{
            background-color: #CCCCCC;
            font-size:1.2em;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row animated fadeInRight">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>更新公告</h5>
                </div>

                <div class="ibox-content timeline">
                    <div class="timeline-item">
                        <div class="row">
                            <div class="col-xs-3 date">
                                <i class="fa fa-user-md"></i>ERP
                                <br>
                                <small class="text-navy">系统更新</small>
                            </div>
                            <div class="col-xs-7 content">
                                <p class="m-b-xs"><strong style="color:red;">2018.5.18更新说明：</strong>
                                <p class="m-b-xs"><strong>MRP上线</strong>
                                    <br/>生产计划管理功能上线两周，有问题请及时联系
                                    <br/>满意度调查添加数据统计功能<span style="color:red"></span>
                                    <br/><span style="color:blue;font-weight:600;">重要：</span><span style="color:red">发生权限不匹配的情况请及时联系</span>
                                </p>

                                <p class="m-b-xs"><strong>销货单配合MRP进行调整</strong>
                                    <br/>下销货单锁库存，除非删除销货单，不然会占用对应数量的库存。（以此判别是否需要下订单生产）
                                    <br/>
                                    <br/>
                                </p>
                                <p class="m-b-xs"><strong>KPI客户申请功能开放</strong>
                                    <br/>需要表示重要客户时，请申请设置为KPI客户。
                                    <br/>
                                    <br/>
                                </p>

                            </div>
                        </div>

                    </div>
                    <div class="timeline-item">
                        <div class="row">
                            <div class="col-xs-3 date">
                                <i class="fa fa-briefcase"></i> 系统优化
                                <br>
                                <small class="text-navy"></small>
                            </div>
                            <div class="col-xs-7 content no-top-border">
                                <p class="m-b-xs"><strong>系统优化</strong>
                                </p>

                                <p>财务订单加入订单生产、出库进度（后续业务部分也将进行调整）</p>
                                <p>售后界面调整</p>
                                <p></p>

                                <p><span data-diameter="40" class="updating-chart" style="display: none;">3,9,6,5,9,4,7,3,2,9,8,7,4,5,1,2,9,5,4,7,2,7,7,3,5,2,3,3,2,1,6,9,8,8,3,7,4</span>
                                    <svg class="peity" height="16" width="64">
                                        <polygon fill="#1ab394" points="0 15 0 10.5 1.7777777777777777 0.5 3.5555555555555554 5.5 5.333333333333333 7.166666666666666 7.111111111111111 0.5 8.88888888888889 8.833333333333332 10.666666666666666 3.833333333333332 12.444444444444443 10.5 14.222222222222221 12.166666666666666 16 0.5 17.77777777777778 2.166666666666666 19.555555555555554 3.833333333333332 21.333333333333332 8.833333333333332 23.11111111111111 7.166666666666666 24.888888888888886 13.833333333333334 26.666666666666664 12.166666666666666 28.444444444444443 0.5 30.22222222222222 7.166666666666666 32 8.833333333333332 33.77777777777778 3.833333333333332 35.55555555555556 12.166666666666666 37.33333333333333 3.833333333333332 39.11111111111111 3.833333333333332 40.888888888888886 10.5 42.666666666666664 7.166666666666666 44.44444444444444 12.166666666666666 46.22222222222222 10.5 48 10.5 49.77777777777777 12.166666666666666 51.55555555555555 13.833333333333334 53.33333333333333 5.5 55.11111111111111 0.5 56.888888888888886 2.166666666666666 58.666666666666664 2.166666666666666 60.44444444444444 10.5 62.22222222222222 3.833333333333332 64 8.833333333333332 64 15"></polygon>
                                        <polyline fill="transparent" points="0 10.5 1.7777777777777777 0.5 3.5555555555555554 5.5 5.333333333333333 7.166666666666666 7.111111111111111 0.5 8.88888888888889 8.833333333333332 10.666666666666666 3.833333333333332 12.444444444444443 10.5 14.222222222222221 12.166666666666666 16 0.5 17.77777777777778 2.166666666666666 19.555555555555554 3.833333333333332 21.333333333333332 8.833333333333332 23.11111111111111 7.166666666666666 24.888888888888886 13.833333333333334 26.666666666666664 12.166666666666666 28.444444444444443 0.5 30.22222222222222 7.166666666666666 32 8.833333333333332 33.77777777777778 3.833333333333332 35.55555555555556 12.166666666666666 37.33333333333333 3.833333333333332 39.11111111111111 3.833333333333332 40.888888888888886 10.5 42.666666666666664 7.166666666666666 44.44444444444444 12.166666666666666 46.22222222222222 10.5 48 10.5 49.77777777777777 12.166666666666666 51.55555555555555 13.833333333333334 53.33333333333333 5.5 55.11111111111111 0.5 56.888888888888886 2.166666666666666 58.666666666666664 2.166666666666666 60.44444444444444 10.5 62.22222222222222 3.833333333333332 64 8.833333333333332" stroke="#169c81" stroke-width="1" stroke-linecap="square"></polyline>
                                    </svg>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="row">
                            <div class="col-xs-3 date">
                                <i class="fa fa-file-text"></i>
                                <br>
                                <small class="text-navy">联系方式</small>
                            </div>
                            <div class="col-xs-7 content">
                                <p class="m-b-xs"><strong>马旭（研发4部）</strong>
                                </p>
                                <p>邮箱：maxu@dwin.com.cn</p>
                            </div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="row">
                            <div class="col-xs-3 date">
                                <i class="fa fa-file-text"></i><br/>
                                <small class="text-navy">tips</small>
                            </div>
                            <div class="col-xs-7 content">
                                <p class="m-b-xs"><strong>下一步开发计划</strong>
                                </p>
                                <p>
                                    库存管理迭代升级、售后问题解决
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    $(document).ready(function () {
        $.ajax({
            type : 'post',
            url : "/Dwin/Index/getOrderMsg",
            success : function (ajaxData) {
                if(ajaxData == 2) {
                    // 提示有订单不合格
                    layer.msg('您提交审核的订单有不合格信息，请前往订单管理修改不合格订单', function(){
                        //关闭后的操作
                    });
                }
            }
        });
    });
</script>
</body>

</html>