<?php
// Include the database connection
include __DIR__ . '/../condb.php';

$totalOrders = getTotalOrders($conn);
$bestSellerData = getBestSeller($conn);
$averageOrderValue = getAverageOrderValue($conn);


$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "fullstack";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Sum Order
function getTotalOrders($conn) {
    $sql = "SELECT COUNT(*) AS total FROM OrderHeader";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

// Best Seller
function getBestSeller($conn) {
    $sql = "SELECT p.ProductName, SUM(d.Qty) AS total_sold 
    FROM OrderDetail d
    JOIN Product p ON d.IDProduct = p.IDProduct
    GROUP BY d.IDProduct 
    ORDER BY total_sold DESC 
    LIMIT 5";
    $result = mysqli_query($conn, $sql);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// AOV
function getAverageOrderValue($conn) {
    $sql = "SELECT AVG(total_price) AS avg_order_value 
            FROM (SELECT SUM(p.PricePerUnit * d.Qty) AS total_price 
                  FROM OrderDetail d
                  JOIN Product p ON d.IDProduct = p.IDProduct
                  GROUP BY d.OrderID) AS order_totals";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return number_format($row['avg_order_value'] ?? 0, 2);
}

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

        
    <div class="dashboard-container">
        <!-- Box 1: จำนวน Order ทั้งหมด -->
        <div class="box">
            <h3>จำนวนออเดอร์ทั้งหมด</h3>
            <p style="font-size: 24px; font-weight: bold;"><?= $totalOrders ?></p>
        </div>

        <!-- Box 2: สินค้า Best Seller -->
        <div class="box">
            <h3>สินค้าขายดี</h3>
            <canvas id="bestSellerChart"></canvas>
        </div>

        <!-- Box 3: ราคาเฉลี่ยต่อออเดอร์ -->
        <div class="box">
            <h3>ราคาเฉลี่ยต่อออเดอร์</h3>
            <p style="font-size: 24px; font-weight: bold;">฿<?= $averageOrderValue ?></p>
        </div>
    </div>

        <!-- Section to display orders -->
        <div class="section">
            <h2>Orders</h2>
            <div id="orders"></div>
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

        // Fetch orders and stock on page load
        fetchOrders();
        fetchStock();
    </script>
</body>

</html>