<?php
require '../koneksi.php'; // Pastikan file koneksi sudah ada

if (isset($_GET['q'])) {
    $query = $_GET['q']; // Ambil input pencarian

    // Pastikan input pencarian tidak kosong
    if (!empty($query)) {
        // Query pencarian: mencari judul film yang cocok (case-insensitive)
        $sql = "SELECT film_id, title, release_schedule, poster_url, studio
                FROM films 
                WHERE title LIKE ? OR studio LIKE ?
                ORDER BY release_schedule DESC";
        
        $stmt = $koneksi->prepare($sql);
        $searchTerm = "%" . $query . "%";
        $searchTerm2 = "%" . $query . "%";
        $stmt->bind_param("ss", $searchTerm, $searchTerm2);
        $stmt->execute();
        $result = $stmt->get_result();

        // Jika ada hasil, kirimkan sebagai JSON
        $films = [];
        while ($row = $result->fetch_assoc()) {
            $films[] = $row;
        }
        echo json_encode($films);
    } else {
        echo json_encode([]);
    }
}
?>