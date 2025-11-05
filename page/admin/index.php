<?php
// index.php — điểm khởi đầu cho trang admin

// Nếu cần, có thể kiểm tra đăng nhập admin ở đây
// session_start();
// if (!isset($_SESSION['admin'])) {
//   header("Location: ../login.php");
//   exit;
// }

// Gọi layout admin (chứa sidebar + main)
include 'layout.php';
