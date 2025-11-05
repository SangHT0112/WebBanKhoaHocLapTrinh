<?php
session_start();
include "../../db.php";
ob_start();

$errors = [];
$message = '';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $avatar = $_FILES['avatar'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra username
    if (empty($username)) {
        $errors['username'] = "Username không được để trống!";
    } elseif (strlen($username) < 5) {
        $errors['username'] = "Username phải có ít nhất 5 ký tự!";
    }

    // Kiểm tra email
    if (empty($email)) {
        $errors['email'] = "Email không được để trống!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email không hợp lệ!";
    } else {
        $check_email = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $check_email);
        if ($result->num_rows > 0) {
            $errors['email'] = "Email đã tồn tại, hãy dùng email khác!";
        }
    }

    // Kiểm tra avatar
    if ($avatar['error'] === 0) {
        $avatar_name = $avatar['name'];
        $avatar_tmp = $avatar['tmp_name'];
        $ext = strtolower(pathinfo($avatar_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            $errors['avatar'] = "Chỉ chấp nhận ảnh JPG, PNG, GIF!";
        } else {
            $new_name = uniqid('avatar_', true) . '.' . $ext;
            $avatar_dest = __DIR__ . '/../uploads/avatars/' . $new_name;
            if (move_uploaded_file($avatar_tmp, $avatar_dest)) {
                $avatar_path = 'uploads/avatars/' . $new_name;
            } else {
                $errors['avatar'] = "Lỗi khi tải ảnh lên!";
            }
        }
    }

    // Kiểm tra mật khẩu
    if (empty($password)) {
        $errors['password'] = "Mật khẩu không được để trống!";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Mật khẩu phải có ít nhất 6 ký tự!";
    }

    // Kiểm tra xác nhận mật khẩu
    if ($confirm_password !== $password) {
        $errors['confirm_password'] = "Mật khẩu xác nhận không khớp!";
    }

    // Nếu không có lỗi, thêm user vào DB
    if (empty($errors)) {
        $sql = "INSERT INTO users(username, email, password, avatar) 
                VALUES ('$username', '$email', '$password', '$avatar_path')";
        if (mysqli_query($conn, $sql)) {
            $message = "🎉 Đăng ký tài khoản thành công!";
        } else {
            $message = "Đăng ký không thành công!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - SangBook</title>
    <link rel="stylesheet" href="LogSign.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white shadow-lg rounded-2xl p-8 w-[430px]">
        <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">SangBook</h1>

        <?php if (!empty($message)): ?>
            <div class="text-green-600 text-center mb-3 font-semibold"><?= $message ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" enctype="multipart/form-data" class="space-y-4">

            <div>
                <input 
                    type="text" 
                    name="username" 
                    placeholder="Username"
                    value="<?= $_POST['username'] ?? '' ?>"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <p class="text-red-500 text-sm mt-1"><?= $errors['username'] ?? '' ?></p>
            </div>

            <div>
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Email"
                    value="<?= $_POST['email'] ?? '' ?>"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <p class="text-red-500 text-sm mt-1"><?= $errors['email'] ?? '' ?></p>
            </div>

            <div>
                <input 
                    type="file" 
                    name="avatar" 
                    accept="image/*"
                    class="w-full px-2 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <p class="text-red-500 text-sm mt-1"><?= $errors['avatar'] ?? '' ?></p>
            </div>

            <div>
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Mật khẩu"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <p class="text-red-500 text-sm mt-1"><?= $errors['password'] ?? '' ?></p>
            </div>

            <div>
                <input 
                    type="password" 
                    name="confirm_password" 
                    placeholder="Xác nhận mật khẩu"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <p class="text-red-500 text-sm mt-1"><?= $errors['confirm_password'] ?? '' ?></p>
            </div>

            <div class="flex gap-3 mt-4">
                <button 
                    type="button"
                    onclick="window.location.href='/page/login/login.php'"
                    class="w-1/2 py-2 border border-blue-500 text-blue-500 rounded-lg hover:bg-blue-50 transition"
                >
                    Đăng nhập
                </button>

                <button 
                    type="submit" 
                    name="register"
                    class="w-1/2 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition"
                >
                    Đăng ký
                </button>
            </div>

        </form>
    </div>

</body>
</html>
