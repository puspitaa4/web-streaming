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

// Query untuk mendapatkan 10 film terbaru
$query = "SELECT film_id, title, poster_url, access_level, created_at FROM films ORDER BY created_at DESC LIMIT 10";
$result = mysqli_query($koneksi, $query);
// Simpan hasil ke dalam array
$films = [];
while ($row = mysqli_fetch_assoc($result)) {
    $films[] = $row;
}
$isActive = true;

$queryHistory = "SELECT wh.last_watched, f.title, f.poster_url, f.film_id FROM watch_history wh JOIN films f ON wh.film_id = f.film_id WHERE wh.user_id = ? ORDER BY wh.last_watched DESC LIMIT 10";

$stmt = $koneksi->prepare($queryHistory);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$resultHistory = $stmt->get_result();
$watch_history = $resultHistory->fetch_all(MYSQLI_ASSOC);

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
        LIMIT 10
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
        LIMIT 10
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
        
        <button class="navbar-toggler-icon menu-icon sm" id="menuButton" type="button"><i class="fa fa-bars" aria-hidden="true"></i></button>

    </nav>

    <div class="side-menu sm" id="sideMenu">
        <button type="button" class="close-btn" id="closeMenu">&times;</button>
        <ul>
            <li><a href="#"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="2_DashboardPengguna.php"><i class="fa-solid fa-user"></i> Dashboard User</a></li>
            <li><a href="4_KategoriKonten.php"><i class="fas fa-film"></i> Genre Film</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>

    </div>

    <div class="container-fluid mt-4">
        <div id="customCarousel" class="carousel slide carousel-fade" data-ride="carousel">
            <div class="carousel-inner">
        <!-- Slide 1 -->
            <?php if(count($films) > 0):?>
                <?php foreach ($films as $film):?>
                    <div class="carousel-item <?= $isActive ? 'active' : ''; ?>">
                        <img src="<?= htmlspecialchars($film['poster_url']); ?>" class="d-block" alt="First Slide">
                        <div class="carousel-caption">
                            <h3><?= htmlspecialchars($film['title']); ?></h3>
                            <p><?= htmlspecialchars($film['access_level']); ?></p>
                            <a href="film_session.php?film_id=<?= $film['film_id']; ?>" class="btn btn-custom">Lihat Lebih Lanjut</a>
                        </div>
                    </div>
                    <?php $isActive = false; ?>
                <?php endforeach; ?>
            <?php endif; ?>    
    <!-- Slide 2 -->
            </div>

  <!-- Controls -->
            <a class="carousel-control-prev" href="#customCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#customCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
        <br>
        <div class="mt-1">
            <h4>History</h4>
            <a href="riwayat_ton.php"><p class="d-flex justify-content-end text-white view">View more</p></a>
        </div>
        <div class="cont sm">
            <?php if(empty($watch_history)):?>
                <p>Tidak ada riwayat tontonan.</p>
            <?php else: ?>
                <?php foreach ($watch_history as $row):?>
                    <div class="col-3">
                        <a href="film_session.php?film_id=<?= $row['film_id']; ?>"><figure class="ss"><img src="<?= htmlspecialchars($row['poster_url']); ?>" alt="<?= htmlspecialchars($row['title']); ?>">
                            <div class="overlay"></div>
                            <figcaption><?= htmlspecialchars($row['title']); ?></figcaption>
                        </figure></a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="mt-1">
            <h4>New Release</h4>
            <a href="newrelease.php"><p class="d-flex justify-content-end text-white view">View more</p></a>
        </div>
        <div class="cont sm">
            <?php if(count($films) > 0):?>
                <?php foreach ($films as $film):?>
                    <div class="col-3">
                        <a href="film_session.php?film_id=<?= $film['film_id']; ?>"><figure class="ss"><img src="<?= htmlspecialchars($film['poster_url']); ?>" alt="<?= htmlspecialchars($film['title']); ?>">
                            <div class="overlay"></div>
                            <figcaption><?= htmlspecialchars($film['title']); ?></figcaption>
                        </figure></a>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Film tidak ditemukan.</p>
            <?php endif; ?>
        </div>
        <div class="mt-5 mb-1">
            <h4>For You</h4>
            <a href="rekomendasi.php"><p class="d-flex justify-content-end text-white view">View more</p></a>
        </div>
        <div class="d-flex"></div>
        <div class="cont sm">
            <?php foreach ($recommendations as $filmss):?>
                <div class="col-3">
                    <a href="film_session.php?film_id=<?= $film['film_id']; ?>"><figure class="ss"><img src="<?= htmlspecialchars($filmss['poster_url']);?>" alt="<?= htmlspecialchars($filmss['title']); ?>">
                        <div class="overlay"></div>
                        <figcaption><?= htmlspecialchars($filmss['title']); ?></figcaption>
                    </figure></a>
                </div>
            <?php endforeach;?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
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