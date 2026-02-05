<?php
session_start();
require_once 'db.php';

/* =========================
   ADMIN ACCESS ONLY
========================= */
if (!isset($_SESSION['teacher']) || $_SESSION['teacher']['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

/* =========================
   FILTERS
========================= */
$view  = $_GET['view'] ?? 'all';      // all | absent
$sort  = $_GET['sort'] ?? 'name';     // name | absences
$class_filter = $_GET['class'] ?? 'ALL';

/* =========================
   LOAD CLASSES
========================= */
$classes = $conn->query("
    SELECT id, class_name
FROM classes
ORDER BY class_name

")->fetch_all(MYSQLI_ASSOC);

/* =========================
   BUILD QUERY
========================= */
$sql = "
SELECT
    s.student_name,
    c.name AS class_name,
    COUNT(CASE WHEN a.present = 0 THEN 1 END) AS absences
FROM students s
JOIN student_classes sc ON s.id = sc.student_id
JOIN classes c ON sc.class_id = c.id
LEFT JOIN attendance a
  ON a.student_id = s.id
 AND a.class_id = c.id
";

$where = [];
$params = [];
$types  = "";

/* Class filter */
if ($class_filter !== 'ALL') {
    $where[] = "c.id = ?";
    $params[] = $class_filter;
    $types .= "i";
}

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " GROUP BY s.id, c.id";

/* Absent filter */
if ($view === 'absent') {
    $sql .= " HAVING absences > 0";
}

/* Sorting */
if ($sort === 'absences') {
    $sql .= " ORDER BY absences DESC";
} else {
    $sql .= " ORDER BY student_name ASC";
}

/* =========================
   EXECUTE
========================= */
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header class="top-bar">
  <h2>Administrator Dashboard</h2>
  <div>
    <a class="btn" href="add_student.php">➕ Add Student</a>
    <a class="logout-btn" href="logout.php">Logout</a>
  </div>
</header>

<div class="admin-container">

<!-- =========================
     CLASS FILTERS
========================= -->
<section class="admin-card admin-actions">
  <h3>Filter by Class</h3>

  <a class="btn" href="admin.php?class=ALL&view=<?=$view?>&sort=<?=$sort?>">
    All Classes
  </a>

  <?php foreach ($classes as $c): ?>
    <a class="btn"
       href="admin.php?class=<?=$c['id']?>&view=<?=$view?>&sort=<?=$sort?>">
       <?=htmlspecialchars($c['name'])?>
    </a>
  <?php endforeach; ?>
</section>

<!-- =========================
     VIEW & SORT
========================= -->
<section class="admin-card admin-actions">
  <h3>View</h3>
  <a class="btn" href="admin.php?view=all&class=<?=$class_filter?>&sort=<?=$sort?>">
    All Students
  </a>
  <a class="btn" href="admin.php?view=absent&class=<?=$class_filter?>&sort=<?=$sort?>">
    Absent Only
  </a>

  <h3 style="margin-top:20px;">Sort</h3>
  <a class="btn" href="admin.php?sort=name&class=<?=$class_filter?>&view=<?=$view?>">
    By Name
  </a>
  <a class="btn" href="admin.php?sort=absences&class=<?=$class_filter?>&view=<?=$view?>">
    By Absences
  </a>
</section>

<!-- =========================
     STUDENT TABLE
========================= -->
<section class="admin-card">
  <h3>Students</h3>

  <div class="admin-table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Student</th>
          <th>Class</th>
          <th>Absences</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$records): ?>
          <tr><td colspan="3">No records found</td></tr>
        <?php endif; ?>

        <?php foreach ($records as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['student_name'])?></td>
          <td><?=htmlspecialchars($r['class_name'])?></td>
          <td><?=$r['absences']?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

</div>

</body>
</html>
