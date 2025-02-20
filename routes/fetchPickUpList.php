<?php
include __DIR__ . '/../condb.php';

$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

// SQL query to fetch data from pickup_list table and aggregate by date and product
$query = "SELECT
            DATE(pickup_date) AS pickup_date, /* Get only the date part */
            IDProduct,
            ProductName,
            SUM(Qty) AS Qty, /* Sum the quantities */
            Status
          FROM pickup_list";

if ($startDate && $endDate) {
    $query .= " WHERE pickup_date >= '$startDate 00:00:00' AND pickup_date <= '$endDate 23:59:59'"; // Date Range WHERE clause on pickup_date
}

$query .= " GROUP BY DATE(pickup_date), IDProduct, ProductName, Status ORDER BY pickup_date, IDProduct"; /* Group by date, product, and status */

// Execute the query
$result = mysqli_query($conn, $query);

if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Query failed: " . mysqli_error($conn)]); // Include SQL error in JSON response
    http_response_code(500); // Set HTTP status code to 500 for error
    die("Query error.");
}

$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);

mysqli_close($conn);
