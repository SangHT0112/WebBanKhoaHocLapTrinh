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
    <li><a href="/page/orders/orders.php" class="hover:text-yellow-400">Đơn hàng</a></li>
    <li><a href="/page/contact/contact.php" class="hover:text-yellow-400">Liên Hệ</a></li>
  </ul>

  <!-- Search + Auth -->
  <div class="flex items-center gap-4 ml-auto">
    <!-- Search -->
    <div class="flex items-center bg-white/20 rounded-full px-3 py-1">
      <input
        type="text"
        id="searchInput"
        placeholder="Tìm kiếm lộ trình..."
        class="bg-transparent text-white placeholder-white/70 focus:outline-none px-2 w-40"
        onkeypress="if(event.key==='Enter') performSearch()"
      />
      <button class="bg-yellow-400 text-gray-800 rounded-full w-8 h-8 flex items-center justify-center hover:bg-yellow-300" onclick="performSearch()">🔍</button>
    </div>

    <!-- Auth -->
    <?php if (isset($_SESSION['id'])): ?>
        <!-- Đã đăng nhập -->
        <div class="flex items-center gap-3 text-white relative" id="user-dropdown">
            
            <!-- Avatar -->
            <img src="/uploads/avatars/<?= $_SESSION['avatar'] ?? 'default.png' ?>"
                 class="w-10 h-10 rounded-full border-2 border-yellow-400 object-cover cursor-pointer" id="dropdown-toggle">

            <!-- Username -->
            <span class="font-semibold cursor-pointer" id="dropdown-toggle"><?= htmlspecialchars($_SESSION['username']) ?></span>
<button
  class="fixed bottom-5 right-28 bg-gradient-to-r from-pink-500 to-rose-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-2xl z-[1000] hover:scale-110 transition-all"
  onclick="openCatalogue()"
  title="Hướng dẫn sử dụng">
  <span class="text-2xl font-bold">?</span>
</button>

            
            <!-- Dropdown -->
            <div class="absolute hidden flex-col top-12 right-0 bg-white text-black rounded-lg shadow-lg w-40 py-2" id="dropdown-menu">
                <a href="/page/profile/profile.php" class="px-4 py-2 hover:bg-gray-100">Trang cá nhân</a>
                <a href="/page/orders/orders.php" class="px-4 py-2 hover:bg-gray-100">Đơn hàng</a>
                <a href="/page/profile/update.php" class="px-4 py-2 hover:bg-gray-100">Cập nhật thông tin</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                  <hr class="my-1">
                  <a href="/page/admin/index.php?page=refunds" class="px-4 py-2 hover:bg-blue-100 text-blue-600 font-bold">⚙️ Admin - Trả hàng</a>
                <?php endif; ?>
                <a href="/page/logout/logout.php" class="px-4 py-2 hover:bg-red-100 text-red-600">Đăng xuất</a>
            </div>
        </div>

        <script>
            const toggle = document.querySelectorAll('#dropdown-toggle');
            const menu = document.getElementById('dropdown-menu');

            toggle.forEach(el => {
                el.addEventListener('click', () => {
                    menu.classList.toggle('hidden');
                });
            });

            // Click ra ngoài thì đóng dropdown
            document.addEventListener('click', (e) => {
                const dropdown = document.getElementById('user-dropdown');
                if (!dropdown.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            });
        </script>

    <?php else: ?>
        <!-- Chưa đăng nhập -->
        <div class="flex gap-2">
            <a href="/page/login/login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-full text-sm">Đăng nhập</a>
            <a href="/page/register/register.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-full text-sm">Đăng ký</a>
        </div>
    <?php endif; ?>
  </div>
</nav>
