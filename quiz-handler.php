<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config/api-key.php';

if (!isset($_POST['lesson_id']) || !isset($_POST['answers'])) {
    echo json_encode(['error' => 'Missing quiz data']);
    exit;
}

$lessonId = intval($_POST['lesson_id']);
$answers = $_POST['answers'];

// 1) Lấy câu hỏi từ DB
$stmt = $conn->prepare("SELECT * FROM lesson_questions WHERE ma_lesson = ? ORDER BY thu_tu ASC");
$stmt->bind_param("i", $lessonId);
$stmt->execute();
$res = $stmt->get_result();
$questions = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($questions)) {
    echo json_encode(['error' => 'No questions found']);
    exit;
}

// 2) Chuẩn hóa dữ liệu
$tongMax = 0;
foreach ($questions as &$q) {
    if (!empty($q['options'])) $q['options'] = json_decode($q['options'], true);
    if (!empty($q['answer']))  $q['answer']  = json_decode($q['answer'], true) ?: $q['answer'];
    $tongMax += intval($q['diem']);
}

$payload = [
    "questions" => $questions,
    "user_answers" => $answers
];


// 3) HÀM GỌI GEMINI – TRẢ JSON THUẦN
function callGeminiQuiz($payload) {
    $apiKey = GEMINI_API_KEYS[0];

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro:generateContent?key=$apiKey";

    $prompt = "Bạn là AI chấm bài quiz lập trình và kiến thức.

Dựa trên dữ liệu JSON sau (questions chứa câu hỏi + đáp án đúng, user_answers chứa câu trả lời của user theo question_id), hãy chấm điểm cho từng câu hỏi. 

- Với multiple choice: Kiểm tra xem user chọn đúng tất cả đáp án đúng không (partial credit nếu cần, nhưng ở đây full hoặc 0).
- Với fill-in: So sánh chính xác (case-insensitive, trim).
- Với code: Đánh giá logic, syntax, và tính đúng đắn (cho điểm dựa trên % đúng, ví dụ 80% nếu gần đúng).

Trả về CHỈ JSON thuần túy (KHÔNG markdown, không text thừa) với cấu trúc chính xác sau:

{
  \"score\": tổng_điểm_user (số nguyên),
  \"max_score\": tổng_điểm_tối_đa (tính từ diem của tất cả questions),
  \"details\": [
    {
      \"question_id\": id_câu_hỏi,
      \"user_answer\": câu_trả_lời_user (giữ nguyên),
      \"is_correct\": true/false,
      \"score\": điểm_câu_này (0 đến diem đầy đủ),
      \"explanation\": giải_thích_ngắn_gọn (nếu sai hoặc code thì feedback cụ thể)
    }
  ]
}

Dữ liệu input:\n\n" . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    $body = [
        "contents" => [[
            "role" => "user",
            "parts" => [[
                "text" => $prompt
            ]]
        ]],
        "generationConfig" => [
            "responseMimeType" => "application/json",
            "temperature" => 0.1
        ]
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['error' => 'API request failed', 'code' => $httpCode, 'response' => $response];
    }

    $aiResponse = json_decode($response, true);

    if (!isset($aiResponse["candidates"][0]["content"]["parts"][0]["text"])) {
        return ['error' => 'Invalid AI response structure', 'raw' => $aiResponse];
    }

    $jsonText = $aiResponse["candidates"][0]["content"]["parts"][0]["text"];

    // Loại bỏ bất kỳ text thừa nếu có (Gemini đôi khi thêm)
    $jsonText = trim(preg_replace('/^```json\s*|\s*```$/s', '', $jsonText));

    $parsed = json_decode($jsonText, true);

    if (!$parsed || !isset($parsed['score']) || !isset($parsed['max_score'])) {
        return ['error' => 'Invalid JSON from AI or missing score fields', 'raw' => $jsonText];
    }

    return $parsed;
}


// 4) GỌI AI
$aiResult = callGeminiQuiz($payload);

if (isset($aiResult['error'])) {
    echo json_encode($aiResult);
    exit;
}

// Lưu vào DB (tùy chọn, sử dụng score từ AI)
// Lưu vào DB (sử dụng score từ AI + ai_phan_hoi)
$ma_nguoi_dung = $_SESSION['id'] ?? 1; // Giả sử từ session
foreach ($aiResult['details'] as $detail) {

    // Chuyển user_answer và explanation sang JSON Unicode-friendly
    $userAnswer = json_encode($detail['user_answer'], JSON_UNESCAPED_UNICODE);
    $aiPhanHoi  = json_encode($detail['explanation'] ?? '', JSON_UNESCAPED_UNICODE);

    $saveSql = "
        INSERT INTO user_quiz_answers 
            (ma_nguoi_dung, ma_lesson, ma_cau_hoi, cau_tra_loi, ai_phan_hoi, diem_dat_duoc) 
        VALUES (?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            diem_dat_duoc = VALUES(diem_dat_duoc),
            ai_phan_hoi = VALUES(ai_phan_hoi)
    ";

    $stmtSave = $conn->prepare($saveSql);
    $stmtSave->bind_param(
        "iiissi", 
        $ma_nguoi_dung, 
        $lessonId, 
        $detail['question_id'], 
        $userAnswer, 
        $aiPhanHoi, 
        $detail['score']
    );
    $stmtSave->execute();
}

// Cập nhật tiến độ (tùy chọn)
$percent = $tongMax > 0 ? ($aiResult['score'] / $tongMax) * 100 : 0;
// TODO: Cập nhật enrollments tien_do

echo json_encode($aiResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
?>