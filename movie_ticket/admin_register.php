<?php
session_start();
include 'db.php'; // Pastikan db.php berada di direktori yang benar

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_karyawan = $conn->real_escape_string($_POST['id_karyawan']); // Ambil ID Karyawan
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash password

    // Cek apakah username sudah ada
    $result = $conn->query("SELECT * FROM admin WHERE username='$username'");
    if ($result->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {
        // Tambahkan admin baru ke database
        $conn->query("INSERT INTO admin (id_karyawan, username, password) VALUES ('$id_karyawan', '$username', '$hashed_password')");
        $success = "Admin berhasil didaftarkan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Admin</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .registration-container {
            max-width: 400px;
            margin: auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-title {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="registration-container mt-5">
        <h2 class="form-title">Pendaftaran Admin Baru</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="id_karyawan">ID Karyawan</label>
                <input type="number" class="form-control" name="id_karyawan" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Daftar</button>
        </form>
        
        <!-- Tombol untuk menuju halaman login admin -->
        <div class="mt-3 text-center">
            <a href="admin_login.php" class="btn btn-link">Sudah punya akun? Login di sini</a>
        </div>
    </div>
</body>
</html>
