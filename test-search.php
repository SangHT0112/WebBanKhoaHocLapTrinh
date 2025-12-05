<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Search Modal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./search-modal.css">
</head>
<body>
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white p-4">
        <h1>Test Search Modal</h1>
        <input 
            type="text" 
            id="searchInput" 
            placeholder="Tìm kiếm..." 
            class="px-4 py-2 text-black rounded"
        />
        <button 
            onclick="performSearch()" 
            class="bg-yellow-400 text-black px-4 py-2 rounded ml-2"
        >
            Search
        </button>
    </div>

    <?php include __DIR__ . '/search-results.php'; ?>

    <script>
        // Debug log
        console.log('Page loaded');
        console.log('searchInput exists:', !!document.getElementById('searchInput'));
        console.log('searchResultsModal exists:', !!document.getElementById('searchResultsModal'));
    </script>
</body>
</html>
