<?php
include "../koneksi.php";
session_start();

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $query = "DELETE FROM users WHERE user_id = $user_id";
    $hasil = mysqli_query($koneksi, $query);

    if ($hasil) {
        header("Location: ../login/login.php");
        exit();
    } else {
        echo "Gagal menghapus akun.";
    }
}
?>
