/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 17:07:09
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crm_busniess
-- ----------------------------
DROP TABLE IF EXISTS `crm_busniess`;
CREATE TABLE `crm_busniess` (
  `bid` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `bname` varchar(255) NOT NULL COMMENT '业务名称',
  `ckeyword` varchar(255) NOT NULL COMMENT '客户关键字',
  `bstatus` enum('合同下单','报价','需求','有购买需求','初次沟通') NOT NULL DEFAULT '初次沟通' COMMENT '业务状态',
  `orderstatus` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否有订单:0:无;1:有;',
  `orderid` int(10) unsigned NOT NULL COMMENT '订单编号',
  `projectstatus` enum('1','0') NOT NULL DEFAULT '0' COMMENT '是否立项:0无;1有',
  `projectid` int(10) unsigned NOT NULL COMMENT '项目编号',
  `picid` int(10) unsigned NOT NULL COMMENT 'personinchargeid负责人',
  `authorid` int(11) NOT NULL COMMENT '审核人id',
  `authorstatus` enum('3','2','1','0') NOT NULL DEFAULT '1' COMMENT '审核状态0失败1未审核2经理通过3总经理审核通过',
  PRIMARY KEY (`bid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_busniess
-- ----------------------------

-- ----------------------------
-- Table structure for crm_contactrecord
-- ----------------------------
DROP TABLE IF EXISTS `crm_contactrecord`;
CREATE TABLE `crm_contactrecord` (
  `cid` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '联系记录id',
  `theme` varchar(255) NOT NULL COMMENT '联系内容主题',
  `content` varchar(2550) NOT NULL COMMENT '联系内容',
  `ctime` int(10) unsigned NOT NULL COMMENT '联系时间',
  `ctype` enum('3','2','1','4') NOT NULL DEFAULT '1' COMMENT '1电话2拜访3会议',
  `picid` int(7) unsigned zerofill NOT NULL COMMENT 'personincharge负责人',
  `posttime` int(10) unsigned NOT NULL,
  `customerid` int(10) unsigned zerofill NOT NULL,
  `audstatus` enum('3','2','1') NOT NULL DEFAULT '1' COMMENT '1:未审核 2:有效 3:无效',
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_contactrecord
-- ----------------------------
INSERT INTO `crm_contactrecord` VALUES ('0000000002', 'test1', 'tes1', '1497252727', '1', '0000001', '1497252734', '0000000005', '1');
INSERT INTO `crm_contactrecord` VALUES ('0000000003', 'test1', 'test1', '1498030360', '1', '0000001', '1497252765', '0000000002', '1');
INSERT INTO `crm_contactrecord` VALUES ('0000000004', '访问', '我无法', '1501138330', '2', '0000001', '1498632739', '0000000253', '1');

-- ----------------------------
-- Table structure for crm_cuscontacter
-- ----------------------------
DROP TABLE IF EXISTS `crm_cuscontacter`;
CREATE TABLE `crm_cuscontacter` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '联系人姓名',
  `position` varchar(20) DEFAULT NULL,
  `cusid` int(10) unsigned zerofill NOT NULL COMMENT '所属公司id',
  `phone` int(11) NOT NULL COMMENT '联系人电话',
  `tel` int(15) DEFAULT NULL COMMENT '联系人邮箱',
  `emailaddr` varchar(50) DEFAULT NULL,
  `wechatnum` varchar(25) DEFAULT NULL,
  `qqnum` int(14) DEFAULT NULL,
  `addtime` int(10) unsigned NOT NULL,
  `addid` int(7) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cid` (`cusid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_cuscontacter
-- ----------------------------
INSERT INTO `crm_cuscontacter` VALUES ('0000000001', 'judi', '经理', '0000000005', '123123131', '1231231', 'invoker@x.com', 'wu ', '0', '1497512116', '0000001');

-- ----------------------------
-- Table structure for crm_cusfile
-- ----------------------------
DROP TABLE IF EXISTS `crm_cusfile`;
CREATE TABLE `crm_cusfile` (
  `fid` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '文件id',
  `cid` int(10) unsigned zerofill NOT NULL,
  `addtime` int(10) unsigned NOT NULL,
  `builderid` int(7) unsigned zerofill NOT NULL,
  `fpath` varchar(60) NOT NULL,
  `fname` varchar(30) NOT NULL,
  `hasfile` int(2) unsigned NOT NULL,
  PRIMARY KEY (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_cusfile
-- ----------------------------
INSERT INTO `crm_cusfile` VALUES ('0000000001', '0000000005', '1497607718', '0000001', '/Public/Upload/0000000005/2017-06-16/5943adc42ebe0.jpg', '5943adc42ebe0.jpg', '1');
INSERT INTO `crm_cusfile` VALUES ('0000000002', '0000000254', '1498523986', '0000001', '/Public/Upload/0000000254/2017-06-27/5951a9523990c.jpg', '5951a9523990c.jpg', '1');
INSERT INTO `crm_cusfile` VALUES ('0000000003', '0000000005', '1498793262', '0000001', '/Public/Upload/0000000005/2017-06-30/5955c527c7c7a.xls', '5955c527c7c7a.xls', '1');
INSERT INTO `crm_cusfile` VALUES ('0000000004', '0000000253', '1498795198', '0000001', '/Public/Upload/0000000253/2017-06-30/5955ccba8b34d.pdf', '5955ccba8b34d.pdf', '1');
INSERT INTO `crm_cusfile` VALUES ('0000000005', '0000000002', '1498799379', '0000001', '/Public/Upload/0000000002/2017-06-30/5955dd134289c.pdf', '5955dd134289c.pdf', '1');
INSERT INTO `crm_cusfile` VALUES ('0000000006', '0000000007', '1498799994', '0000001', '/Public/Upload/0000000007/2017-06-30/北京迪文科技有限公司.pdf', '北京迪文科技有限公司.pdf', '1');

-- ----------------------------
-- Table structure for crm_customer
-- ----------------------------
DROP TABLE IF EXISTS `crm_customer`;
CREATE TABLE `crm_customer` (
  `cid` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT 'customerid',
  `cname` varchar(255) NOT NULL COMMENT 'customername',
  `keyword` varchar(255) NOT NULL COMMENT '客户名简写，关键字',
  `website` varchar(255) NOT NULL COMMENT '公司网址',
  `cphonenumber` int(10) unsigned NOT NULL COMMENT '公司电话',
  `scale` enum('3','2','1','4') DEFAULT '2' COMMENT '公司规模1:0_99;2:100_500;3,500_999;4:1000以上',
  `province` varchar(255) NOT NULL COMMENT '所在城市',
  `addr` varchar(255) NOT NULL COMMENT '公司地址',
  `ctype` enum('外资企业','民营企业','合资企业','国有企业') DEFAULT '民营企业' COMMENT '公司性质',
  `csource` enum('促销活动','Email','媒体宣传','独立开发','客户介绍','老客户','客服','网站','展会') NOT NULL DEFAULT '独立开发' COMMENT '客户来源',
  `clevel` enum('1','2','3','4') NOT NULL DEFAULT '4' COMMENT '客户级别',
  `cstatus` enum('1','2') NOT NULL DEFAULT '2' COMMENT '客户状态1无负责人 2：有负责人',
  `uid` int(7) unsigned zerofill DEFAULT NULL COMMENT '负责人id',
  `founderid` int(7) unsigned zerofill NOT NULL COMMENT '创建人id',
  `tip` varchar(2550) DEFAULT NULL COMMENT '备注',
  `addtime` varchar(25) NOT NULL COMMENT '客户创建时间',
  `auditstatus` enum('4','1','2','3') NOT NULL DEFAULT '1' COMMENT '客户审核状态4:false;1:指定审核人未审核;总经理未审核;3:通过;',
  `auditorid` int(7) unsigned zerofill NOT NULL COMMENT '审核人id',
  `type` enum('3','2','1') DEFAULT '1' COMMENT '审核类型：新添加为1，审核通过后为2，修改客户信息后为3',
  PRIMARY KEY (`cid`),
  KEY `keyword` (`keyword`)
) ENGINE=MyISAM AUTO_INCREMENT=261 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_customer
-- ----------------------------
INSERT INTO `crm_customer` VALUES ('0000000001', '小猪科技有限公司', 'AcFun', 'www.dulj.com', '102312313', '2', '北京', '北京东城区会新街43号李建大厦904', '国有企业', '促销活动', '4', '2', '0000001', '0000001', '', '1496318140', '3', '0000021', '2');
INSERT INTO `crm_customer` VALUES ('0000000002', 'Linux中国', 'linux', 'www.linux.com', '1324288912', '3', '上海', '[\"\\u5317\\u4eac\\u5e02\\u6d77\\u6dc0\\u533a\",\"\\u5185\\u8499\\u53e4\\u8d64\\u5cf0\\u5e02\\u677e\\u5c71\\u533a223\\u8def\",\"\\u5185\\u8499\\u53e4\\u8d64\\u5cf0\\u5e02\\u677e\\u5c71\\u533a223\\u8def\"]', '外资企业', '老客户', '4', '2', '0000001', '0000001', '这是个假消息', '1496324366', '3', '0000021', '2');
INSERT INTO `crm_customer` VALUES ('0000000003', '搜狗科技有限公司', '搜狗', 'www.sougou.com', '12312390', '1', '北京', '[\"\\u641c\\u72d7\\u79d1\\u6280\\u5e73\\u623f\",\"\\u641c\\u72d7\\u79d1\\u6280\\u5c0f\\u697c\",\"\\u641c\\u72d7\\u79d1\\u6280\\u5927\\u697c\"]', '民营企业', '展会', '4', '2', '0000022', '0000001', '', '1496324566', '3', '0000021', '2');
INSERT INTO `crm_customer` VALUES ('0000000004', '易信科技有限公司', '易信', 'www.yixin.com', '1231232412', '2', '北京', '北京东城区二手车路43号', '合资企业', '媒体宣传', '4', '2', '0000001', '0000001', '', '1496324623', '3', '0000021', '2');
INSERT INTO `crm_customer` VALUES ('0000000005', '内蒙古易信里伊利集团股份有限公司', '易信里', 'www.yili.com', '102312312', '3', '内蒙古', '[\"\\u5185\\u8499\\u53e4\\u8d64\\u5cf0\\u5e02\\u677e\\u5c71\",\"\\u5185\\u8499\\u53e4\\u8d64\\u5cf0\\u5e02\\u677e\\u5c71\\u533a2\",\"\\u5185\\u8499\\u53e4\\u8d64\\u5cf0\\u5e02\\u677e\\u5c71\\u533a223\\u8def\"]', '民营企业', '展会', '4', '2', '0000001', '0000001', '', '1496381967', '3', '0000021', '2');
INSERT INTO `crm_customer` VALUES ('0000000007', 'LINUX(UK)', 'linux', 'www.linux.com', '123123131', '3', '北京', '[\"jweifl\",\"jeiflawfj\",\"gejlawiwj\"]', '外资企业', '老客户', '4', '2', '0000022', '0000001', '手动阀', '1497271959', '3', '0000021', '2');
INSERT INTO `crm_customer` VALUES ('0000000008', 'test1', 'test', 'www.test', '1231132112', '3', '河北', '[\"\\u5317\\u4eac\\u5e02\\u8f96\\u533a\\u4e1c\\u57ce\\u533agagaga steet\",\"\\u7518\\u8083\\u5170\\u5dde\\u5e02\\u5e02\\u8f96\\u533ahohoho steet\",\"\\u6cb3\\u5317\\u77f3\\u5bb6\\u5e84\\u5e02\\u5e02\\u8f96\\u533ahahaha steet\"]', '国有企业', '促销活动', '4', '2', '0000001', '0000001', 'zheshi test1', '1497417627', '1', '0000021', '2');
INSERT INTO `crm_customer` VALUES ('0000000252', '小猪科', '而23', '234饿', '23', '3', '北京', '[\"\\u5317\\u4eac\\u5e02\\u8f96\\u533a\\u4e1c\\u57ce\\u533a\\u8303\\u5fb7\\u8428\"]', '国有企业', '促销活动', '4', '1', null, '0000001', '', '1497598356', '3', '0000021', '2');
INSERT INTO `crm_customer` VALUES ('0000000253', '中国科技有限公司', '中科', '234饿', '23', '', '北京', '[\"\\u4e91\\u829d\",\"\\u9999\\u683c\\u91cc\\u62c9\",\"\\u5317\\u4eac\\u5e02\\u8f96\\u533a\\u4e1c\\u57ce\\u533a\\u8303\\u5fb7\\u8428\"]', '国有企业', '促销活动', '4', '2', '0000021', '0000001', '', '1497598760', '3', '0000021', '2');
INSERT INTO `crm_customer` VALUES ('0000000254', '小猪科第三方', '而23', '234饿', '23', '', '北京', '[\"\\u5317\\u4eac\\u5e02\\u8f96\\u533a\\u4e1c\\u57ce\\u533a\\u8303\\u5fb7\\u8428\"]', '国有企业', '促销活动', '4', '2', '0000001', '0000001', '', '1497598820', '3', '0000021', '2');
INSERT INTO `crm_customer` VALUES ('0000000258', '小小', '21', '12', '2121', '1', '北京', '[\"\\u5317\\u4eac\\u5e02\\u8f96\\u533a\\u4e1c\\u57ce\\u533a231\"]', '国有企业', '促销活动', '4', '1', null, '0000001', '1221', '1497599110', '3', '0000021', '2');
INSERT INTO `crm_customer` VALUES ('0000000259', '测试新表用客户', '测试', 'www.test.cmo', '1231919120', '2', '北京', '[\"\\u5317\\u4eac\\u5e02\\u8f96\\u533a\\u4e1c\\u57ce\\u533a\\u66fe\\u7ecf\",\"\\u5317\\u4eac\\u5e02\\u8f96\\u533a\\u6000\\u67d4\\u533alilith\\u8857\\u9053608\\u53f7\\u5c42\"]', '民营企业', '促销活动', '4', '2', '0000001', '0000001', '地方', '1498541250', '4', '0000021', '1');
INSERT INTO `crm_customer` VALUES ('0000000260', '测试客户权限', '客户', 'www.ceshi.com', '12324514', '2', '北京', '[\"\\u5317\\u4eac\\u53bf\\u5ef6\\u5e86\\u53bf\\u6d4b\\u8bd5\\u8857\\u9053121\\u53f7\"]', '民营企业', '促销活动', '4', '2', '0000022', '0000022', '这是测试', '1498556402', '3', '0000021', '2');

-- ----------------------------
-- Table structure for crm_dept
-- ----------------------------
DROP TABLE IF EXISTS `crm_dept`;
CREATE TABLE `crm_dept` (
  `id` int(3) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '部门主键',
  `name` varchar(10) NOT NULL COMMENT '类别名称',
  `parent_id` int(3) unsigned zerofill DEFAULT NULL,
  `level` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_dept
-- ----------------------------
INSERT INTO `crm_dept` VALUES ('001', '总经理', '000', '0');
INSERT INTO `crm_dept` VALUES ('002', '研发本部', '001', '1');
INSERT INTO `crm_dept` VALUES ('003', '研发1部', '001', '1');
INSERT INTO `crm_dept` VALUES ('004', '研发2部', '001', '1');
INSERT INTO `crm_dept` VALUES ('005', '研发3部', '001', '1');
INSERT INTO `crm_dept` VALUES ('006', '研发4部', '001', '1');
INSERT INTO `crm_dept` VALUES ('007', '研发5部', '001', '1');
INSERT INTO `crm_dept` VALUES ('008', '销售1部', '001', '2');
INSERT INTO `crm_dept` VALUES ('009', '销售2部', '001', '2');
INSERT INTO `crm_dept` VALUES ('010', '销售3部', '001', '2');
INSERT INTO `crm_dept` VALUES ('011', '销售6部', '001', '2');
INSERT INTO `crm_dept` VALUES ('012', '售后服务', '001', '3');
INSERT INTO `crm_dept` VALUES ('013', '电话客服', '001', '4');
INSERT INTO `crm_dept` VALUES ('014', '研发本部', '002', '5');
INSERT INTO `crm_dept` VALUES ('015', '研发本部', '003', '5');
INSERT INTO `crm_dept` VALUES ('016', '研发本部', '004', '5');
INSERT INTO `crm_dept` VALUES ('017', '研发1组', '005', '5');
INSERT INTO `crm_dept` VALUES ('018', '研发2组', '005', '5');
INSERT INTO `crm_dept` VALUES ('019', '研发3组', '005', '5');
INSERT INTO `crm_dept` VALUES ('020', '研发4组', '005', '5');
INSERT INTO `crm_dept` VALUES ('021', '研发本部', '006', '5');
INSERT INTO `crm_dept` VALUES ('022', '研发本部', '007', '5');
INSERT INTO `crm_dept` VALUES ('023', '销售本部', '008', '5');
INSERT INTO `crm_dept` VALUES ('024', '销售1组', '008', '5');
INSERT INTO `crm_dept` VALUES ('025', '销售2组', '008', '5');
INSERT INTO `crm_dept` VALUES ('026', '销售3组', '008', '5');
INSERT INTO `crm_dept` VALUES ('027', '销售4组', '008', '5');
INSERT INTO `crm_dept` VALUES ('028', '销售5组', '008', '5');
INSERT INTO `crm_dept` VALUES ('029', '销售6组', '008', '5');
INSERT INTO `crm_dept` VALUES ('030', '销售本部', '009', '5');
INSERT INTO `crm_dept` VALUES ('031', '销售1组', '009', '5');
INSERT INTO `crm_dept` VALUES ('032', '销售2组', '009', '5');
INSERT INTO `crm_dept` VALUES ('033', '销售3组', '009', '5');
INSERT INTO `crm_dept` VALUES ('034', '销售5组', '009', '5');
INSERT INTO `crm_dept` VALUES ('035', '销售本部', '010', '5');
INSERT INTO `crm_dept` VALUES ('036', '销售本部', '011', '5');
INSERT INTO `crm_dept` VALUES ('037', '销售1组', '011', '5');
INSERT INTO `crm_dept` VALUES ('038', '销售2组', '011', '5');
INSERT INTO `crm_dept` VALUES ('039', '销售3组', '011', '5');
INSERT INTO `crm_dept` VALUES ('040', '销售4组', '011', '5');
INSERT INTO `crm_dept` VALUES ('041', '销售5组', '011', '5');
INSERT INTO `crm_dept` VALUES ('042', '研发经理', '014', '5');
INSERT INTO `crm_dept` VALUES ('043', '研发组长', '014', '5');
INSERT INTO `crm_dept` VALUES ('044', '研发工程师', '014', '5');
INSERT INTO `crm_dept` VALUES ('045', '研发经理', '015', '5');
INSERT INTO `crm_dept` VALUES ('046', '研发组长', '015', '5');
INSERT INTO `crm_dept` VALUES ('047', '研发工程师', '015', '5');
INSERT INTO `crm_dept` VALUES ('048', '研发经理', '016', '5');
INSERT INTO `crm_dept` VALUES ('049', '研发组长', '016', '5');
INSERT INTO `crm_dept` VALUES ('050', '研发工程师', '016', '5');
INSERT INTO `crm_dept` VALUES ('051', '研发经理', '017', '5');
INSERT INTO `crm_dept` VALUES ('052', '研发组长', '017', '5');
INSERT INTO `crm_dept` VALUES ('053', '研发工程师', '017', '5');
INSERT INTO `crm_dept` VALUES ('054', '研发组长', '018', '5');
INSERT INTO `crm_dept` VALUES ('055', '研发工程师', '018', '5');
INSERT INTO `crm_dept` VALUES ('056', '助理工程师', '018', '5');
INSERT INTO `crm_dept` VALUES ('057', '研发经理', '019', '5');
INSERT INTO `crm_dept` VALUES ('058', '研发组长', '019', '5');
INSERT INTO `crm_dept` VALUES ('059', '研发工程师', '019', '5');
INSERT INTO `crm_dept` VALUES ('060', '研发组长', '020', '5');
INSERT INTO `crm_dept` VALUES ('061', '研发工程师', '020', '5');
INSERT INTO `crm_dept` VALUES ('062', '助理工程师', '020', '5');
INSERT INTO `crm_dept` VALUES ('063', '研发经理', '021', '5');
INSERT INTO `crm_dept` VALUES ('064', '研发组长', '021', '5');
INSERT INTO `crm_dept` VALUES ('065', '研发工程师', '021', '5');
INSERT INTO `crm_dept` VALUES ('066', '研发组长', '022', '5');
INSERT INTO `crm_dept` VALUES ('067', '研发工程师', '022', '5');
INSERT INTO `crm_dept` VALUES ('068', '助理工程师', '022', '5');
INSERT INTO `crm_dept` VALUES ('069', '销售经理', '023', '5');
INSERT INTO `crm_dept` VALUES ('070', '销售精英', '023', '5');
INSERT INTO `crm_dept` VALUES ('071', '销售工程师', '023', '5');
INSERT INTO `crm_dept` VALUES ('072', '行政助理', '023', '5');
INSERT INTO `crm_dept` VALUES ('073', '销售组长', '024', '5');
INSERT INTO `crm_dept` VALUES ('074', '销售后备人才', '024', '5');
INSERT INTO `crm_dept` VALUES ('075', '销售工程师', '024', '5');
INSERT INTO `crm_dept` VALUES ('076', '销售组长', '025', '5');
INSERT INTO `crm_dept` VALUES ('077', '销售后备人才', '025', '5');
INSERT INTO `crm_dept` VALUES ('078', '销售组长', '026', '5');
INSERT INTO `crm_dept` VALUES ('079', '销售后备人才', '026', '5');
INSERT INTO `crm_dept` VALUES ('080', '销售组长', '027', '5');
INSERT INTO `crm_dept` VALUES ('081', '销售后备人才', '027', '5');
INSERT INTO `crm_dept` VALUES ('082', '销售组长', '028', '5');
INSERT INTO `crm_dept` VALUES ('083', '销售后备人才', '028', '5');
INSERT INTO `crm_dept` VALUES ('084', '销售工程师', '028', '5');
INSERT INTO `crm_dept` VALUES ('085', '销售组长', '029', '5');
INSERT INTO `crm_dept` VALUES ('086', '销售后备人才', '029', '5');
INSERT INTO `crm_dept` VALUES ('087', '销售经理', '030', '5');
INSERT INTO `crm_dept` VALUES ('088', '销售精英', '030', '5');
INSERT INTO `crm_dept` VALUES ('089', '销售工程师', '030', '5');
INSERT INTO `crm_dept` VALUES ('090', '行政助理', '030', '5');
INSERT INTO `crm_dept` VALUES ('091', '销售经理', '035', '5');
INSERT INTO `crm_dept` VALUES ('092', '销售精英', '035', '5');
INSERT INTO `crm_dept` VALUES ('101', '销售组长', '031', '5');
INSERT INTO `crm_dept` VALUES ('102', '销售后备人才', '031', '5');
INSERT INTO `crm_dept` VALUES ('112', '销售组长', '032', '5');
INSERT INTO `crm_dept` VALUES ('113', '销售后备人才', '032', '5');
INSERT INTO `crm_dept` VALUES ('114', '销售组长', '033', '5');
INSERT INTO `crm_dept` VALUES ('115', '销售后备人才', '033', '5');
INSERT INTO `crm_dept` VALUES ('116', '销售组长', '034', '5');
INSERT INTO `crm_dept` VALUES ('117', '销售后备人才', '034', '5');
INSERT INTO `crm_dept` VALUES ('118', '销售经理', '036', '5');
INSERT INTO `crm_dept` VALUES ('119', '销售精英', '036', '5');
INSERT INTO `crm_dept` VALUES ('120', '销售工程师', '036', '5');
INSERT INTO `crm_dept` VALUES ('121', '行政助理', '036', '5');
INSERT INTO `crm_dept` VALUES ('122', '销售组长', '037', '5');
INSERT INTO `crm_dept` VALUES ('123', '销售后备人才', '037', '5');
INSERT INTO `crm_dept` VALUES ('124', '销售组长', '038', '5');
INSERT INTO `crm_dept` VALUES ('125', '销售后备人才', '038', '5');
INSERT INTO `crm_dept` VALUES ('126', '销售组长', '039', '5');
INSERT INTO `crm_dept` VALUES ('127', '销售后备人才', '039', '5');
INSERT INTO `crm_dept` VALUES ('128', '销售组长', '040', '5');
INSERT INTO `crm_dept` VALUES ('129', '销售后备人才', '040', '5');
INSERT INTO `crm_dept` VALUES ('130', '销售组长', '041', '5');
INSERT INTO `crm_dept` VALUES ('131', '销售后备人才', '041', '5');

-- ----------------------------
-- Table structure for crm_onlineservice
-- ----------------------------
DROP TABLE IF EXISTS `crm_onlineservice`;
CREATE TABLE `crm_onlineservice` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '客服记录编号',
  `customer_id` int(10) unsigned zerofill NOT NULL COMMENT '客户编号',
  `content` varchar(255) NOT NULL COMMENT '客户问题',
  `answercontent` varchar(255) NOT NULL COMMENT '处理方式',
  `server_id` int(7) unsigned zerofill NOT NULL COMMENT '电话客服的id',
  `austatus` enum('2','1','3') NOT NULL DEFAULT '1' COMMENT '通话记录有效性3无效1未审核2有效',
  `addtime` int(10) NOT NULL,
  `caller` varchar(8) DEFAULT NULL COMMENT '来电人',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_onlineservice
-- ----------------------------
INSERT INTO `crm_onlineservice` VALUES ('0000000001', '0000000005', '今天天气好', '明天好天气', '0000001', '1', '1496398338', '李希');
INSERT INTO `crm_onlineservice` VALUES ('0000000002', '0000000005', 'MySQL技术内幕', '数据库防sql注入', '0000001', '1', '1496402207', 'gogo');
INSERT INTO `crm_onlineservice` VALUES ('0000000003', '0000000002', 'test2', 'test2', '0000001', '2', '1497494653', 'lily');
INSERT INTO `crm_onlineservice` VALUES ('0000000004', '0000000002', '123', '456', '0000001', '2', '1497693466', '789');
INSERT INTO `crm_onlineservice` VALUES ('0000000005', '0000000004', '审核权限修改', '123', '0000001', '1', '1498613022', '李林');
INSERT INTO `crm_onlineservice` VALUES ('0000000006', '0000000004', '测试修改权限', '测试', '0000001', '2', '1498613052', '利拉');
INSERT INTO `crm_onlineservice` VALUES ('0000000007', '0000000004', '测试接力积分', '阿斯顿发文', '0000001', '3', '1498613069', 'ldn');
INSERT INTO `crm_onlineservice` VALUES ('0000000008', '0000000007', '测权限1', '测', '0000001', '2', '1498628537', '林夕');

-- ----------------------------
-- Table structure for crm_orderform
-- ----------------------------
DROP TABLE IF EXISTS `crm_orderform`;
CREATE TABLE `crm_orderform` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned zerofill NOT NULL COMMENT '订单编号',
  `oname` varchar(255) NOT NULL COMMENT '订单名',
  `oprice` int(11) unsigned NOT NULL COMMENT '订单价格',
  `otime` int(10) unsigned NOT NULL COMMENT '订单时间',
  `deliverytime` int(10) unsigned NOT NULL COMMENT '交货时间',
  `odetail` varchar(3000) NOT NULL COMMENT '订单备注、详情',
  `cus_id` int(10) unsigned zerofill NOT NULL COMMENT '客户编号',
  `picid` int(7) unsigned zerofill NOT NULL COMMENT '负责人id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_orderform
-- ----------------------------
INSERT INTO `crm_orderform` VALUES ('0000000001', '0000000001', '倒计时订单', '1200000', '1496678400', '1512403200', '这是test操作哦', '0000000005', '0000001');
INSERT INTO `crm_orderform` VALUES ('0000000002', '0000000002', 'test哦', '888888', '1493913600', '1496678400', '这是layertest', '0000000005', '0000001');
INSERT INTO `crm_orderform` VALUES ('0000000003', '0000000003', 'test layer哈哈哈', '1233221', '1293811200', '1396540800', '失败还是', '0000000005', '0000001');
INSERT INTO `crm_orderform` VALUES ('0000000004', '0000000012', '123', '123', '1391270400', '1391270400', '123', '0000000005', '0000001');
INSERT INTO `crm_orderform` VALUES ('0000000005', '0000000012', '123', '123', '1391270400', '1391270400', '123', '0000000005', '0000001');
INSERT INTO `crm_orderform` VALUES ('0000000006', '0021532154', 'test提交', '10000', '1430755200', '1465142400', '哈哈哈哈', '0000000005', '0000001');
INSERT INTO `crm_orderform` VALUES ('0000000007', '0000013213', 'TEST2', '1000', '1496728265', '1497505863', 'FDASF', '0000000002', '0000001');

-- ----------------------------
-- Table structure for crm_reschange
-- ----------------------------
DROP TABLE IF EXISTS `crm_reschange`;
CREATE TABLE `crm_reschange` (
  `changeid` int(7) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '记录号',
  `postId` int(7) unsigned zerofill NOT NULL,
  `projid` int(7) unsigned zerofill NOT NULL,
  `auditId` int(7) unsigned zerofill NOT NULL,
  `changetime` int(10) unsigned NOT NULL,
  `oldRpsId` int(7) unsigned zerofill DEFAULT NULL,
  `newRpsId` int(7) unsigned zerofill DEFAULT NULL,
  `oldDeliveryTime` int(12) unsigned DEFAULT NULL,
  `newDeliveryTime` int(12) unsigned DEFAULT NULL,
  `oldPrjNeeds` varchar(5000) DEFAULT NULL,
  `newPrjNeeds` varchar(5000) DEFAULT '',
  `oldBonus` int(11) unsigned DEFAULT NULL,
  `newBonus` int(11) unsigned DEFAULT NULL,
  `newPrjPrice` int(9) DEFAULT NULL,
  `oldPrjPrice` int(9) DEFAULT NULL,
  `newPrjMaint` int(9) DEFAULT NULL,
  `oldPrjMaint` int(9) DEFAULT NULL,
  `newPrjTemp` int(9) DEFAULT NULL,
  `oldPrjTemp` int(9) DEFAULT NULL,
  `oldPrjPcb` int(9) DEFAULT NULL,
  `newPrjPcb` int(9) DEFAULT NULL,
  `newDocWrite` int(9) DEFAULT NULL,
  `oldDocWrite` int(9) DEFAULT NULL,
  `newCodeDesign` int(9) DEFAULT NULL,
  `oldCodeDesign` int(9) DEFAULT NULL,
  `oldPartName` varchar(255) DEFAULT NULL,
  `newPartName` varchar(255) DEFAULT NULL,
  `oldPartner` varchar(255) DEFAULT NULL,
  `newPartner` varchar(255) DEFAULT NULL,
  `oldJXVal` varchar(255) DEFAULT NULL,
  `newJXVal` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`changeid`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_reschange
-- ----------------------------
INSERT INTO `crm_reschange` VALUES ('0000001', '0000001', '0000027', '0000001', '1496907650', null, null, '1416153600', '1496731714', null, null, '1231231', '121', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `crm_reschange` VALUES ('0000002', '0000001', '0000027', '0000001', '1496910235', null, null, null, null, null, null, '1231231', '232', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `crm_reschange` VALUES ('0000003', '0000001', '0000027', '0000001', '1496910376', null, null, null, null, null, null, '1231231', '232', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `crm_reschange` VALUES ('0000004', '0000001', '0000026', '0000004', '1497060351', '0000001', '0000004', '1416153600', '1498269367', '123123', '服务器配置选择考虑问题：并发，数据库存储空间，数据库性能，缓存空间', '1231231', '88888888', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `crm_reschange` VALUES ('0000005', '0000001', '0000026', '0000001', '1497060589', null, null, '1498269367', '1506650965', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `crm_reschange` VALUES ('0000008', '0000001', '0000027', '0000002', '1497062258', null, null, '1416153600', '1501295212', '东方华润', '数据库安全主要是防止sql注入', '232', '666666', null, null, null, null, null, null, null, null, null, null, null, null, '[\"\\u9a6c\\u65ed\",\"\\u675c\\u4f1f\\u6d9b\"]', '[\"\\u9a6c\\u65ed\",\"\\u675c\\u4f1f\\u6d9b\"]', '[\"0000001\",\"0000004\"]', '[\"0000001\",\"0000004\"]', '[\"70\",\"30\"]', '[\"30\",\"70\"]');
INSERT INTO `crm_reschange` VALUES ('0000009', '0000001', '0000027', '0000002', '1497062742', null, null, '1501295212', '1500432305', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '[\"\\u9a6c\\u65ed\",\"\\u675c\\u4f1f\\u6d9b\"]', '[\"\\u9a6c\\u65ed\",\"\\u675c\\u4f1f\\u6d9b\"]', '[\"0000001\",\"0000004\"]', '[\"0000001\",\"0000004\"]', '[\"12\",\"88\"]', '[\"30\",\"70\"]');
INSERT INTO `crm_reschange` VALUES ('0000010', '0000001', '0000026', '0000004', '1497071908', null, null, '1506650965', '1497331097', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '[\"\\u9a6c\\u65ed\",\"\\u675c\\u4f1f\\u6d9b\"]', '[\"\\u9a6c\\u65ed\",\"\\u675c\\u4f1f\\u6d9b\"]', '[\"0000001\",\"0000004\"]', '[\"0000001\",\"0000004\"]', '[\"12\",\"88\"]', '[\"22\",\"78\"]');
INSERT INTO `crm_reschange` VALUES ('0000011', '0000001', '0000050', '0000003', '1498285536', null, null, '1498752000', '1499443200', '测试研发部门权限5', '测试研发部门权限新需求', '132112', '243', '0', '132112', '21', '0', '222', '0', null, null, null, null, null, null, '[\"\\u78143\\u7ec41\",\"\\u78143\\u5de5\\u7a0b\\u5e08\"]', '[\"\\u78143\\u7ec41\"]', '[\"0000018\",\"0000019\"]', '[\"0000018\"]', '[\"50\",\"50\"]', '[\"100\"]');

-- ----------------------------
-- Table structure for crm_research
-- ----------------------------
DROP TABLE IF EXISTS `crm_research`;
CREATE TABLE `crm_research` (
  `proid` int(7) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '项目编号',
  `customerid` int(10) unsigned zerofill NOT NULL COMMENT '项目客户id',
  `proname` varchar(30) NOT NULL COMMENT '项目名称',
  `projectgroup` tinyint(3) unsigned zerofill NOT NULL COMMENT '研发组',
  `projectDepartment` tinyint(3) unsigned zerofill NOT NULL,
  `builderid` int(7) unsigned zerofill NOT NULL COMMENT '立项人id',
  `responsibleid` int(7) unsigned zerofill DEFAULT NULL COMMENT '主管id负责人',
  `proprice` int(11) unsigned DEFAULT NULL COMMENT '研发费',
  `docwrite` int(10) unsigned DEFAULT NULL COMMENT '文档撰写',
  `codedesign` int(10) unsigned DEFAULT NULL COMMENT '代码设计费',
  `propcb` int(10) unsigned DEFAULT NULL COMMENT 'PCB设计费',
  `protemp` int(10) unsigned DEFAULT NULL COMMENT '临时调整费',
  `promaint` int(10) unsigned DEFAULT NULL COMMENT '维护费',
  `prodtime` int(10) unsigned DEFAULT NULL COMMENT '内部验收时间',
  `protime` int(10) unsigned NOT NULL COMMENT '立项时间',
  `finaltime` int(10) unsigned DEFAULT NULL COMMENT '实际完成时间',
  `deliverytime` int(10) unsigned NOT NULL COMMENT '项目截止时间',
  `prostatus` enum('4','3','2','1') NOT NULL DEFAULT '1' COMMENT '1进展中 2内部验收 3完成 4失败',
  `proneeds` varchar(255) NOT NULL COMMENT '需求详情',
  `auditorid` int(7) unsigned zerofill NOT NULL COMMENT '审核人id',
  `auditstatus` enum('3','2','1','4') NOT NULL DEFAULT '1' COMMENT '审核状态4:false;1:指定审核人未审核;2总经理未审核;3:通过;',
  `performbonus` int(10) unsigned NOT NULL COMMENT '绩效',
  `addtime` int(10) unsigned NOT NULL COMMENT '申请项目时间记录',
  PRIMARY KEY (`proid`),
  UNIQUE KEY `proname` (`proname`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_research
-- ----------------------------
INSERT INTO `crm_research` VALUES ('0000026', '0000000002', '机房的精神力', '000', '002', '0000001', '0000004', '12313', null, null, null, null, null, '1497945312', '1415635200', null, '1497331097', '3', '服务器配置选择考虑问题：并发，数据库存储空间，数据库性能，缓存空间', '0000004', '3', '12313', '0');
INSERT INTO `crm_research` VALUES ('0000027', '0000000001', '定时关机', '000', '002', '0000001', '0000004', '21312', null, null, null, null, null, '1497945327', '1415635200', '1497950420', '1500432305', '3', '数据库安全主要是防止sql注入', '0000002', '3', '666666', '0');
INSERT INTO `crm_research` VALUES ('0000028', '0000000001', '绯闻绯闻', '000', '003', '0000001', '0000002', '123123', null, null, null, null, null, null, '1415635200', null, '1416153600', '1', '今天一见如故', '0000004', '3', '123123', '0');
INSERT INTO `crm_research` VALUES ('0000030', '0000000002', '2342', '000', '002', '0000001', '0000004', '3424', null, null, null, null, null, '1497950476', '1415635200', '1498266719', '1416153600', '3', '234234', '0000004', '3', '23423', '0');
INSERT INTO `crm_research` VALUES ('0000031', '0000000001', '测试用1', '000', '002', '0000001', '0000004', '213123', null, null, null, null, null, null, '1496246400', null, '1498752000', '1', '测试用1', '0000010', '3', '1231231', '0');
INSERT INTO `crm_research` VALUES ('0000032', '0000000004', '212', '000', '002', '0000001', '0000004', '1213', null, '0', '0', '0', '123', null, '1497854128', null, '1498804530', '1', '1212', '0000008', '3', '1336', '0');
INSERT INTO `crm_research` VALUES ('0000033', '0000000001', '新风项目', '000', '003', '0000001', '0000002', '123123', null, '0', '0', '222', '12', null, '1497854717', null, '1498200318', '1', '嗷嗷', '0000006', '1', '123357', '0');
INSERT INTO `crm_research` VALUES ('0000034', '0000000005', 'xxxproject', '000', '003', '0000001', '0000008', '12311', null, '0', '0', '0', '0', null, '1497941240', null, '1497941243', '1', '范德萨', '0000006', '3', '12311', '0');
INSERT INTO `crm_research` VALUES ('0000035', '0000000005', '手动阀', '000', '005', '0000001', '0000007', '1231', null, '0', '0', '0', '1', null, '1498028389', null, '1498028395', '1', '', '0000006', '1', '1232', '0');
INSERT INTO `crm_research` VALUES ('0000036', '0000000003', 'doc', '000', '006', '0000001', '0000005', '1213', '1231', '0', '0', '0', '0', null, '1497864296', null, '1498814699', '1', 'doc test', '0000006', '3', '2444', '0');
INSERT INTO `crm_research` VALUES ('0000037', '0000000002', '新系统测试', '021', '006', '0000001', null, '13123', '0', '0', '0', '0', '12321', null, '1498147200', null, '1498838400', '1', '这是测试', '0000003', '3', '25444', '1498189845');
INSERT INTO `crm_research` VALUES ('0000038', '0000000002', '测试跳转', '021', '006', '0000001', null, '222222', '0', '0', '0', '0', '2123', null, '1498147200', null, '1498492800', '1', '这次怎么样', '0000003', '3', '224345', '1498189992');
INSERT INTO `crm_research` VALUES ('0000039', '0000000253', '测试研发部门权限', '017', '005', '0000021', null, '12312', '0', '0', '0', '0', '1212', null, '1498147200', null, '1499443200', '1', '测试研发部门权限', '0000003', '3', '13524', '1498195451');
INSERT INTO `crm_research` VALUES ('0000040', '0000000253', '测试研发部门权限2', '017', '005', '0000021', null, '2312', '0', '0', '0', '1231231', '123123', null, '1498147200', null, '1499443200', '1', '测试研发部门权限1', '0000003', '3', '1356666', '1498195496');
INSERT INTO `crm_research` VALUES ('0000047', '0000000253', '测试研发部门权限3', '017', '005', '0000021', null, '213121', '0', '0', '0', '0', '11212', null, '1498060800', null, '1499356800', '1', '测试研发部门权限1', '0000003', '3', '224333', '1498195753');
INSERT INTO `crm_research` VALUES ('0000048', '0000000253', '测试研发部门权限4', '017', '005', '0000021', null, '21321', '0', '0', '0', '0', '0', '1498267042', '1498147200', null, '1500393600', '2', '测试研发部门权限3', '0000003', '3', '21321', '1498195973');
INSERT INTO `crm_research` VALUES ('0000049', '0000000253', '测试研发部门权限5', '017', '005', '0000021', null, '21312', '0', '0', '0', '0', '0', null, '1498147200', null, '1498752000', '1', '测试研发部门权限5', '0000003', '3', '21312', '1498196527');
INSERT INTO `crm_research` VALUES ('0000050', '0000000253', '测试研发部门权限6', '017', '005', '0000021', null, '0', '0', '0', '0', '222', '21', null, '1497974400', null, '1499443200', '1', '测试研发部门权限新需求', '0000003', '1', '243', '1498196606');
INSERT INTO `crm_research` VALUES ('0000051', '0000000253', '测试项目权限', '017', '005', '0000021', null, '2131', '0', '0', '0', '0', '0', null, '1498752000', null, '1499356800', '1', '21323', '0000003', '3', '2131', '1498196733');

-- ----------------------------
-- Table structure for crm_resjixiao
-- ----------------------------
DROP TABLE IF EXISTS `crm_resjixiao`;
CREATE TABLE `crm_resjixiao` (
  `jxid` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '绩效id',
  `prjid` int(7) unsigned zerofill NOT NULL COMMENT '项目编号',
  `pid` int(7) unsigned zerofill NOT NULL COMMENT '参与人id',
  `jxval` int(3) unsigned NOT NULL,
  `pname` varchar(6) NOT NULL COMMENT '参与人姓名',
  `status` enum('3','2','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`jxid`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_resjixiao
-- ----------------------------
INSERT INTO `crm_resjixiao` VALUES ('0000000007', '0000028', '0000002', '100', '沈立明', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000012', '0000027', '0000001', '30', '马旭', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000013', '0000027', '0000004', '70', '杜伟涛', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000014', '0000026', '0000001', '22', '马旭', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000015', '0000026', '0000004', '78', '杜伟涛', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000016', '0000030', '0000001', '22', '马旭', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000017', '0000030', '0000004', '78', '杜伟涛', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000018', '0000031', '0000001', '40', '马旭', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000019', '0000031', '0000004', '60', '杜伟涛', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000020', '0000032', '0000001', '12', '马旭', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000021', '0000032', '0000004', '88', '杜伟涛', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000022', '0000033', '0000002', '100', '沈立明', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000023', '0000034', '0000002', '100', '沈立明', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000024', '0000035', '0000007', '100', '项目审核人3', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000025', '0000036', '0000005', '100', '项审1', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000026', '0000037', '0000001', '12', '马旭', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000027', '0000037', '0000017', '88', '经理1', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000028', '0000038', '0000001', '50', '马旭', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000029', '0000038', '0000017', '50', '经理1', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000030', '0000039', '0000018', '10', '研3组1', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000031', '0000039', '0000019', '10', '研3工程师', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000032', '0000039', '0000020', '80', '研3经理', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000033', '0000040', '0000020', '100', '研3经理', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000034', '0000047', '0000018', '100', '研3组1', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000035', '0000048', '0000019', '100', '研3工程师', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000036', '0000049', '0000018', '50', '研3组1', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000037', '0000049', '0000020', '50', '研3经理', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000040', '0000051', '0000018', '10', '研3组1', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000041', '0000051', '0000020', '90', '研3经理', '1');
INSERT INTO `crm_resjixiao` VALUES ('0000000042', '0000050', '0000018', '100', '研3组1', '1');

-- ----------------------------
-- Table structure for crm_resprogress
-- ----------------------------
DROP TABLE IF EXISTS `crm_resprogress`;
CREATE TABLE `crm_resprogress` (
  `id` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `project_id` int(7) unsigned zerofill NOT NULL COMMENT '项目id',
  `prjer_id` int(7) unsigned zerofill NOT NULL COMMENT '添加人id',
  `prjcontent` varchar(2550) NOT NULL COMMENT '更新内容',
  `posttime` int(10) NOT NULL COMMENT '发送时间',
  `audistatus` enum('3','2','1') NOT NULL DEFAULT '1' COMMENT '1:未审核；2：有效；3：无效',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_resprogress
-- ----------------------------
INSERT INTO `crm_resprogress` VALUES ('00000000001', '0000027', '0000001', 'dffasdf', '1496743967', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000002', '0000027', '0000001', '编写目的编写本项目进度报告的目的在于更好的控制软件开发的时间,对团队成员的 开发进度作出一个合理的比对', '1496743992', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000003', '0000027', '0000001', '搭噶尔', '1496747956', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000004', '0000027', '0000001', '讽德诵功翁文', '1496747966', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000005', '0000027', '0000001', '的范围违法违法违法是的撒发的发生', '1496747990', '3');
INSERT INTO `crm_resprogress` VALUES ('00000000006', '0000027', '0000001', '飞大神噶啥东方闪电', '1496748078', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000007', '0000027', '0000001', '第三方', '1496748329', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000008', '0000027', '0000001', '峰', '1496748353', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000009', '0000027', '0000001', '奋斗', '1496748443', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000010', '0000027', '0000001', '都是', '1496748539', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000011', '0000027', '0000001', '这是咋回事啊，test1', '1496748566', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000012', '0000027', '0000001', '格瑞特郭文', '1496748597', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000013', '0000027', '0000001', '的发改委顾问服务', '1496748616', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000014', '0000027', '0000001', '佛挡杀佛', '1496748670', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000015', '0000027', '0000001', '范德萨的', '1496748700', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000016', '0000027', '0000001', '菲尔德多test2', '1496749280', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000017', '0000027', '0000001', '手动阀', '1496749319', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000018', '0000027', '0000001', '各个', '1496749412', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000019', '0000027', '0000001', '21', '1496749422', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000020', '0000027', '0000001', 'fd', '1496749431', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000021', '0000027', '0000001', '单身狗的', '1496749445', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000022', '0000027', '0000001', '是的发送', '1496749722', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000023', '0000027', '0000001', '发的发额', '1496749969', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000024', '0000027', '0000001', '而非', '1496749979', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000025', '0000026', '0000001', '分为粉', '1496749987', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000026', '0000027', '0000001', '分为粉富翁', '1496750086', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000027', '0000027', '0000001', 'dfgs', '1496750090', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000028', '0000027', '0000001', '再不行报警test', '1496750634', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000029', '0000026', '0000001', '别搞我了test', '1496750847', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000030', '0000027', '0000001', '分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围', '1496750914', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000031', '0000027', '0000001', '分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而', '1496750941', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000032', '0000027', '0000001', '分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围', '1496751229', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000033', '0000027', '0000001', '份儿饭', '1496753984', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000034', '0000027', '0000001', '发给色粉', '1496753988', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000035', '0000027', '0000001', '更让他看见，没法', '1496753993', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000036', '0000026', '0000001', 'ehrjyukjl.khmyhrgfe', '1496753998', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000037', '0000026', '0000001', '&lt;script&gt;', '1496754009', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000038', '0000028', '0000001', 'select * from  where 1=1', '1496754041', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000039', '0000026', '0000001', '终于搞定了', '1496985672', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000040', '0000027', '0000001', '终于搞定了', '1496986003', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000041', '0000027', '0000001', '&lt;script&gt;alert()&lt;/script&gt;', '1497768000', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000042', '0000030', '0000001', '0620Test', '1497922274', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000043', '0000027', '0000001', 'ajax测试', '1497922639', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000044', '0000027', '0000001', 'layer.ajaxtest\n', '1497922683', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000045', '0000030', '0000001', 'test\n', '1497922864', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000046', '0000026', '0000001', 'test 弹出', '1497922890', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000047', '0000030', '0000001', '防守打法', '1497923212', '3');
INSERT INTO `crm_resprogress` VALUES ('00000000048', '0000030', '0000001', 'test刷新', '1497923262', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000049', '0000030', '0000001', '法', '1497923287', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000050', '0000026', '0000001', '这是测试弹出信息时间', '1497923482', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000051', '0000026', '0000001', '测试', '1497923493', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000052', '0000030', '0000001', '试试', '1497923845', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000053', '0000034', '0000002', 'ceshi', '1497929106', '3');
INSERT INTO `crm_resprogress` VALUES ('00000000054', '0000047', '0000018', '测试审核权限1', '1498210883', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000055', '0000039', '0000019', '测试审核记录的权限，普通', '1498211813', '3');
INSERT INTO `crm_resprogress` VALUES ('00000000056', '0000050', '0000019', '测试普通权限', '1498211829', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000057', '0000049', '0000020', '经理写的更新记录', '1498211973', '2');

-- ----------------------------
-- Table structure for crm_saleservice
-- ----------------------------
DROP TABLE IF EXISTS `crm_saleservice`;
CREATE TABLE `crm_saleservice` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned zerofill NOT NULL COMMENT '产品型号id',
  `addtime` varchar(10) NOT NULL COMMENT '订单号',
  `pro_description` varchar(255) NOT NULL COMMENT '问题描述',
  `pro_solve` varchar(255) NOT NULL,
  `pid` int(7) unsigned zerofill NOT NULL DEFAULT '0000001' COMMENT '客服id',
  `sstatus` enum('3','2','1') NOT NULL DEFAULT '1' COMMENT '1:未审核 2:有效记录 3：无效记录',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_saleservice
-- ----------------------------
INSERT INTO `crm_saleservice` VALUES ('0000000001', '0000000005', '1496388309', 'today\'s fail will change tomorrow\'s life,do u believe it?', 'guess ?', '0000001', '1');
INSERT INTO `crm_saleservice` VALUES ('0000000002', '0000000005', '1496388419', 'hahhah ,这个东西有用嘛', '当然啊，有用，u worth it', '0000001', '3');
INSERT INTO `crm_saleservice` VALUES ('0000000003', '0000000005', '1496388676', 'sdf', 'asdf', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000004', '0000000005', '1496390881', 'coding much better than before ,how?', 'predict the value', '0000001', '1');
INSERT INTO `crm_saleservice` VALUES ('0000000005', '0000000002', '1497404469', 'test1', 'test2', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000006', '0000000002', '1497578457', 'test', 'test', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000007', '0000000002', '1497578462', 'test', 'test', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000008', '0000000002', '1497578466', 'test', 'test', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000009', '0000000002', '1497578471', 'test', 'test', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000010', '0000000002', '1497578475', 'test', 'test', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000011', '0000000004', '1498031094', '特殊test1', 'test2', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000012', '0000000004', '1498612960', '修改审核权限功能', '1231', '0000001', '3');
INSERT INTO `crm_saleservice` VALUES ('0000000013', '0000000004', '1498612976', '测试审核权限', '123', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000014', '0000000004', '1498612987', '测试审核权限2', '123', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000015', '0000000007', '1498628510', '测权限', '测试\r\n', '0000001', '2');

-- ----------------------------
-- Table structure for crm_staff
-- ----------------------------
DROP TABLE IF EXISTS `crm_staff`;
CREATE TABLE `crm_staff` (
  `id` int(7) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '员工id',
  `name` varchar(8) NOT NULL COMMENT '姓名',
  `pwd` varchar(100) NOT NULL COMMENT '密码',
  `username` varchar(20) NOT NULL COMMENT '昵称（登录名）',
  `entrytime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '入职时间',
  `salt` varchar(60) NOT NULL,
  `macid` varchar(20) DEFAULT NULL COMMENT '物理地址',
  `roleid` enum('5','4','3','2','1') NOT NULL DEFAULT '2' COMMENT '1超管2普通3项目审核4客户审核',
  `deptid` int(3) unsigned zerofill NOT NULL COMMENT '职位id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_staff
-- ----------------------------
INSERT INTO `crm_staff` VALUES ('0000002', '3组长', '$2y$16$anFPREExR01TUk5JNEt4Z.3B7o2SUr78gbD1AYKhfFOiZ6cqoVz62', 'newTest', '2017-06-22 16:08:59', 'jqODA1GMSRNI4KxdAxHmQ6Xtjtfy8vYbtXVOPA+arTA=', '', '2', '058');
INSERT INTO `crm_staff` VALUES ('0000001', '马旭', '$2y$16$ZXMzdGh6ZVMwOEFwZzFRYezvQIulHN.ghDnm9zIklGYRCheZuWAGa', 'maxu', '2017-06-22 16:09:02', 'es3thzeS08Apg1QbE9cWuIzLfPoFA/5a/wDMKgVe6hQ=', 'B870F4A00630', '1', '065');
INSERT INTO `crm_staff` VALUES ('0000003', '项审1', '$2y$16$bjRubC9VZk0wbS9XVVFaQevtgzAkkgWmjvmfVAQgabCTwy0MU/ERi', 'test1', '2017-06-23 10:02:05', 'n4nl/UfM0m/WUQZBpd93l2SZsiOJpumLGr3zcN7ZJFU=', '', '3', '042');
INSERT INTO `crm_staff` VALUES ('0000017', '经理1', '$2y$16$SXh4dzVNWHRCbG9jbkxjZuHqUf43Ost/7oK18YcXezv3rMCEOtTam', 'newTest1', '2017-06-22 00:00:00', 'Ixxw5MXtBlocnLcgzdf3nYZVwkUaLAvQa7s0n66Hfgc=', '', '2', '063');
INSERT INTO `crm_staff` VALUES ('0000018', '研3组1', '$2y$16$R2hxNUROTy9KQ1M1blRILuIThttW0C.JIAPUuQxjzI5SY7eZauNEu', 'ceshi1', '2017-06-23 00:00:00', 'Ghq5DNO/JCS5nTH/82R08De0/2ASnvRybd7EDm7ZGrI=', '', '2', '052');
INSERT INTO `crm_staff` VALUES ('0000019', '研3工程师', '$2y$16$MENwYnZuem4rOGIzYWE2UOSHki.UHuBjg.bHgeV0lBCzX0YXMmCee', 'ceshi2', '2017-06-23 13:30:19', '0Cpbvnzn+8b3aa6QKYywT13tUIDsh8C26eX8b3w+qmY=', '', '2', '053');
INSERT INTO `crm_staff` VALUES ('0000020', '研3经理', '$2y$16$YlkrRGpJZVNRZmhlVlZ1ROZ1Nk5cFTnChl3h85cxFBbatKXQ2aBeG', 'ceshi3', '2017-06-23 13:16:04', 'bY+DjIeSQfheVVuEGSKQthfSv0Mrgu/T6rnaiD06eAY=', '', '2', '051');
INSERT INTO `crm_staff` VALUES ('0000021', '销1本', '$2y$16$NnBET3N0ZWtBMGVPaGw4dOQ3LTvM8ogMZvXrC85rOOF7kGw6MgoFe', 'ceshi4', '2017-06-23 13:15:03', '6pDOstekA0eOhl8u8sHI4V7KkdcE5t5hK7yMm6jB29Q=', '', '4', '070');
INSERT INTO `crm_staff` VALUES ('0000022', 'ceshi5', '$2y$16$bjg4bm5NMFVBR2FFVFRreepzlhmT4.Isuk2djetT4KRvoe43C5tMO', 'ceshi5', '2017-06-27 00:00:00', 'n88nnM0UAGaETTkz6th1I9DmuUN3XXaThOwWvObAoac=', '', '2', '070');
INSERT INTO `crm_staff` VALUES ('0000023', 'ceshi6', '$2y$16$M1lGRTE0bStPQ1pBWmpwZ.4mbBc13ix7dUoprq2.RSjph6bO5kyDS', 'ceshi6', '2017-06-28 00:00:00', '3YFE14m+OCZAZjpd2m6dXtBPDqIhIGeKbPeSPinPs/I=', '', '2', '069');

-- ----------------------------
-- Table structure for crm_system
-- ----------------------------
DROP TABLE IF EXISTS `crm_system`;
CREATE TABLE `crm_system` (
  `id` tinyint(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `timelimit` int(10) NOT NULL COMMENT '用于设置记录查看的最长时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_system
-- ----------------------------
INSERT INTO `crm_system` VALUES ('00001', '30');

-- ----------------------------
-- Table structure for orderform
-- ----------------------------
DROP TABLE IF EXISTS `orderform`;
CREATE TABLE `orderform` (
  `oid` int(11) NOT NULL COMMENT '订单编号',
  `oname` varchar(255) NOT NULL COMMENT '订单名',
  `product_id` int(11) NOT NULL COMMENT '产品id',
  `pname` varchar(255) NOT NULL COMMENT '产品名',
  `pnum` int(10) unsigned NOT NULL COMMENT '产品数量',
  `pprice` int(11) NOT NULL COMMENT '产品单价',
  `oprice` int(11) NOT NULL COMMENT '订单价格',
  `otime` datetime NOT NULL COMMENT '订单时间',
  `deliverytime` datetime NOT NULL COMMENT '交货时间',
  `otype` enum('2','1','0') NOT NULL DEFAULT '0' COMMENT '订单类型0库存产品1定制产品2新研发产品',
  `odetail` varchar(255) NOT NULL COMMENT '订单备注、详情',
  `cid` int(11) NOT NULL COMMENT '客户编号',
  `bid` int(11) NOT NULL COMMENT '业务编号',
  `picid` int(11) NOT NULL COMMENT '负责人id',
  `authorid` int(11) NOT NULL COMMENT '审核人id',
  `authorstatus` enum('3','2','1','0') NOT NULL DEFAULT '1' COMMENT '审核状态0不通过1未审核2部门通过3总经理通过',
  PRIMARY KEY (`oid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of orderform
-- ----------------------------
