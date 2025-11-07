<?php
$page = $_GET['page'] ?? 'dashboard'; // nếu không có ?page thì mặc định dashboard
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <div class="flex h-screen">
    
    <!-- SIDEBAR -->
    <aside class="w-64 bg-gray-800 text-white flex flex-col">
      <div class="p-4 text-2xl font-bold text-center border-b border-gray-700">Admin Panel</div>
      <nav class="flex-1 p-3 space-y-2">
        <a href="?page=dashboard" class="block p-2 rounded hover:bg-gray-700 <?= $page=='dashboard'?'bg-gray-700':'' ?>">🏠 Tổng quan</a>
        <a href="?page=courses" class="block p-2 rounded hover:bg-gray-700 <?= $page=='courses'?'bg-gray-700':'' ?>">📚 Khóa học</a>
        <a href="?page=users" class="block p-2 rounded hover:bg-gray-700 <?= $page=='users'?'bg-gray-700':'' ?>">👥 Người dùng</a>
        <a href="?page=settings" class="block p-2 rounded hover:bg-gray-700 <?= $page=='settings'?'bg-gray-700':'' ?>">⚙️ Cài đặt</a>
       <a href="../../index.php" class="block p-2 rounded hover:bg-gray-700 <?= $page=='settings'?'bg-gray-700':'' ?>">🎓 Trang bán khóa học</a>

      </nav>
      <div class="p-3 border-t border-gray-700 text-center text-sm text-gray-400">© 2025 Admin</div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 overflow-y-auto p-6">
      <?php
        $file = __DIR__ . "/pages/{$page}.php";
        if (file_exists($file)) {
          include $file;
        } else {
          echo "<div class='text-center text-red-500 text-lg'>❌ Trang không tồn tại</div>";
        }
      ?>
    </main>
  </div>
</body>
</html>
