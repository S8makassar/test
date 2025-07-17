<?php
$conn = new mysqli("localhost", "root", "", "absensi_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$pesan = "";
$sukses = false;
$nama_karyawan = "";
$tanggal = date("Y-m-d");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_karyawan = $_POST["id_karyawan"];
    $jenis_izin = $_POST["jenis_izin"];
    $keterangan = $_POST["keterangan"];

    // Ambil nama karyawan
    $sql = "SELECT nama_karyawan FROM karyawan WHERE id_karyawan = '$id_karyawan'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nama_karyawan = $row["nama_karyawan"];

        $surat_dokter = "";
        if ($jenis_izin == "Sakit") {
            if (isset($_FILES["surat_dokter"]) && $_FILES["surat_dokter"]["error"] == 0) {
                $folder = "uploads/";
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }
                $nama_file = time() . "_" . basename($_FILES["surat_dokter"]["name"]);
                $target = $folder . $nama_file;
                if (move_uploaded_file($_FILES["surat_dokter"]["tmp_name"], $target)) {
                    $surat_dokter = $target;
                } else {
                    $pesan = "Gagal upload surat dokter.";
                }
            } else {
                $pesan = "Surat dokter wajib diunggah.";
            }
        }

        if ($pesan == "") {
            $insert = "INSERT INTO izin (id_karyawan, nama_karyawan, tanggal, jenis_izin, surat_dokter, keterangan)
                       VALUES ('$id_karyawan', '$nama_karyawan', '$tanggal', '$jenis_izin', '$surat_dokter', '$keterangan')";
            if ($conn->query($insert)) {
                $pesan = "Izin berhasil diajukan!";
                $sukses = true;
            } else {
                $pesan = "Gagal menyimpan data: " . $conn->error;
            }
        }
    } else {
        $pesan = "ID karyawan tidak ditemukan.";
    }
}
if ($sukses) {
    echo "<script>window.onload = function(){ bukaModal(); }</script>";
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengajuan Izin</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f0f0;
            padding: 30px;
        }
        .container {
            background: #fff;
            padding: 25px;
            width: 400px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        label {
            margin-top: 12px;
            display: block;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 9px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        input[readonly] {
            background: #eee;
        }
        button {
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            background: orange;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .pesan {
            margin-top: 15px;
            color: <?= $sukses ? 'green' : 'red' ?>;
            font-weight: bold;
        }
        #suratField {
            display: none;
        }
        .modal {
    display: none; /* Awalnya disembunyikan */
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

.modal-content h2 {
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
 .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
         .btn-back {
            position: absolute;
            top: 55px;
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

    </style>
</head>
<body>
<div class="container">
    <button class="btn-back" onclick="window.history.back()">←</button>
    <div class="title"><h2>Ajukan Izin</h2></div>
    <form method="POST" enctype="multipart/form-data">
        <label>ID Karyawan</label>
        <input type="text" name="id_karyawan" id="id_karyawan" required onkeyup="ambilNama()" value="<?= isset($_POST['id_karyawan']) ? htmlspecialchars($_POST['id_karyawan']) : '' ?>">

        <label>Nama Karyawan</label>
        <input type="text" name="nama_karyawan" id="nama_karyawan" value="<?= htmlspecialchars($nama_karyawan) ?>" readonly>

        <label>Tanggal</label>
        <input type="text" name="tanggal" value="<?= $tanggal ?>" readonly>

        <label>Jenis Izin</label>
        <select name="jenis_izin" id="jenis_izin" required onchange="cekJenis()">
            <option value="">-- Pilih --</option>
            <option value="Cuti" <?= (isset($_POST['jenis_izin']) && $_POST['jenis_izin']=='Cuti')?'selected':''; ?>>Cuti</option>
            <option value="Sakit" <?= (isset($_POST['jenis_izin']) && $_POST['jenis_izin']=='Sakit')?'selected':''; ?>>Sakit</option>
        </select>

        <div id="suratField">
            <label>Upload Surat Dokter</label>
            <input type="file" name="surat_dokter" accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <label>Keterangan</label>
        <textarea name="keterangan"><?= isset($_POST['keterangan']) ? htmlspecialchars($_POST['keterangan']) : '' ?></textarea>

        <button type="submit">Ajukan Izin</button>

        <?php if ($pesan): ?>
            <div class="pesan"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
    </form>
</div>
<!-- Modal Berhasil Terkirim -->
<div class="modal" id="modalBerhasil">
  <div class="modal-content">
    <h2>✅ Berhasil Terkirim</h2>
<button onclick="window.location.href='index.html'">Close</button>   
  </div>
</div>

<script>
function ambilNama() {
    var id = document.getElementById("id_karyawan").value;
    if (id.length > 0) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "get_karyawan.php?id=" + encodeURIComponent(id), true);
        xhr.onload = function() {
            if (xhr.status == 200) {
                document.getElementById("nama_karyawan").value = xhr.responseText;
            }
        };
        xhr.send();
    } else {
        document.getElementById("nama_karyawan").value = "";
    }
}

function cekJenis() {
    var jenis = document.getElementById("jenis_izin").value;
    document.getElementById("suratField").style.display = (jenis=="Sakit") ? "block" : "none";
}

// Panggil sekali saat load
cekJenis();

// Tampilkan modal
function bukaModal() {
    document.getElementById("modalBerhasil").style.display = "flex";
}

// Tutup modal
function tutupModal() {
    document.getElementById("modalBerhasil").style.display = "none";
}


</script>
</body>
</html>
