<?php
include '../config/connection.php';

// Handle different POST actions
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // For debugging
    error_log("Received data: " . print_r($data, true));

    // Handle queuing order
    if (isset($data['saveQueOrder'])) {

        if (!isset($data['date'], $data['time'], $data['cashierName'], $data['itemsOrdered'], $data['totalAmount'], $data['order_take'], $data['table_number'])) {
            echo json_encode(["error" => "Invalid input data"]);
            exit; // Stop execution if input is invalid
        }

        $receiptNumber = $data['receiptNumber'];
        $date = $data['date'];
        $time = $data['time'];
        $cashierName = $data['cashierName'];
        $waiterName = isset($data['waiterName']) ? $data['waiterName'] : ''; // Get waiter name
        $waiterCode = isset($data['waiterCode']) ? $data['waiterCode'] : ''; // Get waiter code
        $itemsOrderedJson = json_encode($data['itemsOrdered']); // Encode modified itemsOrdered
        $totalAmount = $data['totalAmount'];
        $amountPaid = isset($data['amountPaid']) ? $data['amountPaid'] : 0; // Default to 0 if not set
        $change = isset($data['change']) ? $data['change'] : 0; // Default to 0 if not set
        $orderTake = $data['order_take'];
        $tableNumber = $data['table_number']; // New variable for table number
        $orderStatus = "pending"; // Default order status
        
        // Removed the table availability check to allow multiple orders on the same table
        
        // Check if que_orders table has waiter_name and waiter_code columns
        $columnsExist = false;
        $checkColumns = $conn->query("SHOW COLUMNS FROM que_orders LIKE 'waiter_name'");
        if ($checkColumns->num_rows == 0) {
            // Add the columns if they don't exist
            $conn->query("ALTER TABLE que_orders ADD COLUMN waiter_name VARCHAR(100) DEFAULT NULL, ADD COLUMN waiter_code VARCHAR(20) DEFAULT NULL");
            error_log("Added waiter_name and waiter_code columns to que_orders table");
        }
        
        // Insert into que_orders
        $stmt = $conn->prepare("INSERT INTO que_orders (receipt_number, date, time, items_ordered, total_amount, amount_paid, amount_change, order_take, table_number, order_status, waiter_name, waiter_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiiisisss", $receiptNumber, $date, $time, $itemsOrderedJson, $totalAmount, $amountPaid, $change, $orderTake, $tableNumber, $orderStatus, $waiterName, $waiterCode);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["message" => "Order queued successfully"]);
        } else {
            echo json_encode(["error" => "Error queuing order: " . $stmt->error]);
        }
        $stmt->close();
    }
}
?>