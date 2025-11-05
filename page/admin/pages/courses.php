<?php
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../controllers/CourseController.php';

$db = new Database();        // ✅ Khởi tạo class Database
$conn = $db->connect(); 
$controller = new CourseController($conn);
$courses = $controller->model->getAll();
?>

<div class="bg-white shadow rounded-lg p-6">
  <h1 class="text-2xl font-bold mb-4">📚 Danh sách Khóa học</h1>
  <a href="#" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded mb-4 inline-block">+ Thêm khóa học</a>

  <div class="overflow-x-auto">
    <table class="w-full border text-sm">
      <thead class="bg-blue-600 text-white">
        <tr>
          <th class="px-3 py-2">ID</th>
          <th class="px-3 py-2">Tên khóa học</th>
          <th class="px-3 py-2">Giá</th>
          <th class="px-3 py-2">Học viên</th>
          <th class="px-3 py-2">Ảnh</th>
          <th class="px-3 py-2">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($courses as $c): ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="px-3 py-2"><?= $c['id'] ?></td>
          <td class="px-3 py-2"><?= htmlspecialchars($c['ten_khoa_hoc']) ?></td>
          <td class="px-3 py-2"><?= number_format($c['gia'],0,',','.') ?>₫</td>
          <td class="px-3 py-2"><?= $c['so_hoc_vien'] ?></td>
          <td class="px-3 py-2">
            <?php if ($c['anh_dai_dien']): ?>
              <img src="<?= $c['anh_dai_dien'] ?>" class="w-14 h-14 object-cover rounded">
            <?php else: ?>
              <span class="text-gray-400 italic">Không có</span>
            <?php endif; ?>
          </td>
          <td class="px-3 py-2">
            <a href="#" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">Sửa</a>
            <a href="#" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">Xóa</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
