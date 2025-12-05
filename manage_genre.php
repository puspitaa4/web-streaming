<?php
// Koneksi ke database
require 'koneksi.php';
session_start();

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
if (isset($_POST['filter'])) {
    $selected_genre = $_POST['genre'];

    // Query untuk mendapatkan film berdasarkan genre yang dipilih
    $query_films = "
    SELECT f.film_id, f.title, f.poster_url, f.status, f.access_level 
    FROM films f
    LEFT JOIN film_genres fg ON f.film_id = fg.film_id
    WHERE fg.genre_id = ?
    ORDER BY f.title ASC";
    $stmt = $koneksi->prepare($query_films);
    $stmt->bind_param("i", $selected_genre); // $selected_genre sekarang berisi genre_id
    $stmt->execute();
    $result_films = $stmt->get_result();
    $_SESSION['genre'] = $selected_genre;
    // Menyimpan hasil ke array
    while ($row = $result_films->fetch_assoc()) {
        $films[] = $row;
    }
}

if(isset($_POST['tambah'])){
    $nama = $_POST['nama'];
    $queryGenre = "INSERT INTO genres (genre_name) VALUES ('$nama')";
    if (mysqli_query($koneksi, $queryGenre)) {
        header("Location: manage_genre.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}


if (isset($_POST['tambahfilm'])) {
    // Ambil genre_id dari session
    $genre_id = intval($_SESSION['genre']); // Ambil langsung dari session
    $film_id = intval($_POST['film']); // Ambil film_id dari form

    // Validasi input: Pastikan kedua nilai valid
    if ($film_id > 0 && $genre_id > 0) {
        // Query untuk menambahkan film ke genre
        $queryFilm = "INSERT INTO film_genres (film_id, genre_id) VALUES (?, ?)";
        $stmt = $koneksi->prepare($queryFilm);
        $stmt->bind_param("ii", $film_id, $genre_id);

        if ($stmt->execute()) {
            // Redirect jika berhasil
            header("Location: manage_genre.php");
            exit();
        } else {
            echo "<script>alert('Terjadi kesalahan saat menambahkan film ke genre.');</script>";
        }
    } else {
        echo "<script>alert('Pilih film yang valid sebelum menambahkannya ke genre.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Manage Genre</title>
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
                        <a class="nav-link text-light" aria-current="page" href="adm/manage_user.php">Activity Logs</a>
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
    <div class="container mt-5">
        <h1 class="mb-4">Manage Genre</h1>

        <!-- Dropdown untuk memilih genre -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="genre" class="form-label">Select Genre</label>
                <select name="genre" id="genre" class="form-select" required>
                    <option value="">-- Select Genre --</option>
                    <?php while ($genre = mysqli_fetch_assoc($result_genres)) : ?>
                        <option value="<?= $genre['genre_id']; ?>" <?= $selected_genre == $genre['genre_id'] ? 'selected' : ''; ?>>
                            <?= $genre['genre_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="d-flex justify-content-end p-3">
                <button type="submit" class="btn btn-primary" name="filter">Filter</button>
                <button class="btn btn-success btn-custom ms-3" type="button" id="add">Tambah Genre</button>
            </div>
        </form>
        <form method="POST" action="" id="addForm">
            <div class="mb-3">
                <Label for="film" class="form-label">Nama Genre:</Label>
                <input type="text" class="form-control" id="nama" name="nama">
                <div class="d-flex justify-content-end p-3">
                    <button type="submit" class="btn btn-primary" name="tambah">Tambah</button>
                    <button class="btn btn-secondary btn-custom ms-3" type="button" id="back">Kembali</button>
                </div>
            </div>
        </form>

        <!-- Tabel film berdasarkan genre -->
        <table class="table table-bordered table-hover mt-4">
            <thead class="table-primary">
                <tr>
                    <th>No.</th>
                    <th>Poster</th>
                    <th>Judul</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if (count($films) > 0) {
                    foreach ($films as $index => $data) {
                        echo "<tr>
                                <td>" . ($index + 1) . "</td>
                                <td><img src='". $data['poster_url'] . "' width='100'></td>
                                <td>" . htmlspecialchars($data['title']) . "</td>
                                <td>
                                    <a href='delete_from_genre.php?id=" . htmlspecialchars($data['film_id']) . "' class='btn btn-danger btn-sm' onclick=\"return confirm('Apakah Anda yakin ingin menghapus film ini?')\"><i class='fas fa-trash'></i> Hapus</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>Tidak ada Film</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-end p-3">
            <?php
                echo "<a href='hapus_genre.php?id=" . htmlspecialchars($selected_genre) . "' class='btn btn-danger btn-sm' onclick=\"return confirm('Apakah Anda yakin ingin menghapus genre ini?')\"><i class='fas fa-trash'></i> Hapus</a>";
            ?>
            <button class="btn btn-primary btn-custom ms-3" type="button" id="addfilm">Tambah Film</button>
        </div>
        <form method="POST" action="" id="addFilmForm">
            <div class="mb-3">
                <Label for="film" class="form-label">Film:</Label>
                <select name="film" id="film" class="form-select">
                    <option value="">--Pilih Judul Film--</option>
                <?php
                // Ambil data film dari database
                $films_query = "SELECT film_id, title FROM films";
                $films_result = $koneksi->query($films_query);
                while ($film = $films_result->fetch_assoc()) {
                    echo "<option value='{$film['film_id']}'>{$film['title']}</option>";
                }
                ?>
                </select>
                <div class="d-flex justify-content-end p-3">
                    <button type="submit" class="btn btn-primary" name="tambahfilm">Tambah</button>
                    <button class="btn btn-secondary btn-custom ms-3" type="button" id="back2">Kembali</button>
                </div>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){
            function hideForm(){
                $('#addForm').hide();
                $('#addFilmForm').hide();
            }
            $('#add').click(function(){
                $('#addForm').show();
            })
            $('#back').click(function(){
                hideForm();
            })
            $('#addfilm').click(function(){
                $('#addFilmForm').show();
            })
            $('#back2').click(function(){
                hideForm();
            })
            hideForm();
        })
    </script>
</body>
</html>