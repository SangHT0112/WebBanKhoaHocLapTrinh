// search-functions.js - All search-related JavaScript functions

// Tìm kiếm khi click nút hoặc press Enter
async function performSearch() {
  const query = document.getElementById('searchInput').value.trim();
  if (!query) {
    alert('Vui lòng nhập từ khóa tìm kiếm!');
    return;
  }

  showSearchResults();
  document.getElementById('searchQueryDisplay').innerHTML = `Tìm kiếm: <strong>"${escapeHtml(query)}"</strong>`;
  
  // Reset containers
  document.getElementById('coursesContainer').innerHTML = '';
  document.getElementById('noResultsContainer').classList.add('hidden');
  document.getElementById('aiReplyContainer').innerHTML = '<div class="animate-pulse">⏳ Đang tìm kiếm...</div>';

  try {
    const response = await fetch('/search-handler.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ query: query })
    });

    const data = await response.json();

    if (data.error) {
      throw new Error(data.error);
    }

    // Hiển thị AI reply với typewriter effect
    const aiDiv = document.getElementById('aiReplyContainer');
    aiDiv.textContent = '';
    typeWriterEffect(aiDiv, data.reply);

    // Hiển thị courses
    const courses = data.filtered_courses || [];
    const container = document.getElementById('coursesContainer');
    
    if (courses.length === 0) {
      document.getElementById('noResultsContainer').classList.remove('hidden');
      document.getElementById('courseCount').textContent = '0';
      return;
    }

    document.getElementById('courseCount').textContent = courses.length;

    courses.forEach(course => {
      const card = document.createElement('div');
      card.className = 'course-result-card';
      card.onclick = () => window.location.href = `/course-detail.php?id=${course.id}`;
      
      const rating = parseFloat(course.avg_rating || 0).toFixed(1);
      const stars = '⭐'.repeat(Math.round(rating)) + (Math.round(rating) < 5 ? '☆'.repeat(5 - Math.round(rating)) : '');

      card.innerHTML = `
        <div class="course-icon-large">${escapeHtml(course.bieu_tuong || '📚')}</div>
        <h4>${escapeHtml(course.ten_khoa_hoc)}</h4>
        <p>${escapeHtml(course.mo_ta || '')}</p>
        
        <div class="rating-section">
          <span class="rating-stars">${stars}</span>
          <span class="rating-count">(${rating})</span>
        </div>

        <div class="course-meta">
          <span class="course-price-tag">${Number(course.gia).toLocaleString('vi-VN')} VNĐ</span>
          <div class="course-stats">
            <div>${course.so_hoc_vien} học viên</div>
            <div>${course.so_gio_hoc} giờ</div>
          </div>
        </div>

        <button class="course-btn">Xem chi tiết</button>
      `;
      
      container.appendChild(card);
    });

  } catch (error) {
    console.error('Search error:', error);
    document.getElementById('aiReplyContainer').innerHTML = `<span style="color:#dc2626;">❌ Lỗi: ${escapeHtml(error.message)}</span>`;
  }
}

// Typewriter effect
function typeWriterEffect(element, text, speed = 20) {
  let i = 0;
  element.textContent = '';
  
  function type() {
    if (i < text.length) {
      element.textContent += text.charAt(i);
      i++;
      setTimeout(type, speed);
    }
  }
  type();
}

// Show/Close Modal
function showSearchResults() {
  const overlay = document.getElementById('searchResultsOverlay');
  const modal = document.getElementById('searchResultsModal');
  
  overlay.classList.add('show');
  modal.classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeSearchResults() {
  const overlay = document.getElementById('searchResultsOverlay');
  const modal = document.getElementById('searchResultsModal');
  
  overlay.classList.remove('show');
  modal.classList.remove('show');
  document.body.style.overflow = '';
}

// XSS Protection
function escapeHtml(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

// Close on Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeSearchResults();
});
