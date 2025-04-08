<?php
include '../config/connection.php';

// Set headers for CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if action parameter is set
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        case 'updateOrderStatus':
            updateOrderStatus($conn);
            break;
            
        case 'updateDeliveryStatus':
            updateDeliveryStatus($conn);
            break;
            
        default:
            echo json_encode(["success" => false, "message" => "Invalid action specified"]);
            break;
    }
} else {
    echo json_encode(["success" => false, "message" => "No action specified"]);
}

$conn->close();

// Function to update order status (processing, done, etc.)
function updateOrderStatus($conn) {
    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    error_log(print_r($input, true)); // Log the input for debugging

    // Extract variables
    $receipt_number = $input['receipt_number'] ?? null;
    $order_status = $input['order_status'] ?? null;

    // Validate input
    if ($receipt_number && $order_status) {
        // Prepare the SQL statement - receipt_number could either be in receipt_number or que_order_no
        $stmt = $conn->prepare("UPDATE que_orders SET order_status = ? WHERE receipt_number = ? OR que_order_no = ?");
        $stmt->bind_param("sii", $order_status, $receipt_number, $receipt_number);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Order status updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update order status.', 'error' => $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    }
}

function updateDeliveryStatus($conn) {
    // Get the JSON data from the request body
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Log the received data for debugging
    error_log("Received delivery status update: " . print_r($data, true));
    
    // Check if all required data is present
    if (!isset($data['receipt_number']) || !isset($data['order_name']) || !isset($data['order_size']) || !isset($data['delivered'])) {
        echo json_encode(["success" => false, "message" => "Missing required data"]);
        return;
    }
    
    $receipt_number = $data['receipt_number'];
    $order_name = $data['order_name'];
    $order_size = $data['order_size'];
    $delivered = $data['delivered'];
    $status_changed = false; // Initialize status_changed variable
    
    // Get the current que_orders record
    $stmt = $conn->prepare("SELECT items_ordered, order_status FROM que_orders WHERE receipt_number = ? OR que_order_no = ?");
    $stmt->bind_param("ss", $receipt_number, $receipt_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Order not found with receipt number: $receipt_number"]);
        return;
    }
    
    $row = $result->fetch_assoc();
    $items_ordered_json = $row['items_ordered'];
    $current_status = $row['order_status'];
    
    error_log("Raw items_ordered JSON: " . $items_ordered_json);
    
    $items_ordered = json_decode($items_ordered_json, true);
    
    if (!is_array($items_ordered)) {
        echo json_encode(["success" => false, "message" => "Invalid items_ordered data structure"]);
        error_log("JSON decode error: " . json_last_error_msg());
        return;
    }
    
    // Debug the items_ordered structure
    error_log("Decoded items_ordered: " . print_r($items_ordered, true));
    error_log("Looking for item with name: $order_name and size: $order_size");
    
    // Find and update the specific item in the items_ordered array
    $updated = false;
    $found_items = [];
    
    foreach ($items_ordered as $index => &$item) {
        // Debug each item for logging
        error_log("Checking item $index: " . print_r($item, true));
        
        // Try all possible property names for order_name
        $item_name = null;
        if (isset($item['order_name'])) $item_name = $item['order_name'];
        else if (isset($item['title'])) $item_name = $item['title'];
        else if (isset($item['name'])) $item_name = $item['name'];
        
        // Try all possible property names for order_size
        $item_size = null;
        if (isset($item['order_size'])) $item_size = $item['order_size'];
        else if (isset($item['size'])) $item_size = $item['size'];
        
        // If we found valid name and size, add to our found_items for logging
        if ($item_name !== null && $item_size !== null) {
            $found_items[] = "$item_name - $item_size";
        }
        
        // Improved item matching logic (case insensitive and partial matching)
        $name_matches = false;
        $size_matches = false;
        
        // Check name match - use case-insensitive comparison
        if ($item_name !== null) {
            $name_matches = (strtolower($item_name) === strtolower($order_name));
            
            // If no exact match, try partial match
            if (!$name_matches && strpos(strtolower($item_name), strtolower($order_name)) !== false) {
                $name_matches = true;
            }
        }
        
        // Check size match - use case-insensitive comparison
        if ($item_size !== null) {
            $size_matches = (strtolower($item_size) === strtolower($order_size));
        }
        
        // If both name and size match, update the item
        if ($name_matches && $size_matches) {
            error_log("Found matching item at index $index: $item_name - $item_size");
            $item['delivered'] = $delivered;
            $updated = true;
            break;
        }
    }
    
    if (!$updated) {
        error_log("Item not found. Available items: " . implode(", ", $found_items));
        echo json_encode([
            "success" => false, 
            "message" => "Item not found in order. Looking for: '$order_name - $order_size'", 
            "available_items" => $found_items
        ]);
        return;
    }
    
    // Update the items_ordered field in the que_orders table and change status to done if item is delivered
    $items_ordered_json = json_encode($items_ordered);
    
    // If item is marked as delivered, update the order status to 'done' regardless of other items
    if ($delivered === "1" && $current_status === 'pending') {
        $update_stmt = $conn->prepare("UPDATE que_orders SET items_ordered = ?, order_status = 'done' WHERE receipt_number = ? OR que_order_no = ?");
        $update_stmt->bind_param("sss", $items_ordered_json, $receipt_number, $receipt_number);
        error_log("Marked as delivered! Updating order status to done for receipt: " . $receipt_number);
        $status_changed = true;
    } else {
        $update_stmt = $conn->prepare("UPDATE que_orders SET items_ordered = ? WHERE receipt_number = ? OR que_order_no = ?");
        $update_stmt->bind_param("sss", $items_ordered_json, $receipt_number, $receipt_number);
        $status_changed = false;
    }
    
    $success = $update_stmt->execute();
    
    if ($success) {
        $message = "Delivery status updated successfully";
        if ($status_changed) {
            $message .= " and order marked as done";
        }
        echo json_encode([
            "success" => true, 
            "message" => $message, 
            "status_changed" => $status_changed,
            "new_status" => $status_changed ? "done" : $current_status
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update delivery status: " . $update_stmt->error]);
    }
}
?>