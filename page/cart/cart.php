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
$bank_id = "agribank";
$account_no = "7104205318045";
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

  // Khi nhấn "Xác nhận thanh toán"
  btnThanhToan?.addEventListener("click", function() {
    btnThanhToan.classList.add("hidden");
    chonPTTT.classList.remove("hidden");
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


</body>
</html>
