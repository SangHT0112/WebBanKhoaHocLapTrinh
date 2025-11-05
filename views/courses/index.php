<!DOCTYPE html>
<html lang="vi">
<head>
  <title>Danh sách khóa học</title>
  <link rel="stylesheet" href="../../assets/css/category.css">
</head>
<body>
  <h2>Danh sách Khóa học</h2>
  <div class="courses-grid">
    <?php while ($row = $courses->fetch_assoc()): ?>
      <div class="course-card" onclick="location.href='?action=detail&id=<?= $row['id'] ?>'">
        <div class="course-icon"><?= $row['bieu_tuong'] ?></div>
        <h3><?= $row['ten_khoa_hoc'] ?></h3>
        <p><?= $row['mo_ta'] ?></p>
        <div><?= $row['gia'] ?> VNĐ</div>
      </div>
    <?php endwhile; ?>
  </div>
</body>
</html>
