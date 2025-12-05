<?php
// Koneksi ke database
require '../koneksi.php';

session_start();
$admin_id = $_SESSION['user_id'];
$query = "SELECT username FROM users WHERE user_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$stmt->close();

// Inisialisasi variabel
$films = [];
$chart_data = [];
$selected_film_id = null;

// Ambil daftar film untuk dropdown
$query_films = "SELECT film_id, title FROM films";
$result_films = mysqli_query($koneksi, $query_films);

while ($row = mysqli_fetch_assoc($result_films)) {
    $films[] = $row;
}

// Jika film dipilih
if (isset($_POST['film_id'])) {
    $selected_film_id = $_POST['film_id'];

    // Query untuk mendapatkan data rating
    $query_ratings = "
        SELECT rating_value, COUNT(*) AS jumlah
        FROM ratings
        WHERE film_id = ?
        GROUP BY rating_value
        ORDER BY rating_value ASC";
    $stmt = $koneksi->prepare($query_ratings);
    $stmt->bind_param("i", $selected_film_id);
    $stmt->execute();
    $result_ratings = $stmt->get_result();

    // Inisialisasi data rating
    $chart_data = array_fill(0, 5, 0);

    while ($row = $result_ratings->fetch_assoc()) {
        $chart_data[$row['rating_value'] - 1] = (int)$row['jumlah'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Rating Report</title>
    <style>
        body::-webkit-scrollbar{
            display: none;
        }
        div#grafik {
            width: 90%;
            max-width: 800px;
        }
        nav{
            background-color: #444;
        }
    </style>
</head>
<body>
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
                            <li><a class="dropdown-item" href="../manage_films.php">Manage Films</a></li>
                            <li><a class="dropdown-item" href="../manage_genre.php">Manage Genre</a></li>
                            <li><a class="dropdown-item" href="../stream_setting.php">Stream Settings</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            User Reviews
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="rating.php">Ratings Report</a></li>
                            <li><a class="dropdown-item" href="https://projectstream.disqus.com/admin/" target="_blank">Manage Comments</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" aria-current="page" href="log_aktivitas.php">Activity Logs</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-user-circle fa-lg" aria-hidden="true"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="pengaturan_admin.php"><i class="fas fa-user"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="./logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<div class="container mt-5">
    <h1 class="mb-4">Rating Report</h1>

    <!-- Dropdown Pilihan Film -->
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="film_id" class="form-label">Select Film</label>
            <select name="film_id" id="film_id" class="form-select" required>
                <option value="">-- Select Film --</option>
                <?php foreach ($films as $film) : ?>
                    <option value="<?= $film['film_id']; ?>" <?= $selected_film_id == $film['film_id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($film['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">View Report</button>
    </form>

    <!-- Chart -->
    <?php if ($selected_film_id && !empty($chart_data)) : ?>
        <div id="grafik">
            <canvas id="ratingChart" width="400" height="200"></canvas>
        </div>
        <script>
            const ctx = document.getElementById('ratingChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['1', '2', '3', '4', '5'], // Rating Values
                    datasets: [{
                        label: 'Number of Users',
                        data: <?= json_encode($chart_data); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 206, 86, 0.5)',
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(153, 102, 255, 0.5)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Users'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Rating'
                            }
                        }
                    }
                }
            });
        </script>
    <?php elseif ($selected_film_id): ?>
        <div class="alert alert-warning">No rating data available for the selected film.</div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>