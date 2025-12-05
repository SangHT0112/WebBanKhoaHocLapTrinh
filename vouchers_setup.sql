-- Tạo bảng vouchers
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

-- Thêm cột voucher_id vào bảng orders (nếu chưa có)
ALTER TABLE `orders` ADD COLUMN `voucher_id` int(11) DEFAULT NULL;
ALTER TABLE `orders` ADD COLUMN `discount_amount` decimal(10, 2) DEFAULT 0;
ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`);

-- Thêm một số voucher mẫu
INSERT INTO `vouchers` (`code`, `description`, `discount_value`, `discount_type`, `min_order_value`, `start_date`, `end_date`, `usage_limit`, `status`) VALUES
('SAVE10', 'Giảm 10% cho tất cả khóa học', 10, 'percent', 0, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), NULL, 'active'),
('SAVE500K', 'Giảm 500,000 ₫ cho đơn hàng trên 2,000,000 ₫', 500000, 'fixed', 2000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), NULL, 'active'),
('WELCOME50K', 'Giảm 50,000 ₫ cho khách hàng mới', 50000, 'fixed', 0, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 100, 'active');
