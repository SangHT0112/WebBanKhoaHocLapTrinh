<?php
require_once __DIR__ . '/db.php';

// Lấy danh sách bảng trong database
$tables = [];
$result = $conn->query("SHOW TABLES");

if ($result) {
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
}

$schemaInfo = "";

foreach ($tables as $table) {
    $schemaInfo .= "Bảng $table:\n";

    $columns = $conn->query("DESCRIBE `$table`");
    while ($col = $columns->fetch_assoc()) {
        $schemaInfo .= "- {$col['Field']} ({$col['Type']})\n";
    }

    $schemaInfo .= "\n";
}


// Bật error reporting (xóa khi production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

// Bắt lỗi fatal để luôn trả JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode(['error' => 'PHP Fatal Error: ' . $error['message']]);
    }
});

require_once __DIR__ . '/config/api-key.php';

if (!defined('GEMINI_API_KEYS') || empty(GEMINI_API_KEYS)) {
    echo json_encode(['error' => 'No valid API keys configured. Check config/api-key.php']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed (use POST)']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');

if (empty($userMessage)) {
    echo json_encode(['error' => 'No message provided']);
    exit;
}

// Prompt hệ thống cho AI phù hợp với site
$systemPrompt = "
Bạn là AI chuyên truy vấn database MySQL cho hệ thống webbankhoahoc.

Cấu trúc database:
$schemaInfo

Nhiệm vụ:
- Khi người dùng hỏi dữ liệu -> chuyển thành SQL SELECT
- Chỉ tạo câu SQL hợp lệ MySQL
- Không giải thích, không markdown
";

$prompt = $systemPrompt . "\n\nNgười dùng: " . $userMessage . "\nAI:";

// Body request cho Gemini API
$body = [
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 2000
    ]
];

$bodyJson = json_encode($body);

// Helper function failover (tương tự code JS của bạn)
function fetchWithFailover($keys, $apiUrlBase, $bodyJson) {
    $keyIndex = 1;
    foreach ($keys as $apiKey) {
        $apiUrl = $apiUrlBase . '?key=' . $apiKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("⚠️ cURL Error with API key {$keyIndex}: {$curlError}");
            $keyIndex++;
            continue;
        }

        if ($httpCode === 200) {
            error_log("✅ API call succeeded with key {$keyIndex}");
            return json_decode($response, true);
        } else {
            $errorText = substr($response, 0, 200);  // Giống JS: await response.text()
            error_log("⚠️ API key {$keyIndex} failed with status {$httpCode}: {$errorText}");
            $keyIndex++;
            continue;
        }
    }
    throw new Exception("Tất cả các khóa API Gemini đều thất bại hoặc đã hết hạn");
}
function extractGeminiText($apiResponse) {

    // 1. Kiểm tra cấu trúc CANDIDATE
    if (!isset($apiResponse['candidates'][0])) {
        return null;
    }

    $candidate = $apiResponse['candidates'][0];

    // 2. Nếu có parts → lấy text chuẩn
    if (isset($candidate['content']['parts'])) {
        foreach ($candidate['content']['parts'] as $part) {
            if (isset($part['text']) && trim($part['text']) !== '') {
                return $part['text'];
            }
        }
    }

    // 3. Nếu trả về kiểu thô → fallback
    if (isset($candidate['content']) && is_string($candidate['content'])) {
        return $candidate['content'];
    }

    // 4. Nếu không có gì
    return null;
}


// Gọi API với failover
$apiUrlBase = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
try {
    $apiResponse = fetchWithFailover(GEMINI_API_KEYS, $apiUrlBase, $bodyJson);

    if (!isset($apiResponse['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception('Invalid API response structure: ' . json_encode($apiResponse));
    }

   $aiReply = extractGeminiText($apiResponse);

        if ($aiReply === null) {
            throw new Exception('Gemini không trả về nội dung text hợp lệ: ' . json_encode($apiResponse));
        }

        echo json_encode(['reply' => trim($aiReply)]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>