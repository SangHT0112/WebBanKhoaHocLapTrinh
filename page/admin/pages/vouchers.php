<?php
// Admin panel - Quản lý vouchers (thêm vào page/admin/pages/vouchers.php)
session_start();
include "../../../db.php";

// Kiểm tra user là admin (tùy chỉnh theo system của bạn)
if (!isset($_SESSION['admin']) && !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /page/login/login.php");
    exit;
}

// Xử lý thêm voucher mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $code = trim($_POST['code']);
        $description = trim($_POST['description']);
        $discount_value = floatval($_POST['discount_value']);
        $discount_type = $_POST['discount_type'];
        $min_order_value = !empty($_POST['min_order_value']) ? floatval($_POST['min_order_value']) : NULL;
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : NULL;
        $status = $_POST['status'];

        $sql = "INSERT INTO vouchers (code, description, discount_value, discount_type, min_order_value, start_date, end_date, usage_limit, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdidsdis", $code, $description, $discount_value, $discount_type, $min_order_value, $start_date, $end_date, $usage_limit, $status);
        
        if ($stmt->execute()) {
            $message = "✅ Voucher đã được thêm thành công!";
        } else {
            $message = "❌ Lỗi: " . $conn->error;
        }
    } elseif ($_POST['action'] === 'edit') {
        $id = intval($_POST['id']);
        $code = trim($_POST['code']);
        $description = trim($_POST['description']);
        $discount_value = floatval($_POST['discount_value']);
        $discount_type = $_POST['discount_type'];
        $min_order_value = !empty($_POST['min_order_value']) ? floatval($_POST['min_order_value']) : NULL;
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : NULL;
        $status = $_POST['status'];

        $sql = "UPDATE vouchers SET code=?, description=?, discount_value=?, discount_type=?, min_order_value=?, start_date=?, end_date=?, usage_limit=?, status=? WHERE id=?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdidsdisi", $code, $description, $discount_value, $discount_type, $min_order_value, $start_date, $end_date, $usage_limit, $status, $id);
        
        if ($stmt->execute()) {
            $message = "✅ Voucher đã được cập nhật!";
        } else {
            $message = "❌ Lỗi: " . $conn->error;
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM vouchers WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "✅ Voucher đã được xóa!";
        } else {
            $message = "❌ Lỗi: " . $conn->error;
        }
    }
}

// Lấy danh sách vouchers
$sql = "SELECT * FROM vouchers ORDER BY created_at DESC";
$result = $conn->query($sql);
$vouchers = [];
while ($row = $result->fetch_assoc()) {
    $vouchers[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Voucher</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">🎟️ Quản Lý Voucher</h1>

        <?php if (isset($message)): ?>
            <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-800 rounded">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Form thêm voucher -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
            <h2 class="text-xl font-bold mb-4">➕ Thêm Voucher Mới</h2>
            <form method="POST" class="grid grid-cols-2 gap-4">
                <input type="hidden" name="action" value="add">
                
                <input type="text" name="code" placeholder="Mã voucher (VD: SAVE10)" required class="border p-2 rounded col-span-1">
                <input type="text" name="description" placeholder="Mô tả (VD: Giảm 10%)" required class="border p-2 rounded col-span-1">
                
                <input type="number" name="discount_value" placeholder="Giá trị giảm" step="0.01" required class="border p-2 rounded">
                <select name="discount_type" required class="border p-2 rounded">
                    <option value="fixed">Cố định (VNĐ)</option>
                    <option value="percent">Phần trăm (%)</option>
                </select>
                
                <input type="number" name="min_order_value" placeholder="Giá tối thiểu (nếu có)" step="0.01" class="border p-2 rounded col-span-2">
                
                <input type="date" name="start_date" required class="border p-2 rounded">
                <input type="date" name="end_date" required class="border p-2 rounded">
                
                <input type="number" name="usage_limit" placeholder="Giới hạn lượt sử dụng (nếu có)" class="border p-2 rounded">
                <select name="status" required class="border p-2 rounded">
                    <option value="active">Kích hoạt</option>
                    <option value="inactive">Tắt</option>
                </select>
                
                <button type="submit" class="bg-green-600 text-white p-2 rounded col-span-2 hover:bg-green-700">✅ Thêm Voucher</button>
            </form>
        </div>

        <!-- Danh sách vouchers -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold mb-4">📋 Danh Sách Voucher</h2>
            
            <?php if (count($vouchers) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-blue-100">
                                <th class="border p-2">Mã</th>
                                <th class="border p-2">Mô Tả</th>
                                <th class="border p-2">Giảm Giá</th>
                                <th class="border p-2">Ngày Bắt Đầu</th>
                                <th class="border p-2">Ngày Kết Thúc</th>
                                <th class="border p-2">Trạng Thái</th>
                                <th class="border p-2">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vouchers as $voucher): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="border p-2 font-bold"><?= htmlspecialchars($voucher['code']) ?></td>
                                    <td class="border p-2"><?= htmlspecialchars(substr($voucher['description'], 0, 30)) ?>...</td>
                                    <td class="border p-2">
                                        <?= number_format($voucher['discount_value'], 2) ?>
                                        <?= $voucher['discount_type'] === 'percent' ? '%' : '₫' ?>
                                    </td>
                                    <td class="border p-2"><?= $voucher['start_date'] ?></td>
                                    <td class="border p-2"><?= $voucher['end_date'] ?></td>
                                    <td class="border p-2">
                                        <span class="<?= $voucher['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> px-2 py-1 rounded">
                                            <?= $voucher['status'] === 'active' ? '✅ Hoạt động' : '❌ Tắt' ?>
                                        </span>
                                    </td>
                                    <td class="border p-2 text-center">
                                        <button onclick="editVoucher(<?= $voucher['id'] ?>)" class="text-blue-600 hover:underline mr-2">✏️ Sửa</button>
                                        <button onclick="deleteVoucher(<?= $voucher['id'] ?>)" class="text-red-600 hover:underline">🗑️ Xóa</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-600">Chưa có voucher nào. Thêm voucher mới ở phía trên!</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function deleteVoucher(id) {
            Swal.fire({
                title: 'Xóa voucher?',
                text: "Bạn chắc chắn muốn xóa voucher này?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Xóa'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>
