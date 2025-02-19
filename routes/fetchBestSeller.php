<?php
include __DIR__ . '/../condb.php';

function getBestSeller($conn) {
    $sql = "SELECT td.IDProduct, s.ProductName, SUM(td.Qty) AS TotalSold
            FROM transaction_detail td
            JOIN stock s ON td.IDProduct = s.IDProduct
            GROUP BY td.IDProduct, s.ProductName
            ORDER BY TotalSold DESC
            LIMIT 3";
    $result = mysqli_query($conn, $sql);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

$bestSellerData = getBestSeller($conn);
echo json_encode($bestSellerData);

$conn->close();
?>
