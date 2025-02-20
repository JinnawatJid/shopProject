<?php
// Include the database connection
include __DIR__ . '/../condb.php';

$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

// SQL query to fetch transaction headers
$sql = "SELECT IDtrans, IDCust, CustName, status, PendingTimestamp, ApproveTimestamp FROM transaction_header";

if ($startDate && $endDate) {
    $sql .= " WHERE PendingTimestamp >= '$startDate 00:00:00' AND PendingTimestamp <= '$endDate 23:59:59'"; // **Date Range WHERE clause**
}

$result = $conn->query($sql);

if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Query failed"]);
    http_response_code(500); // Set HTTP status code to 500 for error
} elseif ($result->num_rows > 0) {
    // Fetch all rows as an associative array
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($transactions);
} else {
    // No transactions found
    header('Content-Type: application/json');
    echo json_encode([]);
}


// Close the database connection (consider if you want to close it here or manage connection globally)
$conn->close();
