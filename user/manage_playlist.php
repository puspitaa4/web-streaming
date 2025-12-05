<?php
require '../koneksi.php';
session_start();

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['film_id'])) {
        $film_id = intval($_POST['film_id']);

        if (isset($_POST['playlist_id'])) {
            // Tambahkan film ke playlist yang sudah ada
            $playlist_id = intval($_POST['playlist_id']);

            // Cek apakah sudah ada di playlist
            $check_query = "SELECT * FROM playlist_items WHERE playlist_id = ? AND film_id = ?";
            $stmt = $koneksi->prepare($check_query);
            $stmt->bind_param("ii", $playlist_id, $film_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                // Tambahkan ke playlist
                $insert_query = "INSERT INTO playlist_items (playlist_id, film_id) VALUES (?, ?)";
                $stmt = $koneksi->prepare($insert_query);
                $stmt->bind_param("ii", $playlist_id, $film_id);
                $stmt->execute();
                echo json_encode(['success' => true, 'message' => 'Film ditambahkan ke playlist']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Film sudah ada di playlist']);
            }
        } elseif (isset($_POST['new_playlist_name'])) {
            // Buat playlist baru dan tambahkan film
            $new_playlist_name = trim($_POST['new_playlist_name']);

            if (!empty($new_playlist_name)) {
                // Tambahkan playlist baru
                $insert_playlist_query = "INSERT INTO playlists (user_id, name) VALUES (?, ?)";
                $stmt = $koneksi->prepare($insert_playlist_query);
                $stmt->bind_param("is", $user_id, $new_playlist_name);
                $stmt->execute();

                // Dapatkan ID playlist yang baru dibuat
                $new_playlist_id = $koneksi->insert_id;

                // Tambahkan film ke playlist
                $insert_item_query = "INSERT INTO playlist_items (playlist_id, film_id) VALUES (?, ?)";
                $stmt = $koneksi->prepare($insert_item_query);
                $stmt->bind_param("ii", $new_playlist_id, $film_id);
                $stmt->execute();

                echo json_encode(['success' => true, 'message' => 'Playlist baru dibuat dan film ditambahkan']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Nama playlist tidak boleh kosong']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Film tidak ditemukan']);
    }
}
?>