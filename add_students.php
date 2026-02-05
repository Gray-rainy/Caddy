<?php
session_start();
require_once 'db.php';

/* Admin only */
if (!isset($_SESSION['teacher']) || $_SESSION['teacher']['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

/* Load classes */
$classes = $conn->query("
    SELECT id, name
    FROM classes
    ORDER BY name
")->fetch_all(MYSQLI_ASSOC);

/* Handle form submit */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['student_name']);
    $uid  = trim($_POST['student_uid']);
    $assigned = $_POST['classes'] ?? [];

    if ($name && $uid) {
        /* Insert student */
        $stmt = $conn->prepare("
            INSERT INTO students (student_name, student_uid)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ss", $name, $uid);
        $stmt->execute();

        $student_id = $stmt->insert_id;

        /* Assign classes */
        if ($assigned) {
            $stmt = $conn->prepare("
                INSERT INTO student_classes (student_id, class_id)
                VALUES (?, ?)
            ");
            foreach ($assigned as $cid) {
                $stmt->bind_param("ii", $student_id, $cid);
                $stmt->execute();
            }
        }

        header("Location: admin.php");
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Add Student</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header class="top-bar">
  <h2>Add New Student</h2>
  <a class="logout-btn" href="admin.php">← Back</a>
</header>

<div class="admin-container">
<section class="admin-card">

<form method="post">
  <label><strong>Student Name</strong></label><br>
  <input type="text" name="student_name" required><br><br>

  <label><strong>RFID UID</strong></label><br>
  <input type="text" name="student_uid" placeholder="Scan card UID" required><br><br>

  <label><strong>Assign Classes</strong></label><br>
  <?php foreach ($classes as $c): ?>
    <label style="display:block;">
      <input type="checkbox" name="classes[]" value="<?=$c['id']?>">
      <?=htmlspecialchars($c['name'])?>
    </label>
  <?php endforeach; ?>

  <br>
  <button class="btn">Add Student</button>
</form>

</section>
</div>

</body>
</html>
