<?php
class ChiTietKhoaHoc {
    private $conn;
    private $table = 'course_details';

    // Thuộc tính tiếng Việt
    public $id;
    public $ma_khoa_hoc;
    public $mo_ta_day_du;
    public $ten_giang_vien;
    public $gioi_thieu_giang_vien;
    public $loi_ich;
    public $ngay_tao;
    public $ngay_cap_nhat;

    // Thông tin thêm từ bảng courses
    public $ten_khoa_hoc;
    public $gia;
    public $mo_ta_ngan;
    public $bieu_tuong;
    public $so_hoc_vien;
    public $so_gio_hoc;

    // Danh sách module (mảng với lessons con)
    public $modules = []; // [{"module_name": "...", "duration": "...", "content": "...", "sort_order": 1, "lessons": [...]}]

    // Thêm thuộc tính mới cho enrollment
    public $da_dang_ky = false;
    public $tien_do = 0.00;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 🔹 Kiểm tra enrollment (mới)
    public function kiemTraDangKy($ma_khoa_hoc, $ma_nguoi_dung) {
        if (!$ma_nguoi_dung) {
            return ['da_dang_ky' => false, 'tien_do' => 0.00];
        }

        $sql = "SELECT trang_thai, tien_do FROM enrollments 
                WHERE ma_khoa_hoc = ? AND ma_nguoi_dung = ? 
                AND trang_thai IN ('dang_hoc', 'hoan_thanh') 
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $ma_khoa_hoc, $ma_nguoi_dung);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            return [
                'da_dang_ky' => true,
                'tien_do' => $result['tien_do']
            ];
        }

        return ['da_dang_ky' => false, 'tien_do' => 0.00];
    }

    // Tính tiến độ dựa trên lessons đã làm quiz
    public function tinhTienDo($ma_khoa_hoc, $ma_nguoi_dung) {
        // 1. Lấy tất cả lessons của khóa học
        $sqlLessons = "SELECT cl.id 
                    FROM course_modules cm
                    JOIN course_lessons cl ON cm.id = cl.ma_module
                    WHERE cm.course_id = ?";
        $stmt = $this->conn->prepare($sqlLessons);
        $stmt->bind_param("i", $ma_khoa_hoc);
        $stmt->execute();
        $res = $stmt->get_result();
        $lessonIds = [];
        while ($row = $res->fetch_assoc()) {
            $lessonIds[] = $row['id'];
        }
        $stmt->close();

        if (empty($lessonIds)) return 0.0;

        // 2. Đếm số lessons đã làm quiz
        $in = implode(',', $lessonIds);
        $sqlDone = "SELECT COUNT(DISTINCT ma_lesson) AS done_count 
                    FROM user_quiz_answers 
                    WHERE ma_nguoi_dung = ? AND ma_lesson IN ($in)";
        $stmt = $this->conn->prepare($sqlDone);
        $stmt->bind_param("i", $ma_nguoi_dung);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $doneCount = intval($res['done_count'] ?? 0);
        $stmt->close();

        // 3. Tính %
        $totalLessons = count($lessonIds);
        return ($totalLessons > 0) ? ($doneCount / $totalLessons) * 100 : 0.0;
    }


    // 🔹 Lấy câu hỏi cho lesson (mới)
    public function layCauHoiLesson($ma_lesson) {
        $sql = "SELECT * FROM lesson_questions WHERE ma_lesson = ? ORDER BY thu_tu ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $ma_lesson);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 🔹 Lấy chi tiết 1 khóa học (Cập nhật: load lessons cho từng module + kiểm tra enrollment + load questions cho lessons)
    public function layMotKhoaHoc($ma_khoa_hoc, $ma_nguoi_dung = null) {
        $sql = 'SELECT 
            cd.id,
            cd.ma_khoa_hoc,
            cd.mo_ta_day_du,
            cd.ten_giang_vien,
            cd.gioi_thieu_giang_vien,
            cd.loi_ich,
            cd.ngay_tao,
            cd.ngay_cap_nhat,
            c.ten_khoa_hoc,
            c.gia,
            c.mo_ta AS mo_ta_ngan,
            c.bieu_tuong,
            c.so_hoc_vien,
            c.so_gio_hoc
        FROM ' . $this->table . ' cd
        LEFT JOIN courses c ON cd.ma_khoa_hoc = c.id
        WHERE cd.ma_khoa_hoc = ?
        LIMIT 1';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $ma_khoa_hoc);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if (!$data) {
            return null;
        }

        // Lấy modules
        $module_sql = "SELECT id, module_name, duration, content, sort_order 
                       FROM course_modules 
                       WHERE course_id = ?
                       ORDER BY sort_order ASC";

        $stmt2 = $this->conn->prepare($module_sql);
        $stmt2->bind_param("i", $ma_khoa_hoc);
        $stmt2->execute();
        $modules = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

        // Load lessons cho từng module + questions nếu có ma_nguoi_dung
        foreach ($modules as &$module) {
            $lesson_sql = "SELECT id, ten_bai_hoc, loai_bai_hoc, lien_ket_noi_dung, thoi_luong, mo_ta, thu_tu
                           FROM course_lessons 
                           WHERE ma_module = ?
                           ORDER BY thu_tu ASC";
            
            $stmt3 = $this->conn->prepare($lesson_sql);
            $stmt3->bind_param("i", $module['id']);
            $stmt3->execute();
            $lessons = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
            
            // Load questions cho từng lesson nếu có ma_nguoi_dung
            if ($ma_nguoi_dung) {
                foreach ($lessons as &$lesson) {
                    $lesson['questions'] = $this->layCauHoiLesson($lesson['id']);
                }
            }
            
            $module['lessons'] = $lessons;
        }

        $data['modules'] = $modules;

        // Kiểm tra enrollment (nếu có ma_nguoi_dung)
        if ($ma_nguoi_dung) {
            $enrollInfo = $this->kiemTraDangKy($ma_khoa_hoc, $ma_nguoi_dung);
            $data['da_dang_ky'] = $enrollInfo['da_dang_ky'];

            // Tính tiến độ tự động dựa trên lessons đã làm quiz
            $data['tien_do'] = $this->tinhTienDo($ma_khoa_hoc, $ma_nguoi_dung);
        }

        return $data;
    }

    // 🔹 Thêm chi tiết khóa học + modules (giữ nguyên, nhưng có thể mở rộng thêm lessons nếu cần)
    public function them() {
        // B1: thêm course_details
        $sql = 'INSERT INTO ' . $this->table . '
            (ma_khoa_hoc, mo_ta_day_du, ten_giang_vien, gioi_thieu_giang_vien, loi_ich)
        VALUES (?, ?, ?, ?, ?)';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "issss",
            $this->ma_khoa_hoc,
            $this->mo_ta_day_du,
            $this->ten_giang_vien,
            $this->gioi_thieu_giang_vien,
            $this->loi_ich
        );

        if (!$stmt->execute()) return false;

        // B2: thêm module nếu có
        if (!empty($this->modules)) {
            $module_sql = "INSERT INTO course_modules (course_id, module_name, duration, content, sort_order)
                           VALUES (?, ?, ?, ?, ?)";
            $stmt2 = $this->conn->prepare($module_sql);

            foreach ($this->modules as $m) {
                $stmt2->bind_param(
                    "isssi",
                    $this->ma_khoa_hoc,
                    $m['module_name'],
                    $m['duration'],
                    $m['content'],
                    $m['sort_order']
                );
                $stmt2->execute();
            }
        }

        return true;
    }

    // 🔹 Cập nhật chi tiết khóa học + cập nhật modules (giữ nguyên)
    public function capNhat() {
        // B1: update course_details
        $sql = 'UPDATE ' . $this->table . '
            SET 
                mo_ta_day_du = ?, 
                ten_giang_vien = ?, 
                gioi_thieu_giang_vien = ?, 
                loi_ich = ?, 
                ngay_cap_nhat = CURRENT_TIMESTAMP
            WHERE ma_khoa_hoc = ?';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssi",
            $this->mo_ta_day_du,
            $this->ten_giang_vien,
            $this->gioi_thieu_giang_vien,
            $this->loi_ich,
            $this->ma_khoa_hoc
        );

        if (!$stmt->execute()) return false;

        // B2: xóa module cũ
        $delete_sql = "DELETE FROM course_modules WHERE course_id = ?";
        $stmtDel = $this->conn->prepare($delete_sql);
        $stmtDel->bind_param("i", $this->ma_khoa_hoc);
        $stmtDel->execute();

        // B3: thêm module mới
        if (!empty($this->modules)) {
            $module_sql = "INSERT INTO course_modules (course_id, module_name, duration, content, sort_order)
                           VALUES (?, ?, ?, ?, ?)";
            $stmt2 = $this->conn->prepare($module_sql);

            foreach ($this->modules as $m) {
                $stmt2->bind_param(
                    "isssi",
                    $this->ma_khoa_hoc,
                    $m['module_name'],
                    $m['duration'],
                    $m['content'],
                    $m['sort_order']
                );
                $stmt2->execute();
            }
        }

        return true;
    }

    // 🔹 Xóa chi tiết + modules + lessons (Cập nhật: xóa lessons trước)
    public function xoa() {
        // Xóa lessons trước (cascade nếu có, nhưng để an toàn)
        $delLessons = "DELETE FROM course_lessons WHERE ma_module IN (SELECT id FROM course_modules WHERE course_id = ?)";
        $stmtLessons = $this->conn->prepare($delLessons);
        $stmtLessons->bind_param("i", $this->ma_khoa_hoc);
        $stmtLessons->execute();

        // Xóa module
        $delModule = "DELETE FROM course_modules WHERE course_id = ?";
        $stmt1 = $this->conn->prepare($delModule);
        $stmt1->bind_param("i", $this->ma_khoa_hoc);
        $stmt1->execute();

        // Xóa course_detail
        $sql = 'DELETE FROM ' . $this->table . ' WHERE ma_khoa_hoc = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->ma_khoa_hoc);
        return $stmt->execute();
    }
}
?>