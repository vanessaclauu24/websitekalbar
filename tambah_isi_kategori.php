<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: index.php");
    exit();
}

include '../koneksi.php';

$success = "";
$error = "";

// Ambil semua kategori
$kategori_result = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");

// Logika simpan form
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nama_item = trim($_POST['nama_item']);
    $deskripsi = trim($_POST['deskripsi']);
    $link = trim($_POST['link']);
    $sumber_foto = trim($_POST['sumber_foto']); // 🔹 input baru
    $kategori_id = intval($_POST['kategori_id']);

    // ===== Cek duplikasi =====
    $check_stmt = $conn->prepare("SELECT id FROM isi_kategori WHERE nama_item=? AND kategori_id=?");
    $check_stmt->bind_param("si", $nama_item, $kategori_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    if($check_stmt->num_rows > 0){
        $error = "Item sudah ada dalam kategori ini!";
    }
    $check_stmt->close();

    if(!$error){
        // Upload gambar
        if(isset($_FILES['foto']) && $_FILES['foto']['error'] === 0){
            $foto_name = time() . "_" . $_FILES['foto']['name'];
            $foto_tmp = $_FILES['foto']['tmp_name'];
            $target = "uploads/" . $foto_name;
            if(!move_uploaded_file($foto_tmp, $target)){
                $error = "Gagal mengupload gambar.";
            }
        } else {
            $foto_name = "";
        }
    }

    if(!$error){
        // 🔹 Tambahkan kolom sumber_foto
        $stmt = $conn->prepare("INSERT INTO isi_kategori (nama_item, deskripsi, link, kategori_id, foto, sumber_foto) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $nama_item, $deskripsi, $link, $kategori_id, $foto_name, $sumber_foto);
        if($stmt->execute()){
            $success = "Item berhasil ditambahkan!";
        } else {
            $error = "Terjadi kesalahan: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Isi Kategori</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background:#dbc3a3 url('uploads/background batik kalbar.png') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    max-width:900px;
    width: 100%;
    background: rgba(255,248,240,0.95);
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
    padding:20px 30px 40px 30px;
    display:flex;
    flex-direction:column;
    gap:20px;
}

.container h2{
    text-align:center;
    color:#4b2c4a;
    margin-bottom:20px;
    font-size:36px;
    font-weight:bold;
}

.form-wrapper {
    display:flex;
    gap:30px;
    flex-wrap:wrap;
}

.form-left {
    flex:1;
    min-width:300px;
}
.form-left label{
    display:block;
    margin-bottom:5px;
    font-weight:bold;
}
.form-left input[type="text"],
.form-left textarea,
.form-left select{
    width:100%;
    padding:10px;
    margin-bottom:15px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:16px;
}
.form-left textarea{
    resize:none;
    height:100px;
}

.form-right {
    flex:1;
    min-width:300px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    padding:10px;
}

.form-right .preview-box {
    width: 300px;
    height: 300px;
    border: 3px dashed #4b2c4a;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
    background:#fff;
    margin-bottom:15px;
}
.form-right .preview-box img{
    max-width:100%;
    max-height:100%;
    object-fit:contain;
}

.custom-file-btn {
    display: inline-block;
    padding: 12px 30px;
    font-size: 18px;
    font-weight: bold;
    color: white;
    background: linear-gradient(135deg,#5C4033,#b37d5b);
    border-radius: 12px;
    cursor: pointer;
    text-align: center;
    transition: background 0.3s;
}
.custom-file-btn:hover {
    background: linear-gradient(135deg,#9c6747,#81533b);
}
.custom-file-btn input[type="file"]{
    display:none;
}

.button-submit-wrapper{
    text-align:center;
    margin-top:10px;
}
.button-submit-wrapper button{
    background: linear-gradient(135deg,#5C4033,#b37d5b);
    color:white;
    padding:18px 35px;
    font-size:20px;
    font-weight:bold;
    border:none;
    border-radius:20px;
    cursor:pointer;
    transition: transform 0.2s, background 0.3s;
    width:60%;
    max-width:400px;
}
.button-submit-wrapper button:hover{
    transform: scale(1.05);
    background: linear-gradient(135deg,#9c6747,#81533b);
}

.button-submit-wrapper a{
    color:#4b2c4a;
    text-decoration:none;
    font-weight:bold;
    font-size:16px;
    display:inline-block;
    margin-top:15px;
}
.button-submit-wrapper a:hover{
    text-decoration:underline;
}

/* Notifikasi sukses/error */
.success, .error{
    text-align:center;
    padding:15px;
    border:3px solid;
    border-radius:12px;
    font-size:18px;
    font-weight:bold;
}
.success{
    color:green;
    border-color:green;
    background: #e6f9e6;
}
.error{
    color:red;
    border-color:red;
    background: #ffe6e6;
}

@media(max-width:768px){
    .form-wrapper{
        flex-direction:column;
    }
    .form-right .preview-box{
        width:100%;
        height:250px;
    }
}
</style>
</head>
<body>

<div class="container">
    <h2>Tambah Isi Kategori</h2>

    <?php if($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

    <div class="form-wrapper">
        <div class="form-left">
            <label>Nama Item</label>
            <input type="text" name="nama_item" form="formTambah" required>

            <label>Deskripsi</label>
            <textarea name="deskripsi" form="formTambah" required></textarea>

            <label>Link Video/Musik</label>
            <input type="text" name="link" form="formTambah">

            <!-- 🔹 Input baru -->
            <label>Sumber Foto</label>
            <input type="text" name="sumber_foto" form="formTambah" placeholder="Contoh: wadaya.rey1024.com">

            <label>Pilih Kategori</label>
            <select name="kategori_id" form="formTambah" required>
                <option value="">-- Pilih Kategori --</option>
                <?php while($row = $kategori_result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= isset($_GET['kategori']) && $_GET['kategori']==$row['id'] ? 'selected' : '' ?>><?= htmlspecialchars($row['nama_kategori']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-right">
            <div class="preview-box">
                <img id="preview" src="uploads/default.png" alt="">
            </div>
            <label class="custom-file-btn">
                Pilih File
                <input type="file" name="foto" form="formTambah" accept="image/*" onchange="previewImage(event)">
            </label>
        </div>
    </div>

    <div class="button-submit-wrapper">
        <form id="formTambah" method="POST" enctype="multipart/form-data">
            <button type="submit">Simpan Data</button>
        </form>
        <a href="isi_kategori.php?kategori=<?= isset($_GET['kategori']) ? intval($_GET['kategori']) : 1 ?>">← Kembali ke Isi Kategori</a>
    </div>
</div>

<script>
function previewImage(event){
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('preview');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>

</body>
</html>
