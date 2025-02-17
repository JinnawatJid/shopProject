<?php
// updateOrderStatus.php

// Include the database connection
include __DIR__ . '/../condb.php';

// Define the error log file path
$errorLogFile = __DIR__ . '/../error/updateOrderStatusLog.txt';

// Get the input data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    $errorMessage = 'Invalid input data';
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $errorMessage]);
    exit;
}

$orderId = $data['orderId'] ?? null;
$status = $data['status'] ?? null;

if (!$orderId || !$status) {
    $errorMessage = 'Missing orderId or status';
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $errorMessage]);
    exit;
}

// Debug: Log the received data
$debugMessage = "Received orderId: $orderId, status: $status";
file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - $debugMessage\n", FILE_APPEND);

// Update the order status and ApproveTimestamp (using NOW())
$sql = "UPDATE transaction_header SET status = ?, ApproveTimestamp = NOW() WHERE IDtrans = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $error = $conn->error;
    $errorMessage = "Failed to prepare update statement: $error";
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $errorMessage]);
    exit;
}

$stmt->bind_param('si', $status, $orderId);

if ($stmt->execute()) {
    // Check if the status was actually updated
    if ($stmt->affected_rows > 0) {
        // If order status is 'Approve', insert the pickup data
        if ($status === 'Approve') {
            // Insert into pickup_list table.
            // The pickup_date is calculated as one day after the order's ApproveTimestamp.
            $insert_sql = "INSERT INTO pickup_list (pickup_date, IDProduct, ProductName, Qty, Status)
                           SELECT DATE_ADD(th.ApproveTimestamp, INTERVAL 1 DAY) AS pickup_date,
                                  td.IDProduct,
                                  td.ProductName,
                                  td.Qty,
                                  'Approve' AS Status
                           FROM transaction_detail td
                           JOIN transaction_header th ON td.IDtrans = th.IDtrans
                           WHERE td.IDtrans = ?";
            $insert_stmt = $conn->prepare($insert_sql);
            if (!$insert_stmt) {
                $error = $conn->error;
                $errorMessage = "Failed to prepare insert statement: $error";
                file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
                echo json_encode(['success' => false, 'error' => $errorMessage]);
                exit;
            }
            $insert_stmt->bind_param("i", $orderId);
            if (!$insert_stmt->execute()) {
                $error = $insert_stmt->error;
                $errorMessage = "Failed to execute insert statement: $error";
                file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
                // Optionally, you can decide whether to fail the whole request here.
            }
            $insert_stmt->close();
        }
        echo json_encode(['success' => true]);
    } else {
        $errorMessage = "No rows affected. Check if orderId exists or if the status is already set.";
        file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => $errorMessage]);
    }
} else {
    $error = $stmt->error;
    $errorMessage = "Failed to execute update statement: $error";
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $errorMessage]);
}

$stmt->close();
$conn->close();
?>
