<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include '../koneksi.php';

$admin_id = $_SESSION['admin_id'];
$success = '';
$error = '';

$stmt = $conn->prepare("SELECT email, username, password, level, foto FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($email, $username, $password_db, $level, $foto);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = $_POST['email'];
    $new_username = $_POST['username'];
    $new_password = $_POST['password'];

    if (empty($new_email) || empty($new_username)) {
        $error = "Email dan username tidak boleh kosong.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $new_email, $admin_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email sudah digunakan oleh admin lain.";
            $stmt->close();
        } else {
            $stmt->close();

            if (empty($new_password)) {
                $stmt = $conn->prepare("UPDATE admin SET email = ?, username = ? WHERE id = ?");
                $stmt->bind_param("ssi", $new_email, $new_username, $admin_id);
            } else {
                $stmt = $conn->prepare("UPDATE admin SET email = ?, username = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $new_email, $new_username, $new_password, $admin_id);
            }

            if ($stmt->execute()) {
                $_SESSION['username'] = $new_username;
                $username = $new_username;
                $email = $new_email;
                $success = "Data berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui data.";
            }
            $stmt->close();

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $nama_foto = 'admin_' . time() . '.' . $ext;
                $target_path = __DIR__ . '/uploads/' . $nama_foto;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_path)) {
                    $stmt = $conn->prepare("UPDATE admin SET foto = ? WHERE id = ?");
                    $stmt->bind_param("si", $nama_foto, $admin_id);
                    $stmt->execute();
                    $stmt->close();
                    $_SESSION['foto'] = $nama_foto;
                    $foto = $nama_foto;
                    $success .= "<br>Foto berhasil diubah.";
                } else {
                    $error .= "<br>Gagal mengunggah foto.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profil Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      color: #3e2723;
      /* 🔥 Bisa pilih background warna atau gambar sendiri */
      background: #dbc3a3 url('uploads/background batik kalbar.png') no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .card-profile {
      max-width: 850px;
      width: 100%;
      background: #f3e4d0;
      border-radius: 20px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.25);
      padding: 50px 40px;
      border: 2px solid #a16b3f;
      position: relative;
      font-size: 1.05rem;
    }

    .card-header {
      height: 120px;
      background: #a16b3f;
      margin: -50px -40px 30px -40px;
      border-radius: 17px 17px 0 0;
      box-shadow: inset 0 -4px 8px rgba(0,0,0,0.2);
    }

    .avatar {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      border: 3px solid #8b5e3c;
      object-fit: cover;
      margin: -90px auto 15px auto;
      display: block;
      box-shadow: 0 6px 15px rgba(0,0,0,0.25);
      background-color: #f3e4d0;
      transition: transform 0.3s ease;
    }
    .avatar:hover { transform: scale(1.05); }

    h3 {
      color: #3e2723;
      font-size: 2.1rem;
      font-weight: bold;
    }

    .text-muted {
      color: #5d4037 !important;
      font-size: 1.1rem;
    }

    .form-label {
      font-weight: 600;
      color: #3e2723;
      font-size: 1.05rem;
    }

    .form-control {
      border-radius: 10px;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #b49c82;
      background: #f3e4d0;
      color: #3e2723;
      font-size: 1rem;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    }
    .form-control:focus {
      border-color: #8b5e3c;
      box-shadow: 0 0 0 0.2rem rgba(139,94,60,0.25);
      background: #f7ebda;
      color: #3e2723;
    }

    input[type="file"] {
      border-radius: 10px;
      padding: 8px;
      border: 1px solid #b49c82;
      background: #f3e4d0;
      color: #3e2723;
      width: 100%;
      font-size: 1rem;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    }

    input[type="file"]::file-selector-button {
      background: #e8d5bb;
      color: #3e2723;
      border: none;
      padding: 8px 15px;
      border-right: 1px solid #b49c82;
      margin-right: 10px;
      cursor: pointer;
      border-radius: 8px 0 0 8px;
      font-size: 1rem;
      box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }

    .btn-primary {
      background: #8b5e3c;
      color: #fff;
      border-radius: 10px;
      padding: 12px 25px;
      font-weight: bold;
      border: 1px solid #6e4b32;
      font-size: 1.05rem;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      background: #6e4b32;
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.25);
    }

    .btn-secondary {
      background: #a16b3f;
      color: #fff;
      border-radius: 10px;
      padding: 12px 25px;
      font-weight: bold;
      border: 1px solid #8b5e3c;
      font-size: 1.05rem;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      transition: all 0.3s ease;
    }
    .btn-secondary:hover {
      background: #8b5e3c;
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.25);
    }

    .alert-success, .alert-danger {
      font-size: 1rem;
    }

    .button-group { margin-top: 25px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px; }

    @media (max-width: 768px) {
      .card-profile { padding: 40px 25px; }
      .card-header { height: 90px; margin: -40px -25px 20px -25px; }
      .avatar { width: 120px; height: 120px; margin-top: -70px; }
      h3 { font-size: 1.8rem; }
      .button-group { justify-content: center; gap: 15px; }
    }
  </style>
</head>
<body>

<div class="card card-profile">
  <div class="card-header"></div>
  <div class="card-body text-center">
    <img src="<?= htmlspecialchars($foto ? 'uploads/' . $foto : 'uploads/default.jpg') ?>" class="avatar" alt="Foto Profil">

    <h3 class="mt-3"><?= htmlspecialchars($username) ?></h3>
    <p class="text-muted">Level: <?= htmlspecialchars($level) ?> | ID: <?= $admin_id ?></p>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="text-start mt-4">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Password Baru (Opsional)</label>
          <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ganti">
        </div>
        <div class="col-md-6">
          <label class="form-label">Upload Foto Baru</label>
          <input type="file" name="foto" class="form-control" accept="image/*">
        </div>
      </div>
      <div class="button-group">
        <a href="dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
