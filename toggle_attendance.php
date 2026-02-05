<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['teacher'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$student_id = (int)($data['student_id'] ?? 0);
$class_id   = (int)($data['class_id'] ?? 0);

if (!$student_id || !$class_id) {
    http_response_code(400);
    exit;
}

/* Toggle logic */
$stmt = $conn->prepare("
    SELECT status
    FROM attendance
    WHERE student_id = ?
      AND class_id = ?
      AND DATE(timestamp) = CURDATE()
");
$stmt->bind_param("ii", $student_id, $class_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$newStatus = ($result && $result['status'] === 'Present') ? 'Absent' : 'Present';

$stmt = $conn->prepare("
    INSERT INTO attendance (student_id, class_id, status)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE status = ?
");
$stmt->bind_param("iiss", $student_id, $class_id, $newStatus, $newStatus);
$stmt->execute();

echo json_encode(['status' => $newStatus]);
