<?php
require_once "../koneksi.php";
session_start();
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
// Fetch data from users and subscriptions
$query = "
SELECT 
    u.user_id,
    u.name,
    u.email,
    u.role,
    u.status,
    CASE
        -- Jika aktivitas terakhir adalah login dan status pengguna aktif, maka Online
        WHEN u.status = 'active' THEN 'Online'

        -- Jika aktivitas terakhir adalah logout, hitung waktu sejak logout
        WHEN al_last.activity_type = 'Melakukan logout.' THEN 
            CASE 
                WHEN TIMESTAMPDIFF(MINUTE, al_last.timestamp, NOW()) < 60 THEN 
                    CONCAT(TIMESTAMPDIFF(MINUTE, al_last.timestamp, NOW()), ' minutes ago')
                WHEN TIMESTAMPDIFF(HOUR, al_last.timestamp, NOW()) < 24 THEN 
                    CONCAT(TIMESTAMPDIFF(HOUR, al_last.timestamp, NOW()), ' hours ago')
                ELSE 
                    CONCAT(TIMESTAMPDIFF(DAY, al_last.timestamp, NOW()), ' days ago')
            END

        -- Jika tidak ada catatan aktivitas, beri status default
        ELSE 'No activity record'
    END AS online_status,
    CASE 
        -- Status langganan
        WHEN s.subscription_id IS NULL THEN 'No Subscription'
        ELSE CONCAT('Active until ', DATE_FORMAT(s.end_date, '%Y-%m-%d'))
    END AS subscription_status
FROM 
    users u
LEFT JOIN 
    (
        -- Ambil aktivitas terakhir dari masing-masing pengguna
        SELECT user_id, MAX(activity_type) AS activity_type, MAX(timestamp) AS timestamp
        FROM activity_logs
        WHERE activity_type IN ('Melakukan login.', 'Melakukan logout.')
        GROUP BY user_id
    ) al_last
ON 
    u.user_id = al_last.user_id
LEFT JOIN 
    subscriptions s 
ON 
    u.user_id = s.user_id
ORDER BY 
    u.status DESC, al_last.timestamp DESC";

$result = mysqli_query($koneksi, $query);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Manajemen Pengguna</title>
    <style>
        nav {
            background-color: #444;
            width: 100%;
        }

        body::-webkit-scrollbar{
            display: none;
        }

        .heading {
            font-size: 2rem;
            font-weight: bold;
            color: #004085;
            margin-bottom: 5px;
        }

        footer {
            position: relative;
            width: 100%;
            background-color: #444;
            text-align: center;
            color: #fff;
            bottom: -1000px;
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
    
    <div class="container mt-3">
        <h1 class="heading">Manajemen Pengguna</h1>
            <div class="d-flex justify-content-end p-3">
                <a href="tambah_user.php" class="btn btn-success me-2"><i class="fas fa-plus"></i> Tambah Admin</a>
            </div>
            <table class="table table-hover mb-0 table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Status Pengguna</th>
                        <th>Status Langganan</th>
                        <th>Last Online</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $index => $user) : ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($user["name"]) ?></td>
                            <td><?= htmlspecialchars($user["role"]) ?></td>
                            <td><?= htmlspecialchars($user["email"]) ?></td>
                            <td><?= htmlspecialchars(ucfirst($user["status"])) ?></td>
                            <td><?= htmlspecialchars(ucfirst($user["subscription_status"])) ?></td>
                            <td><?= htmlspecialchars(ucfirst($user["online_status"])) ?></td>
                            <td>
                                <a href="hapus_user.php?id=<?= $user['user_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')"><i class="fas fa-trash"></i> Hapus</a>
                            </td>
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