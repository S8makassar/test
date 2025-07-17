<?php
// Koneksi ke database
$host = 'localhost';
$user = 'root';
$pass = ''; // sesuaikan password
$db = 'absensi_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data riwayat dari tabel absensi
$sql = "SELECT id, nama_karyawan, lokasi, tanggal, waktu, keterangan FROM absensi ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Absen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
        }
        .slide {
            width: 340px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 90vh;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #f9f9f9;
            border-bottom: 1px solid #ddd;
        }
        .header button {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #555;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        .content {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            background: #fafafa;
        }
        .status {
            background: #d4edda;
            color: #155724;
            text-align: center;
            border-radius: 6px;
            padding: 6px;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .info p {
            font-size: 14px;
            margin: 2px 0;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            padding: 5px;
            border-top: 1px solid #ddd;
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="slide">
       <div class="header">
    <button onclick="window.history.back()">←</button>
    <button onclick="window.location.href='index.html'">x</button>
       </div>

        <div class="title">Riwayat Absen</div>
        <div class="content">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="card">
                        <div class="status"><?php echo htmlspecialchars($row['keterangan']); ?></div>
                        <div class="info">
                            <p>Nama: <?php echo htmlspecialchars($row['nama_karyawan']); ?></p>
                            <p>Lokasi: <?php echo htmlspecialchars($row['lokasi']); ?></p>
                            <p>Tanggal: <?php echo htmlspecialchars($row['tanggal']); ?> / Waktu: <?php echo htmlspecialchars($row['waktu']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color:#777;">Belum ada data absen.</p>
            <?php endif; ?>
        </div>
        <div class="footer">@summerhillschool</div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
