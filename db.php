<?php
$host = "localhost";
$user = "caddy";
$pass = "bada289c076c8f8faef5f63a840e5c5ed467a37506179e075c1da5d6e65b94bb";
$db   = "esp32_mc_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed");
}
?>
