## 🧪 Hướng Dẫn Test Voucher

### ✅ Test 1: Áp Dụng Voucher Giảm Phần Trăm

**Setup:**
- Thêm 2 khóa học vào giỏ (1M₫ mỗi khóa)
- Tổng: 2,000,000₫

**Test:**
1. Nhập mã: `SAVE10`
2. Nhấn "Áp dụng"
3. **Kết quả dự kiến:**
   - ✅ Tiền hàng: 2,000,000₫
   - ✅ Giảm: 200,000₫ (10%)
   - ✅ Tổng thanh toán: 1,800,000₫

---

### ✅ Test 2: Voucher Yêu Cầu Giá Tối Thiểu

**Setup:**
- Thêm 1 khóa học: 1,500,000₫
- SAVE500K yêu cầu tối thiểu 2,000,000₫

**Test:**
1. Nhập mã: `SAVE500K`
2. **Kết quả dự kiến:**
   - ❌ "Giá trị đơn hàng tối thiểu là 2,000,000 ₫"
3. Thêm khóa học khác để đạt 2,000,000₫
4. Nhập lại: `SAVE500K`
5. **Kết quả dự kiến:**
   - ✅ Giảm: 500,000₫
   - ✅ Tổng thanh toán: 2,000,000₫ (4,000,000 - 500,000)

---

### ✅ Test 3: Voucher Cố Định

**Setup:**
- Thêm khóa học: 1,000,000₫
- WELCOME50K: giảm cố định 50,000₫

**Test:**
1. Nhập mã: `WELCOME50K`
2. **Kết quả dự kiến:**
   - ✅ Tiền hàng: 1,000,000₫
   - ✅ Giảm: 50,000₫
   - ✅ Tổng thanh toán: 950,000₫

---

### ✅ Test 4: Xóa Voucher Đã Áp Dụng

**Setup:**
- Đã áp dụng SAVE10 thành công
- Bấm nút "Bỏ"

**Kết quả dự kiến:**
- ✅ Reload trang
- ✅ Hiến thị lại tổng tiền gốc (không giảm)
- ✅ Ô voucher trống

---

### ✅ Test 5: Voucher Không Hợp Lệ

**Test:**
1. Nhập mã: `INVALID123`
2. Nhấn "Áp dụng"
3. **Kết quả dự kiến:**
   - ❌ "Mã voucher không hợp lệ hoặc đã hết hạn"

---

### ✅ Test 6: QR Thanh Toán Với Voucher

**Setup:**
- Giỏ hàng: 2,000,000₫
- Áp dụng SAVE10: -200,000₫
- Tổng: 1,800,000₫

**Test:**
1. Nhấn "Xác nhận thanh toán"
2. Chọn "Quét mã VietQR"
3. **Kết quả dự kiến:**
   - ✅ QR code hiển thị số tiền: **1,800,000₫**
   - ✅ Nội dung: `Thanh toan khoa hoc user [USER_ID]`

---

### ✅ Test 7: Admin - Tạo Voucher Mới

**Điều hướng:**
1. Truy cập: `page/admin/pages/vouchers.php`
2. Điền form:
   - Mã: `NEWYEAR25`
   - Mô tả: `Giảm 25% dịp năm mới`
   - Giảm: `25`
   - Loại: `percent`
   - Từ: `2025-01-01`
   - Đến: `2025-01-31`
3. Nhấn "Thêm Voucher"

**Kết quả dự kiến:**
- ✅ "✅ Voucher đã được thêm thành công!"
- ✅ Voucher xuất hiện trong danh sách

---

### ✅ Test 8: Admin - Tắt Voucher

**Test:**
1. Tìm NEWYEAR25 trong danh sách
2. Bấm nút "Sửa"
3. Đổi Status thành "Tắt"
4. Lưu

**Kết quả dự kiến:**
1. Quay lại giỏ hàng
2. Nhập `NEWYEAR25`
3. ❌ "Mã voucher không hợp lệ hoặc đã hết hạn"

---

### 🎯 Kiểm Tra Cuối Cùng

```bash
# 1. Bảng vouchers tồn tại?
mysql> SELECT * FROM vouchers;

# 2. 3 voucher mẫu đã được tạo?
# SAVE10, SAVE500K, WELCOME50K

# 3. Cột trong orders?
mysql> SELECT column_name FROM information_schema.columns WHERE table_name='orders' AND column_name IN ('voucher_id', 'discount_amount');

# 4. Session lưu voucher?
# Sau khi áp dụng, $_SESSION['applied_voucher'] phải chứa dữ liệu
```

---

## 💡 Ghi Chú

- Voucher WELCOME50K có giới hạn 100 lần dùng. Sau 100 lần sẽ không dùng được.
- SAVE500K yêu cầu đơn tối thiểu 2M, nếu dưới không thể áp dụng.
- QR thanh toán sẽ tự update giá trị khi áp dụng/bỏ voucher.

---

**Happy Testing! 🎉**
