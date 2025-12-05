<?php
session_start();
require '../koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$queryHistory = "SELECT wh.last_watched, f.title, f.poster_url, f.film_id FROM watch_history wh JOIN films f ON wh.film_id = f.film_id WHERE wh.user_id = ? ORDER BY wh.last_watched DESC";

$stmt = $koneksi->prepare($queryHistory);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$resultHistory = $stmt->get_result();
$watch_history = $resultHistory->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Tontonan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #282828;
            overflow-x: hidden;
        }

        body::-webkit-scrollbar {
            display: none;
        }

        .app-header {
            background-color: #6a0dad;
            color: white;
            text-align: center;
            padding: 1rem;
        }

        .container {
            display: flex;
            height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            width: 200px;
            background-color: #4b0082;
            color: white;
            padding: 1rem;
            box-sizing: border-box;
            margin-right: 20px;
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
            background-color: #282828;
            overflow-y: auto;
        }

        .main-content::-webkit-scrollbar {
            display: none;
        }

        .main-content h1{
            color: white;
            text-align: center;
        }

        header {
            background-color: #6200ea;
            color: white;
            padding: 1rem;
            text-align: center;
            width: 100%;
            z-index: 5;
        }

        main h1{
            color: white;
        }

        .results {
            display: flex;
            flex-wrap: 1;
            gap: 20px;
        }

        .card {
            display: flex;
            flex-direction: column;
            width: 250px;
            height: 300px;
            background-color: #282828;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-info {
            padding: 5px;
            flex: 1;
        }

        .card-body h5 {
            margin: 0;
            text-align: center;
            margin-top: 10px;
            color: #fff;
            font-size: 1.2em;
        }

        .results p {
            text-align: center;
            font-size: 1.2em;
            color: #333;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header class="header text-white">
        <h1 class="text-white">Dashboard Pengguna</h1>
    </header>

    <div class="container">
        <!-- Sidebar kiri -->
        <aside class="sidebar">
            <ul>
                <li><a href="1_HalamanBeranda.php"><i class="fas fa-home"></i> Home </a></li>
                <li><a href="2_DashboardPengguna.php"><i class="fa-solid fa-user"></i>Dashboard</a></li>
                <li><a href="3_ProfilPengguna.php"><i class="fa-solid fa-gear"></i>Profil</a></li>
                <li><a href="#"><i class="fa-solid fa-film"></i>Riwayat</a></li>
                <li><a href="playlist.php"><i class="fa-solid fa-play"></i>Playlist</a></li>
                <li><a href="favorite.php"><i class="fa-solid fa-star"></i>Favorit</a></li>
                <li><a href="langganan.php"><i class="fa-solid fa-dollar-sign"></i>Langganan</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        <!-- Konten utama -->
        <main class="flex-grow-1 p-4 bg-dark">
            <h1 class="text-white">Riwayat Tontonan</h1>
            <div class="results">
                <?php foreach($watch_history as $his):?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <a href="film_session.php?film_id=<?php echo htmlspecialchars($his['film_id']);?>">
                                <div class="card">
                                    <img alt="Cover" class="card-img-top" src="<?php echo htmlspecialchars($his['poster_url']);?>" />
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($his['title']);?></h5>
                                        <h5 class="card-title">Last watched: <?php echo htmlspecialchars($his['last_watched']);?></h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                <?php endforeach;?>

            </div>
        </main>
    </div>
</body>
</html>
