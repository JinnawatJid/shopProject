<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
include __DIR__ . '/../condb.php';

$logFile = __DIR__ . '/../error/fetchBestSellerLog.txt'; // Path to log file

// Function to write to log file
function logMessage($message, $logFile)
{
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] " . $message . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

logMessage("--- Start fetchBestSeller.php ---", $logFile);

$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

logMessage("Received startDate: " . json_encode($startDate), $logFile);
logMessage("Received endDate: " . json_encode($endDate), $logFile);

function getBestSeller($conn, $logFile, $startDate = null, $endDate = null)
{ // Pass $conn, $logFile, and date parameters
    $sql = "SELECT
        p.ProductName,
        SUM(td.Qty) AS TotalSold
     FROM transaction_detail td
     JOIN stock p ON td.IDProduct = p.IDProduct
     JOIN transaction_header th ON td.IDtrans = th.IDtrans "; // **Added JOIN trans_header here**

    if ($startDate && $endDate) {
        $sql .= " WHERE th.PendingTimestamp >= '$startDate 00:00:00' AND th.PendingTimestamp <= '$endDate 23:59:59'";
    }

    $sql .= "
    GROUP BY p.ProductName
    ORDER BY TotalSold DESC
    LIMIT 3";

    logMessage("SQL Query: " . $sql, $logFile);

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        logMessage("SQL Query Error: " . mysqli_error($conn), $logFile);
        return null; // Or handle error as needed, e.g., throw exception
    }

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    logMessage("Best Seller Data: " . json_encode($data), $logFile);
    return $data;
}

$bestSellerData = getBestSeller($conn, $logFile, $startDate, $endDate); // Pass $logFile and date parameters to the function

if ($bestSellerData) {
    header('Content-Type: application/json');
    echo json_encode($bestSellerData);
    logMessage("JSON response sent successfully", $logFile);
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Failed to fetch best seller data"]);
    logMessage("JSON error response sent (Failed to fetch data)", $logFile);
}

logMessage("--- End fetchBestSeller.php ---", $logFile);
// $conn->close(); // Consider connection closing strategy
