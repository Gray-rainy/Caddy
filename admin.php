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
    s.id AS student_id,
    s.student_name,
    c.class_name,
    c.id AS class_id,
    COUNT(CASE WHEN a.status = 'Present' AND a.`date` = CURDATE() THEN 1 END) AS present_today,
    COUNT(CASE WHEN a.status = 'Absent'  THEN 1 END) AS total_absences,
    COUNT(CASE WHEN a.status = 'Present' THEN 1 END) AS total_present,
    COUNT(a.id) AS total_days
FROM students s
JOIN student_classes sc ON s.id = sc.student_id
JOIN classes c ON sc.class_id = c.id
LEFT JOIN attendance a
  ON a.student_id = s.id
 AND a.class_id   = c.id
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
    $sql .= " HAVING total_absences > 0";
}

/* Sorting */
if ($sort === 'absences') {
    $sql .= " ORDER BY total_absences DESC";
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
    <a class="btn" href="add_students.php">➕ Add Student</a>
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
       <?=htmlspecialchars($c['class_name'])?>
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
  <h3>Students — <?= date('F j, Y') ?></h3>

  <div class="admin-table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Student</th>
          <th>Class</th>
          <th>Today</th>
          <th>Total Absences</th>
          <th>Total Present</th>
          <th>Days Recorded</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$records): ?>
          <tr><td colspan="6">No records found</td></tr>
        <?php endif; ?>

        <?php foreach ($records as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['student_name'])?></td>
          <td><?=htmlspecialchars($r['class_name'])?></td>
          <td style="font-weight:700; color:<?= $r['present_today'] ? '#16a34a' : '#dc2626' ?>">
            <?= $r['present_today'] ? 'Present' : 'Absent' ?>
          </td>
          <td><?=$r['total_absences']?></td>
          <td><?=$r['total_present']?></td>
          <td><?=$r['total_days']?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- =========================
     PAST ATTENDANCE RECORDS
========================= -->
<?php
/* Load past records (all days except today) */
$history_sql = "
    SELECT
        s.student_name,
        c.class_name,
        a.`date`,
        a.status
    FROM attendance a
    JOIN students s ON s.id = a.student_id
    JOIN classes  c ON c.id = a.class_id
    WHERE a.`date` < CURDATE()
";
$history_params = [];
$history_types  = "";

if ($class_filter !== 'ALL') {
    $history_sql .= " AND a.class_id = ?";
    $history_params[] = $class_filter;
    $history_types   .= "i";
}

$history_sql .= " ORDER BY a.`date` DESC, s.student_name ASC";

$hstmt = $conn->prepare($history_sql);
if ($history_params) {
    $hstmt->bind_param($history_types, ...$history_params);
}
$hstmt->execute();
$history = $hstmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<section class="admin-card">
  <h3>Past Attendance Records</h3>

  <div class="admin-table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Student</th>
          <th>Class</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$history): ?>
          <tr><td colspan="4">No past records yet</td></tr>
        <?php endif; ?>

        <?php foreach ($history as $h): ?>
        <tr>
          <td><?= date('M j, Y', strtotime($h['date'])) ?></td>
          <td><?=htmlspecialchars($h['student_name'])?></td>
          <td><?=htmlspecialchars($h['class_name'])?></td>
          <td style="font-weight:700; color:<?= $h['status']==='Present' ? '#16a34a' : '#dc2626' ?>">
            <?=htmlspecialchars($h['status'])?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

</div>

</body>
</html>
