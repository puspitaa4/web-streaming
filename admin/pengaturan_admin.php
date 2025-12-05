<?php
require "../koneksi.php";
session_start();

$admin_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (isset($_POST['save'])){
    $nama = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $date = $_POST['birth_date'];

    $queryUpdate = "UPDATE users SET username = '$username', email = '$email', name = '$nama', birthdate = '$date', updated_at = CURRENT_TIMESTAMP WHERE user_id = '$admin_id'";
    if (mysqli_query($koneksi, $queryUpdate)) {
        header("Location: pengaturan_admin.php");
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
            background-color: #f5f5f5;
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
            height: 100%;
            width: 100%;
        }

        .main-container{
            height: 100%;
        }

        .sidebar {
            width: 200px;
            background-color: #005792;
            color: white;
            height: 500px;
            padding: 1rem;
            box-sizing: border-box;
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

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 1rem 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            margin-right: 0.5rem;
        }

        .main-content {
            flex-grow: 1;
            padding: 2rem;
            background-color: #ffffff;
            border-left: 1px solid #ddd;
        }

        .main-content h2 {
            color: #005792;
        }

        .profile-layout {
            display: flex;
            gap: 2rem;
        }

        .photo-section {
            text-align: center;
        }

        .photo-section img {
            width: 100px;
            height: 100px;
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
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .profile-form input, .profile-form select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .profile-form input:disabled, .profile-form select:disabled {
            background-color: #e0e0e0;
        }

        .save-btn {
            background-color: #005792;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .save-btn:hover {
            background-color: #003f66;
        }

        .edit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 1rem;
            font-size: 1rem;
        }
        .back-btn {
            background-color:rgb(123, 123, 123);
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 1rem;
            font-size: 1rem;
        }

        .edit-btn:hover {
            background-color: #45a049;
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
            background-color: #444;
            color: white;
            padding: 1rem;
            height: 50px;
            text-align: center;
            width: 97.5%;
        }

        footer {
            position: relative;
            width: 100%;
            height: 30px;
            margin-top: 0;
            background-color: #444;
            text-align: center;
            color: #fff;
            bottom: 0;
        }
    </style>
</head>
<body>

    <header>
        <h1>Pengaturan Admin</h1>
    </header>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="profile-image">
                <img id="sidebar-profile-picture" src="https://www.seekpng.com/png/detail/966-9665317_placeholder-image-person-jpg.png" alt="Profile Image">
                <p><?php echo $admin['name']?></p>
            </div>
            <ul>
                <li><a href="#"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="../home.php"><i class="fas fa-sign-out-alt "></i>Kembali</a></li>
            </ul>
        </div>

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
                        <input type="text" id="fullname" name="fullname" value="<?php echo $admin['name']?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" value="<?php echo $admin['username']?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo $admin['email']?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="birth_date">Tanggal Lahir</label>
                        <input type="date" id="birth_date" name="birth_date" value="<?php echo $admin['birthdate']?>" disabled>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="save-btn" id="save-btn" name="save">Save profile details</button>
                        <button type="button" class="edit-btn" id="edit-btn" onclick="editProfile()">Edit Profile</button>
                        <button type="button" class="back-btn" id="back-btn" onclick="backProfile()"><i class="fa fa-arrow-left" aria-hidden="true"></i>Kembali</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Project Pemrograman Aplikasi Web | Kelompok 5</p>
    </footer>

</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function editProfile() {
        // Enable form fields for editing
        const formElements = document.querySelectorAll('#profile-form input, #profile-form select');
        formElements.forEach(element => element.disabled = false);
    }
    function backProfile() {
        // Enable form fields for editing
        const formElements = document.querySelectorAll('#profile-form input, #profile-form select');
        formElements.forEach(element => element.disabled = true);
    }

    $(document).ready(function(){
        function hideButton(){
            $('#save-btn').hide();
            $('#back-btn').hide();
        }
        $('#edit-btn').click(function(){
            $('#save-btn').show();
            $('#back-btn').show();
            $('#edit-btn').hide();
        })
        $('#back-btn').click(function(){
            $('#save-btn').hide();
            $('#back-btn').hide();
            $('#edit-btn').show();
        })

        hideButton();

    })
</script>
</html>
