/*
Navicat MySQL Data Transfer

Source Server         : 1
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : dwin

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-03 21:04:18
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crm_resprogress
-- ----------------------------
DROP TABLE IF EXISTS `crm_resprogress`;
CREATE TABLE `crm_resprogress` (
  `id` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `project_id` int(7) unsigned zerofill NOT NULL COMMENT '项目id',
  `prjer_id` int(7) unsigned zerofill NOT NULL COMMENT '添加人id',
  `prjcontent` varchar(2550) NOT NULL COMMENT '更新内容',
  `posttime` int(10) NOT NULL COMMENT '发送时间',
  `audistatus` enum('3','2','1') NOT NULL DEFAULT '1' COMMENT '1:未审核；2：有效；3：无效',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of crm_resprogress
-- ----------------------------
INSERT INTO `crm_resprogress` VALUES ('00000000001', '0000027', '0000001', 'dffasdf', '1496743967', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000002', '0000027', '0000001', '编写目的编写本项目进度报告的目的在于更好的控制软件开发的时间,对团队成员的 开发进度作出一个合理的比对', '1496743992', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000003', '0000027', '0000001', '搭噶尔', '1496747956', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000004', '0000027', '0000001', '讽德诵功翁文', '1496747966', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000005', '0000027', '0000001', '的范围违法违法违法是的撒发的发生', '1496747990', '3');
INSERT INTO `crm_resprogress` VALUES ('00000000006', '0000027', '0000001', '飞大神噶啥东方闪电', '1496748078', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000007', '0000027', '0000001', '第三方', '1496748329', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000008', '0000027', '0000001', '峰', '1496748353', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000009', '0000027', '0000001', '奋斗', '1496748443', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000010', '0000027', '0000001', '都是', '1496748539', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000011', '0000027', '0000001', '这是咋回事啊，test1', '1496748566', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000012', '0000027', '0000001', '格瑞特郭文', '1496748597', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000013', '0000027', '0000001', '的发改委顾问服务', '1496748616', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000014', '0000027', '0000001', '佛挡杀佛', '1496748670', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000015', '0000027', '0000001', '范德萨的', '1496748700', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000016', '0000027', '0000001', '菲尔德多test2', '1496749280', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000017', '0000027', '0000001', '手动阀', '1496749319', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000018', '0000027', '0000001', '各个', '1496749412', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000019', '0000027', '0000001', '21', '1496749422', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000020', '0000027', '0000001', 'fd', '1496749431', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000021', '0000027', '0000001', '单身狗的', '1496749445', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000022', '0000027', '0000001', '是的发送', '1496749722', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000023', '0000027', '0000001', '发的发额', '1496749969', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000024', '0000027', '0000001', '而非', '1496749979', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000025', '0000026', '0000001', '分为粉', '1496749987', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000026', '0000027', '0000001', '分为粉富翁', '1496750086', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000027', '0000027', '0000001', 'dfgs', '1496750090', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000028', '0000027', '0000001', '再不行报警test', '1496750634', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000029', '0000026', '0000001', '别搞我了test', '1496750847', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000030', '0000027', '0000001', '分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围', '1496750914', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000031', '0000027', '0000001', '分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而', '1496750941', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000032', '0000027', '0000001', '分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围分为而范围范围', '1496751229', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000033', '0000027', '0000001', '份儿饭', '1496753984', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000034', '0000027', '0000001', '发给色粉', '1496753988', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000035', '0000027', '0000001', '更让他看见，没法', '1496753993', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000036', '0000026', '0000001', 'ehrjyukjl.khmyhrgfe', '1496753998', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000037', '0000026', '0000001', '&lt;script&gt;', '1496754009', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000038', '0000028', '0000001', 'select * from  where 1=1', '1496754041', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000039', '0000026', '0000001', '终于搞定了', '1496985672', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000040', '0000027', '0000001', '终于搞定了', '1496986003', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000041', '0000027', '0000001', '&lt;script&gt;alert()&lt;/script&gt;', '1497768000', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000042', '0000030', '0000001', '0620Test', '1497922274', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000043', '0000027', '0000001', 'ajax测试', '1497922639', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000044', '0000027', '0000001', 'layer.ajaxtest\n', '1497922683', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000045', '0000030', '0000001', 'test\n', '1497922864', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000046', '0000026', '0000001', 'test 弹出', '1497922890', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000047', '0000030', '0000001', '防守打法', '1497923212', '3');
INSERT INTO `crm_resprogress` VALUES ('00000000048', '0000030', '0000001', 'test刷新', '1497923262', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000049', '0000030', '0000001', '法', '1497923287', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000050', '0000026', '0000001', '这是测试弹出信息时间', '1497923482', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000051', '0000026', '0000001', '测试', '1497923493', '1');
INSERT INTO `crm_resprogress` VALUES ('00000000052', '0000030', '0000001', '试试', '1497923845', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000053', '0000034', '0000002', 'ceshi', '1497929106', '3');
INSERT INTO `crm_resprogress` VALUES ('00000000054', '0000047', '0000018', '测试审核权限1', '1498210883', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000055', '0000039', '0000019', '测试审核记录的权限，普通', '1498211813', '3');
INSERT INTO `crm_resprogress` VALUES ('00000000056', '0000050', '0000019', '测试普通权限', '1498211829', '2');
INSERT INTO `crm_resprogress` VALUES ('00000000057', '0000049', '0000020', '经理写的更新记录', '1498211973', '2');
