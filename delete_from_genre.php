<?php
include "koneksi.php";
session_start();

if (isset($_GET['id'])) {
    $id_film = $_GET['id'];
    $genre_id = $_SESSION['genre'];
    $query_film_genre = "DELETE FROM film_genres WHERE film_id = '$id_film' AND genre_id = '$genre_id'";
    $hasil_2 = mysqli_query($koneksi, $query_film_genre);

    if ($hasil || $hasil_2) {
        header("Location: manage_genre.php");
        exit();
    } else {
        echo "Gagal menghapus film dari genre.";
    }
}
?>