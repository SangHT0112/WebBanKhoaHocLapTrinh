# 🔍 Hệ Thống Tìm Kiếm Khóa Học - Hướng Dẫn Sử Dụng

## ✨ Các Tính Năng

### 1. **Tìm Kiếm Trong Header** (Tất cả các trang)
- **Vị trí:** Thanh navigation header
- **Cách sử dụng:**
  - Gõ từ khóa tìm kiếm vào input
  - Nhấn **ENTER** hoặc click nút **🔍**
  - Modal kết quả hiển thị lên

### 2. **Hiển Thị Kết Quả**
Modal sẽ hiển thị:
- **💡 Gợi ý từ AI** - Lời giới thiệu được AI tạo ra sâu sắc
- **📚 Danh sách khóa học** - Thẻ khóa học tương ứng với 3 cột trên desktop, 1 cột trên mobile
- **Thông tin chi tiết:**
  - Icon khóa học
  - Tên khóa học
  - Mô tả ngắn (tối đa 2 dòng)
  - ⭐ Rating
  - 💰 Giá khóa học
  - 📊 Số học viên & giờ học
  - Nút "Xem chi tiết"

### 3. **Tính Năng Từ search-handler.php**
- Tìm kiếm trong database khóa học
- Gọi API Gemini AI để tạo gợi ý sâu
- Lọc kết quả theo từ khóa
- Sắp xếp theo số học viên + rating

## 🎨 Giao Diện

### Modal Kết Quả
```
┌─────────────────────────────────────────────┐
│ 🔍 Kết Quả Tìm Kiếm  [x]                   │  <- Header Gradient Purple
├─────────────────────────────────────────────┤
│ Tìm kiếm: "từ khóa"                        │
│                                            │
│ 💡 Gợi ý từ AI (Amber background)          │
│ [Text AI Response - Typewriter effect]     │
│                                            │
│ 📚 Khóa học phù hợp (3)                    │
│ ┌──────────────┐ ┌──────────────┐ ...      │
│ │ 📚           │ │              │          │
│ │ Tên khóa học │ │              │          │
│ │ Mô tả ngắn   │ │              │          │
│ │ ⭐ Rating    │ │              │          │
│ │ 1500 VNĐ     │ │              │          │
│ │ 50 học viên  │ │              │          │
│ │ [Xem chi...]│ │              │          │
│ └──────────────┘ └──────────────┘ ...      │
│                                            │
├─────────────────────────────────────────────┤
│ 💻 Kết quả AI   [Đóng]                     │  <- Footer
└─────────────────────────────────────────────┘
```

## 🛠️ Cài Đặt Yêu Cầu

### 1. API Key Gemini
Cần thiết để AI tạo gợi ý. Thêm vào `config/api-key.php`:
```php
<?php
define('GEMINI_API_KEYS', [
    'your-api-key-1',
    'your-api-key-2',  // Dự phòng
]);
?>
```

### 2. Database
Cần có bảng `courses`:
- id
- ten_khoa_hoc
- mo_ta
- gia
- so_hoc_vien
- so_gio_hoc
- bieu_tuong
- danh_muc_id

### 3. HTML Structure
Modal được include trong tất cả các trang qua:
- `index.php` - ✅ Đã thêm
- `category.php` - ✅ Đã thêm
- `course-detail.php` - ✅ Đã thêm
- `learn.php` - ✅ Đã thêm
- `page/cart/cart.php` - ✅ Đã thêm

## 📱 Responsive Design

### Desktop (> 768px)
- 3 cột grid cho khóa học
- Modal rộng 1000px
- Header đầy đủ

### Mobile (≤ 768px)
- 1 cột grid
- Modal full width (95vw)
- Padding nhỏ hơn
- Footer flex column

## 🎬 Hiệu Ứng Animation

### Typewriter Effect
- AI reply hiển thị từng chữ
- Tốc độ: 20ms/chữ
- Tạo trải nghiệm tương tác sinh động

### Smooth Transitions
- Modal scale up 0.95 → 1 (0.3s)
- Overlay fade in/out (0.3s)
- Card hover translate up (-4px)
- Border color change on hover

## 🚀 Cách Hoạt Động

### Luồng Tìm Kiếm
```
1. User nhập từ khóa + nhấn ENTER/🔍
   ↓
2. JavaScript gọi performSearch()
   ↓
3. AJAX POST đến search-handler.php
   ↓
4. Server:
   - Lấy tất cả khóa học từ DB
   - Lọc theo từ khóa
   - Sắp xếp theo popularity
   - Gọi Gemini API tạo reply
   ↓
5. Return JSON:
   {
     query: "từ khóa",
     filtered_courses: [...],
     reply: "AI text response"
   }
   ↓
6. JavaScript hiển thị:
   - Typewriter AI reply
   - Render course cards
   - Show modal
```

## 🔧 Tùy Chỉnh

### Thay Đổi Số Cột Grid
File `search-modal.css` dòng ~142:
```css
#coursesContainer {
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
}
```

### Tốc Độ Typewriter
File `search-results.php` dòng ~490:
```javascript
typeWriterEffect(aiReplyDiv, data.reply, 25); // 25ms/chữ
```

### Màu Sắc & Gradient
File `search-modal.css`:
- Header gradient: `#667eea` → `#764ba2`
- Button gradient: Tương tự
- AI section: `#fef3c7` → `#fed7aa`

## ✅ Kiểm Tra Hoạt Động

1. Truy cập trang bất kỳ (index.php, category.php, ...)
2. Tìm kiếm input trong header
3. Gõ "React" hoặc "PHP" hoặc "Python"
4. Nhấn ENTER hoặc click 🔍
5. Modal hiển thị kết quả ✨

## 📝 Ghi Chú

- ✅ Hỗ trợ UTF-8 tiếng Việt
- ✅ Xử lý lỗi API gracefully
- ✅ Fallback UI khi AI không available
- ✅ Mobile-first responsive
- ✅ Accessibility labels trong form
- ✅ XSS protection với `escapeHtml()`

## 🎯 Tiếp Theo (Optional)

- [ ] Thêm favorite/bookmark khóa học
- [ ] History tìm kiếm gần đây
- [ ] Filter nâng cao (giá, rating, ...)
- [ ] Advanced search syntax
- [ ] Voice search integration
