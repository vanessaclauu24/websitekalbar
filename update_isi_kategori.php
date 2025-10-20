<?php
session_start();
include '../koneksi.php';

$id = intval($_POST['id']);
$nama = trim($_POST['nama']);
$deskripsi = trim($_POST['deskripsi']);
$link = trim($_POST['link']);
$sumber_foto = trim($_POST['sumber_foto']);

// Cek duplikasi nama
$stmtCheck = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM isi_kategori 
    WHERE nama_item=? 
    AND kategori_id=(SELECT kategori_id FROM isi_kategori WHERE id=?) 
    AND id<>?
");
$stmtCheck->bind_param("sii", $nama, $id, $id);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result()->fetch_assoc();

if($resCheck['count'] > 0){
    echo "Nama item sudah ada di kategori ini!";
    exit;
}

// Update data
$stmt = $conn->prepare("
    UPDATE isi_kategori 
    SET nama_item=?, deskripsi=?, link=?, sumber_foto=? 
    WHERE id=?
");
$stmt->bind_param("ssssi", $nama, $deskripsi, $link, $sumber_foto, $id);

if($stmt->execute()){
    // Handle upload foto jika ada
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){
        $file = $_FILES['foto'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = 'item_'.$id.'_'.time().'.'.$ext;
        $path = 'uploads/'.$newName;
        if(move_uploaded_file($file['tmp_name'], $path)){
            $stmt2 = $conn->prepare("UPDATE isi_kategori SET foto=? WHERE id=?");
            $stmt2->bind_param("si", $newName, $id);
            $stmt2->execute();
        }
    }
    echo "Perubahan berhasil disimpan";
}else{
    echo "Gagal menyimpan perubahan";
}
?>
