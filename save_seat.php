<?php
session_start();
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare("
  UPDATE student_classes
  SET seat_x = ?, seat_y = ?
  WHERE student_id = ? AND class_id = ?
");

$stmt->bind_param(
  "iiii",
  $data['x'],
  $data['y'],
  $data['student_id'],
  $data['class_id']
);

$stmt->execute();
