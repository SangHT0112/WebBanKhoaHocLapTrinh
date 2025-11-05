<?php
class Order {
  private $conn;

  public function __construct($conn) {
    $this->conn = $conn;
  }

  // Lấy tất cả đơn hàng
  public function getAll() {
    $sql = "SELECT o.*, u.username 
            FROM orders o 
            JOIN users u ON u.id = o.user_id 
            ORDER BY o.ngay_tao DESC";
    return $this->conn->query($sql);
  }

  // Cập nhật trạng thái
  public function updateStatus($id, $status) {
    $stmt = $this->conn->prepare("UPDATE orders SET trang_thai = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    return $stmt->execute();
  }
}
?>
