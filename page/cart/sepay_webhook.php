<?php
include "../../db.php";
header("Content-Type: application/json; charset=UTF-8");

// Nhận dữ liệu JSON từ SePay
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Kiểm tra dữ liệu
if (!$data || !isset($data["description"])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing data"]);
    exit;
}

// Ghi log để kiểm tra
file_put_contents("sepay_log.txt", date("Y-m-d H:i:s") . " - " . $raw . "\n", FILE_APPEND);

// Lấy thông tin từ webhook
$description = trim($data["description"]);
$amount = isset($data["transferAmount"]) ? (float)$data["transferAmount"] : 0;

// Tìm user_id trong nội dung
if (preg_match("/user\s+(\d+)/i", $description, $matches)) {
    $user_id = (int)$matches[1];
} else {
    file_put_contents("sepay_log.txt", "Không tìm thấy user_id trong nội dung\n", FILE_APPEND);
    echo json_encode(["status" => "ignored", "reason" => "no_user_id"]);
    exit;
}

// Kiểm tra xem có giỏ hàng (cart) cho user không
$stmt = $conn->prepare("
    SELECT c.id, c.course_id, c.quantity, cs.gia AS price
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

    // Kiểm tra số tiền chuyển khoản có khớp với total cart không (tolerate 1% error cho phí ngân hàng)
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

    // Tạo đơn hàng mới từ cart (trạng thái 'đã duyệt' ngay lập tức)
    $insert = $conn->prepare("
        INSERT INTO orders (user_id, tong_tien, trang_thai, ngay_tao)
        VALUES (?, ?, 'đã duyệt', NOW())
    ");
    $insert->bind_param("id", $user_id, $calculated_total);
    $insert->execute();
    $order_id = $conn->insert_id; // Lấy ID đơn hàng mới

    // Optional: Lưu chi tiết items vào order_details nếu có bảng này
    // Giả sử có bảng order_details (order_id, course_id, quantity, price)
    // foreach ($cart_items as $item) {
    //     $detail_insert = $conn->prepare("INSERT INTO order_details (order_id, course_id, quantity, price) VALUES (?, ?, ?, ?)");
    //     $detail_insert->bind_param("iiid", $order_id, $item['course_id'], $item['quantity'], $item['price']);
    //     $detail_insert->execute();
    // }

    // Xóa giỏ hàng sau khi tạo order
    $delete = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
    $delete->bind_param("i", $user_id);
    $delete->execute();

    // 🔥🔥🔥 Gửi thông báo realtime đến Node server
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

    // Log CURL nếu fail
    if ($http_code !== 200) {
        file_put_contents("sepay_log.txt", "Notify fail: HTTP $http_code, Response: $curl_response\n", FILE_APPEND);
    }
    // 🔥🔥🔥 END realtime

    echo json_encode([
        "status" => "success",
        "message" => "Đơn hàng đã được tạo và duyệt tự động từ giỏ hàng",
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