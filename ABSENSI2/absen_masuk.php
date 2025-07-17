<?php 
$conn = new mysqli("localhost", "root", "", "absensi_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$nama_karyawan = "";
$tanggal = date("Y-m-d");
$waktu = date("H:i:s");
$lokasi = "";
$keterangan = "Masuk";
$pesan = "";
$sukses = false;
$sudah_absen = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_karyawan = $_POST["id_karyawan"];
    $lokasi = $_POST["lokasi"]; // Ambil lokasi dari input

    // Cari nama karyawan
    $sql = "SELECT nama_karyawan FROM karyawan WHERE id_karyawan = '$id_karyawan'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $nama_karyawan = $data["nama_karyawan"];

        // Cek sudah absen?
        $cek = "SELECT * FROM absensi 
                WHERE id_karyawan='$id_karyawan' 
                  AND tanggal='$tanggal' 
                  AND keterangan='Masuk'";
        $cek_result = $conn->query($cek);

        if ($cek_result->num_rows == 0) {
            // Simpan absen
            $insert = "INSERT INTO absensi (id_karyawan, nama_karyawan, tanggal, waktu, lokasi, keterangan) 
                       VALUES ('$id_karyawan', '$nama_karyawan', '$tanggal', '$waktu', '$lokasi', '$keterangan')";
            if ($conn->query($insert)) {
                $pesan = "Absen masuk berhasil!";
                $sukses = true;
            } else {
                $pesan = "Gagal menyimpan absen masuk.";
            }
        } else {
            $pesan = "Anda sudah absen masuk hari ini!";
            $sudah_absen = true;
        }
    } else {
        $pesan = "ID karyawan tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Absen Masuk GPS</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f0f0;
            padding: 30px;
        }
        .container {
            position: relative;
            background: #fff;
            padding: 25px;
            width: 400px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        label {
            margin-top: 15px;
            display: block;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            background: green;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .pesan {
            margin-top: 15px;
            color: red;
            font-weight: bold;
        }
        .map {
            margin-top: 15px;
            width: 100%;
            height: 200px;
            border: 1px solid #ccc;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
            z-index: 999;
        }
        .modal-content {
            background: #fff;
            padding: 30px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }
        .modal-content h2, .modal-content h5 {
            margin-bottom: 20px;
        }
        .modal-content button {
            background: orange;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-back {
            position: absolute;
            top: 10px;
            left: -190px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #333;
        }
        .btn-back:hover {
            color: black;
        }
         .title {
            text-align: center;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <button class="btn-back" onclick="window.history.back()">←</button>

   <div class="title"> <h2>Absen Masuk</h2></div>
    <form method="POST">
        <label>ID Karyawan</label>
        <input type="text" name="id_karyawan" id="id_karyawan" required value="<?= isset($_POST['id_karyawan']) ? $_POST['id_karyawan'] : '' ?>">

        <label>Nama</label>
        <input type="text" name="nama_karyawan" id="nama_karyawan" value="<?= $nama_karyawan ?>" readonly>

        <label>Tanggal</label>
        <input type="text" name="tanggal" value="<?= $tanggal ?>" readonly>

        <label>Waktu</label>
        <input type="text" name="waktu" value="<?= $waktu ?>" readonly>

        <label>Lokasi (dari GPS)</label>
        <input type="text" name="lokasi" id="lokasi" value="<?= htmlspecialchars($lokasi) ?>" readonly>

        <!-- Map view -->
        <div class="map" id="map"></div>

        <label>Keterangan</label>
        <input type="text" name="keterangan" value="<?= $keterangan ?>" readonly>

        <button type="submit">Kirim Absen Masuk</button>

        <?php if (!$sukses && !$sudah_absen && $pesan): ?>
            <div class="pesan"><?= $pesan ?></div>
        <?php endif; ?>
    </form>
</div>

<!-- Modal Berhasil -->
<div class="modal" id="modalBerhasil" style="display: <?= $sukses ? 'flex' : 'none' ?>">
    <div class="modal-content">
        <h2>✅ Absen Masuk Berhasil</h2>
        <button onclick="window.location.href='riwayat.php'">Lihat Riwayat Absen</button>
    </div>
</div>

<!-- Modal Sudah Absen -->
<div class="modal" id="modalSudahAbsen" style="display: <?= $sudah_absen ? 'flex' : 'none' ?>">
    <div class="modal-content">
        <h5>⚠️ Anda sudah absen masuk hari ini!</h5>
        <button onclick="window.location.href='index.html'">Keluar</button>
    </div>
</div>

<script>
// Ambil lokasi GPS dan tampilkan nama lokasi
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(pos => {
        const lat = pos.coords.latitude;
        const lon = pos.coords.longitude;

        // Panggil API Nominatim untuk reverse geocoding
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
        .then(response => response.json())
        .then(data => {
            if (data.display_name) {
                document.getElementById("lokasi").value = data.display_name;
            } else {
                document.getElementById("lokasi").value = `Lat: ${lat.toFixed(5)}, Lon: ${lon.toFixed(5)}`;
            }
        })
        .catch(err => {
            document.getElementById("lokasi").value = `Lat: ${lat.toFixed(5)}, Lon: ${lon.toFixed(5)}`;
        });

        // Tampilkan map Google (iframe)
        document.getElementById("map").innerHTML = `
            <iframe width="100%" height="200" 
                src="https://maps.google.com/maps?q=${lat},${lon}&z=16&output=embed"></iframe>`;
    }, err => {
        document.getElementById("lokasi").value = "Lokasi tidak tersedia";
        document.getElementById("map").innerHTML = "<p style='color:red;'>Tidak bisa mendeteksi lokasi.</p>";
    });
}

// Ambil nama karyawan otomatis
document.getElementById('id_karyawan').addEventListener('input', function() {
    const id = this.value;
    if (id.length > 0) {
        fetch('get_karyawan.php?id=' + encodeURIComponent(id))
        .then(res => res.text())
        .then(nama => {
            document.getElementById('nama_karyawan').value = nama;
        });
    } else {
        document.getElementById('nama_karyawan').value = "";
    }
});
</script>

</body>
</html>
