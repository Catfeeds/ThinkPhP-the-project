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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/theme-chalk/index.css" rel="stylesheet">
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
            background-color: #C0C0C0
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
        /* 居中 */
        .contBorder{
            border: 1px solid #C0C4CC;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            font-size: 13px;
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
       .el-tabs__item.is-disabled{
           color: red !important;
           cursor:not-allowed;
       }
       .is-top{
            color: green !important;
       }
       .is-active{
            color: blue !important;
       }
       /* layer.tiele delete */
        .layui-layer .layui-layer-title{
           display: none !important;
       }
       .red_bor{
           border-color: red;
       }
       /* progress bar show is fixed*/
       /* .progress_bar{
            width: 100%;
            position: fixed;
            top: 4%;
            height: 23%;
            padding-top: 1%;
            line-height: 18px;
            background-color: #6966a7;
            text-align: center;
            opacity: 0.1;
       } */
    </style>
</head>

<body>
    <div id="app" v-loading="loading">
        <h1 class="headline">供应商信息登记表</h1>
        <!-- 第一部分  供应商基本信息 -->
        <el-row :span="24">
            <el-col :span="22" :offset="1">
                <el-form ref="form" :model="form" label-width="150px" enctype="multipart/form-data">
                    <el-tabs  @tab-click="handleClick" v-model="activeName" id="tabs_list">
                        <el-tab-pane label="基本信息" name="first"
                        :disabled="getDsabled!=='first'&&getDsabled!=='second'&&getDsabled!=='third'&&getDsabled!=='fourth'&&getDsabled!=='fifth'&&getDsabled!=='sixth'&&getDsabled!=='seventh'&&getDsabled!=='eighth'&&getDsabled!=='ninth'&&getDsabled!=='tenth'&&getDsabled!=='eleventh'"
                        >
                                <h3 class="theSecondTitle">必填项：</h3>
                                <h3 class="TheThirdTitle">一、供应商基本信息</h3>
                                <br>
                            <!-- 公司名称  、 法人代表 -->
                            <el-row >   
                                <el-col :span="13">
                                    <el-form-item label="供应商名称：" required>
                                        <el-input v-model="form.supplier_name" id="supplier_nameID" @blur="examine_Duplication(form.supplier_name)"  placeholder="供应商名称(须与银行户名一致)"></el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="11">
                                    <el-form-item label="公司编号：" required>
                                            <el-input v-model="form.supplier_id" style="width: 60%;" readonly placeholder="点击获取公司编号"></el-input>
                                            <el-button type="primary" @click="getNumber()">获取编号</el-button>
                                        </el-form-item>
                                </el-col>
                            </el-row>
                            <br>
                            <el-row> 
                                <el-col :span="8">
                                    <el-form-item label="是否上市：" required>
                                        <el-radio-group v-model="form.is_listed">
                                            <el-radio label="0">未上市</el-radio>
                                            <el-radio label="1">已上市</el-radio>
                                        </el-radio-group>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="16">
                                    <el-form-item label="企业性质：" required>
                                        <el-radio-group v-model="form.enterprise_cate">
                                            <el-radio label="国企"></el-radio>
                                            <el-radio label="民营"></el-radio>
                                            <el-radio label="合资合作"></el-radio>
                                            <el-radio label="外商独资"></el-radio>
                                            <el-radio label="政府机构"></el-radio>
                                        </el-radio-group>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                            <br>
                            <!-- 注册资本、实收资本 -->
                            <el-row>
                                <el-col :span="12">
                                        <el-form-item label="注册资本(万)：" required>
                                            <el-input v-model.number="form.registered_capital" @change="notNegative(form.registered_capital)"  onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46"  placeholder="请输入内容" type="number"></el-input>
                                        </el-form-item>
                                </el-col>
                                <el-col :span="12">
                                    <el-form-item label="实收资本(万)：" required>
                                        <el-input v-model.number="form.paid_up_capital" @keyup.native = "onlyNum1($event)" :class="[this.flag1?'errer_input':'']" placeholder="请输入内容" type="number"></el-input>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                            <br>
                            <!--营业执照号码、营业执照起止期限 -->
                            <el-row>
                                <el-col :span="12">
                                        <el-form-item label="营业执照号码：" required>
                                            <el-input v-model="form.business_licence"  placeholder="请输入内容"></el-input>
                                        </el-form-item>
                                </el-col>
                                <el-col :span="12">
                                    <el-form-item label="法人代表：" required>
                                        <el-input v-model="form.legal_name"  placeholder="请输入内容"></el-input>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                            <br>
                            <el-row>
                                <el-col :span="12">
                                        <el-form-item label="营业执照生效时间：" required>
                                            <!-- <span class="demonstration">营业执照生效时间</span> -->
                                            <el-date-picker
                                            style="width: 100%;"
                                            size="large"
                                            v-model="form.start_date"
                                            value-format="timestamp"
                                            format="yyyy-MM-dd"
                                            type="date"
                                            placeholder="生效日期"
                                            :picker-options="pickerOptions0"
                                            >
                                        </el-date-picker>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="12">
                                        <el-form-item label="营业执照失效时间：" required>
                                            <el-date-picker
                                            style="width: 100%;"
                                            v-model="form.end_date"
                                            value-format="timestamp"
                                            format="yyyy-MM-dd"
                                            type="date"
                                            placeholder="失效日期"
                                            :picker-options="pickerOptions1"
                                            >
                                        </el-date-picker>
                                        </el-form-item>
                                </el-col>
                            </el-row>
                            <br>
                            <!-- 是否上市、股票代码 -->
                            <el-row>
                                <el-col :span="12 ">
                                    <!-- <div> -->
                                        <el-form-item label="公司网址：" required>
                                            <el-input v-model="form.websitea_address" :class="[this.www?'errer_input':'']" @blur = "isurl($event)" @keyup.native="wwwviefy()"  placeholder="请输入内容"></el-input>
                                        </el-form-item>  
                                </el-col>
                                <el-col :span="12 ">
                                        <el-form-item label="股票代码：" required>
                                            <el-input v-model="form.stock_code"  placeholder="请输入内容"></el-input>
                                        </el-form-item>
        
                                </el-col>
                            </el-row>
                            <br>
                            <!-- 开户行、账号 -->
                            <el-row>
                                <el-col :span="12 ">
                                    <el-form-item label="开户行：" required>
                                        <el-input v-model="form.account_bank"  placeholder="请输入内容"></el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="12 ">
                                    <el-form-item label="银行账号：" required>
                                        <el-input v-model="form.account_number" @blur="onlyNum_telephome($event)"  placeholder="请输入内容"></el-input>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                            <br>
                            <!-- 主要业务范围（营业执照） -->
                            <el-row>
                                <el-col :span="24">
                                        <el-form-item label="主要业务范围： " required>
                                            <el-input v-model="form.business_scope"  placeholder="请输入内容" type="textarea"></el-input>
                                        </el-form-item>
                                    
                                </el-col>
                            </el-row>
                        </el-tab-pane>
                        <el-tab-pane label="联系信息" name="second" :disabled="getDsabled!=='second'&&getDsabled!=='third'&&getDsabled!=='fourth'&&getDsabled!=='fifth'&&getDsabled!=='sixth'&&getDsabled!=='seventh'&&getDsabled!=='eighth'&&getDsabled!=='ninth'&&getDsabled!=='tenth'&&getDsabled!=='eleventh'"
                        >
                            <!--第二部分 供应商联系信息 -->
                            <h3 class="TheThirdTitle">二、供应商联系信息</h3>
                            <el-table ref="multipleTable" :data="contactData" tooltip-effect="dark" style="width: 100%" border>
                                <el-table-column label="联系人" align="center" header-align="center">
                                    <template slot-scope="scope">
                                            <el-select v-model="scope.row.contact_position"  placeholder="请选择" class="ComboBox_sty">
                                                <el-option v-for="item in options" :key="item.value" :label="item.value" :value="item.value">
                                                </el-option> 
                                            </el-select>
                                    </template>
                                </el-table-column>
                                <el-table-column label="姓名" align="center" header-align="center">
                                    <template  slot-scope="scope">
                                        <el-input  v-model="scope.row.contact"  placeholder="请输入"></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column label="电话" align="center" header-align="center">
                                    <template  slot-scope="scope">
                                        <el-input  v-model="scope.row.telephone"  onkeypress="return" @change="upperCase($event)"  placeholder="请输入"></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column label="手机" align="center" header-align="center" >
                                    <template  slot-scope="scope">
                                        <el-input  v-model="scope.row.phone" @change="upperCase($event)"  placeholder="请输入"></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column label="传真" align="center" header-align="center" >
                                    <template  slot-scope="scope">
                                        <el-input  v-model="scope.row.fax"  @change="upperCase($event)"    placeholder="请输入"></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column label="电子邮件" align="center" header-align="center" >
                                    <template  slot-scope="scope">
                                        <el-input  v-model="scope.row.e_mail" id="email_address" @change="check($event)" placeholder="请输入"></el-input>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <el-row>
                                <el-col :span="2">
                                    <el-button type="primary" @click="superaddition()">追加</el-button>
                                </el-col>
                                <el-col :span="3">
                                    <el-button type="danger" class="remove_btu" @click="removeAdd()">删 除</el-button>
                                </el-col>
                            </el-row>
                            <br>
                        </el-tab-pane>
                        <el-tab-pane label="地址信息" name="third" 
                        :disabled="getDsabled!=='third'&&getDsabled!=='fourth'&&getDsabled!=='fifth'&&getDsabled!=='sixth'&&getDsabled!=='seventh'&&getDsabled!=='eighth'&&getDsabled!=='ninth'&&getDsabled!=='tenth'&&getDsabled!=='eleventh'"
                        >
                            <!--供应商联系信息 公司地址 -->
                            <h3 class="TheThirdTitle">三、地址信息(公司地址和描述)</h3>
                            <el-container>
                                    <!-- <el-aside width="16.5%" class="contBorder el-aside_center">
                                        <div class="son">
                                            公司地址和描述
                                        </div>
                                    </el-aside> -->
                                    <el-main>
                                        <el-table ref="multipleTable" :data="addressData" tooltip-effect="dark" style="width: 100%" border>
                                            <el-table-column label="地址信息" align="center" header-align="center">
                                                <template  slot-scope="scope">
                                                    <el-input  v-model="scope.row.address"  placeholder="请输入"></el-input>
                                                </template>
                                            </el-table-column>
                                            <el-table-column label="地址描述" align="center" header-align="center" >
                                                <template  slot-scope="scope">
                                                    <el-input  v-model="scope.row.addr_description"  placeholder="请输入"></el-input>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                    </el-main>
                                </el-container>
                                <el-row >
                                    <el-col :span="2">
                                        <el-button type="primary" @click="addAddr()">追 加</el-button>
                                    </el-col>
                                    <el-col :span="3">
                                        <el-button type="danger" @click="removeAddr()">删 除</el-button>
                                    </el-col>
                                </el-row>
                                <br>
                        </el-tab-pane>
                        <el-tab-pane label="资质认证" name="fourth" :disabled="getDsabled!=='fourth'&&getDsabled!=='fifth'&&getDsabled!=='sixth'&&getDsabled!=='seventh'&&getDsabled!=='eighth'&&getDsabled!=='ninth'&&getDsabled!=='tenth'&&getDsabled!=='eleventh'"
                        >
                            <!--第三部分 资质认证 -->
                            <h3 class="TheThirdTitle">四、资质认证（与公司业务开展相关的资质，如行业资质或法规要求的强制性认证 资质，非营业执照等工商证照）</h3>
                            <el-container>
                                <!-- <el-aside width="16.5%" class="contBorder el-aside_center">
                                    <div>
                                        资质文件（与公司业务开展相关的资质，如行业资质或法规要求的强制性认证 资质，非营业执照等工商证照）
                                    </div>
                                </el-aside> -->
                                <el-main>
                                    <el-table ref="multipleTable" :data="certificationData" tooltip-effect="dark" style="width: 100%" border>
                                        <el-table-column label="ID" align="center" header-align="center" v-if="false">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.file_id"  placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="资质名称" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.cer_name"  placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="颁发机构" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.issuing_authority"  placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="生效时间" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <!-- <el-input  v-model="scope.row.start_time"  placeholder="请输入"></el-input> -->
                                                <el-date-picker
                                                    class="time_witch"
                                                    v-model="scope.row.start_time"
                                                    value-format="timestamp"
                                                    type="date"
                                                    placeholder="有效起始时间"
                                                    :picker-options="pickerOptions_certifGo"
                                                    >
                                                </el-date-picker>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="失效时间" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <!-- <el-input  v-model="scope.row.stop_time"  placeholder="请输入"></el-input> -->
                                                <el-date-picker
                                                    class="time_witch"
                                                    v-model="scope.row.stop_time"
                                                    value-format="timestamp"
                                                    type="date"
                                                    placeholder="有效起始时间"
                                                    :picker-options="pickerOptions_certifEnd"
                                                    >
                                                </el-date-picker>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="上传证书" align="center" header-align="center" >
                                            <template  slot-scope="scope">
                                                    <el-upload
                                                        class="uploadResume"
                                                        action="<?php echo U('/dwin/purchase/upload');?>"
                                                        :data="upLoadData"
                                                        :on-success="papersUploadSuccess"
                                                        :on-error="uploadError"
                                                        :auto-upload="true"
                                                        >
                                                        <el-button size="small" type="primary" @click="clickUpdata(scope.$index)">上传证书</el-button>
                                                    </el-upload>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="上传证书状态" align="center" header-align="center" >
                                            <template  slot-scope="scope">
                                                <!-- <el-col :span="8" v-model="scope.row.file_status" class="addButStyle"> -->
                                                    <div class="el-icon-success" v-if="scope.row.file_status === '1'" style="color:green">已上传</div>
                                                    <div class="el-icon-upload" v-if="scope.row.file_status === '0'" style="color:red">未上传</div> 
                                                <!-- </el-col> -->
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                </el-main>
                            </el-container>
                            <el-row>
                                <el-col :span="2">
                                    <el-button type="primary" @click="certificateAdd()">追加</el-button>
                                </el-col>
                                <el-col :span="3">
                                    <el-button type="danger" class="remove_btu" @click="removeCertificateAdd()">删 除</el-button>
                                </el-col>
                            </el-row>
                        </el-tab-pane>
                        <el-tab-pane label="获奖情况" name="fifth" :disabled="getDsabled!=='fifth'&&getDsabled!=='sixth'&&getDsabled!=='seventh'&&getDsabled!=='eighth'&&getDsabled!=='ninth'&&getDsabled!=='tenth'&&getDsabled!=='eleventh'"
                        >
                            <!--第四部分 获奖情况 -->
                            <h3 class="TheThirdTitle">五、获奖情况(公司获奖情况)</h3>
                            <el-container>
                                <!-- <el-aside width="16.5%" class="contBorder el-aside_center">
                                        公司获奖情况
                                </el-aside> -->
                                <el-main>
                                    <el-table ref="multipleTable" :data="awardsData" tooltip-effect="dark" style="width: 100%" border>
                                        <el-table-column label="ID" v-if="false" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.file_id"  placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="获奖名称" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.awards_name"  placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="颁发机构" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.issuing_authority"  placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="获奖时间" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <!-- <el-input  v-model="scope.row.address"  placeholder="请输入"></el-input> -->
                                                <el-date-picker
                                                    class="time_witch"
                                                    v-model="scope.row.validity_time"
                                                    value-format="timestamp"
                                                    type="date"
                                                    placeholder="获奖时间">
                                                </el-date-picker>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="上传证书" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <!-- <el-input  v-model="scope.row.address"  placeholder="请输入"></el-input> -->
                                                <el-upload
                                                    class="uploadResume"
                                                    action="<?php echo U('/dwin/purchase/upload');?>"
                                                    :data="upLoadData_award"
                                                    :on-success="papersUploadSuccess_award"
                                                    :on-error="uploadError_award"
                                                    :auto-upload="true"
                                                    >
                                                    <el-button size="small" type="primary" @click="clickUpdata_award(scope.$index)">上传证书</el-button>
                                                </el-upload>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="上传证书状态" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <div class="el-icon-success" v-if="scope.row.file_status === '1'" style="color:green">已上传</div>
                                                <div class="el-icon-upload" v-if="scope.row.file_status === '0'" style="color:red">未上传</div> 
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                </el-main>
                            </el-container>
                            <el-row>
                                <el-col :span="2">
                                    <el-button type="primary" @click="awardsDataAdd()">追加</el-button>
                                </el-col>
                                <el-col :span="3">
                                    <el-button type="danger" class="remove_btu" @click="awardsDataRemove()">删 除</el-button>
                                </el-col>
                            </el-row>
                            <br>
                        </el-tab-pane>
                        <el-tab-pane label="客户情况" name="sixth" 
                        :disabled="getDsabled!=='sixth'&&getDsabled!=='seventh'&&getDsabled!=='eighth'&&getDsabled!=='ninth'&&getDsabled!=='tenth'&&getDsabled!=='eleventh'"
                        >
                            <!-- 第五部分 客户情况 -->
                            <h3 class="TheThirdTitle">六、客户情况(主要客户介绍)</h3>
                            <el-container>
                                <!-- <el-aside width="16.5%" class="contBorder el-aside_center">
                                        主要客户介绍
                                </el-aside> -->
                                <el-main>
                                    <el-table ref="multipleTable" :data="customerData" tooltip-effect="dark" style="width: 100%" border>      
                                        <el-table-column label="客户名称" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.cus_name"  placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="项目或主要产品/服务" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.main_project"  placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="实施时间" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <!-- <el-input  v-model="scope.row.address"  placeholder="请输入"></el-input> -->
                                                <el-date-picker
                                                    class="time_witch"
                                                    v-model="scope.row.project_exec_time"
                                                    value-format="timestamp"
                                                    type="date"
                                                    placeholder="获奖时间">
                                                </el-date-picker>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="联系人" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.main_contact"  placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="电话" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.main_phone" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46"   placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="项目金额(万)" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.project_amount" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46"   placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                </el-main>
                            </el-container>
                            <el-row>
                                <el-col :span="2">
                                    <!-- 追 加 -->
                                    <el-button type="primary" @click="customerDataAdd()">追加</el-button>
                                </el-col>
                                <el-col :span="3">
                                    <!-- 删除 -->
                                    <el-button type="danger" class="remove_btu" @click="customerDataRemove()">删 除</el-button>
                                </el-col>
                            </el-row>
                            <br>
                        </el-tab-pane>
                        <el-tab-pane label="合作情况" name="seventh" 
                        :disabled="getDsabled!=='seventh'&&getDsabled!=='eighth'&&getDsabled!=='ninth'&&getDsabled!=='tenth'&&getDsabled!=='eleventh'"
                        >
                            <!-- 与银行或金融机构合作情况 -->
                            <h3 class="TheThirdTitle">七、与银行或金融机构合作情况</h3>
                            <el-container>
                                    <!-- <el-aside width="16.5%" class="contBorder el-aside_center">
                                            与银行或金融机构合作情况
                                    </el-aside> -->
                                    <el-main>
                                        <el-table ref="multipleTable" :data="cooperationData" tooltip-effect="dark" style="width: 100%" border>      
                                            <el-table-column label=" 机构名称(总分支级别)" align="center" header-align="center">
                                                <template  slot-scope="scope">
                                                    <el-input  v-model="scope.row.institution_name"  placeholder="请输入"></el-input>
                                                </template>
                                            </el-table-column>
                                            <el-table-column label="项目或主要产品/服务" align="center" header-align="center">
                                                <template  slot-scope="scope">
                                                    <el-input  v-model="scope.row.main_project"  placeholder="请输入"></el-input>
                                                </template>
                                            </el-table-column>
                                            <el-table-column label="实施时间" align="center" header-align="center">
                                                <template  slot-scope="scope">
                                                    <!-- <el-input  v-model="scope.row.address"  placeholder="请输入"></el-input> -->
                                                    <el-date-picker
                                                        class="time_witch"
                                                        v-model="scope.row.project_exec_time"
                                                        value-format="timestamp"
                                                        type="date"
                                                        placeholder="获奖时间">
                                                    </el-date-picker>
                                                </template>
                                            </el-table-column>
                                            <el-table-column label="联系人" align="center" header-align="center">
                                                <template  slot-scope="scope">
                                                    <el-input  v-model="scope.row.main_contact"  placeholder="请输入"></el-input>
                                                </template>
                                            </el-table-column>
                                            <el-table-column label="电话" align="center" header-align="center">
                                                <template  slot-scope="scope">
                                                    <el-input  v-model="scope.row.main_phone" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46"   placeholder="请输入"></el-input>
                                                </template>
                                            </el-table-column>
                                            <el-table-column label="项目金额" align="center" header-align="center">
                                                <template  slot-scope="scope">
                                                    <el-input  v-model="scope.row.project_amount"  @keyup.native="onlyNum_telephome($event)"  placeholder="请输入"></el-input>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                    </el-main>
                                </el-container>
                                <el-row>
                                    <el-col :span="2">
                                        <!-- 追 加 -->
                                        <el-button type="primary" @click="cooperationDataAdd()">追加</el-button>
                                    </el-col>
                                    <el-col :span="3" >
                                        <!-- 删除 -->
                                        <el-button type="danger" class="remove_btu" @click="cooperationDataRemove()">删 除</el-button>
                                    </el-col>
                                </el-row>
                                <br>
                        </el-tab-pane>
                        <el-tab-pane label="股权结构" name="eighth" 
                        :disabled="getDsabled!=='eighth'&&getDsabled!=='ninth'&&getDsabled!=='tenth'&&getDsabled!=='eleventh'"
                        >
                                <!-- 第一部分  公司股权结构-->
                            <h3 class="theSecondTitle">可填项：</h3>
                            <h3 class="TheThirdTitle">一、公司股权结构(（股份在5%以上的股东）)</h3>
                            <el-container>
                                <!-- <el-aside width="16.5%" class="contBorder el-aside_center">
                                    
                                                公司股东结构
                                        
                                            （股份在5%以上的股东）
                                </el-aside> -->
                                <el-main>
                                    <el-table ref="multipleTable" :data="equityData" tooltip-effect="dark" style="width: 100%" border>      
                                        <el-table-column label=" 股东名称" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.shareholder_name"  placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="持股比列" align="center" header-align="center">
                                            <template  slot-scope="scope">
                                                <el-input  v-model="scope.row.shareholding_ratio" @keyup.native="shareholding_repity($event)"  placeholder="请输入"></el-input>
                                            </template>
                                        </el-table-column>  
                                    </el-table>
                                </el-main>
                            </el-container>
                            <el-row>
                                <el-col :span="2">
                                    <!-- 追 加 -->
                                    <el-button type="primary" @click="equityDataAdd()">追加</el-button>
                                </el-col>
                                <el-col :span="3">
                                    <!-- 删除 -->
                                    <el-button type="danger" class="remove_btu" @click="equityDataRemove()">删 除</el-button>
                                </el-col>
                            </el-row>
                            <br>
                        </el-tab-pane>
                        <el-tab-pane label="财务情况" name="ninth" 
                        :disabled="getDsabled!=='ninth'&&getDsabled!=='tenth'&&getDsabled!=='eleventh'"
                        >
                             <!-- 第二部分 供应财务情况 -->
                            <h3 class="TheThirdTitle">二、供应财务情况(万元)</h3>
                            <el-table ref="multipleTable" :data="financeData" tooltip-effect="dark" style="width: 100%" border>      
                                <el-table-column label=" 近两年经营业绩" align="center" header-align="center">
                                    <template  slot-scope="scope">
                                        <el-input  v-model="scope.row.finance_year"  placeholder="请输入"></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column label="资产总额" align="center" header-align="center">
                                    <template  slot-scope="scope">
                                        <el-input  v-model="scope.row.total_assets" @keyup.native="onlyNum_telephome($event)"  placeholder="请输入"></el-input>
                                    </template>
                                </el-table-column>  
                                <el-table-column label="主营业务收入" align="center" header-align="center">
                                    <template  slot-scope="scope">
                                        <el-input  v-model="scope.row.main_income"  placeholder="请输入"></el-input>
                                    </template>
                                </el-table-column>  
                                <el-table-column label="净利润" align="center" header-align="center">
                                    <template  slot-scope="scope">
                                        <el-input  v-model="scope.row.net_profit"  placeholder="请输入"></el-input>
                                    </template>
                                </el-table-column>  
                                <el-table-column label="利润率" align="center" header-align="center">
                                    <template  slot-scope="scope">
                                        <el-input  v-model="scope.row.profit_rat"  placeholder="请输入"></el-input>
                                    </template>
                                </el-table-column>  
                            </el-table>
                            <br>
                        </el-tab-pane>
                        <el-tab-pane label="团队情况" name="tenth" 
                        :disabled="getDsabled!=='tenth'&&getDsabled!=='eleventh'"
                        >
                            <!-- 第三部分 供应商团队情况 -->
                            <h3 class="TheThirdTitle">三、供应商团队情况</h3>
                            <el-table ref="multipleTable" :data="teamData" tooltip-effect="dark" style="width: 100%" border>      
                                <el-table-column label=" 类别" align="center" header-align="center">
                                    <template  slot-scope="scope">
                                        <el-select v-model="scope.row.team_cate"  placeholder="请选择" class="ComboBox_sty">
                                            <el-option v-for="item in personnelType" :key="item.value" :label="item.value" :value="item.value">
                                            </el-option> 
                                        </el-select>
                                    </template>
                                </el-table-column>
                                <el-table-column label="人员数量" align="center" header-align="center">
                                    <template  slot-scope="scope">
                                        <el-input  v-model="scope.row.team_number" onkeypress="return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode==46"   placeholder="请输入"></el-input>
                                    </template>
                                </el-table-column>  
                                <el-table-column label="备注" align="center" header-align="center">
                                    <template  slot-scope="scope">
                                        <el-input  v-model="scope.row.tips"  placeholder="请输入"></el-input>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <el-row>
                                    <el-col :span="2">
                                        <!-- 追 加 -->
                                        <el-button type="primary" @click="teamCateAdd()">新增</el-button>
                                    </el-col>
                                    <el-col :span="3">
                                        <!-- 删除 -->
                                        <el-button type="danger" class="remove_btu" @click="teamCateRemove()">删 除</el-button>
                                    </el-col>
                            </el-row>
                        <br>
                        </el-tab-pane>
                        <el-tab-pane label="其他说明" name="eleventh" 
                        :disabled="getDsabled!=='eleventh'">
                            <!-- 第四部分 其他说明信息 -->
                            <h3 class="TheThirdTitle">四、其他需说明信息</h3>
                            <el-row>
                                <el-col :span="24">
                                    <el-input type="textarea" v-model="form.tips" placeholder="请输入其他需要说明的信息"></el-input>
                                </el-col>
                            </el-row>
                            <!-- 提交 -->
                            <el-row type="flex" class="row-bg" justify="end">
                                    <el-col :span="13">
                                        <el-button type="primary " style="margin:5% 0" @click="onblle(form) ">提 交</el-button>
                                    </el-col>
                            </el-row>
                        </el-tab-pane>
                    </el-tabs>
                    <br>
                    <div style="width:100%;text-align: center">
                        <el-button type="success" @click="nextStep('top')" :disabled="topAnddD == 'first'">上一步</el-button>
                        <el-button type="success" @click="nextStep('down')" :disabled="topAnddD == 'eleventh'">下一步</el-button>
                    </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.3.6/index.js "></script>
<script>
    var vm = new Vue({
        el: '#app',
        data: function() {
            return {
                loading:true,
                // 校验
                reulity:false,
                www:false,
                flag:false,
                flag1:false,
                form:{
                    id:'',//ID
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
                curveBut_award:0,
                // 营业执照有效时间 开始不能大于结束
                pickerOptions0:{
                    disabledDate: (time) => {
                        if (this.form.end_date != "") {
                            return time.getTime() > Date.now() || time.getTime() > this.form.end_date;
                        } else {
                            // return time.getTime() < Date.now();
                        }

                    }
                },
                pickerOptions1:{
                    disabledDate: (time) => {
                        return time.getTime() < this.form.start_date;
                    }
                },
                // 资质认证有效时间 开始不能大于结束
                pickerOptions_certifGo:{
                    disabledDate_certifGo: (time) => {
                        debugger
                        if (this.certificationData.end_date != "") {
                            return time.getTime() > Date.now() || time.getTime() > this.certificationData.end_date;
                        } else {
                            // return time.getTime() < Date.now();
                        }
                    }
                },
                pickerOptions_certifEnd:{
                    disabledDate_certifGo: (time) => {
                        return time.getTime() < this.certificationData.start_date;
                    }
                },
                scroll:0,
                activeName:'first',
                nextArray : ['first','second','third','fourth','fifth','sixth','seventh','eighth','ninth','tenth','eleventh'], 
                getDsabled:'first',
                topAnddD:'first'
            }    
        },
        mounted(){
            this.loading = false
        },
        created() {
        },
        watch : {
            'activeName':function(val,oldVal) { //监听切换状态
                
            }
        },
        methods: {
            // examine is duplication in supplier_name
            examine_Duplication(name){
                if(!name){
                    return false
                }
                $.post('/Dwin/Purchase/checkSupplierName',{supplier_name:name},function(data){
                    var supplier_nameID = document.getElementById('supplier_nameID')
                    if(data.status !== 200){
                        supplier_nameID.classList.add('red_bor')
                        layer.msg(data.msg)
                    }else{
                        supplier_nameID.classList.remove('red_bor')
                    }
                })
            },
            // 下一步 / 上一步
            nextStep(payment){
                if(payment == 'top'){
                    var activeData_new = this.activeName
                    this.nextArray.forEach((item,index) => {
                        if(activeData_new == item){
                            var transfer = this.nextArray[index - 1]
                            // console.log(index)  
                            if(index - 1 == 0){
                                this.topAnddD = this.nextArray[0]
                            }else if(index == 0){
                                this.topAnddD = this.nextArray[0]
                            }else{
                                this.topAnddD = transfer
                            }
                            vm.activeName = transfer
                        }
                    })
                }else if(payment == 'down'){
                    var activeData_new = this.activeName
                    this.nextArray.forEach((item,index) => {
                        function downGo (index){
                            var transfer = vm.nextArray[index + 1]
                            if(index + 1 == vm.nextArray.length - 1){
                                vm.topAnddD = vm.nextArray[vm.nextArray.length - 1]
                            }else{
                                vm.topAnddD = transfer
                            }
                            vm.getDsabled = transfer
                            vm.activeName = transfer
                        }
                        if(activeData_new == item){
                            switch(index){
                                case 0:
                                    var nameIs_listed = true
                                    if(this.form.is_listed == 1){
                                        if(this.form.stock_code == ''){
                                            layer.msg('上市公司股票代码必须填写')
                                            nameIs_listed = false
                                        }else{
                                            for(var key in this.form){
                                                if(key != 'is_listed' && key != 'stock_code' && key != 'tips'){
                                                    if(this.form[key] == ''){
                                                        layer.msg('请将数据填写完整')
                                                        nameIs_listed = false
                                                        break
                                                    }
                                                }
                                            }
                                        }
                                    }else{
                                        for(let key in this.form){
                                            if(key != 'stock_code' && key != 'tips'){
                                                if(vm.form[key] == ''){
                                                    layer.msg('请将数据填写完整')
                                                    nameIs_listed = false
                                                    break
                                                }
                                            }
                                        }
                                        
                                    }
                                    if(nameIs_listed){
                                        downGo(index) 
                                    }
                                    break
                                case 1:
                                    var nameContactData = true
                                    for(let num in this.contactData){
                                        for(let key in this.contactData[num]){
                                            if(this.contactData[num][key] == ''){
                                                layer.msg('请将数据填写完整')
                                                nameContactData = false
                                                break
                                            }
                                        }
                                    }
                                    if(nameContactData){
                                        downGo(index)
                                    }
                                    break
                                case 2:
                                    var nameAddressData = true
                                    for(let num in this.addressData){
                                        if(this.addressData[num].address == ''){
                                            layer.msg('请将数据填写完整')
                                            nameAddressData = false
                                            break
                                        }
                                    }
                                    if(nameAddressData){
                                        downGo(index)
                                    }
                                    break
                                case 3:
                                    var nameCertificationData = true
                                    for(let num in this.certificationData){
                                        for(let key in this.certificationData[num]){
                                            // cer_name  issuing_authority  start_time stop_time
                                            if(this.certificationData[num][key] == '' && key != 'file_id'){
                                                console.log(key)
                                                layer.msg('请将数据填写完整')
                                                nameCertificationData = false
                                                break
                                            }
                                            if(this.certificationData[num].start_time > this.certificationData[num].stop_time){
                                                layer.msg('生效时间不能大于结束时间')
                                                nameCertificationData = false
                                                break
                                            }
                                        }
                                    }
                                    if(nameCertificationData){
                                        downGo(index)
                                    }
                                    break
                                case 4:
                                    var nameAwardsData = true
                                    for(let num in this.awardsData){
                                        for(let key in this.awardsData[num]){
                                            // awards_name issuing_authority validity_time
                                            if(this.awardsData[num][key] == '' && key != 'file_id'){
                                                layer.msg('请将数据填写完整')
                                                nameAwardsData = false
                                                break
                                            }
                                        }
                                    }
                                    if(nameAwardsData){
                                        downGo(index)
                                    }
                                    break
                                case 5:
                                    var nameCustomerData = true
                                    for(let num in this.customerData){
                                        for(let key in this.customerData[num]){
                                            if(this.customerData[num][key] == ''){
                                                layer.msg('请将数据填写完整')
                                                nameCustomerData = false
                                                break
                                            }
                                        }
                                    }
                                    if(nameCustomerData){
                                        downGo(index)
                                    }
                                    break
                                case 6:
                                    var nameCooperationData = true
                                    for(let num in this.cooperationData){
                                        for(let key in this.cooperationData[num]){
                                            if(this.cooperationData[num][key] == ''){
                                                layer.msg('请将数据填写完整')
                                                nameCooperationData = false
                                                break
                                            }
                                        }
                                    }
                                    if(nameCooperationData){
                                        downGo(index)
                                    }
                                    break
                                case 7:
                                    var nameEquityData = true
                                    for(let num in this.equityData){
                                        if(this.equityData[num].shareholder_name != ''&&this.equityData[num].shareholder_name != undefined){
                                            if(this.equityData[num].shareholding_ratio == ''||this.equityData[num].shareholding_ratio == undefined){
                                                layer.msg('填写完整或不填')
                                                nameEquityData = false
                                                break
                                            }
                                        }
                                        if(this.equityData[num].shareholding_ratio != ''&&this.equityData[num].shareholding_ratio != undefined){
                                            if(this.equityData[num].shareholder_name == ''||this.equityData[num].shareholder_name == undefined){
                                                layer.msg('填写完整或不填')
                                                nameEquityData = false
                                                break
                                            }
                                        }
                                    }
                                    if(nameEquityData){
                                        downGo(index)
                                    }
                                    break
                                case 8:
                                    var namefinanceData = true
                                    for(let num in this.financeData){
                                        var finace_number = 0
                                        for(let key in this.financeData[num]){
                                            if(this.financeData[num][key] != ''){
                                                finace_number++
                                            }
                                        }
                                        if(finace_number < 5&&finace_number != 0){
                                            layer.msg('填写完整或不填')
                                            namefinanceData = false
                                            break
                                        }
                                    }
                                    if(namefinanceData){
                                        downGo(index)
                                    }
                                    break
                                case 9:
                                    var nameteamData = true
                                    for(let num in this.teamData){
                                        var finace_number = 0
                                        for(let key in this.teamData[num]){
                                            if(this.teamData[num][key] != ''&&key != 'tips'){
                                                finace_number++
                                            }
                                        }
                                        if(finace_number < 2&&finace_number != 0){
                                            layer.msg('填写完整或不填')
                                            nameteamData = false
                                            break
                                        }
                                    }
                                    if(nameteamData){
                                        downGo(index)
                                    }
                                    break
                            }   
                        }
                    })
                }
            },
            // tabs
            handleClick(tab,event) {
                vm.topAnddD = vm.nextArray[tab.index]
            },
            // 输入校验   ==========GO=========》
            // 注册资金不能为负数
            notNegative(val){
                if(val<0){
                    vm.form.registered_capital = 0
                    layer.msg('不能为负值')
                } 
            },
            // 持股比例
            shareholding_repity(event){
                var values = event.srcElement.value
                if(Number(values) > 100){
                    this.$message({
                        showClose: true,
                        message: '请检查输入持股比列是否正确！',
                        type: 'warning'
                    });
                }
            },
            // 网址 去除红边
            wwwviefy(){
                this.www = false
            },
            // 网址 // 验证url   
            isurl(str_url){
                if(str_url.target.value){
                    var strregex = "^((https|http|ftp|rtsp|mms)?://)"  
                            + "?(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?" // ftp的user@   
                            + "(([0-9]{1,3}.){3}[0-9]{1,3}" // ip形式的url- 199.194.52.184   
                            + "|" // 允许ip和domain（域名）   
                            + "([0-9a-z_!~*'()-]+.)*" // 域名- www.   
                            + "([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]." // 二级域名   
                            + "[a-z]{2,6})" // first level domain- .com or .museum   
                            + "(:[0-9]{1,4})?" // 端口- :80   
                            + "((/?)|" // a slash isn't required if there is no file name   
                            + "(/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+/?)$";   
                            var objExp=new RegExp(strregex);     
                            var consequence = objExp.test(str_url.target.value)
                    if(consequence){
                    }else{
                        this.$message({
                            showClose: true,
                            message: '输入网址有误，请检查！',
                            type: 'warning'
                        });
                        this.form.websitea_address = ''
                        this.www = true
                    }
                }
            },
            // 资金  注册
            onlyNum(event){
                var value = event.target.value;
                if(value){
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
                }
            },
            // 资金  实收
            onlyNum1(event){
                var value1 = event.target.value;
                if(value1){
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
                }
            },
            // 手机验证
            upperCase (event) {
                if(event){
                    for(var i = 0;i < event.length;i++){
                        if(event.charCodeAt(i) > 255){
                            this.$message({
                                showClose: true,
                                message: '请检查输入是否正确！',
                                type: 'warning'
                            }); 
                        }
                    }
                }
            },
            // 传真验证
            upperFax (event) {
                if(event){
                    if(!(/^[+]{0,1}(\d){1,3}[ ]?([-]?((\d)|[ ]){1,12})+$/.test(event))){
                        this.$message({
                            showClose: true,
                            message: '输入传真有误，请检查传真输入是否正确！！',
                            type: 'warning'
                        });
                        return false; 
                    } 
                }
            },
            // 电子邮件
            check (event){
                console.log(event)
                if(event){
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
                }
            },
            // 总资产
            onlyNum_telephome(event){
                var value = event.target.value;
                console.log(value)
                if(value){
                    if (!/^\+?[1-9][0-9]*$/.test(value)) {
                        this.$message({
                            showClose: true,
                            message: '只能输入数字',
                            type: 'warning'
                        });
                        // event.target.value.splice(value.length-1,1);
                        this.reulity = true
                    }
                }
            },
            // 营业执照号码
            businessLicense15(event){
                var ints = event.target.value;
                // 判断是15位还是18位
                if(ints.length == 15){
                    let ti = 0;
                    let si = 0;// pi|11+ti
                    let cj = 0;// （si||10==0？10：si||10）*2
                    let pj = 10;// pj=cj|11==0?10:cj|11
                    let lastNum = '';
                    for (let i=0;i<ints.length;i++) {
                        ti = parseInt(ints[i]);
                        si = pj + ti;
                        cj = (0 == si % 10 ? 10 : si % 10) * 2;
                        pj = cj % 11;
                        if (i == ints.length-1) {
                            //lastNum =(1 - pj < 0 ? 11 - pj : 1 - pj) % 10;
                            lastNum = si%10
                        }
                    }
                    if(lastNum==1){
                        // this.$message({
                        //     showClose: true,
                        //     message: '营业执照号码输入正确!',
                        //     type: 'success'
                        // });
                    }else{
                        this.$message({
                            showClose: true,
                            message: '营业执照号码输入有误！',
                            type: 'warning'
                        });
                    }
                }else if(ints.length == 18){
                    var reg = /^([159Y]{1})([1239]{1})([0-9ABCDEFGHJKLMNPQRTUWXY]{6})([0-9ABCDEFGHJKLMNPQRTUWXY]{9})([0-9ABCDEFGHJKLMNPQRTUWXY])$/;
                    if(!reg.test(ints)){
                        return false;
                    }
                    var str = '0123456789ABCDEFGHJKLMNPQRTUWXY';
                    var ws =[1,3,9,27,19,26,16,17,20,29,25,13,8,24,10,30,28];
                    var codes = new Array();
                    codes[0] = ints.substr(0,ints.length-1);
                    codes[1] = ints.substr(ints.length-1,ints.length);
                    var sum = 0;
                    for(var i=0;i<17;i++){
                        sum += str.indexOf(codes[0].charAt(i)) * ws[i];
                    }
                    var c18 = 31 - (sum % 31);
                    if(c18 == 31){
                        c18 = 'Y';
                    }else if(c18 == 30){
                        c18 = '0';
                    }
                    if(str.charAt(c18) != codes[1].charAt(0)){
                        this.$message({
                            showClose: true,
                            message: '营业执照号码输入有误！',
                            type: 'warning'
                        });
                        return false;
                    }
                    return true;  
                }else{
                    this.$message({
                        showClose: true,
                        message: '营业执照号码输入有误！',
                        type: 'warning'
                    });
                }
            },
            //                     输入校验   ===========END========》
            // 供应商联系信息 追加一行
            superaddition() {
                // 判断是否重复新增
                if(this.contactData[this.contactData.length - 1] != undefined){
                    if(this.contactData[this.contactData.length - 1].contact_position){
                        var newObj = {
                            contact_position:'',
                            contact:'',
                            telephone:'',
                            phone:'',
                            e_mail:'',
                            fax:''
                        }
                        this.contactData.push(newObj)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    var newObj = {
                        contact_position:'',
                        contact:'',
                        telephone:'',
                        phone:'',
                        e_mail:'',
                        fax:''
                    }
                    this.contactData.push(newObj)
                }
            },
            // 供应商联系信息 删除一行
            removeAdd() {
                if(this.contactData.length !== 1){
                    this.contactData.splice(this.contactData.length-1,1)
                }
            },
            // 资质认证 追加一行
            certificateAdd() {
                 // 判断是否重复新增
                 if(this.certificationData[this.certificationData.length - 1] != undefined){
                    if(this.certificationData[this.certificationData.length - 1].cer_name){
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
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
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
                }
            },
            // 资质认证 删除
            removeCertificateAdd () {
                if(this.certificationData.length !== 1){
                    this.certificationData.splice(this.certificationData.length-1,1)
                }
            },
            // 公司地址 新增
            addAddr (){
                 // 判断是否重复新增
                 if(this.addressData[this.addressData.length - 1] != undefined){
                    if(this.addressData[this.addressData.length - 1].address){
                        var newaddAddr = {
                            address: '',
                            addr_description: ''
                        }
                        this.addressData.push(newaddAddr)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    var newaddAddr = {
                        address: '',
                        addr_description: ''
                    }
                    this.addressData.push(newaddAddr)
                }
            },
            // 公司地址 删除
            removeAddr () {
                if(this.addressData.length !== 1){
                    this.addressData.splice(this.addressData.length-1,1)
                }
            },
            // 奖惩情况 新增
            awardsDataAdd () {
                if(this.awardsData[this.awardsData.length - 1] != undefined){
                    if(this.awardsData[this.awardsData.length - 1].awards_name){
                        let newAwardsData = {
                            awards_name:'',
                            issuing_authority:'',
                            validity_time:'',
                            file_id:'',
                            awardsData_file:'无',
                            file_status:'0'
                        }
                        this.awardsData.push(newAwardsData)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    let newAwardsData = {
                            awards_name:'',
                            issuing_authority:'',
                            validity_time:'',
                            file_id:'',
                            awardsData_file:'无',
                            file_status:'0'
                        }
                        this.awardsData.push(newAwardsData)
                }
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
                // 判断是否重复新增
                if(this.customerData[this.customerData.length - 1] != undefined){
                    if(this.customerData[this.customerData.length - 1].cus_name){
                        let newCustomerData = {
                            cus_name:'',    //客户名称
                            main_project:'',    //项目/产品
                            main_contact: '',    //联系人
                            main_phone:'',       //电话
                            project_exec_time:'',    //实施时间
                            project_amount:''    //项目金额
                        }
                        this.customerData.push(newCustomerData)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    let newCustomerData = {
                        cus_name:'',    //客户名称
                        main_project:'',    //项目/产品
                        main_contact: '',    //联系人
                        main_phone:'',       //电话
                        project_exec_time:'',    //实施时间
                        project_amount:''    //项目金额
                    }
                    this.customerData.push(newCustomerData)
                }
            },
            // 客户情况 删除
            customerDataRemove () {
                if (this.customerData.length !== 1) {
                    this.customerData.splice(this.customerData.length-1,1)
                }
            },
            // 银行合作 新增
            cooperationDataAdd () {
                // 判断是否重复新增
                if(this.cooperationData[this.cooperationData.length - 1] != undefined){
                    if(this.cooperationData[this.cooperationData.length - 1].institution_name){
                        let newCooperationData = {
                            institution_name:'',     //机构名称
                            main_project:'',      //项目
                            main_contact:'',     //联系人
                            main_phone:'',       //电话
                            project_exec_time:'',     //时间
                            project_amount:''    //金额
                        }
                        this.cooperationData.push(newCooperationData)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    let newCooperationData = {
                        institution_name:'',     //机构名称
                        main_project:'',      //项目
                        main_contact:'',     //联系人
                        main_phone:'',       //电话
                        project_exec_time:'',     //时间
                        project_amount:''    //金额
                    }
                    this.cooperationData.push(newCooperationData)
                }
            },
            // 银行合作  删除
            cooperationDataRemove () {
                if(this.cooperationData.length !== 1){
                    this.cooperationData.splice(this.cooperationData.length-1,1)
                }
            },
            // 股权构成 新增
            equityDataAdd () {
                // 判断是否重复新增
                if(this.equityData[this.equityData.length - 1] != undefined){
                    if(this.equityData[this.equityData.length - 1].shareholder_name){
                        let newEquityData =
                            {
                                shareholder_name:'',
                                shareholding_ratio:''
                            }
                        this.equityData.push(newEquityData)
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    let newEquityData =
                        {
                            shareholder_name:'',
                            shareholding_ratio:''
                        }
                    this.equityData.push(newEquityData)
                }
            },
            // 股权结构 删除
            equityDataRemove () {
                if(this.equityData.length !== 1){
                    this.equityData.splice(this.equityData.length-1,1)
                }
            },
            // 供应商团队人员情况 新增
            teamCateAdd  () {
                // 判断是否重复新增
                if(this.teamData[this.teamData.length - 1] != undefined){
                    if(this.teamData[this.teamData.length - 1].team_cate){
                        var newTeamData = 
                            {
                                team_cate:'',
                                team_number:'',
                                tips:''
                                                
                            }
                        this.teamData.push(newTeamData)  
                    }else{
                        layer.msg('已有新增行，不能重复新增！')
                    }
                }else{
                    var newTeamData = 
                        {
                            team_cate:'',
                            team_number:'',
                            tips:''
                                            
                        }
                    this.teamData.push(newTeamData)  
                }
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
                            vm.form.id = res.data.id;
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
                console.log(res)
            },

            //  点击上传   => 获奖情况
            clickUpdata_award(index){
                console.log(index)
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
                console.log(res)
            },
            //  提交
             onblle (formData) {
                var JSON_judge = true
                var supplierData = []

                // 参数
                supplierData = formData
                var dataJSON_judge = supplierData
                // 判断必填项
                var  calculations = 0;
                for(let i in dataJSON_judge){ 
                    if(i == 'supplier_name'){
                        if(dataJSON_judge[i] == ''){
                            JSON_judge = false
                            layer.msg('供应商名称必须填写完整！')
                            break
                        }
                    }
                    if(i == 'supplier_id'){
                        if(dataJSON_judge[i] == ''){
                            JSON_judge = false
                            layer.msg('供应商编号必须填写完整！')
                            break
                        }
                    }
                    if(i == 'is_listed'){
                        if(dataJSON_judge[i] == ''){
                            JSON_judge = false
                            layer.msg('请选择企业是否上市！')
                            break
                        }
                        if(dataJSON_judge[i] == '1'){
                            if(dataJSON_judge['stock_code'] == ''){
                                JSON_judge = false
                                layer.msg('上市公司股票代码必填！')
                                break
                            }
                        }
                    }else{
                        if(dataJSON_judge[i] == ''){
                            console.log(dataJSON_judge[i])
                            if(i == 'websitea_address'|| i == 'tips' || i == 'stock_code'){
                                
                            }else{ 
                                JSON_judge = false
                                layer.msg('必填项数据填写不完整！')
                                break
                            }
                        }
                    }
                }
                // 联系人校验
                if(JSON_judge){
                    for(let i in this.contactData){
                        for(let key in this.contactData[i]){
                            if(this.contactData[i][key] == ''){
                                if(key == 'telephone' || key == 'phone' || key == 'fax' || key == 'e_mail'){
                                    if(this.contactData[i][key] == ''){
                                        calculations++
                                    }
                                    if(calculations == 4){
                                        JSON_judge = false
                                        layer.msg('联系人电话、手机、传真、电子邮件不能全为空！')
                                        break
                                    }
                                }else{
                                    JSON_judge = false
                                    layer.msg('联系人和练习人姓名不能为空！')
                                    break
                                }
                            }
                        }
                    }
                }
                // 资质认证校验
                if(JSON_judge){
                    for(let i in this.certificationData){
                        for(let key in this.certificationData[i]){
                            if(this.certificationData[i][key] == ''){
                                if(key == 'certificationData_file'||key == 'file_id'){
                                    
                                }else{
                                    JSON_judge = false
                                    layer.msg('资质认证除证书都为必填！')
                                    break
                                }
                            }
                        }
                    }
                }
                // 获奖校验
                if(JSON_judge){
                    for(let i in this.awardsData){
                        for(let key in this.awardsData[i]){
                            if(this.awardsData[i][key] == ''){
                                if(key == 'awardsData_file'||key == 'file_id'){
                                    
                                }else{
                                    JSON_judge = false
                                    layer.msg('请将获奖情况信息填写完整！')
                                    break
                                }
                            }
                        }   
                    }
                }
                // 股东验证
                if(JSON_judge){
                    for(let key in this.equityData){
                        for(let i in this.equityData[key]){
                            if(this.equityData[key]['shareholder_name']){
                                if(this.equityData[key]['shareholding_ratio'] == ''){
                                    JSON_judge = false
                                    layer.msg('股东比例不能为空！')
                                    break
                                }
                            }
                            if(this.equityData[key]['shareholding_ratio']){
                                if(this.equityData[key]['shareholder_name'] == ''){
                                    JSON_judge = false
                                    layer.msg('股东名称不能为空！')
                                    break
                                }
                            }
                        }
                    }
                }
                if(JSON_judge){
                    // 时间 / 1000
                    formData.start_date = formData.start_date / 1000
                    formData.end_date = formData.end_date / 1000
                    for(var i = 0;i < this.certificationData.length; i++){
                        this.certificationData[i].start_time = this.certificationData[i].start_time / 1000
                        this.certificationData[i].stop_time = this.certificationData[i].stop_time / 1000
                    }
                    for(var i = 0;i < this.awardsData.length; i++){
                        this.awardsData[i].validity_time = this.awardsData[i].validity_time / 1000
                    }
                    for(var i = 0;i < this.customerData.length; i++){
                        this.customerData[i].project_exec_time = this.customerData[i].project_exec_time / 1000
                    }
                    for(var i = 0;i < this.cooperationData.length; i++){
                        this.cooperationData[i].project_exec_time = this.cooperationData[i].project_exec_time / 1000
                    }
                    var data = {
                        'supplierData' : supplierData,
                        'addressData' : this.addressData,
                        'contactData' : this.contactData,
                        'certificationData' :  this.certificationData,
                        'awardsData' : this.awardsData,
                        'customerData' : this.customerData,
                        'equityData' : this.equityData,
                        'financeData' : this.financeData,
                        'teamData' : this.teamData,
                        'cooperationData' : this.cooperationData
                    }
                    $.ajax({
                        url:'/Dwin/Purchase/addSupplier',
                        type:'post',
                        data:data,
                        success:function (res) {
                            if(res.status === 200){
                                // layer.open页面关闭
                                layer.msg(res.msg)
                                var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                parent.layer.close(index)
                                parent.location.close()
                            }else{
                                formData.start_date = formData.start_date * 1000
                                formData.end_date = formData.end_date * 1000
                                for(var i = 0;i < vm.certificationData.length; i++){
                                    vm.certificationData[i].start_time = vm.certificationData[i].start_time * 1000
                                    vm.certificationData[i].stop_time = vm.certificationData[i].stop_time * 1000
                                }
                                for(var i = 0;i < vm.awardsData.length; i++){
                                    vm.awardsData[i].validity_time = vm.awardsData[i].validity_time * 1000
                                }
                                for(var i = 0;i < vm.customerData.length; i++){
                                    vm.customerData[i].project_exec_time = vm.customerData[i].project_exec_time * 1000
                                }
                                for(var i = 0;i < vm.cooperationData.length; i++){
                                    vm.cooperationData[i].project_exec_time = vm.cooperationData[i].project_exec_time * 1000
                                }
                            }
                            layer.msg(res.msg)
                        }   
                    })
                }
            }
        }
    })
</script>

</html>