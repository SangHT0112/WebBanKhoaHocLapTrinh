<?php
session_start();
// Nếu truyền code => xóa voucher cụ thể, nếu không => xóa tất cả
$codeToRemove = isset($_POST['code']) ? trim($_POST['code']) : null;

if ($codeToRemove) {
    if (isset($_SESSION['applied_vouchers']) && is_array($_SESSION['applied_vouchers'])) {
        foreach ($_SESSION['applied_vouchers'] as $k => $v) {
            if ($v['code'] === $codeToRemove || $v['id'] == $codeToRemove) {
                unset($_SESSION['applied_vouchers'][$k]);
            }
        }
        // Reindex
        $_SESSION['applied_vouchers'] = array_values($_SESSION['applied_vouchers']);
    }
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Voucher đã bị xóa']);
    exit;
}

// Nếu không có code, xóa tất cả
if (isset($_SESSION['applied_vouchers'])) {
    unset($_SESSION['applied_vouchers']);
}

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Tất cả voucher đã bị xóa']);
?>
