<?php
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/models/CourseDetail.php';

$db = (new Database())->connect();

$courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if (!isset($_SESSION['id'])) {
    header("Location: /page/login/login.php");
    exit;
}

$ma_nguoi_dung = $_SESSION['id'];

$chiTietKhoaHoc = new ChiTietKhoaHoc($db);
$courseDetail = $chiTietKhoaHoc->layMotKhoaHoc($courseId, $ma_nguoi_dung);

if (!$courseDetail || !($courseDetail['da_dang_ky'] ?? false)) {
    header("Location: course-detail.php?id=" . $courseId . "&error=not_enrolled");
    exit;
}

$tien_do = $courseDetail['tien_do'] ?? 0.00;

// Kiểm tra thông báo quiz
$quizMessage = '';
if (isset($_GET['quiz_completed'])) {
    $score = $_GET['score'] ?? '0/0';
    $quizMessage = "Hoàn thành quiz! Điểm: " . htmlspecialchars($score);
}

// THÊM HÀM getQuizResult Ở ĐÂY (trong PHP block)
function getQuizResult($db, $ma_nguoi_dung, $lessonId) {
    $sql = "
        SELECT 
            uqa.cau_tra_loi, 
            uqa.ai_phan_hoi, 
            uqa.diem_dat_duoc AS score,
            lq.id AS question_id,
            lq.cau_hoi,
            lq.diem AS score_max,
            lq.thu_tu
        FROM user_quiz_answers uqa
        JOIN lesson_questions lq ON uqa.ma_cau_hoi = lq.id
        WHERE uqa.ma_nguoi_dung = ? AND uqa.ma_lesson = ?
        ORDER BY lq.thu_tu ASC
    ";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed in getQuizResult for lesson {$lessonId}: " . $db->error);
        return ['has_completed' => false, 'score' => 0, 'max_score' => 0, 'details' => []];
    }
    $stmt->bind_param("ii", $ma_nguoi_dung, $lessonId);
    $stmt->execute();
    $res = $stmt->get_result();
    $details = [];
    $totalScore = 0;
    $totalMax = 0;
    while ($row = $res->fetch_assoc()) {
        $userAnswer = json_decode($row['cau_tra_loi'], true) ?? $row['cau_tra_loi'];
        $explanation = json_decode($row['ai_phan_hoi'], true) ?? $row['ai_phan_hoi'];
        $isCorrect = ($row['score'] >= $row['score_max']); // Full point = đúng
        
        $details[] = [
            'question_id' => $row['question_id'],
            'thu_tu' => $row['thu_tu'],
            'cau_hoi' => $row['cau_hoi'],
            'user_answer' => $userAnswer,
            'is_correct' => $isCorrect,
            'score' => $row['score'],
            'score_max' => $row['score_max'],
            'explanation' => $explanation
        ];
        $totalScore += $row['score'];
        $totalMax += $row['score_max'];
    }
    $stmt->close();
    
    return [
        'has_completed' => !empty($details),
        'score' => $totalScore,
        'max_score' => $totalMax,
        'details' => $details
    ];
}
?>



<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($courseDetail['ten_khoa_hoc']); ?> - Học Ngay</title>
    <?php include __DIR__ . '/layout/head.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Thêm SweetAlert2 CDN vào <head> nếu chưa có -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="bg-gray-50 text-gray-800">
    <?php include __DIR__ . '/layout/header.php'; ?>

    <!-- HEADER -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-10 text-center shadow-lg">
        <h1 class="text-4xl font-bold">
            <?php echo htmlspecialchars($courseDetail['ten_khoa_hoc']); ?>
        </h1>
        <p class="mt-2 text-lg opacity-90">
            <?php echo htmlspecialchars($courseDetail['mo_ta_ngan']); ?>
        </p>

        <!-- Progress -->
        <div class="w-3/4 md:w-1/2 mx-auto mt-6">
            <p class="text-sm mb-1">Tiến độ học: <span class="font-bold"><?php echo number_format($tien_do, 1); ?>%</span></p>
            <div class="w-full bg-white/30 rounded-full h-3">
                <div class="h-3 bg-green-400 rounded-full" style="width: <?php echo $tien_do; ?>%"></div>
            </div>
        </div>

        <?php if ($quizMessage): ?>
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            <?php echo htmlspecialchars($quizMessage); ?>
        </div>
        <?php endif; ?>

        <a href="course-detail.php?id=<?php echo $courseId; ?>" 
           class="inline-block mt-5 px-6 py-2 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition">
            Quay Lại Chi Tiết Khóa Học
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="max-w-5xl mx-auto p-6">

        <!-- MODULE LIST -->
        <h2 class="text-2xl font-bold mt-8 mb-4">Chương Trình Học</h2>

        <?php 
        $modules = $courseDetail['modules'] ?? [];
        if (empty($modules)): ?>
            <p class="text-gray-600">Chương trình học đang được cập nhật...</p>
        <?php else: ?>

        <div class="space-y-4">

            <?php foreach ($modules as $module): ?>
            <div class="border rounded-xl bg-white shadow">

                <!-- HEADER -->
                <button onclick="toggleLessons(this)"
                    class="w-full flex justify-between items-start px-5 py-4 text-left hover:bg-gray-100 transition">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">
                            <?php echo htmlspecialchars($module['module_name']); ?>
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            <?php echo nl2br(htmlspecialchars($module['content'])); ?>
                        </p>
                    </div>
                    <span class="text-xl rotate-0 transition-transform">⌄</span>
                </button>

                <!-- CONTENT -->
                <div class="max-h-0 overflow-hidden transition-all duration-300">

                    <?php 
                    $lessons = $module['lessons'] ?? [];
                    if (empty($lessons)): ?>
                        <p class="p-4 text-gray-500">Chưa có bài học.</p>
                    <?php else: ?>

                    <ul class="divide-y">
                        <?php foreach ($lessons as $lesson): ?>
                        <li class="p-4 bg-gray-50">

                            <div class="flex justify-between items-start">
                                <div class="w-3/4">
                                    <h4 class="font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($lesson['ten_bai_hoc']); ?>
                                    </h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <?php echo htmlspecialchars($lesson['mo_ta']); ?>
                                    </p>

                                    <!-- VIDEO -->
                                    <?php if ($lesson['loai_bai_hoc'] === 'video'): ?>

                                        <?php 
                                        $youtube_id = '';
                                        if (preg_match('/(?:watch\?v=|youtu\.be\/|embed\/)([^&]+)/', $lesson['lien_ket_noi_dung'], $m)) {
                                            $youtube_id = $m[1];
                                        }
                                        ?>

                                        <?php if ($youtube_id): ?>
                                        <div class="relative w-full pt-[56.25%] mt-3 rounded-lg overflow-hidden shadow">
                                            <iframe class="absolute inset-0 w-full h-full"
                                                src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>"
                                                allowfullscreen></iframe>
                                        </div>
                                        <?php endif; ?>

                                    <?php elseif ($lesson['loai_bai_hoc'] === 'tai_lieu'): ?>
                                        <a href="<?php echo $lesson['lien_ket_noi_dung']; ?>" 
                                           target="_blank"
                                           class="inline-block mt-3 px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                                           Tải Tài Liệu (PDF)
                                        </a>

                                    <?php elseif ($lesson['loai_bai_hoc'] === 'kiem_tra'): ?>
                                        <?php 
                                        // Lấy kết quả cũ
                                        $quizResult = getQuizResult($db, $ma_nguoi_dung, $lesson['id']);
                                        $questions = $lesson['questions'] ?? []; // Để hiển thị câu hỏi nếu cần
                                        if (is_string($questions)) {
                                            $questions = json_decode($questions, true) ?? [];
                                        }
                                        $tongDiemMax = $quizResult['max_score'] ?? array_sum(array_column($questions, 'diem'));
                                        ?>
                                        
                                        <?php if (!$quizResult['has_completed']): ?>
                                            <!-- Chưa làm: Hiển thị form như cũ -->
                                            <form method="POST" action="quiz-handler.php" id="quizForm_<?php echo $lesson['id']; ?>">
                                                <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
                                                <?php if (empty($questions)): ?>
                                                    <p class="text-gray-500">Chưa có câu hỏi cho bài kiểm tra này.</p>
                                                <?php else: ?>
                                                    <div class="space-y-4">
                                                        <?php foreach ($questions as $q): ?>
                                                            <div class="border rounded-lg p-4 bg-white">
                                                                <h5 class="font-semibold mb-2">Câu <?php echo $q['thu_tu']; ?>: <?php echo htmlspecialchars($q['cau_hoi']); ?> (<?php echo $q['diem']; ?> điểm)</h5>
                                                                
                                                                <?php if ($q['loai_cau_hoi'] === 'multiple'): ?>
                                                                    <?php 
                                                                    $options = $q['options'] ?? [];
                                                                    if (is_string($options)) {
                                                                        $options = json_decode($options, true) ?? [];
                                                                    }
                                                                    ?>
                                                                    <?php if (is_array($options) && !empty($options)): ?>
                                                                        <?php foreach ($options as $opt): ?>
                                                                            <label class="block mt-2">
                                                                                <input type="checkbox" name="answers[<?php echo $q['id']; ?>][]" value="<?php echo htmlspecialchars($opt['value']); ?>" class="mr-2">
                                                                                <?php echo htmlspecialchars($opt['label']); ?>
                                                                            </label>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                
                                                                <?php elseif ($q['loai_cau_hoi'] === 'fill'): ?>
                                                                    <label for="answer-<?php echo $q['id']; ?>" class="block text-sm font-medium mt-2 mb-1">Điền đáp án:</label>
                                                                    <input type="text" id="answer-<?php echo $q['id']; ?>" name="answers[<?php echo $q['id']; ?>]" class="w-full p-2 border rounded mt-2" placeholder="Điền đáp án..." required>
                                                                
                                                                <?php elseif ($q['loai_cau_hoi'] === 'code'): ?>
                                                                    <label for="answer-<?php echo $q['id']; ?>" class="block text-sm font-medium mt-2 mb-1">Viết code:</label>
                                                                    <textarea id="answer-<?php echo $q['id']; ?>" name="answers[<?php echo $q['id']; ?>]" class="w-full p-2 border rounded mt-2" rows="5" placeholder="Viết code..." required></textarea>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                        
                                                        <div class="flex justify-between items-center pt-4 border-t">
                                                            <p class="text-sm text-gray-600">Tổng điểm: <?php echo $tongDiemMax; ?></p>
                                                            <button type="submit" class="px-6 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                                                Nộp Bài
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </form>
                                        
                                        <?php else: ?>
                                            <!-- Đã làm: Hiển thị kết quả cũ (không form) -->
                                            <div class="border rounded-lg p-6 bg-green-50">
                                                <h5 class="font-bold text-lg mb-4 text-green-800">Kết Quả Bài Kiểm Tra (Đã Hoàn Thành)</h5>
                                                <p class="text-xl font-semibold mb-4">Điểm của bạn: <?php echo $quizResult['score']; ?>/<?php echo $quizResult['max_score']; ?> (<?php echo round(($quizResult['score'] / $quizResult['max_score']) * 100, 1); ?>%)</p>
                                                
                                                <?php if (!empty($quizResult['details'])): ?>
                                                    <div class="space-y-3">
                                                        <?php foreach ($quizResult['details'] as $detail): ?>
                                                            <div class="border-l-4 border-green-400 pl-4 bg-white p-3 rounded">
                                                                <h6 class="font-medium">Câu <?php echo $detail['thu_tu']; ?>: <?php echo htmlspecialchars($detail['cau_hoi']); ?></h6>
                                                                <p class="text-sm text-gray-600 mt-1"><strong>Đáp án của bạn:</strong> <?php echo htmlspecialchars(json_encode($detail['user_answer'])); ?></p>
                                                                <p class="text-sm"><strong>Kết quả:</strong> <?php echo $detail['is_correct'] ? '✅ Đúng' : '❌ Sai'; ?> (<?php echo $detail['score']; ?>/<?php echo $detail['score_max']; ?>)</p>
                                                                <?php if ($detail['explanation']): ?>
                                                                    <p class="text-sm text-blue-600 mt-1"><strong>Giải thích:</strong> <?php echo htmlspecialchars($detail['explanation']); ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-gray-500">Không có chi tiết kết quả.</p>
                                                <?php endif; ?>
                                                
                                                <!-- Optional: Nút làm lại nếu muốn cho phép -->
                                                <!-- <a href="#" onclick="resetQuiz(<?php echo $lesson['id']; ?>)" class="text-sm text-blue-500 underline mt-4 inline-block">Làm lại bài?</a> -->
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- TYPE + DURATION -->
                                <div class="text-right">
                                    <span class="px-2 py-1 text-xs bg-gray-200 rounded">
                                        <?php echo htmlspecialchars($lesson['loai_bai_hoc']); ?>
                                    </span>
                                    <p class="text-xs text-gray-500 mt-2">
                                        <?php echo htmlspecialchars($lesson['thoi_luong']); ?>
                                    </p>
                                </div>
                            </div>

                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php endif; ?>

                </div>
            </div>
            <?php endforeach; ?>

        </div>

        <?php endif; ?>

        <!-- INSTRUCTOR -->
        <div class="mt-10 p-6 bg-white rounded-xl shadow flex space-x-4 items-center">
            <div class="w-14 h-14 bg-indigo-600 text-white flex items-center justify-center rounded-full text-xl font-bold">
                <?php 
                $gv = trim($courseDetail['ten_giang_vien']);
                echo mb_substr($gv, 0, 1, 'UTF-8');
                ?>
            </div>
            <div>
                <h3 class="text-xl font-semibold">
                    <?php echo htmlspecialchars($gv); ?>
                </h3>
                <p class="text-gray-600">
                    <?php echo htmlspecialchars($courseDetail['gioi_thieu_giang_vien']); ?>
                </p>
            </div>
        </div>

    </div>

    <?php include __DIR__ . '/layout/footer.php'; ?>

<script>
    
function toggleLessons(btn) {
    const content = btn.nextElementSibling;
    const icon = btn.querySelector("span");

    if (content.style.maxHeight) {
        content.style.maxHeight = null;
        icon.style.transform = "rotate(0deg)";
    } else {
        content.style.maxHeight = content.scrollHeight + "px";
        icon.style.transform = "rotate(180deg)";
    }
}
// JS cho từng form quiz (vì có thể có nhiều lesson)
document.addEventListener('DOMContentLoaded', function() {
    const quizForms = document.querySelectorAll('form[id^="quizForm_"]');
    quizForms.forEach(function(form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
               // ⭐ HIỆN LOADING SWEETALERT
            Swal.fire({
                title: 'Đang chấm bài...',
                html: 'AI đang phân tích và chấm điểm bài làm của bạn.<br>Vui lòng chờ trong giây lát...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            try {
                const res = await fetch('./quiz-handler.php', {
                    method: 'POST',
                    body: formData
                });

                if (!res.ok) throw new Error('Network response was not ok');

                const data = await res.json();
                 // 🔥 ĐÓNG LOADING
                Swal.close();
                if (data.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: data.error,
                    });
                    console.error('Error details:', data);
                    return;
                }

                // Hiển thị kết quả đẹp với Swal
                const score = data.score || 0;
                const maxScore = data.max_score || 0;

                let html = `<p>Điểm của bạn: <strong>${score}/${maxScore}</strong></p>`;

                if (data.details && data.details.length) {
                    html += '<ul style="text-align:left;margin-top:10px;">';
                    data.details.forEach(detail => {
                        html += `<li>
                            Câu ${detail.question_id}: <strong>${detail.is_correct ? 'Đúng ✅' : 'Sai ❌'}</strong> 
                            (${detail.score}/${10})<br>
                            <em>${detail.explanation || ''}</em>
                        </li>`;
                    });
                    html += '</ul>';
                }

                await Swal.fire({
                    title: 'Kết quả Quiz',
                    html: html,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });

                // Optional: reload để cập nhật tiến độ
                location.reload();

            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Có lỗi xảy ra',
                    text: error.message
                });
                console.error('Fetch error:', error);
            }
        });
    });
});

</script>

</body>
</html>
<?php include __DIR__ . '/search-results.php'; ?>