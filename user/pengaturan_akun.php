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

if (isset($_POST['save'])){
    $nama = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $date = $_POST['birth_date'];

    $queryUpdate = "UPDATE users SET username = '$username', email = '$email', name = '$nama', birthdate = '$date', updated_at = CURRENT_TIMESTAMP WHERE user_id = '$user_id'";
    if (mysqli_query($koneksi, $queryUpdate)) {
        header("Location: 3_ProfilPengguna.php");
        exit();
    } else {
        echo "<p class='text-danger'>Error: " . mysqli_error($koneksi) . "</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #282828;
        }

        body::-webkit-scrollbar{
            display: none;
        }

        .app-header {
            background-color: #005792;
            color: white;
            text-align: center;
            padding: 1rem 0;
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

        .sidebar .profile-image {
            text-align: center;
            margin-bottom: 2rem;
        }

        .sidebar .profile-image img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }

        .main-content {
            flex-grow: 1;
            padding: 2rem;
            background-color: #282828;
            border-left: 1px solid #ddd;
        }

        .main-content h2 {
            color: #fff;
        }

        .profile-layout {
            display: flex;
            gap: 2rem;
        }

        .photo-section {
            text-align: center;
        }

        .photo-section img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
        }

        .profile-form {
            flex-grow: 1;
        }

        .profile-form .form-group {
            margin-bottom: 1rem;
        }

        .profile-form label {
            display: block;
            color: white;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .profile-form input, .profile-form select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            color: white;
            border-radius: 5px;
            background-color: #282828;
        }

        .profile-form input:disabled, .profile-form select:disabled {
            background-color: #282828;
            color: white;
        }

        .save-btn {
            background-color: #005792;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .save-btn:hover {
            background-color: #003f66;
        }

        .back-btn {
            background-color: #4CAF50;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 1rem;
            font-size: 1rem;
        }

        .back-btn:hover {
            background-color: #45a049;
        }
        .hapus-btn {
            background-color: #ff0000;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 1rem;
            font-size: 1rem;
        }

        .hapus-btn:hover {
            background-color: #800000;
        }

        .change-photo-btn {
            background-color: #FF9800;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 1rem;
            font-size: 1rem;
        }

        .change-photo-btn:hover {
            background-color: #e68900;
        }

        .button-group {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-top: 1rem;
        }

        .photo-section p {
            margin-top: 0.5rem;
        }

        nav.top-nav {
            display: flex;
            justify-content: center;
            background-color: #005792;
            padding: 0.5rem;
            margin-bottom: 1rem;
        }

        nav.top-nav ul {
            list-style: none;
            display: flex;
            padding: 0;
            margin: 0;
        }

        nav.top-nav li {
            margin: 0 1rem;
        }

        nav.top-nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        nav.top-nav a:hover {
            text-decoration: underline;
        }

        header {
            background-color: #6200ea;
            color: white;
            padding: 1rem;
            text-align: center;
            width: 100%;
            z-index: 5;
        }
    </style>
</head>
<body>

    <header>
        <h1>Dashboard Pengguna</h1>
    </header>

    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <ul>
                <li><a href="1_HalamanBeranda.php"><i class="fas fa-home"></i> Home </a></li>
                <li><a href="2_DashboardPengguna.php"><i class="fa-solid fa-user"></i>Dashboard</a></li>
                <li><a href="#"><i class="fa-solid fa-gear"></i>Profil</a></li>
                <li><a href="riwayat_ton.php"><i class="fa-solid fa-film"></i>Riwayat</a></li>
                <li><a href="playlist.php"><i class="fa-solid fa-play"></i>Playlist</a></li>
                <li><a href="favorite.php"><i class="fa-solid fa-star"></i>Favorit</a></li>
                <li><a href="langganan.php"><i class="fa-solid fa-dollar-sign"></i>Langganan</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <h2>My Profile</h2>
           

            <div class="profile-layout">
                <!-- Photo Upload Section -->
                <div class="photo-section">
                    <label for="upload-photo">
                        <img id="profile-picture" src="https://www.seekpng.com/png/detail/966-9665317_placeholder-image-person-jpg.png" alt="Profile Image">
                    </label>
                </div>

                <!-- Form Section -->
                <form action="" method="POST" class="profile-form" id="profile-form">
                    <div class="form-group">
                        <label for="fullname">Nama Lengkap:</label>
                        <input type="text" id="fullname" name="fullname" value="<?php echo $user['name']?>" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" value="<?php echo $user['username']?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo $user['email']?>" required>
                    </div>
                    <div class="form-group">
                        <label for="birth_date">Tanggal Lahir</label>
                        <input type="date" id="birth_date" name="birth_date" value="<?php echo $user['birthdate']?>" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="save-btn btn btn-success" id="edit-btn" name="save">Edit Profile</button>
                        <button type="button" class="back-btn btn btn-secondary" id="back-btn" onclick="kembali()"><i class="fa fa-arrow-left" aria-hidden="true"></i> Kembali</button>
                        <a href="hapus_akun.php?user_id=<?php echo htmlspecialchars($user_id);?>" class="hapus-btn btn" id="hapus-btn" onclick="confirm('Apakah anda yakin ingin menghapus akun anda?')"><i class="fa fa-trash-o" aria-hidden="true"></i> Hapus Akun</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('profile-picture');
            output.src = reader.result;

            // Update sidebar profile image
            const sidebarProfilePicture = document.getElementById('sidebar-profile-picture');
            sidebarProfilePicture.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    function confirmLogout(event) {
        event.preventDefault();
        if (confirm('Apakah Anda yakin untuk keluar dari akun Anda?')) {
            window.location.href = "../logout.php";
        }
    }

    function kembali() {
        window.location.href = "3_ProfilPengguna.php"
    }

    function saveProfile() {
        // Here we would send the updated data to the server (e.g., using AJAX or form submission)
        alert('Profile saved successfully!');
        
        // After saving, disable the fields again
        const formElements = document.querySelectorAll('#profile-form input, #profile-form select');
        formElements.forEach(element => element.disabled = true);
        
        document.getElementById('save-btn').style.display = 'none';
    }
</script>
</html>
