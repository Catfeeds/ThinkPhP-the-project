/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:03:52
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crm_dept
-- ----------------------------
DROP TABLE IF EXISTS `crm_dept`;
CREATE TABLE `crm_dept` (
  `id` int(3) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '部门主键',
  `name` varchar(10) NOT NULL COMMENT '类别名称',
  `parent_id` int(3) unsigned zerofill DEFAULT NULL,
  `level` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_dept
-- ----------------------------
INSERT INTO `crm_dept` VALUES ('001', '总经理', '000', '0');
INSERT INTO `crm_dept` VALUES ('002', '研发本部', '001', '1');
INSERT INTO `crm_dept` VALUES ('003', '研发1部', '001', '1');
INSERT INTO `crm_dept` VALUES ('004', '研发2部', '001', '1');
INSERT INTO `crm_dept` VALUES ('005', '研发3部', '001', '1');
INSERT INTO `crm_dept` VALUES ('006', '研发4部', '001', '1');
INSERT INTO `crm_dept` VALUES ('007', '研发5部', '001', '1');
INSERT INTO `crm_dept` VALUES ('008', '销售1部', '001', '2');
INSERT INTO `crm_dept` VALUES ('009', '销售2部', '001', '2');
INSERT INTO `crm_dept` VALUES ('010', '销售3部', '001', '2');
INSERT INTO `crm_dept` VALUES ('011', '销售6部', '001', '2');
INSERT INTO `crm_dept` VALUES ('012', '售后服务', '001', '3');
INSERT INTO `crm_dept` VALUES ('013', '电话客服', '001', '4');
INSERT INTO `crm_dept` VALUES ('014', '研发本部', '002', '5');
INSERT INTO `crm_dept` VALUES ('015', '研发本部', '003', '5');
INSERT INTO `crm_dept` VALUES ('016', '研发本部', '004', '5');
INSERT INTO `crm_dept` VALUES ('017', '研发1组', '005', '5');
INSERT INTO `crm_dept` VALUES ('018', '研发2组', '005', '5');
INSERT INTO `crm_dept` VALUES ('019', '研发3组', '005', '5');
INSERT INTO `crm_dept` VALUES ('020', '研发4组', '005', '5');
INSERT INTO `crm_dept` VALUES ('021', '研发本部', '006', '5');
INSERT INTO `crm_dept` VALUES ('022', '研发本部', '007', '5');
INSERT INTO `crm_dept` VALUES ('023', '销售本部', '008', '5');
INSERT INTO `crm_dept` VALUES ('024', '销售1组', '008', '5');
INSERT INTO `crm_dept` VALUES ('025', '销售2组', '008', '5');
INSERT INTO `crm_dept` VALUES ('026', '销售3组', '008', '5');
INSERT INTO `crm_dept` VALUES ('027', '销售4组', '008', '5');
INSERT INTO `crm_dept` VALUES ('028', '销售5组', '008', '5');
INSERT INTO `crm_dept` VALUES ('029', '销售6组', '008', '5');
INSERT INTO `crm_dept` VALUES ('030', '销售本部', '009', '5');
INSERT INTO `crm_dept` VALUES ('031', '销售1组', '009', '5');
INSERT INTO `crm_dept` VALUES ('032', '销售2组', '009', '5');
INSERT INTO `crm_dept` VALUES ('033', '销售3组', '009', '5');
INSERT INTO `crm_dept` VALUES ('034', '销售5组', '009', '5');
INSERT INTO `crm_dept` VALUES ('035', '销售本部', '010', '5');
INSERT INTO `crm_dept` VALUES ('036', '销售本部', '011', '5');
INSERT INTO `crm_dept` VALUES ('037', '销售1组', '011', '5');
INSERT INTO `crm_dept` VALUES ('038', '销售2组', '011', '5');
INSERT INTO `crm_dept` VALUES ('039', '销售3组', '011', '5');
INSERT INTO `crm_dept` VALUES ('040', '销售4组', '011', '5');
INSERT INTO `crm_dept` VALUES ('041', '销售5组', '011', '5');
INSERT INTO `crm_dept` VALUES ('042', '研发经理', '014', '5');
INSERT INTO `crm_dept` VALUES ('043', '研发组长', '014', '5');
INSERT INTO `crm_dept` VALUES ('044', '研发工程师', '014', '5');
INSERT INTO `crm_dept` VALUES ('045', '研发经理', '015', '5');
INSERT INTO `crm_dept` VALUES ('046', '研发组长', '015', '5');
INSERT INTO `crm_dept` VALUES ('047', '研发工程师', '015', '5');
INSERT INTO `crm_dept` VALUES ('048', '研发经理', '016', '5');
INSERT INTO `crm_dept` VALUES ('049', '研发组长', '016', '5');
INSERT INTO `crm_dept` VALUES ('050', '研发工程师', '016', '5');
INSERT INTO `crm_dept` VALUES ('051', '研发经理', '017', '5');
INSERT INTO `crm_dept` VALUES ('052', '研发组长', '017', '5');
INSERT INTO `crm_dept` VALUES ('053', '研发工程师', '017', '5');
INSERT INTO `crm_dept` VALUES ('054', '研发组长', '018', '5');
INSERT INTO `crm_dept` VALUES ('055', '研发工程师', '018', '5');
INSERT INTO `crm_dept` VALUES ('056', '助理工程师', '018', '5');
INSERT INTO `crm_dept` VALUES ('057', '研发经理', '019', '5');
INSERT INTO `crm_dept` VALUES ('058', '研发组长', '019', '5');
INSERT INTO `crm_dept` VALUES ('059', '研发工程师', '019', '5');
INSERT INTO `crm_dept` VALUES ('060', '研发组长', '020', '5');
INSERT INTO `crm_dept` VALUES ('061', '研发工程师', '020', '5');
INSERT INTO `crm_dept` VALUES ('062', '助理工程师', '020', '5');
INSERT INTO `crm_dept` VALUES ('063', '研发经理', '021', '5');
INSERT INTO `crm_dept` VALUES ('064', '研发组长', '021', '5');
INSERT INTO `crm_dept` VALUES ('065', '研发工程师', '021', '5');
INSERT INTO `crm_dept` VALUES ('066', '研发组长', '022', '5');
INSERT INTO `crm_dept` VALUES ('067', '研发工程师', '022', '5');
INSERT INTO `crm_dept` VALUES ('068', '助理工程师', '022', '5');
INSERT INTO `crm_dept` VALUES ('069', '销售经理', '023', '5');
INSERT INTO `crm_dept` VALUES ('070', '销售精英', '023', '5');
INSERT INTO `crm_dept` VALUES ('071', '销售工程师', '023', '5');
INSERT INTO `crm_dept` VALUES ('072', '行政助理', '023', '5');
INSERT INTO `crm_dept` VALUES ('073', '销售组长', '024', '5');
INSERT INTO `crm_dept` VALUES ('074', '销售后备人才', '024', '5');
INSERT INTO `crm_dept` VALUES ('075', '销售工程师', '024', '5');
INSERT INTO `crm_dept` VALUES ('076', '销售组长', '025', '5');
INSERT INTO `crm_dept` VALUES ('077', '销售后备人才', '025', '5');
INSERT INTO `crm_dept` VALUES ('078', '销售组长', '026', '5');
INSERT INTO `crm_dept` VALUES ('079', '销售后备人才', '026', '5');
INSERT INTO `crm_dept` VALUES ('080', '销售组长', '027', '5');
INSERT INTO `crm_dept` VALUES ('081', '销售后备人才', '027', '5');
INSERT INTO `crm_dept` VALUES ('082', '销售组长', '028', '5');
INSERT INTO `crm_dept` VALUES ('083', '销售后备人才', '028', '5');
INSERT INTO `crm_dept` VALUES ('084', '销售工程师', '028', '5');
INSERT INTO `crm_dept` VALUES ('085', '销售组长', '029', '5');
INSERT INTO `crm_dept` VALUES ('086', '销售后备人才', '029', '5');
INSERT INTO `crm_dept` VALUES ('087', '销售经理', '030', '5');
INSERT INTO `crm_dept` VALUES ('088', '销售精英', '030', '5');
INSERT INTO `crm_dept` VALUES ('089', '销售工程师', '030', '5');
INSERT INTO `crm_dept` VALUES ('090', '行政助理', '030', '5');
INSERT INTO `crm_dept` VALUES ('091', '销售经理', '035', '5');
INSERT INTO `crm_dept` VALUES ('092', '销售精英', '035', '5');
INSERT INTO `crm_dept` VALUES ('101', '销售组长', '031', '5');
INSERT INTO `crm_dept` VALUES ('102', '销售后备人才', '031', '5');
INSERT INTO `crm_dept` VALUES ('112', '销售组长', '032', '5');
INSERT INTO `crm_dept` VALUES ('113', '销售后备人才', '032', '5');
INSERT INTO `crm_dept` VALUES ('114', '销售组长', '033', '5');
INSERT INTO `crm_dept` VALUES ('115', '销售后备人才', '033', '5');
INSERT INTO `crm_dept` VALUES ('116', '销售组长', '034', '5');
INSERT INTO `crm_dept` VALUES ('117', '销售后备人才', '034', '5');
INSERT INTO `crm_dept` VALUES ('118', '销售经理', '036', '5');
INSERT INTO `crm_dept` VALUES ('119', '销售精英', '036', '5');
INSERT INTO `crm_dept` VALUES ('120', '销售工程师', '036', '5');
INSERT INTO `crm_dept` VALUES ('121', '行政助理', '036', '5');
INSERT INTO `crm_dept` VALUES ('122', '销售组长', '037', '5');
INSERT INTO `crm_dept` VALUES ('123', '销售后备人才', '037', '5');
INSERT INTO `crm_dept` VALUES ('124', '销售组长', '038', '5');
INSERT INTO `crm_dept` VALUES ('125', '销售后备人才', '038', '5');
INSERT INTO `crm_dept` VALUES ('126', '销售组长', '039', '5');
INSERT INTO `crm_dept` VALUES ('127', '销售后备人才', '039', '5');
INSERT INTO `crm_dept` VALUES ('128', '销售组长', '040', '5');
INSERT INTO `crm_dept` VALUES ('129', '销售后备人才', '040', '5');
INSERT INTO `crm_dept` VALUES ('130', '销售组长', '041', '5');
INSERT INTO `crm_dept` VALUES ('131', '销售后备人才', '041', '5');
