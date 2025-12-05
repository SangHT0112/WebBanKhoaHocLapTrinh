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

if (isset($_POST['update_voucher'])) {
  $order_id = $_POST['order_id'];
  $voucher_id = $_POST['voucher_id'] ? $_POST['voucher_id'] : null;
  
  $update_query = "UPDATE orders SET voucher_id = ? WHERE id = ?";
  $stmt = $conn->prepare($update_query);
  $stmt->bind_param("ii", $voucher_id, $order_id);
  $stmt->execute();
  $stmt->close();
  
  header("Location: orders.php");
  exit();
}

// Lấy danh sách vouchers
$vouchers_query = "SELECT id, code, description, discount_value, discount_type FROM vouchers WHERE status = 'active' ORDER BY code";
$vouchers_result = $conn->query($vouchers_query);
$vouchers = [];
while ($v = $vouchers_result->fetch_assoc()) {
  $vouchers[] = $v;
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
          <th class="px-3 py-2">Voucher</th>
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
            <form method="POST" class="inline-block">
              <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
              <select name="voucher_id" class="border rounded px-2 py-1 text-sm" onchange="this.form.submit()">
                <option value="">-- Chọn voucher --</option>
                <?php foreach ($vouchers as $voucher): ?>
                  <option value="<?= $voucher['id'] ?>" <?= isset($order['voucher_id']) && $order['voucher_id'] == $voucher['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($voucher['code']) ?> - <?= number_format($voucher['discount_value']) ?><?= $voucher['discount_type'] === 'percent' ? '%' : '₫' ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="update_voucher" value="1">
            </form>
          </td>
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
              <select name="status" class="border rounded px-2 py-1 text-sm">
                <option value="chờ duyệt" <?= $order['trang_thai'] === 'chờ duyệt' ? 'selected' : '' ?>>Chờ duyệt</option>
                <option value="đã duyệt" <?= $order['trang_thai'] === 'đã duyệt' ? 'selected' : '' ?>>Đã duyệt</option>
                <option value="đã hủy" <?= $order['trang_thai'] === 'đã hủy' ? 'selected' : '' ?>>Đã hủy</option>
              </select>
              <button name="update" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">Lưu</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
