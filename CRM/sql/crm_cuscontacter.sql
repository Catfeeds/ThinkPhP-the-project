/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:03:34
*/

SET FOREIGN_KEY_CHECKS=0;

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
