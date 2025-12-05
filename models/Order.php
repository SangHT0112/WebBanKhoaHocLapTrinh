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

  // Lấy đơn hàng của user
  public function getByUserId($user_id) {
    $sql = "SELECT o.id, o.tong_tien, o.trang_thai, o.ngay_tao, o.fullname, o.email, o.phone, o.address
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.ngay_tao DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
  }

  // Lấy chi tiết khóa học trong đơn hàng
  public function getOrderDetails($order_id) {
    $sql = "SELECT oi.id, oi.order_id, oi.course_id, oi.so_luong, oi.don_gia,
                   c.ten_khoa_hoc, c.bieu_tuong, c.mo_ta, c.so_gio_hoc
            FROM order_items oi
            JOIN courses c ON c.id = oi.course_id
            WHERE oi.order_id = ?
            ORDER BY oi.id DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result();
  }

  // Cập nhật trạng thái
  public function updateStatus($id, $status) {
    $stmt = $this->conn->prepare("UPDATE orders SET trang_thai = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    return $stmt->execute();
  }

  // Xử lý trả hàng
  public function processRefund($order_id, $reason) {
    $stmt = $this->conn->prepare("UPDATE orders SET trang_thai = 'đã hủy' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $result = $stmt->execute();
    
    if ($result) {
      // Log refund request
      $sql = "INSERT INTO refund_requests (order_id, reason, created_at) VALUES (?, ?, NOW())";
      $stmt2 = $this->conn->prepare($sql);
      $stmt2->bind_param("is", $order_id, $reason);
      return $stmt2->execute();
    }
    return false;
  }

  // Lấy thông tin refund
  public function getRefundInfo($order_id) {
    $sql = "SELECT o.id, o.tong_tien, o.user_id, u.email, u.username, u.phone,
                   GROUP_CONCAT(c.ten_khoa_hoc) as courses,
                   rr.reason, rr.created_at as refund_date
            FROM orders o
            JOIN users u ON u.id = o.user_id
            LEFT JOIN order_items oi ON oi.order_id = o.id
            LEFT JOIN courses c ON c.id = oi.course_id
            LEFT JOIN refund_requests rr ON rr.order_id = o.id
            WHERE o.id = ?
            GROUP BY o.id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }
}
?>
