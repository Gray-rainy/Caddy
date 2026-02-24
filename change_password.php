<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['teacher'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];

    $stmt = $conn->prepare("
        SELECT password FROM teachers WHERE id = ?
    ");
    $stmt->bind_param("i", $_SESSION['teacher']['id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($current, $user['password'])) {
        $error = "Current password is incorrect.";
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            UPDATE teachers
            SET password = ?
            WHERE id = ?
        ");
        $stmt->bind_param("si", $hash, $_SESSION['teacher']['id']);
        $stmt->execute();

        $success = "Password updated successfully.";
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Change Password</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Change Password</h2>

<?php if ($error): ?>
  <p class="error"><?=$error?></p>
<?php endif; ?>

<?php if ($success): ?>
  <p class="success"><?=$success?></p>
<?php endif; ?>

<form method="post">
  <input type="password" name="current_password" placeholder="Current password" required>
  <input type="password" name="new_password" placeholder="New password" required>
  <button type="submit">Update Password</button>
</form>

<br>
<a href="dashboard.php">Back</a>

</body>
</html>
