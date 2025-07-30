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

 Date: 30/07/2025 09:19:01
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for rawat_inap_dr
-- ----------------------------
DROP TABLE IF EXISTS `rawat_inap_dr`;
CREATE TABLE `rawat_inap_dr`  (
  `no_rawat` varchar(17) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `kd_jenis_prw` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kd_dokter` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_perawatan` date NOT NULL DEFAULT '0000-00-00',
  `jam_rawat` time NOT NULL DEFAULT '00:00:00',
  `material` double NOT NULL,
  `bhp` double NOT NULL,
  `tarif_tindakandr` double NOT NULL,
  `kso` double NULL DEFAULT NULL,
  `menejemen` double NULL DEFAULT NULL,
  `biaya_rawat` double NULL DEFAULT NULL,
  PRIMARY KEY (`no_rawat`, `kd_jenis_prw`, `kd_dokter`, `tgl_perawatan`, `jam_rawat`) USING BTREE,
  INDEX `no_rawat`(`no_rawat`) USING BTREE,
  INDEX `kd_jenis_prw`(`kd_jenis_prw`) USING BTREE,
  INDEX `kd_dokter`(`kd_dokter`) USING BTREE,
  INDEX `tgl_perawatan`(`tgl_perawatan`) USING BTREE,
  INDEX `biaya_rawat`(`biaya_rawat`) USING BTREE,
  INDEX `jam_rawat`(`jam_rawat`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
