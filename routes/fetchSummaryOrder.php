<?php
// Include the database connection
include __DIR__ . '/../condb.php';

// SQL query to fetch Sum Order
$sql = "SELECT COUNT(*) AS total FROM transaction_header";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// Check if the query returned a valid result
$sumOrder = $row ? $row['total'] : 0;
echo json_encode(["total" => $sumOrder]);

// Close the database connection
$conn->close();
?>
