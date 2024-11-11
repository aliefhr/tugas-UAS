<?php
session_start(); // Mulai sesi

include 'db.php';

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
    <style>
        .film-image {
            width: 100%;
            height: 300px; /* Tetapkan tinggi gambar */
            object-fit: cover; /* Pastikan gambar terisi penuh tanpa mendistorsi */
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
        .table {
            border: none;
        }
        .table td {
            border: none;
        }
        .film-info {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
        }
        .film-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .film-actions {
            text-align: right;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-left">Daftar Film</h1>
            <div>
                <a href="index.php" class="btn btn-secondary mr-2">Home</a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="btn btn-primary mr-2">Login</a>
                    <a href="register.php" class="btn btn-success">Registrasi</a>
                <?php else: ?>
                    <span class="btn btn-info">Selamat datang, <?php echo $_SESSION['username']; ?></span>
                    <a href="index.php?logout=true" class="btn btn-danger ml-2">Logout</a>
                <?php endif; ?>
            </div>
        </div>

        <form action="" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari film..." aria-label="Cari film..." value="<?php echo htmlspecialchars($search); ?>">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit">Cari</button>
                </div>
            </div>
        </form>

        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4"> <!-- Ubah menjadi col-md-4 untuk membuat 3 kolom -->
                    <div class="card h-100"> <!-- Tambahkan h-100 untuk membuat tinggi card sama -->
                        <h5 class="card-title p-3"><?php echo htmlspecialchars($row['judul']); ?></h5>
                        <img src="<?php echo $row['foto']; ?>" class="film-image card-img-top" alt="<?php echo htmlspecialchars($row['judul']); ?>">
                        <div class="card-body d-flex flex-column">
                            <div class="film-details flex-grow-1">
                                <div>
                                    <p><strong>Durasi:</strong> <?php echo htmlspecialchars($row['durasi']); ?> menit</p>
                                    <p><strong>Genre:</strong> <?php echo htmlspecialchars($row['genre']); ?></p>
                                    <p><strong>Tanggal Tayang:</strong> <?php echo htmlspecialchars($row['tanggal_tayang']); ?></p>
                                    <p><strong>Harga:</strong> Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                            <div class="film-description mb-3" title="<?php echo htmlspecialchars($row['deskripsi']); ?>">
                                <?php echo htmlspecialchars($row['deskripsi']); ?>
                            </div>
                            <div class="film-actions mt-auto">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="beli_tiket.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-block">Beli Tiket</a>
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