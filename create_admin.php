<?php
session_start();

// ====== KONFIGURASI DATABASE ======
$host = "localhost";   
$user = "root";        
$pass = "";            
$db   = "budaya_dayak"; 

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$success = "";
$error = "";

// ====== LOGIKA SIMPAN ADMIN ======
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($username) && !empty($password)) {
        // cek apakah email atau username sudah ada
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email atau Username sudah digunakan!";
        } else {
            $stmt->close();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // ===== UPLOAD FOTO PROFIL =====
            $foto = 'default.jpg';
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $fileName = time() . '_' . $_FILES['foto']['name'];
                $fileTmp = $_FILES['foto']['tmp_name'];
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                if (move_uploaded_file($fileTmp, $uploadDir . $fileName)) {
                    $foto = $fileName;
                }
            }

            // simpan admin ke database
            $query = "INSERT INTO admin (email, username, password, foto, level, created_at) 
                      VALUES (?, ?, ?, ?, 'off', NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $email, $username, $hashed_password, $foto);

            if ($stmt->execute()) {
                $success = "Admin berhasil ditambahkan!";
            } else {
                $error = "Error saat menambahkan admin.";
            }
            $stmt->close();
        }
    } else {
        $error = "Semua field harus diisi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Admin</title>
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
    padding: 40px 35px;
    border-radius: 20px;
    width: 500px;
    box-shadow: 0 15px 25px rgba(0,0,0,0.25),
                0 5px 10px rgba(0,0,0,0.15);
    border: 2px solid #a16b3f;
    transition: 0.3s;
}
.form-box:hover {
    box-shadow: 0 20px 35px rgba(0,0,0,0.3),
                0 8px 15px rgba(0,0,0,0.2);
    transform: translateY(-3px);
}
.form-box h4 {
    text-align: center;
    color: #3e2723;
    margin-bottom: 25px;
    font-weight: bold;
    font-size: 1.8rem;
}
.form-label {
    font-weight: 600;
    color: #3e2723;
    margin-bottom: 5px;
    font-size: 1rem;
}
.form-control {
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 18px;
    border: 1px solid #b49c82;
    background: #f3e4d0;
    color: #3e2723;
    font-size: 1rem;
}
.form-control:focus {
    border-color: #8b5e3c;
    box-shadow: 0 0 0 0.2rem rgba(139,94,60,0.2);
    background: #f7ebda;
    color: #3e2723;
}
.btn-submit {
    background: #8b5e3c;
    color: white;
    border: 1px solid #6e4b32;
    border-radius: 12px;
    width: 100%;
    padding: 14px;
    font-size: 1.05rem;
    font-weight: bold;
    transition: 0.3s;
    margin-bottom: 12px;
}
.btn-submit:hover {
    background: #6e4b32;
    transform: translateY(-1px);
}
.btn-back {
    display: block;
    text-align: center;
    margin-top: 10px;
    color: #a16b3f;
    text-decoration: none;
    font-weight: 500;
}
.btn-back:hover {
    color: #8b5e3c;
    text-decoration: underline;
}
.alert-success {
    background-color: #28a745;
    color: #fff;
    font-weight: bold;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}
.alert-error {
    background-color: #dc3545;
    color: #fff;
    font-weight: bold;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}
</style>
</head>
<body>

<div class="form-box">
    <h4>Tambah Admin</h4>

    <!-- ALERT -->
    <?php if ($success): ?>
        <div class="alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>

        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required>

        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>

        <label class="form-label">Foto Profil</label>
        <input type="file" name="foto" class="form-control">

        <button type="submit" class="btn-submit">Simpan</button>
        <a href="t_admin.php" class="btn-back">← Kembali ke Data Admin</a>
    </form>
</div>

</body>
</html>
