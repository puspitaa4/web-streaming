<?php
session_start();
require '../koneksi.php';

// Periksa apakah user_id dan id film tersedia
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    die("User ID or Film ID is missing!");
}else{
    $user_id = $_SESSION['user_id'];
    $id_film = $_GET['id'];

    // Periksa apakah film sudah ada di favorit
    $queryCheck = "SELECT COUNT(*) AS count FROM favorites WHERE user_id = ? AND film_id = ?";
    $stmtCheck = $koneksi->prepare($queryCheck);
    $stmtCheck->bind_param("ii", $user_id, $id_film);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $rowCheck = $resultCheck->fetch_assoc();

    if ($rowCheck['count'] > 0) {
        echo "Film is already in favorites!";
        header("Location: 5_KontenDetail.php");
        exit();
    }

    // Tambahkan film ke favorit
    $queryFavorite = "INSERT INTO favorites (user_id, film_id, added_at) VALUES (?, ?, CURRENT_TIMESTAMP)";
    $stmt = $koneksi->prepare($queryFavorite);
    $stmt->bind_param("ii", $user_id, $id_film);

    if ($stmt->execute()) {
        header("Location: 5_KontenDetail.php");
        exit();
    } else {
        // Debug error jika query gagal
        echo "Failed to add film to favorites: " . $koneksi->error;
    }
}
?>
