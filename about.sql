-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 26, 2026 lúc 03:15 AM
-- Phiên bản máy phục vụ: 10.11.16-MariaDB
-- Phiên bản PHP: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `smarter_future_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `about`
--

CREATE TABLE `about` (
  `id` int(11) NOT NULL,
  `member_name` varchar(100) NOT NULL,
  `student_id` varchar(10) NOT NULL,
  `quote_text` varchar(200) NOT NULL,
  `part1_role` varchar(200) NOT NULL,
  `part2_role` varchar(200) NOT NULL,
  `dream_job` varchar(100) NOT NULL,
  `snack` varchar(100) NOT NULL,
  `hometown` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `about`
--

INSERT INTO `about` (`id`, `member_name`, `student_id`, `quote_text`, `part1_role`, `part2_role`, `dream_job`, `snack`, `hometown`) VALUES
(1, 'Võ Minh Khôi', 'SWS02154', 'Nếu bạn buồn, bạn hãy đừng buồn (If you are sad, don\'t be sad)', 'Main coder for about.html', 'Convert pages to PHP and modularize .inc files, settings.php and about.php', 'Lawyer', 'Icecream', 'Gia Lai'),
(2, 'Lương Quốc Hiển Minh', 'SWS02432', 'Hãy yêu đời (Love your life)', 'Main coder for index.html', 'Update Home page(index.php) and add validated records (process_eoi.php)', 'Jobless', 'Rice', 'Hà Nội'),
(3, 'Phạm Nguyễn Gia Bảo', 'SWS02160', 'Hãy đơn giản hóa mọi thứ! (Embrace Simplicity in All We Do)', 'Main coder for jobs.html', 'Update jobs.php and add manage.php + search.php', 'Chef', 'KitKat', 'Thanh Hóa'),
(4, 'Nguyễn Lê Trường Khải', 'SWS02128', 'Nếu tôi có 40 tỷ (Only if I had 40 billion)', 'Main coder for apply.html', 'Update apply.php and add manage.php', 'Aerospace engineer', 'Chia seeds', 'Đồng Tháp');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `about`
--
ALTER TABLE `about`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
