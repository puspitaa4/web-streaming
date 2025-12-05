<?php
// Koneksi ke database
require '../koneksi.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();



$querySub = "SELECT subscription_id, user_id, status, end_date FROM subscriptions WHERE user_id = ? AND subscription_id = (SELECT MAX(subscription_id) FROM subscriptions)";
$stmtSub = $koneksi->prepare($querySub);
$stmtSub->bind_param("i", $user_id);
$stmtSub->execute();
$resultSub = $stmtSub->get_result();
$subs = $resultSub->fetch_assoc();
if($subs > 0){
    $current_date = new DateTime();
    $end_date = new DateTime($subs['end_date']);
    $interval = $current_date->diff($end_date);
    $days_left = $interval->days;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengguna</title>
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
            color: white;
            background-color: #282828;
            overflow-y: auto;
        }

        header {
            background-color: #6200ea;
            color: white;
            padding: 1rem;
            text-align: center;
            width: 100%;
            z-index: 5;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        /* Variasi warna untuk alert */
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
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
                <li><a href="#"><i class="fa-solid fa-user"></i>Dashboard</a></li>
                <li><a href="3_ProfilPengguna.php"><i class="fa-solid fa-gear"></i>Profil</a></li>
                <li><a href="riwayat_ton.php"><i class="fa-solid fa-film"></i>Riwayat</a></li>
                <li><a href="playlist.php"><i class="fa-solid fa-play"></i>Playlist</a></li>
                <li><a href="favorite.php"><i class="fa-solid fa-star"></i>Favorit</a></li>
                <li><a href="langganan.php"><i class="fa-solid fa-dollar-sign"></i>Langganan</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Konten utama -->
        <main class="main-content">
            <h2>Selamat Datang <?php echo htmlspecialchars($user['username']);?>!</h2>
            <?php
            if (isset($subs['status']) && $subs['status'] === 'active' && $user_id === $subs['user_id']) {
                if ($current_date > $end_date) {
                    echo '<div class="alert alert-danger">Langganan anda telah berakhir.</div>';
                } elseif ($days_left <= 7) {
                    echo '<div class="alert alert-warning">Langganan anda akan segera berakhir.</div>';
                } else {
                    echo '<div class="alert alert-success">Langganan anda telah aktif.</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Anda belum mengaktifkan langganan, akses film akan terbatas.</div>';
            }
            ?>
        </main>
    </div>
</body>
</html>