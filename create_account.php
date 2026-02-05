<?php
require_once 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($username && $password && $role) {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO teachers (username, password, role)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sss", $username, $hash, $role);

        if ($stmt->execute()) {
            $message = "✅ Account created successfully!";
        } else {
            $message = "❌ Username already exists.";
        }

    } else {
        $message = "❌ All fields are required.";
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Create Account (Temporary)</title>
<style>
body { font-family: Arial; background:#f4f4f4; }
.card {
  width: 320px; margin: 80px auto; padding: 20px;
  background: white; border-radius: 8px;
}
input, select, button {
  width: 100%; padding: 8px; margin: 6px 0;
}
.success { color: green; }
.error { color: red; }
</style>
</head>
<body>

<div class="card">
  <h2>Create Account</h2>

  <?php if ($message): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>

  <form method="post">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>

    <select name="role" required>
      <option value="teacher">Teacher</option>
      <option value="admin">Admin</option>
    </select>

    <button type="submit">Create Account</button>
  </form>

  <p style="margin-top:15px;font-size:12px;color:#666;">
    ⚠️ Delete this file after creating accounts
  </p>
</div>

</body>
</html>
