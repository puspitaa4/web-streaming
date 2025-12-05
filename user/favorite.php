<?php
session_start();
require '../koneksi.php'; // Pastikan file koneksi sudah benar

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data film favorit dari database
$query = "
    SELECT films.film_id, films.title, films.poster_url 
    FROM favorites 
    INNER JOIN films ON favorites.film_id = films.film_id 
    WHERE favorites.user_id = ?
";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Siapkan array untuk data favorit
$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .sidebar {
            background-color: #4b0082;
            color: white;
            height: 100vh;
            padding: 1rem;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            display: block;
            padding: 0.5rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #5a189a;
        }
        .sidebar a i {
            margin-right: 10px;
        }
        header {
            background-color: #6200ea;
            color: white;
            padding: 1rem;
            text-align: center;
        }

        .card {
            width: 250px;
            height: 300px;

        }

        .card img {
            height: 300px;
        }

        .card-body {
            height: 50px;
            text-align: center;
            color: white;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Dashboard Pengguna</h1>
    </header>

    <div class="d-flex">
        <!-- Sidebar kiri -->
        <aside class="sidebar d-flex flex-column">
            <a href="1_HalamanBeranda.php"><i class="fas fa-home"></i> Home</a>
            <a href="2_DashboardPengguna"><i class="fa-solid fa-user"></i> Dashboard</a>
            <a href="3_ProfilPengguna.php"><i class="fa-solid fa-gear"></i> Profil</a>
            <a href="riwayat_ton.php"><i class="fa-solid fa-film"></i> Riwayat</a>
            <a href="playlist.php"><i class="fa-solid fa-play"></i> Playlist</a>
            <a href="#"><i class="fa-solid fa-star"></i> Favorit</a>
            <a href="langganan.php"><i class="fa-solid fa-dollar-sign"></i> Langganan</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </aside>

        <!-- Konten utama -->
        <main class="flex-grow-1 p-4 bg-dark">
            <h1 class="text-white">Favorit</h1>
            <div class="row">
                    <?php foreach ($favorites as $fav): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <a href="film_session.php?film_id=<?= $fav['film_id']; ?>">
                                <div class="card">
                                    <img alt="Cover" class="card-img-top" src="<?php echo htmlspecialchars($fav['poster_url']);?>" />
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($fav['title']);?></h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach;?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
