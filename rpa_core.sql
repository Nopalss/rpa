-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2025 at 04:49 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rpa_core`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_data`
--

CREATE TABLE `tbl_data` (
  `data_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `line_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `header_id` int(11) NOT NULL,
  `data_1` varchar(64) DEFAULT NULL,
  `data_2` varchar(64) DEFAULT NULL,
  `data_3` varchar(64) DEFAULT NULL,
  `data_4` varchar(64) DEFAULT NULL,
  `data_5` varchar(64) DEFAULT NULL,
  `data_6` varchar(64) DEFAULT NULL,
  `data_7` varchar(64) DEFAULT NULL,
  `data_8` varchar(64) DEFAULT NULL,
  `data_9` varchar(64) DEFAULT NULL,
  `data_10` varchar(64) DEFAULT NULL,
  `data_11` varchar(64) DEFAULT NULL,
  `data_12` varchar(64) DEFAULT NULL,
  `data_13` varchar(64) DEFAULT NULL,
  `data_14` varchar(64) DEFAULT NULL,
  `data_15` varchar(64) DEFAULT NULL,
  `data_16` varchar(64) DEFAULT NULL,
  `data_17` varchar(64) DEFAULT NULL,
  `data_18` varchar(64) DEFAULT NULL,
  `data_19` varchar(64) DEFAULT NULL,
  `data_20` varchar(64) DEFAULT NULL,
  `data_21` varchar(64) DEFAULT NULL,
  `data_22` varchar(64) DEFAULT NULL,
  `data_23` varchar(64) DEFAULT NULL,
  `data_24` varchar(64) DEFAULT NULL,
  `data_25` varchar(64) DEFAULT NULL,
  `data_26` varchar(64) DEFAULT NULL,
  `data_27` varchar(64) DEFAULT NULL,
  `data_28` varchar(64) DEFAULT NULL,
  `data_29` varchar(64) DEFAULT NULL,
  `data_30` varchar(64) DEFAULT NULL,
  `data_31` varchar(64) DEFAULT NULL,
  `data_32` varchar(64) DEFAULT NULL,
  `data_33` varchar(64) DEFAULT NULL,
  `data_34` varchar(64) DEFAULT NULL,
  `data_35` varchar(64) DEFAULT NULL,
  `data_36` varchar(64) DEFAULT NULL,
  `data_37` varchar(64) DEFAULT NULL,
  `data_38` varchar(64) DEFAULT NULL,
  `data_39` varchar(64) DEFAULT NULL,
  `data_40` varchar(64) DEFAULT NULL,
  `data_41` varchar(64) DEFAULT NULL,
  `data_42` varchar(64) DEFAULT NULL,
  `data_43` varchar(64) DEFAULT NULL,
  `data_44` varchar(64) DEFAULT NULL,
  `data_45` varchar(64) DEFAULT NULL,
  `data_46` varchar(64) DEFAULT NULL,
  `data_47` varchar(64) DEFAULT NULL,
  `data_48` varchar(64) DEFAULT NULL,
  `data_49` varchar(64) DEFAULT NULL,
  `data_50` varchar(64) DEFAULT NULL,
  `data_51` varchar(64) DEFAULT NULL,
  `data_52` varchar(64) DEFAULT NULL,
  `data_53` varchar(64) DEFAULT NULL,
  `data_54` varchar(64) DEFAULT NULL,
  `data_55` varchar(64) DEFAULT NULL,
  `data_56` varchar(64) DEFAULT NULL,
  `data_57` varchar(64) DEFAULT NULL,
  `data_58` varchar(64) DEFAULT NULL,
  `data_59` varchar(64) DEFAULT NULL,
  `data_60` varchar(64) DEFAULT NULL,
  `data_61` varchar(64) DEFAULT NULL,
  `data_62` varchar(64) DEFAULT NULL,
  `data_63` varchar(64) DEFAULT NULL,
  `data_64` varchar(64) DEFAULT NULL,
  `data_65` varchar(64) DEFAULT NULL,
  `data_66` varchar(64) DEFAULT NULL,
  `data_67` varchar(64) DEFAULT NULL,
  `data_68` varchar(64) DEFAULT NULL,
  `data_69` varchar(64) DEFAULT NULL,
  `data_70` varchar(64) DEFAULT NULL,
  `data_71` varchar(64) DEFAULT NULL,
  `data_72` varchar(64) DEFAULT NULL,
  `data_73` varchar(64) DEFAULT NULL,
  `data_74` varchar(64) DEFAULT NULL,
  `data_75` varchar(64) DEFAULT NULL,
  `data_76` varchar(64) DEFAULT NULL,
  `data_77` varchar(64) DEFAULT NULL,
  `data_78` varchar(64) DEFAULT NULL,
  `data_79` varchar(64) DEFAULT NULL,
  `data_80` varchar(64) DEFAULT NULL,
  `data_81` varchar(64) DEFAULT NULL,
  `data_82` varchar(64) DEFAULT NULL,
  `data_83` varchar(64) DEFAULT NULL,
  `data_84` varchar(64) DEFAULT NULL,
  `data_85` varchar(64) DEFAULT NULL,
  `data_86` varchar(64) DEFAULT NULL,
  `data_87` varchar(64) DEFAULT NULL,
  `data_88` varchar(64) DEFAULT NULL,
  `data_89` varchar(64) DEFAULT NULL,
  `data_90` varchar(64) DEFAULT NULL,
  `data_91` varchar(64) DEFAULT NULL,
  `data_92` varchar(64) DEFAULT NULL,
  `data_93` varchar(64) DEFAULT NULL,
  `data_94` varchar(64) DEFAULT NULL,
  `data_95` varchar(64) DEFAULT NULL,
  `data_96` varchar(64) DEFAULT NULL,
  `data_97` varchar(64) DEFAULT NULL,
  `data_98` varchar(64) DEFAULT NULL,
  `data_99` varchar(64) DEFAULT NULL,
  `data_100` varchar(64) DEFAULT NULL,
  `data_101` varchar(64) DEFAULT NULL,
  `data_102` varchar(64) DEFAULT NULL,
  `data_103` varchar(64) DEFAULT NULL,
  `data_104` varchar(64) DEFAULT NULL,
  `data_105` varchar(64) DEFAULT NULL,
  `data_106` varchar(64) DEFAULT NULL,
  `data_107` varchar(64) DEFAULT NULL,
  `data_108` varchar(64) DEFAULT NULL,
  `data_109` varchar(64) DEFAULT NULL,
  `data_110` varchar(64) DEFAULT NULL,
  `data_111` varchar(64) DEFAULT NULL,
  `data_112` varchar(64) DEFAULT NULL,
  `data_113` varchar(64) DEFAULT NULL,
  `data_114` varchar(64) DEFAULT NULL,
  `data_115` varchar(64) DEFAULT NULL,
  `data_116` varchar(64) DEFAULT NULL,
  `data_117` varchar(64) DEFAULT NULL,
  `data_118` varchar(64) DEFAULT NULL,
  `data_119` varchar(64) DEFAULT NULL,
  `data_120` varchar(64) DEFAULT NULL,
  `data_121` varchar(64) DEFAULT NULL,
  `data_122` varchar(64) DEFAULT NULL,
  `data_123` varchar(64) DEFAULT NULL,
  `data_124` varchar(64) DEFAULT NULL,
  `data_125` varchar(64) DEFAULT NULL,
  `data_126` varchar(64) DEFAULT NULL,
  `data_127` varchar(64) DEFAULT NULL,
  `data_128` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_filename`
--

CREATE TABLE `tbl_filename` (
  `file_id` int(11) NOT NULL,
  `filename` varchar(64) NOT NULL,
  `create_at` varchar(64) NOT NULL,
  `create_by` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_line`
--

CREATE TABLE `tbl_line` (
  `line_id` int(11) NOT NULL,
  `line_name` varchar(64) NOT NULL,
  `create_at` varchar(64) NOT NULL,
  `create_by` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `rule` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_data`
--
ALTER TABLE `tbl_data`
  ADD PRIMARY KEY (`data_id`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_line` (`line_id`),
  ADD KEY `idx_model` (`model_id`),
  ADD KEY `idx_file` (`file_id`),
  ADD KEY `idx_header` (`header_id`),
  ADD KEY `idx_time` (`time`) USING BTREE;

--
-- Indexes for table `tbl_filename`
--
ALTER TABLE `tbl_filename`
  ADD PRIMARY KEY (`file_id`);

--
-- Indexes for table `tbl_line`
--
ALTER TABLE `tbl_line`
  ADD PRIMARY KEY (`line_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_data`
--
ALTER TABLE `tbl_data`
  MODIFY `data_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_filename`
--
ALTER TABLE `tbl_filename`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_line`
--
ALTER TABLE `tbl_line`
  MODIFY `line_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
