<?php
// Bắt đầu session (nếu chưa có)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
$db = (new Database())->connect();

$courseId = isset($_GET['id']) ? intval($_GET['id']) : 0;

/* ==================== XỬ LÝ GỬI ĐÁNH GIÁ ==================== */
$review_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten     = trim($_POST['ten'] ?? '');
    $rating  = max(1, min(5, intval($_POST['so_sao'] ?? 5)));
    $comment = trim($_POST['noi_dung'] ?? '');

    if ($courseId > 0 && $ten !== '' && $comment !== '') {
        $id     = null;
        $userId = $_SESSION['id']; 

        $stmt = $db->prepare("
            INSERT INTO reviews 
                (id, course_id, user_id, rating, comment, ngay_tao) 
            VALUES 
                (?,  ?,         ?,       ?,      ?,       NOW())
        ");
        $stmt->bind_param("iiiis", $id, $courseId, $userId, $rating, $comment);
        
        if ($stmt->execute()) {
            $review_success = true;
            // Lưu thông báo thành công vào session
            $_SESSION['review_success'] = true;
        }
    }
}

/* ==================== LẤY ĐÁNH GIÁ ===*/
$reviews = [];
$avgRating = 0;
$totalReviews = 0;

if ($courseId > 0) {
    $stmt = $db->prepare("SELECT * FROM reviews WHERE course_id = ? ORDER BY ngay_tao DESC");
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }

    $totalReviews = count($reviews);
    if ($totalReviews > 0) {
        $sum = array_sum(array_column($reviews, 'rating'));
        $avgRating = round($sum / $totalReviews, 1);
    }
}
?>

<!-- THÔNG BÁO THÀNH CÔNG (hiển thị 1 lần rồi xóa) -->
<?php if (isset($_SESSION['review_success'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Thành công!',
            text: 'Đánh giá của bạn đã được gửi thành công. Cảm ơn bạn!',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
    <?php unset($_SESSION['review_success']); ?>
<?php endif; ?>

<!-- ==================== GIAO DIỆN ĐÁNH GIÁ ==================== -->
<section class="reviews-section" id="reviews">
    <div class="reviews-container">
        <h2 class="section-title">
            Đánh Giá Từ Học Viên
            <?php if ($totalReviews > 0): ?>
                <span class="rating-summary">
                    <?= $avgRating ?> stars (<?= $totalReviews ?> đánh giá)
                </span>
            <?php endif; ?>
        </h2>

        <!-- Form gửi đánh giá -->
        <div class="review-form-card">
            <h3>Để lại đánh giá của bạn</h3>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="ten" placeholder="Họ và tên" required maxlength="50">
                </div>

                <div class="form-group">
                    <label>Chọn số sao:</label>
                    <select name="so_sao" required>
                        <option value="5">5 Sao – Tuyệt vời!</option>
                        <option value="4">4 Sao – Rất tốt</option>
                        <option value="3">3 Sao – Tạm ổn</option>
                        <option value="2">2 Sao – Cần cải thiện</option>
                        <option value="1">1 Sao – Không hài lòng</option>
                    </select>
                </div>

                <div class="form-group">
                    <textarea name="noi_dung" rows="4" placeholder="Chia sẻ cảm nhận của bạn..." required></textarea>
                </div>

                <button type="submit" class="submit-review-btn">Gửi đánh giá</button>
            </form>
        </div>

        <!-- Danh sách đánh giá -->
        <div class="reviews-list">
            <?php if ($totalReviews > 0): ?>
                <?php foreach ($reviews as $r): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-avatar">
                                <?= strtoupper(mb_substr($r['ten_nguoi_danh_gia'] ?? 'KH', 0, 2, 'UTF-8')) ?>
                            </div>
                            <div class="reviewer-info">
                                <h4><?= htmlspecialchars($_SESSION['username'] ?? 'Khách') ?></h4>
                               <div class="stars">
    <?php
        // Hiển thị số lượng ngôi sao tương ứng với rating
        $rating = intval($r['rating']); // chắc chắn là số nguyên
        echo str_repeat('⭐', $rating);
    ?>
    <span class="rating-text"><?= htmlspecialchars($r['rating']) ?>.0</span>
</div>

                            </div>
                        </div>
                        <p class="review-content"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                        <small class="review-date"><?= date('d/m/Y H:i', strtotime($r['ngay_tao'])) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-reviews">
                    <p>Chưa có đánh giá nào cho khóa học này.</p>
                    <p>Hãy là người đầu tiên chia sẻ cảm nhận của bạn!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
    /* CSS đẹp như cũ – giữ nguyên */
    .reviews-section { max-width:900px; margin:50px auto; padding:0 20px; }
    .section-title { text-align:center; font-size:26px; color:#333; margin-bottom:30px; }
    .rating-summary { background:#fff3cd; color:#856404; padding:6px 14px; border-radius:30px; font-weight:bold; margin-left:10px; }
    .review-form-card { background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,.1); margin-bottom:40px; }
    .review-form-card h3 { margin:0 0 20px; color:#2575fc; font-size:21px; }
    .form-group { margin-bottom:18px; }
    .form-group input, .form-group textarea, .form-group select { width:100%; padding:12px 15px; border:1px solid #ddd; border-radius:8px; font-size:15px; }
    .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline:none; border-color:#6a11cb; box-shadow:0 0 0 3px rgba(106,17,203,.15); }
    .submit-review-btn { background:linear-gradient(135deg,#6a11cb,#2575fc); color:#fff; border:none; padding:13px 32px; border-radius:8px; font-size:16px; cursor:pointer; }
    .submit-review-btn:hover { background:linear-gradient(135deg,#2575fc,#6a11cb); transform:translateY(-2px); }
    .reviews-list { display:grid; gap:20px; }
    .review-card { background:#fff; padding:22px; border-radius:12px; box-shadow:0 3px 12px rgba(0,0,0,.08); border-left:5px solid #6a11cb; }
    .review-header { display:flex; align-items:center; gap:15px; margin-bottom:12px; }
    .reviewer-avatar { width:50px; height:50px; background:linear-gradient(135deg,#6a11cb,#2575fc); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:18px; }
    .stars { color:#ffb400; font-size:19px; }
    .rating-text { color:#666; margin-left:6px; }
    .review-content { line-height:1.7; color:#444; margin:12px 0; }
    .review-date { color:#999; font-size:13px; }
    .no-reviews { text-align:center; padding:50px; color:#777; }
    @media (max-width:768px) { .review-header { flex-direction:column; text-align:center; } }
</style>