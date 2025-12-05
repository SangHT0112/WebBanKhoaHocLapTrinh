<?php
session_start();  // Bắt đầu session

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/models/CourseDetail.php';

// Kết nối database
$db = (new Database())->connect();

// Lấy course_id từ URL
$courseId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Kiểm tra login (nếu chưa, redirect đến login)
if (!isset($_SESSION['id'])) {
    header("Location: /page/login/login.php");
    exit;
}
$ma_nguoi_dung = $_SESSION['id'];  // Lấy user_id từ session

// Khởi tạo model ChiTietKhoaHoc
$chiTietKhoaHoc = new ChiTietKhoaHoc($db);

// Lấy thông tin chi tiết khóa học (truyền ma_nguoi_dung để kiểm tra enrollment)
$courseDetail = $chiTietKhoaHoc->layMotKhoaHoc($courseId, $ma_nguoi_dung);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <?php include __DIR__ . '/layout/head.php'; ?>
    <link rel="stylesheet" href="course-detail.css">
    <style>
        /* Thêm CSS cho accordion */
        .curriculum-item {
            border: 1px solid #ddd;
            margin-bottom: 10px;
            border-radius: 8px;
            overflow: hidden;
        }
        .curriculum-item-header {
            background-color: #f8f9fa;
            padding: 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .curriculum-item-header:hover {
            background-color: #e9ecef;
        }
        .curriculum-item-header .toggle-icon {
            font-size: 18px;
            transition: transform 0.3s;
        }
        .curriculum-item-header.active .toggle-icon {
            transform: rotate(180deg);
        }
        .curriculum-item-content {
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s;
        }
        .curriculum-item-content.active {
            padding: 15px;
            max-height: 1000px; /* Điều chỉnh theo nhu cầu */
        }
        .lessons-list {
            list-style: none;
            padding: 0;
        }
        .lesson-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .lesson-item:last-child {
            border-bottom: none;
        }
        .lesson-type {
            font-size: 12px;
            color: #666;
            padding: 2px 6px;
            border-radius: 4px;
            background-color: #f0f0f0;
        }
        .lesson-duration {
            color: #999;
        }
        /* Thêm CSS cho nút bắt đầu học */
        .start-btn {
            background-color: #28a745;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .start-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <header>
        <?php include __DIR__ . '/layout/header.php'; ?>
    </header>

    <section class="courses-section">
        <div class="product-detail">
            <?php if ($courseDetail): ?>
                <div class="course-detail-box">
                    <div class="course-icon-big"><?php echo htmlspecialchars($courseDetail['bieu_tuong']); ?></div>
                    <div class="course-info">
                        <h1><?php echo htmlspecialchars($courseDetail['ten_khoa_hoc']); ?></h1>
                        <p>
                            <?php echo htmlspecialchars($courseDetail['mo_ta_ngan']); ?><br>
                            <strong>Nội dung chi tiết:</strong> 
                            <?php echo htmlspecialchars($courseDetail['mo_ta_day_du']); ?>
                        </p>
                        <div class="price"><?php echo htmlspecialchars(number_format($courseDetail['gia'], 0, ',', '.')); ?> VNĐ</div>
                        <div class="stats">
                            <span><?php echo htmlspecialchars($courseDetail['so_hoc_vien']); ?> học viên</span>
                            <span><?php echo htmlspecialchars($courseDetail['so_gio_hoc']); ?> giờ học</span>
                        </div>
                        
                        <!-- Nút đăng ký / bắt đầu học (có điều kiện dựa trên da_dang_ky từ model) -->
                        <?php if (isset($courseDetail['da_dang_ky']) && $courseDetail['da_dang_ky']): ?>
                            <a href="learn.php?course_id=<?php echo intval($courseId); ?>" class="start-btn">Bắt Đầu Khóa Học</a>
                            <?php if (isset($courseDetail['tien_do'])): ?>
                                <p style="margin-top: 10px; color: #666;">Tiến độ hiện tại: <?php echo number_format($courseDetail['tien_do'], 1); ?>%</p>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="enroll-btn" onclick="enrollCourse(<?php echo $courseDetail['id']; ?>)">
                                Đăng Ký Khóa Học
                            </button>
                        <?php endif; ?>
                        
                        <a href="category.php" class="cta-button">Quay Lại Danh Mục</a>

                        <!-- Phần tổng quan lợi ích -->
                        <div class="course-overview">
                            <h3>Lợi Ích Khi Tham Gia</h3>
                            <?php 
                            $loiIch = json_decode($courseDetail['loi_ich'], true);
                            if ($loiIch && is_array($loiIch)): 
                            ?>
                            <ul>
                                <?php foreach ($loiIch as $loi_ich): ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($loi_ich['title'] ?? ''); ?>:</strong>
                                        <?php echo htmlspecialchars($loi_ich['description'] ?? ''); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>

                       <div class="curriculum-section">
                        <h3>Chương Trình Học</h3>

                        <?php if (!empty($courseDetail['modules'])): ?>
                            <div class="curriculum-list">
                                <?php foreach ($courseDetail['modules'] as $module): ?>
                                    <div class="curriculum-item">
                                        <div class="curriculum-item-header">
                                            <span>
                                                <?php echo htmlspecialchars($module['module_name']); ?> 
                                                (<?php echo htmlspecialchars($module['duration']); ?>)
                                            </span>
                                            <span class="toggle-icon">▼</span>
                                        </div>

                                        <div class="curriculum-item-content">
                                            <p><?php echo htmlspecialchars($module['content']); ?></p>

                                            <?php if (!empty($module['lessons'])): ?>
                                            <ul class="lessons-list">
                                                <?php foreach ($module['lessons'] as $lesson): ?>
                                                    <li class="lesson-item">
                                                        <span><?php echo htmlspecialchars($lesson['ten_bai_hoc']); ?></span>
                                                        <span class="lesson-duration"><?php echo htmlspecialchars($lesson['thoi_luong']); ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>Chưa có chương trình học.</p>
                        <?php endif; ?>
                    </div>

                    <script>
                    document.querySelectorAll('.curriculum-item-header').forEach(header => {
                        header.addEventListener('click', () => {
                            header.classList.toggle('active');
                            header.nextElementSibling.classList.toggle('active');
                        });
                    });
                    </script>


                        <!-- Phần giảng viên -->
                        <div class="instructor-section">
                            <div class="instructor-avatar">
                                <?php 
                                $kyTuDau = mb_substr($courseDetail['ten_giang_vien'], 0, 2, 'UTF-8');
                                echo htmlspecialchars($kyTuDau);
                                ?>
                            </div>
                            <div class="instructor-info">
                                <h4><?php echo htmlspecialchars($courseDetail['ten_giang_vien']); ?> - Giảng viên chính</h4>
                                <p><?php echo htmlspecialchars($courseDetail['gioi_thieu_giang_vien']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <h1 style="text-align: center; color: #666;">Không tìm thấy khóa học!</h1>
            <?php endif; ?>
        </div>
        <?php if ($courseDetail): ?>
        <div class="related-products">
            <?php include __DIR__ . '/review.php'; ?>
            <h2>Lộ Trình Liên Quan</h2>
            <div class="courses-grid">
                <?php
                // TODO: Implement related courses logic here
                // This will be implemented when we have the courses table in database
                ?>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <footer id="contact">
        <?php include __DIR__ . '/layout/footer.php'; ?>
    </footer>

    <script>
    function enrollCourse(id) {
        // Lấy thông tin khóa học từ PHP
        const courseDetail = <?php echo json_encode($courseDetail); ?>;

        // Gửi dữ liệu sang PHP để lưu session
        fetch('page/cart/add-to-cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${courseDetail.ma_khoa_hoc}&name=${encodeURIComponent(courseDetail.ten_khoa_hoc)}&price=${encodeURIComponent(courseDetail.gia)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'added') {
                
                alert(`Đã thêm "${courseDetail.ten_khoa_hoc}" vào giỏ hàng!`);
                window.location.href = "page/cart/cart.php";
            } else if (data.status === 'exists') {
                alert(`"${courseDetail.ten_khoa_hoc}" đã có trong giỏ hàng.`);
            } else {
                alert('Lỗi khi thêm khóa học!');
            }
        });
    }
    // Copy mảng courses từ category.html
    const courses = [
      { id: 1, name: 'Lộ Trình PHP Master', price: '2.500.000 VNĐ', desc: 'Xây dựng web app mạnh mẽ với PHP, MySQL và Laravel.', icon: '🐘', students: '1.200', hours: '45', category: 'backend' },
      { id: 2, name: 'Lộ Trình React Pro', price: '3.200.000 VNĐ', desc: 'Tạo giao diện động với React, Hooks và Redux.', icon: '⚛️', students: '950', hours: '60', category: 'frontend' },
      { id: 3, name: 'Lộ Trình C++ Advanced', price: '2.800.000 VNĐ', desc: 'Lập trình hệ thống với C++, STL và OOP.', icon: '⚡', students: '750', hours: '50', category: 'system' },
      { id: 4, name: 'Lộ Trình Mobile Flutter', price: '3.500.000 VNĐ', desc: 'Xây dựng ứng dụng iOS & Android với Flutter.', icon: '📱', students: '600', hours: '55', category: 'mobile' },
      { id: 5, name: 'Lộ Trình AI Cơ Bản', price: '4.200.000 VNĐ', desc: 'Machine Learning & Deep Learning với Python.', icon: '🤖', students: '430', hours: '70', category: 'ai' },
      { id: 6, name: 'Lộ Trình DevOps Thực Chiến', price: '3.800.000 VNĐ', desc: 'CI/CD, Docker, Kubernetes, AWS, Cloud.', icon: '☁️', students: '520', hours: '65', category: 'devops' }
    ];

    // Lấy id từ URL
    const urlParams = new URLSearchParams(window.location.search);
    const courseId = parseInt(urlParams.get('id'));

    const course = courses.find(c => c.id === courseId);

    function goToDetail(id) {
      window.location.href = `course-detail.php?id=${id}`;
    }

    function enrollCourse(id) {
      const course = courses.find(c => c.id === id);

      // Gửi dữ liệu sang PHP để lưu session
      fetch('page/cart/add-to-cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${course.id}&name=${encodeURIComponent(course.name)}&price=${encodeURIComponent(course.price)}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'added') {
          alert(`Đã thêm "${course.name}" vào giỏ hàng!`);
          window.location.href = "page/cart/cart.php"; // 👉 chuyển sang trang giỏ hàng
        } else if (data.status === 'exists') {
          alert(`"${course.name}" đã có trong giỏ hàng.`);
        } else {
          alert('Lỗi khi thêm khóa học!');
        }
      });
    }
    </script>
</body>
</html>
<?php include __DIR__ . '/search-results.php'; ?>