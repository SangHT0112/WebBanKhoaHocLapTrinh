<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/models/CourseDetail.php';

// Kết nối database
$db = (new Database())->connect();

// Lấy course_id từ URL
$courseId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Khởi tạo model ChiTietKhoaHoc
$chiTietKhoaHoc = new ChiTietKhoaHoc($db);

// Lấy thông tin chi tiết khóa học
$courseDetail = $chiTietKhoaHoc->layMotKhoaHoc(ma_khoa_hoc: $courseId);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <?php include __DIR__ . '/layout/head.php'; ?>
    <link rel="stylesheet" href="course-detail.css">
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
                        <button class="enroll-btn" onclick="enrollCourse(<?php echo $courseDetail['id']; ?>)">
                            Đăng Ký Khóa Học
                        </button>
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

                        <!-- Phần chương trình học -->
                        <div class="curriculum-section">
                          <h3>Chương Trình Học Chi Tiết</h3>
                          <?php 
                          $chuongTrinh = json_decode($courseDetail['chuong_trinh_hoc'], true);
                          if ($chuongTrinh && is_array($chuongTrinh)):
                          ?>
                          <div class="curriculum-list">
                              <?php foreach ($chuongTrinh as $module): ?>
                                  <div class="curriculum-item">
                                      <h4><?php echo htmlspecialchars($module['module'] ?? ''); ?> (<?php echo htmlspecialchars($module['duration'] ?? ''); ?>)</h4>
                                      <p><?php echo htmlspecialchars($module['content'] ?? ''); ?></p>
                                  </div>
                              <?php endforeach; ?>
                          </div>
                          <?php endif; ?>
                      </div>

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
                alert(`Đã thêm "${courseDetail.course_name}" vào giỏ hàng!`);
                window.location.href = "page/cart/cart.php";
            } else if (data.status === 'exists') {
                alert(`"${courseDetail.course_name}" đã có trong giỏ hàng.`);
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

    if (course) {
      let detailedContent = '';
      if (courseId === 1) { // Đặc biệt cho PHP Master
        detailedContent = `
          <div class="course-detail-box">
            <div class="course-icon-big">${course.icon}</div>
            <div class="course-info">
              <h1>${course.name}</h1>
              <p>${course.desc} <br><strong>Nội dung chi tiết:</strong> Khóa học được thiết kế dành cho người mới bắt đầu đến nâng cao, tập trung vào phát triển web backend thực tế. Với hơn 45 giờ video chất lượng HD, bài tập thực hành và dự án cuối khóa, bạn sẽ tự tin xây dựng ứng dụng web hoàn chỉnh.</p>
              <div class="price">${course.price}</div>
              <div class="stats">
                <span>${course.students} học viên</span>
                <span>${course.hours} giờ học</span>
              </div>
              <button class="enroll-btn" onclick="enrollCourse(${course.id})">Đăng Ký Khóa Học</button>
              <a href="category.php" class="cta-button">Quay Lại Danh Mục</a>

              <!-- Phần tổng quan lợi ích -->
              <div class="course-overview">
                <h3>Lợi Ích Khi Tham Gia</h3>
                <ul>
                  <li>Chứng chỉ hoàn thành từ Code Cùng Sang, được công nhận trong ngành IT.</li>
                  <li>Hỗ trợ mentor 1:1 qua Discord và Zoom suốt khóa học.</li>
                  <li>Truy cập lifetime vào tài liệu, code mẫu và cộng đồng alumni.</li>
                  <li>Bảo hành hoàn tiền 100% nếu không hài lòng trong 30 ngày đầu.</li>
                </ul>
              </div>

              <!-- Phần chương trình học -->
              <div class="curriculum-section">
                <h3>Chương Trình Học Chi Tiết</h3>
                <div class="curriculum-list">
                  <div class="curriculum-item">
                    <h4>Module 1: PHP Cơ Bản (Tuần 1-2)</h4>
                    <p>Giới thiệu PHP, syntax, biến, hàm, mảng. Xây dựng form xử lý đơn giản.</p>
                  </div>
                  <div class="curriculum-item">
                    <h4>Module 2: Database với MySQL (Tuần 3-4)</h4>
                    <p>Kết nối PDO, CRUD operations, SQL injection prevention. Dự án: Hệ thống quản lý user.</p>
                  </div>
                  <div class="curriculum-item">
                    <h4>Module 3: OOP & Composer (Tuần 5-6)</h4>
                    <p>Class, inheritance, namespaces. Quản lý package với Composer.</p>
                  </div>
                  <div class="curriculum-item">
                    <h4>Module 4: Laravel Framework (Tuần 7-9)</h4>
                    <p>Routing, MVC, Eloquent ORM, Authentication. Xây dựng API RESTful.</p>
                  </div>
                  <div class="curriculum-item">
                    <h4>Module 5: Deploy & Best Practices (Tuần 10)</h4>
                    <p>Deploy lên Heroku/AWS, security, performance optimization. Dự án cuối: E-commerce backend.</p>
                </div>
                </div>
              </div>

              <!-- Phần giảng viên -->
              <div class="instructor-section">
                <div class="instructor-avatar">NS</div>
                <div class="instructor-info">
                  <h4>Huỳnh Thanh Sang - Lead Instructor</h4>
                  <p>5+ năm kinh nghiệm PHP/Laravel tại FPT Software. Đã đào tạo 500+ học viên, chia sẻ trên YouTube với 50k subs.</p>
                </div>
              </div>

              <!-- Phần lợi ích nổi bật -->
              <div class="benefits-section">
                <div class="benefit-card">
                  <i>🎯</i>
                  <h4>Thực Hành 100%</h4>
                  <p>Mọi module đều có dự án thực tế để áp dụng ngay.</p>
                </div>
                <div class="benefit-card">
                  <i>📈</i> 
                  <h4>Cập Nhật 2025</h4>
                  <p>Nội dung theo PHP 8.3, Laravel 11 mới nhất.</p>
                </div>
                <div class="benefit-card">
                  <i>💼</i>
                  <h4>Job Ready</h4>
                  <p>Portfolio dự án để apply việc làm backend dev.</p>
                </div>
              </div>
            </div>
          </div>
        `;
      } else {
        // Nội dung mặc định cho các khóa khác
        detailedContent = `
          <div class="course-detail-box">
            <div class="course-icon-big">${course.icon}</div>
            <div class="course-info">
              <h1>${course.name}</h1>
              <p>${course.desc} <br> Nội dung chi tiết: Video bài giảng, bài tập thực hành, hỗ trợ mentor 24/7.</p>
              <div class="price">${course.price}</div>
              <div class="stats">
                <span>${course.students} học viên</span>
                <span>${course.hours} giờ học</span>
              </div>
              <button class="enroll-btn" onclick="enrollCourse(${course.id})">Đăng Ký Ngay</button>
              <a href="category.php" class="cta-button">Quay Lại Danh Mục</a>
            </div>
          </div>
        `;
      }

      document.getElementById('courseDetail').innerHTML = detailedContent;

      // Gợi ý 2 khóa liên quan (luôn hiển thị)
      const related = courses.filter(c => c.id !== courseId).slice(0, 2);
      const relatedHTML = related.map(r => `
        <div class="course-card" onclick="goToDetail(${r.id})">
          <div class="course-icon">${r.icon}</div>
          <h3 class="course-title">${r.name}</h3>
          <p class="course-desc">${r.desc}</p>
          <div class="price">${r.price}</div>
          <div class="stats">
            <span>${r.students} học viên</span>
            <span>${r.hours} giờ</span>
          </div>
          <button class="enroll-btn">Chi Tiết</button>
        </div>
      `).join('');
      document.getElementById('relatedCourses').innerHTML = relatedHTML;
    } else {
      document.getElementById('courseDetail').innerHTML = '<h1 style="text-align: center; color: #666;">Không tìm thấy khóa học!</h1>';
    }

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