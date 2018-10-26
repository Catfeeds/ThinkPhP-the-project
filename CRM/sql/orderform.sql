/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:04:39
*/

SET FOREIGN_KEY_CHECKS=0;

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
