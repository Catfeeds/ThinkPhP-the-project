/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:04:14
*/

SET FOREIGN_KEY_CHECKS=0;

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
