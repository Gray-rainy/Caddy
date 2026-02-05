<?php
session_start();
if (!isset($_SESSION['teacher'])) {
    header("Location: index.php");
    exit;
}

require_once 'db.php';
$teacher = $_SESSION['teacher'];

/* =========================
   MARK PRESENT (MANUAL)
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['class_id'])) {
    $stmt = $conn->prepare("
        INSERT INTO attendance (student_id, class_id, status)
        VALUES (?, ?, 'Present')
        ON DUPLICATE KEY UPDATE status = 'Present'
    ");
    $stmt->bind_param("ii", $_POST['student_id'], $_POST['class_id']);
    $stmt->execute();

    header("Location: dashboard.php?class_id=".$_POST['class_id']);
    exit;
}

/* =========================
   LOAD CLASSES
========================= */
$classes = $conn->query("
    SELECT id, class_name, start_time
    FROM classes
    ORDER BY start_time
")->fetch_all(MYSQLI_ASSOC);

if (!$classes) {
    die("No classes found.");
}

$active_class_id = $_GET['class_id'] ?? $classes[0]['id'];

/* =========================
   LOAD STUDENTS FOR CLASS
========================= */
$stmt = $conn->prepare("
    SELECT
        s.id,
        s.student_name,
        COALESCE(a.status,'Absent') AS status
    FROM students s
    JOIN student_classes sc ON sc.student_id = s.id
    LEFT JOIN attendance a
      ON a.student_id = s.id
     AND a.class_id = sc.class_id
     AND DATE(a.timestamp) = CURDATE()
    WHERE sc.class_id = ?
    ORDER BY s.student_name
");
$stmt->bind_param("i", $active_class_id);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Attendance Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="top-bar">
  <h1>Attendance Dashboard</h1>
  <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="container">

<p>Welcome <strong><?=htmlspecialchars($teacher['username'])?></strong></p>

<!-- CLASS TABS -->
<div class="class-tabs">
<?php foreach ($classes as $c): ?>
  <a href="dashboard.php?class_id=<?=$c['id']?>"
     class="<?=($c['id']==$active_class_id)?'active':''?>">
     <?=htmlspecialchars($c['class_name'])?>
  </a>
<?php endforeach; ?>
</div>

<!-- SEATING GRID -->
<div class="attendance-grid" data-class="<?=$active_class_id?>">
<?php foreach ($students as $s): ?>
<div
  class="seat <?= ($s['status'] === 'Present') ? 'present' : 'absent'; ?>"
  draggable="true"
  data-student="<?= $s['id']; ?>"
  data-class="<?= $active_class_id; ?>"
>
  <?= htmlspecialchars($s['student_name']); ?>
</div>


<?php endforeach; ?>
</div>

</div>

<!-- =========================
     DRAG & DROP SCRIPT
========================= -->
<script>
let draggedSeat = null;

document.querySelectorAll('.seat').forEach(seat => {

  seat.addEventListener('dragstart', () => {
    draggedSeat = seat;
    seat.classList.add('dragging');
  });

  seat.addEventListener('dragend', () => {
    seat.classList.remove('dragging');
  });

  seat.addEventListener('dragover', e => {
    e.preventDefault();
    seat.classList.add('over');
  });

  seat.addEventListener('dragleave', () => {
    seat.classList.remove('over');
  });

  seat.addEventListener('drop', e => {
    e.preventDefault();
    seat.classList.remove('over');

    if (!draggedSeat || draggedSeat === seat) return;

    const tempHTML = seat.innerHTML;
    seat.innerHTML = draggedSeat.innerHTML;
    draggedSeat.innerHTML = tempHTML;

    const tempID = seat.dataset.student;
    seat.dataset.student = draggedSeat.dataset.student;
    draggedSeat.dataset.student = tempID;

    saveSeat(seat);
    saveSeat(draggedSeat);
  });
});

function saveSeat(seat) {
  fetch('save_seat.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      student_id: seat.dataset.student,
      class_id: seat.dataset.class,
      row: seat.dataset.row,
      col: seat.dataset.col
    })
  });
}
document.querySelectorAll('.seat').forEach(seat => {

  seat.addEventListener('click', e => {

    // Prevent click when dragging
    if (seat.classList.contains('dragging')) return;

    fetch('toggle_attendance.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        student_id: seat.dataset.student,
        class_id: seat.dataset.class
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'Present') {
        seat.classList.remove('absent');
        seat.classList.add('present');
      } else {
        seat.classList.remove('present');
        seat.classList.add('absent');
      }
    });
  });

});

</script>

</body>
</html>
