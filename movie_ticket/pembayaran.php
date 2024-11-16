<?php
include 'db.php';
session_start();

// Menggunakan null coalescing operator untuk menghindari peringatan
$film_id = $_GET['film_id'] ?? null;
$jumlah_tiket = $_GET['jumlah_tiket'] ?? null;
$nama_pembeli = $_GET['nama_pembeli'] ?? null; // Menambahkan untuk nama pembeli
$kursi = json_decode($_GET['kursi'] ?? '[]', true); // Menambahkan untuk kursi yang dipilih

// Ambil harga tiket dari database berdasarkan film_id
$harga_per_tiket = 0; // Inisialisasi harga

if ($film_id) {
    $stmt = $conn->prepare("SELECT harga FROM film WHERE id = ?");
    $stmt->bind_param("i", $film_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $harga_per_tiket = $row['harga']; // Ambil harga dari database
    } else {
        // Jika film tidak ditemukan, berikan nilai default atau tangani kesalahan
        echo "<script>alert('Film tidak ditemukan!');</script>";
        header("Location: index.php");
        exit();
    }
    
    $stmt->close();
}

// Hitung total harga
$total_harga = $jumlah_tiket * $harga_per_tiket;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Proses konfirmasi pembayaran
    echo "<script>alert('Pembayaran berhasil!');</script>";
    // Redirect ke halaman konfirmasi atau kembali ke halaman utama
    header("Location: index.php");
    exit();
}

//var_dump($kursi);

// Siapkan pesan untuk WhatsApp
$message = urlencode("Konfirmasi Pembayaran Tiket\n" .
    "Nama Pembeli: " . htmlspecialchars($nama_pembeli) . "\n" .
    "Jumlah Tiket: " . htmlspecialchars($jumlah_tiket) . "\n" .
    "Nomor Bangku: " . implode(", ", array_map('htmlspecialchars', $kursi)) . "\n" .
    "Harga per Tiket: Rp " . number_format($harga_per_tiket, 0, ',', '.') . "\n" .
    "Total Harga: Rp " . number_format($total_harga, 0, ',', '.'));

$whatsapp_url = "https://api.whatsapp.com/send?text=" . $message;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembayaran Tiket</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="text-center">
    <div class="container mt-5">
        <h1>Konfirmasi Pembayaran Tiket</h1>
        <div class="mt-4 p-4 bg-white rounded shadow">
            <p><strong>Nama Pembeli:</strong> <?php echo htmlspecialchars($nama_pembeli); ?></p>
            <p><strong>Jumlah Tiket:</strong> <?php echo htmlspecialchars($jumlah_tiket); ?></p>
            <p><strong>Nomor Bangku:</strong> <?php echo implode(", ", array_map('htmlspecialchars', $kursi)); ?></p>
            <p><strong>Harga per Tiket:</strong> Rp <?php echo number_format($harga_per_tiket, 0, ',', '.'); ?></p>
            <p><strong>Total Harga:</strong> Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></p>
            
            <form method="POST" action="">
                <input type="hidden" name="film_id" value="<?php echo htmlspecialchars($film_id); ?>">
                <input type="hidden" name="jumlah_tiket" value="<?php echo htmlspecialchars($jumlah_tiket); ?>">
                <input type="hidden" name="nama_pembeli" value="<?php echo htmlspecialchars($nama_pembeli); ?>">
                <input type="hidden" name="kursi" value="<?php echo htmlspecialchars(json_encode($kursi)); ?>">
                <input type="submit" class="btn btn-primary" value="Konfirmasi Pembayaran">
                <a href="download_tiket.php?film_id=<?php echo htmlspecialchars($film_id); ?>&jumlah_tiket=<?php echo htmlspecialchars($jumlah_tiket); ?>&nama_pembeli=<?php echo urlencode($nama_pembeli); ?>&kursi=<?php echo htmlspecialchars(json_encode($kursi)); ?>" class="btn btn-info" target="_blank">Download Tiket</a>
                <a href="<?php echo $whatsapp_url; ?>" class="btn btn-success" target="_blank">Kirim via WhatsApp</a>
            </form>
        </div>
        <br>
        <a href="index.php" class="btn btn-link">Kembali ke Daftar Film</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>
