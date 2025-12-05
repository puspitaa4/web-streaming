<?php
session_start();
require '../koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT playlist_id, name FROM playlists WHERE user_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$playlists = $result->fetch_all(MYSQLI_ASSOC);

// Cek langganan user
$query_sub = "SELECT subscription_id, user_id, status, end_date FROM subscriptions WHERE user_id = ? AND subscription_id = (SELECT MAX(subscription_id) FROM subscriptions)";
$stmt_sub = $koneksi->prepare($query_sub);
$stmt_sub->bind_param("i", $user_id);
$stmt_sub->execute();
$result_sub = $stmt_sub->get_result();
$subscription = $result_sub->fetch_assoc();

$is_accessible = false;
$current_date = new DateTime();

if ($subscription) {
    $end_date = new DateTime($subscription['end_date']);
}


if(isset($_SESSION['film_id'])){
    $film_id = $_SESSION['film_id'];

    $query = "SELECT * FROM films WHERE film_id = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $film_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $film = $result->fetch_assoc();



// Ambil genre film
    $query_genres = "SELECT g.genre_name FROM genres g INNER JOIN film_genres fg ON g.genre_id = fg.genre_id WHERE fg.film_id = ?";
    $stmt_genres = $koneksi->prepare($query_genres);
    $stmt_genres->bind_param("i", $film_id);
    $stmt_genres->execute();
    $result_genres = $stmt_genres->get_result();

    $genre_names = [];
    while ($row = $result_genres->fetch_assoc()){
        $genre_names[] = htmlspecialchars($row['genre_name']);
    }  
    
    $query_check_favorited = "SELECT COUNT(*) AS count FROM favorites WHERE user_id = ? AND film_id = ?";
    $stmt_check_favorited = $koneksi->prepare($query_check_favorited);
    $stmt_check_favorited->bind_param("ii", $_SESSION['user_id'], $film_id);
    $stmt_check_favorited->execute();
    $result_check_favorited = $stmt_check_favorited->get_result();
    if ($result_check_favorited->num_rows > 0) {
        $row = $result_check_favorited->fetch_assoc();
        $is_favorited = $row['count'] > 0;
    } else {
        $is_favorited = false;
    }

    $queryRate = "SELECT AVG(rating_value) AS rating_value FROM ratings WHERE film_id = ?";
    $stmtRate = $koneksi->prepare($queryRate);
    $stmtRate->bind_param("i", $film_id);
    $stmtRate->execute();
    $resultRate = $stmtRate->get_result();
    $rating = $resultRate->fetch_assoc();

    $is_accessible = false; // Default: tidak bisa diakses

    if ($film['access_level'] === 'subscription') {
        // Jika aksesnya membutuhkan langganan
        if ($film['status'] === 'available' && $subscription && $subscription['status'] === 'active') {
            // Hanya bisa diakses jika statusnya available dan pengguna memiliki langganan aktif
            $is_accessible = true;
        }
    } elseif ($film['access_level'] === 'free') {
        // Jika aksesnya gratis
        if ($film['status'] === 'available') {
            // Bisa diakses jika statusnya available
            $is_accessible = true;
        }
    }

}
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
            color:white;
            font-family: Arial, sans-serif;
            background-color: #282828;
        }

        body::-webkit-scrollbar{
            display: none;
        }
        .header {
            background-color: #282828;
            padding: 10px;
            border-radius: 5px;
        }

        .header h1 {
            font-size: 35px;
            margin: 0;
        }

        .info-section {
            margin-top: 10px;
            background-color: #282828;
            padding: 20px;
            border-radius: 5px;
        }

        .info-section .info-content {
            display: flex;
            flex-wrap: wrap;
        }

        .info-section .info-content img {
            max-width: 200px;
            max-height: 300px;
            border-radius: 5px;
            margin-right: 20px;
        }

        .info-section .info-content .info-details {
            flex: 1;
        }

        .info-section .info-content .info-details table {
            width: 500px;
            color: #fff;
            border-collapse: collapse;
        }

        .info-section .info-content .info-details table td {
            padding: 5px;
        }

        /* Watch Now button below info */
        .watch-now {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #444;  /* Adds a line separating the button from the content */
        }

        .watch-now a {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #c0392b;
            color: #fff;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .watch-now a:hover {
            background-color: #e74c3c;
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
        
        <button class="navbar-toggler-icon menu-icon sm" id="menuButton" type="button"><i class="fa fa-bars" aria-hidden="true"></i></button>

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

    <div class="container-fluid mt-3">
        <div class="header">
            <h1><b>Informasi Film</b></h1>
        </div>
        <div class="add d-flex justify-content-end">
            <a href="<?= $is_favorited ? 'hapus_favorit.php' : 'tambah_favorit.php'; ?>?id=<?= htmlspecialchars($film['film_id'])?>" class="btn btn-<?= $is_favorited ? 'danger' : 'primary'; ?> mr-3"><?= $is_favorited ? "Delete from favorite <i class='fa fa-trash' aria-hidden='true'></i>" : "Add to favorite <i class='fa fa-bookmark' aria-hidden='true'></i>"; ?></a>
            <button class="btn btn-primary mr-3" onclick="openModal()">
                <i class="fa fa-plus" aria-hidden="true"></i> Add to playlist
            </button>
        </div>
        <div class="info-section">
            <div class="info-content">
                <img src="<?= htmlspecialchars($film['poster_url']);?>" alt="<?= htmlspecialchars($film['title']);?>">
                <div class="info-details">
                    <table>
                        <tr><td><strong>Judul:</strong></td><td><?= htmlspecialchars($film['title']);?></td></tr>
                        <tr><td><strong>Studio:</strong></td><td><?= htmlspecialchars($film['studio']);?></td></tr>
                        <tr><td><strong>Tanggal Rilis:</strong></td><td><?= htmlspecialchars($film['release_schedule']);?></td></tr>
                        <tr><td><strong>Status:</strong></td><td><?= htmlspecialchars($film['status']);?></td></tr>
                        <tr><td><strong>Akses:</strong></td><td><?= htmlspecialchars($film['access_level']);?></td></tr>
                        <tr><td><strong>Rating:</strong></td><td><?= htmlspecialchars($rating['rating_value']);?>/5.0</td></tr>
                        <tr><td><strong>Genre:</strong></td><td><?php 
                            if (!empty($genre_names)) {
                                echo implode(", ", $genre_names);
                            } else {
                                echo "No genres available.";
                            }
                            ?></td></tr>
                    </table>
                    <br>
                    <p><strong>Sinopsis:</strong><br><?= htmlspecialchars($film['description']);?></p>
                    <br>
                    <p><b>Trailer:</b></p>
                    <iframe width='500' height='300' frameborder='0' src="<?= htmlspecialchars($film['trailer']);?>" allowfullscreen></iframe>

                </div>
            </div>
            <!-- Watch Now Button placed below the info content -->
            <div class="watch-now">
                <?php if ($is_accessible): ?>
                    <a href="streaming_video.php" target="_blank">Watch Now</a>
                <?php else: ?>
                    <a href="#" class="btn btn-danger" onclick="alert('Anda tidak dapat mengakses film ini. Silahkan cek status langganan atau ketersediaan film!');">Watch Now</a>
                <?php endif; ?>
            </div>


            <div id="playlistModal" style="display:none; position:fixed; top:10px; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
                <div style="margin: 50px auto; padding: 20px; background-color: rgba(0,0,0,0.5); width: 50%;">
                    <h2>Choose a Playlist</h2>
                    <?php if (!empty($playlists)): ?>
                        <form>
                            <?php foreach ($playlists as $playlist): ?>
                                <label>
                                    <input type="radio" name="playlist" value="<?php echo $playlist['playlist_id']; ?>">
                                    <?php echo htmlspecialchars($playlist['name']); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </form>
                    <?php else: ?>
                        <p>No playlists available. Create a new one below.</p>
                    <?php endif; ?>

                    <button onclick="toggleNewPlaylistForm()" class="btn-secondary">+ Create New Playlist</button>

                    <!-- Form untuk playlist baru -->
                    <div id="newPlaylistForm" style="display:none; margin-top: 20px;">
                        <h3>New Playlist</h3>
                        <input type="text" id="newPlaylistName" placeholder="New Playlist Name"><br><br>
                    </div>
                    
                    <button onclick="addToPlaylist(<?php echo $film_id; ?>)" class="btn-success">Submit</button>
                    <button onclick="closePlaylistModal()" style="margin-top: 20px;" class="btn-danger">Cancel</button>
                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
         // Menampilkan atau menyembunyikan form untuk playlist baru
        function toggleNewPlaylistForm() {
            const form = document.getElementById('newPlaylistForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function addToPlaylist(filmId) {
            const playlistId = document.querySelector('input[name="playlist"]:checked')?.value;
            const newPlaylistName = document.getElementById('newPlaylistName')?.value;

            const data = playlistId 
                ? `film_id=${filmId}&playlist_id=${playlistId}` 
                : `film_id=${filmId}&new_playlist_name=${encodeURIComponent(newPlaylistName)}`;

            fetch('manage_playlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: data
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    // Reset pilihan playlist
                    const selectedPlaylist = document.querySelector('input[name="playlist"]:checked');
                    if (selectedPlaylist) {
                        selectedPlaylist.checked = false;
                    }
                    closePlaylistModal();
                }
            })
            .catch(error => console.error('Error:', error));
        }



        function openModal() {
            document.getElementById('playlistModal').style.display = 'block';
        }

        function closePlaylistModal() {
            const form = document.getElementById('newPlaylistForm');
            form.style.display = 'none';
            const selectedPlaylist = document.querySelector('input[name="playlist"]:checked');
            if (selectedPlaylist) {
                selectedPlaylist.checked = false;
            }
            document.getElementById('newPlaylistName').value = '';
            document.getElementById('playlistModal').style.display = 'none';
        }


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