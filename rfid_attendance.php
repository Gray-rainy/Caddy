<?php
/* ============================
   RFID ATTENDANCE ENDPOINT
   ============================ */

header("Content-Type: application/json");

// ---------- DB (LIMITED USER) ----------
$conn = new mysqli(
    "localhost",
    "esp32",
    "esp32_pass",
    "esp32_mc_db"
);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

// ---------- READ JSON ----------
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['uid'], $input['class_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

$uid = strtoupper(trim($input['uid']));
$class_id = (int)$input['class_id'];

// ---------- FIND STUDENT ----------
$stmt = $conn->prepare("
    SELECT id, student_name
    FROM students
    WHERE student_uid = ?
    LIMIT 1
");
$stmt->bind_param("s", $uid);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    http_response_code(404);
    echo json_encode(["error" => "Unknown card"]);
    exit;
}

$student_id = $student['id'];

// ---------- VERIFY CLASS MEMBERSHIP ----------
$stmt = $conn->prepare("
    SELECT 1
    FROM student_classes
    WHERE student_id = ? AND class_id = ?
");
$stmt->bind_param("ii", $student_id, $class_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "Student not in class"]);
    exit;
}

// ---------- PREVENT DUPLICATE SCANS ----------
$stmt = $conn->prepare("
    SELECT 1
    FROM attendance
    WHERE student_id = ?
      AND class_id = ?
      AND DATE(timestamp) = CURDATE()
");
$stmt->bind_param("ii", $student_id, $class_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    http_response_code(200);
    echo json_encode([
        "status" => "already_marked",
        "student" => $student['student_name']
    ]);
    exit;
}

// ---------- INSERT ATTENDANCE ----------
$stmt = $conn->prepare("
    INSERT INTO attendance (student_id, class_id, status)
    VALUES (?, ?, 'Present')
");
$stmt->bind_param("ii", $student_id, $class_id);
$stmt->execute();

http_response_code(200);
echo json_encode([
    "status" => "success",
    "student" => $student['student_name']
]);
