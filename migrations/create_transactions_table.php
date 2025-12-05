<?php
/**
 * Migration: Create refund_transactions table
 */

require_once __DIR__ . '/../db.php';

$db = (new Database())->connect();

$sql = "CREATE TABLE IF NOT EXISTS `refund_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($db->query($sql)) {
    echo "Tao bang refund_transactions thanh cong";
} else {
    echo "Loi: " . $db->error;
}

$db->close();
?>
