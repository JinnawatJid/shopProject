<?php
include 'condb.php';
require_once 'vendor/autoload.php';
?>

<style>
    <?php include 'style/receipt.css'; ?>
</style>

<?php
$success = $_GET['success'] ?? 'false'; // Get success parameter from the URL
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $selectedItems = $_GET['items'] ?? [];
    $quantities = $_GET['quantity'] ?? [];
    $selectedCustomers = $_GET['selected_customers'] ?? [];
    $customerNames = $_GET['customer_names'] ?? [];
    $totalCost = 0;

    // Simulate fetching product data for the selected items
    $products = [
        'P001' => ['name' => 'Apple', 'price' => 2.5],
        'P002' => ['name' => 'Banana', 'price' => 1.2],
        'P003' => ['name' => 'Orange', 'price' => 3.0],
        // Add more products here as needed
    ];

    echo "<h1>Receipt</h1>";

    // Display selected customers
    if (!empty($selectedCustomers)) {
        echo "<h2>Customers</h2>";
        echo "<ul>";
        foreach ($selectedCustomers as $customerID) {
            $customerName = htmlspecialchars($customerNames[$customerID] ?? 'Unknown');
            echo "<li><strong>Customer ID:</strong> {$customerID}, <strong>Name:</strong> {$customerName}</li>";
        }
        echo "</ul>";
    }

    // Initialize an array to store the product names for the hidden field
    $productNames = [];

    // Display the table of purchased items
    echo "<h2>Purchased Items</h2>";
    echo "<table border='1' cellpadding='10' cellspacing='0'>";
    echo "<tr><th>Product Name</th><th>Quantity</th><th>Price per Unit</th><th>Subtotal</th></tr>";

    foreach ($selectedItems as $itemId) {
        $quantity = intval($quantities[$itemId] ?? 0);
        if (isset($products[$itemId]) && $quantity > 0) {
            $product = $products[$itemId];
            $subtotal = $quantity * $product['price'];
            $totalCost += $subtotal;

            // Collect the product name for the hidden form field
            $productNames[] = $product['name'];

            echo "<tr>";
            echo "<td>" . htmlspecialchars($product['name']) . "</td>";
            echo "<td>{$quantity}</td>";
            echo "<td>\${$product['price']}</td>";
            echo "<td>\${$subtotal}</td>";
            echo "</tr>";
        }
    }

    echo "</table>";

    // Calculate VAT and total cost with VAT
    $vat = $totalCost * 0.07; // 7% VAT
    $totalWithVAT = $totalCost + $vat;

    // Display total cost, VAT, and total with VAT
    echo "<h2>Total Cost: \$" . number_format($totalCost, 2) . "</h2>";
    echo "<h2>VAT (7%): \$" . number_format($vat, 2) . "</h2>";
    echo "<h2>Total Cost with VAT: \$" . number_format($totalWithVAT, 2) . "</h2>";
}
?>

<!-- Submit button -->
<form action="submitTransaction.php" method="POST" style="text-align: center; margin-top: 20px;">
    <!-- Header data: Customer details -->
    <input type="hidden" name="selected_customers" value="<?php echo htmlspecialchars(json_encode($selectedCustomers)); ?>">
    <input type="hidden" name="customer_names" value='<?php echo json_encode($customerNames); ?>'>

    <!-- Detail data: Items, quantities, product names -->
    <input type="hidden" name="items" value="<?php echo htmlspecialchars(json_encode($selectedItems)); ?>">
    <input type="hidden" name="quantities" value="<?php echo htmlspecialchars(json_encode($quantities)); ?>">
    <input type="hidden" name="product_names" value="<?php echo htmlspecialchars(json_encode($productNames)); ?>">

    <!-- Calculations for VAT and total -->
    <input type="hidden" name="vat" value="<?php echo number_format($vat, 2); ?>">
    <input type="hidden" name="total_with_vat" value="<?php echo number_format($totalWithVAT, 2); ?>">

    <button type="submit" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
        Submit to Database
    </button>
</form>

<!-- Export to PDF button -->
<p style="text-align: center; margin-top: 20px;">
    <a href="generate_po.php?<?php echo http_build_query([
                                    'items' => $selectedItems,
                                    'quantity' => $quantities,
                                    'selected_customers' => $selectedCustomers,
                                    'customer_names' => $customerNames
                                ]); ?>" target="_blank"
        style="display: inline-block; background-color: #FF5722; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; transition: 0.3s;">
        Export PO
    </a>
</p>

<!-- Back to Catalog button -->
<p style="text-align: center; margin-top: 20px;">
    <a href="catalog.php" style="display: inline-block; background-color: purple; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; transition: 0.3s;">
        Back to Catalog
    </a>
</p>

<!-- Modal -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Transaction Submitted Successfully!</h2>
        <p style="text-align: center;">The transaction has been processed and stock updated.</p>
        <p style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="display: inline-block; background-color: purple; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; transition: 0.3s;">
                Go to index
            </a>
        </p>
    </div>
</div>

<script>
    // Show the modal if success is true
    function showModal() {
        document.getElementById("successModal").style.display = "block";
    }

    // Close the modal
    function closeModal() {
        document.getElementById("successModal").style.display = "none";
    }

    // Display the modal if the success flag is true
    <?php if ($success === 'true') { ?>
        showModal();
    <?php } ?>
</script>
