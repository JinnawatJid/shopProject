<?php
require_once 'vendor/autoload.php';

use Spipu\Html2Pdf\Html2Pdf;

// Fetch data from GET parameters
$selectedItems = $_GET['items'] ?? [];
$quantities = $_GET['quantity'] ?? [];
$selectedCustomers = $_GET['selected_customers'] ?? [];
$customerNames = $_GET['customer_names'] ?? [];
$totalCost = 0;

// Simulate fetching product data
$products = [
    'P001' => ['name' => 'Apple', 'price' => 2.5],
    'P002' => ['name' => 'Banana', 'price' => 1.2],
    'P003' => ['name' => 'Orange', 'price' => 3.0],
    // Add more products here as needed
];

// Start output buffering to capture HTML content
ob_start();

?>

<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        margin: 0;
        padding: 0;
        color: #333;
    }

    .receipt-container {
        width: 80%;
        margin: auto;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .receipt-header {
        text-align: center;
        margin-bottom: 20px;
    }

    .receipt-header h1 {
        font-size: 24px;
        margin: 0;
    }

    .receipt-header p {
        font-size: 14px;
        color: #777;
        margin: 0;
    }

    .customer-info {
        margin-bottom: 20px;
    }

    .customer-info strong {
        display: block;
        margin-bottom: 5px;
    }

    .table-container {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    table {
        border-collapse: collapse;
        width: auto;
        margin: 0 auto;
    }

    table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
        padding: 10px;
        text-align: center;
    }

    th {
        background-color: #f4f4f4;
        text-align: center;
    }

    .totals {
        text-align: right;
    }

    .totals h2 {
        font-size: 14px;
        margin: 5px 0;
    }

    .footer {
        text-align: center;
        font-size: 12px;
        color: #777;
        border-top: 1px solid #ddd;
        padding-top: 10px;
    }
</style>

<div class="receipt-container">
    <div class="receipt-header">
        <h1>Receipt</h1>
        <p>Thank you for your purchase!</p>
    </div>

    <?php if (!empty($selectedCustomers)): ?>
        <div class="customer-info">
            <strong>Customer Information:</strong>
            <ul>
                <?php foreach ($selectedCustomers as $customerID): ?>
                    <li>
                        <strong>Customer ID:</strong> <?php echo htmlspecialchars($customerID); ?>,<br>
                        <strong>Name:</strong> <?php echo htmlspecialchars($customerNames[$customerID] ?? 'Unknown'); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price per Unit</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($selectedItems as $itemId): ?>
                    <?php
                    $quantity = intval($quantities[$itemId] ?? 0);
                    if (isset($products[$itemId]) && $quantity > 0) {
                        $product = $products[$itemId];
                        $subtotal = $quantity * $product['price'];
                        $totalCost += $subtotal;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo $quantity; ?></td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                    <?php } ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    // Calculate VAT and total cost with VAT
    $vat = $totalCost * 0.07; // 7% VAT
    $totalWithVAT = $totalCost + $vat;
    ?>

    <div class="totals">
        <h2>Total Cost: $<?php echo number_format($totalCost, 2); ?></h2>
        <h2>VAT (7%): $<?php echo number_format($vat, 2); ?></h2>
        <h2>Total Cost with VAT: $<?php echo number_format($totalWithVAT, 2); ?></h2>
    </div>

    <div class="footer">
        <p>Generated on <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>Visit us again!</p>
    </div>
</div>

<?php
// Get the HTML content
$htmlContent = ob_get_clean();

try {
    // Create an HTML2PDF object
    $html2pdf = new Html2Pdf('P', 'A4', 'en');
    $html2pdf->writeHTML($htmlContent);
    $html2pdf->output('receipt.pdf'); // Serve the PDF to the browser
} catch (Exception $e) {
    echo 'Error generating PDF: ' . $e->getMessage();
}
