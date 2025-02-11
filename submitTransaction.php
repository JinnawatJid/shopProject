<?php
include 'condb.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define the log file path
$logFile = './error/submitTransaction.txt';

// Clear the log file at the start of each run
file_put_contents($logFile, '');

// Function to write logs to the log file
function write_log($message) {
    global $logFile;
    // Append the message to the log file with a timestamp
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Debug: Log script start
write_log("Script started.");

// Check if data is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log that we are processing a POST request
    write_log("Processing POST request.");

    // Decode JSON-encoded hidden inputs
    $selectedCustomers = json_decode($_POST['selected_customers'], true);
    $customerNames = json_decode($_POST['customer_names'], true); // Decoding into associative array

    $items = json_decode($_POST['items'], true);
    $quantities = json_decode($_POST['quantities'], true);
    $vat = floatval($_POST['vat']);
    $totalWithVAT = floatval($_POST['total_with_vat']);

    // Debug: Log the received data
    write_log("Selected Customers: " . print_r($selectedCustomers, true));
    write_log("Customer Names: " . print_r($customerNames, true));
    write_log("Items: " . print_r($items, true));
    write_log("Quantities: " . print_r($quantities, true));
    write_log("VAT: " . $vat);
    write_log("Total with VAT: " . $totalWithVAT);

    // Transaction date
    $transactionDate = date('Y-m-d H:i:s');

    // Fetch all products from fetchProduct.php
    ob_start(); // Start output buffering
    include './routes/fetchProduct.php'; // Include fetchProduct.php
    $productDataJSON = ob_get_clean(); // Get the JSON output from fetchProduct.php
    $products = json_decode($productDataJSON, true); // Decode JSON into an associative array

    if (!$products) {
        write_log("Error fetching product data.");
        die("Error fetching product data.");
    }

    // Debug: Log the fetched products
    write_log("Fetched Products: " . print_r($products, true));

    // Convert products array to an associative array with product ID as the key
    $productsById = [];
    foreach ($products as $product) {
        $productsById[$product['IDProduct']] = $product;
    }

    // Debug: Log the products by ID
    write_log("Products by ID: " . print_r($productsById, true));

    // Start the transaction
    $conn->begin_transaction();

    try {
        // Debug: Log database connection status
        if ($conn->connect_error) {
            write_log("Database connection failed: " . $conn->connect_error);
            die("Database connection failed.");
        } else {
            write_log("Database connection successful.");
        }

        // Loop through selected customers and insert data for each one
        foreach ($selectedCustomers as $index => $customerID) {
            // Fetch the customer name using the customerID
            $customerName = $customerNames[$customerID] ?? 'Unknown';  // Default to 'Unknown' if no name found

            // Debug: Log the customer ID and name
            write_log("Processing customer ID: $customerID, Name: $customerName");

            try {
                // Insert into transaction header table
                $insertTransactionHeader = $conn->prepare("
                    INSERT INTO transaction_header (
                        IDCust, CustName, PendingTimestamp
                    ) VALUES (?, ?, ?)
                ");

                if (!$insertTransactionHeader) {
                    throw new Exception("Error preparing header statement: " . $conn->error);
                }

                // Bind the customer ID, customer name, and transaction date
                $insertTransactionHeader->bind_param("sss", $customerID, $customerName, $transactionDate);

                // Execute the query
                $insertTransactionHeader->execute();

                if ($insertTransactionHeader->affected_rows === 0) {
                    throw new Exception("Failed to insert into transaction header table.");
                }

                $transactionHeaderID = $conn->insert_id; // Get the inserted transaction header ID

                // Debug: Log the transaction header ID
                write_log("Inserted transaction header ID: $transactionHeaderID");
            } catch (Exception $e) {
                write_log("Error in transaction header insertion: " . $e->getMessage());
                throw $e; // Re-throw the exception to stop the script
            }

            // Insert transaction details and update stock for each item
            foreach ($items as $itemId) {
                $quantity = intval($quantities[$itemId] ?? 0);

                if ($quantity > 0 && isset($productsById[$itemId])) {
                    $product = $productsById[$itemId];
                    $productName = $product['ProductName'];  // Correctly retrieve the product name
                    $pricePerUnit = $product['PricePerUnit'];

                    // Debug: Log the product details
                    write_log("Processing product ID: $itemId, Name: $productName, Quantity: $quantity, Price: $pricePerUnit");

                    // Check stock availability
                    if ($product['StockQty'] < $quantity) {
                        throw new Exception("Insufficient stock for product ID: $itemId");
                    }

                    // Calculate subtotal
                    $subtotal = $quantity * $pricePerUnit;

                    // Debug: Log the subtotal
                    write_log("Subtotal for product ID: $itemId is $subtotal");

                    // Insert into transaction detail table
                    $insertTransactionDetail = $conn->prepare("
                        INSERT INTO transaction_detail (
                            IDtrans, IDProduct, ProductName, Qty, PricePerUnit, Subtotal, Vat, Total
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    if (!$insertTransactionDetail) {
                        throw new Exception("Error preparing detail statement: " . $conn->error);
                    }

                    // Calculate the total price for each item
                    $total = $subtotal + ($vat / count($items)); // Split VAT equally among items

                    // Debug: Log the total price
                    write_log("Total price for product ID: $itemId is $total");

                    // Bind parameters correctly (matching the number of placeholders and their types)
                    $insertTransactionDetail->bind_param(
                        "isdsdddd",  // IDtrans (int), IDProduct (string), ProductName (string), Qty (int), PricePerUnit (double), Subtotal (double), Vat (double), Total (double)
                        $transactionHeaderID,
                        $itemId,
                        $productName,
                        $quantity,
                        $pricePerUnit,
                        $subtotal,
                        $vat,
                        $total
                    );

                    // Execute the statement to insert the detail record
                    $insertTransactionDetail->execute();

                    if ($insertTransactionDetail->affected_rows === 0) {
                        throw new Exception("Failed to insert into transaction detail table.");
                    }

                    // Update stock quantity
                    $updateStock = $conn->prepare("
                        UPDATE stock SET stockQty = stockQty - ? WHERE IDProduct = ? 
                    ");
                    $updateStock->bind_param("is", $quantity, $itemId);
                    $updateStock->execute();

                    // Ensure stock was updated successfully
                    if ($conn->affected_rows <= 0) {
                        throw new Exception("Failed to update stock for product ID: $itemId");
                    }
                } else {
                    throw new Exception("Invalid product ID: $itemId or insufficient quantity.");
                }
            }
        }

        // Commit transaction
        $conn->commit();
        // Debug: Log successful transaction
        write_log("Transaction committed successfully.");
        // Redirect to receipt.php with success=true
        write_log("Redirecting to receipt.php with success=true");
        header("Location: receipt.php?success=true");
        exit();
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        // Log error message for debugging
        write_log("Transaction Error: " . $e->getMessage());
        // Redirect to receipt.php with success=false and error message
        write_log("Redirecting to receipt.php with success=false");
        header("Location: receipt.php?success=false&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Debug: Log that the request method is not POST
    write_log("Invalid request method. Expected POST.");
    echo "Invalid request method.";
}