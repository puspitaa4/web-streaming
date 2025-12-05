<?php
session_start();
require '../koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$query = "SELECT username FROM users WHERE user_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$selected_genre = '';
$films = [];

// Query untuk mendapatkan daftar genre
$query_genres = "SELECT genre_id, genre_name FROM genres";
$result_genres = mysqli_query($koneksi, $query_genres);

$query_Film = "SELECT film_id, title FROM films";
$result_film = mysqli_query($koneksi, $query_Film);
$hasil = mysqli_fetch_assoc($result_film);

// Jika genre dipilih melalui dropdown
$selected_access = ''; // Menyimpan filter akses

if (isset($_POST['filter'])) {
    $selected_genre = $_POST['genre'];
    $selected_access = $_POST['access'];

    // Awal query
    $query_films = "
    SELECT DISTINCT f.film_id, f.title, f.poster_url, f.status, f.access_level 
    FROM films f
    LEFT JOIN film_genres fg ON f.film_id = fg.film_id
    WHERE 1=1";
    
    $params = [];
    $types = '';

    // Tambahkan filter genre jika ada
    if (!empty($selected_genre) && $selected_genre !== '-- Select Genre --') {
        $query_films .= " AND fg.genre_id = ?";
        $params[] = $selected_genre;
        $types .= 'i';
    }
    
    // Tambahkan filter akses jika ada
    if (!empty($selected_access)) {
        $query_films .= " AND f.access_level = ?";
        $params[] = $selected_access;
        $types .= 's';
    }

    $query_films .= " ORDER BY f.title ASC";

    // Persiapkan statement
    $stmt = $koneksi->prepare($query_films);

    // Bind parameter hanya jika ada parameter
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result_films = $stmt->get_result();

    // Simpan hasil ke array
    $films = [];
    while ($row = $result_films->fetch_assoc()) {
        $films[] = $row;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Konten</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styleberanda.css">
    <style>
        .category-card {
        text-align: center;
        border: 1px solid #f0f0f0;
        border-radius: 10px;
        color: white;
        text-decoration: none;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        }

        .category-card:hover {
        transform: translateY(-5px);
        }

        .category-card img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 10px;
        }

        .category-card .views {
        color: #777;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-sm">
        <a class="navbar-brand" href="#" style="font-family: 'Cursive'; color: gold;">STREAMING APP</a>
        <button class="navbar-toggler" type="button" id="menuToggle">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="input-wrapper my-2 my-sm-0 ml-auto">
            <button class="icon" id="search" onclick="search()"> 
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="25px" width="25px">
                    <path stroke-linejoin="round" stroke-linecap="round" stroke-width="1.5" stroke="#fff" d="M11.5 21C16.7467 21 21 16.7467 21 11.5C21 6.25329 16.7467 2 11.5 2C6.25329 2 2 6.25329 2 11.5C2 16.7467 6.25329 21 11.5 21Z"></path>
                    <path stroke-linejoin="round" stroke-linecap="round" stroke-width="1.5" stroke="#fff" d="M22 22L20 20"></path>
                </svg>
            </button>
        </div>
        
        <button class="navbar-toggler-icon menu-icon sm" id="menuButton" type="button"><i class="fa fa-bars" aria-hidden="true"></i></button>

    </nav>

    <div class="side-menu sm" id="sideMenu">
        <button type="button" class="close-btn" id="closeMenu">&times;</button>
        <ul>
            <li><a href="1_HalamanBeranda.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="2_DashboardPengguna.php"><i class="fa-solid fa-user"></i> Dashboard User</a></li>
            <li><a href="#"><i class="fas fa-film"></i> Genre Film</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>

    </div>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Kategori Konten</h2>
        <form method="POST" action="">
            <div class="text-center mb-4">
                <select name="genre" id="genre" class="custom-select" required>
                    <option selected>-- Select Genre --</option>
                    <?php while ($genre = mysqli_fetch_assoc($result_genres)) : ?>
                        <option value="<?= $genre['genre_id']; ?>" <?= $selected_genre == $genre['genre_id'] ? 'selected' : ''; ?>>
                            <?= $genre['genre_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="text-center mb-4">
                <select name="access" id="access" class="custom-select">
                    <option value="">-- Select Access Level --</option>
                    <option value="free" <?= isset($_POST['access']) && $_POST['access'] === 'free' ? 'selected' : ''; ?>>Free</option>
                    <option value="subscription" <?= isset($_POST['access']) && $_POST['access'] === 'subscription' ? 'selected' : ''; ?>>Premium</option>
                </select>
            </div>

            <div class="d-flex justify-content-start mb-3">
                <button type="submit" class="btn btn-primary" name="filter">Filter</button>
            </div>
        </form>
        <div class="row">
            <?php foreach ($films as $index => $data):?>
                <div class="col-md-4 mb-4">
                    <a href="film_session?film_id=<?php htmlspecialchars($data['film_id']);?>">
                        <div class="category-card">
                            <img src="<?php echo htmlspecialchars($data['poster_url']);?>" alt="Action">
                            <h5><?php echo htmlspecialchars($data['title']);?></h5>
                        </div>
                    </a>
                </div>
            <?php endforeach;?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Open side menu
        document.getElementById("menuButton").addEventListener("click", function() {
            document.getElementById("sideMenu").style.width = "250px";
        });

        // Close side menu
        document.getElementById("closeMenu").addEventListener("click", function() {
            document.getElementById("sideMenu").style.width = "0";
        });

        function search(){
            window.location = "pencarian.php";
        };
    </script>
</body>
</html>
