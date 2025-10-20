<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include '../koneksi.php';

$admin_id = $_SESSION['admin_id'];
$query = mysqli_query($conn, "SELECT username, foto FROM admin WHERE id = $admin_id");
$data = mysqli_fetch_assoc($query);

$username = $data['username'];
$foto = $data['foto'] ? 'uploads/' . $data['foto'] : 'uploads/default.jpg';

// Ambil jumlah admin & kategori
$count_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM admin"))['total'];
$count_kategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM kategori"))['total'];
$count_isi_kategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM isi_kategori"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ===== BODY ===== */
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    color: #4b2c4a;
    overflow-x: hidden;
}

/* ===== SLIDESHOW BACKGROUND ===== */
.bg-slide {
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background-size: cover;
    background-position: center;
    transition: opacity 1s ease-in-out;
    z-index: -1;
    opacity: 0;
}
.bg-slide::after {
    content: "";
    position: absolute;
    inset:0;
    background-color: rgba(139,69,19,0.15);
}
.bg-slide.show { opacity: 1; }

/* ===== MENU TOGGLE ===== */
.menu-toggle {
    position: fixed;
    top: 16px;
    left: 15px;
    width: 54px;
    height: 54px;
    font-size: 25px;
    color: white;
    background-color: rgba(205, 134, 63, 0);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 1100;
    box-shadow: 0 3px 8px rgba(0,0,0,0.4);
    transition: background-color 0.3s ease, transform 0.2s, opacity 0.3s;
}
.menu-toggle:hover { 
    background-color: rgba(222,184,135,0.85);
    transform: scale(1.1);
}
.sidebar.active ~ .menu-toggle {
    opacity: 0;
    pointer-events: none;
}

/* ===== SIDEBAR ===== */
.sidebar {
    position: fixed;
    left: -250px;
    top: 0;
    width: 250px;
    height: 100%;
    background: linear-gradient(180deg, #5d4037, #3e2723);
    color: #fff8dc;
    transition: left 0.3s ease;
    z-index: 1000;
    border-right: 4px solid #daa520;
    overflow: hidden;
}
.sidebar.active { left:0; }
.sidebar::after {
    content: "";
    position: absolute;
    top: 0;
    right: -5px;
    width: 40px;
    height: 100%;
    background: url('uploads/ornamen_dayak.png') repeat-y;
    background-size: contain;
    z-index: -1;
}
.sidebar h2 {
    text-align: center;
    padding: 20px 0 10px;
    background: rgba(53, 37, 18, 0.9);
    margin:0;
    font-size: 35px;
    font-family: 'Papyrus', cursive;
    letter-spacing: 1px;
}
.sidebar ul { list-style:none; padding:0; margin:0; }
.sidebar ul li { border: none; }
.sidebar ul li a {
    display:flex;
    align-items:center;
    padding:18px 20px;
    margin: 12px;
    background: linear-gradient(145deg, #5d4037, #3e2723);
    border-radius: 10px;
    border: 2px solid #daa520;
    color:#f5f5f5;
    text-decoration:none;
    font-size: 20px;
    font-weight: 600;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.4);
    transition: background 0.3s, transform 0.2s;
}
.sidebar ul li a i {
    margin-right: 12px;
    min-width: 22px;
    text-align: center;
    color: #ffd700;
}
.sidebar ul li a:hover {
    background: linear-gradient(145deg, #8d6e63, #5d4037);
    transform: translateY(-3px);
}

/* ===== LOGOUT ===== */
.logout-section {
    position: absolute;
    bottom: 40px;
    width: 100%;
    border-top: 2px solid #663d64;
    padding-top: 5px;
}
.logout-section a {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px 20px;
    font-size: 22px;
    font-weight: bold;
    color: #fffbe6;
    text-decoration: none;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
    transition: background 0.3s;
}
.logout-section a i {
    margin-right: 10px;
    color: #ffd700;
}
.logout-section a:hover {
    background: rgba(205,133,63,0.85);
}

/* ===== CONTENT ===== */
.content {
    padding: 2.5rem;
    margin-left:0;
    transition: margin-left 0.3s ease;
}
.sidebar.active ~ .content { margin-left:250px; }

/* ===== CARD ===== */
.card {
    background: rgba(255, 248, 240, 0.85);
    backdrop-filter: blur(10px);
    padding: 50px 40px;
    border-radius: 25px;
    box-shadow: 0 20px 40px rgba(75,44,74,0.5);
    max-width: 900px;
    margin: 80px auto 30px auto;
    display:flex;
    justify-content: space-between;
    align-items: center;
    border: 2px solid #daa520;
    position: relative;
    flex-wrap: wrap;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 30px 60px rgba(75,44,74,0.6);
}
.card .welcome { flex: 1; min-width: 300px; }
.card .welcome h1 { margin:0 0 20px; font-size:36px; color:#4b2c4a; font-family:'Papyrus', cursive; font-weight:bold; }
.card .welcome h2 { margin:0; font-size:22px; color:#6b4e68; font-family:'Papyrus', cursive; font-weight:600; }

/* ===== PROFILE PIC ===== */
.profile-pic { text-align:center; flex: 0 0 160px; }
.profile-pic img {
    width:130px; height:130px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #daa520;
    box-shadow: 0 6px 15px rgba(139,69,19,0.5);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.profile-pic img:hover {
    transform: scale(1.1) rotate(-3deg);
    box-shadow: 0 10px 20px rgba(139,69,19,0.6);
}
.profile-pic p { margin:8px 0 0 0; font-weight:bold; font-size:18px; color:#4b2c4a; }

/* ===== INFO BOXES ===== */
.info-boxes {
    display: flex;
    gap: 25px;
    margin: 30px auto 50px auto;
    justify-content: center;
    flex-wrap: wrap;
    max-width: 900px;
}
.info-box {
    flex: 1;
    min-width: 200px;
    background: rgba(255, 243, 205, 0.85);
    backdrop-filter: blur(8px);
    border: 2px solid #daa520;
    border-radius: 20px;
    padding: 25px 20px;
    text-align: center;
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s;
}
.info-box:hover {
    transform: translateY(-10px) scale(1.03);
    box-shadow: 0 12px 30px rgba(0,0,0,0.35);
    background: rgba(255, 235, 179, 0.85);
}
.info-box i {
    display: block;
    margin-bottom: 10px;
    font-size: 36px;
    color:#8b4513;
    transition: transform 0.3s, color 0.3s;
}
.info-box:hover i {
    transform: scale(1.3) rotate(15deg);
    color: #d4a017;
}
.info-box h3 { font-size:28px; color:#4b2c4a; font-weight:bold; }
.info-box p { margin-top: 8px; font-size:18px; color:#5a3e50; }

/* ===== AUDIO WIDGET ===== */
.audio-widget {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 70px; height: 70px;
    background: rgba(205,133,63,0.8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(0,0,0,0.5);
    transition: transform 0.2s, background 0.3s;
    z-index: 1200;
    backdrop-filter: blur(4px);
}
.audio-widget:hover {
    transform: scale(1.2);
    background: rgba(222,184,135,0.9);
}
.audio-widget i { font-size: 28px; color: #fffdf5; }

/* ===== RESPONSIVE ===== */
@media(max-width:768px){
    .card { flex-direction: column; gap:30px; padding:30px; }
    .card .welcome { text-align:center; }
    .card h1 { font-size:32px; }
    .card h2 { font-size:20px; }
    .profile-pic img { width:100px; height:100px; }
    .profile-pic p { font-size:16px; }

    .info-boxes { gap:15px; }
    .info-box { padding:20px; min-width:140px; }
    .info-box h3 { font-size:22px; }
    .info-box p { font-size:16px; }
    .info-box i { font-size:28px; }
}
</style>
</head>
<body>

<!-- BACKGROUND SLIDES -->
<div id="bg1" class="bg-slide"></div>
<div id="bg2" class="bg-slide"></div>

<!-- MENU TOGGLE -->
<div class="menu-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <h2>Menu</h2>
    <ul>
        <li><a href="dashboard.php"><i class="fas fa-home"></i> Beranda</a></li>
        <li><a href="t_admin.php"><i class="fas fa-user-cog"></i> Kelola Admin</a></li>
        <li><a href="kategori.php"><i class="fas fa-tags"></i> Kelola Kategori</a></li>
    </ul>
    <div class="logout-section">
        <a href="#" onclick="confirmLogout(event)"><i class="fas fa-door-open"></i> Logout</a>
    </div>
</div>

<!-- CONTENT -->
<div class="content">
    <div class="card">
        <div class="welcome">
            <h1>Selamat Datang di Dashboard Admin</h1>
            <h2>Dunia Budaya Kalimantan Barat</h2>
        </div>
        <div class="profile-pic">
            <a href="profile.php">
                <img src="<?= htmlspecialchars($foto) ?>" alt="Foto Profil">
                 <p><?= htmlspecialchars($username) ?></p>
            </a>
        </div>
    </div>

    <!-- INFO BOXES -->
    <div class="info-boxes">
        <div class="info-box">
            <i class="fas fa-user-shield"></i>
            <h3><?= $count_admin ?></h3>
            <p>Total Admin</p>
        </div>
        <div class="info-box">
            <i class="fas fa-tags"></i>
            <h3><?= $count_kategori ?></h3>
            <p>Total Kategori</p>
        </div>
        <div class="info-box">
            <i class="fas fa-folder-open"></i>
            <h3><?= $count_isi_kategori ?></h3>
            <p>Total Isi Kategori</p>
        </div>
    </div>
</div>

<!-- AUDIO WIDGET -->
<div class="audio-widget" id="audioWidget" title="Klik untuk play/pause musik Dayak">
    <i class="fas fa-play" id="audioIcon"></i>
</div>
<audio id="dayakAudio" loop>
    <source src="uploads/musik_dayak.mp3" type="audio/mpeg">
    Browser Anda tidak mendukung audio.
</audio>

<script>
// SIDEBAR TOGGLE
function toggleSidebar(){ 
    document.getElementById("sidebar").classList.toggle("active"); 
}

// LOGOUT CONFIRM
function confirmLogout(event){
    event.preventDefault();
    if(confirm("Apakah Anda yakin ingin logout?")){
        window.location.href="index.php";
    }
}

// AUDIO WIDGET PLAY/PAUSE
const audio = document.getElementById('dayakAudio');
const audioWidget = document.getElementById('audioWidget');
const audioIcon = document.getElementById('audioIcon');
audioWidget.addEventListener('click', function(){
    if(audio.paused){
        audio.play();
        audioIcon.classList.remove('fa-play');
        audioIcon.classList.add('fa-pause');
    } else {
        audio.pause();
        audioIcon.classList.remove('fa-pause');
        audioIcon.classList.add('fa-play');
    }
});

// DRAG AUDIO WIDGET
let isDragging = false, offsetX, offsetY;
audioWidget.addEventListener('mousedown', e => {
    isDragging = true;
    offsetX = e.clientX - audioWidget.getBoundingClientRect().left;
    offsetY = e.clientY - audioWidget.getBoundingClientRect().top;
});
document.addEventListener('mousemove', e => {
    if(isDragging){
        audioWidget.style.left = (e.clientX - offsetX) + 'px';
        audioWidget.style.top = (e.clientY - offsetY) + 'px';
    }
});
document.addEventListener('mouseup', () => { isDragging = false; });

// SLIDESHOW BACKGROUND
let backgrounds = ['uploads/kalimantan1.jpeg','uploads/kalimantan2.jpg','uploads/kalimantan3.jpg'];
let index = 0, visible = true;
let bg1 = document.getElementById('bg1');
let bg2 = document.getElementById('bg2');
bg1.style.backgroundImage = `url('${backgrounds[index]}')`;
bg1.classList.add('show');
setInterval(() => {
    index = (index + 1) % backgrounds.length;
    if(visible){
        bg2.style.backgroundImage = `url('${backgrounds[index]}')`;
        bg2.classList.add('show');
        bg1.classList.remove('show');
    } else {
        bg1.style.backgroundImage = `url('${backgrounds[index]}')`;
        bg1.classList.add('show');
        bg2.classList.remove('show');
    }
    visible = !visible;
}, 5000);

// NOTIFIKASI SELAMAT DATANG
window.addEventListener('load', function() {
    alert("Selamat datang, <?= htmlspecialchars($username)?>!");
});
</script>

</body>
</html>
