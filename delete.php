<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include '../koneksi.php';
$admin_id = $_SESSION['admin_id'];

// ===== HAPUS ADMIN OTOMATIS =====
// Hapus admin dengan level 'off' dan umur akun > 10 detik
mysqli_query($conn, "
    DELETE FROM admin 
    WHERE level = 'off' 
    AND TIMESTAMPDIFF(MONTH, created_at, NOW()) > 1
");

// Ambil username admin yang sedang login
$query_admin = mysqli_query($conn, "SELECT username FROM admin WHERE id = '$admin_id'");
if ($query_admin && mysqli_num_rows($query_admin) > 0) {
    $admin_data = mysqli_fetch_assoc($query_admin);
    $username = $admin_data['username'];
} else {
    header("Location: index.php");
    exit();
}
