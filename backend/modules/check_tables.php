<?php
    include '../config/connection.php';
    
    // Query to get all tables with their orders that aren't 'done'
    $query = "SELECT table_number, COUNT(*) as order_count FROM que_orders WHERE order_status != 'done' GROUP BY table_number";
    $result = $conn->query($query);

    $tableStatus = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Store the count of active orders for each table
            $tableStatus[$row['table_number']] = true;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($tableStatus);
?>
    
    
    