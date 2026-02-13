<?php
// config/database.php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'novel_db';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Jakarta');
?>
