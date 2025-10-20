<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
include '../koneksi.php';
$admin_id = $_SESSION['admin_id'];
$kategori_result = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kelola Kategori - Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;700&family=Roboto:wght@500&display=swap" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    color: #4b2c4a;
    overflow-x: hidden;
    background: linear-gradient(135deg, #e0d2f5, #cbb4ec);
    min-height: 100vh;
}

/* ==== Background slideshow ==== */
.bg-slide {
    position: fixed;
    top:0; left:0; width:100%; height:100%;
    background-size: cover;
    background-position: center;
    transition: opacity 1s ease-in-out;
    z-index: -1;
    opacity: 0;
}
.bg-slide.show { opacity: 1; }
.bg-slide::after { content:""; position:absolute; inset:0; background-color: rgba(139,69,19,0.15); }

/* ==== Sidebar (pakai transform, lebih halus) ==== */
.sidebar {
    position: fixed;
    top: 0;
    width: 250px;
    height: 100%;
    background: linear-gradient(180deg, #5d4037, #3e2723);
    color: #fff8dc;
    transform: translateX(-250px);
    transition: transform 0.45s cubic-bezier(0.25, 0.8, 0.25, 1);
    z-index: 1000;
    border-right: 4px solid #daa520;
}
.sidebar.active { transform: translateX(0); }

.sidebar h2 {
    text-align: center;
    padding: 20px 0 10px 0;
    margin:0;
    font-size: 35px;
    font-family: 'Papyrus', cursive;
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
    transition: background 0.3s;
}
.sidebar ul li a i { margin-right:12px; color:#ffd700; }
.sidebar ul li a:hover { background: linear-gradient(145deg, #8d6e63, #5d4037); }

.logout-section { position: absolute; bottom: 40px; width:100%; border-top:2px solid #663d64; padding-top:5px;}
.logout-section a { display:flex; align-items:center; justify-content:center; padding:18px 20px; font-size:22px; font-weight:bold; color:#fffbe6; text-decoration:none;}
.logout-section a i { margin-right:10px; color:#ffd700; }
.logout-section a:hover { background: rgba(205,133,63,0.85); }

/* ==== Menu Toggle ==== */
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
.menu-toggle:hover { background-color: rgba(222,184,135,0.85); transform: scale(1.1); }

/* ==== Content (ikut geser dengan sidebar) ==== */
.content { 
    width: 100%;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 80px 20px 20px;
    box-sizing: border-box;
    transition: transform 0.45s cubic-bezier(0.25, 0.8, 0.25, 1);
    will-change: transform;
}
.content.shifted { transform: translateX(250px); }

/* ==== Card wrapper ==== */
.card-wrapper {
    background: linear-gradient(145deg, #fff8f0, #ffd699);
    padding: 30px;
    border-radius: 25px;
    box-shadow: 0 30px 60px rgba(75,44,74,0.35);
    border: 3px solid #8b4513;
    width: 100%;
    max-width: 1100px;
    position: relative;
    min-height: 500px;
    display:flex;
    flex-direction:column;
}

/* ==== Judul ==== */
.card-title {
    font-size: 30px;
    font-weight:bold;
    margin-bottom:20px;
    font-family: 'Poppins', sans-serif;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
}
.card-title i { color:#8b4513; }

.card-buttons { position: absolute; top: 40px; right: 20px; }
.btn-tambah {
    padding:10px 18px;
    border:none;
    border-radius:10px;
    font-weight:bold;
    color:white;
    cursor:pointer;
    background: linear-gradient(135deg, #27ae60, #2ecc71);
}
.btn-tambah:hover { background: linear-gradient(135deg, #229954, #58d68d); }

/* ==== Grid kategori ==== */
.kategori-grid {
    flex:1;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:20px;
    margin-top:40px;
}
.kategori-item {
    position:relative;
    border-radius:15px;
    overflow:hidden;
    cursor:pointer;
    background-size:cover;
    background-position:center;
    transition:transform 0.3s ease;
    min-height:260px;
}
.kategori-item:hover { transform:translateY(-5px); }
.kategori-overlay { position:absolute; inset:0; background-color:rgba(0,0,0,0.4); }
.kategori-nama {
    position:absolute; inset:0;
    display:flex; justify-content:center; align-items:center;
    color:white; font-weight:bold; font-size:24px;
    text-shadow:0 0 6px rgba(0,0,0,0.8);
    font-family:'Roboto',sans-serif;
}
.kategori-item .grid-btn {
    position:absolute; bottom:15px;
    padding:6px 12px; font-weight:bold;
    border-radius:6px; text-decoration:none;
    font-size:14px; opacity:0;
    transition:opacity 0.3s ease, transform 0.3s ease;
}
.kategori-item .hapus-btn { left:10px; background:rgba(255,99,71,0.9); color:white; }
.kategori-item .edit-btn { right:10px; background:rgba(60,179,113,0.9); color:white; }
.kategori-item:hover .grid-btn { opacity:1; transform:translateY(0); }

.kategori-placeholder {
    grid-column:1 / -1;
    display:flex; justify-content:center; align-items:center;
    font-size:22px; font-weight:bold;
    color:#4b2c4a; min-height:400px;
}

/* ==== Audio widget ==== */
.audio-widget {
    position:fixed; bottom:30px; right:30px;
    width:70px; height:70px;
    background:rgba(205,133,63,0.8);
    border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer;
    box-shadow:0 8px 20px rgba(0,0,0,0.5);
    transition:transform 0.2s, background 0.3s;
    z-index:1200; backdrop-filter:blur(4px);
}
.audio-widget:hover { transform:scale(1.2); background:rgba(222,184,135,0.9); }
.audio-widget i { font-size:28px; color:#fffdf5; }
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
<div class="content" id="content">
    <div class="card-wrapper">
        <div class="card-title"><i class="fas fa-feather-alt"></i> Kategori Budaya Kalimantan Barat</div>
        <div class="card-buttons">
            <a href="tambah_kategori.php" class="btn-tambah"><i class="fas fa-plus"></i> Tambah</a>
        </div>

        <div class="kategori-grid">
            <?php if(mysqli_num_rows($kategori_result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($kategori_result)): ?>
                    <div class="kategori-item" 
                         style="background-image:url('uploads/<?= htmlspecialchars($row['foto']) ?>');"
                         ondblclick="window.location.href='isi_kategori.php?kategori=<?= $row['id'] ?>'">
                        <div class="kategori-overlay"></div>
                        <div class="kategori-nama"><?= htmlspecialchars($row['nama_kategori']) ?></div>
                        <a href="hapus_kategori.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus kategori ini?');" class="grid-btn hapus-btn">Hapus</a>
                        <a href="edit_kategori.php?id=<?= $row['id'] ?>" class="grid-btn edit-btn">Edit</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="kategori-placeholder">Belum ada kategori</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- AUDIO WIDGET -->
<div class="audio-widget" id="audioWidget" title="Klik untuk play/pause musik Dayak">
    <i class="fas fa-play" id="audioIcon"></i>
</div>
<audio id="dayakAudio" loop>
    <source src="uploads/musik_dayak.mp3" type="audio/mpeg">
</audio>

<script>
function toggleSidebar(){
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    sidebar.classList.toggle('active');
    content.classList.toggle('shifted');
}

function confirmLogout(e){
    e.preventDefault();
    if(confirm("Apakah Anda yakin ingin logout?"))
        window.location.href="index.php";
}

// background slide
let bgs=['uploads/kalimantan1.jpeg','uploads/kalimantan2.jpg','uploads/kalimantan3.jpg'];
let idx=0,v=true;
let bg1=document.getElementById('bg1'), bg2=document.getElementById('bg2');
bg1.style.backgroundImage=`url('${bgs[idx]}')`;
bg1.classList.add('show');
setInterval(()=>{
    idx=(idx+1)%bgs.length;
    if(v){ bg2.style.backgroundImage=`url('${bgs[idx]}')`; bg2.classList.add('show'); bg1.classList.remove('show'); }
    else{ bg1.style.backgroundImage=`url('${bgs[idx]}')`; bg1.classList.add('show'); bg2.classList.remove('show'); }
    v=!v;
},5000);

// Audio control
const audio=document.getElementById('dayakAudio');
const audioWidget=document.getElementById('audioWidget');
const audioIcon=document.getElementById('audioIcon');
audioWidget.addEventListener('click',()=>{
    if(audio.paused){ audio.play(); audioIcon.classList.replace('fa-play','fa-pause'); }
    else{ audio.pause(); audioIcon.classList.replace('fa-pause','fa-play'); }
});
</script>
</body>
</html>
