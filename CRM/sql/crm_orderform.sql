/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:04:01
*/

SET FOREIGN_KEY_CHECKS=0;

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
