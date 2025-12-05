<?php
session_start();
include "../../db.php";

// Cài PHPMailer: composer require phpmailer/phpmailer hoặc tải thủ công
// Di chuyển require và use statements lên đầu file để tránh syntax error
require_once '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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

// Tính toán discount từ các voucher lưu trong session (nếu có)
$discount_amount = 0;
$applied_vouchers = isset($_SESSION['applied_vouchers']) && is_array($_SESSION['applied_vouchers']) ? $_SESSION['applied_vouchers'] : [];
$final_total = $total;
if (!empty($applied_vouchers)) {
    $fixed_sum = 0;
    $percent_list = [];
    foreach ($applied_vouchers as $av) {
        if ($av['discount_type'] === 'fixed') {
            $fixed_sum += floatval($av['discount_value']);
        } else {
            $percent_list[] = floatval($av['discount_value']);
        }
    }
    $remaining = max(0, $total - $fixed_sum);
    $mult = 1.0;
    foreach ($percent_list as $p) {
        $mult *= (1 - ($p / 100.0));
    }
    $after_percent = $remaining * $mult;
    $final_total = max(0, $after_percent);
    $discount_amount = max(0, min($total - $final_total, $total));
}

// Nếu final_total == 0 => free registration: proceed without payment
// order_total là số tiền thực tế lưu vào đơn hàng
$order_total = $final_total;

// --- Bắt đầu lưu đơn hàng ---
$conn->begin_transaction();

try {
    // Thêm vào bảng orders (status 'success' cho VietQR - giả sử manual verify ở đây, bạn có thể thêm admin check sau)
    $insertOrder = $conn->prepare(
        "INSERT INTO orders (user_id, tong_tien, trang_thai, ngay_tao)
        VALUES (?, ?, 'thành công', NOW())"
    );
    $insertOrder->bind_param("id", $user_id, $order_total);
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

    // Thêm quyền truy cập khóa học vào user_courses (nếu chưa có bảng, tạo trước)
    // Lưu thông tin voucher đã áp dụng (nếu có) vào orders.applied_vouchers
    $applied_vouchers = isset($_SESSION['applied_vouchers']) && is_array($_SESSION['applied_vouchers']) ? $_SESSION['applied_vouchers'] : [];

    if (!empty($applied_vouchers)) {
        // Kiểm tra xem cột applied_vouchers có tồn tại không, nếu không thì thêm cột TEXT
        $checkCol = $conn->prepare("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'applied_vouchers'");
        $checkCol->execute();
        $colRes = $checkCol->get_result()->fetch_assoc();
        if (intval($colRes['cnt']) === 0) {
            $conn->query("ALTER TABLE orders ADD COLUMN applied_vouchers TEXT NULL");
        }
        // Lưu JSON danh sách mã/voucher id tạm thời (mã đủ dễ đọc)
        $codes = array_map(function($v){ return $v['code']; }, $applied_vouchers);
        $codes_json = json_encode($codes, JSON_UNESCAPED_UNICODE);
        // Cập nhật orders sau khi insert để lưu applied_vouchers (thực hiện sau khi có $order_id)
    }

    // Thêm bản ghi vào bảng enrollments để cấp quyền học
    // Bảng enrollments đã có trong DB dump: (ma_khoa_hoc, ma_nguoi_dung, ngay_dang_ky, trang_thai, tien_do, ngay_hoan_thanh)
    $enrollStmt = $conn->prepare(
        "INSERT INTO enrollments (ma_khoa_hoc, ma_nguoi_dung, ngay_dang_ky, trang_thai, tien_do) VALUES (?, ?, NOW(), 'dang_hoc', 0.00)"
    );
    foreach ($items as $item) {
        $enrollStmt->bind_param("ii", $item['course_id'], $user_id);
        $enrollStmt->execute();
    }

    // Nếu có applied_vouchers, cập nhật vào orders.applied_vouchers
    if (!empty($applied_vouchers)) {
        if (!isset($codes_json)) {
            $codes = array_map(function($v){ return $v['code']; }, $applied_vouchers);
            $codes_json = json_encode($codes, JSON_UNESCAPED_UNICODE);
        }
        $updateOrder = $conn->prepare("UPDATE orders SET applied_vouchers = ? WHERE id = ?");
        if ($updateOrder) {
            $updateOrder->bind_param("si", $codes_json, $order_id);
            $updateOrder->execute();
            $updateOrder->close();
        }
    }

    // Xóa giỏ hàng sau khi đặt
    $deleteCart = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
    $deleteCart->bind_param("i", $user_id);
    $deleteCart->execute();

    // Clear applied vouchers in session after successful order
    if (isset($_SESSION['applied_vouchers'])) {
        unset($_SESSION['applied_vouchers']);
    }

    $conn->commit();

    // Lấy email user
    $userEmailSql = "SELECT email FROM users WHERE id = ?";
    $userStmt = $conn->prepare($userEmailSql);
    if (!$userStmt) {
        error_log("[ERROR] Failed to prepare email query: " . $conn->error);
        $user_email = null;
    } else {
        $userStmt->bind_param("i", $user_id);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $user = $userResult->fetch_assoc();
        $user_email = $user['email'] ?? null; // Không dùng default, để biết khi nào email thiếu
        if (!$user_email) {
            error_log("[WARNING] No email found for user_id={$user_id}");
        }
    }

    // Gửi email cho từng khóa học (tạo ZIP riêng)
    foreach ($items as $item) {
        $course_id = $item['course_id'];
        $course_name = $item['ten_khoa_hoc'];
        
        // Đường dẫn thư mục files khóa học - dùng __DIR__ để đảm bảo absolute path
        $project_root = dirname(dirname(dirname(__DIR__))); // D:\A.Project\DoAnWebTMDT
        $zip_dir = $project_root . "/uploads/courses/{$course_id}/files/";
        $zip_file = $project_root . "/uploads/temp/{$user_id}_{$course_id}_files.zip";
        
        if (!file_exists(dirname($zip_file))) {
            mkdir(dirname($zip_file), 0777, true);
        }
        
        // Tạo ZIP bằng ZipArchive (built-in PHP)
        error_log("[DEBUG] Creating ZIP - dir: {$zip_dir}, file: {$zip_file}");
        $zip = new ZipArchive();
        $zipCreated = $zip->open($zip_file, ZipArchive::CREATE);
        if ($zipCreated === TRUE) {
            if (is_dir($zip_dir)) {
                // Quét và thêm tất cả file từ thư mục
                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($zip_dir), RecursiveIteratorIterator::LEAVES_ONLY);
                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($zip_dir));
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                error_log("[SUCCESS] ZIP created for course {$course_id}");
            } else {
                error_log("[ERROR] Source directory not found: {$zip_dir}");
            }
            $zip->close();
            
            // Kiểm tra xem email có xác định không
            if (!$user_email) {
                error_log("[ERROR] Cannot send email - user_email is empty for user_id={$user_id}");
                continue;
            }
            
            // Gửi email với attachment
            $mail = new PHPMailer(true);
            try {
                // Cấu hình SMTP (ví dụ Gmail - dùng app password)
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'huynhtsang2004@gmail.com'; // Thay bằng email gửi của bạn
                $mail->Password   = 'xtrgjliokmzruehr';    // App password Gmail
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                
                $mail->setFrom('huynhtsang2004@gmail.com', 'KhoaHocOnline');
                $mail->addAddress($user_email);
                
                // Kiểm tra ZIP file trước khi attach
                if (file_exists($zip_file)) {
                    $mail->addAttachment($zip_file);
                } else {
                    error_log("[WARNING] ZIP file not found: {$zip_file}");
                }
                
                $mail->isHTML(true);
                $mail->Subject = "🎉 Thanh toán thành công - Tải khóa học: {$course_name}";
                $mail->Body    = "
                    <h2>Xin chào!</h2>
                    <p>Cảm ơn bạn đã mua khóa học <strong>{$course_name}</strong> qua VietQR.</p>
                    <p>Đơn hàng #{$order_id} đã được xác nhận. File ZIP chứa toàn bộ tài liệu (video, PDF, bài giảng) được đính kèm.</p>
                    <p>Bạn có thể truy cập khóa học trực tiếp trên website sau khi đăng nhập.</p>
                    <p>Nếu có vấn đề, liên hệ support@khoahoconline.com</p>
                    <p>Trân trọng,<br>KhoaHocOnline</p>
                ";
                
                $mail->send();
                error_log("[SUCCESS] Email sent to {$user_email} for course {$course_id}");
                
                // Xóa ZIP tạm sau gửi (tùy chọn)
                if (file_exists($zip_file)) {
                    unlink($zip_file);
                }
                
            } catch (Exception $e) {
                // Log lỗi
                error_log("[ERROR] Mail error for course {$course_id}: {$mail->ErrorInfo} | " . $e->getMessage());
            }
        } else {
            error_log("[ERROR] Failed to create ZIP file: {$zip_file}. ZipArchive code: {$zipCreated}");
        }
    }

    // --- NOTIFY REALTIME QUA NODE.JS SERVER ---
    $notify_data = [
        'user_id' => $user_id,
        'message' => "Thanh toán VietQR thành công! Đơn hàng #{$order_id} đã được xử lý. Kiểm tra email để tải file ZIP khóa học."
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:3001/notify');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notify_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Lỗi khi lưu đơn hàng: " . $e->getMessage());
    die("Lỗi khi xử lý thanh toán VietQR: " . $e->getMessage());
}

// Redirect về success page
header("Location: /page/cart/cart.php");
exit;
?>