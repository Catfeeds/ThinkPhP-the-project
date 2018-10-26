<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdn.bootcss.com/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">
    <style>
        td input.el-upload__input{
           display: none;
           margin: 0 auto
       }
       table tbody td{
           text-align: center
       }
    </style>
</head>
<body>
<div id="app" v-loading="loading">
    <h3 style="margin: 20px;" class="text-center">供应商资质认证情况</h3>
    <table class="table table-striped table-hover table-bordered">
        <tr>
            <th>证书名称</th>
            <th>颁发机构</th>
            <th>开始时间</th>
            <th>结束时间</th>
            <th>文件名称</th>
            <th>上传文件</th>
            <th>操作</th>
        </tr>
        <tr v-for="(item, index) in certification" v-if="flag!=='del'">
            <td>
                <el-input v-model="item.cer_name" placeholder="证书名称"></el-input>
            </td>
            <td>
                <el-input v-model="item.issuing_authority" placeholder="颁发机构"></el-input>
            </td>
            <td>
                <el-date-picker
                    v-model="item.start_time"
                    type="date"
                    value-format="timestamp" 
                    format="yyyy 年 MM 月 dd 日"
                    placeholder="开始时间"
                    @blur="pickerOptions_editGO(item)"
                    >
                </el-date-picker>
            </td>
            <td>
                <el-date-picker
                    v-model="item.stop_time"
                    type="date"
                    value-format="timestamp"
                    format="yyyy 年 MM 月 dd 日"
                    placeholder="结束时间"
                    @blur="pickerOptions_editEnd(item)"
                    >
                </el-date-picker>
            </td>
            <td>
                <a href="" @click="awardsLookUp(item)" title="预览文件">
                    {{item.file_name}}
                </a>
            </td>
            <td>
                <el-upload
                        class="uploadResume"
                        action="<?php echo U('/dwin/purchase/upload');?>"
                        :data="{type: 1}"
                        :on-success="papersUploadSuccess"
                        :limit="1"
                >
                    <el-button size="small" @click="fileSuccessErrer(index)" type="primary">上传文件</el-button>
                </el-upload>
            </td>
            <td style="width: 130px;">
                <button class="btn btn-warning" @click="delCertification(index)" v-if="flag == 'get'">删除</button>
                <button class="btn btn-primary" @click="saveCertification(index)" v-if="flag == 'get'">保存</button>
            </td>
        </tr>
    </table>
    <button class="btn btn-info" @click="addCertification" style="margin-left: 50px;">新增资质认证</button>
    <button class="btn btn-info" @click="allSaveContact" style="margin-left: 50px;">保存所有数据</button>
</div>
<script src="/Public/html/js/jquery-1.11.3.min.js"></script>
<script src="/Public/html/js/vue.js"></script>
<script src="/Public/html/js/jquery.form.js"></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/html/js/plugins/jeditable/jquery.jeditable.js"></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="/Public/html/js/content.min.js?v=1.0.0"></script>
<script src="/Public/html/js/plugins/layer/layer.js"></script>
<script src="https://cdn.bootcss.com/element-ui/2.3.6/index.js"></script>
<script>
    var id = '<?php echo ($id); ?>';
    var vm = new Vue({
        el: "#app",
        data: function () {
            var vm = this
            
            return {
                num:0,
                loading: true,
                id:id,
                flag:'get',
                // reads:'no',
                certification:[],
                uploadIndex:''
            }
        },
        created: function () {
            this.uploadIndex = ''
            this.getData()
        },
        methods: {
            //  ===============校验  go=====================
            pickerOptions_editGO(item){
                if(item.stop_time){
                    if(item.start_time > item.stop_time){
                        item.stop_time = ''
                        layer.msg('生效时间不能大于失效时间')
                    }
                }
            },
            pickerOptions_editEnd(item){
                if(item.start_time){
                    if(item.start_time > item.stop_time){
                        item.stop_time = ''
                        layer.msg('生效时间不能大于失效时间')
                    }
                }
            },
            //  ===============校验  end=====================
            getData: function () {
                this.uploadIndex = ''
                var vm = this;
                $.post('<?php echo U("/Dwin/Purchase/getCertification");?>', {'id' : id}, function (res) {
                    if(res.status == 200){
                        vm.loading = false
                        for(var i=0;i<res.data.length;i++){
                            res.data[i].start_time =  res.data[i].start_time * 1000
                            res.data[i].stop_time = res.data[i].stop_time * 1000
                        }
                        vm.certification = res.data
                        vm.num = vm.certification.length
                    }
                })
            },
            // 文件改变的回调
            fileSuccessErrer(index){
                this.uploadIndex = index
            },
            // 上传文件成功的回调
            papersUploadSuccess (response, file, fileList) {
                if (response.status == 200){
                    vm.certification[this.uploadIndex].file_id = response.data.id
                    this.certification[this.uploadIndex].file_name = file.name
                }
                layer.msg(response.msg +',请保存！')
            },
            // 资质文件 点击名称-> 预览
            awardsLookUp(item){
                if(item.id == null||item.id == ''){
                    layer.msg('没有找到文件！')
                }else{
                    if(item.file_type == 'pdf'){
                        window.open('<?php echo U("previewCerPdf", [], "");?>/id/' + item.id)
                    }else{
                        window.open(item.file_url)
                    }
                }
            },
            // 单行删除
            delCertification: function (index) {
                var indexs = index + 1
                var vm = this
                if(indexs > vm.num){
                    this.certification.splice(index,1)
                }else if(indexs <= vm.num){
                    if(this.certification.length > vm.num){
                        layer.msg('请先保存修改的内容或删除')
                    }else{
                        var data = {
                            'id' : id,
                            'type':'certification',
                            'data' : this.certification[index]
                        }
                        layer.confirm('确认删除?', function (aaa) {
                            $.post('<?php echo U("/Dwin/Purchase/delSupplierOtherMsg");?>', data, function (res) {
                                if (res.status == 200) {
                                    vm.getData();
                                    location.reload();
                                }
                                layer.msg(res.msg)
                            })
                        })
                    }
                }
            },
            // 提交数据 单行保存
            saveCertification: function (index) {
                var vm = this
                vm.certification[index].start_time = vm.certification[index].start_time / 1000
                vm.certification[index].stop_time = vm.certification[index].stop_time / 1000
                var data = {
                    'id': id,
                    'type':'certification',
                    'data' : vm.certification[index]
                }
                $.post('<?php echo U("/Dwin/Purchase/editOrAddSupplierOneMsg");?>', data, function (res) {
                    if(res.status == 200){
                        vm.getData();
                        location.reload();
                    }else{
                        vm.certification[index].start_time = vm.certification[index].start_time * 1000
                        vm.certification[index].stop_time = vm.certification[index].stop_time * 1000
                    }
                    layer.msg(res.msg)
                })  
            },
            // 新增一行空数据
            addCertification: function () {
                 // 判断是否重复新增
                 if(this.certification[this.certification.length - 1] != undefined){
                    if(this.certification[this.certification.length - 1].cer_name){
                        var obj = {}
                        this.certification.push(obj)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    var obj = {}
                    this.certification.push(obj)
                }
                
            },
            // 保存所有数据
            allSaveContact () {
                var vm = this
                // 修改的数据
                var allAmend = vm.certification.slice(0,vm.num)
                for (var i = 0;i<allAmend.length;i++) {
                    allAmend[i].start_time = allAmend[i].start_time / 1000
                    allAmend[i].stop_time = allAmend[i].stop_time / 1000
                }
                // 新增的数据
                var allAdd = vm.certification.slice(vm.num)
                for (var i = 0;i<vm.certification.slice(vm.num).length;i++) {
                    allAdd[i].start_time = allAdd[i].start_time / 1000
                    allAdd[i].stop_time = allAdd[i].stop_time / 1000
                }
                var data = []
                var params = {
                    'id' : id,
                    'type':'certification',
                    'editData': allAmend,
                    'addData': allAdd
                }
                $.post('<?php echo U("/Dwin/Purchase/editSupplierMsg");?>', params, function (res) {
                    if(res.status == 200){
                        vm.getData();
                        // layer.open页面关闭
                        // var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                        // parent.layer.close(index)
                    }else{
                        for(var i = 0;i<allAmend.length;i++) {
                            allAmend[i].start_time = allAmend[i].start_time * 1000
                            allAmend[i].stop_time = allAmend[i].stop_time * 1000
                        }
                        for(var i = 0;i<vm.certification.slice(vm.num).length;i++) {
                            allAdd[i].start_time = allAdd[i].start_time * 1000
                            allAdd[i].stop_time = allAdd[i].stop_time * 1000
                        }
                    }
                    layer.msg(res.msg)
                })  
            }
        }
    })
</script>
</body>
</html>