<?php
class Course {
  private $conn;

  public function __construct($db) {
    $this->conn = $db;
  }

 public function getAll() {
  $sql = "SELECT * FROM courses ORDER BY id ASC";
  $result = $this->conn->query($sql);
  return $result->fetch_all(MYSQLI_ASSOC); // ✅ trả về mảng dữ liệu
}


  public function getById($id) {
    $sql = "SELECT * FROM courses WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public function getByCategory($category) {
    $sql = "
      SELECT c.*, cat.ten_danh_muc 
      FROM courses c
      JOIN categories cat ON c.danh_muc_id = cat.id
      WHERE LOWER(cat.ten_danh_muc) = ?
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }


}
