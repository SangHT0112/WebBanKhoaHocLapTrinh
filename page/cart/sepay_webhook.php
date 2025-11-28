<?php
include "../../db.php";
header("Content-Type: application/json; charset=UTF-8");

// Require PHPMailer
require_once '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Nhận dữ liệu JSON từ SePay
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Kiểm tra dữ liệu
if (!$data || !isset($data["description"])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing data"]);
    exit;
}

// Ghi log
file_put_contents("sepay_log.txt", date("Y-m-d H:i:s") . " - " . $raw . "\n", FILE_APPEND);

// Lấy thông tin từ webhook
$description = trim($data["description"]);
$amount = isset($data["transferAmount"]) ? (float)$data["transferAmount"] : 0;

// Tìm user_id
if (preg_match("/user\s+(\d+)/i", $description, $matches)) {
    $user_id = (int)$matches[1];
} else {
    file_put_contents("sepay_log.txt", "Không tìm thấy user_id trong nội dung\n", FILE_APPEND);
    echo json_encode(["status" => "ignored", "reason" => "no_user_id"]);
    exit;
}

// Lấy giỏ hàng (thêm ten_khoa_hoc để liệt kê trong email)
$stmt = $conn->prepare("
    SELECT c.course_id, c.quantity, cs.gia AS price, cs.ten_khoa_hoc AS course_name
    FROM carts c
    JOIN courses cs ON cs.id = c.course_id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$calculated_total = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $calculated_total += $row['price'] * $row['quantity'];
    }

    // Kiểm tra số tiền
    $tolerance = 0.01; // 1%
    if (abs($amount - $calculated_total) > ($calculated_total * $tolerance)) {
        file_put_contents("sepay_log.txt", "Số tiền không khớp: Paid $amount, Expected $calculated_total\n", FILE_APPEND);
        echo json_encode([
            "status" => "amount_mismatch",
            "message" => "Số tiền chuyển khoản không khớp với tổng giỏ hàng",
            "paid" => $amount,
            "expected" => $calculated_total
        ]);
        exit;
    }

    // Lấy thông tin user
    $userQuery = $conn->prepare("
        SELECT username, email, phone, address 
        FROM users 
        WHERE id = ?
    ");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userInfo = $userQuery->get_result()->fetch_assoc();

    $fullname = $userInfo['username'];
    $email = $userInfo['email'];
    $phone = $userInfo['phone'];
    $address = $userInfo['address'];

    // === TẠO ĐƠN HÀNG (orders) ===
    $insertOrder = $conn->prepare("
        INSERT INTO orders (user_id, fullname, email, phone, address, tong_tien, trang_thai, ngay_tao)
        VALUES (?, ?, ?, ?, ?, ?, 'đã duyệt', NOW())
    ");
    $insertOrder->bind_param(
        "issssd",
        $user_id,
        $fullname,
        $email,
        $phone,
        $address,
        $calculated_total
    );
    $insertOrder->execute();
    $order_id = $conn->insert_id;

    // === THÊM TỪNG ITEM VÀO order_items ===
    $insertItem = $conn->prepare("
        INSERT INTO order_items (order_id, course_id, so_luong, don_gia)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($cart_items as $item) {
        $insertItem->bind_param(
            "iiid",
            $order_id,
            $item['course_id'],
            $item['quantity'],
            $item['price']
        );
        $insertItem->execute();
    }

    // Xóa giỏ hàng
    $deleteCart = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
    $deleteCart->bind_param("i", $user_id);
    $deleteCart->execute();

    // === GỬI EMAIL CẢM ƠN (đơn giản, không ZIP) ===
    if ($email) {
        // Liệt kê các khóa học trong email
        $courses_list = '';
        foreach ($cart_items as $item) {
            $courses_list .= "<li><strong>{$item['course_name']}</strong> (Số lượng: {$item['quantity']}, Giá: " . number_format($item['price'], 0, ',', '.') . " ₫)</li>";
        }
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'huynhtsang2004@gmail.com';
            $mail->Password   = 'xtrgjliokmzruehr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            $mail->setFrom('huynhtsang2004@gmail.com', 'KhoaHocOnline');
            $mail->addAddress($email, $fullname);
            
            $mail->isHTML(true);
            $mail->Subject = "🎉 Cảm ơn bạn đã mua khóa học! Đơn hàng #{$order_id}";
            $mail->Body    = "
                <h2>Xin chào {$fullname}!</h2>
                <p>Cảm ơn bạn đã mua khóa học qua SePay. Đơn hàng #{$order_id} của bạn đã được xác nhận thành công!</p>
                <p><strong>Tổng tiền:</strong> " . number_format($calculated_total, 0, ',', '.') . " ₫</p>
                <h3>Các khóa học bạn đã mua:</h3>
                <ul>
                    {$courses_list}
                </ul>
                <p>Bạn có thể truy cập khóa học ngay sau khi đăng nhập vào tài khoản.</p>
                <p>Nếu có vấn đề, liên hệ support@khoahoconline.com</p>
                <p>Trân trọng,<br>KhoaHocOnline</p>
            ";
            
            $mail->send();
            error_log("[SUCCESS] Webhook: Thank you email sent to {$email} for order #{$order_id}");
            
        } catch (Exception $e) {
            error_log("[ERROR] Webhook: Email error for order #{$order_id}: {$mail->ErrorInfo}");
        }
    } else {
        error_log("[WARNING] Webhook: No email for user_id={$user_id}");
    }

    // Gửi notify realtime (giữ nguyên - không mất chức năng thông báo)
    $payload = json_encode([
        "user_id" => $user_id,
        "message" => "Thanh toán thành công! Đơn hàng #" . $order_id . " đã được duyệt."
    ]);

    $ch = curl_init("http://localhost:3001/notify");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $curl_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        file_put_contents("sepay_log.txt", "Notify fail: HTTP $http_code, Response: $curl_response\n", FILE_APPEND);
    }

    echo json_encode([
        "status" => "success",
        "message" => "Đơn hàng đã được tạo và duyệt tự động",
        "order_id" => $order_id,
        "user_id" => $user_id,
        "total" => $calculated_total
    ]);

} else {
    echo json_encode([
        "status" => "no_cart_found",
        "message" => "Không tìm thấy giỏ hàng cho user này"
    ]);
}
?>