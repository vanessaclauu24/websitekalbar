<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include '../koneksi.php';

// Pastikan ada id yang dikirim
if(isset($_GET['id'])){
    $id = intval($_GET['id']);

    // Ambil data kategori untuk mendapatkan nama file gambar
    $query = mysqli_query($conn, "SELECT foto FROM kategori WHERE id = $id");
    if($query && mysqli_num_rows($query) > 0){
        $row = mysqli_fetch_assoc($query);
        $fileFoto = '../uploads/' . $row['foto'];

        // Hapus file gambar jika ada
        if(file_exists($fileFoto)){
            unlink($fileFoto);
        }

        // Hapus data kategori dari database
        $hapus = mysqli_query($conn, "DELETE FROM kategori WHERE id = $id");
        if($hapus){
            $_SESSION['success'] = "Kategori berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus kategori.";
        }
    } else {
        $_SESSION['error'] = "Kategori tidak ditemukan.";
    }
} else {
    $_SESSION['error'] = "ID kategori tidak valid.";
}

// Kembali ke halaman kategori
header("Location: kategori.php");
exit();
?>
