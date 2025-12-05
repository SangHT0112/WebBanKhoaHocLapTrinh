# Hướng Dẫn Hệ Thống Voucher Giảm Giá

## 📋 Tính Năng

- ✅ Áp dụng mã voucher giảm giá khi checkout
- ✅ Hỗ trợ 2 loại giảm giá: **cố định** (VD: 500K) và **phần trăm** (VD: 10%)
- ✅ Kiểm tra ngày hết hạn, giới hạn lượt sử dụng, giá trị đơn hàng tối thiểu
- ✅ Hiển thị tổng tiền gốc, tiền giảm, và tiền thanh toán cuối cùng
- ✅ Nhân viên có thể quản lý voucher qua database

---

## 🚀 Cài Đặt

### Bước 1: Chạy Script Setup

Truy cập URL này trong trình duyệt:
```
http://localhost/WebBanKhoaHocLapTrinh/setup-vouchers.php
```

Script này sẽ:
- ✅ Tạo bảng `vouchers` nếu chưa tồn tại
- ✅ Thêm cột `voucher_id` và `discount_amount` vào bảng `orders`
- ✅ Tạo 3 voucher mẫu (SAVE10, SAVE500K, WELCOME50K)

**Nếu chạy SQL trực tiếp**, sử dụng file `vouchers_setup.sql`

### Bước 2: Xóa File Setup (Tùy Chọn)

Sau khi chạy xong, bạn có thể xóa file `setup-vouchers.php` để bảo mật.

---

## 💰 Các Voucher Mẫu

| Mã Voucher | Mô Tả | Giảm Giá | Điều Kiện |
|-----------|-------|---------|----------|
| **SAVE10** | Giảm 10% cho tất cả khóa học | 10% | Không yêu cầu |
| **SAVE500K** | Giảm 500K cho đơn lớn | 500,000₫ | Tối thiểu 2,000,000₫ |
| **WELCOME50K** | Giảm 50K cho khách hàng mới | 50,000₫ | Giới hạn 100 lần |

---

## 📝 Quản Lý Voucher (Database)

### Thêm Voucher Mới

```sql
INSERT INTO `vouchers` 
(`code`, `description`, `discount_value`, `discount_type`, `min_order_value`, `start_date`, `end_date`, `usage_limit`, `status`) 
VALUES 
('NEWYEAR25', 'Giảm 25% dịp năm mới', 25, 'percent', 1000000, '2025-01-01', '2025-01-31', 50, 'active');
```

### Các Cột Trong Bảng `vouchers`

| Cột | Kiểu Dữ Liệu | Mô Tả |
|-----|--------------|-------|
| `id` | INT | ID tự động |
| `code` | VARCHAR(50) | Mã voucher (duy nhất) |
| `description` | VARCHAR(255) | Mô tả ngắn |
| `discount_value` | DECIMAL(10,2) | Số tiền hoặc % giảm |
| `discount_type` | ENUM | 'fixed' (cố định) hoặc 'percent' (%) |
| `min_order_value` | DECIMAL(10,2) | Giá tối thiểu để dùng (NULL = không yêu cầu) |
| `start_date` | DATE | Ngày bắt đầu |
| `end_date` | DATE | Ngày kết thúc |
| `usage_limit` | INT | Tối đa bao nhiêu lần sử dụng (NULL = vô hạn) |
| `status` | ENUM | 'active' hoặc 'inactive' |

### Tắt/Bật Voucher

```sql
-- Tắt voucher
UPDATE `vouchers` SET `status` = 'inactive' WHERE `code` = 'SAVE10';

-- Bật voucher
UPDATE `vouchers` SET `status` = 'active' WHERE `code` = 'SAVE10';
```

---

## 🎯 Cách Sử Dụng (Khách Hàng)

1. **Thêm khóa học vào giỏ hàng**
2. **Trên trang giỏ hàng**, nhập mã voucher vào ô "Áp dụng mã giảm giá"
3. **Nhấn nút "Áp dụng"** hoặc nhấn **Enter**
4. Hệ thống sẽ:
   - ✅ Kiểm tra mã hợp lệ
   - ✅ Kiểm tra ngày hết hạn
   - ✅ Kiểm tra giá trị đơn hàng tối thiểu
   - ✅ Kiểm tra lượt sử dụng
5. **Hiển thị tiền giảm** và **tổng tiền cuối**
6. **Thanh toán** với số tiền đã giảm

---

## 📂 Các File

| File | Mô Tả |
|-----|-------|
| `setup-vouchers.php` | Script setup ban đầu |
| `vouchers_setup.sql` | SQL setup nếu chạy thủ công |
| `page/cart/cart.php` | Trang giỏ hàng (đã cập nhật) |
| `page/cart/voucher-handler.php` | Handler kiểm tra & áp dụng voucher |
| `page/cart/remove-voucher.php` | Xóa voucher đã áp dụng |

---

## 🔒 Bảo Mật

- ✅ Kiểm tra session (user phải đăng nhập)
- ✅ Kiểm tra giá trị đơn hàng trước khi áp dụng
- ✅ Lưu voucher trong session (không lưu database dùng lại lần sau)
- ✅ Kiểm tra ngày bắt đầu/kết thúc

---

## 🐛 Xử Lý Lỗi

| Lỗi | Nguyên Nhân | Cách Sửa |
|-----|-----------|---------|
| "Mã voucher không hợp lệ" | Mã không tồn tại hoặc không hoạt động | Kiểm tra mã và trạng thái |
| "Giá trị đơn hàng tối thiểu" | Tổng giỏ nhỏ hơn yêu cầu | Thêm khóa học để đạt mức tối thiểu |
| "Hết lượt sử dụng" | Voucher đã được dùng hết | Chọn voucher khác |
| "Đã hết hạn" | Ngày hôm nay ngoài khoảng start_date đến end_date | Sử dụng voucher khác |

---

## 💡 Ví Dụ

### Ví Dụ 1: Giảm 10% cho 1 triệu đơn hàng
```sql
INSERT INTO `vouchers` (`code`, `description`, `discount_value`, `discount_type`, `start_date`, `end_date`, `status`) 
VALUES ('SAVE10', 'Giảm 10%', 10, 'percent', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'active');
```
- Tiền hàng: 1,000,000₫
- Giảm: 1,000,000 × 10% = 100,000₫
- **Thanh toán: 900,000₫**

### Ví Dụ 2: Giảm cố định 500K cho đơn trên 2 triệu
```sql
INSERT INTO `vouchers` (`code`, `description`, `discount_value`, `discount_type`, `min_order_value`, `start_date`, `end_date`, `status`) 
VALUES ('SAVE500K', 'Giảm 500K', 500000, 'fixed', 2000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'active');
```
- Tiền hàng: 2,500,000₫
- Giảm: 500,000₫ (cố định)
- **Thanh toán: 2,000,000₫**

---

## 📧 Hỗ Trợ

Nếu có vấn đề, kiểm tra:
1. Database đã được setup?
2. Voucher có trạng thái 'active'?
3. Ngày hôm nay nằm giữa start_date và end_date?
4. Console browser có lỗi không?

---

**Bản cập nhật: December 5, 2025**
