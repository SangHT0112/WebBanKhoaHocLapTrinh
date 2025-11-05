<?php
require_once __DIR__ . '/../models/Course.php';

class CourseController {
  public $model; // 👈 đổi từ private → public

  public function __construct($db) {
    $this->model = new Course($db);
  }

  public function index() {
    include __DIR__ . '/../category.php';
    return $this->model->getAll();
  }

  public function detail($id) {
    $course = $this->model->getById($id);
    include __DIR__ . '/../../views/courses/detail.php';
  }

  public function getByCategory($category) {
  return $this->model->getByCategory($category);
}
}
