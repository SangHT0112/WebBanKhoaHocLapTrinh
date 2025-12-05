<?php
session_start();
// Bật error reporting (xóa khi production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR])) {
        http_response_code(500);
        echo json_encode(['error' => 'PHP Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ' line ' . $error['line']]);
        exit;
    }
});

// Require DB
try {
    require_once __DIR__ . '/db.php';
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('DB Connection failed: ' . ($conn->connect_error ?? 'Unknown error'));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

require_once __DIR__ . '/config/api-key.php';

if (!defined('GEMINI_API_KEYS') || empty(GEMINI_API_KEYS)) {
    echo json_encode(['error' => 'No valid API keys configured. Check config/api-key.php']);
    exit;
}

// Helper: Lấy user_id hiện tại (từ session hoặc auth token; tùy chỉnh theo hệ thống của bạn)
function getCurrentUserId() {
    // Ví dụ: Từ session (sau khi login)
    $user_id = $_SESSION['id'];
    return $user_id ?? null;
    // Hoặc từ JWT: return decodeJWT($_SERVER['HTTP_AUTHORIZATION'] ?? '')['user_id'] ?? null;
}

// Helper: Lưu tin nhắn vào DB (bây giờ bao gồm user_id)
function saveMessage($userId, $sessionId, $role, $message) {
    global $conn;
    if ($userId) {
        $stmt = $conn->prepare("INSERT INTO chat_history (user_id, session_id, role, message) VALUES (?, ?, ?, ?)");
        if (!$stmt) throw new Exception('Prepare save failed: ' . $conn->error);
        $stmt->bind_param('isss', $userId, $sessionId, $role, $message);
    } else {
        // Fallback cho anonymous (chỉ session_id)
        $stmt = $conn->prepare("INSERT INTO chat_history (user_id, session_id, role, message) VALUES (NULL, ?, ?, ?)");
        if (!$stmt) throw new Exception('Prepare save failed: ' . $conn->error);
        $stmt->bind_param('sss', $sessionId, $role, $message);
    }
    if (!$stmt->execute()) throw new Exception('Save message failed: ' . $stmt->error);
    $stmt->close();
}

// Helper: Load history từ DB (50 tin gần nhất, ưu tiên theo user_id nếu có)
function loadHistory($userId, $sessionId) {
    global $conn;
    if ($userId) {
        $stmt = $conn->prepare("SELECT role, message, created_at FROM chat_history WHERE user_id = ? AND session_id = ? ORDER BY created_at ASC LIMIT 50");
        if (!$stmt) throw new Exception('Prepare load failed: ' . $conn->error);
        $stmt->bind_param('is', $userId, $sessionId);
    } else {
        $stmt = $conn->prepare("SELECT role, message, created_at FROM chat_history WHERE session_id = ? ORDER BY created_at ASC LIMIT 50");
        if (!$stmt) throw new Exception('Prepare load failed: ' . $conn->error);
        $stmt->bind_param('s', $sessionId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $history = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $history;
}

// Xử lý request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['load_history'])) {
    // Load history
    $userId = getCurrentUserId();
    $sessionId = $_GET['session_id'] ?? '';
    if (empty($sessionId)) {
        echo json_encode(['history' => []]);
        exit;
    }
    try {
        $history = loadHistory($userId, $sessionId);
        echo json_encode(['history' => $history]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed (use POST)']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}
$userMessage = trim($input['message'] ?? '');
$sessionId = $input['session_id'] ?? '';
$userId = getCurrentUserId();  // Lấy từ auth

if (empty($userMessage) || empty($sessionId)) {
    echo json_encode(['error' => 'Missing message or session_id']);
    exit;
}

if (!$userId) {
    error_log('Warning: No user_id found; using anonymous mode');
}

// Lưu user message trước
try {
    saveMessage($userId, $sessionId, 'user', $userMessage);
} catch (Exception $e) {
    echo json_encode(['error' => 'Save user message failed: ' . $e->getMessage()]);
    exit;
}

// Tiếp tục logic AI (giữ nguyên từ code cũ: schema, systemPrompt, helpers...)
$schemaFile = __DIR__ . '/uploads/data/schema_inline.txt';
try {
    if (!file_exists($schemaFile)) throw new Exception('Schema file not found: ' . $schemaFile);
    $schemaDescription = file_get_contents($schemaFile);
    if (empty($schemaDescription)) throw new Exception('Schema file is empty');
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

$systemPrompt = "Bạn là AI hỗ trợ của Code Cùng Sang - nền tảng học lập trình. Trả lời ngắn gọn, hữu ích, thân thiện bằng tiếng Việt. Tập trung vào khóa học PHP (backend web), React (frontend web), C++ (lập trình hệ thống). 

- Nếu hỏi giá khóa học cụ thể (ví dụ: 'giá khóa PHP'): Liệt kê tên khóa, giá VND, số học viên, giờ học. Định dạng bảng đơn giản nếu nhiều kết quả.
- Nếu hỏi khuyến nghị học web (ví dụ: 'muốn học web thì học gì'): Gợi ý lộ trình: Bắt đầu PHP cho backend + React cho frontend. Đề xuất 2-3 khóa top (dựa trên rating/số học viên), lý do chọn, lợi ích.
- Nếu hỏi phát triển di động (ví dụ: 'học gì để làm app mobile'): Gợi ý lộ trình chung (Flutter/Dart cho cross-platform, hoặc Swift/Kotlin riêng). Vì nền tảng chưa có khóa mobile, khuyến khích học web trước (PHP/React) làm nền tảng, rồi bổ sung. Gợi ý khóa liên quan nếu có (như React Native nếu mở rộng).
- Luôn gợi ý lộ trình học nếu phù hợp (bước 1: cơ bản, bước 2: nâng cao). Kết thúc bằng lời kêu gọi hành động: 'Đăng ký ngay để nhận ưu đãi!'.

Sử dụng ngôn ngữ gần gũi, thêm emoji nếu phù hợp (📚, 💻). Không đề cập DB/SQL.";

function callGemini($prompt, $keys, $apiUrlBase) {
    try {
        $body = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.8, 'maxOutputTokens' => 2000000]  // Tăng temperature cho đa dạng
        ];
        $bodyJson = json_encode($body);
        $apiResponse = fetchWithFailover($keys, $apiUrlBase, $bodyJson);
        if (!isset($apiResponse['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Invalid API response structure');
        }
        $reply = extractGeminiText($apiResponse);
        if ($reply === null) throw new Exception('Gemini không trả về nội dung text hợp lệ');
        return $reply;
    } catch (Exception $e) {
        error_log('Gemini call error: ' . $e->getMessage());
        throw $e;
    }
}

function fetchWithFailover($keys, $apiUrlBase, $bodyJson) {
    $keyIndex = 1;
    foreach ($keys as $apiKey) {
        $apiUrl = $apiUrlBase . '?key=' . $apiKey;
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $bodyJson,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
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
            $errorText = substr($response, 0, 200);
            error_log("⚠️ API key {$keyIndex} failed with status {$httpCode}: {$errorText}");
            $keyIndex++;
            continue;
        }
    }
    throw new Exception("Tất cả các khóa API Gemini đều thất bại hoặc đã hết hạn");
}

function extractGeminiText($apiResponse) {
    if (!isset($apiResponse['candidates'][0])) return null;
    $candidate = $apiResponse['candidates'][0];
    if (isset($candidate['content']['parts'])) {
        foreach ($candidate['content']['parts'] as $part) {
            if (isset($part['text']) && trim($part['text']) !== '') return $part['text'];
        }
    }
    if (isset($candidate['content']) && is_string($candidate['content'])) return $candidate['content'];
    return null;
}

    $apiUrlBase = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';


try {
    // Phân loại & generate reply (cập nhật classification để xử lý khuyến nghị tốt hơn)
    $classificationPrompt = "
    Bạn là hệ thống phân loại yêu cầu truy vấn.

    Dưới đây là mô tả CSDL thực tế:

    $schemaDescription

    --- NHIỆM VỤ RÕ RÀNG ---
    1. Nếu câu hỏi yêu cầu dữ liệu cụ thể từ DB:
    - lấy danh sách khóa học / theo category (ví dụ: PHP, React)
    - tìm khoá học theo tên (fuzzy search)
    - xem giá / chi tiết / số học viên / giờ học của khóa cụ thể
    ➡️ Trả về: QUERY_DB
    và VIẾT SQL SELECT đúng 100% theo CSDL ở trên (sử dụng LIKE cho tên, = cho danh_muc).

    2. Nếu câu hỏi là khuyến nghị lộ trình, gợi ý khóa học dựa trên chủ đề (web, mobile, v.v.), không cần dữ liệu chính xác từ DB:
    - học web / frontend / backend
    - phát triển di động / app mobile
    - so sánh ngôn ngữ / lộ trình học
    ➡️ Trả về: GENERAL

    3. Các trường hợp khác (chào hỏi, hỏi chung): GENERAL

    --- QUY TẮC SQL ---
    - Chỉ SELECT, ? placeholder.
    - Fuzzy tên: LIKE CONCAT('%', ?, '%')
    - Category: danh_muc = ? hoặc JOIN categories.
    - Không tạo bảng/cột mới.
    - JOIN nếu cần: courses LEFT JOIN categories ON danh_muc_id = id; LEFT JOIN reviews ON id = course_id cho rating.

    --- ĐỊNH DẠNG BẮT BUỘC ---
    QUERY_DB
    SELECT ... (full SQL)

    hoặc

    GENERAL

    --- CÂU HỎI ---
    $userMessage
    ";

    $classification = callGemini($classificationPrompt, GEMINI_API_KEYS, $apiUrlBase);
    
    error_log('Classification response: ' . $classification);
    
    if (strpos($classification, 'GENERAL') !== false) {
        // Cập nhật prompt cho GENERAL để đa dạng, dựa trên ví dụ
        $generalPrompt = $systemPrompt . "\n\nVí dụ trả lời đa dạng:\n" .
                         "- Hỏi giá: 'Khóa PHP Master giá 1.500.000 VNĐ, có 500 học viên. 📈'\n" .
                         "- Học web: 'Lộ trình web: 1. PHP backend (khóa 'Lộ Trình PHP Master'). 2. React frontend (khóa 'React Pro'). Đăng ký combo giảm 20%! 💻'\n" .
                         "- Mobile: 'Cho mobile, học Flutter sau khi vững web. Bắt đầu với React để làm React Native. Gợi ý khóa React trước! 🚀'\n\n" .
                         "Người dùng: " . $userMessage . "\nAI:";
        $aiReply = callGemini($generalPrompt, GEMINI_API_KEYS, $apiUrlBase);
    } else {
        $sqlMatch = [];
        if (preg_match('/QUERY_DB\s*(.+)/s', $classification, $sqlMatch)) {
            $generatedSql = trim($sqlMatch[1]);
            
            if (stripos($generatedSql, 'SELECT') !== 0 || 
                stripos($generatedSql, 'INSERT') !== false || 
                stripos($generatedSql, 'UPDATE') !== false || 
                stripos($generatedSql, 'DELETE') !== false) {
                throw new Exception('SQL không hợp lệ: Phải là SELECT an toàn');
            }
            
            $params = [];
            $paramCount = substr_count($generatedSql, '?');
            if ($paramCount > 0) {
                $lowerMessage = strtolower($userMessage);
                $categoryMap = [
                    'php' => 'PHP', 
                    'react' => 'React', 
                    'c++' => 'C++',
                    'web' => 'PHP',  // Mặc định cho web
                    'mobile' => 'React'  // Gợi ý React cho mobile web
                ];
                foreach ($categoryMap as $key => $value) {
                    if (strpos($lowerMessage, $key) !== false) {
                        $params[] = $value;
                    }
                }
                // Fuzzy cho tên khóa
                if (strpos($lowerMessage, 'khóa') !== false || strpos($lowerMessage, 'course') !== false) {
                    $params[] = $userMessage;  // Sử dụng message gốc cho fuzzy
                }
                while (count($params) < $paramCount) $params[] = '%';
                $params = array_slice($params, 0, $paramCount);
            }
            
            error_log('Generated SQL: ' . $generatedSql . ' | Params: ' . json_encode($params));
            
            $stmt = $conn->prepare($generatedSql);
            if (!$stmt) throw new Exception('Prepare SQL failed: ' . $conn->error);
            
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                $bindParams = array_merge([$types], $params);
                call_user_func_array([$stmt, 'bind_param'], $bindParams);
            }
            
            if (!$stmt->execute()) throw new Exception('Execute SQL failed: ' . $stmt->error);
            
            $result = $stmt->get_result();
            $results = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            
            $resultsJson = json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
            // Cập nhật replyPrompt để đa dạng dựa trên loại query
            $replyPrompt = $systemPrompt . "\n\nCâu hỏi người dùng: " . $userMessage . 
                           "\n\nKết quả từ DB:\n" . $resultsJson . 
                           "\n\nNhiệm vụ: Dựa vào kết quả DB, trả lời đa dạng, hữu ích bằng tiếng Việt. 
                           - Nếu giá: Liệt kê rõ ràng, thêm emoji 💰.
                           - Nếu danh sách: Gợi ý top 1-2, lý do.
                           - Nếu không kết quả: Chuyển sang gợi ý GENERAL (web/mobile).
                           Không đề cập đến DB hoặc SQL. Giữ ngắn gọn, hấp dẫn.";
            
            $aiReply = callGemini($replyPrompt, GEMINI_API_KEYS, $apiUrlBase);
        } else {
            throw new Exception('Không thể trích xuất SQL từ phản hồi phân loại: ' . $classification);
        }
    }
    
    // Lưu AI reply sau khi generate
    try {
        saveMessage($userId, $sessionId, 'ai', $aiReply);
    } catch (Exception $e) {
        error_log('Save AI reply failed: ' . $e->getMessage());  // Không throw để không break chat
    }
    
    echo json_encode(['reply' => trim($aiReply)]);

} catch (Exception $e) {
    error_log('Chat error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?>