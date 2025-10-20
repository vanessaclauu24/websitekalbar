<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include '../koneksi.php';
$admin_id = $_SESSION['admin_id'];

// ===== HAPUS ADMIN OTOMATIS =====
// Hapus admin dengan level 'off' dan umur akun > 1 bulan
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

$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Ambil data admin kecuali yang sedang login
$result = mysqli_query($conn, "SELECT * FROM admin WHERE username != '$username' LIMIT $start, $limit");
$total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM admin WHERE username != '$username'");
$total_row = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];
$total_page = ceil($total_data / $limit);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Kelola Admin - Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
/* ===== BODY ===== */
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    color: #4b2c4a;
    overflow-x: hidden;
    background: linear-gradient(135deg, #e0d2f5, #cbb4ec);
    min-height: 100vh;
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

/* ===== LOGOUT SECTION ===== */
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
    width: 100%;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    box-sizing: border-box;
}

/* ===== CARD WRAPPER ===== */
.card-wrapper {
    background: linear-gradient(145deg, #fff8f0, #ffd699);
    padding: 40px 50px;
    border-radius: 25px;
    box-shadow: 0 30px 60px rgba(75,44,74,0.35);
    border: 3px solid #8b4513;
    width: 100%;
    max-width: 1000px;
    text-align: center;
    font-size: 20px;
    display: flex;
    flex-direction: column;
    gap: 25px;
    align-items: center;
    transition: transform 0.3s ease;
}

/* ===== CARD HEADER ===== */
.card-header {
    display: flex;
    justify-content: space-between; 
    align-items: center;
    width: 100%;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.card-header h3 {
    font-size: 32px;
    margin: 0;
    text-align: left;
}
.card-header .buttons-left a {
    font-size: 18px;
    padding: 10px 18px;
}

/* ===== TABLE ===== */
.table-responsive { width: 100%; overflow-x: auto; }
table, thead tr th, tbody tr td { font-size: 18px; }
table { width: 100%; border-collapse: separate; border-spacing: 0 10px; color: #4b2c4a; }
thead tr { background: linear-gradient(135deg, #5d4037, #3e2723); color: #fff8dc; font-weight: 700; border-radius: 12px; }
thead tr th { padding: 12px 15px; text-align: left; }
tbody tr { background: #fff8f0; box-shadow: 0 4px 8px rgba(75,44,74,0.1); border-radius: 12px; transition: background 0.3s; }
tbody tr:hover { background: #ffd699; }
tbody tr td { padding: 12px 15px; vertical-align: middle; list-style: none; }

/* ===== BUTTONS ===== */
.btn-brown { background: linear-gradient(135deg, #472d0fff, #685141ff); color: white; padding: 8px 14px; border: none; border-radius: 8px; font-weight: bold; text-decoration: none; display: inline-block; }
.btn-brown:hover { background: linear-gradient(135deg, #8b4513, #c76b39); transform: scale(1.03); }
.btn-gray { background-color: #95a5a6; color: white; padding: 8px 14px; border: none; border-radius: 8px; font-weight: bold; text-decoration: none; display: inline-block; }
.btn-gray:hover { background-color: #7f8c8d; transform: scale(1.03); }

/* ===== BADGE ===== */
.badge { font-size: 0.9em; padding: 6px 10px; border-radius: 8px; font-weight: 600; }
.bg-success { background-color: #28a745 !important; color: white; }
.bg-secondary { background-color: #6c757d !important; color: white; }

/* ===== PAGINATION ===== */
.pagination { margin-top: 20px; display:flex; justify-content:center; gap:5px; flex-wrap:wrap; list-style: none; padding-left: 0; }
.pagination .page-item .page-link { color: #4b2c4a; border-radius: 8px; border: 1px solid transparent; padding: 6px 12px; font-weight: 600; text-decoration: none; }
.pagination .page-item.active .page-link { background-color: #5d4037; border-color: #5d4037; color: #fff8dc; }
.pagination .page-item .page-link:hover { background-color: #8d6e63; color: #fff8dc; }

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
.audio-widget:hover { transform: scale(1.2); background: rgba(222,184,135,0.9); }
.audio-widget i { font-size: 28px; color: #fffdf5; }
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
    <li><a href="kategori.php"><i class="fas fa-list"></i> Kelola Kategori</a></li>
  </ul>
  <div class="logout-section">
    <a href="#" onclick="confirmLogout(event)"><i class="fas fa-door-open"></i> Logout</a>
  </div>
</div>

<!-- CONTENT -->
<div class="content">
  <div class="card-wrapper">
    <div class="card-header">
      <h3><i class="fas fa-user-cog"></i> Data Admin</h3>
      <div class="buttons-left">
        <a href="create_admin.php" class="btn-brown">+ Tambah Admin</a>
        <a href="dashboard.php" class="btn-gray">Kembali</a>
      </div>
    </div>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Username</th>
            <th>Email</th>
            <th>Level</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = $start + 1; ?>
          <?php while ($row = mysqli_fetch_assoc($result)) : ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td>
              <?php if ($row['level'] == 'on') : ?>
                <span class="badge bg-success">Aktif</span>
              <?php else : ?>
                <span class="badge bg-secondary">Nonaktif</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($row['level'] == 'on') : ?>
                <a href='toogle.php?id=<?= $row['id'] ?>&level=off&page=<?= $page ?>' class='btn-gray'>Nonaktifkan</a>
              <?php else : ?>
                <a href='toogle.php?id=<?= $row['id'] ?>&level=on&page=<?= $page ?>' class='btn-brown'>Aktifkan</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- PAGINATION -->
    <nav>
      <ul class="pagination">
        <?php if ($page > 1): ?>
          <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">&laquo;</a></li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_page; $i++): ?>
          <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <?php if ($page < $total_page): ?>
          <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">&raquo;</a></li>
        <?php endif; ?>
      </ul>
    </nav>
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
function toggleSidebar() { 
    const sidebar = document.getElementById("sidebar");
    const card = document.querySelector(".card-wrapper");
    sidebar.classList.toggle("active"); 

    if(sidebar.classList.contains("active")){
        card.style.transform = "translateX(250px)";
    } else {
        card.style.transform = "translateX(0)";
    }
}
function confirmLogout(e){ e.preventDefault(); if(confirm("Apakah Anda yakin ingin logout?")){ window.location.href="index.php"; } }

// Background slideshow
let backgrounds = ['uploads/kalimantan1.jpeg','uploads/kalimantan2.jpg','uploads/kalimantan3.jpg'];
let index = 0, visible = true;
let bg1 = document.getElementById('bg1');
let bg2 = document.getElementById('bg2');
bg1.style.backgroundImage = `url('${backgrounds[index]}')`;
bg1.classList.add('show');
setInterval(()=>{
    index=(index+1)%backgrounds.length;
    if(visible){ bg2.style.backgroundImage=`url('${backgrounds[index]}')`; bg2.classList.add('show'); bg1.classList.remove('show'); } 
    else { bg1.style.backgroundImage=`url('${backgrounds[index]}')`; bg1.classList.add('show'); bg2.classList.remove('show'); }
    visible=!visible;
},5000);

// Audio widget
const audio=document.getElementById('dayakAudio');
const audioWidget=document.getElementById('audioWidget');
const audioIcon=document.getElementById('audioIcon');
audioWidget.addEventListener('click',()=>{
    if(audio.paused){ audio.play(); audioIcon.classList.remove('fa-play'); audioIcon.classList.add('fa-pause'); } 
    else{ audio.pause(); audioIcon.classList.remove('fa-pause'); audioIcon.classList.add('fa-play'); }
});
// Drag
let isDragging=false, offsetX, offsetY;
audioWidget.addEventListener('mousedown', e=>{ isDragging=true; offsetX=e.clientX-audioWidget.getBoundingClientRect().left; offsetY=e.clientY-audioWidget.getBoundingClientRect().top; });
document.addEventListener('mousemove', e=>{ if(isDragging){ audioWidget.style.left=(e.clientX-offsetX)+"px"; audioWidget.style.top=(e.clientY-offsetY)+"px"; audioWidget.style.right="auto"; audioWidget.style.bottom="auto"; audioWidget.style.position="fixed"; }}); 
document.addEventListener('mouseup', ()=>isDragging=false);
</script>
</body>
</html>