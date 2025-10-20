<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include '../koneksi.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id = intval($_GET['id']);

// Ambil data isi_kategori dulu untuk hapus file fotonya
$stmt = $conn->prepare("SELECT kategori_id, foto FROM isi_kategori WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Data tidak ditemukan.");
}

$data = $result->fetch_assoc();
$kategori_id = $data['kategori_id'];
$foto = $data['foto'];

// Hapus data dari database
$stmt_del = $conn->prepare("DELETE FROM isi_kategori WHERE id=?");
$stmt_del->bind_param("i", $id);
if ($stmt_del->execute()) {
    // Hapus file foto jika ada
    if (!empty($foto) && file_exists("uploads/" . $foto)) {
        unlink("uploads/" . $foto);
    }

    header("Location: isi_kategori.php?kategori=" . $kategori_id . "&msg=hapus_sukses");
    exit();
} else {
    echo "Gagal menghapus data.";
}
?>
