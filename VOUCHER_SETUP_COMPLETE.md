# ✅ Hệ Thống Voucher Giảm Giá - Cài Đặt Hoàn Thành

## 🎯 Các Tính Năng Đã Thêm

### 1️⃣ **Giỏ Hàng (cart.php)** - Cập nhật
- ✅ Hiển thị ô nhập mã voucher
- ✅ Hiển thị tiền hàng gốc
- ✅ Hiển thị tiền giảm giá (nếu áp dụng voucher)
- ✅ Hiển thị tổng tiền thanh toán cuối cùng
- ✅ Tích hợp với QR thanh toán (VietQR sử dụng số tiền đã giảm)

### 2️⃣ **Xử Lý Voucher (voucher-handler.php)** - Mới
- ✅ Kiểm tra mã voucher hợp lệ
- ✅ Kiểm tra ngày hết hạn
- ✅ Kiểm tra giá trị đơn hàng tối thiểu
- ✅ Kiểm tra lượt sử dụng còn lại
- ✅ Lưu vào session
- ✅ Trả về JSON để update giao diện

### 3️⃣ **Xóa Voucher (remove-voucher.php)** - Mới
- ✅ Cho phép khách hàng bỏ voucher đã áp dụng
- ✅ Reload trang để cập nhật

### 4️⃣ **Bảng Vouchers (Database)**
- ✅ Tạo bảng `vouchers` với các cột cần thiết
- ✅ Thêm cột `voucher_id` và `discount_amount` vào `orders`
- ✅ 3 voucher mẫu được tạo sẵn

### 5️⃣ **Admin Panel (page/admin/pages/vouchers.php)** - Mới
- ✅ Quản lý (thêm, sửa, xóa) voucher
- ✅ Bật/tắt trạng thái voucher
- ✅ Đặt ngày hết hạn, giới hạn lượt sử dụng, giá tối thiểu

---

## 🚀 Cách Cài Đặt

### **Bước 1: Chạy Setup**
```
http://localhost/WebBanKhoaHocLapTrinh/setup-vouchers.php
```

Output sẽ hiển thị:
```
✅ Bảng vouchers đã được tạo hoặc đã tồn tại.
✅ Các cột voucher_id và discount_amount đã được thêm vào bảng orders.
✅ Voucher 'SAVE10' đã được thêm.
✅ Voucher 'SAVE500K' đã được thêm.
✅ Voucher 'WELCOME50K' đã được thêm.
✅ Cài đặt hoàn thành! Bạn có thể xóa file này sau khi chạy.
```

### **Bước 2: Xóa file setup (tùy chọn)**
```bash
rm setup-vouchers.php
```

---

## 💰 Các Voucher Mẫu Được Tạo

| Mã | Giảm | Điều Kiện | Hạn |
|----|------|---------|-----|
| **SAVE10** | 10% | Không | 30 ngày |
| **SAVE500K** | 500K₫ | Tối thiểu 2M₫ | 30 ngày |
| **WELCOME50K** | 50K₫ | Không | 60 ngày, giới hạn 100 lần |

---

## 📝 Hướng Dẫn Sử Dụng

### Khách Hàng:
1. Thêm khóa học vào giỏ
2. Nhập mã voucher (VD: `SAVE10`)
3. Nhấn "Áp dụng" hoặc Enter
4. Xem tiền giảm và tổng thanh toán
5. Thanh toán với số tiền đã giảm

### Nhân Viên Admin:
1. Truy cập: `page/admin/pages/vouchers.php`
2. Thêm voucher mới từ form
3. Sửa/xóa voucher từ danh sách
4. Bật/tắt trạng thái

---

## 📂 Các File Liên Quan

```
WebBanKhoaHocLapTrinh/
├── setup-vouchers.php                    (Chạy 1 lần, sau đó có thể xóa)
├── vouchers_setup.sql                    (SQL backup - chạy nếu cần)
├── VOUCHER_GUIDE.md                      (Hướng dẫn chi tiết)
├── page/cart/
│   ├── cart.php                          (✏️ Cập nhật)
│   ├── voucher-handler.php               (🆕 Mới)
│   └── remove-voucher.php                (🆕 Mới)
└── page/admin/pages/
    └── vouchers.php                      (🆕 Quản lý voucher)
```

---

## 🔌 API Response (voucher-handler.php)

### ✅ Thành công:
```json
{
  "status": "success",
  "message": "Áp dụng voucher thành công",
  "discount_value": 10,
  "discount_type": "percent",
  "description": "Giảm 10% cho tất cả khóa học"
}
```

### ❌ Lỗi:
```json
{
  "status": "error",
  "message": "Mã voucher không hợp lệ hoặc đã hết hạn"
}
```

---

## 🔒 Bảo Mật

- ✅ Kiểm tra đăng nhập bắt buộc
- ✅ Validate dữ liệu voucher
- ✅ Kiểm tra ngày trước khi áp dụng
- ✅ Lưu session, không lưu database cho lần sau

---

## 🐛 Troubleshooting

| Lỗi | Nguyên Nhân | Giải Pháp |
|-----|-----------|---------|
| Bảng vouchers không được tạo | Database không kết nối | Kiểm tra db.php |
| Voucher không nhận | Trạng thái inactive | Bật ở admin panel |
| Không giảm tiền | Ngày hôm nay ngoài phạm vi | Kiểm tra start_date/end_date |

---

## ✨ Hoàn Tất!

Hệ thống voucher giảm giá đã sẵn sàng. Khách hàng có thể:
- 🎟️ Nhập mã giảm giá trên giỏ hàng
- 💰 Thấy ngay tiền giảm
- 💳 Thanh toán với giá đã giảm

Admin có thể:
- ➕ Tạo voucher mới
- ✏️ Sửa/xóa voucher
- 🔐 Quản lý trạng thái và hạn chế

---

**Cập nhật: December 5, 2025**
