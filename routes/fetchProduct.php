<?php

include __DIR__ . '/../condb.php';

// Fetch products from the database
$sql = "SELECT IDProduct, ProductName, ProductDesc, PricePerUnit, ImageURL, StockQty FROM stock";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $products = array();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode($products); // Return products as JSON
} else {
    echo json_encode([]); // Return an empty array if no products found
}

?>
