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

 Date: 30/07/2025 09:18:36
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for pegawai
-- ----------------------------
DROP TABLE IF EXISTS `pegawai`;
CREATE TABLE `pegawai`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nik` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nama` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jk` enum('Pria','Wanita') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jbtn` varchar(25) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jnj_jabatan` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kode_kelompok` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kode_resiko` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kode_emergency` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `departemen` char(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `bidang` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `stts_wp` char(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `stts_kerja` char(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `npwp` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `pendidikan` varchar(80) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `gapok` double NOT NULL,
  `tmp_lahir` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tgl_lahir` date NOT NULL,
  `alamat` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kota` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `mulai_kerja` date NOT NULL,
  `ms_kerja` enum('<1','PT','FT>1') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `indexins` char(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `bpd` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `rekening` varchar(25) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `stts_aktif` enum('AKTIF','CUTI','KELUAR','TENAGA LUAR') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `wajibmasuk` tinyint(2) NOT NULL,
  `pengurang` double NOT NULL,
  `indek` tinyint(4) NOT NULL,
  `mulai_kontrak` date NULL DEFAULT NULL,
  `cuti_diambil` int(11) NOT NULL,
  `dankes` double NOT NULL,
  `photo` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `no_ktp` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `nik_2`(`nik`) USING BTREE,
  INDEX `departemen`(`departemen`) USING BTREE,
  INDEX `bidang`(`bidang`) USING BTREE,
  INDEX `stts_wp`(`stts_wp`) USING BTREE,
  INDEX `stts_kerja`(`stts_kerja`) USING BTREE,
  INDEX `pendidikan`(`pendidikan`) USING BTREE,
  INDEX `indexins`(`indexins`) USING BTREE,
  INDEX `jnj_jabatan`(`jnj_jabatan`) USING BTREE,
  INDEX `bpd`(`bpd`) USING BTREE,
  INDEX `nama`(`nama`) USING BTREE,
  INDEX `jbtn`(`jbtn`) USING BTREE,
  INDEX `npwp`(`npwp`) USING BTREE,
  INDEX `dankes`(`dankes`) USING BTREE,
  INDEX `cuti_diambil`(`cuti_diambil`) USING BTREE,
  INDEX `mulai_kontrak`(`mulai_kontrak`) USING BTREE,
  INDEX `stts_aktif`(`stts_aktif`) USING BTREE,
  INDEX `tmp_lahir`(`tmp_lahir`) USING BTREE,
  INDEX `alamat`(`alamat`) USING BTREE,
  INDEX `mulai_kerja`(`mulai_kerja`) USING BTREE,
  INDEX `gapok`(`gapok`) USING BTREE,
  INDEX `kota`(`kota`) USING BTREE,
  INDEX `pengurang`(`pengurang`) USING BTREE,
  INDEX `indek`(`indek`) USING BTREE,
  INDEX `jk`(`jk`) USING BTREE,
  INDEX `ms_kerja`(`ms_kerja`) USING BTREE,
  INDEX `tgl_lahir`(`tgl_lahir`) USING BTREE,
  INDEX `rekening`(`rekening`) USING BTREE,
  INDEX `wajibmasuk`(`wajibmasuk`) USING BTREE,
  INDEX `kode_emergency`(`kode_emergency`) USING BTREE,
  INDEX `kode_kelompok`(`kode_kelompok`) USING BTREE,
  INDEX `kode_resiko`(`kode_resiko`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 640 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
