<?php
class User {
  private $conn;

  public function __construct($db) {
    $this->conn = $db;
  }

  public function getAll() {
    $sql = "SELECT id, username, email, avatar, role, created_at FROM users ORDER BY id ASC";
    $result = $this->conn->query($sql);
    if (!$result) {
      die("Lỗi truy vấn: " . $this->conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
  }

  public function getById($id) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public function delete($id) {
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }
}
?>
