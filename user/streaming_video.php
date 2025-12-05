<?php
session_start();
require '../koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if(isset($_SESSION['film_id'])){
    $film_id = $_SESSION['film_id'];

    $query = "SELECT * FROM films WHERE film_id = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $film_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $film = $result->fetch_assoc();
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

        .container{
            margin-bottom: 100px;
        }

        .header {
            background-color: #282828;
            padding: 10px;
            border-radius: 5px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0;
        }

        .info-section {
            margin-top: 40px;
            background-color: #282828;
            padding: 20px;
            border-radius: 5px;
        }

        .info-section .info-header {
            font-size: 18px;
            margin-bottom: 15px;
        }

        .info-section .info-content {
            display: flex;
            flex-wrap: wrap;
        }

        .info-section .info-content img {
            max-width: 200px;
            border-radius: 5px;
            margin-right: 20px;
        }

        .info-section .info-content .info-details {
            flex: 1;
        }

        .info-section .info-content .info-details table {
            width: 100%;
            color: #fff;
            border-collapse: collapse;
        }

        .info-section .info-content .info-details table td {
            padding: 5px 0;
        }

        .download-button {
            background-color: #f39c12;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-align: center;
            display: block;
            width: 200px;
            margin: 10px auto;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .play-button {
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-align: center;
            width: 200px;
            margin: 10px auto;
            cursor: pointer;
        }

        .download-button:hover {
            text-decoration: none;
            color: #fff;
            background-color: #e67e22;
        }

        .ulasan-button {
            border: 1px solid white;
            border-radius: 5px;
            height: 50px;
            text-decoration: none;
        }

        .ulasan-button:hover {
            text-decoration: none;
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

    <div class="container">
        <div class="header">
            <h1><b><?= htmlspecialchars($film['title']);?></b></h1>
        </div>

        <button id="play-button" class="btn btn-primary mt-3 mb-3"><i class="fa fa-play" aria-hidden="true"></i> Play Film</button>

        <iframe id="vid" width='100%' height='500px' frameborder='0' src="<?= htmlspecialchars($film['film_url']);?>" allowfullscreen></iframe>
        <a href="<?= htmlspecialchars($film['download_url']);?>" target="_blank" class="download-button">Download Film</a><br>
        <a href="ulasan.php?film_id=<?php echo $film['film_id'];?>" target="_blank" class="ulasan-button d-flex justify-content-center align-items-center text-white">Berikan ulasan untuk film ini</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            function hideVideo(){
                $('#vid').hide();
            };
            $('#play-button').click(function() {
                $.ajax({
                    url: 'add_history.php', // Endpoint untuk menambahkan ke watch_history
                    type: 'POST',
                    data: {
                        film_id: <?= json_encode($film['film_id']); ?> // Kirim ID film ke server
                    },
                    success: function(response) {
                        console.log('Response from add_history:', response); // Debug respons
                        let result;
                        try {
                            result = JSON.parse(response); // Parsing respons JSON
                            if (result.status === 'success') {
                                console.log('History updated successfully:', result.message);
                            } else {
                                console.error('Error updating history:', result.message);
                            }
                        } catch (e) {
                            console.error('Invalid JSON response:', response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText, error);
                    }
                });
                $('#vid').show();
                $('#play-button').hide();    
            });
            hideVideo();
        });
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

