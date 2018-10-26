/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:04:26
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crm_staff
-- ----------------------------
DROP TABLE IF EXISTS `crm_staff`;
CREATE TABLE `crm_staff` (
  `id` int(7) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '员工id',
  `name` varchar(8) NOT NULL COMMENT '姓名',
  `pwd` varchar(100) NOT NULL COMMENT '密码',
  `username` varchar(20) NOT NULL COMMENT '昵称（登录名）',
  `entrytime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '入职时间',
  `salt` varchar(60) NOT NULL,
  `macid` varchar(20) DEFAULT NULL COMMENT '物理地址',
  `roleid` enum('5','4','3','2','1') NOT NULL DEFAULT '2' COMMENT '1超管2普通3项目审核4客户审核',
  `deptid` int(3) unsigned zerofill NOT NULL COMMENT '职位id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_staff
-- ----------------------------
INSERT INTO `crm_staff` VALUES ('0000002', '3组长', '$2y$16$anFPREExR01TUk5JNEt4Z.3B7o2SUr78gbD1AYKhfFOiZ6cqoVz62', 'newTest', '2017-06-22 16:08:59', 'jqODA1GMSRNI4KxdAxHmQ6Xtjtfy8vYbtXVOPA+arTA=', '', '2', '058');
INSERT INTO `crm_staff` VALUES ('0000001', '马旭', '$2y$16$ZXMzdGh6ZVMwOEFwZzFRYezvQIulHN.ghDnm9zIklGYRCheZuWAGa', 'maxu', '2017-06-22 16:09:02', 'es3thzeS08Apg1QbE9cWuIzLfPoFA/5a/wDMKgVe6hQ=', 'B870F4A00630', '1', '065');
INSERT INTO `crm_staff` VALUES ('0000003', '项审1', '$2y$16$bjRubC9VZk0wbS9XVVFaQevtgzAkkgWmjvmfVAQgabCTwy0MU/ERi', 'test1', '2017-06-23 10:02:05', 'n4nl/UfM0m/WUQZBpd93l2SZsiOJpumLGr3zcN7ZJFU=', '', '3', '042');
INSERT INTO `crm_staff` VALUES ('0000017', '经理1', '$2y$16$SXh4dzVNWHRCbG9jbkxjZuHqUf43Ost/7oK18YcXezv3rMCEOtTam', 'newTest1', '2017-06-22 00:00:00', 'Ixxw5MXtBlocnLcgzdf3nYZVwkUaLAvQa7s0n66Hfgc=', '', '2', '063');
INSERT INTO `crm_staff` VALUES ('0000018', '研3组1', '$2y$16$R2hxNUROTy9KQ1M1blRILuIThttW0C.JIAPUuQxjzI5SY7eZauNEu', 'ceshi1', '2017-06-23 00:00:00', 'Ghq5DNO/JCS5nTH/82R08De0/2ASnvRybd7EDm7ZGrI=', '', '2', '052');
INSERT INTO `crm_staff` VALUES ('0000019', '研3工程师', '$2y$16$MENwYnZuem4rOGIzYWE2UOSHki.UHuBjg.bHgeV0lBCzX0YXMmCee', 'ceshi2', '2017-06-23 13:30:19', '0Cpbvnzn+8b3aa6QKYywT13tUIDsh8C26eX8b3w+qmY=', '', '2', '053');
INSERT INTO `crm_staff` VALUES ('0000020', '研3经理', '$2y$16$YlkrRGpJZVNRZmhlVlZ1ROZ1Nk5cFTnChl3h85cxFBbatKXQ2aBeG', 'ceshi3', '2017-06-23 13:16:04', 'bY+DjIeSQfheVVuEGSKQthfSv0Mrgu/T6rnaiD06eAY=', '', '2', '051');
INSERT INTO `crm_staff` VALUES ('0000021', '销1本', '$2y$16$NnBET3N0ZWtBMGVPaGw4dOQ3LTvM8ogMZvXrC85rOOF7kGw6MgoFe', 'ceshi4', '2017-06-23 13:15:03', '6pDOstekA0eOhl8u8sHI4V7KkdcE5t5hK7yMm6jB29Q=', '', '4', '070');
INSERT INTO `crm_staff` VALUES ('0000022', 'ceshi5', '$2y$16$bjg4bm5NMFVBR2FFVFRreepzlhmT4.Isuk2djetT4KRvoe43C5tMO', 'ceshi5', '2017-06-27 00:00:00', 'n88nnM0UAGaETTkz6th1I9DmuUN3XXaThOwWvObAoac=', '', '2', '070');
INSERT INTO `crm_staff` VALUES ('0000023', 'ceshi6', '$2y$16$M1lGRTE0bStPQ1pBWmpwZ.4mbBc13ix7dUoprq2.RSjph6bO5kyDS', 'ceshi6', '2017-06-28 00:00:00', '3YFE14m+OCZAZjpd2m6dXtBPDqIhIGeKbPeSPinPs/I=', '', '2', '069');
