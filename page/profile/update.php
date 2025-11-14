<?php
// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['id'])) {
    header('Location: /page/login/login.php');
    exit;
}

include "../../db.php"; // file chứa kết nối $conn (mysqli)

// Lấy thông tin user hiện tại
$id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    // Xử lý lỗi nếu không tìm thấy user
    header('Location: /page/login/login.php');
    exit;
}

// Xử lý form cập nhật
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validate cơ bản (có thể mở rộng)
    if (empty($fullname) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Vui lòng kiểm tra lại thông tin đầy đủ và hợp lệ.';
    } else {
        $avatar = $user['avatar'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../uploads/avatars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $new_filename = $id . '_' . time() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                    // Xóa avatar cũ nếu không phải default
                    if ($avatar !== 'uploads/avatars/default.png' && file_exists('../../' . $avatar)) {
                        unlink('../../' . $avatar);
                    }
                    $avatar = 'uploads/avatars/' . $new_filename;
                } else {
                    $error_message = 'Lỗi upload avatar.';
                }
            } else {
                $error_message = 'Chỉ hỗ trợ file ảnh JPG, PNG, GIF.';
            }
        }

        if (empty($error_message)) {
            $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ?, address = ?, avatar = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("sssssi", $fullname, $email, $phone, $address, $avatar, $id);
            if ($stmt->execute()) {
                // Cập nhật session
                $_SESSION['fullname'] = $fullname; // Nếu bạn lưu fullname vào session
                $_SESSION['avatar'] = $avatar;
                $user['fullname'] = $fullname;
                $user['email'] = $email;
                $user['phone'] = $phone;
                $user['address'] = $address;
                $user['avatar'] = $avatar;
                $success_message = 'Cập nhật thông tin thành công!';

                // Kiểm tra và redirect nếu từ cart/checkout
                if (isset($_GET['redirect']) && $_GET['redirect'] === 'cart') {
                    header('Location: /page/cart/cart.php'); // Thay bằng đường dẫn chính xác đến trang giỏ hàng (checkout)
                    exit;
                }
            } else {
                $error_message = 'Lỗi cập nhật thông tin.';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật thông tin - SangBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../style.css">
</head>
<body class="bg-gray-100">
    <!-- Include nav từ file riêng hoặc paste trực tiếp -->
    <?php include __DIR__ . '/../../layout/header.php'; ?>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Cập nhật thông tin</h2>
                <p class="mt-2 text-center text-sm text-gray-600">Cập nhật hồ sơ của bạn</p>
            </div>

            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($success_message) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="" method="POST" enctype="multipart/form-data">
                <!-- Avatar hiện tại -->
                <div class="text-center">
                    <img src="/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="mx-auto h-24 w-24 rounded-full object-cover border-2 border-gray-300">
                    <p class="mt-2 text-sm text-gray-500">Avatar hiện tại</p>
                </div>

                <!-- Fullname -->
                <div>
                    <label for="fullname" class="block text-sm font-medium text-gray-700">Họ và tên</label>
                    <input type="text" name="fullname" id="fullname" required
                           value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 focus:z-10 sm:text-sm">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" required
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 focus:z-10 sm:text-sm">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                    <input type="tel" name="phone" id="phone"
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 focus:z-10 sm:text-sm">
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Địa chỉ</label>
                    <input type="text" name="address" id="address"
                           value="<?= htmlspecialchars($user['address'] ?? '') ?>"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 focus:z-10 sm:text-sm">
                </div>

                <!-- Avatar upload -->
                <div>
                    <label for="avatar" class="block text-sm font-medium text-gray-700">Cập nhật avatar (tùy chọn)</label>
                    <input type="file" name="avatar" id="avatar" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                </div>

                <div>
                    <button type="submit" name="update" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        Cập nhật thông tin
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>