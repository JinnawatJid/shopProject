<?php
// Include the database connection
include __DIR__ . '/../condb.php';

$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

$sql = "SELECT AVG(order_totals.total_price) AS avg_order_value
FROM (SELECT SUM(p.PricePerUnit * td.Qty) AS total_price
FROM transaction_detail td
JOIN stock p ON td.IDProduct = p.IDProduct
JOIN transaction_header th ON td.IDtrans = th.IDtrans"; // **JOIN transaction_header**

if ($startDate && $endDate) {
    $sql .= " WHERE th.PendingTimestamp >= '$startDate 00:00:00' AND th.PendingTimestamp <= '$endDate 23:59:59'"; // **Date Range WHERE clause**
}

$sql .= " GROUP BY td.IDtrans) AS order_totals";


$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(["error" => "Query failed"]);
    http_response_code(500); // Set HTTP status code to 500 for error
} else {
    $row = mysqli_fetch_assoc($result);
    $AOV = $row && !is_null($row['avg_order_value']) ? $row['avg_order_value'] : 0;
    echo json_encode(["total" => round($AOV, 2)]); // Rounded AOV
}
