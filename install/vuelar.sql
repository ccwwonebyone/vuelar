/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : vuelar

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2017-05-03 15:16:21
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `vl_column`
-- ----------------------------
DROP TABLE IF EXISTS `vl_column`;
CREATE TABLE `vl_column` (
  `id` int(6) NOT NULL AUTO_INCREMENT COMMENT '栏位id',
  `table_id` int(11) NOT NULL COMMENT '表ID',
  `Field` char(255) NOT NULL COMMENT '字段名',
  `Comment` char(255) DEFAULT NULL COMMENT '注释',
  `Type` char(255) DEFAULT NULL COMMENT '类型',
  `Key` char(255) DEFAULT NULL COMMENT '主键',
  `introduce` text COMMENT '介绍字段',
  `Null` char(255) DEFAULT NULL COMMENT '能否为空',
  `Default` char(255) DEFAULT NULL COMMENT '默认值',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1542 DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vl_database_config`
-- ----------------------------
DROP TABLE IF EXISTS `vl_database_config`;
CREATE TABLE `vl_database_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '数据库id',
  `driver` char(255) NOT NULL DEFAULT 'mysql' COMMENT '数据库类型',
  `dsn` char(255) DEFAULT NULL COMMENT 'dsn',
  `database` char(40) NOT NULL DEFAULT 'manp' COMMENT '数据库',
  `host` char(40) NOT NULL DEFAULT 'localhost' COMMENT '主机名',
  `username` char(40) NOT NULL DEFAULT 'root' COMMENT '用户名',
  `password` char(40) NOT NULL DEFAULT '123456' COMMENT '密码',
  `prefix` char(255) DEFAULT NULL COMMENT '表前缀',
  `charset` char(255) DEFAULT 'utf8' COMMENT '字符集',
  `port` int(6) DEFAULT '3306' COMMENT '端口号',
  `params` char(255) DEFAULT NULL COMMENT '额外参数',
  `collation` varchar(40) DEFAULT NULL COMMENT '字符集',
  `comment` varchar(40) DEFAULT NULL COMMENT '注释',
  `introduce` text COMMENT '介绍',
  `user_id` int(6) DEFAULT NULL COMMENT '用户id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vl_table`
-- ----------------------------
DROP TABLE IF EXISTS `vl_table`;
CREATE TABLE `vl_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL COMMENT '表名',
  `comment` varchar(20) DEFAULT NULL COMMENT '表的注释',
  `introduce` text COMMENT '表介绍',
  `db_id` int(11) NOT NULL COMMENT '数据库ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=158 DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `vl_user`
-- ----------------------------
DROP TABLE IF EXISTS `vl_user`;
CREATE TABLE `vl_user` (
  `id` int(11) NOT NULL,
  `user` varchar(10) NOT NULL COMMENT '用户',
  `pwd` varchar(40) NOT NULL COMMENT '密码',
  `real_name` varchar(40) DEFAULT NULL COMMENT '真实姓名',
  `nick_name` varchar(40) DEFAULT NULL COMMENT '昵称',
  `level` int(2) DEFAULT '2' COMMENT '权限级别',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
