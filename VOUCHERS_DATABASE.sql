-- ============================================
-- VOUCHER SYSTEM - DATABASE CHANGES
-- ============================================

-- 1. TẠO BẢNG VOUCHERS
-- ============================================
CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
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

-- 2. CẬP NHẬT BẢNG ORDERS
-- ============================================
-- Thêm cột voucher_id (tham chiếu đến bảng vouchers)
ALTER TABLE `orders` ADD COLUMN `voucher_id` int(11) DEFAULT NULL;

-- Thêm cột discount_amount (lưu tiền giảm)
ALTER TABLE `orders` ADD COLUMN `discount_amount` decimal(10, 2) DEFAULT 0;

-- Thêm foreign key
ALTER TABLE `orders` 
ADD CONSTRAINT `fk_orders_voucher` 
FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`);

-- 3. THÊM VOUCHER MẪU
-- ============================================

-- Voucher 1: SAVE10 - Giảm 10% cho tất cả
INSERT INTO `vouchers` 
(`code`, `description`, `discount_value`, `discount_type`, `min_order_value`, `start_date`, `end_date`, `usage_limit`, `status`) 
VALUES 
('SAVE10', 'Giảm 10% cho tất cả khóa học', 10, 'percent', 0, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), NULL, 'active');

-- Voucher 2: SAVE500K - Giảm 500K cho đơn tối thiểu 2M
INSERT INTO `vouchers` 
(`code`, `description`, `discount_value`, `discount_type`, `min_order_value`, `start_date`, `end_date`, `usage_limit`, `status`) 
VALUES 
('SAVE500K', 'Giảm 500,000 ₫ cho đơn hàng từ 2,000,000 ₫', 500000, 'fixed', 2000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), NULL, 'active');

-- Voucher 3: WELCOME50K - Giảm 50K cho khách hàng mới (giới hạn 100)
INSERT INTO `vouchers` 
(`code`, `description`, `discount_value`, `discount_type`, `min_order_value`, `start_date`, `end_date`, `usage_limit`, `status`) 
VALUES 
('WELCOME50K', 'Giảm 50,000 ₫ cho khách hàng mới', 50000, 'fixed', 0, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 100, 'active');

-- ============================================
-- KIỂM CHỨNG - VERIFY SETUP
-- ============================================

-- Kiểm tra bảng vouchers được tạo
SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vouchers';

-- Kiểm tra 3 voucher được tạo
SELECT code, description, discount_value, discount_type, status FROM vouchers ORDER BY created_at;

-- Kiểm tra cột mới trong orders
SELECT COLUMN_NAME, COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_NAME = 'orders' 
AND COLUMN_NAME IN ('voucher_id', 'discount_amount');

-- ============================================
-- QUẢN LÝ VOUCHER - MANAGEMENT QUERIES
-- ============================================

-- Lấy tất cả voucher đang hoạt động
SELECT * FROM vouchers WHERE status = 'active' AND CURDATE() BETWEEN start_date AND end_date ORDER BY created_at DESC;

-- Lấy số lần sử dụng của một voucher
SELECT COUNT(*) as used FROM orders WHERE voucher_id = (SELECT id FROM vouchers WHERE code = 'SAVE10');

-- Tắt một voucher
UPDATE vouchers SET status = 'inactive' WHERE code = 'SAVE10';

-- Bật lại voucher
UPDATE vouchers SET status = 'active' WHERE code = 'SAVE10';

-- Cập nhật ngày kết thúc voucher
UPDATE vouchers SET end_date = DATE_ADD(CURDATE(), INTERVAL 7 DAY) WHERE code = 'SAVE10';

-- Xóa một voucher
DELETE FROM vouchers WHERE code = 'SAVE10';

-- ============================================
-- THỐNG KÊ - STATISTICS
-- ============================================

-- Voucher được sử dụng nhiều nhất
SELECT v.code, v.description, COUNT(o.id) as usage_count, SUM(o.discount_amount) as total_discount
FROM vouchers v
LEFT JOIN orders o ON v.id = o.voucher_id
GROUP BY v.id
ORDER BY usage_count DESC;

-- Tổng tiền giảm theo voucher
SELECT v.code, SUM(o.discount_amount) as total_discount_amount
FROM vouchers v
JOIN orders o ON v.id = o.voucher_id
GROUP BY v.id
ORDER BY total_discount_amount DESC;

-- Đơn hàng sử dụng voucher
SELECT o.id, o.user_id, o.total_amount, v.code, o.discount_amount, (o.total_amount + o.discount_amount) as original_amount
FROM orders o
JOIN vouchers v ON o.voucher_id = v.id
ORDER BY o.created_at DESC;

-- ============================================
-- EXAMPLE - CÁC VÍ DỤ
-- ============================================

-- Ví dụ 1: Thêm voucher Black Friday - Giảm 30% (Max 5M)
INSERT INTO `vouchers` 
(`code`, `description`, `discount_value`, `discount_type`, `min_order_value`, `start_date`, `end_date`, `usage_limit`, `status`) 
VALUES 
('BLACKFRIDAY', 'Giảm 30% dịp Black Friday', 30, 'percent', 1000000, '2025-12-24', '2025-12-31', 1000, 'active');

-- Ví dụ 2: Voucher Limited Edition - Số lượng hạn chế
INSERT INTO `vouchers` 
(`code`, `description`, `discount_value`, `discount_type`, `min_order_value`, `start_date`, `end_date`, `usage_limit`, `status`) 
VALUES 
('LIMITED100', 'Giảm 100% - Miễn phí (Chỉ 50 lần)', 100, 'percent', 1000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 50, 'active');

-- ============================================
-- BACKUP & RESTORE
-- ============================================

-- Nếu cần backup dữ liệu vouchers
-- mysqldump -u user -p database vouchers > vouchers_backup.sql

-- Nếu cần restore
-- mysql -u user -p database < vouchers_backup.sql

-- ============================================
-- TROUBLESHOOTING - KHẮC PHỤC SỰ CỐ
-- ============================================

-- Nếu vouchers không hiển thị - kiểm tra status
SELECT * FROM vouchers WHERE status = 'inactive';

-- Nếu voucher hết hạn - cập nhật ngày
UPDATE vouchers SET end_date = DATE_ADD(CURDATE(), INTERVAL 30 DAY) WHERE code = 'SAVE10';

-- Nếu foreign key bị lỗi - kiểm tra
SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'orders' AND COLUMN_NAME = 'voucher_id';

-- Xóa tất cả vouchers (BE CAREFUL!)
-- DELETE FROM vouchers;

-- ============================================
-- END OF SCRIPT
-- ============================================
