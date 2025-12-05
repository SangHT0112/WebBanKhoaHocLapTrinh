<?php
/**
 * page/admin/pages/refunds.php - Quan ly yeu cau tra hang
 */

require_once __DIR__ . '/../../../db.php';

// Kiem tra admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header('Location: /page/login/login.php');
    exit;
}

$db = (new Database())->connect();

// Lay danh sach yeu cau tra hang
$sql = "SELECT rr.id, rr.order_id, rr.reason, rr.status, rr.created_at,
               o.tong_tien, o.fullname, o.email, u.username
        FROM refund_requests rr
        JOIN orders o ON o.id = rr.order_id
        JOIN users u ON u.id = o.user_id
        ORDER BY rr.created_at DESC";

$result = $db->query($sql);
$refunds = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="w-full">
  <h1 class="text-3xl font-bold mb-6">🔄 Quan ly tra hang</h1>

  <!-- QR Scanner Section -->
  <div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">📱 Quet ma QR (Scan thanh toan)</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-bold mb-2">Nhap hoac quet ma QR:</label>
        <input 
          type="text" 
          id="qrInput" 
          placeholder="REFUND|Order:123|User:1|Amount:1000000|Time:..." 
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
          onkeypress="if(event.key==='Enter') scanQRCode()">
        <button 
          onclick="scanQRCode()" 
          class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-bold">
          ✅ Quet QR
        </button>
      </div>
      <div id="qrResult" class="hidden">
        <div class="p-4 bg-green-50 border border-green-300 rounded-lg">
          <p class="text-sm font-bold text-green-800 mb-2">✅ QR da quet thanh cong!</p>
          <p id="qrOrderId" class="text-sm">Don hang: <strong>#123</strong></p>
          <p id="qrAmount" class="text-sm">So tien: <strong>1,000,000 VND</strong></p>
        </div>
      </div>
    </div>
  </div>

  <?php if (empty($refunds)): ?>
    <div class="text-center text-gray-500 py-12">
      <p class="text-lg">Khong co yeu cau tra hang nao</p>
    </div>
  <?php else: ?>
    <div class="grid gap-6">
      <?php foreach ($refunds as $refund): ?>
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 <?php 
          echo $refund['status'] === 'pending' ? 'border-yellow-400' : 
               ($refund['status'] === 'approved' ? 'border-green-400' : 'border-red-400');
        ?>">
          
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-xl font-bold">Don hang #<?= $refund['order_id'] ?></h3>
              <p class="text-gray-600 text-sm">Khach: <?= htmlspecialchars($refund['username']) ?></p>
              <p class="text-gray-600 text-sm">Email: <?= htmlspecialchars($refund['email']) ?></p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-bold <?php 
              echo $refund['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                   ($refund['status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
            ?>">
              <?= $refund['status'] === 'pending' ? 'Cho duyet' : 
                  ($refund['status'] === 'approved' ? 'Da duyet' : 'Tu choi') ?>
            </span>
          </div>

          <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
              <p class="text-gray-600 text-sm">So tien tra</p>
              <p class="text-2xl font-bold text-red-600"><?= number_format($refund['tong_tien'], 0, ',', '.') ?> VND</p>
            </div>
            <div>
              <p class="text-gray-600 text-sm">Ngay yeu cau</p>
              <p class="text-lg"><?= date('d/m/Y H:i', strtotime($refund['created_at'])) ?></p>
            </div>
            <div>
              <p class="text-gray-600 text-sm">Ly do tra hang</p>
              <p class="text-sm"><?= htmlspecialchars(substr($refund['reason'], 0, 50)) ?>...</p>
            </div>
          </div>

          <div class="bg-gray-50 p-4 rounded mb-4">
            <p class="text-sm font-bold mb-2">Ly do chi tiet:</p>
            <p class="text-sm text-gray-700"><?= htmlspecialchars($refund['reason']) ?></p>
          </div>

          <?php if ($refund['status'] === 'pending'): ?>
            <div class="flex gap-3">
              <button 
                class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-bold transition"
                onclick="approveRefund(<?= $refund['id'] ?>, <?= $refund['order_id'] ?>)">
                ✅ Duyet va tra tien
              </button>
              <button 
                class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-bold transition"
                onclick="rejectRefund(<?= $refund['id'] ?>)">
                ❌ Tu choi
              </button>
            </div>

            <!-- QR Code Section -->
            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
              <p class="text-sm font-bold mb-3">📱 Quet ma QR de tra tien:</p>
              <div class="text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=REFUND%7COrder:<?= $refund['order_id'] ?>%7CAmount:<?= intval($refund['tong_tien']) ?>" 
                     alt="QR Code" class="mx-auto border-2 border-gray-300 rounded p-2 bg-white">
                <p class="text-xs text-gray-600 mt-2">Quay ma nay tu on-banking hoac cac ung dung thanh toan</p>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
  function approveRefund(refundId, orderId) {
    if (!confirm('Ban chac chan muon duyet tra hang va xoa don hang nay khong?')) {
      return;
    }

    const formData = new FormData();
    formData.append('action', 'approve-refund');
    formData.append('refund_id', refundId);
    formData.append('order_id', orderId);

    fetch('/admin-refund-handler.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Da duyet va xoa don hang thanh cong. Email xac nhan da duoc gui.');
        location.reload();
      } else {
        alert('Loi: ' + (data.error || 'Khong ro loi'));
      }
    })
    .catch(error => alert('Loi: ' + error.message));
  }

  function rejectRefund(refundId) {
    const reason = prompt('Nhap ly do tu choi:');
    if (!reason) return;

    const formData = new FormData();
    formData.append('action', 'reject-refund');
    formData.append('refund_id', refundId);
    formData.append('reject_reason', reason);

    fetch('/admin-refund-handler.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Da tu choi yeu cau tra hang');
        location.reload();
      } else {
        alert('Loi: ' + (data.error || 'Khong ro loi'));
      }
    })
    .catch(error => alert('Loi: ' + error.message));
  }

  function scanQRCode() {
    const qrData = document.getElementById('qrInput').value.trim();
    
    if (!qrData) {
      alert('Vui long nhap hoac quet ma QR');
      return;
    }

    const formData = new FormData();
    formData.append('qr_data', qrData);

    fetch('/verify-qr-payment.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        document.getElementById('qrOrderId').innerHTML = 'Don hang: <strong>#' + data.order_id + '</strong>';
        document.getElementById('qrAmount').innerHTML = 'So tien: <strong>' + new Intl.NumberFormat('vi-VN').format(data.amount) + ' VND</strong>';
        document.getElementById('qrResult').classList.remove('hidden');
        document.getElementById('qrInput').value = '';
        
        setTimeout(() => {
          alert('Da cap nhat va xoa don hang thanh cong!');
          location.reload();
        }, 2000);
      } else {
        alert('Loi: ' + (data.error || 'Ma QR khong hop le'));
      }
    })
    .catch(error => alert('Loi: ' + error.message));
  }
</script>
