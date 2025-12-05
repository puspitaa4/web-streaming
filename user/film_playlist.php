<?php
session_start();
require '../koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM playlists WHERE user_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$playlist = $result->fetch_assoc();
// Periksa apakah parameter playlist_id ada di URL
if (!isset($_GET['playlist_id'])) {
    die("Playlist tidak ditemukan!");
}

$playlist_id = intval($_GET['playlist_id']);

// Query untuk mendapatkan daftar film dalam playlist
$query = "
    SELECT films.film_id, films.title, films.poster_url
    FROM playlist_items 
    JOIN films ON playlist_items.film_id = films.film_id 
    WHERE playlist_items.playlist_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $playlist_id);
$stmt->execute();
$result = $stmt->get_result();
$movies = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streaming Film</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

    <style>
body {
    background-color: #282828;
    color: white;
    font-family: Arial, sans-serif;
}

body::-webkit-scrollbar{
    display: none;
}

.navbar {
    background-color:rgb(41, 43, 187);
    display: flex;
    position: sticky;
    top: 0;
    z-index: 999;
}

.navbar-brand {
    font-size: 1.5rem;
    color: gold;
}

.icon {
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  right: 70px;
  cursor: pointer;
  width: 50px;
  height: 0px;
  outline: none;
  border-style: none;
  border-radius: 50%;
  pointer-events: painted;
  background-color: transparent;
  transition: .2s linear;
}

.menu-icon {
    border: none;
    background: transparent;
    font-size: 1.5rem;
    color: white;
    margin-left: 10px;
    margin-right: 0;
    border-radius: 5px;
}

.menu-icon:hover {
    color: #ff0000;
}

.form-control {
    background-color: #2c2c2c;
    color: white;
    border: 1px solid #ff4444;
}

.btn-outline-danger {
    color: white;
    border-color: #ff4444;
    margin: 1px;
}

.btn-outline-danger:hover {
    color: #ff0000;
    background: transparent;
}

.side-menu {
    height: 100%;
    width: 0;
    position: fixed;
    top: 0;
    right: 0;
    background-color:rgb(149, 177, 238);
    overflow-x: hidden;
    transition: 0.5s;
    padding-top: 60px;
    z-index: 999;
}

.side-menu a {
    padding: 10px 15px;
    text-decoration: none;
    font-size: 20px;
    color: #fff;
    display: block;
    transition: 0.3s;
}

.side-menu a:hover {
    background-color: #ff4444;
}


.a{
    border: 1px solid black;
    width: 100px;
    text-decoration: none;
    padding: 10px 20px;
    background-color:rgb(60, 43, 192);
    color: #fff;
    border-radius: 5px;
    font-size: 16px;
    transition: background-color 0.3s
}
.a:hover{
    background-color: #e74c3c
}
.close-btn {
    position: absolute;
    top: 10px;
    right: 25px;
    font-size: 36px;
    color: #fff;
    border: none;
    background: none;
}

.close-btn:hover {
    color: #ff4444;
}

.cont {
    display: flex !important; /* Menggunakan Flexbox untuk tata letak horizontal */
    flex-wrap: wrap; /* Membuat item turun ke baris berikutnya jika tidak muat */
    gap: 1rem; /* Memberikan jarak antar card */
    justify-content: flex-start; /* Mengatur perataan ke kiri */
}

.card {
    flex: 0 0 calc(100% - 1rem) !important; /* Menentukan ukuran card (25% dari parent) */
    width: 250px; /* Maksimal lebar card */
    background-color: #333; /* Warna latar belakang card (opsional) */
    border-radius: 8px; /* Memberikan border radius */
    overflow: hidden; /* Mengatasi konten yang keluar dari card */
}

.card .card-body{
    height: 50px;
    color: white;
    text-decoration: none;
}
.card .card-body:hover{
    color: white;
    text-decoration: none;
}
    </style>
</head>
<body class="sm">
    <nav class="navbar navbar-expand-sm">
        <a class="navbar-brand" href="#" style="font-family: 'Cursive'; color: gold;">STREAMING APP</a>
        <button class="navbar-toggler" type="button" id="menuToggle">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="input-wrapper my-2 my-sm-0 ml-auto">
            <button class="icon" id="search" onclick="search()"> 
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="25px" width="25px">
                    <path stroke-linejoin="round" stroke-linecap="round" stroke-width="1.5" stroke="#fff" d="M11.5 21C16.7467 21 21 16.7467 21 11.5C21 6.25329 16.7467 2 11.5 2C6.25329 2 2 6.25329 2 11.5C2 16.7467 6.25329 21 11.5 21Z"></path>
                    <path stroke-linejoin="round" stroke-linecap="round" stroke-width="1.5" stroke="#fff" d="M22 22L20 20"></path>
                </svg>
            </button>
        </div>
        
        <button class="navbar-toggler-icon menu-icon sm" id="menuButton" type="button"><b>=</b></button>

    </nav>

    <div class="side-menu sm" id="sideMenu">
        <button type="button" class="close-btn" id="closeMenu">&times;</button>
        <ul>
            <li><a href="1_HalamanBeranda.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="2_DashboardPengguna.php"><i class="fa-solid fa-user"></i> Dashboard User</a></li>
            <li><a href="4_KategoriKonten.php"><i class="fas fa-film"></i> Genre Film</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>

    </div>
    
    <div class="container mt-4">
        <h1>Daftar film dalam playlist <?php echo htmlspecialchars($playlist['name']);?></h1>
        <div class="row text-white mt-5">
            <div class="cont">
                <?php foreach ($movies as $movie => $row): ?>
                    <a href="film_session.php?film_id=<?= $row['film_id']; ?>">
                        <div class="card">
                            <div class="position-relative">
                                <img alt="" class="card-img-top" height="300" src="<?php echo htmlspecialchars($row['poster_url']); ?>" />
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Open side menu
        document.getElementById("menuButton").addEventListener("click", function() {
            document.getElementById("sideMenu").style.width = "250px";
        });

        // Close side menu
        document.getElementById("closeMenu").addEventListener("click", function() {
            document.getElementById("sideMenu").style.width = "0";
        });

        function search(){
            window.location = "pencarian.php";
        };
    </script>
</body>
</html>
