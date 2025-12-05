<?php
require_once 'koneksi.php';
session_start();
// Ambil daftar genre
$query_genres = "SELECT * FROM genres";
$result_genres = $koneksi->query($query_genres);

$admin_id = $_SESSION['user_id'];
$query = "SELECT username FROM users WHERE user_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (isset($_POST['tambah'])) {
    $film_url = $_POST['film_url'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $poster_url = $_POST['poster_url'];
    $access_level = $_POST['access_level'];
    $release_schedule = $_POST['release_schedule'];
    $download = $_POST['download'];
    $studio = $_POST['studio'];
    $trailer = $_POST['trailer'];
    $genres = isset($_POST['genres']) ? $_POST['genres'] : []; // Array genre yang dipilih
    $current_date = date('Y-m-d');
    if ($release_schedule > $current_date){
        $status = 'unavailable';
    }else{
        $status = 'available';
    }
    // Tambahkan film ke tabel films
    $query_film = "INSERT INTO films (film_url, title, description, poster_url, status, access_level, release_schedule, download_url, studio, trailer) VALUES (?, ?, ?, ?, '$status',?, ?, ?, ?, ?)";
    $stmt_film = $koneksi->prepare($query_film);
    $stmt_film->bind_param("sssssssss", $film_url, $title, $description, $poster_url, $access_level, $release_schedule, $download, $studio, $trailer);
    $stmt_film->execute();
    $film_id = $stmt_film->insert_id; // Ambil ID film yang baru ditambahkan
    $stmt_film->close();

    // Tambahkan genre yang dipilih ke tabel film_genres
    if (!empty($genres)) {
        $query_genre = "INSERT INTO film_genres (film_id, genre_id) VALUES (?, ?)";
        $stmt_genre = $koneksi->prepare($query_genre);

        foreach ($genres as $genre_id) {
            $stmt_genre->bind_param("ii", $film_id, $genre_id);
            $stmt_genre->execute();
        }

        $stmt_genre->close();
    }

    // Redirect kembali ke halaman manajemen film
    header('Location: manage_films.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Film</title>
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
            margin-bottom: 30px;
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
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active text-light" aria-current="page" href="home.php">Home</a>
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
        <h1 class="heading">Tambah Film</h1>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="judul_film" class="form-label">Judul Film:</label>
                <input type="text" class="form-control" id="judul_film" name="title" required>
            </div>

            <div class="mb-3">
                <label for="film_url" class="form-label">Film (URL):</label>
                <input type="text" class="form-control" id="film_url" name="film_url" required>
            </div>

            <div class="mb-3">
                <label for="poster" class="form-label">Poster (URL):</label>
                <input type="text" class="form-control" id="poster" name="poster_url" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi:</label>
                <textarea name="description" id="deskripsi" class="form-control" required></textarea>
            </div>

            <div class="mb-3">
                <label for="akses" class="form-label">Akses Level:</label>
                <select name="access_level" id="akses" class="form-select">
                <option value="">--Pilih Level Akses--</option>
                <option value="free">Gratis</option>
                <option value="subscription">Berlangganan</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="rilis" class="form-label">Jadwal Rilis:</label>
                <input type="date" name="release_schedule" id="rilis" class="form-control">
            </div>

            <div class="mb-3">
                <label for="download" class="form-label">Download (URL):</label>
                <input type="text" class="form-control" id="download" name="download" required>
            </div>

            <div class="mb-3">
                <label for="studio" class="form-label">Studio:</label>
                <input type="text" class="form-control" id="studio" name="studio" required>
            </div>

            <div class="mb-3">
                <label for="trailer" class="form-label">Trailer (URL):</label>
                <input type="text" class="form-control" id="trailer" name="trailer" required>
            </div>

            <div class="mb-3">
            <label class="form-label">Genre:</label><br>
            <?php while ($genre = $result_genres->fetch_assoc()) { ?>
            <input type="checkbox" name="genres[]" value="<?php echo $genre['genre_id']; ?>"> 
            <?php echo $genre['genre_name']; ?><br>
            <?php } ?>
            </div>

            <button type="submit" class="btn btn-primary" name="tambah">Simpan</button>
            <a href="manage_films.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>