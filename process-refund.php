<?php
/**
 * process-refund.php - Xu ly yeu cau tra hang
 */

session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Kiem tra dang nhap
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Vui long dang nhap']);
    exit;
}

// Chi cho phep POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Nhan du lieu
$order_id = intval($_POST['order_id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

if (!$order_id || !$reason) {
    http_response_code(400);
    echo json_encode(['error' => 'Thieu du lieu']);
    exit;
}

try {
    $db = (new Database())->connect();
    $controller = new OrderController($db);
    
    // Kiem tra order ton tai va thuoc ve user
    $ordersResult = $controller->model->getByUserId($_SESSION['id']);
    $orders = $ordersResult->fetch_all(MYSQLI_ASSOC);
    $orderExists = false;
    $orderData = null;
    
    foreach ($orders as $o) {
        if ($o['id'] == $order_id) {
            $orderExists = true;
            $orderData = $o;
            break;
        }
    }
    
    if (!$orderExists) {
        http_response_code(403);
        echo json_encode(['error' => 'Don hang khong ton tai']);
        exit;
    }

    // Xu ly tra hang
    $result = $controller->model->processRefund($order_id, $reason);
    
    if (!$result) {
        throw new Exception('Loi xu ly tra hang');
    }

    // Lay thong tin refund
    $refundInfo = $controller->model->getRefundInfo($order_id);
    
    // Tao QR code
    $qrData = generateRefundQR($order_id, $_SESSION['id'], $refundInfo['tong_tien']);
    $qrCode = $qrData['qr_image'];
    $qrAmount = number_format($refundInfo['tong_tien'], 0, ',', '.');
    
    // Gui email cho admin
    sendRefundEmailToAdmin($order_id, $refundInfo, $qrCode, $qrAmount);
    
    echo json_encode([
        'success' => true,
        'message' => 'Yeu cau tra hang da duoc gui. Admin se xu ly trong 24 gio.',
        'qr_code' => $qrCode
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function generateRefundQR($order_id, $user_id, $amount) {
    $text = "REFUND|Order:" . $order_id . "|User:" . $user_id . "|Amount:" . intval($amount) . "|Time:" . time();
    $encoded = urlencode($text);
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . $encoded;
    
    $qrImage = file_get_contents($qr_url);
    
    $filename = "qr_refund_" . $order_id . "_" . time() . ".png";
    $filepath = __DIR__ . "/uploads/qr/" . $filename;
    
    if (!is_dir(dirname($filepath))) {
        mkdir(dirname($filepath), 0755, true);
    }
    
    file_put_contents($filepath, $qrImage);
    
    return [
        'qr_image' => $filepath,
        'qr_filename' => $filename,
        'qr_url' => "/uploads/qr/" . $filename
    ];
}

function sendRefundEmailToAdmin($order_id, $refundInfo, $qrImagePath, $qrAmount) {
    require_once __DIR__ . '/config/api-key.php';
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = GMAIL_USERNAME;
        $mail->Password = GMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(GMAIL_USERNAME, 'Code Cung Sang - He thong tra hang');
        $mail->addAddress('huynhtsang2004@gmail.com', 'Admin');

        $mail->isHTML(true);
        $mail->Subject = "Yeu cau tra hang - Don #" . $order_id;

        $htmlContent = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #667eea; color: white; padding: 20px; border-radius: 8px; text-align: center; }
                .content { background: #f9fafb; padding: 20px; margin: 20px 0; border-radius: 8px; }
                .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #667eea; }
                .qr-section { text-align: center; padding: 20px; background: white; border-radius: 8px; }
                .qr-section img { max-width: 300px; }
                .amount { font-size: 24px; font-weight: bold; color: #dc2626; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>YEU CAU TRA HANG MOI</h1>
                </div>

                <div class='content'>
                    <h2>Thong tin don hang:</h2>
                    <div class='info-box'>
                        <p><strong>Ma don:</strong> #" . $order_id . "</p>
                        <p><strong>Khach hang:</strong> " . htmlspecialchars($refundInfo['username']) . " (" . htmlspecialchars($refundInfo['email']) . ")</p>
                        <p><strong>Dien thoai:</strong> " . htmlspecialchars($refundInfo['phone']) . "</p>
                        <p><strong>Khoa hoc:</strong> " . htmlspecialchars($refundInfo['courses']) . "</p>
                    </div>

                    <h2>So tien tra:</h2>
                    <div class='info-box' style='text-align: center;'>
                        <div class='amount'>" . $qrAmount . " VND</div>
                    </div>

                    <h2>Ly do tra hang:</h2>
                    <div class='info-box'>
                        <p>" . htmlspecialchars($refundInfo['reason']) . "</p>
                    </div>

                    <h2>Quet ma QR de tra tien:</h2>
                    <div class='qr-section'>
                        <p>Quet ma QR ben duoi de chuyen tien tra hang cho khach</p>
                        <img src='cid:qr_refund' alt='QR Code Refund' style='max-width: 300px;'>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->Body = $htmlContent;

        if (file_exists($qrImagePath)) {
            $mail->addAttachment($qrImagePath, 'qr_refund.png');
            $mail->addEmbeddedImage($qrImagePath, 'qr_refund', 'qr_refund.png');
        }

        $mail->send();
        return true;

    } catch (Exception $e) {
        throw new Exception("Loi gui email");
    }
}
?>
