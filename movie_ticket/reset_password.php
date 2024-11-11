<?php
include 'db.php';

// Ambil token dari URL
$token = $_GET['token'] ?? '';

// Cek apakah token valid dan belum kedaluwarsa
if ($token) {
    $sql = "SELECT * FROM users WHERE reset_token = '$token' AND reset_token_expiry > NOW()";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        echo "Link reset password tidak valid atau telah kedaluwarsa.";
        exit;
    }
}

// Proses jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];

    // Perbarui password pengguna
    $sql = "UPDATE users SET password = '$password', reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = '$token'";
    $conn->query($sql);

    echo "Password Anda telah berhasil diubah.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>
    <form method="POST">
        <label for="password">Password Baru:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
