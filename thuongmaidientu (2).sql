-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2025 at 02:10 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `thuongmaidientu`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `course_id`, `quantity`, `created_at`) VALUES
(3, 1, 1, 1, '2025-11-05 19:48:05');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `ten_danh_muc` varchar(100) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `ten_danh_muc`, `mo_ta`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'backend', 'Khóa học lập trình phía server', '2025-10-23 22:23:32', '2025-10-23 22:23:32'),
(2, 'frontend', 'Khóa học giao diện người dùng', '2025-10-23 22:23:32', '2025-10-23 22:23:32'),
(3, 'system', 'Khóa học lập trình hệ thống', '2025-10-23 22:23:32', '2025-10-23 22:23:32'),
(4, 'mobile', 'Phát triển ứng dụng di động', '2025-10-23 22:23:32', '2025-10-23 22:23:32'),
(5, 'ai', 'Trí tuệ nhân tạo và học máy', '2025-10-23 22:23:32', '2025-10-23 22:23:32'),
(6, 'devops', 'Triển khai, CI/CD và hệ thống máy chủ', '2025-10-23 22:23:32', '2025-10-23 22:23:32');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `ten_khoa_hoc` varchar(255) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `gia` decimal(10,2) NOT NULL,
  `so_hoc_vien` int(11) DEFAULT 0,
  `so_gio_hoc` int(11) DEFAULT 0,
  `danh_muc` varchar(100) DEFAULT NULL,
  `bieu_tuong` varchar(10) DEFAULT NULL,
  `anh_dai_dien` varchar(255) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `danh_muc_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `ten_khoa_hoc`, `mo_ta`, `gia`, `so_hoc_vien`, `so_gio_hoc`, `danh_muc`, `bieu_tuong`, `anh_dai_dien`, `ngay_tao`, `ngay_cap_nhat`, `danh_muc_id`) VALUES
(1, 'Lộ Trình PHP Master', 'Xây dựng web app mạnh mẽ với PHP, MySQL và Laravel.', 2500000.00, 1200, 45, '1', '🐘', 'php.jpg', '2025-10-23 22:25:51', '2025-10-24 09:40:38', 1),
(2, 'Lộ Trình React Pro', 'Tạo giao diện động với React, Hooks và Redux.', 3200000.00, 950, 60, '2', '⚛️', 'react.jpg', '2025-10-23 22:25:51', '2025-10-24 09:40:40', 2),
(3, 'Lộ Trình C++ Advanced', 'Lập trình hệ thống với C++, STL và OOP.', 2800000.00, 750, 50, '3', '⚡', 'cpp.jpg', '2025-10-23 22:25:51', '2025-10-24 09:40:43', 3),
(4, 'Lộ Trình Mobile Flutter', 'Xây dựng ứng dụng iOS & Android với Flutter.', 3500000.00, 600, 55, '4', '📱', 'flutter.jpg', '2025-10-23 22:25:51', '2025-10-24 09:40:45', 4),
(5, 'Lộ Trình AI Cơ Bản', 'Machine Learning & Deep Learning với Python.', 4200000.00, 430, 70, '5', '🤖', 'ai.jpg', '2025-10-23 22:25:51', '2025-10-24 09:40:48', 5),
(6, 'Lộ Trình DevOps Thực Chiến', 'CI/CD, Docker, Kubernetes, AWS, Cloud.', 3800000.00, 520, 65, '6', '☁️', 'devops.jpg', '2025-10-23 22:25:51', '2025-10-24 09:40:51', 6);

-- --------------------------------------------------------

--
-- Table structure for table `course_details`
--

CREATE TABLE `course_details` (
  `id` int(11) NOT NULL,
  `ma_khoa_hoc` int(11) NOT NULL,
  `mo_ta_day_du` text NOT NULL,
  `chuong_trinh_hoc` text NOT NULL,
  `ten_giang_vien` varchar(255) NOT NULL,
  `gioi_thieu_giang_vien` text NOT NULL,
  `loi_ich` text NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_details`
--

INSERT INTO `course_details` (`id`, `ma_khoa_hoc`, `mo_ta_day_du`, `chuong_trinh_hoc`, `ten_giang_vien`, `gioi_thieu_giang_vien`, `loi_ich`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 1, 'Khóa học PHP Master giúp bạn làm chủ PHP, MySQL và Laravel, từ cơ bản đến chuyên sâu.', '[\r\n    {\"module\": \"Module 1: PHP Cơ Bản\", \"duration\": \"2 tuần\", \"content\": \"Cấu trúc, biến, hàm, vòng lặp.\"},\r\n    {\"module\": \"Module 2: Cơ Sở Dữ Liệu MySQL\", \"duration\": \"2 tuần\", \"content\": \"Kết nối, CRUD, chống SQL Injection.\"},\r\n    {\"module\": \"Module 3: Lập Trình Hướng Đối Tượng & Composer\", \"duration\": \"2 tuần\", \"content\": \"Class, kế thừa, autoload, Composer.\"},\r\n    {\"module\": \"Module 4: Laravel Framework\", \"duration\": \"3 tuần\", \"content\": \"MVC, Eloquent ORM, Authentication, API RESTful.\"},\r\n    {\"module\": \"Module 5: Triển Khai & Tối Ưu\", \"duration\": \"1 tuần\", \"content\": \"Triển khai Heroku/AWS, bảo mật, tối ưu hiệu năng.\"}\r\n]', 'Huỳnh Thanh Sang', '5+ năm kinh nghiệm PHP/Laravel tại FPT Software, đào tạo hơn 500 học viên và chia sẻ kiến thức trên YouTube.', '[\r\n    {\"title\": \"Chứng chỉ hoàn thành\", \"description\": \"Được công nhận trong ngành IT\"},\r\n    {\"title\": \"Hỗ trợ mentor 1:1\", \"description\": \"Qua Discord và Zoom suốt khóa học\"},\r\n    {\"title\": \"Truy cập trọn đời\", \"description\": \"Toàn bộ bài giảng và code mẫu\"},\r\n    {\"title\": \"Hoàn tiền 100%\", \"description\": \"Nếu không hài lòng trong 30 ngày\"}\r\n]', '2025-10-24 04:08:39', '2025-10-24 04:08:39');

-- --------------------------------------------------------

--
-- Table structure for table `course_instructors`
--

CREATE TABLE `course_instructors` (
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_instructors`
--

INSERT INTO `course_instructors` (`course_id`, `instructor_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `ngay_dang_ky` datetime DEFAULT current_timestamp(),
  `tien_do` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `id` int(11) NOT NULL,
  `ho_ten` varchar(255) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `anh_dai_dien` varchar(255) DEFAULT NULL,
  `kinh_nghiem` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `lien_he` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`id`, `ho_ten`, `mo_ta`, `anh_dai_dien`, `kinh_nghiem`, `email`, `lien_he`) VALUES
(1, 'Huỳnh Thanh Sang', '5+ năm kinh nghiệm PHP/Laravel tại FPT Software. Đào tạo 500+ học viên.', 'sang.jpg', 5, 'sang@codecungsang.vn', '0901234567'),
(2, 'Nguyễn Minh Tuấn', 'Chuyên gia React & Next.js, 4 năm kinh nghiệm tại Tiki.', 'tuan.jpg', 4, 'tuan@codecungsang.vn', '0907654321'),
(3, 'Lê Ngọc Phúc', 'Senior Developer C++ & System Programming.', 'phuc.jpg', 7, 'phuc@codecungsang.vn', '0909999999'),
(4, 'Trần Thảo Nhi', 'Mobile Developer Flutter, 3 năm kinh nghiệm tại VNG.', 'nhi.jpg', 3, 'nhi@codecungsang.vn', '0908888888'),
(5, 'Đặng Hoàng Duy', 'AI Engineer chuyên về TensorFlow & PyTorch.', 'duy.jpg', 6, 'duy@codecungsang.vn', '0906666666'),
(6, 'Phan Gia Khang', 'DevOps Engineer, chuyên Docker/Kubernetes.', 'khang.jpg', 5, 'khang@codecungsang.vn', '0905555555');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `module_id` int(11) DEFAULT NULL,
  `tieu_de` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `thoi_luong` int(11) DEFAULT NULL,
  `thu_tu` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `ten_module` varchar(255) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `thu_tu` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `course_id`, `ten_module`, `mo_ta`, `thu_tu`) VALUES
(1, 1, 'PHP Cơ Bản', 'Giới thiệu PHP, cú pháp, biến, hàm.', 1),
(2, 1, 'MySQL & CRUD', 'Kết nối CSDL, thao tác dữ liệu.', 2),
(3, 1, 'Laravel Framework', 'MVC, Routing, Eloquent ORM.', 3),
(4, 2, 'React Fundamentals', 'Component, Props, State.', 1),
(5, 2, 'React Hooks & Redux', 'useState, useEffect, Redux Toolkit.', 2),
(6, 3, 'C++ OOP', 'Class, kế thừa, đa hình.', 1),
(7, 3, 'STL & Template', 'Vector, Map, Generic programming.', 2);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tong_tien` decimal(10,2) NOT NULL,
  `trang_thai` enum('chờ duyệt','đã duyệt','đã hủy') DEFAULT 'chờ duyệt',
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `tong_tien`, `trang_thai`, `ngay_tao`) VALUES
(3, 2, 2500000.00, 'chờ duyệt', '2025-10-31 15:47:45'),
(4, 1, 2500000.00, 'chờ duyệt', '2025-11-05 19:47:27');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `so_luong` int(11) DEFAULT 1,
  `don_gia` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `course_id`, `so_luong`, `don_gia`) VALUES
(1, 3, 1, 1, 2500000.00),
(2, 4, 1, 1, 2500000.00);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `avatar` varchar(255) DEFAULT 'uploads/avatars/default.png',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'Thanh Sang', 'huynhtsang2004@gmail.com', '111111', 'admin', '', '2025-10-17 15:40:34', '2025-10-31 14:55:46'),
(2, 'Gia Bảo', 'bao022101023@tgu.edu.vn', '111111', 'user', '', '2025-10-31 14:56:58', '2025-10-31 14:56:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `danh_muc_id` (`danh_muc_id`);

--
-- Indexes for table `course_details`
--
ALTER TABLE `course_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_khoa_hoc` (`ma_khoa_hoc`);

--
-- Indexes for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD PRIMARY KEY (`course_id`,`instructor_id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `course_details`
--
ALTER TABLE `course_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`danh_muc_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `course_details`
--
ALTER TABLE `course_details`
  ADD CONSTRAINT `course_details_ibfk_1` FOREIGN KEY (`ma_khoa_hoc`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD CONSTRAINT `course_instructors_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `course_instructors_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`);

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
