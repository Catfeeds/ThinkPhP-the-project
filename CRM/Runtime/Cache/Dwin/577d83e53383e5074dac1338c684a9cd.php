<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="/Public/html/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/html/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Public/html/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/html/css/animate.min.css" rel="stylesheet">
    <link href="/Public/html/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <link href="https://cdn.bootcss.com/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">
    <style>
        /* 多选框前面字体调整 */
        bode {
            color: #000;
        }
        /* 多选框整体调整 */
        
        .el-form-item__content {
            line-height: 36px;
        }
        /* 多选框前面预留 */
        
        .el-checkbox {
            margin-left: 16px;
        }
        
        .el-form-item {
            margin-bottom: 0px;
        }
        
        .tableWR {
            height: 40px;
            width: 100%;
            text-align: center;
            line-height: 40px;
            border: 1px solid #C0C4CC;
            font-weight: bold;
            color: #909399
        }
        
        .el-col-3 {
            margin-top: -1px;
        }s
        
        .el-aside_center {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            height: 100%;
        }
        
        .el-main {
            padding: 0;
        }

        .headline{
            margin:  20px auto;
            font-weight: bold;
            color: #000;
            text-align: center
        }
        .theSecondTitle{
            color: #e4393c
        }
        .TheThirdTitle{
            color: #000;
            padding: 7px 0;
            /* background-color: #C0C0C0 */
            border-bottom: 1px solid #ccc;
        }
        /* .demonstration{
            display: inline;
            background-color: rgb(245, 247, 250);
            padding: 11px 20px;
            color: rgb(144, 147, 153);
            border-right: 1px solid #DCDFE6;
            margin-right: -4px;
        } */
        .corporateProperty{
            border-bottom: 1px solid #DCDFE6;
        }
        .contBorder{
            text-align: center;
            border: 1px solid #C0C4CC;
            /* display: flex !important;
            justify-content: center !important;
            align-items: center !important */
        }
        .cont_head_sty{
            padding: 0;
            height: 42px !important;
            border: 1px solid #C0C4CC;
            border-top: none;
            border-left: none;
        }
        .ComboBox_sty{
            height: 100%;
            width: 100%;
        }
        .div_handerText{
            width: 100%;
            text-align: center;
            left:50;
        }
        .CompanyAddress{
            font-weight: bold;
        }
        .CompanyAddress_add{
            margin: 0 auto;
            width:70px;
            height:30px;
            font-size: 12px;
            padding: 0
        }
        .companyDescribe{
            border: 1px solid #C0C4CC
        }
        .col_sty{
            border-right: 1px solid #C0C4CC
        }
        .remove_btu{
            border-bottom: 1px solid #fff;
            border-top: 1px solid #fff
        }
        .table_head{
            width: 100%;
            height: 100%;
            text-align: center;
            line-height: 40px;
            font-size: 14px;
            font-weight: bold
        }
        .updata_sty{
            border-right: 1px solid #C0C4CC;
            border-bottom: 1px solid #C0C4CC;
            text-align: center
        }
        .up_sty{
            margin: 8px 0;
        }
        .addButStyle{
            font-size: 15px;
            text-align:center;
            border-bottom: 1px solid #C0C4CC;
            margin-top: -1px;
            line-height: 40px;
            border-right: 1px solid #C0C4CC;
        }
        .el-input-group__prepend{
            font-weight: bold;
            color: #000
        }
        .el-form-item__label{
            margin: 0 !important
        }
        .el-radio-group{
            margin-top: 10px !important;
        }
        .footer_after{
            padding: 0 !important;
            height: 40px !important;
        }
        /* .el-form-item__content{
            border:1px solid #dcdfe6
        } */
        .demonstration {
            font-weight: bold
        }
        /* 上传 */
        .uploadResume .el-upload .el-upload__input{
           display: none !important;
       }
       .el-upload-list__item{
           display: none
       }
       .el-upload--text{
           height: 38px;
       }
       .time_witch{
           width: 100% !important;
       }
       .click_coding:hover{
           background-color: #4BA51F!important
       }
       .click_coding{
           background-color: #67C23A!important;
            margin-left: 5px
       }
       /* input is errer */
       .errer_input{
           margin: -2px;
           border:1px solid #e4393c;
           border-radius: 4px;
       }
       .companyName{
            height: 51px;
            text-align: center;
            line-height: 51px;
            font-size: 22px;
            font-weight: bold;
            font-family: 微软雅黑
        }
        b{
            font-weight: bold;
            font-size: 15px
        }
        .detail_doc{
            cursor:pointer;
            color: blue;
        }
    </style>
</head>

<body>
    <div id="app">
        <!-- 第一部分  供应商基本信息 -->
        <el-row :span="24">
            <el-col :span="22" :offset="1">
                <el-row>
                    <el-col :span="5">
                        <img :src="imgURL" alt="湖南迪文科技有限公司">
                    </el-col>
                    <el-col :span="11" :offset="7" class="companyName">
                        湖南迪文科技有限公司供应商信息详情
                    </el-col>
                </el-row> 
                <br>
                    <!-- <h3 class="theSecondTitle">必填项：</h3> -->
                    <h3 class="TheThirdTitle">一、供应商基本信息</h3>
                    <br>
                <el-form ref="form" :model="form" label-width="150px" enctype="multipart/form-data">
                    <!-- 公司名称  、 法人代表 -->
                    <el-row >
                        <el-col :span="12" :offset="3">
                            <b>供应商名称：</b>{{form.supplier_name}}
                        </el-col>
                        <el-col :span="9">
                                <b>公司编号：</b>{{form.supplier_id}}
                        </el-col>
                    </el-row>
                    <br>
                    <el-row>
                        <el-col  :span="12" :offset="3">
                                <b>是否上市：</b>{{form.is_listed == 0? '未上市':'已上市'}}
                        </el-col>
                        <el-col :span="9">
                            <b>企业性质：</b>{{form.enterprise_cate}}
                        </el-col>
                    </el-row>
                    <br>
                    <!-- 注册资本、实收资本 -->
                    <el-row>
                        <el-col :span="12" :offset="3">
                            <b>注册资本(万)：</b>{{form.registered_capital}}
                        </el-col>
                        <el-col :span="9">
                                <b>实收资本(万)：</b>{{form.paid_up_capital}}
                        </el-col>
                    </el-row>
                    <br>
                    <!--营业执照号码、营业执照起止期限 -->
                    <el-row>
                        <el-col  :span="12" :offset="3">
                                <b>营业执照号码：</b>{{form.business_licence}}
                        </el-col>
                        <el-col :span="9">
                                <b>法人代表：</b>{{form.legal_name}}
                        </el-col>
                    </el-row>
                    <br>
                    <el-row>
                        <el-col  :span="12" :offset="3">
                            <b>营业执照生效时间：</b>{{form.start_date}}
                        </el-col>
                        <el-col :span="9">
                            <b>营业执照失效时间：</b>{{form.end_date}}
                        </el-col>
                    </el-row>
                    <br>
                    <!-- 是否上市、股票代码 -->
                    <el-row>
                        <el-col  :span="12" :offset="3">
                            <!-- <div> -->
                                <b>公司网址：</b>{{form.websitea_address}}
                        </el-col>
                        <el-col :span="9">
                            <b>股票代码：</b>{{form.stock_code}}
                        </el-col>
                    </el-row>
                    <br>
                    <!-- 开户行、账号 -->
                    <el-row>
                        <el-col  :span="12" :offset="3">
                            <b>开户行：</b>{{form.account_bank}}
                        </el-col>
                        <el-col :span="9">
                                <b>账号：</b>{{form.account_number}}
                        </el-col>
                    </el-row>
                    <br>
                    <!-- 主要业务范围（营业执照） -->
                    <el-row>
                        <el-col :span="21" :offset="3" >
                            <b>主要业务范围：</b>{{form.business_scope}}
                        </el-col>

                    </el-row>

        
                    <!--第二部分 供应商联系信息 -->
                    <h3 class="TheThirdTitle">二、供应商联系信息</h3>
                    <div v-for="(item, index) in contactData"  style="margin: 15px 0">
                        <el-row>
                            <el-col :span = "7" :offset="1">
                                <b>联系人：</b>{{item.contact_position}}
                            </el-col>
                            <el-col :span = "8">
                                <b>姓名：</b>{{item.contact}}
                            </el-col>
                            <el-col :span = "8">
                                <b>电话：</b>{{item.telephone}}
                            </el-col>
                    </el-row>
                    <el-row>
                            <el-col  :span = "7" :offset="1">
                                <b>手机：</b>{{item.phone}}
                            </el-col>
                            <el-col :span = "8">
                                <b>传真：</b>{{item.fax}}
                            </el-col>
                            <el-col :span = "8">
                                <b>电子邮件：</b>{{item.e_mail}}
                            </el-col>
                        </el-row>
                    </div>
                    <br>

                    <!--供应商联系信息 公司地址 -->
                    <h3 class="TheThirdTitle">三、地址信息</h3>
                    <div v-for="(item, index) in addressData"  style="margin: 15px 0">
                        <el-row>
                            <el-col :span = "10" :offset="1">
                                <b>地址信息：</b>{{item.address}}
                            </el-col>     
                            <el-col :span = "13">
                                <b>地址描述：</b>{{item.addr_description}}
                            </el-col>
                        </el-row>
                    </div>
   
        
                    <!--第三部分 资质认证 -->
                    <h3 class="TheThirdTitle">四、资质认证</h3>
                    <div v-for="(item, index) in certificationData"  style="margin: 15px 0">
                        <el-row>
                            <el-col :span = "8" :offset="1">
                                <b>资质名称：</b>{{item.cer_name}}
                            </el-col>     
                            <el-col :span = "9">
                                <b>颁发机制：</b>{{item.issuing_authority}}
                            </el-col>
                            <el-col :span = "6">
                                <b>有效时间：</b>{{item.start_time}} - {{item.stop_time}}
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span = "8" :offset="1">
                                <b>是否上传证书：</b>{{item.file_status == 0?'未上传':'已上传'}}
                            </el-col>
                            <el-col :span = "15">
                                <b>证书名称：</b>
                                <span class="detail_doc" title="单击查看文件" @click="awardsLookUp(item)">{{item.file_name}}</span>
                            </el-col>
                        </el-row>
                    </div>
                    <br>      
                    
                     <!--第四部分 获奖情况 -->
                     <h3 class="TheThirdTitle">五、获奖情况</h3>
                     <div v-for="(item, index) in awardsData" style="margin: 15px 0">
                            <el-row>
                                <el-col :span = "8" :offset="1">
                                    <b>获奖名称：</b>{{item.awards_name}}
                                </el-col>     
                                <el-col :span = "9">
                                    <b>颁发机制：</b>{{item.issuing_authority}}
                                </el-col>
                                <el-col :span = "6">
                                    <b>获奖时间：</b>{{item.validity_time}}
                                </el-col>
                            </el-row>
                            <el-row>
                                <el-col :span = "8" :offset="1">
                                    <b>是否上传证书：</b>{{item.file_status == 0?'未上传':'已上传'}}
                                </el-col>
                                <el-col :span = "15">
                                    <b>证书名称：</b>
                                    <span class="detail_doc" title="单击查看文件" @click="previewAwardPdfLookUp(item)">{{item.file_name}}</span>
                                </el-col>
                            </el-row>
                        </div>
                    <br>
        
        
                     <!-- 第五部分 客户情况 -->
                     <h3 class="TheThirdTitle">六、客户情况</h3>
                     <div v-for="(item, index) in customerData"  style="margin: 15px 0">
                        <el-row>
                            <el-col :span = "8" :offset="1">
                                <b>客户名称：</b>{{item.cus_name}}
                            </el-col>     
                            <el-col :span = "9">
                                <b>项目/产品/服务：</b>{{item.main_project}}
                            </el-col>
                            <el-col :span = "6">
                                <b>实施时间：</b>{{item.project_exec_time}}
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span = "8" :offset="1">
                                <b>联系人：</b>{{item.main_contact}}
                            </el-col>
                            <el-col :span = "9">
                                <b>电话：</b>{{item.main_phone}}
                            </el-col>
                            <el-col :span = "6">
                                <b>项目金额：</b>{{item.project_amount}}
                            </el-col>
                        </el-row>
                    </div>
                    <br>


                     <!-- 与银行或金融机构合作情况 -->
                     <h3 class="TheThirdTitle">七、与银行或金融机构合作情况</h3>
                     <div v-for="(item, index) in cooperationData" style="margin: 15px 0">
                        <el-row>
                            <el-col :span = "8" :offset="1">
                                <b>机构名称：</b>{{item.institution_name}}
                            </el-col>     
                            <el-col :span = "9">
                                <b>项目/产品/服务：</b>{{item.main_project}}
                            </el-col>
                            <el-col :span = "6">
                                <b>实施时间：</b>{{item.project_exec_time}}
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span = "8" :offset="1">
                                <b>联系人：</b>{{item.main_contact}}
                            </el-col>
                            <el-col :span = "9">
                                <b>电话：</b>{{item.main_phone}}
                            </el-col>
                            <el-col :span = "6">
                                <b>项目金额：</b>{{item.project_amount}}
                            </el-col>
                        </el-row>
                    </div>
                    <br>

        
                        <!-- 第一部分  公司股权结构-->
                     <!-- <h3 class="theSecondTitle">可填项：</h3> -->
                     <h3 class="TheThirdTitle">八、公司股权结构</h3>
                     <div v-for="(item, index) in equityData"  style="margin: 15px 0">
                        <el-row>
                            <el-col :span = "12">
                                <b>股东名称：</b>{{item.shareholder_name}}
                            </el-col>     
                            <el-col :span = "12">
                                <b>持股比列：</b>{{item.shareholding_ratio}}
                            </el-col>
                        </el-row>
                    </div>
                    <br>

        
                     <!-- 第二部分 供应财务情况 -->
                     <h3 class="TheThirdTitle">九、供应财务情况(万元)</h3>
                     <div v-for="(item, index) in financeData" style="margin: 15px 0">
                        <el-row>
                            <el-col :span = "6">
                                <b>近两年经营业绩：</b>{{item.finance_year}}
                            </el-col>     
                            <el-col :span = "5">
                                <b>资产总额：</b>{{item.total_assets}}
                            </el-col>
                            <el-col :span = "5">
                                <b>主营业务收入：</b>{{item.main_income}}
                            </el-col>
                            <el-col :span = "4">
                                <b>净利润：</b>{{item.net_profit}}
                            </el-col>
                            <el-col :span = "4">
                                <b>利润率：</b>{{item.profit_rat}}
                            </el-col>
                        </el-row>
                    </div>
                     <br>
        
        
                        <!-- 第三部分 供应商团队情况 -->
                        <h3 class="TheThirdTitle">十、供应商团队情况</h3>
                        <div v-for="(item, index) in teamData" style="margin: 15px 0">
                            <el-row>
                                <el-col :span = "5">
                                    <b>类别：</b>{{item.team_cate}}
                                </el-col>     
                                <el-col :span = "8">
                                    <b>人员数量：</b>{{item.team_number}}
                                </el-col>
                                <el-col :span = "11">
                                    <b>备注：</b>{{item.tips}}
                                </el-col>
                            </el-row>
                        </div>
                       <br>

                        <!-- 第三部分 供应商团队情况 -->
                        <h3 class="TheThirdTitle">十一、质控审核信息</h3>
                        <div v-for="(item, index) in audit" style="margin: 15px 0">
                            <el-row>
                                    <!-- <td v-if="item.status === '0'">未审核</td>
                                    <td v-else-if="item.status === '1'">不合格</td>
                                    <td v-else-if="item.status === '2'">合 格</td>
                                    <td v-else-if="item.status === null">未知</td> -->
                                <el-col :span = "6">
                                    <b>审核名称：</b>{{item.type_name}}
                                </el-col>     
                                <el-col :span = "4">
                                    <b>审核人：</b>{{item.name}}
                                </el-col>
                                <el-col :span = "4">
                                    <b>审核时间：</b>{{formatDateTime(item.audit_time)}}
                                </el-col>
                                <el-col :span = "4">
                                    <b>审核状态：</b>{{item.status == 0? '未审核':item.status == 1? '不合格':item.status == 2? '合 格':'未知'}}
                                </el-col>
                                <el-col :span = "6">
                                    <b>证书名称：</b>
                                    <span class="detail_doc" title="单击查看文件" @click="previewSecondAuditPdfLookUp(item)">{{item.file_name}}</span>
                                </el-col>
                                <el-col :span = "6">
                                    <b>备 注：</b>{{item.tips}}
                                </el-col>
                            </el-row>
                        </div>
                       <br>

                    <!-- 第四部分 其他说明信息 -->
                    <h3 class="TheThirdTitle">十二、其他需说明信息</h3>
                    <el-row style="margin-bottom: 50px">
                        <el-col :span="24">
                            <b>其他需要说明：</b>{{form.tips}}
                            <!-- <el-input type="textarea" v-model="form.tips" placeholder="请输入其他需要说明的信息"></el-input> -->
                        </el-col>
                    </el-row>
                   
                </el-form>
            </el-col>
        </el-row>


    </div>

</body>
<script src="/Public/html/js/jquery-1.11.3.min.js "></script>
<script src="/Public/html/js/vue.js "></script>
<script src="/Public/html/js/jquery.form.js "></script>
<script src="/Public/html/js/bootstrap.min.js?v=3.3.6 "></script>
<script src="/Public/html/js/plugins/jeditable/jquery.jeditable.js "></script>
<script src="/Public/html/js/plugins/dataTables/jquery.dataTables.js "></script>
<script src="/Public/html/js/plugins/dataTables/dataTables.bootstrap.js "></script>
<script src="/Public/html/js/content.min.js?v=1.0.0 "></script>
<script src="/Public/html/js/plugins/layer/layer.js "></script>
<script src="https://cdn.bootcss.com/element-ui/2.3.6/index.js "></script>
<script>
    var base = <?php echo (json_encode($base)); ?>;
    var address = <?php echo (json_encode($address)); ?>;
    var contact = <?php echo (json_encode($contact)); ?>;
    var certification = <?php echo (json_encode($certification)); ?>;
    var awards = <?php echo (json_encode($awards)); ?>;
    var customer = <?php echo (json_encode($customer)); ?>;
    var equity = <?php echo (json_encode($equity)); ?>;
    var finance = <?php echo (json_encode($finance)); ?>;
    var team = <?php echo (json_encode($team)); ?>;
    var cooperation = <?php echo (json_encode($cooperation)); ?>;
    var audit = <?php echo (json_encode($audit)); ?>;
    var vm = new Vue({
        el: '#app',
        data: function() {
            return {
                // 校验
                flag:false,
                flag1:false,
                imgURL:'/Public/Admin/images/dwinlogo.png',
                form:{
                    supplier_name:'', //'供应商名称（必须与银行户名一致）',
                    supplier_id:'', //'供应商编号',
                    legal_name:'', //'法人代表',
                    registered_capital:'', //'注册资本',
                    paid_up_capital:'', //'实收资本',
                    business_scope:'', // '营业范围',
                    business_licence:'', //'营业执照号码',
                    start_date:'', //'营业执照生效时间',
                    end_date:'', //'营业执照失效时间',
                    is_listed:'', //'是否上市 0-未上市 1 - 已上市',
                    stock_code:'', //'股票代码	',
                    account_bank:'',//'开户行',
                    account_number:'', //'账号	',
                    websitea_address:'', // '网站地址',
                    enterprise_cate:'', // '企业性质:国有、民营、合资合作、外资、政府机构',
                    tips:''//'其他备注信息',
                },
                options: [{
                    value: '公司负责人'
                }, {
                    value: '区域负责人'
                }, {
                    value: '客户经理'
                }],
                personnelType: [{
                    value: '专业技术人员'
                }, {
                    value: '营销人员'
                }, {
                    value: '其他人员'
                }],
                //公司信息 循环
                contactData: [
                    {
                        contact_position:'',
                        contact:'',
                        telephone:'',
                        phone:'',
                        e_mail:'',
                        fax:''
                    }
                ], 
                // 公司地址 
                addressData: [
                    {
                        address: '',
                        addr_description: ''
                    }
                ],
                // 资质认证
                certificationData: [
                    {
                        cer_name:'',
                        issuing_authority:'',
                        // validityPeriod:'',
                        start_time:'',
                        stop_time:'',
                        file_id:'',
                        certificationData_file:'无',
                        file_status:'0'
                    }
                ],
                // 获奖 文件名显示
                certificationData_filename:[
                    {certificationData_file:''}   
                ],
                upload: 'wait',
                // 奖惩情况
                awardsData:[
                    {
                        awards_name:'',
                        issuing_authority:'',
                        validity_time:'',
                        file_id:'',
                        awardsData_file:'无',
                        file_status:'0'
                    }
                ],
                // 获奖 文件名显示
                awardsData_filename:[
                    {awardsData_file:''}   
                ],
                // 主要客户
                customerData:[
                    {
                        cus_name:'',    //客户名称
                        main_project:'',    //项目/产品
                        main_contact: '',    //联系人
                        main_phone:'',       //电话
                        project_exec_time:'',    //实施时间
                        project_amount:''    //项目金额
                    }
                ],
                // 合作 情况
                cooperationData:[
                    {
                        institution_name:'',     //机构名称
                        main_project:'',      //项目
                        main_contact:'',     //联系人
                        main_phone:'',       //电话
                        project_exec_time:'',     //时间
                        project_amount:''    //金额
                    }
                ],
                // 股权结构
                equityData:[
                    {
                        // shareholder_name:'',
                        // shareholding_ratio:''
                    }
                ],
                // 供应商财务
                financeData:[
                    {
                        finance_year:'',
                        total_assets:'',
                        main_income:'',
                        net_profit:'',
                        profit_rat:''
                    },
                    {
                        finance_year:'',
                        total_assets:'',
                        main_income:'',
                        net_profit:'',
                        profit_rat:''
                    }
                ],
                // 团队
                teamData:[
                    {
                        team_cate:'',
                        team_number:'',
                        tips:''                   
                    }  
                ],
                upLoadData:{
                    type:'1'
                },
                upLoadData_award:{
                    type:'2'
                },
                curveBut:0,
                curveBut_award:0
            }    
        },
        created () {
            base.start_date = this.formatDateTime(base.start_date)
            base.end_date = this.formatDateTime(base.end_date)
            this.form = base
            this.addressData = address
            this.contactData = contact
            for(var i = 0;i < certification.length;i++){
                certification[i].start_time = this.formatDateTime(certification[i].start_time)
                certification[i].stop_time =  this.formatDateTime(certification[i].stop_time)
            }
            this.certificationData = certification
            for(var i = 0;i < awards.length;i++){
                awards[i].validity_time = this.formatDateTime(awards[i].validity_time)
            }
            this.awardsData = awards
            for(var i = 0;i < customer.length;i++){
                customer[i].project_exec_time = this.formatDateTime(customer[i].project_exec_time)
            }
            this.customerData = customer
            this.equityData = equity
            this.financeData = finance
            this.teamData = team
            for(var i = 0;i < cooperation.length;i++){
                cooperation[i].project_exec_time = this.formatDateTime(cooperation[i].project_exec_time)
            }
            this.cooperationData = cooperation
            this.audit = audit
        },
        methods: {
            // 时间戳转化为时间
            formatDateTime:function (timeStamp) { 
                if(timeStamp != null&&timeStamp != 0){
                    var date = new Date();
                    date.setTime(timeStamp * 1000);
                    var y = date.getFullYear();    
                    var m = date.getMonth() + 1;    
                    m = m < 10 ? ('0' + m) : m;    
                    var d = date.getDate();    
                    d = d < 10 ? ('0' + d) : d;    
                    var h = date.getHours();  
                    h = h < 10 ? ('0' + h) : h;  
                    var minute = date.getMinutes();  
                    var second = date.getSeconds();  
                    minute = minute < 10 ? ('0' + minute) : minute;    
                    second = second < 10 ? ('0' + second) : second;   
                    // return y + '-' + m + '-' + d+' '+h+':'+minute+':'+second;  
                    return y + '-' + m + '-' + d;  
                }else{
                    return ''
                }
            },
            // 输入校验   ==========GO=========》
            // 资金
            onlyNum(event){
                var value = event.target.value;
                if (!/^\+?[1-9][0-9]*$/.test(value)) {
                    this.$message({
                        showClose: true,
                        message: '资本只能输入单位为万的整数！',
                        type: 'warning'
                    });
                    this.flag = true
                    event.target.value.splice(value.length-1,1);
                }else{
                   this.flag = false 
                }
            },
            onlyNum1(event){
                var value1 = event.target.value;
                if (!/^\+?[1-9][0-9]*$/.test(value1)) {
                    this.$message({
                        showClose: true,
                        message: '资金只能输入单位为万的整数！',
                        type: 'warning'
                    });
                    this.flag1 = true
                    if(event.target.value){
                        event.target.value.splice(value1.length-1,1);
                    }
                }else{
                   this.flag1 = false 
                }
            },
            // 手机验证
            upperCase (event) {
                if(!(/^1[34578]\d{9}$/.test(event))){
                    this.$message({
                        showClose: true,
                        message: '手机号码有误，请检查手机号是否正确！',
                        type: 'warning'
                    }); 
                    return false; 
                } 
            },
            // 传真验证
            upperFax (event) {
                if(!(/^[+]{0,1}(\d){1,3}[ ]?([-]?((\d)|[ ]){1,12})+$/.test(event))){
                    this.$message({
                        showClose: true,
                        message: '输入传真有误，请检查传真输入是否正确！！',
                        type: 'warning'
                    });
                    return false; 
                } 
            },
            // 电子邮件
            check (event){
                var regex = /^([0-9A-Za-z\-_\.]+)@([0-9a-z]+\.[a-z]{2,3}(\.[a-z]{2})?)$/g;
                if ( regex.test( event ) )
                {
                    var user_name = event.replace( regex, "$1" );
                    var domain_name = event.replace( regex, "$2" );
                    var alert_string = "您输入的电子邮件地址合法\n\n";
                    alert_string += "用户名：" + user_name + "\n";
                    alert_string += "域名：" + domain_name;
                    this.$message({
                        showClose: true,
                        message: alert_string,
                        type: 'success'
                    });
                    return true;
                }
                else
                {
                    this.$message({
                        showClose: true,
                        message: '您输入的电子邮件地址不合法！',
                        type: 'warning'
                    });
                }
            },
            onlyNum_telephome(event){
                var value = event.target.value;
                if (!/^\+?[1-9][0-9]*$/.test(value)) {
                    this.$message({
                        showClose: true,
                        message: '电话号只能输入数字',
                        type: 'warning'
                    });
                    event.target.value.splice(value.length-1,1);
                }
            },
            // 输入校验   ===========END========》
            // 供应商联系信息 追加一行
            superaddition() {
                var newObj = {
                    contact_position:'',
                    contact:'',
                    telephone:'',
                    phone:'',
                    e_mail:'',
                    fax:''
                }
                this.contactData.push(newObj)
            },
            // 供应商联系信息 删除一行
            removeAdd() {
                if(this.contactData.length !== 1){
                    this.contactData.splice(this.contactData.length-1,1)
                }
            },
            // 资质认证 追加一行
            certificateAdd() {
                // var obj = {}
                // // vm.newArrCertificate.push(obj)
                var newArrCertificate = {
                    cer_name:'',
                    issuing_authority:'',
                    // validityPeriod:'',
                    start_time:'',
                    stop_time:'',
                    cerUrl:'',
                    file_id:'',
                    certificationData_file:'无',
                    file_status:'0'
                }
                this.certificationData.push(newArrCertificate)
            },
            // 资质认证 删除
            removeCertificateAdd () {
                if(this.certificationData.length !== 1){
                    this.certificationData.splice(this.certificationData.length-1,1)
                }
            },
            // 公司地址 新增
            addAddr (){
                var newaddAddr = {
                    address: '',
                    addr_description: ''
                }
                this.addressData.push(newaddAddr)
            },
            // 公司地址 删除
            removeAddr () {
                if(this.addressData.length !== 1){
                    this.addressData.splice(this.addressData.length-1,1)
                }
            },
            // 奖惩情况 新增
            awardsDataAdd () {
                let newAwardsData = {
                    awards_name:'',
                    issuing_authority:'',
                    validity_time:'',
                    file_id:'',
                    awardsData_file:'无',
                    file_status:'0'
                }
                // let flidName = {awardsData_file:''}
                this.awardsData.push(newAwardsData)
                // this.awardsData_filename.push(flidName)
            },
            // 奖惩情况 删除
            awardsDataRemove () {
                if (this.awardsData.length === 1) {

                }else{
                    this.awardsData.splice(this.awardsData.length-1,1)
                }
            },
            // 客户情况 新增
            customerDataAdd () {
                let newCustomerData = {
                    cus_name:'',    //客户名称
                    main_project:'',    //项目/产品
                    main_contact: '',    //联系人
                    main_phone:'',       //电话
                    project_exec_time:'',    //实施时间
                    project_amount:''    //项目金额
                }
                this.customerData.push(newCustomerData)
            },
            // 客户情况 删除
            customerDataRemove () {
                if (this.customerData.length !== 1) {
                    this.customerData.splice(this.customerData.length-1,1)
                }
            },
            // 银行合作 新增
            cooperationDataAdd () {
                let newCooperationData = {
                    institution_name:'',     //机构名称
                    main_project:'',      //项目
                    main_contact:'',     //联系人
                    main_phone:'',       //电话
                    project_exec_time:'',     //时间
                    project_amount:''    //金额
                }
                this.cooperationData.push(newCooperationData)
            },
            // 银行合作  删除
            cooperationDataRemove () {
                if(this.cooperationData.length !== 1){
                    this.cooperationData.splice(this.cooperationData.length-1,1)
                }
            },
            // 股权构成 新增
            equityDataAdd () {
                let newEquityData =
                    {
                        shareholder_name:'',
                        shareholding_ratio:''
                    }
                this.equityData.push(newEquityData)
            },
            // 股权结构 删除
            equityDataRemove () {
                if(this.equityData.length !== 1){
                    this.equityData.splice(this.equityData.length-1,1)
                }
            },
            // 供应商团队人员情况 新增
            teamCateAdd  () {
                var newTeamData = 
                    {
                        team_cate:'',
                        team_number:'',
                        tips:''
                                           
                    }
                this.teamData.push(newTeamData)  
            },
             // 供应商团队人员情况 删除
             teamCateRemove () {
                if(this.teamData.length !==1 ){
                    this.teamData.splice(this.teamData.length-1,1)
                }
             },
            //  获取公司编号
              getNumber () {
                $.ajax({
                    url:'/Dwin/Purchase/createSupplierId',
                    type:'get',
                    dataType:'json',
                    success:function (res) {
                        if(res.status === 200){
                            vm.form.supplier_id = res.data.supplierIdString;
                        }
                    }
                })
             },
            //  点击上传 资质文件
             clickUpdata(index){
                 vm.curveBut = index
             },
            //  上传文件  成功回调  => 资质认证
            papersUploadSuccess (res,file) {
                if(res.status == 200){
                    vm.certificationData[vm.curveBut].file_status = '1'
                    vm.certificationData[vm.curveBut].file_id = res.data.id
                    vm.certificationData[vm.curveBut].certificationData_file = file.name

                }else{
                    vm.certificationData[vm.curveBut].file_status = '0'
                }
                layer.msg(res.msg)
            },
            //  上传文件 失败 回调 => 资质认证
            uploadError (res) {
            },

            //  点击上传   => 获奖情况
            clickUpdata_award(index){
                 vm.curveBut_award = index
             },
             //  上传文件  成功回调  => 获奖情况
            papersUploadSuccess_award (res,file) {
                if(res.status == 200){
                    vm.awardsData[vm.curveBut_award].file_status = '1' 
                    vm.awardsData[vm.curveBut_award].file_id = res.data.id
                    vm.awardsData[vm.curveBut_award].awardsData_file = file.name
                }else{
                    vm.awardsData[vm.curveBut_award].file_status = '0'
                }
                layer.msg(res.msg)
            },
            //  上传文件 失败 回调 => 获奖情况
            uploadError_award (res) {
                
            },
            // 资质文件 预览LOOK
            awardsLookUp(item){
                if(item.file_url == null||item.file_url == ''){
                    layer.msg('没有找到文件！')
                }else{
                    if(item.file_type == 'pdf'){
                        window.open('<?php echo U("previewCerPdf", [], "");?>/id/' + item.id)
                    }else{
                        window.open(item.file_url)
                    }
                }
            },
            // 奖励证书 预览LOOK
            previewAwardPdfLookUp(item){
                if(item.file_url == null||item.file_url == ''){
                    layer.msg('没有找到文件！')
                }else{
                    if(item.file_type == 'pdf'){
                        window.open('<?php echo U("previewAwardPdf", [], "");?>/id/' + item.id)
                    }else{
                        window.open(item.file_url)
                    }
                }
            },
            // 质控审核 预览LOOK
            previewSecondAuditPdfLookUp(item){
                console.log(item)
                if(item.file_url == null||item.file_url == ''){
                    layer.msg('没有找到文件！')
                }else{
                    if(item.file_type == 'pdf'){
                        window.open('<?php echo U("previewSecondAuditPdf", [], "");?>/id/' + item.id)
                    }else{
                        window.open(item.file_url)
                    }
                }
            }
        }
    })
</script>

</html>