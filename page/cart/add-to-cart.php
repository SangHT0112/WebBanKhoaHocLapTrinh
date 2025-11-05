<?php
session_start();
include "../../db.php"; // file chứa kết nối $conn

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'not_logged_in']);
    exit;
}

$user_id = $_SESSION['id'];
$course_id = $_POST['id'] ?? null;
$quantity = 1;

if ($course_id) {
    // Kiểm tra xem sản phẩm đã có trong giỏ chưa
    $check = $conn->prepare("SELECT * FROM carts WHERE user_id = ? AND course_id = ?");
    $check->bind_param("ii", $user_id, $course_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'exists']);
    } else {
        // Thêm vào giỏ hàng
        $insert = $conn->prepare("INSERT INTO carts (user_id, course_id, quantity) VALUES (?, ?, ?)");
        $insert->bind_param("iii", $user_id, $course_id, $quantity);
        $insert->execute();
        echo json_encode(['status' => 'added']);
    }
} else {
    echo json_encode(['status' => 'error']);
}
