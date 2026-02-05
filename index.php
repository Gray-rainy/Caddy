<?php
session_start();
require_once 'db.php'; // Make sure this is the fixed db.php

// Redirect if already logged in
if (isset($_SESSION['teacher'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        // Get teacher info
        $stmt = $conn->prepare("
            SELECT id, username, password, role
            FROM teachers
            WHERE username = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Check password (supports hashed passwords)
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['teacher'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            }
        }

        $error = "Invalid username or password.";
    } else {
        $error = "Please enter username and password.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Teacher Login</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

<div id="card">
  <div class="login-card">

    <?php if ($error): ?>
      <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post">
      <h2>Teacher Login</h2>
      <h3>Same login for Teachers and Administrators</h3>

      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>

      <button type="submit">Login</button>
    </form>

  </div>
</div>

</body>
</html>
