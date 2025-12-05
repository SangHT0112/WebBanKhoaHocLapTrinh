<?php
session_start();
include __DIR__ . "/db.php";

// Kiểm tra user có phải admin không (tùy chọn)
// Tạo bảng vouchers nếu chưa tồn tại
$create_table = "
CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `description` varchar(255),
  `discount_value` decimal(10, 2) NOT NULL,
  `discount_type` enum('fixed', 'percent') NOT NULL COMMENT 'fixed = số tiền cố định, percent = phần trăm',
  `min_order_value` decimal(10, 2),
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `usage_limit` int(11),
  `status` enum('active', 'inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if ($conn->query($create_table)) {
    echo "✅ Bảng vouchers đã được tạo hoặc đã tồn tại.\n";
} else {
    echo "❌ Lỗi tạo bảng: " . $conn->error . "\n";
}

// Kiểm tra và thêm cột vào bảng orders
$check_column = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'orders' AND COLUMN_NAME = 'voucher_id'";
$result = $conn->query($check_column);

if ($result->num_rows === 0) {
    $alter_orders = "
    ALTER TABLE `orders` 
    ADD COLUMN `voucher_id` int(11) DEFAULT NULL,
    ADD COLUMN `discount_amount` decimal(10, 2) DEFAULT 0,
    ADD CONSTRAINT `fk_orders_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`)
    ";
    
    if ($conn->query($alter_orders)) {
        echo "✅ Các cột voucher_id và discount_amount đã được thêm vào bảng orders.\n";
    } else {
        echo "⚠️ Cột có thể đã tồn tại hoặc có lỗi: " . $conn->error . "\n";
    }
} else {
    echo "ℹ️ Các cột voucher_id đã tồn tại.\n";
}

// Thêm các voucher mẫu
$today = date('Y-m-d');
$end_date = date('Y-m-d', strtotime('+30 days'));
$end_date_60 = date('Y-m-d', strtotime('+60 days'));

$vouchers = [
    ['SAVE10', 'Giảm 10% cho tất cả khóa học', 10, 'percent', 0, $today, $end_date, NULL],
    ['SAVE500K', 'Giảm 500,000 ₫ cho đơn hàng trên 2,000,000 ₫', 500000, 'fixed', 2000000, $today, $end_date, NULL],
    ['WELCOME50K', 'Giảm 50,000 ₫ cho khách hàng mới', 50000, 'fixed', 0, $today, $end_date_60, 100]
];

foreach ($vouchers as $voucher) {
    $check = "SELECT id FROM vouchers WHERE code = ?";
    $stmt_check = $conn->prepare($check);
    $stmt_check->bind_param("s", $voucher[0]);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows === 0) {
        $insert = "INSERT INTO vouchers (code, description, discount_value, discount_type, min_order_value, start_date, end_date, usage_limit, status) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        $stmt_insert = $conn->prepare($insert);
        $stmt_insert->bind_param("ssdidsdii", $voucher[0], $voucher[1], $voucher[2], $voucher[3], $voucher[4], $voucher[5], $voucher[6], $voucher[7]);
        
        if ($stmt_insert->execute()) {
            echo "✅ Voucher '{$voucher[0]}' đã được thêm.\n";
        } else {
            echo "❌ Lỗi thêm voucher '{$voucher[0]}': " . $conn->error . "\n";
        }
    } else {
        echo "ℹ️ Voucher '{$voucher[0]}' đã tồn tại.\n";
    }
}

echo "\n✅ Cài đặt hoàn thành! Bạn có thể xóa file này sau khi chạy.\n";
?>
