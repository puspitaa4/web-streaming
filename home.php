<?php
// Mulai session
session_start();
// Periksa apakah pengguna sudah login dan memiliki role 'admin'
if(!isset($_SESSION['user_id'])){
    header("location: login/login.php");
}
// Sambungkan ke database
require_once 'koneksi.php';

// Ambil data admin
$admin_id = $_SESSION['user_id'];
$query = "SELECT username FROM users WHERE user_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Ambil jumlah data dari tabel
$queryUsers = "SELECT COUNT(*) AS total_users FROM users WHERE role = 'user'";
$queryFilms = "SELECT COUNT(*) AS total_films FROM films";
$queryActive = "SELECT COUNT(status) AS total_active FROM users WHERE role = 'user' AND status = 'active'";
$totalUsers = $koneksi->query($queryUsers)->fetch_assoc()['total_users'];
$totalActive = $koneksi->query($queryActive)->fetch_assoc()['total_active'];
$totalFilms = $koneksi->query($queryFilms)->fetch_assoc()['total_films'];

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <title>Admin Dashboard</title>
    <style>
        header {
            background-color: #333;
            color: #fff;
            padding: 1rem;
            text-align: center;
        }
       
        .card {
            background-color: #fff;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        footer {
            position: relative;
            width: 100%;
            background-color: #444;
            text-align: center;
            color: #fff;
            bottom: -25px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, Admin <?php echo htmlspecialchars($admin['username']); ?>!</h1>
    </header>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active text-light" aria-current="page" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" aria-current="page" href="adm/manage_user.php">Manage Users</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Films
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="manage_films.php">Manage Films</a></li>
                            <li><a class="dropdown-item" href="manage_genre.php">Manage Genre</a></li>
                            <li><a class="dropdown-item" href="stream_setting.php">Stream Settings</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            User Reviews
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="adm/rating.php">Ratings Report</a></li>
                            <li><a class="dropdown-item" href="https://projectstream.disqus.com/admin/" target="_blank">Manage Comments</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" aria-current="page" href="adm/log_aktivitas.php">Activity Logs</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-user-circle fa-lg" aria-hidden="true"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="adm/pengaturan_admin.php"><i class="fas fa-user"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="./logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2>Dashboard Overview</h2>
        <div class="card">
            <h3>Total Users</h3>
            <p><?php echo $totalUsers; ?> Registered Users</p>
        </div>
        <div class="card">
            <h3>Total Users Online</h3>
            <p><?php echo $totalActive; ?> Users Online</p>
        </div>
        <div class="card">
            <h3>Total Films</h3>
            <p><?php echo $totalFilms; ?> Films in Database</p>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Project Pemrograman Aplikasi Web | Kelompok 5</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>