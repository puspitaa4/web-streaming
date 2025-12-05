<?php
include "koneksi.php";
session_start();


if (isset($_GET['id'])) {
    $id_genre = $_GET['id'];

    $query = "DELETE FROM genres WHERE genre_id = '$id_genre'";
    $hasil = mysqli_query($koneksi, $query);

    $query_film_genre = "DELETE FROM film_genres WHERE genre_id = '$id_genre'";
    $hasil_2 = mysqli_query($koneksi, $query_film_genre);

    if ($hasil || $hasil_2) {
        header("Location: manage_genre.php");
        exit();
    } else {
        echo "Gagal menghapus genre.";
    }
}
?>