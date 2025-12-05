<?php
require_once '../koneksi.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login/login.php");
    exit();
}

$id = $_GET['id'];

if (!filter_var($id, FILTER_VALIDATE_INT)) {
    echo "Invalid user ID.";
    exit();
}

$query = "DELETE FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    header("Location: manage_user.php");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
