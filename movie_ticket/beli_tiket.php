<?php
include 'db.php';
session_start();

$film_id = $_GET['id'] ?? null;
$film = null;
$kursi_terisi = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug untuk melihat data kursi yang dikirim ke server
    //echo "<pre>";
    //print_r($_POST['kursi']); // Ini akan menampilkan data kursi yang dikirim
    //echo "</pre>";
    
    $film_id = $_POST['film_id'];
    $nama_pembeli = $_POST['nama_pembeli'];
    $jumlah_tiket = $_POST['jumlah_tiket'];
    $kursi_terpilih = json_decode($_POST['kursi'], true) ?? [];

    // Pastikan jumlah kursi yang dipilih sesuai dengan jumlah tiket
    if (is_array($kursi_terpilih) && count($kursi_terpilih) == $jumlah_tiket) {
        foreach ($kursi_terpilih as $kursi) {
            $sql = "INSERT INTO tiket (film_id, nama_pembeli, jumlah_tiket, kursi) VALUES ('$film_id', '$nama_pembeli', '1', '$kursi')";
            if (!$conn->query($sql)) {
                echo "Error: " . $sql . "<br>" . $conn->error;
                exit();
            }
        }
        
        // Redirect ke halaman pembayaran
        header("Location: pembayaran.php?film_id=" . $film_id . "&jumlah_tiket=" . $jumlah_tiket . "&nama_pembeli=" . urlencode($nama_pembeli) . "&kursi=" . urlencode(json_encode($kursi_terpilih)));
        exit();
    } else {
        echo "<script>alert('Jumlah kursi yang dipilih tidak sesuai dengan jumlah tiket.');</script>";
    }
}


// Ambil informasi film
if ($film_id) {
    $sql = "SELECT * FROM film WHERE id = $film_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $film = $result->fetch_assoc();
    } else {
        echo "Film tidak ditemukan.";
    }
}

// Ambil informasi tiket yang sudah dibeli
if ($film_id) {
    $kursi_terisi = []; // Inisialisasi sebagai array kosong
    $sql_tiket = "SELECT * FROM tiket WHERE film_id = $film_id";
    $result_tiket = $conn->query($sql_tiket);
    if ($result_tiket) {
        while ($row = $result_tiket->fetch_assoc()) {
            $kursi_terisi[] = (int)$row['kursi'];
        }
    }
}
if (!$film) {
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beli Tiket - <?php echo $film['judul']; ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
        }
        .kursi {
            width: 50px;
            height: 50px;
            margin: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .tersedia {
            background-color: #28a745;
            color: white;
        }
        .terisi {
            background-color: #dc3545;
            color: white;
            cursor: not-allowed;
        }
        .terpilih {
            background-color: #ffc107;
            color: black;
        }
    </style>
</head>
<body class="text-center">
    <div class="container mt-5">
        <h1>Beli Tiket untuk <?php echo $film['judul']; ?></h1>

        <form method="POST" action="" class="mt-4 p-4 bg-white rounded shadow">
            <input type="hidden" name="film_id" value="<?php echo $film['id']; ?>">
            <input type="hidden" name="kursi" id="kursi" value="">
            <div class="form-group">
                <label for="nama_pembeli">Nama Pembeli:</label>
                <input type="text" name="nama_pembeli" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="jumlah_tiket">Jumlah Tiket:</label>
                <input type="number" name="jumlah_tiket" id="jumlah_tiket" class="form-control" min="1" required>
            </div>
            <h2>Pilih Kursi</h2>
        <div class="row">
            <div class="col">
                <h3>Sebelah Kiri</h3>
                <div class="d-flex flex-wrap justify-content-center">
                    <?php
                    for ($i = 1; $i <= 20; $i++) {
                        $status = in_array($i, $kursi_terisi) ? 'terisi' : 'tersedia';
                        echo "<div class='kursi $status' data-kursi='$i'>$i</div>";
                    }
                    ?>
                </div>
            </div>
            <div class="col">
                <h3>Sebelah Kanan</h3>
                <div class="d-flex flex-wrap justify-content-center">
                    <?php
                    for ($i = 21; $i <= 40; $i++) {
                        $status = in_array($i, $kursi_terisi) ? 'terisi' : 'tersedia';
                        echo "<div class='kursi $status' data-kursi='$i'>$i</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
            <input type="submit" class="btn btn-primary" value="Beli Tiket">
        </form>
        <br>
        <a href="index.php" class="btn btn-link">Kembali ke Daftar Film</a>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
    const kursiElements = document.querySelectorAll('.kursi');
    const kursiInput = document.getElementById('kursi');
    const jumlahTiketInput = document.getElementById('jumlah_tiket');

    let selectedSeats = [];

    kursiElements.forEach(kursi => {
        const kursiNumber = parseInt(kursi.getAttribute('data-kursi'));

        // Atur kursi menjadi terisi jika sudah dibeli
        if (<?php echo json_encode($kursi_terisi); ?>.includes(kursiNumber)) {
            kursi.classList.remove('tersedia');
            kursi.classList.add('terisi');
        }

        kursi.addEventListener('click', function() {
            if (this.classList.contains('tersedia')) {
                if (selectedSeats.length < jumlahTiketInput.value) {
                    this.classList.remove('tersedia');
                    this.classList.add('terpilih');
                    selectedSeats.push(kursiNumber);
                } else {
                    alert("Anda hanya bisa memilih " + jumlahTiketInput.value + " kursi.");
                }
            } else if (this.classList.contains('terpilih')) {
                this.classList.remove('terpilih');
                this.classList.add('tersedia');
                selectedSeats = selectedSeats.filter(seat => seat !== kursiNumber);
            }

            // Update input hidden dengan data kursi terpilih dalam JSON
            kursiInput.value = JSON.stringify(selectedSeats);
            console.log("Kursi yang dipilih:", kursiInput.value); // Debug
        });
    });
});
    </script>
</body>
</html>

<?php $conn->close(); ?>
