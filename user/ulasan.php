<?php
session_start();
require '../koneksi.php'; // Pastikan file koneksi sudah ada

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    die("Anda harus login untuk memberikan rating.");
}

$user_id = $_SESSION['user_id'];
$film_id = isset($_GET['film_id']) ? intval($_GET['film_id']) : null; // Ambil film_id dari URL

// Periksa apakah film_id valid
if (!$film_id) {
    die("Film tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Tangkap nilai rating dari POST dan konversi ke integer
    $rating_value = isset($_POST['rating']) ? intval($_POST['rating']) : 0; 

    // Periksa apakah nilai rating valid (1 hingga 5)
    if ($rating_value < 1 || $rating_value > 5) {
        die("Nilai rating tidak valid.");
    }

    // Periksa apakah pengguna sudah memberikan rating untuk film ini
    $query = "SELECT * FROM ratings WHERE user_id = ? AND film_id = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ii", $user_id, $film_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Jika rating sudah ada, update nilai rating
        $query = "UPDATE ratings SET rating_value = ?, created_at = NOW() WHERE user_id = ? AND film_id = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("iii", $rating_value, $user_id, $film_id);
    } else {
        // Jika belum ada, masukkan rating baru
        $query = "INSERT INTO ratings (film_id, user_id, rating_value) VALUES (?, ?, ?)";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("iii", $film_id, $user_id, $rating_value);
    }

    if ($stmt->execute()) {
        echo "<script> alert('Rating berhasil disimpan')</script>";
    } else {
        echo "Terjadi kesalahan saat menyimpan rating.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan Pengguna</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #282828;
            margin: 0;
            padding: 0;
        }

        body::-webkit-scrollbar{
            display: none;
        }

        .container {
            width: 100%;
            margin: auto;
            background-color: #282828;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            color: white;
            margin-bottom: 5px;
            font-weight: bold;
        }

        select, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            padding: 8px 16px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .rate-review {
            background-color: #282828;
        }

        .review-list {
            margin-top: 20px;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 30px;
            color: #ddd;
            transition: color 0.3s;
        }

        .star-rating input:checked ~ label {
            color: #f5c518; /* Warna bintang yang terisi */
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #f5c518; /* Sorot bintang saat hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Formulir untuk memberikan rating -->
        <div class="rate-review">
            <form method="POST" action="ulasan.php?film_id=<?php echo htmlspecialchars($film_id);?>">
                <label for="rating">Pilih Rating:</label>
                <div class="star-rating">
                    <input type="radio" id="star5" name="rating" value="5"><label for="star5">★</label>
                    <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                    <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                    <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                    <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                </div>
                <button type="submit" name="ratingsub">Kirim Rating</button>
            </form>
        </div>

        <!-- Daftar Ulasan -->
        <div class="review-list">
        </div>
    </div>
    
    <div id="disqus_thread"></div>
    <script>
        /**
        *  RECOMMENDED CONFIGURATION VARIABLES: EDIT AND UNCOMMENT THE SECTION BELOW TO INSERT DYNAMIC VALUES FROM YOUR PLATFORM OR CMS.
        *  LEARN WHY DEFINING THESE VARIABLES IS IMPORTANT: https://disqus.com/admin/universalcode/#configuration-variables    */
        var disqus_config = function () {
        this.page.url = window.location.href;  // Replace PAGE_URL with your page's canonical URL variable
        this.page.identifier = '<?php echo "film_" . htmlspecialchars($film_id); ?>'; // Replace PAGE_IDENTIFIER with your page's unique identifier variable
        };
        (function() { // DON'T EDIT BELOW THIS LINE
        var d = document, s = d.createElement('script');
        s.src = 'https://projectstream.disqus.com/embed.js';
        s.setAttribute('data-timestamp', +new Date());
        (d.head || d.body).appendChild(s);
        })();
    </script>
    <noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>

    <script>
        // Pastikan form mengirimkan data yang benar
        const form = document.querySelector('form');
        form.addEventListener('submit', function(event) {
            const rating = document.querySelector('input[name="rating"]:checked');
            if (!rating) {
                alert('Anda harus memilih rating terlebih dahulu.');
                event.preventDefault();
            }
        });
    </script>

</body>
</html>
