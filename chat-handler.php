<?php
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

// Helper: Lưu tin nhắn vào DB
function saveMessage($sessionId, $role, $message) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO chat_history (session_id, role, message) VALUES (?, ?, ?)");
    if (!$stmt) throw new Exception('Prepare save failed: ' . $conn->error);
    $stmt->bind_param('sss', $sessionId, $role, $message);
    if (!$stmt->execute()) throw new Exception('Save message failed: ' . $stmt->error);
    $stmt->close();
}

// Helper: Load history từ DB (50 tin gần nhất)
function loadHistory($sessionId) {
    global $conn;
    $stmt = $conn->prepare("SELECT role, message, created_at FROM chat_history WHERE session_id = ? ORDER BY created_at ASC LIMIT 50");
    if (!$stmt) throw new Exception('Prepare load failed: ' . $conn->error);
    $stmt->bind_param('s', $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $history;
}

// Xử lý request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['load_history'])) {
    // Load history
    $sessionId = $_GET['session_id'] ?? '';
    if (empty($sessionId)) {
        echo json_encode(['history' => []]);
        exit;
    }
    try {
        $history = loadHistory($sessionId);
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
$sessionId = $input['session_id'] ?? '';  // Nhận session_id từ frontend

if (empty($userMessage) || empty($sessionId)) {
    echo json_encode(['error' => 'Missing message or session_id']);
    exit;
}

// Lưu user message trước
try {
    saveMessage($sessionId, 'user', $userMessage);
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

$systemPrompt = "Bạn là AI hỗ trợ của Code Cùng Sang - nền tảng học lập trình. Trả lời ngắn gọn, hữu ích bằng tiếng Việt về khóa học PHP, React, C++. Gợi ý lộ trình nếu phù hợp.";

function callGemini($prompt, $keys, $apiUrlBase) {
    try {
        $body = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 2000000]
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
    // Phân loại & generate reply (giữ nguyên logic cũ)
    $classificationPrompt = $systemPrompt . "\n\nSchema CSDL:\n" . $schemaDescription . 
                            "\n\nCâu hỏi người dùng: " . $userMessage . 
                            "\n\nNhiệm vụ: Nếu câu hỏi liên quan đến thông tin khóa học cụ thể (tên, giá, mô tả, danh sách theo category), trả lời 'QUERY_DB' theo sau là câu SQL SELECT phù hợp (chỉ SELECT, dùng ? cho tham số nếu cần, ví dụ: SELECT * FROM courses WHERE category = ?). Nếu không cần DB (lộ trình chung, chào hỏi), trả lời 'GENERAL'. Định dạng chính xác: QUERY_DB\n[SQL QUERY] hoặc GENERAL.";
    
    $classification = callGemini($classificationPrompt, GEMINI_API_KEYS, $apiUrlBase);
    
    error_log('Classification response: ' . $classification);
    
    if (strpos($classification, 'GENERAL') !== false) {
        $prompt = $systemPrompt . "\n\nNgười dùng: " . $userMessage . "\nAI:";
        $aiReply = callGemini($prompt, GEMINI_API_KEYS, $apiUrlBase);
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
                $categoryMap = ['php' => 'PHP', 'react' => 'React', 'c++' => 'C++'];
                foreach ($categoryMap as $key => $value) {
                    if (strpos($lowerMessage, $key) !== false) {
                        $params[] = $value;
                    }
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
            
            $replyPrompt = $systemPrompt . "\n\nCâu hỏi người dùng: " . $userMessage . 
                           "\n\nKết quả từ DB:\n" . $resultsJson . 
                           "\n\nNhiệm vụ: Dựa vào kết quả DB, trả lời ngắn gọn, hữu ích bằng tiếng Việt. Nếu không có kết quả, gợi ý khóa học liên quan. Không đề cập đến DB hoặc SQL.";
            
            $aiReply = callGemini($replyPrompt, GEMINI_API_KEYS, $apiUrlBase);
        } else {
            throw new Exception('Không thể trích xuất SQL từ phản hồi phân loại: ' . $classification);
        }
    }
    
    // Lưu AI reply sau khi generate
    try {
        saveMessage($sessionId, 'ai', $aiReply);
    } catch (Exception $e) {
        error_log('Save AI reply failed: ' . $e->getMessage());  // Không throw để không break chat
    }
    
    echo json_encode(['reply' => trim($aiReply)]);

} catch (Exception $e) {
    error_log('Chat error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?>