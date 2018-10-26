/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:04:09
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crm_research
-- ----------------------------
DROP TABLE IF EXISTS `crm_research`;
CREATE TABLE `crm_research` (
  `proid` int(7) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '项目编号',
  `customerid` int(10) unsigned zerofill NOT NULL COMMENT '项目客户id',
  `proname` varchar(30) NOT NULL COMMENT '项目名称',
  `projectgroup` tinyint(3) unsigned zerofill NOT NULL COMMENT '研发组',
  `projectDepartment` tinyint(3) unsigned zerofill NOT NULL,
  `builderid` int(7) unsigned zerofill NOT NULL COMMENT '立项人id',
  `responsibleid` int(7) unsigned zerofill DEFAULT NULL COMMENT '主管id负责人',
  `proprice` int(11) unsigned DEFAULT NULL COMMENT '研发费',
  `docwrite` int(10) unsigned DEFAULT NULL COMMENT '文档撰写',
  `codedesign` int(10) unsigned DEFAULT NULL COMMENT '代码设计费',
  `propcb` int(10) unsigned DEFAULT NULL COMMENT 'PCB设计费',
  `protemp` int(10) unsigned DEFAULT NULL COMMENT '临时调整费',
  `promaint` int(10) unsigned DEFAULT NULL COMMENT '维护费',
  `prodtime` int(10) unsigned DEFAULT NULL COMMENT '内部验收时间',
  `protime` int(10) unsigned NOT NULL COMMENT '立项时间',
  `finaltime` int(10) unsigned DEFAULT NULL COMMENT '实际完成时间',
  `deliverytime` int(10) unsigned NOT NULL COMMENT '项目截止时间',
  `prostatus` enum('4','3','2','1') NOT NULL DEFAULT '1' COMMENT '1进展中 2内部验收 3完成 4失败',
  `proneeds` varchar(255) NOT NULL COMMENT '需求详情',
  `auditorid` int(7) unsigned zerofill NOT NULL COMMENT '审核人id',
  `auditstatus` enum('3','2','1','4') NOT NULL DEFAULT '1' COMMENT '审核状态4:false;1:指定审核人未审核;2总经理未审核;3:通过;',
  `performbonus` int(10) unsigned NOT NULL COMMENT '绩效',
  `addtime` int(10) unsigned NOT NULL COMMENT '申请项目时间记录',
  PRIMARY KEY (`proid`),
  UNIQUE KEY `proname` (`proname`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_research
-- ----------------------------
INSERT INTO `crm_research` VALUES ('0000026', '0000000002', '机房的精神力', '000', '002', '0000001', '0000004', '12313', null, null, null, null, null, '1497945312', '1415635200', null, '1497331097', '3', '服务器配置选择考虑问题：并发，数据库存储空间，数据库性能，缓存空间', '0000004', '3', '12313', '0');
INSERT INTO `crm_research` VALUES ('0000027', '0000000001', '定时关机', '000', '002', '0000001', '0000004', '21312', null, null, null, null, null, '1497945327', '1415635200', '1497950420', '1500432305', '3', '数据库安全主要是防止sql注入', '0000002', '3', '666666', '0');
INSERT INTO `crm_research` VALUES ('0000028', '0000000001', '绯闻绯闻', '000', '003', '0000001', '0000002', '123123', null, null, null, null, null, null, '1415635200', null, '1416153600', '1', '今天一见如故', '0000004', '3', '123123', '0');
INSERT INTO `crm_research` VALUES ('0000030', '0000000002', '2342', '000', '002', '0000001', '0000004', '3424', null, null, null, null, null, '1497950476', '1415635200', '1498266719', '1416153600', '3', '234234', '0000004', '3', '23423', '0');
INSERT INTO `crm_research` VALUES ('0000031', '0000000001', '测试用1', '000', '002', '0000001', '0000004', '213123', null, null, null, null, null, null, '1496246400', null, '1498752000', '1', '测试用1', '0000010', '3', '1231231', '0');
INSERT INTO `crm_research` VALUES ('0000032', '0000000004', '212', '000', '002', '0000001', '0000004', '1213', null, '0', '0', '0', '123', null, '1497854128', null, '1498804530', '1', '1212', '0000008', '3', '1336', '0');
INSERT INTO `crm_research` VALUES ('0000033', '0000000001', '新风项目', '000', '003', '0000001', '0000002', '123123', null, '0', '0', '222', '12', null, '1497854717', null, '1498200318', '1', '嗷嗷', '0000006', '1', '123357', '0');
INSERT INTO `crm_research` VALUES ('0000034', '0000000005', 'xxxproject', '000', '003', '0000001', '0000008', '12311', null, '0', '0', '0', '0', null, '1497941240', null, '1497941243', '1', '范德萨', '0000006', '3', '12311', '0');
INSERT INTO `crm_research` VALUES ('0000035', '0000000005', '手动阀', '000', '005', '0000001', '0000007', '1231', null, '0', '0', '0', '1', null, '1498028389', null, '1498028395', '1', '', '0000006', '1', '1232', '0');
INSERT INTO `crm_research` VALUES ('0000036', '0000000003', 'doc', '000', '006', '0000001', '0000005', '1213', '1231', '0', '0', '0', '0', null, '1497864296', null, '1498814699', '1', 'doc test', '0000006', '3', '2444', '0');
INSERT INTO `crm_research` VALUES ('0000037', '0000000002', '新系统测试', '021', '006', '0000001', null, '13123', '0', '0', '0', '0', '12321', null, '1498147200', null, '1498838400', '1', '这是测试', '0000003', '3', '25444', '1498189845');
INSERT INTO `crm_research` VALUES ('0000038', '0000000002', '测试跳转', '021', '006', '0000001', null, '222222', '0', '0', '0', '0', '2123', null, '1498147200', null, '1498492800', '1', '这次怎么样', '0000003', '3', '224345', '1498189992');
INSERT INTO `crm_research` VALUES ('0000039', '0000000253', '测试研发部门权限', '017', '005', '0000021', null, '12312', '0', '0', '0', '0', '1212', null, '1498147200', null, '1499443200', '1', '测试研发部门权限', '0000003', '3', '13524', '1498195451');
INSERT INTO `crm_research` VALUES ('0000040', '0000000253', '测试研发部门权限2', '017', '005', '0000021', null, '2312', '0', '0', '0', '1231231', '123123', null, '1498147200', null, '1499443200', '1', '测试研发部门权限1', '0000003', '3', '1356666', '1498195496');
INSERT INTO `crm_research` VALUES ('0000047', '0000000253', '测试研发部门权限3', '017', '005', '0000021', null, '213121', '0', '0', '0', '0', '11212', null, '1498060800', null, '1499356800', '1', '测试研发部门权限1', '0000003', '3', '224333', '1498195753');
INSERT INTO `crm_research` VALUES ('0000048', '0000000253', '测试研发部门权限4', '017', '005', '0000021', null, '21321', '0', '0', '0', '0', '0', '1498267042', '1498147200', null, '1500393600', '2', '测试研发部门权限3', '0000003', '3', '21321', '1498195973');
INSERT INTO `crm_research` VALUES ('0000049', '0000000253', '测试研发部门权限5', '017', '005', '0000021', null, '21312', '0', '0', '0', '0', '0', null, '1498147200', null, '1498752000', '1', '测试研发部门权限5', '0000003', '3', '21312', '1498196527');
INSERT INTO `crm_research` VALUES ('0000050', '0000000253', '测试研发部门权限6', '017', '005', '0000021', null, '0', '0', '0', '0', '222', '21', null, '1497974400', null, '1499443200', '1', '测试研发部门权限新需求', '0000003', '1', '243', '1498196606');
INSERT INTO `crm_research` VALUES ('0000051', '0000000253', '测试项目权限', '017', '005', '0000021', null, '2131', '0', '0', '0', '0', '0', null, '1498752000', null, '1499356800', '1', '21323', '0000003', '3', '2131', '1498196733');
