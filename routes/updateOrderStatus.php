<?php
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

// Debugging: Log the received data
$debugMessage = "Received orderId: $orderId, status: $status";
file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - $debugMessage\n", FILE_APPEND);

// Update the order status and ApprovedTimestamp
$sql = "UPDATE transaction_header SET status = ?, ApproveTimestamp = NOW() WHERE IDtrans = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $error = $conn->error;
    $errorMessage = "Failed to prepare statement: $error";
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $errorMessage]);
    exit;
}

$stmt->bind_param('si', $status, $orderId);

if ($stmt->execute()) {
    // Check if the status was actually updated
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        $errorMessage = "No rows affected. Check if orderId exists or status is valid.";
        file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => $errorMessage]);
    }
} else {
    $error = $stmt->error;
    $errorMessage = "Failed to execute statement: $error";
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $errorMessage]);
}

// Close the database connection
$stmt->close();
$conn->close();
?>