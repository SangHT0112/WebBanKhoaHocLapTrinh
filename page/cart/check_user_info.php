<?php
session_start();
include "../../db.php";

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'not_logged_in']);
    exit;
}

$user_id = $_SESSION['id'];

// Kiểm tra thông tin user có đầy đủ không (fullname, phone, address)
$stmt = $conn->prepare("SELECT fullname, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || empty(trim($user['fullname'])) || empty(trim($user['phone'])) || empty(trim($user['address']))) {
    echo json_encode(['status' => 'incomplete']);
} else {
    echo json_encode(['status' => 'complete']);
}
?>