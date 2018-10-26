/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:04:05
*/

SET FOREIGN_KEY_CHECKS=0;

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
