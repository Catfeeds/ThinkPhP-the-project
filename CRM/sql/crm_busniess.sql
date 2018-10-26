/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:03:20
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
