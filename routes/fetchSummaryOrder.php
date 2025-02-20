<?php

// Include the database connection
include __DIR__ . '/../condb.php';

$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

// SQL query to fetch Sum Order
$sql = "SELECT COUNT(*) AS total FROM transaction_header";

if ($startDate && $endDate) {
    // Modify SQL to filter by date range
    $sql = "SELECT COUNT(*) AS total
            FROM transaction_header
            WHERE PendingTimestamp >= '$startDate 00:00:00' AND PendingTimestamp <= '$endDate 23:59:59'";
}

$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $sumOrder = $row ? $row['total'] : 0;
    $responseData = ["total" => $sumOrder]; // Data to be encoded as JSON

    header('Content-Type: application/json');
    echo json_encode($responseData);
} else {
    $errorMsg = "Database error: " . mysqli_error($condb);
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["error" => $errorMsg]);
}

?>
