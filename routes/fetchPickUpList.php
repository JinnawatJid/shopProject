<?php
include __DIR__ . '/../condb.php';

// Set up error logging to a file
$errorLogFile = __DIR__ . '/../error/pickupListLog.txt';
ini_set("log_errors", 1);
ini_set("error_log", $errorLogFile);

// Use DATE_ADD to add 1 day to the approval date
$query = "SELECT 
            DATE_ADD(th.ApproveTimestamp, INTERVAL 1 DAY) AS pickup_date, 
            td.IDProduct, 
            td.ProductName, 
            SUM(td.Qty) AS Qty,
            'Approve' AS Status
          FROM transaction_detail td
          JOIN transaction_header th ON td.IDtrans = th.IDtrans
          WHERE th.status = 'Approve'
          GROUP BY td.IDProduct, td.ProductName";

// Execute the query
$result = mysqli_query($conn, $query);
if (!$result) {
    error_log("Error in fetchPickupList query: " . mysqli_error($conn) . "\n", 3, $errorLogFile);
    die("Query error.");
}

$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
