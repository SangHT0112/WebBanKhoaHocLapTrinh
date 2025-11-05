<?php
class ChiTietKhoaHoc {
    private $conn;
    private $table = 'course_details';

    // Thuộc tính tiếng Việt
    public $id;
    public $ma_khoa_hoc;
    public $mo_ta_day_du;
    public $chuong_trinh_hoc;
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

    public function __construct($db) {
        $this->conn = $db;
    }

    // 🔹 Lấy chi tiết 1 khóa học
    public function layMotKhoaHoc($ma_khoa_hoc) {
        $sql = 'SELECT 
            cd.id,
            cd.ma_khoa_hoc,
            cd.mo_ta_day_du,
            cd.chuong_trinh_hoc,
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
        return $result->fetch_assoc();
    }

    // 🔹 Thêm chi tiết khóa học
    public function them() {
        $sql = 'INSERT INTO ' . $this->table . '
            (ma_khoa_hoc, mo_ta_day_du, chuong_trinh_hoc, ten_giang_vien, gioi_thieu_giang_vien, loi_ich)
        VALUES (?, ?, ?, ?, ?, ?)';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "isssss",
            $this->ma_khoa_hoc,
            $this->mo_ta_day_du,
            $this->chuong_trinh_hoc,
            $this->ten_giang_vien,
            $this->gioi_thieu_giang_vien,
            $this->loi_ich
        );

        return $stmt->execute();
    }

    // 🔹 Cập nhật chi tiết khóa học
    public function capNhat() {
        $sql = 'UPDATE ' . $this->table . '
            SET 
                mo_ta_day_du = ?, 
                chuong_trinh_hoc = ?, 
                ten_giang_vien = ?, 
                gioi_thieu_giang_vien = ?, 
                loi_ich = ?, 
                ngay_cap_nhat = CURRENT_TIMESTAMP
            WHERE ma_khoa_hoc = ?';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "sssssi",
            $this->mo_ta_day_du,
            $this->chuong_trinh_hoc,
            $this->ten_giang_vien,
            $this->gioi_thieu_giang_vien,
            $this->loi_ich,
            $this->ma_khoa_hoc
        );

        return $stmt->execute();
    }

    // 🔹 Xóa chi tiết khóa học
    public function xoa() {
        $sql = 'DELETE FROM ' . $this->table . ' WHERE ma_khoa_hoc = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->ma_khoa_hoc);
        return $stmt->execute();
    }
}
?>
