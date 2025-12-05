<?php
// search-results.php - Modal hiển thị kết quả tìm kiếm đẹp
?>

<!-- Link CSS và JS - Sử dụng absolute path -->
<link rel="stylesheet" href="/search-modal.css">
<script src="/search-functions.js"></script>

<!-- Search Results Overlay -->
<div id="searchResultsOverlay" onclick="closeSearchResults()"></div>

<!-- Search Results Modal -->
<div id="searchResultsModal">
  <div>
    <!-- Header -->
    <div class="search-header">
      <div>
        <h2>🔍 Kết Quả Tìm Kiếm</h2>
        <p id="searchQueryDisplay">Tìm kiếm: <strong>""</strong></p>
      </div>
      <button class="search-close" onclick="closeSearchResults()">×</button>
    </div>

    <!-- Content -->
    <div class="search-content">
      <!-- AI Reply Section -->
      <div class="ai-reply-section">
        <h3>💡 Gợi ý từ AI</h3>
        <div id="aiReplyContainer" class="ai-reply-text">
          <div class="flex items-center gap-2">
            <div class="animate-pulse">⏳</div>
            <span>Đang tìm kiếm...</span>
          </div>
        </div>
      </div>

      <!-- Courses Header -->
      <div class="courses-header">
        <h3>📚 Khóa học phù hợp</h3>
        <span id="courseCount" class="course-count-badge">0</span>
      </div>

      <!-- Courses Grid -->
      <div id="coursesContainer"></div>

      <!-- No Results -->
      <div id="noResultsContainer" class="hidden">
        <div class="no-results-emoji">😔</div>
        <p class="no-results-text">Không tìm thấy khóa học phù hợp</p>
        <p class="no-results-hint">Hãy thử tìm kiếm với từ khóa khác hoặc xem <a href="category.php" style="color:#4f46e5; text-decoration:underline;">danh mục đầy đủ</a></p>
      </div>
    </div>

    <!-- Footer -->
    <div class="search-footer">
      <p class="search-footer-text">💻 Kết quả tìm kiếm được AI phân tích</p>
      <button class="search-footer-btn" onclick="closeSearchResults()">Đóng</button>
    </div>
  </div>
</div>
