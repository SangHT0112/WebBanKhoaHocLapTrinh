<?php
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../controllers/UserController.php';

$db = new Database();
$conn = $db->connect();

$controller = new UserController($conn);
$users = $controller->index();
?>

<div class="bg-white shadow rounded-lg p-6">
  <h1 class="text-2xl font-bold mb-4">👥 Danh sách Người dùng</h1>

  <div class="overflow-x-auto">
    <table class="w-full border text-sm">
      <thead class="bg-indigo-600 text-white">
        <tr>
          <th class="px-3 py-2">ID</th>
          <th class="px-3 py-2">Avatar</th>
          <th class="px-3 py-2">Username</th>
          <th class="px-3 py-2">Email</th>
          <th class="px-3 py-2">Role</th>
          <th class="px-3 py-2">Ngày tạo</th>
          <th class="px-3 py-2">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="px-3 py-2"><?= $u['id'] ?></td>
          <td class="px-3 py-2">
            <img src="<?= $u['avatar'] ?>" alt="avatar" class="w-10 h-10 rounded-full object-cover">
          </td>
          <td class="px-3 py-2 font-semibold"><?= htmlspecialchars($u['username']) ?></td>
          <td class="px-3 py-2"><?= htmlspecialchars($u['email']) ?></td>
          <td class="px-3 py-2">
            <span class="px-2 py-1 rounded text-white 
              <?= $u['role'] === 'admin' ? 'bg-red-600' : 'bg-green-600' ?>">
              <?= $u['role'] ?>
            </span>
          </td>
          <td class="px-3 py-2"><?= $u['created_at'] ?></td>
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
