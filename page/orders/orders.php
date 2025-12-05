<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../controllers/OrderController.php';


// Kiểm tra đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header('Location: /page/login/login.php');
    exit;
}

$user_id = $_SESSION['id'];
$db = (new Database())->connect();
$controller = new OrderController($db);

// Lấy danh sách đơn hàng của user
$ordersResult = $controller->getUserOrders($user_id);
$orders = $ordersResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include __DIR__ . '/../../layout/head.php'; ?>
  <link rel="stylesheet" href="orders.css">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 20px 0;
    }

    .orders-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .page-header {
      background: white;
      padding: 30px;
      border-radius: 12px;
      margin-bottom: 30px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .page-header h1 {
      font-size: 32px;
      font-weight: bold;
      color: #1f2937;
      margin: 0 0 10px 0;
    }

    .page-header p {
      color: #6b7280;
      margin: 0;
    }

    .orders-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
    }

    .order-card {
      background: white;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .order-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
    }

    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 20px;
      border-bottom: 2px solid #e5e7eb;
    }

    .order-id {
      font-size: 14px;
      color: #6b7280;
    }

    .order-id strong {
      color: #1f2937;
      font-size: 18px;
    }

    .order-status {
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 13px;
      text-transform: uppercase;
    }

    .status-pending {
      background: #fef3c7;
      color: #92400e;
    }

    .status-approved {
      background: #d1fae5;
      color: #065f46;
    }

    .status-cancelled {
      background: #fee2e2;
      color: #991b1b;
    }

    .order-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 20px;
    }

    .info-item {
      padding: 12px;
      background: #f9fafb;
      border-radius: 8px;
    }

    .info-label {
      font-size: 12px;
      text-transform: uppercase;
      color: #9ca3af;
      margin-bottom: 4px;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .info-value {
      font-size: 16px;
      color: #1f2937;
      font-weight: 500;
    }

    .order-items {
      margin-bottom: 20px;
    }

    .items-title {
      font-size: 14px;
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .course-item {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 12px;
      background: #f9fafb;
      border-radius: 8px;
      margin-bottom: 8px;
    }

    .course-icon {
      font-size: 32px;
      min-width: 40px;
      text-align: center;
    }

    .course-info {
      flex: 1;
    }

    .course-name {
      font-size: 16px;
      font-weight: 600;
      color: #1f2937;
      margin: 0 0 4px 0;
    }

    .course-meta {
      display: flex;
      gap: 16px;
      font-size: 12px;
      color: #6b7280;
    }

    .course-price {
      text-align: right;
      font-size: 16px;
      font-weight: 600;
      color: #667eea;
    }

    .order-total {
      display: flex;
      justify-content: flex-end;
      padding-top: 20px;
      border-top: 2px solid #e5e7eb;
      font-size: 20px;
      font-weight: bold;
      color: #1f2937;
    }

    .order-total span {
      color: #667eea;
      margin-left: 8px;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: 12px;
    }

    .empty-emoji {
      font-size: 80px;
      margin-bottom: 20px;
    }

    .empty-text {
      font-size: 24px;
      color: #1f2937;
      margin-bottom: 10px;
      font-weight: bold;
    }

    .empty-hint {
      font-size: 16px;
      color: #6b7280;
      margin-bottom: 30px;
    }

    .empty-button {
      display: inline-block;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 12px 32px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: transform 0.2s;
    }

    .empty-button:hover {
      transform: scale(1.05);
    }

    .refund-btn {
      display: block;
      width: 100%;
      margin-top: 16px;
      padding: 12px;
      background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .refund-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
    }

    /* Modal Refund */
    #refundOverlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }

    #refundOverlay.show {
      display: flex;
    }

    .refund-modal {
      background: white;
      border-radius: 12px;
      padding: 30px;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
      from {
        transform: translateY(-50px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .refund-modal h2 {
      font-size: 24px;
      color: #1f2937;
      margin: 0 0 20px 0;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      font-weight: 600;
      color: #374151;
      margin-bottom: 8px;
    }

    .form-group textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-family: Arial, sans-serif;
      resize: vertical;
      min-height: 100px;
      font-size: 14px;
    }

    .form-group textarea:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .modal-actions {
      display: flex;
      gap: 12px;
      margin-top: 24px;
    }

    .modal-actions button {
      flex: 1;
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-submit {
      background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
      color: white;
    }

    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
    }

    .btn-cancel {
      background: #e5e7eb;
      color: #374151;
    }

    .btn-cancel:hover {
      background: #d1d5db;
    }

    .loading {
      display: none;
      text-align: center;
      padding: 20px;
    }

    .spinner {
      border: 4px solid #f3f4f6;
      border-top: 4px solid #667eea;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 0 auto 10px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
      .orders-container {
        padding: 10px;
      }

      .page-header {
        padding: 20px;
      }

      .page-header h1 {
        font-size: 24px;
      }

      .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
      }

      .course-item {
        flex-wrap: wrap;
      }

      .course-price {
        text-align: left;
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <header>
    <?php include __DIR__ . '/../../layout/header.php'; ?>
  </header>

  <main class="orders-container">
    <div class="page-header">
      <h1>📚 Khóa học đã đăng ký</h1>
      <p>Quản lý và xem danh sách tất cả khóa học mà bạn đã mua</p>
    </div>

    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <div class="empty-emoji">🛒</div>
        <div class="empty-text">Chưa có khóa học nào</div>
        <p class="empty-hint">Bạn chưa mua bất kỳ khóa học nào. Hãy khám phá các khóa học tuyệt vời của chúng tôi!</p>
        <a href="/category.php" class="empty-button">Xem danh mục khóa học</a>
      </div>
    <?php else: ?>
      <div class="orders-grid">
        <?php foreach ($orders as $order): ?>
          <?php
            $orderDetails = $controller->getOrderDetails($order['id']);
            $items = $orderDetails->fetch_all(MYSQLI_ASSOC);
            $statusClass = 'status-pending';
            $statusText = '⏳ Chờ duyệt';
            
            if ($order['trang_thai'] === 'đã duyệt') {
              $statusClass = 'status-approved';
              $statusText = '✅ Đã duyệt';
            } elseif ($order['trang_thai'] === 'đã hủy') {
              $statusClass = 'status-cancelled';
              $statusText = '❌ Đã hủy';
            }
          ?>
          <div class="order-card">
            <div class="order-header">
              <div>
                <div class="order-id">Mã đơn hàng: <strong>#<?= $order['id'] ?></strong></div>
              </div>
              <span class="order-status <?= $statusClass ?>"><?= $statusText ?></span>
            </div>

            <div class="order-info">
              <div class="info-item">
                <div class="info-label">Người nhận</div>
                <div class="info-value"><?= htmlspecialchars($order['fullname']) ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value"><?= htmlspecialchars($order['email']) ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Điện thoại</div>
                <div class="info-value"><?= htmlspecialchars($order['phone']) ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Ngày mua</div>
                <div class="info-value"><?= date('d/m/Y H:i', strtotime($order['ngay_tao'])) ?></div>
              </div>
            </div>

            <div class="order-items">
              <div class="items-title">📖 Khóa học</div>
              <?php foreach ($items as $item): ?>
                <div class="course-item">
                  <div class="course-icon"><?= $item['bieu_tuong'] ?></div>
                  <div class="course-info">
                    <p class="course-name"><?= htmlspecialchars($item['ten_khoa_hoc']) ?></p>
                    <div class="course-meta">
                      <span>⏱️ <?= $item['so_gio_hoc'] ?> giờ</span>
                      <span>📦 Số lượng: <?= $item['so_luong'] ?></span>
                    </div>
                  </div>
                  <div class="course-price"><?= number_format($item['don_gia'], 0, ',', '.') ?> VNĐ</div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="order-total">
              Tổng cộng: <span><?= number_format($order['tong_tien'], 0, ',', '.') ?> VNĐ</span>
            </div>

            <?php if ($order['trang_thai'] !== 'đã hủy'): ?>
              <button class="refund-btn" onclick="openRefundModal(<?= $order['id'] ?>)">
                🔄 Yêu cầu trả hàng
              </button>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <footer>
    <?php include __DIR__ . '/../../layout/footer.php'; ?>
  </footer>

  <!-- Refund Modal -->
  <div id="refundOverlay" class="refund-overlay">
    <div class="refund-modal">
      <h2>🔄 Yêu cầu trả hàng</h2>
      <form id="refundForm" onsubmit="submitRefund(event)">
        <div class="form-group">
          <label for="refundReason">Lý do trả hàng:</label>
          <textarea 
            id="refundReason" 
            name="reason" 
            placeholder="Ví dụ: Khóa học không phù hợp với nhu cầu, hoặc khác..." 
            required></textarea>
        </div>
        <div class="loading" id="refundLoading">
          <div class="spinner"></div>
          <p>Đang xử lý...</p>
        </div>
        <div id="refundMessage" style="display: none; padding: 12px; margin-bottom: 12px; border-radius: 8px;"></div>
        <div class="modal-actions">
          <button type="submit" class="modal-actions button btn-submit" id="refundSubmitBtn">Gửi yêu cầu</button>
          <button type="button" class="modal-actions button btn-cancel" onclick="closeRefundModal()">Hủy</button>
        </div>
      </form>
    </div>
  </div>

  <?php include __DIR__ . '/../../search-results.php'; ?>

  <script>
    function openRefundModal(orderId) {
      document.getElementById('refundOverlay').classList.add('show');
      document.getElementById('refundForm').dataset.orderId = orderId;
      document.getElementById('refundForm').reset();
      document.getElementById('refundLoading').style.display = 'none';
      document.getElementById('refundMessage').style.display = 'none';
    }

    function closeRefundModal() {
      document.getElementById('refundOverlay').classList.remove('show');
    }

    function submitRefund(event) {
      event.preventDefault();
      
      const orderId = document.getElementById('refundForm').dataset.orderId;
      const reason = document.getElementById('refundReason').value.trim();
      const form = document.getElementById('refundForm');
      const loadingDiv = document.getElementById('refundLoading');
      const messageDiv = document.getElementById('refundMessage');
      const submitBtn = document.getElementById('refundSubmitBtn');

      if (!reason) {
        messageDiv.style.display = 'block';
        messageDiv.style.background = '#fee2e2';
        messageDiv.style.color = '#991b1b';
        messageDiv.textContent = 'Vui lòng nhập lý do trả hàng';
        return;
      }

      loadingDiv.style.display = 'block';
      submitBtn.disabled = true;
      messageDiv.style.display = 'none';

      const formData = new FormData();
      formData.append('order_id', orderId);
      formData.append('reason', reason);

      fetch('/process-refund.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        loadingDiv.style.display = 'none';
        submitBtn.disabled = false;

        if (data.success) {
          messageDiv.style.display = 'block';
          messageDiv.style.background = '#d1fae5';
          messageDiv.style.color = '#065f46';
          messageDiv.innerHTML = '<strong>✅ Thành công!</strong><br>' + data.message;
          
          setTimeout(() => {
            location.reload();
          }, 2000);
        } else {
          messageDiv.style.display = 'block';
          messageDiv.style.background = '#fee2e2';
          messageDiv.style.color = '#991b1b';
          messageDiv.textContent = 'Lỗi: ' + (data.error || 'Không rõ lỗi');
        }
      })
      .catch(error => {
        loadingDiv.style.display = 'none';
        submitBtn.disabled = false;
        messageDiv.style.display = 'block';
        messageDiv.style.background = '#fee2e2';
        messageDiv.style.color = '#991b1b';
        messageDiv.textContent = 'Lỗi: ' + error.message;
      });
    }

    // Close modal khi click overlay
    document.getElementById('refundOverlay').addEventListener('click', function(e) {
      if (e.target === this) {
        closeRefundModal();
      }
    });
  </script>
</body>
</html>
