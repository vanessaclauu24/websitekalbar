<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include '../koneksi.php';
$kategori_id = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;
if ($kategori_id <= 0) die("Kategori tidak ditemukan.");

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
<title>Isi Kategori <?= htmlspecialchars($kategori_name) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
    margin:0;
    font-family: 'Segoe UI', sans-serif;
    background: #dbc3a3 url('uploads/background batik kalbar.png') no-repeat center center fixed;
    background-size: cover;
}
.container {
    max-width:1200px;
    margin:50px auto;
    padding:0 20px;
    position: relative;
}
.top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.top-bar a {
    padding:15px 30px;
    font-size:18px;
    font-weight:bold;
    text-decoration:none;
    color:white;
    border-radius:10px;
    background: linear-gradient(135deg,#5C4033,#b37d5b);
    box-shadow:0 4px 8px rgba(0,0,0,0.2);
    transition: transform 0.2s, background 0.3s;
}
.top-bar a:hover {
    transform: scale(1.05);
    background: linear-gradient(135deg,#9c6747,#81533b);
}
.card-wrapper {
    background: rgba(255,248,240,0.85);
    padding:20px 30px 30px 30px;
    border-radius:25px;
    box-shadow:0 20px 40px rgba(0,0,0,0.2);
    border:3px solid #8b4513;
    backdrop-filter: blur(5px);
}
.card-wrapper .title {
    font-family: 'Times New Roman', Times, serif;
    font-size:40px;
    font-weight:bold;
    color:#C79C6E;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    text-align:center;
    margin-bottom:25px;
}

/* ===== GRID 3x3 STYLE ===== */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
    gap: 35px;
    justify-content: start;
    align-items: start;
}
.grid-item-wrapper {
    width: 100%;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.25);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.grid-item-wrapper:hover {
    transform: scale(1.02);
    box-shadow: 0 10px 20px rgba(0,0,0,0.3);
}
.grid-item {
    position: relative;
    width: 100%;
    height: 320px;
    overflow: hidden;
    border-bottom: 3px solid #b37d5b;
}
.grid-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.grid-item:hover img {
    transform: scale(1.05);
}
.overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.35);
    color: white;
    font-weight: bold;
    font-size: 22px;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    transition: background 0.3s ease;
}
.grid-item:hover .overlay {
    background: rgba(0,0,0,0.5);
}
.button-box {
    display:flex;
    justify-content:space-between;
    background:white;
    padding:15px 10px;
}
.button-box a {
    background:#8b5e3c;
    color:white;
    padding:8px 20px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
    transition: transform 0.2s, background 0.3s;
}
.button-box a:hover {
    transform: scale(1.05);
    background:#6e4b32;
}
.empty-msg {
    text-align:center;
    font-size:20px;
    font-weight:bold;
    padding:40px 0;
    color:#444;
}

/* ===== POPUP EDIT ===== */
.popup {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
.popup-content {
    background: linear-gradient(135deg, #fff5eb, #f7e1c4);
    border-radius: 20px;
    display: flex;
    flex-wrap: wrap;
    width: 85%;
    max-width: 950px;
    padding: 30px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    border: 4px solid #8b5e3c;
    position: relative;
    opacity: 0;
    transform: translateX(20px);
    transition: all 0.4s ease;
}
.popup-content.show {
    opacity: 1;
    transform: translateX(0);
}
.popup-left {
    flex: 1.2;
    min-width: 300px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding-top: 40px;
}
.image-container {
    width: 90%;
    max-width: 350px;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    margin-bottom: 15px;
}
.image-container img {
    width: 100%;
    height: 320px;
    object-fit: cover;
    border-radius: 15px;
    border: 3px solid #b08968;
}
#fotoInput { display: none; }
.custom-file-upload {
    display: inline-block;
    background: linear-gradient(135deg,#8b5e3c,#b37d5b);
    color: white;
    padding: 12px 35px;
    border-radius: 30px;
    cursor: pointer;
    font-weight: bold;
    font-size: 16px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    transition: all 0.3s;
}
.custom-file-upload:hover {
    transform: scale(1.07);
    background: linear-gradient(135deg,#9c6747,#6e4b32);
}
.popup-right {
    flex: 1.8;
    padding: 0 30px;
}
.popup-right label {
    font-weight: bold;
    color: #5a3d2b;
    margin-bottom: 6px;
    display: block;
    font-size: 20px;
}
.popup input[type="text"],
.popup textarea {
    width: 100%;
    padding: 14px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: 1px solid #c6a688;
    background: #fff9f4;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    font-size: 17px;
}
.popup textarea { height: 180px; resize: none; }
.popup-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 25px;
}
.btn {
    padding: 13px 30px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    font-size: 17px;
    transition: all 0.3s;
}
.btn-green { background: #28a745; color: white; }
.btn-green:hover { background: #218838; transform: scale(1.05); }
.btn-blue { background: #007bff; color: white; }
.btn-blue:hover { background: #0056b3; transform: scale(1.05); }

/* ===== TOAST ===== */
#toast {
    position: fixed;
    top: 25px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 99999;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(6px);
    border-left: 8px solid #28a745;
    color: #333;
    padding: 15px 30px;
    border-radius: 12px;
    font-weight: bold;
    font-size: 18px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.25);
    display: none;
    text-align: center;
    animation: fadeSlide 3s ease;
}
#toast.error { border-left-color: #dc3545; }
@keyframes fadeSlide {
    0% { opacity: 0; transform: translate(-50%, -40px); }
    10%,90% { opacity: 1; transform: translate(-50%, 0); }
    100% { opacity: 0; transform: translate(-50%, -40px); }
}

/* ===== NAVIGATION BUTTONS ===== */
.nav-btn {
    position: fixed;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(139, 94, 60, 0.9);
    color: white;
    border: none;
    font-size: 34px;
    width: 65px;
    height: 65px;
    border-radius: 50%;
    cursor: pointer;
    z-index: 10000;
    transition: background 0.3s, transform 0.3s;
    display: none;
}
.nav-btn:hover {
    background: rgba(108, 72, 44, 0.95);
    transform: translateY(-50%) scale(1.1);
}
.prev-btn { left: 30px; }
.next-btn { right: 30px; }

@media(max-width:768px){
    .grid { grid-template-columns: 1fr; gap:20px; }
    .top-bar { flex-direction: column; gap:10px; }
    .popup-content { flex-direction: column; width: 95%; padding: 20px; }
    .popup-right { padding: 10px 0; }
    .image-container img { height: 250px; }
    .nav-btn { width: 50px; height: 50px; font-size: 26px; }
    .prev-btn { left: 15px; }
    .next-btn { right: 15px; }
}
</style>
</head>
<body>

<div id="toast"></div>

<div class="container">
    <div class="top-bar">
        <a href="kategori.php"><i class="fas fa-arrow-left"></i> Kembali</a>
        <a href="tambah_isi_kategori.php?kategori=<?= $kategori_id ?>"><i class="fas fa-plus"></i> Tambah</a>
    </div>

    <div class="card-wrapper">
        <div class="title"><?= htmlspecialchars($kategori_name) ?></div>

        <?php if($isi_result->num_rows>0): ?>
        <div class="grid">
            <?php while($row=$isi_result->fetch_assoc()): ?>
                <div class="grid-item-wrapper" 
                    data-id="<?= $row['id'] ?>"
                    data-nama="<?= htmlspecialchars($row['nama_item']) ?>"
                    data-deskripsi="<?= htmlspecialchars($row['deskripsi']) ?>"
                    data-foto="<?= htmlspecialchars($row['foto']) ?>"
                    data-link="<?= htmlspecialchars($row['link']) ?>"
                    data-sumber_foto="<?= htmlspecialchars($row['sumber_foto'] ?? '') ?>"
                    onclick="openPopup(this)">
                    <div class="grid-item">
                        <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" alt="<?= htmlspecialchars($row['nama_item']) ?>">
                        <div class="overlay"><?= htmlspecialchars($row['nama_item']) ?></div>
                    </div>
                    <div class="button-box">
                        <a href="hapus_isi_kategori.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                        <a href="javascript:void(0)" onclick="event.stopPropagation(); openPopup(this.parentElement.parentElement)">Edit</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
            <p class="empty-msg">Belum ada item di kategori ini.</p>
        <?php endif; ?>
    </div>
</div>

<!-- POPUP EDIT -->
<div class="popup" id="popupEdit">
    <button class="nav-btn prev-btn" onclick="navigatePopup(-1)"><i class="fas fa-chevron-left"></i></button>
    <button class="nav-btn next-btn" onclick="navigatePopup(1)"><i class="fas fa-chevron-right"></i></button>

    <div class="popup-content" id="popupContent">
        <div class="popup-left">
            <div class="image-container">
                <img id="popupFoto" src="" alt="Foto Item">
            </div>
            <label for="fotoInput" class="custom-file-upload">Pilih File</label>
            <input type="file" id="fotoInput" accept="image/*">
        </div>
        <div class="popup-right">
            <input type="hidden" id="editId">
            <label>Nama</label>
            <input type="text" id="editNama">
            <label>Deskripsi</label>
            <textarea id="editDeskripsi"></textarea>
            <label>Link Video</label>
            <input type="text" id="editLink">
            <label>Sumber Foto</label>
            <input type="text" id="editSumberFoto" placeholder="contoh: Wikipedia / https://example.com">
            <div class="popup-buttons">
                <button class="btn btn-green" onclick="closePopup()">Kembali</button>
                <button class="btn btn-blue" onclick="saveChanges()">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentIndex = 0;
let allItems = [];

function openPopup(el){
    allItems = Array.from(document.querySelectorAll('.grid-item-wrapper'));
    currentIndex = allItems.indexOf(el);

    const popup = document.getElementById('popupEdit');
    const popupContent = document.getElementById('popupContent');

    popup.style.display='flex';
    document.querySelectorAll('.nav-btn').forEach(btn => btn.style.display = 'block');
    
    popupContent.classList.remove('show');
    setTimeout(()=> popupContent.classList.add('show'), 50);

    const id = el.dataset.id;
    document.getElementById('editId').value=id;
    document.getElementById('editNama').value=el.dataset.nama;
    document.getElementById('editDeskripsi').value=el.dataset.deskripsi;
    document.getElementById('editLink').value=el.dataset.link;
    document.getElementById('editSumberFoto').value=el.dataset.sumber_foto || '';
    document.getElementById('popupFoto').src='uploads/'+el.dataset.foto;
}

function navigatePopup(dir){
    if(!allItems.length) return;
    currentIndex += dir;
    if(currentIndex < 0) currentIndex = allItems.length - 1;
    if(currentIndex >= allItems.length) currentIndex = 0;

    const nextItem = allItems[currentIndex];
    const popupContent = document.getElementById('popupContent');

    popupContent.classList.remove('show');
    setTimeout(() => {
        openPopup(nextItem);
    }, 200);
}

function closePopup(){
    document.getElementById('popupEdit').style.display='none';
    document.querySelectorAll('.nav-btn').forEach(btn => btn.style.display = 'none');
}

function showToast(message, success=true){
    const toast=document.getElementById('toast');
    toast.innerText=message;
    toast.className=success?'':'error';
    toast.style.display='block';
    setTimeout(()=>{ toast.style.display='none'; },3000);
}

function saveChanges(){
    const id = document.getElementById('editId').value;
    const nama = document.getElementById('editNama').value.trim();
    const deskripsi = document.getElementById('editDeskripsi').value;
    const link = document.getElementById('editLink').value;
    const sumber_foto = document.getElementById('editSumberFoto').value;
    const foto = document.getElementById('fotoInput').files[0];

    if(!nama){
        showToast('Nama tidak boleh kosong!', false);
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('nama', nama);
    formData.append('deskripsi', deskripsi);
    formData.append('link', link);
    formData.append('sumber_foto', sumber_foto);
    if(foto) formData.append('foto', foto);

    fetch('update_isi_kategori.php', { method:'POST', body:formData })
    .then(r => r.text())
    .then(res => {
        if(res.includes('berhasil')){
            showToast('Perubahan disimpan!');
            
            const currentEl = allItems[currentIndex];
            currentEl.dataset.nama = nama;
            currentEl.dataset.deskripsi = deskripsi;
            currentEl.dataset.link = link;
            currentEl.dataset.sumber_foto = sumber_foto;
            currentEl.querySelector('.overlay').innerText = nama;

            if(foto){
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('popupFoto').src = e.target.result;
                    currentEl.querySelector('img').src = e.target.result;
                };
                reader.readAsDataURL(foto);
            }
        } else {
            showToast(res, false); 
        }
    })
    .catch(err => showToast('Terjadi kesalahan', false));
}
</script>
</body>
</html>
