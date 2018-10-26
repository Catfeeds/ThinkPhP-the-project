/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:03:56
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crm_onlineservice
-- ----------------------------
DROP TABLE IF EXISTS `crm_onlineservice`;
CREATE TABLE `crm_onlineservice` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '客服记录编号',
  `customer_id` int(10) unsigned zerofill NOT NULL COMMENT '客户编号',
  `content` varchar(255) NOT NULL COMMENT '客户问题',
  `answercontent` varchar(255) NOT NULL COMMENT '处理方式',
  `server_id` int(7) unsigned zerofill NOT NULL COMMENT '电话客服的id',
  `austatus` enum('2','1','3') NOT NULL DEFAULT '1' COMMENT '通话记录有效性3无效1未审核2有效',
  `addtime` int(10) NOT NULL,
  `caller` varchar(8) DEFAULT NULL COMMENT '来电人',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_onlineservice
-- ----------------------------
INSERT INTO `crm_onlineservice` VALUES ('0000000001', '0000000005', '今天天气好', '明天好天气', '0000001', '1', '1496398338', '李希');
INSERT INTO `crm_onlineservice` VALUES ('0000000002', '0000000005', 'MySQL技术内幕', '数据库防sql注入', '0000001', '1', '1496402207', 'gogo');
INSERT INTO `crm_onlineservice` VALUES ('0000000003', '0000000002', 'test2', 'test2', '0000001', '2', '1497494653', 'lily');
INSERT INTO `crm_onlineservice` VALUES ('0000000004', '0000000002', '123', '456', '0000001', '2', '1497693466', '789');
INSERT INTO `crm_onlineservice` VALUES ('0000000005', '0000000004', '审核权限修改', '123', '0000001', '1', '1498613022', '李林');
INSERT INTO `crm_onlineservice` VALUES ('0000000006', '0000000004', '测试修改权限', '测试', '0000001', '2', '1498613052', '利拉');
INSERT INTO `crm_onlineservice` VALUES ('0000000007', '0000000004', '测试接力积分', '阿斯顿发文', '0000001', '3', '1498613069', 'ldn');
INSERT INTO `crm_onlineservice` VALUES ('0000000008', '0000000007', '测权限1', '测', '0000001', '2', '1498628537', '林夕');
