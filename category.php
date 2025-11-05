<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/CourseController.php';

// Kết nối DB
$db = (new Database())->connect();
// Tạo controller
$controller = new CourseController($db);
// Lấy danh sách khóa học
$courses = $controller->model->getAll();

$category = $_GET['cat'] ?? 'all';

if ($category === 'all') {
  $courses = $controller->model->getAll();
} else {
  $courses = $controller->model->getByCategory($category);
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include __DIR__ . '/layout/head.php'; ?>
  <link rel="stylesheet" href="category.css">
</head>
<body>
  <header>
    <?php include __DIR__ . '/layout/header.php'; ?>
  </header>

  <section class="courses-section">
    <div class="filter-menu">
      <div class="filter-menu">
        <a href="category.php?cat=all" class="active">Tất Cả</a>
        <a href="category.php?cat=backend">Backend</a>
        <a href="category.php?cat=frontend">Frontend</a>
        <a href="category.php?cat=system">System</a>
        <a href="category.php?cat=mobile">Mobile</a>
        <a href="category.php?cat=ai">AI / Machine Learning</a>
        <a href="category.php?cat=devops">DevOps</a>
      </div>

    </div>

    <div class="courses-grid" id="categoryCourses">
      <?php foreach ($courses as $course): ?>
        <div class="course-card" data-category="<?= strtolower($course['danh_muc']) ?>" onclick="goToDetail(<?= $course['id'] ?>)">
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

  <footer>
    <p>&copy; 2025 Code Cùng Sang. Tất cả quyền được bảo lưu.</p>
  </footer>

  <script>
    function filterCategory(cat) {
      const cards = document.querySelectorAll('.course-card');
      cards.forEach(card => {
        if (cat === 'all' || card.dataset.category === cat) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
      document.querySelectorAll('.filter-menu button').forEach(btn => btn.classList.remove('active'));
      event.target.classList.add('active');
    }

    function goToDetail(id) {
      window.location.href = `course-detail.php?id=${id}`;
    }
  </script>
</body>
</html>
