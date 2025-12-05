<?php
session_start();
require '../koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Cek genre yang paling sering ditonton
$query_genre_count = "
    SELECT fg.genre_id, COUNT(*) AS genre_count
    FROM watch_history wh
    JOIN film_genres fg ON wh.film_id = fg.film_id
    WHERE wh.user_id = ?
    GROUP BY fg.genre_id
    ORDER BY genre_count DESC
    LIMIT 1
";
$stmt = $koneksi->prepare($query_genre_count);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$dominant_genre = $result->fetch_assoc();

// Jika tidak ada riwayat tontonan, tampilkan rekomendasi random
if (!$dominant_genre) {
    $query_recommendations = "
        SELECT f.film_id, f.title, f.poster_url
        FROM films f
        ORDER BY RAND()
    ";
    $stmt = $koneksi->prepare($query_recommendations);
} else {
    // Jika ada genre dominan, ambil rekomendasi film yang belum ditonton
    $genre_id = $dominant_genre['genre_id'];
    $query_recommendations = "
        SELECT DISTINCT f.film_id, f.title, f.poster_url
        FROM films f
        JOIN film_genres fg ON f.film_id = fg.film_id
        WHERE fg.genre_id = ?
        AND f.film_id NOT IN (
            SELECT wh.film_id
            FROM watch_history wh
            WHERE wh.user_id = ?
        )
    ";
    $stmt = $koneksi->prepare($query_recommendations);
    $stmt->bind_param("ii", $genre_id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$recommendations = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streaming Film</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styleberanda.css">
</head>

    <style>
body {
    background-color: #282828;
    color: white;
    font-family: Arial, sans-serif;
}

body::-webkit-scrollbar{
    display: none;
}

.container {
    width: 100%;
    margin-top: 1rem;
}
.card-body{
    height: 60px;
    text-align: center;
    color: white;
}
    </style>
</head>
<body class="sm">
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
        
        <button class="navbar-toggler-icon menu-icon sm" id="menuButton" type="button"><b>=</b></button>

    </nav>

    <div class="side-menu sm" id="sideMenu">
        <button type="button" class="close-btn" id="closeMenu">&times;</button>
        <ul>
            <li><a href="1_HalamanBeranda.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="2_DashboardPengguna.php"><i class="fa-solid fa-user"></i> Dashboard User</a></li>
            <li><a href="4_KategoriKonten.php"><i class="fas fa-film"></i> Genre Film</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>

    </div>
    
    <div class="container mt-4">
        <h1>Direkomendasikan Untuk Anda</h1>
        <div class="row text-white">
            <?php foreach ($recommendations as $recom):?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <a href="film_session.php?film_id=<?= $recom['film_id']; ?>">
                        <div class="card bg-dark">
                            <div class="position-relative">
                                <img alt="<?php echo htmlspecialchars($recom['title']);?>" class="card-img-top" height="300" src="<?php echo htmlspecialchars($recom['poster_url']);?>" width="200"/>
                            </div>
                            <div class="card-body align-items-center">
                                <h5 class="card-title"><?php echo htmlspecialchars($recom['title']);?> </h5>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach;?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
