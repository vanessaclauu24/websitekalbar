<?php
session_start();
include '../koneksi.php';

$email_verified = false;
$email = "";
$success = "";
$error = "";

// ====== CEK EMAIL ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_email'])) {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        $stmt = $conn->prepare("SELECT id, email FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res && $res->num_rows === 1) {
                $_SESSION['reset_email'] = $email;
                $email_verified = true;
            } else {
                $error = "Email tidak ditemukan!";
            }
        } else {
            $error = "Terjadi kesalahan pada server (cek query).";
        }
        $stmt->close();
    }
}

// ====== GANTI PASSWORD ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (isset($_SESSION['reset_email'])) {
        $email = $_SESSION['reset_email'];
    } else {
        $error = "Session email tidak ditemukan. Silakan masukkan email lagi.";
    }

    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $error = "Semua kolom wajib diisi!";
        $email_verified = true;
    } elseif ($new_password !== $confirm_password) {
        $error = "Password dan konfirmasi tidak cocok!";
        $email_verified = true;
    } elseif (strlen($new_password) < 3) {
        $error = "Password minimal 3 karakter!";
        $email_verified = true;
    } else {
        $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_password, $email);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success = "Password berhasil diubah. Silakan login kembali.";
            unset($_SESSION['reset_email']);
            $email_verified = false;
        } else {
            $error = "Gagal mengubah password. Pastikan email masih valid.";
            $email_verified = true;
        }
        $stmt->close();
    }
}

// ====== RESET FLOW ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_flow'])) {
    unset($_SESSION['reset_email']);
    $email_verified = false;
    $email = "";
    $error = "";
    $success = "";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Lupa Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
        margin: 0;
        height: 100vh;
        font-family: 'Segoe UI', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    body::before {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url("uploads/kalimantan.jpg") no-repeat center center/cover;
        z-index: -2;
    }

    body::after {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(135deg, 
            rgba(75, 46, 46, 0.5),   
            rgba(122, 59, 46, 0.5),  
            rgba(212, 167, 106, 0.5) 
        );
        z-index: -1;
    }

    .card-box {
        background-color: rgba(92, 61, 46, 0.9);
        border-radius: 20px;
        padding: 40px 30px;
        width: 450px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
        color: #ffffff;
        border: 2px solid #d4a76a;
        position: relative;
        z-index: 1;
    }

    h2 {
        font-weight: bold;
        color: #f9e4b7;
        text-align: center;
        margin-bottom: 20px;
        font-size: 30px;
    }

    label {
        color: #f0d4a4;
    }

    .form-control {
        background-color: #7a4e32;
        color: #ffffff;
        border: 1px solid #d4a76a;
        border-radius: 10px;
        font-size: 15px;
        padding: 12px;
    }

    .form-control:focus {
        background-color: #8b5e3c;
        border-color: #ffdd8a;
        box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.3);
    }

    .btn-purple {
        background-color: #a54229;
        border: none;
        color: #fff;
        font-weight: bold;
        padding: 12px;
        border-radius: 12px;
        width: 100%;
        transition: 0.3s;
        font-size: 18px;
    }

    .btn-purple:hover {
        background-color: #922b21;
    }

    .link-theme {
        text-align: center;
        margin-top: 15px;
        font-size: 16px;
        color: #ffdd8a;
        text-decoration: none;
        display: block;
    }

    .link-theme:hover {
        text-decoration: underline;
    }

    /* ====== ICON MATA SEJAJAR ====== */
    .input-wrapper {
        position: relative;
    }

    .input-wrapper input.form-control {
        padding-right: 45px;
    }

    .toggle-password {
        position: absolute;
        right: 15px;
        cursor: pointer;
        color: #ffdd8a;
        font-size: 20px;
        top: 70%;
        transform: translateY(-50%);
    }
  </style>
</head>
<body>
  <div class="card-box">
    <h2>Reset Password</h2>

    <?php if ($error): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
      <div class="text-center mt-2">
        <a href="index.php" class="btn btn-success w-100">Kembali ke Login</a>
      </div>
    <?php elseif (!isset($_SESSION['reset_email']) || $_SESSION['reset_email'] === ""): ?>
      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label">Masukkan Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" name="check_email" class="btn btn-purple">Lanjutkan</button>
        <a href="index.php" class="link-theme">← Kembali ke Login</a>
      </form>
    <?php else: ?>
      <form method="POST" novalidate>
        <div class="mb-2">
          <small style="color:#f0d4a4">Mengubah password untuk: <strong><?= htmlspecialchars($_SESSION['reset_email']) ?></strong></small>
        </div>
        <div class="mb-3 input-wrapper">
          <label class="form-label">Password Baru</label>
          <input type="password" name="new_password" class="form-control" id="new_password" required>
          <i class="bi bi-eye-slash toggle-password" onclick="togglePassword('new_password', this)"></i>
        </div>
        <div class="mb-3 input-wrapper">
          <label class="form-label">Konfirmasi Password</label>
          <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
          <i class="bi bi-eye-slash toggle-password" onclick="togglePassword('confirm_password', this)"></i>
        </div>
        <button type="submit" name="change_password" class="btn btn-purple">Simpan Password</button>
      </form>
      <form method="POST" class="mt-2">
        <button type="submit" name="reset_flow" class="btn btn-link link-theme">Masukkan email lain</button>
      </form>
    <?php endif; ?>
  </div>

  <script>
    function togglePassword(id, el) {
      const input = document.getElementById(id);
      if (input.type === "password") {
        input.type = "text";
        el.classList.remove("bi-eye-slash");
        el.classList.add("bi-eye");
      } else {
        input.type = "password";
        el.classList.remove("bi-eye");
        el.classList.add("bi-eye-slash");
      }
    }
  </script>
</body>
</html>