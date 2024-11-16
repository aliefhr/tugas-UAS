<?php
include 'db.php';
session_start();

// Pastikan user_id ada di session
if (!isset($_SESSION['user_id'])) {
    // Redirect atau tampilkan pesan error
    die("Anda harus login untuk melihat data pembelian.");
}

$user_id = $_SESSION['user_id']; // Ambil ID pengguna dari session

// Ambil data tiket yang sudah dibeli dari database
$stmt = $conn->prepare("SELECT nama_pembeli, jumlah_tiket, kursi, DATE(created_at) as tanggal_pembelian, 
                         (jumlah_tiket * (SELECT harga FROM film WHERE film.id = tiket.film_id)) AS total_harga 
                         FROM tiket 
                         WHERE created_at IS NOT NULL AND user_id = ? 
                         ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id); // "i" untuk integer
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tiket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
        }
        .ticket {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 20px;
            margin: 15px 0;
            transition: transform 0.3s;
        }
        .ticket:hover {
            transform: scale(1.02);
        }
        .ticket h5 {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .ticket .details {
            font-size: 14px;
            color: #555;
        }
        .total {
            font-weight: bold;
            font-size: 18px;
            color: #28a745;
            margin-top: 10px;
        }
        .alert {
            background-color: rgba(255, 193, 7, 0.8);
            font-size: 16px;
        }
        .btn-primary {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(to left, #6a11cb, #2575fc);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center text-black mb-4 ">Konfirmasi Pembelian Tiket</h1>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='ticket'>";
                echo "<h5><i class='bi bi-person-circle'></i> Nama Pembeli: " . htmlspecialchars($row['nama_pembeli']) . "</h5>";
                echo "<div class='details'><i class='bi bi-ticket-detailed'></i> Jumlah Tiket: " . htmlspecialchars($row['jumlah_tiket']) . "</div>";
                echo "<div class='details'><i class='bi bi-chair'></i> Kursi: " . htmlspecialchars($row['kursi']) . "</div>"; 
                echo "<div class='details'><i class='bi bi-calendar-event'></i> Tanggal Pembelian: " . htmlspecialchars($row['tanggal_pembelian']) . "</div>";
                echo "<div class='total'><i class='bi bi-currency-dollar'></i> Total Harga: Rp " . number_format($row['total_harga'], 0, ',', '.') . "</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='alert alert-warning text-center'>Belum ada data tiket yang telah dibeli.</div>";
        }
        ?>
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary px-4 py-2">Kembali ke Daftar Film</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php 
$stmt->close(); 
$conn->close(); 
?>
