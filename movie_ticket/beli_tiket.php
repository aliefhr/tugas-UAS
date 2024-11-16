<?php
include 'db.php';
session_start();

$film_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$film = null;
$kursi_terisi = [];

// Pastikan user_id ada di session
if (!isset($_SESSION['user_id'])) {
    die("Anda harus login untuk membeli tiket.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $film_id = intval($_POST['film_id']);
    $user_id = intval($_POST['user_id']); // Ambil ID pengguna dari form
    $nama_pembeli = $conn->real_escape_string($_POST['nama_pembeli']);
    $jumlah_tiket = intval($_POST['jumlah_tiket']);
    $kursi_terpilih = json_decode($_POST['kursi'], true) ?? [];

    if (is_array($kursi_terpilih) && count($kursi_terpilih) === $jumlah_tiket) {
        $conn->begin_transaction();
        try {
            foreach ($kursi_terpilih as $kursi) {
                $kursi = intval($kursi);
                $stmt = $conn->prepare("INSERT INTO tiket (film_id, user_id, nama_pembeli, jumlah_tiket, kursi) VALUES (?, ?, ?, 1, ?)");
                $stmt->bind_param('iisi', $film_id, $user_id, $nama_pembeli, $kursi);
                $stmt->execute();
                $stmt->close();
            }
            $conn->commit();
            header("Location: pembayaran.php?film_id=$film_id&jumlah_tiket=$jumlah_tiket&nama_pembeli=" . urlencode($nama_pembeli) . "&kursi=" . urlencode(json_encode($kursi_terpilih)));
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
            exit();
        }
    } else {
        echo "<script>alert('Jumlah kursi yang dipilih tidak sesuai dengan jumlah tiket.');</script>";
    }
}

// Ambil data film
if ($film_id) {
    $stmt = $conn->prepare("SELECT * FROM film WHERE id = ?");
    $stmt->bind_param('i', $film_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $film = $result->fetch_assoc();
    } else {
        echo "Film tidak ditemukan.";
    }
    $stmt->close();
}

// Ambil kursi yang terisi
if ($film_id) {
    $kursi_terisi = [];
    $stmt = $conn->prepare("SELECT kursi FROM tiket WHERE film_id = ?");
    $stmt->bind_param('i', $film_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $kursi_terisi[] = (int)$row['kursi'];
        }
    }
    $stmt->close();
}

if (!$film) {
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beli Tiket - <?php echo htmlspecialchars($film['judul']); ?></title>
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
        <h1>Beli Tiket untuk <?php echo htmlspecialchars($film['judul']); ?></h1>

        <img src="<?php echo htmlspecialchars($film['foto']); ?>" class="img-fluid mb-4" alt="<?php echo htmlspecialchars($film['judul']); ?>">
        <p><?php echo htmlspecialchars($film['deskripsi']); ?></p>

        <form method="POST" action="" class="mt-4 p-4 bg-white rounded shadow">
            <input type="hidden" name="film_id" value="<?php echo htmlspecialchars($film['id']); ?>">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>"> <!-- Menambahkan user_id -->
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
            <input type="submit" class="btn btn-primary" value="Beli Tiket" style="background: linear-gradient(to right, #6a11cb, #2575fc); border: none;">
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

                kursiInput.value = JSON.stringify(selectedSeats);
                console.log("Kursi yang dipilih:", kursiInput.value);
            });
        });
    });
    </script>
</body>
</html>

<?php $conn->close(); ?>
