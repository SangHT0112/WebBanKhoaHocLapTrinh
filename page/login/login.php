<?php
session_start();
include "../../db.php";
ob_start();

$errors = [];
$message = '';

if (isset($_POST['login'])) {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // --- Kiểm tra dữ liệu nhập ---
    if (empty($email)) {
        $errors['email'] = "Email không được để trống!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email không hợp lệ!";
    }

    if (empty($password)) {
        $errors['password'] = "Mật khẩu không được để trống!";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Mật khẩu phải có ít nhất 6 ký tự!";
    }

    // --- Nếu không có lỗi ---
    if (empty($errors)) {
        // ✅ Dùng prepare statement để tránh SQL injection
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Nếu bạn chưa mã hóa password, thì so sánh trực tiếp
            if ($password === $user['password']) {
                // Lưu session
                $_SESSION['id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['avatar'] = $user['avatar'];
                $_SESSION['role'] = $user['role'];

                // ✅ Kiểm tra quyền và chuyển hướng
                if ($user['role'] === 'admin') {
                    header("Location: /page/admin/index.php");
                } else {
                    header("Location: /index.php");
                }
                exit();
            } else {
                $message = "Mật khẩu không chính xác!";
            }
        } else {
            $message = "Tài khoản không tồn tại!";
        }

        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - SangBook</title>
    <link rel="stylesheet" href="LogSign.css">

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white shadow-lg rounded-2xl p-8 w-[400px]">
        <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">SangBook</h1>

        <?php if (!empty($message)): ?>
            <div class="text-red-500 text-center mb-3 font-semibold"><?= $message ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <input 
                    type="text" 
                    name="email" 
                    placeholder="Email"
                    value="<?= $_POST['email'] ?? '' ?>"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <p class="text-red-500 text-sm mt-1"><?= $errors['email'] ?? '' ?></p>
            </div>

            <div>
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Mật khẩu"
                    value="<?= $_POST['password'] ?? '' ?>"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <p class="text-red-500 text-sm mt-1"><?= $errors['password'] ?? '' ?></p>
            </div>

            <div class="text-right">
                <a href="forgot_password.php" class="text-sm text-blue-500 hover:underline">Quên mật khẩu?</a>
            </div>

            <div class="flex gap-3 mt-4">
                <button 
                    type="button"
                    onclick="window.location.href='/page/register/register.php'"
                    class="w-1/2 py-2 border border-blue-500 text-blue-500 rounded-lg hover:bg-blue-50 transition"
                >
                    Đăng ký
                </button>

                <button 
                    type="submit" 
                    name="login"
                    class="w-1/2 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition"
                >
                    Đăng nhập
                </button>
            </div>
        </form>
    </div>

</body>
</html>
