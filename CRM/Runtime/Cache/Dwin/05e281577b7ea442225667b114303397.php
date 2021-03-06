<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>售后维修记录</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        .selected{
            background-color: #5BC0DE !important;
        }
        .active{
            cursor:pointer;
        }
        body{
            color:black;
        }
        .two_content{
            height: 100%;
            width: 100%;
            text-align: center;
        }
        .styleBtu{
            display: inline-block;
            margin-top: 10px;
            background-color: #fff !important;
            border-color: #1c84c6 !important;
            color: #1c84c6 !important; 
            border-radius: 3px !important;
            height: 40px;
            padding: 1px 5px; 
            width: 30%;
            font-size: 14px;       
        }
        #odd_input_text{
            margin-top: 10px;
            border:1px solid#1c84c6 !important;
            border-radius: 3px !important;
            height: 40px;
            width: 30%;
        }
        .styleBtu_button{
            margin-top: 18px;
            background-color: #1c84c6;
            font-weight: bold;
            font-size: 15px;
            color: #fff;
            border-radius: 3px !important;
            border:1px solid #1c84c6;
            height: 35px;
            width: 20%;
        }
        .odd_input{
            text-align: center;
            margin: 5px 0px;
        }
        .ele-BUT{
           display: inline-block;
            font-size: 12px;
           height: 25px;
            color: #1c84c6;
            border: 1px solid #1c84c6;
            border-radius:3px;
            padding: 0;
       }
       .col-sm-2{
           width: 13%;
       }
       .row{
           height: 35px;
       }
    </style>
</head>
<body>
    <div class="wrapper wrapper-content">
    <div class="col-sm-2" id="prj_order_type" style="margin-left: -12px;">
        <select class="form-control chosen-select btn-outline ele-BUT push_down" name="order_type" id="order_type" style="width:100%;" tabindex="2">
            <option value="0">个人记录</option>
            <option value="1">下属记录</option>
            <option value="2">售后人员查看选项</option>
        </select>
    </div>
    <div class="col-sm-2" id="prj_order_over" style="margin-left: -12px;">
        <select class="form-control chosen-select btn-outline ele-BUT push_down" name="order_over" id="order_over" style="width:100%;" tabindex="2">
            <option value="0">未审核</option>
            <option value="1">待收费确认</option>
        </select>
    </div>
    <div class="row" style="margin-top: 20px;">
        <div class="col-md-12" style="margin-top: 10px;">
            <table class="table table-striped table-bordered table-hover dataTables-example" id="table0">
                <thead>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div id="auditBtn" style="margin-top: 15px;"><input type="hidden" id="user_name" value="<?php echo (session('staffId')); ?>"></div>
        </div>
    </div>
    </div>
</body>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script>
    var saleType = <?php echo (json_encode($saleType)); ?>;
    var controller = "/Dwin/SaleService";
    $(document).ready(function() {
        $('.dataTables-example').dataTable({
            "order"        : [[0,'desc']],
            "paging"       : true,
            "autoWidth"    : false,
            "pagingType"   : "full_numbers",
            "lengthMenu"   : [10, 15, 20, 100],
            "bDeferRender" : true,
            "processing"   : true,
            "searching"    : true, //是否开启搜索
            "serverSide"   : true,//开启服务器获取数据
            "ajax"         :{ // 获取数据
                "type"  : "post",
                "url"   : controller + "/showAllUnrecord",
                "data"  : {
                    "prj_order_type" : function () {
                        return document.getElementById('order_type').value;
                    },
                    "prj_order_over" : function () {
                        return document.getElementById('order_over').value;
                    },
                },
            },
            "columns" :[ //定义列数据来源
                {'title' : "售后单号",   'data' : 'sale_number','class':'active'},
                {'title' : "售后录入时间",'data' : 'repair_t'},
                {'title' : "收货快递单号",'data' : 'courier_number'},
                {'title' : "客户名称",   'data' : "cusname"},
                {'title' : "业务员",     'data' : "salename"},
                {'title' : "售后<br>专员",   'data' : "sale_commissioner_name"},
                {'title' : "送修<br>时间",   'data' : "start_t"},
                {'title' : "故障<br>类型",   'data' : "reperson_question"},
                {'title' : "维修品<br>状态",' data' : "is_ok"},
                {'title' : "业务<br>审核",   'data' : "is_show"},
                {'title' : "是否有<br>售后维修单",'data' : "is_repairorder"},
                {'title' : "结束<br>时间",   'data' : "over_t"},
                {'title' : "流程<br>状态",   'data' : "is_over"},
                {'title' : "销售<br>单号",   'data' : "sale_slip"},
                {'title' : "审核<br>类型",   'data' : "sale_type"}
            ],
            "columnDefs"   : [ //自定义列
                {
                    "targets" : 2,
                    "data" : 'courier_number',
                    "visible": false,
                    "render" : function(data, type, row) {
                        var html = row.courier_number;
                        return html;
                    }
                },
                {
                    "targets":  8 ,
                    "data"   : 'is_ok',
                    "render" : function(data, type, row) {
                        var html = row.is_ok;
                        return html;
                    }
                },
                {
                    "targets" : 9,
                    "data" : 'is_show',
                    "render" : function(data, type, row) {
                        var html = row.is_show;
                        if(html == 1){
                            html = '有效';
                            return html;
                        }else if(html == 2){
                            html = '无效';
                            return html;
                        }else{
                            html = '未审核';
                            return html;
                        }
                    }
                },
                {
                    "targets" : 12,
                    "data" : 'is_over',
                    "render" : function(data, type, row) {
                        var html = row.is_over;
                        if(html == 1){
                            html = '完结';
                            return html;
                        }else{
                            html = '进行中';
                            return html;
                        }
                    }
                }
            ],


            "language"     : { // 定义语言
                "sProcessing"     : "加载中...",
                "sLengthMenu"     : "每页显示 _MENU_ 条记录",
                "sZeroRecords"    : "没有匹配的结果",
                "sInfo"           : "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                "sInfoEmpty"      : "显示第 0 至 0 项结果，共 0 项",
                "sInfoFiltered"   : "(由 _MAX_ 项结果过滤)",
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
        });
    });
    // 点击tr 获取数据
    var tr_data
    $('tbody').on('click','tr',function(){
        var oTable = $(".dataTables-example").DataTable();
        tr_data = oTable.row(this).data();
    })
    // 点击获取对应数据
    $("#order_type").on('change', function () {
        var oTable = $(".dataTables-example").DataTable();
        oTable.ajax.reload();
    });

    $("#order_over").on('change', function () {
        var oTable = $(".dataTables-example").DataTable();
        oTable.ajax.reload();
    });
    //增加属性 是本人且达到is_ok = 2 审核状态
    $('.dataTables-example tbody').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        }
        else {
            $('.dataTables-example').find('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var sid = $(this).attr('id');
            $.ajax({
                type:"post",
                url :"/Dwin/SaleService/checkDoubleAudit",
                data:{
                    sid:sid,
                },
                success :function(msg){
                    if(msg == 1){
                        $("#auditBtn").html("");
                        $("#auditBtn").append('<button class="btn  btn-success btn-outline" id="auditbutton"  type="button">审核选中项</button>');
                        $("#shoufei").remove();
                        $("#auditBtn").after('<button class="btn  btn-success btn-outline" id="shoufei" style="float:left;margin-top: 10px;" type="button">确认是否维修</button>');
                    }else if(msg == 2){
                        $("#shoufei").remove();
                        $("#auditBtn").html("");
                        $("#auditBtn").append('<button class="btn  btn-success btn-outline" id="auditbutton"  type="button">审核选中项</button>');
                    }else if(msg == 3){
                        $("#shoufei").remove();
                        $("#auditBtn").html("");
                        $("#auditBtn").append('<button class="btn  btn-success btn-outline" id="shoufei" style="float:left;margin-top: 10px;" type="button">确认是否维修</button>');
                    }else{
                        $("#auditBtn").html("");
                        $("#shoufei").remove();
                    }
                }
            });
        }
    });

    //收费确认
    $(document).on('click','#shoufei',function(){
        $('#shoufei').attr('disabled', 'disabled');
        // var orderId;
        $(".dataTables-example tbody tr").each(function () {
            if ($(this).hasClass('selected')) {
                orderId = $(this).attr('id');
            }
        });
        if (orderId) {
            layer.confirm('是否维修？',
                    {
                        btn  : ['通过选项','否'],
                        icon : 6,
                        cancel: function(){
                            $(".dataTables-example tbody tr").each(function () {
                                if ($(this).hasClass('selected')) {
                                    $(this).removeClass('selected');
                                }
                            });
                            $('#shoufei').attr('disabled', false);
                        }
                    }, function() {
                        layer.close(layer.index);
                        layer.open({
                            type: 1,
                            skin: 'layui-layer-demo', //样式类名
                            anim: 2,
                            title:'审核选项',
                            closeBtn: 1, //不显示关闭按钮
                            shadeClose: true, //开启遮罩关闭
                            area : ['50%', '40%'],
                            content: '<div class="two_content"><div class="odd_input">销货单号：<input type="text" id="odd_input_text"  placeholder="请输入销货单号"></div><div>维修类型：<select name="" id="production_line" class="form-control change-data styleBtu"><?php if(is_array($saleType)): $i = 0; $__LIST__ = $saleType;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($vol); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></div><div><button type="button" id="determine" onclick="determine_click_bug(orderId)" class="styleBtu_button">通 过</button></div></div>'
                        });
                        $('#odd_input_text').val(tr_data.sale_slip)
                        $('#production_line').val(tr_data.sale_type)
                        if(tr_data.sale_type !== '0'){
                            $('#odd_input_text').attr('disabled', 'disabled');
                            $('#production_line').attr('disabled', 'disabled');
                        }
                    }, function(){
                        layer.open({
                            type: 2,
                            closeBtn: 1, //不显示关闭按钮
                            shadeClose: true, //开启遮罩关闭
                            area : ['70%', '70%'],
                            content : '/Dwin/SaleService/addAuditReback/orderId/' +  orderId,
                            end : function () {
                                $('auditbutton').attr('disabled', false);
                            }
                        });
                    });
        } else {
            layer.alert("没有选中售后单");
            $('#shoufei').attr('disabled', false);
        }
    });
    //审核
    var orderId;
    $(document).on('click','#auditbutton',function(){
        $('#auditbutton').attr('disabled', 'disabled');
        // var orderId;
        $(".dataTables-example tbody tr").each(function () {
            if ($(this).hasClass('selected')) {
                orderId = $(this).attr('id');
            }
        });
        if (orderId) {
            layer.confirm('是否通过审核？',
                    {
                        btn  : ['通过选项','否'],
                        icon : 6,
                        cancel: function(){
                            $(".dataTables-example tbody tr").each(function () {
                                if ($(this).hasClass('selected')) {
                                    $(this).removeClass('selected');
                                }
                            });
                            $('#auditbutton').attr('disabled', false);
                        }
                    }, function() {
                        layer.close(layer.index);
                        layer.open({
                            type: 1,
                            skin: 'layui-layer-demo', //样式类名
                            anim: 2,
                            title:'审核选项',
                            closeBtn: 1, //不显示关闭按钮
                            shadeClose: true, //开启遮罩关闭
                            area : ['50%', '40%'],
                            content: '<div class="two_content"><div class="odd_input">销货单号：<input type="text" id="odd_input_text" placeholder="请输入销货单号"></div><div>审核类型：<select name="" id="production_line" class="form-control change-data styleBtu"><?php if(is_array($saleType)): $i = 0; $__LIST__ = $saleType;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vol): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($vol); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></div><div><button type="button" id="determine" onclick="determine_click(orderId)" class="styleBtu_button">通 过</button></div></div>'
                        });
                        $('#odd_input_text').val(tr_data.sale_slip)
                        $('#production_line').val(tr_data.sale_type)
                        if(tr_data.sale_type !== '0'){
                            $('#odd_input_text').attr('disabled', 'disabled');
                            $('#production_line').attr('disabled', 'disabled');
                        }
                    }, function(){
                         layer.open({
                            type: 2,
                            closeBtn: 1, //不显示关闭按钮
                            shadeClose: true, //开启遮罩关闭
                            area : ['70%', '70%'],
                            content : '/Dwin/SaleService/addAuditReback/orderId/' +  orderId,
                            end : function () {
                            $('auditbutton').attr('disabled', false);
                            }
                         });
                    });
        } else {
            layer.alert("没有选中售后单");
            $('#auditbutton').attr('disabled', false);
        }
    });
    // 维修 二级确定  
    function determine_click_bug(){
        var production_line_ = document.getElementById('production_line').value;
        var odd_input_text_ = document.getElementById('odd_input_text').value;
        if(odd_input_text_ == ''){
            layer.msg('请输入销货单号')
            return false
        }
        if(production_line_ == 0){
            layer.msg('请选择审核类型')
            return false
        }
        $.ajax({
            type : 'POST',
            url  : "/Dwin/SaleService/checkIsOk",
            data : {
                sale_type:production_line_,
                sale_slip:odd_input_text_,
                sid : orderId,
                flag : 5,
                money:0,
            },
            success : function(msg){
                if(msg['status'] == 1) {
                    layer.msg("ok,审核成功",
                            {
                                icon : 6,
                                time : 1000
                            },
                            function () {
                                window.location.reload();
                            }
                    );
                }else if(msg['status'] == 2){
                    layer.msg("审核出错！",
                            {
                                icon : 5,
                                time : 1000
                            },
                            function () {
                                window.location.reload();
                            }
                    );
                }else if(msg['status'] == 3){
                    layer.msg("sorry,您不是该单负责人！",
                            {
                                icon : 5,
                                time : 1000
                            },
                            function () {
                                window.location.reload();
                            }
                    );
                }else if(msg['status'] == 4){
                    layer.msg("产品未检测完毕，目前无法审核！",
                            {
                                icon : 5,
                                time : 1000
                            },
                            function () {
                                window.location.reload();
                            }
                    );
                }else if(msg['status'] == 5){
                    layer.msg("已审核，不能重复操作！",
                            {
                                icon : 5,
                                time : 1000
                            },
                            function () {
                                window.location.reload();
                            }
                    );
                }
            }
        });
    }
    // 审核二级确定  
    function determine_click(){
        var production_line_ = document.getElementById('production_line').value;
        var odd_input_text_ = document.getElementById('odd_input_text').value;
        if(odd_input_text_ == ''){
            layer.msg('请输入销货单号')
            return false
        }
        if(production_line_ == 0){
            layer.msg('请选择审核类型')
            return false
        }
        $.ajax({
            type : 'POST',
            url  : "/Dwin/SaleService/checkIsOk",
            data : {
                sale_type:production_line_,
                sale_slip:odd_input_text_,
                sid : orderId,
                flag : 5,
                money:1,
            },
            success : function(msg){
                if(msg['status'] == 1) {
                    layer.msg("ok,审核成功",
                            {
                                icon : 6,
                                time : 1000
                            },
                            function () {
                                window.location.reload();
                            }
                    );
                }else if(msg['status'] == 2){
                    layer.msg("审核出错！",
                            {
                                icon : 5,
                                time : 1000
                            },
                            function () {
                                window.location.reload();
                            }
                    );
                }else if(msg['status'] == 3){
                    layer.msg("sorry,您不是该单负责人！",
                            {
                                icon : 5,
                                time : 1000
                            },
                            function () {
                                window.location.reload();
                            }
                    );
                }else if(msg['status'] == 4){
                    layer.msg("产品未检测完毕，目前无法审核！",
                            {
                                icon : 5,
                                time : 1000
                            },
                            function () {
                                window.location.reload();
                            }
                    );
                }else if(msg['status'] == 5){
                    layer.msg("已审核，不能重复操作！",
                            {
                                icon : 5,
                                time : 1000
                            },
                            function () {
                                window.location.reload();
                            }
                    );
                }
            }
        });
    }
    //跳转对应状态页面
    $(document).on("click",'tbody .active', function () {
        var sid = $(this).parent().attr('id');
        layer.open({
            type: 2,
            title: '编辑维修记录',
            shadeClose : true,
            end : function () {
                $('.dataTables-example').find('tr.selected').removeClass('selected');
            },
            area: ['100%', '100%'],
            content: "/Dwin/SaleService/cusshowdetailSaleRepairing/sid/" + sid //iframe的url
        });
    });

    $("#addSaleRepairing").on('click', function () {
        layer.open({
            type: 2,
            shadeClose : true,
            end : function () {
                $('.dataTables-example').find('tr.selected').removeClass('selected');
            },
            title: '添加维修记录',
            area: ['100%', '100%'],
            content: "/Dwin/SaleService/addSaleRepairing"  //iframe的url
        });
    });
    //数据报表生成
    $("#dataExport").on('click', function () {
        layer.open({
            type: 2,
            shadeClose : true,
            title: '数据报表',
            area: ['100%', '100%'],
            content: "/Dwin/SaleService/saledataExport"  //iframe的url
        });
    });
</script>
</html>