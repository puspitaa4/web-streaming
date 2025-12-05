<?php
session_start();
require '../koneksi.php'; // Koneksi ke database
if(!isset($_SESSION['user_id'])){
    header("location: ../login/login.php");
}

$admin_id = $_SESSION['user_id'];
$query = "SELECT username FROM users WHERE user_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$stmt->close();
// Ambil semua data aktivitas dari tabel activity_logs
$query = "SELECT al.log_id, u.username, u.role, al.activity_type, al.timestamp
          FROM activity_logs al
          JOIN users u ON al.user_id = u.user_id
          ORDER BY al.timestamp DESC";
$stmt = $koneksi->prepare($query);
$stmt->execute();
$logs = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Log Aktivitas Admin</title>
    <style>
        /* CSS untuk Halaman Log Aktivitas dengan Tema Biru */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            color: #333;
            margin: 0;
            padding: 0;
        }

        body::-webkit-scrollbar{
            display: none;
        }

        nav {
            background-color: #444;
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

        .main-container {
            width: 80%;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #1e73be;
            font-size: 2em;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #1e73be;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e0f4ff;
        }

        a {
            color: #1e73be;
            text-decoration: none;
            font-size: 1em;
        }

        a:hover {
            text-decoration: underline;
        }

        .btn-back {
            padding: 10px 15px;
            background-color: #1e73be;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }

        .btn-back:hover {
            background-color: #145a8a;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active text-light" aria-current="page" href="../home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" aria-current="page" href="manage_user.php">Manage Users</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Films
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="../manage_films.php">Manage Films</a></li>
                            <li><a class="dropdown-item" href="../manage_genre.php">Manage Genre</a></li>
                            <li><a class="dropdown-item" href="../stream_setting.php">Stream Settings</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            User Reviews
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="rating.php">Ratings Report</a></li>
                            <li><a class="dropdown-item" href="https://projectstream.disqus.com/admin/" target="_blank">Manage Comments</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" aria-current="page" href="log_aktivitas.php">Activity Logs</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-user-circle fa-lg" aria-hidden="true"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="pengaturan_admin.php"><i class="fas fa-user"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container main-container mt-5">
        <h2>Log Aktivitas Admin</h2>

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Jenis Aktivitas</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $loge => $logss) : ?>
                    <tr>
                        <td><?= $loge + 1 ?></td>
                        <td><?= htmlspecialchars($logss["username"]) ?></td>
                        <td><?= htmlspecialchars($logss["role"]) ?></td>
                        <td><?= htmlspecialchars($logss["activity_type"]) ?></td>
                        <td><?= date('Y-m-d H:i:s', strtotime($logss["timestamp"])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <footer>
            <p>&copy; 2024 Project Pemrograman Aplikasi Web | Kelompok 5</p>
        </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
