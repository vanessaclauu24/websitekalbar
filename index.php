<?php
session_start();
include "../koneksi.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM admin WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("location: dashboard.php");
        exit();
    } else {
        echo "<script>alert('Email atau password salah!'); window.location.href='index.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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

    /* Background dari folder uploads */
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

    .login-container {
        background-color: rgba(92, 61, 46, 0.9);
        border-radius: 20px;
        padding: 50px 40px;
        width: 450px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
        color: #ffffff;
        border: 2px solid #d4a76a;
        position: relative;
        z-index: 1;
    }

    .login-container h2 {
        font-weight: bold;
        color: #f9e4b7;
        text-align: center;
        margin-bottom: 25px;
        font-size: 35px;
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

    .btn-login {
        background-color: #a54229;
        border: none;
        color: #fff;
        font-weight: bold;
        padding: 12px;
        border-radius: 12px;
        width: 100%;
        transition: 0.3s;
        font-size: 20px;
    }

    .btn-login:hover {
        background-color: #922b21;
    }

    .text-link {
        text-align: center;
        margin-top: 15px;
        font-size: 17px;
    }

    .text-link a {
        color: #ffdd8a;
        text-decoration: none;
    }

    .text-link a:hover {
        text-decoration: underline;
    }

    .input-group-text {
        background-color: #7a4e32;
        color: #ffffff;
        border: 1px solid #d4a76a;
        border-radius: 10px;
        cursor: pointer;
    }

    .toggle-password i {
        width: 20px;
        text-align: center;
    }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required />
        </div>
        <div class="mb-4">
            <label for="password" class="form-label">Password:</label>
            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" required />
                <span class="input-group-text toggle-password" onclick="togglePassword()">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>
        </div>
        <button type="submit" class="btn btn-login">Login</button>
        <div class="text-link">
            Lupa sandi? <a href="lupa_pass.php">Klik di sini</a>
        </div>
    </form>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById("password");
    const toggleIcon = document.querySelector(".toggle-password i");

    if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    } else {
        passwordField.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    }
}
</script>
</body>
</html>
