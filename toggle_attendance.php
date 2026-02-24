<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['teacher'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$student_id = (int)($data['student_id'] ?? 0);
$class_id   = (int)($data['class_id']   ?? 0);

if (!$student_id || !$class_id) {
    http_response_code(400);
    exit;
}

$today = date('Y-m-d');

/* Check today's status */
$stmt = $conn->prepare("
    SELECT status
    FROM attendance
    WHERE student_id = ?
      AND class_id   = ?
      AND `date`     = ?
");
$stmt->bind_param("iis", $student_id, $class_id, $today);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

$newStatus = ($row && $row['status'] === 'Present') ? 'Absent' : 'Present';

/* Upsert — unique key is now (student_id, class_id, date) */
$stmt = $conn->prepare("
    INSERT INTO attendance (student_id, class_id, `date`, status)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE status = ?
");
$stmt->bind_param("iisss", $student_id, $class_id, $today, $newStatus, $newStatus);
$stmt->execute();

echo json_encode(['status' => $newStatus]);
