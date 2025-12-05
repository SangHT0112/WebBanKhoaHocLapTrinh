<?php
require_once __DIR__ . '/../models/Order.php';

class OrderController {
  public $model;

  public function __construct($conn) {
    $this->model = new Order($conn);
  }

  public function index() {
    return $this->model->getAll();
  }

  public function getUserOrders($user_id) {
    return $this->model->getByUserId($user_id);
  }

  public function getOrderDetails($order_id) {
    return $this->model->getOrderDetails($order_id);
  }

  public function update($id, $status) {
    return $this->model->updateStatus($id, $status);
  }
}
?>
