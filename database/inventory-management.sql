-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost:3306
-- 生成日時: 2026-10-22 03:15:21
-- サーバのバージョン： 5.7.24
-- PHP のバージョン: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `inventory-management`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `inventory`
--

CREATE TABLE `inventory` (
  `stock_id` int(11) NOT NULL,
  `stock_name` varchar(100) NOT NULL,
  `border_line` int(11) NOT NULL,
  `weight` decimal(10,3) NOT NULL,
  `img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `inventory_history`
--

CREATE TABLE `inventory_history` (
  `history_id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `update_date` datetime NOT NULL,
  `quantity` int(11) NOT NULL,
  `weight` decimal(10,3) NOT NULL,
  `max_weight` decimal(10,3) NOT NULL,
  `stock_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `mat_specification`
--

CREATE TABLE `mat_specification` (
  `mat_id` int(11) NOT NULL,
  `load_capacity` int(11) NOT NULL,
  `measurement_unit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `possess_mat`
--

CREATE TABLE `possess_mat` (
  `serial_id` char(12) NOT NULL,
  `identification_id` int(11) NOT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `mat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `authority` tinyint(1) NOT NULL,
  `mail_address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`stock_id`);

--
-- テーブルのインデックス `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `stock_id` (`stock_id`);

--
-- テーブルのインデックス `mat_specification`
--
ALTER TABLE `mat_specification`
  ADD PRIMARY KEY (`mat_id`);

--
-- テーブルのインデックス `possess_mat`
--
ALTER TABLE `possess_mat`
  ADD PRIMARY KEY (`serial_id`),
  ADD KEY `stock_id` (`stock_id`),
  ADD KEY `mat_id` (`mat_id`);

--
-- テーブルのインデックス `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `inventory`
--
ALTER TABLE `inventory`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `inventory_history`
--
ALTER TABLE `inventory_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `mat_specification`
--
ALTER TABLE `mat_specification`
  MODIFY `mat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD CONSTRAINT `inventory_history_ibfk_1` FOREIGN KEY (`stock_id`) REFERENCES `inventory` (`stock_id`);

--
-- テーブルの制約 `possess_mat`
--
ALTER TABLE `possess_mat`
  ADD CONSTRAINT `possess_mat_ibfk_1` FOREIGN KEY (`stock_id`) REFERENCES `inventory` (`stock_id`),
  ADD CONSTRAINT `possess_mat_ibfk_2` FOREIGN KEY (`mat_id`) REFERENCES `mat_specification` (`mat_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
