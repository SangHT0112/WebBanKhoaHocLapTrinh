<?php
/**
 * verify-qr-payment.php - Verify payment QR code va cap nhat trang thai refund
 * Nhan QR data, verify, va tru don hang ra khoi he thong
 */

session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

// Kiem tra admin
if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Khong co quyen truy cap']);
    exit;
}

// Chi cho phep POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$qr_data = $_POST['qr_data'] ?? '';

if (!$qr_data) {
    http_response_code(400);
    echo json_encode(['error' => 'Thieu du lieu QR']);
    exit;
}

try {
    // Parse QR data: REFUND|Order:123|User:1|Amount:1000000|Time:1234567890
    $parts = explode('|', $qr_data);
    
    if (count($parts) < 4 || $parts[0] !== 'REFUND') {
        throw new Exception('Ma QR khong hop le');
    }

    $order_id = null;
    $user_id = null;
    $amount = null;
    $time = null;

    foreach ($parts as $part) {
        if (strpos($part, 'Order:') === 0) {
            $order_id = intval(substr($part, 6));
        } elseif (strpos($part, 'User:') === 0) {
            $user_id = intval(substr($part, 5));
        } elseif (strpos($part, 'Amount:') === 0) {
            $amount = intval(substr($part, 7));
        } elseif (strpos($part, 'Time:') === 0) {
            $time = intval(substr($part, 5));
        }
    }

    if (!$order_id || !$user_id || !$amount) {
        throw new Exception('Du lieu QR khong day du');
    }

    $db = (new Database())->connect();

    // Kiem tra refund request ton tai
    $sql_check = "SELECT rr.id, rr.status FROM refund_requests WHERE order_id = ? AND status = 'pending'";
    $stmt_check = $db->prepare($sql_check);
    $stmt_check->bind_param("i", $order_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Khong tim thay yeu cau tra hang pending cho don hang nay');
    }

    $refund_request = $result->fetch_assoc();

    // Cap nhat refund status thanh approved
    $sql_update = "UPDATE refund_requests SET status = 'approved', admin_note = ? WHERE id = ?";
    $stmt_update = $db->prepare($sql_update);
    $admin_note = "Duyet va tra tien bang QR code vao " . date('d/m/Y H:i:s') . " (Admin ID: " . $_SESSION['id'] . ")";
    $stmt_update->bind_param("si", $admin_note, $refund_request['id']);
    $stmt_update->execute();

    // Xoa don hang va chi tiet
    $sql_delete_items = "DELETE FROM order_items WHERE order_id = ?";
    $stmt_delete_items = $db->prepare($sql_delete_items);
    $stmt_delete_items->bind_param("i", $order_id);
    $stmt_delete_items->execute();

    $sql_delete_order = "DELETE FROM orders WHERE id = ?";
    $stmt_delete_order = $db->prepare($sql_delete_order);
    $stmt_delete_order->bind_param("i", $order_id);
    $stmt_delete_order->execute();

    // Log transaction
    logTransaction($db, $order_id, $user_id, $amount, 'approved_by_qr', $_SESSION['id']);

    echo json_encode([
        'success' => true,
        'message' => 'Da cap nhat trang thai tra hang va xoa don hang thanh cong',
        'order_id' => $order_id,
        'amount' => $amount
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function logTransaction($db, $order_id, $user_id, $amount, $status, $admin_id) {
    $sql = "INSERT INTO refund_transactions (order_id, user_id, amount, status, admin_id, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("iiisi", $order_id, $user_id, $amount, $status, $admin_id);
    $stmt->execute();
}
?>
