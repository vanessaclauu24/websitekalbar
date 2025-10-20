<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include '../koneksi.php';
$admin_id = $_SESSION['admin_id'];

$success = "";
$error = "";

// Ambil id kategori dari query string
if (!isset($_GET['id'])) {
    header("Location: kategori.php");
    exit();
}
$id = intval($_GET['id']);

// Ambil data kategori lama
$stmt = $conn->prepare("SELECT * FROM kategori WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$kategori = $result->fetch_assoc();
$stmt->close();

if (!$kategori) {
    header("Location: kategori.php");
    exit();
}

// ====== LOGIKA UPDATE KATEGORI ======
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_kategori = trim($_POST['nama_kategori']);
    $foto_kategori = $_FILES['foto_kategori'];

    if (!empty($nama_kategori)) {

        // cek apakah kategori baru sudah ada (kecuali sendiri)
        $stmt = $conn->prepare("SELECT id FROM kategori WHERE nama_kategori = ? AND id != ?");
        $stmt->bind_param("si", $nama_kategori, $id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Nama kategori sudah digunakan!";
        } else {
            $stmt->close();

            // proses upload foto → folder uploads/
            $foto_name = $kategori['foto']; // default pakai foto lama
            if (!empty($foto_kategori['name'])) {
                $ext = pathinfo($foto_kategori['name'], PATHINFO_EXTENSION);
                $foto_name = uniqid('kategori_').'.'.$ext;

                $uploadDir = "uploads/"; // folder uploads di root project
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $upload_path = $uploadDir . $foto_name;

                if (!move_uploaded_file($foto_kategori['tmp_name'], $upload_path)) {
                    $error = "Gagal mengunggah foto baru.";
                }
            }

            if (!$error) {
                $stmt = $conn->prepare("UPDATE kategori SET nama_kategori=?, foto=? WHERE id=?");
                $stmt->bind_param("ssi", $nama_kategori, $foto_name, $id);
                if ($stmt->execute()) {
                    $success = "Kategori berhasil diperbarui!";
                    // ambil data terbaru untuk preview
                    $kategori['nama_kategori'] = $nama_kategori;
                    $kategori['foto'] = $foto_name;
                } else {
                    $error = "Gagal memperbarui kategori.";
                }
                $stmt->close();
            }
        }
    } else {
        $error = "Nama kategori tidak boleh kosong.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Kategori</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: #dbc3a3 url('uploads/background batik kalbar.png') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    font-family: 'Segoe UI', sans-serif;
    color: #3e2723;
    margin: 0;
    padding: 20px;
}
.form-box {
    background-color: #f3e4d0;
    padding: 60px 50px;
    border-radius: 25px;
    width: 650px;
    box-shadow: 0 20px 35px rgba(0,0,0,0.3),
                0 8px 15px rgba(0,0,0,0.2);
    border: 2px solid #a16b3f;
    transition: 0.3s;
}
.form-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 25px 40px rgba(0,0,0,0.35),
                0 10px 20px rgba(0,0,0,0.25);
}
.form-box h4 {
    text-align: center;
    color: #3e2723;
    margin-bottom: 30px;
    font-weight: bold;
    font-size: 2rem;
}
.form-label {
    font-weight: 600;
    color: #3e2723;
    margin-bottom: 8px;
    font-size: 1.1rem;
}
.form-control {
    border-radius: 15px;
    padding: 18px;
    margin-bottom: 20px;
    border: 1px solid #b49c82;
    background: #f3e4d0;
    color: #3e2723;
    font-size: 1.1rem;
}
.form-control:focus {
    border-color: #8b5e3c;
    box-shadow: 0 0 0 0.25rem rgba(139,94,60,0.2);
    background: #f7ebda;
    color: #3e2723;
}
.btn-submit {
    background: #8b5e3c;
    color: white;
    border: 1px solid #6e4b32;
    border-radius: 15px;
    width: 100%;
    padding: 16px;
    font-size: 1.15rem;
    font-weight: bold;
    margin-bottom: 15px;
}
.btn-submit:hover {
    background: #6e4b32;
    transform: translateY(-1px);
}
.btn-back {
    display: block;
    text-align: center;
    margin-top: 12px;
    color: #a16b3f;
    text-decoration: none;
    font-weight: 600;
}
.btn-back:hover {
    color: #8b5e3c;
    text-decoration: underline;
}
.alert-success, .alert-error {
    font-weight: bold;
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 25px;
    text-align: center;
    font-size: 1.1rem;
}
.alert-success { background-color: #28a745; color: #fff; }
.alert-error { background-color: #dc3545; color: #fff; }
.img-preview-box {
    width: 100%;
    height: 250px;
    border: 2px dashed #8b5e3c;
    border-radius: 15px;
    margin-bottom: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    background: #f7ebda;
}
.img-preview-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
</style>
</head>
<body>

<div class="form-box">
    <h4>Edit Kategori</h4>

    <?php if ($success): ?>
        <div class="alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label class="form-label">Nama Kategori</label>
        <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($kategori['nama_kategori']) ?>" required>

        <label class="form-label">Foto Kategori (opsional ganti)</label>
        <div class="img-preview-box" id="imgPreview">
            <img src="uploads/<?= htmlspecialchars($kategori['foto']) ?>" alt="Preview">
        </div>
        <input type="file" name="foto_kategori" class="form-control" accept="image/*" id="fotoInput">

        <button type="submit" class="btn-submit">Simpan Perubahan</button>
        <a href="kategori.php" class="btn-back">← Kembali ke Kategori</a>
    </form>
</div>

<script>
const fotoInput = document.getElementById('fotoInput');
const imgPreview = document.getElementById('imgPreview');

fotoInput.addEventListener('change', function() {
    const file = this.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){
            imgPreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        }
        reader.readAsDataURL(file);
    } else {
        imgPreview.innerHTML = `<img src="uploads/<?= htmlspecialchars($kategori['foto']) ?>" alt="Preview">`;
    }
});
</script>

</body>
</html>