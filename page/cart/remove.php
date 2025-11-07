<?php
session_start();
include "../../db.php";

// Nếu chưa đăng nhập thì quay về đăng nhập
if (!isset($_SESSION['id'])) {
    header("Location: /page/login/login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Kiểm tra xem có tham số index hoặc id giỏ hàng được gửi lên không
if (!isset($_GET['index'])) {
    header("Location: cart.php");
    exit;
}

// Dùng index để xác định id giỏ hàng thực tế
$index = (int)$_GET['index'];

// Lấy danh sách giỏ hàng để tìm id thực
$sql = "
    SELECT c.id
    FROM carts c
    JOIN courses cs ON cs.id = c.course_id
    WHERE c.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart = [];

while ($row = $result->fetch_assoc()) {
    $cart[] = $row;
}

// Nếu index tồn tại trong mảng thì xóa theo id thực
if (isset($cart[$index])) {
    $cart_id = $cart[$index]['id'];
    $delete_stmt = $conn->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $cart_id, $user_id);
    $delete_stmt->execute();
}

// Quay lại trang giỏ hàng
header("Location: cart.php");
exit;
?>
