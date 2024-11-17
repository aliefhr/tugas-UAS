<?php
session_start(); // Mulai sesi

include 'db.php'; // Menghubungkan ke database

// Cek apakah ada input pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Siapkan query SQL dengan kondisi pencarian
$sql = "SELECT * FROM film";
if ($search) {
    $search = $conn->real_escape_string($search); // Sanitasi input untuk mencegah SQL Injection
    $sql .= " WHERE judul LIKE '%$search%'"; // Menambahkan kondisi pencarian
}

$result = $conn->query($sql);

// Cek apakah query berhasil
if (!$result) {
    die("Query gagal: " . $conn->error); // Menampilkan pesan kesalahan jika query gagal
}

// Cek apakah tombol logout diklik
if (isset($_GET['logout'])) {
    session_destroy(); // Menghapus semua data sesi
    header("Location: index.php"); // Mengarahkan kembali ke halaman utama setelah logout
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Film</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .film-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .film-description {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            border: none;
        }

        .input-group {
            max-width: 500px;
            margin: auto;
        }

        .dropdown-menu a {
            color: #495057;
        }

        .dropdown-menu a:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-left">Daftar Film</h1>
            <div>
                <div class="dropdown">
                    <button class="btn navbar-toggler dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="index.php">Home</a>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a class="dropdown-item" href="login.php">Login</a>
                            <a class="dropdown-item" href="register.php">Registrasi</a>
                        <?php else: ?>
                            <span class="dropdown-item disabled">Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <a class="dropdown-item" href="index.php?logout=true">Logout</a>
                            <a class="dropdown-item" href="lihat_tiket.php">Lihat Tiket yang Dibeli</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pencarian -->
        <form action="" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari film..." value="<?php echo htmlspecialchars($search); ?>">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit">Cari</button>
                </div>
            </div>
        </form>

        <!-- Daftar Film -->
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($row['foto']); ?>" class="film-image card-img-top" alt="<?php echo htmlspecialchars($row['judul']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['judul']); ?></h5>
                            <p><strong>Durasi:</strong> <?php echo htmlspecialchars($row['durasi']); ?> menit</p>
                            <p><strong>Genre:</strong> <?php echo htmlspecialchars($row['genre']); ?></p>
                            <p><strong>Tanggal Tayang:</strong> <?php echo htmlspecialchars($row['tanggal_tayang']); ?></p>
                            <p><strong>Harga:</strong> Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                            <p class="film-description" title="<?php echo htmlspecialchars($row['deskripsi']); ?>">
                                <?php echo htmlspecialchars($row['deskripsi']); ?>
                            </p>
                            <div class="mt-auto">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="beli_tiket.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-primary btn-block">Beli Tiket</a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-warning btn-block">Login untuk Beli Tiket</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
