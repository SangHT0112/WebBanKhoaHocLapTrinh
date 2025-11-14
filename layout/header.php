<?php
// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="flex items-center justify-between max-w-6xl mx-auto px-8 py-4">
  <!-- Logo -->
  <a href="/index.php" class="logo flex items-center gap-2">
    <img src="/public/logo.png" class="h-10 w-auto hover:scale-105 transition-transform duration-200" alt="SangBook">
  </a>

  <!-- Links -->
  <ul class="flex gap-8 text-white font-medium">
    <li><a href="/index.php" class="hover:text-yellow-400">Trang Chủ</a></li>
    <li><a href="/category.php" class="hover:text-yellow-400">Sản phẩm</a></li>
    <li><a href="/page/cart/cart.php" class="hover:text-yellow-400">Giỏ hàng</a></li>
    <li><a href="/page/contact/contact.php" class="hover:text-yellow-400">Liên Hệ</a></li>
  </ul>

  <!-- Search + Auth -->
  <div class="flex items-center gap-4 ml-auto">
    <!-- Search -->
    <div class="flex items-center bg-white/20 rounded-full px-3 py-1">
      <input
        type="text"
        placeholder="Tìm kiếm lộ trình..."
        class="bg-transparent text-white placeholder-white/70 focus:outline-none px-2 w-40"
      />
      <button class="bg-yellow-400 text-gray-800 rounded-full w-8 h-8 flex items-center justify-center hover:bg-yellow-300">🔍</button>
    </div>

    <!-- Auth -->
    <?php if (isset($_SESSION['id'])): ?>
        <!-- Đã đăng nhập -->
        <div class="flex items-center gap-3 text-white relative group">
            
            <!-- Avatar -->
            <img src="/uploads/avatars/<?= $_SESSION['avatar'] ?? 'default.png' ?>"
                 class="w-10 h-10 rounded-full border-2 border-yellow-400 object-cover cursor-pointer">

            <!-- Username -->
            <span class="font-semibold cursor-pointer"><?= htmlspecialchars($_SESSION['username']) ?></span>

            <!-- Dropdown -->
            <div class="absolute hidden group-hover:flex flex-col top-12 right-0 bg-white text-black rounded-lg shadow-lg w-40 py-2">
                <a href="/page/profile/profile.php" class="px-4 py-2 hover:bg-gray-100">Trang cá nhân</a>
                <a href="/page/orders/orders.php" class="px-4 py-2 hover:bg-gray-100">Đơn hàng</a>
                <a href="/page/profile/update.php" class="px-4 py-2 hover:bg-gray-100">Cập nhật thông tin</a>
                <a href="/page/logout/logout.php" class="px-4 py-2 hover:bg-red-100 text-red-600">Đăng xuất</a>
            </div>
        </div>

    <?php else: ?>
        <!-- Chưa đăng nhập -->
        <div class="flex gap-2">
            <a href="/page/login/login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-full text-sm">Đăng nhập</a>
            <a href="/page/register/register.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-full text-sm">Đăng ký</a>
        </div>
    <?php endif; ?>
  </div>
</nav>
