/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-04 10:12:12
*/

SET FOREIGN_KEY_CHECKS=0;

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
