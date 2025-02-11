<?php
// Include the database connection
include __DIR__ . '/../condb.php';

// SQL query to fetch transaction details
$sql = "SELECT IDdetail, IDtrans, IDProduct, ProductName, Qty, PricePerUnit, Subtotal, Vat, Total FROM transaction_detail"; // Replace with your actual table name
$result = $conn->query($sql);  // Store the result of the query in the $result variable

// Check if there are any transaction details
if ($result->num_rows > 0) {
    // Fetch all rows as an associative array
    $transactionDetails = [];
    while ($row = $result->fetch_assoc()) {
        $transactionDetails[] = $row;
    }
    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($transactionDetails);
} else {
    // No transaction details found
    header('Content-Type: application/json');
    echo json_encode([]);
}

// Close the database connection
$conn->close();
?>