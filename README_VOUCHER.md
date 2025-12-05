# 🎟️ Hệ Thống Voucher Giảm Giá - Tài Liệu Hoàn Chỉnh

## 📖 Mục Lục

1. [Giới Thiệu](#-giới-thiệu)
2. [Cài Đặt Nhanh](#-cài-đặt-nhanh)
3. [Tính Năng](#-tính-năng)
4. [Hướng Dẫn Sử Dụng](#-hướng-dẫn-sử-dụng)
5. [Quản Lý Admin](#-quản-lý-admin)
6. [Database](#-database)
7. [Test & Troubleshooting](#-test--troubleshooting)

---

## 🎯 Giới Thiệu

Hệ thống voucher giảm giá cho phép:
- **Khách hàng**: Nhập mã để giảm giá khi mua khóa học
- **Admin**: Quản lý, tạo, sửa, xóa vouchers
- **Thanh toán**: Tự động giảm theo mã áp dụng

---

## 🚀 Cài Đặt Nhanh

### Bước 1: Chạy Script Setup
```bash
http://localhost/WebBanKhoaHocLapTrinh/setup-vouchers.php
```

**Kết quả mong đợi:**
```
✅ Bảng vouchers đã được tạo hoặc đã tồn tại.
✅ Các cột voucher_id và discount_amount đã được thêm vào bảng orders.
✅ Voucher 'SAVE10' đã được thêm.
✅ Voucher 'SAVE500K' đã được thêm.
✅ Voucher 'WELCOME50K' đã được thêm.
✅ Cài đặt hoàn thành!
```

### Bước 2: Kiểm Tra
- Giỏ hàng: `page/cart/cart.php`
- Admin: `page/admin/pages/vouchers.php`

### Bước 3: Xóa File Setup (Tùy Chọn)
```bash
rm setup-vouchers.php
```

---

## ✨ Tính Năng

### Khách Hàng
✅ Nhập mã voucher trên giỏ hàng  
✅ Xem tiền giảm realtime  
✅ Xem QR thanh toán với giá đã giảm  
✅ Bỏ voucher nếu thay đổi ý  
✅ Thông báo lỗi rõ ràng  

### Admin
✅ Tạo voucher mới  
✅ Sửa voucher  
✅ Xóa voucher  
✅ Bật/tắt voucher  
✅ Xem danh sách đầy đủ  

### Hệ Thống
✅ 2 loại giảm: cố định (VNĐ) & phần trăm (%)  
✅ Kiểm tra ngày hết hạn  
✅ Kiểm tra giá tối thiểu  
✅ Kiểm tra lượt sử dụng  
✅ Lưu session an toàn  

---

## 📝 Hướng Dẫn Sử Dụng

### Khách Hàng - Áp Dụng Voucher

**Bước 1:** Thêm khóa học vào giỏ
```
Giỏ: Lộ Trình PHP Master (2,000,000₫)
     Lộ Trình React Pro (1,500,000₫)
Tổng: 3,500,000₫
```

**Bước 2:** Tìm ô "Áp dụng mã giảm giá"
```
🎟️ Áp dụng mã giảm giá (nếu có)
┌────────────────────────────┐
│ Nhập mã voucher [Áp dụng] │
└────────────────────────────┘
```

**Bước 3:** Nhập mã (ví dụ: `SAVE10`)
```
Nhập: SAVE10
```

**Bước 4:** Xem kết quả
```
✅ SAVE10 - Giảm 10% cho tất cả khóa học
────────────────────────────────────
Tiền hàng:        3,500,000₫
Giảm giá:          -350,000₫ (10%)
────────────────────────────────────
Tổng cộng:        3,150,000₫
```

**Bước 5:** Thanh toán
- Nhấn "Xác nhận thanh toán"
- Chọn phương thức (VietQR / MOMO)
- QR hiển thị: **3,150,000₫** (đã giảm)

### Bỏ Voucher

Nếu thay đổi ý định:
```
✅ SAVE10 - Giảm 10% [Bỏ] ← Nhấn đây
```

Reload trang, quay về giá gốc.

---

## 🔐 Quản Lý Admin

### Truy Cập
```
http://localhost/WebBanKhoaHocLapTrinh/page/admin/pages/vouchers.php
```

### Thêm Voucher Mới

**Form:**
```
Mã voucher:         NEWYEAR2025
Mô tả:              Giảm 25% dịp năm mới
Giảm giá:           25
Loại:               Phần trăm (%)
Giá tối thiểu:      500,000 (để trống = không yêu cầu)
Từ ngày:            2025-01-01
Đến ngày:           2025-01-31
Giới hạn:           (để trống = vô hạn)
Trạng thái:         Kích hoạt
```

**Nhấn:** ✅ Thêm Voucher

**Kết quả:**
```
✅ Voucher đã được thêm thành công!
[NEWYEAR2025] ✅ [Sửa] [Xóa]
```

### Sửa Voucher

1. Tìm voucher trong danh sách
2. Nhấn nút **[Sửa]**
3. Cập nhật các trường
4. Nhấn **[Thêm Voucher]** để lưu

### Xóa Voucher

1. Tìm voucher trong danh sách
2. Nhấn nút **[Xóa]**
3. Xác nhận: "Bạn chắc chắn muốn xóa voucher này?"
4. Voucher bị xóa khỏi hệ thống

### Tắt Voucher

Nếu không muốn xóa nhưng tạm dừng:
1. Nhấn **[Sửa]**
2. Đổi Trạng thái thành **Tắt**
3. Lưu

Khách hàng sẽ không thể dùng voucher này.

---

## 💰 Database

### Bảng `vouchers`

```sql
id                  INT - ID tự động
code                VARCHAR(50) - Mã (duy nhất)
description         VARCHAR(255) - Mô tả
discount_value      DECIMAL - Số tiền / %
discount_type       ENUM - 'fixed' hoặc 'percent'
min_order_value     DECIMAL - Tối thiểu (NULL = không)
start_date          DATE - Bắt đầu
end_date            DATE - Kết thúc
usage_limit         INT - Giới hạn (NULL = vô hạn)
status              ENUM - 'active' hoặc 'inactive'
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

### Vouchers Mẫu

| Mã | Giảm | Loại | Tối Thiểu | Giới Hạn |
|----|------|------|----------|---------|
| **SAVE10** | 10 | % | 0 | ∞ |
| **SAVE500K** | 500,000 | VNĐ | 2,000,000 | ∞ |
| **WELCOME50K** | 50,000 | VNĐ | 0 | 100 |

### Truy Vấn Hữu Ích

**Lấy tất cả voucher hoạt động:**
```sql
SELECT * FROM vouchers 
WHERE status = 'active' 
AND CURDATE() BETWEEN start_date AND end_date;
```

**Đếm lần sử dụng voucher:**
```sql
SELECT COUNT(*) FROM orders 
WHERE voucher_id = (SELECT id FROM vouchers WHERE code = 'SAVE10');
```

**Tổng tiền giảm:**
```sql
SELECT SUM(discount_amount) FROM orders 
WHERE voucher_id = (SELECT id FROM vouchers WHERE code = 'SAVE10');
```

---

## 🧪 Test & Troubleshooting

### Test 1: Giảm 10%
```
Giỏ: 1,000,000₫
Voucher: SAVE10 (10%)
Kết quả: Giảm 100,000₫ → Tổng: 900,000₫
```

### Test 2: Yêu Cầu Tối Thiểu
```
Giỏ: 1,500,000₫
Voucher: SAVE500K (yêu cầu 2M)
Kết quả: ❌ "Giá trị đơn hàng tối thiểu là 2,000,000 ₫"
```

### Test 3: Giảm Cố Định
```
Giỏ: 1,000,000₫
Voucher: WELCOME50K (50K cố định)
Kết quả: Giảm 50,000₫ → Tổng: 950,000₫
```

### Test 4: Voucher Không Hợp Lệ
```
Nhập: KHONG_TON_TAI
Kết quả: ❌ "Mã voucher không hợp lệ hoặc đã hết hạn"
```

### Troubleshooting

| Vấn Đề | Nguyên Nhân | Giải Pháp |
|--------|-----------|---------|
| Voucher không hiển thị | Status = inactive | Sửa admin panel |
| Mã không nhận | Hôm nay ngoài phạm vi | Kiểm tra start/end date |
| Giảm quá ít | Loại sai (% vs VNĐ) | Kiểm tra discount_type |
| Bảng không tạo | DB không kết nối | Kiểm tra db.php |

---

## 📚 Tài Liệu Khác

| File | Nội Dung |
|------|---------|
| `VOUCHER_GUIDE.md` | Hướng dẫn chi tiết |
| `IMPLEMENTATION_SUMMARY.md` | Tóm tắt tính năng |
| `TEST_VOUCHER.md` | 8 test cases |
| `VOUCHERS_DATABASE.sql` | SQL queries |
| `SETUP_CHECKLIST.md` | Checklist setup |

---

## 🎁 Vouchers Mẫu Ready To Use

### SAVE10 - Giảm 10%
```
Mã: SAVE10
Giảm: 10%
Hạn: 30 ngày
Yêu cầu: Không
Lượt: Vô hạn
```

### SAVE500K - Giảm 500K
```
Mã: SAVE500K
Giảm: 500,000₫
Hạn: 30 ngày
Yêu cầu: Tối thiểu 2,000,000₫
Lượt: Vô hạn
```

### WELCOME50K - Giảm 50K
```
Mã: WELCOME50K
Giảm: 50,000₫
Hạn: 60 ngày
Yêu cầu: Không
Lượt: Giới hạn 100 lần
```

---

## 🔐 Bảo Mật

- ✅ Kiểm tra user đã đăng nhập
- ✅ Validate mã từ database
- ✅ Kiểm tra ngày hết hạn server-side
- ✅ Kiểm tra giá trị tối thiểu
- ✅ Kiểm tra lượt sử dụng
- ✅ Lưu session (không lưu cookie/localStorage)

---

## 📞 Hỗ Trợ

### Nếu cần thêm tính năng:
1. Mã discount code tự động
2. Email thông báo voucher hết hạn
3. Thống kê sử dụng voucher
4. Tích hợp với email marketing

### Liên Hệ:
Chỉnh sửa các file PHP hoặc liên hệ developer.

---

## ✅ Checklist Hoàn Thành

- ✅ Bảng vouchers tạo
- ✅ 3 vouchers mẫu thêm
- ✅ UI giỏ hàng cập nhật
- ✅ Tính discount hoạt động
- ✅ Admin panel ready
- ✅ Tài liệu hoàn chỉnh
- ✅ Test cases sẵn sàng

---

## 🎉 Ready for Production!

Hệ thống hoàn toàn sẵn sàng. Chỉ cần:

```
1. Chạy: setup-vouchers.php
2. Test: Các voucher mẫu
3. Sử dụng: Bình thường
```

---

**Phiên bản:** 1.0  
**Ngày:** December 5, 2025  
**Trạng thái:** ✅ PRODUCTION READY  

---

**Cảm ơn bạn đã sử dụng hệ thống voucher giảm giá!** 🎟️
