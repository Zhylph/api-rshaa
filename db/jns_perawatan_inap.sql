/*
 Navicat Premium Data Transfer

 Source Server         : Master
 Source Server Type    : MySQL
 Source Server Version : 50744 (5.7.44-log)
 Source Host           : 192.168.0.3:3939
 Source Schema         : rsaz_sik

 Target Server Type    : MySQL
 Target Server Version : 50744 (5.7.44-log)
 File Encoding         : 65001

 Date: 30/07/2025 09:19:40
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for jns_perawatan_inap
-- ----------------------------
DROP TABLE IF EXISTS `jns_perawatan_inap`;
CREATE TABLE `jns_perawatan_inap`  (
  `kd_jenis_prw` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nm_perawatan` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL,
  `kd_kategori` char(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `material` double NULL DEFAULT NULL,
  `bhp` double NOT NULL,
  `tarif_tindakandr` double NULL DEFAULT NULL,
  `tarif_tindakanpr` double NULL DEFAULT NULL,
  `kso` double NULL DEFAULT NULL,
  `menejemen` double NULL DEFAULT NULL,
  `total_byrdr` double NULL DEFAULT NULL,
  `total_byrpr` double NULL DEFAULT NULL,
  `total_byrdrpr` double NOT NULL,
  `kd_pj` char(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kd_bangsal` char(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status` enum('0','1') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kelas` enum('-','Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`kd_jenis_prw`) USING BTREE,
  INDEX `kd_pj`(`kd_pj`) USING BTREE,
  INDEX `kd_bangsal`(`kd_bangsal`) USING BTREE,
  INDEX `kd_kategori`(`kd_kategori`) USING BTREE,
  INDEX `nm_perawatan`(`nm_perawatan`) USING BTREE,
  INDEX `material`(`material`) USING BTREE,
  INDEX `tarif_tindakandr`(`tarif_tindakandr`) USING BTREE,
  INDEX `tarif_tindakanpr`(`tarif_tindakanpr`) USING BTREE,
  INDEX `total_byrdr`(`total_byrdr`) USING BTREE,
  INDEX `total_byrpr`(`total_byrpr`) USING BTREE,
  INDEX `bhp`(`bhp`) USING BTREE,
  INDEX `kso`(`kso`) USING BTREE,
  INDEX `menejemen`(`menejemen`) USING BTREE,
  INDEX `status`(`status`) USING BTREE,
  INDEX `total_byrdrpr`(`total_byrdrpr`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
