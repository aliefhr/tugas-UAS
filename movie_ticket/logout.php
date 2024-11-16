<?php
session_start();
session_destroy(); // Menghancurkan semua sesi
header("Location: admin_login.php"); // Arahkan ke halaman login admin
exit;
?>
