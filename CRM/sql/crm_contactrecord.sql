/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:03:29
*/

SET FOREIGN_KEY_CHECKS=0;

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
