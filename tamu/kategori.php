<?php
session_start();
include '../koneksi.php';

// Ambil id kategori dari URL
$kategori_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($kategori_id <= 0) die("Kategori tidak valid");

// Ambil nama kategori
$stmt = $conn->prepare("SELECT nama_kategori FROM kategori WHERE id=?");
$stmt->bind_param("i", $kategori_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) die("Kategori tidak ditemukan.");
$kategori_name = $res->fetch_assoc()['nama_kategori'];

// Ambil isi kategori
$stmt2 = $conn->prepare("SELECT * FROM isi_kategori WHERE kategori_id=? ORDER BY id DESC");
$stmt2->bind_param("i", $kategori_id);
$stmt2->execute();
$isi_result = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($kategori_name) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background: url('../admin/uploads/batik_kalimantan_barat.jpg') no-repeat center center fixed;
    background-size: cover;
    position: relative;
}
/* overlay gelap di background saja */
body::after {
    content: "";
    position: fixed;
    inset: 0;
    background-color: rgba(0,0,0,0.3); /* tingkat transparansi bisa diubah */
    z-index: 0; /* di belakang kontainer */
}
.container {
    max-width:1200px;
    margin:50px auto;
    padding:0 20px;
     position: relative;
    z-index: 1; /* supaya konten muncul di atas overlay */
}
.top-bar a {
    padding:12px 25px;
    font-size:16px;
    font-weight:bold;
    text-decoration:none;
    color:white;
    border-radius:10px;
    background: linear-gradient(135deg,#5C4033,#b37d5b);
    margin-bottom:20px;
    display:inline-block;
}
.top-bar a:hover { background: linear-gradient(135deg,#9c6747,#81533b); }
.card-wrapper {
    background: rgba(255,248,240,0.85);
    padding:20px 30px;
    border-radius:25px;
    box-shadow:0 20px 40px rgba(0,0,0,0.2);
    border:3px solid #8b4513;
    backdrop-filter: blur(5px);
}
.card-wrapper .title {
    font-family:'Times New Roman',Times,serif;
    font-size:36px;
    font-weight:bold;
    color:#C79C6E;
    text-align:center;
    margin-bottom:25px;
}
.grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:30px;
}
.grid-item-wrapper {
    width:100%;
    background: rgba(255,255,255,0.95);
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 4px 10px rgba(0,0,0,0.25);
    transition: transform 0.3s;
    cursor:pointer;
    display:flex;
    flex-direction:column;
    justify-content:flex-start;
}
.grid-item-wrapper:hover { transform:scale(1.02); }
.grid-item {
    position:relative;
    width:100%;
    height:300px;
    overflow:hidden;
    border-bottom:3px solid #b37d5b;
}
.grid-item img { width:100%; height:100%; object-fit:cover; transition: transform 0.4s ease; }
.grid-item:hover img { transform:scale(1.05); }
.overlay {
    position:absolute;
    inset:0;
    background:rgba(0,0,0,0.35);
    color:white;
    font-weight:bold;
    font-size:22px;
    display:flex;
    justify-content:center;
    align-items:center;
    text-align:center;
    transition: background 0.3s ease;
}
.grid-item:hover .overlay { background: rgba(0,0,0,0.5); }
.preview-desc {
    text-align:center;
    margin:15px 20px;
    font-size:14px;
    color:#444;
    line-height:1.4;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 4.2em;
}
.btn-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}
.play-btn {
    width:40px;
    height:40px;
    background:#aaa;
    color:white;
    border-radius:50%;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:16px;
    text-decoration:none;
    flex-shrink:0;
    transition: transform 0.2s, background 0.3s;
}
.play-btn:hover { transform: scale(1.1); background:#888; }
.detail-btn {
    width:200px;
    padding:10px 0;
    background:#8b5e3c;
    color:white;
    border-radius:12px;
    font-weight:bold;
    text-align:center;
    text-decoration:none;
    transition: transform 0.2s, background 0.3s;
}
.detail-btn:hover { transform: scale(1.05); background:#6e4b32; }

/* POPUP */
.popup {
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.6);
    justify-content:center;
    align-items:center;
    z-index:9999;
    overflow-y:auto;
    padding:20px;
}
.popup-content {
    background: linear-gradient(135deg,#fff5eb,#f7e1c4);
    border-radius:20px;
    width:90%;
    max-width:600px;
    padding:25px;
    box-shadow:0 15px 40px rgba(0,0,0,0.3);
    position:relative;
    overflow-y:auto;
    max-height:70vh;
}
.popup-content img {
    width:100%;
    max-height:300px;
    object-fit:cover;
    border-radius:15px;
    margin-bottom:15px;
}

/* Close button di luar popup */
.popup-close-outside {
    position: absolute;
    width:35px;
    height:35px;
    border:none;
    border-radius:50%;
    background:#fff;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:18px;
    cursor:pointer;
    box-shadow:0 2px 5px rgba(0,0,0,0.2);
    transition: transform 0.2s ease, background 0.2s ease;
    z-index:1001;
}
.popup-close-outside i { color:#333; }
.popup-close-outside:hover { background:#f0f0f0; transform:scale(1.1); }

.popup-content h2 {
    font-size:26px;
    color:#8b5e3c;
    margin-bottom:10px;
    text-align:center;
}
.popup-content p {
    font-size:15px;
    color:#444;
    margin-bottom:10px;
    text-align:justify;
}
.popup-content a { color:#1a73e8; text-decoration:none; }
.popup-content a:hover { text-decoration:underline; }

body.modal-open { position: fixed; overflow: hidden; width: 100%; }

@media(max-width:768px){ 
    .grid { grid-template-columns:1fr; gap:20px; } 
    .btn-wrapper { justify-content:center; }
}
</style>
</head>
<body>

<div class="container">
    <div class="top-bar">
        <a href="javascript:void(0);" onclick="goBack()"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <div class="card-wrapper">
        <div class="title"><?= htmlspecialchars($kategori_name) ?></div>
        <?php if($isi_result->num_rows>0): ?>
        <div class="grid">
            <?php while($row=$isi_result->fetch_assoc()):
                $fotoPath = '../admin/uploads/'.$row['foto'];
                if(!file_exists($fotoPath) || empty($row['foto'])) $fotoPath='../uploads/placeholder.jpg';
                $linkVideo = str_replace("watch?v=", "embed/", $row['link']);
            ?>
            <div class="grid-item-wrapper" 
                 data-nama="<?= htmlspecialchars($row['nama_item']) ?>"
                 data-deskripsi="<?= htmlspecialchars($row['deskripsi']) ?>"
                 data-sumber="<?= htmlspecialchars($row['sumber_foto'] ?? '') ?>"
                 data-foto="<?= $fotoPath ?>"
                 data-video="<?= htmlspecialchars($row['link']) ?>">

                <div class="grid-item">
                    <img src="<?= $fotoPath ?>" alt="<?= htmlspecialchars($row['nama_item']) ?>">
                    <div class="overlay"><?= htmlspecialchars($row['nama_item']) ?></div>
                </div>

                <div class="preview-desc">
                    <?= htmlspecialchars(mb_strimwidth($row['deskripsi'], 0, 200, '...')) ?>
                </div>

                <div class="btn-wrapper">
                    <?php if(!empty($row['link'])): ?>
                    <a class="play-btn" href="javascript:void(0)" 
                       onclick="openVideoPopup('<?= htmlspecialchars($linkVideo) ?>')">
                       <i class="fas fa-play"></i>
                    </a>
                    <?php endif; ?>
                    <a class="detail-btn" href="javascript:void(0)" 
                       onclick="openPopup(this.parentElement.parentElement)">
                       Detail
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
            <p class="empty-msg">Belum ada item di kategori ini.</p>
        <?php endif; ?>
    </div>
</div>

<!-- POPUP DETAIL -->
<div class="popup" id="popupDetail">
    <div class="popup-content">
        <img id="popupFoto" src="" alt="Foto Item">
        <h2 id="popupNama"></h2>
        <p id="popupDeskripsi"></p>
        <p id="popupSumber"></p>
    </div>
    <button class="popup-close-outside" onclick="closePopup()">
        <i class="fas fa-xmark"></i>
    </button>
</div>

<!-- POPUP VIDEO -->
<div class="popup" id="popupVideo">
    <div class="popup-content">
        <iframe id="videoFrame" width="100%" height="300" 
                src="" frameborder="0" allowfullscreen 
                allow="autoplay; encrypted-media"></iframe>
    </div>
    <button class="popup-close-outside" onclick="closeVideoPopup()">
        <i class="fas fa-xmark"></i>
    </button>
</div>

<script>
let scrollY = 0;

function goBack() {
    if (document.referrer !== "") history.back();
    else window.location.href = "home_tamu.php";
}

function positionCloseButton(popupId) {
    const popup = document.getElementById(popupId);
    const btn = popup.querySelector('.popup-close-outside');
    const content = popup.querySelector('.popup-content');
    if (!btn || !content) return;
    const rect = content.getBoundingClientRect();
    btn.style.top = (rect.top + window.scrollY - 10) + 'px';
    btn.style.left = (rect.right + 10 + window.scrollX) + 'px';
}

function openPopup(el) {
    scrollY = window.scrollY;
    document.body.style.top = `-${scrollY}px`;
    document.body.classList.add('modal-open');

    document.getElementById('popupFoto').src = el.dataset.foto;
    document.getElementById('popupNama').innerText = el.dataset.nama;
    document.getElementById('popupDeskripsi').innerText = el.dataset.deskripsi || '-';
    document.getElementById('popupSumber').innerHTML = "<strong>Sumber Foto: </strong>" + (el.dataset.sumber || '-');

    const popup = document.getElementById('popupDetail');
    popup.style.display = 'flex';
    positionCloseButton('popupDetail');
}

function closePopup() {
    const popup = document.getElementById('popupDetail');
    popup.style.display = 'none';
    document.body.classList.remove('modal-open');
    document.body.style.top = '';
    window.scrollTo(0, scrollY);
}

function openVideoPopup(url) {
    if(!url) return; // jika tidak ada link, popup tidak muncul
    scrollY = window.scrollY;
    document.body.style.top = `-${scrollY}px`;
    document.body.classList.add('modal-open');

    document.getElementById('videoFrame').src = url;
    const popup = document.getElementById('popupVideo');
    popup.style.display = 'flex';
    positionCloseButton('popupVideo');
}

function closeVideoPopup() {
    const popup = document.getElementById('popupVideo');
    popup.style.display = 'none';
    document.body.classList.remove('modal-open');
    document.body.style.top = '';
    window.scrollTo(0, scrollY);
    document.getElementById('videoFrame').src = '';
}
</script>

</body>
</html>
