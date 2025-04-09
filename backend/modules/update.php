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
    $order_name = trim($data['order_name']); // Trim whitespace
    $order_size = trim($data['order_size']); // Trim whitespace
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
    error_log("Looking for item with name: '$order_name' and size: '$order_size'");
    
    // Find and update the specific item in the items_ordered array
    $updated = false;
    $found_items = [];
    
    // Special case for "Fried" which should match "Chicken"
    $special_match = false;
    if (strtolower($order_name) === 'fried') {
        error_log("Special case detected: 'Fried' - will try to match with 'Chicken'");
        // We'll look for "Chicken" as "Fried" might be referring to Fried Chicken
        $alternate_name = 'Chicken';
        $special_match = true;
    }
    
    foreach ($items_ordered as $index => &$item) {
        // Debug each item for logging
        error_log("Checking item $index: " . print_r($item, true));
        
        // Try all possible property names for order_name
        $item_name = null;
        if (isset($item['order_name'])) $item_name = trim($item['order_name']);
        else if (isset($item['title'])) $item_name = trim($item['title']);
        else if (isset($item['name'])) $item_name = trim($item['name']);
        
        // Try all possible property names for order_size
        $item_size = null;
        if (isset($item['order_size'])) $item_size = trim($item['order_size']);
        else if (isset($item['size'])) $item_size = trim($item['size']);
        
        // Format for display in found_items
        $quantity_str = "";
        if (isset($item['order_quantity'])) {
            // Handle both "x1" format and numeric format
            $quantity_str = is_numeric($item['order_quantity']) ? 
                " (x".$item['order_quantity'].")" : 
                " (".$item['order_quantity'].")";
        }
        
        // If we found valid name and size, add to our found_items for logging
        if ($item_name !== null && $item_size !== null) {
            $found_items[] = "$item_name - $item_size$quantity_str";
        }
        
        // Log actual string comparison for debugging
        error_log("Comparing: '$item_name' vs '$order_name' and '$item_size' vs '$order_size'");
        error_log("Case-insensitive comparison: " . (strcasecmp($item_name, $order_name) === 0 ? "true" : "false") . ", " . (strcasecmp($item_size, $order_size) === 0 ? "true" : "false"));
        
        if ($special_match) {
            error_log("Also comparing with alternate name: '$item_name' vs '$alternate_name'");
        }
        
        // Improved item matching logic with flexible comparison
        $name_matches = false;
        $size_matches = false;
        
        // Check name match - try different comparison methods
        if ($item_name !== null) {
            // Direct match
            if (strcasecmp($item_name, $order_name) === 0) {
                $name_matches = true;
                error_log("Direct name match found between '$item_name' and '$order_name'");
            }
            // Special case for "Fried" vs "Chicken"
            else if ($special_match && strcasecmp($item_name, $alternate_name) === 0) {
                $name_matches = true;
                error_log("Special case match found: '$item_name' matches alternate name '$alternate_name'");
            }
            // Check if item name contains the order name or vice versa
            else if (stripos($item_name, $order_name) !== false || stripos($order_name, $item_name) !== false) {
                $name_matches = true;
                error_log("Partial name match found between '$item_name' and '$order_name'");
            }
        }
        
        // Check size match - use case-insensitive comparison
        if ($item_size !== null) {
            if (strcasecmp($item_size, $order_size) === 0) {
                $size_matches = true;
                error_log("Size match found between '$item_size' and '$order_size'");
            }
        }
        
        // If both name and size match, update the item
        if ($name_matches && $size_matches) {
            error_log("Found matching item at index $index: $item_name - $item_size");
            
            // Make sure the 'delivered' property is set, even if it wasn't before
            if (!isset($item['delivered'])) {
                $item['delivered'] = "0"; // Default to not delivered
            }
            
            // Update the delivered status
            $item['delivered'] = $delivered;
            $updated = true;
            break;
        }
    }
    
    if (!$updated) {
        error_log("Item not found. Available items: " . implode(", ", $found_items));
        
        // Try a second pass with more flexible matching
        foreach ($items_ordered as $index => &$item) {
            $item_name = null;
            if (isset($item['order_name'])) $item_name = trim($item['order_name']);
            else if (isset($item['title'])) $item_name = trim($item['title']);
            else if (isset($item['name'])) $item_name = trim($item['name']);
            
            $item_size = null;
            if (isset($item['order_size'])) $item_size = trim($item['order_size']);
            else if (isset($item['size'])) $item_size = trim($item['size']);
            
            // Very flexible name matching (any overlap of words)
            $name_words_match = false;
            if ($item_name !== null && $order_name !== null) {
                $item_words = preg_split('/\s+/', strtolower($item_name));
                $order_words = preg_split('/\s+/', strtolower($order_name));
                
                // Check if any word from order_name appears in item_name
                foreach ($order_words as $word) {
                    if (in_array($word, $item_words) && strlen($word) > 2) {  // Only match on words longer than 2 chars
                        $name_words_match = true;
                        error_log("Word match found: '$word' between '$item_name' and '$order_name'");
                        break;
                    }
                }
            }
            
            if ($name_words_match && strcasecmp($item_size, $order_size) === 0) {
                error_log("Found matching item using word matching at index $index: $item_name - $item_size");
                
                if (!isset($item['delivered'])) {
                    $item['delivered'] = "0";
                }
                
                $item['delivered'] = $delivered;
                $updated = true;
                break;
            }
        }
        
        if (!$updated) {
            echo json_encode([
                "success" => false, 
                "message" => "Item not found in order. Looking for: '$order_name - $order_size'", 
                "available_items" => $found_items
            ]);
            return;
        }
    }
    
    // Update the items_ordered field in the que_orders table and change status to done if item is delivered
    $items_ordered_json = json_encode($items_ordered);
    
    // Check if all items are delivered or if any items are undelivered
    $all_delivered = true;
    $any_delivered = false;
    $total_items = 0;
    $delivered_items = 0;
    
    foreach ($items_ordered as $item) {
        $total_items++;
        
        // Handle different 'delivered' value formats (string/boolean)
        $is_delivered = false;
        if (isset($item['delivered'])) {
            if (is_string($item['delivered'])) {
                $is_delivered = ($item['delivered'] === "1" || $item['delivered'] === "true");
            } else if (is_bool($item['delivered'])) {
                $is_delivered = $item['delivered'];
            } else if (is_numeric($item['delivered'])) {
                $is_delivered = ((int)$item['delivered'] === 1);
            }
        }
        
        if ($is_delivered) {
            $delivered_items++;
            $any_delivered = true;
        } else {
            $all_delivered = false;
        }
    }
    
    error_log("Delivery count: $delivered_items of $total_items items are delivered");
    
    // Determine whether to change the order status
    $new_status = $current_status;
    
    // Check if all items are delivered - this should make the order 'done'
    if ($all_delivered) {
        $new_status = 'done';
        $status_changed = ($current_status !== 'done');
        error_log("All items are delivered - setting status to 'done'. Status changed: " . ($status_changed ? "Yes" : "No"));
    } 
    // Check if not all items are delivered and status is currently 'done' - revert to 'pending'
    else if (!$all_delivered && $current_status === 'done') {
        $new_status = 'pending';
        $status_changed = true;
        error_log("Not all items delivered - reverting status to 'pending'");
    }
    // No change needed
    else {
        error_log("No status change required. Current status: $current_status, All delivered: " . ($all_delivered ? "true" : "false"));
    }
    
    // Diagnostics to debug delivery status
    error_log("STATUS CHECK - All delivered: " . ($all_delivered ? "true" : "false") . 
              ", Any delivered: " . ($any_delivered ? "true" : "false") . 
              ", Current status: $current_status, New status: $new_status, 
              Status changed: " . ($status_changed ? "true" : "false") . 
              ", Delivered items: $delivered_items of $total_items total");
    
    // For each item, log its delivered status
    foreach ($items_ordered as $idx => $item) {
        $item_delivered = isset($item['delivered']) ? $item['delivered'] : "unknown";
        error_log("Item $idx: " . 
                 (isset($item['order_name']) ? $item['order_name'] : "unnamed") . 
                 " - delivered: $item_delivered");
    }
    
    // Update the database
    if ($status_changed) {
        $update_stmt = $conn->prepare("UPDATE que_orders SET items_ordered = ?, order_status = ? WHERE receipt_number = ? OR que_order_no = ?");
        $update_stmt->bind_param("ssss", $items_ordered_json, $new_status, $receipt_number, $receipt_number);
        error_log("Updating order status to $new_status for receipt: $receipt_number");
    } else {
        $update_stmt = $conn->prepare("UPDATE que_orders SET items_ordered = ? WHERE receipt_number = ? OR que_order_no = ?");
        $update_stmt->bind_param("sss", $items_ordered_json, $receipt_number, $receipt_number);
    }
    
    $success = $update_stmt->execute();
    
    if ($success) {
        $message = $delivered === "1" ? "Item marked as delivered" : "Item marked as not delivered";
        
        if ($status_changed) {
            $message .= " and order status changed to " . strtoupper($new_status);
        }
        
        echo json_encode([
            "success" => true, 
            "message" => $message, 
            "status_changed" => $status_changed,
            "new_status" => $new_status,
            "all_delivered" => $all_delivered,
            "any_delivered" => $any_delivered,
            "delivered_count" => "$delivered_items of $total_items"
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update delivery status: " . $update_stmt->error]);
    }
}
?>