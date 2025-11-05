<?php
session_start();
include "../../db.php";

if (!isset($_SESSION['id'])) {
    header("Location: /page/login/login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Lấy giỏ hàng của user
$sql = "
    SELECT c.course_id, cs.ten_khoa_hoc, cs.gia, c.quantity
    FROM carts c
    JOIN courses cs ON cs.id = c.course_id
    WHERE c.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total += $row['gia'] * $row['quantity'];
}

if (count($items) === 0) {
    die("Giỏ hàng trống.");
}

// --- Bắt đầu lưu đơn hàng ---
$conn->begin_transaction();

try {
    // Thêm vào bảng orders
    $insertOrder = $conn->prepare("
        INSERT INTO orders (user_id, tong_tien, trang_thai, ngay_tao)
        VALUES (?, ?, 'chờ duyệt', NOW())
    ");
    $insertOrder->bind_param("id", $user_id, $total);
    $insertOrder->execute();
    $order_id = $conn->insert_id;

    // Thêm từng khóa học vào order_item
    $insertItem = $conn->prepare("
        INSERT INTO order_items (order_id, course_id, so_luong, don_gia)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($items as $item) {
        $insertItem->bind_param("iiid", $order_id, $item['course_id'], $item['quantity'], $item['gia']);
        $insertItem->execute();
    }

    // Xóa giỏ hàng sau khi đặt
    $deleteCart = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
    $deleteCart->bind_param("i", $user_id);
    $deleteCart->execute();

    $conn->commit();
    header("Location: /page/orders/success.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Lỗi khi lưu đơn hàng: " . $e->getMessage());
}
?>
