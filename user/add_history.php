<?php
header('Content-Type: application/json');
require '../koneksi.php';
session_start();

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    $film_id = isset($_POST['film_id']) ? intval($_POST['film_id']) : 0;
    if ($film_id <= 0) {
        throw new Exception('Invalid film ID');
    }

    $user_id = $_SESSION['user_id'];

    // Cek apakah film sudah ada di watch_history
    $query_check = "SELECT * FROM watch_history WHERE user_id = ? AND film_id = ?";
    $stmt_check = $koneksi->prepare($query_check);
    $stmt_check->bind_param("ii", $user_id, $film_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Update last_watched jika sudah ada
        $query_update = "UPDATE watch_history SET last_watched = NOW() WHERE user_id = ? AND film_id = ?";
        $stmt_update = $koneksi->prepare($query_update);
        $stmt_update->bind_param("ii", $user_id, $film_id);
        $stmt_update->execute();
        echo json_encode(['status' => 'success', 'message' => 'History updated']);
    } else {
        // Tambahkan ke watch_history jika belum ada
        $query_insert = "INSERT INTO watch_history (user_id, film_id, last_watched) VALUES (?, ?, NOW())";
        $stmt_insert = $koneksi->prepare($query_insert);
        $stmt_insert->bind_param("ii", $user_id, $film_id);
        $stmt_insert->execute();
        echo json_encode(['status' => 'success', 'message' => 'History added']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
