<?php
session_start();
include "../../db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập']);
    exit;
}

if (!isset($_POST['voucher_code']) || empty($_POST['voucher_code'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập mã voucher']);
    exit;
}

$voucher_code = trim($_POST['voucher_code']);
$user_id = $_SESSION['id'];

// Lấy thông tin voucher từ database
$sql = "SELECT * FROM vouchers WHERE code = ? AND status = 'active' AND CURDATE() BETWEEN start_date AND end_date";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi database']);
    exit;
}

$stmt->bind_param("s", $voucher_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Mã voucher không hợp lệ hoặc đã hết hạn']);
    exit;
}

$voucher = $result->fetch_assoc();

// Kiểm tra giới hạn lượt sử dụng
if ($voucher['usage_limit'] !== null && $voucher['usage_limit'] > 0) {
    $check_usage = "SELECT COUNT(*) as used FROM orders WHERE voucher_id = ?";
    $stmt2 = $conn->prepare($check_usage);
    $stmt2->bind_param("i", $voucher['id']);
    $stmt2->execute();
    $usage = $stmt2->get_result()->fetch_assoc();
    
    if ($usage['used'] >= $voucher['usage_limit']) {
        echo json_encode(['status' => 'error', 'message' => 'Mã voucher đã hết lượt sử dụng']);
        exit;
    }
}

// Kiểm tra giá trị đơn hàng tối thiểu (nếu có)
if ($voucher['min_order_value'] !== null && $voucher['min_order_value'] > 0) {
    // Tính tổng giá trị giỏ hàng
    $get_cart = "
        SELECT SUM(cs.gia * c.quantity) as total
        FROM carts c
        JOIN courses cs ON cs.id = c.course_id
        WHERE c.user_id = ?
    ";
    $stmt3 = $conn->prepare($get_cart);
    $stmt3->bind_param("i", $user_id);
    $stmt3->execute();
    $cart_total = $stmt3->get_result()->fetch_assoc()['total'];
    
    if ($cart_total < $voucher['min_order_value']) {
        $min_required = number_format($voucher['min_order_value'], 0, ',', '.');
        echo json_encode([
            'status' => 'error', 
            'message' => 'Giá trị đơn hàng tối thiểu là ' . $min_required . ' ₫'
        ]);
        exit;
    }
}
// Lưu voucher vào session (hỗ trợ nhiều voucher)
if (!isset($_SESSION['applied_vouchers']) || !is_array($_SESSION['applied_vouchers'])) {
    $_SESSION['applied_vouchers'] = [];
}

// Kiểm tra đã áp dụng trước đó chưa
foreach ($_SESSION['applied_vouchers'] as $av) {
    if ($av['id'] == $voucher['id'] || $av['code'] === $voucher['code']) {
        echo json_encode(['status' => 'error', 'message' => 'Voucher đã được áp dụng trước đó']);
        exit;
    }
}

$new = [
    'id' => $voucher['id'],
    'code' => $voucher['code'],
    'discount_value' => $voucher['discount_value'],
    'discount_type' => $voucher['discount_type'], // 'fixed' hoặc 'percent'
    'description' => $voucher['description']
];

$_SESSION['applied_vouchers'][] = $new;

echo json_encode([
    'status' => 'success',
    'message' => 'Áp dụng voucher thành công',
    'voucher' => $new
]);
?>
