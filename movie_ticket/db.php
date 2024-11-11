<?php
$host = 'localhost';
$user = 'root'; // ganti dengan username database Anda
$password = ''; // ganti dengan password database Anda
$dbname = 'bioskop';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>