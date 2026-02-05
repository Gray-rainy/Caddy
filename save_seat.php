<?php
session_start();
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare("
  UPDATE student_classes
  SET seat_row = ?, seat_col = ?
  WHERE student_id = ? AND class_id = ?
");

$stmt->bind_param(
  "iiii",
  $data['row'],
  $data['col'],
  $data['student_id'],
  $data['class_id']
);

$stmt->execute();
