<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
  public $model;

  public function __construct($db) {
    $this->model = new User($db);
  }

  public function index() {
    return $this->model->getAll();
  }

  public function show($id) {
    return $this->model->getById($id);
  }

  public function delete($id) {
    return $this->model->delete($id);
  }
}
?>
