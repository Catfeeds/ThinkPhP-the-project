/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:04:33
*/

SET FOREIGN_KEY_CHECKS=0;

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
