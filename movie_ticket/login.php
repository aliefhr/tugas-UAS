<?php
session_start(); // Memulai sesi jika belum dilakukan

// Cek apakah pengguna sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Jika sudah login, arahkan ke halaman utama
    exit;
}

// Proses login jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db.php';
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Menggunakan prepared statement untuk mencegah SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username); // Menyiapkan query dengan parameter username
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Ambil data pengguna

        // Verifikasi password menggunakan password_verify (asumsi password disimpan dengan hash)
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; // Menyimpan ID pengguna dalam sesi
            $_SESSION['username'] = $user['username']; // Menyimpan username dalam sesi
            header("Location: index.php"); // Arahkan ke halaman utama
            exit;
        } else {
            $error = "Username atau password salah."; // Jika password tidak cocok
        }
    } else {
        $error = "Username atau password salah."; // Jika username tidak ditemukan
    }

    $stmt->close(); // Menutup prepared statement
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .form-title {
            margin-bottom: 20px;
            text-align: center;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .btn-login {
            width: 100%;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .link {
            display: block;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h3 class="form-title">Login</h3>

        <!-- Menampilkan pesan error jika ada -->
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary btn-login">Login</button>
        </form>

        <a href="lupa_password.php" class="link">Lupa Password?</a>
        <a href="register.php" class="link">Belum punya akun? Daftar sekarang</a>
    </div>
</body>
</html>
