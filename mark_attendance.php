<?php
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$uid = $data['uid'] ?? null;
if (!$uid) exit("No UID");

$stmt = $conn->prepare("
SELECT s.id, sc.class_id
FROM students s
JOIN student_classes sc ON sc.student_id = s.id
WHERE s.student_uid = ?
");
$stmt->bind_param("s", $uid);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res) exit("Unknown Card");

$student_id = $res['id'];
$class_id = $res['class_id'];

$stmt = $conn->prepare("
INSERT INTO attendance (student_id, class_id, status)
VALUES (?, ?, 'Present')
ON DUPLICATE KEY UPDATE
status = IF(status='Present','Absent','Present')
");
$stmt->bind_param("ii", $student_id, $class_id);
$stmt->execute();

echo "Status Toggled";
