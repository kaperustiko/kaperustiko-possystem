<?php
include '../config/connection.php';

// Log the request method for debugging
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

// Route handling based on request type
$requestMethod = $_SERVER['REQUEST_METHOD'];
switch ($requestMethod) {
    case 'DELETE':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'deleteProduct':
                    delete_product($conn);
                    break;

                case 'deleteAllOrders':
                    delete_all_orders($conn);
                    break;

                case 'voidOrder':
                    void_order($conn);
                    break;

                case 'deleteSalesInformation':
                    delete_sales_information($conn);
                    break;

                case 'deleteVoucher':
                    delete_voucher($conn);
                    break;
                case 'deleteTableOccupancy':
                    delete_table_occupancy($conn);
                    break;

                case 'deleteReturn':
                    $return_id = $_GET['return_id'] ?? null;
                    if ($return_id) {
                        $stmt = $conn->prepare("DELETE FROM `remit_returns` WHERE return_id = ?");
                        $stmt->bind_param("i", $return_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true]);
                        } else {
                            echo json_encode(["success" => false, "message" => $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(["success" => false, "message" => "No return ID provided"]);
                    }
                    break;

                case 'deleteRemit':
                    $remit_id = $_GET['remit_id'] ?? null;
                    if ($remit_id) {
                        $stmt = $conn->prepare("DELETE FROM remit_sales WHERE remit_id = ?");
                        $stmt->bind_param("i", $remit_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true]);
                        } else {
                            echo json_encode(["success" => false, "message" => $stmt->error]);
                        }
                        $stmt->close();
                    }
                    break;

                case 'deleteByTableNumber':
                    $table_number = $_GET['table_number'] ?? null;
                    if ($table_number) {
                        $stmt = $conn->prepare("DELETE FROM reserve_table WHERE table_number = ?");
                        $stmt->bind_param("i", $table_number);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true]);
                        } else {
                            echo json_encode(["success" => false, "message" => $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(["success" => false, "message" => "No table number provided"]);
                    }
                    break;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "No action specified"]);
        }
        break;

    case 'OPTIONS':
        // Handle preflight request
        http_response_code(200);
        exit;

    default:
        echo json_encode(["status" => "error", "message" => "Method not allowed"]);
        break;
}

// Close the connection
$conn->close();

function delete_product($conn) {
    // Get the code from the request
    $data = json_decode(file_get_contents("php://input"), true);
    $code = $data['code'] ?? null;

    if ($code) {
        // Prepare and bind
        $stmt = $conn->prepare("DELETE FROM `pos-menu` WHERE `code` = ?");
        $stmt->bind_param("s", $code);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["message" => "Product deleted successfully."]);
        } else {
            echo json_encode(["message" => "Error deleting product: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["message" => "No code provided."]);
    }
}

function delete_all_orders($conn) {
    // SQL to delete all records
    $sql = "DELETE FROM orders";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "All orders deleted successfully."]);
    } else {
        echo json_encode(["message" => "Error deleting orders: " . $conn->error]);
    }
}

function void_order($conn) {
    $orderName = $_GET['order_name'] ?? null;
    $orderSize = $_GET['order_size'] ?? null;
    
    // Get request payload for additional item details
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Log for debugging
    error_log("Voiding order: " . $orderName . " size: " . $orderSize);
    error_log("Request data: " . print_r($data, true));

    if ($orderName && $orderSize) {
        // Use more specific query with order_name AND order_size
        $query = "DELETE FROM orders WHERE order_name = ? AND order_size = ?";
        
        // If we received additonal data with add-ons, use those too
        if (!empty($data) && isset($data['order_addons'])) {
            $query = "DELETE FROM orders WHERE order_name = ? AND order_size = ? AND order_addons = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $orderName, $orderSize, $data['order_addons']);
        } else {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $orderName, $orderSize);
        }

        // Execute the statement
        if ($stmt->execute()) {
            // Check if we deleted exactly one row
            if ($stmt->affected_rows == 1) {
                echo json_encode(["message" => "Order voided successfully."]);
            } else {
                echo json_encode(["message" => "Order voided successfully. Affected rows: " . $stmt->affected_rows]);
            }
        } else {
            echo json_encode(["message" => "Error voiding order: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["message" => "Missing order name or size."]);
    }
}

function delete_sales_information($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    $receipt_number = $data['receipt_number'] ?? null;

    if ($receipt_number) {
        // Prepare and bind
        $stmt = $conn->prepare("DELETE FROM total_sales WHERE receipt_number = ?");
        $stmt->bind_param("s", $receipt_number);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Sale deleted successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error deleting sale: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "No receipt number provided."]);
    }
}

function delete_voucher($conn) {
    $voucher_code = $_GET['voucher_code'] ?? null;
    if ($voucher_code) {
        $stmt = $conn->prepare("DELETE FROM vouchers WHERE voucher_code = ?");
        $stmt->bind_param("s", $voucher_code);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Voucher deleted successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error deleting voucher: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "No voucher code provided."]);
    }
}

function delete_table_occupancy($conn) {
    $receipt_number = $_GET['receipt_number'] ?? null;
    if ($receipt_number) {
        $stmt = $conn->prepare("DELETE FROM que_orders WHERE que_order_no = ?");
        $stmt->bind_param("i", $receipt_number);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Table occupancy deleted successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error deleting table occupancy: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "No table number provided."]);
    }
}