-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2025 at 09:00 AM
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
-- Database: `webbankhoahoc2`
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
-- Table structure for table `chat_history`
--

CREATE TABLE `chat_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID người dùng từ bảng users',
  `session_id` varchar(255) NOT NULL COMMENT 'ID session để identify user (từ PHP session hoặc UUID)',
  `role` enum('user','ai') NOT NULL COMMENT 'Loại tin nhắn: user (người dùng) hoặc ai (AI)',
  `message` text NOT NULL COMMENT 'Nội dung tin nhắn',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Thời gian gửi tin nhắn'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu lịch sử chat AI';

--
-- Dumping data for table `chat_history`
--

INSERT INTO `chat_history` (`id`, `user_id`, `session_id`, `role`, `message`, `created_at`) VALUES
(13, 1, 'chat_1764303306996_r0t6ljj1k', 'user', 'khóa học php giá bao nhiêu', '2025-12-03 14:44:22'),
(14, 1, 'chat_1764303306996_r0t6ljj1k', 'ai', 'Chào bạn! 🤩 Khóa học PHP bên mình có mức giá rất hấp dẫn đây:\n\n*   **Lộ Trình PHP Master**: **2.000.000 VND** 💰\n\nĐăng ký ngay để nhận ưu đãi! 🚀', '2025-12-03 14:44:28'),
(15, 1, 'chat_1764303306996_r0t6ljj1k', 'user', 'khóa học nào mắc tiền nhất thế', '2025-12-03 14:47:00'),
(16, 1, 'chat_1764303306996_r0t6ljj1k', 'ai', 'Chào bạn! 👋 Hiện tại, khóa học có mức giá cao nhất tại Code Cùng Sang là **Lộ Trình AI Cơ Bản** với **4.200.000 VND** 💰.\n\nMặc dù vậy, Code Cùng Sang nổi bật với các khóa học chất lượng cao về phát triển web như PHP (backend) và React (frontend), cùng với C++ cho lập trình hệ thống đó.\n\nNếu bạn quan tâm đến lộ trình học web chuyên nghiệp, mình có thể gợi ý các khóa học này nhé!\nĐăng ký ngay để nhận ưu đãi đặc biệt! 💻', '2025-12-03 14:47:13'),
(17, 1, 'chat_1764303306996_r0t6ljj1k', 'user', 'muốn làm website thì học khóa học nào hợp lý', '2025-12-03 14:49:08'),
(18, 1, 'chat_1764303306996_r0t6ljj1k', 'ai', 'Chào bạn! Muốn xây dựng website thì học lập trình web là chuẩn rồi đó! 🚀\n\nĐể làm một website hoàn chỉnh, bạn sẽ cần học cả phần backend (xử lý dữ liệu, logic) và frontend (giao diện người dùng). Code Cùng Sang gợi ý lộ trình học tuyệt vời sau:\n\n1.  **Bước 1: Backend với PHP** 💻\n    *   **Khóa học đề xuất:** **Lộ Trình PHP Master (Toàn Diện)**\n    *   **Lý do:** Đây là khóa học nền tảng và nâng cao về PHP, giúp bạn xây dựng hệ thống xử lý dữ liệu mạnh mẽ cho website của mình. PHP rất phổ biến và dễ học cho người mới bắt đầu.\n    *   **Lợi ích:** Bạn sẽ nắm vững cách tạo API, quản lý dữ liệu phía máy chủ, và xây dựng logic cho ứng dụng web.\n\n2.  **Bước 2: Frontend với React** 🎨\n    *   **Khóa học đề xuất:** **React Cho Người Mới Bắt Đầu**\n    *   **Lý do:** React là một thư viện JavaScript cực kỳ mạnh mẽ để xây dựng giao diện người dùng tương tác, hiện đại và đẹp mắt. Nó được các công ty lớn tin dùng.\n    *   **Lợi ích:** Bạn sẽ học cách tạo ra các thành phần giao diện động, tối ưu trải nghiệm người dùng, và làm cho website của bạn trở nên chuyên nghiệp hơn.\n\nBắt đầu với PHP để xây dựng \"bộ não\" của website, sau đó dùng React để tạo ra \"khuôn mặt\" đẹp và tương tác nhé!\n\nĐăng ký ngay để nhận ưu đãi và bắt đầu hành trình xây dựng website của riêng bạn! 📚', '2025-12-03 14:49:15');

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
(1, 'Lộ Trình PHP Master', 'Xây dựng web app mạnh mẽ với PHP, MySQL và Laravel.', 2000.00, 1204, 45, '1', '🐘', 'php.jpg', '2025-10-23 22:25:51', '2025-12-03 13:51:04', 1),
(2, 'Lộ Trình React Pro', 'Tạo giao diện động với React, Hooks và Redux.', 1000.00, 952, 60, '2', '⚛️', 'react.jpg', '2025-10-23 22:25:51', '2025-12-03 13:51:04', 2),
(3, 'Lộ Trình C++ Advanced', 'Lập trình hệ thống với C++, STL và OOP.', 2800000.00, 750, 50, '3', '⚡', 'cpp.jpg', '2025-10-23 22:25:51', '2025-10-24 09:40:43', 3),
(4, 'Lộ Trình Mobile Flutter', 'Xây dựng ứng dụng iOS & Android với Flutter.', 3500000.00, 600, 55, '4', '📱', 'flutter.jpg', '2025-10-23 22:25:51', '2025-10-24 09:40:45', 4),
(5, 'Lộ Trình AI Cơ Bản', 'Machine Learning & Deep Learning với Python.', 4200000.00, 430, 70, '5', '🤖', 'ai.jpg', '2025-10-23 22:25:51', '2025-10-24 09:40:48', 5),
(6, 'Lộ Trình DevOps Thực Chiến', 'CI/CD, Docker, Kubernetes, AWS, Cloud.', 3800000.00, 520, 65, '6', '☁️', 'devops.jpg', '2025-10-23 22:25:51', '2025-10-24 09:40:51', 6),
(7, 'Lộ Trình HTML CSS Nâng Cao', 'Nâng cao kỹ năng HTML5 và CSS3: Semantic tags, Flexbox, Grid, Animations, Responsive Design và Best Practices.', 1500.00, 0, 40, '2', '🌐', 'htmlcss.jpg', '2025-11-30 10:39:00', '2025-11-30 10:39:00', 2);

-- --------------------------------------------------------

--
-- Table structure for table `course_details`
--

CREATE TABLE `course_details` (
  `id` int(11) NOT NULL,
  `ma_khoa_hoc` int(11) NOT NULL,
  `mo_ta_day_du` text NOT NULL,
  `ten_giang_vien` varchar(255) NOT NULL,
  `gioi_thieu_giang_vien` text NOT NULL,
  `loi_ich` text NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_details`
--

INSERT INTO `course_details` (`id`, `ma_khoa_hoc`, `mo_ta_day_du`, `ten_giang_vien`, `gioi_thieu_giang_vien`, `loi_ich`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 1, 'Khóa học PHP Master giúp bạn làm chủ PHP, MySQL và Laravel, từ cơ bản đến chuyên sâu.', 'Huỳnh Thanh Sang', '5+ năm kinh nghiệm PHP/Laravel tại FPT Software, đào tạo hơn 500 học viên và chia sẻ kiến thức trên YouTube.', '[\r\n    {\"title\": \"Chứng chỉ hoàn thành\", \"description\": \"Được công nhận trong ngành IT\"},\r\n    {\"title\": \"Hỗ trợ mentor 1:1\", \"description\": \"Qua Discord và Zoom suốt khóa học\"},\r\n    {\"title\": \"Truy cập trọn đời\", \"description\": \"Toàn bộ bài giảng và code mẫu\"},\r\n    {\"title\": \"Hoàn tiền 100%\", \"description\": \"Nếu không hài lòng trong 30 ngày\"}\r\n]', '2025-10-24 04:08:39', '2025-10-24 04:08:39'),
(2, 2, 'Khóa học React Pro giúp bạn xây dựng giao diện web động, quản lý state hiệu quả, và kết hợp Redux.', 'Huỳnh Thanh Sang', '5+ năm kinh nghiệm Frontend, React/Redux tại FPT Software.', '[ {\"title\": \"Chứng chỉ hoàn thành\", \"description\": \"Được công nhận trong ngành IT\"} ]', '2025-11-14 07:45:59', '2025-11-14 07:45:59'),
(3, 3, 'Khóa học C++ Advanced giúp bạn thành thạo lập trình hệ thống và OOP.', 'Nguyễn Văn A', 'Kinh nghiệm 7+ năm lập trình C++ hệ thống.', '[ {\"title\": \"Chứng chỉ hoàn thành\", \"description\": \"Được công nhận trong ngành IT\"} ]', '2025-11-14 07:45:59', '2025-11-14 07:45:59'),
(4, 4, 'Khóa học Mobile Flutter giúp bạn xây dựng ứng dụng iOS/Android từ A-Z.', 'Trần Thị B', '3+ năm kinh nghiệm Flutter, phát triển ứng dụng mobile.', '[ {\"title\": \"Chứng chỉ hoàn thành\", \"description\": \"Được công nhận trong ngành IT\"} ]', '2025-11-14 07:45:59', '2025-11-14 07:45:59'),
(5, 5, 'Khóa học AI Cơ Bản giúp bạn làm quen Machine Learning & Deep Learning bằng Python.', 'Nguyễn Thị C', 'Kinh nghiệm AI/ML thực chiến, mentor nhiều học viên.', '[ {\"title\": \"Chứng chỉ hoàn thành\", \"description\": \"Được công nhận trong ngành IT\"} ]', '2025-11-14 07:45:59', '2025-11-14 07:45:59'),
(6, 6, 'Khóa học DevOps Thực Chiến giúp bạn làm chủ CI/CD, Docker, Kubernetes và AWS.', 'Trần Văn D', 'Kinh nghiệm DevOps thực chiến, triển khai nhiều dự án doanh nghiệp.', '[ {\"title\": \"Chứng chỉ hoàn thành\", \"description\": \"Được công nhận trong ngành IT\"} ]', '2025-11-14 07:45:59', '2025-11-14 07:45:59'),
(7, 7, 'Khóa học HTML CSS Nâng Cao giúp bạn làm chủ các kỹ thuật hiện đại của HTML5 và CSS3, từ semantic markup đến layout responsive, animations mượt mà và tối ưu hóa performance. Phù hợp cho developer frontend muốn nâng tầm kỹ năng.', 'Nguyễn Minh Tuấn', 'Chuyên gia React & Next.js, 4 năm kinh nghiệm tại Tiki. Đã đào tạo hàng trăm học viên về frontend fundamentals và advanced CSS.', '[\r\n    {\"title\": \"Chứng chỉ hoàn thành\", \"description\": \"Được công nhận trong ngành IT\"},\r\n    {\"title\": \"Hỗ trợ mentor 1:1\", \"description\": \"Qua Discord và Zoom suốt khóa học\"},\r\n    {\"title\": \"Truy cập trọn đời\", \"description\": \"Toàn bộ bài giảng và code mẫu\"},\r\n    {\"title\": \"Hoàn tiền 100%\", \"description\": \"Nếu không hài lòng trong 30 ngày\"},\r\n    {\"title\": \"Dự án thực tế\", \"description\": \"Xây dựng portfolio website responsive\"}\r\n]', '2025-11-30 03:39:00', '2025-11-30 03:39:00');

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
(6, 6),
(7, 2);

-- --------------------------------------------------------

--
-- Table structure for table `course_lessons`
--

CREATE TABLE `course_lessons` (
  `id` int(11) NOT NULL,
  `ma_module` int(11) NOT NULL,
  `ten_bai_hoc` varchar(255) NOT NULL,
  `loai_bai_hoc` enum('video','tai_lieu','kiem_tra') NOT NULL DEFAULT 'video',
  `lien_ket_noi_dung` varchar(500) DEFAULT NULL,
  `thoi_luong` varchar(50) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `thu_tu` int(11) DEFAULT 1,
  `hoan_thanh` tinyint(1) DEFAULT 0,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_lessons`
--

INSERT INTO `course_lessons` (`id`, `ma_module`, `ten_bai_hoc`, `loai_bai_hoc`, `lien_ket_noi_dung`, `thoi_luong`, `mo_ta`, `thu_tu`, `hoan_thanh`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 1, 'Bài 1.1: Giới thiệu PHP và cài đặt XAMPP', 'video', 'https://www.youtube.com/watch?v=btauAEqaifw', '12:45', 'Tổng quan lịch sử PHP, cài đặt môi trường phát triển XAMPP và VS Code.', 1, 0, '2025-11-30 11:54:08', '2025-11-30 15:48:30'),
(2, 1, 'Bài 1.2: Biến, kiểu dữ liệu và toán tử cơ bản', 'video', 'https://www.youtube.com/watch?v=FLs6rAVQWs0', '18:30', 'Khai báo biến, kiểu dữ liệu (string, int, float, bool), toán tử số học và so sánh.', 2, 0, '2025-11-30 11:54:08', '2025-11-30 15:48:30'),
(3, 1, 'Bài 1.3: Cấu trúc điều khiển và vòng lặp', 'tai_lieu', 'https://www.w3schools.com/php/php_looping.asp', NULL, 'Tài liệu chi tiết if/else, switch, for/while/do-while với ví dụ code.', 3, 0, '2025-11-30 11:54:08', '2025-11-30 15:48:30'),
(4, 1, 'Bài 1.4: Bài tập thực hành PHP cơ bản', 'kiem_tra', 'https://quizlet.com/68576128/php-quiz-1-flash-cards/', NULL, 'Quiz 10 câu về biến và vòng lặp, tự động chấm điểm.', 4, 0, '2025-11-30 11:54:08', '2025-11-30 15:48:30'),
(5, 2, 'Bài 2.1: Cài đặt MySQL và tạo database đầu tiên', 'video', 'https://www.youtube.com/watch?v=wgRwITQHszU', '15:20', 'Cài MySQL Workbench, tạo DB và table cơ bản qua phpMyAdmin.', 1, 0, '2025-11-30 11:54:08', '2025-11-30 15:52:43'),
(6, 2, 'Bài 2.2: Kết nối PHP với MySQL bằng PDO', 'video', 'https://www.youtube.com/watch?v=lo1eZvwrhGY', '20:15', 'Sử dụng PDO để kết nối, xử lý lỗi connection.', 2, 0, '2025-11-30 11:54:08', '2025-11-30 15:52:43'),
(7, 2, 'Bài 2.3: Thao tác CRUD (Create, Read, Update, Delete)', 'video', 'https://www.youtube.com/watch?v=XuTUxnOL7cA', '25:40', 'Ví dụ thực tế: Xây dựng form đăng ký user với INSERT/SELECT/UPDATE/DELETE.', 3, 0, '2025-11-30 11:54:08', '2025-11-30 15:52:43'),
(8, 2, 'Bài 2.4: Bảo mật SQL Injection và Joins', 'tai_lieu', 'https://info.veracode.com/rs/790-ZKW-291/images/sqli-prevention-guide-2022-en.pdf', NULL, 'Hướng dẫn prepared statements và ví dụ INNER JOIN, LEFT JOIN.', 4, 0, '2025-11-30 11:54:08', '2025-11-30 15:52:43'),
(9, 3, 'Bài 3.1: Khái niệm OOP và tạo Class/Object', 'video', 'https://www.youtube.com/watch?v=yrFr5PMdk2A', '22:10', 'Constructor, properties, methods; ví dụ class User.', 1, 0, '2025-11-30 11:54:08', '2025-11-30 15:53:20'),
(10, 3, 'Bài 3.2: Kế thừa, đa hình và encapsulation', 'video', 'https://www.youtube.com/watch?v=s3DoSX4FzCs', '28:55', 'Extends class, abstract class, interface, private/protected/public.', 2, 0, '2025-11-30 11:54:08', '2025-11-30 15:53:20'),
(11, 3, 'Bài 3.3: Composer và autoload PSR-4', 'tai_lieu', 'https://a-centauri.com/archivio/Libri/php.pdf', NULL, 'Cài Composer, require packages, tạo autoloader cho project OOP.', 3, 0, '2025-11-30 11:54:08', '2025-11-30 15:53:20'),
(12, 4, 'Bài 4.1: Cài đặt Laravel và cấu trúc project', 'video', 'https://www.youtube.com/watch?v=5IACWsTc_0E', '16:35', 'Composer create-project, artisan commands, môi trường .env.', 1, 0, '2025-11-30 11:54:08', '2025-11-30 15:53:20'),
(13, 4, 'Bài 4.2: Routing và Controller cơ bản', 'video', 'https://www.youtube.com/watch?v=zDNF73Fdb5U', '19:50', 'Định tuyến GET/POST, tạo controller, middleware intro.', 2, 0, '2025-11-30 11:54:08', '2025-11-30 15:53:20'),
(14, 4, 'Bài 4.3: Eloquent ORM và Migration', 'video', 'https://www.youtube.com/watch?v=PgeP3vsWbTc', '24:20', 'Tạo model, migration table, relationships (hasMany, belongsTo).', 3, 0, '2025-11-30 11:54:08', '2025-11-30 15:53:20'),
(15, 4, 'Bài 4.4: Authentication và API RESTful', 'tai_lieu', 'https://www.kufunda.net/publicdocs/Build%20an%20API%20with%20Laravel%20by%20Wacky%20Studio%20%28Thomas%20Gamborg%20N%C3%B8rgaard%29.pdf', NULL, 'Laravel Sanctum, login/register API, JWT token.', 4, 0, '2025-11-30 11:54:08', '2025-11-30 15:53:20'),
(16, 4, 'Bài 4.5: Blade templating và Form validation', 'kiem_tra', 'https://quizlet.com/study-guides/laravel-http-requests-validation-and-blade-views-key-concepts-and-usage-1664d5b0-10c9-4b16-ac0f-084c4b766bce', NULL, 'Quiz về validation rules và Blade directives (@if, @foreach).', 5, 0, '2025-11-30 11:54:08', '2025-11-30 15:53:20'),
(17, 5, 'Bài 5.1: Deployment lên Heroku và AWS', 'video', 'https://www.youtube.com/watch?v=639Pe0PpVLQ', '21:05', 'Procfile, git push heroku, cấu hình database cloud.', 1, 0, '2025-11-30 11:54:08', '2025-11-30 15:53:20'),
(18, 5, 'Bài 5.2: Bảo mật ứng dụng (CSRF, XSS)', 'video', 'https://www.youtube.com/watch?v=NnYN9b_Fp_4', '17:40', 'Middleware auth, encryption, rate limiting.', 2, 0, '2025-11-30 11:54:08', '2025-11-30 15:53:20'),
(19, 5, 'Bài 5.3: Tối ưu hiệu năng với Cache và Queue', 'tai_lieu', 'https://wedareyou.tourismthailand.org/HomePages/virtual-library/Y67749/LaravelQueuesInAction.pdf', NULL, 'Redis caching, Horizon cho job queue, performance tips.', 3, 0, '2025-11-30 11:54:08', '2025-11-30 15:53:20'),
(20, 6, 'Bài 1.1: Giới thiệu Semantic HTML5 và cấu trúc trang web', 'video', 'https://www.youtube.com/watch?v=example-html5-semantic', '15:30', 'Tổng quan về semantic tags như <header>, <nav>, <main>, <article>, <section>, <aside>, <footer>. Ví dụ xây dựng outline trang web chuẩn SEO.', 1, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(21, 6, 'Bài 1.2: Accessibility (A11y) với ARIA và keyboard navigation', 'video', 'https://www.youtube.com/watch?v=example-html5-accessibility', '18:45', 'Sử dụng ARIA roles, states, properties; kiểm tra accessibility với tools như WAVE hoặc Lighthouse.', 2, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(22, 6, 'Bài 1.3: Form elements nâng cao và validation HTML5', 'tai_lieu', 'https://developer.mozilla.org/en-US/docs/Learn/Forms', NULL, 'Tài liệu chi tiết về input types (date, range, color), required, pattern attributes, và custom validation JS.', 3, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(23, 6, 'Bài 1.4: Bài tập: Xây dựng form đăng ký semantic', 'kiem_tra', 'https://quizlet.com/example-html-forms-quiz', NULL, 'Quiz 12 câu về semantic elements và accessibility, với code challenge tự động kiểm tra.', 4, 1, '2025-11-30 10:39:00', '2025-11-30 22:10:48'),
(24, 7, 'Bài 2.1: CSS Selectors nâng cao (pseudo-classes, pseudo-elements)', 'video', 'https://www.youtube.com/watch?v=example-css-selectors', '20:10', 'Nth-child, nth-of-type, :hover, :focus, ::before, ::after với ví dụ thực tế.', 1, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(25, 7, 'Bài 2.2: CSS Custom Properties (Variables) và Functions', 'video', 'https://www.youtube.com/watch?v=example-css-variables', '16:20', 'Tính toán giá trị với calc(), var(), clamp(), min(), max(); fallback values.', 2, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(26, 7, 'Bài 2.3: Giới thiệu SASS/SCSS: Nesting, Mixins và Partials', 'tai_lieu', 'https://sass-lang.com/guide', NULL, 'Cài đặt SASS, nesting rules, @mixin, @extend, variables, và compile sang CSS.', 3, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(27, 7, 'Bài 2.4: Bài tập: Refactor CSS với SASS variables', 'kiem_tra', 'https://quizlet.com/example-sass-quiz', NULL, 'Quiz về selectors và SASS, kèm bài tập refactor code CSS vanilla sang SCSS.', 4, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(28, 8, 'Bài 3.1: Flexbox cơ bản: Container và Items properties', 'video', 'https://www.youtube.com/watch?v=example-flexbox-basics', '22:35', 'display: flex, justify-content, align-items, flex-direction, flex-wrap.', 1, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(29, 8, 'Bài 3.2: Flexbox nâng cao: Order, Grow, Shrink và Align-self', 'video', 'https://www.youtube.com/watch?v=example-flexbox-advanced', '19:50', 'flex-grow, flex-shrink, flex-basis, order, align-self; ví dụ navigation bar.', 2, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(30, 8, 'Bài 3.3: CSS Grid: Grid template và Areas', 'tai_lieu', 'https://css-tricks.com/snippets/css/complete-guide-grid/', NULL, 'grid-template-columns/rows, grid-gap, grid-area, named areas cho layout phức tạp.', 3, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(31, 8, 'Bài 3.4: Bài tập: Layout dashboard với Grid và Flexbox', 'kiem_tra', 'https://quizlet.com/example-grid-flexbox-quiz', NULL, 'Quiz 15 câu, challenge xây dựng responsive grid layout cho dashboard.', 4, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(32, 9, 'Bài 4.1: CSS Transitions: Timing functions và Delay', 'video', 'https://www.youtube.com/watch?v=example-css-transitions', '17:15', 'transition-property, duration, timing-function (ease, cubic-bezier), delay.', 1, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(33, 9, 'Bài 4.2: Keyframe Animations với @keyframes', 'video', 'https://www.youtube.com/watch?v=example-keyframes', '21:40', 'Tạo animation phức tạp như loading spinner, bounce effect; animation-iteration-count.', 2, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(34, 9, 'Bài 4.3: Transforms: Rotate, Scale, Translate và Perspective', 'tai_lieu', 'https://developer.mozilla.org/en-US/docs/Web/CSS/transform', NULL, '2D/3D transforms, transform-origin, perspective cho hiệu ứng 3D card flip.', 3, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(35, 9, 'Bài 4.4: Bài tập: Tạo hero section với animations', 'kiem_tra', 'https://quizlet.com/example-animations-quiz', NULL, 'Quiz về transitions/keyframes, challenge animate elements cho landing page.', 4, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(36, 10, 'Bài 5.1: Media Queries và Mobile-First Approach', 'video', 'https://www.youtube.com/watch?v=example-media-queries', '18:25', 'Breakpoints chuẩn (mobile, tablet, desktop), @media rules, container queries intro.', 1, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(37, 10, 'Bài 5.2: Responsive Images và Viewport Units', 'video', 'https://www.youtube.com/watch?v=example-responsive-images', '14:55', 'srcset, sizes, picture element; vw, vh, vmin, vmax cho fluid design.', 2, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(38, 10, 'Bài 5.3: Best Practices: Performance, Cross-browser và Tools', 'tai_lieu', 'https://web.dev/learn/performance/', NULL, 'Minify CSS, critical CSS, Autoprefixer, testing với BrowserStack; accessibility recap.', 3, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00'),
(39, 10, 'Bài 5.4: Dự án cuối khóa: Xây dựng website portfolio responsive', 'kiem_tra', 'https://quizlet.com/example-responsive-quiz', NULL, 'Project challenge: Tích hợp tất cả kiến thức vào một website đầy đủ, peer review qua GitHub.', 4, 0, '2025-11-30 10:39:00', '2025-11-30 10:39:00');

-- --------------------------------------------------------

--
-- Table structure for table `course_modules`
--

CREATE TABLE `course_modules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `module_name` varchar(255) NOT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_modules`
--

INSERT INTO `course_modules` (`id`, `course_id`, `module_name`, `duration`, `content`, `sort_order`) VALUES
(1, 1, 'Chương 1: PHP Cơ Bản', '2 tuần', 'Cấu trúc, biến, hàm, vòng lặp.', 1),
(2, 1, 'Chương 2: Cơ Sở Dữ Liệu MySQL', '2 tuần', 'Kết nối, CRUD, chống SQL Injection.', 2),
(3, 1, 'Chương 3: Lập Trình OOP & Composer', '2 tuần', 'Class, kế thừa, autoload, Composer.', 3),
(4, 1, 'Chương 4: Laravel Framework', '3 tuần', 'MVC, Eloquent ORM, Authentication, API RESTful.', 4),
(5, 1, 'Chương 5: Triển Khai & Tối Ưu', '1 tuần', 'Triển khai Heroku/AWS, bảo mật, tối ưu hiệu năng.', 5),
(6, 7, 'Chương 1: HTML5 Semantic và Accessibility', '1 tuần', 'Semantic elements (header, nav, article), ARIA attributes, form validation nâng cao.', 1),
(7, 7, 'Chương 2: CSS3 Selectors, Variables và Preprocessors', '1 tuần', 'Advanced selectors, CSS custom properties, SASS/SCSS basics.', 2),
(8, 7, 'Chương 3: Flexbox và CSS Grid Layout', '1 tuần', 'Flexible layouts với Flexbox, Grid system cho responsive design.', 3),
(9, 7, 'Chương 4: Animations, Transitions và Transforms', '1 tuần', 'CSS transitions, keyframe animations, 3D transforms.', 4),
(10, 7, 'Chương 5: Responsive Design, Media Queries và Best Practices', '1 tuần', 'Mobile-first approach, media queries, performance optimization, cross-browser compatibility.', 5);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `ma_khoa_hoc` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ngay_dang_ky` datetime DEFAULT current_timestamp(),
  `trang_thai` enum('dang_hoc','hoan_thanh','bo_hoc') NOT NULL DEFAULT 'dang_hoc',
  `tien_do` decimal(5,2) DEFAULT 0.00,
  `ngay_hoan_thanh` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `ma_khoa_hoc`, `ma_nguoi_dung`, `ngay_dang_ky`, `trang_thai`, `tien_do`, `ngay_hoan_thanh`) VALUES
(7, 1, 1, '2025-12-03 13:51:04', 'dang_hoc', 0.00, NULL),
(8, 2, 1, '2025-12-03 13:51:04', 'dang_hoc', 0.00, NULL),
(9, 7, 1, '2025-12-03 13:53:12', 'dang_hoc', 0.00, NULL),
(10, 7, 1, '2025-12-03 13:53:15', 'dang_hoc', 0.00, NULL);

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
-- Table structure for table `lesson_questions`
--

CREATE TABLE `lesson_questions` (
  `id` int(11) NOT NULL,
  `ma_lesson` int(11) NOT NULL,
  `cau_hoi` text NOT NULL,
  `loai_cau_hoi` enum('multiple','fill','code') NOT NULL DEFAULT 'multiple',
  `options` text DEFAULT NULL,
  `answer` text NOT NULL,
  `diem` int(11) NOT NULL DEFAULT 10,
  `thu_tu` int(11) DEFAULT 1,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lesson_questions`
--

INSERT INTO `lesson_questions` (`id`, `ma_lesson`, `cau_hoi`, `loai_cau_hoi`, `options`, `answer`, `diem`, `thu_tu`, `ngay_tao`) VALUES
(73, 20, 'Semantic tag nào dùng cho phần đầu trang web?', 'multiple', '[{\"label\":\"A. <header>\",\"value\":\"header\"},{\"label\":\"B. <footer>\",\"value\":\"footer\"},{\"label\":\"C. <nav>\",\"value\":\"nav\"}]', '[\"header\"]', 10, 1, '2025-11-30 17:07:47'),
(74, 20, 'Tag <article> dùng để đại diện cho?', 'fill', NULL, 'nội dung độc lập', 10, 2, '2025-11-30 17:07:47'),
(75, 20, 'Viết code HTML semantic cho một section bài viết:', 'code', NULL, '<section><article><h2>Tiêu đề</h2><p>Nội dung</p></article></section>', 10, 3, '2025-11-30 17:07:47'),
(76, 21, 'Thuộc tính ARIA role dùng để?', 'multiple', '[{\"label\":\"A. Mô tả chức năng element\",\"value\":\"role\"},{\"label\":\"B. Định vị layout\",\"value\":\"position\"},{\"label\":\"C. Thay đổi màu\",\"value\":\"color\"}]', '[\"role\"]', 10, 1, '2025-11-30 17:07:47'),
(77, 21, 'ARIA state nào cho element đang focus?', 'fill', NULL, 'aria-selected', 10, 2, '2025-11-30 17:07:47'),
(78, 21, 'Viết code thêm ARIA cho button accessible:', 'code', NULL, '<button aria-label=\"Đóng\" aria-expanded=\"false\">X</button>', 10, 3, '2025-11-30 17:07:47'),
(79, 21, 'Công cụ kiểm tra A11y phổ biến là?', 'multiple', '[{\"label\":\"A. Lighthouse\",\"value\":\"lighthouse\"},{\"label\":\"B. GitHub\",\"value\":\"github\"}]', '[\"lighthouse\"]', 10, 4, '2025-11-30 17:07:47'),
(80, 22, 'Input type nào cho ngày tháng?', 'multiple', '[{\"label\":\"A. date\",\"value\":\"date\"},{\"label\":\"B. text\",\"value\":\"text\"}]', '[\"date\"]', 10, 1, '2025-11-30 17:07:47'),
(81, 22, 'Thuộc tính pattern dùng để?', 'fill', NULL, 'validate regex', 10, 2, '2025-11-30 17:07:47'),
(82, 22, 'Viết form input với required và minlength:', 'code', NULL, '<input type=\"text\" required minlength=\"5\" placeholder=\"Tên\">', 10, 3, '2025-11-30 17:07:47'),
(83, 23, 'Form semantic cần bao gồm label cho accessibility?', 'multiple', '[{\"label\":\"A. Có\",\"value\":\"yes\"},{\"label\":\"B. Không\",\"value\":\"no\"}]', '[\"yes\"]', 10, 1, '2025-11-30 17:07:47'),
(84, 23, 'Tag <fieldset> dùng để nhóm?', 'fill', NULL, 'form elements', 10, 2, '2025-11-30 17:07:47'),
(85, 23, 'Viết code form đăng ký semantic đầy đủ:', 'code', NULL, '<form><fieldset><legend>Đăng ký</legend><label for=\"email\">Email:</label><input id=\"email\" type=\"email\" required></fieldset></form>', 10, 3, '2025-11-30 17:07:47'),
(86, 24, 'Pseudo-class :nth-child(2) chọn?', 'multiple', '[{\"label\":\"A. Element thứ 2\",\"value\":\"second\"},{\"label\":\"B. Element đầu\",\"value\":\"first\"}]', '[\"second\"]', 10, 1, '2025-11-30 17:07:47'),
(87, 24, '::after dùng để thêm?', 'fill', NULL, 'content giả', 10, 2, '2025-11-30 17:07:47'),
(88, 24, 'Viết selector cho li đầu tiên trong ul:', 'code', NULL, 'ul li:first-child { color: red; }', 10, 3, '2025-11-30 17:07:47'),
(89, 25, 'CSS custom property bắt đầu bằng?', 'multiple', '[{\"label\":\"A. --var\",\"value\":\"--var\"},{\"label\":\"B. $var\",\"value\":\"$var\"}]', '[\"--var\"]', 10, 1, '2025-11-30 17:07:47'),
(90, 25, 'Hàm calc() dùng để?', 'fill', NULL, 'tính toán giá trị', 10, 2, '2025-11-30 17:07:47'),
(91, 25, 'Viết code CSS variable cho màu chính:', 'code', NULL, ':root { --primary-color: blue; } .btn { color: var(--primary-color); }', 10, 3, '2025-11-30 17:07:47'),
(92, 25, 'Fallback cho var() là?', 'multiple', '[{\"label\":\"A. Không có\",\"value\":\"none\"},{\"label\":\"B. Giá trị mặc định\",\"value\":\"default\"}]', '[\"default\"]', 10, 4, '2025-11-30 17:07:47'),
(93, 26, 'Nesting trong SASS là?', 'multiple', '[{\"label\":\"A. Nhúng selector\",\"value\":\"nesting\"},{\"label\":\"B. Lặp code\",\"value\":\"loop\"}]', '[\"nesting\"]', 10, 1, '2025-11-30 17:07:47'),
(94, 26, 'Mixin @mixin dùng để?', 'fill', NULL, 'tái sử dụng code', 10, 2, '2025-11-30 17:07:47'),
(95, 26, 'Viết mixin SASS cho button rounded:', 'code', NULL, '@mixin rounded { border-radius: 5px; } .btn { @include rounded; }', 10, 3, '2025-11-30 17:07:47'),
(96, 27, 'SASS compile sang?', 'multiple', '[{\"label\":\"A. CSS\",\"value\":\"css\"},{\"label\":\"B. JS\",\"value\":\"js\"}]', '[\"css\"]', 10, 1, '2025-11-30 17:07:47'),
(97, 27, 'Partials trong SASS bắt đầu bằng?', 'fill', NULL, '_filename', 10, 2, '2025-11-30 17:07:47'),
(98, 27, 'Viết code SASS import partial:', 'code', NULL, '@import \"variables\"; $primary: #007bff; .btn { color: $primary; }', 10, 3, '2025-11-30 17:07:47'),
(99, 27, 'Extend trong SASS dùng @?', 'multiple', '[{\"label\":\"A. @extend\",\"value\":\"extend\"},{\"label\":\"B. @include\",\"value\":\"include\"}]', '[\"extend\"]', 10, 4, '2025-11-30 17:07:47'),
(100, 28, 'justify-content: center làm gì?', 'multiple', '[{\"label\":\"A. Căn giữa ngang\",\"value\":\"center\"},{\"label\":\"B. Căn giữa dọc\",\"value\":\"vertical\"}]', '[\"center\"]', 10, 1, '2025-11-30 17:07:47'),
(101, 28, 'align-items dùng cho?', 'fill', NULL, 'căn dọc flex items', 10, 2, '2025-11-30 17:07:47'),
(102, 28, 'Viết code Flexbox container:', 'code', NULL, '.container { display: flex; justify-content: space-between; align-items: center; }', 10, 3, '2025-11-30 17:07:47'),
(103, 29, 'flex-grow: 1 nghĩa là?', 'multiple', '[{\"label\":\"A. Mở rộng chiếm không gian\",\"value\":\"grow\"},{\"label\":\"B. Thu nhỏ\",\"value\":\"shrink\"}]', '[\"grow\"]', 10, 1, '2025-11-30 17:07:47'),
(104, 29, 'order: -1 dùng để?', 'fill', NULL, 'sắp xếp thứ tự', 10, 2, '2025-11-30 17:07:47'),
(105, 29, 'Viết code flex item với grow và order:', 'code', NULL, '.item { flex-grow: 1; order: -1; }', 10, 3, '2025-11-30 17:07:47'),
(106, 29, 'flex-basis là?', 'multiple', '[{\"label\":\"A. Kích thước ban đầu\",\"value\":\"basis\"},{\"label\":\"B. Màu sắc\",\"value\":\"color\"}]', '[\"basis\"]', 10, 4, '2025-11-30 17:07:47'),
(107, 30, 'grid-template-columns: repeat(3, 1fr) tạo?', 'multiple', '[{\"label\":\"A. 3 cột đều\",\"value\":\"3cols\"},{\"label\":\"B. 1 cột\",\"value\":\"1col\"}]', '[\"3cols\"]', 10, 1, '2025-11-30 17:07:47'),
(108, 30, 'grid-area dùng để?', 'fill', NULL, 'đặt tên vùng grid', 10, 2, '2025-11-30 17:07:47'),
(109, 30, 'Viết code Grid layout 2x2:', 'code', NULL, '.grid { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }', 10, 3, '2025-11-30 17:07:47'),
(110, 31, 'Grid gap là?', 'multiple', '[{\"label\":\"A. Khoảng cách giữa cells\",\"value\":\"gap\"},{\"label\":\"B. Màu nền\",\"value\":\"background\"}]', '[\"gap\"]', 10, 1, '2025-11-30 17:07:47'),
(111, 31, 'align-self cho item?', 'fill', NULL, 'căn tự thân', 10, 2, '2025-11-30 17:07:47'),
(112, 31, 'Viết code dashboard sidebar với Grid:', 'code', NULL, '.dashboard { display: grid; grid-template-areas: \"sidebar main\" \"sidebar footer\"; } .sidebar { grid-area: sidebar; }', 10, 3, '2025-11-30 17:07:47'),
(113, 31, 'justify-self là?', 'multiple', '[{\"label\":\"A. Căn ngang item\",\"value\":\"justify\"},{\"label\":\"B. Căn dọc\",\"value\":\"align\"}]', '[\"justify\"]', 10, 4, '2025-11-30 17:07:47'),
(114, 32, 'transition-duration: 0.5s nghĩa là?', 'multiple', '[{\"label\":\"A. Thời gian chuyển 0.5 giây\",\"value\":\"duration\"},{\"label\":\"B. Độ trễ\",\"value\":\"delay\"}]', '[\"duration\"]', 10, 1, '2025-11-30 17:07:47'),
(115, 32, 'timing-function: ease-in-out dùng cho?', 'fill', NULL, 'hiệu ứng mượt', 10, 2, '2025-11-30 17:07:47'),
(116, 32, 'Viết code transition cho hover:', 'code', NULL, '.box { transition: all 0.3s ease; } .box:hover { transform: scale(1.1); }', 10, 3, '2025-11-30 17:07:47'),
(117, 33, '@keyframes bounce { from { transform: translateY(0); } to { transform: translateY(-10px); } } dùng để?', 'multiple', '[{\"label\":\"A. Tạo animation\",\"value\":\"keyframes\"},{\"label\":\"B. Thay đổi màu\",\"value\":\"color\"}]', '[\"keyframes\"]', 10, 1, '2025-11-30 17:07:47'),
(118, 33, 'animation-iteration-count: infinite là?', 'fill', NULL, 'lặp vô hạn', 10, 2, '2025-11-30 17:07:47'),
(119, 33, 'Viết code animation spinner:', 'code', NULL, '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } } .spinner { animation: spin 1s linear infinite; }', 10, 3, '2025-11-30 17:07:47'),
(120, 33, 'animation-fill-mode: forwards giữ?', 'multiple', '[{\"label\":\"A. Trạng thái cuối\",\"value\":\"forwards\"},{\"label\":\"B. Trạng thái đầu\",\"value\":\"backwards\"}]', '[\"forwards\"]', 10, 4, '2025-11-30 17:07:47'),
(121, 34, 'transform: rotate(45deg) làm gì?', 'multiple', '[{\"label\":\"A. Xoay 45 độ\",\"value\":\"rotate\"},{\"label\":\"B. Phóng to\",\"value\":\"scale\"}]', '[\"rotate\"]', 10, 1, '2025-11-30 17:07:47'),
(122, 34, 'perspective dùng cho?', 'fill', NULL, 'hiệu ứng 3D', 10, 2, '2025-11-30 17:07:47'),
(123, 34, 'Viết code 3D card flip:', 'code', NULL, '.card { perspective: 1000px; } .card-inner { transform-style: preserve-3d; transition: transform 0.6s; } .card:hover .card-inner { transform: rotateY(180deg); }', 10, 3, '2025-11-30 17:07:47'),
(124, 35, 'transform-origin mặc định là?', 'multiple', '[{\"label\":\"A. center\",\"value\":\"center\"},{\"label\":\"B. top left\",\"value\":\"top-left\"}]', '[\"center\"]', 10, 1, '2025-11-30 17:07:47'),
(125, 35, 'scale(1.2) nghĩa là?', 'fill', NULL, 'phóng to 120%', 10, 2, '2025-11-30 17:07:47'),
(126, 35, 'Viết code animate hero button:', 'code', NULL, '.hero-btn { transition: transform 0.3s; } .hero-btn:hover { transform: translateY(-5px) scale(1.05); }', 10, 3, '2025-11-30 17:07:47'),
(127, 35, 'translateX(50px) di chuyển?', 'multiple', '[{\"label\":\"A. Ngang 50px\",\"value\":\"x\"},{\"label\":\"B. Dọc\",\"value\":\"y\"}]', '[\"x\"]', 10, 4, '2025-11-30 17:07:47'),
(128, 36, 'Media query cho mobile là?', 'multiple', '[{\"label\":\"A. @media (max-width: 768px)\",\"value\":\"mobile\"},{\"label\":\"B. @media (min-width: 1200px)\",\"value\":\"desktop\"}]', '[\"mobile\"]', 10, 1, '2025-11-30 17:07:47'),
(129, 36, 'Mobile-first nghĩa là?', 'fill', NULL, 'thiết kế mobile trước', 10, 2, '2025-11-30 17:07:47'),
(130, 36, 'Viết media query cho tablet:', 'code', NULL, '@media (min-width: 768px) and (max-width: 1024px) { .container { width: 90%; } }', 10, 3, '2025-11-30 17:07:47'),
(131, 37, 'srcset dùng để?', 'multiple', '[{\"label\":\"A. Nhiều kích thước ảnh\",\"value\":\"srcset\"},{\"label\":\"B. Màu ảnh\",\"value\":\"color\"}]', '[\"srcset\"]', 10, 1, '2025-11-30 17:07:47'),
(132, 37, 'sizes attribute chỉ?', 'fill', NULL, 'kích thước hiển thị', 10, 2, '2025-11-30 17:07:47'),
(133, 37, 'Viết code picture responsive:', 'code', NULL, '<picture><source srcset=\"img-large.jpg\" media=\"(min-width: 768px)\"><img src=\"img-small.jpg\" alt=\"Responsive image\"></picture>', 10, 3, '2025-11-30 17:07:47'),
(134, 37, 'vw unit là?', 'multiple', '[{\"label\":\"A. Viewport width\",\"value\":\"vw\"},{\"label\":\"B. Viewport height\",\"value\":\"vh\"}]', '[\"vw\"]', 10, 4, '2025-11-30 17:07:47'),
(135, 38, 'Minify CSS để?', 'multiple', '[{\"label\":\"A. Giảm kích thước file\",\"value\":\"minify\"},{\"label\":\"B. Thêm comment\",\"value\":\"comment\"}]', '[\"minify\"]', 10, 1, '2025-11-30 17:07:47'),
(136, 38, 'Autoprefixer dùng cho?', 'fill', NULL, 'thêm vendor prefix', 10, 2, '2025-11-30 17:07:47'),
(137, 38, 'Viết code critical CSS inline:', 'code', NULL, '<style>.hero { background: red; }</style><link rel=\"stylesheet\" href=\"styles.css\">', 10, 3, '2025-11-30 17:07:47'),
(138, 39, 'Container queries (CSS4) dùng để?', 'multiple', '[{\"label\":\"A. Responsive theo container\",\"value\":\"container\"},{\"label\":\"B. Theo viewport\",\"value\":\"media\"}]', '[\"container\"]', 10, 1, '2025-11-30 17:07:47'),
(139, 39, 'Cross-browser testing tool?', 'fill', NULL, 'BrowserStack', 10, 2, '2025-11-30 17:07:47'),
(140, 39, 'Viết media query desktop cho portfolio:', 'code', NULL, '@media (min-width: 1200px) { .portfolio-grid { grid-template-columns: repeat(3, 1fr); } }', 10, 3, '2025-11-30 17:07:47'),
(141, 39, 'Performance metric quan trọng?', 'multiple', '[{\"label\":\"A. Largest Contentful Paint\",\"value\":\"LCP\"},{\"label\":\"B. File size\",\"value\":\"size\"}]', '[\"LCP\"]', 10, 4, '2025-11-30 17:07:47');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `tong_tien` decimal(10,2) NOT NULL,
  `trang_thai` enum('chờ duyệt','đã duyệt','đã hủy') DEFAULT 'chờ duyệt',
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `fullname`, `email`, `phone`, `address`, `tong_tien`, `trang_thai`, `ngay_tao`) VALUES
(32, 1, 'Thanh Sang', 'sang022101032@tgu.edu.vn', '0915131493', 'Bến Tre', 3000.00, 'đã hủy', '2025-12-03 13:51:04');

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
(29, 32, 1, 1, 2000.00),
(30, 32, 2, 1, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `refund_requests`
--

CREATE TABLE `refund_requests` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refund_requests`
--

INSERT INTO `refund_requests` (`id`, `order_id`, `reason`, `status`, `admin_note`, `created_at`, `updated_at`) VALUES
(1, 32, 'dở', 'pending', NULL, '2025-12-05 14:51:09', '2025-12-05 14:51:09');

-- --------------------------------------------------------

--
-- Table structure for table `refund_transactions`
--

CREATE TABLE `refund_transactions` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `course_id`, `user_id`, `rating`, `comment`, `ngay_tao`) VALUES
(4, 1, 1, 5, 'Khóa học rất hay và bổ ích', '2025-12-03 13:59:19'),
(5, 1, 1, 5, 'Khóa học tuyệ vời luôn', '2025-12-03 14:00:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `fullname` varchar(150) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `avatar` varchar(255) DEFAULT 'uploads/avatars/default.png',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `fullname`, `email`, `phone`, `address`, `password`, `role`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'Thanh Sang', 'Thanh Sang', 'sang022101032@tgu.edu.vn', '0915131493', 'Bến Tre', '111111', 'admin', '', '2025-10-17 15:40:34', '2025-12-01 08:02:59'),
(2, 'Gia Bảo', 'Gia Bảo', 'bao022101023@tgu.edu.vn', '093445232', 'mỹ tho', '111111', 'user', '', '2025-10-31 14:56:58', '2025-11-14 14:27:39');

-- --------------------------------------------------------

--
-- Table structure for table `user_quiz_answers`
--

CREATE TABLE `user_quiz_answers` (
  `id` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ma_lesson` int(11) NOT NULL,
  `ma_cau_hoi` int(11) NOT NULL,
  `cau_tra_loi` text NOT NULL,
  `ai_phan_hoi` text DEFAULT NULL,
  `diem_dat_duoc` decimal(5,2) DEFAULT 0.00,
  `ngay_nop` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_quiz_answers`
--

INSERT INTO `user_quiz_answers` (`id`, `ma_nguoi_dung`, `ma_lesson`, `ma_cau_hoi`, `cau_tra_loi`, `ai_phan_hoi`, `diem_dat_duoc`, `ngay_nop`) VALUES
(28, 1, 23, 83, '[\"yes\"]', '\"\"', 10.00, '2025-11-30 22:17:11'),
(29, 1, 23, 84, '\"form elements\"', '\"\"', 10.00, '2025-11-30 22:17:11'),
(30, 1, 23, 85, '\"không biết\"', '\"Câu trả lời không phải là code. Cần viết code HTML cho một form đăng ký semantic.\"', 0.00, '2025-11-30 22:17:11'),
(43, 1, 27, 96, '[\"css\"]', '\"\"', 10.00, '2025-12-03 15:48:16'),
(44, 1, 27, 97, '\"_filename\"', '\"\"', 10.00, '2025-12-03 15:48:16'),
(45, 1, 27, 98, '\"@import \\\"variables\\\"; $primary: #007bff; .btn { color:\"', '\"Code gần đúng nhưng chưa hoàn chỉnh. Thiếu phần sử dụng biến \'$primary\' và dấu đóng \'}\'.\"', 8.00, '2025-12-03 15:48:16'),
(46, 1, 27, 99, '[\"extend\"]', '\"\"', 10.00, '2025-12-03 15:48:16');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `discount_type` enum('fixed','percent') NOT NULL COMMENT 'fixed = số tiền cố định, percent = phần trăm',
  `min_order_value` decimal(10,2) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `description`, `discount_value`, `discount_type`, `min_order_value`, `start_date`, `end_date`, `usage_limit`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SAVE10', 'Giảm 10% cho tất cả khóa học', 10.00, 'percent', 0.00, '2025-12-05', '2026-01-04', NULL, 'active', '2025-12-04 23:52:19', '2025-12-04 23:52:19'),
(2, 'SAVE500K', 'Giảm 500,000 ₫ cho đơn hàng từ 2,000,000 ₫', 500000.00, 'fixed', 2000000.00, '2025-12-05', '2026-01-04', NULL, 'active', '2025-12-04 23:52:19', '2025-12-04 23:52:19'),
(3, 'WELCOME50K', 'Giảm 50,000 ₫ cho khách hàng mới', 50000.00, 'fixed', 0.00, '2025-12-05', '2026-02-03', 100, 'active', '2025-12-04 23:52:19', '2025-12-04 23:52:19');

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
-- Indexes for table `chat_history`
--
ALTER TABLE `chat_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_session` (`user_id`,`session_id`),
  ADD KEY `idx_created_at` (`created_at`);

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
-- Indexes for table `course_lessons`
--
ALTER TABLE `course_lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ma_module` (`ma_module`);

--
-- Indexes for table `course_modules`
--
ALTER TABLE `course_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ma_khoa_hoc` (`ma_khoa_hoc`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lesson_questions`
--
ALTER TABLE `lesson_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ma_lesson` (`ma_lesson`);

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
-- Indexes for table `refund_requests`
--
ALTER TABLE `refund_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `refund_transactions`
--
ALTER TABLE `refund_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `user_quiz_answers`
--
ALTER TABLE `user_quiz_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `ma_lesson` (`ma_lesson`),
  ADD KEY `ma_cau_hoi` (`ma_cau_hoi`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `chat_history`
--
ALTER TABLE `chat_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `course_details`
--
ALTER TABLE `course_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `course_lessons`
--
ALTER TABLE `course_lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `course_modules`
--
ALTER TABLE `course_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `lesson_questions`
--
ALTER TABLE `lesson_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `refund_requests`
--
ALTER TABLE `refund_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `refund_transactions`
--
ALTER TABLE `refund_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_quiz_answers`
--
ALTER TABLE `user_quiz_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_history`
--
ALTER TABLE `chat_history`
  ADD CONSTRAINT `fk_chat_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `course_lessons`
--
ALTER TABLE `course_lessons`
  ADD CONSTRAINT `course_lessons_ibfk_1` FOREIGN KEY (`ma_module`) REFERENCES `course_modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_modules`
--
ALTER TABLE `course_modules`
  ADD CONSTRAINT `course_modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`ma_khoa_hoc`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_questions`
--
ALTER TABLE `lesson_questions`
  ADD CONSTRAINT `lesson_questions_ibfk_1` FOREIGN KEY (`ma_lesson`) REFERENCES `course_lessons` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `refund_requests`
--
ALTER TABLE `refund_requests`
  ADD CONSTRAINT `refund_requests_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_quiz_answers`
--
ALTER TABLE `user_quiz_answers`
  ADD CONSTRAINT `user_quiz_answers_ibfk_1` FOREIGN KEY (`ma_lesson`) REFERENCES `course_lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_quiz_answers_ibfk_2` FOREIGN KEY (`ma_cau_hoi`) REFERENCES `lesson_questions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
