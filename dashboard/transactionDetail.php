<style>
    <?php include '../style/dashboard.css'; ?>
</style>

<?php
include __DIR__ . '/../condb.php'; // Adjust path to your condb.php

$IDtrans = $_GET['IDtrans'] ?? null; // Get IDtrans from URL, handle if missing

if ($IDtrans) {
    $sqlDetail = "SELECT IDdetail, IDtrans, IDProduct, ProductName, Qty, PricePerUnit, Subtotal, Vat, Total FROM transaction_detail WHERE IDtrans = '$IDtrans'";
    $resultDetail = $conn->query($sqlDetail);

    if ($resultDetail && $resultDetail->num_rows > 0) {
        $transactionDetails = [];
        $totalSubtotal = 0; // Initialize total without VAT
        $totalGrandTotal = 0; // Initialize total with VAT
        while ($rowDetail = $resultDetail->fetch_assoc()) {
            $transactionDetails[] = $rowDetail;
            $totalSubtotal += $rowDetail['Subtotal']; // Accumulate Subtotal
            $totalGrandTotal += $rowDetail['Total'];   // Accumulate Total
        }
?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Transaction Details</title>
        </head>

        <body>
            <div style="text-align: left;">
                <a href="adminDashboard.php">
                    <button style="background-color: #4caf50 ; color: white;">Back to Admin Dashboard</button>
                </a>
            </div>

            <h1>Transaction Details for IDtrans: <?php echo htmlspecialchars($IDtrans); ?></h1>
            <table>
                <thead>
                    <tr>
                        <th>ID Detail</th>
                        <th>ID Transaction</th>
                        <th>ID Product</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price Per Unit</th>
                        <th>Subtotal</th>
                        <th>Vat</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactionDetails as $detail): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($detail['IDdetail']); ?></td>
                        <td><?php echo htmlspecialchars($detail['IDtrans']); ?></td>
                        <td><?php echo htmlspecialchars($detail['IDProduct']); ?></td>
                        <td><?php echo htmlspecialchars($detail['ProductName']); ?></td>
                        <td><?php echo htmlspecialchars($detail['Qty']); ?></td>
                        <td><?php echo htmlspecialchars($detail['PricePerUnit']); ?></td>
                        <td><?php echo htmlspecialchars($detail['Subtotal']); ?></td>
                        <td><?php echo htmlspecialchars($detail['Vat']); ?></td>
                        <td><?php echo htmlspecialchars($detail['Total']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="summary" style="text-align: right;">
                <p style="font-size: 20px;"><strong>Total (without VAT):</strong> <?php echo htmlspecialchars(number_format($totalSubtotal, 2)); ?></p>
                <p style="font-size: 24px; color: #4caf50 ; font-weight: bold;"><strong>Total (with VAT):</strong> <?php echo htmlspecialchars(number_format($totalGrandTotal, 2)); ?></p>
            </div>

        </body>

        </html>
<?php
    } else {
        echo "No details found for Transaction ID: " . htmlspecialchars($IDtrans);
    }
} else {
    echo "Transaction ID is missing.";
}

$conn->close();
?>
