<?php
// home_tamu.php
session_start();
include '../koneksi.php';

// Ambil semua kategori dari database
$kategoriQuery = "SELECT * FROM kategori ORDER BY id ASC";
$kategoriResult = mysqli_query($conn, $kategoriQuery);

if (!$kategoriResult) {
    die("Query gagal: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Website Kalimantan Barat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { 
  margin:0; 
  font-family:'Segoe UI', sans-serif; 
  background:#ffff;
  overflow-x: hidden; /* biar layar gak bisa geser ke samping */
}

/* Navbar */
nav { 
  background:#3c3c3c; 
  padding:10px 60px; 
  display:flex; 
  justify-content:space-between; 
  align-items:center; 
  color:#fff; 
  box-shadow:0 2px 5px rgba(0,0,0,0.3); 
  position:relative; 
  z-index:10; 
}
nav .logo { 
  font-size:1.3em; 
  font-weight:bold; 
  text-decoration:none; 
  color:#fff; 
}
nav .menu { 
  display:flex; 
  align-items:center; 
  position:relative; 
  gap:25px; 
}
nav .menu a { 
  color:#fff; 
  text-decoration:none; 
  padding:5px 10px; 
  border-radius:5px; 
  transition:0.3s; 
}
nav .menu a:hover { 
  background:#6c757d; 
  color:#fff; 
}

/* Dropdown — diperbaiki */
.dropdown { 
  position:relative; 
  cursor:pointer; 
}
.dropdown > a { 
  color:#fff; 
  text-decoration:none; 
  padding:6px 12px; 
  display:inline-block; 
  border-radius:6px; 
}
.dropdown > a:hover { 
  background:#6c757d; 
}
.dropdown-content { 
  display:none; 
  position:absolute; 
  top:46px; 
  left:50%; 
  transform:translateX(-50%); 
  background:#2f2f2f; 
  min-width:240px; 
  border-radius:8px; 
  box-shadow:0 4px 12px rgba(0,0,0,0.3); 
  overflow:hidden; 
  z-index:9999; 
  padding:5px 0; 
  max-width:90vw; /* biar gak offside di layar kecil */
}
.dropdown-content a {
  color:#fff;
  padding:10px 18px;
  text-decoration:none;
  display:block;
  transform: translateY(-10px);
  opacity: 0;
  transition: all 0.28s ease;
  border-bottom: 1px solid rgba(255,255,255,0.12);
  text-align:left;
  white-space:nowrap;
}
.dropdown-content a:last-child { border-bottom: none; }
.dropdown-content a.show { transform: translateY(0); opacity: 1; }
.dropdown-content a:hover { background:#575757; color:#fff; }

/* Responsif dropdown */
@media (max-width:768px){
  .dropdown-content {
    left:50%;
    transform:translateX(-50%);
    width:80%;
  }
}
@media (max-width:420px){
  .dropdown-content {
    left:50%;
    transform:translateX(-50%);
    width:90%;
    min-width:unset;
  }
}

/* Header */
header { 
  background:url('https://www.iwarebatik.org/wp-content/uploads/2019/11/pulau-derawan-1.jpg') center/cover no-repeat; 
  height:350px; 
  position:relative; 
  text-align:center; 
  display:flex; 
  align-items:center; 
  justify-content:center; 
  color:white; 
}
header::after { 
  content:''; 
  position:absolute; 
  top:0; left:0; right:0; bottom:0; 
  background-color: rgba(0,0,0,0.4); 
}
header h1 { 
  position:relative; 
  font-size:2.5em; 
  font-weight:500; 
  text-shadow:0 2px 6px rgba(0,0,0,0.5); 
}

/* Section Asal Usul */
.section {
    background:#f9f9f9;
    width:90%;
    max-width:1200px;
    margin:40px auto;
    padding:50px 60px;
    border-radius:8px;
    border:1px solid #ddd;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
    min-height: 80vh;
}
.section h2 {
    text-align:center;
    font-size:2em;
    background:#e8f5e9;
    padding:15px;
    border-radius:5px;
    color:#2e7d32;
    margin-bottom:30px;
    border:1px solid #c8e6c9;
}
.section p {
    text-align:justify;
    color:#333;
    line-height:1.9;
    font-size:1.15em;
    margin-bottom:20px;
}

/* Kategori Cards */
.kategori-container { 
  display:flex; 
  flex-wrap:wrap; 
  gap:20px; 
  justify-content:center; 
  margin:20px auto; 
  max-width:1100px; 
}
.kategori-card { 
  width:220px; 
  border:1px solid #ddd; 
  border-radius:8px; 
  overflow:hidden; 
  box-shadow:0 2px 6px rgba(0,0,0,0.1); 
  text-align:center; 
  background:#fff; 
  transition:transform 0.3s, opacity 0.3s; 
  opacity:0; 
  transform:translateY(10px); 
}
.kategori-card.show { opacity:1; transform:translateY(0); }
.kategori-card:hover { transform:translateY(-5px); }
.kategori-card img { width:100%; height:150px; object-fit:cover; }
.kategori-card h3 { padding:10px; font-size:1em; color:#2e7d32; }

/* Timeline */
.timeline {
  display:flex;
  flex-direction:column;
  justify-content:flex-start;
  padding:20px;
  border-left:4px solid #2e7d32;
  width:50%;
  margin:0 auto;
  position:relative;
}
.timeline-item {
  position:relative;
  margin-bottom:40px;
  opacity:0;
  transform:translateY(20px);
  transition: all 0.5s ease;
}
.timeline-item::before {
  content:''; position:absolute; left:-12px; top:0; width:20px; height:20px; background:#2e7d32; border-radius:50%; border:3px solid #fff;
}
.timeline-date { font-weight:bold; margin-bottom:5px; color:#2e7d32; }
.timeline-content h3 { margin:0 0 5px; font-size:1.1em; }
.timeline-content p { margin:0; color:#333; }
.timeline-content img { width:100%; border-radius:5px; margin-top:5px; }

footer { 
  background:#333;  
  color:#ddd; 
  text-align:center; 
  padding:15px 0; 
  margin-top:40px; 
}

@media(max-width:1024px){ .timeline { width:100%; padding-left:20px; } }
@media(max-width:768px){ header h1{ font-size:1.8em; padding:0 10px;} nav{flex-direction:column; gap:10px;} .kategori-container{flex-direction:column; align-items:center;} }
</style>
</head>
<body>

<nav>
  <a href="#" class="logo">Website Kalimantan Barat</a>
  <div class="menu">
    <a href="#">Home</a>
    <div class="dropdown" id="kategoriDropdown">
      <a id="kategoriBtn">Kategori ▾</a>
      <div class="dropdown-content" id="kategoriList">
        <?php
        if(mysqli_num_rows($kategoriResult) > 0){
            mysqli_data_seek($kategoriResult, 0);
            while($row = mysqli_fetch_assoc($kategoriResult)){
                echo '<a href="kategori.php?id='.$row['id'].'">'.htmlspecialchars($row['nama_kategori']).'</a>';
            }
        } else {
            echo '<a href="#">Kategori kosong</a>';
        }
        ?>
      </div>
    </div>
  </div>
</nav>

<header>
  <h1>Selamat datang di <br>Website Kalimantan Barat</h1>
</header>

<!-- Asal Usul Kalimantan Barat -->
<section class="section">
  <h2>Asal Usul Kalimantan Barat</h2>
  <div style="max-width:100%; margin:0 auto; text-align:justify; line-height:1.9; color:#333;">
    <p>Kalimantan Barat, sebuah provinsi di ujung barat Pulau Kalimantan, merupakan wilayah yang kaya akan sejarah, budaya, dan alam. Keberadaannya yang strategis, berbatasan langsung dengan negara Malaysia di utara dan Laut Natuna di barat, membuat Kalimantan Barat menjadi tempat yang selalu menjadi jalur pertemuan berbagai peradaban sejak ribuan tahun lalu.</p>
    <p>Sejak zaman prasejarah, manusia telah menapaki tanah Kalimantan Barat. Bukti-bukti arkeologis berupa alat batu, gua tempat tinggal, dan lukisan sederhana di beberapa daerah menunjukkan bahwa manusia prasejarah sudah menetap dan memanfaatkan sumber daya alam yang melimpah. Sungai-sungai besar, khususnya Sungai Kapuas, menjadi urat nadi kehidupan mereka. Dari hulu hingga muara, sungai ini tidak hanya menyediakan air dan ikan, tetapi juga jalur transportasi dan perdagangan antarkomunitas. Pada masa itu, kehidupan manusia sederhana: berburu, menangkap ikan, dan mengumpulkan hasil hutan. Mereka hidup secara komunal, saling berbagi, dan menghormati alam sebagai bagian dari kehidupan sehari-hari.</p>
    <p>Seiring waktu, komunitas-komunitas kecil ini berkembang menjadi kerajaan-kerajaan yang terorganisir. Pada abad ke-17 dan ke-18, beberapa kerajaan lokal muncul di wilayah Kalimantan Barat. Kerajaan Sambas, misalnya, terletak di pesisir utara dan dikenal sebagai pusat perdagangan lada dan hasil bumi. Kerajaan ini menjalin hubungan dagang dengan pedagang dari Tiongkok, Arab, dan Eropa. Sementara itu, di hulu Sungai Kapuas berdiri Kerajaan Landak, yang mayoritas penduduknya adalah suku Dayak. Kerajaan ini mempertahankan tradisi dan adat istiadat yang kaya, termasuk upacara panen dan ritual penyambutan tamu. Di bagian barat, berdirilah Kerajaan Pontianak pada tahun 1771 oleh Sultan Syarif Abdurrahman Alkadrie. Kota Pontianak dibangun tepat di garis khatulistiwa, sebuah lokasi yang unik sekaligus strategis untuk perdagangan dan administrasi. Sultan Abdurrahman membangun kota dengan prinsip-prinsip hukum Islam dan adat Melayu, menjaga hubungan harmonis dengan suku Dayak setempat.</p>
    <p>Masa kolonial membawa perubahan besar bagi Kalimantan Barat. Belanda tertarik dengan kekayaan alam provinsi ini, terutama karet, emas, kayu, dan hasil hutan lainnya. Mereka membangun pelabuhan, jalan, dan sistem administrasi, meski pengaruhnya lebih terasa di kota pesisir. Interaksi dengan pedagang Tionghoa, Arab, dan Eropa menambah keragaman budaya lokal, sehingga kota-kota seperti Pontianak dan Singkawang menjadi melting pot etnis dan tradisi. Singkawang, misalnya, terkenal dengan komunitas Tionghoa yang besar, yang hingga kini masih melestarikan budaya dan festival tradisional, seperti Cap Go Meh.</p>
    <p>Saat menjelang kemerdekaan Indonesia, Kalimantan Barat menjadi bagian dari perjuangan rakyat melawan kolonialisme. Penduduk lokal aktif dalam perlawanan, baik melalui gerakan bersenjata maupun dukungan logistik. Setelah Proklamasi Kemerdekaan Indonesia pada 17 Agustus 1945, Kalimantan Barat resmi menjadi bagian dari Republik Indonesia. Kota Pontianak kemudian ditetapkan sebagai ibu kota provinsi, menjadi pusat pemerintahan, pendidikan, dan ekonomi.</p>
    <p>Memasuki era modern, Kalimantan Barat terus berkembang. Sungai Kapuas tetap menjadi urat nadi kehidupan, namun kini juga didukung oleh jalan raya dan bandara, memudahkan mobilitas masyarakat dan perdagangan. Ekonomi provinsi ini didominasi oleh pertanian, perkebunan, dan pertambangan. Padi, kelapa sawit, kopi, dan karet menjadi komoditas utama, sementara emas dan batu bara menjadi sektor pertambangan yang signifikan. Pariwisata juga mulai berkembang, dengan Taman Nasional Gunung Palung, Danau Sentarum, dan titik nol Khatulistiwa di Pontianak menjadi destinasi utama. Kota Singkawang tetap menjadi pusat budaya dan festival, menarik wisatawan dari berbagai daerah.</p>
    <p>Masyarakat Kalimantan Barat saat ini hidup dalam harmoni antara tradisi dan modernitas. Etnis Melayu, Dayak, Tionghoa, dan Bugis hidup berdampingan, saling melestarikan budaya masing-masing. Upacara adat, tarian tradisional, dan kuliner khas tetap menjadi bagian penting dari kehidupan sehari-hari, sementara pendidikan dan teknologi semakin berkembang. Provinsi ini menjadi contoh bagaimana kekayaan alam, budaya, dan sejarah bisa saling bersinergi dalam membentuk identitas masyarakat.</p>
    <p>Dari manusia prasejarah yang hidup di tepi sungai, kerajaan-kerajaan yang menjaga adat dan perdagangan, era kolonial yang memperkenalkan perubahan, hingga masyarakat modern yang memadukan tradisi dan kemajuan, Kalimantan Barat telah menapaki perjalanan panjang yang kaya makna. Hari ini, provinsi ini bukan hanya permata Pulau Kalimantan, tetapi juga simbol keberagaman, toleransi, dan keindahan alam yang memikat setiap pengunjung yang datang.</p>
  </div>
</section>

<footer>
  <p>&copy; 2025 Website Kalimantan Barat</p>
</footer>

<script>
const kategoriBtn = document.getElementById('kategoriBtn');
const kategoriList = document.getElementById('kategoriList');
let isOpen = false;

kategoriBtn.addEventListener('click', ()=>{
    const items = kategoriList.querySelectorAll('a');
    if(!isOpen){
        kategoriList.style.display='block';
        items.forEach((item,index)=>{ setTimeout(()=>{ item.classList.add('show'); }, index*90); });
    } else {
        items.forEach(item=>{ item.classList.remove('show'); });
        setTimeout(()=>{ kategoriList.style.display='none'; }, 300);
    }
    isOpen = !isOpen;
});

document.addEventListener('click',(e)=>{
    if(!kategoriBtn.contains(e.target) && !kategoriList.contains(e.target)){
        const items = kategoriList.querySelectorAll('a');
        items.forEach(item=>{ item.classList.remove('show'); });
        kategoriList.style.display='none';
        isOpen=false;
    }
});
</script>

</body> 
</html>