<?php
$conn = new mysqli("localhost", "root", "", "absensi_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$id = $_GET['id'];

$sql = "SELECT nama_karyawan FROM karyawan WHERE id_karyawan = '$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo $data['nama_karyawan'];
} else {
    echo "";
}
?>
