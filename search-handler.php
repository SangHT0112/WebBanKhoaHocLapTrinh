<?php
// search-handler.php - Tìm kiếm khóa học dựa trên CSDL + AI gợi ý
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

// Shutdown handler
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR])) {
        http_response_code(500);
        echo json_encode(['error' => 'PHP Fatal Error: ' . $error['message']]);
        exit;
    }
});

try {
    // Kết nối DB
    require_once __DIR__ . '/db.php';
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('DB Connection failed: ' . ($conn->connect_error ?? 'Unknown error'));
    }

    require_once __DIR__ . '/config/api-key.php';
    if (!defined('GEMINI_API_KEYS') || empty(GEMINI_API_KEYS)) {
        throw new Exception('No valid API keys configured. Check config/api-key.php');
    }

    // Chỉ cho phép POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed (use POST)']);
        exit;
    }

    // Nhận input JSON
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    $searchQuery = trim($input['query'] ?? '');
    if (empty($searchQuery)) {
        throw new Exception('Missing search query');
    }

    // --- 1️⃣ Lấy tất cả khóa học từ CSDL ---
    $sql = "
        SELECT c.id, c.ten_khoa_hoc, c.mo_ta, c.gia, c.so_hoc_vien, c.so_gio_hoc,
               c.bieu_tuong, cat.ten_danh_muc, IFNULL(AVG(r.rating),0) AS avg_rating
        FROM courses c
        LEFT JOIN categories cat ON c.danh_muc_id = cat.id
        LEFT JOIN reviews r ON c.id = r.course_id
        GROUP BY c.id
    ";
    $result = $conn->query($sql);
    $courses = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    // --- 2️⃣ Lọc khóa học dựa trên query người dùng ---
    $searchLower = mb_strtolower($searchQuery, 'UTF-8');

    $keywords = [
        'mobile' => ['mobile', 'flutter', 'android', 'ios', 'di động'],
        'web' => ['web', 'html', 'css', 'php', 'react'],
        'backend' => ['php', 'laravel', 'mysql'],
        'frontend' => ['react', 'html', 'css', 'javascript']
    ];

    $filtered_courses = array_filter($courses, function($course) use ($searchLower, $keywords) {
        $fields = [
            mb_strtolower($course['ten_khoa_hoc'], 'UTF-8'),
            mb_strtolower($course['mo_ta'], 'UTF-8'),
            mb_strtolower($course['ten_danh_muc'], 'UTF-8')
        ];

        // So khớp trực tiếp
        foreach ($fields as $f) {
            if (mb_stripos($f, $searchLower) !== false) return true;
        }

        // So khớp mở rộng theo từ khóa
        foreach ($keywords as $key => $kws) {
            if (mb_stripos($searchLower, $key) !== false) {
                foreach ($fields as $f) {
                    foreach ($kws as $kw) {
                        if (mb_stripos($f, $kw) !== false) return true;
                    }
                }
            }
        }

        return false;
    });

    // --- 3️⃣ Sắp xếp theo số học viên + rating ---
    usort($filtered_courses, function($a, $b) {
        return ($b['so_hoc_vien'] + $b['avg_rating']) <=> ($a['so_hoc_vien'] + $a['avg_rating']);
    });

    // Giới hạn top 5
    $filtered_courses = array_slice($filtered_courses, 0, 5);

    // --- 4️⃣ Gọi AI để tạo reply hấp dẫn ---
    $systemPrompt = "Bạn là AI tìm kiếm khóa học. Trả lời bằng tiếng Việt, ngắn gọn, hấp dẫn, sử dụng emoji 💻📚📱. 
Gợi ý các khóa học dưới dạng bullet points với tên khóa học, giá, lý do nên học.
Nếu không có kết quả, gợi ý khóa phổ biến nhất.";

    // SỬA: Pass $searchQuery vào function để fallback dùng được
    function callGemini($prompt, $keys, $apiUrlBase, $searchQuery) {
        foreach ($keys as $apiKey) {
            $ch = curl_init();
            $body = [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['temperature'=>0.7,'maxOutputTokens'=>2000]
            ];
            curl_setopt_array($ch, [
                CURLOPT_URL => $apiUrlBase . '?key=' . $apiKey,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            $resp = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $decoded = json_decode($resp,true);
                if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($decoded['candidates'][0]['content']['parts'][0]['text']);
                }
            }
        }
        return "Chào bạn! Dưới đây là các khóa học phù hợp với '{$searchQuery}':"; // fallback - giờ có $searchQuery
    }

    $apiUrlBase = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    $replyPrompt = $systemPrompt . "\nQuery: " . $searchQuery . "\nDanh sách khóa học:\n" . json_encode($filtered_courses, JSON_UNESCAPED_UNICODE);
    $aiReply = callGemini($replyPrompt, GEMINI_API_KEYS, $apiUrlBase, $searchQuery);  // Pass $searchQuery

    // --- 5️⃣ Trả kết quả JSON ---
    echo json_encode([
        'query' => $searchQuery,
        'filtered_courses' => $filtered_courses,
        'reply' => $aiReply
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>