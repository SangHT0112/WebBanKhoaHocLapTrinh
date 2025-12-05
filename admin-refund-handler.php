<?php
/**
 * admin-refund-handler.php - Xu ly refund tu admin
 * Nhan QR code scan, cap nhat refund status, va xoa don hang
 */

session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/models/Order.php';

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

$action = $_POST['action'] ?? '';

try {
    $db = (new Database())->connect();
    $orderModel = new Order($db);

    if ($action === 'get-refunds') {
        // Lay danh sach yeu cau tra hang
        $sql = "SELECT rr.id, rr.order_id, rr.reason, rr.status, rr.created_at,
                       o.tong_tien, o.fullname, o.email, u.username, u.id as user_id
                FROM refund_requests rr
                JOIN orders o ON o.id = rr.order_id
                JOIN users u ON u.id = o.user_id
                ORDER BY rr.created_at DESC";
        
        $result = $db->query($sql);
        $refunds = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode([
            'success' => true,
            'refunds' => $refunds
        ]);

    } elseif ($action === 'approve-refund') {
        // Duyet va xoa don hang
        $refund_id = intval($_POST['refund_id'] ?? 0);
        $order_id = intval($_POST['order_id'] ?? 0);
        
        if (!$refund_id || !$order_id) {
            throw new Exception('Thieu du lieu');
        }

        // Cap nhat trang thai refund -> approved
        $sql1 = "UPDATE refund_requests SET status = 'approved', admin_note = ? WHERE id = ?";
        $stmt1 = $db->prepare($sql1);
        $admin_note = "Duyet boi admin vao " . date('d/m/Y H:i:s');
        $stmt1->bind_param("si", $admin_note, $refund_id);
        $stmt1->execute();

        // Xoa don hang
        $sql2 = "DELETE FROM order_items WHERE order_id = ?";
        $stmt2 = $db->prepare($sql2);
        $stmt2->bind_param("i", $order_id);
        $stmt2->execute();

        $sql3 = "DELETE FROM orders WHERE id = ?";
        $stmt3 = $db->prepare($sql3);
        $stmt3->bind_param("i", $order_id);
        $stmt3->execute();

        // Gui email xac nhan cho user
        $sql4 = "SELECT u.email, u.username, o.tong_tien FROM orders o 
                 JOIN users u ON u.id = o.user_id WHERE o.id = ?";
        $stmt4 = $db->prepare($sql4);
        $stmt4->bind_param("i", $order_id);
        $stmt4->execute();
        $userResult = $stmt4->get_result()->fetch_assoc();

        if ($userResult) {
            sendRefundApprovedEmail($userResult['email'], $userResult['username'], $userResult['tong_tien']);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Da duyet va xoa don hang thanh cong'
        ]);

    } elseif ($action === 'reject-refund') {
        // Tu choi refund
        $refund_id = intval($_POST['refund_id'] ?? 0);
        $reject_reason = trim($_POST['reject_reason'] ?? '');

        if (!$refund_id) {
            throw new Exception('Thieu refund_id');
        }

        $sql = "UPDATE refund_requests SET status = 'rejected', admin_note = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("si", $reject_reason, $refund_id);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Da tu choi refund'
        ]);
    } else {
        throw new Exception('Action khong hop le: ' . $action);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function sendRefundApprovedEmail($email, $username, $amount) {
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/config/api-key.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = GMAIL_USERNAME;
        $mail->Password = GMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(GMAIL_USERNAME, 'Code Cung Sang');
        $mail->addAddress($email, $username);

        $mail->isHTML(true);
        $mail->Subject = 'Hoan tien thanh cong - Code Cung Sang';

        $amountFormatted = number_format($amount, 0, ',', '.');
        $htmlContent = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
                .content { background: #f9fafb; padding: 20px; margin: 20px 0; border-radius: 8px; }
                .success-box { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; border-radius: 8px; }
                .amount { font-size: 28px; font-weight: bold; color: #10b981; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Hoan tien thanh cong!</h1>
                </div>

                <div class='content'>
                    <p>Xin chao {$username},</p>
                    <p>Yeu cau tra hang cua ban da duoc phe duyet va xr ly.</p>
                    
                    <div class='success-box'>
                        <p style='margin: 0 0 10px 0;'><strong>So tien hoan lai:</strong></p>
                        <div class='amount'>{$amountFormatted} VND</div>
                        <p style='margin: 10px 0 0 0; font-size: 12px; color: #666;'>So tien nay se duoc chuyen vao tai khoan cua ban trong 1-3 ngay lam viec.</p>
                    </div>

                    <p style='margin-top: 20px;'>Cam on ban da su dung dich vu cua chung toi!<br>Neu co bat ky thac mac, vui long lien he: support@codecungsang.com</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->Body = $htmlContent;
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return false;
    }
}
?>
