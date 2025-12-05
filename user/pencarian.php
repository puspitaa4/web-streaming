<?php
session_start();
require '../koneksi.php';
// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Konten</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #282828;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background-color: #282828;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            font-size: 2.5em;
            color: #fff;
            margin-bottom: 30px;
        }

        .search-bar {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }

        .search-bar input {
            width: 100%;
            padding: 12px;
            font-size: 1.1em;
            color: #fff;
            background-color: #282828;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .results {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .card {
            display: flex;
            flex-direction: column;
            background-color: #282828;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-info h3 {
            margin: 0;
            text-align: center;
            color: #fff;
            font-size: 1.2em;
        }

        .results p {
            text-align: center;
            width: 100%;
            font-size: 1.2em;
            color: #fff;
            font-weight: bold;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Pencarian Konten</h1>

        <div class="search-bar">
            <input type="text" id="searchInput" name="searchInput" placeholder="Cari judul atau studio..." value="<?= htmlspecialchars($_POST['searchInput'] ?? '') ?>">
        </div>

        <div class="results" id="results">
        </div>
    </div>

    <script>
    $(document).ready(function () {
        $('#searchInput').on('input', function () {
            let query = $(this).val();

            if (query.trim() !== "") {
                $.ajax({
                    url: 'search.php',
                    type: 'GET',
                    data: { q: query },
                    dataType: 'json',
                    success: function (data) {
                        let resultDiv = $('.results');
                        resultDiv.empty();

                        if (data.length > 0) {
                            data.forEach(function (film) {
                                let filmCard = `
                                    <div class="card">
                                        <a href="film_session.php?film_id=${film.film_id}" style="text-decoration: none; color: inherit;">
                                            <img src="${film.poster_url}" alt="${film.title}">
                                            <div class="card-info">
                                                <h3>${film.title}</h3>
                                            </div>
                                        </a>
                                    </div>`;
                                resultDiv.append(filmCard);
                            });
                        } else {
                            resultDiv.html('<p>Tidak ada hasil ditemukan.</p>');
                        }
                    },
                    error: function () {
                        alert('Terjadi kesalahan saat memuat hasil pencarian.');
                    }
                });
            } else {
                $('.results').empty();
                }
        });
    });
    </script>
</body>
</html>