<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $user = $_POST['user'];
    $name = $_POST['name'] ?? 'Khách hàng';
    $email = $_POST['email'] ?? '';

    // 🔹 Cấu hình MOMO sandbox
    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
    $partnerCode = "MOMO";
    $accessKey = "F8BBA842ECF85";
    $secretKey = "K951B6PE1waDMi640xX08PD3vg6EkVlz";

    $orderId = time();
    $orderInfo = "Thanh toán khóa học cho user $user";
    $redirectUrl = "http://localhost/page/cart/checkout.php";
    $ipnUrl = "http://localhost/page/cart/checkout_ipn.php";
    $extraData = base64_encode(json_encode(["user" => $user, "email" => $email]));
    $requestId = time() . "";
    $requestType = "captureWallet";

    // 🔹 Tạo chữ ký
    $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    $data = [
        'partnerCode' => $partnerCode,
        'partnerName' => "KhoaHocOnline",
        'storeId' => "OnlineCourseStore",
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'lang' => 'vi',
        'extraData' => $extraData,
        'requestType' => $requestType,
        'signature' => $signature
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $result = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($result, true);

    if (isset($response['payUrl'])) {
        header("Location: " . $response['payUrl']);
        exit;
    } else {
        echo "<pre style='color:red'>Lỗi khi tạo thanh toán MOMO:\n";
        print_r($response);
        echo "</pre>";
        exit;
    }
}

// ===== GET DATA =====
$amount = $_GET['amount'] ?? 0;
$user = $_SESSION['username'] ?? 'Khách';
$emailSession = $_SESSION['email'] ?? '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thanh toán MOMO</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">

  <form method="POST" class="bg-white p-8 rounded-2xl shadow-md w-full max-w-md">
    <h1 class="text-2xl font-bold text-pink-600 mb-6 text-center">💰 Thanh toán MOMO</h1>

    <div class="mb-4">
      <label class="block text-gray-700 mb-1">Tên khách hàng</label>
      <input type="text" name="name"
             value="<?= htmlspecialchars($user) ?>"
             class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-pink-400" required>
    </div>

    <div class="mb-4">
      <label class="block text-gray-700 mb-1">Email</label>
      <input type="email" name="email"
             value="<?= htmlspecialchars($emailSession) ?>"
             class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-pink-400">
    </div>

    <div class="mb-6">
      <label class="block text-gray-700 mb-1">Số tiền thanh toán</label>
      <input type="text" value="<?= number_format($amount, 0, ',', '.') ?> ₫" disabled
             class="w-full border rounded-lg p-2 bg-gray-50 text-green-600 font-semibold">

      <input type="hidden" name="amount" value="<?= $amount ?>">
      <input type="hidden" name="user" value="<?= htmlspecialchars($user) ?>">
    </div>

    <button type="submit"
            class="w-full bg-pink-600 text-white py-3 rounded-full font-semibold hover:bg-pink-700 transition">
      🔒 Thanh toán bằng MOMO
    </button>

    <p class="mt-4 text-center text-sm text-gray-500">
      Bạn sẽ được chuyển đến cổng thanh toán MOMO để xác nhận.
    </p>
  </form>

</body>
</html>
