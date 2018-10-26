/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:04:22
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crm_saleservice
-- ----------------------------
DROP TABLE IF EXISTS `crm_saleservice`;
CREATE TABLE `crm_saleservice` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned zerofill NOT NULL COMMENT '产品型号id',
  `addtime` varchar(10) NOT NULL COMMENT '订单号',
  `pro_description` varchar(255) NOT NULL COMMENT '问题描述',
  `pro_solve` varchar(255) NOT NULL,
  `pid` int(7) unsigned zerofill NOT NULL DEFAULT '0000001' COMMENT '客服id',
  `sstatus` enum('3','2','1') NOT NULL DEFAULT '1' COMMENT '1:未审核 2:有效记录 3：无效记录',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_saleservice
-- ----------------------------
INSERT INTO `crm_saleservice` VALUES ('0000000001', '0000000005', '1496388309', 'today\'s fail will change tomorrow\'s life,do u believe it?', 'guess ?', '0000001', '1');
INSERT INTO `crm_saleservice` VALUES ('0000000002', '0000000005', '1496388419', 'hahhah ,这个东西有用嘛', '当然啊，有用，u worth it', '0000001', '3');
INSERT INTO `crm_saleservice` VALUES ('0000000003', '0000000005', '1496388676', 'sdf', 'asdf', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000004', '0000000005', '1496390881', 'coding much better than before ,how?', 'predict the value', '0000001', '1');
INSERT INTO `crm_saleservice` VALUES ('0000000005', '0000000002', '1497404469', 'test1', 'test2', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000006', '0000000002', '1497578457', 'test', 'test', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000007', '0000000002', '1497578462', 'test', 'test', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000008', '0000000002', '1497578466', 'test', 'test', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000009', '0000000002', '1497578471', 'test', 'test', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000010', '0000000002', '1497578475', 'test', 'test', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000011', '0000000004', '1498031094', '特殊test1', 'test2', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000012', '0000000004', '1498612960', '修改审核权限功能', '1231', '0000001', '3');
INSERT INTO `crm_saleservice` VALUES ('0000000013', '0000000004', '1498612976', '测试审核权限', '123', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000014', '0000000004', '1498612987', '测试审核权限2', '123', '0000001', '2');
INSERT INTO `crm_saleservice` VALUES ('0000000015', '0000000007', '1498628510', '测权限', '测试\r\n', '0000001', '2');
