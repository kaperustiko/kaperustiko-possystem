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
        
        // Check if que_orders has total_order column for order numbering
        $checkTotalOrder = $conn->query("SHOW COLUMNS FROM que_orders LIKE 'total_order'");
        if ($checkTotalOrder->num_rows == 0) {
            // Add the total_order column if it doesn't exist
            $conn->query("ALTER TABLE que_orders ADD COLUMN total_order INT DEFAULT 0");
            error_log("Added total_order column to que_orders table");
        }
        
        // Check if the receipt_number already exists
        $checkReceipt = $conn->prepare("SELECT COUNT(*) as count FROM que_orders WHERE receipt_number = ?");
        $checkReceipt->bind_param("i", $receiptNumber);
        $checkReceipt->execute();
        $result = $checkReceipt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            // If the receipt number already exists, get the next available receipt number
            $stmt = $conn->query("SELECT MAX(receipt_number) as max_receipt FROM que_orders");
            $maxRow = $stmt->fetch_assoc();
            $receiptNumber = ($maxRow['max_receipt'] ?? 0) + 1;
            error_log("Receipt number already exists, using next available: $receiptNumber");
        }
        
        // Insert into que_orders with a unique que_order_no
        $stmt = $conn->prepare("INSERT INTO que_orders (receipt_number, date, time, items_ordered, total_amount, amount_paid, amount_change, order_take, table_number, order_status, waiter_name, waiter_code, total_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiiisissis", $receiptNumber, $date, $time, $itemsOrderedJson, $totalAmount, $amountPaid, $change, $orderTake, $tableNumber, $orderStatus, $waiterName, $waiterCode, $receiptNumber);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["message" => "Order queued successfully", "receipt_number" => $receiptNumber]);
        } else {
            echo json_encode(["error" => "Error queuing order: " . $stmt->error]);
        }
        $stmt->close();
    }
}
?>