<?php
require "koneksi.php";
session_start();

$admin_id = $_SESSION['user_id'];
$query = "SELECT username FROM users WHERE user_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pelanggan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }

        body::-webkit-scrollbar{
            display: none;
        }

        nav{
            background-color: #444;
        }

        .main-container {
            margin-top: 30px;
            margin-bottom: 10px;
        }

        .heading {
            font-size: 2rem;
            font-weight: bold;
            color: #004085;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
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
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <h1 class="heading">Edit Film</h1>

        <?php

        $id_film = $judul = $deskripsi = $poster = $release = "";
        $error_message = "";

        if (isset($_GET['id'])) {
            $id_film = $_GET['id'];
            $query = "SELECT film_id, film_url, status, access_level FROM films WHERE film_id = '$id_film'";
            $result = mysqli_query($koneksi, $query);
            $data = mysqli_fetch_assoc($result);

            if ($data) {
                $url = $data['film_url'];
                $status = $data['status'];
                $akses = $data['access_level'];
            } else {
                echo "<p class='text-danger'>Data film tidak ditemukan.</p>";
                exit();
            }
        }

        if (isset($_POST['update'])) {
            $url = trim($_POST['url']);
            $status = trim($_POST['status']);
            $akses = trim($_POST['akses']);

            if (empty($url) || empty($status) || empty($akses))  {
                $error_message .= "Semua field harus terisi.<br>";
            }

            if (empty($error_message)) {
                $query = "UPDATE films SET film_url = '$film', status = '$status', access_level = '$akses' WHERE film_id = '$id_film'";
                if (mysqli_query($koneksi, $query)) {
                    header("Location: stream_setting.php");
                    exit();
                } else {
                    echo "<p class='text-danger'>Error: " . mysqli_error($koneksi) . "</p>";
                }
            }
        }
        ?>

        <form method="POST" action="update_film.php?id=<?php echo htmlspecialchars($id_film); ?>">
            <div class="mb-3">
                <label for="url" class="form-label">Film URL:</label>
                <input type="url" class="form-control" id="url" name="url" value="<?php echo htmlspecialchars($url); ?>" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status:</label>
                <select name="status" id="status" class="form-control">
                    <option value="">--Pilih Status Film--</option>
                    <option value="available">Available</option>
                <option value="unavailable">unavailable</option>
                </select>
            </div>

            <div class="mb-3">
            <label for="akses" class="form-label">Akses Level:</label>
            <select name="akses" id="akses" class="form-control">
                <option value="">--Pilih Level Akses--</option>
                <option value="free">Gratis</option>
                <option value="subscription">Berlangganan</option>
            </select>
            </div>

            <button type="submit" class="btn btn-primary" name="update">Update</button>
            <a href="stream_setting.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </form>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger mt-3">
                <?php echo nl2br($error_message); ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>
