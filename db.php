
<?php
class Database {
  private $host = 'localhost';
  private $user = 'root';
  private $pass = '';
  private $dbname = 'webbankhoangoai2'; // đổi tên database của bạn

  public function connect() {
    $conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
    if ($conn->connect_error) {
      die("Kết nối thất bại: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
  }
}
// ⚠️ THÊM 2 DÒNG NÀY VÀO CUỐI FILE
$db = new Database();
$conn = $db->connect();


?>