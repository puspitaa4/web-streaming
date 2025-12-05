<?php
include "../koneksi.php";
session_start();

$user_id = $_SESSION['user_id'];
if (isset($_GET['id'])) {
    $id_film = $_GET['id'];

    $query = "DELETE FROM favorites WHERE film_id = '$id_film' AND user_id = $user_id";
    $hasil = mysqli_query($koneksi, $query);

    if ($hasil) {
        header("Location: 5_KontenDetail.php");
        exit();
    } else {
        echo "Gagal menghapus film.";
    }
}
?>
