<?php
// Include the database connection
include __DIR__ . '/../condb.php';

// SQL query to fetch transaction headers
$sql = "SELECT IDtrans, IDCust, CustName, status, PendingTimestamp, ApproveTimestamp, SELECT COUNT(*) as totalOrders FROM transaction_header"; // Replace with your actual table name
$result = $conn->query($sql);  // Store the result of the query in the $result variable

// Check if there are any transactions
if ($result->num_rows > 0) {
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

// Close the database connection
$conn->close();
?>