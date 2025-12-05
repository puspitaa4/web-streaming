<?php
// Mulai session
session_start();

// Periksa apakah pengguna sudah login
if (isset($_SESSION['user_id'])) {
    // Sambungkan ke database
    require_once 'koneksi.php';

    // Ambil user_id dari session
    $user_id = $_SESSION['user_id'];

    // Update status pengguna menjadi 'inactive'
    $query = "UPDATE users SET status = 'inactive' WHERE user_id = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $activity_sql = "INSERT INTO activity_logs (user_id, activity_type) VALUES (?, 'Melakukan logout.')";
    $activity_stmt = $koneksi->prepare($activity_sql);
    $activity_stmt->bind_param("i", $user_id);
    $activity_stmt->execute();

    // Tutup koneksi database
    $koneksi->close();
}

// Hapus session
session_unset();
session_destroy();

// Arahkan ke halaman login
header('Location: ./login/login.php');
exit();
?>