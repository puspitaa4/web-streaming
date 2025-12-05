<?php
require '../koneksi.php';

// Periksa apakah form telah disubmit
if (isset($_POST['Register'])) {
    // Ambil data dari form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $fullname = $_POST['fullname'];
    $date = $_POST['date'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($username) || empty($fullname) || empty($date) || empty($email) || empty($password) || empty($confirm_password)) {
        die("All fields are required.");
    }

    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Simpan data ke database
    $sql = "INSERT INTO users (username, email, password, role, status, name, birthdate) VALUES (?, ?, ?, 'admin', 'inactive', '$fullname', '$date')";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "<script> alert('Account registered successfully. You can now login.');</script>";
        header("location: manage_user.php");
    } else {
        $error = "Akun gagal didaftarkan.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pegawai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }

        body::-webkit-scrollbar{
            display: none;
        }

        nav {
            background-color: #444;
        }

        .main-container {
            margin-top: 30px;
            margin-bottom: 50px;
        }

        .heading {
            font-size: 2rem;
            font-weight: bold;
            color: #004085;
            margin-bottom: 20px;
        }

        .togglePassword {
            color: #7f7f7f;
            font-size:13px;
            width: 20px;
            heigth: 20px;
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
        <h1 class="heading">Tambah User</h1>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama:</label>
                <input type="text" class="form-control" id="nama" name="fullname" required>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required><br>
                <label for="confirm_password" class="form-label">Confirm Password:</label>
                <input type="password" class="form-control" id="confim_password" name="confirm_password" required>
                <input type="checkbox" id="togglePassword" class="togglePassword">
                <label for="togglePassword" id="togglePassword" class="togglePassword">show</label>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="date" class="form-label">Tanggal lahir</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>

            <button type="submit" class="btn btn-primary" name="Register">Simpan</button>
            <a href="manage_user.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script>
        document.getElementById("togglePassword").addEventListener("change", function () {
            const passwordField = document.getElementById("password");
            const confirm_passwordField = document.getElementById("confim_password");
    // Switch between "text" and "password" types
            if (this.checked) {
                passwordField.type = "text";
                confirm_passwordField.type = "text";
            } else {
                passwordField.type = "password";
                confirm_passwordField.type = "password";
            }
        });
    </script>
</body>

</html>