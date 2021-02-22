/*
 Navicat Premium Data Transfer

 Source Server         : Localhost
 Source Server Type    : MySQL
 Source Server Version : 100412
 Source Host           : localhost:3306
 Source Schema         : helper

 Target Server Type    : MySQL
 Target Server Version : 100412
 File Encoding         : 65001

 Date: 22/02/2021 19:24:41
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for commands
-- ----------------------------
DROP TABLE IF EXISTS `commands`;
CREATE TABLE `commands`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `author` int NULL DEFAULT NULL,
  `created` int NULL DEFAULT NULL,
  `updated` int NULL DEFAULT NULL,
  `status` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk-command-author`(`author`) USING BTREE,
  CONSTRAINT `fk-command-author` FOREIGN KEY (`author`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tips
-- ----------------------------
DROP TABLE IF EXISTS `tips`;
CREATE TABLE `tips`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `author_id` int NULL DEFAULT NULL,
  `created` int NULL DEFAULT NULL,
  `updated` int NULL DEFAULT NULL,
  `status` int NULL DEFAULT NULL,
  `command_id` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk-tip-author`(`author_id`) USING BTREE,
  INDEX `fk-tip-command`(`command_id`) USING BTREE,
  CONSTRAINT `fk-tip-author` FOREIGN KEY (`author_id`) REFERENCES `users` (`telegram_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk-tip-command` FOREIGN KEY (`command_id`) REFERENCES `commands` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for user_roles
-- ----------------------------
DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `telegram_id` int NOT NULL,
  `fio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tg_username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `role_id` int NULL DEFAULT NULL,
  `started` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `telegram_id`) USING BTREE,
  INDEX `fk-user-role`(`role_id`) USING BTREE,
  INDEX `telegram_id`(`telegram_id`) USING BTREE,
  CONSTRAINT `fk-user-role` FOREIGN KEY (`role_id`) REFERENCES `user_roles` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
