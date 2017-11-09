/*
 Navicat MySQL Data Transfer

 Source Server         : 本地
 Source Server Type    : MySQL
 Source Server Version : 50716
 Source Host           : localhost
 Source Database       : test

 Target Server Type    : MySQL
 Target Server Version : 50716
 File Encoding         : utf-8

 Date: 11/09/2017 11:09:43 AM
*/

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `pdo_user`
-- ----------------------------
DROP TABLE IF EXISTS `pdo_user`;
CREATE TABLE `pdo_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` char(32) NOT NULL,
  `email` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Records of `pdo_user`
-- ----------------------------
BEGIN;
INSERT INTO `pdo_user` VALUES ('4', 'king1', 'fe32ed4e9dd056d2cae190d6a5bf7c20', 'imooc@qq.com'), ('5', 'king2', 'd0079d9c035490ee9443e278b8e6820f', 'imooc@qq.com'), ('6', 'king3', '837f020582fa7b8998ab27337ad61e05', 'imooc@qq.com'), ('7', 'king4', 'king4', 'imooc4@qq.com'), ('10', 'king5', 'king5', 'imooc5@qq.com'), ('13', 'king6', 'king5', 'imooc5@qq.com'), ('14', 'king7', '7973bf7cad54a324cfd912a16ce39200', 'imooc7.@qq.com'), ('15', ':username', ':password', ':email'), ('19', 'imooc', 'imooc', 'imooc@imooc.com'), ('20', 'Mr.king', 'Mr.king', 'Mr.king@imooc.com'), ('23', 'test', 'test', 'test@imooc.com');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
