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
    <div class="go-to-index">
        <a href="../index.php" style="text-decoration: none;">
            <button>Go to Index</button>
        </a>
        <div class="filter-section">
            <label for="startDate">Start:</label>
            <input type="date" id="startDate" name="startDate">

            <label for="endDate">End:</label>
            <input type="date" id="endDate" name="endDate">

            <button onclick="applyDateFilter()">Apply Filter</button>
        </div>
    </div>

    <div class="container">
        <h1>Admin Dashboard</h1>


        <div class="dashboard-container">
            <!-- Box 1: Sum Order  -->
            <div class="box">
                <h3>Summary Order</h3>
                <p style="font-size: 24px; font-weight: bold;" id="SummeryOrder"></p>
            </div>

            <!-- Box 2: สินค้า Best Seller -->
            <div class="box">
                <h3>Beset Seller</h3>
                <canvas id="bestSellerChart"></canvas>
            </div>

            <!-- Box 3: Average Order Value -->
            <div class="box">
                <h3>Average Order Value</h3>
                <p style="font-size: 24px; font-weight: bold;" id="AOV"></p>
            </div>
        </div>

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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <script>
        // Function to fetch and display Summary Order
        function fetchSumOrder(startDate = null, endDate = null) { // Add startDate and endDate parameters
            let url = '../routes/fetchSummaryOrder.php';
            if (startDate && endDate) {
                url += `?startDate=${startDate}&endDate=${endDate}`; // Append dates as query parameters
            }
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('SummeryOrder').textContent = data.total; // แสดงค่าตรงๆ ไม่ต้องใช้ table
                })
                .catch(error => console.error('Error fetching summary order:', error));
        }

        let bestSellerChartInstance = null; // Variable to hold the chart instance

        // Function to fetch and display Best Seller
        function fetchBestSeller(startDate = null, endDate = null) { // Add startDate and endDate parameters
            let url = '../routes/fetchBestSeller.php';
            if (startDate && endDate) {
                url += `?startDate=${startDate}&endDate=${endDate}`; // Append dates as query parameters
            }

            console.log("Fetching Best Seller from URL:", url); // Log the URL

            fetch(url)
                .then(response => {
                    console.log("Raw response status:", response.status); // Log the response status code
                    console.log("Raw response headers:", response.headers); // Log headers
                    return response.text(); // Get the raw response text first for debugging
                })
                .then(rawText => {
                    console.log("Raw response text:", rawText); // Log the raw text
                    let data; // Declare data outside try-catch scope
                    try {
                        data = JSON.parse(rawText); // Try to parse as JSON
                        console.log("Parsed JSON data:", data); // Log parsed JSON data
                    } catch (error) {
                        console.error('Error parsing JSON:', error);
                        console.error('Raw text that failed to parse:', rawText);
                        return; // Exit the function if JSON parsing fails
                    }

                    if (data.length > 0) { // Check if data is array and has length
                        let labels = data.map(item => item.ProductName);
                        let values = data.map(item => item.TotalSold);

                        let ctx = document.getElementById('bestSellerChart').getContext('2d');

                        // **Destroy existing chart if it exists**
                        if (bestSellerChartInstance) {
                            bestSellerChartInstance.destroy();
                        }

                        bestSellerChartInstance = new Chart(ctx, { // **Assign new chart to the variable**
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Quantity (pieces)',
                                    data: values,
                                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                                    borderColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: true
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => value + ' pcs'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    } else {
                        document.getElementById('bestSellerChart').parentElement.innerHTML = "<p style='color: red;'>No Best Seller Data</p>";
                    }
                })
                .catch(error => console.error('Error fetching best seller:', error));
        }
        // Function to fetch and display AverageOrderValue with date filter
        function fetchAOV(startDate = null, endDate = null) { // Add startDate and endDate parameters
            let url = '../routes/fetchAverageOrderValue.php';
            if (startDate && endDate) {
                url += `?startDate=${startDate}&endDate=${endDate}`; // Append dates as query parameters
            }

            console.log("Fetching AOV from URL:", url); // Log the URL

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('AOV').textContent = data.total; // Display AOV value
                })
                .catch(error => console.error('Error fetching Average Order Value:', error));
        }

        // Function to fetch and display orders
        function fetchOrders(startDate = null, endDate = null) { // Add startDate and endDate parameters
            let url = '../routes/fetchTransactionHeader.php';
            if (startDate && endDate) {
                url += `?startDate=${startDate}&endDate=${endDate}`; // Append dates as query parameters
            }

            console.log("Fetching Orders from URL:", url); // Log the URL

            fetch(url)
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

        function fetchPickUpList(startDate = null, endDate = null) { // Add startDate and endDate parameters
            let url = '../routes/fetchPickupList.php';
            if (startDate && endDate) {
                url += `?startDate=${startDate}&endDate=${endDate}`; // Append dates as query parameters
            }

            console.log("Fetching Pickup List from URL:", url); // Log the URL

            fetch(url)
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

        // Apply Date Filter Function
        function applyDateFilter() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            fetchSumOrder(startDate, endDate);
            fetchBestSeller(startDate, endDate);
            fetchAOV(startDate, endDate);
            fetchOrders(startDate, endDate);
            fetchPickUpList(startDate, endDate);
        }

        fetchPickUpList();
        fetchSumOrder();
        fetchBestSeller();
        fetchAOV();
        // Fetch orders and stock on page load
        fetchOrders();
        fetchStock();
    </script>
</body>

</html>