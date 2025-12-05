<?php
session_start();
require '../koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT subscription_id, user_id, status FROM subscriptions WHERE user_id = ? AND subscription_id = (SELECT MAX(subscription_id) FROM subscriptions)";
$stmt = $koneksi->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$subs = $result->fetch_assoc();
$is_subscribed = ($subs['status'] === 'active'); // Menggunakan perbandingan ===
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langganan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #282828;
        }
        body::-webkit-scrollbar{
            display : none;
        }
        .app-header {
            background-color: #005792;
            color: white;
            text-align: center;
            padding: 1rem 0;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        header {
            background-color: #6200ea;
            color: white;
            padding: 1rem;
            text-align: center;
            width: 100%;
            z-index: 5;
        }
        .sidebar {
            width: 250px;
            background-color: #4b0082;
            color: white;
            padding: 1rem;
            box-sizing: border-box;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin: 1rem 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            display: block;
            padding: 0.5rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .sidebar ul li a:hover {
            background-color: #5a189a;
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        .plan {
        border-radius: 16px;
        box-shadow: 0 30px 30px -25px rgba(0, 38, 255, 0.205);
        padding: 10px;
        background-color: #fff;
        color: #697e91;
        max-width: 300px;
        margin-left: 50px;
        max-height: 300px;
        margin-top: 20px;
        }

        .plan strong {
        font-weight: 600;
        color: #425275;
        }

        .plan .inner {
        align-items: center;
        padding: 20px;
        padding-top: 40px;
        background-color: #ecf0ff;
        border-radius: 12px;
        position: relative;
        }

        .plan .pricing {
        position: absolute;
        top: 0;
        right: 0;
        background-color: #bed6fb;
        border-radius: 99em 0 0 99em;
        align-items: center;
        padding: 0.625em 0.75em;
        font-size: 1.25rem;
        font-weight: 600;
        color: #425475;
        }

        .plan .pricing small {
        color: #707a91;
        font-size: 0.75em;
        margin-left: 0.25em;
        }

        .plan .title {
        font-weight: 600;
        font-size: 1.25rem;
        color: #425675;
        }

        .plan .title + * {
        margin-top: 0.75rem;
        }

        .plan .info + * {
        margin-top: 1rem;
        }

        .plan .features {
        display: flex;
        flex-direction: column;
        }

        .plan .features li {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        }

        .plan .features li + * {
        margin-top: 0.75rem;
        }

        .plan .features .icon {
        background-color: #1FCAC5;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        }

        .plan .features .icon svg {
        width: 14px;
        height: 14px;
        }

        .plan .features + * {
        margin-top: 1.25rem;
        }

        .plan .action {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: end;
        }

        .plan .button {
        background-color: #6558d3;
        border-radius: 6px;
        color: #fff;
        font-weight: 500;
        font-size: 1.125rem;
        text-align: center;
        border: 0;
        outline: 0;
        width: 100%;
        padding: 0.625em 0.75em;
        text-decoration: none;
        }

        .plan .button:hover, .plan .button:focus {
        background-color: #4133B7;
        }

        .plan .active {
        background-color: #c0c0c0;
        border-radius: 6px;
        color: #fff;
        font-weight: 500;
        font-size: 1.125rem;
        text-align: center;
        border: 0;
        outline: 0;
        width: 100%;
        padding: 0.625em 0.75em;
        text-decoration: none;
        }

        .plan .unsubscribe {
        background-color:rgb(255, 0, 0);
        border-radius: 6px;
        color: #fff;
        font-weight: 500;
        font-size: 1.125rem;
        text-align: center;
        border: 0;
        outline: 0;
        width: 100%;
        padding: 0.625em 0.75em;
        text-decoration: none;
        }

        .plan .active:hover, .plan .active:focus {
        background-color: #c0c0c0;
        }
    </style>
</head>
<body>

    <header>
        <h1>Dashboard Pengguna</h1>
    </header>
    
    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="1_HalamanBeranda.php"><i class="fas fa-home"></i> Home </a></li>
                <li><a href="2_DashboardPengguna.php"><i class="fa-solid fa-user"></i>Dashboard</a></li>
                <li><a href="3_ProfilPengguna.php"><i class="fa-solid fa-gear"></i>Profil</a></li>
                <li><a href="riwayat_ton.php"><i class="fa-solid fa-film"></i>Riwayat</a></li>
                <li><a href="playlist.php"><i class="fa-solid fa-play"></i>Playlist</a></li>
                <li><a href="favorite.php"><i class="fa-solid fa-star"></i>Favorit</a></li>
                <li><a href="langganan.php"><i class="fa-solid fa-dollar-sign"></i>Langganan</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Card Gratis -->
        <div class="plan">
            <div class="inner">
                <span class="pricing">
                    <span>
                        <small>Free</small>
                    </span>
                </span>
                <p class="title">Gratis</p>
                <p class="info">Ini akan memberi anda tontonan biasa saja</p>
                <ul class="features">
                    <li>
                        <span class="icon">
                            <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none"></path>
                                <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                            </svg>
                        </span>
                        <span>Akses film terbatas</span>
                    </li>
                </ul>
                <div class="action">
                <button class="<?= !$is_subscribed ? 'active' : 'unsubscribe'; ?>" <?= !$is_subscribed ? 'disabled' : ''; ?> onclick="<?= $is_subscribed ? 'unsubscribe()' : '';?>">
                    <?= !$is_subscribed ? 'Active' : 'Cancel Subscribtion';?>
                </button> <!-- Tombol selalu menunjukkan status Active -->
                </div>
            </div>
        </div>

        <!-- Card Premium -->
        <div class="plan">
            <div class="inner">
                <span class="pricing">
                    <span>
                        25.000 <small>/ bulan</small>
                    </span>
                </span>
                <p class="title">Premium</p>
                <p class="info">Ini akan memberi anda tontonan ekslusif yang hanya bisa diakses pengguna premium</p>
                <ul class="features">
                    <li>
                        <span class="icon">
                            <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none"></path>
                                <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                            </svg>
                        </span>
                        <span>Akses semua film tanpa batasan</span>
                    </li>
                </ul>
                <div class="action">
                    <button class="<?= $is_subscribed ? 'active': 'button'; ?>" <?= $is_subscribed ? 'disabled' : ''; ?> onclick="<?= !$is_subscribed ? 'subscribe()' : ''; ?>">
                        <?= $is_subscribed ? 'Active' : 'Choose Plan'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

</body>
<script>
    function subscribe() {
        window.location = 'subs.php';
    }
    function unsubscribe() {
        if (confirm('Do you want to cancel your subscription?')) {
            fetch('subscribe.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'unsubscribe' })
            }).then(response => response.json())
              .then(data => {
                  if (data.status === 'success') {
                      alert(data.message);
                      location.reload();
                  } else {
                      alert(data.message);
                  }
              });
        }
    }
</script>
</html>
