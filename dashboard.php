<?php
session_start();
if (!isset($_SESSION['teacher'])) {
    header("Location: index.php");
    exit;
}

require_once 'db.php';
$teacher = $_SESSION['teacher'];

/* =========================
   LOAD CLASSES
========================= */
$classes = $conn->query("
    SELECT id, class_name, start_time
    FROM classes
    ORDER BY start_time
")->fetch_all(MYSQLI_ASSOC);

if (!$classes) die("No classes found.");

$active_class_id = (int)($_GET['class_id'] ?? $classes[0]['id']);

/* =========================
   LOAD STUDENTS FOR CLASS
========================= */
$stmt = $conn->prepare("
    SELECT
        s.id,
        s.student_name,
        sc.seat_x,
        sc.seat_y,
        COALESCE(a.status, 'Absent') AS status
    FROM students s
    JOIN student_classes sc ON sc.student_id = s.id
    LEFT JOIN attendance a
      ON a.student_id = s.id
     AND a.class_id   = sc.class_id
     AND a.`date`     = CURDATE()
    WHERE sc.class_id = ?
    ORDER BY sc.seat_y ASC, sc.seat_x ASC
");
$stmt->bind_param("i", $active_class_id);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$present = count(array_filter($students, fn($s) => $s['status'] === 'Present'));
$total   = count($students);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Attendance Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
  <h1>Attendance Dashboard</h1>
  <div style="display:flex;gap:12px;align-items:center;">
    <span style="font-weight:600;">👤 <?=htmlspecialchars($teacher['username'])?></span>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</div>

<!-- CLASS TABS -->
<div class="class-tabs">
  <?php foreach ($classes as $c): ?>
    <a href="dashboard.php?class_id=<?=$c['id']?>"
       class="<?=($c['id']==$active_class_id)?'active':''?>">
       <?=htmlspecialchars($c['class_name'])?>
    </a>
  <?php endforeach; ?>
</div>

<!-- DASHBOARD BODY -->
<div class="dashboard-wrapper">

  <div class="dashboard-header">
    <div style="font-size:0.85rem;color:#555;">
      Drag seats anywhere · Click to toggle attendance
    </div>
    <div class="attendance-legend" id="legend">
      <span><span class="legend-dot" style="background:#7ef18c;"></span>Present: <?=$present?></span>
      <span><span class="legend-dot" style="background:#f56a6a;"></span>Absent: <?=$total-$present?></span>
      <span style="color:#888;">Total: <?=$total?></span>
    </div>
  </div>

  <!-- CLASSROOM CANVAS -->
  <div class="classroom" id="classroom">

    <!-- Teacher's desk marker -->
    <div class="teacher-desk">📋 Teacher</div>

    <?php foreach ($students as $s): ?>
    <div
      class="seat <?= $s['status'] === 'Present' ? 'present' : 'absent' ?>"
      data-student="<?= $s['id'] ?>"
      data-class="<?= $active_class_id ?>"
      style="left:<?= $s['seat_x'] ?>%;top:<?= $s['seat_y'] ?>%;"
      title="<?= htmlspecialchars($s['student_name']) ?>"
    >
      <?= htmlspecialchars($s['student_name']) ?>
    </div>
    <?php endforeach; ?>

  </div>

</div>

<script>
const classroom = document.getElementById('classroom');
let dragging = null;
let dragOffsetX = 0;
let dragOffsetY = 0;
let moved = false;

document.querySelectorAll('.seat').forEach(seat => {

  seat.addEventListener('mousedown', e => {
    e.preventDefault();
    dragging = seat;
    moved = false;

    const rect = seat.getBoundingClientRect();
    dragOffsetX = e.clientX - rect.left;
    dragOffsetY = e.clientY - rect.top;

    seat.classList.add('dragging');
    seat.style.zIndex = 1000;
  });

  /* Click = toggle attendance (only if not dragged) */
  seat.addEventListener('click', () => {
    if (moved) return;

    fetch('toggle_attendance.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        student_id: parseInt(seat.dataset.student),
        class_id:   parseInt(seat.dataset.class)
      })
    })
    .then(r => r.json())
    .then(data => {
      seat.classList.toggle('present', data.status === 'Present');
      seat.classList.toggle('absent',  data.status === 'Absent');
      updateCounts();
    });
  });

});

document.addEventListener('mousemove', e => {
  if (!dragging) return;
  moved = true;

  const box   = classroom.getBoundingClientRect();
  const seatW = dragging.offsetWidth;
  const seatH = dragging.offsetHeight;

  /* Position in px, clamped inside classroom */
  let x = e.clientX - box.left - dragOffsetX;
  let y = e.clientY - box.top  - dragOffsetY;

  x = Math.max(0, Math.min(x, box.width  - seatW));
  y = Math.max(0, Math.min(y, box.height - seatH));

  /* Convert to percentage so it's resolution-independent */
  const xPct = (x / box.width)  * 100;
  const yPct = (y / box.height) * 100;

  dragging.style.left = xPct + '%';
  dragging.style.top  = yPct + '%';

  dragging.dataset.xPct = xPct;
  dragging.dataset.yPct = yPct;
});

document.addEventListener('mouseup', () => {
  if (!dragging) return;

  if (moved) {
    /* Save final position */
    fetch('save_seat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        student_id: parseInt(dragging.dataset.student),
        class_id:   parseInt(dragging.dataset.class),
        x: Math.round(parseFloat(dragging.dataset.xPct)),
        y: Math.round(parseFloat(dragging.dataset.yPct))
      })
    });
  }

  dragging.classList.remove('dragging');
  dragging.style.zIndex = '';
  dragging = null;
});

function updateCounts() {
  const seats   = document.querySelectorAll('.seat');
  const present = [...seats].filter(s => s.classList.contains('present')).length;
  const total   = seats.length;
  document.getElementById('legend').innerHTML =
    `<span><span class="legend-dot" style="background:#7ef18c;"></span>Present: ${present}</span>
     <span><span class="legend-dot" style="background:#f56a6a;"></span>Absent: ${total - present}</span>
     <span style="color:#888;">Total: ${total}</span>`;
}
</script>

</body>
</html>
