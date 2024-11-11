<?php
include 'db.php';
session_start();

$film_id = $_GET['film_id'] ?? null;
$jumlah_tiket = $_GET['jumlah_tiket'] ?? null;
$nama_pembeli = $_GET['nama_pembeli'] ?? null;
$kursi = json_decode($_GET['kursi'] ?? '[]', true);

// Ambil harga tiket dari database
$harga_per_tiket = 0;

if ($film_id) {
    $stmt = $conn->prepare("SELECT harga FROM film WHERE id = ?");
    $stmt->bind_param("i", $film_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $harga_per_tiket = $row['harga'];
    }
    
    $stmt->close();
}

$total_harga = $jumlah_tiket * $harga_per_tiket;

// Mulai output buffering
ob_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tiket Pembelian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .ticket-container {
            width: 100%;
            text-align: center;
            padding: 30px;
        }
        .ticket {
            border: 1px solid #000;
            padding: 20px;
            margin: 20px auto;
            width: 80%;
            text-align: center;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            margin: 5px 0;
        }
        .detail-item {
            margin: 10px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="ticket">
            <h1>Konfirmasi Pembayaran Tiket</h1>
            <p><strong>Nama Pembeli:</strong> <?php echo htmlspecialchars($nama_pembeli); ?></p>
            <p><strong>Jumlah Tiket:</strong> <?php echo htmlspecialchars($jumlah_tiket); ?></p>
            <p><strong>Nomor Bangku:</strong> <?php echo implode(", ", array_map('htmlspecialchars', $kursi)); ?></p>
            <p><strong>Harga per Tiket:</strong> Rp <?php echo number_format($harga_per_tiket, 0, ',', '.'); ?></p>
            <p><strong>Total Harga:</strong> Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></p>
        </div>
    </div>
</body>
</html>

<?php
$html = ob_get_clean();

// Set header untuk mengunduh file
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="tiket_pembelian.pdf"');

// Gunakan library mPDF untuk mengkonversi HTML ke PDF
require_once __DIR__ . '/vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf(['format' => 'A4', 'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 10]);
$mpdf->WriteHTML($html);
$mpdf->Output('tiket_pembelian.pdf', 'D');

$conn->close();
exit();