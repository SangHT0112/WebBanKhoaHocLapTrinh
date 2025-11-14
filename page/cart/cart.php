<?php
session_start();
include "../../db.php";

if (!isset($_SESSION['id'])) {
    header("Location: /page/login/login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Lấy danh sách khóa học trong giỏ
$sql = "
    SELECT c.id, cs.ten_khoa_hoc AS name, cs.gia AS price, c.quantity
    FROM carts c
    JOIN courses cs ON cs.id = c.course_id
    WHERE c.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $cart[] = $row;
    $total += $row['price'] * $row['quantity'];
}

// Cấu hình VietQR
$bank_id = "KLB";
$account_no = "101499100004323939";
$account_name = "KhoaHocOnline";
$template = "compact2";
$description = "Thanh toan khoa hoc user " . $user_id;

// Sinh URL mã VietQR động
$vietqr_url = "https://img.vietqr.io/image/{$bank_id}-{$account_no}-{$template}.png?amount={$total}&addInfo=" . urlencode($description) . "&accountName=" . urlencode($account_name);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Giỏ Hàng Khóa Học</title>
  <link rel="stylesheet" href="../../style.css">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

  <header>
    <?php include __DIR__ . '/../../layout/header.php'; ?>
  </header>

  <main class="flex-grow flex justify-center items-center">
    <section class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-3xl text-center">
      <h1 class="text-2xl font-bold mb-6 text-blue-600">🛒 Giỏ Hàng Khóa Học</h1>

      <?php if (count($cart) > 0): ?>
        <div class="overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-blue-100 text-gray-700">
                <th class="py-3 px-4 border">Tên khóa học</th>
                <th class="py-3 px-4 border">Giá</th>
                <th class="py-3 px-4 border">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cart as $index => $item): ?>
                <tr class="hover:bg-gray-50">
                  <td class="py-2 px-4 border"><?= htmlspecialchars($item['name']) ?></td>
                  <td class="py-2 px-4 border text-green-600 font-semibold"><?= number_format($item['price'], 0, ',', '.') ?> ₫</td>
                  <td class="py-2 px-4 border">
                    <a href="remove.php?index=<?= $index ?>" class="text-red-500 hover:text-red-700">❌ Xóa</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Tổng tiền -->
        <div class="flex justify-between items-center mt-6 border-t pt-4">
          <div class="text-lg font-semibold text-gray-700">Tổng cộng:</div>
          <div class="text-2xl font-bold text-green-600">
            <?= number_format($total, 0, ',', '.') ?> ₫
          </div>
        </div>

        <!-- Nút xác nhận thanh toán -->
        <div class="mt-6">
          <button id="btnThanhToan" class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 transition">
            ✅ Xác nhận thanh toán
          </button>
        </div>

        <!-- Chọn phương thức thanh toán -->
        <div id="chonPTTT" class="hidden mt-6 text-center">
          <p class="text-gray-700 mb-3 font-semibold">Chọn phương thức thanh toán:</p>
          <div class="flex justify-center gap-4 flex-wrap">
            <button id="btnVietQR" class="bg-red-500 text-white px-5 py-2 rounded-full hover:bg-red-600 transition">
              📱 Quét mã VietQR
            </button>
            <button id="btnMomo" class="bg-pink-500 text-white px-5 py-2 rounded-full hover:bg-pink-600 transition">
              💰 Thanh toán MOMO
            </button>
          </div>
        </div>

        <!-- QR Thanh toán VietQR -->
        <div id="vietqrSection" class="mt-6 text-center hidden">
          <p class="text-gray-700 mb-2">📱 Quét mã VietQR để thanh toán:</p>
          <img src="<?= $vietqr_url ?>" alt="VietQR Thanh toán" class="mx-auto w-64 rounded-lg shadow-md border">
          <p class="mt-2 text-sm text-gray-500">
            Ngân hàng: <b>Agribank</b><br>
            STK: <b>710 420 5318045</b><br>
            Tên TK: <b>KhoaHocOnline</b><br>
            Nội dung: <b><?= htmlspecialchars($description) ?></b>
          </p>
          <div class="mt-4">
            <a href="checkout.php" class="bg-green-600 text-white px-6 py-2 rounded-full hover:bg-green-700 transition">
              ✅ Tôi đã chuyển khoản xong
            </a>
          </div>
        </div>

      <?php else: ?>
        <p class="text-gray-600">
          Giỏ hàng trống. <a href="/page/category/category.php" class="text-blue-500 hover:underline">Quay lại chọn khóa học</a>
        </p>
      <?php endif; ?>
    </section>
  </main>

  <script>
    const btnThanhToan = document.getElementById("btnThanhToan");
    const chonPTTT = document.getElementById("chonPTTT");
    const vietqrSection = document.getElementById("vietqrSection");
    const btnVietQR = document.getElementById("btnVietQR");
    const btnMomo = document.getElementById("btnMomo");

    // Khi nhấn "Xác nhận thanh toán" - Kiểm tra thông tin user trước
    btnThanhToan?.addEventListener("click", async function() {
      try {
        const response = await fetch('check_user_info.php', {
          method: 'GET', // Hoặc POST nếu cần
          headers: {
            'Content-Type': 'application/json',
          }
        });
        const data = await response.json();

        if (data.status === 'complete') {
          // Thông tin đầy đủ, hiển thị phương thức thanh toán
          btnThanhToan.classList.add("hidden");
          chonPTTT.classList.remove("hidden");
        } else if (data.status === 'incomplete') {
          // Thông tin chưa đầy đủ, cảnh báo và redirect đến update
          Swal.fire({
            title: '⚠️ Thông tin chưa đầy đủ!',
            text: 'Vui lòng cập nhật đầy đủ họ tên, số điện thoại và địa chỉ trước khi thanh toán.',
            icon: 'warning',
            confirmButtonText: 'Cập nhật ngay',
            allowOutsideClick: false
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = '/page/profile/update.php?redirect=cart';
            }
          });
        } else {
          // Lỗi khác, ví dụ not_logged_in
          Swal.fire({
            title: 'Lỗi!',
            text: 'Vui lòng đăng nhập lại.',
            icon: 'error',
            confirmButtonText: 'OK'
          }).then(() => {
            window.location.href = '/page/login/login.php';
          });
        }
      } catch (error) {
        console.error('Lỗi kiểm tra thông tin:', error);
        Swal.fire({
          title: 'Lỗi kết nối!',
          text: 'Không thể kiểm tra thông tin. Vui lòng thử lại.',
          icon: 'error'
        });
      }
    });

    // Khi chọn VietQR
    btnVietQR?.addEventListener("click", function() {
      chonPTTT.classList.add("hidden");
      vietqrSection.classList.remove("hidden");
    });

    // Khi chọn MOMO
    btnMomo?.addEventListener("click", function() {
      window.location.href = "momo_payment.php?amount=<?= $total ?>&user=<?= $user_id ?>";
    });
  </script>
  <script src="http://localhost:3001/socket.io/socket.io.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    const userId = <?= $user_id ?>;
    const socket = io("http://localhost:3001");

    socket.on("connect", () => {
        socket.emit("register_user", userId);  // ĐÚNG EVENT NAME
        console.log("Connected as user:", userId);
    });

    // Lắng nghe sự kiện do realtime server phát ra khi thanh toán thành công
    // Server hiện emit event tên "payment_success" với payload { message }
    socket.on("payment_success", (data) => {
      Swal.fire({
          title: '🎉 Thanh toán thành công!',
          text: data.message || 'Đơn hàng đã được duyệt.',
          icon: 'success',        // success, error, warning, info, question
          confirmButtonText: 'OK'
      }).then(() => {
          // Refresh trang sau khi người dùng nhấn OK
          window.location.reload();
      });
    });
  </script>

</body>
</html>