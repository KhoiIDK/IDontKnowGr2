-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 25, 2026 lúc 03:13 PM
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
-- Cấu trúc bảng cho bảng `jobs`
--

CREATE TABLE `jobs` (
  `job_id` int(11) NOT NULL,
  `job_ref_num` varchar(5) NOT NULL,
  `title` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `job_type` varchar(50) NOT NULL,
  `salary` varchar(100) NOT NULL,
  `reporting_line` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `responsibilities` text NOT NULL,
  `requirements` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `jobs`
--

INSERT INTO `jobs` (`job_id`, `job_ref_num`, `title`, `location`, `department`, `job_type`, `salary`, `reporting_line`, `description`, `responsibilities`, `requirements`) VALUES
(1, 'SCM01', 'Smarter Transport Systems Engineer', 'Melbourne, Australia (Hybrid)', 'Smart Transport & Mobility', 'Full-time', '$95,000 – $115,000 / year', 'Reports to the Head of Smart Infrastructure', 'We are seeking an experienced Smart Transport Systems Engineer to design, integrate, and optimize IoT-driven traffic management and urban mobility platforms for metropolitan councils.', 'Design IoT architectures for smart traffic signal coordination and congestion monitoring.\nCollaborate with municipal stakeholders to integrate real-time sensor data into public transit dashboards.\nConduct system security audits and performance testing on deployed transport networks.', 'Degree in Software Engineering, Electrical Engineering, or related technical field.\nProficiency in Python, MQTT protocols, and cloud-based data pipelines (AWS/Azure).\nDemonstrated experience in smart city or IoT infrastructure projects.'),
(2, 'ENRG2', 'Urban Energy Monitoring Analyst', 'Sydney, Australia (On-site)', 'Sustainable Energy Grids', 'Full-time', '$85,000 – $100,000 / year', 'Reports to the Senior Energy Solutions Manager', 'An opportunity for an analytical professional to monitor, analyze, and optimize municipal energy grids and renewable power distribution networks across smart city sectors.', 'Analyze real-time power consumption metrics across public infrastructure grids.\nDevelop predictive models to optimize municipal energy distribution and reduce carbon footprints.\nPrepare technical compliance reports and data visualizations for city council partners.', 'Bachelor’s degree in Data Science, Energy Systems, or Computer Science.\nStrong proficiency in data analysis libraries (Pandas, NumPy) and SQL database queries.\nStrong interpersonal and technical communication skills.');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_id`),
  ADD UNIQUE KEY `job_ref_num` (`job_ref_num`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
