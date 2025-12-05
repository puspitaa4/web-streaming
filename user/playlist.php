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
$playlist = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playlist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        body::-webkit-scrollbar{
            display : none;
        }

        .app-header {
            background-color: #6a0dad;
            color: white;
            text-align: center;
            padding: 1rem;
        }

        .header {
            text-align: center;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #4b0082;
            color: white;
            padding: 1rem;
            box-sizing: border-box;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin: 1rem 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            display: block;
            padding: 0.5rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .sidebar ul li a:hover {
            background-color: #5a189a;
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        .main-content {
            flex-grow: 1;
            padding: 2rem;
            background-color: #ffffff;
            overflow-y: auto;
        }

        .navbar {
            background-color: #6a0dad;
            padding: 0.5rem 1rem;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            justify-content: center;
            padding: 0;
            margin: 0;
        }

        .navbar ul li {
            margin: 0 1rem;
        }

        .navbar ul li a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .navbar ul li a:hover {
            background-color: #5a189a;
        }
        header {
            background-color: #6200ea;
            color: white;
            padding: 1rem;
            text-align: center;
            width: 100%;
            z-index: 5;
        }

        .movie-card {
            display: flex;
            align-items: center;
            background: #fff;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.8);
            overflow: hidden;
        }

        .movie-info {
            padding: 20px;
            flex: 1;
        }

        .movie-info a{
            text-decoration: none;
            color: black;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Dashboard Pengguna</h1>
    </header>
    <!-- Navbar atas -->
    

   

    <div class="container">
        <!-- Sidebar kiri -->
        <aside class="sidebar">
            <ul>
                <li><a href="1_HalamanBeranda.php"><i class="fas fa-home"></i> Home </a></li>
                <li><a href="2_DashboardPengguna.php"><i class="fa-solid fa-user"></i>Dashboard</a></li>
                <li><a href="3_ProfilPengguna.php"><i class="fa-solid fa-gear"></i>Profil</a></li>
                <li><a href="riwayat_ton.php"><i class="fa-solid fa-film"></i>Riwayat</a></li>
                <li><a href="#"><i class="fa-solid fa-play"></i>Playlist</a></li>
                <li><a href="favorite.php"><i class="fa-solid fa-star"></i>Favorit</a></li>
                <li><a href="langganan.php"><i class="fa-solid fa-dollar-sign"></i>Langganan</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Konten utama -->
        <main class="main-content">
            <div class="header">
                <h1>Daftar Playlist</h1>
            </div>
            <?php foreach($playlist as $play): ?>
                <div class="movie-card">
                    <div class="movie-info">
                        <a href="film_playlist.php?playlist_id=<?php echo htmlspecialchars($play['playlist_id']);?>" class="movie-title"><?php echo htmlspecialchars($play['name']);?></a>
                    </div>
                </div>
            <?php endforeach;?>
        </main>
    </div>
</body>
</html>