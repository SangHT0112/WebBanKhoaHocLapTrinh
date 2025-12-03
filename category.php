<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/CourseController.php';

// Kết nối DB
$db = (new Database())->connect();
// Tạo controller
$controller = new CourseController($db);
// Lấy danh sách khóa học
$courses = $controller->model->getAll();

$category = $_GET['cat'] ?? 'all';

if ($category === 'all') {
  $courses = $controller->model->getAll();
} else {
  $courses = $controller->model->getByCategory($category);
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include __DIR__ . '/layout/head.php'; ?>
  <link rel="stylesheet" href="category.css">
  <style>
   
  </style>
</head>
<body>
  <header>
    <?php include __DIR__ . '/layout/header.php'; ?>
  </header>
  
  <section class="courses-section">
    <div class="filter-menu">
      <div class="filter-menu">
        <a href="category.php?cat=all" class="active">Tất Cả</a>
        <a href="category.php?cat=backend">Backend</a>
        <a href="category.php?cat=frontend">Frontend</a>
        <a href="category.php?cat=system">System</a>
        <a href="category.php?cat=mobile">Mobile</a>
        <a href="category.php?cat=ai">AI / Machine Learning</a>
        <a href="category.php?cat=devops">DevOps</a>
      </div>
    
      <!-- SỬA: Thêm ID cho input và button để JS xử lý -->
      <div class="flex items-center bg-white/20 rounded-full px-3 py-1">
          <input
            type="text"
            id="searchInput"
            placeholder="Tìm kiếm lộ trình... (gõ để lọc, nhấn 🔍 để AI sâu)"
            class="bg-transparent text-white placeholder-white/70 focus:outline-none px-2 w-40"
          />
          <button id="searchBtn" class="bg-yellow-400 text-gray-800 rounded-full w-8 h-8 flex items-center justify-center hover:bg-yellow-300">🔍</button>
      </div>
    </div>

    <!-- SỬA: Hiển thị info live search count -->
    <div id="liveSearchInfo" class="live-search-info" style="display: none;"></div>

    <div class="courses-grid" id="categoryCourses">
      <?php foreach ($courses as $course): ?>
        <!-- SỬA: Thêm data attributes cho live filter -->
        <div class="course-card" data-category="<?= strtolower($course['danh_muc']) ?>" data-title="<?= htmlspecialchars(strtolower($course['ten_khoa_hoc'])) ?>" data-desc="<?= htmlspecialchars(strtolower($course['mo_ta'])) ?>" onclick="goToDetail(<?= $course['id'] ?>)">
          <div class="course-icon"><?= htmlspecialchars($course['bieu_tuong']) ?></div>
          <h3 class="course-title"><?= htmlspecialchars($course['ten_khoa_hoc']) ?></h3>
          <p class="course-desc"><?= htmlspecialchars($course['mo_ta']) ?></p>
          <div class="price"><?= number_format($course['gia']) ?> VNĐ</div>
          <div class="stats">
            <span><?= $course['so_hoc_vien'] ?> học viên</span>
            <span><?= $course['so_gio_hoc'] ?> giờ</span>
          </div>
          <button class="enroll-btn">Chi Tiết</button>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- SỬA: Section để hiển thị AI search results -->
    <div id="searchResults" class="search-results-section"></div>
  </section>

  <footer>
    <p>&copy; 2025 Code Cùng Sang. Tất cả quyền được bảo lưu.</p>
  </footer>

  <script>
    // SỬA: JS cho live search khi typing + AI search khi click button
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const liveSearchInfo = document.getElementById('liveSearchInfo');
        const cards = document.querySelectorAll('.course-card');

        // Live search: Filter khi typing (debounce 300ms)
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(() => {
                let visibleCount = 0;

                cards.forEach(card => {
                    const title = card.dataset.title || '';
                    const desc = card.dataset.desc || '';
                    const category = card.dataset.category || '';

                    if (query === '' || title.includes(query) || desc.includes(query) || category.includes(query)) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (query !== '') {
                    liveSearchInfo.style.display = 'block';
                    liveSearchInfo.textContent = `Kết quả live cho "${query}": ${visibleCount} khóa học`;
                } else {
                    liveSearchInfo.style.display = 'none';
                }
            }, 300);
        });

        // Reset khi blur nếu rỗng
        searchInput.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                cards.forEach(card => card.style.display = 'block');
                liveSearchInfo.style.display = 'none';
            }
        });

        // AI Deep Search: Khi click button
        searchBtn.addEventListener('click', function() {
            const query = searchInput.value.trim();
            if (!query) return;  // Không search nếu rỗng

            // Hiển thị loading
            searchBtn.innerHTML = '⏳';

            // AJAX POST đến search-handler.php
            fetch('/search-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ query: query })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('AI Search error:', data.error);
                    alert('Lỗi tìm kiếm AI: ' + data.error);
                    return;
                }

                // Render AI results
                updateSearchResults(data.reply, data.raw_results, query);
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Lỗi kết nối AI: ' + error);
            })
            .finally(() => {
                searchBtn.innerHTML = '🔍';  // Reset
            });
        });

        // Enter để trigger AI search (nếu muốn, hoặc chỉ live)
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBtn.click();  // Trigger AI
            }
        });
    });

    // Hàm render AI results (reply + raw_results cards)
    function updateSearchResults(reply, rawResults, query) {
        let resultsSection = document.getElementById('searchResults');
        if (!resultsSection) {
            resultsSection = document.createElement('div');
            resultsSection.id = 'searchResults';
            resultsSection.className = 'search-results-section mt-8 p-4 bg-gray-100 rounded-lg';
            document.querySelector('.courses-grid').parentNode.appendChild(resultsSection);
        }

        // Render AI reply
        const replyDiv = document.createElement('div');
        replyDiv.className = 'ai-reply mb-4 text-center';
        replyDiv.innerHTML = `<h3 class="text-xl font-bold mb-2">Gợi ý AI sâu cho "${query}":</h3><p>${reply}</p>`;
        resultsSection.innerHTML = '';
        resultsSection.appendChild(replyDiv);

        // Render raw_results cards
        if (rawResults && rawResults.length > 0) {
            const gridDiv = document.createElement('div');
            gridDiv.className = 'courses-grid-ai mt-4';
            rawResults.forEach(course => {
                const card = document.createElement('div');
                card.className = 'course-card-ai';
                card.onclick = () => goToDetail(course.id);
                card.innerHTML = `
                    <div class="course-icon">${course.bieu_tuong || '📚'}</div>
                    <h3 class="course-title">${course.ten_khoa_hoc}</h3>
                    <p class="course-desc">${course.mo_ta || course.description || ''}</p>
                    <div class="price">${parseFloat(course.gia || course.price).toLocaleString()} VNĐ</div>
                    <div class="stats">
                        <span>${course.so_hoc_vien || course.student_count || 0} học viên</span>
                        <span>${course.so_gio_hoc || course.total_hours || 0} giờ</span>
                        ${course.avg_rating ? `<span>⭐ ${course.avg_rating.toFixed(1)}</span>` : ''}
                    </div>
                    <button class="enroll-btn">Chi Tiết</button>
                `;
                gridDiv.appendChild(card);
            });
            resultsSection.appendChild(gridDiv);
        } else {
            const noResults = document.createElement('p');
            noResults.className = 'no-results text-center text-gray-500';
            noResults.textContent = 'AI không tìm thấy khóa học phù hợp. Thử từ khóa khác nhé! 🔍';
            resultsSection.appendChild(noResults);
        }

        // Scroll đến AI results
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }

    function filterCategory(cat) {
      const cards = document.querySelectorAll('.course-card');
      cards.forEach(card => {
        if (cat === 'all' || card.dataset.category === cat) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
      document.querySelectorAll('.filter-menu a').forEach(a => a.classList.remove('active'));
      event.target.classList.add('active');
    }

    function goToDetail(id) {
      window.location.href = `course-detail.php?id=${id}`;
    }
  </script>
</body>
</html>