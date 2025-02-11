<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items for Sale</title>

    <style>
        <?php include 'style/catalog.css'; ?>
    </style>

    <script>
        fetch("./routes/fetchProduct.php")
            .then((response) => response.json())
            .then((data) => {
                const container = document.querySelector(".item-container");

                data.forEach((product) => {
                    const itemBox = `
    <div class="item-box">
        <img src="${product.ImageURL}" alt="${product.ProductName}">
        <h3>${product.ProductName}</h3>
        <p>${product.ProductDesc}</p>
        <p><strong class="price">Price: $${product.PricePerUnit}</strong></p>
        <label>
            Quantity:
            <input 
                type="number" 
                name="quantity[${product.IDProduct}]" 
                value="1" 
                min="1" 
                max="${product.StockQty}" 
                ${product.StockQty === 0 ? 'disabled' : ''}>
        </label>
        <div class="stock-error" style="color: red; display: none; margin-top: 5px;">
            Cannot order more than available stock
        </div> <!-- Error message moved to its own line -->
        <p><strong>In Stock:</strong> ${product.StockQty} left</p> <!-- Display stock quantity -->
        <label>
            <input type="checkbox" name="items[]" value="${product.IDProduct}" ${product.StockQty === 0 ? 'disabled' : ''}>
            Select Item
        </label>
    </div>
  `;

                    // Append itemBox to the catalog
                    container.innerHTML += itemBox;
                });

                // Add event listener to validate quantity inputs dynamically
                container.addEventListener("input", (event) => {
                    if (event.target.type === "number") {
                        const input = event.target;
                        const maxStock = parseInt(input.getAttribute("max"), 10);
                        const errorSpan = input.closest(".item-box").querySelector(".stock-error");

                        if (parseInt(input.value, 10) > maxStock) {
                            input.value = maxStock; // Restrict value to max stock
                            errorSpan.style.display = "block"; // Show error message in a new line
                        } else {
                            errorSpan.style.display = "none"; // Hide error message
                        }
                    }
                });
            });

        // Toggle the visibility of the customer list
        function toggleCustomerList() {
            const customerList = document.querySelector(".customer-list");
            customerList.style.display =
                customerList.style.display === "none" || customerList.style.display === "" ?
                "block" :
                "none";
        }
    </script>

</head>

<body>

    <div class="selected-customers">
        <button class="dropdown-button" onclick="toggleCustomerList()">Show/Hide Selected Customers</button>
        <div class="customer-list">
            <?php
            // Check if there are selected customers passed through the URL
            if (isset($_GET['selected_customers']) && !empty($_GET['selected_customers'])) {
                $selectedCustomers = $_GET['selected_customers']; // Array of selected customer IDs
                $customerNames = $_GET['customer_names']; // Array of customer names

                // Loop through the selected customers and display them
                foreach ($selectedCustomers as $customerID) {
                    if (isset($customerNames[$customerID])) {
                        $customerName = $customerNames[$customerID];
                        echo '<div class="customer-item">';
                        echo '<div class="customer-id">Customer ID: ' . htmlspecialchars($customerID) . '</div>';
                        echo '<div class="customer-name">Name: ' . htmlspecialchars($customerName) . '</div>';
                        echo '</div>';
                    }
                }
            } else {
                echo '<div class="empty-message">No customers selected.</div>';
            }
            ?>
        </div>
    </div>

    <h1>Items for Sale</h1>
    <form id="order-form" action="receipt.php" method="GET">
        <div class="item-container">
            <!-- Product items will be dynamically added here -->
        </div>

        <!-- Hidden fields for passing customer data -->
        <div id="customer-data">
            <?php
            if (isset($_GET['selected_customers']) && !empty($_GET['selected_customers'])) {
                foreach ($selectedCustomers as $customerID) {
                    if (isset($customerNames[$customerID])) {
                        // Add hidden inputs for customer IDs and names
                        echo '<input type="hidden" name="selected_customers[]" value="' . htmlspecialchars($customerID) . '">';
                        echo '<input type="hidden" name="customer_names[' . htmlspecialchars($customerID) . ']" value="' . htmlspecialchars($customerNames[$customerID]) . '">';
                    }
                }
            }
            ?>
        </div>
        <p style="text-align: center;">
            <button type="submit" style="display: inline-block; background-color: #4CAF50; color: white; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: 0.3s;">
                Submit
            </button>
        </p>
    </form>

    <p style="text-align: center;">
        <a href="index.php" style="display: inline-block; background-color: purple; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; transition: 0.3s;">
            Back to Select Customer
        </a>
    </p>
</body>

</html>