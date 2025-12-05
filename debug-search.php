<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Search Modal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white p-8">
        <h1 class="text-3xl font-bold mb-4">🔍 Test Search Modal</h1>
        <div class="flex gap-4">
            <input 
                type="text" 
                id="searchInput" 
                placeholder="Ví dụ: React, PHP, Mobile..." 
                class="px-4 py-2 text-black rounded-lg flex-1"
                value="React"
            />
            <button 
                onclick="performSearch()" 
                class="bg-yellow-400 text-black px-6 py-2 rounded-lg font-bold hover:bg-yellow-300"
            >
                Search
            </button>
        </div>
        <p class="text-yellow-200 mt-4 text-sm">💡 Hãy nhập từ khóa và click Search để xem modal</p>
    </div>

    <!-- Debug: Hiển thị modal HTML -->
    <div id="debugContainer" class="p-4 bg-gray-100 m-4 rounded-lg" style="display: none;">
        <h3 class="font-bold mb-2">📋 Debug Info:</h3>
        <div id="debugOutput" class="bg-white p-2 rounded text-sm font-mono"></div>
    </div>

    <?php include __DIR__ . '/search-results.php'; ?>

    <script>
        // Debug functions
        function log(msg) {
            const debugDiv = document.getElementById('debugOutput');
            debugDiv.innerHTML += msg + '<br>';
            console.log(msg);
        }

        // Load search functions dynamically to see if there are errors
        const script = document.querySelector('script[src*="search-functions.js"]');
        if (script) {
            log(`✅ search-functions.js script tag found: ${script.src}`);
        } else {
            log('❌ search-functions.js script tag NOT FOUND');
        }

        const css = document.querySelector('link[href*="search-modal.css"]');
        if (css) {
            log(`✅ search-modal.css link found: ${css.href}`);
        } else {
            log('❌ search-modal.css link NOT FOUND');
        }

        // Check if functions exist
        setTimeout(() => {
            log(`performSearch function exists: ${typeof performSearch !== 'undefined' ? '✅' : '❌'}`);
            log(`showSearchResults function exists: ${typeof showSearchResults !== 'undefined' ? '✅' : '❌'}`);
            log(`Modal overlay exists: ${document.getElementById('searchResultsOverlay') ? '✅' : '❌'}`);
            log(`Modal container exists: ${document.getElementById('searchResultsModal') ? '✅' : '❌'}`);
            
            // Show debug info
            document.getElementById('debugContainer').style.display = 'block';
        }, 500);

        // Override performSearch to show debug
        const originalPerformSearch = window.performSearch;
        window.performSearch = function() {
            log('🚀 performSearch called!');
            if (originalPerformSearch) {
                return originalPerformSearch.apply(this, arguments);
            }
        };
    </script>
</body>
</html>
