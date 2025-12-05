<?php
session_start();

if (isset($_GET['film_id'])) {
    // Simpan film_id ke dalam session
    $_SESSION['film_id'] = $_GET['film_id'];
    
    // Redirect ke halaman detail film
    header('Location: 5_KontenDetail.php');
    exit();
} else {
    echo "Film ID not found!";
}
?>