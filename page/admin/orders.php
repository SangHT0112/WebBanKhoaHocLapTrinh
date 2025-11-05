<?php
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../controllers/OrderController.php';

$db = new Database();
$conn = $db->connect();
$controller = new OrderController($conn);

if (isset($_POST['update'])) {
  $controller->update($_POST['order_id'], $_POST['status']);
  header("Location: orders.php");
  exit();
}

$orders = $controller->index();
?>

<div class="bg-white shadow rounded-lg p-6">
  <h1 class="text-2xl font-bold mb-4">🧾 Quản lý đơn đặt hàng</h1>

  <div class="overflow-x-auto">
    <table class="w-full border text-sm">
      <thead class="bg-blue-600 text-white">
        <tr>
          <th class="px-3 py-2">ID</th>
          <th class="px-3 py-2">Khách hàng</th>
          <th class="px-3 py-2">Tổng tiền</th>
          <th class="px-3 py-2">Trạng thái</th>
          <th class="px-3 py-2">Ngày tạo</th>
          <th class="px-3 py-2">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($order = $orders->fetch_assoc()): ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="px-3 py-2"><?= $order['id'] ?></td>
          <td class="px-3 py-2"><?= htmlspecialchars($order['username']) ?></td>
          <td class="px-3 py-2 text-green-600 font-semibold"><?= number_format($order['tong_tien'], 0, ',', '.') ?> ₫</td>
          <td class="px-3 py-2">
            <span class="
              px-2 py-1 rounded text-white 
              <?= $order['trang_thai'] === 'đã duyệt' ? 'bg-green-600' : ($order['trang_thai'] === 'đã hủy' ? 'bg-red-600' : 'bg-yellow-500') ?>
            ">
              <?= $order['trang_thai'] ?>
            </span>
          </td>
          <td class="px-3 py-2"><?= $order['ngay_tao'] ?></td>
          <td class="px-3 py-2">
            <form method="POST" class="flex gap-2">
              <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
              <select name="status" class="border rounded px-2 py-1">
                <option value="chờ duyệt" <?= $order['trang_thai'] === 'chờ duyệt' ? 'selected' : '' ?>>Chờ duyệt</option>
                <option value="đã duyệt" <?= $order['trang_thai'] === 'đã duyệt' ? 'selected' : '' ?>>Đã duyệt</option>
                <option value="đã hủy" <?= $order['trang_thai'] === 'đã hủy' ? 'selected' : '' ?>>Đã hủy</option>
              </select>
              <button name="update" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Lưu</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
