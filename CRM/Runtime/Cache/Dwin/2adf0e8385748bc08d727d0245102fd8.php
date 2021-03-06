<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>生产延期投诉</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.0/animate.min.css" rel="stylesheet">
    <!--<link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">-->
    <!--<link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">-->
    <!--<link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">-->
    <!--<link href="/Public/html/css/animate.min.css" rel="stylesheet">-->
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">

    <style>
        body{
            color:black;
        }
        .selected{
            background: #d0d27e!important;
        }
        th{
            position: relative;
        }
        .resizeSymbol{
            position: absolute;
            right: 0;
            cursor: col-resize;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="ibox float-e-margins">
        <div class="ibox-content">
            <div class="title">
                <h4><?php echo ($title); ?></h4>
                <div>
                    <button class="btn btn-xs btn-outline btn-success add"><span class="glyphicon glyphicon-plus"></span>添加</button>
                    <button class="btn btn-xs btn-outline btn-success research"><span class="glyphicon glyphicon-edit"></span>调查</button>
                    <button class="btn btn-xs btn-outline btn-success validity"><span class="glyphicon glyphicon-edit"></span>监督</button>
                    <button class="btn btn-xs btn-outline btn-success del"><span class="glyphicon glyphicon-close"></span>删除</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="staff" class="table table-bordered table-hover table-striped">
                    <thead>
                    <th>编号 <span class="resizeSymbol">&nbsp;&nbsp;&nbsp;</span></th>
                    <th>日期<span class="resizeSymbol">&nbsp;&nbsp;&nbsp;</span></th>
                    <th>投诉事由<span class="resizeSymbol">&nbsp;&nbsp;&nbsp;</span></th>
                    <th>投诉人<span class="resizeSymbol">&nbsp;&nbsp;&nbsp;</span></th>
                    <th>状态<span class="resizeSymbol">&nbsp;&nbsp;&nbsp;</span></th>
                    <th>原因调查<span class="resizeSymbol">&nbsp;&nbsp;&nbsp;</span></th>
                    <th>处理措施<span class="resizeSymbol">&nbsp;&nbsp;&nbsp;</span></th>
                    <th>调查人<span class="resizeSymbol">&nbsp;&nbsp;&nbsp;</span></th>
                    <th>措施有效性<span class="resizeSymbol">&nbsp;&nbsp;&nbsp;</span></th>
                    <th>责任人<span class="resizeSymbol">&nbsp;&nbsp;&nbsp;</span></th>
                    <th>监督人<span class="resizeSymbol">&nbsp;&nbsp;&nbsp;</span></th>
                    <?php if($title != "服务质量投诉"): ?><th>生产单号<span class="resizeSymbol">&nbsp;&nbsp;&nbsp;</span></th><?php endif; ?>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.1.0/jquery.form.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/3.3.6/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/dataTables.bootstrap.min.js"></script>
<!--<script src="/Public/html/js/jquery-1.11.3.min.js"></script>-->
<!--<script src="/Public/html/js/vue.js"></script>-->
<!--<script src="/Public/html/js/jquery.form.js"></script>-->
<!--<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>-->
<!--<script src="/Public/html/js/plugins/jeditable/jquery.jeditable.js"></script>-->
<!--<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>-->
<!--<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>-->
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/index.js"></script>
<script>
    var table = $('#staff'). DataTable({
        ajax: {
            type: 'post'
        },
        serverSide: true,
        order:[[1, 'desc']],
        columns: [
            {data: 'number'},
            {data: 'create_time'},
            {data: 'complain_reason', render: function (value,a, row) {
                if (value){
                    value =  value.replace(/\r\n|\n/g, '<br>')
                    var allData = table.data();
                    var index = allData.indexOf(row);
                    var className = 'complain_reason' + index;
                    var str = ''
                    if (value.length > 10){
                        str = value.slice(0, 10) + '...'
                    } else {
                        str = value
                    }
                    return "<span class='complain_reason' id='" + className + "'>" + str + "</span>"
                }
                return value
            }},
            {data: 'proposer_name'},
            {data: 'status_str',searchable: false, orderable:false},
            {data: 'research', render: function (value,a, row) {
                    if (value){
                        value =  value.replace(/\r\n|\n/g, '<br>')
                        var allData = table.data();
                        var index = allData.indexOf(row);
                        var className = 'research' + index;
                        var str = ''
                        if (value.length > 10){
                            str = value.slice(0, 10) + '...'
                        } else {
                            str = value
                        }
                        str = str.replace(/\r\n|\n/g, '<br>')
                        return "<span class='research' id='" + className + "'>" + str + "</span>"
                    }
                    return value
            }},
            {data: 'processes', render: function (value,a, row) {
                    if (value){
                        value =  value.replace(/\r\n|\n/g, '<br>')
                        var allData = table.data();
                        var index = allData.indexOf(row);
                        var className = 'processes' + index;
                        var str = ''
                        if (value.length > 10){
                            str = value.slice(0, 10) + '...'
                        } else {
                            str = value
                        }
                        str = str.replace(/\r\n|\n/g, '<br>')
                        return "<span class='processes' id='" + className + "'>" + str + "</span>"
                    }
                    return value
            }},
            {data: 'handler'},
            {data: 'processes_validity', render: function (value,a, row) {
                    if (value){
                        value =  value.replace(/\r\n|\n/g, '<br>')
                        var allData = table.data();
                        var index = allData.indexOf(row);
                        var className = 'processes_validity' + index;
                        var str = ''
                        if (value.length > 10){
                            str = value.slice(0, 10) + '...'
                        } else {
                            str = value
                        }
                        str = str.replace(/\r\n|\n/g, '<br>')
                        return "<span class='processes_validity' id='" + className + "'>" + str + "</span>"
                    }
                    return value
            }},
            {data: 'liable',orderable:false, render: function (value,a, row) {
                    if (value){
                        value =  value.replace(/\r\n|\n/g, '<br>')
                        var allData = table.data();
                        var index = allData.indexOf(row);
                        var className = 'liable' + index;
                        var str = ''
                        if (value.length > 10){
                            str = value.slice(0, 10) + '...'
                        } else {
                            str = value
                        }
                        str = str.replace(/\r\n|\n/g, '<br>')
                        return "<span class='liable' id='" + className + "'>" + str + "</span>"
                    }
                    return value
                }},
            {data: 'auditor'}
            <?php if($title != "服务质量投诉"): ?>,
            {data: 'production_order'}<?php endif; ?>
        ],
        oLanguage: {
            "oAria": {
                "sSortAscending": " - click/return to sort ascending",
                "sSortDescending": " - click/return to sort descending"
            },
            "LengthMenu": "显示 _MENU_ 记录",
            "ZeroRecords": "对不起，查询不到任何相关数据",
            "EmptyTable": "未有相关数据",
            "LoadingRecords": "正在加载数据-请等待...",
            "Info": "当前显示 _START_ 到 _END_ 条，共 _TOTAL_ 条记录。",
            "InfoEmpty": "当前显示0到0条，共0条记录",
            "InfoFiltered": "（数据库中共为 _MAX_ 条记录）",
            "Processing": "<img src='../resources/user_share/row_details/select2-spinner.gif'/> 正在加载数据...",
            "Search": "搜索：",
            "Url": "",
            "Paginate": {
                "sFirst": "首页",
                "sPrevious": " 上一页 ",
                "sNext": " 下一页 ",
                "sLast": " 尾页 "
            }
        }
    })
    $('#staff').on('mouseenter', 'td', function () {
        var that = $(this).children('span');
        if (that.hasClass('complain_reason') || that.hasClass('research') || that.hasClass('processes') || that.hasClass('processes_validity') || that.hasClass('liable')) {
            var data = table.cell(that.parents('td')).data()
            layer.tips(data, $(this), {time: 9999999})
        }

    })
    $('#staff').on('mouseleave', 'th', function () {
        layer.closeAll();
    })
    var id
    var currentData
    $('tbody').on('click', 'tr', function () {
        currentData = table.row(this).data();
        id = currentData.id;
        $('tr').removeClass('selected')
        $(this).addClass('selected')
    })
    $('table').on('processing.dt', function () {
        id = undefined;
        $('tr').removeClass('selected')
    })

    $('.add').on('click', function () {
        var index = layer.open({
            type: 2,
            title: '投诉',
            shadeClose:true,
            content: '<?php echo ($postUrl); ?>',
            area: ['50%', '90%'],
            end: function () {
                table.ajax.reload()
            }
        })
    })

    $('.research').on('click', function () {
        if (id !== undefined){
            var index = layer.open({
                type: 2,
                title: '投诉',
                shadeClose:true,
                content: '<?php echo ($researchUrl); ?>' + /id/ + id,
                area: ['50%', '90%'],
                end: function () {
                    table.ajax.reload()
                }
            })
        } else {
            layer.msg('请先选择一行')
        }
    })
    $('.validity').on('click', function () {
        if (id !== undefined){
            var index = layer.open({
                type: 2,
                title: '投诉',
                shadeClose:true,
                content: '<?php echo ($validityUrl); ?>' + /id/ + id,
                area: ['50%', '90%'],
                end: function () {
                    table.ajax.reload()
                }
            })
        } else {
            layer.msg('请先选择一行')
        }
    })
    $('.del').on('click', function () {
        if (id !== undefined){
            layer.confirm('确认删除?', function (index) {
                $.post('<?php echo ($delUrl); ?>', {id: id}, function (res) {
                    layer.msg(res.msg)
                    table.ajax.reload()
                })
                layer.close(index)
            })
        } else {
            layer.msg('请先选择一行')
        }
    })



</script>
</body>
</html>