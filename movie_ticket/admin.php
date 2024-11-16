<?php
session_start();
include 'db.php'; // Pastikan db.php berada di direktori yang benar

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    // Jika belum login, arahkan ke halaman login
    header("Location: admin_login.php"); // Ganti 'login.php' dengan nama file halaman login Anda
    exit;
}

// Inisialisasi variabel error
$error = '';

// Proses tambah, hapus, dan ubah film
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tambah film
    if (isset($_POST['add_film'])) {
        $judul = $conn->real_escape_string($_POST['judul']);
        $durasi = (int)$_POST['durasi'];
        $genre = $conn->real_escape_string($_POST['genre']);
        $tanggal_tayang = $conn->real_escape_string($_POST['tanggal_tayang']);
        $harga = (int)$_POST['harga'];
        $foto = $conn->real_escape_string($_POST['foto']);
        $deskripsi = $conn->real_escape_string($_POST['deskripsi']);

        if ($conn->query("INSERT INTO film (judul, durasi, genre, tanggal_tayang, harga, foto, deskripsi) VALUES ('$judul', $durasi, '$genre', '$tanggal_tayang', $harga, '$foto', '$deskripsi')")) {
            // Tambahan sukses dapat ditangani di sini jika perlu
        } else {
            $error = "Gagal menambahkan film: " . $conn->error;
        }
    }

    // Hapus film
    if (isset($_POST['delete_film'])) {
        $id = (int)$_POST['film_id'];
        if (!$conn->query("DELETE FROM film WHERE id=$id")) {
            $error = "Gagal menghapus film: " . $conn->error;
        }
    }

    // Ubah harga film
    if (isset($_POST['update_price'])) {
        $id = (int)$_POST['film_id'];
        $harga = (int)$_POST['new_price'];
        if (!$conn->query("UPDATE film SET harga=$harga WHERE id=$id")) {
            $error = "Gagal mengubah harga: " . $conn->error;
        }
    }
}

// Ambil daftar film
$result = $conn->query("SELECT * FROM film");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-container {
            max-width: 800px;
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
        .film-table th, .film-table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="dashboard-container mt-5">
        <h2>Dashboard Admin</h2>
        <h3>Tambah Film</h3>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="judul">Judul</label>
                <input type="text" class="form-control" name="judul" required>
            </div>
            <div class="form-group">
                <label for="durasi">Durasi (menit)</label>
                <input type="number" class="form-control" name="durasi" required>
            </div>
            <div class="form-group">
                <label for="genre">Genre</label>
                <input type="text" class="form-control" name="genre" required>
            </div>
            <div class="form-group">
                <label for="tanggal_tayang">Tanggal Tayang</label>
                <input type="date" class="form-control" name="tanggal_tayang" required>
            </div>
            <div class="form-group">
                <label for="harga">Harga (IDR)</label>
                <input type="number" class="form-control" name="harga" required>
            </div>
            <div class="form-group">
                <label for="foto">URL Foto</label>
                <input type="text" class="form-control" name="foto" required>
            </div>
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea class="form-control" name="deskripsi" required></textarea>
            </div>
            <button type="submit" name="add_film" class="btn btn-success btn-block">Tambah Film</button>
        </form>
        <h3>Daftar Film</h3>
        <table class="table film-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Durasi</th>
                    <th>Genre</th>
                    <th>Tanggal Tayang</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($film = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $film['id']; ?></td>
                        <td><?php echo $film['judul']; ?></td>
                        <td><?php echo $film['durasi']; ?> menit</td>
                        <td><?php echo $film['genre']; ?></td>
                        <td><?php echo $film['tanggal_tayang']; ?></td>
                        <td><?php echo $film['harga']; ?> IDR</td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="film_id" value="<?php echo $film['id']; ?>">
                                <input type="number" name="new_price" placeholder="Harga Baru" required class="form-control form-control-sm d-inline" style="width: 120px;">
                                <button type="submit" name="update_price" class="btn btn-warning btn-sm">Ubah Harga</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="film_id" value="<?php echo $film['id']; ?>">
                                <button type="submit" name="delete_film" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="mt-3 text-center">
            <a href="logout.php" class="btn btn-danger mt-3">Logout</a>
            <a href="admin_register.php" class="btn btn-link">Belum punya akun? Daftar di sini</a>
        </div>
    </div>
</body>
</html>
