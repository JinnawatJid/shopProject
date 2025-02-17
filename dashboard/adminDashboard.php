<?php
// Include the database connection
include __DIR__ . '/../condb.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        <?php include '../style/dashboard.css'; ?>
    </style>
</head>

<body>
    <!-- Go to Index Button -->
    <div class="go-to-index">
        <a href="../index.php" style="text-decoration: none;">
            <button>Go to Index</button>
        </a>
    </div>
    <div class="container">
        <h1>Admin Dashboard</h1>

        <!-- Section to display orders -->
        <div class="section">
            <h2>Orders</h2>
            <div id="orders"></div>
        </div>

        <!-- Section to display pickup list -->
        <div class="section">
            <h2>Pick Up List</h2>
            <div id="pkList"></div>
        </div>

        <!-- Section to display stock -->
        <div class="section">
            <h2>Stock</h2>
            <div id="stock"></div>
        </div>
    </div>

    <script>
        // Function to fetch and display orders
        function fetchOrders() {
            fetch('../routes/fetchTransactionHeader.php')
                .then(response => response.json())
                .then(data => {
                    let ordersHtml = '<table><tr><th>IDtrans</th><th>IDCust</th><th>CustName</th><th>Status</th><th>Pending Timestamp</th><th>Approve Timestamp</th><th>Actions</th></tr>';
                    data.forEach(order => {
                        // Determine the CSS class based on the status
                        let statusClass = '';
                        if (order.status === 'Pending') {
                            statusClass = 'status-pending';
                        } else if (order.status === 'Approve') {
                            statusClass = 'status-approve';
                        } else if (order.status === 'Cancel') {
                            statusClass = 'status-cancel';
                        }
                        ordersHtml += `<tr>
                            <td>${order.IDtrans}</td>
                            <td>${order.IDCust}</td>
                            <td>${order.CustName}</td>
                            <td class="${statusClass}">${order.status}</td>
                            <td>${order.PendingTimestamp}</td>
                            <td>${order.ApproveTimestamp}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="approve" onclick="updateOrderStatus(${order.IDtrans}, 'Approve')">Approve</button>
                                    <button class="cancel" onclick="updateOrderStatus(${order.IDtrans}, 'Cancel')">Cancel</button>
                                </div>
                            </td>
                        </tr>`;
                    });
                    ordersHtml += '</table>';
                    document.getElementById('orders').innerHTML = ordersHtml;
                });
        }

        // Function to fetch and display stock
        function fetchStock() {
            fetch('../routes/fetchProduct.php')
                .then(response => response.json())
                .then(data => {
                    let stockHtml = '<table><tr><th>IDProduct</th><th>ProductName</th><th>ProductDesc</th><th>PricePerUnit</th><th>StockQty</th></tr>';
                    data.forEach(product => {
                        stockHtml += `<tr>
                            <td>${product.IDProduct}</td>
                            <td>${product.ProductName}</td>
                            <td>${product.ProductDesc}</td>
                            <td>${product.PricePerUnit}</td>
                            <td>${product.StockQty}</td>
                        </tr>`;
                    });
                    stockHtml += '</table>';
                    document.getElementById('stock').innerHTML = stockHtml;
                });
        }

        function fetchPickUpList() {
            fetch('../routes/fetchPickupList.php')
                .then(response => response.json())
                .then(data => {
                    console.log("Pickup List Data:", data); // Add this line to inspect the data
                    let pkListHtml = '<table><tr><th>Date</th><th>IDProduct</th><th>ProductName</th><th>Qty</th><th>Status</th></tr>';
                    data.forEach(item => {
                        let statusClass = '';
                        if (item.Status === 'Pending') { // Assuming 'status' should be 'Status'
                            statusClass = 'status-pending';
                        } else if (item.Status === 'Approve') { // Assuming 'status' should be 'Status'
                            statusClass = 'status-approve';
                        } else if (item.Status === 'Cancel') { // Assuming 'status' should be 'Status'
                            statusClass = 'status-cancel';
                        }
                        pkListHtml += `<tr>
                    <td>${item.pickup_date}</td>
                    <td>${item.IDProduct}</td>
                    <td>${item.ProductName}</td>
                    <td>${item.Qty}</td>
                    <td class="${statusClass}">${item.Status}</td>
                </tr>`;
                    });
                    pkListHtml += '</table>';
                    document.getElementById('pkList').innerHTML = pkListHtml;
                })
                .catch(error => console.error('Error fetching pickup list:', error));
        }

        // Function to update order status
        function updateOrderStatus(orderId, status) {
            if (confirm(`Are you sure you want to ${status.toLowerCase()} this order?`)) {
                fetch('../routes/updateOrderStatus.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            orderId: orderId,
                            status: status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(`Order status updated to ${status}`);
                            fetchOrders(); // Refresh the orders list
                        } else {
                            alert('Failed to update order status');
                        }
                    });
            }
        }

        // Fetch orders, stock, and pickup list on page load
        fetchOrders();
        fetchStock();
        fetchPickUpList();
    </script>
</body>

</html>