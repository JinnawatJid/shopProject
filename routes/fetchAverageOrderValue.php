<?php
// Include the database connection
include __DIR__ . '/../condb.php';

$sql = "SELECT AVG(total_price) AS avg_order_value 
            FROM (SELECT SUM(p.PricePerUnit * d.Qty) AS total_price 
                  FROM transaction_detail d
                  JOIN stock p ON d.IDProduct = p.IDProduct
                  GROUP BY d.IDtrans) AS order_totals";
$result = mysqli_query($conn, $sql);

// ตรวจสอบผลลัพธ์
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $AOV = $row && !is_null($row['avg_order_value']) ? $row['avg_order_value'] : 0;
    echo json_encode(["total" => round($AOV, 2)]); // ปัดเศษให้ดูสวยงาม
} else {
    echo json_encode(["error" => "Query failed"]);
}

// Close the database connection
$conn->close();
?>
