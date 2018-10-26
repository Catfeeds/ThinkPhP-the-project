/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:03:41
*/

SET FOREIGN_KEY_CHECKS=0;

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
