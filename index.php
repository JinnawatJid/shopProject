<?php

// Include the database connection
include 'condb.php';

// Step 1: SQL query to fetch customers
$sql = "SELECT IDCust, CustName, Tel FROM customer"; // Replace with your actual table name
$result = $conn->query($sql);  // Store the result of the query in the $result variable

// Set the number of records per page
$recordsPerPage = 5;

// Get the total number of records
$totalRecords = $result->num_rows;

// Calculate the number of pages
$numPages = ceil($totalRecords / $recordsPerPage);

// Get the current page number
if (isset($_GET['page'])) {
    $currentPage = $_GET['page'];
} else {
    $currentPage = 1;
}

// Calculate the starting record number
$startRecord = ($currentPage - 1) * $recordsPerPage;

// Add the LIMIT clause to the SQL query
$sql .= " LIMIT $startRecord, $recordsPerPage";

// Re-run the query with the LIMIT clause
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Customer</title>

    <style>
        <?php include 'style/index.css'; ?>
    </style>

</head>

<body>
    <div style="text-align: right; margin-bottom: 20px;">
        <a href="./dashboard/adminDashboard.php" style="text-decoration: none;">
            <button style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
                Go to Admin Dashboard
            </button>
        </a>
    </div>
    <h1 style="padding-top: 20px;">Select a Customer</h1>
    <form method="GET" action="catalog.php">
        <div class="container">
            <?php
            // Step 2: Check if there are any customers
            if ($result->num_rows > 0) {
                // Loop through the result
                while ($row = mysqli_fetch_array($result)) {
                    $customerID = $row["IDCust"];
                    $customerName = $row["CustName"];
                    $customerTel = $row["Tel"];
            ?>
                    <div class="customer-box">
                        <!-- Customer Image -->
                        <img src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_640.png" alt="Customer Image">
                        <!-- Customer Details -->
                        <h3><?php echo $customerID; ?></h3>
                        <p><?php echo $customerName; ?>, Tel: <?php echo $customerTel; ?></p>
                        <!-- Checkbox -->
                        <label>
                            <input type="checkbox" name="selected_customers[]" value="<?php echo $customerID; ?>"
                                data-name="<?php echo urlencode($customerName); ?>"
                                onclick="updateSelectedCustomers(this, '<?php echo $customerID; ?>', '<?php echo $customerName; ?>')"> Select this Customer
                        </label>
                        <!-- Hidden input for customer name, initially not included -->
                    </div>
            <?php
                }
            } else {
                echo "<p>No customers found.</p>";
            }
            ?>
        </div>
        <div class="button-container">
            <button type="submit">Proceed to Order</button>
        </div>
        <div class="page-selector">
            <?php
            if ($currentPage > 1) {
                echo "<a href='?page=" . ($currentPage - 1) . "'>Previous Page</a>";
            }

            for ($i = 1; $i <= $numPages; $i++) {
                if ($i == $currentPage) {
                    echo "<a href='?page=$i' class='active'>$i</a>";
                } else {
                    echo "<a href='?page=$i'>$i</a>";
                }
            }

            if ($currentPage < $numPages) {
                echo "<a href='?page=" . ($currentPage + 1) . "'>Next Page</a>";
            }
            ?>
        </div>
    </form>

    <script>
        // Store the selected customer names
        let selectedCustomers = {};

        // Function to dynamically add customer names to the form
        function updateSelectedCustomers(checkbox, customerID, customerName) {
            let form = document.querySelector('form');

            if (checkbox.checked) {
                // Create a new hidden input for the customer name
                let hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `customer_names[${customerID}]`;
                hiddenInput.value = customerName;
                form.appendChild(hiddenInput); // Append to form
            } else {
                // Remove the hidden input if checkbox is unchecked
                let hiddenInput = form.querySelector(`input[name="customer_names[${customerID}]"]`);
                if (hiddenInput) {
                    form.removeChild(hiddenInput);
                }
            }
        }
    </script>


</body>

</html>

<?php
// Close the database connection
$conn->close();
?>