<?php
include 'db.php';
session_start();

// Pastikan user_id ada di session
if (!isset($_SESSION['user_id'])) {
    die("Anda harus login untuk melihat data pembelian.");
}

$user_id = $_SESSION['user_id']; // Ambil ID pengguna dari session

// Ambil data tiket yang sudah dibeli dari database
$stmt = $conn->prepare("SELECT tiket.id, nama_pembeli, kursi, DATE(created_at) as tanggal_pembelian, 
                         SUM(jumlah_tiket) AS total_tiket,
                         SUM(jumlah_tiket * (SELECT harga FROM film WHERE film.id = tiket.film_id)) AS total_harga,
                         status
                         FROM tiket 
                         WHERE created_at IS NOT NULL AND user_id = ? 
                         GROUP BY tiket.id, nama_pembeli, kursi, tanggal_pembelian, status
                         ORDER BY tanggal_pembelian DESC");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Proses pembatalan tiket via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_tiket'])) {
    $tiket_id = (int)$_POST['tiket_id'];
    error_log("Tiket ID: " . $tiket_id);

    // Hapus tiket dari database
    $delete_stmt = $conn->prepare("DELETE FROM tiket WHERE id = ?");
    $delete_stmt->bind_param("i", $tiket_id);
    
    if ($delete_stmt->execute()) {
        echo "success"; // Mengembalikan respons sukses
    } else {
        echo "error";  // Menampilkan pesan error jika penghapusan gagal
    }
}

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
        .ticket {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 20px;
            margin: 15px 0;
            position: relative; /* Tambahkan ini agar status bisa diposisikan relatif terhadap tiket */
            transition: transform 0.3s;
        }

        .ticket:hover {
            transform: scale(1.02);
        }

        /* Gaya untuk status */
        .ticket .status {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #28a745; /* Warna latar belakang status, bisa disesuaikan */
            color: white;
            padding: 5px 10px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Agar terlihat seperti gambar */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ticket .status.dibatalkan {
            background-color: #dc3545; /* Warna merah untuk status dibatalkan */
        }

        .ticket .status.pending {
            background-color: #ffc107; /* Warna kuning untuk status pending */
        }

        .ticket .status.dibayar {
            background-color: #007bff; /* Warna biru untuk status dibayar */
        }

        .ticket .status.selesai {
            background-color: #28a745; /* Warna hijau untuk status selesai */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center text-black mb-4 ">Konfirmasi Pembelian Tiket</h1>
        <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='ticket' id='ticket_" . $row['id'] . "'>";
                    echo "<h5><i class='bi bi-person-circle'></i> Nama Pembeli: " . htmlspecialchars($row['nama_pembeli']) . "</h5>";
                    echo "<div class='details'><i class='bi bi-ticket-detailed'></i> Jumlah Tiket: " . htmlspecialchars($row['total_tiket']) . "</div>";
                    echo "<div class='details'><i class='bi bi-chair'></i> Kursi: " . htmlspecialchars($row['kursi']) . "</div>"; 
                    echo "<div class='details'><i class='bi bi-calendar-event'></i> Tanggal Pembelian: " . htmlspecialchars($row['tanggal_pembelian']) . "</div>";
                    echo "<div class='total'><i class='bi bi-currency-dollar'></i> Total Harga: Rp " . number_format($row['total_harga'], 0, ',', '.') . "</div>";
                    
                    // Menampilkan Status dengan kelas yang sesuai
                    echo "<div class='status " . strtolower($row['status']) . "'>";
                    echo "<i class='bi bi-info-circle'></i> " . htmlspecialchars($row['status']);
                    echo "</div>";
                    
                    if ($row['status'] == 'pending') {
                        echo "<button class='btn btn-danger btn-sm' onclick='cancelTicket(" . $row['id'] . ")'>Batalkan Tiket</button>";
                    }

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

    <script>
        function cancelTicket(tiketId) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "lihat_tiket.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                console.log("Status: " + xhr.status); // Tambahkan log status
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                    if (xhr.responseText === "success") {
                        const ticketElement = document.getElementById('lihat_tiket' + tiketId);
                        ticketElement.remove(); // Menghapus elemen tiket dari DOM
                    } else {
                        alert("Gagal membatalkan tiket.");
                    }
                } else {
                    alert("Terjadi kesalahan: " + xhr.status); // Menampilkan pesan kesalahan
                }
            };
            xhr.send("cancel_tiket=true&tiket_id=" + tiketId); // Kirim data ke server
        }
    </script>
</body>
</html>

<?php 
$stmt->close(); 
$conn->close(); 
?>
