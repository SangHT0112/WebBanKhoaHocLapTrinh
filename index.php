<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/CourseController.php';

$db = (new Database())->connect();
$controller = new CourseController($db);
$courses = $controller->model->getAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include __DIR__ . '/layout/head.php'; ?>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
  <header>
    <?php include __DIR__ . '/layout/header.php'; ?>
  </header>

  <section id="home" class="hero">
    <div class="hero-content">
      <h1>Khám Phá Lộ Trình Học Lập Trình Tương Lai</h1>
      <p>Từ PHP Backend đến React Frontend và C++ System, chúng tôi mang đến kiến thức thực chiến để bạn tỏa sáng trong thế giới code.</p>
      <a href="category.php" class="cta-button">Bắt Đầu Học Ngay</a>
    </div>
  </section>

  <section id="courses" class="courses-section">
    <h2 class="section-title">Các Lộ Trình Nổi Bật</h2>
    <div class="courses-grid">
      <?php foreach ($courses as $course): ?>
        <div class="course-card" onclick="goToDetail(<?= $course['id'] ?>)">
          <div class="course-icon"><?= htmlspecialchars($course['bieu_tuong']) ?></div>
          <h3 class="course-title"><?= htmlspecialchars($course['ten_khoa_hoc']) ?></h3>
          <p class="course-desc"><?= htmlspecialchars($course['mo_ta']) ?></p>
          <div class="price"><?= number_format($course['gia']) ?> VNĐ</div>
          <div class="stats">
            <span><?= $course['so_hoc_vien'] ?> học viên</span>
            <span><?= $course['so_gio_hoc'] ?> giờ</span>
          </div>
          <button class="enroll-btn">Chi Tiết</button>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <footer id="contact">
    <p>&copy; 2025 Code Cùng Sang. Tất cả quyền được bảo lưu. | Liên hệ: huynhtsang2004@gmail.com</p>
  </footer>

  <?php include __DIR__ . '/layout/chat-ai.php'; ?>
  <?php include __DIR__ . '/layout/catalogue.php'; ?>

  <script>
    function goToDetail(id) {
      window.location.href = `course-detail.php?id=${id}`;
    }
  </script>
  
</body>
</html>