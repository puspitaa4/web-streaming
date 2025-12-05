<?php
session_start();
include '../koneksi.php';

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'];

if ($action === 'subscribe') {
    // Periksa apakah pengguna sudah memiliki langganan
    $queryCheck = "SELECT * FROM subscriptions WHERE user_id = ? ORDER BY end_date DESC LIMIT 1";
    $stmtCheck = $koneksi->prepare($queryCheck);
    $stmtCheck->bind_param('i', $user_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $subscription = $resultCheck->fetch_assoc();

    if ($subscription) {
        if ($subscription['end_date'] > date('Y-m-d H:i:s')) {
            // Langganan masih aktif
            echo json_encode(['status' => 'error', 'message' => 'You already have an active subscription.']);
            exit;
        } else {
            // Langganan sudah expired, perbarui langganan
            $queryUpdate = "UPDATE subscriptions SET start_date = NOW(), end_date = DATE_ADD(NOW(), INTERVAL 30 DAY), status = 'active' WHERE subscription_id = ?";
            $stmtUpdate = $koneksi->prepare($queryUpdate);
            $stmtUpdate->bind_param('i', $subscription['subscription_id']);
            
            if ($stmtUpdate->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Subscription reactivated for 30 days!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to reactivate subscription.']);
            }
            exit;
        }
    } else {
        // Tambahkan langganan baru
        $queryInsert = "INSERT INTO subscriptions (user_id, start_date, end_date, status) VALUES (?, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'active')";
        $stmtInsert = $koneksi->prepare($queryInsert);
        $stmtInsert->bind_param('i', $user_id);

        if ($stmtInsert->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Subscription activated for 30 days!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to activate subscription.']);
        }
        exit;
    }
} elseif ($action === 'unsubscribe') {
    // Hentikan langganan
    $query = "UPDATE subscriptions SET end_date = NOW(), status = 'canceled' WHERE user_id = ? AND (end_date IS NULL OR end_date > NOW())";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Subscription canceled!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to cancel subscription.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
}
?>