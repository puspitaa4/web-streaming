<?php
include "koneksi.php";
session_start();


if (isset($_GET['id'])) {
    $id_film = $_GET['id'];

    $query = "DELETE FROM films WHERE film_id = '$id_film'";
    $hasil = mysqli_query($koneksi, $query);

    if ($hasil) {
        header("Location: manage_films.php");
        exit();
    } else {
        echo "Gagal menghapus film.";
    }
}
?>
