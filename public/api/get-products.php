<?php
// API endpoint to get products with available quantities
header('Content-Type: application/json');

try {
    // Use the same database connection as other models
    $db = new mysqli('localhost', 'root', '', 'dheergayu_db');
    
    if ($db->connect_error) {
        throw new Exception("Database connection failed: " . $db->connect_error);
    }
    
    // Get products with their total quantities from batches
    $sql = "SELECT p.product_id, p.name, 
                   COALESCE(SUM(b.quantity), 0) AS total_quantity,
                   COUNT(b.product_id) AS batches_count
            FROM products p
            LEFT JOIN batches b ON b.product_id = p.product_id
            GROUP BY p.product_id, p.name
            ORDER BY p.name";
    
    $result = $db->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $db->error);
    }
    
    $formattedProducts = [];
    while ($row = $result->fetch_assoc()) {
        $formattedProducts[] = [
            'id' => (int)$row['product_id'],
            'name' => $row['name'],
            'available_quantity' => (int)$row['total_quantity'],
            'batches_count' => (int)$row['batches_count']
        ];
    }
    
    $db->close();
    
    echo json_encode([
        'success' => true,
        'products' => $formattedProducts
    ]);
    
} catch (Exception $e) {
    error_log("Error getting products: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load products: ' . $e->getMessage()
    ]);
}
?>
