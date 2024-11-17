<?php
session_start();
include 'db.php'; // Pastikan db.php berada di direktori yang benar

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    // Jika belum login, arahkan ke halaman login
    header("Location: admin_login.php");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-container {
            max-width: 1000px;
            margin: auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-title {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .film-table th, .film-table td {
            vertical-align: middle;
        }
        .btn-custom {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
            border: none;
        }
        .btn-custom:hover {
            background: linear-gradient(to right, #5a0eb1, #1f62da);
        }
        .navbar-brand {
            font-weight: bold;
        }
        .navbar-light .navbar-nav .nav-link {
            color: #495057;
            font-weight: 500;
        }
        .navbar-light .navbar-nav .nav-link:hover {
            color: #2575fc;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">Dashboard Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_riwayat.php">Kelola Pembelian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_register.php">Daftar Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="dashboard-container mt-5">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Bagian Tambah Film -->
        <div class="mb-4">
            <h3 class="form-title">Tambah Film</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="judul" class="form-label">Judul</label>
                        <input type="text" class="form-control" name="judul" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="durasi" class="form-label">Durasi (menit)</label>
                        <input type="number" class="form-control" name="durasi" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="genre" class="form-label">Genre</label>
                        <input type="text" class="form-control" name="genre" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_tayang" class="form-label">Tanggal Tayang</label>
                        <input type="date" class="form-control" name="tanggal_tayang" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="harga" class="form-label">Harga (IDR)</label>
                    <input type="number" class="form-control" name="harga" required>
                </div>
                <div class="mb-3">
                    <label for="foto" class="form-label">Upload Foto</label>
                    <input type="file" class="form-control" name="foto" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" rows="3" required></textarea>
                </div>
                <button type="submit" name="add_film" class="btn btn-custom btn-block w-100">Tambah Film</button>
            </form>
        </div>

        <!-- Bagian Daftar Film -->
        <h3 class="form-title">Daftar Film</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover film-table">
                <thead class="table-dark">
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
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($film = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $film['id']; ?></td>
                                <td><?php echo htmlspecialchars($film['judul']); ?></td>
                                <td><?php echo htmlspecialchars($film['durasi']); ?> menit</td>
                                <td><?php echo htmlspecialchars($film['genre']); ?></td>
                                <td><?php echo htmlspecialchars($film['tanggal_tayang']); ?></td>
                                <td>Rp <?php echo number_format($film['harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="film_id" value="<?php echo $film['id']; ?>">
                                        <input type="number" name="new_price" placeholder="Harga Baru" required class="form-control form-control-sm d-inline w-auto">
                                        <button type="submit" name="update_price" class="btn btn-warning btn-sm">Ubah Harga</button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="film_id" value="<?php echo $film['id']; ?>">
                                        <button type="submit" name="delete_film" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada film yang tersedia.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
