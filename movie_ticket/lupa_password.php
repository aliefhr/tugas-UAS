<?php
// Cek apakah form sudah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db.php';
    $email = $_POST['email'];

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Cek apakah email ada di database
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Menghasilkan kode verifikasi atau token untuk reset password
            $token = bin2hex(random_bytes(50));
            $expires = date("U") + 3600; // Token berlaku selama 1 jam

            // Simpan token dan waktu kedaluwarsa
            $sql = "UPDATE users SET reset_token = '$token', reset_expires = '$expires' WHERE email = '$email'";
            if ($conn->query($sql)) {
                // Kirimkan email dengan link reset
                $resetLink = "http://yourdomain.com/reset_password.php?token=$token";
                mail($email, "Reset Password", "Klik link berikut untuk mereset password: $resetLink");

                $message = "Email untuk mereset password telah dikirim!";
            }
        } else {
            $error = "Email tidak ditemukan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .reset-container {
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
        .btn-reset {
            width: 100%;
        }
        .error-message, .success-message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .success-message {
            color: green;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h3 class="form-title">Lupa Password</h3>

        <!-- Menampilkan pesan error jika ada -->
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Menampilkan pesan sukses -->
        <?php if (isset($message)): ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <p>Masukkan alamat email Anda, kami akan mengirimkan link untuk mereset password Anda.</p>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary btn-reset">Kirim Link Reset Password</button>
        </form>

        <a href="login.php" class="d-block text-center mt-3">Kembali ke Login</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
